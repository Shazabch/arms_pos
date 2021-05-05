<?
/*
6/24/2011 5:08:05 PM Andy
- Make all branch default sort by sequence, code.

3/15/2012 11:05:32 AM Justin
- Added "/pda" to redirect user back to pda login menu page.

2/24/2014 4:24 PM Andy
- Add the COOKIE path to "/" to fixed the scorpio login bug.

7/30/2014 10:44:14 AM Andy
- Add server port checking for 443
*/
//print_r($_SERVER);
if (isset($_REQUEST['getuid'])) 
{
	define('TERMINAL',1);
	define('NO_OB',1);
}
define('HQ_PORT',2001);

if (isset($_POST['login_branch']) && ($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==HQ_PORT || $_SERVER['SERVER_PORT']==4000 || $_SERVER['SERVER_PORT']==443))
{	
	// set the login branch in cookie
	setcookie('arms_login_branch', $_POST['login_branch'], 0, "/");
	$_COOKIE['arms_login_branch'] = $_POST['login_branch'];
	//$_SESSION['arms_login_branch'] = $_COOKIE['arms_login_branch'];
}

include("common.php");
//print_r($_COOKIE);
//checking login for supplier
if (isset($_REQUEST['ac'])){
	$ip=ms($_SERVER['REMOTE_ADDR']);

	$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
	$r = $con->sql_fetchrow();
	$bid = $r[0];

	$con->sql_query("select * from login_tickets where ac=".ms($_REQUEST['ac'])." and branch_id=$bid and active");
	$r=$con->sql_fetchrow();
	if($r){
		$ac=ms($r['ac']);
		$create_day = strtotime($r['added']);
		$valid_period=$create_day+($config['po_vendor_ticket_expiry']*86400);
		$today=strtotime(date("Y-m-d G:i:s"));

		if($valid_period>=$today){
	    	$con->sql_query("update login_tickets set last_update=CURRENT_TIMESTAMP, ssid=".ms($ssid).", access_ip=$ip where ac=$ac");
			$_SESSION['ticket'] = $r;
			header("Location: /vendor_po_request.home.php");
		}
		else{
	    	$con->sql_query("update login_tickets set last_update=CURRENT_TIMESTAMP, active=0 where ac=$ac");
			js_redirect(sprintf($LANG['VENDOR_PO_REQUEST_TICKET_EXPIRED'], 'TICKET_EXPIRED', BRANCH_CODE), "/index.php");
		}
	    exit;
	}
	$smarty->assign("errmsg2", $LANG['VENDOR_PO_REQUEST_INVALID_TICKET']);
}

elseif (isset($_REQUEST['getuid']))
{
	// return the userid that is associated with this session to remote calling server
	$con->sql_query("select * from session where ssid = ".ms($_REQUEST['getuid']));
	$r=$con->sql_fetchrow();
	if ($r)
	{
		print "$r[user_id]";
	}
	else
	{
	    print "FAILED";
	}
	exit;
}

elseif (isset($_REQUEST['auth']))
{
	// verify the SSID we sent, and give us the login access of this user

	// find calling server
	if (preg_match('/(http:\/\/[^:]+:\d+\/)/',$_SERVER['HTTP_REFERER'],$match))
	{
	    $from = $match[1];
	    //$con->sql_query("select id from branch where ip like '$_SERVER[REMOTE_ADDR]:%'");
		// scanning ip from branch database for $_SERVER[REMOTE_ADDR]
	    /*if ($con->sql_numrows()<=0)
	    {
	        // this IP is no in database
			header("Location: /");
	    	exit;
		}*/
	}
	else
	{
		header("Location: /");  // reject malformed URL
		exit;
	}


	//check calling server for user-id
	$ret = join('',file($from . "login.php?getuid=$_REQUEST[auth]"));

	// no session id associated
	if ($ret == 'FAILED')
	{
		$smarty->assign("errmsg", $LANG['AUTO_LOGIN_FAILED']);
	}
	else
	{
	    $user_id = intval($ret);
	    $con->sql_query("delete from session where ssid = ".ms($ssid));
	    //$con->sql_query("insert into session (user_id, ssid) values ($user_id, ".ms($ssid).")");
	    $con->sql_query("replace into session (user_id, ssid) values ($user_id, ".ms($ssid).")");
	    log_br($user_id,"LOGIN","","Autologin from $_SERVER[HTTP_REFERER]");
	    header("Location: $_REQUEST[redir]");
		exit;
	}
}
elseif (isset($_REQUEST['login_as']))
{
	if (!isset($_SESSION['admin_session']))
		$level = $sessioninfo['level'];
	else
		$level = $_SESSION['admin_session']['level'];

	// if user is admin
	if ($level<1100) { //Not allowed
		die($LANG['PERMISSION_DENIED']);
		exit;
	}
	//become the user, set no log
	$newuser=check_login($_REQUEST['login_as'],MASTER_PASSWORD,$msg);
	if (!$newuser)
	{
		die($msg);
	}
	if (!isset($_SESSION['admin_session'])) $_SESSION['admin_session'] = $sessioninfo;
	print 'OK';
	exit;

}

