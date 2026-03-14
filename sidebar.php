<aside class="main-sidebar">
    <div class="sidebar-brand">
        <div class="brand-box">
            <i class="fas fa-cubes floating-icon"></i>
        </div>
        <div class="brand-text">
            <h4 class="fw-bold mb-0">F-ZONE</h4>
            <small class="text-uppercase tracking-widest">Company</small>
        </div>
    </div>

    <hr class="sidebar-divider">

    <ul class="nav nav-pills nav-sidebar flex-column mt-2">
        <li class="nav-item">
            <a href="dashboard.php" class="nav-link <?= $active_page=='dashboard'?'active':'' ?>">
                <div class="icon-box"><i class="fas fa-tachometer-alt"></i></div>
                <span>Dashboard</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="barang.php" class="nav-link <?= $active_page=='barang'?'active':'' ?>">
                <div class="icon-box"><i class="fas fa-warehouse"></i></div>
                <span>Data Barang</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="transaksi_masuk.php" class="nav-link <?= $active_page=='transaksi_masuk'?'active':'' ?>">
                <div class="icon-box"><i class="fas fa-arrow-down"></i></div>
                <span>Transaksi Masuk</span>
            </a>
        </li>

        <li class="nav-item">
            <a href="transaksi_keluar.php" class="nav-link <?= $active_page=='transaksi_keluar'?'active':'' ?>">
                <div class="icon-box"><i class="fas fa-arrow-up"></i></div>
                <span>Transaksi Keluar</span>
            </a>
        </li>

        <?php if ($_SESSION['role'] === 'admin') : ?>
        <li class="nav-item">
            <a href="admin.php" class="nav-link <?= $active_page=='admin'?'active':'' ?>">
                <div class="icon-box"><i class="fas fa-user-shield"></i></div>
                <span>Admin & Petugas</span>
            </a>
        </li>
        <?php endif; ?>

        <li class="nav-item">
            <a href="akun.php" class="nav-link <?= $active_page=='akun'?'active':'' ?>">
                <div class="icon-box"><i class="fas fa-user-circle"></i></div>
                <span>Akun</span>
            </a>
        </li>

        <li class="nav-item mt-auto">
            <a href="logout.php" class="nav-link logout-link">
                <div class="icon-box"><i class="fas fa-sign-out-alt"></i></div>
                <span>Keluar</span>
            </a>
        </li>
    </ul>
</aside>

<style>
/* === CUSTOM SIDEBAR STYLING === */
.main-sidebar {
    width: 280px;
    min-height: 100vh;
    position: fixed;
    left: 0;
    top: 0;
    background: linear-gradient(180deg, #111827 0%, #1e293b 100%);
    box-shadow: 4px 0 15px rgba(0,0,0,0.3);
    border-right: 1px solid rgba(255,255,255,0.05);
    display: flex;
    flex-direction: column;
    z-index: 1000;
}

.sidebar-brand {
    padding: 30px 25px;
    display: flex;
    align-items: center;
    gap: 15px;
}

.brand-box {
    width: 45px;
    height: 45px;
    background: rgba(59, 130, 246, 0.1);
    border: 1px solid rgba(59, 130, 246, 0.3);
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #3b82f6;
    font-size: 1.2rem;
    box-shadow: 0 0 15px rgba(59, 130, 246, 0.2);
}

.brand-text h4 {
    color: #ffffff;
    font-size: 1.1rem;
    letter-spacing: 1px;
}

.brand-text small {
    color: #94a3b8;
    font-size: 0.65rem;
}

.sidebar-divider {
    border-top: 1px solid rgba(255,255,255,0.05);
    margin: 0 25px 15px;
}

/* Menu Items */
.nav-sidebar .nav-link {
    color: #94a3b8;
    margin: 4px 15px;
    padding: 12px 15px;
    border-radius: 12px;
    display: flex;
    align-items: center;
    gap: 15px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    border: 1px solid transparent;
}

.icon-box {
    width: 30px;
    display: flex;
    justify-content: center;
    font-size: 1.1rem;
}

.nav-sidebar .nav-link:hover {
    background: rgba(255, 255, 255, 0.05);
    color: #ffffff;
    transform: translateX(5px);
}

/* Active State - Mengikuti Tema Login */
.nav-sidebar .nav-link.active {
    background: rgba(59, 130, 246, 0.1) !important;
    border: 1px solid rgba(59, 130, 246, 0.4);
    color: #3b82f6 !important;
    box-shadow: 0 4px 15px rgba(59, 130, 246, 0.2);
}

.nav-sidebar .nav-link.active .icon-box {
    filter: drop-shadow(0 0 5px #3b82f6);
}

/* Logout khusus */
.logout-link {
    margin-top: 30px !important;
    margin-bottom: 20px !important;
    color: #f87171 !important;
}

.logout-link:hover {
    background: rgba(239, 68, 68, 0.1) !important;
    border: 1px solid rgba(239, 68, 68, 0.3) !important;
}

/* Animasi Floating Icon */
@keyframes floating {
    0% { transform: translateY(0px); }
    50% { transform: translateY(-5px); }
    100% { transform: translateY(0px); }
}

.floating-icon {
    animation: floating 3s ease-in-out infinite;
}

/* Desktop Adjustment */
@media (min-width: 769px) {
    .content-wrapper {
        margin-left: 280px;
        transition: 0.3s;
    }
}
</style>