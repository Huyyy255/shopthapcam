<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}

require_once(__DIR__ . '/../../../models/is_user.php');

$body = [
    'title'   => '🎒 Kho Đồ Của Tôi | ' . $CMSNT->site('title'),
    'desc'    => 'Xem toàn bộ tài khoản/code đã nhận từ túi mù Liên Quân.',
    'keyword' => 'kho đồ, túi mù, liên quân, lịch sử'
];
$body['header'] = '
<style>
@import url("https://fonts.googleapis.com/css2?family=Rajdhani:wght@600;700&family=Be+Vietnam+Pro:wght@400;500;600&display=swap");
:root { --gold:#FFD700; --purple:#7B2FFF; --cyan:#00E5FF; }
body { font-family:"Be Vietnam Pro",sans-serif; }

.inv-hero {
    background: linear-gradient(135deg,#0a0015,#1a0035,#0d0020);
    padding: 40px 0 30px; text-align:center;
    border-bottom: 1px solid rgba(123,47,255,0.25);
    position:relative; overflow:hidden;
}
.inv-hero::before {
    content:""; position:absolute; inset:0;
    background: radial-gradient(ellipse at 50% 0%, rgba(123,47,255,0.2) 0%, transparent 65%);
}
.inv-hero-title {
    font-family:"Rajdhani",sans-serif; font-size:2.2rem; font-weight:700;
    background:linear-gradient(90deg,#FFD700,#00E5FF);
    -webkit-background-clip:text; -webkit-text-fill-color:transparent;
    margin-bottom:6px; position:relative;
}
.inv-hero-sub { color:rgba(255,255,255,0.55); font-size:0.95rem; position:relative; }

.stats-strip {
    display:flex; gap:16px; flex-wrap:wrap; justify-content:center;
    margin: 28px 0;
}
.stat-card {
    background:rgba(255,255,255,0.04);
    border:1px solid rgba(255,255,255,0.1);
    border-radius:14px; padding:16px 24px;
    text-align:center; min-width:140px;
}
.stat-val {
    font-family:"Rajdhani",sans-serif; font-size:2rem; font-weight:700;
    color: var(--gold); line-height:1;
}
.stat-label { font-size:0.8rem; color:rgba(255,255,255,0.5); margin-top:4px; }

/* Filter tabs */
.filter-tabs {
    display:flex; gap:10px; flex-wrap:wrap; margin-bottom:24px;
}
.filter-tab {
    padding:7px 20px; border-radius:30px; font-size:0.875rem; font-weight:600;
    background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.12);
    color:rgba(255,255,255,0.6); cursor:pointer; transition:all 0.2s;
}
.filter-tab.active, .filter-tab:hover {
    background:rgba(123,47,255,0.25); border-color:rgba(123,47,255,0.5);
    color:#fff;
}
.filter-tab[data-tier="VIP"].active   { background:rgba(255,215,0,0.15); border-color:rgba(255,215,0,0.4); color:var(--gold); }
.filter-tab[data-tier="Xịn"].active  { background:rgba(206,147,216,0.15); border-color:rgba(206,147,216,0.4); color:#CE93D8; }
.filter-tab[data-tier="Chung"].active { background:rgba(144,202,249,0.15); border-color:rgba(144,202,249,0.4); color:#90CAF9; }

/* Inventory table / cards */
.inv-table-wrap {
    background:rgba(255,255,255,0.03);
    border:1px solid rgba(255,255,255,0.08);
    border-radius:16px; overflow:hidden;
}
.inv-table { width:100%; border-collapse:collapse; }
.inv-table thead tr {
    background:rgba(123,47,255,0.12);
    border-bottom: 1px solid rgba(123,47,255,0.25);
}
.inv-table thead th {
    padding:14px 16px; font-size:0.8rem; font-weight:700;
    text-transform:uppercase; letter-spacing:0.5px;
    color:rgba(255,255,255,0.6);
}
.inv-table tbody tr {
    border-bottom:1px solid rgba(255,255,255,0.05);
    transition: background 0.15s;
}
.inv-table tbody tr:hover { background:rgba(255,255,255,0.03); }
.inv-table tbody tr:last-child { border-bottom:none; }
.inv-table td { padding:12px 16px; font-size:0.88rem; vertical-align:middle; }

.tier-badge {
    display:inline-flex; align-items:center; gap:5px;
    padding:3px 12px; border-radius:30px; font-size:0.75rem; font-weight:700;
    border:1px solid;
}
.tier-VIP   { color:#FFD700; border-color:rgba(255,215,0,0.4); background:rgba(255,215,0,0.1); }
.tier-Xịn   { color:#CE93D8; border-color:rgba(206,147,216,0.4); background:rgba(206,147,216,0.1); }
.tier-Chung { color:#90CAF9; border-color:rgba(144,202,249,0.4); background:rgba(144,202,249,0.1); }

.acc-info-cell {
    font-family: monospace; font-size:0.85rem;
    color:rgba(255,255,255,0.9);
    max-width:280px; word-break:break-all;
}
.btn-copy-sm {
    padding:4px 12px; border-radius:30px; font-size:0.75rem; font-weight:600;
    background:rgba(123,47,255,0.25); border:1px solid rgba(123,47,255,0.4);
    color:#fff; cursor:pointer; transition:all 0.2s; white-space:nowrap;
}
.btn-copy-sm:hover { background:rgba(123,47,255,0.5); }
.btn-copy-sm.copied { background:rgba(105,219,124,0.25); border-color:rgba(105,219,124,0.4); color:#69DB7C; }

.empty-inv {
    text-align:center; padding:70px 20px;
    color:rgba(255,255,255,0.35);
}
.empty-inv-icon { font-size:4rem; margin-bottom:16px; }
</style>
';
$body['footer'] = '';

require_once(__DIR__ . '/header.php');

// Stats
$total_opened = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `user_inventory` WHERE `user_id` = '" . $getUser['id'] . "'")['cnt'];
$count_vip    = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `user_inventory` WHERE `user_id` = '" . $getUser['id'] . "' AND `item_tier` = 'VIP'")['cnt'];
$count_xin    = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `user_inventory` WHERE `user_id` = '" . $getUser['id'] . "' AND `item_tier` = 'Xịn'")['cnt'];
$count_chung  = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `user_inventory` WHERE `user_id` = '" . $getUser['id'] . "' AND `item_tier` = 'Chung'")['cnt'];

// Active filter
$filter_tier = isset($_GET['tier']) ? check_string($_GET['tier']) : '';
$where_tier = $filter_tier ? " AND `item_tier` = '$filter_tier' " : '';

// Pagination
$page = max(1, (int)(isset($_GET['page']) ? $_GET['page'] : 1));
$per_page = 15;
$offset = ($page - 1) * $per_page;
$total_filtered = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `user_inventory` WHERE `user_id` = '" . $getUser['id'] . "' $where_tier")['cnt'];
$total_pages = ceil($total_filtered / $per_page);

$items = $CMSNT->get_list(
    "SELECT ui.*, b.name as box_name FROM `user_inventory` ui 
     LEFT JOIN `boxes` b ON b.id = ui.box_id
     WHERE ui.`user_id` = '" . $getUser['id'] . "' $where_tier
     ORDER BY ui.`id` DESC LIMIT $per_page OFFSET $offset"
);
?>
<?php require_once(__DIR__ . '/sidebar.php'); ?>

<div class="content-page">
    <!-- Hero -->
    <div class="inv-hero">
        <div class="container">
            <div class="inv-hero-title">🎒 Kho Đồ Của Tôi</div>
            <div class="inv-hero-sub">Toàn bộ tài khoản & phần thưởng bạn đã mở từ túi mù</div>
        </div>
    </div>

    <div class="container" style="padding: 32px 0 60px;">
        <!-- Stats strip -->
        <div class="stats-strip">
            <div class="stat-card">
                <div class="stat-val"><?= $total_opened; ?></div>
                <div class="stat-label">Tổng đã mở</div>
            </div>
            <div class="stat-card" style="border-color:rgba(255,215,0,0.25);">
                <div class="stat-val" style="color:var(--gold)"><?= $count_vip; ?></div>
                <div class="stat-label">👑 Trúng VIP</div>
            </div>
            <div class="stat-card" style="border-color:rgba(206,147,216,0.25);">
                <div class="stat-val" style="color:#CE93D8"><?= $count_xin; ?></div>
                <div class="stat-label">🌟 Trúng Xịn</div>
            </div>
            <div class="stat-card" style="border-color:rgba(144,202,249,0.25);">
                <div class="stat-val" style="color:#90CAF9"><?= $count_chung; ?></div>
                <div class="stat-label">🎁 Loại Chung</div>
            </div>
        </div>

        <!-- Filter tabs -->
        <div class="filter-tabs">
            <a href="?<?= http_build_query(['page'=>1]); ?>" 
               class="filter-tab <?= !$filter_tier ? 'active' : ''; ?>" data-tier="">
               Tất Cả (<?= $total_opened; ?>)
            </a>
            <a href="?<?= http_build_query(['tier'=>'VIP','page'=>1]); ?>" 
               class="filter-tab <?= $filter_tier=='VIP' ? 'active' : ''; ?>" data-tier="VIP">
               👑 VIP (<?= $count_vip; ?>)
            </a>
            <a href="?<?= http_build_query(['tier'=>'Xịn','page'=>1]); ?>" 
               class="filter-tab <?= $filter_tier=='Xịn' ? 'active' : ''; ?>" data-tier="Xịn">
               🌟 Xịn (<?= $count_xin; ?>)
            </a>
            <a href="?<?= http_build_query(['tier'=>'Chung','page'=>1]); ?>" 
               class="filter-tab <?= $filter_tier=='Chung' ? 'active' : ''; ?>" data-tier="Chung">
               🎁 Chung (<?= $count_chung; ?>)
            </a>
        </div>

        <!-- Inventory table -->
        <?php if (empty($items)): ?>
        <div class="empty-inv">
            <div class="empty-inv-icon">📦</div>
            <p>Bạn chưa mở túi nào.<br>
               <a href="<?= base_url('client/blind-boxes'); ?>" style="color:var(--cyan)">Đến cửa hàng mở túi ngay!</a>
            </p>
        </div>
        <?php else: ?>
        <div class="inv-table-wrap">
            <table class="inv-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Túi Mù</th>
                        <th>Hạng</th>
                        <th>Thông Tin Tài Khoản</th>
                        <th>Thời Gian</th>
                        <th>Thao Tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $i = ($page - 1) * $per_page + 1; foreach ($items as $item): ?>
                    <tr>
                        <td style="color:rgba(255,255,255,0.4)"><?= $i++; ?></td>
                        <td>
                            <span style="color:rgba(255,255,255,0.8); font-weight:600;">
                                <?= htmlspecialchars($item['box_name'] ?? 'N/A'); ?>
                            </span>
                        </td>
                        <td>
                            <?php 
                                $tier = htmlspecialchars($item['item_tier'] ?? 'Chung');
                                $tier_icons = ['VIP'=>'👑','Xịn'=>'🌟','Chung'=>'🎁'];
                                $icon = $tier_icons[$tier] ?? '🎁';
                            ?>
                            <span class="tier-badge tier-<?= $tier; ?>"><?= $icon; ?> <?= $tier; ?></span>
                        </td>
                        <td class="acc-info-cell" id="acc-<?= $item['id']; ?>">
                            <?= htmlspecialchars($item['item_won_info']); ?>
                        </td>
                        <td style="color:rgba(255,255,255,0.45); font-size:0.8rem; white-space:nowrap;">
                            <?= $item['created_at']; ?>
                        </td>
                        <td>
                            <button class="btn-copy-sm" id="copy-btn-<?= $item['id']; ?>"
                                onclick="copyAcc(<?= $item['id']; ?>)">
                                <i class="fas fa-copy"></i> Copy
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div style="display:flex; justify-content:center; gap:8px; margin-top:24px; flex-wrap:wrap;">
            <?php for ($p = 1; $p <= $total_pages; $p++): ?>
            <a href="?<?= http_build_query(array_merge($_GET, ['page'=>$p])); ?>"
               style="display:inline-flex;align-items:center;justify-content:center;
                      width:36px;height:36px;border-radius:8px;font-weight:600;font-size:0.875rem;
                      text-decoration:none;transition:all 0.2s;
                      <?= $p == $page ? 'background:var(--purple);color:#fff;' : 'background:rgba(255,255,255,0.05);color:rgba(255,255,255,0.5);border:1px solid rgba(255,255,255,0.1);'; ?>">
                <?= $p; ?>
            </a>
            <?php endfor; ?>
        </div>
        <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<script>
function copyAcc(id) {
    var el  = document.getElementById('acc-' + id);
    var btn = document.getElementById('copy-btn-' + id);
    if (!el || !btn) return;
    navigator.clipboard.writeText(el.textContent.trim()).then(function() {
        btn.classList.add('copied');
        btn.innerHTML = '<i class="fas fa-check"></i> Đã Copy';
        setTimeout(function() {
            btn.classList.remove('copied');
            btn.innerHTML = '<i class="fas fa-copy"></i> Copy';
        }, 2500);
    });
}
</script>

<?php require_once(__DIR__ . '/footer.php'); ?>
