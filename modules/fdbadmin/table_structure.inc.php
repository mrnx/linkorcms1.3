<?php

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

$name = SafeEnv($_GET['name'], 255, str);

$info = $db->GetTableColumns($name);

$text = '<table cellspacing="0" cellpadding="0" class="cfgtable">
	<tr>
		<th></th>
		<th>����</th>
		<th>���</th>
		<th>��������</th>
		<th>����</th>
		<th>�� ���������</th>
		<th>���� ����������</th>
		<th>�������</th>
	</tr>';
$i = 0;

foreach($info as $col){
	if(!isset($col['notnull'])){
		$col['notnull'] = false;
	}
	if(!isset($col['default'])){
		$col['default'] = '';
	}
	if(!isset($col['attributes'])){
		$col['attributes'] = '';
	}
	if(!isset($col['auto_increment'])){
		$col['auto_increment'] = false;
	}

	$func = '';
	$func .= SpeedButton('����������� ���������� ��� ���������', $config['admin_file'].'?exe=fdbadmin&a=viewcollinfo&name='.$name.'&collid='.$i, 'images/admin/info.png');
	$func .= SpeedButton('������� �������', $config['admin_file'].'?exe=fdbadmin&a=deletecoll&name='.$name.'&collid='.$i, 'images/admin/delete.png');

	$text .= '<tr>
	<td>'.$i.'</td>
	<td>'.$col['name'].'</td>
	<td>'.$col['type'].(isset($col['length']) ? '('.$col['length'].')' : '').'</td>
	<td>'.$col['attributes'].'</td>
	<td>'.($col['notnull'] ? '<font color="#0000FF">���</font>' : '<font color="#FF0000">��</font>').'</td>
	<td>'.$col['default'].'</td>
	<td>'.($col['auto_increment'] ? '<font color="#FF0000">��</font>' : '<font color="0000FF">���</a>').'</td>
	<td>'.$func.'</td>';
	$i++;
}

$text .= '</table><br />';
$text .= '.:�������� �������:.';
FormRow('����� (������ ����)', $site->Edit('toindex', '', false, 'style="width: 100px;"'), 160);
AddCenterBox('��������� ������� "'.$name.'"');
AddText($text);
AddForm('<form action="'.$config['admin_file'].'?exe=fdbadmin&a=newcoll&name='.$name.'" method="post">', $site->Submit('�����', 'title="������� � ����. ���� ���������� �������."'));
AdminFdbAdminGenTableMenu($name);

?>