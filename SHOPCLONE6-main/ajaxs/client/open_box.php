<?php

define("IN_SITE", true);
require_once(__DIR__."/../../config.php");
require_once(__DIR__."/../../libs/db.php");
require_once(__DIR__."/../../libs/lang.php");
require_once(__DIR__."/../../libs/helper.php");
require_once(__DIR__."/../../libs/database/users.php");

$User   = new users();
$CMSNT  = new DB();
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (!isset($_POST['token'])) {
        die(json_encode(['status' => 'error', 'msg' => 'Vui lòng đăng nhập']));
    }
    
    $token = check_string($_POST['token']);
    $box_id = isset($_POST['box_id']) ? check_string($_POST['box_id']) : 0;
    
    if (!$getUser = $CMSNT->get_row("SELECT * FROM `users` WHERE `token` = '$token' AND `banned` = 0 ")) {
        die(json_encode(['status' => 'error', 'msg' => 'Vui lòng đăng nhập hoặc tài khoản bị khóa']));
    }
    
    $box = $CMSNT->get_row("SELECT * FROM `boxes` WHERE `id` = '$box_id' AND `status` = 1");
    if (!$box) {
        die(json_encode(['status' => 'error', 'msg' => 'Túi mù không tồn tại hoặc đã đóng']));
    }
    
    $price = $box['price'];

    // Anti-spam cooldown: 1 giây giữa 2 lần mở
    $cooldown_key = 'open_box_last_' . $getUser['id'];
    if (isset($_SESSION[$cooldown_key]) && (time() - $_SESSION[$cooldown_key]) < 1) {
        die(json_encode(['status' => 'error', 'msg' => 'Bạn đang thao tác quá nhanh, vui lòng thử lại!']));
    }
    $_SESSION[$cooldown_key] = time();

    // Start Transaction to avoid race conditions
    $CMSNT->query("START TRANSACTION");
    
    // Lock the user row for update
    $lockedUser = $CMSNT->get_row("SELECT `money` FROM `users` WHERE `id` = '".$getUser['id']."' FOR UPDATE");
    if ($lockedUser['money'] < $price) {
        $CMSNT->query("ROLLBACK");
        die(json_encode(['status' => 'error', 'msg' => 'Số dư không đủ, vui lòng nạp thêm']));
    }
    
    // Deduct money
    $isTru = $CMSNT->tru("users", "money", $price, " `id` = '".$getUser['id']."' ");
    if (!$isTru) {
        $CMSNT->query("ROLLBACK");
        die(json_encode(['status' => 'error', 'msg' => 'Không thể trừ tiền, vui lòng thử lại']));
    }
    
    // Add transaction log
    $User->AddCredits($getUser['id'], -$price, "Mở túi mù: ".$box['name']);
    
    // RNG Logic (1-100)
    $rand = rand(1, 100);
    $rates = $CMSNT->get_list("SELECT * FROM `box_rates` WHERE `box_id` = '$box_id' ORDER BY `win_rate` DESC");
    
    $won_tier = null;
    $current_rate = 0;
    
    foreach ($rates as $rate) {
        $current_rate += $rate['win_rate'];
        if ($rand <= $current_rate) {
            $won_tier = $rate['item_tier'];
            break;
        }
    }
    
    // Fallback if no tier matches
    if (!$won_tier) {
        $CMSNT->query("ROLLBACK");
        die(json_encode(['status' => 'error', 'msg' => 'Lỗi cấu hình phần thưởng, vui lòng báo Admin']));
    }
    
    // Pick an unsold item of that tier
    $item = $CMSNT->get_row("SELECT * FROM `box_items` WHERE `box_id` = '$box_id' AND `item_tier` = '$won_tier' AND `is_sold` = 0 LIMIT 1 FOR UPDATE");
    
    if (!$item) {
        $CMSNT->query("ROLLBACK");
        die(json_encode(['status' => 'error', 'msg' => 'Rất tiếc, kho phần thưởng loại '.$won_tier.' đã hết hàng! Vui lòng báo Admin.']));
    }
    
    // Mark as sold
    $CMSNT->update("box_items", ['is_sold' => 1], " `id` = '".$item['id']."' ");
    
    // Save to inventory (with tier and box name)
    $CMSNT->insert("user_inventory", [
        'user_id'       => $getUser['id'],
        'box_id'        => $box_id,
        'box_name'      => $box['name'],
        'item_won_info' => $item['account_info'],
        'item_tier'     => $won_tier,
        'created_at'    => gettime()
    ]);
    
    // Commit transaction
    $CMSNT->query("COMMIT");
    
    die(json_encode([
        'status' => 'success', 
        'msg' => 'Mở túi thành công!',
        'item_tier' => $won_tier,
        'item_info' => $item['account_info']
    ]));
}
