<?php
include 'conn.php';
include 'auth.php';
include 'alert-helper.php';

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

$active_page = "transaksi_masuk";

if (isset($_POST['tambah_barang_masuk'])) {
    $id_user = $_SESSION['id_user']; 
    $tanggal = date('Y-m-d');
    $status_transaksi = 'masuk';

    $namas    = $_POST['nama'];
    $stoks    = $_POST['stok'];
    $hargas   = $_POST['harga'];

    $count = 0;
    foreach ($namas as $i => $val) {
        $nama   = mysqli_real_escape_string($conn, $namas[$i]);
        $stok   = (int)$stoks[$i];
        $harga  = (int)$hargas[$i];

        if ($stok <= 0 || $harga <= 0) continue; 

        $cek_barang = $conn->query("SELECT id_barang, stok, stok_baik FROM barang WHERE nama = '$nama' AND harga = '$harga' LIMIT 1");

        if ($cek_barang->num_rows > 0) {
            $data_lama = $cek_barang->fetch_assoc();
            $id_barang_final = $data_lama['id_barang'];
            $stok_baru = $data_lama['stok'] + $stok;
            $stok_baik_baru = $data_lama['stok_baik'] + $stok;

            $sql_update = "UPDATE barang SET stok = '$stok_baru', stok_baik = '$stok_baik_baru' WHERE id_barang = '$id_barang_final'";
            $conn->query($sql_update);
        } else {
            $sql_barang = "INSERT INTO barang (nama, stok, harga, stok_baik, stok_rusak) 
                           VALUES ('$nama', '$stok', '$harga', '$stok', 0)";
            if ($conn->query($sql_barang)) {
                $id_barang_final = $conn->insert_id; 
            }
        }

        if (isset($id_barang_final)) {
            $sql_transaksi = "INSERT INTO transaksi (id_user, id_barang, stok, tanggal, status) 
                              VALUES ('$id_user', '$id_barang_final', '$stok', '$tanggal', '$status_transaksi')"; 
            if ($conn->query($sql_transaksi)) {
                $count++;
            }
        }
    }

    if ($count > 0) {
        setAlert('success', "Berhasil memproses $count data barang masuk!");
        header("Location: transaksi_masuk.php");
        exit;
    }
}

