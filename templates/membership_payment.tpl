{*
4/19/2017 9:37 AM Khausalya
- Enhanced changes from RM to use config setting. 
*}


{include file=header.tpl}
<div align=center>
<h1>{$config.membership_cardname} Application Completed</h1>
<h2>Please collect {$config.arms_currency.symbol}{$smarty.request.m} from customer</h2>
<input type=button value="Complete" onClick="window.location='{$smarty.server.PHP_SELF}?t={$smarty.request.t}'">
</div>
{include file=footer.tpl}
