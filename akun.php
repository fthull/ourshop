<?php
include 'conn.php';
include 'auth.php';

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$active_page = "akun";

// Ambil data user
$id_user = $_SESSION['id_user'];
$user = $conn->query("SELECT * FROM users WHERE id_user='$id_user'")->fetch_assoc();

if (isset($_POST['update'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $password_md5 = md5($password);
        $update = mysqli_query($conn, "UPDATE users SET username='$username', nama='$nama', password='$password_md5' WHERE id_user='$id_user'");
    } else {
        $update = mysqli_query($conn, "UPDATE users SET username='$username', nama='$nama' WHERE id_user='$id_user'");
    }

    if ($update) {
        $_SESSION['username']  = $username;
        $_SESSION['nama_user'] = $nama;
        $success = "Profil Anda berhasil diperbarui!";
    } else {
        $error = "Terjadi kesalahan saat memperbarui profil.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Akun Saya | F-ZONE COMPANY</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0f172a;
            --card-dark: #1e293b;
            --accent-blue: #3b82f6;
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        body {
            background-color: var(--bg-dark);
            color: var(--text-main);
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        .content-wrapper {
            padding: 40px;
            margin-left: 280px;
            transition: 0.3s;
        }

        /* --- CARD STYLE --- */
        .glass-card {
            background: var(--card-dark);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        /* --- AVATAR & PROFIL --- */
        .avatar-circle {
            width: 100px;
            height: 100px;
            background: linear-gradient(135deg, #6366f1 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            font-weight: 700;
            color: white;
            box-shadow: 0 8px 20px rgba(99, 102, 241, 0.3);
            margin: 0 auto 20px;
        }

        .badge-role {
            background: rgba(59, 130, 246, 0.15);
            color: #60a5fa;
            border: 1px solid rgba(59, 130, 246, 0.3);
            padding: 6px 16px;
            border-radius: 100px;
            font-size: 0.75rem;
            letter-spacing: 1px;
            font-weight: 700;
        }

        /* --- FORM STYLING --- */
        .form-label {
            color: var(--text-muted);
            font-size: 0.85rem;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .form-control {
            background: rgba(15, 23, 42, 0.5) !important;
            border: 1px solid rgba(255, 255, 255, 0.1) !important;
            color: white !important;
            border-radius: 12px;
            padding: 12px 16px;
        }

        .form-control:focus {
            background: rgba(15, 23, 42, 0.8) !important;
            border-color: var(--accent-blue) !important;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1);
        }

        .btn-save {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%);
            border: none;
            border-radius: 12px;
            padding: 12px 25px;
            font-weight: 700;
            transition: 0.3s;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(37, 99, 235, 0.3);
        }

        /* --- ALERT --- */
        .alert {
            border-radius: 15px;
            border: none;
            background: rgba(16, 185, 129, 0.1);
            color: #34d399;
        }
        .alert-danger {
            background: rgba(239, 68, 68, 0.1);
            color: #f87171;
        }

        @media (max-width: 992px) {
            .content-wrapper { margin-left: 0; padding: 20px; }
        }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper w-100">
        
        <div class="mb-4">
            <h2 class="fw-bold mb-1">Akun Saya</h2>
            <p class="text-muted">Kelola informasi pribadi dan keamanan akun Anda.</p>
        </div>

        <div class="row g-4">
            <div class="col-lg-4">
                <div class="glass-card p-5 text-center h-100 d-flex flex-column justify-content-center">
                    <div class="avatar-circle">
                        <?= strtoupper(substr($user['nama'], 0, 1)) ?>
                    </div>
                    <h4 class="fw-bold mb-1"><?= htmlspecialchars($user['nama']) ?></h4>
                    <p class="text-muted mb-4">@<?= htmlspecialchars($user['username']) ?></p>
                    
                    <div>
                        <span class="badge-role">
                            <i class="fas fa-shield-alt me-1"></i> <?= strtoupper($user['role']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <div class="col-lg-8">
                <div class="glass-card p-4 p-md-5">
                    <h5 class="fw-bold mb-4">
                        <i class="fas fa-user-cog me-2 text-primary"></i>Pengaturan Profil
                    </h5>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-4">
                            <i class="fas fa-check-circle me-2"></i><?= $success ?>
                            <button class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-4">
                            <i class="fas fa-exclamation-circle me-2"></i><?= $error ?>
                            <button class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <form method="post">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Username</label>
                                <input type="text" name="username" class="form-control" 
                                       value="<?= htmlspecialchars($user['username']) ?>" required>
                            </div>
                            <div class="col-md-6 mb-4">
                                <label class="form-label">Nama Lengkap</label>
                                <input type="text" name="nama" class="form-control" 
                                       value="<?= htmlspecialchars($user['nama']) ?>" required>
                            </div>
                            <div class="col-12 mb-4">
                                <label class="form-label">Ganti Password</label>
                                <input type="password" name="password" class="form-control" 
                                       placeholder="••••••••">
                                <div class="form-text text-muted mt-2" style="font-size: 0.75rem;">
                                    *Kosongkan jika tidak ingin mengganti password lama Anda.
                                </div>
                            </div>
                        </div>

                        <div class="text-end mt-2">
                            <button type="submit" name="update" class="btn btn-save btn-primary px-5">
                                <i class="fas fa-save me-2"></i>Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>