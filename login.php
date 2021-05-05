<?php
/*
5/21/2007 3:35:24 PM - yinsee
- add autologin checking and redirection

12/3/2007 3:35:40 PM gary
- comment out getuid set TERMINAL

2/14/2008 12:57:56 PM gary
- alter insert into session to replace.

12/27/2008 4:17:06 PM yinsee
- single server login with same http-port

4/22/2009 11:48:46 AM yinsee
- add admin login-as other user

3/25/2010 11:18:46 AM Andy
- add checking for test server port and include test_server_config.php

10/26/2010 12:19:45 PM yinsee
- default ticket 1 day expire

11/4/2010 3:07:08 PM Andy
- Fix "login as" bugs: sometime after user use "login as" and then click logout, it wont return to previous user but totally logout.

2/17/2011 11:52:11 AM Andy
- Add after login redirect user back to the page they has been kickout.

4/7/2011 2:59:33 PM Andy
- Add redirect user to requested page after login.

6/24/2011 4:23:11 PM Andy
- Make all branch default sort by sequence, code.

10/10/2011 11:37:59 AM Andy
- Add checking to prevent user to login as root user or themself.
- Add log when user login as failed.

12/14/2011 10:05:43 AM Justin
- Added login and logout handlers for Sales Agent.

7/10/2012 5:10 PM Andy
- Add can login using vendor portal key.

7/12/2012 4:07 PM Andy
- Add "Go to branch" feature for Vendor Portal.

9/11/2012 3:24 PM Andy
- Enhance vendor login ticket,email, link to debtor to saved by branch.

4/2/2013 5:50 PM Andy
- Add debtor login screen.

5/27/2013 2:14 PM Andy
- Enhance the debtor login to show error if user try to login with in-active debtor.

11/29/2013 11:23 AM Andy
- Enhance the multi server mode to allow some sub-branch to working in HQ server.

2/24/2014 4:24 PM Andy
- Add the COOKIE path to "/" to fixed the scorpio login bug.

7/25/2014 10:44:20 AM Andy
- Add to check if the server is armsgo then the login page will use https.

7/30/2014 10:44:14 AM Andy
- Add server port checking for 443

2014/8/19 16:04:30 PM Andy
- Change the hostname checking to use php gethostname()

8/21/2014 10:33:58 AM Andy
- Add the checking for url and only redirect to https if the url contain arms-go

06/02/2016 16:50 Edwin
- Bug fix on "Logout as" prompt error when users' login and username are different

6/3/2016 10:00 AM Andy
- Fix check function gethostname only exists in php 5.3.0 and above.

2/16/2017 11:54 AM Andy
- Fixed if user don't have the login privilege to other branch, the auto redict will casue the user logout.

4/10/2017 10:11 AM Justin
- Enhanced to have validation checking for "Terms & Conditions".

4/26/2017 12:44 PM Andy
- Add to check if the server is arms.com.my then the login page will use https.

5/9/2017 9:42 AM Andy
- Remove https gethostbyname checking for arms.com.my.

5/9/2017 3:57 PM Andy
- Enhanced to auto redirect ddns.my to arms.com.my

5/10/2017 5:12 PM Andy
- Enhanced to remove server port when redirect to https.

6/16/2017 3:45 PM Andy
- Enhanced not to do https checking if got config.https_redirect_disabled.
- Enhanced to append https port if found got config.https_port.

7/18/2017 2:14 PM Andy
- Fixed "Go to branch" at multi server mode failed to autologin if login from the server which contain https.

10/5/2017 4:48 PM Justin
- Enhanced to show custom header base on config.

4/18/2019 3:33 PM Andy
- Enhanced to show only HQ for consignment login.

11/5/2019 3:15 PM Justin
- Enhanced sales agent login process to similar with other login portals.

4/2/2020 1:23 PM William
- Enhanced to capture vendor portal login and logout log. 

4/3/2020 10:27 AM William
- Enhanced to capture debtor portal login and logout log. 
*/
if (isset($_REQUEST['getuid'])) 
{
	define('TERMINAL',1);
	define('NO_OB',1);
}
@include_once('test_server_config.php');
if (!defined('HQ_PORT')) define('HQ_PORT',2001);

//print_r($_REQUEST);
//print_r($_SERVER);

if (isset($_REQUEST['login_branch']) && ($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==HQ_PORT || $_SERVER['SERVER_PORT']==4000 || $_SERVER['SERVER_PORT'] == 443))
{
	// set the login branch in cookie
	setcookie('arms_login_branch', $_POST['login_branch'], 0, "/");
	//setcookie('arms_login_branch', $_REQUEST['login_branch']);
	$_COOKIE['arms_login_branch'] = $_REQUEST['login_branch'];
}

