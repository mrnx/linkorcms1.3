<?php

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

TAddSubTitle('�����');

if(!$user->CheckAccess2('blocks', 'blocks')){
	AddTextBox('������', $config['general']['admin_accd']);
	return;
}

function AdminBlocks( $action )
{
	switch($action){
		case 'main':
			AdminBlocksMain();
			break;
		case 'add':
			AdminBlocksEdit($action);
			break;
		case 'newsave':
			AdminBlocksSave($action);
			break;
		case 'del':
			AdminBlockDelete();
			break;
		case 'edit':
			AdminBlocksEdit($action);
			break;
		case 'update':
			AdminBlocksSave($action);
			break;
		case 'changestatus':
			AdminBlocksChangeStatus();
			break;
		case 'move':
			AdminBlocksMove();
			break;
		default:
			AdminBlocksMain();
	}
}

if(isset($_GET['a'])){
	AdminBlocks($_GET['a']);
}else{
	AdminBlocks('main');
}

function GetPlace( $pos, $id )
{
	global $config, $db;
	$db->Select('blocks', "`position`='".$pos."'");
	while($row = $db->FetchRow()){
		if(SafeDB($row['id'], 11, int) == $id){
			return SafeDB($row['place'], 11, int);
		}
	}
	return $db->NumRows();
}

function AdminBlocksMain()
{
	global $config, $db, $site;
	$db->Select('block_types', '');
	while($type = $db->FetchRow()){
		$types[SafeDB($type['folder'], 255, str)] = SafeDB($type['name'], 255, str);
	}
	unset($type);
	$b_pos = array('L', 'R', 'T', 'B');
	$text = '';
	$text .= '<table cellspacing="0" cellpadding="0" class="cfgtable">'
	.'<tr>
		<th style="background-repeat: no-repeat;">���������</th>
		<th>���������</th>
		<th>���</th>
		<th>��� �����</th>
		<th>������</th>
		<th>�������</th>
	</tr>';

	for($i = 0; $i < 4; $i++){
		switch($b_pos[$i]){
			case 'L':
				$pos = '����� �����';
				break;
			case 'R':
				$pos = '������ �����';
				break;
			case 'T':
				$pos = '������� �����';
				break;
			case 'B':
				$pos = '������ �����';
				break;
		}
		$db->Select('blocks', "`position`='".$b_pos[$i]."'");
		$maxplace = $db->NumRows() - 1;
		if($maxplace + 1 > 0){
			$text .= '<tr><th colspan="6">'.$pos.'</th></tr>';
		}
		usort($db->QueryResult, 'AdminBlocksSort');
		while($block = $db->FetchRow()){
			$block_id = SafeDB($block['id'], 11, int);
			switch($block['enabled']){
				case "1":
					$st = '<a href="'.$config['admin_file'].'?exe=blocks&a=changestatus&id='.$block_id.'" title="�������� ������"><font color="#008000">���.</font></a>';
					break;
				case "0":
					$st = '<a href="'.$config['admin_file'].'?exe=blocks&a=changestatus&id='.$block_id.'" title="�������� ������"><font color="#FF0000">����.</font></a>';
					break;
			}
			$vi = ViewLevelToStr(SafeDB($block['view'], 1, int));

			$move_menu = '';
			if($maxplace == 0){ // ������������ ������� � ������
				$move_menu .= ' - ';
			}else{
				if($block['place'] >= 0 && $block['place'] < $maxplace){ // ������ �������
					$move_menu .= SpeedButton('����', $config['admin_file'].'?exe=blocks&a=move&to=down&id='.$block_id, 'images/admin/down.png');
				}
				if($block['place'] <= $maxplace && $block['place'] > 0){
					$move_menu .= SpeedButton('�����', $config['admin_file'].'?exe=blocks&a=move&to=up&id='.$block_id, 'images/admin/up.png');
				}
			}

			$func = '';
			$func .= SpeedButton('�������������', $config['admin_file'].'?exe=blocks&a=edit&id='.$block_id, 'images/admin/edit.png');
			$func .= SpeedButton('�������', $config['admin_file'].'?exe=blocks&a=del&id='.$block_id.'&ok=0', 'images/admin/delete.png');

			$text .= '
			<tr>
			<td><a href="'.$config['admin_file'].'?exe=blocks&a=edit&id='.$block_id.'">'.'<b>'.SafeDB($block['title'], 255, str).'</b></a></td>
			<td>'.$move_menu.'</td>
			<td>'.$types[SafeDB($block['type'], 255, str)].'</td>
			<td>'.$vi.'</td>
			<td>'.$st.'</td>
			<td>'.$func.'</td>
			</tr>';
		}
	}
	$text .= '</table><br />';
	$db->Select('block_types', '');
	while($row = $db->FetchRow()){
		$site->DataAdd($btd, SafeDB($row['folder'], 255, str), SafeDB($row['name'], 255, str));
	}
	FormRow('���', $site->Select('type', $btd, false, 'style="width:200px;"'), 60);
	$text .= '.:�������� ����:.';
	AddCenterBox('�����');
	AddText($text);
	AddForm('<form action="'.$config['admin_file'].'?exe=blocks&a=add" method="post">', $site->Submit('�����'));
}

