<?php 
include 'db.php'; // Pastikan file ini berisi session_start() dan koneksi $conn

// --- PROTEKSI LOGIN ---
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$myId   = $_SESSION['user_id'];
$myName = $_SESSION['user_name'];
$myRole = $_SESSION['role'] ?? 'user';

// --- FUNGSI HAPUS FOLDER REKURSIF ---
function hapusFolder($dir) {
    if (!file_exists($dir)) return true;
    if (!is_dir($dir)) return unlink($dir);
    foreach (scandir($dir) as $item) {
        if ($item == '.' || $item == '..') continue;
        if (!hapusFolder($dir . DIRECTORY_SEPARATOR . $item)) return false;
    }
    return rmdir($dir);
}

// --- LOGIKA DELETE (Khusus Admin) ---
if (isset($_GET['delete_id']) && $myRole == 'admin') {
    $delId = intval($_GET['delete_id']);
    $res = $conn->query("SELECT folder_path FROM list_pdf WHERE id = '$delId'");
    if ($res && ($row = $res->fetch_assoc())) {
        // Hapus folder fisik (storage/ID)
        $folderToDelete = dirname($row['folder_path']);
        if (!empty($folderToDelete) && is_dir($folderToDelete)) {
            hapusFolder($folderToDelete);
        }
        // Hapus data di database
        $conn->query("DELETE FROM pdf_access WHERE pdf_id = '$delId'");
        $conn->query("DELETE FROM list_pdf WHERE id = '$delId'");
        header("Location: dashboard.php?pesan=terhapus");
        exit;
    }
}

// --- LOGIKA UPLOAD (Khusus Admin) ---
if (isset($_POST['upload']) && $myRole == 'admin') {
    if (isset($_FILES['pdf_file']) && $_FILES['pdf_file']['error'] === 0) {
        $fileName = $_FILES['pdf_file']['name'];
        $tmpName  = $_FILES['pdf_file']['tmp_name'];

        // Simpan data awal untuk dapat ID
        $conn->query("INSERT INTO list_pdf (file_name, uploaded_by) VALUES ('$fileName', '$myId')");
        $newId = $conn->insert_id;

        // Buat folder storage/ID
        $targetFolder = "storage/" . $newId;
        if (!file_exists($targetFolder)) mkdir($targetFolder, 0777, true);

        $targetPath = $targetFolder . "/" . $fileName;

        if (move_uploaded_file($tmpName, $targetPath)) {

            // simpan path file kerja ke DB
            $conn->query("UPDATE list_pdf SET folder_path = '$targetPath' WHERE id = '$newId'");

            // =============================
            // AUTO CONVERT KE PDF 1.4
            // =============================
            $originalPath  = $targetPath; // storage/ID/nama.pdf
            $convertedPath = $targetFolder . "/WORK_" . $fileName; // storage/ID/WORK_nama.pdf

            // Convert ke PDF 1.4 (prepress supaya text tetap bagus)
            $cmd = "gs -sDEVICE=pdfwrite "
                 . "-dCompatibilityLevel=1.4 "
                 . "-dPDFSETTINGS=/prepress "
                 . "-dNOPAUSE -dBATCH -dQUIET "
                 . "-sOutputFile=" . escapeshellarg($convertedPath) . " "
                 . escapeshellarg($originalPath);

            $output = [];
            $returnVar = 0;
            exec($cmd, $output, $returnVar);

            // kalau sukses & file hasil convert masuk akal -> replace file kerja
            if ($returnVar === 0 && file_exists($convertedPath) && filesize($convertedPath) > 1000) {
                @unlink($originalPath);
                @rename($convertedPath, $originalPath);
            } else {
                // kalau gagal, jangan ganggu file kerja asli (biar sistem tetap jalan)
                @unlink($convertedPath);
                error_log("Ghostscript convert gagal. return=$returnVar file=$originalPath output=" . implode(" | ", $output));
            }

            header("Location: dashboard.php?pesan=sukses");
            exit;
        }
    }
}

