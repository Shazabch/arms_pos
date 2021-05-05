<?php
/*

12/5/2012 5:42 PM Andy
- New script to check sock 2/3 and auto start it.

SAMPLE
=====================
php sockcheck.php -sock 2,3

*/
ini_set('memory_limit', '256M');
set_time_limit(0);
error_reporting (E_ALL ^ E_NOTICE);

$argv = $_SERVER['argv'];
array_shift($argv);	// remove the first

$sock_num_list = array();

while($a = array_shift($argv)){
	switch($a){
		case '-sock':
			$str = trim(array_shift($argv));
			if($str){
				$sock_num_list = explode(",", $str);
			}
			break;
		default:
			die("Invalid option: $a\n");
			exit;
	}
}

if(!$sock_num_list){
	die("Please provide -sock with sock number\n");
}

$sock_num_list;
//print_r($sock_num_list);
$result = array();

// check if myself is running, exit if yes
print "Start Check\n";
@exec("mysqld_multi report", $exec);

print "================\n";
while($str = array_shift($exec)){
	print "$str\n";
	
	foreach($sock_num_list as $sock_num){
		if(isset($result[$sock_num]))	continue;
		
		if(strpos($str, "mysqld".$sock_num." is running")){
			$result[$sock_num]['ok'] = 1;
			
			if(count($result) == count($sock_num_list)){
				break 2;
			}
		}
	}
}
print "================\n";
print "Summary\n================\n";
//print "Result\n";
//print_r($result);

foreach($sock_num_list as $sock_num){
	if($result[$sock_num]['ok']){
		print "sock".$sock_num." is running.\n";
	}else{
		print "sock".$sock_num." is stopped.\n";
		
		$command = "mysqld_multi start $sock_num";
		print "Try this command to start it\n$command\n";
		@exec($command, $exec);
		print "Result\n";
		print_r($exec);
	}
}
?>
