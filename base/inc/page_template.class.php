<?php

# LinkorCMS 1.3
# © 2006-2010 Галицкий Александр Николаевич (galitsky@pochta.ru)
# Файл: page_template.class.php
# Назначение: Общий шаблонизатор

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

include ($config['inc_dir'].'starkyt.class.php'); //class Starkyt

class PageTemplate extends Starkyt
{
	public $Title = ''; //Заголовок страницы
	public $Icon = ''; // Иконка для сайта
	//<link rel="shortcut icon" href="favicon.ico">

	public $Doctype = '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">';
	//Тег валидатора определяющего версию языка разметки HTML

	// Модуль MetaTags
	public $Charset = ''; //Определяет тип содержимого и кодировку
	//<meta http-equiv="content-type" content="text/html; charset=Windows-1251">

	public $Copyright = ''; //Определяет авторские права на страницу
	//<meta name="copyright" content="© 2006 www.yoursite.ru">

	public $ContentLang = ''; //Определяет язык сайта
	//<meta http-equiv="content-language" content="ru">

	public $Rating = 'general'; //Роботы
	//<meta name="rating" content="general">
	//Одно из 4-х значений: 'general', '14 years', 'restricted', 'mature'.

	public $Robots = ''; //<meta name="robots" content="index, follow">
	//<meta name="robots" content="noindex, nofollow">

	public $Generator = ''; //<meta name="generator" content="Cms">
	public $KeyWords = ''; //Ключевые слова (разделять запятой)
	//<meta name="keywords" content="слово,слово,слово">

	public $Description = ''; //Описание
	//<meta name="description" content="Здесь ваше описание">

	public $Author = ''; //Автор страницы/сайта/содержимого
	//<meta name="author" content="Ваше имя">

	public $RevisitAfter = 0; //Периодичность обхода роботами поисковых систем в днях
	//<meta name="revisit-after" content="X days">

	public $OtherMeta = ''; // Дополнительные мета-теги.

	// Модуль подключения RSS
	public $RssTitle = ''; // Заголовок RSS-канала
	public $RssLink = ''; // Ссылка на RSS-канал
	//<LINK REL="alternate" TYPE="application/rss+xml" TITLE="title" HREF="link">

	// Модуль SEO
	public $SeoTitle = '';
	public $SeoDescription = '';
	public $SeoKeyWords = '';

	// Модуль CSS и JavaScript
	private $css = array(); //Имена файлов css которые следует подключить
	private $js = array(); //Имена файлов JavaScript которые следует подключить
	private $TextJavaScript = ''; //Сюда записывается javaScript который потом вставится в шапку страницы

	// Модуль WYSIWYG редактор
	private $HtmlAreaInit = false;


	// Модуль GZip
	private $GZipCompressPage = false;
	private $SupportGZip = false;
	private $print_log = false; // Вывести ли лог компиляции. Может помочь при отладке новых шаблонов.

	public function InitPageTemplate()
	{
		global $config;
		if(!ob_get_level()){
			ob_start();
		}
		@Header('Expires: Mon, 1 Jan 2006 00:00:00 GMT');
		@Header('Last-Modified:'.gmdate('D, d M Y H:i:s').' GMT');
		@Header('Cache-Control: no-store, no-cache, must-revalidate');
		@Header('Pragma: no-cache');
		$this->AddBlock('head');
		$this->InitStarkyt($config['inc_dir'], 'page.php');
		$this->Charset = 'windows-1251';
		$this->Generator = CMS_NAME.' '.CMS_VERSION.' '.CMS_BUILD;
		if(isset($config['meta_tags'])){
			$this->Author = $config['meta_tags']['author'];
			$this->Copyright = $config['meta_tags']['copyright'];
			$this->Description = $config['meta_tags']['description'];
			$this->KeyWords = $config['meta_tags']['key_words'];
			$this->Robots = $config['meta_tags']['robots'];
			$this->RevisitAfter = $config['meta_tags']['revisit_after'];
			$this->Icon = $config['meta_tags']['favicon'];
			$this->OtherMeta = $config['meta_tags']['other_meta'];
		}
	}

