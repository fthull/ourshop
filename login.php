<?php
// DEBUG (hapus jika sudah production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include 'conn.php';

// Jika sudah login, langsung ke dashboard
if (isset($_SESSION['login'])) {
    header("Location: dashboard.php");
    exit;
}

$error = "";

if (isset($_POST['login'])) {

    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = md5($_POST['password']); // sesuai data kamu (UKK)

    $query = mysqli_query($conn, "
        SELECT id_user, username, nama, role 
        FROM users 
        WHERE username='$username' 
        AND password='$password'
        LIMIT 1
    ");

    if (!$query) {
        die("Query Error: " . mysqli_error($conn));
    }

    if (mysqli_num_rows($query) === 1) {

        $user = mysqli_fetch_assoc($query);

        // SESSION LOGIN
        $_SESSION['login']     = true;
        $_SESSION['id_user']   = $user['id_user'];
        $_SESSION['username']  = $user['username'];
        $_SESSION['nama_user'] = $user['nama'];
        $_SESSION['role']      = $user['role'];

        header("Location: dashboard.php");
        exit;

    } else {
        $error = "Username atau Password salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login | F-ZONE COMPANY</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: linear-gradient(135deg, #111827, #1f2933);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            border-radius: 15px;
            box-shadow: 0 15px 30px rgba(0,0,0,0.4);
        }
        .login-header {
            background: #2563eb;
            color: white;
            border-radius: 15px 15px 0 0;
        }
    </style>
</head>
<body>

<div class="card login-card">
    <div class="card-header login-header text-center py-4">
        <h4 class="fw-bold mb-0">
            <i class="fas fa-boxes"></i> F-ZONE COMPANY
        </h4>
        <small>Sistem Manajemen Stok</small>
    </div>

    <div class="card-body p-4">
        <?php if ($error): ?>
            <div class="alert alert-danger text-center">
                <?= $error ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <div class="mb-3">
                <label class="form-label fw-semibold">Username</label>
                <input type="text" name="username" class="form-control" required autofocus>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit" name="login" class="btn btn-primary w-100 py-2">
                <i class="fas fa-sign-in-alt me-1"></i> Login
            </button>
        </form>
    </div>

    <div class="card-footer text-center text-muted">
        <small>&copy; <?= date('Y') ?> F-ZONE COMPANY</small>
    </div>
</div>

</body>
</html>
