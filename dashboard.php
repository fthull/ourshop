<?php
include "conn.php";
include "auth.php";
$active_page = 'dashboard';

if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}

// --- Query Data (Sama seperti sebelumnya) ---
$total_Barang = $conn->query("SELECT COUNT(*) as jml FROM barang")->fetch_assoc()['jml'];
$total_barangMasuk = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE status='masuk' AND tanggal=CURDATE()")->fetch_assoc()['jml'];
$total_barangKeluar = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE status='keluar' AND tanggal=CURDATE()")->fetch_assoc()['jml'];
$total_stokBarang = $conn->query("SELECT SUM(stok) AS total_stok FROM barang")->fetch_assoc()['total_stok'] ?? 0;

$tanggal = []; $keluar = []; $masuk = [];
$q = $conn->query("SELECT DATE(tanggal) tgl, SUM(CASE WHEN status='keluar' THEN stok ELSE 0 END) AS keluar, SUM(CASE WHEN status='masuk' THEN stok ELSE 0 END) AS masuk FROM transaksi GROUP BY DATE(tanggal) ORDER BY DATE(tanggal) ASC");
while ($r = $q->fetch_assoc()) {
    $tanggal[] = date('d M', strtotime($r['tgl']));
    $keluar[]  = $r['keluar'];
    $masuk[]   = $r['masuk'];
}

