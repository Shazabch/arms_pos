{*
9/3/2010 10:45:52 AM Andy
- Fix wording error.
- Add branch code for deliver to branch.

1/20/2011 4:42:19 PM Andy
- Add feature to deliver to "open".

7/15/2011 11:36:16 AM Andy
- Add include 'header.print.tpl' for all printing templates, added support charset utf8.

10/30/2018 10:28 AM Justin
- Enhanced to show Branch Company Registration No. after company name.
*}
{config_load file="site.conf"}
{if !$skip_header}
	{include file='header.print.tpl'}

<style>

</style>
<body onload="window.print()">
{/if}

<div class="printarea">
	<table width="100%">
		<tr>
		    <td><img src="{get_logo_url}" height="80" hspace="5" vspace="5" /></td>
		    <td width=100%>
			<h2>{$from_branch.description} {if $from_branch.company_no}({$from_branch.company_no}){/if}</h2>
			{$from_branch.address|nl2br}<br>
			Tel: {$from_branch.phone_1}{if $from_branch.phone_2} / {$from_branch.phone_2}{/if}
			{if $from_branch.phone_3}
			&nbsp;&nbsp; Fax: {$from_branch.phone_3}
			{/if}
			</td>
			<td>
			    <table class="xlarge">
				<tr>
					<td colspan=2 nowrap><div style="background:#000;padding:4px;color:#fff" align=center><b>TRANSPORT NOTE</b></div></td>
				</tr>
				<tr><td>Date</td><td>: {$smarty.now|date_format:$config.dat_format}</td></tr>
			  	</table>
			</td>
		</tr>
	</table>
	<p><i>Kindly deliver the following cargo to the address below:</i></p>
	<div style="border:1px solid #000; padding:5px;float:left;min-width:130px;">
		{if $form.deliver_type eq 'branch'}
			<b>{$to_branch.code} - {$to_branch.description}</b><br>
			{$to_branch.address|nl2br}<br>
			Tel: {$to_branch.phone_1|default:"-"}{if $to_branch.phone_2} / {$to_branch.phone_2}{/if}
			{if $to_branch.phone_3}<br>Fax: {$to_branch.phone_3}{/if}
		{elseif $form.deliver_type eq 'open'}
		    <b>{$form.open.name|default:'-'}</b><br>
		    {$form.open.address|nl2br}
		{/if}
	</div><br style="clear:both;" /><br />
	
	<b>DO No:</b> {$form.do_no}
	<p>Carton Size:</p>
	<table>
	    <tr>
	        <td width="50"><b>S</b></td>
	        <td width="5">=</td>
	        <td width="50" align="right">{$form.carton_s|number_format}</td>
	    </tr>
	    <tr>
	        <td width="50"><b>M</b></td>
	        <td width="5">=</td>
	        <td width="50" align="right">{$form.carton_m|number_format}</td>
	    </tr>
	    <tr>
	        <td width="50"><b>L</b></td>
	        <td width="5">=</td>
	        <td width="50" align="right">{$form.carton_l|number_format}</td>
	    </tr>
	    <tr>
	        <td width="50"><b>XL</b></td>
	        <td width="5">=</td>
	        <td width="50" align="right">{$form.carton_xl|number_format}</td>
	    </tr>
	    <tr>
	        <td colspan="4" style="border-top:1px dashed black;line-height:2px;">&nbsp;</td>
	    </tr>
	    <tr>
	        <td><b>Total</b></td>
	        <td>=</td>
	        <td align="right">{$form.carton_s+$form.carton_m+$form.carton_l+$form.carton_xl|number_format}</td>
	        <td>Ctn(s)</td>
	    </tr>
	</table>
	<br /><br />
	ACKNOWLEDGE BY (CHOP & SIGN)
	<br />{$form.transporter}
</div>
