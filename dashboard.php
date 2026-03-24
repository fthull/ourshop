<?php
include "conn.php";
include "auth.php";
$active_page = 'dashboard';

if (!isset($_SESSION['login'])) {
    header("Location: index.php");
    exit;
}

// --- Query Data ---
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
    <title>Dashboard | OurShop</title>

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
        --table-header: #0f172a; /* Warna header tabel yang konsisten */
    }

    body { 
        font-family: 'Inter', sans-serif;
        background-color: var(--dash-bg);
        color: var(--text-main);
    }

    .app-wrapper { display: flex; min-height: 100vh; }
    .content-wrapper { flex: 1; padding: 30px; margin-left: 280px; transition: 0.3s; }

    /* --- Elegant Dark Card --- */
    .card { 
        border: 1px solid var(--card-border); 
        border-radius: 15px; 
        background: var(--card-bg);
        backdrop-filter: blur(10px);
        margin-bottom: 25px;
        overflow: hidden;
    }

    .card-header {
        background: rgba(255, 255, 255, 0.02);
        border-bottom: 1px solid var(--card-border);
        padding: 15px 20px;
    }
    .card-header h3 { font-size: 1rem; font-weight: 700; margin-bottom: 0; color: #ffffff; }

    /* --- STAT BOXES --- */
    .stats-container { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 20px; margin-bottom: 30px; }
    .stat-box { 
        padding: 25px; 
        border-radius: 20px; 
        color: #fff; 
        display: flex; 
        align-items: center; 
        justify-content: space-between; 
        transition: 0.3s;
        border: 1px solid rgba(255,255,255,0.1);
    }
    .stat-box:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.3); }
    .bg-gradient-blue { background: linear-gradient(135deg, #1e40af, #3b82f6); }
    .bg-gradient-green { background: linear-gradient(135deg, #064e3b, #10b981); }
    .bg-gradient-red { background: linear-gradient(135deg, #7f1d1d, #ef4444); }
    .bg-gradient-orange { background: linear-gradient(135deg, #78350f, #f59e0b); }

    /* --- TABLE DARK CUSTOM (Gaya Konsisten) --- */
    .table-dark-custom { color: #cbd5e1; margin-bottom: 0; }
    .table-dark-custom thead th {
        background-color: var(--table-header) !important;
        color: #6366f1 !important; /* Warna ungu/biru neon */
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 15px;
        border: none;
        border-bottom: 2px solid rgba(99, 102, 241, 0.2) !important;
    }
    .table-dark-custom tbody td {
        background-color: transparent;
        border-bottom: 1px solid var(--card-border);
        padding: 14px 15px;
        vertical-align: middle;
        color: #e2e8f0;
    }
    .table-dark-custom tbody tr:hover { 
        background-color: rgba(99, 102, 241, 0.05) !important; 
        transition: 0.3s; 
    }

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
                <h2 class="fw-bold mb-1">Overview Dashboard</h2>
                <p class="text-muted small mb-0">Monitor aktivitas inventaris <strong>OurShop</strong>.</p>
            </div>
            <div class="text-end">
                <span class="badge bg-dark text-white shadow-sm p-2 px-3 border border-secondary">
                    <i class="far fa-calendar-alt me-2 text-primary"></i> <?= date('d M Y') ?>
                </span>
            </div>
        </div>

        <div class="stats-container">
            <div class="stat-box bg-gradient-blue">
                <div class="info"><h3><?= $total_Barang; ?></h3><p class="mb-0 opacity-75">Jenis Barang</p></div>
                <div class="icon fs-1 opacity-25"><i class="fas fa-box"></i></div>
            </div>
            <div class="stat-box bg-gradient-green">
                <div class="info"><h3><?= $total_barangMasuk; ?></h3><p class="mb-0 opacity-75">Masuk Hari Ini</p></div>
                <div class="icon fs-1 opacity-25"><i class="fas fa-file-import"></i></div>
            </div>
            <div class="stat-box bg-gradient-red">
                <div class="info"><h3><?= $total_barangKeluar; ?></h3><p class="mb-0 opacity-75">Keluar Hari Ini</p></div>
                <div class="icon fs-1 opacity-25"><i class="fas fa-file-export"></i></div>
            </div>
            <div class="stat-box bg-gradient-orange">
                <div class="info"><h3><?= number_format($total_stokBarang); ?></h3><p class="mb-0 opacity-75">Total Unit</p></div>
                <div class="icon fs-1 opacity-25"><i class="fas fa-warehouse"></i></div>
            </div>
        </div>

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow">
                    <div class="card-header">
                        <h3><i class="fas fa-chart-area me-2 text-primary"></i>Tren Stok Harian</h3>
                    </div>
                    <div class="card-body">
                        <canvas id="chartHarian" style="max-height: 300px;"></canvas>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card shadow">
                    <div class="card-header">
                        <h3><i class="fas fa-user-circle me-2 text-primary"></i>Profil Sesi</h3>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3 p-3 rounded-3 bg-white bg-opacity-5">
                            <div class="flex-shrink-0 bg-primary p-3 rounded-circle text-white me-3">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">User Aktif</p>
                                <h6 class="mb-0 fw-bold"><?= $_SESSION['nama_user'] ?></h6>
                            </div>
                        </div>
                        <div class="d-flex align-items-center p-3 rounded-3 bg-white bg-opacity-5">
                            <div class="flex-shrink-0 bg-success p-3 rounded-circle text-white me-3">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div>
                                <p class="mb-0 small text-muted">Status</p>
                                <h6 class="mb-0 fw-bold text-success"><?= $_SESSION['role'] ?></h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3><i class="fas fa-history me-2 text-primary"></i>Log Transaksi Terbaru</h3>
                <span class="badge bg-primary opacity-75">5 Terakhir</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-dark-custom align-middle">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Nama Barang</th>
                                <th>Qty</th>
                                <th>Total Nilai</th>
                                <th class="text-center">Status</th>
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
                                        <td class="fw-bold text-white"><?= htmlspecialchars($row['nama']) ?></td>
                                        <td><span class="badge bg-secondary"><?= $row['stok'] ?></span></td>
                                        <td class="text-primary fw-bold">Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
                                        <td class="text-center">
                                            <?php if($row['status'] == 'masuk'): ?>
                                                <span class="badge bg-success-subtle">Masuk</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle">Keluar</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center py-5 text-muted small">Belum ada aktivitas hari ini.</td></tr>
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
        <div class="modal-content bg-dark border-secondary text-white" style="border-radius: 20px;">
            <div class="modal-body p-4" id="notaArea">
                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-0 text-primary">OurShop</h4>
                    <small class="text-muted">Inventory Digital Receipt</small>
                    <hr class="border-secondary">
                </div>
                <div class="row g-3 mb-4">
                    <div class="col-6 small text-muted">Tanggal</div>
                    <div class="col-6 text-end fw-bold" id="notaTanggal"></div>
                    <div class="col-6 small text-muted">Barang</div>
                    <div class="col-6 text-end fw-bold" id="notaNama"></div>
                    <div class="col-6 small text-muted">Jumlah</div>
                    <div class="col-6 text-end fw-bold text-info" id="notaJumlah"></div>
                    <div class="col-6 small text-muted">Total Nilai</div>
                    <div class="col-6 text-end fw-bold text-primary fs-5">Rp <span id="notaTotal"></span></div>
                </div>
                <div id="notaStatus" class="text-center p-2 rounded-3 bg-white bg-opacity-5 fw-bold"></div>
            </div>
            <div class="modal-footer border-0">
                <button onclick="cetakNota()" class="btn btn-primary w-100 py-2"><i class="fas fa-print me-2"></i>Cetak Nota</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
// Modal Logic
$(document).on('click', '.transaksi-row', function () {
    $('#notaTanggal').text($(this).data('tanggal'));
    $('#notaNama').text($(this).data('nama'));
    $('#notaJumlah').text($(this).data('jumlah') + ' Unit');
    $('#notaTotal').text($(this).data('total'));
    $('#notaStatus').html($(this).data('status') == 'masuk' ? '<span class="text-success">DITERIMA</span>' : '<span class="text-danger">DIKELUARKAN</span>');
    new bootstrap.Modal(document.getElementById('modalNota')).show();
});

function cetakNota() {
    var isi = document.getElementById('notaArea').innerHTML;
    var win = window.open('', '', 'width=600,height=600');
    win.document.write('<html><head><title>Nota</title><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"></head><body onload="window.print()">'+isi+'</body></html>');
    win.document.close();
}

// Chart Logic
const ctx = document.getElementById('chartHarian').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($tanggal) ?>,
        datasets: [
            { label: 'Masuk', data: <?= json_encode($masuk) ?>, borderColor: '#10b981', backgroundColor: 'rgba(16, 185, 129, 0.1)', tension: 0.4, fill: true },
            { label: 'Keluar', data: <?= json_encode($keluar) ?>, borderColor: '#f43f5e', backgroundColor: 'rgba(244, 63, 94, 0.1)', tension: 0.4, fill: true }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { labels: { color: '#94a3b8' } } },
        scales: {
            y: { grid: { color: 'rgba(255,255,255,0.05)' }, ticks: { color: '#94a3b8' } },
            x: { grid: { display: false }, ticks: { color: '#94a3b8' } }
        }
    }
});
</script>
</body>
</html>