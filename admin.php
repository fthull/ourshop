<?php
include "conn.php";
include "auth.php";
include "alert-helper.php";

$active_page = 'admin';

// --- LOGIC: TAMBAH ---
if (isset($_POST['tambah'])) {
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $gmail    = mysqli_real_escape_string($conn, $_POST['gmail']);
    $password = md5($_POST['password']);
    $role     = $_POST['role'];
    
    if (!in_array($role, ['admin', 'petugas'])) { $role = 'petugas'; }

    if (mysqli_query($conn, "INSERT INTO users (username, nama, gmail, password, role) VALUES ('$username', '$nama', '$gmail', '$password', '$role')")) {
        setAlert('success', 'Petugas berhasil ditambahkan!');
    } else {
        setAlert('error', 'Gagal menambahkan petugas: ' . mysqli_error($conn));
    }
    header("Location: admin.php");
    exit;
}

// --- LOGIC: UPDATE ---
if (isset($_POST['update'])) {
    $id_user  = $_POST['id_user'];
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $nama     = mysqli_real_escape_string($conn, $_POST['nama']);
    $gmail    = mysqli_real_escape_string($conn, $_POST['gmail']);
    $role     = $_POST['role'];
    $password = $_POST['password'];

    if (!empty($password)) {
        $password = md5($password);
        $query = "UPDATE users SET username='$username', nama='$nama', gmail='$gmail', role='$role', password='$password' WHERE id_user='$id_user'";
    } else {
        $query = "UPDATE users SET username='$username', nama='$nama', gmail='$gmail', role='$role' WHERE id_user='$id_user'";
    }

    if (mysqli_query($conn, $query)) {
        setAlert('success', 'Data petugas berhasil diperbarui!');
    } else {
        setAlert('error', 'Gagal memperbarui data: ' . mysqli_error($conn));
    }
    header("Location: admin.php");
    exit;
}

// --- LOGIC: DELETE ---
if (isset($_POST['hapus'])) {
    $id_user = $_POST['id_user'];
    // First delete related transactions
    mysqli_query($conn, "DELETE FROM transaksi WHERE id_user='$id_user'");
    // Then delete the user
    if (mysqli_query($conn, "DELETE FROM users WHERE id_user='$id_user'")) {
        setAlert('success', 'Petugas berhasil dihapus!');
    } else {
        setAlert('error', 'Gagal menghapus petugas: ' . mysqli_error($conn));
    }
    header("Location: admin.php");
    exit;
}

