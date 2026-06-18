<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}
$body = [
    'title'   => 'Import Kho Hàng Túi Mù',
    'desc'    => 'CMSNT Panel',
    'keyword' => 'cmsnt, gacha, import'
];
$body['header'] = '';
$body['footer'] = '';

require_once(__DIR__ . '/../../../models/is_admin.php');
require_once(__DIR__ . '/header.php');
require_once(__DIR__ . '/sidebar.php');
require_once(__DIR__ . '/nav.php');

$boxes = $CMSNT->get_list("SELECT * FROM `boxes` WHERE `status` = 1 ORDER BY `id` ASC");
$default_box_id = isset($_GET['box_id']) ? (int)$_GET['box_id'] : ($boxes[0]['id'] ?? 0);

$import_result = null;
if (isset($_POST['do_import'])) {
    if ($CMSNT->site('status_demo') != 0) {
        die('<script>alert("Demo mode!");window.history.back();</script>');
    }
    $imp_box_id = (int)$_POST['imp_box_id'];
    $imp_tier   = check_string($_POST['imp_tier']);
    $raw_data   = trim($_POST['raw_items']);
    $lines      = array_filter(explode("\n", $raw_data));
    $count_ok   = 0; $count_skip = 0;
    foreach ($lines as $line) {
        $line = trim($line);
        if (empty($line)) { $count_skip++; continue; }
        $CMSNT->insert('box_items', [
            'box_id'       => $imp_box_id,
            'item_tier'    => $imp_tier,
            'account_info' => $line,
            'is_sold'      => 0,
            'created_at'   => gettime()
        ]);
        $count_ok++;
    }
    $import_result = ['ok' => $count_ok, 'skip' => $count_skip, 'box_id' => $imp_box_id];
}
// Delete all unsold items in a box
if (isset($_GET['clear_box'])) {
    $clear_id = (int)$_GET['clear_box'];
    $CMSNT->delete('box_items', "`box_id` = '$clear_id' AND `is_sold` = 0");
    die('<script>alert("Đã xóa hết hàng chưa bán!");location.href="' . BASE_URL('admin/box-import') . '";</script>');
}

