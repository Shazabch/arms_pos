<?php
declare(ticks = 1);

//pcntl_signal(SIGTERM, "sig_handler");
//pcntl_signal(SIGINT, "sig_handler");

include("../config.php");
include("../include/db.php");




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

$address = '192.168.1.254';
$port = 9101;

if (($sock = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)) < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($sock) . "\n";
    exit;
}

if (!socket_bind($sock, $address, $port)) {
    echo "socket_bind() failed\n";
    exit;
}

	print "listen\n";
	if (!socket_listen($sock, 5)) {
	    echo "socket_listen() failed\n";
		exit;
	}

do {

    print "waiting for connection\n";
	if (($msgsock = socket_accept($sock)) < 0) {
        echo "socket_accept() failed: reason: " . socket_strerror($msgsock) . "\n";
        break;
    }
    
    $pid = pcntl_fork();
	if ($pid == -1) {
	     die('could not fork');
	} else if ($pid) {
	     // we are the parent
	     //pcntl_wait($status); //Protect against Zombie children
	     print "forked $pid, parent should continue\n";
	     print memory_get_usage() . " memory used\n";
	} else {
	    process_child($msgsock);
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
    $msg = tohex('1b').tohex('42').tohex(30).'Welcome, loading...';
    socket_write($msgsock, $msg, strlen($msg));

    do {
        $buf = socket_read($msgsock, 2048, PHP_NORMAL_READ);

	    $output = tohex('1b')."%";
	    //socket_write($msgsock, $output);
		$talkback = "[".$buf."]";
        //socket_write($msgsock, $talkback, strlen($talkback));
        echo "child $pid received $buf\n";
        get_item($msgsock, $buf);
    } while (true);
    socket_close($msgsock);
}

function get_item($msgsock, $msg){
	global $con;
	
    $code = ms($msg);
    $branch_id = 1;
    
	$con->sql_query("
		select sku_items.id, sku_items.sku_item_code, sku_items.mcode, sku_items.artno, sku_items.link_code, sku_items.description, sku_items.selling_price as master_price, sku_items.sku_apply_items_id, sku_apply_items.photo_count, sku_items_price.price, sku_apply_items.photo_count, brand.description as brand, branch.code as branch_code, branch.ip as branch_ip
		from sku_items
			left join sku on sku_id = sku.id
			left join brand on brand_id = brand.id
			left join branch on apply_branch_id = branch.id
			left join sku_apply_items on sku_apply_items_id = sku_apply_items.id
			left join sku_items_price on sku_items_price.sku_item_id = sku_items.id and branch_id = $branch_id
		where sku_items.active=1 and (sku_items.mcode = $code or sku_items.link_code = $code or sku_items.sku_item_code = $code) order by sku_items.sku_item_code limit 1");
	$sku = $con->sql_fetchrow();

	if (!$sku)
	{
		$output = tohex('1b')."%";
		$output .= tohex('1b').tohex('42').tohex(30);
		$output .= $msg.tohex('0d');
		$output .= "Item Not Found";
		socket_write($msgsock, $output);
	}
	else
	{
	    // use master price if local price is not defined
		if ($sku['price']==0) $sku['price'] = $sku['master_price'];

		// check promotion table and calculate promotion price
		$con->sql_query("select p.title, p.date_from, p.date_to, p.time_from, p.time_to, pi.*
			from promotion p left join promotion_items pi on p.branch_id = pi.branch_id and p.id = pi.promo_id
			where
			promo_branch_id like '%".BRANCH_CODE."%' and
			pi.sku_item_id = $sku[id] and
			CURDATE() between date_from and date_to and p.approved = 1 and p.active = 1
			");

		$member = array();
		$non_member = array();
		while($p=$con->sql_fetchrow())
		{
			// member_disc_p	member_disc_a	non_member_disc_p	non_member_disc_a
			if ($p['member_disc_a']>0)
			{
				$member_price = $p['member_disc_a'];
			}
			elseif (strstr($p['member_disc_p'],'%'))
			{
				$member_price = $sku['price'] * (1 - doubleval($p['member_disc_p'])/100);
			}
			else
			{
				$member_price = $sku['price']  - doubleval($p['member_disc_p']);
			}
			$member_price = round($member_price,2);
			if ($p['non_member_disc_a']>0)
			{
				$non_member_price = $p['non_member_disc_a'];
			}
			elseif (strstr($p['non_member_disc_p'],'%'))
			{
				$non_member_price = $sku['price'] * (1 - doubleval($p['non_member_disc_p'])/100);
			}
			else
			{
				$non_member_price = $sku['price']  - doubleval($p['non_member_disc_p']);
			}
			$non_member_price = round($non_member_price,2);
			if ($member_price>$non_member_price) $member_price = $non_member_price;


			$member[] = $member_price;
			$non_member[] = $non_member_price;
		}

		$sku['member_price'] = @min($member);
		$sku['member_discount'] = 100-intval($sku['member_price']/$sku['price']*100);
		$sku['non_member_price'] = @min($non_member);
	    $sku['non_member_discount'] = 100-intval($sku['non_member_price']/$sku['price']*100);

	   // if ($sku['photo_count']>0)
	//		$sku['imgpath'] = get_branch_file_url($sku['branch_code'], $sku['branch_ip']);

	 //	$sku['photos'] = get_sku_item_photos($sku['id']);

		$output = tohex('1b')."%";
		$output .= tohex('1b').tohex('42').tohex(30);
		$output .= tohex('1b').tohex('24');
		$output .= $msg;
		$output .= $sku['description'].tohex('0d');
		if($sku['member_price']!=$sku['non_member_price']){
            $output .= 'Member Price: RM'.$sku['member_price'].tohex('0d')."( discount ".$sku['member_discount'].'%)'.tohex('0d');
		}
		
		$output .= tohex('1b').tohex('42').tohex('31');
    	$output .= tohex('1b').tohex('2e').tohex('38')." RM".number_format($sku['price'],2).tohex('03');
    
		socket_write($msgsock, $output);
		echo $output;
	}
}

function sig_handler($signo)
{
	global $sock;
	
var_dump($sock);
				print "Closing socket " . socket_close($sock);
				echo "OK.\n\n";
             exit;
}

function ms($str,$null_if_empty=0)
{
	if (trim($str) === '' && $null_if_empty) return "null";
	settype($str, 'string');
	$str = str_replace("'", "''", $str);
	$str = str_replace("\\", "\\\\", $str);
	return "'" . (trim($str)) . "'";
}

// setup signal handlers

//pcntl_signal(SIGHUP, "sig_handler");

?>
