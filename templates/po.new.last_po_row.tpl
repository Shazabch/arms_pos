{*
5/12/2009 4:15:00 PM Andy
- Hide Cost if no $sessioninfo.privilege.SHOW_COST
- Hide GP if no $sessioninfo.privilege.SHOW_REPORT_GP

7/31/2009 4:19:27 PM Andy
- Edit colspan control

6/7/2011 3:50:50 PM Andy
- Add checking for config "po_use_simple_mode" and only show some simple column.

8/25/2011 4:32:34 PM Andy
- Change PO to ignore "SHOW COST" privilege and will show Cost & Cost indicator all the time.

9/9/2011 3:17:45 PM Alex
- add the missing T.selling/T.cost and total qty and foc when single branch

9/20/2011 12:28:11 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.

3/30/2012 5:28:32 PM Justin
- Added table's "tr".

4/4/2012 4:04:43 PM Justin
- Fixed the bugs where system unable to delete po row.

1/28/2013 5:39 PM Justin
- Bug fixed on selling & order UOM can be edited.
- Enhanced to align selling & order UOM to center.

4/4/2013 4:20 PM Fithri
- item last po row, enhance to check less than (po date+1 day) and less than added
- cost indicate in last po row just show "-"

11/8/2014 10:40 AM Justin
- Enhanced to have GST calculation and settings.

4/17/2018 3:41 PM Andy 
- Added Foreign Currency feature.

6/26/2018 12:24 PM Andy
- Fixed NSP and SSP column bug.

12/7/2018 2:25 PM Justin
- Enhanced to show Old Code column base on config.

12/13/2018 2:48 PM Justin
- Enhanced to always show Art No and MCode in 2 columns instead of either one.

06/22/2020 11:50 AM Sheila
- Fixed table boxes alignment and width.
*}

{if $form.branch_id==1 && !$form.po_branch_id && is_array($form.deliver_to)}
	{assign var=view_hq value=1}
{else}
	{assign var=view_hq value=0}
{/if}
{assign var=item_id value=$item.id}
<tr onmouseover="this.bgColor='#ffeecc';" onmouseout="this.bgColor='';" id="last_po_item{$l_item.pi_id|default:$pi_id}">
{assign var=colspan value=5}
{if $config.link_code_name && $config.docs_show_link_code}
	{assign var=colspan value=$colspan+1}
{/if}

<td nowrap colspan="{$colspan}">
&nbsp;
</td>
<!--============================================================================== -->
<!--============================================================================== -->

{if $view_hq}
<!--START MULTIPLE DELIVER BRANCHES-->
<td>
<input value="{$l_item.selling_uom_code|default:'EACH'}" size=8 style="background:#ef9; margin: 1px;text-align: left;font-size: 11px; padding-left: 5px" disabled>
</td>

<td align=center>
<input value="{$l_item.uom_code|default:'EACH'}" size=8 style="background:#ef9; margin: 1px;text-align: left;font-size: 11px; padding-left: 5px" disabled>
</td>

<td align="right">
	{if $l_item.currency_code}{$l_item.currency_code}{/if}
	<input size=6 value="{$l_item.order_price|default:$l_item.resell_price|number_format:$config.global_cost_decimal_points:".":""}" style="background:#ef9;" disabled />
</td>

<td align=center>
{*{$l_item.cost_indicate|default:"-"}*}
-
</td>

{if $view_hq}
{assign var=count value=0}
{section name=i loop=$branch}
	{if in_array($branch[i].id,$form.deliver_to)}
		{assign var=bid value=`$branch[i].id`}
		{if $count==0}
			<td align=center nowrap valign=top  style="width:70px;">
				<table width="100%">
					<tr>
						<td align="center">
							<input disabled value="{$l_item.qty}" style="background:#ef9; width:40px" size=1 class="col_width-1">
						</td>
						<td align="center">
							<input disabled value="{$l_item.qty_loose}" style="background:#ef9; width:40px" size=1 class="col_width-1">		
						</td>
					</tr>
				</table>	
				{*
				<div align=center>
					<table width="100%">
						<tr>
							<td align="center">
								<span style="padding-left: 15px;text-align: right;" class="col-left-title">S.P</span>
							</td>
							<td align="center">
								<input value="{$l_item.selling_price|default:$item.selling_price|number_format:2:".":""}" style="background:#ef9; width:40px" size="3" disabled class="col_width-1" />		
							</td>
						</tr>
					</table>	
				</div>
				*}
				
				<div align="left">
					{if $form.branch_is_under_gst}
						{if $l_item.inclusive_tax eq "yes"}
							S.S.P
							<input value="{$l_item.gst_selling_price|number_format:2:'.':''}" style="width:40px;background:#ef9" size="3" disabled />
							<br />
							<font color="blue">
								<span>
									N.S.P
									<input value="{$l_item.selling_price|number_format:2:".":""}" style="width:40px;background:#ef9" size="3" disabled />
								</span>
							</font>
						{else}

						<table width="100%">
							<tr>
								<td align="center">
									<span style="padding-left: 15px;text-align: right;" class="col-left-title">N.S.P</span>
								</td>
								<td align="center">
								<input class="col_width-1" value="{$l_item.selling_price|number_format:2:".":""}" style="background:#ef9;width:40px" size="3" disabled />		
								</td>
							</tr>
						</table>

						<table width="100%">
							<tr>
								<td align="center">
									<font style="padding-left: 15px;text-align: right;" class="col-left-title" color="blue">S.S.P</font>
								</td>
								<td align="center">
								<input class="col_width-1" type="text" value="{$l_item.gst_selling_price|number_format:2:".":""}" style="background:#ef9; width:40px" size="3" disabled />	
								</td>
							</tr>
						</table>
						{/if}
					{else}

					<table width="100%">
						<tr>
							<td align="center">
								<span style="padding-left: 15px;text-align: right;" class="col-left-title">N.S.P</span>
							</td>
							<td align="center">
							<input value="{$l_item.selling_price|number_format:2:".":""}" style="background:#ef9; width:40px" size="3" disabled class="col_width-1"/>		
							</td>
						</tr>
					</table>	
					{/if}
				</div>
				
			</td>
			
			<!-- FOC -->
			<td align=right nowrap valign=top  style="width:60px;{if $config.po_use_simple_mode}display:none;{/if}">

				<table width="100%">
					<tr>
						<td align="center">
							<input class="col_width-1" style="background:#ef9;width:40px" size=1 value="{$l_item.foc}" disabled>
						</td>
						<td align="center">
							<input class="col_width-1" style="background:#ef9;width:40px" size=1 disabled value="{$l_item.foc_loose}">		
						</td>
					</tr>
				</table>	
			</td>
		{else}
			<td style="{if $config.po_use_simple_mode}display:none;{/if}">&nbsp;</td>
			<td>&nbsp;</td>
		{/if}
	{assign var=count value=$count+1}
	{/if}
{/section}	
{/if}
<!--END MULTIPLE DELIVER BRANCHES-->

