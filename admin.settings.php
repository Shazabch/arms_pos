<?php
/*
12/28/2011 10:45:06 AM Andy
- Fix warning message cause by no parameter pass to constructor.
*/
include("include/common.php");
include("include/class.report.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");



class Settings extends Module
{
	var $valid_files = array('color.txt','size.txt');
	function _default()
	{
	    global $smarty;
	    
	    $file = $_REQUEST['file'];
	    if (!$file) die($this->display("header.tpl")."Unhandled Request");
		if (!in_array($file, $this->valid_files)) die($this->display("header.tpl")."Unhandled Request");
		if (!file_exists($file)){
			$fp = fopen($file, "w+") or die($this->display("header.tpl")."Couldn't create new file");
			fclose($fp);
		}
		

		$smarty->assign('PAGE_TITLE', "Settings ($file)");

		$this->display("header.tpl");

		if (isset($_REQUEST['content']))
		{
		    // save the file...
		    $content=strtoupper($_REQUEST['content']);
		    
   			file_put_contents($file,$content);

		}
		print "<div class=\"breadcrumb-header justify-content-between\">";
			print "<div class=\"my-auto\">";
				print "<div class=\"d-flex\">";
				print "<h4 class=\"content-title mb-0 my-auto ml-4 text-primary\">Settings (".$file.")</h4>";
		print "</div></div></div>";
				
		
		print "<div class=\"card mx-3\">";
		print "<div class=\"card-body\">";
		print "<table width=40% border=0><tr><td>";
	    print "<form method=Post><textarea class=\"form-control\" style='width:100%;height:200px' name='content'>";
	    print htmlentities(file_get_contents($file));
	    print "</textarea></td>";
		print "<tr><td align='center'><input class=\"btn btn-primary\" type=submit value='Save Records'></td>";
 	    print "<input type=hidden name=file value=".$file.">";
		print "</tr></form></table></div></div>";
	    
		$this->display("footer.tpl");
	}
}

$report = new Settings('Settings');


?>