$tiers = ['VIP', 'Xịn', 'Chung'];
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">📥 Import Kho Hàng</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/box-manager'); ?>">Box Manager</a></li>
                        <li class="breadcrumb-item active">Import Hàng</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <?php if ($import_result): ?>
            <div class="callout callout-success">
                <h5>✅ Import Thành Công!</h5>
                <p>Đã thêm <b><?= $import_result['ok']; ?></b> dòng vào kho | Bỏ qua <b><?= $import_result['skip']; ?></b> dòng trống</p>
            </div>
            <?php endif; ?>
            <div class="row">
                <!-- Import Form -->
                <div class="col-lg-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-upload mr-2"></i>Import Hàng Loạt</h3>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="do_import" value="1">
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Chọn Túi Mù <span class="text-danger">*</span></label>
                                    <select class="form-control" name="imp_box_id" required>
                                        <?php foreach ($boxes as $box): ?>
                                        <option value="<?= $box['id']; ?>" <?= $box['id'] == $default_box_id ? 'selected' : ''; ?>>
                                            [ID:<?= $box['id']; ?>] <?= htmlspecialchars($box['name']); ?>
                                        </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>Tier Hàng <span class="text-danger">*</span></label>
                                    <select class="form-control" name="imp_tier" required>
                                        <?php foreach ($tiers as $t): ?>
                                        <option value="<?= $t; ?>"><?= $t; ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label>
                                        Danh sách tài khoản/code
                                        <small class="text-muted">(mỗi dòng 1 item)</small>
                                    </label>
                                    <textarea class="form-control" name="raw_items" rows="15"
                                        placeholder="username|password&#10;username2|password2&#10;CODE_XYZ123&#10;..."
                                        style="font-family:monospace;font-size:0.875rem;" required></textarea>
                                    <small class="text-muted">
                                        Hỗ trợ: <code>username|password</code> hoặc code đơn giản
                                    </small>
                                </div>
                                <div id="preview-count" class="text-info" style="margin-bottom:8px;"></div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success">
                                    <i class="fas fa-upload mr-1"></i>Import Ngay
                                </button>
                                <button type="button" class="btn btn-secondary ml-2" onclick="previewCount()">
                                    <i class="fas fa-eye mr-1"></i>Đếm dòng
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Kho hiện tại -->
                <div class="col-lg-6">
                    <div class="card card-info card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-warehouse mr-2"></i>Tồn Kho Hiện Tại</h3>
                        </div>
                        <div class="card-body p-0">
                            <table class="table table-bordered table-sm mb-0 text-center">
                                <thead class="bg-dark text-white">
                                    <tr>
                                        <th>Túi Mù</th>
                                        <th>Tier</th>
                                        <th>Còn Lại</th>
                                        <th>Đã Bán</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php
                                $stock_data = $CMSNT->get_list("
                                    SELECT b.id as box_id, b.name as box_name, bi.item_tier,
                                        SUM(bi.is_sold=0) as remaining,
                                        SUM(bi.is_sold=1) as sold_cnt
                                    FROM boxes b
                                    LEFT JOIN box_items bi ON bi.box_id = b.id
                                    GROUP BY b.id, bi.item_tier
                                    ORDER BY b.id, bi.item_tier
                                ");
                                if (empty($stock_data)): ?>
                                <tr><td colspan="5" class="text-center text-muted py-3">Chưa có hàng nào</td></tr>
                                <?php else:
                                $last_box = null;
                                foreach ($stock_data as $row):
                                    if (!$row['item_tier']) continue;
                                ?>
                                <tr>
                                    <td class="text-left">
                                        <?php if ($last_box !== $row['box_id']): ?>
                                        <b><?= htmlspecialchars($row['box_name']); ?></b>
                                        <?php $last_box = $row['box_id']; endif; ?>
                                    </td>
                                    <td><span class="badge badge-dark"><?= htmlspecialchars($row['item_tier']); ?></span></td>
                                    <td><span class="badge badge-<?= $row['remaining'] > 0 ? 'success' : 'danger'; ?>"><?= $row['remaining']; ?></span></td>
                                    <td><span class="badge badge-secondary"><?= $row['sold_cnt']; ?></span></td>
                                    <td>
                                        <a href="<?= BASE_URL('admin/box-import'); ?>?box_id=<?= $row['box_id']; ?>" class="btn btn-xs btn-primary">
                                            <i class="fas fa-plus"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; endif; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer text-right">
                            <?php foreach ($boxes as $box): ?>
                            <a href="?clear_box=<?= $box['id']; ?>"
                               onclick="return confirm('Xóa hết hàng chưa bán của <?= htmlspecialchars($box['name']); ?>?')"
                               class="btn btn-xs btn-danger mr-1">
                                <i class="fas fa-trash"></i> Clear <?= htmlspecialchars(substr($box['name'],0,12)); ?>
                            </a>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Format guide -->
                    <div class="callout callout-info">
                        <h5><i class="fas fa-info-circle mr-1"></i> Hướng Dẫn Import</h5>
                        <p>Mỗi dòng là 1 item. Các format được hỗ trợ:</p>
                        <ul>
                            <li><code>username|password</code> — Tài khoản game</li>
                            <li><code>CODE_ABCDEF123</code> — Code đổi quà</li>
                            <li>Dòng trống sẽ tự động bỏ qua</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(__DIR__ . '/footer.php'); ?>
<script>
function previewCount() {
    var ta = document.querySelector('textarea[name="raw_items"]');
    var lines = ta.value.split('\n').filter(function(l){ return l.trim().length > 0; });
    document.getElementById('preview-count').textContent = '📦 Sẽ import ' + lines.length + ' item(s)';
}
document.querySelector('textarea[name="raw_items"]').addEventListener('input', previewCount);
</script>
