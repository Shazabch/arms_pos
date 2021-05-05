{*
2017-08-24 14:32 PM Qiu Ying
- Enhanced to add pagination in dashboard pm
*}

{if $pm}
	{assign var=lastpm value=""}
	{assign var=pm_count value=0}
	<h5><font color=#999999><img src=/ui/notify_pm.png align=absmiddle border=0> For Your Information</font></h5>
	<div class=ntc>Click message to see detail.</div><br />
	<div style="width:100%;height:10px">
		<div style="float:left;width:80%;">
			<form>
				{if $pagination}
					<b>Go To Page</b>
					<select id="s" name="s" onchange="ajax_get_pm(this.value)">
						{foreach from=$pagination key=k item=paging}
							<option value="{$k}" {if $selected_page eq $k}selected{/if}>{$paging}</option>
						{/foreach}
					</select>
				{/if}
				<span class=ntc>Total {$total_pm} messages</span>
			</form>
		</div>
		<div style="float:right;text-align:right;width:20%;">
			<a href="javascript:void(clear_all_pm());">Clear All</a>
		</div>
	</div>
	<br>
	<div style="width:100%;height:400px;border:2px inset black;overflow-x:hidden;overflow-y:auto;position:relative;" class="ui-corner-all" id="div_pm_notification">
	<table width="100%" cellpadding="4" cellspacing="1" border="0">
	<tbody class="tbody_container" id="tbody_pm_list">
	{section name=i loop=$pm}
	<!-- {$pm_count++} -->
	<tr id="pm-{$pm[i].branch_id}-{$pm[i].id}" style="{if !$pm[i].opened}background-color:FFFFE0 ;{/if}">
		<td width="20">
			<img src="/ui/closewin.png" align="absmiddle" class="clickable img_delete_pm_row" title="Mark as read and close" onclick="pm_delete({$pm[i].branch_id},{$pm[i].id});" />
		</td>
		<td>
			<a href="/pm.php?a=view_pm&branch_id={$pm[i].branch_id}&id={$pm[i].id}" target=_blank  style="{if !$pm[i].opened}font-weight: bold;{/if}" onclick="change_style(this,{$pm[i].branch_id},{$pm[i].id});">{$pm[i].msg}</a>
			<br><font color=#666666 class=small >{$pm[i].u} ({$pm[i].branch} - {$pm[i].position}) @ {$pm[i].timestamp}</font>
		<td>
	</tr>
	{/section}
	</tbody>
	</table>
	</div>
	<script>
	pm_count = {$pm_count};
	</script>
{/if}