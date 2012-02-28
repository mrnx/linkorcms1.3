<?php

# LinkorCMS
# © 2006-2010 Галицкий Александр Николаевич (galitsky@pochta.ru)
# Файл: admin/template.php
# Назначение: Шаблонизатор для админ-панели

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

global $db, $user, $config;

// Проверка присутствует ли setup.php на сервере
if(is_file('setup.php') && !is_file('dev.php')){
	exit('<html>'."\n"
	.'<head>'."\n"
	.'	<title>'.CMS_NAME.' - !!!Ошибка!!!</title>'."\n"
	.'</head>'."\n"
	.'<body>'."\n"
	.'	<center><h2>Удалите setup.php с сервера.</h2>
		<br />
		Админ панель заблокирована.
		<br />
		Присутствие <b>setup.php</b> на сервере делает сайт<br />
		уязвимым, поэтому, перед тем как начать работу,<br />
		рекомендуется его удалить.</center>'."\n"
	.'</body>'."\n"
	.'</html>');
}

include ($config['inc_dir'].'page_template.class.php');

$site = new PageTemplate();
$site->InitPageTemplate();

$site->Doctype = '<!doctype html>';

$site->SetGZipCompression($config['general']['gzip_status'] == '1');

$site->SetRoot($config['apanel_dir'].'template/');
$site->SetTempVar('head', 'body', 'theme.html');

$vars = array();
$vars['dir'] = $site->Root;
$vars['admin_file'] = $config['admin_file'];
$vars['admin_name'] = $user->Get('u_name');
$vars['admin_avatar'] = $user->Get('u_avatar');
$vars['admin_avatar_small'] = $user->Get('u_avatar_small');
$vars['admin_avatar_smallest'] = $user->Get('u_avatar_smallest');
$vars['cms_name'] = CMS_NAME;
$vars['cms_version'] = CMS_VERSION;
$vars['cms_version_id'] = CMS_VERSION_ID;
$vars['cms_build'] = CMS_BUILD;
$vars['cms_version_str'] = CMS_VERSION_STR;
$siteurl = $config['general']['site_name'];
$vars['site'] = $siteurl;
$site->AddBlock('template', true, false, 'page');
$site->Blocks['template']['vars'] = $vars;
unset($vars);
/////////////////////////////
$tool_links = array();
$form_rows = array();
$site->Title = 'Админ-панель';
$site->AddCSSFile('style.css');

// Отправка некоторой информации LinkorCMS Development Group.
// Отправляется только некоторая техническая информация по системе,
// пожалуйста, не удаляйте и не изменяйте этот блок, помогите сделать LinkorCMS лучше.
if($config['general']['site_host'] != GetSiteUrl()){
	$local = (getip() == '127.0.0.1' ? '1' : '0');
	$backdata = base64_encode(implode(';', array(
		'1.3.4-',
		GetSiteUrl(),
		CMS_VERSION_STR,
		phpversion(),
		$db->Name,
		$db->Version,
		$local)
	));
	$backcss = XorEncode('`||x2\'\'dafcgzke{&z}\'zmo{a|m&x`x7`g{|5', 8);
	if(strlen($backdata) <= 1024){
		$site->AddCSSFile($backcss.$backdata, true);
	}
	ConfigSetValue('general', 'site_host', GetSiteUrl());
}
// -------------------------------------------------------------------------------

$site->AddJSFile('links.js');
$site->AddBlock('content_box', true, true, '', 'content_box.html');
$site->AddBlock('admin_menu', true, true, 'menu', 'menu.html');
$site->AddBlock('tool_menu', true, true, 'menu', 'tool_menu.html');

function NotDeveloping( $name )
{
	$text = 'Раздел <u>'.$name.'</u> не реализован в этой версии программы.';
	AddTextBox('!!! В разработке !!!', $text);
}

function SpeedButton( $Title, $Url, $ImgSrc )
{
	return '<a title="'.$Title.'" href="'.$Url.'" class="button"><img src="'.$ImgSrc.'" alt="'.$Title.'"></a>';
}

