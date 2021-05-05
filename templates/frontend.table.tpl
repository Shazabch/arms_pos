{*
10/05/2011 11:31:09 AM Kee Kee
- Add Mprice Privilege in counter settings

10/13/2011 5:03:43 PM Andy
- Add setting to turn on/off "Print Receipt Reference Code".

11/09/2011 11:00:00 AM Kee Kee
- set "Print Receipt Reference Code" default is no

12/6/2011 3:05:32 PM Justin
- Fixed the allow_print_receipt_reference_code bugs.

01/16/2012 5:34:00 PM Kee Kee
- set "Deposit Settings" default is not allow

03/13/2012 9:10:00 AM Kee Kee
- add "Return Policy" settings
- set "Return Policy" default is not allow

08/07/2012 3:47 PM Kee Kee
- add "Allow Adjust Member Point" in membership settings

11/12/2012 1:57 PM Kee Kee
- Added "Hold Bill Slot" in pos settings

4/15/2014 11:26 AM Kee Kee
- Enhanced to add Sync to weight scale column.

01/21/2016 10:13 AM Edwin
- Change popup save/edit, reload table by using ajax
- Network name not allow to edit except user_id = 1
- Add temporary counter with date from/to.

9/5/2016 17:39 Qiu Ying
- Hide "Return Policy" column

01/18/2017 9:45 PM Kee Kee
- Add "block_goods_return" into table list

10/30/2017 5:53 PM Justin
- Enhanced to allow user to unset counter_status (need privilege).

4/22/2019 1:24 PM Justin
- Enhanced to show "Self Checkout Counter" column.
*}

{config_load file=site.conf}

<span id="span_refreshing"></span>
<table  border=0 cellpadding=4 cellspacing=1>
	<tr>
		<th rowspan=2 bgcolor={#TB_CORNER#} width=40>&nbsp;</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Network Name</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Location</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>POS<br>Settings</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Print<br>Receipt Reference Code</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Deposit Setting</th>
		<!--<th rowspan=2 bgcolor={#TB_COLHEADER#}>Return Policy</th>-->
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Trade In</th>
		{if $config.membership_control_counter_adjust_point}<th rowspan=2 bgcolor={#TB_COLHEADER#}>Adjust Member Point</th>{/if}
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Block Goods Return</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Hold Bill Slot</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>MEMBERSHIP<br>Settings</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Last User</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Open Drawer<br> Count</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>C.O.H</th>
		<th colspan=3 bgcolor={#TB_COLHEADER#}>Card Inventory</th>
		<th {if !$mprice}style="display:none;"{/if} colspan="{$mprice_colspan}" bgcolor={#TB_COLHEADER#}>Mprice Settings</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Sync to weight scale</th>
		<th rowspan=2 bgcolor={#TB_COLHEADER#}>Self Checkout Counter</th>
	</tr>
	<tr>
		<th bgcolor={#TB_COLHEADER#}>R</th>
		<th bgcolor={#TB_COLHEADER#}>G</th>
		<th bgcolor={#TB_COLHEADER#}>B</th>
		{foreach from=$mprice item=val}
			<th bgcolor={#TB_COLHEADER#}>{$val}</th>
		{/foreach}
	</tr>
	{section name=i loop=$counters}
	<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
		<td bgcolor={#TB_ROWHEADER#} nowrap>
			<a href="javascript:void(open({$counters[i].id}))"><img src=ui/ed.png title="Edit" border=0></a>
			<a href="javascript:void(act({$counters[i].id},{if $counters[i].active}0{else}1{/if}))">
				{if $counters[i].active}<img src=ui/deact.png title="Deactivate" border=0>{else}<img src=ui/act.png title="Activate" border=0>{/if}
			</a>
			{if $sessioninfo.privilege.COUNTER_ALLOW_UNSET_STATUS && $counters[i].cst_id}
				<a href="javascript:void(unset_counter_status({$counters[i].id}))">
					<img src="ui/icons/computer_delete.png" title="Delete Counter Status" border="0">
				</a>
			{/if}
		</td>
		<td><b>{$counters[i].network_name}</b>
			{if !$counters[i].active} <br><span class=small style="color: red">(inactive)</span>
			{else}
				{if $counters[i].pos_settings.temporary_counter.allow eq 1}<br><span class=small style="color: blue">(til {$counters[i].pos_settings.temporary_counter.date_to})</span>{/if}
			{/if}
		</td>
		<td><b>{$counters[i].location}</b></td>
		<td align=center>{if $counters[i].pos_settings.allow_pos}Allowed{/if}</td>
		<td align=center>
			{if $counters[i].pos_settings.allow_print_receipt_reference_code}Allowed{/if}
		</td>
		<td align=center>
			{if $counters[i].pos_settings.allow_do_deposit_payment}Allowed{/if}
		</td>
		<!--<td align=center>
			{if $counters[i].pos_settings.allow_do_return_policy}Allowed{/if}
		</td>-->
		<td align=center>
			{if $counters[i].pos_settings.allow_do_trade_in}Allowed{/if}
		</td>
		{if $config.membership_control_counter_adjust_point}
		<td align=center>
			{if $counters[i].pos_settings.counter_allow_adjust_member_point}Allowed{/if}
		</td>
		{/if}
		<td align=center>
			{if $counters[i].pos_settings.block_goods_return}Yes{else}No{/if}
		</td>
		<td align=center>
			{if !$counters[i].pos_settings.hold_bill_slot}0{else}{$counters[i].pos_settings.hold_bill_slot}{/if}
		</td>
		<td align=center>{if $counters[i].membership_settings.allow_membership}Allowed{/if}</td>
		<td align=center>{$counters[i].current_user}</td>
		<td align=center>{$counters[i].drawer_open_count}</td>
		<td align=right>{if $counters[i].inventory.COH}{$counters[i].inventory.COH|number_format:2}{/if}</td>
		<td align=center>{$counters[i].inventory.CARD_R}</td>
		<td align=center>{$counters[i].inventory.CARD_G}</td>
		<td align=center>{$counters[i].inventory.CARD_B}</td>
		{if $mprice}
			{if $counters[i].mprice_settings eq ""}
				{foreach from=$mprice item=val}
					<td align="center">Allowed</td>
				{/foreach}
			{else}
				{foreach from=$mprice item=val}
					<td align="center">{if $counters[i].mprice_settings.$val && !$counters[i].mprice_settings.not_allow}Allowed{/if}</td>
				{/foreach}
			{/if}
		{/if}
		<td>{if $counters[i].pos_settings.sync_weight}{$counters[i].pos_settings.sync_weight}{else}No{/if}</td>
		<td align="center">{if $counters[i].pos_settings.is_self_checkout}Yes{else}No{/if}</td>
	</tr>
	{/section}
</table>