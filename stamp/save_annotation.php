<?php
header('Content-Type: application/json');
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Pakai koneksi & session dari db.php (biar konsisten)
include 'db.php';

// Fallback nama user login untuk mengganti "Guest"
$loginName = $_SESSION['user_name'] ?? 'User';

// Mengambil data JSON dari POST request
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (isset($data['annotations']) && is_array($data['annotations'])) {
    $file_id = $conn->real_escape_string($data['file_id']);
    $count = 0;

    foreach ($data['annotations'] as $ann) {
        $annot_id = $conn->real_escape_string($ann['id']);
        $raw_author = $ann['author'] ?? 'Guest';
        // Kalau masih Guest/kosong, pakai nama user login
        if (!$raw_author || stripos((string)$raw_author, 'guest') !== false) {
            $raw_author = $loginName;
            $ann['author'] = $raw_author;
            // Pastikan creator lengkap
            $ann['creator'] = [
                'id' => (string)($_SESSION['user_id'] ?? ''),
                'name' => $raw_author,
                'type' => 'Person'
            ];
        }
        $current_author = $conn->real_escape_string((string)$raw_author);
        
        // 1. CEK DULU: Apakah annot_id ini sudah ada pemilik aslinya?
        $checkSql = "SELECT author_name FROM pdf_annotations WHERE annot_id = '$annot_id' LIMIT 1";
        $result = $conn->query($checkSql);
        $existing = $result->fetch_assoc();
    
        if ($existing) {
            // Jika sudah ada, paksa isi JSON menggunakan nama dari kolom database (Pemilik Asli)
            $author_asli = $existing['author_name'];
            $ann['author'] = $author_asli;
            // Pastikan creator lengkap (name + id + type)
            $ann['creator'] = [
                'id' => substr(md5($author_asli), 0, 12),
                'name' => $author_asli,
                'type' => 'Person'
            ];
        } else {
            // Jika belum ada (Insert Baru), gunakan nama user yang kirim
            $author_asli = $current_author;
            $ann['author'] = $author_asli;
            $ann['creator'] = [
                'id' => (string)($_SESSION['user_id'] ?? substr(md5($author_asli), 0, 12)),
                'name' => $author_asli,
                'type' => 'Person'
            ];
        }
    
        $json_fixed = $conn->real_escape_string(json_encode($ann));
    
        /**
         * 2. EKSEKUSI SQL
         * Kita update data_json dengan $json_fixed yang sudah kita perbaiki namanya.
         * Kolom author_name tidak kita update agar tetap sakral.
         */
        $sql = "INSERT INTO pdf_annotations (file_id, annot_id, author_name, data_json) 
                VALUES ('$file_id', '$annot_id', '$author_asli', '$json_fixed')
                ON DUPLICATE KEY UPDATE data_json = '$json_fixed'";
        
        $conn->query($sql);
        $count++;
    }

    // ===== HAPUS FILE STAMP (APPROVE) JIKA ADA =====
    // file_id format: pdf_123
    $deleted_stamp = false;
    if (preg_match('/^pdf_(\d+)$/', $file_id, $m)) {
        $pdfId = (int)$m[1];
        $qr = $conn->query("SELECT file_name FROM list_pdf WHERE id = '$pdfId' LIMIT 1");
        if ($qr && ($row = $qr->fetch_assoc())) {
            $fileName = $row['file_name'];
            $approvePath = "storage/$pdfId/approve/APPROVED_" . $fileName;
            if (file_exists($approvePath)) {
                $deleted_stamp = @unlink($approvePath);
            }
        }
    }

    echo json_encode([
        "status" => "success",
        "message" => "$count coretan berhasil disinkronkan",
        "stamp_deleted" => $deleted_stamp
    ]);
} else {
    echo json_encode(["status" => "error", "message" => "Data kosong"]);
}
$conn->close();