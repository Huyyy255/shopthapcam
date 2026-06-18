<?php

define("IN_SITE", true);
require_once(__DIR__ . "/../../config.php");
require_once(__DIR__ . "/../../libs/db.php");
require_once(__DIR__ . "/../../libs/lang.php");
require_once(__DIR__ . "/../../libs/helper.php");

$CMSNT = new DB();

// Verify admin login
if (!isset($_SESSION['admin_login']) || $_SESSION['admin_login'] != 1) {
    die(json_encode(['status' => 'error', 'msg' => 'Unauthorized']));
}

$action = isset($_POST['action']) ? check_string($_POST['action']) : '';

switch ($action) {

    // -------------------------------------------------------
    // DELETE BOX
    // -------------------------------------------------------
    case 'delete_box':
        $id = (int)$_POST['id'];
        if (!$id) die(json_encode(['status' => 'error', 'msg' => 'ID không hợp lệ']));
        $CMSNT->delete('boxes',      "`id` = '$id'");
        $CMSNT->delete('box_rates',  "`box_id` = '$id'");
        $CMSNT->delete('box_items',  "`box_id` = '$id' AND `is_sold` = 0");
        die(json_encode(['status' => 'success', 'msg' => 'Đã xóa túi mù #' . $id]));

    // -------------------------------------------------------
    // TOGGLE BOX STATUS
    // -------------------------------------------------------
    case 'toggle_box':
        $id = (int)$_POST['id'];
        $box = $CMSNT->get_row("SELECT `status` FROM `boxes` WHERE `id` = '$id'");
        if (!$box) die(json_encode(['status' => 'error', 'msg' => 'Không tìm thấy túi']));
        $new_status = $box['status'] == 1 ? 0 : 1;
        $CMSNT->update('boxes', ['status' => $new_status], "`id` = '$id'");
        die(json_encode([
            'status' => 'success',
            'msg'    => 'Đã ' . ($new_status ? 'bật' : 'tắt') . ' túi mù',
            'new_status' => $new_status
        ]));

    // -------------------------------------------------------
    // GET BOX STOCK COUNT
    // -------------------------------------------------------
    case 'get_stock':
        $id = (int)$_POST['id'];
        $stock = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `box_items` WHERE `box_id` = '$id' AND `is_sold` = 0")['cnt'];
        $sold  = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM `box_items` WHERE `box_id` = '$id' AND `is_sold` = 1")['cnt'];
        die(json_encode(['status' => 'success', 'stock' => $stock, 'sold' => $sold]));

    // -------------------------------------------------------
    // SAVE DROP RATES (JSON payload)
    // -------------------------------------------------------
    case 'save_rates':
        $box_id = (int)$_POST['box_id'];
        $rates  = json_decode($_POST['rates'], true);
        if (!$box_id || !is_array($rates)) {
            die(json_encode(['status' => 'error', 'msg' => 'Dữ liệu không hợp lệ']));
        }
        $total = 0;
        foreach ($rates as $r) { $total += (int)$r['rate']; }
        if ($total !== 100) {
            die(json_encode(['status' => 'error', 'msg' => "Tổng tỉ lệ phải = 100%. Hiện tại: {$total}%"]));
        }
        $CMSNT->delete('box_rates', "`box_id` = '$box_id'");
        foreach ($rates as $r) {
            $tier = check_string($r['tier']);
            $rate = (int)$r['rate'];
            if ($tier && $rate > 0) {
                $CMSNT->insert('box_rates', [
                    'box_id'    => $box_id,
                    'item_tier' => $tier,
                    'win_rate'  => $rate
                ]);
            }
        }
        die(json_encode(['status' => 'success', 'msg' => 'Đã lưu tỉ lệ cho box #' . $box_id]));

    // -------------------------------------------------------
    // BULK IMPORT ITEMS
    // -------------------------------------------------------
    case 'import_items':
        $box_id  = (int)$_POST['box_id'];
        $tier    = check_string($_POST['tier']);
        $raw     = trim($_POST['raw_items']);
        if (!$box_id || !$tier || !$raw) {
            die(json_encode(['status' => 'error', 'msg' => 'Thiếu thông tin import']));
        }
        $lines    = array_filter(explode("\n", $raw), function($l) { return trim($l) !== ''; });
        $count_ok = 0;
        foreach ($lines as $line) {
            $line = trim($line);
            $CMSNT->insert('box_items', [
                'box_id'       => $box_id,
                'item_tier'    => $tier,
                'account_info' => $line,
                'is_sold'      => 0,
                'created_at'   => gettime()
            ]);
            $count_ok++;
        }
        die(json_encode([
            'status'  => 'success',
            'msg'     => "Import thành công {$count_ok} item vào box #{$box_id} - Tier: {$tier}",
            'count'   => $count_ok
        ]));

    // -------------------------------------------------------
    // DELETE UNSOLD ITEMS (clear stock)
    // -------------------------------------------------------
    case 'clear_stock':
        $box_id = (int)$_POST['box_id'];
        $tier   = isset($_POST['tier']) ? check_string($_POST['tier']) : '';
        $where  = "`box_id` = '$box_id' AND `is_sold` = 0";
        if ($tier) $where .= " AND `item_tier` = '$tier'";
        $CMSNT->delete('box_items', $where);
        die(json_encode(['status' => 'success', 'msg' => 'Đã xóa hàng chưa bán' . ($tier ? " - Tier: $tier" : '')]));

    // -------------------------------------------------------
    // GET HISTORY STATS (for dashboard widget)
    // -------------------------------------------------------
    case 'get_stats':
        $total  = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM user_inventory")['cnt'];
        $vip    = (int)$CMSNT->get_row("SELECT COUNT(*) as cnt FROM user_inventory WHERE item_tier='VIP'")['cnt'];
        $rev_q  = $CMSNT->get_row("SELECT SUM(b.price) as total FROM user_inventory ui JOIN boxes b ON b.id=ui.box_id");
        $revenue = (float)($rev_q['total'] ?? 0);
        die(json_encode([
            'status'  => 'success',
            'total'   => $total,
            'vip'     => $vip,
            'revenue' => $revenue,
            'revenue_fmt' => number_format($revenue, 0, '.', ',') . 'đ'
        ]));

    default:
        die(json_encode(['status' => 'error', 'msg' => 'Action không hợp lệ']));
}