$transaksi_terakhir = $conn->query("SELECT t.tanggal, b.nama, t.stok, b.harga, (t.stok * b.harga) AS total, t.status FROM transaksi t JOIN barang b ON t.id_barang = b.id_barang ORDER BY t.tanggal DESC, t.id_transaksi DESC LIMIT 5");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | F-ZONE COMPANY</title>
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

    <style>
    :root {
        --sidebar-bg: #111827;
        --dash-bg: #1e293b; /* Warna background utama dashboard (berbeda dari sidebar) */
        --card-bg: rgba(255, 255, 255, 0.03); /* Efek Glass */
        --card-border: rgba(255, 255, 255, 0.08);
        --text-main: #f8fafc;
        --text-muted: #94a3b8;
    }

    body { 
        font-family: 'Inter', sans-serif;
        background-color: var(--dash-bg);
        color: var(--text-main);
    }

    .app-wrapper { display: flex; min-height: 100vh; }

    .content-wrapper { 
        flex: 1; 
        padding: 30px; 
        margin-left: 280px; 
        transition: 0.3s;
    }

    /* Header Styling */
    h2.fw-bold { color: #ffffff; text-shadow: 0 2px 4px rgba(0,0,0,0.2); }
    .text-muted { color: var(--text-muted) !important; }

    /* --- Elegant Dark Card (Glassmorphism) --- */
    .card { 
        border: 1px solid var(--card-border); 
        border-radius: 20px; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
        background: var(--card-bg);
        backdrop-filter: blur(10px); /* Efek blur di belakang kartu */
        margin-bottom: 25px;
    }

    .card-header {
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--card-border);
        padding: 20px 25px;
        color: #ffffff;
    }

    .card-header h3 { font-size: 1.1rem; font-weight: 700; margin-bottom: 0; color: #ffffff; }

    /* --- Stat Boxes (Neon Style) --- */
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    
    .stat-box { 
        padding: 25px; 
        border-radius: 20px; 
        color: #fff; 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        transition: all 0.3s ease;
        border: 1px solid rgba(255,255,255,0.1);
    }
    
    .stat-box:hover { transform: translateY(-7px); box-shadow: 0 15px 30px rgba(0,0,0,0.3); }

    /* Gradasi yang lebih "Deep" untuk Dark Mode */
    .bg-gradient-blue { background: linear-gradient(135deg, #1e40af, #3b82f6); }
    .bg-gradient-green { background: linear-gradient(135deg, #064e3b, #10b981); }
    .bg-gradient-red { background: linear-gradient(135deg, #7f1d1d, #ef4444); }
    .bg-gradient-orange { background: linear-gradient(135deg, #78350f, #f59e0b); }

    /* --- Table Styling (Dark) --- */
    .table { color: #cbd5e1; }
    .table thead th {
        background: rgba(255, 255, 255, 0.05);
        color: #94a3b8;
        border: none;
        font-weight: 600;
    }
    .table tbody td { border-color: var(--card-border); padding: 15px; }
    .table-hover tbody tr:hover { background-color: rgba(255,255,255,0.02); }

    /* Badge Custom */
    .bg-success-subtle { background: rgba(16, 185, 129, 0.1) !important; color: #10b981 !important; border: 1px solid rgba(16, 185, 129, 0.2); }
    .bg-danger-subtle { background: rgba(244, 63, 94, 0.1) !important; color: #f43f5e !important; border: 1px solid rgba(244, 63, 94, 0.2); }

    @media (max-width: 992px) { .content-wrapper { margin-left: 0; } }
</style>
</head>
<body>

<div class="app-wrapper">
    <?php include 'sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold mb-1">Overview</h2>
                <p class="text-muted small mb-0">Selamat datang kembali, <strong><?= $_SESSION['nama_user'] ?></strong>!</p>
            </div>
            <div class="text-end">
                <span class="badge bg-white text-dark shadow-sm p-2 px-3 border-radius-10">
                    <i class="far fa-calendar-alt me-2 text-primary"></i> <?= date('d M Y') ?>
                </span>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box bg-gradient-blue">
                <div class="info">
                    <h3><?= $total_Barang; ?></h3>
                    <p>Jenis Barang</p>
                </div>
                <div class="icon"><i class="fas fa-box"></i></div>
            </div>

            <div class="stat-box bg-gradient-green">
                <div class="info">
                    <h3><?= $total_barangMasuk; ?></h3>
                    <p>Masuk Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-file-import"></i></div>
            </div>

            <div class="stat-box bg-gradient-red">
                <div class="info">
                    <h3><?= $total_barangKeluar; ?></h3>
                    <p>Keluar Hari Ini</p>
                </div>
                <div class="icon"><i class="fas fa-file-export"></i></div>
            </div>

            <div class="stat-box bg-gradient-orange">
                <div class="info">
                    <h3><?= number_format($total_stokBarang); ?></h3>
                    <p>Total Stok Unit</p>
                </div>
                <div class="icon"><i class="fas fa-warehouse"></i></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3><i class="fas fa-chart-area me-2 text-primary"></i>Tren Stok Harian</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartHarian" style="max-height: 350px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-info-circle me-2 text-primary"></i>Status Sistem</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="flex-shrink-0 bg-dark p-3 rounded-circle text-primary me-3">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Role Anda</p>
                                <h6 class="mb-0 fw-bold"><?= ucfirst($_SESSION['role']) ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center">
                            <div class="flex-shrink-0 bg-dark p-3 rounded-circle text-success me-3">
                                <i class="fas fa-database"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Database</p>
                                <h6 class="mb-0 fw-bold">Connected</h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3><i class="fas fa-history me-2 text-primary"></i>Transaksi Terakhir</h3>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Barang</th>
                                <th>Qty</th>
                                <th>Total Nilai</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($transaksi_terakhir->num_rows > 0): ?>
                                <?php while ($row = $transaksi_terakhir->fetch_assoc()): ?>
                                    <tr class="transaksi-row" 
                                        data-tanggal="<?= date('d/m/Y', strtotime($row['tanggal'])) ?>"
                                        data-nama="<?= htmlspecialchars($row['nama']) ?>"
                                        data-jumlah="<?= $row['stok'] ?>"
                                        data-harga="<?= number_format($row['harga'], 0, ',', '.') ?>"
                                        data-total="<?= number_format($row['total'], 0, ',', '.') ?>"
                                        data-status="<?= $row['status'] ?>"
                                        style="cursor:pointer">
                                        <td><span class="text-muted small"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></span></td>
                                        <td class="fw-bold"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><?= $row['stok'] ?></td>
                                        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        <td>
                                            <?php if($row['status'] == 'masuk'): ?>
                                                <span class="badge badge-status bg-success-subtle text-success border border-success-subtle">Masuk</span>
                                            <?php else: ?>
                                                <span class="badge badge-status bg-danger-subtle text-danger border border-danger-subtle">Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted">Belum ada aktivitas transaksi.</td></tr>
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
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4" id="notaArea">
                <div class="text-center mb-4">
                    <div class="bg-primary text-white d-inline-block p-3 rounded-circle mb-3">
                        <i class="fas fa-receipt fa-2x"></i>
                    </div>
                    <h4 class="fw-bold mb-0">F-ZONE COMPANY</h4>
                    <small class="text-muted uppercase">Inventory Receipt</small>
                </div>
                <table class="table table-sm table-borderless">
                    <tr><td>Tanggal</td><td class="text-end fw-bold" id="notaTanggal"></td></tr>
                    <tr><td>Nama Barang</td><td class="text-end fw-bold" id="notaNama"></td></tr>
                    <tr><td>Jumlah</td><td class="text-end fw-bold" id="notaJumlah"></td></tr>
                    <tr><td>Harga Satuan</td><td class="text-end fw-bold">Rp <span id="notaHarga"></span></td></tr>
                    <tr class="border-top"><td class="pt-2">Total Nilai</td><td class="text-end pt-2 fw-bold text-primary fs-5">Rp <span id="notaTotal"></span></td></tr>
                    <tr><td>Tipe Transaksi</td><td class="text-end" id="notaStatus"></td></tr>
                </table>
            </div>
            <div class="modal-footer border-0 p-4 pt-0">
                <button onclick="cetakNota()" class="btn btn-primary w-100 py-2" style="border-radius: 12px;">
                    <i class="fas fa-print me-2"></i>Cetak Dokumen
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Logic Modal
$(document).on('click', '.transaksi-row', function () {
    $('#notaTanggal').text($(this).data('tanggal'));
    $('#notaNama').text($(this).data('nama'));
    $('#notaJumlah').text($(this).data('jumlah'));
    $('#notaHarga').text($(this).data('harga'));
    $('#notaTotal').text($(this).data('total'));
    $('#notaStatus').html($(this).data('status') == 'masuk' ? '<span class="text-success">BARANG MASUK</span>' : '<span class="text-danger">BARANG KELUAR</span>');
    new bootstrap.Modal(document.getElementById('modalNota')).show();
});

function cetakNota() {
    var isi = document.getElementById('notaArea').innerHTML;
    var win = window.open('', '', 'width=600,height=600');
    win.document.write('<html><head><title>Cetak Nota</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body onload="window.print()">'+isi+'</body></html>');
    win.document.close();
}

// Logic Chart dengan warna tema
const ctx = document.getElementById('chartHarian').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($tanggal) ?>,
        datasets: [
            {
                label: 'Barang Masuk',
                data: <?= json_encode($masuk) ?>,
                borderColor: '#10b981',
                backgroundColor: 'rgba(16, 185, 129, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            },
            {
                label: 'Barang Keluar',
                data: <?= json_encode($keluar) ?>,
                borderColor: '#f43f5e',
                backgroundColor: 'rgba(244, 63, 94, 0.1)',
                borderWidth: 3,
                tension: 0.4,
                fill: true
            }
        ]
    },
    options: {
    responsive: true,
    plugins: { 
        legend: { 
            labels: { color: '#94a3b8' } // Warna teks legenda
        } 
    },
    scales: {
        y: { 
            grid: { color: 'rgba(255,255,255,0.05)' }, 
            ticks: { color: '#94a3b8' } // Warna angka sumbu Y
        },
        x: { 
            grid: { display: false }, 
            ticks: { color: '#94a3b8' } // Warna teks sumbu X
        }
    }
}
});
</script>
</body>
</html>