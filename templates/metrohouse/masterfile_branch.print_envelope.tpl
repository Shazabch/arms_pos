{config_load file="site.conf"}

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<link rel="stylesheet" type="text/css" href="templates/print.css">

<body onload="window.print()">
<div style="height:6in;width:9in;border:0px solid black;">
	<table width="100%" height="80%">
	    <tr>
	        <td valign="middle" align="center">
	            <table>
	                <tr>
	                    <td align="left">
							<i>
								<h1>{$to_branch.description}</h1>
								<span style="font-size:12pt;">{$to_branch.address|nl2br}</span>
							</i>
				        </td>
				    </tr>
				</table>
			</td>
		</tr>
	</table>
	<img src="templates/metrohouse/address.jpg" width="500" height="100" />
</div>
</body>
</html>
