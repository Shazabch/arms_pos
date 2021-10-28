{*
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

4/18/2014 2:54 PM Justin
- Enhanced to allow user search by Serial No.

8/7/2014 9:19 AM Justin
- Enhanced to have remark column.

6/18/2015 11:30 AM Justin
- Enhanced to check and show error message if user trying to add existing S/N from other branch.
- Enhanced to have GRN No column.

2017-09-12 11:58 AM Qiu Ying
- Bug fixed on setting wrong colspan value
*}

<br>
<div id="err" {if !$exception_list}style="display:none;"{/if}><div class="errmsg"><ul>
	<li><div class="sold" id="dup_sn_msg">Following S/N is not inserted due to duplication:<div id="dup_sn"></div></div>
	{foreach from=$exception_list key=felist item=elist name=el}
		{$elist}{if !$smarty.foreach.el.last},{/if}
	{/foreach}
	</li>
	<li><div class="sold" {if $exception_list}style="display:none;"{/if} id="sold_sn_msg">Following S/N is not inserted due to have been sold:<div id="sn_sold"></div></div></li>
	<li><div class="sold" {if $exception_list}style="display:none;"{/if} id="diff_branch_sn_msg">Following S/N is existed and located on other branches:<div id="sn_diff_branch"></div></div></li>
</ul></div></div>

<div class="row mx-3">
	<div class="tab row mx-3 mb-3" style="white-space:nowrap;">
		<input type="hidden" name="curr_tab" id="curr_tab" value="{$tab|default:1}">
		<a  href="javascript:void(list_sel(1))" id="lst1" class="btn btn-outline-primary btn-rounded a_tab {if $tab eq '1' || !$tab}active{/if}">Active</a>
	&nbsp;&nbsp;	<a href="javascript:void(list_sel(2))" id="lst2" class="btn btn-outline-primary btn-rounded  a_tab {if $tab eq '2'}active{/if}">Inactive</a>
		<a class="a_tab {if $tab eq '3'}active{/if}" id="lst3">
			<div class="form-inline">
			&nbsp;&nbsp;	Find S/N 
		&nbsp;	<input class="form-control" id="inp_sn_search" onKeyPress="search_input_keypress(event);" value="{$str_search}" /> 
			&nbsp;<input type="button" class="btn btn-primary" value="Go" onclick="list_sel(3);" /></a>
			</div>
	</div>
</div>

<div class="tabcontent">
{assign var=colsHeader value=13}
<div class="card mx-3">
	<div class="card-body">
		<div class="table-responsive">
			<table class="serial_no_tbl sortable table mb-0 text-md-nowrap  table-hover" id="sn_tbl" width="100%" >
				<thead class="bg-gray-100">
					<tr >
						<th>#</th>
						<th>Serial No</th>
						{if $BRANCH_CODE eq 'HQ'}
							<th>Apply<br />Branch</th>
							{assign var=colsHeader value=$colsHeader+1}
						{/if}
						<th>Located<br />Branch</th>
						<th>Status</th>
						<th>I/C</th>
						<th>Name</th>
						<th>Address</th>
						<th>Phone No</th>
						<th>Email</th>
						<th>Warranty <br />Expired</th>
						<th>Remark</th>
						<th>GRN No</th>
						<th width="1%">&nbsp;</th>
					</tr>
				</thead>
				<tbody class="fs-08" id="serial_no_items" {if count($items)>$sn_rows} style="width:650;height:500;overflow-y:auto;overflow-x:hidden;"{/if}>
					{foreach from=$items key=fitem item=item name=i}
						{assign var=sid value=$item.sku_item_id}
						{if $si_info_list && $si_info_list.$sid.id ne $curr_sid}
							<tr><td bgcolor="#cdd" colspan="{$colsHeader}"><b>{$si_info_list.$sid.sku_item_code} - {$si_info_list.$sid.description}</b></td></tr>
							{assign var=curr_sid value=$sid}
						{/if}
						{include file="masterfile_sku_items.serial_no.list_row.tpl"}
					{/foreach}
					{if !$items}
						<tr id="empty_row">
							<td colspan="{$colsHeader}" align="center">-- No record --</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
	</div>
</div>
</div>
<br>
<div align="center">
{if $smarty.foreach.si.last}
	<span id="upd_btn" {if !$items}style="display:none;"{/if}>
		<input type="button" value="Update" onclick="save_sn_items();">
	</span>
	{if $tab ne '3' && $sku_items}
		<input type="button" value="Add S/N" onclick="showdiv('add_sn'); document.f_sn.serial_no_list.focus(); curtain(true);">
	{/if}
{/if}
	<input type="hidden" name="tab" id="tab" value="{$tab|default:1}">
</div>

<script>
	ts_makeSortable($('sn_tbl'));
</script>