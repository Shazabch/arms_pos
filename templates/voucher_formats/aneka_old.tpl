{*
4/28/2011 5:34:14 PM Alex
- create by me
*}

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
.margin_offset div {
	border:0px solid #000;
}
.margin_offset {
	margin-top: -0.2cm;
	margin-left: 0cm;
}

.sample {
	width: 170mm;
	height: 82.4mm;
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 8pt;
	float: left;
	margin-left:5mm;
	margin-top:41mm;
}

</style>
{/literal}

{section name=page loop=$pages.sheet}
<div class=printarea>
	<div class="margin_offset sample">
		{section name=row loop=$pages.row}
			<div class=barcode>*{$voucher[page][row].barcode_voucher_prefix}{$voucher[page][row].secur_barcode}*</div>
		{/section}
	</div>
</div>
{/section}
