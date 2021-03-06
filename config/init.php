<?php

# LinkorCMS
# � 2006-2010 �������� ��������� ���������� (galitsky@pochta.ru)
# ����: init.php
# ����������: ���� �������������

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

// �������������� ������������
include_once 'config/config.php';

// ��������� ������� LinkorCMS
include_once('config/version.php');
if(isset($_GET['checklcsite'])){
	exit(CMS_VERSION_STR);
}

if(version_compare(phpversion(), '5.0.0', '<')){
	exit('<html>
		<head>
			<title>'.CMS_NAME.' - ������!</title>
		</head>
		<body>
			<center><h2>'.CMS_NAME.': ��������� ������ PHP >= 5.0.0.</h2>
				�� ����������� PHP '.phpversion().'.</center>
		</body>
		</html>');
}

ob_start();

# �������� register_globals = off;
if(ini_get('register_globals') == 1){
	foreach($GLOBALS as $key=>$value){
		if($key != 'GLOBALS' and $key != 'key' and $key != '_REQUEST' and $key != '_GET' and $key != '_POST' and $key != '_COOKIE' and $key != '_SESSION' and $key != '_FILES' and $key != '_ENV' and $key != '_SERVER'){
			unset($GLOBALS[$key]);
		}
	}
	unset($key);
}

# �������� magic_quotes_gpc = off;
if(get_magic_quotes_gpc()){
	function hstripslashes( $var )
	{
		return (is_array($var) ? array_map('hstripslashes', $var) : stripslashes($var));
	}
	$_POST = array_map('hstripslashes', $_POST);
	$_GET = array_map('hstripslashes', $_GET);
}

// �������� ����� ������ ���������� �������
$script_start_time = microtime(true);

$config = array();
$plug_config = array();

$system = array(
	'no_templates'=>false,
	'no_messages'=>false,
	'no_echo'=>false,
	'stop_hit'=>false
);

if(is_file('config/db_config.php')){
	include('config/db_config.php');
}elseif(!defined('SETUP_SCRIPT')){
	Header("Location: setup.php");
	exit();
}

include('config/name_config.php');

// ��������� ������
include_once ($config['inc_dir'].'error_handler.php');


if(!defined('SETUP_SCRIPT')){

	// ��������� ������ ���� ������
	if( !is_file('config/db_config.php') || substr($config['db_version'], 0, 3) != substr(CMS_VERSION, 0, 3)){
		exit('<html>
			<head>
				<title>'.CMS_NAME.' - ������!</title>
			</head>
			<body>
				<center><h2>'.CMS_NAME.': ��������� ���������� ���� ������.</h2></center>
			</body>
			</html>');
	}

	// ��������� ���
	include('config/salt.php');

	//����
	include_once ($config['inc_dir'].'logi.class.php');
	$SiteLog = new Logi($config['log_dir'].'site.log.php');
	$ErrorsLog = new Logi($config['log_dir'].'errors.log.php');
}

?>