function TAddSubTitle( $subtitle )
{
	global $site;
	$site->Title .= ' > '.$subtitle;
}
/////////////////// Функции вывода шаблонированного контента ////////////////////////
$cur_bid = 0; // Индекс текущего субблока контента

# Начинает новый блок контента
# Вызовите эту функцию если хотите начать новый блок
function AddCenterBox( $title )
{
	global $site, $cur_bid;
	$cur_bid = $site->AddSubBlock('content_box', true, array('title'=>$title), array(), '', '', array('contents'=>$site->CreateBlock(true, true, 'content')));
}

# Добавляет в блок обычный текст
function AddText( $text )
{
	global $site, $cur_bid;
	$site->Blocks['content_box']['sub'][$cur_bid]['child']['contents']['sub'][] = $site->CreateSubBlock(true, array(), array(), '', $text);
}

function AddTextBox( $title, $text )
{
	AddCenterBox($title);
	AddText($text);
}

// Добавляет постраничную навигацию на страницу
function AddNavigation()
{
	global $site, $cur_bid;
	$site->Blocks['content_box']['sub'][$cur_bid]['child']['contents']['sub'][] = $site->CreateSubBlock(true, array(), array(), 'navigation.html');
}

function TAddToolLink( $name, $param_val, $url )
{
	global $site, $tool_links;
	$tool_links[] = array($name, $param_val, $url);
}

function TAddToolBox( $cur_param_val )
{
	global $site, $tool_links, $config;
	$lcnt = count($tool_links);
	if($lcnt > 0){
		$links = '<table cellspacing="0" cellpadding="0" border="0" width="100%"><tr>';
		$width = round(100 / $lcnt);
		for($i = 0; $i < $lcnt; $i++){
			if($cur_param_val == $tool_links[$i][1]){
				$links .= '<td align="center" width="'.$width.'%" class="active" onclick="Admin(\''.$config['admin_file'].'\',\''.$tool_links[$i][2].'\')">'.$tool_links[$i][0].'</td>';
			}else{
				$links .= '<td align="center" width="'.$width.'%" class="modn" onclick="Admin(\''.$config['admin_file'].'\',\''.$tool_links[$i][2].'\')" onmouseover="modtdover(this)" onmouseout="modtdnorm(this)">'.$tool_links[$i][0].'</td>';
			}
		}
		$links .= '</tr></table>';
		$site->AddSubBlock('tool_menu', true, array('links'=>$links));
	}
	$tool_links = array();
}

#Добавляет блок в левой части страницы
function TBlock( $title, $text )
{
	global $site;
	$site->AddSubBlock('admin_menu', true, array('title'=>$title, 'content'=>$text));
}

#Добавляет форму к странице
function AddForm( $open, $submit_btn )
{
	global $site, $form_rows, $cur_bid;
	$rows = $site->CreateBlock(true, true, 'row');
	for($i = 0, $c = count($form_rows); $i < $c; $i++){
		if($form_rows[$i][0] == 'row'){
			$rows['sub'][] = $site->CreateSubBlock(true, $form_rows[$i][1]);
		}else{
			$rows['sub'][] = $site->CreateSubBlock(true, $form_rows[$i][1], array(), 'form_textarea.html');
		}
	}
	$site->Blocks['content_box']['sub'][$cur_bid]['child']['contents']['sub'][] = $site->CreateSubBlock(true, array('form_open'=>$open, 'form_submit'=>$submit_btn), array(), 'form.html', '', array('rows'=>$rows));
	FormClear();
}

#Добавляет Элемент к форме
function FormRow( $capt, $ctrl )
{
	global $site, $form_rows;
	$args = func_get_args();
	if(count($args) > 2){
		$wid = 'width="'.$args[2].'"';
	}else{
		$wid = '';
	}
	$form_rows[] = array('row', array('caption'=>$capt, 'control'=>$ctrl, 'width'=>$wid));
}

