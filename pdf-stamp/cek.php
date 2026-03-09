<?php
// ====================== MULAI SESSION ======================
session_start();

// ====================== AMBIL SEMUA PARAMETER GET ======================
$pdf_id   = $_GET['pdf_id']   ?? '';
$fileName = $_GET['fileName'] ?? '';
$lpath    = $_GET['lpath']    ?? '';
$idrole   = $_GET['idrole']   ?? '0';
$iduser   = $_GET['iduser']   ?? '0';

// ====================== TENTUKAN ROLE (SESUAI GAMBAR) ======================
if ($idrole == 49) {
    $role = "admin";
} else {
    $role = "user";
}

// ====================== SET SESSION (SESUAI GAMBAR + LENGKAP) ======================
$_SESSION['user_id']     = $iduser;
$_SESSION['user_name']   = "admin";           // seperti di gambar kamu (bisa diubah nanti)
$_SESSION['role']        = $role;
$_SESSION['pdf_id']      = $pdf_id;           // diperbaiki dari "admin" di gambar
$_SESSION['fname']       = $fileName;         // fname = fileName
$_SESSION['lpath']       = $lpath;

// Simpan juga idrole & iduser supaya lebih lengkap di index.php nanti
$_SESSION['idrole']      = $idrole;
$_SESSION['iduser']      = $iduser;

// ====================== REDIRECT KE index.php (Query String Sama) ======================
$redirect_url = "http://dzaries.my.id/edmg-github-clone/edmg/pdf-stamp/index.php?" . http_build_query([
    'pdf_id'   => $pdf_id,
    'fileName' => $fileName,
    'lpath'    => $lpath,
    'idrole'   => $idrole,
    'iduser'   => $iduser
]);

// echo $redirect_url;

 
 // echo '<pre>';
 // print_r($_SESSION);die;
?>
<script>window.location="<?=$redirect_url;?>" </script>

<?php
//header("Location: " . $redirect_url);
exit();   // pastikan tidak ada kode lagi yang dijalankan setelah redirect
?>