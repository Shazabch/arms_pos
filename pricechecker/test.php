<?php
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

function tohex($str){
	return  chr(hexdec($str));
}

error_reporting(E_ALL);

echo "<h2>TCP/IP Connection</h2>\n";

/* Get the port for the WWW service. */
//$service_port = getservbyname('www', 'tcp');
$service_port = 9101;

/* Get the IP address for the target host. */
//$address = gethostbyname('www.example.com');
$address = '192.168.1.111';

/* Create a TCP/IP socket. */
$socket = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);
if ($socket < 0) {
    echo "socket_create() failed: reason: " . socket_strerror($socket) . "\n";
} else {
    echo "OK.\n";
}

echo "Attempting to connect to '$address' on port '$service_port'...";
$result = socket_connect($socket, $address, $service_port);
if ($result < 0) {
    echo "socket_connect() failed.\nReason: ($result) " . socket_strerror($result) . "\n";
} else {
    echo "OK.\n";
}

/*$in = "HEAD / HTTP/1.1\r\n";
$in .= "Host: www.example.com\r\n";
$in .= "Connection: Close\r\n\r\n";
$out = '';

echo "Sending HTTP HEAD request...";
socket_write($socket, $in, strlen($in));
echo "OK.\n";*/

echo "Reading response:\n\n";
while ($out = socket_read($socket, 2048)) {
    echo $out;
    //$code = tohex('1b')."%".tohex('0d')."test";
    //echo $code;
    /*$output = tohex('1b').tohex('42').tohex(30);
    $output .= tohex('1b').tohex('24');
    $output .= $out;
    //$output .= tohex('0d');
    $output .= '1 qty';
    $output .= tohex('1b').tohex('42').tohex('31');
    $output .= tohex('1b').tohex('2e').tohex('38')." $ 1.99 ".tohex('03');*/
    
    //socket_write ($socket, $output);

    $code = $out;
    
    $code = ms($code);

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
		$output .= $out;
		$output .= "Item Not Found";
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

		$smarty->assign("sku", $sku);
	    $smarty->display("check.detail.tpl");
	}
    echo "\n";
}

echo "Closing socket...";

socket_close($socket);
echo "OK.\n\n";


?>
