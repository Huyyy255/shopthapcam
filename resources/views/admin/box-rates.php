<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}
$body = [
    'title'   => 'Cài Đặt Tỉ Lệ Rớt Đồ',
    'desc'    => 'CMSNT Panel',
    'keyword' => 'cmsnt, gacha, drop rate'
];
$body['header'] = '';
$body['footer'] = '';

require_once(__DIR__ . '/../../../models/is_admin.php');
require_once(__DIR__ . '/header.php');
require_once(__DIR__ . '/sidebar.php');
require_once(__DIR__ . '/nav.php');

$boxes = $CMSNT->get_list("SELECT * FROM `boxes` ORDER BY `id` ASC");
$selected_box_id = isset($_GET['box_id']) ? (int)$_GET['box_id'] : ($boxes[0]['id'] ?? 0);

// Handle save rates
if (isset($_POST['save_rates'])) {
    if ($CMSNT->site('status_demo') != 0) {
        die('<script>alert("Demo mode!");window.history.back();</script>');
    }
    $box_id_post = (int)$_POST['box_id'];
    $tiers = $_POST['tier'];
    $rates = $_POST['rate'];
    // Validate total = 100
    $total = array_sum($rates);
    if ($total != 100) {
        die('<script>alert("Tổng tỉ lệ phải bằng 100%! Hiện tại: ' . $total . '%");window.history.back();</script>');
    }
    // Delete old rates for this box
    $CMSNT->delete('box_rates', "`box_id` = '$box_id_post'");
    // Insert new
    foreach ($tiers as $i => $tier) {
        $tier  = check_string($tier);
        $rate  = (int)$rates[$i];
        if ($tier && $rate > 0) {
            $CMSNT->insert('box_rates', [
                'box_id'    => $box_id_post,
                'item_tier' => $tier,
                'win_rate'  => $rate
            ]);
        }
    }
    die('<script>alert("Lưu tỉ lệ thành công!");location.href="' . BASE_URL('admin/box-rates') . '?box_id=' . $box_id_post . '";</script>');
}

