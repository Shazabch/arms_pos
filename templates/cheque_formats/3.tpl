<!--RHB BANK CHEQUE PRINTING TEMPLATE-->
{config_load file="site.conf"}
{if !$skip_header}
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<link rel="stylesheet" type="text/css" href="templates/print.css">

<body onload="window.print()">

{literal}
<style>

#margin_offset {
	/*
	margin-top:-0.4cm;
	margin-left:1.2cm;
	*/
	margin-top:{/literal}{$margin.top|default:5.7}cm{literal};	
	margin-left:{/literal}{$margin.left|default:11.4}cm{literal};
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
	margin-top:1.18in;
	margin-left:0.70in;
	height:0.29in;
}

#amount_str
{
	font-size:10pt;
	margin-left:0.33in;
	line-height:27pt;
}

#amount
{
	font-size:14pt;
	margin-top:0.1in;
	margin-left:0.44in;
	line-height:25pt;
}

#date
{
	font-size:14pt;
	margin-top:0.63in;
	margin-left:0.65in;
	height:0.4in;
	line-height:25pt;
	letter-spacing: 14pt;
}

#line
{
	font-size:14pt;
	margin-top:0.12in;
	margin-left:1.55in;
}
</style>
{/literal}
{/if}

<div class=printarea>
<img src=/ui/pixel.gif width=1 height=1>
<div id=margin_offset>
<div id=sample>
<div style="float:left;width:4.49in;">
<div id=vendor>{$form.issue_name|default:$form.vendor}</div>
<div id=amount_str>
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
{$form.total_in_words|upper}</div>
</div>
<div style="float:left">
<div id=date>{*$form.payment_date|date_format:$config.dat_format*}</div>
<div id=line>XXXXXX</div>
<div id=amount>**{$form.total|number_format:2}**</div>
</div>
</div>
</div>
</div>
{assign var=skip_header value=1}
