<?php

# LinkorCMS
# � 2006-2010 �������� ��������� ���������� (galitsky@pochta.ru)
# ����: logi.inc.php
# ����������: ���� ������������� �����

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

include_once ($config['inc_dir'].'logi.class.php');
$SiteLog = new Logi($config['log_dir'].'site.log.php');
$ErrorsLog = new Logi($config['log_dir'].'errors.log.php');
include_once ($config['inc_dir'].'error_handler.php');

?>