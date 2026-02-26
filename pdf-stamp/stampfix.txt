<?php
include 'db.php';

header("Content-Type: application/json; charset=utf-8");
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Pragma: no-cache");
header("Expires: 0");

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$userId = (int)$_SESSION['user_id'];

// ===== CEK HAK STAMP =====
$qUser = $conn->query("SELECT can_stamp, role FROM users WHERE id = '$userId' LIMIT 1");
$user = $qUser ? $qUser->fetch_assoc() : null;

$canStamp = false;
if ($user) {
    $canStamp = ($user['role'] === 'admin') || ((int)$user['can_stamp'] === 1);
}
if (!$canStamp) {
    http_response_code(403);
    echo json_encode(['status' => 'error', 'message' => 'Tidak punya akses stamp']);
    exit;
}

// ===== INPUT JSON =====
$input = json_decode(file_get_contents("php://input"), true);

$pdf_id     = (int)($input['pdf_id'] ?? 0);
$pageNo     = (int)($input['page'] ?? 1);
$type       = preg_replace('/[^a-z0-9_]/i', '', (string)($input['type'] ?? ''));

$x_pct      = (float)($input['x_pct'] ?? 0);
$y_pct_top  = (float)($input['y_pct_top'] ?? 0);
$w_pct      = (float)($input['w_pct'] ?? 0);
$h_pct      = (float)($input['h_pct'] ?? 0);

if ($pdf_id <= 0 || $pageNo <= 0 || $type === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Parameter tidak valid']);
    exit;
}

// clamp nilai persen biar aman
$x_pct     = max(0.0, min(1.0, $x_pct));
$y_pct_top = max(0.0, min(1.0, $y_pct_top));
$w_pct     = max(0.0, min(1.0, $w_pct));
$h_pct     = max(0.0, min(1.0, $h_pct));

// ===== AMBIL DATA PDF =====
$res = $conn->query("SELECT * FROM list_pdf WHERE id = '$pdf_id' LIMIT 1");
$pdfData = $res ? $res->fetch_assoc() : null;
if (!$pdfData) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'PDF tidak ditemukan']);
    exit;
}

$fileName  = $pdfData['file_name'];

// ===== INPUT: FILE KERJA (TETAP) =====
$inputPath = "storage/$pdf_id/$fileName";
if (!file_exists($inputPath)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'File PDF kerja tidak ditemukan']);
    exit;
}

// ===== CEK FILE STAMP =====
$stampPath = "assets/stamps/$type.png";
if (!file_exists($stampPath)) {
    http_response_code(404);
    echo json_encode(['status' => 'error', 'message' => 'File stamp tidak ditemukan']);
    exit;
}

// ===== FOLDER APPROVE =====
$approveDir = "storage/$pdf_id/approve";
if (!is_dir($approveDir)) {
    if (!mkdir($approveDir, 0755, true)) {
        http_response_code(500);
        echo json_encode(['status' => 'error', 'message' => 'Gagal membuat folder approve']);
        exit;
    }
}

// ===== OUTPUT FIXED NAME (REPLACE) =====
$approvedPath = $approveDir . "/APPROVED_" . $fileName;

// ===== LOAD FPDI =====
require __DIR__ . "/vendor/autoload.php";
use setasign\Fpdi\Fpdi;

try {
    $pdf = new Fpdi();
    $pageCount = $pdf->setSourceFile($inputPath);

    if ($pageNo < 1 || $pageNo > $pageCount) {
        throw new Exception("Halaman tidak valid. Total halaman: $pageCount");
    }

    for ($i = 1; $i <= $pageCount; $i++) {
        $tplId = $pdf->importPage($i);
        $size  = $pdf->getTemplateSize($tplId);

        $pdf->AddPage($size['orientation'], [$size['width'], $size['height']]);
        $pdf->useTemplate($tplId);

        if ($i === $pageNo) {
            $pageW = (float)$size['width'];
            $pageH = (float)$size['height'];

            // % -> unit PDF
            $w = max(10, $w_pct * $pageW);
            $h = max(10, $h_pct * $pageH);
            $x = $x_pct * $pageW;

            // y_pct_top = jarak dari atas (top-origin), FPDI origin = bawah
            $y_from_top = $y_pct_top * $pageH;
            
            // FPDI/FPDF origin kiri-atas, jadi Y cukup dari atas
            $y = $pageH - $y_from_top - 160;

            // clamp
            $x = max(0, min($x, $pageW - $w));
            $y = max(0, min($y, $pageH - $h));

            $pdf->Image($stampPath, $x, $y, $w, $h);
        }
    }

    // ===== OUTPUT TMP lalu replace APPROVE =====
    $tmpName = "__tmp_approved_" . time() . "_" . bin2hex(random_bytes(3)) . ".pdf";
    $tmpPath = $approveDir . "/" . $tmpName;

    $pdf->Output($tmpPath, 'F');

    if (!file_exists($tmpPath) || filesize($tmpPath) < 500) {
        throw new Exception("Gagal membuat file approve (tmp).");
    }

    if (file_exists($approvedPath)) @unlink($approvedPath);

    if (!rename($tmpPath, $approvedPath)) {
        if (!copy($tmpPath, $approvedPath)) {
            @unlink($tmpPath);
            throw new Exception("Gagal simpan approve. Cek permission folder approve.");
        }
        @unlink($tmpPath);
    }

    echo json_encode([
        'status' => 'success',
        'approved_url' => $approvedPath,
        'v' => @filemtime($approvedPath)
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
    exit;
}
