<?php

//Корневая директория сайта
define('DIR_ROOT', $_SERVER['DOCUMENT_ROOT']);

$path = dirname(__FILE__);
$path = str_replace('\\','/',$path);
$path = str_replace(DIR_ROOT,"",$path);
$path = str_replace("plugins/editors/tiny_mce/plugins/images/connector/php","",$path);

//Директория с изображениями (относительно корневой)
define('DIR_IMAGES', $path.'/uploads/storage');
//Директория с файлами (относительно корневой)
define('DIR_FILES', $path.'/uploads/storage');


//Высота и ширина картинки до которой будет сжато исходное изображение и создана ссылка на полную версию
define('WIDTH_TO_LINK', 300);
define('HEIGHT_TO_LINK', 300);

//Атрибуты которые будут присвоены ссылке (для скриптов типа lightbox)
define('CLASS_LINK', 'lightview');
define('REL_LINK', 'lightbox');

?>