// --- LOGIKA SIMPAN AKSES (Khusus Admin) ---
if (isset($_POST['simpan_akses']) && $myRole == 'admin') {
    $pdf_id = intval($_POST['pdf_id']);
    $user_ids = $_POST['reviewer_ids'] ?? [];
    
    $conn->query("DELETE FROM pdf_access WHERE pdf_id = '$pdf_id'");
    foreach ($user_ids as $uid) {
        $uid = intval($uid);
        $conn->query("INSERT INTO pdf_access (pdf_id, user_id) VALUES ('$pdf_id', '$uid')");
    }
    header("Location: dashboard.php?pesan=akses_updated");
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard PDF Review</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    
    <style>
        /* Desain Select2 Abu-abu Standar */
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #dee2e6;
            min-height: 38px;
            border-radius: 0.375rem;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #e9ecef !important;
            border: 1px solid #ced4da !important;
            color: #495057 !important;
            padding: 2px 8px;
            margin-top: 6px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card p-4 shadow-sm mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h4>Halo, <?= htmlspecialchars($myName) ?> (<?= ucfirst($myRole) ?>)</h4>
                <a href="login.php?logout=1" class="btn btn-danger btn-sm">Logout</a>
            </div>
            <hr>
            <?php if ($myRole == 'admin'): ?>
                <form action="" method="POST" enctype="multipart/form-data" class="d-flex gap-2">
                    <input type="file" name="pdf_file" class="form-control" accept=".pdf" required>
                    <button type="submit" name="upload" class="btn btn-success">Upload PDF</button>
                </form>
            <?php endif; ?>
        </div>

        <div class="card shadow-sm">
            <table class="table mb-0 align-middle">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nama Dokumen</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    // Filter: Admin lihat semua, User hanya yang diberi akses
                    $query = ($myRole == 'admin') 
                        ? "SELECT * FROM list_pdf ORDER BY id DESC" 
                        : "SELECT p.* FROM list_pdf p JOIN pdf_access a ON p.id = a.pdf_id WHERE a.user_id = '$myId' ORDER BY p.id DESC";
                    
                    $list = $conn->query($query);
                    while($row = $list->fetch_assoc()):
                        $pdfId = (int)$row['id'];
                        $fileName = $row['file_name'];

                        // === CEK APAKAH SUDAH ADA STAMP (APPROVE) ===
                        $approvePath = "storage/$pdfId/approve/APPROVED_$fileName";
                        $isStamped = file_exists($approvePath);

                        // === RULE: admin boleh edit kapanpun, user biasa kalau stamped -> disable ===
                        $canReview = ($myRole === 'admin') || !$isStamped;
                    ?>
                    <tr>
                        <td><?= $pdfId ?></td>
                        <td>
                            <strong><?= htmlspecialchars($fileName) ?></strong>
                            <?php if ($isStamped): ?>
                                <a href="index.php?pdf_id=<?= $pdfId ?>&stamp=1" class="btn btn-primary btn-sm ms-2">STAMPED</a>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="btn-group">

                                <?php if ($canReview): ?>
                                    <a href="index.php?pdf_id=<?= $pdfId ?>" class="btn btn-primary btn-sm">Review</a>
                                <?php else: ?>
                                    <a href="javascript:void(0)"
                                       class="btn btn-secondary btn-sm"
                                       style="opacity:.65; cursor:not-allowed;"
                                       title="Dokumen sudah di-stamp. User biasa tidak bisa edit.">
                                        Review
                                    </a>
                                <?php endif; ?>

                                <?php if($myRole == 'admin'): ?>
                                    <button
                                        onclick="bukaModalAkses(<?= $pdfId ?>, '<?= htmlspecialchars($fileName, ENT_QUOTES) ?>')"
                                        class="btn btn-outline-secondary btn-sm">👥 Akses</button>

                                    <a href="dashboard.php?delete_id=<?= $pdfId ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus folder & file ini?')">Hapus</a>
                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalAkses" tabindex="-1">
        <div class="modal-dialog">
            <form action="" method="POST" class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Kelola Reviewer</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="pdf_id" id="pdf_id_input">
                    <p id="nama_file_modal" class="fw-bold text-primary"></p>
                    <div class="mb-3">
                        <label class="form-label">Cari & Pilih User:</label>
                        <select name="reviewer_ids[]" id="reviewer_select" class="form-control" multiple="multiple" style="width: 100%">
                            <?php 
                            $u = $conn->query("SELECT id, username FROM users WHERE role = 'user'");
                            while($user = $u->fetch_assoc()) echo "<option value='".$user['id']."'>".$user['username']."</option>";
                            ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" name="simpan_akses" class="btn btn-dark">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#reviewer_select').select2({
            placeholder: ' Masukkan nama user...',
            dropdownParent: $('#modalAkses')
        });
    });

    function bukaModalAkses(id, nama) {
        document.getElementById('pdf_id_input').value = id;
        document.getElementById('nama_file_modal').innerText = "File: " + nama;

        $('#reviewer_select').val(null).trigger('change');

        // Fetch user yang sudah punya akses (AJAX)
        fetch('get_akses.php?pdf_id=' + id)
            .then(res => res.json())
            .then(data => {
                $('#reviewer_select').val(data).trigger('change');
                var myModal = new bootstrap.Modal(document.getElementById('modalAkses'));
                myModal.show();
            });
    }
    </script>
</body>
</html>
