{config_load file="site.conf"}
{if !$skip_header}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<link rel="stylesheet" type="text/css" href="templates/print.css">

<body onload="window.print()">
{/if}

{literal}
<script>
</script>
<style>
#tbl_voucher td
{
	padding:0; margin: 0; border:none;
	background:transparent;
	font:normal 18px Arial;
}

#margin_offset div {
	border:0px solid #000;
}
#margin_offset {
	margin-top: -0.6cm;
	margin-left: 1.2cm;
}

#sample {
	width:7in;
	height:3.5in;
	overflow:hidden;
	background:url(/images/cheque.jpg1) left 0in;
}
#vendor
{
	font-size:10pt;
	margin-top:1.23in;
	margin-left:0.8in;
	height:0.29in;
}

#amount_str
{
	font-size:10pt;
	/*margin-top:1.52in;*/
	margin-left:0.30in;
	/*width:11cm;*/
	line-height:27pt;
}

#amount
{
	font-size:14pt;
	margin-top:0.06in;
	margin-left:0.45in;
	/*width:5cm;*/
	line-height:25pt;
}

#date
{
	font-size:14pt;
	margin-top:0.7in;
	margin-left:0.65in;
	height:0.4in;
	/*width:1.5cm;*/
	line-height:25pt;
	letter-spacing: 14pt;
}

#line
{
	font-size:14pt;
	margin-top:0.14in;
	margin-left:0.1in;
	/*height:0.46in;*/
	/*line-height:25pt;*/
}
</style>
{/literal}

<div class=printarea>
<div id=margin_offset>
<div id=sample>
<div style="float:left;width:4.49in;">
<div id=vendor>{$form.issue_name|default:$form.vendor}</div>
<div id=amount_str>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{$form.total_in_words|upper}</div>
</div>
<div style="float:left;">
<div id=date>{$form.payment_date|date_format:$config.dat_format}</div>
<div id=line>xxxxxxxxxxxxxxxxxxxxxx</div>
<div id=amount>**{$form.total|number_format:2}**</div>
</div>
<br style="clear:both">
</div>
</div>
</div>
