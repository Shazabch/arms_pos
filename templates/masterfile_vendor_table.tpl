{*
REVISION HISTORY
================

1/8/2008 3:00:13 PM gary
- add open vendor's grn summary.

6/17/2010 3:09:38 PM alex
- add vendor block branch

12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

4/30/2012 4:59:15 PM Andy
- Add show loading process icon when reload vendor list.

7/5/2012 1:33 PM Andy
- Add can generate vendor portal key for vendor at vendor master file.

10/3/2012 4:37 PM Andy
- Change edit vendor portal info to open in new window page.

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

6/16/2015 11:33 AM Eric
- Add GST Registration no. column 

11/28/2018 9:18 AM Justin
- Enhanced to have Quotation Cost direct access from Vendor module when found user has privilege.
*}

{config_load file=site.conf}

{if $smarty.request.a ne 'ajax_reload_table'}
<div id="udiv" class="stdframe">
{/if}
<span id="span_loading_vendor_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...<br /><br /></span>

{if $pagination}
<div class="form-inline">
	Page:&nbsp;&nbsp;
<select class="form-control" name="pg" id="pg" onchange="reload_table(true)">
	{$pagination}
</select>&nbsp;of <b>&nbsp;{$total_page}</b>
&nbsp;&nbsp;
{/if}
<span style="color:#CE0000;"><b>(Total of {$vcount} records)</b></span>
</div>
<br /><br />

<div class="table-responsive">
	<table  class="sortable table mb-0 text-md-nowrap  table-hover" id="vendor_tbl"  width=100%>
		<thead style="height: 25px;">
			<tr>
				{if $sessioninfo.privilege.MST_VENDOR}
				<th bgcolor={#TB_CORNER#} width=50>&nbsp;</th>
				{/if}
				<th bgcolor={#TB_COLHEADER#} width=130>&nbsp;</th>
				<th bgcolor={#TB_COLHEADER#}>Code</th>
				<th bgcolor={#TB_COLHEADER#}>Description</th>
				<th bgcolor={#TB_COLHEADER#} nowrap>Company No</th>
				<th bgcolor={#TB_COLHEADER#} nowrap>GST Registration Number</th>
				<th bgcolor={#TB_COLHEADER#} nowrap>Address</th>
				<th bgcolor={#TB_COLHEADER#} nowrap>Phone #1</th>
				<th bgcolor={#TB_COLHEADER#} nowrap>Phone #2</th>
				<th bgcolor={#TB_COLHEADER#} nowrap>Fax No.</th>
				<th bgcolor={#TB_COLHEADER#}>Contact</th>
				</tr>
		</thead>
		{section name=i loop=$vendors}
		<tbody class="fs-08">
			<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
				{if $sessioninfo.privilege.MST_VENDOR}
					<td bgcolor={#TB_ROWHEADER#} nowrap>
					<a href="javascript:void(ed({$vendors[i].id}))"><img src=ui/ed.png title="Edit" 	></a>
					
					<a href="javascript:void(act({$vendors[i].id},{if $vendors[i].active}0))"><img src=ui/deact.png title="Deactivate" border=0>{else}1))"><img src=ui/act.png title="Activate" border=0>{/if}</a>
					</td>
				{/if}
				
				<td align=center nowrap>
					<a href="javascript:void(showtd({$vendors[i].id}, '{$vendors[i].description|escape:'javascript'}'))">
					<img src=ui/table.png title="open Trade Discount Table" border=0>
					</a>
					
					{if $config.payment_voucher_vendor_maintenance}
					<img src=ui/report_edit.png title="Payment Voucher Maintenance" border=0 onclick="javascript:void(show_vvc({$vendors[i].id}, '{$vendors[i].description|escape:'javascript'}'))">
					{/if}
					
					<!--By clicking the vendor on the master vendor list module, able to view all the GRN received - Link to grn summary (new)-->
					<a href="javascript:void(open_grn({$vendors[i].id}))">
					<img src=ui/lorry.png title="open Vendor GRN" border=0>
					</a>
					
					<img src="ui/table_delete.png" title="open Branch Block List" border =0 onclick="javascript:void(show_vbb({$vendors[i].id},'{$vendors[i].description|escape:'javascript'}'))">
					
					{if $config.enable_vendor_portal}
						{* <a href="javascript:void(VENDOR_PORTAL_POPUP.open('{$vendors[i].id}'));"> *}
						<a href="masterfile_vendor.vendor_portal.php?vid={$vendors[i].id}" target="_blank">
							<img src="ui/icons/key.png" border="0" title="Manage Vendor Portal Access Key" />
						</a>
					{/if}
					
					{if $sessioninfo.privilege.MST_VENDOR_QUOTATION_COST}
						<a href="masterfile_vendor.quotation_cost.php?vendor_id={$vendors[i].id}" target="_blank">
						<img src="ui/icons/database_edit.png" title="Edit Quotation Cost" border="0" />
						</a>
					{/if}
				</td>
				
				<td>
				<b>{$vendors[i].code}</b>{if !$vendors[i].active}<br><span class=small>(inactive)</span>{/if}
				</td>
				<td>{$vendors[i].description}</td>
				<td>{$vendors[i].company_no}</td>
				<td>{$vendors[i].gst_register_no}</td>
				<td>{$vendors[i].address|nl2br}</td>
				<td>{$vendors[i].phone_1}</td>
				<td>{$vendors[i].phone_2}</td>
				<td>{$vendors[i].phone_3}</td>
				<td>{$vendors[i].contact_person}<br>
				<a href="mailto:{$vendors[i].contact_email}">{$vendors[i].contact_email}</a></td>
				</tr>
		</tbody>
		{/section}
		</table>
</div>
{if $smarty.request.a ne 'ajax_reload_table'}
</div>

<script>
	parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('vendor_tbl'));
</script>
{/if}
