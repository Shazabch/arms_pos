{*
REVISION HISTORY
================
12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

11/1/2016 11:41 AM Andy
- Enhanced to have debtor gst start date and gst registration number.

2017-08-25 15:08 PM Qiu Ying
- Enhanced to add Debtor blocklist for Credit Sales DO at debtor masterfile

12/1/2017 11:33 AM Justin
- Changed the "Address" become "Address (Bill)".
- Enhanced to have "Address (Deliver)".

3/30/2018 4:13 PM HockLee
- Added new input Integration Code.

8/24/2018 12:16 PM Andy
- Added new module "Debtor Price List".
*}

{config_load file=site.conf}

<span id="span_refreshing"></span>

<div class="form-inline">
	{if $pagination}
<br />
<b class="form-inline">
	Page:&nbsp;
</b>
<select class="form-control" name="pg" id="pg" onchange="reload_table(true)">
	{$pagination}
</select>&nbsp;of &nbsp;<b>{$total_page}</b>&nbsp;
{/if}

<span style="color:#CE0000;"><b>(Total of {$dcount} records)</b></span>
</div>

<div class="table-responsive">
	<table class="sortable table mb-0 text-md-nowrap  table-hover mt-3" id="debtor_tbl" width="100%">
		<thead style="height:30px;">
			<tr>
				<th style="min-width:80px;">&nbsp;</th>
				<th>Code</th>
				<th>Description</th>
				<th nowrap>Company No</th>
				<th nowrap>Address (Bill)</th>
				<th nowrap>Address (Deliver)</th>
				<th nowrap>Area</th>
				<th nowrap>Phone #1</th>
				<th nowrap>Phone #2</th>
				<th nowrap>Fax No.</th>
				<th>Contact</th>
				{if $config.enable_gst}
					<th bgcolor="{#TB_COLHEADER#}">GST No.</th>
				{/if}
				{if $config.enable_reorder_integration}
					<th bgcolor="{#TB_COLHEADER#}">Integration Code</th>
				{/if}
				</tr>
		</thead>
		{foreach from=$table item=r}
		<tbody class="fs-08">
			<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
				<td bgcolor={#TB_ROWHEADER#} nowrap>
					<a href="javascript:void(open('{$r.id}'))"><img src=ui/ed.png title="Edit" border=0></a>
					<a href="javascript:void(act('{$r.id}','{if $r.active}0{else}1{/if}'))">
					{if $r.active}
						<img src="ui/deact.png" title="Deactivate" border="0">
					{else}
						<img src="ui/act.png" title="Activate" border="0">
					{/if}
					</a>
					<img src="ui/table_delete.png" title="Open Branch Block List" border="0" onclick="javascript:void(show_blocklist('{$r.id}','{$r.description|escape:'javascript'}'))" />
					
					{if $sessioninfo.privilege.MST_DEBTOR_PRICE_LIST}
						<a href="masterfile_debtor_price.php?debtor_id={$r.id}" target="_blank">
						<img src="ui/icons/database_edit.png" title="Edit Debtor Price List" border="0" />
						</a>
					{/if}
				</td>
				<td>
				<b>{$r.code}</b>{if !$r.active}<br><span class=small>(inactive)</span>{/if}
				</td>
				<td>{$r.description}</td>
				<td>{$r.company_no}</td>
				<td>{$r.address|nl2br}</td>
				<td>{$r.delivery_address|nl2br}</td>
				<td>{$r.area}</td>
				<td>{$r.phone_1}</td>
				<td>{$r.phone_2}</td>
				<td>{$r.phone_3}</td>
				<td>{$r.contact_person}<br>
				<a href="mailto:{$r.contact_email}">{$r.contact_email}</a></td>
				{if $config.enable_gst}
					<td>{$r.gst_register_no}<br>
				{/if}
				{if $config.enable_reorder_integration}
					<td>{$r.integration_code}<br>
				{/if}
				</tr>
		</tbody>
		{/foreach}
		</table>
</div>

<script>
	ts_makeSortable($('debtor_tbl'));
</script>