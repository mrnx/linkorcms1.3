<?php

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

global $text;

$text .= '<table cellspacing="0" cellpadding="0" class="cfgtable">'
.'<tr>'
.'<th colspan="2" width="50%">��������� ����������</th>'
.'<th colspan="2">������������</th></tr>'
.'<tr>'
.'<td align="left" width="15%">CMS</td>'
.'<td align="left"><b>'.CMS_VERSION_STR.'</b></td>'
.'<td align="left" width="15%">����� ������</td>'
.'<td align="left">'.get_cfg_var('memory_limit').'�.</td>'
.'</tr>'
.'<tr>'
.'<td align="left">��</td>'
.'<td align="left">'.php_uname().'</td>'
.'<td align="left">����� �������</td>'
.'<td align="left">'.get_cfg_var('max_execution_time').' ���.</td>'
.'</tr>'
.'<tr>'
.'<td align="left">������</td>'
.'<td align="left">'.$_SERVER['SERVER_SOFTWARE'].'</td>'
.'<td align="left">REGISTER_GLOBALS</td>'
.'<td align="left">'.((ini_get('register_globals') == 1) ? '<font color="#D56A00">��������' : '<font color="#2A7D00">���������').'</font></td>'
.'</tr>'
.'<tr>'
.'<td align="left">PHP ������</td>'
.'<td align="left">'.phpversion().'</td>'
.'<td align="left">SAFE_MODE</td>'
.'<td align="left">'.((ini_get('safe_mode') == 1 || strtolower(ini_get('safe_mode')) == 'on') ? '<font color="#D56A00">��������' : '<font color="#2A7D00">���������').'</font></td>'
.'</tr>'
.'<tr>'
.'<td align="left">���� ������</td>'
.'<td align="left">'.$db->Name.'&nbsp;'.$db->Version.'</td>'
.'<td align="left">MAGIC_QUOTES_GPC</td>'
.'<td align="left">'.((get_magic_quotes_gpc() || strtolower(ini_get('magic_quotes_gpc')) == 'on') ? '<font color="#D56A00">��������' : '<font color="#2A7D00">���������').'</font></td>'
.'</tr>'
.'</table>';

?>