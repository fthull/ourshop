<?php
include "conn.php";
include "auth.php";
$active_page = 'dashboard';
if (!isset($_SESSION['login'])) {
    header("Location: login.php");
    exit;
}
$total_Barang = $conn->query("SELECT COUNT(*) as jml FROM barang")->fetch_assoc()['jml'];
$total_barangMasuk = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE status='masuk' AND tanggal=CURDATE()")->fetch_assoc()['jml'];
$total_barangKeluar = $conn->query("SELECT COUNT(*) as jml FROM transaksi WHERE status='keluar' AND tanggal=CURDATE()")->fetch_assoc()['jml'];
$result = $conn->query("SELECT SUM(stok) AS total_stok FROM barang");
$data = $result->fetch_assoc();
$total_stokBarang = $data['total_stok'] ?? 0;

$tanggal = [];
$keluar = [];
$masuk  = [];

$q = $conn->query("
    SELECT DATE(tanggal) tgl,
           SUM(CASE WHEN status='keluar' THEN stok ELSE 0 END) AS keluar,
           SUM(CASE WHEN status='masuk' THEN stok ELSE 0 END) AS masuk
    FROM transaksi
    GROUP BY DATE(tanggal)
    ORDER BY DATE(tanggal)
");

while ($r = $q->fetch_assoc()) {
    $tanggal[] = date('d M', strtotime($r['tgl']));
    $keluar[]  = $r['keluar'];
    $masuk[]   = $r['masuk'];
}


$transaksi_terakhir = $conn->query("
    SELECT 
        t.tanggal,
        b.nama,
        t.stok,
        b.harga,
        (t.stok * b.harga) AS total,
        t.status
    FROM transaksi t
    JOIN barang b ON t.id_barang = b.id_barang
    ORDER BY t.tanggal DESC, t.id_transaksi DESC
    LIMIT 5
");

?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
  <style>
     body { background: #f4f6f9; }
    .container { margin-top: 40px; }
    .card { border-radius: 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.08); }
    table { font-size: 14px; }

    @media (max-width: 768px) { .card-header h4 { font-size: 18px; } table { font-size: 13px; } }
    @media (max-width: 576px) {
      table thead { display: none; }
      table tr { display: block; margin-bottom: 10px; border-bottom: 1px solid #ddd; }
      table td { display: block; text-align: right; font-size: 13px; }
      table td::before { content: attr(data-label); float: left; font-weight: bold; text-transform: capitalize; }
    }

    /* Statistik */
    .stats-container { display: flex; flex-wrap: wrap; gap: 20px; margin-bottom: 20px; }
    .stat-box { flex: 1 1 calc(25% - 20px); min-width: 200px; padding: 20px; border-radius: 15px; color: #fff; display: flex; align-items: center; justify-content: space-between; transition: transform 0.2s; }
    .stat-box:hover { transform: translateY(-5px); }
    .stat-box .info h3 { font-size: 26px; margin: 0; }
    .stat-box .info p { margin: 0; font-size: 14px; opacity: 0.9; }
    .stat-box .icon { font-size: 40px; opacity: 0.7; }
    .bg-info { background: #17a2b8; }
    .bg-success { background: #28a745; }
    .bg-danger { background: #dc3545; }
    .bg-warning { background: #ffc107; color: #333; }
    @media (max-width: 992px) { .stat-box { flex: 1 1 calc(50% - 20px); } }
    @media (max-width: 576px) { .stat-box { flex: 1 1 100%; } }

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
  </style>
</head>
<body>

<div class="app-wrapper">

  <!-- SIDEBAR -->
  <?php include 'sidebar.php'; ?>

  <!-- CONTENT -->
  <div class="content-wrapper">

    <section class="content-header mb-4">
      <h2><i class="fas fa-tachometer-alt me-2"></i>Dashboard</h2>
    </section>

    <!-- Statistik -->
    <div class="stats-container">
      <div class="stat-box bg-info">
        <div class="info">
          <h3><?= $total_Barang; ?> Jenis</h3>
          <p>Total Barang</p>
        </div>
        <div class="icon"><i class="fas fa-warehouse"></i></div>
      </div>

      <div class="stat-box bg-success">
        <div class="info">
          <h3><?= $total_barangMasuk; ?> Jenis</h3>
          <p>Barang Masuk Hari Ini</p>
        </div>
        <div class="icon"><i class="fas fa-arrow-down"></i></div>
      </div>

      <div class="stat-box bg-danger">
        <div class="info">
          <h3><?= $total_barangKeluar; ?> Jenis</h3>
          <p>Barang Keluar Hari Ini</p>
        </div>
        <div class="icon"><i class="fas fa-arrow-up"></i></div>
      </div>

      <div class="stat-box bg-warning">
        <div class="info">
          <h3><?= $total_stokBarang; ?> Item</h3>
          <p>Total Stok</p>
        </div>
        <div class="icon"><i class="fas fa-boxes"></i></div>
      </div>
    </div>

    <!-- Grafik -->
    <div class="card mb-4">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-chart-line me-2"></i>Statistik Transaksi
        </h3>
      </div>
      <div class="card-body">
        <canvas id="chartHarian"></canvas>
      </div>
    </div>

    <!-- Transaksi Terbaru -->
    <div class="card">
      <div class="card-header">
        <h3 class="card-title">
          <i class="fas fa-bell me-2"></i>Transaksi Terbaru
        </h3>
      </div>
      <div class="card-body table-responsive">
          <table class="table table-striped align-middle">
            <thead class="table-light">
              <tr>
                <th>Tanggal</th>
                <th>Nama Barang</th>
                <th >Jumlah</th>
                <th>Harga</th>
                <th >Total</th>
                <th >Status</th>
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

        <td><?= date('d/m/Y', strtotime($row['tanggal'])) ?></td>
        <td><?= htmlspecialchars($row['nama']) ?></td>
        <td><?= $row['stok'] ?></td>
        <td>Rp <?= number_format($row['harga'], 0, ',', '.') ?></td>
        <td>Rp <?= number_format($row['total'], 0, ',', '.') ?></td>
        <td><?= $row['status'] ?></td>
    </tr>
  <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center text-muted py-4">
            Belum ada transaksi barang 
        </td>
    </tr>
<?php endif; ?>
</tbody>
          </table>
        </div>
      </div>

        </div>
      </div>
    </section>
  </div>
</div>
<div class="modal fade" id="modalNota" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content" id="notaArea">
      <div class="modal-header">
        <h5 class="modal-title fw-bold">🧾 NOTA TRANSAKSI</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <div class="text-center mb-3">
          <h4 class="fw-bold">F-ZONE COMPANY</h4>
          <small>Transaksi Barang</small>
        </div>

        <table class="table table-borderless">
          <tr>
            <td>Tanggal</td>
            <td class="text-end" id="notaTanggal"></td>
          </tr>
          <tr>
            <td>Nama Barang</td>
            <td class="text-end" id="notaNama"></td>
          </tr>
          <tr>
            <td>Jumlah</td>
            <td class="text-end" id="notaJumlah"></td>
          </tr>
          <tr>
            <td>Harga</td>
            <td class="text-end">Rp <span id="notaHarga"></span></td>
          </tr>
          <tr class="border-top fw-bold">
            <td>Total</td>
            <td class="text-end text-danger">Rp <span id="notaTotal"></span></td>
          </tr>
          <tr>
            <td>Status</td>
            <td class="text-end" id="notaStatus"></td>
          </tr>
        </table>

        <p class="text-center mt-3">Terima kasih 🙏</p>
      </div>

      <div class="modal-footer justify-content-end">
        <button onclick="cetakNota()" class="btn btn-success">
          <i class="fas fa-print me-1"></i> Cetak Nota
        </button>
      </div>
      </div>
    </div>

  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).on('click', '.transaksi-row', function () {
    $('#notaTanggal').text($(this).data('tanggal'));
    $('#notaNama').text($(this).data('nama'));
    $('#notaJumlah').text($(this).data('jumlah'));
    $('#notaHarga').text($(this).data('harga'));
    $('#notaTotal').text($(this).data('total'));
    $('#notaStatus').text($(this).data('status'));

    new bootstrap.Modal(document.getElementById('modalNota')).show();
});

function cetakNota() {
    var isi = document.getElementById('notaArea').innerHTML;
    var win = window.open('', '', 'width=400,height=600');
    win.document.write(`
      <html>
        <head>
          <title>Cetak Nota</title>
          <style>
            body { font-family: Arial; padding: 20px; }
            table { width: 100%; }
            td { padding: 4px 0; }
          </style>
        </head>
        <body>${isi}</body>
      </html>
    `);
    win.document.close();
    win.print();
}
const ctx = document.getElementById('chartHarian').getContext('2d');

new Chart(ctx, {
    type: 'line',
    data: {
        labels: <?= json_encode($tanggal) ?>,
        datasets: [
          {
              label: 'Barang Masuk',
              data: <?= json_encode($masuk) ?>,
              borderWidth: 3,
              tension: 0.4,
              fill: true
          },
            {
                label: 'Barang Keluar',
                data: <?= json_encode($keluar) ?>,
                borderWidth: 3,
                tension: 0.4,
                fill: true
            },
        ]
    },
    options: {
        responsive: true,
        interaction: {
            mode: 'index',
            intersect: false
        },
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                enabled: true
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                title: {
                    display: true,
                    text: 'Jumlah Barang'
                }
            },
            x: {
                title: {
                    display: true,
                    text: 'Tanggal'
                }
            }
        }
    }
});
</script>
</body>
</html>
