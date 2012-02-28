<?php

if(!defined('VALID_RUN')){
	Header("Location: http://".getenv("HTTP_HOST")."/index.php");
	exit;
}

global $site, $config, $plug_config, $include_plugin_path;

$textarea_name = $site->textarea_name;

$js =<<<JS
	var editor = CodeMirror.fromTextArea('$textarea_name', {
		width: "{$site->textarea_width}px",
		height: "{$site->textarea_height}px",
		parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js", "parsehtmlmixed.js"],
		stylesheet: ["{$include_plugin_path}css/jscolors.css", "{$include_plugin_path}css/csscolors.css", "{$include_plugin_path}css/xmlcolors.css"],
		path: "{$include_plugin_path}js/",
		lineNumbers: true,
		textWrapping: true
	});
JS;

$site->AddCSSFile('plugins/editors/codemirror/css.css', true, true);
$site->AddJSFile($config['plug_dir'].'editors/codemirror/js/codemirror.js',true);

$site->textarea_html = '<div style="border: 1px solid #AAAAFF; padding: 0px; background-color: #FFF;">'
.$site->TextArea($textarea_name, $site->textarea_value, 'id="'.$textarea_name.'"')
.'</div>'
.'<script type="text/javascript">'
.$js
.'</script>';

?>