// Ambil data untuk tabel
$petugas = mysqli_query($conn, "SELECT * FROM users");
// Ambil data cadangan untuk modal (agar tidak bentrok dengan pointer tabel)
$petugas_modal = mysqli_query($conn, "SELECT * FROM users");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Petugas | F-ZONE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
        :root {
            --sidebar-bg: #111827;
            --dash-bg: #1e293b;
            --card-bg: rgba(255, 255, 255, 0.03);
            --card-border: rgba(255, 255, 255, 0.08);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --table-header: #0f172a;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--dash-bg);
            color: var(--text-main);
        }

        .content-wrapper { padding: 30px; margin-left: 280px; transition: 0.3s; }

        /* --- CARD GLASS --- */
        .card { 
            border: 1px solid var(--card-border); 
            border-radius: 15px; 
            background: var(--card-bg);
            backdrop-filter: blur(10px);
        }

        /* --- TABLE STYLE --- */
        .table-dark-custom thead th {
            background-color: var(--table-header) !important;
            color: #6366f1 !important;
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 15px;
            border-bottom: 2px solid rgba(99, 102, 241, 0.2) !important;
        }
        .table-dark-custom tbody td {
            background-color: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 14px 15px;
            color: #e2e8f0;
        }

        /* --- ROLE BADGE --- */
        .badge-role {
            padding: 5px 12px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
            text-transform: uppercase;
        }
        .bg-admin { background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); }
        .bg-petugas { background: rgba(16, 185, 129, 0.15); color: #34d399; border: 1px solid rgba(16, 185, 129, 0.3); }

        /* --- MODAL CONSISTENCY --- */
        .modal-content {
            background: rgba(30, 41, 59, 1) !important; /* Solid agar tidak tembus pandang berlebihan */
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 24px;
        }
        .modal-header {
            background: linear-gradient(90deg, rgba(99, 102, 241, 0.1), rgba(168, 85, 247, 0.1));
            border-bottom: 1px solid rgba(255, 255, 255, 0.05) !important;
        }
        .modal-body .form-control, .modal-body .form-select {
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #f8fafc;
            border-radius: 12px;
        }
        .btn-save {
            background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
            border: none; color: white; font-weight: 600;
        }

        @media (max-width: 992px) { .content-wrapper { margin-left: 0; } }
    </style>
</head>
<body>

<div class="d-flex">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper w-100">
        <div class="alert-container"><?= displayAlert(); ?></div>

        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Manajemen Petugas</h2>
                <p class="text-muted small mb-0">Kelola hak akses dan akun pengguna sistem.</p>
            </div>
            <button class="btn btn-primary rounded-pill px-4 shadow" data-bs-toggle="modal" data-bs-target="#modalTambah">
                <i class="fas fa-user-plus me-2"></i>Tambah User
            </button>
        </div>

        <div class="card shadow">
            <div class="table-responsive">
                <table class="table table-dark-custom align-middle">
                    <thead>
                        <tr class="text-center">
                            <th width="60">No</th>
                            <th class="text-start">User Info</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th width="120">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $no=1; while($p = mysqli_fetch_assoc($petugas)): ?>
                        <tr>
                            <td class="text-center text-muted"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-white"><?= htmlspecialchars($p['nama']) ?></div>
                                <div class="small text-muted">@<?= htmlspecialchars($p['username']) ?></div>
                            </td>
                            <td class="text-center"><?= htmlspecialchars($p['gmail']) ?></td>
                            <td class="text-center">
                                <span class="badge-role <?= $p['role'] == 'admin' ? 'bg-admin' : 'bg-petugas' ?>">
                                    <?= $p['role'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalEdit<?= $p['id_user'] ?>">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalTambah" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <div class="modal-header border-0 p-4">
                <h5 class="fw-bold text-primary mb-0"><i class="fas fa-plus-circle me-2"></i>Tambah Pengguna Baru</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label text-muted small">Username</label>
                    <input type="text" name="username" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Email</label>
                    <input type="email" name="gmail" class="form-control" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="petugas">Petugas</option>
                            <option value="admin">Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="tambah" class="btn btn-primary rounded-pill px-4">Simpan User</button>
            </div>
        </form>
    </div>
</div>

<?php while($pm = mysqli_fetch_assoc($petugas_modal)): ?>
<div class="modal fade" id="modalEdit<?= $pm['id_user'] ?>" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="post" class="modal-content">
            <div class="modal-header border-0 p-4">
                <h5 class="fw-bold text-warning mb-0"><i class="fas fa-user-edit me-2"></i>Edit Pengguna</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" name="id_user" value="<?= $pm['id_user'] ?>">
                <div class="mb-3">
                    <label class="form-label text-muted small">Username</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($pm['username']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($pm['nama']) ?>" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Email</label>
                    <input type="email" name="gmail" class="form-control" value="<?= htmlspecialchars($pm['gmail']) ?>" required>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Role</label>
                        <select name="role" class="form-select" required>
                            <option value="petugas" <?= $pm['role'] == 'petugas' ? 'selected' : '' ?>>Petugas</option>
                            <option value="admin" <?= $pm['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label text-muted small">Ganti Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••">
                        <small class="text-muted" style="font-size: 10px;">Kosongkan jika tidak diubah</small>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button type="submit" name="hapus" class="btn btn-link text-danger text-decoration-none me-auto small" onclick="return confirm('Hapus user ini?')">
                    <i class="fas fa-trash-alt me-1"></i>Hapus
                </button>
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" name="update" class="btn btn-save rounded-pill px-4 text-white">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>
<?php endwhile; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>