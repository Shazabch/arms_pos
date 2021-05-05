{*
*}
<p><b>Search: </b><input type="text" size="25" id="search_commission_{$branch_id}" value="{$search_str}"> <button name="sac_search_btn" onclick="SA_COMMISSION_MODULE.search_branch_commission({$branch_id}, 1);">Go</button> <span id="span_sac_branch_loading_{$branch_id}"></span></p>
{if count($sac_list) >= 50}<div align="right">Record shown in maximum 50 items</div>{/if}
<table border="0" cellpadding="4" cellspacing="1" width="100%" style="border:1px solid #000">
	<tr bgcolor="#cccccc">
		<th width="10%">&nbsp;</th>
		<th width="10%">Commission No.</th>
		<th width="80%">Title</th>
	</tr>
	{foreach from=$sac_list item=sac name=sa_c}
		{assign var=sac_id value=$sac.id}
		<tr onmouseover="this.bgColor='#FFFFCC';" onmouseout="this.bgColor='';">
			<td bgcolor="{#cccccc#}" align="center" nowrap>
				{if $sac.commission_used > 0}
					<img src="ui/approved.png" title="Currently using this Commission" border="0">
				{else}
					<img src="ui/icons/calculator_add.png" title="Use this Commission" commission_title="{$sac.title}" border="0" onclick="SA_COMMISSION_MODULE.use_branch_commission({$sac_id}, {$sac.branch_id}, this);">
				{/if}
			</td>
			<td align="center"><a onclick="SA_COMMISSION_MODULE.commission_items_table_appear({$sac_id}, {$sac.branch_id});" title="Click to view Commission Items">#{$sac.id|string_format:"%05d"}</a></td>
			<td nowrap>{$sac.title}</td>
		</tr>
	{foreachelse}
		<td colspan="6" align="center">-- No Record --</td>
	{/foreach}
</table>
