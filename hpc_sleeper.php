<?php
/*
 * hopiCron, Cron Deamon for Apache or other webserver
 * Authors:	Mario Wenzel, maweki.de
 * Licence: GNU General Public License, http://www.gnu.org/licenses/gpl.html
 * this software is distributed without any warranty
 * 
 */

	function start_dispatcher() {
		unlock_dispatcher();
		dispatch_generic_job(HPC_LOCAL_DOMAIN, HPC_LOCAL_DOMAIN_PATH.'hpc_dispatcher.php?pass='.HPC_INT_PASS);
	}
	
	require('hpc/hpc_conf.php');
	
	hpc_check_auth();
	
	if ((!file_exists(HPC_STOP_FILE)) or (file_get_contents(HPC_STOP_FILE) == '1')) {
		die('SERVICE STOP');
	}
	
	if ((!file_exists(HPC_SLEEPER_LOCK)) or (file_get_contents(HPC_SLEEPER_LOCK) == '1')) {
		die('SLEEPER LOCKED');
	}

	lock_sleeper();
	
	if (HPC_SLEEP_TIME > 0) {
		sleep(HPC_SLEEP_TIME);
		start_dispatcher();
	}
	else {
		$last_dispatch = file_get_contents(HPC_LAST_DISPATCHER_FILE);
		if (!$last_dispatch) {
			$last_dispatch = 0;
		}
		$now = time();
		$now_f = date('YmdHi', $now);
		$last_f = date('YmdHi', $last_dispatch);
		
		if ($now_f == $last_f) {
			$sleep = 60 - (date('s', $now)+0) + abs(HPC_SLEEP_TIME);
			sleep($sleep);
		}
		start_dispatcher();
	}
?>
