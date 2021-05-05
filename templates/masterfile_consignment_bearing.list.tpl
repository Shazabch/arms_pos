{*
3/15/2011 5:34:50 PM Alex
- add activation images and checking

12/16/2011 3:30:54 PM Justin
- Added sort by header feature.
*}


<div id="consignment_list">

<div style="border:1px solid #aaa;padding:3px">
<span style="float:right;">{$pagination}</span>
<table class="sortable" id="trnsprt_tbl" border=0 cellspacing=1 cellpadding=4 width=100%>
	<tr style="background-color:#ffee99 !important;">
		<th>&nbsp;</th>
		<th>Branch</th>
		<th>Department</th>
		<th>Vendor</th>
		<th>Brand</th>
		<th>Last Update</th>
		<th>Added</th>
		<th>Created By</th>
	</tr>
{if $consignment}
{foreach from=$consignment item=cs}
	<tr>
	    <td width="5%" align="center" nowrap>
			<a href="?a=view_items&group_id={$cs.group_id}" >
				<img src=ui/view.png align=absmiddle border=0 title="View"></a>&nbsp;&nbsp;
			{if $sessioninfo.privilege.MST_CONTABLE_EDIT}
			<a href="?a=edit_items&group_id={$cs.group_id}" >
				<img src=ui/ed.png align=absmiddle border=0 title="Edit"></a>&nbsp;&nbsp;
				<span id="activate_id_{$cs.group_id}">
					{if $cs.active}
					<img src=/ui/deact.png onclick="if (confirm('Are you sure to deactivate this?')) act_consignment({$cs.group_id},'0');" title="Deactivate" align=absmiddle border=0>
					{else}
					<img src=/ui/act.png onclick="if (confirm('Are you sure to activate this?')) act_consignment({$cs.group_id},'1');" title="Activate" align=absmiddle border=0>
     				{/if}
				</span>

			{/if}
		</td>
		<td>{$cs.branch_code}</td>
        <td width="10%">{$cs.cat_des}</td>
		<td width="30%">{$cs.vendor_des|default:"-"}</td>
		<td width="30%">{$cs.brand_des|default:"-"}</td>
		<td width="10%">{$cs.last_update}</td>
		<td width="10%">{$cs.addded_date}</td>
		<td width="5">{$cs.username|upper}</td>
	</tr>
{/foreach}
{else}
<tr>
	<td colspan=8 align="center">-- No Data --</td>
</tr>
{/if}
</table>
</div>

</div>

<script>
	ts_makeSortable($('trnsprt_tbl'));
</script>