	public function SetGZipCompression( $value )
	{
		if(isset($_SERVER['HTTP_ACCEPT_ENCODING']) && extension_loaded('zlib')){
			$AllowGZ = $_SERVER['HTTP_ACCEPT_ENCODING'];
		}else{
			$AllowGZ = '';
		}
		$this->GZipCompressPage = $value;
		$this->SupportGZip = (strpos($AllowGZ, 'gzip') !== false) && $value;
	}

	//Вставляет редактор HTML-контента на страницу, поддерживает плагины
	public function HtmlEditor( $textarea_name, $value, $width = 600, $height = 400 )
	{
		//Загружаем плагины
		$this->textarea_name = $textarea_name;
		$this->textarea_html = $this->TextArea($textarea_name, $value, 'id="'.$textarea_name.'"  rows="15" cols="80" style="width:'.$width.'px;height:'.$height.'px;"');
		$this->textarea_width = $width;
		$this->textarea_height = $height;
		$this->textarea_value = & $value;
		if(defined('PLUGINS')){
			IncludePluginsGroup('editors');
		}
		return $this->textarea_html;
	}

	public function AddCSSFile( $filename, $local = false, $inc = false )
	{
		if(!$local){
			$filename = $this->Root.'style/'.$filename;
		}
		$this->css[] = array('filename'=>$filename, 'inc'=>$inc);
	}

	public function AddJSFile( $filename, $local = false, $inc = false )
	{
		if(!$local){
			$filename = $this->Root.'java/'.$filename;
		}
		$this->js[] = array('filename'=>$filename, 'inc'=>$inc);
	}

	public function AddJS( $JsText )
	{
		$this->TextJavaScript .= "\n".$JsText."\n";
	}

	public function Body( $body )
	{
		$this->CompileTo($this->head, 'body', $body);
	}

	public function Seo( $Title, $KeyWords, $Description )
	{
		$this->SeoTitle = $Title;
		$this->SeoKeyWords = $KeyWords;
		$this->SeoDescription = $Description;
	}

	public function SetTitle( $Title )
	{
		$this->Title = $Title;
	}

