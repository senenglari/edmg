<?php
$host = "localhost";
$user = "dzariesm_sandi";
$pass = "@Pass123!@#";
$db   = "dzariesm_db";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
session_start();
