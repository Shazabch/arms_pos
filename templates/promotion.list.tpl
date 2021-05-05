{*
8/20/2010 6:08:16 PM Andy
- add print promotion at main promotion page

11/4/2010 6:32:01 PM Justin
- Added report print for Ministry of Trade on approved tab.

12/16/2010 3:06:25 PM Andy
- Add new promotion type (mix_and_match), default promotion type will be 'discount'.

3/6/2018 5:44 PM HockLee
- Add new icon to trigger function export_to_csv() to export Promotion to CSV file.

2/19/2019 5:55 PM Andy
- Enhanced Print Promotion to use shared template.
- Add can Print by branch.

6/28/2019 1:26 PM Andy
- Enhanced to can show Discount Promotion in Membership Mobile App.

8/12/2020 1:35 PM William
- Enhanced to add new module "Promotion Pop Card".
*}

{$pagination}
<table class=sortable id=promo_tbl width=100% cellpadding=4 cellspacing=1 border=0 style="padding:2px">
<tr bgcolor=#ffee99>
	<th>&nbsp;</th>
	<th>Promo#</th>
	<th>Description</th>
	<th>Type</th>
	<th>From</th>
	<th>To</th>
	<th>Branch</th>
	<th>Last Update</th>
	<th>Created By</th>
</tr>

{section name=i loop=$promo_list}
    {assign var=pt value=$promo_list[i].promo_type}
    {assign var=phpfile value="promotion.php"}
    {if $pt eq 'mix_and_match'}{assign var=phpfile value="promotion.mix_n_match.php"}{/if}
<tr bgcolor={cycle values=",#eeeeee"}>
	<td nowrap>
		{if !$promo_list[i].status}
		    {if $promo_list[i].branch_id!=$sessioninfo.branch_id}
				<a href="{$phpfile}?a=view&id={$promo_list[i].id}&branch_id={$promo_list[i].branch_id}"><img src="ui/approved.png" title="Open this promotion" border=0></a>
			{else}
				<a href="{$phpfile}?a=open&id={$promo_list[i].id}&branch_id={$promo_list[i].branch_id}"><img src="ui/ed.png" title="Open this promotion" border=0></a>
			{/if}
            <a href="javascript:void(PROMO_PRINT.show('{$promo_list[i].branch_id}', '{$promo_list[i].id}', '{$promo_list[i].promo_type}', '', '{$promo_list[i].str_promo_branch_id_list}', '{$promo_list[i].active}', '{$promo_list[i].status}', '{$promo_list[i].approved}'))"><img src="ui/print.png" border="0" title="Print Promotion" /></a>
        {elseif $promo_list[i].status==2}
			<a href="{$phpfile}?a=open&id={$promo_list[i].id}&branch_id={$promo_list[i].branch_id}" target="_blank"><img src="ui/rejected.png" title="Open this promotion" border=0></a>
		{elseif $promo_list[i].status==4 or $promo_list[i].status==5}
			<a href="{$phpfile}?a=view&id={$promo_list[i].id}&branch_id={$promo_list[i].branch_id}" target="_blank"><img src="ui/cancel.png" title="Open this promotion" border=0></a>
		{else}
			<a href="{$phpfile}?a=view&id={$promo_list[i].id}&branch_id={$promo_list[i].branch_id}" target="_blank"><img src="ui/approved.png" title="Open this promotion" border=0></a>
			<a href="javascript:void(PROMO_PRINT.show('{$promo_list[i].branch_id}', '{$promo_list[i].id}', '{$promo_list[i].promo_type}', '', '{$promo_list[i].str_promo_branch_id_list}', '{$promo_list[i].active}', '{$promo_list[i].status}', '{$promo_list[i].approved}'))"><img src="ui/print.png" border="0" title="Print Promotion" /></a>
			{if $pt eq 'discount'}
				<a href="javascript:void(PROMO_PRINT.show('{$promo_list[i].branch_id}', '{$promo_list[i].id}', '{$promo_list[i].promo_type}', 'mot', '{$promo_list[i].str_promo_branch_id_list}', '{$promo_list[i].active}', '{$promo_list[i].status}', '{$promo_list[i].approved}'))"><img src="ui/my.gif" border="0" title="Print Ministry of Trade" /></a>
				<a href="javascript:void(export_to_csv('{$promo_list[i].branch_id}','{$promo_list[i].id}'))"><img src="ui/icons/page_excel.png" border="0" title="Export to CSV" /></a>
				
				{if $config.membership_mobile_settings and $promo_list[i].branch_id eq $sessioninfo.branch_id and $promo_list[i].status eq 1 and $promo_list[i].approved eq 1 and $sessioninfo.privilege.PROMOTION_MEMBER_MOBILE_CONFIGURE}
					<a href="promotion.php?a=edit_member_mobile&branch_id={$promo_list[i].branch_id}&id={$promo_list[i].id}" target="_blank">
						<img src="ui/icons/ipod_cast.png" title="Configure Membership Mobile Settings" />
					</a>
				{/if}
				{if $promo_list[i].status eq 1 and $promo_list[i].approved eq 1 and $sessioninfo.privilege.PROMOTION_POP_CARD}
				<a href="promotion.php?a=promotion_pop_card_setting&branch_id={$promo_list[i].branch_id}&id={$promo_list[i].id}" target="_blank">
					<img src="ui/icons/photo.png" title="Print Pop Card" />
				</a>
				{/if}
			{/if}
		{/if}
		
	</td>
	<td align=center>{$promo_list[i].id}</td>
	<td>{$promo_list[i].title}
		{if preg_match('/\d/',$promo_list[i].approvals) and $promo_list[i].status==1}
			<div class=small>Approvals: <font color=#0000ff>{get_user_list list=$promo_list[i].approvals aorder_id=$promo_list[i].approval_order_id}</font></div>
		{/if}
	</td>
	<td>
		{$promo_type_info.$pt}
	</td>
	<td>{$promo_list[i].date_from} {$promo_list[i].time_from}</td>
	<td>{$promo_list[i].date_to} {$promo_list[i].time_to}</td>
	<td>{$promo_list[i].promo_branch_id}</td>
	<td>{$promo_list[i].last_update}</td>
	<td>{$promo_list[i].u}</td>
</tr>
{sectionelse}
<tr>
	<td colspan=6>- no record -</td>
</tr>
{/section}
</table>
<script>
ts_makeSortable($('promo_tbl'));
</script>
