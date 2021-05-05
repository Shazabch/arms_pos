<?
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

$smarty->assign("PAGE_TITLE", "Brand Group Master File");


if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	switch ($_REQUEST['a'])
	{
		case 'a':
			if (!privilege('MST_BRANDGROUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRANDGROUP', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$con->sql_query("insert into brgroup " . mysql_insert_by_field($form, array('code', 'description')));
				$id = $con->sql_nextid();
				foreach ($form['brands'] as $v)
				{
					$con->sql_query("insert into brand_brgroup (brgroup_id, brand_id) values ($id, " . ms($v) . ")");
				}
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand Group create ' . $form['code']);
				load_table();
				print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTBRGROUP_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
		case 'e':
			if (!privilege('MST_BRANDGROUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRANDGROUP', BRANCH_CODE), "/index.php");

			$con->sql_query("select * from brgroup where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid Brand-Group ID: $id');</script>\n";
				exit;
			}
			$form = $con->sql_fetchrow();
			$con->sql_query("select brand_id from brand_brgroup where brgroup_id = $id");
			$brands = "|";
			while ($r = $con->sql_fetchrow())
			{
				$brands .= $r['brand_id'] . "|";
			}
			$form['brands'] = $brands;
			IRS_fill_form("f_b", array("code", "description", "_brands"), $form);
			exit;
		case 'v':
			if (!privilege('MST_BRANDGROUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRANDGROUP', BRANCH_CODE), "/index.php");

			$con->sql_query("update brgroup set active = ".mb($_REQUEST['v'])." where id = $id");
			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand Group activate ' . $_REQUEST['code']);
			else
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand Group deactivate ' . $_REQUEST['code']);
			load_table();
			exit;
		case 'u':
			if (!privilege('MST_BRANDGROUP')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_BRANDGROUP', BRANCH_CODE), "/index.php");

			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				// store basic info
				$con->sql_query("update brgroup set code = ".ms($form['code']).", description = ".ms($form['description'])." where id = $id");
				$af1 = $con->sql_affectedrows();

				$sql = '';
				foreach ($form['brands'] as $v)
				{
					if ($sql != '') $sql .= ",";
					$sql .= "($id, " . ms($v) . ")";
				}
				$con->sql_query("delete from brand_brgroup where brgroup_id = $id");
				$con->sql_query("insert into brand_brgroup (brgroup_id, brand_id) values $sql");
				$af2 = $con->sql_affectedrows();

				if ($af1>0 || $af2>0)
				{
					if ($af1 > 0)
					{
						// code changed
						$changes = "";
						foreach (preg_split("/\|/", $form["changed_fields"]) as $ff)
						{
							// strip array
							$ff = preg_replace("/\[.*\]/", '', $ff);
							if ($ff != "") $uqf[$ff] = 1;
						}
						$changes .= "\nEdited fields: (" . join(", ", array_keys($uqf)) . ")";

						log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand Group update information ' . $form['code'] . $changes);
					}

					/*if ($af2 > 0)
					{
						log_br($sessioninfo['id'], 'MASTERFILE', 0, 'Brand Group update brand table for ' . $form['code']);
					}*/

					load_table();
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTBRGROUP_DATA_UPDATED]');</script>";
				}
				else
					print "<script>parent.window.hidediv('ndiv');alert('$LANG[NO_CHANGES_MADE]');</script>";
			}
			exit;
		default:
			print "<h3>Unhandled Request</h3>";
			print_r($_REQUEST);
			exit;
	}

}

$con->sql_query("select * from brand where active order by description");
$smarty->assign("brands", $con->sql_fetchrowset());
load_table(true);
$smarty->display("masterfile_brgroup_index.tpl");

function get_brands($id)
{
	global $con;
	$con->sql_query("select brand.description as d from brand_brgroup left join brand on brand_id = brand.id where brgroup_id = $id order by d");
	$names = array();
	while ($r=$con->sql_fetchrow())
	{
	    $names[] = $r[0];
	}
	return join(", ", $names);
}


function load_table($sqlonly = false)
{
	global $con, $smarty;

	$res = $con->sql_query("select * from brgroup order by description");
	$arr = array();
	while ($r=$con->sql_fetchrow($res))
	{
		$r['brands'] = get_brands($r['id']);
	    $arr[] = $r;
	}
	$smarty->assign("brgroups", $arr);
	if (!$sqlonly) $smarty->display("masterfile_brgroup_table.tpl");
}


function validate_data(&$form)
{
	global $LANG, $con, $id;

	$errm = array();
	if ($form['code'] == '')
		$errm[] = $LANG['MSTBRGROUP_CODE_EMPTY'];
	$form['code'] = strtoupper($form['code']);
	// if old code != new code, check new code exists
	$con->sql_query("select * from brgroup where id <> $id and code = " . ms($form['code']));
	if ($con->sql_numrows() > 0)
	{
		$errm[] = sprintf($LANG['MSTBRGROUP_CODE_DUPLICATE'], $form['code']);
	}

	if ($form['description'] == '')
		$errm[] = $LANG['MSTBRGROUP_DESCRIPTION_EMPTY'];
	//break brand into array
	foreach (preg_split("/\|/", $form['_brands']) as $v)
	{
		if ($v === '') continue;
		$form['brands'][] = $v;
	}
	return $errm;
}

?>

