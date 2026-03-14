<?php
/**
 * Alert Helper Functions
 * Untuk menampilkan pesan alert yang konsisten di semua halaman
 */

/**
 * Set alert message untuk ditampilkan setelah redirect
 * @param string $type 'success', 'error', 'warning', 'info'
 * @param string $message Pesan yang ingin ditampilkan
 */
function setAlert($type, $message) {
    $_SESSION['alert'] = [
        'type' => $type,
        'message' => $message
    ];
}

/**
 * Tampilkan alert dan hapus dari session
 */
function displayAlert() {
    if (!isset($_SESSION['alert'])) {
        return '';
    }

    $alert = $_SESSION['alert'];
    $type = $alert['type'];
    $message = $alert['message'];
    
    // Map type ke Bootstrap class dan icon
    $alertConfig = [
        'success' => [
            'class' => 'alert-success',
            'icon' => 'fas fa-check-circle',
            'bgLight' => '#d4edda',
            'border' => '#c3e6cb',
            'color' => '#155724'
        ],
        'error' => [
            'class' => 'alert-danger',
            'icon' => 'fas fa-exclamation-circle',
            'bgLight' => '#f8d7da',
            'border' => '#f5c6cb',
            'color' => '#721c24'
        ],
        'warning' => [
            'class' => 'alert-warning',
            'icon' => 'fas fa-exclamation-triangle',
            'bgLight' => '#fff3cd',
            'border' => '#ffeaa7',
            'color' => '#856404'
        ],
        'info' => [
            'class' => 'alert-info',
            'icon' => 'fas fa-info-circle',
            'bgLight' => '#d1ecf1',
            'border' => '#bee5eb',
            'color' => '#0c5460'
        ]
    ];

    $config = $alertConfig[$type] ?? $alertConfig['info'];

    $html = '
    <div class="alert ' . $config['class'] . ' alert-dismissible fade show d-flex align-items-center shadow-sm rounded-3 border-0 mb-4" role="alert" style="background-color: ' . $config['bgLight'] . '; border-left: 4px solid ' . $config['color'] . ';">
        <i class="' . $config['icon'] . ' me-3" style="font-size: 20px; color: ' . $config['color'] . ';"></i>
        <div style="flex: 1;">
            <strong style="color: ' . $config['color'] . '; font-weight: 600;">' . ucfirst($type) . '!</strong>
            <p class="mb-0" style="color: ' . $config['color'] . '; margin-top: 4px;">' . $message . '</p>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    ';

    unset($_SESSION['alert']);
    return $html;
}

/**
 * Cek apakah ada alert pending
 */
function hasAlert() {
    return isset($_SESSION['alert']);
}
?>
