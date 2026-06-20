<!-- Dev By CMSNT.CO | FB.COM/CMSNT.CO | ZALO.ME/0947838128 | MMO Solution -->
<?php
define("IN_SITE", true);

if (!empty($_GET['ajax_file'])) {
    $ajax_file = $_GET['ajax_file'];
    $ajax_file = str_replace(['../', '..\\'], '', $ajax_file);
    $ajax_path = __DIR__ . '/ajaxs/' . $ajax_file;
    if (file_exists($ajax_path) && is_file($ajax_path)) {
        require_once $ajax_path;
        exit;
    } else {
        http_response_code(404);
        exit('Ajax file not found.');
    }
}

require_once(__DIR__.'/../libs/db.php');
require_once(__DIR__.'/../config.php');
require_once(__DIR__.'/../libs/lang.php');
require_once(__DIR__.'/../libs/helper.php');
require_once(__DIR__.'/../libs/database/users.php');
$CMSNT = new DB();
 
$module = !empty($_GET['module']) ? check_path($_GET['module']) : 'client';
$home   = $module == 'client' ? $CMSNT->site('home_page') : 'home';
$action = !empty($_GET['action']) ? check_path($_GET['action']) : $home;

if($module == 'client'){
    if ($CMSNT->site('status') != 1 && !check_admin_session()) {
        require_once(__DIR__.'/../resources/views/common/maintenance.php');
        exit();
    }
}

if($action == 'footer' || $action == 'header' || $action == 'sidebar' || $action == 'nav'){
    require_once(__DIR__.'/../resources/views/common/404.php');
    exit();
}
$path = "resources/views/$module/$action.php";
if (file_exists(__DIR__.'/../'.$path)) {
    require_once(__DIR__.'/../'.$path);
    exit();
} else {
    require_once(__DIR__.'/../resources/views/common/404.php');
    exit();
}
?>
