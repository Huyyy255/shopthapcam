<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}

$body = [
    'title'   => '🎁 Cửa Hàng Túi Mù Liên Quân | ' . $CMSNT->site('title'),
    'desc'    => 'Mở túi mù Liên Quân Mobile - nhận ngay tài khoản xịn với giá cực tốt!',
    'keyword' => 'túi mù, liên quân, gacha, mở túi, acc liên quân'
];
$body['header'] = '
<style>
/* ============ GACHA BLIND BOX STORE ============ */
@import url("https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap");

:root {
    --gold: #FFD700;
    --gold-light: #FFE94D;
    --purple: #7B2FFF;
    --purple-dark: #4A0099;
    --cyan: #00E5FF;
    --rare: #9C27B0;
    --epic: #FF6B35;
    --glass-bg: rgba(255,255,255,0.04);
    --glass-border: rgba(255,255,255,0.12);
}

body { font-family: "Be Vietnam Pro", sans-serif; }

.gacha-hero {
    background: linear-gradient(135deg, #0a0015 0%, #1a0035 40%, #0d0020 100%);
    padding: 48px 0 36px;
    text-align: center;
    position: relative;
    overflow: hidden;
    border-bottom: 1px solid rgba(123,47,255,0.3);
}
.gacha-hero::before {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(ellipse at 50% 0%, rgba(123,47,255,0.25) 0%, transparent 65%);
    pointer-events: none;
}
.gacha-hero-title {
    font-family: "Rajdhani", sans-serif;
    font-size: 2.8rem;
    font-weight: 700;
    background: linear-gradient(90deg, #FFD700, #FF6B35, #FFD700);
    background-size: 200% auto;
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    animation: shimmer 3s linear infinite;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
@keyframes shimmer { to { background-position: 200% center; } }
.gacha-hero-sub {
    color: rgba(255,255,255,0.65);
    font-size: 1rem;
    margin-bottom: 24px;
}
.balance-badge {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    background: rgba(255,215,0,0.1);
    border: 1px solid rgba(255,215,0,0.35);
    border-radius: 50px;
    padding: 8px 22px;
    color: var(--gold);
    font-weight: 700;
    font-size: 1rem;
}

/* Box Cards */
.boxes-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 28px;
    padding: 40px 0;
}
.box-card {
    background: linear-gradient(145deg, rgba(123,47,255,0.08), rgba(10,0,21,0.9));
    border: 1px solid var(--glass-border);
    border-radius: 20px;
    overflow: hidden;
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    position: relative;
}
.box-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 20px 60px rgba(123,47,255,0.35);
    border-color: rgba(123,47,255,0.5);
}
.box-card-badge {
    position: absolute;
    top: 14px;
    right: 14px;
    z-index: 10;
    padding: 4px 12px;
    border-radius: 30px;
    font-size: 0.72rem;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
}
.badge-vip { background: linear-gradient(90deg,#FFD700,#FF8C00); color: #000; }
.badge-hot { background: linear-gradient(90deg,#FF416C,#FF4B2B); color: #fff; }
.badge-new { background: linear-gradient(90deg,#00c6ff,#0072ff); color: #fff; }
.badge-sold { background: rgba(100,100,100,0.7); color: #ccc; }

.box-img-wrap {
    height: 200px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: linear-gradient(135deg, rgba(123,47,255,0.12), rgba(0,229,255,0.06));
    position: relative;
    overflow: hidden;
}
.box-img-wrap::after {
    content: "";
    position: absolute;
    inset: 0;
    background: radial-gradient(circle at 50% 100%, rgba(123,47,255,0.2), transparent 60%);
}
.box-img {
    width: 130px;
    height: 130px;
    object-fit: contain;
    filter: drop-shadow(0 0 25px rgba(123,47,255,0.7));
    animation: float 3s ease-in-out infinite;
    position: relative;
    z-index: 1;
}
.box-img-placeholder {
    font-size: 5rem;
    animation: float 3s ease-in-out infinite;
    position: relative;
    z-index: 1;
    filter: drop-shadow(0 0 20px rgba(255,215,0,0.5));
}
@keyframes float {
    0%,100% { transform: translateY(0); }
    50%      { transform: translateY(-10px); }
}
.box-card-body { padding: 20px 22px; }
.box-card-name {
    font-family: "Rajdhani", sans-serif;
    font-size: 1.35rem;
    font-weight: 700;
    color: #fff;
    margin-bottom: 6px;
}
.box-card-desc {
    color: rgba(255,255,255,0.55);
    font-size: 0.875rem;
    line-height: 1.5;
    margin-bottom: 16px;
    min-height: 42px;
}
.drop-rates-mini {
    display: flex;
    gap: 8px;
    flex-wrap: wrap;
    margin-bottom: 16px;
}
.rate-pill {
    padding: 3px 10px;
    border-radius: 30px;
    font-size: 0.72rem;
    font-weight: 600;
    border: 1px solid;
}
.rate-pill.vip   { color:#FFD700; border-color:rgba(255,215,0,0.4); background:rgba(255,215,0,0.08); }
.rate-pill.xin   { color:#CE93D8; border-color:rgba(206,147,216,0.4); background:rgba(206,147,216,0.08); }
.rate-pill.chung { color:#90CAF9; border-color:rgba(144,202,249,0.4); background:rgba(144,202,249,0.08); }

.box-card-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px;
    padding: 14px 22px;
    border-top: 1px solid rgba(255,255,255,0.06);
    background: rgba(0,0,0,0.2);
}
.box-price {
    font-family: "Rajdhani", sans-serif;
    font-size: 1.5rem;
    font-weight: 700;
    color: var(--gold);
}
.box-price span { font-size: 0.8rem; color: rgba(255,215,0,0.7); }
.btn-open-box {
    padding: 10px 22px;
    border-radius: 50px;
    font-weight: 700;
    font-size: 0.9rem;
    background: linear-gradient(135deg, #7B2FFF, #4A0099);
    border: 1px solid rgba(123,47,255,0.6);
    color: #fff;
    text-decoration: none;
    transition: all 0.25s ease;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    white-space: nowrap;
}
.btn-open-box:hover {
    background: linear-gradient(135deg, #9C27B0, #7B2FFF);
    box-shadow: 0 0 20px rgba(123,47,255,0.6);
    color: #FFD700;
    text-decoration: none;
    transform: scale(1.04);
}
.btn-open-box.disabled {
    background: rgba(100,100,100,0.3);
    border-color: rgba(100,100,100,0.4);
    color: rgba(255,255,255,0.4);
    pointer-events: none;
}
.stock-count {
    font-size: 0.78rem;
    color: rgba(255,255,255,0.45);
    margin-top: 4px;
    text-align: right;
}
.stock-low { color: #FF6B6B; }
.stock-ok  { color: #69DB7C; }

/* Empty state */
.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: rgba(255,255,255,0.4);
}
.empty-state-icon { font-size: 4rem; margin-bottom: 16px; }
.empty-state-text { font-size: 1.1rem; }
</style>
';
$body['footer'] = '';

require_once(__DIR__ . '/../../../models/is_user.php');
require_once(__DIR__ . '/header.php');

// Lấy danh sách túi mù đang hoạt động
$boxes = $CMSNT->get_list("SELECT * FROM `boxes` WHERE `status` = 1 ORDER BY `id` ASC");

// Lấy tỉ lệ cho từng túi
$rates_map = [];
$all_rates = $CMSNT->get_list("SELECT * FROM `box_rates` ORDER BY `box_id`, FIELD(`item_tier`,'VIP','Xịn','Chung')");
foreach ($all_rates as $r) {
    $rates_map[$r['box_id']][] = $r;
}

// Đếm stock còn lại
$stock_map = [];
$all_stock = $CMSNT->get_list("SELECT `box_id`, COUNT(*) as cnt FROM `box_items` WHERE `is_sold` = 0 GROUP BY `box_id`");
foreach ($all_stock as $s) {
    $stock_map[$s['box_id']] = $s['cnt'];
}
?>
<?php require_once(__DIR__ . '/sidebar.php'); ?>

<div class="content-page">
    <!-- Hero Banner -->
    <div class="gacha-hero">
        <div class="container">
            <div class="gacha-hero-title">🎁 CỬA HÀNG TÚI MÙ LIÊN QUÂN</div>
            <div class="gacha-hero-sub">Mở túi mù – nhận acc ngẫu nhiên – giao hàng tức thì!</div>
            <?php if (isset($getUser)): ?>
            <div class="balance-badge">
                <i class="fas fa-wallet"></i>
                Số dư: <b><?= format_currency($getUser['money']); ?></b>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="container" style="padding-bottom: 60px;">

        <!-- How it works -->
        <div class="row mt-4 mb-2">
            <div class="col-12">
                <div style="background:rgba(123,47,255,0.08);border:1px solid rgba(123,47,255,0.25);border-radius:14px;padding:18px 24px;display:flex;gap:28px;flex-wrap:wrap;align-items:center;">
                    <div style="color:rgba(255,255,255,0.8);font-size:0.9rem;display:flex;gap:20px;flex-wrap:wrap;">
                        <span>🎯 <b style="color:#FFD700">Chọn túi</b> phù hợp với ngân sách</span>
                        <span>💳 <b style="color:#00E5FF">Thanh toán</b> từ số dư tài khoản</span>
                        <span>⚡ <b style="color:#69DB7C">Nhận ngay</b> acc/code tức thì không chờ đợi</span>
                        <span>🔐 <b style="color:#CE93D8">Copy thông tin</b> từ Kho Đồ của bạn</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Box Grid -->
        <?php if (empty($boxes)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📦</div>
            <div class="empty-state-text">Hiện chưa có túi mù nào. Quay lại sau bạn nhé!</div>
        </div>
        <?php else: ?>
        <div class="boxes-grid">
            <?php foreach ($boxes as $i => $box): 
                $box_id  = $box['id'];
                $stock   = isset($stock_map[$box_id]) ? $stock_map[$box_id] : 0;
                $box_rates = isset($rates_map[$box_id]) ? $rates_map[$box_id] : [];
                $is_vip  = ($i == 1); // second box gets VIP badge
                $is_new  = ($i == 0);
                $sold_out = ($stock == 0);
            ?>
            <div class="box-card">
                <!-- Badge -->
                <?php if ($sold_out): ?>
                    <div class="box-card-badge badge-sold">HẾT HÀNG</div>
                <?php elseif ($is_vip): ?>
                    <div class="box-card-badge badge-vip">👑 VIP</div>
                <?php elseif ($is_new): ?>
                    <div class="box-card-badge badge-new">🔥 HOT</div>
                <?php endif; ?>

                <!-- Image -->
                <div class="box-img-wrap">
                    <?php if ($box['image'] && file_exists($box['image'])): ?>
                    <img src="<?= base_url($box['image']); ?>" class="box-img" alt="<?= htmlspecialchars($box['name']); ?>">
                    <?php else: ?>
                    <div class="box-img-placeholder"><?= $is_vip ? '👑' : '🎁'; ?></div>
                    <?php endif; ?>
                </div>

                <!-- Body -->
                <div class="box-card-body">
                    <div class="box-card-name"><?= htmlspecialchars($box['name']); ?></div>
                    <div class="box-card-desc"><?= htmlspecialchars($box['description']); ?></div>

                    <!-- Drop rates mini pills -->
                    <?php if (!empty($box_rates)): ?>
                    <div class="drop-rates-mini">
                        <?php foreach ($box_rates as $rate): 
                            $tier_class = strtolower(str_replace(['ị', 'ú'], ['i', 'u'], $rate['item_tier']));
                            $tier_map = ['VIP' => 'vip', 'Xịn' => 'xin', 'Chung' => 'chung'];
                            $cls = $tier_map[$rate['item_tier']] ?? 'chung';
                        ?>
                        <span class="rate-pill <?= $cls; ?>">
                            <?= $rate['item_tier']; ?> <?= $rate['win_rate']; ?>%
                        </span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>

                    <div class="stock-count <?= $stock > 5 ? 'stock-ok' : ($stock > 0 ? 'stock-low' : ''); ?>">
                        <?php if ($sold_out): ?>
                        ❌ Hết hàng
                        <?php elseif ($stock <= 5): ?>
                        ⚠️ Chỉ còn <?= $stock; ?> phần thưởng
                        <?php else: ?>
                        ✅ Còn <?= $stock; ?> phần thưởng
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Footer -->
                <div class="box-card-footer">
                    <div>
                        <div class="box-price"><?= format_currency($box['price']); ?> <span>VNĐ</span></div>
                    </div>
                    <?php if ($sold_out): ?>
                    <a href="#" class="btn-open-box disabled">
                        <i class="fas fa-ban"></i> Hết hàng
                    </a>
                    <?php elseif (!isset($getUser)): ?>
                    <a href="<?= base_url('client/login'); ?>" class="btn-open-box">
                        <i class="fas fa-sign-in-alt"></i> Đăng nhập
                    </a>
                    <?php else: ?>
                    <a href="<?= base_url('client/unbox?id=' . $box_id); ?>" class="btn-open-box">
                        <i class="fas fa-gift"></i> Mở Ngay
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

    </div><!-- /container -->
</div><!-- /content-page -->

<?php require_once(__DIR__ . '/footer.php'); ?>
