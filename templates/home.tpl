{*
12/2/2010 3:43:58 PM Andy
- Fix notification when click "Mark as read and close", it could close wrong pm.

11/18/2015 4:25 PM Qiu Ying
- Enhance PM layout and allow user to delete
*}

{include file=header.tpl}
{literal}
<style>
#notifications {
	float:right;
	width:200px;
	color: #333;
}

#notifications a {
	color: #c00;
}

#notifications ol {
	padding:5px; margin:0;
	list-style-type: circle;
}
#notifications ol li {
	margin-left: 15px;
}

</style>
<script>
var pm_count = 0;
function pm_read(bid, id)
{
	Effect.BlindUp('pm-'+bid+'-'+id, {duration:0.2});
	pm_count--;
	//if (pm_count == 0) Effect.BlindUp('pm');
}
</script>
{/literal}

{if $sessioninfo}
	{include file=notifications.tpl}
{elseif $vp_session}
	{include file="vp.home.tpl"}
{elseif $sa_session}
	{include file="sa.home.tpl"}
{/if}

{include file=footer.tpl}
