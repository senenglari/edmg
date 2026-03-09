<?php
header('Content-Type: application/json');
// Mencegah caching agar data selalu terbaru
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

$host = "localhost";
$user = "root";
$pass = "";
$db   = "dzariesm_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die(json_encode(["error" => "Koneksi database gagal: " . $conn->connect_error]));
}

// Ambil file_id dari parameter URL, default doc_001
$file_id = isset($_GET['file_id']) ? $conn->real_escape_string($_GET['file_id']) : 'doc_001';

/**
 * MENGAMBIL DATA
 * Kita mengambil data_json (mentah dari Adobe) 
 * DAN author_name (identitas asli yang kita kunci di kolom database)
 */
$sql = "SELECT annot_id, author_name, data_json FROM pdf_annotations WHERE file_id = '$file_id'";
$result = $conn->query($sql);

$annots = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $annoItem = json_decode($row['data_json'], true);
        
        // Pastikan JSON valid sebelum diproses
        if ($annoItem) {
            /**
             * PROSES SUNTIK BALIK (RECOVERY)
             * Kita paksa isi JSON mengikuti kolom author_name database.
             * Ini mencegah nama 'meniban' di tampilan user.
             */
            $asli = $row['author_name'];
            
            $annoItem['author'] = $asli; // Untuk tampilan list komentar

            // Pastikan creator lengkap (name + id + type) supaya Adobe SDK
            // tidak menampilkan "Guest" saat render ulang.
            $stableId = substr(md5($asli), 0, 12);
            if (isset($annoItem['creator']) && is_array($annoItem['creator'])) {
                $annoItem['creator']['name'] = $asli;
                if (empty($annoItem['creator']['id'])) {
                    $annoItem['creator']['id'] = $stableId;
                }
                if (empty($annoItem['creator']['type'])) {
                    $annoItem['creator']['type'] = 'Person';
                }
            } else {
                $annoItem['creator'] = [
                    "id" => $stableId,
                    "name" => $asli,
                    "type" => "Person"
                ];
            }
            
            $annots[] = $annoItem;
        }
    }
}

// Mengirimkan array objek anotasi ke JavaScript (Adobe SDK)
echo json_encode($annots);

$conn->close();