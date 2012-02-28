<?php

if(!defined('VALID_RUN')){
	Header("Location: http://".getenv("HTTP_HOST")."/index.php");
	exit;
}

global $site, $config, $plug_config, $include_plugin_path;

$textarea_name = $site->textarea_name;
$sitecss = $config['tpl_dir'].$config['general']['site_template'].'/style/textstyles.css';
$theme = $plug_config['editors.tiny_mce']['theme'];

$buttons = array();
$buttons[] = $plug_config['editors.tiny_mce']['theme_advanced_buttons1'];
$buttons[] = $plug_config['editors.tiny_mce']['theme_advanced_buttons2'];
$buttons[] = $plug_config['editors.tiny_mce']['theme_advanced_buttons3'];
$buttons[] = $plug_config['editors.tiny_mce']['theme_advanced_buttons4'];

$jk = 0;
$buttons_text = '';
foreach($buttons as $panels){
	if($panels != ''){
		$jk++;
		$buttons_text .= "\t\t".'theme_advanced_buttons'.$jk.' : "'.$panels.'",'."\n";
	}
}		

$js =<<<JS
	tinyMCE.init({
		mode : "exact",
		elements : "$textarea_name",
		theme : "$theme",
		skin : "o2k7",
		skin_variant : "black",
		language: "ru",

		plugins : "images,safari,style,table,save,advhr,advimage,advlink,iespell,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,inlinepopups",

		// Редактирование
		force_p_newlines : 0,
		forced_root_block:"",
		
		
		// Path
		relative_urls : false,
		remove_script_host : true,

		// Theme options
$buttons_text

		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true,

		content_css : "$sitecss"

	});

JS;

$site->AddJSFile($config['plug_dir'].'editors/tiny_mce/tiny_mce.js',true);
$site->AddJS($js);

$site->textarea_html = $site->TextArea($textarea_name, $site->textarea_value, 'id="'.$textarea_name.'"  rows="15" cols="80" style="width:'.$site->textarea_width.'px;height:'.$site->textarea_height.'px;"');

?>