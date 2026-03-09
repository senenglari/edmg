<?php session_start();
include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login Reviewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; height: 100vh; }
        .card { width: 100%; max-width: 400px; margin: auto; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="card p-4">
        <h3 class="text-center mb-4 text-primary">Login Reviewer</h3>
        <?php
        if (isset($_POST['login'])) {
            $user = $conn->real_escape_string($_POST['username']);
            $pass = md5($_POST['password']);
            //echo "SELECT * FROM users WHERE username='$user' AND password='$pass'";die;
            $res = $conn->query("SELECT * FROM users WHERE username='$user' AND password='$pass'");
			//echo $res->num_rows;die;
            if ($res->num_rows > 0) {
                $data = $res->fetch_assoc();
                $_SESSION['user_id']   = $data['id'];
                $_SESSION['user_name'] = $data['username'];
                $_SESSION['role']      = $data['role'];
				//print_r($_SESSION);die;
                
                header("Location: dashboard.php");
                exit;
            } else {
                echo "<div class='alert alert-danger text-center'>Login Gagal! Akun tidak ditemukan.</div>";
            }
        }
        ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label font-weight-bold">Username</label>
                <input type="text" name="username" class="form-control" required>
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>
            <button type="submit" name="login" class="btn btn-primary w-100 mb-3">Masuk Sekarang</button>
            <p class="text-center small">Belum punya akun? <a href="register.php">Daftar Akun Baru</a></p>
        </form>
    </div>
</body>
</html>