elseif (isset($_REQUEST['logout_as']))
{
	if (isset($_SESSION['admin_session']))
	{
		//restore the user, unset no_log
		$newuser=check_login($_SESSION['admin_session']['u'],MASTER_PASSWORD,$msg);
		if (!$newuser)
		{
			die($msg);
		}
		unset($_SESSION['admin_session']);
		$_SESSION['no_log'] = 0;
		//print_r($_SESSION);
	}
	header("Location: /");
	exit;

}

if ($login)
{
	
	
	// autologin to other server
    if (isset($_REQUEST['server']))
	{
	    if (!isset($_REQUEST['redir'])) $_REQUEST['redir'] = '/pda/home.php';

	 	// same server, just redirect ;)
		if ($_REQUEST['server'] == BRANCH_CODE)
		{
			header("Location: ".$_REQUEST['redir']);
			exit;
		}
		elseif ($config['single_server_mode'])
		{
		    if ($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==HQ_PORT || $_SERVER['SERVER_PORT']==4000 || $_SERVER['SERVER_PORT']==443)
		    {
				// change the login branch and redirect
				setcookie('arms_login_branch', $_POST['login_branch'], 0, "/");
				//setcookie('arms_login_branch', $_GET['server']);
				$_COOKIE['arms_login_branch'] = $_GET['server'];
				header("Location: ".$_REQUEST['redir']);
			}
			else
			{
	            // get the local port and redirect
	            $con->sql_query("select id from branch where code = ".ms($_REQUEST['server']));
	            $r = $con->sql_fetchrow();
	            if (!$r) die("Invalid Branch $_REQUEST[server]");
	            $port = $config['single_server_port_begin'] + $r[id];
                $url = "http://$_SERVER[SERVER_NAME]:$port/";
				$url .= "login.php?auth=".$ssid;
			    $url .= "&redir=".urlencode($_REQUEST['redir']);
				header("Location: ".$url);
			}
			exit;
		}
		else
		{
            $url = sprintf($config['no_ip_string'],strtolower($_REQUEST['server']));
			$url .= "login.php?auth=".$ssid;
		    $url .= "&redir=".urlencode($_REQUEST['redir']);
		    header("Location: ".$url);
		    exit;
		}
	}

	if (isset($_REQUEST['logout']))
	{
		$smarty->assign("usagetime", do_logout());
		/*$smarty->assign("PAGE_TITLE", "Log Out");
		$smarty->assign("sessioninfo", array());
		$smarty->display("logout.tpl");
		exit;*/
	}

	header("Location: /pda/index.php");
	exit;
}

if ($_REQUEST['u']!= '' && $_REQUEST['p']!='')
{
	if (check_login($_REQUEST['u'], $_REQUEST['p'], $errmsg))
	{
	    header("Location: /pda/index.php");
	    exit;
	}
	else
		$smarty->assign("errmsg", $errmsg);
}
elseif (isset($_REQUEST['u']))
{
	$smarty->assign("errmsg", $LANG['INVALID_LOGIN_TRY_AGAIN']);
}

$con->sql_query("select * from branch where active=1 order by sequence,code");
$smarty->assign("branch",$con->sql_fetchrowset());
$smarty->assign("PAGE_TITLE", "Login");
$smarty->display("login.tpl");
?>
