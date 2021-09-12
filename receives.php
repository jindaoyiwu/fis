<?php
@error_reporting(E_ALL & ~E_NOTICE & ~E_WARNING);
function mkdirs($path, $mod = 0644) {
	if (is_dir($path)) {
		return chmod($path, $mod);
	} else {
		$old = umask(0);
		if(mkdir($path, $mod, true) && is_dir($path)){
			echo 'make dir success'.PHP_EOL;
			umask($old);
			return true;
		} else {
			$error = error_get_last();
			echo $error['message'].PHP_EOL;
			umask($old);
		}
	}
	return false;
}
function is_valid_path($path){
	static $valid_dirs = false;
	if($valid_dirs == false){
		$valid_dirs = file(__DIR__ . '/valid_dirs.conf', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
	}
	if(!preg_match('#^\.|/\.|^~/#', $path)){
		foreach($valid_dirs as $valid_dir){
			if(strpos($path, trim($valid_dir)) == 0){
				return true;
			}
		}
	}
	return false;
}


if(!empty($_POST['to'])){
	if ($_POST['source'] != 'ovs'){
		echo "you are a bad guy";
		exit();
	}
	if ($_POST['sign'] != md5(md5($_POST['file'].$_POST['time']).$_POST['source'])){
		echo "you are a bad guy";
		exit();
	}
	echo 'begin'.date("Y-m-d H:i:s").PHP_EOL;
	$path = urldecode($_POST['to']);
//	if(!is_valid_path($path)){
//		echo "invalid target path: $path, access denied";
//		exit;
//	}
	if(is_dir($path) || $_FILES['file']['error'] > 0){
		header("Status: 500 Internal Server Error");
	} else {
		if(file_exists($path)){
			unlink($path);
		} else {
			$dir = dirname($path);
			if(!file_exists($dir)){
				if(mkdirs($dir) == false){
					echo "make dir fail";
					return false;
				}
			}
		}
		echo move_uploaded_file($_FILES['file']['tmp_name'], $path) ? 0 : 1;
	}
	echo 'end'.date("Y-m-d H:i:s").PHP_EOL;
} else {
	echo 'to must be set';
}
