
<aside class="main-sidebar sidebar-dark-primary bg-dark p-3">
  <h2 class="text-white">
    <i class="fas fa-store"></i> F-Zone Company
  </h2>

  <ul class="nav nav-pills nav-sidebar flex-column mt-4">
    <li class="nav-item">
      <a href="dashboard.php" class="nav-link <?= $active_page=='dashboard'?'active':'' ?>">
        <i class="fas fa-tachometer-alt"></i> <p>Dashboard</p>
      </a>
    </li>

    <li class="nav-item">
      <a href="barang.php" class="nav-link <?= $active_page=='barang'?'active':'' ?>">
        <i class="fas fa-warehouse"></i> <p>Data Barang</p>
      </a>
    </li>

    <li class="nav-item">
      <a href="transaksi_masuk.php" class="nav-link <?= $active_page=='transaksi_masuk'?'active':'' ?>">
        <i class="fas fa-arrow-down"></i> <p>Transaksi Masuk</p>
      </a>
    </li>

    <li class="nav-item">
      <a href="transaksi_keluar.php" class="nav-link <?= $active_page=='transaksi_keluar'?'active':'' ?>">
        <i class="fas fa-arrow-up"></i> <p>Transaksi Keluar</p>
      </a>
    </li>
<?php if ($_SESSION['role'] === 'admin') : ?>
    <li class="nav-item">
      <a href="admin.php" class="nav-link <?= $active_page=='admin'?'active':'' ?>">
        <i class="fas fa-user-cog"></i> <p>Admin & Petugas</p>
      </a>
    </li>
 <?php endif; ?>
    <li class="nav-item">
      <a href="akun.php" class="nav-link <?= $active_page=='akun'?'active':'' ?>">
        <i class="fas fa-user-cog"></i> <p>Akun</p>
      </a>
    </li>

    <li class="nav-item">
      <a href="logout.php" class="nav-link logout-link">
        <i class="fas fa-sign-out-alt"></i> <p>Logout</p>
      </a>
    </li>
  </ul>
</aside>
