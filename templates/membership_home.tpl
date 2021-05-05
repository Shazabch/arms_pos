{*
06/29/2020 02:151 PM Sheila
- Updated button css.
*}

{include file=header.tpl}
<div align=center>
<table class=body cellpadding=0 cellspacing=0 border=0><tr>
<td align=center width=300> <!--background="images/akad02.jpg"-->
	<div style="padding:20px">
	<h1>{$config.membership_cardname} Membership</h1>
	<h2>({if $smarty.request.t eq 'apply'}
	Application &amp; Renewal
	{elseif $smarty.request.t eq 'history'}
	Check Points &amp; History
	{elseif $smarty.request.t eq 'update'}
	Update Information
	{/if})</h2>
	<br><br>
	<div class="stdframe">
	<b>Please scan {$config.membership_cardname} or IC number</b><br><br>
	<form name=f_i method=post>
	<input type=hidden name=a value='i'>
	<input name=nric size=30 onBlur="uc(this)"><br><br>
	<input class="btn btn-primary" type=submit value="Enter">
	</div>
	</form>

	</div>
</td>
<!--td valign=top><img src=images/akad01.jpg></td-->
</tr></table>
</div>
<script>
document.f_i.nric.focus();
</script>
{include file=footer.tpl}
