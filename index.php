<?php

// LinkorCMS 1.3
// � 2006 - 2010 �������� ��������� ���������� (linkorcms@yandex.ru)
// ����: index.php
// ����������: ������� ��������

if($_SERVER['REQUEST_METHOD'] == "HEAD"){ // ��������� HEAD �������
	header("X-Request: HEAD");
	exit();
}

define('MAIN_SCRIPT', true);
define('VALID_RUN', true);

include_once('config/init.php'); // ������������ � �������������
include_once($config['inc_dir'].'system_plugins.inc.php'); // �������
include_once($config['inc_dir'].'system.php'); // �������
include_once($config['db_dir'].'database.php'); // ����� ��� ������ � ����� ������

// �������� ������������ �����
LoadSiteConfig($config);
LoadSiteConfig($plug_config, 'plugins_config', 'plugins_config_groups');

// ��������������
include('config/autoupdate.php');

// ���
if($config['general']['ufu'] && isset($_GET['ufu'])){
	$_GET = UfuRewrite($_GET['ufu']);
}

// ������������� ��������� ���� �� ���������
SetDefaultTimezone();

// ������
include_once($config['inc_dir'].'user.class.php');

// ������� ���� ��� �������������
if($config['general']['private_site'] && $user->AccessLevel() != 1){
	include_once($config['apanel_dir'].'template.login.php');
	AdminShowLogin('���� ������ ��� �������������');
}

// ����������
$stats_alloy = $config['general']['statistika'];
if($stats_alloy){
	include_once($config['inc_dir'].'statistika.inc.php');
}

// �������� ��� ������
$ModuleName = '';
if(!isset($_GET['name'])){
	define('INDEX_PHP', true); // ������ �� ������� ��������
	$ModuleName = SafeEnv($config['general']['site_module'], 255, str, false, false);
}else{
	define('INDEX_PHP', false);
	$ModuleName = SafeEnv($_GET['name'], 255, str);
}

// ��������� �������� �� ������ ������
$db->Select('modules', "`enabled`='1' and `folder`='$ModuleName'");
$valid = false;
$valid_init = false;
if($db->NumRows() > 0){
	$mod = $db->FetchRow();
	if($user->AccessIsResolved($mod['view'], $userAccess)){
		define('MOD_DIR', $config['mod_dir'].$ModuleName.'/');
		define('MOD_FILE', MOD_DIR.'index.php');
		define('MOD_INIT', MOD_DIR.'init.php');
		define('MOD_THEME', RealPath2(SafeDB($mod['theme'], 255, str)));
		$valid = file_exists(MOD_FILE);
		$valid_init = file_exists(MOD_INIT);
	}else{
		$msg = '<center>������ ��������.</center>';
	}
}else{
	$msg = '<center>������ �������� ('.SafeDB($ModuleName, 255, str).') �� ���������� ��� �� �������� � ������ ������.</center>';
}

include_once($config['inc_dir'].'plugins.inc.php'); // �������

if($valid){
	// ������������� ������
	if($valid_init){
		include (MOD_INIT);
		if(function_exists('mod_initialization')){
			mod_initialization();
		}
	}
	// ������������
	if(!$system['no_templates']){
		include_once($config['inc_dir'].'index_template.inc.php');
	}
	// ���������
	if(!$system['no_messages']){
		include_once($config['inc_dir'].'messages.inc.php');
	}
	include(MOD_FILE); // ������
	// ��������� �����
	if(!$system['no_messages']){
		BottomMessages();
	}
	// ����� ������ ������������
	if(!$system['no_echo']){
		$site->TEcho();
	}
	// ���������� ����������
	if(!$system['stop_hit'] && $stats_alloy){
		HitStatisticProcess();
		StatisticProcess();
	}
	if($valid_init){
		if(function_exists('mod_finalization')){
			mod_finalization();
		}
	}
}else{
	include_once($config['inc_dir'].'index_template.inc.php');
	$site->AddTextBox('������', $msg);
	$site->TEcho();
}

?>