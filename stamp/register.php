<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Register Reviewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f8f9fa; display: flex; align-items: center; height: 100vh; }
        .card { width: 100%; max-width: 400px; margin: auto; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="card p-4">
        <h3 class="text-center mb-4">Daftar Akun</h3>
        <?php
        if (isset($_POST['register'])) {
            $user = $conn->real_escape_string($_POST['username']);
            $pass = md5($_POST['password']);
            
            $check = $conn->query("SELECT id FROM users WHERE username='$user'");
            if ($check->num_rows > 0) {
                echo "<div class='alert alert-danger'>Username sudah dipakai!</div>";
            } else {
                $conn->query("INSERT INTO users (username, password) VALUES ('$user', '$pass')");
                echo "<div class='alert alert-success'>Berhasil! Silakan <a href='login.php'>Login</a></div>";
            }
        }
        ?>
        <form method="POST">
            <div class="mb-3">
                <label class="form-label">Username</label>
                <input type="text" name="username" class="form-control" required placeholder="Contoh: dzakwan">
            </div>
            <div class="mb-3">
                <label class="form-label">Password</label>
                <input type="password" name="password" class="form-control" required placeholder="******">
            </div>
            <button type="submit" name="register" class="btn btn-primary w-100 mb-3">Register</button>
            <p class="text-center small">Sudah punya akun? <a href="login.php">Login di sini</a></p>
        </form>
    </div>
</body>
</html>