<!--=================================================================================-->

<!--START SINGLE DELIVER BRANCHES-->
{else}

{*
<td align=right>
	<input value="{$l_item.selling_price|number_format:2:'.':''}" style="background:#ef9" size=6 disabled>
</td>

{if $form.is_under_gst}
	<td>&nbsp;</td>
{/if}
*}

{if $form.branch_is_under_gst}
	{if $l_item.inclusive_tax eq "yes"}
		<td align="center">
			<input size="6" value="{$l_item.gst_selling_price|number_format:2:'.':''}" disabled style="background:#ef9" />
		</td>
		<td align="center">
			<input size="6" value="{$l_item.selling_price|number_format:2:'.':''}" disabled style="background:#ef9" />
		</td>
	{else}
		<td align="center">
			<input size="6" value="{$l_item.selling_price|number_format:2:'.':''}" disabled style="background:#ef9" />
		</td>
		
		<td align="center">
			<input type="text" size="6" value="{$l_item.gst_selling_price|number_format:2:'.':''}" disabled style="background:#ef9" />
		</td>
	{/if}
{else}
	<td align="center">
		<input size="6" value="{$l_item.selling_price|number_format:2:'.':''}" disabled style="background:#ef9" />
	</td>
{/if}

<td align=center>
<input value="{$l_item.selling_uom_code|default:'EACH'}" size=8 style="background:#ef9; text-align:left;margin: 1px;text-align: right;font-size: 11px; padding-left: 5px" disabled>
</td>

<td align=center>
<input value="{$l_item.uom_code|default:'EACH'}" size=8 style="background:#ef9; text-align:center;margin: 1px;text-align: left;font-size: 11px; padding-left: 5px" disabled>
</td>


<td align="right">
	{if $l_item.currency_code}
		{$l_item.currency_code}@{$l_item.currency_rate}
	{/if}
	<input size=6 value="{$l_item.order_price|default:$l_item.resell_price|number_format:$config.global_cost_decimal_points:".":""}" style="background:#ef9;" disabled>
</td>

<td align=center>
{$l_item.cost_indicate|default:"-"}
</td>

<td align=center>
	<input value="{$l_item.qty}" size=1 style="width:30px;background:#ef9" disabled>
	<input style="width:30px; background:#ef9;" size=1 value="{$l_item.qty_loose}" disabled>
</td>

	<!-- FOC -->
	<td align="center" style="{if $config.po_use_simple_mode}display:none;{/if}">
		<input value="{$l_item.foc}"size=1 style="width:30px;background:#ef9;" disabled>
		<input style="width:30px; background:#ef9;" size=1 value="{$l_item.foc_loose}" disabled>
	</td>
{/if}
<!--END SINGLE DELIVER BRANCHES-->

<!--============================================================================== -->

<!-- Sales Trend -->
<td style="{if $config.po_use_simple_mode}display:none;{/if}">&nbsp;</td>

<!-- Total Pcs -->
<td>&nbsp;</td>

<!-- Total FOC -->
<td>&nbsp;</td>

<!-- Gross Amount -->
<td>&nbsp;</td>

<!-- Tax -->
<td style="{if $config.po_use_simple_mode}display:none;{/if}">
	<input value="{$l_item.tax}"size=3 style="background:#ef9;" disabled>
</td>

<!-- Discount-->
<td style="{if $config.po_use_simple_mode}display:none;{/if}">
	<input value="{$l_item.discount}"size=10 style="background:#ef9;" disabled>
</td>

{assign var=colspan value=3}
{if $form.is_under_gst}
	{assign var=colspan value=$colspan+2}
{/if}
{if $sessioninfo.privilege.SHOW_REPORT_GP}{assign var=colspan value=$colspan+1}{/if}
<td colspan="{$colspan}">&nbsp;</td>
</tr>