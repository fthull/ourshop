<?php
include "conn.php";
include "auth.php";
include "alert-helper.php";

$active_page = 'barang';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

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
            --table-header: #0f172a;
        }

        body { 
            font-family: 'Inter', sans-serif;
            background-color: var(--dash-bg);
            color: var(--text-main);
        }

        .app-wrapper { display: flex; min-height: 100vh; }
        .content-wrapper { flex: 1; padding: 30px; margin-left: 280px; transition: 0.3s; }

        /* === CARD & GLASSMORPHISM === */
        .card { 
            background: var(--card-bg);
            backdrop-filter: blur(10px);
            border: 1px solid var(--card-border);
            border-radius: 15px;
            margin-bottom: 30px;
        }
        .card-header { 
            background: rgba(255, 255, 255, 0.02);
            border-bottom: 1px solid var(--card-border);
            padding: 20px;
        }

        /* === DARK TABLE STYLE (CONSISTENT) === */
        .table-dark-custom { color: #cbd5e1; margin-bottom: 0; }
        .table-dark-custom thead th {
            background-color: var(--table-header);
            color: #6366f1; /* Neon Purple Accent */
            text-transform: uppercase;
            font-size: 0.75rem;
            letter-spacing: 1px;
            padding: 18px 15px;
            border: none;
            border-bottom: 2px solid rgba(99, 102, 241, 0.2);
        }
        .table-dark-custom tbody td {
            background-color: transparent;
            border-bottom: 1px solid var(--card-border);
            padding: 16px 15px;
            vertical-align: middle;
            color: #e2e8f0;
        }
        .table-dark-custom tbody tr:hover { 
            background-color: rgba(99, 102, 241, 0.05); 
            transition: 0.3s; 
        }

        /* --- Form Inputs (Dark Style) --- */
        .form-control {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid var(--card-border);
            color: #fff;
            border-radius: 10px;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.08);
            border-color: var(--accent);
            box-shadow: none;
            color: #fff;
        }

        /* --- Badges --- */
        .badge-baik { background: rgba(16, 185, 129, 0.1); color: #10b981; border: 1px solid rgba(16, 185, 129, 0.2); }
        .badge-rusak { background: rgba(244, 63, 94, 0.1); color: #f43f5e; border: 1px solid rgba(244, 63, 94, 0.2); }
        .badge-stok { background: #334155; color: #f8fafc; }

        /* --- Modal Style --- */
        .modal-content { background: #1e293b; border: 1px solid var(--card-border); color: white; }
        .btn-close-white { filter: invert(1) grayscale(100%) brightness(200%); }

        @media (max-width: 992px) { .content-wrapper { margin-left: 0; } }
    </style>
</head>

<body>
<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <section class="mb-4 d-flex justify-content-between align-items-center">
            <h2 class="fw-bold"><i class="fas fa-boxes me-2 text-primary"></i>Data Inventaris</h2>
            <div class="text-muted small">Total: <span class="text-white fw-bold"><?= $result->num_rows ?></span> Item</div>
        </section>

        <?= displayAlert(); ?>

        <?php if ($edit_data): ?>
        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold text-warning"><i class="fas fa-edit me-2"></i>Edit Barang: <?= htmlspecialchars($edit_data['nama']) ?></h5>
                <span class="badge bg-primary">Stok Sistem: <?= $edit_data['stok'] ?></span>
            </div>
            <div class="card-body">
                <form method="POST" id="formEdit">
                    <input type="hidden" name="id" value="<?= $edit_data['id_barang'] ?>">
                    <input type="hidden" id="maxStokValue" value="<?= $edit_data['stok'] ?>">
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="small text-muted">Nama Barang</label>
                            <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?>" required>
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Kondisi Baik</label>
                            <input type="number" name="stok_baik" id="input_baik" class="form-control" value="<?= $edit_data['stok_baik'] ?>" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Kondisi Rusak</label>
                            <input type="number" name="stok_rusak" id="input_rusak" class="form-control" value="<?= $edit_data['stok_rusak'] ?>" min="0">
                        </div>
                        <div class="col-md-2">
                            <label class="small text-muted">Harga Satuan</label>
                            <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?>" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end">
                            <button type="submit" name="ubah_barang" class="btn btn-primary w-100 shadow"><i class="fas fa-save me-2"></i>Simpan</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <div class="card shadow">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark-custom align-middle">
                        <thead>
                            <tr>
                                <th width="50">No</th>
                                <th>Nama Barang</th>
                                <th>Stok Total</th>
                                <th>Harga Satuan</th>
                                <th>Valuasi</th>
                                <th>Status Kondisi</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result->num_rows > 0): $no=1; while ($d = $result->fetch_assoc()): ?>
                            <tr class="view-detail" style="cursor:pointer" 
                                data-id="<?= $d[$primary_key] ?>" data-nama="<?= $d['nama'] ?>" 
                                data-stok="<?= $d['stok'] ?>" data-harga="<?= $d['harga'] ?>" 
                                data-baik="<?= $d['stok_baik'] ?>" data-rusak="<?= $d['stok_rusak'] ?>">
                                <td><span class="text-muted small"><?= $no++ ?></span></td>
                                <td><span class="text-white fw-bold"><?= $d['nama'] ?></span></td>
                                <td><span class="badge badge-stok px-2 py-1"><?= $d['stok'] ?></span></td>
                                <td>Rp <?= number_format($d['harga'], 0, ',', '.') ?></td>
                                <td class="text-primary fw-bold">Rp <?= number_format($d['stok'] * $d['harga'], 0, ',', '.') ?></td>
                                <td>
                                    <span class="badge badge-baik">B: <?= $d['stok_baik'] ?></span>
                                    <span class="badge badge-rusak">R: <?= $d['stok_rusak'] ?></span>
                                </td>
                                <td class="text-center">
                                    <a href="?edit=<?= $d[$primary_key] ?>" class="btn btn-outline-warning btn-sm" onclick="event.stopPropagation();">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; else: ?>
                            <tr><td colspan="7" class="text-center py-5 text-muted small">Belum ada data barang dalam database.</td></tr>
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
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-bold"><i class="fas fa-info-circle me-2 text-primary"></i>Rincian Inventaris</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="text-center mb-4">
                    <h2 id="detail-nama" class="fw-bold mb-0 text-white"></h2>
                    <p class="text-muted small">ID Barang: #<span id="detail-id"></span></p>
                </div>
                <div class="row g-3">
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-white bg-opacity-5 text-center">
                            <small class="text-muted d-block mb-1">Stok Total</small>
                            <span id="detail-stok" class="fs-4 fw-bold text-white"></span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="p-3 rounded-3 bg-white bg-opacity-5 text-center">
                            <small class="text-muted d-block mb-1">Harga Satuan</small>
                            <span id="detail-harga" class="fs-5 fw-bold text-primary"></span>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-3 bg-primary bg-opacity-10 text-center border border-primary border-opacity-25">
                            <small class="text-primary d-block fw-bold mb-1">Total Nilai Aset</small>
                            <span id="detail-total" class="fs-4 fw-bold text-white"></span>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="p-2 border border-success border-opacity-25 rounded-3">
                            <small class="text-success fw-bold d-block">Kondisi Baik</small>
                            <h4 id="detail-baik" class="text-white mb-0"></h4>
                        </div>
                    </div>
                    <div class="col-6 text-center">
                        <div class="p-2 border border-danger border-opacity-25 rounded-3">
                            <small class="text-danger fw-bold d-block">Kondisi Rusak</small>
                            <h4 id="detail-rusak" class="text-white mb-0"></h4>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary w-100" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $('.view-detail').click(function() {
        const d = $(this).data();
        $('#detail-id').text(d.id);
        $('#detail-nama').text(d.nama);
        $('#detail-stok').text(d.stok + ' Unit');
        $('#detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(d.harga));
        $('#detail-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(d.stok * d.harga));
        $('#detail-baik').text(d.baik);
        $('#detail-rusak').text(d.rusak);
        $('#modalDetail').modal('show');
    });

    $('#formEdit').submit(function(e) {
        let max = parseInt($('#maxStokValue').val());
        let baik = parseInt($('#input_baik').val()) || 0;
        let rusak = parseInt($('#input_rusak').val()) || 0;

        if ((baik + rusak) > max) {
            alert("❌ Gagal: Jumlah Baik + Rusak tidak boleh melebihi stok total (" + max + ")!");
            return false;
        }
        return true;
    });
});
</script>
</body>
</html>