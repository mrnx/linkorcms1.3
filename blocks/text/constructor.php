<?php

//����� �������������� ����� ��������� ����� ������� ����������� ��� ��������� �����
//��� �� ���� ��������� ��� ����� ���������� ����� �������������� �����, ����� �����
// ��� ���� ��������� �� ������ ���������� ������������ ����� � ���������� $block_config
//���������� �� ��, ��� ���������� �������������� ����� ����� ������� ���������� $a. ���
// �������������� ��� ������ ����� �������� 'edit'.
//�� ������ ������������ ���������� $title ��� ��������� ��������� �����.

if(!defined('VALID_RUN')){
	header("HTTP/1.1 404 Not Found");
	exit;
}

$mod_text = '';
if($a == 'edit'){
	$mod_text = SafeDB($block_config, 0, str, false);
}

FormTextRow('�����', $site->HtmlEditor('mod_text', $mod_text, 600, 400));
$title = '������������ ���������� �����';

?>