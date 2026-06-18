<?php if (!defined('IN_SITE')) {
    die('The Request Not Found');
}

// Require login
require_once(__DIR__ . '/../../../models/is_user.php');

// Lấy box_id
$box_id = isset($_GET['id']) ? (int)check_string($_GET['id']) : 0;
if (!$box_id) { redirect(base_url('client/blind-boxes')); }

$box = $CMSNT->get_row("SELECT * FROM `boxes` WHERE `id` = '$box_id' AND `status` = 1");
if (!$box) { redirect(base_url('client/blind-boxes')); }

// Đếm stock còn lại
$stock = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `box_items` WHERE `box_id` = '$box_id' AND `is_sold` = 0")['cnt'];

// Lấy tỉ lệ
$rates = $CMSNT->get_list("SELECT * FROM `box_rates` WHERE `box_id` = '$box_id' ORDER BY `win_rate` ASC");

$body = [
    'title'   => 'Mở ' . htmlspecialchars($box['name']) . ' | ' . $CMSNT->site('title'),
    'desc'    => 'Mở túi mù Liên Quân – nhận acc tức thì!',
    'keyword' => 'mở túi mù, liên quân, gacha'
];
$body['header'] = '
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<style>
@import url("https://fonts.googleapis.com/css2?family=Rajdhani:wght@500;600;700&family=Be+Vietnam+Pro:wght@400;500;600;700&display=swap");
:root {
    --gold: #FFD700; --purple: #7B2FFF; --cyan: #00E5FF;
    --green: #69DB7C; --red: #FF6B6B;
}
body { font-family: "Be Vietnam Pro", sans-serif; background: #0a0015; color: #fff; }

/* ======= UNBOX PAGE ======= */
.unbox-wrapper {
    min-height: 100vh;
    background: radial-gradient(ellipse at 50% 0%, rgba(123,47,255,0.2) 0%, transparent 60%),
                linear-gradient(180deg, #0a0015 0%, #100025 100%);
    padding: 40px 0 80px;
}
.unbox-back {
    display: inline-flex; align-items: center; gap: 6px;
    color: rgba(255,255,255,0.5); text-decoration: none;
    font-size: 0.875rem; margin-bottom: 28px;
    transition: color 0.2s;
}
.unbox-back:hover { color: var(--gold); text-decoration: none; }

/* Stage */
.unbox-stage {
    text-align: center;
    margin-bottom: 40px;
}
.unbox-title {
    font-family: "Rajdhani", sans-serif;
    font-size: 2.2rem; font-weight: 700;
    background: linear-gradient(90deg,#FFD700,#FF6B35,#FFD700);
    background-size: 200% auto;
    -webkit-background-clip: text; -webkit-text-fill-color: transparent;
    animation: shimmer 3s linear infinite;
    margin-bottom: 8px;
}
@keyframes shimmer { to { background-position: 200% center; } }
.unbox-price-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: rgba(255,215,0,0.1); border: 1px solid rgba(255,215,0,0.35);
    border-radius: 50px; padding: 7px 20px;
    color: var(--gold); font-weight: 700; font-size: 1rem;
    margin-bottom: 36px;
}

/* Box visual */
.box-visual-wrap {
    position: relative; display: inline-block;
    cursor: pointer; margin-bottom: 32px;
}
.box-visual {
    font-size: 8rem; display: block;
    transition: transform 0.2s;
    filter: drop-shadow(0 0 40px rgba(123,47,255,0.7));
    animation: idle-float 4s ease-in-out infinite;
}
@keyframes idle-float {
    0%,100% { transform: translateY(0) rotate(-2deg); }
    50%      { transform: translateY(-14px) rotate(2deg); }
}
.box-visual.shaking {
    animation: shake-box 0.12s linear infinite;
    filter: drop-shadow(0 0 60px rgba(255,215,0,0.9));
}
@keyframes shake-box {
    0%   { transform: rotate(-8deg) scale(1.08); }
    25%  { transform: rotate(8deg)  scale(1.12); }
    50%  { transform: rotate(-6deg) scale(1.10); }
    75%  { transform: rotate(6deg)  scale(1.08); }
    100% { transform: rotate(-8deg) scale(1.08); }
}
.sparkle {
    position: absolute; pointer-events: none;
    font-size: 1.5rem; opacity: 0;
    animation: sparkle-burst 0.6s ease-out forwards;
}
@keyframes sparkle-burst {
    0%   { opacity: 1; transform: scale(0) translate(0,0); }
    100% { opacity: 0; transform: scale(1.5) translate(var(--tx), var(--ty)); }
}

/* Open Button */
.btn-open-gacha {
    display: inline-flex; align-items: center; gap: 10px;
    padding: 16px 48px; border-radius: 50px;
    font-family: "Rajdhani", sans-serif;
    font-size: 1.4rem; font-weight: 700; letter-spacing: 1px;
    background: linear-gradient(135deg, #7B2FFF 0%, #FF6B35 100%);
    border: none; color: #fff;
    box-shadow: 0 8px 32px rgba(123,47,255,0.5);
    cursor: pointer; transition: all 0.25s ease;
    text-transform: uppercase;
}
.btn-open-gacha:hover:not(:disabled) {
    transform: scale(1.06);
    box-shadow: 0 12px 48px rgba(123,47,255,0.7);
}
.btn-open-gacha:disabled {
    opacity: 0.5; cursor: not-allowed; transform: none;
}
.btn-open-gacha.loading span { display: none; }
.btn-open-gacha.loading::after {
    content: "Đang mở..."; font-size: 1.2rem;
}

.balance-info {
    margin-top: 16px; font-size: 0.9rem;
    color: rgba(255,255,255,0.5);
}
.balance-info b { color: var(--gold); }

/* Drop rate table */
.rates-card {
    background: rgba(255,255,255,0.04);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px; overflow: hidden;
    margin-bottom: 24px;
}
.rates-card-header {
    padding: 14px 20px; border-bottom: 1px solid rgba(255,255,255,0.08);
    font-family: "Rajdhani", sans-serif; font-size: 1.1rem;
    font-weight: 700; letter-spacing: 0.5px;
    color: rgba(255,255,255,0.9);
    display: flex; align-items: center; gap: 8px;
}
.rate-row {
    display: flex; align-items: center;
    justify-content: space-between;
    padding: 12px 20px;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.rate-row:last-child { border-bottom: none; }
.tier-name {
    display: flex; align-items: center; gap: 10px;
    font-weight: 600; font-size: 0.95rem;
}
.tier-dot {
    width: 10px; height: 10px; border-radius: 50%;
}
.tier-VIP .tier-dot   { background: var(--gold); box-shadow: 0 0 8px var(--gold); }
.tier-Xịn .tier-dot   { background: #CE93D8; box-shadow: 0 0 8px #CE93D8; }
.tier-Chung .tier-dot { background: #90CAF9; box-shadow: 0 0 8px #90CAF9; }
.tier-VIP   { color: var(--gold); }
.tier-Xịn   { color: #CE93D8; }
.tier-Chung { color: #90CAF9; }
.rate-bar-wrap {
    flex: 1; margin: 0 16px;
    height: 6px; background: rgba(255,255,255,0.08);
    border-radius: 10px; overflow: hidden;
}
.rate-bar {
    height: 100%; border-radius: 10px;
    transition: width 1s ease;
}
.tier-VIP   .rate-bar { background: linear-gradient(90deg,#FFD700,#FF8C00); }
.tier-Xịn   .rate-bar { background: linear-gradient(90deg,#CE93D8,#9C27B0); }
.tier-Chung .rate-bar { background: linear-gradient(90deg,#90CAF9,#1565C0); }
.rate-pct { font-weight: 700; font-size: 0.95rem; min-width: 45px; text-align: right; }

/* Info card */
.info-card {
    background: rgba(255,255,255,0.03);
    border: 1px solid rgba(255,255,255,0.08);
    border-radius: 16px; padding: 20px;
    margin-bottom: 24px;
}
.info-card h6 {
    font-family: "Rajdhani", sans-serif;
    font-size: 1rem; font-weight: 700;
    color: rgba(255,255,255,0.7); margin-bottom: 12px;
    text-transform: uppercase; letter-spacing: 0.5px;
}
.info-row {
    display: flex; justify-content: space-between;
    padding: 8px 0;
    border-bottom: 1px solid rgba(255,255,255,0.06);
    font-size: 0.9rem;
}
.info-row:last-child { border-bottom: none; }
.info-row .label { color: rgba(255,255,255,0.5); }
.info-row .val   { font-weight: 600; }

/* SweetAlert2 custom */
.swal2-popup.gacha-popup {
    background: linear-gradient(145deg, #1a0035, #0a0015);
    border: 1px solid rgba(123,47,255,0.5);
    border-radius: 20px;
    color: #fff;
}
.swal2-popup.gacha-popup .swal2-title { color: #fff; }
.swal2-popup.gacha-popup .swal2-html-container { color: rgba(255,255,255,0.85); }
.swal2-popup.gacha-popup .swal2-confirm {
    background: linear-gradient(135deg,#7B2FFF,#4A0099) !important;
    border-radius: 50px !important;
    padding: 10px 32px !important;
    font-weight: 700 !important;
}

.reward-display {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.15);
    border-radius: 12px;
    padding: 14px;
    font-family: monospace;
    font-size: 1rem;
    word-break: break-all;
    margin: 12px 0;
}
.copy-btn {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 7px 18px; border-radius: 50px;
    background: rgba(123,47,255,0.3); border: 1px solid rgba(123,47,255,0.5);
    color: #fff; font-size: 0.85rem; font-weight: 600;
    cursor: pointer; transition: all 0.2s;
}
.copy-btn:hover { background: rgba(123,47,255,0.6); }
</style>
';
$body['footer'] = '
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
';

require_once(__DIR__ . '/header.php');
require_once(__DIR__ . '/sidebar.php');
?>
<div class="content-page">
<div class="unbox-wrapper">
<div class="container">
    <a href="<?= base_url('client/blind-boxes'); ?>" class="unbox-back">
        <i class="fas fa-arrow-left"></i> Quay lại cửa hàng
    </a>

    <div class="row">
        <!-- LEFT: Box Stage -->
        <div class="col-lg-7">
            <div class="unbox-stage">
                <div class="unbox-title"><?= htmlspecialchars($box['name']); ?></div>
                <p style="color:rgba(255,255,255,0.55); margin-bottom:20px;"><?= htmlspecialchars($box['description']); ?></p>

                <div class="unbox-price-badge">
                    <i class="fas fa-coins"></i>
                    Giá mở: <b><?= format_currency($box['price']); ?></b>
                </div>

                <!-- Box Visual + Sparkle area -->
                <div style="position:relative; display:inline-block;" id="box-stage-wrap">
                    <div class="box-visual-wrap" id="boxVisual" onclick="openBox()">
                        <span class="box-visual" id="boxEmoji"><?= ($box['id'] % 2 == 0) ? '👑' : '🎁'; ?></span>
                    </div>
                    <div id="sparkle-container" style="position:absolute;top:0;left:0;width:100%;height:100%;pointer-events:none;"></div>
                </div>

                <!-- Open Button -->
                <?php if ($stock > 0): ?>
                <div>
                    <button class="btn-open-gacha" id="openBtn" onclick="openBox()">
                        <i class="fas fa-gift"></i>
                        <span>🎮 MỞ TÚI MÙ</span>
                    </button>
                    <div class="balance-info">
                        Số dư của bạn: <b><?= format_currency($getUser['money']); ?></b>
                        &nbsp;|&nbsp; Còn <b style="color:<?= $stock <= 5 ? '#FF6B6B' : '#69DB7C'; ?>"><?= $stock; ?></b> phần thưởng
                    </div>
                </div>
                <?php else: ?>
                <div>
                    <button class="btn-open-gacha" disabled>
                        <i class="fas fa-ban"></i> HẾT HÀNG
                    </button>
                    <p style="color:#FF6B6B; margin-top:12px; font-size:0.9rem;">Kho phần thưởng đã hết. Vui lòng quay lại sau!</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- RIGHT: Info panels -->
        <div class="col-lg-5">
            <!-- Drop Rates -->
            <?php if (!empty($rates)): ?>
            <div class="rates-card">
                <div class="rates-card-header">
                    <i class="fas fa-percentage" style="color:var(--gold)"></i>
                    Tỉ lệ rớt đồ
                </div>
                <?php foreach ($rates as $rate): 
                    $tier_key = $rate['item_tier'];
                ?>
                <div class="rate-row tier-<?= htmlspecialchars($tier_key); ?>">
                    <div class="tier-name">
                        <div class="tier-dot"></div>
                        <?= htmlspecialchars($tier_key); ?>
                    </div>
                    <div class="rate-bar-wrap">
                        <div class="rate-bar" style="width:<?= $rate['win_rate']; ?>%"></div>
                    </div>
                    <div class="rate-pct"><?= $rate['win_rate']; ?>%</div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Box Info -->
            <div class="info-card">
                <h6><i class="fas fa-info-circle mr-2" style="color:var(--cyan)"></i>Thông tin túi</h6>
                <div class="info-row">
                    <span class="label">Giá mở</span>
                    <span class="val" style="color:var(--gold)"><?= format_currency($box['price']); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Phần thưởng còn lại</span>
                    <span class="val" style="color:<?= $stock > 5 ? '#69DB7C' : '#FF6B6B'; ?>"><?= $stock; ?></span>
                </div>
                <div class="info-row">
                    <span class="label">Loại phần thưởng</span>
                    <span class="val">Tài khoản game</span>
                </div>
                <div class="info-row">
                    <span class="label">Giao hàng</span>
                    <span class="val" style="color:#69DB7C"><i class="fas fa-bolt"></i> Tức thì</span>
                </div>
            </div>

            <!-- Guide -->
            <div class="info-card">
                <h6><i class="fas fa-lightbulb mr-2" style="color:var(--gold)"></i>Lưu ý quan trọng</h6>
                <ul style="color:rgba(255,255,255,0.6);font-size:0.875rem;padding-left:16px;line-height:1.8; margin:0;">
                    <li>Phần thưởng sẽ được giao ngay sau khi mở thành công</li>
                    <li>Lưu thông tin acc ngay, hệ thống không lưu mật khẩu trùng lặp</li>
                    <li>Vào <a href="<?= base_url('client/inventory'); ?>" style="color:var(--cyan)">Kho Đồ</a> để xem lại mọi lúc</li>
                    <li>Không hoàn tiền sau khi đã mở túi</li>
                </ul>
            </div>
        </div>
    </div><!-- /row -->
</div><!-- /container -->
</div><!-- /unbox-wrapper -->
</div><!-- /content-page -->

<script>
var isOpening = false;
var boxId     = <?= $box_id; ?>;
var token     = '<?= $getUser['token']; ?>';
var price     = <?= $box['price']; ?>;
var balance   = <?= $getUser['money']; ?>;

function spawnSparkles() {
    var container = document.getElementById('sparkle-container');
    var wrap      = document.getElementById('box-stage-wrap');
    var emojis    = ['✨','⭐','💫','🌟','💥','🎊'];
    container.innerHTML = '';
    for (var i = 0; i < 12; i++) {
        (function(i) {
            setTimeout(function() {
                var el = document.createElement('span');
                el.className = 'sparkle';
                el.textContent = emojis[Math.floor(Math.random() * emojis.length)];
                var tx = (Math.random() * 160 - 80) + 'px';
                var ty = (Math.random() * 160 - 80) + 'px';
                el.style.setProperty('--tx', tx);
                el.style.setProperty('--ty', ty);
                el.style.top  = (Math.random() * 80 + 10) + '%';
                el.style.left = (Math.random() * 80 + 10) + '%';
                container.appendChild(el);
            }, i * 50);
        })(i);
    }
}

function openBox() {
    if (isOpening) return;
    if (balance < price) {
        Swal.fire({
            customClass: { popup: 'gacha-popup' },
            icon: 'warning',
            title: '💸 Số dư không đủ!',
            html: 'Bạn cần nạp thêm <b style="color:#FFD700">' + price.toLocaleString() + ' VNĐ</b> để mở túi này.<br><br><a href="<?= base_url('client/recharge'); ?>" class="btn-open-gacha" style="font-size:1rem;padding:10px 28px;">Nạp Tiền Ngay</a>',
            showConfirmButton: false,
        });
        return;
    }
    isOpening = true;
    var btn = document.getElementById('openBtn');
    var box = document.getElementById('boxEmoji');
    if (btn) { btn.disabled = true; btn.classList.add('loading'); }
    box.classList.add('shaking');
    spawnSparkles();

    $.ajax({
        url: '<?= BASE_URL('ajaxs/client/open_box.php'); ?>',
        method: 'POST',
        dataType: 'JSON',
        data: { token: token, box_id: boxId },
        success: function(res) {
            box.classList.remove('shaking');
            if (res.status === 'success') {
                // Update local balance
                balance -= price;
                // Color mapping per tier
                var tierColors = { 'VIP': '#FFD700', 'Xịn': '#CE93D8', 'Chung': '#90CAF9' };
                var tierIcons  = { 'VIP': '👑', 'Xịn': '🌟', 'Chung': '🎁' };
                var col  = tierColors[res.item_tier] || '#90CAF9';
                var icon = tierIcons[res.item_tier]  || '🎁';
                var uniqueId = 'reward-info-' + Date.now();
                Swal.fire({
                    customClass: { popup: 'gacha-popup' },
                    title: icon + ' MỞ TÚI THÀNH CÔNG!',
                    html: '<div style="margin-bottom:10px;">Bạn nhận được phần thưởng loại <b style="color:' + col + ';">' + res.item_tier + '</b></div>'
                        + '<div style="font-size:0.85rem;color:rgba(255,255,255,0.5);margin-bottom:8px;">🔐 Thông tin tài khoản:</div>'
                        + '<div class="reward-display" id="' + uniqueId + '">' + res.item_info + '</div>'
                        + '<button class="copy-btn" onclick="copyReward(\'' + uniqueId + '\')"><i class="fas fa-copy"></i> Sao chép</button>'
                        + '<div style="margin-top:14px;font-size:0.8rem;color:rgba(255,255,255,0.4)">📦 Xem lại trong <a href="<?= base_url('client/inventory'); ?>" style="color:var(--cyan)">Kho Đồ</a></div>',
                    confirmButtonText: '🎮 Mở Tiếp!',
                    showCancelButton: true,
                    cancelButtonText: 'Xem Kho Đồ',
                }).then(function(result) {
                    isOpening = false;
                    if (btn) { btn.disabled = false; btn.classList.remove('loading'); }
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        window.location.href = '<?= base_url('client/inventory'); ?>';
                    } else {
                        location.reload();
                    }
                });
            } else {
                Swal.fire({
                    customClass: { popup: 'gacha-popup' },
                    icon: 'error',
                    title: '❌ Thất bại',
                    text: res.msg,
                    confirmButtonText: 'Đã hiểu',
                }).then(function() {
                    isOpening = false;
                    if (btn) { btn.disabled = false; btn.classList.remove('loading'); }
                });
            }
        },
        error: function() {
            box.classList.remove('shaking');
            isOpening = false;
            if (btn) { btn.disabled = false; btn.classList.remove('loading'); }
            Swal.fire({
                customClass: { popup: 'gacha-popup' },
                icon: 'error', title: 'Lỗi kết nối',
                text: 'Vui lòng thử lại.'
            });
        }
    });
}

function copyReward(elemId) {
    var el = document.getElementById(elemId);
    if (!el) return;
    navigator.clipboard.writeText(el.textContent.trim()).then(function() {
        Swal.showValidationMessage('✅ Đã sao chép!');
        setTimeout(function() { Swal.resetValidationMessage(); }, 2000);
    });
}
</script>

<?php require_once(__DIR__ . '/footer.php'); ?>
