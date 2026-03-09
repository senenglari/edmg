<?php session_start();
$host = "localhost";
$user = "dzariesm_edms";
$pass = "@Edms123!@#";
$db   = "dzariesm_edms";
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

