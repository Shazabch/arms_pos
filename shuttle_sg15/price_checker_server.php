<?php

/*
8/21/2009 5:12:41 PM Andy
- add getting argument from server, arg[1] for port num and arg[2] for branch code

11/4/2009 6:04:42 PM yinsee
- remove process is running checking so that multiple branch can be used at once

11/15/2010 4:33:46 PM Alex
- Add "Nett" at left bottom
- if member and non-member discount not same, juz display price

3/23/2011 9:54:15 AM Andy
- Add define terminal.

3/25/2011 12:01:58 PM Andy
- Add show normal price when got promotion or category discount.
- Add if got $config['shuttle_sg_only_show_nett_price'], will only show nett selling price. (no normal and member price)

10/11/2012 6:00 PM Andy
- Add function terminal_direct_check_price to allow direct check price at terminal.

5/17/2013 4:04 PM Justin
- Enhanced to recognize and show member info.

3/26/2015 3:30 PM Andy
- Enhance to show GST Indicator.

5/16/2016 4:10 PM Andy
- Enhanced to compatible with php7.

4/26/2017 10:31 AM Khausalya
- Enhanced changes from RM to use config setting.

10/23/2017 4:32 PM Andy
- Enhanced to have default sync server config.
*/
/*
@exec('ps ax | grep -v grep | grep -v /bin/sh | grep price_checker_server.php', $exec);

if (count($exec)>1)
{
	print date("[H:i:s m.d.y]")." Another process is already running\n";
	print_r($exec);
	exit;
}

php price_checker_server.php terminal_direct_check_price branch_code itemcode
*/
declare(ticks = 1);

//pcntl_signal(SIGTERM, "sig_handler");
//pcntl_signal(SIGINT, "sig_handler");
define('EXIT_CODE',42137597);
define('SKIP_BROWSER',1);
define('DEBUG',0);
define('PRICE_CHECKER',1);
define('TERMINAL', 1);

include("../default_ss_config.php");
include("../config.php");

if((version_compare(PHP_VERSION, '7.0.0', '>='))){
	include("../include/mysqli.php");
}else{
	include("../include/mysql.php");
}
include_once("../include/price_checker.include.php");

if(!extension_loaded('sockets'))
{
if(strtoupper(substr(PHP_OS, 3)) == 'WIN')
{
dl('php_sockets.dll');
}
else
{
dl('sockets.so');
}
}


error_reporting(E_ALL);

/* Allow the script to hang around waiting for connections. */
set_time_limit(0);

/* Turn on implicit output flushing so we see what we're getting
 * as it comes in. */
ob_implicit_flush();

//$address = '192.168.1.254';
$arg = $_SERVER['argv'];

if($arg[1] == 'terminal_direct_check_price'){
	terminal_direct_check_price();
	exit;
}

$port = (intval($arg[1])>0) ? intval($arg[1]) : 9000;
$branch_code = $arg[2] ? $arg[2] : BRANCH_CODE;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($sock) . "\n";
    exit;
}

if (!socket_bind($sock, '0.0.0.0', $port)) {
    echo "socket_bind() failed\n";
    exit;
}

	//print "\nlisten\n";
	if (!socket_listen($sock, 5)) {
	    echo "socket_listen() failed\n";
		exit;
	}

do {
    print "\nwaiting for new client to connect\n";
	if (($msgsock = socket_accept($sock)) < 0) {
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
        break;
    }
    if($msgsock)	
		echo "client connected.\n";
    else{
		echo "client failed to connect.\n";
		continue;
	}
    
    $pid = pcntl_fork();
	if ($pid == -1) {
	     die('could not fork');
	} else if ($pid) {
	     // we are the parent
	     //pcntl_wait($status); //Protect against Zombie children
	     print "forked $pid created.\n";
	     print memory_get_usage() . " memory used\n";
	} else {
	    process_child($msgsock);
	    print "$pid child is dead\n";
    }
    
} while (true);
socket_close($sock);

