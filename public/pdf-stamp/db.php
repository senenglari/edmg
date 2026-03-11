<?php
$host = "localhost";
$user = "root";
$pass = "edmg2026";
$db   = "db_edms";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}
session_start();