$selected_rates = $CMSNT->get_list("SELECT * FROM `box_rates` WHERE `box_id` = '$selected_box_id' ORDER BY `win_rate` DESC");
$default_tiers = ['VIP', 'Xịn', 'Chung'];
// Merge existing rates into form defaults
$rate_defaults = [];
foreach ($selected_rates as $r) { $rate_defaults[$r['item_tier']] = $r['win_rate']; }
?>
<div class="content-wrapper">
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6"><h1 class="m-0">⚙️ Cài Đặt Tỉ Lệ Rớt Đồ</h1></div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/'); ?>">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="<?= BASE_URL('admin/box-manager'); ?>">Box Manager</a></li>
                        <li class="breadcrumb-item active">Tỉ Lệ</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <div class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Select box -->
                <div class="col-lg-4">
                    <div class="card card-info card-outline">
                        <div class="card-header"><h3 class="card-title">Chọn Túi Mù</h3></div>
                        <div class="card-body">
                            <div class="list-group">
                                <?php foreach ($boxes as $box): ?>
                                <a href="?box_id=<?= $box['id']; ?>"
                                   class="list-group-item list-group-item-action <?= $box['id'] == $selected_box_id ? 'active' : ''; ?>">
                                    <i class="fas fa-gift mr-2"></i><?= htmlspecialchars($box['name']); ?>
                                    <small class="float-right"><?= number_format($box['price'], 0, '.', ','); ?>đ</small>
                                </a>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    <div class="callout callout-warning">
                        <h5>⚠️ Lưu ý quan trọng</h5>
                        <p>Tổng tỉ lệ của tất cả tier phải bằng đúng <strong>100%</strong>. Hệ thống sẽ không lưu nếu tổng khác 100.</p>
                    </div>
                </div>

                <!-- Rate editor -->
                <div class="col-lg-8">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-percentage mr-2"></i>
                                Tỉ Lệ Cho Túi: 
                                <b class="text-warning">
                                    <?= htmlspecialchars($CMSNT->get_row("SELECT name FROM boxes WHERE id='$selected_box_id'")['name'] ?? 'N/A'); ?>
                                </b>
                            </h3>
                        </div>
                        <form method="POST" onsubmit="return validateTotal()">
                            <input type="hidden" name="box_id" value="<?= $selected_box_id; ?>">
                            <input type="hidden" name="save_rates" value="1">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered" id="rateTable">
                                        <thead class="bg-dark text-white">
                                            <tr>
                                                <th>Tên Tier</th>
                                                <th>Tỉ Lệ (%)</th>
                                                <th>Thanh tiến độ</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php $rowIndex = 0; foreach ($default_tiers as $tier): 
                                                $r = $rate_defaults[$tier] ?? 0;
                                            ?>
                                            <tr>
                                                <td>
                                                    <input type="text" class="form-control" name="tier[]"
                                                        value="<?= htmlspecialchars($tier); ?>" required>
                                                </td>
                                                <td style="width:130px;">
                                                    <input type="number" class="form-control rate-input" name="rate[]"
                                                        value="<?= $r; ?>" min="0" max="100" step="1"
                                                        oninput="updateBars()" required>
                                                </td>
                                                <td>
                                                    <div class="progress" style="height:20px;margin:0;">
                                                        <div class="progress-bar bg-<?= $tier=='VIP'?'warning':($tier=='Xịn'?'purple':'info'); ?>"
                                                            id="bar-<?= $rowIndex; ?>"
                                                            style="width:<?= $r; ?>%;transition:width 0.3s;">
                                                            <?= $r; ?>%
                                                        </div>
                                                    </div>
                                                </td>
                                                <td style="width:50px;">
                                                    <button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                            <?php $rowIndex++; endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                                <button type="button" class="btn btn-sm btn-secondary" onclick="addRow()">
                                    <i class="fas fa-plus mr-1"></i>Thêm Tier
                                </button>
                                <div class="mt-3">
                                    <h5>Tổng tỉ lệ: <span id="totalRate" class="text-danger font-weight-bold">0</span>%
                                        <small class="text-muted">(phải bằng 100%)</small>
                                    </h5>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save mr-1"></i>Lưu Tỉ Lệ
                                </button>
                                <a href="<?= BASE_URL('admin/box-manager'); ?>" class="btn btn-secondary ml-2">Quay Lại</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php require_once(__DIR__ . '/footer.php'); ?>
<script>
function updateBars() {
    var inputs = document.querySelectorAll('.rate-input');
    var total = 0;
    inputs.forEach(function(inp, i) { total += parseInt(inp.value) || 0; });
    document.getElementById('totalRate').textContent = total;
    document.getElementById('totalRate').style.color = total == 100 ? '#28a745' : '#dc3545';
}
function removeRow(btn) {
    btn.closest('tr').remove();
    updateBars();
}
function addRow() {
    var tbody = document.querySelector('#rateTable tbody');
    var tr = document.createElement('tr');
    tr.innerHTML = '<td><input type="text" class="form-control" name="tier[]" placeholder="Tên tier" required></td>'
        + '<td><input type="number" class="form-control rate-input" name="rate[]" value="0" min="0" max="100" oninput="updateBars()" required></td>'
        + '<td><div class="progress" style="height:20px;margin:0;"><div class="progress-bar bg-secondary" style="width:0%">0%</div></div></td>'
        + '<td><button type="button" class="btn btn-xs btn-danger" onclick="removeRow(this)"><i class="fas fa-times"></i></button></td>';
    tbody.appendChild(tr);
}
function validateTotal() {
    var inputs = document.querySelectorAll('.rate-input');
    var total = 0;
    inputs.forEach(function(inp) { total += parseInt(inp.value) || 0; });
    if (total !== 100) { alert('Tổng tỉ lệ phải bằng 100%! Hiện tại: ' + total + '%'); return false; }
    return true;
}
updateBars();
</script>
