<?
/*
12/13/2013 3:31 PM Justin
- Enhanced to take away the compulsory checking for Email.

7/25/2017 4:46 PM Justin
- Enhanced to use email regular expression checking from global settings.
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('UPDATE_PROFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'UPDATE_PROFILE', BRANCH_CODE), "/index.php");

if (isset($_REQUEST['a']))
{
	$errmsg = array();
	switch ($_REQUEST['a'])
	{
	
		// update profile
		case 'm' :
			$id = intval($sessioninfo['id']);
		
			// save personal info
			$p = strval($_REQUEST['password']);
			$l = strval($_REQUEST['loginid']);
			$fn = strval($_REQUEST['fullname']);
			$e = strtolower(strval($_REQUEST['email']));

			// check inputs
			if ($p != '' && (strlen($p) < $MIN_PASSWORD_LENGTH || !(preg_match("/[0-9]/i", $p) && preg_match("/[a-z]/i", $p))))
			{
				$errmsg['a'][] = $LANG['USERS_INVALID_NEW_PASSWORD_PATTERN'];
			}

			if ($e == '')
			{
				//$errmsg['a'][] = $LANG['USERS_INVALID_NEW_EMAIL_EMPTY'];
			}
			elseif (!preg_match(EMAIL_REGEX, $e))
			{
				$errmsg['a'][] = sprintf($LANG['USERS_INVALID_NEW_EMAIL_PATTERN'], $e);
			}
			
			$con->sql_query("select * from user where l=".ms($l)." and id <> $id");
			if ($con->sql_numrows()>0)
			{
				$errmsg['a'][] = sprintf($LANG['USERS_INVALID_LOGIN_ID'], $l);
			}
			

			if ($errmsg['a'])
			{
				$smarty->assign('errmsg', $errmsg);
			}
			else
			{
				// update password if changed
				$crow = 0;
				if ($p != '')
				{
					$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, p = md5(".ms($p).") where id = $id");
					$crow += $con->sql_affectedrows();
				}

				// update the rest of info
				$con->sql_query("update user set last_update = CURRENT_TIMESTAMP, l = " . ms($l) . ", fullname = " . ms($fn) . ", email = ".ms($e)." where id = $id");
				$crow += $con->sql_affectedrows();

				if ($crow)
				{
					$changes = "";
					$uqf = array();
					foreach (preg_split("/\|/", $_REQUEST["changed_fields"]) as $ff)
					{
						// keep array
						if ($ff != "") $uqf[$ff] = 1;
					}
					$changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";

					log_br($id, 'UPDATE_PROFILE', $sessioninfo['id'], 'Account profile updated by ' . $sessioninfo[u] . $changes);
					$alert = $LANG['USERS_PROFILE_UPDATED'];
				}
				else
					$alert = $LANG['NO_CHANGES_MADE'];
				
			}
			break;

		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;

	}
	$smarty->assign("errmsg", $errmsg);
	$smarty->assign("msg", $msg);
}

$con->sql_query("select * from user where id = $sessioninfo[id]");
$smarty->assign("user", $con->sql_fetchrow());

$smarty->assign("PAGE_TITLE", "Update My Profile");
$smarty->display("my_profile.tpl");

if ($alert)
{
	print "\n<script>alert('$alert')</script>\n";
}
