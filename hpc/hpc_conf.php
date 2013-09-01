<?php
	/* *Crontab settings */
	/* HPC_EXT_PASS
	 * This password is used for starting and stopping the "service"
	 * Change as soon as possible
	 */
	define('HPC_EXT_PASS', '1234');
	
	/* HPC_AUTH_PASS
	 * If this is set to true, the sleeper and the dispatcher will only
	 * work if executed with HPC_INT_PASS. If you use this, you should
	 * change HPC_INT_PASS */
	define('HPC_AUTH_PASS', false);
	
	/* HPC_AUTH_IP
	 * If this is set to true, the sleeper and the dispatcher will only
	 * work if called from the same ip adress as the server they are
	 * running on. This is more secure and sophisticated then HPC_INT_PASS
	 * but may not work on your particular setup. */
	define('HPC_AUTH_IP', true);
	
	/*
	 * HPC_INT_PASS
	 * this password is used internally, so that the sleeper and
	 * dispatcher know, that they were called by each other. Change
	 * this if you use HPC_AUTH_PASS. */
	define('HPC_INT_PASS', '1234');
	
	define('HPC_CRONTAB_PATH', 'hpc/crontab');
	/* HPC_CRONTAB_GLOB
	If true, the dispatcher uses glob() to look for crontab files. Glob is able
	to use wildcards in its parameter. If false, the dispatcher will use fopen() */
	define('HPC_CRONTAB_GLOB', false);

	/* locking files and dispatch-time-file - MUST BE WRITEABLE !!! */
	define('HPC_DISPATCHER_LOCK', 'hpc/dispatcher.lock');
	define('HPC_SLEEPER_LOCK', 'hpc/sleeper.lock');
	define('HPC_LAST_DISPATCHER_FILE', 'hpc/last_dispatch');
	define('HPC_STOP_FILE', 'hpc/stop');
	
	/* Sleeper config */
	/* HPC_SLEEP_TIME
	This value denotes the time, the sleeper sleeps until the next dispatch.
	A value of 0 means that the time to the next full system minute is slept
	until the next dispatch. Any other (positive) number is the time in seconds
	used by sleep().
	This is useful because on unix, sleep does not count towards max_execution_time,
	if your weird os/server-combination (e.g. windows) excerts different behaviour, use this
	function to start the sleeper thread more often (Be sure to let the dispatcher
	check its last dispatch time, otherwise jobs will be dispatches multiple times
	in the same minute). http://www.php.net/manual/en/function.set-time-limit.php#75557
	Values of near 60 an more can result in jobs dropped due to no execution of
	the dispatcher in this exact minute.
	Negative values will behave like 0 but the absolute value will be added to
	the calculated sleep time. */
	define('HPC_SLEEP_TIME', -1);

	/* Dispatcher config */
	/* HPC_CHECK_LAST_DISPATCH_TIME
	This value denotes whether the dispatcher should check for the last time
	it was dispatched and start the sleeper if it is still the same minute
	since the last dispatch.
	I can really not think of a reason to set this to false, but, I guess,
	whatever floats your boat. */
	define('HPC_CHECK_LAST_DISPATCH_TIME', true);
	/* HPC_DISPATCH_LOCAL_JOBS
	Should local jobs (with filesystem-path) be dispatched (/w include).
	Until there is no seperate loader for these jobs, the sum of the jobs'
	execution time will count towards max_execution_time and will maybe not
	call the sleeper if the dispatcher is terminated by the system.
	At this time you should call jobs through http by their full canonical url.
	* THIS IS NOT YET IMPLEMENTED  */
	define('HPC_DISPATCH_LOCAL_JOBS', false);
	/* HPC_LOG_FILE
	 * This defines the local file where dispatched jobs should be logged.
	 * If empty, no logs will be created. Check the writability of that
	 * file beforehand. */
	define('HPC_LOG_FILE', 'hpc/dispatch.log');
	
	/* The following lines are for self config. These should only be
	 * changed if on your special configuration a certain variable is
	 * not available */
	define('HPC_LOCAL_DOMAIN', $_SERVER['SERVER_NAME']);
	define('HPC_LOCAL_DOMAIN_PATH', dirname($_SERVER['REQUEST_URI']).'/');
	define('HPC_LOCAL_PATH', realpath(dirname(__FILE__).'/..').'/' );
	
	/* HPC_DEBUG_MODE
	 * If set to true, the monitor will show all settings and
	 * crontab files. */
	define('HPC_DEBUG_MODE', false);
	/* HPC_DEBUG_MODE
	 * If set to true, the monitor will show all settings and
	 * crontab files if activated with HPC_EXT_PASS. */
	define('HPC_DEBUG_MODE_WITH_PASS', true);
	
	include('hpc_object.php');
	include('crontab_reader.php');
?>
