<?php

# LinkorCMS
# � 2006-2009 �������� ��������� ���������� (galitsky@pochta.ru)
# ����: admin.php
# ����������: ������� �������� �����-������

if($_SERVER['REQUEST_METHOD'] == "HEAD"){ // ��������� HEAD �������
	header("X-Request: HEAD");
	exit();
}

define('ADMIN_SCRIPT', true);
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

// ������������� ��������� ���� �� ���������
SetDefaultTimezone();

include_once($config['inc_dir'].'user.class.php'); // ������
include_once($config['apanel_dir'].'functions.php');

function AdminMain()
{
	global $config, $user, $site, $db; // $user, $site � $db ������������ � �������������.

	if(isset($_GET['exe']) && $_GET['exe'] == 'exit'){
		$user->UnsetCookie('admin');
		GO(Ufu('index.php'));
	}elseif(!isset($_GET['exe'])){
		$exe = 'adminpanel';
	}else{
		$mods = GetModuleList();
		if(isset($mods[$_GET['exe']])){
			$exe = RealPath2(SafeEnv($_GET['exe'], 255, str));
		}else{
			include_once($config['apanel_dir'].'template.php');
			GenAdminMenu();
			AddTextBox('����� ������ - ������', '<div style="text-align: center;">������ "'.SafeDB($_GET['exe'], 255, str).'" �� ������!</div>');
		}
	}

	if(isset($exe)){
		include_once($config['apanel_dir'].'template.php');
		GenAdminMenu();
		define('MOD_DIR', $config['mod_dir'].$exe.'/');
		define('MOD_FILE', MOD_DIR.'admin.php');
	}
}

// �������� ������������
if($userAuth === 1 && $userAccess === 1 && isset($_COOKIE['admin']) && $user->AllowCookie('admin', true)){
	AdminMain();
}else{
	if(isset($_POST['admin_login'])){
		$admin_name = SafeEnv($_POST['admin_name'], 255, str);
		$admin_password = SafeEnv($_POST['admin_password'], 255, str);
		$a = $user->Login($admin_name, $admin_password, false, true);
		if($a === true && $user->SecondLoginAdmin){
			$user->SetAdminCookie($admin_name, $admin_password);
			GoRefererUrl($_GET['_back']);
		}else{
			$user->UnsetCookie('admin');
			include_once($config['apanel_dir'].'template.login.php');
			AdminShowLogin();
		}
	}else{
		include_once($config['apanel_dir'].'template.login.php');
		AdminShowLogin();
	}
}

include_once($config['inc_dir'].'plugins.inc.php'); // �������

if(is_file(MOD_FILE)){
	include_once(MOD_FILE);
}

// ����� ������
TEcho();

?>