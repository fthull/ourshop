<?php
include "conn.php";
include "auth.php";
include "alert-helper.php";

$active_page = 'barang';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// DEFINISI PRIMARY KEY
$primary_key = 'id_barang'; 

// Proses Edit Barang
if (isset($_POST['ubah_barang'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = (int) $_POST['harga'];
    
    $cek = $conn->query("SELECT stok FROM barang WHERE id_barang = $id")->fetch_assoc();
    $stok_maksimal = (int)$cek['stok'];

    $s_baik  = max(0, (int)$_POST['stok_baik']);
    $s_rusak = max(0, (int)$_POST['stok_rusak']);

    if (($s_baik + $s_rusak) > $stok_maksimal) {
        setAlert('error', "Total Baik ($s_baik) + Rusak ($s_rusak) melebihi stok tersedia ($stok_maksimal).");
        header("Location: barang.php?edit=$id");
        exit;
    } else {
        $sql = "UPDATE barang SET nama = '$nama', harga = '$harga', stok_baik = '$s_baik', stok_rusak = '$s_rusak' WHERE id_barang = $id";
        if ($conn->query($sql)) {
            setAlert('success', 'Data barang berhasil diperbarui!');
            header("Location: barang.php");
            exit;
        }
    }
}

$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $res_edit = $conn->query("SELECT * FROM barang WHERE $primary_key=$id");
    if ($res_edit && $res_edit->num_rows > 0) {
        $edit_data = $res_edit->fetch_assoc();
    }
}

$result = $conn->query("SELECT * FROM barang ORDER BY nama ASC");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Barang | F-ZONE COMPANY</title>
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
            --accent: #3b82f6;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--dash-bg);
            color: var(--text-main);
            overflow-x: hidden;
        }

        .app-wrapper { display: flex; min-height: 100vh; }

        .content-wrapper { 
            flex: 1; 
            padding: 30px; 
            margin-left: 280px; 
            transition: 0.3s;
        }

    /* Fokus pada Table Dark Mode */
    .card {
        background: rgba(30, 41, 59, 0.7); /* Background kartu sedikit lebih terang dari body */
        border: 1px solid rgba(255, 255, 255, 0.05);
    }

    .table {
        color: #142439; /* Warna teks abu-abu terang */
        border-color: rgba(255, 255, 255, 0.05);
    }

    /* Header Tabel Lebih Solid & Gelap */
    .table thead th {
        background-color: #0f172a !important; /* Warna paling gelap untuk header */
        color: #6366f1 !important; /* Warna ungu/biru neon untuk judul kolom */
        font-weight: 700;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        border-bottom: 2px solid rgba(99, 102, 241, 0.2) !important;
        padding: 18px 15px;
    }

    /* Efek Zebra & Hover */
    .table-striped tbody tr:nth-of-type(odd) {
        background-color: rgba(255, 255, 255, 0.01);
    }

    .table-hover tbody tr:hover {
        background-color: rgba(99, 102, 241, 0.05) !important; /* Highlight tipis warna biru */
        transition: 0.2s;
    }

    .table tbody td {
        padding: 16px 15px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.03);
        vertical-align: middle;
    }

    /* Styling Teks & Badge di dalam tabel */
    .text-primary-neon {
        color: #818cf8 !important;
        font-weight: 600;
    }

    .badge-dark-outline {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid rgba(255, 255, 255, 0.1);
        color: #cbd5e1;
    }
        /* --- Form Inputs (Dark Style) --- */
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: #fff;
            border-radius: 10px;
            padding: 10px 15px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
            color: #fff;
        }
        label { color: var(--text-muted); font-size: 0.85rem; margin-bottom: 5px; }

        /* --- Badges --- */
        .badge-baik { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-rusak { background: rgba(244, 63, 94, 0.1); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.2); }

        /* --- Buttons --- */
        .btn-primary { background: var(--accent); border: none; border-radius: 10px; padding: 10px 20px; font-weight: 600; }
        .btn-warning { background: #f59e0b; border: none; color: #fff; border-radius: 8px; }

        @media (max-width: 992px) { .content-wrapper { margin-left: 0; } }
    </style>
</head>

<body>
<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="mb-4 d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="fas fa-boxes me-2 text-primary"></i>Manajemen Barang</h2>
            <div class="text-muted small">Total: <?= $result->num_rows ?> Jenis Barang</div>
        </section>

        <?= displayAlert(); ?>

        <?php if ($edit_data): ?>
        <div class="card mb-4 animate__animated animate__fadeInDown">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3>Edit Barang: <?= htmlspecialchars($edit_data['nama']) ?></h3>
                <span class="badge bg-primary px-3 py-2">Maks Stok: <?= $edit_data['stok'] ?></span>
            </div>
            <div class="card-body">
                <form method="POST" id="formEdit">
                    <input type="hidden" name="id" value="<?= $edit_data['id_barang'] ?>">
                    <input type="hidden" id="maxStokValue" value="<?= $edit_data['stok'] ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label>Nama Barang</label>
                            <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label>Stok Baik</label>
                            <input type="number" name="stok_baik" id="input_baik" class="form-control" value="<?= $edit_data['stok_baik'] ?>" min="0">
                        </div>
                        <div class="col-md-2">
                            <label>Stok Rusak</label>
                            <input type="number" name="stok_rusak" id="input_rusak" class="form-control" value="<?= $edit_data['stok_rusak'] ?>" min="0">
                        </div>
                        <div class="col-md-2">
                            <label>Harga Satuan</label>
                            <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?>" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="ubah_barang" class="btn btn-primary w-100"><i class="fas fa-save me-2"></i>Update</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Barang</th>
                                <th>Stok Total</th>
                                <th>Harga Satuan</th>
                                <th>Valuasi Stok</th>
                                <th>Kondisi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): $no=1; while ($d = $result->fetch_assoc()): ?>
                            <tr class="view-detail" style="cursor:pointer" 
                                data-id="<?= $d[$primary_key] ?>" data-nama="<?= $d['nama'] ?>" 
                                data-stok="<?= $d['stok'] ?>" data-harga="<?= $d['harga'] ?>" 
                                data-baik="<?= $d['stok_baik'] ?>" data-rusak="<?= $d['stok_rusak'] ?>">
                                <td><?= $no++ ?></td>
                                <td><span class="text-white fw-semibold"><?= $d['nama'] ?></span></td>
                                <td><span class="badge bg-light text-dark px-2"><?= $d['stok'] ?></span></td>
                                <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                                <td class="text-primary fw-bold">Rp <?= number_format($d['stok'] * $d['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge badge-baik">B: <?= $d['stok_baik'] ?></span>
                                    <span class="badge badge-rusak">R: <?= $d['stok_rusak'] ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="?edit=<?= $d[$primary_key] ?>" class="btn btn-warning btn-sm" onclick="event.stopPropagation();">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted">Belum ada data barang.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalDetail" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card border-0">
            <div class="modal-header border-bottom border-secondary">
                <h5 class="modal-title fw-bold text-white"><i class="fas fa-info-circle me-2"></i>Rincian Barang</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h2 id="detail-nama" class="fw-bold mb-0"></h2>
                    <p class="text-muted">Informasi Detail Inventaris</p>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-white bg-opacity-5 text-center">
                            <small class="text-muted d-block">Stok Total</small>
                            <span id="detail-stok" class="fs-4 fw-bold text-white"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-4 bg-white bg-opacity-5 text-center">
                            <small class="text-muted d-block">Harga Unit</small>
                            <span id="detail-harga" class="fs-5 fw-bold text-primary"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-4 bg-primary bg-opacity-10 text-center border border-primary border-opacity-25">
                            <small class="text-primary d-block fw-bold">Estimasi Nilai Stok</small>
                            <span id="detail-total" class="fs-4 fw-bold text-white"></span>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <label class="text-success fw-bold">Kondisi Baik</label>
                        <h4 id="detail-baik" class="text-white"></h4>
                    </div>
                    <div class="col-6 text-center">
                        <label class="text-danger fw-bold">Kondisi Rusak</label>
                        <h4 id="detail-rusak" class="text-white"></h4>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    // Modal Detail Trigger
    $('.view-detail').click(function() {
        const d = $(this).data();
        $('#detail-nama').text(d.nama);
        $('#detail-stok').text(d.stok + ' Unit');
        $('#detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(d.harga));
        $('#detail-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(d.stok * d.harga));
        $('#detail-baik').text(d.baik);
        $('#detail-rusak').text(d.rusak);
        $('#modalDetail').modal('show');
    });

    // Validasi Form Edit
    $('#formEdit').submit(function(e) {
        let max = parseInt($('#maxStokValue').val());
        let baik = parseInt($('#input_baik').val()) || 0;
        let rusak = parseInt($('#input_rusak').val()) || 0;

        if ((baik + rusak) > max) {
            alert("❌ Kesalahan: Total Baik ("+baik+") + Rusak ("+rusak+") melebihi total stok yang ada ("+max+")!");
            return false;
        }
        return true;
    });
});
</script>
</body>
</html>