function tohex($str){
	return  chr(hexdec($str));
}

function process_child($msgsock){
    // we are the child
     $pid = time();
    print "accepted child process $pid\n";

    $output = tohex('1b')."%";
    socket_write($msgsock, $output);

    /* Send instructions. */
    $msg = tohex('1b').tohex('42').tohex('30').'Connected to Server.';
	$msg .= tohex('1b').tohex('42').tohex('31').tohex('1b').tohex('2e').tohex('34').'Welcome !'.tohex('03');
	$msg .= tohex('1b').tohex('42').tohex('30');
    socket_write($msgsock, $msg, strlen($msg));

    do {
        $buf = socket_read($msgsock, 2048, PHP_NORMAL_READ);
      if ($buf===false) {
        // bvoken pipe, child is dead
        print "child $pid disconnected.\n";
        break;
      }
	    $output = tohex('1b')."%";
		$buf = trim(preg_replace("/^[^0-9]+/i",'',$buf)); // remove prefix junks
	    //socket_write($msgsock, $output);
		//$talkback = "[".$buf."]";
        //socket_write($msgsock, $talkback, strlen($talkback));
        echo "child $pid received $buf\n";
        get_item($msgsock, $buf);
    } while (true);
    socket_close($msgsock);
}

function get_item($msgsock, $msg){
	global $branch_code, $db_default_connection, $config;
	
	$con = new sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3], false);
	if(!$con->db_connect_id) {
	 	print mysql_error();
		return;
  	}
	$con->sql_query("select id from branch where code = ".ms($branch_code));
    $branch_id = $con->sql_fetchfield(0);
    
    if (preg_match("/^28/",$msg)) $msg = substr($msg,0,12);
	$code = $msg;
    
    $params = array();
	$params['branch_id'] = $branch_id;
	$params['code'] = $code;
	$params['mysql_con'] = $con;
	
    $sku = check_price($params);
    
    if ($sku['error'])
	{
		$member = check_member($params);
		if(!$member){
			$output = tohex('1b')."%";
			$output .= tohex('1b').tohex('42').tohex(30);
			$output .= $msg.tohex('0d');
			$output .= "Item Not Found";
		}else{ // member info
			$output = tohex('1b')."%";
			$output .= tohex('1b').tohex('42').tohex('30');
			$output .= tohex('1b').tohex('24');
			$output .= $member['name'].tohex('0d')."IC: ".$member['nric'].tohex('0d')."Pts: ".$member['points'].tohex('0d')."Pts Update: ".date("d/m/y", strtotime($member['points_update'])).tohex('0d')."Exp. Date: ".date("d/m/y", strtotime($member['next_expiry_date'])).tohex('03');
			
			// bottom left
			//$output .= tohex('1b').tohex('2e').tohex('36')."Exp. Date: ".date("d/m/y", strtotime($member['next_expiry_date'])).tohex('03');
		}
	}else{
		if($sku['is_under_gst']){
			$str_gst_indicator = " ".$sku['output_gst']['indicator_receipt'];
		}
		
        $output = tohex('1b')."%";
		$output .= tohex('1b').tohex('42').tohex('30');
		$output .= tohex('1b').tohex('24');
		//$output .= $msg;
		$output .= $sku['description'].tohex('0d');
		if($sku['member_price']>0 && $sku['member_price'] != $sku['price']){

            $price = $sku['non_member_price'];

		    //member price same as non member price
            if($sku['member_price']==$sku['non_member_price']){
                // member price same as non-member price
	            $output .= "(Discount ".$sku['member_discount'].'%)'.tohex('0d');

				if(!$config['shuttle_sg_only_show_nett_price']){
                    $output .= "Normal: ". $config["arms_currency"]["symbol"] . " " . number_format($sku['default_price'], 2).tohex('0d');
				}
       			$output .= tohex('1b').tohex('42').tohex('31'); //font size large
		    	$output .= tohex('1b').tohex('2e').tohex('36')."Nett".tohex('03');
		    	$output .= tohex('1b').tohex('2e').tohex('38').$config["arms_currency"]["symbol"].number_format($price,2).$str_gst_indicator.tohex('03');
			}else{
			    // member price different with non-member price
			    if(!$config['shuttle_sg_only_show_nett_price']){
                	$output .= "Normal: ". $config["arms_currency"]["symbol"] . " " . number_format($sku['default_price'], 2).$str_gst_indicator.tohex('0d');
                }
       			$output .= tohex('1b').tohex('42').tohex('31');
		    	$output .= tohex('1b').tohex('2c').tohex('30').tohex('50')."Nett";
		    	$output .= tohex('1b').tohex('2e').tohex('3B').$config["arms_currency"]["symbol"].number_format($price,2).$str_gst_indicator.tohex('03');
       			$output .= tohex('1b').tohex('42').tohex('30'); //font size normal
       			if(!$config['shuttle_sg_only_show_nett_price']){
			    	$output .= tohex('1b').tohex('2e').tohex('38')."Member: ". $config["arms_currency"]["symbol"] .number_format($sku['member_price'],2).$str_gst_indicator.tohex('03');
			    }
//              $output .= "Discount ".$sku['member_discount'].'%'.tohex('0d');

			}
			$output .= tohex('1b').tohex('42').tohex('31');
		}elseif ($sku['disc']){
			//category disc
	        $output.= "(Discount ".$sku['disc']."%)".tohex('0d');
	        if(!$config['shuttle_sg_only_show_nett_price']){
	        	$output .= "Normal: ". $config["arms_currency"]["symbol"] . " " . number_format($sku['default_price'], 2).tohex('0d');
	        }
   			$price = $sku['price'];

			$output .= tohex('1b').tohex('42').tohex('31');
	    	$output .= tohex('1b').tohex('2e').tohex('36')."Nett".tohex('03');
	    	$output .= tohex('1b').tohex('2e').tohex('38').$config["arms_currency"]["symbol"].number_format($price,2).$str_gst_indicator.tohex('03');
		}else{
			$price = $sku['price'];

			$output .= tohex('1b').tohex('42').tohex('31');
	    	$output .= tohex('1b').tohex('2e').tohex('36')."Nett".tohex('03');
	    	$output .= tohex('1b').tohex('2e').tohex('38').$config["arms_currency"]["symbol"].number_format($price,2).$str_gst_indicator.tohex('03');
		}
	}
	
	socket_write($msgsock, $output);
	$con->sql_close();
	unset($con);
}

