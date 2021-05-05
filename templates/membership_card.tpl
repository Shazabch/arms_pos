{*

4/26/2017 8:26 AM Khausalya
- Enhanced changes from RM to use config setting. 

*}

{include file=header.tpl}
<script>
{literal}
function check_t()
{
	if (rdcbn_empty('atype',3,'You must select one of the application type'))
	{
		return false;
	}
	if (rdcbn_empty('ctype',3,'You must select one of the card type'))
	{
		return false;
	}
	if (empty(document.f_a.card_no, 'You must input the card number'))
	{
		return false;
	}
	return true;
}
{/literal}
</script>
<h1>{$config.membership_cardname} Application</h1>
<div id="errdiv" class=errmsg></div>
<form action="{$smarty.server.PHP_SELF}" name=f_a method=post onsubmit="return check_t()" target=_irs>
<input type=hidden name=a value="t">
<input type=hidden name=t value="apply">
<input type=hidden name=expiry value="{$history.expiry_date|default:0}">
<input type=hidden name=nric value="{$form.nric}">
<div class="stdframe">
{if $history}
<p>
<h4>Last {$config.membership_cardname} of {$form.nric}</h4>
<b>Card no:</b> {$history.card_no}<br>
<b>Issuing Branch:</b> {$history.branch_code}<br>
<b>Issue Date:</b> {$history.issue_date|date_format:"%e/%m/%Y"}<br>
<b>Expiry Date:</b> {$history.expiry_date|date_format:"%e/%m/%Y"}<br>
</p>
{/if}

<p>
<h5>Select application type</h5>
{if !$history}
<input id=atype1 type=radio name=atype value="N,+1 year,{$CHARGES_NEW_CARD}" checked> New application ({$config.arms_currency.symbol}{$CHARGES_NEW_CARD})<br>
{elseif $history.expiry_date < $smarty.now}
<input id=atype1 type=radio name=atype value="R,+1 year,{$CHARGES_RENEW_CARD}"> Renewal ({$config.arms_currency.symbol}{$CHARGES_RENEW_CARD})<br>
<input id=atype2 type=radio name=atype value="LR,+1 year,{$CHARGES_LOST_CARD+$CHARGES_RENEW_CARD}"> Lost + Renewal ({$config.arms_currency.symbol}{$CHARGES_LOST_CARD+$CHARGES_RENEW_CARD})<br>
{else}
<input id=atype1 type=radio name=atype value="R,+1 year,{$CHARGES_RENEW_CARD}"> Renewal ({$config.arms_currency.symbol}{$CHARGES_RENEW_CARD})<br>
<input id=atype2 type=radio name=atype value="L,0,{$CHARGES_LOST_CARD}"> Lost Card ({$config.arms_currency.symbol}{$CHARGES_LOST_CARD})<br>
<input id=atype3 type=radio name=atype value="LR,+1 year,{$CHARGES_LOST_CARD+$CHARGES_RENEW_CARD}"> Lost + Renewal ({$config.arms_currency.symbol}{$CHARGES_LOST_CARD+$CHARGES_RENEW_CARD})<br>
{/if}
</p>
<p>
<h5>Select a card type</h5>
<table class=body cellpadding=4><tr>
	<td>
	<img src=images/akad-R.gif onclick="check_field('ctype1')"><br>
	<input id=ctype1 type=radio name="ctype" value="R,0"> Red {$config.membership_cardname}
	</td><td>
	<img src=images/akad-G.gif onclick="check_field('ctype2')"><br>
	<input id=ctype2 type=radio name="ctype" value="G,0"> Green {$config.membership_cardname}
	</td><td>
	<img src=images/akad-B.gif onclick="check_field('ctype3')"><br>
	<input id=ctype3 type=radio name="ctype" value="B,0"> Blue {$config.membership_cardname}
	</td>
</tr></table>
</p>
<p>
<h5>Input {$config.membership_cardname} number</h5>
<input name=card_no size=20 maxlength=20 onBlur="uc(this)">
</p>
<p align=center><input type=submit value="Continue"></p>
</div>

<script>
document.getElementById('atype1').focus();
</script>

<div style="visibility:invisible"><iframe name=_irs width=1 height=1 frameborder=0></iframe></div>
{include file=footer.tpl}