function AdminBlocksEdit( $a )
{
	global $config, $db, $site;
	$text = '';
	$title = '';
	$button = 'OK';
	$template = '';
	if(isset($_POST['type']) || $a == 'edit'){
		$b_pos = array('L'=>false, 'R'=>false, 'T'=>false, 'B'=>false);
		$b_vi = array('1'=>false, '2'=>false, '3'=>false, '4'=>false);
		$b_title = '';
		$b_en = false;
		if($a == 'edit'){
			$db->Select('blocks', "`id`='".SafeEnv($_GET['id'], 11, int)."'");
			$r = $db->FetchRow();
			$b_title = SafeDB($r['title'], 255, str);
			$b_pos[SafeDB($r['position'], 1, str)] = true;
			$b_vi[SafeDB($r['view'], 1, int)] = true;
			$b_en = !SafeDB($r['enabled'], 1, bool);
			$block_config = $r['config'];
			$b_type = SafeDB($r['type'], 255, str);
			$template = SafeDB($r['template'], 255, str);
			$title = '�������������� �����';
			$a_form = $config['admin_file'].'?exe=blocks&a=update&id='.SafeEnv($_GET['id'], 11, int);
			$button = '��������� ���������';
		}else{
			$a_form = $config['admin_file'].'?exe=blocks&a=newsave';
			$b_type = SafeEnv($_POST['type'], 255, str);
			$b_vi[4] = true;
			$title = '������������ �����';
			$button = '�������';
		}
		unset($r);
		FormRow('���������', $site->Edit('title', $b_title, false, 'style="width:200px;"'));
		$constructor = $config['blocks_dir'].$b_type.'/constructor.php';
		if(is_file($constructor)){
			include_once ($constructor);
		}
		$btems = GetBlockTemplates();
		$temdata = array();
		foreach($btems as $tem){
			$site->DataAdd($temdata, $tem, $tem, $tem == $template);
		}
		FormRow('������ �����', $site->Select('template', $temdata));
		$site->DataAdd($posdata, 'Left', '������� �����', $b_pos['L']);
		$site->DataAdd($posdata, 'Right', '������� ������', $b_pos['R']);
		$site->DataAdd($posdata, 'Top', '� ������ ������', $b_pos['T']);
		$site->DataAdd($posdata, 'Bottom', '� ������ �����', $b_pos['B']);
		FormRow('����������������', $site->Select('position', $posdata));
		$site->DataAdd($visdata, 'admins', '������ ��������������', $b_vi['1']);
		$site->DataAdd($visdata, 'members', '������ ������������', $b_vi['2']);
		$site->DataAdd($visdata, 'guests', '������ �����', $b_vi['3']);
		$site->DataAdd($visdata, 'all', '���', $b_vi['4']);
		FormRow('��� �����', $site->Select('view', $visdata));
		$site->DataAdd($endata, 'on', '��');
		$site->DataAdd($endata, 'off', '���', $b_en);
		FormRow('��������', $site->Select('enabled', $endata));
		AddCenterBox($title);
		AddForm('<form action="'.$a_form.'" method="post">'.$site->Hidden('type', $b_type), $site->Button('������', 'onclick="history.go(-1);"').$site->Submit($button));
	}else{
		GO($config['admin_file']);
	}
}