function sig_handler($signo)
{
	global $sock;
	
	var_dump($sock);
	print "Closing socket " . socket_close($sock);
	echo "OK.\n\n";
	exit;
}

/*function ms($str,$null_if_empty=0)
{
	if (trim($str) === '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	$str = str_replace("\\", "\\\\", $str);
	return "'" . (trim($str)) . "'";
}*/

// setup signal handlers

//pcntl_signal(SIGHUP, "sig_handler");

function terminal_direct_check_price(){
	global $arg, $db_default_connection, $config;
	
	$bcode = trim($arg[2]);
	$item_code = trim($arg[3]);
	
	if(!$bcode)	die("No Branch Code\n");
	if(!$item_code)	die("No item Code\n");
	
	$con = new sql_db($db_default_connection[0], $db_default_connection[1], $db_default_connection[2], $db_default_connection[3], false);
	if(!$con->db_connect_id) {
	 	print mysql_error();
		return;
  	}
	$con->sql_query("select id from branch where code = ".ms($bcode));
    $branch_id = $con->sql_fetchfield(0);
    
    if (preg_match("/^28/",$item_code)) $item_code = substr($item_code,0,12);
    
    $params = array();
	$params['branch_id'] = $branch_id;
	$params['code'] = $item_code;
	$params['mysql_con'] = $con;
	
    $sku = check_price($params);
    
    print_r($sku);
}

?>