if($_SESSION['sa_ticket']){
	unset($_SESSION);
}

//print_r($_COOKIE);

include("include/common.php");


// check https on arms-go server
if(!$config['https_redirect_disabled'] && (version_compare(PHP_VERSION, '5.3.0', '>=')) && function_exists('gethostname')){
	//print_r($_SERVER);
	$HTTP_HOST = $_SERVER['HTTP_HOST'];
	if(strpos($HTTP_HOST, "ddns.my") !== false)	$HTTP_HOST = str_replace("ddns.my", "arms.com.my", $HTTP_HOST);
	if((strpos(gethostname(), "arms-go") !== false && strpos($HTTP_HOST, "arms-go") !== false) || ((strpos($HTTP_HOST, "arms.com.my") !== false)))
	{		
		if(!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] == 'off'){
			list($HTTP_HOST, $server_port) = explode(":", $HTTP_HOST);
			//print "HTTP_HOST = $HTTP_HOST, server_port = $server_port";
			if($config['https_port'])	$HTTP_HOST .= ":".$config['https_port'];
			$newURL = "Location: https://".$HTTP_HOST.$_SERVER['REQUEST_URI'];
			//print $newURL;
			header($newURL);
			exit;
		}
	}
}


//die("BRANCH_CODE = ".BRANCH_CODE);

