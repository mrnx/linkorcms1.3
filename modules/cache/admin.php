<?php

// ������ ��� ������� ����
TAddSubTitle('���������� �����');

function AdminCache( $action )
{
	switch ($action){
		case 'main':
			AdminCacheMain();
			break;
		case 'clear':
			AdminCacheClean();
			break;
		case 'cleanup':
			AdminCacheCleanup();
			break;
		}
}

if(isset($_GET['a'])){
	AdminCache($_GET['a']);
}else{
	AdminCache('main');
}

function AdminCacheMain()
{
	global $config;
	$cache = LmFileCache::Instance();
	$groups = $cache->GetGroups();

	if(!$cache->Enabled){
		if(USE_CACHE){
			AddTextBox('��������', '<h2 style="color: #FF0000;">��������! ����� "'.$cache->Path.'" �� �������� ��� ������. ������� ����������� ���������.</h2>');
		}else{
			AddTextBox('��������', '<h2 style="color: #FF0000;">������� ����������� ��������� � ���������������� ����� "config/config.php".</h2>');
		}
	}

	AddCenterBox('���������� �����');
	$text = '<table cellspacing="0" cellpadding="0" class="cfgtable">';
	$text .= '<tr><th>������</th><th>�����</th><th>�������</th><th>���������� �����</th><th>�������</th></tr>';

	$num_rows = 0;
	$total_size = 0;
	foreach($groups as $g){

		$file_size = 0;
		$num_files = 0;
		$folder = $cache->Path.$g;
		$files = scandir($folder);
		foreach($files as $file){
			if (($file!='.') && ($file!='..')){
				$f = $folder.'/'.$file;
				if(!is_dir($f)){
					$file_size += filesize($f);
				}
				$num_files++;
			}
		}

		$func = SpeedButton('��������', $config['admin_file'].'?exe=cache&a=clear&group='.SafeDB($g, 255, str), 'images/admin/cleanup.png');

		$rows = floor($num_files / 2);
		$text .= '<tr>'
			.'<td>'.SafeDB($g, 255, str).'</td>'
			.'<td>'.SafeDB($folder, 255, str).'</td>'
			.'<td>'.$rows.'</td>'
			.'<td>'.FormatFileSize($file_size).'</td>'
			.'<td>'.$func.'</td>'
			.'</tr>';
		$num_rows += $rows;
		$total_size += $file_size;
	}

	$text .= '</table><br />';
	$text .= '����� <b>'.count($groups).'</b> �����(�), <b>'.$num_rows.'</b> ������� � <b>'.FormatFileSize($total_size).'</b> ������.<br />';
	$text .= '<a href="'.$config['admin_file'].'?exe=cache&a=cleanup">�������� ��� ������.</a><br /><br />';

	AddText($text);
}

function AdminCacheClean()
{
	$group = $_GET['group'];
	$cache = LmFileCache::Instance();
	$cache->Clear($group);
	AdminCacheMain();
}

function AdminCacheCleanup()
{
	$cache = LmFileCache::Instance();
	$groups = $cache->GetGroups();
	foreach($groups as $g){
		$cache->Clear($g);
	}
	AdminCacheMain();
}

?>