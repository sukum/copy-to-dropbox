<?php
if(!defined('sugarEntry') || !sugarEntry) die('Not A Valid Entry Point');

global $app_strings;
global $app_list_strings;
global $mod_strings;
global $theme;
global $current_user;
global $timedate;
global $current_language;

$GLOBALS['log']->debug('In '.__FILE__);

if ($current_user->is_admin != 1) {
  sugar_die('Access to this page is limited to Administrator.');
  die('!');
}

$module = 'sk_Dropbox';
$action = 'index';
require_once "modules/{$module}/{$module}.php";
//$bean   = $beanList[$module] ;
//require_once $beanFiles[$bean];
//$mod_strings = return_module_language($current_language, $module);

$display_name = '';
$email        = '';
$authorized   = '';

if (isset($_REQUEST['not_approved'])) {
	header("Location: index.php");
	$exit_on_cleanup = true;
  sugar_cleanup($exit_on_cleanup);
}

//if (isset($_REQUEST['authorize'])) {
//	header("Location: index.php?module={$module}&action={$action}");
//	$exit_on_cleanup = true;
//  sugar_cleanup($exit_on_cleanup);
  try {
    $account_info = $module::get_dropbox_account_info();
  } catch (Exception $e) {
    //echo $e->getMessage();
    $module::clear_token();
  }
  if (is_array($account_info) && isset($account_info['body']) && is_object($account_info['body'])) {
    $display_name = $account_info['body']->display_name;
    $email        = $account_info['body']->email;
    $authorized   = 'Authorized';
  }
//}


$title  = "Dropbox &raquo;  Authorize App ";
echo get_module_title($module, $title, false);

$xtpl= new XTemplate (__DIR__."/{$action}.html");
$xtpl->assign("MOD", $mod_strings);
$xtpl->assign("APP", $app_strings);
$xtpl->assign("APP_LIST", $app_list_strings);
$xtpl->assign("JAVASCRIPT", get_set_focus_js());

$xtpl->assign("MODULE",		$module);
$xtpl->assign("ACTION",		$action);

$xtpl->assign("DISPLAY_NAME",		$display_name);
$xtpl->assign("EMAIL",		      $email);
$xtpl->assign("AUTHORIZED",     $authorized);


$xtpl->parse("main");
$xtpl->out("main");

$exit_on_cleanup = false;
sugar_cleanup($exit_on_cleanup);
