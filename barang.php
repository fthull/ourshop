<?php
include "conn.php";
include "auth.php";
include "alert-helper.php";

$active_page = 'barang';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
include 'sidebar.php';

// DEFINISI PRIMARY KEY TABEL BARANG (Sesuai barang.sql)
$primary_key = 'id_barang'; 

// Proses Edit Barang
// Proses Edit Barang
if (isset($_POST['ubah_barang'])) {
    $id = (int) $_POST['id'];
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $harga = (int) $_POST['harga'];
    
    // Ambil data stok saat ini dari database untuk validasi
    $cek = $conn->query("SELECT stok FROM barang WHERE id_barang = $id")->fetch_assoc();
    $stok_maksimal = (int)$cek['stok'];

    $s_baik  = max(0, (int)$_POST['stok_baik']);
    $s_rusak = max(0, (int)$_POST['stok_rusak']);

    // VALIDASI: Total baik + rusak tidak boleh melebihi stok yang ada
    if (($s_baik + $s_rusak) > $stok_maksimal) {
        setAlert('error', "Total Baik ($s_baik) + Rusak ($s_rusak) melebihi stok yang ada ($stok_maksimal).");
        header("Location: barang.php?edit=$id");
        exit;
    } else {
        // Update rincian kondisi (stok utama tetap sesuai data awal)
        $sql = "UPDATE barang SET 
                nama = '$nama', 
                harga = '$harga', 
                stok_baik = '$s_baik', 
                stok_rusak = '$s_rusak' 
                WHERE id_barang = $id";
        
        if ($conn->query($sql)) {
            setAlert('success', 'Kondisi barang berhasil diperbarui!');
            header("Location: barang.php");
            exit;
        } else {
            setAlert('error', 'Gagal memperbarui kondisi barang');
            header("Location: barang.php?edit=$id");
            exit;
        }
    }
}
// Ambil data untuk form edit
$edit_data = null;
if (isset($_GET['edit'])) {
    $id = (int) $_GET['edit'];
    $res_edit = $conn->query("SELECT * FROM barang WHERE $primary_key=$id");
    if ($res_edit && $res_edit->num_rows > 0) {
        $edit_data = $res_edit->fetch_assoc();
    }
}


// Query Utama
$result = $conn->query("SELECT * FROM barang ORDER BY nama ASC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Data Barang | F-ZONE COMPANY</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link rel="stylesheet" href="dist/css/adminlte.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
  <style>
    body { background: #f4f6f9; }
    /* === LAYOUT === */
.app-wrapper {
    display: flex;
    min-height: 100vh;
    background: #f4f6f9;
}

.content-wrapper {
    flex: 1;
    padding: 25px;
    margin-left: 260px;
}

/* === SIDEBAR === */
.main-sidebar {
    width: 260px;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #111827, #1f2933);
    box-shadow: 2px 0 12px rgba(0,0,0,0.25);
}

/* LOGO */
.main-sidebar h2 {
    font-size: 20px;
    font-weight: 700;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.15);
    padding-bottom: 15px;
}

/* === MENU === */
.nav-sidebar .nav-link {
    color: #cfd8dc;
    padding: 12px 15px;
    margin-bottom: 6px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    gap: 12px;
    transition: all 0.25s ease;
    font-weight: 500;
}

.nav-sidebar .nav-link i {
    width: 22px;
    text-align: center;
    font-size: 15px;
}

/* HOVER */
.nav-sidebar .nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: #ffffff;
}

/* ACTIVE */
.nav-sidebar .nav-link.active {
    background: #2563eb;
    color: #ffffff;
    box-shadow: 0 4px 10px rgba(37,99,235,0.4);
}

/* LOGOUT KHUSUS */
.logout-link {
    margin-top: 20px;
    background: rgba(239,68,68,0.15);
    color: #f87171 !important;
}

