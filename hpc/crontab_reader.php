<?php
/*
 * hopiCron, Cron Deamon for Apache or other webserver
 * Authors:	Mario Wenzel, maweki.de
 * Licence: GNU General Public License, http://www.gnu.org/licenses/gpl.html
 * this software is distributed without any warranty
 * 
 */

class cron_dispatcher {
	private $cron_lines = array();
	private $jobs_to_dispatch = array();
	private $crontab_entities = array(
		'yearly'   => '0 0 1 1 *',
		'annually' => '0 0 1 1 *',
		'monthly'  => '0 0 1 * *',
		'weekly'   => '0 0 * * 0',
		'daily'    => '0 0 * * *',
		'midnight' => '0 0 * * *',
		'hourly'   => '0 * * * *'
	);
	
	private $name_rep = array(
		'jan' => '1', 'feb' => '2', 'mar' => '3',
		'apr' => '4', 'may' => '5', 'jun' => '6',
		'jul' => '7', 'aug' => '8', 'sep' => '9',
		'oct' => '10', 'nov' => '11', 'dec' => '12',
		'mon' => '1', 'tue' => '2', 'wed' => '3',
		'thu' => '4', 'fri' => '5', 'sat' => '6',
		'sun' => '0'
	);
	
	public function read_crontab($file) {
		if (file_exists($file)) {
			$lines = file($file);
		}
		else {
			return false;
		}
		
		$this->cron_lines = array_merge($this->cron_lines, $lines);
	}
	
	public function read_crontab_glob($path) {
		$files = glob($path);
		foreach ($file as $files) {
			$this->read_crontab($file);
		}
	}
	
	public function crontab_from_array($source) {
		if (is_array($source)) {
			$this->cron_lines = array_merge($this->cron_lines, $source);
		}
	}
	
	public function reset_read_crontab() {
		$this->cron_lines = array();
	}
	
	private function is_hit($rule, $curr) {
		echo $rule.'.'.$curr.' ';
		if ($rule == '*') {
			return true;
		}
		if (($rule[0] == '*') and ($rule[1] == '/')) {
			$skip = strstr($rule, '/');
			$skip = substr_replace($skip, '', 0,1);
			return (($curr % $skip) == 0);
		}
		
		if (!is_numeric($rule) and isset($this->name_rep[$rule])) {
			$rule = $this->name_rep[$rule];
		}	
		
		$ruleparts = explode(',',$rule);
		foreach ($ruleparts as $part) {
			if (strpos($part, '/')) {
				$skip = strstr($rule, '/');
				$skip = substr_replace($skip, '', 0,1);
				$part = str_replace('/'.$skip, '', $part);
			}
			else {
				$skip = 1;
			}
			
			if (strpos($part, '-')) {
				$start = substr($part, 0, strpos($part, '-'));
				$end = str_replace($start.'-', '', $part);
			}
			else {
				$start = $end = $part;
			}
			
			for ($i = $start; $i <= $end; $i = $i+$skip) {
				if ($i == $curr) {
					return true;
				}
			}
		}
		return false;
	}
	
	private function reduce_lines($timestamp) {
		$this->jobs_to_dispatch = array();
		$lines = $this->cron_lines;
		$lines = array_reverse($lines);
		$remaining = array();
		while (($line = array_pop($lines)) !== NULL) {
			$line = str_replace(chr(9), ' ', $line);
			$line = trim($line);
			if ((empty($line)) or ($line[0] == '#')) {
				// comment or empty line
				continue;
			}
			if ($line[0] == '@') {
				//check for entity
				$entityname = substr($line, 1, strpos($line, ' ') - 2);
				if ($replacement = $this->crontab_entities[$entityname]) {
					continue; // entity unknown
				}
				else {
					$line = substr_replace($line, $replacement, 0, strpos($line, ' ') - 1);
				}
			}
			$cron_command_arr = explode(' ', $line, 6);
			$cron_time_arr = explode(' ', date('i G j n w'));
			if (count($cron_command_arr) < 6) {
				continue;
			}
			
			for ($i = 0; $i <= 4; $i++) {
				if (($i == 4) and ($cron_time_arr[$i] == 0)) { 
					/* Sunday fix */
					if ((!$this->is_hit($cron_command_arr[$i], 0)) and
					   (!$this->is_hit($cron_command_arr[$i], 7))) {
						continue 2;
					}
				}
				else {
					if (!$this->is_hit($cron_command_arr[$i], $cron_time_arr[$i]+0)) {
						continue 2;
					}
				}
			}
			$remaining[] = trim($cron_command_arr[5]);
		}
		$this->jobs_to_dispatch = $remaining;
		return $remaining;
	}
	
	public function dispatch_jobs($timestamp = false) {
		if ($timestamp === false) {
			$timestamp = time();
		}
		$this->reduce_lines($timestamp);
		foreach ($this->jobs_to_dispatch as $job) {
			$anal = parse_url($job);
			if (!$anal['port']) { $anal['port'] = 80; }
			$qry = $anal['path'];
			if ($anal['query']) { $qry .= $anal['query']; }
			dispatch_generic_job($anal['host'], $qry, $anal['port']);
			write_hpc_log($anal['host'].$qry);
		}
		return $this->jobs_to_dispatch;
	}
	
	public function simulate($timestamp = false) {
		/* simulate() returns the lines that would have been dispatched */
		if ($timestamp === false) {
			$timestamp = time();
		}
		$this->reduce_lines($timestamp);
		return $this->jobs_to_dispatch;
	}
}

?>