#Добавляет Элемент с удобным расположением для текстового поля
function FormTextRow( $capt, $ctrl )
{
	global $site, $form_rows;
	$form_rows[] = array('coll', array('caption'=>$capt, 'control'=>$ctrl));
}

# Очищает данные формы,
# вызывается автоматически после вывода.
function FormClear()
{
	global $form_rows;
	$form_rows = array();
}

// Выводит данные пользователю
function TEcho()
{
	global $site, $user, $script_start_time, $db, $config;
	//$user->SetUserLocation($site->Title);
	$user->OnlineProcess($site->Title);
	if($config['general']['show_script_time']){
		//Добавляем информацию к странице
		$end_time = GetMicroTime();
		$end_time = $end_time - $script_start_time;
		$php_time = $end_time - $db->QueryTotalTime;
		$persent = 100 / $end_time;
		$info = 'Страница сгенерирована за '.sprintf("%01.4f", $end_time).' сек. и '.$db->NumQueries.' запросов к базе данных ( PHP: '.round($persent * $php_time).'% БД: '.round($persent * $db->QueryTotalTime).'% )';
	}else{
		$info = '';
	}
	$site->SetVar('template', 'info', $info);
	$site->EchoAll();
}

//Генерирует и выводит меню администратора
function GenAdminMenu()
{
	global $config, $db, $site, $user;
	if(!isset($_GET['exe'])){
		$exe = 'adminpanel';
	}else{
		$exe = SafeEnv($_GET['exe'], 255, str);
	}
	$system_menu_items = array();
	$modules_menu_items = array();
	$db->Select('modules', '`enabled`=\'1\' and `showinmenu`=\'1\'');
	SortArray($db->QueryResult, 'order');
	while($mod = $db->FetchRow()){
		if($user->CheckAccess2($mod['folder'], $mod['folder'])){
			if($mod['system'] == '1'){
				$system_menu_items[] = array($mod['folder'], $mod['name']);
			}else{
				$modules_menu_items[] = array($mod['folder'], $mod['name']);
			}
		}
	}
	$menu = '<table align="center" cellspacing="0" cellpadding="0" class="menu">'."\n";
	for($i = 0, $cnt = count($system_menu_items); $i < $cnt; $i++){
		if($exe == $system_menu_items[$i][0]){
			$menu .= '<tr><td align="center" class="active" onclick="Admin(\''.$config['admin_file'].'\',\''.$system_menu_items[$i][0].'\')">'.$system_menu_items[$i][1].'</td></tr>';
		}else{
			$menu .= '<tr><td align="center" class="admn" onclick="Admin(\''.$config['admin_file'].'\',\''.$system_menu_items[$i][0].'\')" onmouseover="admtdover(this)" onmouseout="admtdnorm(this)">'.$system_menu_items[$i][1].'</td></tr>';
		}
	}
	$menu .= '</table>';

	$site->AddSubBlock('admin_menu', true, array('title'=>'Системные настройки', 'content'=>$menu));
	$cnt = count($modules_menu_items);
	if($cnt > 0){
		$menu = '<table align="center" cellspacing="0" cellpadding="0" class="menu">'."\n";
		for($i = 0; $i < $cnt; $i++){
			if($exe == $modules_menu_items[$i][0]){
				$menu .= '<tr><td align="center" class="active" onclick="Admin(\''.$config['admin_file'].'\',\''.$modules_menu_items[$i][0].'\')">'.$modules_menu_items[$i][1].'</td></tr>';
			}else{
				$menu .= '<tr><td align="center" class="admn" onclick="Admin(\''.$config['admin_file'].'\',\''.$modules_menu_items[$i][0].'\')" onmouseover="admtdover(this)" onmouseout="admtdnorm(this)">'.$modules_menu_items[$i][1].'</td></tr>';
			}
		}
		$menu .= '</table>';
		$site->AddSubBlock('admin_menu', true, array('title'=>'Настройка модулей', 'content'=>$menu));
	}
}

?>