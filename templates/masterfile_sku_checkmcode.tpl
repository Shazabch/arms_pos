{*9/26/2007 11:11:12 AM yinsee/gary- addded category id and brand id field *}{config_load file="site.conf"}<link rel="stylesheet" href="{#SITE_CSS#}" type="text/css"><div style="padding:10px"><h1>Check Article/MCode</h1><form name="f_a" action="masterfile_sku_application.php" method=post><input type=hidden name=a value="mcode_check"><input type=hidden name=vendor_id value="{$smarty.request.vendor_id}"><input type=hidden name=category_id value="{$smarty.request.category_id}"><input type=hidden name=brand_id value="{$smarty.request.brand_id}"><b>Vendor:</b> {$vendor}<br><p align=center>Enter the codes you want to verify (one at each line)<br><textarea name=list rows=10 cols=40>{$smarty.request.list}</textarea><br><br><input type=submit value="Check"> <input type=button onclick="window.close()" value="Close Window"></p></form>{if $msg}<h4>Check result</h4><ul>{$msg}</ul>{/if}</div>