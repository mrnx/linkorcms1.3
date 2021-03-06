<?php

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

global $db, $user, $config;

$vars['title'] = $title;
$tempvars['content'] = 'block/content/menu.html';

$uri = $_SERVER['REQUEST_URI'];

$bcache = LmFileCache::Instance();
$bcache_name = 'menu'.$user->AccessLevel();
if($bcache->HasCache('block', $bcache_name)){
	$block_menu_items = $bcache->Get('block', $bcache_name);
	foreach($block_menu_items['sub'] as $k=>$item){
		$block_menu_items['sub'][$k]['vars']['selected'] = (strpos($uri, $item['vars']['link']) !== false);
		foreach($item['child']['block_menu_subitems']['sub'] as $n=>$subitem){
			$block_menu_items['sub'][$k]['child']['block_menu_subitems']['sub'][$n]['vars']['selected'] = (strpos($uri, $subitem['vars']['link']) !== false);
		}
	}
	$childs['block_menu_items'] = $block_menu_items;
	return;
}

$where = "`enabled`='1'";
$w2 = GetWhereByAccess('view');
if($w2 != ''){
	$where .= ' and ('.$w2.')';
}
$pages = $db->Select('pages', $where);
SortArray($pages, 'order');

$catsPid = array();
$catsId = array();
foreach($pages as $page){
	$catsPid[$page['parent']][] = $page;
}

if(isset($catsPid[0])){
	$pages = $catsPid[0];
}else{
	$pages = array();
}

$block_menu_items = $site->CreateBlock(true, true, 'menu_item');
foreach($pages as $page){
	if($page['showinmenu'] == '1'){
		if($page['type'] == 'page'){
			$link = Ufu('index.php?name=pages&file='.SafeDB($page['link'], 255, str), 'pages/{file}.html');
		}elseif($page['type'] == 'link'){
			$link = SafeDB($page['text'], 255, str);
			if(substr($link, 0, 6) == 'mod://'){
				$link = Ufu('index.php?name='.substr($link, 6), '{name}/');
			}
		}
		$vars_item = array('title'=>$page['title'], 'link'=> str_replace('&amp;', '&', $link), 'subitems'=>false);
		$vars_item['selected'] = (strpos($uri, $link) !== false);

		$block_menu_subitems = $site->CreateBlock(true, true, 'menu_subitem');
		if(isset($catsPid[$page['id']]) && $page['showinmenu'] == '1'){
			$subpages = $catsPid[$page['id']];
			$vars_item['subitems'] = count($subpages) > 0;
			foreach($subpages as $subpage){
				if($subpage['showinmenu'] == '1'){
					if($subpage['type'] == 'page'){
						$link = Ufu('index.php?name=pages&file='.SafeDB($subpage['link'], 255, str), 'pages/{file}.html');
					}elseif($subpage['type'] == 'link'){
						$link = SafeDB($subpage['text'], 255, str);
						if(substr($link, 0, 6) == 'mod://'){
							$link = Ufu('index.php?name='.substr($link, 6), '{name}/');
						}
					}
					$selected = strpos($uri, $link) !== false;
					$vars_subitem = array('title'=>$subpage['title'], 'link'=>str_replace('&amp;', '&', $link));
					$vars_subitem['selected'] = (strpos($uri, $link) !== false);
					$block_menu_subitems['sub'][] = $site->CreateSubBlock(true, $vars_subitem);
				}
			}
		}
		$menu_item = $site->CreateSubBlock(true, $vars_item, array(), '', '', array('block_menu_subitems'=>$block_menu_subitems));
		$block_menu_items['sub'][] = $menu_item;
	}
}

$childs['block_menu_items'] = $block_menu_items;
$bcache->Write('block', $bcache_name, $block_menu_items);

?>