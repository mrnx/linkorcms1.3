<?php

# LinkorCMS
# � 2006-2009 �������� ��������� ���������� (galitsky@pochta.ru)
# ����: index.php
# ����������: �����������

if($_SERVER['REQUEST_METHOD'] == "HEAD"){ // ��������� HEAD �������
	header("X-Request: HEAD");
	exit();
}

define("SETUP_SCRIPT", true);
define("VALID_RUN", true);

@set_time_limit(600);

include_once('config/init.php');
error_reporting(E_ALL);

$default_prefix = 'table';
$bases_path = 'setup/bases/';
$info_ext = '.MYD';
$data_ext = '.FRM';

$config['s_dir'] = 'setup/';
$config['s_plug_dir'] = 'setup/plugins/';
$config['s_inc_dir'] = 'setup/inc/';
$config['s_lng_dir'] = 'setup/lng/';
$config['s_mod_dir'] = 'setup/mods/';
$config['s_tpl_dir'] = 'setup/template/';

include_once($config['inc_dir'].'system_plugins.inc.php'); //��������� �������
include_once($config['inc_dir'].'system.php'); //�������
include_once($config['inc_dir'].'user.class.php'); //������
include_once($config['s_inc_dir'].'functions.php');
include_once($config['s_inc_dir'].'template.php');// ������

$site->AddJSFile($config['s_inc_dir'].'functions.js', true, true);

include_once($config['s_inc_dir'].'setup.class.php'); // ����� ���������� �������������
include_once($config['s_inc_dir'].'plugins.php'); // ��������� ��������
include_once($config['s_lng_dir'].'lang-russian.php'); // ���������������

if(isset($_GET['mod'])){
	$mod = SafeEnv($_GET['mod'], 255, str);
}else{
	$mod = '';
}

$setup->Page($mod);

?>