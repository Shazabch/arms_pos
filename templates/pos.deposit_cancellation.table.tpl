{*
8/20/2013 10:15 AM Andy
- Add column "Used Amount".
- Add a new tab "Used",

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".
*}

<br>
{if $finalized}
	<p align="left"><font color="red"><b>* Following date has being finalised, please unfinalise it before cancel deposit </b></font></p>
{else}
	<div id="err" {if !$exception_list}style="display:none;"{/if}><div class="errmsg"><ul>
		<li><div class="sold" id="dup_sn_msg">Following S/N is not inserted due to duplication:<div id="dup_sn"></div></div>
		{foreach from=$exception_list key=felist item=elist name=el}
			{$elist}{if !$smarty.foreach.el.last},{/if}
		{/foreach}
		</li>
		<li><div class="sold" {if $exception_list}style="display:none;"{/if} id="sold_sn_msg">Following S/N is not inserted due to have been sold:<div id="sn_sold"></div></div></li>
	</ul></div></div>

	<div class="tab" style="height:25px;white-space:nowrap;">
		&nbsp;&nbsp;&nbsp;
		<input type="hidden" name="curr_tab" id="curr_tab" value="{$tab|default:1}">
		<a href="javascript:void(DEPOSIT_CANCELLATION_MODULE.search_deposit('', 1));" id="lst1" class="a_tab {if $tab eq '1' || !$tab}active{/if}">Available</a>
		<a href="javascript:void(DEPOSIT_CANCELLATION_MODULE.search_deposit('', 4));" id="lst4" class="a_tab {if $tab eq '4'}active{/if}">Used</a>
		<a href="javascript:void(DEPOSIT_CANCELLATION_MODULE.search_deposit('', 2));" id="lst2" class="a_tab {if $tab eq '2'}active{/if}">Cancelled</a>
		<a class="a_tab {if $tab eq '3'}active{/if}" id="lst3">Find Receipt No <input id="inp_deposit_search" onKeyPress="DEPOSIT_CANCELLATION_MODULE.search_input_keypress(event);" value="{$str_search}" /> <input type="button" value="Go" onclick="DEPOSIT_CANCELLATION_MODULE.search_deposit('', 3);" /></a>
	</div>

	<div class="tabcontent" style="height:500;overflow-y:auto;overflow-x:hidden;">
	<table class="deposit_tbl sortable" id="deposit_tbl" width="100%" cellspacing="1" cellpadding="4" border="0" style="border: 1px solid rgb(0, 0, 0);">
		<tr style="background-color:#ffee99 !important;">
			<th width="5%"></th>
			<th>Deposit Receipt</th>
			<th>Branch</th>
			<th>Cashier</th>
			<th>Approved By</th>
			<th>Deposit Amount</th>
			<th>Status</th>
			<th>Used Receipt</th>
			<th>Used at Branch</th>
			<th>Used Amount</th>
		</tr>
		<tbody id="deposit_items">
			{foreach from=$items key=fitem item=item name=i}
				{include file="pos.deposit_cancellation.table.list.tpl"}
			{foreachelse}
				<tr id="empty_row">
					<td colspan="7" align="center">-- No record --</td>
				</tr>
			{/foreach}
		</tbody>
	</table>
	</div>
	<br>
	<div align="center">
	{*if $smarty.foreach.si.last && $tab eq 1}
		<span id="upd_btn" {if !$items}style="display:none;"{/if}>
			<input type="button" value="Cancel" onclick="save_sn_items();">
		</span>
	{/if*}
		<input type="hidden" name="tab" id="tab" value="{$tab|default:1}">
	</div>

	<script>
		ts_makeSortable($('deposit_tbl'));
	</script>
{/if}