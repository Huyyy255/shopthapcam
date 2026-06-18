<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}
$body = [
    'title'   => 'Lịch Sử Mở Túi Mù',
    'desc'    => 'CMSNT Panel',
    'keyword' => 'cmsnt, gacha, history'
];
$body['header'] = '
    <link rel="stylesheet" href="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
';
$body['footer'] = '
    <script src="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables/jquery.dataTables.min.js"></script>
    <script src="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-bs4/js/dataTables.bootstrap4.min.js"></script>
    <script src="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-responsive/js/dataTables.responsive.min.js"></script>
    <script src="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-responsive/js/responsive.bootstrap4.min.js"></script>
';

require_once(__DIR__ . '/../../../models/is_admin.php');
require_once(__DIR__ . '/header.php');
require_once(__DIR__ . '/sidebar.php');
require_once(__DIR__ . '/nav.php');

// Stats
$total_opens  = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `user_inventory`")['cnt'];
$total_revenue_raw = $CMSNT->get_row("
    SELECT SUM(b.price) as total 
    FROM user_inventory ui 
    JOIN boxes b ON b.id = ui.box_id
");
$total_revenue = $total_revenue_raw['total'] ?? 0;
$vip_count = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM user_inventory WHERE item_tier='VIP'")['cnt'];

// History list (last 500)
$history = $CMSNT->get_list("
    SELECT ui.id, ui.item_tier, ui.item_won_info, ui.created_at,
           u.username, u.email,
           b.name as box_name, b.price as box_price
    FROM user_inventory ui
    LEFT JOIN users u ON u.id = ui.user_id
    LEFT JOIN boxes b ON b.id  = ui.box_id
    ORDER BY ui.id DESC
    LIMIT 500
");
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">📋 Lịch Sử Mở Túi</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/box-manager'); ?>">Box Manager</a></li>
                        <li class="breadcrumb-item active">Lịch Sử</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <!-- Stats cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner"><h3><?= number_format($total_opens); ?></h3><p>Tổng Lượt Mở</p></div>
                        <div class="icon"><i class="fas fa-gift"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3><?= number_format($total_revenue, 0, '.', ','); ?>đ</h3>
                            <p>Tổng Doanh Thu</p>
                        </div>
                        <div class="icon"><i class="fas fa-coins"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner"><h3><?= number_format($vip_count); ?></h3><p>Lượt Trúng VIP</p></div>
                        <div class="icon"><i class="fas fa-crown"></i></div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3><?= $total_opens > 0 ? round($vip_count / $total_opens * 100, 1) : 0; ?>%</h3>
                            <p>Tỷ Lệ Trúng VIP Thực</p>
                        </div>
                        <div class="icon"><i class="fas fa-percentage"></i></div>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-history mr-2"></i>Chi Tiết Lịch Sử (500 gần nhất)</h3>
                    <div class="card-tools">
                        <a href="<?= BASE_URL('admin/box-manager'); ?>" class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i>Quay Lại
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="dtHistory" class="table table-hover table-striped table-bordered mb-0 text-center" style="width:100%">
                            <thead class="bg-dark text-white">
                                <tr>
                                    <th>#</th>
                                    <th>Người Dùng</th>
                                    <th>Túi Đã Mở</th>
                                    <th>Tier Trúng</th>
                                    <th>Thông Tin Nhận Được</th>
                                    <th>Doanh Thu</th>
                                    <th>Thời Gian</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i = 1; foreach ($history as $row): 
                                    $tier = htmlspecialchars($row['item_tier'] ?? 'Chung');
                                    $tier_colors = ['VIP'=>'warning','Xịn'=>'purple','Chung'=>'info'];
                                    $badge_class = 'badge-' . ($tier_colors[$tier] ?? 'secondary');
                                ?>
                                <tr>
                                    <td><?= $i++; ?></td>
                                    <td class="text-left">
                                        <b><?= htmlspecialchars($row['username'] ?? 'N/A'); ?></b>
                                        <br><small class="text-muted"><?= htmlspecialchars($row['email'] ?? ''); ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($row['box_name'] ?? 'N/A'); ?></td>
                                    <td>
                                        <span class="badge <?= $badge_class; ?>">
                                            <?= $tier == 'VIP' ? '👑' : ($tier == 'Xịn' ? '🌟' : '🎁'); ?>
                                            <?= $tier; ?>
                                        </span>
                                    </td>
                                    <td class="text-left" style="font-family:monospace;font-size:0.8rem;max-width:250px;word-break:break-all;">
                                        <span title="<?= htmlspecialchars($row['item_won_info']); ?>">
                                            <?= htmlspecialchars(substr($row['item_won_info'], 0, 40)); ?>
                                            <?= strlen($row['item_won_info']) > 40 ? '...' : ''; ?>
                                        </span>
                                    </td>
                                    <td><b class="text-success"><?= number_format($row['box_price'] ?? 0, 0, '.', ','); ?>đ</b></td>
                                    <td style="white-space:nowrap;"><?= $row['created_at']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(__DIR__ . '/footer.php'); ?>
<script>
$(function() {
    $('#dtHistory').DataTable({
        order: [[0, 'desc']],
        pageLength: 25,
        responsive: true
    });
});
</script>
