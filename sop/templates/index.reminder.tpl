<style>
{literal}
.usr_reminder_urgent{
	background: red;
	color: white;
}
.usr_reminder_warning{
	background: yellow;
}
{/literal}
</style>

<h5><img src="/ui/icons/clock.png" align="absmiddle" border="0" /> Reminder</h5>

<div style="border:1px solid grey;padding:3px;" class="ui-corner-all small">
	{if !$user_reminder}
	    <img src="/ui/icons/clock_error.png" align="absmiddle" /> You have no set any reminder.
	{else}
	    <ul>
		    {foreach from=$user_reminder item=r}
				<li>{$r.title} <span class="{$r.urgent_type}">(due {$r.date_to})</span></li>
		    {/foreach}
	    </ul>
	{/if}
	<div class="r">
		<a href="reminder.php">
			<img src="/ui/icons/clock_edit.png" align="absmiddle" border="0" /> Edit Reminder
		</a>
	</div>
</div>
