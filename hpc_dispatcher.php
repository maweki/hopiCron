<?php
/*
 * hopiCron, Cron Deamon for Apache or other webserver
 * Authors:	Mario Wenzel, maweki.de
 * Licence: GNU General Public License, http://www.gnu.org/licenses/gpl.html
 * this software is distributed without any warranty
 * 
 */
	
	function start_sleeper() {
		unlock_sleeper();
		dispatch_generic_job(HPC_LOCAL_DOMAIN, HPC_LOCAL_DOMAIN_PATH.'hpc_sleeper.php?pass='.HPC_INT_PASS);
	}
	
	require('hpc/hpc_conf.php');
	
	hpc_check_auth();
	
	if ((!file_exists(HPC_STOP_FILE)) or (file_get_contents(HPC_STOP_FILE) == '1')) {
		die('SERVICE STOP');
	}
	
	if ((!file_exists(HPC_DISPATCHER_LOCK)) or (file_get_contents(HPC_DISPATCHER_LOCK) == '1')) {
		die('DISPATCHER LOCKED');
	}

	lock_dispatcher();
	
	if (file_exists(HPC_LAST_DISPATCHER_FILE) and is_writable(HPC_LAST_DISPATCHER_FILE)) {
		$last_dispatch = file_get_contents(HPC_LAST_DISPATCHER_FILE);
		if (!$last_dispatch) {
			$last_dispatch = 0;
		}
		file_put_contents(HPC_LAST_DISPATCHER_FILE, time());
	}
	else {
		die('DISPATCHER-LOG UNWRITABLE');
	}
	
	if (HPC_CHECK_LAST_DISPATCH_TIME) {
		$now = time();
		$now_f = date('YmdHi', $now);
		$last_f = date('YmdHi', $last_dispatch);
		
		if ($now_f == $last_f) {
			/* the dispatcher did allready run this minute */
			start_sleeper();
			die;
		}
	}
	
	/*
		HERE THE CRONTAB SHOULD BE READ AND JOBS DISPATCHED
	*/
	$crontab = new cron_dispatcher;
	if (HPC_CRONTAB_GLOB) {
		$crontab->read_crontab_glob(HPC_LOCAL_PATH.HPC_CRONTAB_PATH);
	}
	else {
		$crontab->read_crontab(HPC_LOCAL_PATH.HPC_CRONTAB_PATH);
	}
	$crontab->dispatch_jobs();
	
	start_sleeper();
