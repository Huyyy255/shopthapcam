<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}
$body = [
    'title'   => 'Quản Lý Túi Mù Gacha',
    'desc'    => 'CMSNT Panel',
    'keyword' => 'cmsnt, gacha'
];
$body['header'] = '
    <link rel="stylesheet" href="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-bs4/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" href="' . BASE_URL('public/AdminLTE3/') . 'plugins/datatables-responsive/css/responsive.bootstrap4.min.css">
<style>
.tier-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:30px; font-size:0.75rem; font-weight:700; border:1px solid; }
.tier-VIP   { color:#FFD700; border-color:rgba(255,215,0,0.5); background:rgba(255,215,0,0.1); }
.tier-Xịn   { color:#9C27B0; border-color:rgba(156,39,176,0.5); background:rgba(156,39,176,0.1); }
.tier-Chung { color:#1565C0; border-color:rgba(21,101,192,0.5); background:rgba(21,101,192,0.1); }
</style>
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

// Handle POST: Add new box
if (isset($_POST['action_box'])) {
    if ($CMSNT->site('status_demo') != 0) {
        die('<script>alert("Demo mode - không thể thêm/sửa.");window.history.back();</script>');
    }
    $name  = check_string($_POST['box_name']);
    $desc  = check_string($_POST['box_desc']);
    $price = (float)$_POST['box_price'];
    $img   = check_string($_POST['box_image']);
    $status = (int)$_POST['box_status'];

    if ($_POST['action_box'] == 'add') {
        $CMSNT->insert('boxes', [
            'name' => $name, 'description' => $desc,
            'price' => $price, 'image' => $img,
            'status' => $status, 'created_at' => gettime()
        ]);
        die('<script>alert("Thêm túi mù thành công!");location.href="' . BASE_URL('admin/box-manager') . '";</script>');
    } elseif ($_POST['action_box'] == 'edit') {
        $edit_id = (int)$_POST['edit_id'];
        $CMSNT->update('boxes', [
            'name' => $name, 'description' => $desc,
            'price' => $price, 'image' => $img, 'status' => $status
        ], "`id` = '$edit_id'");
        die('<script>alert("Cập nhật thành công!");location.href="' . BASE_URL('admin/box-manager') . '";</script>');
    }
}
// Handle delete via GET
if (isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    $CMSNT->delete('boxes', "`id` = '$del_id'");
    $CMSNT->delete('box_rates', "`box_id` = '$del_id'");
    die('<script>alert("Đã xóa túi mù!");location.href="' . BASE_URL('admin/box-manager') . '";</script>');
}
// Edit mode
$edit_box = null;
if (isset($_GET['edit_id'])) {
    $edit_box = $CMSNT->get_row("SELECT * FROM `boxes` WHERE `id` = '" . (int)$_GET['edit_id'] . "'");
}

$boxes = $CMSNT->get_list("SELECT b.*, 
    (SELECT COUNT(*) FROM box_items WHERE box_id=b.id AND is_sold=0) as stock,
    (SELECT COUNT(*) FROM box_items WHERE box_id=b.id AND is_sold=1) as sold
    FROM `boxes` b ORDER BY b.id DESC");
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">🎁 Quản Lý Túi Mù Gacha</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item active">Box Manager</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Form thêm/sửa -->
                <section class="col-lg-4">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-plus-circle mr-2"></i>
                                <?= $edit_box ? 'Sửa Túi Mù' : 'Thêm Túi Mù Mới'; ?>
                            </h3>
                        </div>
                        <form method="POST">
                            <input type="hidden" name="action_box" value="<?= $edit_box ? 'edit' : 'add'; ?>">
                            <?php if ($edit_box): ?>
                            <input type="hidden" name="edit_id" value="<?= $edit_box['id']; ?>">
                            <?php endif; ?>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Tên túi mù <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="box_name"
                                        placeholder="VD: Túi VIP Xịn" required
                                        value="<?= $edit_box ? htmlspecialchars($edit_box['name']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Mô tả</label>
                                    <textarea class="form-control" name="box_desc" rows="3"
                                        placeholder="Mô tả túi mù..."><?= $edit_box ? htmlspecialchars($edit_box['description']) : ''; ?></textarea>
                                </div>
                                <div class="form-group">
                                    <label>Giá mở (VNĐ) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control" name="box_price"
                                        placeholder="VD: 5000" min="0" step="500" required
                                        value="<?= $edit_box ? $edit_box['price'] : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>URL Hình ảnh</label>
                                    <input type="text" class="form-control" name="box_image"
                                        placeholder="assets/img/box.png hoặc https://..."
                                        value="<?= $edit_box ? htmlspecialchars($edit_box['image']) : ''; ?>">
                                </div>
                                <div class="form-group">
                                    <label>Trạng thái</label>
                                    <select class="form-control" name="box_status">
                                        <option value="1" <?= ($edit_box && $edit_box['status']==1) ? 'selected' : ''; ?>>✅ Hoạt động</option>
                                        <option value="0" <?= ($edit_box && $edit_box['status']==0) ? 'selected' : ''; ?>>❌ Tắt</option>
                                    </select>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>
                                    <?= $edit_box ? 'Cập Nhật' : 'Thêm Túi'; ?>
                                </button>
                                <?php if ($edit_box): ?>
                                <a href="<?= BASE_URL('admin/box-manager'); ?>" class="btn btn-secondary ml-2">Hủy</a>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                </section>

                <!-- Danh sách túi -->
                <section class="col-lg-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-list mr-2"></i>Danh Sách Túi Mù</h3>
                            <div class="card-tools">
                                <a href="<?= BASE_URL('admin/box-import'); ?>" class="btn btn-sm btn-success">
                                    <i class="fas fa-upload mr-1"></i>Import Hàng
                                </a>
                                <a href="<?= BASE_URL('admin/box-rates'); ?>" class="btn btn-sm btn-info ml-1">
                                    <i class="fas fa-percentage mr-1"></i>Cài Tỉ Lệ
                                </a>
                                <a href="<?= BASE_URL('admin/box-history'); ?>" class="btn btn-sm btn-warning ml-1">
                                    <i class="fas fa-history mr-1"></i>Lịch Sử
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table id="dtBoxes" class="table table-hover table-striped table-bordered mb-0 text-center">
                                    <thead class="bg-dark text-white">
                                        <tr>
                                            <th>ID</th>
                                            <th>Tên Túi</th>
                                            <th>Giá Mở</th>
                                            <th>Tồn Kho</th>
                                            <th>Đã Bán</th>
                                            <th>Trạng Thái</th>
                                            <th>Thao Tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($boxes as $box): ?>
                                        <tr>
                                            <td><?= $box['id']; ?></td>
                                            <td class="text-left">
                                                <b><?= htmlspecialchars($box['name']); ?></b>
                                                <br><small class="text-muted"><?= htmlspecialchars(substr($box['description'], 0, 50)); ?>...</small>
                                            </td>
                                            <td><b class="text-warning"><?= number_format($box['price'], 0, '.', ','); ?> đ</b></td>
                                            <td>
                                                <span class="badge badge-<?= $box['stock'] > 0 ? 'success' : 'danger'; ?>">
                                                    <?= $box['stock']; ?>
                                                </span>
                                            </td>
                                            <td><span class="badge badge-info"><?= $box['sold']; ?></span></td>
                                            <td>
                                                <?php if ($box['status'] == 1): ?>
                                                <span class="badge badge-success">Hoạt động</span>
                                                <?php else: ?>
                                                <span class="badge badge-danger">Đã tắt</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="?edit_id=<?= $box['id']; ?>" class="btn btn-xs btn-warning">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <a href="<?= BASE_URL('admin/box-import'); ?>?box_id=<?= $box['id']; ?>" class="btn btn-xs btn-success" title="Import acc">
                                                    <i class="fas fa-upload"></i>
                                                </a>
                                                <a href="<?= BASE_URL('admin/box-rates'); ?>?box_id=<?= $box['id']; ?>" class="btn btn-xs btn-info" title="Cài tỉ lệ">
                                                    <i class="fas fa-percentage"></i>
                                                </a>
                                                <a href="?delete_id=<?= $box['id']; ?>" class="btn btn-xs btn-danger"
                                                   onclick="return confirm('Xóa túi mù này? Dữ liệu rates cũng bị xóa!')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </td>
                                        </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</div>
<?php require_once(__DIR__ . '/footer.php'); ?>
<script>$(function() { $('#dtBoxes').DataTable({order:[[0,'desc']]}); });</script>