function AdminBlocksSave( $a )
{
	global $config, $db;
	$block_config = '';
	$editsave = $config['blocks_dir'].SafeEnv($_POST['type'], 255, str).'/editsave.php';
	if(file_exists($editsave)){
		include_once ($editsave); // ���-�� ���������� � $block_config
	}
	switch($_POST['view']){
		case 'admins':
			$b_v = 1;
			break;
		case 'members':
			$b_v = 2;
			break;
		case 'guests':
			$b_v = 3;
			break;
		case 'all':
			$b_v = 4;
			break;
	}
	switch($_POST['enabled']){
		case 'on':
			$b_en = 1;
			break;
		case 'off':
			$b_en = 0;
			break;
	}
	if(isset($_GET['id'])){
		$id = SafeEnv($_GET['id'], 11, int);
	}else{
		$id = 0;
	}
	$place = GetPlace(SafeEnv($_POST['position'][0], 1, str), $id);
	if($id != 0){
		$block = $db->Select('blocks', "`id`='$id'");
	}
	$vals = Values('', SafeEnv($_POST['title'], 255, str), SafeEnv($_POST['type'], 255, str), $place, '', '1', $block_config, SafeEnv($_POST['template'], 255, str), SafeEnv($_POST['position'][0], 1, str), $b_v, $b_en);
	if($a == 'newsave'){
		$db->Insert('blocks', $vals);
	}elseif($a == 'update'){
		$db->Update('blocks', $vals, "`id`='".$id."'", true);
		if($block[0]['position'] != $_POST['position'][0]){ // ������������� ��� order � ���������� position
			$blocks = $db->Select('blocks', "`position`='".SafeEnv($block[0]['position'], 1, str)."'");
			if(count($blocks) > 0){
				usort($blocks, 'AdminBlocksSort');
				foreach($blocks as $i=>$b){
					$db->Update('blocks', "`place`='$i'", "`id`='".SafeEnv($b['id'], 11, int)."'");
				}
			}
		}
	}
	GO($config['admin_file'].'?exe=blocks');
}

function AdminBlockDelete()
{
	global $config, $db;
	if(isset($_GET['ok']) && $_GET['ok'] == '1'){
		$db->Delete('blocks', "`id`='".SafeEnv($_GET['id'], 11, int)."'");
		GO($config['admin_file'].'?exe=blocks');
		exit();
	}else{
		$r = $db->Select('blocks', "`id`='".SafeEnv($_GET['id'], 11, int)."'");
		$text = '�� ������������� ������ ������� ���� "'.$r[0]['title'].'"<br />'.'<a href="'.$config['admin_file'].'?exe=blocks&a=del&id='.SafeEnv($_GET['id'], 11, int).'&ok=1">��</a> &nbsp;&nbsp;&nbsp; <a href="javascript:history.go(-1)">���</a>';
		AddTextBox("��������", $text);
	}
}

function AdminBlocksChangeStatus()
{
	global $config, $db;
	$db->Select('blocks', "`id`='".SafeEnv($_GET['id'], 11, int)."'");
	if($db->NumRows() > 0){
		$r = $db->FetchRow();
		if(SafeDB($r['enabled'], 1, int) == 1){
			$en = '0';
		}else{
			$en = '1';
		}
		$db->Update('blocks', "enabled='$en'", "`id`='".SafeEnv($_GET['id'], 11, int)."'");
	}
	GO($config['admin_file'].'?exe=blocks');
}

function AdminBlocksSort( $a, $b )
{
	if($a['place'] == $b['place'])
		return 0;
	return ($a['place'] < $b['place']) ? -1 : 1;
}

function AdminBlocksMove()
{
	global $config, $db;
	$move = SafeEnv($_GET['to'], 4, str);
	$id = SafeEnv($_GET['id'], 11, int);
	$db->Select('blocks', "`id`='".$id."'");
	if($db->NumRows() > 0){
		$block = $db->FetchRow();
		$pos = SafeDB($block['place'], 255, str);
		$blocks = $db->Select('blocks', "`position`='".SafeDB($block['position'], 1, str)."'");
		usort($blocks, 'AdminBlocksSort');
		$c = count($blocks);
		$cur_pos = 0;
		for($i = 0; $i < $c; $i++){
			$blocks[$i]['place'] = $i;
			if($blocks[$i]['id'] == $id){
				$cur_pos = $i;
			}
		}
		//������ �����������
		$rep_pos = $cur_pos;
		if($move == 'up'){
			$rep_pos = $cur_pos - 1;
		}elseif($move == 'down'){
			$rep_pos = $cur_pos + 1;
		}else{
			$rep_pos = $cur_pos;
		}
		if($rep_pos < 0 || $rep_pos >= $c){
			$rep_pos = $cur_pos;
		}
		$temp = intval($blocks[$cur_pos]['place']);
		$blocks[$cur_pos]['place'] = intval($blocks[$rep_pos]['place']);
		$blocks[$rep_pos]['place'] = intval($temp);
		//��������� ������
		for($i = 0; $i < $c; $i++){
			$db->Update('blocks', "place='".SafeDB($blocks[$i]['place'], 11, int)."'", "`id`='".SafeDB($blocks[$i]['id'], 11, int)."'");
		}
	}
	GO($config['admin_file'].'?exe=blocks');
}