	public function EchoAll()
	{
		global $script_start_time, $config;

		if(defined('INDEX_PHP') && INDEX_PHP == true){ // Заголовок главной страницы
			$title = $config['general']['site_name'].($config['general']['main_title'] != '' ? ' - '.$config['general']['main_title'] : '');
		}elseif($this->SeoTitle != ''){
			$title = $this->SeoTitle.' - '.$config['general']['site_name'];
		}else{
			$title = ($this->Title != '' ? $this->Title.' - ' : '').$config['general']['site_name'];
		}
		$this->SetVar('head', 'title', $title);

		$this->SetVar('head', 'doctype', $this->Doctype);
		$Meta = '';
		$Head = '';
		$Head = '<base href="'.GetSiteUrl().'" />'."\n";
		//Генерируем шапку
		if($this->Charset != ''){
			$Meta .= '<meta http-equiv="content-type" content="text/html; charset='.$this->Charset.'" />'."\n";
		}
		if($this->Copyright != ''){
			$Meta .= '<meta name="copyright" content="'.$this->Copyright.'" />'."\n";
		}
		if($this->ContentLang != ''){
			$Meta .= '<meta http-equiv="content-language" content="'.$this->ContentLang.'" />'."\n";
		}
		if($this->Rating != ''){
			$Meta .= '<meta name="rating" content="'.$this->Rating.'" />'."\n";
		}
		if($this->Robots != ''){
			$Meta .= '<meta name="robots" content="'.$this->Robots.'" />'."\n";
		}
		if($this->Generator != ''){
			$Meta .= '<meta name="generator" content="'.$this->Generator.'" />'."\n";
		}
		if($this->SeoKeyWords != ''){
			$Meta .= '<meta name="keywords" content="'.$this->SeoKeyWords.'" />'."\n";
		}elseif($this->KeyWords != ''){
			$Meta .= '<meta name="keywords" content="'.$this->KeyWords.'" />'."\n";
		}
		if($this->SeoDescription != ''){
			$Meta .= '<meta name="description" content="'.$this->SeoDescription.'" />'."\n";
		}elseif($this->Description != ''){
			$Meta .= '<meta name="description" content="'.$this->Description.'" />'."\n";
		}
		if($this->Author != ''){
			$Meta .= '<meta name="author" content="'.$this->Author.'" />'."\n";
		}
		if($this->RevisitAfter != 0){
			$Meta .= '<meta name="revisit-after" content="'.$this->RevisitAfter.' days" />'."\n";
		}
		if($this->Icon != ''){
			$Meta .= '<link rel="shortcut icon" href="'.$this->Icon.'" />'."\n";
		}
		if($this->RssTitle != '' && $this->RssLink != ''){
			$Meta .= '<link rel="alternate" type="application/rss+xml" title="'.$this->RssTitle.'" href="'.$this->RssLink.'" />'."\n";
		}
		$Meta .= $this->OtherMeta."\n";
		//Подключаем таблицы стилей
		for($i = 0, $cnt = count($this->css); $i < $cnt; $i++){
			if(($this->css[$i]['inc'] == '0')){
				$Head .= '<link rel="StyleSheet" href="'.$this->css[$i]['filename'].'" type="text/css" />'."\n";
			}else{
				$Head .= "<style>\n".file_get_contents($this->css[$i]['filename'])."\n</style>\n";
			}
		}
		//Подключаем скрипты
		for($i = 0, $cnt = count($this->js); $i < $cnt; $i++){
			if(($this->js[$i]['inc'] == '0')){
				$Head .= '<script src="'.$this->js[$i]['filename'].'" type="text/javascript"></script>'."\n";
			}else{
				if(file_exists($this->js[$i]['filename'])){
					$this->TextJavaScript = "\n".file_get_contents($this->js[$i]['filename'])."\n".$this->TextJavaScript;
				}
			}
		}
		if($this->TextJavaScript != ''){
			$Head .= "<script type=\"text/javascript\">\n<!--\n".$this->TextJavaScript."\n//-->\n</script>\n";
		}
		$this->SetVar('head', 'meta', $Meta);
		unset($Meta);
		$this->SetVar('head', 'text', $Head);
		unset($Head);
		$gzip_contents = $this->Compile(); // Компиляция всей страницы
		global $ErrorsNum;
		if($ErrorsNum > 0){
			$gzip_contents = $gzip_contents;
		}
		if(ob_get_length() > 0 || ob_get_level() > 0){
			$gzip_contents = ob_get_contents().$gzip_contents;
			ob_end_clean();
		}
		if($this->SupportGZip){
			@Header('Content-Encoding: gzip');
			preg_match_all("{[\w\-]+}", $_SERVER["HTTP_ACCEPT_ENCODING"], $matches);
			$encoding = false;
			if(in_array("x-gzip", $matches[0]))
				$encoding = "x-gzip";
			if(in_array("gzip", $matches[0]))
				$encoding = "gzip";
			if($encoding !== false && function_exists("gzcompress")){
				$gzip_size = strlen($gzip_contents);
				$gzip_crc = crc32($gzip_contents);
				$gzip_contents = gzcompress($gzip_contents, 9);
				$gzip_contents = substr($gzip_contents, 0, strlen($gzip_contents) - 4);
				echo "\x1f\x8b\x08\x00\x00\x00\x00\x00";
				echo $gzip_contents;
				echo pack('V', $gzip_crc);
				echo pack('V', $gzip_size);
			}else{
				echo $gzip_contents;
			}
		}else{
			echo $gzip_contents;
		}
	}

}

?>