$transaksi_terakhir = $conn->query("
    SELECT t.tanggal, b.nama, t.stok, b.harga, (t.stok * b.harga) AS total
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    WHERE t.status = 'masuk'
    ORDER BY t.tanggal DESC, t.id_transaksi DESC
    LIMIT 5
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Transaksi Masuk | F-ZONE COMPANY</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  
  <style>
    :root {
        --dash-bg: #1e293b;
        --card-bg: rgba(255, 255, 255, 0.03);
        --card-border: rgba(255, 255, 255, 0.08);
        --text-main: #f8fafc;
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

    /* === CARD STYLE === */
    .card { 
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        border: 1px solid var(--card-border);
        border-radius: 15px;
        margin-bottom: 30px;
        overflow: hidden;
    }
    .card-header { 
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--card-border);
        padding: 15px 20px;
    }

    /* === DARK TABLE STYLE === */
    .table-dark-custom { color: #cbd5e1; margin-bottom: 0; }
    .table-dark-custom thead th {
        background-color: var(--table-header);
        color: #818cf8;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 15px;
        border: none;
    }
    .table-dark-custom tbody td {
        background-color: transparent;
        border-bottom: 1px solid var(--card-border);
        padding: 15px;
        color: #e2e8f0;
    }
    .table-dark-custom tbody tr:hover {
        background-color: rgba(255, 255, 255, 0.02);
    }

    /* === FORM STYLE === */
    .form-control {
        background: rgba(255, 255, 255, 0.05);
        border: 1px solid var(--card-border);
        color: white;
        border-radius: 8px;
    }
    .form-control:focus {
        background: rgba(255, 255, 255, 0.08);
        border-color: var(--accent);
        box-shadow: none;
        color: white;
    }

    .btn-remove { color: #f43f5e; cursor: pointer; font-size: 1.3rem; transition: 0.2s; }
    .btn-remove:hover { transform: scale(1.2); }

    /* === MODAL STYLE === */
    .modal-content {
        background: #1e293b;
        border: 1px solid var(--card-border);
        color: white;
    }
    .modal-header { border-bottom: 1px solid var(--card-border); }

    @media (max-width: 992px) { .content-wrapper { margin-left: 0; } }
  </style>
</head>
<body>

<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="alert-container">
            <?= displayAlert(); ?>
        </div>

        <section class="mb-4">
            <h2 class="fw-bold"><i class="fas fa-arrow-alt-circle-down text-success me-2"></i>Transaksi Barang Masuk</h2>
        </section>

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="m-0 fw-bold"><i class="fas fa-edit me-2 text-primary"></i>Input Item Masuk</h5>
                <button type="button" id="addBtn" class="btn btn-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-plus me-1"></i> Tambah Baris
                </button>
            </div>
            <div class="card-body p-0">
                <form method="post">
                    <div class="table-responsive">
                        <table class="table table-dark-custom align-middle">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th width="250">Harga Satuan (Rp)</th>
                                    <th width="150">Jumlah</th>
                                    <th width="80" class="text-center">Hapus</th>
                                </tr>
                            </thead>
                            <tbody id="formBody">
                                <tr>
                                    <td>
                                        <input type="text" name="nama[]" class="form-control" required placeholder="Masukkan nama barang...">
                                    </td>
                                    <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
                                    <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
                                    <td class="text-center">
                                        <i class="fas fa-times-circle btn-remove removeRow"></i>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="p-4 border-top border-secondary text-end">
                        <button type="reset" class="btn btn-outline-light px-4 me-2">Reset</button>
                        <button type="submit" name="tambah_barang_masuk" class="btn btn-success px-5 shadow">
                            <i class="fas fa-save me-2"></i>Simpan Transaksi
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header">
                <h5 class="m-0 fw-bold text-info"><i class="fas fa-history me-2"></i>5 Transaksi Terakhir</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark-custom table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Jumlah</th>
                                <th>Harga Satuan</th>
                                <th>Total Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transaksi_terakhir->num_rows > 0): while ($row = $transaksi_terakhir->fetch_assoc()): ?>
                                <tr class="transaksi-row" 
                                    data-tanggal="<?= date('d/m/Y', strtotime($row['tanggal'])) ?>"
                                    data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                    data-jumlah="<?= $row['stok'] ?>"
                                    data-harga="<?= number_format($row['harga'], 0, ',', '.') ?>"
                                    data-total="<?= number_format($row['total'], 0, ',', '.') ?>"
                                    style="cursor:pointer">
                                    <td><span class="text-muted"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></span></td>
                                    <td><span class="text-white fw-bold"><?= htmlspecialchars($row['nama']) ?></span></td>
                                    <td><span class="badge bg-primary"><?= $row['stok'] ?></span></td>
                                    <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
                                    <td class="text-success fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                </tr>
                            <?php endwhile; else: ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted small">Belum ada riwayat transaksi masuk.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalNota" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" id="notaArea">
      <div class="modal-header">
        <h5 class="modal-title fw-bold text-primary">🧾 NOTA TRANSAKSI</h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-4">
        <div class="text-center mb-4 border-bottom border-secondary pb-3">
          <h3 class="fw-bold mb-0">F-ZONE COMPANY</h3>
          <small class="text-muted">Inbound Log Inventory System</small>
        </div>
        <table class="table table-borderless text-white">
          <tr><td>Tanggal</td><td class="text-end" id="notaTanggal"></td></tr>
          <tr><td>Barang</td><td class="text-end fw-bold" id="notaNama"></td></tr>
          <tr><td>Kuantitas</td><td class="text-end" id="notaJumlah"></td></tr>
          <tr><td>Harga Unit</td><td class="text-end">Rp <span id="notaHarga"></span></td></tr>
          <tr class="border-top border-secondary fw-bold fs-5">
            <td class="pt-3">TOTAL</td>
            <td class="text-end text-success pt-3">Rp <span id="notaTotal"></span></td>
          </tr>
        </table>
        <p class="text-center mt-4 text-muted small italic">*** Dokumen Sah Inventaris Perusahaan ***</p>
      </div>
      <div class="modal-footer border-0">
        <button onclick="cetakNota()" class="btn btn-primary w-100">
          <i class="fas fa-print me-2"></i>Cetak Nota Fisik
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function() {
    $("#addBtn").click(function() {
        var row = `<tr>
            <td><input type="text" name="nama[]" class="form-control" required placeholder="Nama Barang"></td>
            <td><input type="number" name="harga[]" class="form-control" required min="1" placeholder="0"></td>
            <td><input type="number" name="stok[]" class="form-control" required min="1" placeholder="0"></td>
            <td class="text-center"><i class="fas fa-times-circle btn-remove removeRow"></i></td>
        </tr>`;
        $("#formBody").append(row);
    });

    $(document).on('click', '.removeRow', function() {
        if ($('#formBody tr').length > 1) {
            $(this).closest('tr').fadeOut(200, function(){ $(this).remove(); });
        } else {
            alert("Minimal harus ada satu baris!");
        }
    });

    $(document).on('click', '.transaksi-row', function () {
        $('#notaTanggal').text($(this).data('tanggal'));
        $('#notaNama').text($(this).data('nama'));
        $('#notaJumlah').text($(this).data('jumlah') + " Unit");
        $('#notaHarga').text($(this).data('harga'));
        $('#notaTotal').text($(this).data('total'));
        new bootstrap.Modal(document.getElementById('modalNota')).show();
    });
});

function cetakNota() {
    var isi = document.getElementById('notaArea').innerHTML;
    var win = window.open('', '', 'width=500,height=700');
    win.document.write(`<html><head><title>Nota Masuk</title><style>
        body { font-family: 'Courier New', monospace; padding: 40px; color: black; }
        .text-end { text-align: right; }
        table { width: 100%; margin-top: 20px; border-top: 1px dashed black; }
        .fw-bold { font-weight: bold; }
        .text-center { text-align: center; }
        .btn-close-white, .btn-primary, .modal-header { display: none; }
    </style></head><body>${isi}</body></html>`);
    win.document.close();
    win.focus();
    setTimeout(() => { win.print(); win.close(); }, 500);
}
</script>
</body>
</html>