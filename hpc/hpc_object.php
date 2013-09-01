<?php
/*
 * hopiCron, Cron Deamon for Apache or other webserver
 * Authors:	Mario Wenzel, maweki.de
 * Licence: GNU General Public License, http://www.gnu.org/licenses/gpl.html
 * this software is distributed without any warranty
 * 
 */
	
function lock_dispatcher() {
	hpc_un_lock_helper_function (HPC_DISPATCHER_LOCK, '1');
}

function unlock_dispatcher() {
	hpc_un_lock_helper_function (HPC_DISPATCHER_LOCK, '0');
}

function lock_sleeper() {
	hpc_un_lock_helper_function (HPC_SLEEPER_LOCK, '1');
}

function unlock_sleeper() {
	hpc_un_lock_helper_function (HPC_SLEEPER_LOCK, '0');
}

function enable_stopper() {
	hpc_un_lock_helper_function (HPC_STOP_FILE, '1');
}

function disable_stopper() {
	hpc_un_lock_helper_function (HPC_STOP_FILE, '0');
}

function hpc_un_lock_helper_function ($file, $value) {
	$handle = fopen($file, 'w');
	fwrite($handle, $value);
	fclose($handle);
}

function hpc_check_auth($message_on_fail = 'AUTH FAILED') {
	if ((HPC_AUTH_PASS) and ($_GET['pass'] != HPC_EXT_PASS)) {
		sleep(1); // Trying to keep the fun out of brute force attacks
		die($message_on_fail);
	}
	if ((HPC_AUTH_IP) and ($_SERVER['SERVER_ADDR'] != $_SERVER['REMOTE_ADDR'])) {
		die($message_on_fail);
	}
}

function write_hpc_log($text) {
	if ((HPC_LOG_FILE) and (is_writable(HPC_LOG_FILE))) {
		file_put_contents(HPC_LOG_FILE, date('Y-m-d H:i:s ').$text."\n", FILE_APPEND);
	}
}

function dispatch_generic_job($server, $url, $port = 80) {
	@set_time_limit(0);
	
	$timeout = 10;
	$rw_timeout = 86400;
	$error_number = '';
	$error_string = '';
	
	$con = fsockopen($server, $port, $error_number, $error_string, $timeout);
	if (!$con) {
	   echo $error_string.' ('.$error_number.')<br />'."\n";
	   return false;
	}
	$qry = 'GET '.$url.' HTTP/1.1'."\r\n";
	$qry .= 'Host: '.$server."\r\n";
	$qry .= 'Connection: Close'."\r\n\r\n";
	
	stream_set_blocking($con, false);
	stream_set_timeout($con, $rw_timeout);
	fwrite($con, $qry);
	
	return true;
}

?>
