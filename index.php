<!-- Dev By CMSNT.CO | FB.COM/CMSNT.CO | ZALO.ME/0947838128 | MMO Solution -->
<?php
define("IN_SITE", true);


require_once(__DIR__.'/libs/db.php');
require_once(__DIR__.'/config.php');
require_once(__DIR__.'/libs/lang.php');
require_once(__DIR__.'/libs/helper.php');
require_once(__DIR__.'/libs/database/users.php');
$CMSNT = new DB();

if (!isset($_GET['module']) && isset($_SERVER['REQUEST_URI'])) {
    $uriPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
    $uriPath = trim($uriPath, '/');
    if ($uriPath !== '') {
        $segs = explode('/', $uriPath);
        $first = $segs[0] ?? '';
        if (in_array($first, ['Dashboard', 'Dashbroad', 'dashboard'])) {
            $_GET['module'] = 'client';
            $_GET['action'] = 'home';
        } elseif ($first === 'page' && isset($segs[1])) {
            $_GET['module'] = 'client';
            $_GET['action'] = 'page';
            $_GET['slug'] = $segs[1];
        } elseif ($first === 'join' && isset($segs[1])) {
            $_GET['module'] = 'client';
            $_GET['action'] = 'join';
            $_GET['ref'] = $segs[1];
        } elseif ($first === 'admin' && ($segs[1] ?? '') === 'login') {
            $_GET['module'] = 'admin';
            $_GET['action'] = 'home';
        } else {
            $_GET['module'] = $first;
            if (isset($segs[1])) {
                $action = $segs[1];
                $paramMap = [
                    'order' => 'trans_id', 'payment' => 'invoice',
                    'verify' => 'token', 'verify-otp-mail' => 'token',
                    'profile-ctv' => 'username',
                ];
                if (isset($segs[3]) && $action === 'notification' && $segs[2] === 'view') {
                    $_GET['action'] = 'notification-view';
                    $_GET['id'] = $segs[3];
                } else {
                    $_GET['action'] = $action;
                    if (isset($segs[2])) {
                        $_GET[$paramMap[$action] ?? 'id'] = $segs[2];
                    }
                }
            }
        }
    }
}

$module = !empty($_GET['module']) ? check_path($_GET['module']) : 'client';
$home   = $module == 'client' ? $CMSNT->site('home_page') : 'home';
$action = !empty($_GET['action']) ? check_path($_GET['action']) : $home;

if($module == 'client'){
    if ($CMSNT->site('status') != 1 && !isset($_SESSION['admin_login'])) {
        require_once(__DIR__.'/resources/views/common/maintenance.php');
        exit();
    }
}

if($action == 'footer' || $action == 'header' || $action == 'sidebar' || $action == 'nav'){
    require_once(__DIR__.'/resources/views/common/404.php');
    exit();
}
$path = "resources/views/$module/$action.php";
if (file_exists($path)) {
    require_once(__DIR__.'/'.$path);
    exit();
} else {
    require_once(__DIR__.'/resources/views/common/404.php');
    exit();
}
?>
