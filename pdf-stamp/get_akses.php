<?php
include 'db.php';
// Matikan error reporting agar tidak merusak format JSON
error_reporting(0); 

$pdf_id = isset($_GET['pdf_id']) ? intval($_GET['pdf_id']) : 0;
$res = $conn->query("SELECT user_id FROM pdf_access WHERE pdf_id = '$pdf_id'");

$data = [];
if($res) {
    while($row = $res->fetch_assoc()){
        $data[] = $row['user_id'];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
exit;