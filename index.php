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
    $password = md5($_POST['password']); 

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
    <title>Login | OurShop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
 
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --primary-color: #3b82f6;
            --secondary-color: #1e293b;
            --accent-color: #60a5fa;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top left, #1e293b, #0f172a);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animasi Masuk Halaman */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .login-card {
            width: 100%;
            max-width: 420px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: fadeInUp 0.8s ease-out;
            color: white;
        }

        .login-header {
            padding: 2.5rem 1rem 1.5rem;
            text-align: center;
        }

        .login-header i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: var(--primary-color);
            filter: drop-shadow(0 0 10px rgba(59, 130, 246, 0.5));
        }

        .form-control {
            background: rgba(255, 255, 255, 0.08);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: white;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: rgba(255, 255, 255, 0.12);
            border-color: var(--primary-color);
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.25);
            color: white;
        }

        .form-label {
            color: #cbd5e1;
        }

        .btn-login {
            background: var(--primary-color);
            border: none;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 0.5px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            margin-top: 10px;
        }

        .btn-login:hover {
            background: #2563eb;
            transform: translateY(-2px);
            box-shadow: 0 10px 20px -5px rgba(37, 99, 235, 0.4);
        }

        .btn-login:active {
            transform: translateY(0);
        }

        .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.2);
            color: #f87171;
            font-size: 0.9rem;
        }

        .footer-text {
            color: #94a3b8;
            font-size: 0.85rem;
            margin-top: 2rem;
        }

        /* Floating Animation untuk Icon */
        .floating-icon {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
            100% { transform: translateY(0px); }
        }
    </style>
</head>
<body>

<div class="card login-card p-2">
    <div class="login-header">
        <div class="floating-icon">
            <i class="fas fa-cubes"></i>
        </div>
        <h3 class="fw-bold mb-0">My Store</h3>
        <p class="text-muted small text-uppercase tracking-widest mt-1">Management System</p>
    </div>

    <div class="card-body px-4 pb-4">
        <?php if ($error): ?>
            <div class="alert alert-danger d-flex align-items-center mb-4">
                <i class="fas fa-exclamation-circle me-2"></i>
                <div><?= $error ?></div>
            </div>
        <?php endif; ?>

        <form method="post" autocomplete="off">
            <div class="form-floating mb-3">
                <input type="text" name="username" class="form-control" id="userInput" placeholder="Username" required autofocus>
                <label for="userInput" class="text-secondary">Username</label>
            </div>

            <div class="form-floating mb-4">
                <input type="password" name="password" class="form-control" id="passInput" placeholder="Password" required>
                <label for="passInput" class="text-secondary">Password</label>
            </div>

            <button type="submit" name="login" class="btn btn-primary btn-login w-100 shadow">
                <i class="fas fa-sign-in-alt me-2"></i> MASUK KE SISTEM
            </button>
        </form>

        <div class="text-center footer-text">
            &copy; <?= date('Y') ?> <span class="text-white fw-semibold">My Store</span> <br>
            <small>Project UKK - 2026</small>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>