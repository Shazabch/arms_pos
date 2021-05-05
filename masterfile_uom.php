<?

/*
2/5/2014 11:39 AM Fithri
- add more / missing details in user log

1/7/2019 3:47 PM Andy
- Enhanced to load how many sku using the uom.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('MASTERFILE')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MASTERFILE', BRANCH_CODE), "/index.php");

$smarty->assign("PAGE_TITLE", "UOM Master File");

if (isset($_REQUEST['a']))
{
	$id = intval($_REQUEST['id']);
	switch ($_REQUEST['a'])
	{
		case 'a':
			if (!privilege('MST_UOM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_UOM', BRANCH_CODE), "/index.php");
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				$con->sql_query("insert into uom " . mysql_insert_by_field($form, array('code', 'description', 'fraction')));
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'UOM create ' . $form['code']);
				load_table();
				print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTUOM_NEW_RECORD_ADDED]');</script>\n";
			}
			exit;
		case 'e':
			if (!privilege('MST_UOM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_UOM', BRANCH_CODE), "/index.php");
   			$con->sql_query("select * from uom where id = $id");
			if ($con->sql_numrows()<=0)
			{
				print "<script>alert('Invalid uom ID: $id');</script>\n";
				exit;
			}
			IRS_fill_form("f_b", array("code", "description", "fraction"), $con->sql_fetchrow());
			exit;
		case 'v':
			if (!privilege('MST_UOM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_UOM', BRANCH_CODE), "/index.php");
			$con->sql_query("update uom set active = ".mb($_REQUEST['v'])." where id = $id");
			$con->sql_query("select code from uom where id = $id");
			$v1 = $con->sql_fetchassoc();
			if ($_REQUEST['v'])
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'UOM activate ' . $v1['code']);
			else
				log_br($sessioninfo['id'], 'MASTERFILE', 0, 'UOM deactivate ' . $v1['code']);
			load_table();
			exit;
		case 'u':
			if (!privilege('MST_UOM')) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'MST_UOM', BRANCH_CODE), "/index.php");
			$form = $_REQUEST;
			$errmsg = validate_data($form);
			if ($errmsg)
			{
				IRS_dump_errors($errmsg);
			}
			else
			{
				// store basic info
				$con->sql_query("update uom set code = ".ms($form['code']).", description = ".ms($form['description']).", fraction = ".mf($form['fraction'])." where id = $id");

				if ($con->sql_affectedrows()>0)
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

					log_br($sessioninfo['id'], 'MASTERFILE', 0, 'UOM update information ' . $form['code'] . $changes);
					load_table();
					// saved. back to front page
					print "<script>parent.window.hidediv('ndiv');\nalert('$LANG[MSTUOM_DATA_UPDATED]');</script>";
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


load_table(true);
$smarty->display("masterfile_uom_index.tpl");

function load_table($sqlonly = false)
{
	global $con, $smarty;

	$con->sql_query("select uom.*, (select count(*) from sku_items si where si.packing_uom_id=uom.id limit 1) as used_sku_count
		from uom 
		order by uom.id");
	$smarty->assign("uom", $con->sql_fetchrowset());
	$con->sql_freeresult();
	
	if(!$sqlonly){
		$smarty->display("masterfile_uom_table.tpl");
	}	
}


function validate_data(&$form)
{
	global $LANG, $con, $id;

	$errm = array();
	if ($form['code'] == '')
		$errm[] = $LANG['MSTUOM_CODE_EMPTY'];
	$form['code'] = strtoupper($form['code']);
	// if old code != new code, check new code exists
	$con->sql_query("select * from uom where id <> $id and code = " . ms($form['code']));
	if ($con->sql_numrows() > 0)
	{
		$errm[] = sprintf($LANG['MSTUOM_CODE_DUPLICATE'], $form['code']);
	}

	if ($form['description'] == '')
		$errm[] = $LANG['MSTUOM_DESCRIPTION_EMPTY'];
	if ($form['fraction'] == '')
		$errm[] = $LANG['MSTUOM_FRACTION_EMPTY'];

	return $errm;
}

?>

