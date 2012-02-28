<?php

# LinkorCMS
# © 2006-2010 Галицкий Александр Николаевич (galitsky@pochta.ru)
# Файл: logi.inc.php
# Назначение: Файл инициализации логов

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

include_once ($config['inc_dir'].'logi.class.php');
$SiteLog = new Logi($config['log_dir'].'site.log.php');
$ErrorsLog = new Logi($config['log_dir'].'errors.log.php');
include_once ($config['inc_dir'].'error_handler.php');

?>