//checking login for supplier
if (isset($_REQUEST['ac'])){
	$ip= $_SERVER['REMOTE_ADDR'];
	
	$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
	$r = $con->sql_fetchrow();
	$bid = $r[0];
	
	$ac = trim($_REQUEST['ac']);
	
	if($config['enable_vendor_portal'] && strlen($ac)==10){	// new vendor portal format
		// check ticket exists or not
		//$con->sql_query("select * from vendor_portal_info where login_ticket=".ms($ac));
		$con->sql_query("select vpbi.*, vpi.allowed_branches
		from vendor_portal_branch_info vpbi
		join vendor_portal_info vpi on vpi.vendor_id = vpbi.vendor_id
		where vpbi.branch_id=".mi($bid)." and vpbi.login_ticket=".ms($ac)." and vpbi.active=1");
		$vp = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($vp){	// correct login ticket
			$vp['allowed_branches'] = unserialize($vp['allowed_branches']);
			
			// check expire data
			if(strtotime(date("Y-m-d"))>strtotime($vp['expire_date'])){
				js_redirect(sprintf($LANG['VENDOR_PO_REQUEST_TICKET_EXPIRED'], 'TICKET_EXPIRED', BRANCH_CODE), "/index.php");
				exit;
			}
			
			// check allow branch
			if(!$vp['allowed_branches'][$bid]){
				js_redirect("You have no privilege to access this branch data.", "/index.php");
				exit;
			}
			
			// check vendor portal active status
			$con->sql_query("select active_vendor_portal from vendor where id=".$vp['vendor_id']);
			$tmp_vendor = $con->sql_fetchassoc();
			$con->sql_freeresult();
			
			if($tmp_vendor){
				if(!$tmp_vendor['active_vendor_portal']){
					js_redirect("Vendor Portal is in-active.", "/index.php");
					exit;
				}
			}else{
				js_redirect("Vendor Not Found.", "/index.php");
				exit;
			}
			
			// store in session
			$_SESSION['vendor_portal']['vendor_id'] = $vp['vendor_id'];
			$_SESSION['vendor_portal']['login_ticket'] = $ac;
			
			// update login info
			$upd = array();
			$upd['ssid'] = $ssid;
			$upd['last_login_ip'] = $ip;
			$upd['last_login'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update vendor_portal_info set ".mysql_update_by_field($upd)." where vendor_id=".mi($vp['vendor_id']));
			if($_SESSION['restore_request_uri']){
				$restore_request_uri = $_SESSION['restore_request_uri'];
				unset($_SESSION['restore_request_uri']);
				header("Location: ".$restore_request_uri);
			}else{
	            header("Location: /index.php");
			}
			log_vp($vp['vendor_id'], 'LOGIN', '', "Login Successful ($client_ip)");
			exit;
		}
	}else{	// old login ticket format
		$con->sql_query("select * from login_tickets where ac=".ms($_REQUEST['ac'])." and branch_id=$bid and active=1");
		$r=$con->sql_fetchrow();
		if($r){
			$ac=ms($r['ac']);
			$create_day = strtotime($r['added']);
			// default 1 day
			$valid_period=$create_day+(($config['po_vendor_ticket_expiry']?$config['po_vendor_ticket_expiry']:1)*86400);
			$today=strtotime(date("Y-m-d G:i:s"));
	
			if($valid_period>=$today){
		    	$con->sql_query("update login_tickets set last_update=CURRENT_TIMESTAMP, ssid=".ms($ssid).", access_ip=".ms($ip)." where ac=$ac");		
				$_SESSION['ticket'] = $r;
				header("Location: /vendor_po_request.home.php");
			}
			else{
		    	$con->sql_query("update login_tickets set last_update=CURRENT_TIMESTAMP, active=0 where ac=$ac");	
				js_redirect(sprintf($LANG['VENDOR_PO_REQUEST_TICKET_EXPIRED'], 'TICKET_EXPIRED', BRANCH_CODE), "/index.php");
			}		
		    exit;
		}
	}
	
	$smarty->assign("errmsg2", $LANG['VENDOR_PO_REQUEST_INVALID_TICKET']);
}
elseif(isset($_REQUEST['debtor_key'])){
	$ip= $_SERVER['REMOTE_ADDR'];
	
	$con->sql_query("select id from branch where code = ".ms(BRANCH_CODE));
	$r = $con->sql_fetchrow();
	$bid = $r[0];
	
	$debtor_key = trim($_REQUEST['debtor_key']);

	if($debtor_key){
		// check whether got debtor with this login ticket
		$con->sql_query("select * from debtor where active=1 and login_ticket=".ms($debtor_key));
		$debtor = $con->sql_fetchassoc();
		$con->sql_freeresult();
		
		if($debtor){
			// store in session
			$_SESSION['debtor_portal']['debtor_id'] = $debtor['id'];
			$_SESSION['debtor_portal']['login_ticket'] = $debtor_key;
			
			$upd = array();
			$upd['last_dp_login_ip'] = $ip;
			$upd['last_dp_login'] = 'CURRENT_TIMESTAMP';
			
			$con->sql_query("update debtor set ".mysql_update_by_field($upd)." where id=".mi($debtor['id']));
						
			if($_SESSION['restore_request_uri']){
				$restore_request_uri = $_SESSION['restore_request_uri'];
				unset($_SESSION['restore_request_uri']);
				header("Location: ".$restore_request_uri);
			}else{
	            header("Location: /index.php");
			}
			log_dp($debtor['id'], 'LOGIN', '', "Login Successful ($client_ip)");
			exit;
		}
	}
	
	// error
	$smarty->assign("deb_login_err", $LANG['DP_INVLID_LOGIN']);
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
	if (preg_match('/((http|https):\/\/[^:]+:\d+\/)/',$_SERVER['HTTP_REFERER'],$match))
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
	if(!trim($_REQUEST['login_as'])){
		log_br($sessioninfo['id'], 'LOGIN', 'NULL', "Invalid login to user '$_REQUEST[login_as]' ($client_ip)");
		die($LANG['INVALID_LOGIN_TRY_AGAIN']);
	}else{
		if(trim($_REQUEST['login_as']) == $sessioninfo['u']){
			die("Cannot login as yourself.");
		}
	}
	
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
	$newuser=check_login($_REQUEST['login_as'],MASTER_PASSWORD,$msg, 1);
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
		$_SESSION['no_log'] = 0;
		$newuser=check_login($_SESSION['admin_session']['l'],MASTER_PASSWORD,$msg);
		if (!$newuser)
		{
			die($msg); 
		}
		unset($_SESSION['admin_session']);
		//$_SESSION['no_log'] = 0;
	}
	header("Location: /");
	exit;
	
}

elseif (isset($_REQUEST['sa_ticket']) || $_SESSION['sa_ticket']){
	//$ip=ms($_SERVER['REMOTE_ADDR']);
	
	if(isset($_REQUEST['sa_ticket'])){
		$con->sql_query("select * from sa where ticket_no=".ms($_REQUEST['sa_ticket'])." and active=1");
		$r=$con->sql_fetchrow();
		if($r){
			//$ac=ms($r['sa_ticket']);
			$today=strtotime(date("Y-m-d H:i:s"));
			$expired_time = strtotime($r['ticket_valid_before']);

			if($expired_time>=$today){		
				$_SESSION['sa_ticket'] = $r;
				header("Location: /home.php");
			}else{
				$con->sql_query("update sa set last_update=CURRENT_TIMESTAMP, ticket_no = '', ticket_valid_before = '' where ticket_no=".ms($_REQUEST['sa_ticket']));	
				js_redirect(sprintf($LANG['SA_TICKET_EXPIRED'], 'TICKET_EXPIRED', BRANCH_CODE), "/index.php");
			}		
			exit;
		}
		$smarty->assign("errmsg3", $LANG['SA_INVALID_TICKET']);
	}else header("Location: /home.php");
}

if ($login)
{
	// autologin to other server
    if (isset($_REQUEST['server']))
	{
	    if (!isset($_REQUEST['redir'])) $_REQUEST['redir'] = '/';
	 	// same server, just redirect ;)
		if ($_REQUEST['server'] == BRANCH_CODE)
		{
			header("Location: ".$_REQUEST['redir']);
			exit;
		}
		elseif ($config['single_server_mode'])
		{
			$login_ret = $appCore->userManager->checkUserAllowLoginToBranch($sessioninfo['id'], get_branch_id($_REQUEST['server']), !is_intranet());
			if($login_ret['error']){
				js_redirect($login_ret['error'], "/index.php");
				exit;
			}
			
		    if ($_SERVER['SERVER_PORT']==80 || $_SERVER['SERVER_PORT']==HQ_PORT || $_SERVER['SERVER_PORT']==4000 || $_SERVER['SERVER_PORT']==2005 || $_SERVER['SERVER_PORT']==443)
		    {
		    	
				// change the login branch and redirect
				setcookie('arms_login_branch', $_GET['server'], 0, "/");
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
		else	// multi server
		{
			// got some branch is at hq server
			if($config['branch_at_hq']){
				if(BRANCH_CODE == 'HQ' || $config['branch_at_hq'][BRANCH_CODE]){	// currently at HQ, or the branch in hq
					if($_REQUEST['server'] == 'HQ' || $config['branch_at_hq'][$_REQUEST['server']]){	// this branch should login to HQ, or is login to hq, no need to change server
						// change the login branch and redirect
						setcookie('arms_login_branch', $_GET['server'], 0, "/");
						//setcookie('arms_login_branch', $_GET['server']);
						$_COOKIE['arms_login_branch'] = $_GET['server'];
						header("Location: ".$_REQUEST['redir']);
						exit;
					}
				}else{	// currently at other branch which is not in hq server
					if($config['branch_at_hq'][$_REQUEST['server']]){	// this branch should login to HQ
						$override_url = sprintf($config['no_ip_string'], 'hq');
					}
				}
			}
			$need_login_branch = false;
			if($override_url){
				$url = $override_url;
				$need_login_branch = true;
			}else{
				$url = sprintf($config['no_ip_string'],strtolower($_REQUEST['server']));
			}
            
			$url .= "login.php?auth=".$ssid;
		    $url .= "&redir=".urlencode($_REQUEST['redir']);
		    
		    if($need_login_branch){
		    	$url .= "&login_branch=".urlencode($_REQUEST['server']);
		    }
		    
		    
		    header("Location: ".$url);
		    exit;
		}
	}

	if (isset($_REQUEST['logout']))
	{
		$smarty->assign("usagetime", do_logout());
		$smarty->assign("PAGE_TITLE", "Log Out");
		$smarty->assign("sessioninfo", array());
		$smarty->display("logout.tpl");
		exit;
	}
	
	header("Location: /index.php");
	exit;
}

if (isset($_REQUEST['sa_logout']) && $sa_login)
{
	unset($_SESSION['sa_ticket']);
	$smarty->assign("sa_session", array());
	header("Location: /login.php");
	exit;
}elseif($sa_login){
	if (isset($_REQUEST['server'])){	// go to other branch
		if (!isset($_REQUEST['redir'])) $_REQUEST['redir'] = '/';
		
		if ($_REQUEST['server'] == BRANCH_CODE)
		{
			header("Location: ".$_REQUEST['redir']);
			exit;
		}elseif ($config['single_server_mode'])
		{
			setcookie('arms_login_branch', $_GET['server'], 0, "/");
		    //setcookie('arms_login_branch', $_GET['server']);
			$_COOKIE['arms_login_branch'] = $_GET['server'];
			header("Location: ".$_REQUEST['redir']);
			exit;
		}
		else
		{
			// currently not support multiple branch vendor login
            /*$url = sprintf($config['no_ip_string'],strtolower($_REQUEST['server']));
			$url .= "login.php?auth=".$ssid;
		    $url .= "&redir=".urlencode($_REQUEST['redir']);
		    header("Location: ".$url);*/
		    exit;
		}
	}

	header("Location: /index.php");
	exit;
}

// vendor portal
if(isset($_REQUEST['vp_logout']) && $vp_login){
	log_vp($vp_session['id'], 'LOGIN', '', "Logout Successful ($client_ip)");
	unset($_SESSION['vendor_portal']);
	header("Location: /login.php");
	exit;
}elseif($vp_login){
	if (isset($_REQUEST['server'])){	// go to other branch
		if (!isset($_REQUEST['redir'])) $_REQUEST['redir'] = '/';
		
		if ($_REQUEST['server'] == BRANCH_CODE)
		{
			header("Location: ".$_REQUEST['redir']);
			exit;
		}elseif ($config['single_server_mode'])
		{
			setcookie('arms_login_branch', $_GET['server'], 0, "/");
		    //setcookie('arms_login_branch', $_GET['server']);
			$_COOKIE['arms_login_branch'] = $_GET['server'];
			header("Location: ".$_REQUEST['redir']);
			exit;
		}
		else
		{
			// currently not support multiple branch vendor login
            /*$url = sprintf($config['no_ip_string'],strtolower($_REQUEST['server']));
			$url .= "login.php?auth=".$ssid;
		    $url .= "&redir=".urlencode($_REQUEST['redir']);
		    header("Location: ".$url);*/
		    exit;
		}
	}
	header("Location: /index.php");
	exit;
}

// debtor portal
if(isset($_REQUEST['dp_logout']) && $dp_login){
	log_dp($dp_session['id'], 'LOGIN', '', "Logout Successful ($client_ip)");
	unset($_SESSION['debtor_portal']);
	header("Location: /login.php");
	exit;
}elseif($dp_login){
	if (isset($_REQUEST['server'])){	// go to other branch
		if (!isset($_REQUEST['redir'])) $_REQUEST['redir'] = '/';
		
		if ($_REQUEST['server'] == BRANCH_CODE)
		{
			header("Location: ".$_REQUEST['redir']);
			exit;
		}elseif ($config['single_server_mode'])
		{
			setcookie('arms_login_branch', $_GET['server'], 0, "/");
		    //setcookie('arms_login_branch', $_GET['server']);
			$_COOKIE['arms_login_branch'] = $_GET['server'];
			header("Location: ".$_REQUEST['redir']);
			exit;
		}
		else
		{
			// currently not support multiple branch vendor login
            /*$url = sprintf($config['no_ip_string'],strtolower($_REQUEST['server']));
			$url .= "login.php?auth=".$ssid;
		    $url .= "&redir=".urlencode($_REQUEST['redir']);
		    header("Location: ".$url);*/
		    exit;
		}
	}

	header("Location: /index.php");
	exit;
}

if($_REQUEST['u']!= '' && $_REQUEST['p']!=''){
	if(!$_REQUEST['tnc']){
		$smarty->assign("errmsg", $LANG['LOGIN_TNC_REQUIRED']);
	}elseif (check_login($_REQUEST['u'], $_REQUEST['p'], $errmsg)){
	    if($_SESSION['restore_request_uri']){
			$restore_request_uri = $_SESSION['restore_request_uri'];
			unset($_SESSION['restore_request_uri']);
			header("Location: ".$restore_request_uri);
		}else{
            header("Location: /index.php");
		}
		
	    exit;
	}else $smarty->assign("errmsg", $errmsg);
}
elseif (isset($_REQUEST['u'])){
	$smarty->assign("errmsg", $LANG['INVALID_LOGIN_TRY_AGAIN']);
}

if(isset($_REQUEST['redir'])){
    if(trim($_REQUEST['redir']))	$_SESSION['restore_request_uri'] = $_REQUEST['redir'];
}

$filter = array();
$filter[] = "active=1";
if($config['consignment_modules']){
	$filter[] = "code='HQ'";
}
$str_filter = "where ".join(' and ', $filter);
$con->sql_query("select * from branch $str_filter order by sequence,code");
$smarty->assign("branch",$con->sql_fetchrowset());
$smarty->assign("PAGE_TITLE", "Login");

if($config['login_page_header']){
	$header_info = array();
	foreach($config['login_page_header'] as $arr_key=>$r){
		// need to know whether got image or not
		if($r['type'] == "image"){
			if($arr_key==0 && !$r['next_row']) $header_info['show_image_first'] = true;
			$header_info['have_image'] = true;
		}else $header_info['rowspan_count']++;
		
		if($r['next_row']) $header_info['next_row_count']++;
	}
	
	// need to unset the row count if found it is the same with next row count
	if($header_info['rowspan_count'] == $header_info['next_row_count']) unset($header_info['rowspan_count']);
	
	$smarty->assign("header_info", $header_info);
}

$smarty->display("login.tpl");
?>
