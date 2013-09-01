<?php
/*
 * hopiCron, Cron Deamon for Apache or other webserver
 * Authors:	Mario Wenzel, maweki.de
 * Licence: GNU General Public License, http://www.gnu.org/licenses/gpl.html
 * this software is distributed without any warranty
 * 
 */
	require('hpc/hpc_conf.php');
	
	if (isset($_POST['start']) and ($_POST['start'] == HPC_EXT_PASS)) {
		disable_stopper();
		lock_sleeper();
		unlock_dispatcher();
		dispatch_generic_job(HPC_LOCAL_DOMAIN, HPC_LOCAL_DOMAIN_PATH.'hpc_dispatcher.php?pass='.HPC_INT_PASS);
		sleep(3);
		header('Location: hpc.php');
		exit();
	}
	
	if (isset($_POST['stop']) and ($_POST['stop'] == HPC_EXT_PASS)) {
		enable_stopper();
		header('Location: hpc.php');
		exit();
	}
	
?><html>
<head><title>hopiCron - Monitor</title>
<style type="text/css">
#start, #stop, #debug {
	display: none;
}

#start:target, #stop:target, #debug:target {
	display: block;
}

body {
	background-color: #fff;
}

div, h1, form {
	width: 500px;
	margin: 0 auto;
	background-color: #ddd;
	padding: 10px;
	border-style: solid;
	border-width: 1px;
	color: #000;
	border-color: #000;
}

div.warning {
	background-color: #ff5b5b;
}

div+div {
	border-top-style: none;
}

#actions a {
	display: block;
	border-style: outset;
	width: 25%;
	color: #fff;
	background-color: #000;
	padding: 5px;
	margin: 5px;
	text-align: center;
}
</style>
</head>
<body>
<h1>hopiCron - Monitor</h1>
<div id="status">
<?php
	$last_dispatch = file_get_contents(HPC_LAST_DISPATCHER_FILE);
	if (!$last_dispatch) {
		$last_dispatch = 0;
	}
	$now = time();
	$diff = $now - $last_dispatch;
	
	echo '<p>The hopi-dispatcher was last run '.$diff.' seconds ago.</p>';
	
	if ($diff > 60) {
		if ($diff > 120) {
			if ($last_dispatch == 0) {
				echo '<p>The service probably has never been run.</p>';
				$start = true;
				$stop = false;
			}
			else {
				echo '<p>The service is probably not running.</p>';
				$start = true;
				$stop = false;
			}
		}
		else {
			echo '<p>The service is maybe not running.</p>';
			$start = false;
			$stop = false;
		}
	}
	else {
		echo '<p>The service is probably running.</p>';
		$stop = true;
		$start = false;
	}
?>
</div>

<?php
	function create_hpc_warning($text) {
		echo '<div class="warning"><strong>WARNING:</strong> '.$text.'</div>';
	}

	function check_writable($filename) {
		if (!is_writable($filename)) {
			create_hpc_warning('file \''.$filename.'\' is not writeable.');
			return false;
		}
		return true;
	}
	$files_needed_writable = array(HPC_DISPATCHER_LOCK,HPC_SLEEPER_LOCK,HPC_LAST_DISPATCHER_FILE,HPC_STOP_FILE);
	if (HPC_LOG_FILE) {
		$files_needed_writable[] = HPC_LOG_FILE;
	}
	$all_writable = true;
	foreach ($files_needed_writable as $file) {
		if (!check_writable($file)) {
			$all_writable = false;
		}
	}
?>

<?php
	/* Check for supported modes */
	if (!function_exists('fsockopen')) {
		create_hpc_warning('Needed function &quot;fsockopen&quot; does not exist. hopiCron will not work.');
	}
	
	if (function_exists('stream_get_transports')) {
		$transports = stream_get_transports();
		if (!in_array('tcp', $transports)) {
			create_hpc_warning('Needed transport stream &quot;tcp&quot; seems not to be supported. hopiCron will probably not work.');
		}
	}
?>

<div id="actions">
<?php
	if ($start and $all_writable) {
		echo '<a href="#start">Start</a>';
	}
	if ($stop) {
		echo '<a href="#stop">Stop</a>';
	}
	if (HPC_DEBUG_MODE_WITH_PASS) {
		echo '<a href="#debug">Debug</a>';
	}
?>
</div>

<form id="debug" action="hpc.php" method="post">
<label>Password: <input type="password" name="debug" value="" /></label>
<input type="submit" />
</form>

<form id="start" action="hpc.php" method="post">
<label>Password: <input type="password" name="start" value="" /></label>
<input type="submit" />
</form>

<form id="stop" action="hpc.php" method="post">
<label>Password: <input type="password" name="stop" value="" /></label>
<input type="submit" />
</form>

<?php
	if ((HPC_DEBUG_MODE) or (HPC_DEBUG_MODE_WITH_PASS and (isset($_POST['debug']) and ($_POST['debug'] == HPC_EXT_PASS)))) {
		echo '<div id="debug_info">';
		$all_constants = get_defined_constants(true);
		echo '<pre>';
		$own_constants = $all_constants['user'];
		foreach ($own_constants as $key => $value) {
			echo $key.' = ';
			if ($value === true) {
				echo 'true';
			} elseif ($value === false) {
				echo 'false';
			} elseif ($value === NULL) {
				echo 'NULL';
			} else {
				echo $value;
			}
			echo '<br />';
		}			
		echo '</pre>';
		echo '</div>';
	}
?>
</body>
</html>	
	
