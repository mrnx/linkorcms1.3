<?php

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

AddCenterBox('����� ������');

$tables = $db->GetTableInfo();

$sort = 'name';
$sort_dec = false;
if(isset($_GET['sort'])) $sort = $_GET['sort'];
if(isset($_GET['dec'])) $sort_dec = true;
SortArray($tables, $sort, $sort_dec);

$text = '<table cellspacing="0" cellpadding="0" class="cfgtable">'
.'<tr>
	<th>#</th>
	<th><a href="'.$config['admin_file'].'?exe=fdbadmin&sort=name'.($sort == 'name' && !$sort_dec ? '&dec=1' : '').'">�������</a></th>
	<th><a href="'.$config['admin_file'].'?exe=fdbadmin&sort=num_rows'.($sort == 'num_rows' && !$sort_dec ? '&dec=1' : '').'">�������</a></th>
	<th><a href="'.$config['admin_file'].'?exe=fdbadmin&sort=size'.($sort == 'size' && !$sort_dec ? '&dec=1' : '').'">������</a></th>
	<th>���</th>
	<th>��������</th>
</tr>';
$totalsize = 0;
$totalrows = 0;
$light = array();

$i = 0;
foreach($tables as $r){
	$i++;
	$l = '';
	$a = '';
	if($sort == 'name'){
		if(!isset($light[SafeDb($r['name'], 1, str)])){
			$light[SafeDb($r['name'], 1, str)] = SafeDb($r['name'], 1, str);
			$l = 'background-color:#BCFABC';
			$a = '<span style="float:right; font-size:18px; margin-right:10px;"><B>'.strtoupper(SafeDb($r['name'], 1, str)).'</B></span>';
		}
	}

	$func = '';
	$func .= SpeedButton('�������������', $config['admin_file'].'?exe=fdbadmin&a=renametable&name='.$r['name'], 'images/admin/rename.png');
	$func .= SpeedButton('�������', $config['admin_file'].'?exe=fdbadmin&a=droptable&name='.$r['name'].'&ok=0', 'images/admin/delete.png');

	$text .= '<tr>'
	.'<td style="text-align:left; padding-left:10px; '.$l.'">'.$i.$a.'</td>'
	.'<td ><a href="'.$config['admin_file'].'?exe=fdbadmin&a=structure&name='.$r['name'].'">'.$r['name'].'</a></td>'
	.'<td>'.$r['num_rows'].'</td>'
	.'<td>'.FormatFileSize($r['size']).'</td>'
	.'<td>'.(isset($r['type'])?$r['type']:'�� ���������').'</td>'
	.'<td class="cfgtd">'.$func.'</td>'
	.'</tr>';
	$totalsize += $r['size'];
	$totalrows += $r['num_rows'];
}
$text .= '</table>����� <b>'.$db->NumRows().'</b> ������(�), <b>'.$totalrows.'</b> ������� � <b>'.FormatFileSize($totalsize).'</b> ������.<br><br>';
$text .= '.:������� ����� �������:.';

AddText($text);

FormRow('��� �������',$site->Edit('name', '', false, 'style="width: 200px;"'));
FormRow('���������� �����',$site->Edit('cols', '', false, 'style="width: 50px;" title="������� ���� ���������� �������"'));
AddForm('<form action="'.$config['admin_file'].'?exe=fdbadmin&a=newtable" method="post">', $site->Submit('�����','title="������� � ����. ���� �������� �������."'));

?>