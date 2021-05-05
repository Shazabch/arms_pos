{*
4/22/2010 6:13:28 PM Andy
- Add print envelope

12/16/2011 3:30:54 PM Justin
- Added sort by header feature.

3/26/2012 3:03:06 PM Andy
- Reconstruct module structure to use ajax update instead of IRS.

5/21/2013 11:55 AM Justin
- Enhanced to allow only Admin to have Copy Settings.

5/30/2013 5:41 PM Justin
- Bug fixed on user cannot activate/deactive branch from consignment customer.

6/16/2015 11:33 AM Eric
- Add GST Registration no. column 

3/30/2018 4:13 PM HockLee
- Added new input Integration Code.

11/22/2019 5:57 PM William
- Add new branch outlet photo to branch.
*}

{config_load file=site.conf}

<table class="sortable" id="branch_tbl" border=0 cellpadding=4 cellspacing=1 width=100%>
<tr>
	<th bgcolor="{#TB_CORNER#}" width="40">&nbsp;</th>
	<th bgcolor="{#TB_COLHEADER#}">Code</th>
	<th bgcolor="{#TB_COLHEADER#}">Company Name</th>
	<th bgcolor="{#TB_COLHEADER#}">Company Registration No.</th>
	<th bgcolor="{#TB_COLHEADER#}">Contact Person</th>
	<th bgcolor="{#TB_COLHEADER#}">GST Registration Number</th>
	{if $config.enable_reorder_integration}
		<th bgcolor="{#TB_COLHEADER#}">Integration Code</th>
	{/if}
</tr>
{section name=i loop=$branches}
	<tr onmouseover="this.bgColor='{#TB_ROWHIGHLIGHT#}';" onmouseout="this.bgColor='';">
	<td bgcolor="{#TB_ROWHEADER#}" nowrap>
		<a href="javascript:void(ed({$branches[i].id}))">
			<img src="ui/ed.png" title="Edit" border=0>
		</a>
		{if $allow_add_branch}
		<a href="javascript:void(act({$branches[i].id},{if $branches[i].active}0))"><img src=ui/deact.png title="Deactivate" border=0>{else}1))"><img src=ui/act.png title="Activate" border=0>{/if}</a>
		{/if}
		{if $config.payment_voucher_vendor_maintenance}
			<img src="ui/report_edit.png" title="Payment Voucher Maintenance" border="0" onclick="javascript:void(show_vvc({$branches[i].id}, '{$branches[i].description}'))">
		{/if}
	
		<a href="javascript:void(show_trade_discount('{$branches[i].id}'))">
			<img src="ui/table.png" title="open Trade Discount Table" border="0" />
		</a>
		{if $config.masterfile_branch_allow_print_envelope}
			<a href="javascript:void(print_envelope('{$branches[i].id}'));">
				<img src="/ui/icons/page_white_text_width.png" title="Print Envelope" border="0" />
			</a>
		{/if}
		
		{if $allow_add_branch}
			<a href="javascript:void(copy_settings_dialog('{$branches[i].id}'));">
				<img src="ui/icons/cog_go.png" title="Copy Branch Settings for {$branches[i].code}" border="0" />
			</a>
		
		{/if}
		<a href="javascript:void(OUTLET_PHOTO_DIALOG.open('{$branches[i].id}'))">
			<img src="ui/icons/image.png" title="Branch Outlet Photo" border="0" />
		</a>
	</td>
	<td><b>{$branches[i].code}</b>{if !$branches[i].active}<br><span class=small>(inactive)</span>{/if}</td>
	<td>{$branches[i].description}</td>
	<td>{$branches[i].company_no}</td>
	<td>{$branches[i].contact_person}<br>
	<a href="mailto:{$branches[i].contact_email}">{$branches[i].contact_email}</a></td>
	<td>{$branches[i].gst_register_no}<br>
	{if $config.enable_reorder_integration}
	<td>{$branches[i].integration_code}</td>
	{/if}
	</tr>
{/section}
</table>

<script>
	//parent.window.document.getElementById('udiv').innerHTML = document.getElementById('udiv').innerHTML;
	ts_makeSortable($('branch_tbl'));
</script>
