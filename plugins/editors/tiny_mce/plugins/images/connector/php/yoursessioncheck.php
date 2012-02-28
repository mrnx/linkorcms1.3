<?php

/**
 * Файл служит проверкой доступа по сессии,
 * вместо user подставьте ваше значение.
 * 
 * Если вы понятия не имеете о чем идет речь
 * и вам безразлична явная уязвимость в безопасности,
 * просто закомментируйте или удалите этот код.
 * 
 */
 
if(!defined("USER")){
	define("USER", true);
	define("EXTRA_ADMIN_COOKIE", '3794y7v387o3');
}else{
	return;
}

function getip()
{
	global $_SERVER, $config;

		if(isset($_SERVER['REMOTE_ADDR'])){
			$ip = $_SERVER['REMOTE_ADDR'];
		}elseif(isset($HTTP_SERVER_VARS['REMOTE_ADDR'])){
			$ip = $HTTP_SERVER_VARS['REMOTE_ADDR'];
		}elseif(getenv('REMOTE_ADDR')){
			$ip = getenv('REMOTE_ADDR');
		}
		if($ip!=""){
			if(preg_match("/^([0-9]+\.[0-9]+\.[0-9]+\.[0-9]+)/",$ip,$ipm)){
				$private = array("/^0\./","/^127\.0\.0\.1/","/^192\.168\..*/","/^172\.16\..*/"
				,"/^10..*/","/^224..*/","/^240..*/");
				$ip = preg_replace($private,$ip,$ipm[1]);
			}
		}
		if (strlen($ip)>16) $ip = substr($ip, 0, 16);
		return $ip;
}

include('../../../../../../../config/salt.php');

if(isset($_SESSION['u_level']) && $_SESSION['u_level'] == '1' && isset($_SESSION['u_login'])){

	if(isset($_COOKIE['admin'])){
		$cookie = $_COOKIE['admin'];
	}elseif(isset($_REQUEST['admin'])){ // Можно передавать в POST и GET
		$cookie = $_REQUEST['admin'];
	}else{
		echo 'В доступе отказано!';
		exit();
	}

	$auth = base64_decode($cookie);
	$auth = explode(":", $auth);
	$login = $auth[0];
	$cookie_md5 = $auth[1];

	$u_login = $_SESSION['u_login'];
	$u_pass = $_SESSION['u_md5'];

	$scode = md5($u_pass.$config['salt'].EXTRA_ADMIN_COOKIE);
	
	if($cookie_md5 != $scode){
		echo 'В доступе отказано!';
		exit();
	}

}else{
	echo 'В доступе отказано!';
	exit();
}
 
?>