.logout-link:hover {
    background: #ef4444 !important;
    color: white !important;
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .content-wrapper {
        margin-left: 0;
    }
    .main-sidebar {
        position: relative;
        width: 100%;
        min-height: auto;
    }
}

    .nav-link.active { background-color: #007bff !important; color: white !important; }
    .card { border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        .logout-link { color: red !important; }
    @media (max-width: 768px) { .content-wrapper { margin-left: 0; } .main-sidebar { position: relative; width: 100%; } }
    /* ===== ALERT ===== */
    .alert {
        padding: 14px 16px;
        border-radius: 12px;
        border: none;
        font-size: 14px;
        animation: slideDown 0.4s ease-out;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .alert-success {
        background-color: #d4edda;
        border-left: 4px solid #28a745;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-left: 4px solid #dc3545;
        color: #721c24;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-left: 4px solid #ffc107;
        color: #856404;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-left: 4px solid #17a2b8;
        color: #0c5460;
    }

    .alert .btn-close {
        opacity: 0.7;
        transition: opacity 0.2s;
    }

    .alert .btn-close:hover {
        opacity: 1;
    }
  </style>
</head>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Fungsi saat baris tabel diklik
    $('.view-detail').click(function() {
        const id = $(this).data('id');
        const nama = $(this).data('nama');
        const stok = $(this).data('stok');
        const harga = $(this).data('harga');
        const baik = $(this).data('baik');
        const rusak = $(this).data('rusak');
        const totalHarga = stok * harga;

        // Isi data ke dalam Modal
        $('#detail-nama').text(nama);
        $('#detail-stok').text(stok);
        $('#detail-harga').text('Rp ' + new Intl.NumberFormat('id-ID').format(harga));
        $('#detail-total').text('Rp ' + new Intl.NumberFormat('id-ID').format(totalHarga));
        $('#detail-baik').text(baik);
        $('#detail-rusak').text(rusak);
        // Tanggal default (karena di SQL tidak ada kolom tanggal, bisa diisi '-' atau tambahkan kolom tgl_masuk)
        $('#detail-tanggal').text('Pembaruan Terakhir'); 

        // Tampilkan Modal
        $('#modalDetail').modal('show');
    });
});
</script>
<body class="hold-transition sidebar-mini">
<div class="app-wrapper">

  <div class="content-wrapper w-100">
    <section class="content-header mb-3">
      <h2><i class="fas fa-boxes"></i> Manajemen Stok Barang</h2>
    </section>

    <section class="content">
        <!-- ALERT MESSAGE -->
        <div class="alert-container">
            <?= displayAlert(); ?>
        </div>
        
        <?php if ($edit_data): ?>
<div class="card card-warning mb-4 shadow">
    <div class="card-header">
        <h3 class="card-title text-dark">
            <?= $edit_data['nama'] ?> 
            (Stok: <span id="maxStok"><?= $edit_data['stok'] ?></span>)
        </h3>
    </div>
    <div class="card-body">
        <form method="POST" onsubmit="return validasiStok()">
            <input type="hidden" name="id" value="<?= $edit_data['id_barang'] ?>">
            <div class="row g-3">
                <div class="col-md-3">
                    <label class="fw-bold">Nama Barang</label>
                    <input type="text" name="nama" class="form-control" value="<?= $edit_data['nama'] ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold text-success">Jumlah Baik</label>
                    <input type="number" name="stok_baik" id="input_baik" class="form-control" 
                           value="<?= $edit_data['stok_baik'] ?>" min="0" max="<?= $edit_data['stok'] ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold text-danger">Jumlah Rusak</label>
                    <input type="number" name="stok_rusak" id="input_rusak" class="form-control" 
                           value="<?= $edit_data['stok_rusak'] ?>" min="0" max="<?= $edit_data['stok'] ?>" required>
                </div>
                <div class="col-md-2">
                    <label class="fw-bold">Harga (Rp)</label>
                    <input type="number" name="harga" class="form-control" value="<?= $edit_data['harga'] ?>" required>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" name="ubah_barang" class="btn btn-primary w-100 shadow-sm">Simpan</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
function validasiStok() {
    let max = parseInt(document.getElementById('maxStok').innerText);
    let baik = parseInt(document.getElementById('input_baik').value) || 0;
    let rusak = parseInt(document.getElementById('input_rusak').value) || 0;

    if ((baik + rusak) > max) {
        alert("Total jumlah Baik dan Rusak tidak boleh lebih dari " + max);
        return false; // Batalkan submit form
    }
    return true;
}
</script>
<?php endif; ?>

        <div class="card">
          <div class="card-body p-0">
            <table class="table table-striped table-hover m-0">
                <thead class="table-dark">
                    <tr>
                        <th>No</th>
                        <th>Nama Barang</th>
                        <th>Stok</th>
                        <th>Harga</th>
                        <th>Total Harga</th>
                        <th>Kondisi</th>
                        <th class="text-center">Aksi</th>
                    </tr>
                </thead>
     <tbody>
<?php
if ($result && $result->num_rows > 0) {
  $no=1;
    while ($d = $result->fetch_assoc()) {
        // Tambahkan class 'view-detail' dan data-attributes
        echo "<tr class='view-detail' style='cursor:pointer' 
                  data-id='{$d[$primary_key]}' 
                  data-nama='{$d['nama']}' 
                  data-stok='{$d['stok']}' 
                  data-harga='{$d['harga']}' 
                  data-baik='{$d['stok_baik']}' 
                  data-rusak='{$d['stok_rusak']}'>
            <td>$no</td>
            <td><span class='text-primary fw-bold'>{$d['nama']}</span></td>
            <td><b>{$d['stok']}</b></td>
            <td>Rp " . number_format($d['harga'], 0, ',', '.') . "</td>
            <td>Rp " . number_format($d['stok'] * $d['harga'], 0, ',', '.') . "</td>
            <td>
                <span class='badge bg-success'>B: {$d['stok_baik']}</span>
                <span class='badge bg-danger'>R: {$d['stok_rusak']}</span>
            </td>
            <td class='text-center'>
                <a href='?edit={$d[$primary_key]}' class='btn btn-warning btn-sm' onclick='event.stopPropagation();'><i class='fas fa-edit'></i></a>
            </td>
        </tr>";
        $no++;
    }
}
?>
</tbody>
            </table>
          </div>
        </div>
    </section>
  </div>
</div>
<div class="modal fade" id="modalDetail" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header bg-dark text-white">
        <h5 class="modal-title"><i class="fas fa-info-circle"></i> Detail Barang</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body p-0">
        <table class="table table-striped m-0">
            <tr>
                <th class="ps-3 w-50">Nama Barang</th>
                <td id="detail-nama"></td>
            </tr>
            
            <tr>
                <th class="ps-3">Stok Keseluruhan</th>
                <td id="detail-stok" class="fw-bold"></td>
            </tr>
            <tr>
                <th class="ps-3">Harga Satuan</th>
                <td id="detail-harga"></td>
            </tr>
            <tr class="table-primary">
                <th class="ps-3">Total Harga</th>
                <td id="detail-total" class="fw-bold text-primary"></td>
            </tr>
            <tr>
                <th class="ps-3 text-success">Kondisi Baik</th>
                <td><span class="badge bg-success" id="detail-baik"></span> Unit</td>
            </tr>
            <tr>
                <th class="ps-3 text-danger">Kondisi Rusak</th>
                <td><span class="badge bg-danger" id="detail-rusak"></span> Unit</td>
            </tr>
        </table>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
      </div>
    </div>
  </div>
</div>
</body>
</html>