{*
2/24/2011 4:25:33 PM Alex
- price change amount calculation bugs

3/2/2011 6:07:50 PM Alex
- fix closing stock calculation bugs

3/3/2011 2:09:45 PM Alex
- disable input of vendor if found that vendor not from generated cache

3/7/2011 7:00:11 PM Alex
- save extra value for calculation

5/30/2011 5:21:13 PM Alex
- change stock take variance as (stock check - opening stock)

6/30/2011 10:39:53 AM Alex
- Turn days use 0 number format

7/15/2011 11:44:00 AM Alex
- remove % symbol in excel mode to avoid auto convert to numberic in microsoft excel

7/27/2011 10:46:30 AM Alex
- rename "GRN / GRA Adjustment" => "GRN / GRA Adjustment / Write Off"
- rename "Other Income" => "Other Income / Rebate"
- hide "Rebate"
- change calculation "(Average Opening Stock + Closing Stock) / 2"

11/15/2011 3:12:51 PM Alex
- remove "Rebate"

3/5/2012 3:42:34 PM Alex
- add title for each column

3/7/2012 3:48:23 PM Alex
- add title for gp % and other column

3/13/2012 10:13:39 AM Alex
- remove adj (%) column after actual sales

3/19/2012 3:59:10 PM Alex
- fix stock check data to compatible with previous cache

4/6/2012 11:03:40 AM Alex
- show item list
- fix vendor missing add stock before stock check data

*}

{foreach from=$vendor.$cid key=vid item=v}

	<!--------SYSTEM OPENING STOCK PRICE----------->
	{assign var=vos_OUTRIGHT_cost value=$vos.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vos_OUTRIGHT_selling value=$vos.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vos_CONSIGN_cost value=$vos.$cid.$vid.CONSIGN.cost_price}
	{assign var=vos_CONSIGN_selling value=$vos.$cid.$vid.CONSIGN.selling_price}

	<!--------OPENING STOCK PRICE----------->
	{assign var=vcos_OUTRIGHT_cost value=$vcos.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vcos_OUTRIGHT_selling value=$vcos.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vcos_CONSIGN_cost value=$vcos.$cid.$vid.CONSIGN.cost_price}
	{assign var=vcos_CONSIGN_selling value=$vcos.$cid.$vid.CONSIGN.selling_price}

	<!--------STOCK TAKE VARIANCE PRICE----------->
	{*
	{if $stv.$cid.OUTRIGHT}
		{assign var=vstv_OUTRIGHT_cost value=$vos_OUTRIGHT_cost-$vcos_OUTRIGHT_cost}
		{assign var=vstv_OUTRIGHT_selling value=$vos_OUTRIGHT_selling-$vcos_OUTRIGHT_selling}
		{assign var=vstv_CONSIGN_cost value=$vos_CONSIGN_cost-$vcos_CONSIGN_cost}
		{assign var=vstv_CONSIGN_selling value=$vos_CONSIGN_selling-$vcos_CONSIGN_selling}
	{else}
		{assign var=vstv_OUTRIGHT_cost value=0}
		{assign var=vstv_OUTRIGHT_selling value=0}
		{assign var=vstv_CONSIGN_cost value=0}
		{assign var=vstv_CONSIGN_selling value=0}
	{/if}
	*}
		
	{assign var=vstv_OUTRIGHT_cost value=$vstv.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vstv_OUTRIGHT_selling value=$vstv.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vstv_CONSIGN_cost value=$vstv.$cid.$vid.CONSIGN.cost_price}
	{assign var=vstv_CONSIGN_selling value=$vstv.$cid.$vid.CONSIGN.selling_price}

	<!--------STOCK CHECK STOCK PRICE----------->
	{if $stc_date || $vstv_OUTRIGHT_cost != '' || $vstv_OUTRIGHT_selling != ''}
		{assign var=vstc_OUTRIGHT_cost value=$vcos_OUTRIGHT_cost+$vstv_OUTRIGHT_cost}
		{assign var=vstc_OUTRIGHT_selling value=$vcos_OUTRIGHT_selling+$vstv_OUTRIGHT_selling}
		{assign var=vstc_CONSIGN_cost value=$vcos_CONSIGN_cost+$vstv_CONSIGN_cost}
		{assign var=vstc_CONSIGN_selling value=$vcos_CONSIGN_selling+$vstv_CONSIGN_selling}
	{/if}

	<!--------CSA OPENING VARIANCE STOCK PRICE----------->
	{assign var=vcov_OUTRIGHT_cost value=$vcov.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vcov_OUTRIGHT_selling value=$vcov.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vcov_CONSIGN_cost value=$vcov.$cid.$vid.CONSIGN.cost_price}
	{assign var=vcov_CONSIGN_selling value=$vcov.$cid.$vid.CONSIGN.selling_price}

	<!--------ACTUAL OPENING STOCK PRICE----------->
	{assign var=vaos_OUTRIGHT_cost value=$vcos_OUTRIGHT_cost+$vstv_OUTRIGHT_cost+$vcov_OUTRIGHT_cost}
	{assign var=vaos_OUTRIGHT_selling value=$vcos_OUTRIGHT_selling+$vstv_OUTRIGHT_selling+$vcov_OUTRIGHT_selling}
	{assign var=vaos_CONSIGN_cost value=$vcos_CONSIGN_cost+$vstv_CONSIGN_cost+$vcov_OUTRIGHT_cost}
	{assign var=vaos_CONSIGN_selling value=$vcos_CONSIGN_selling+$vstv_CONSIGN_selling+$vcov_OUTRIGHT_selling}

	<!--------STOCK RECEIVE == GRN PRICE----------->
	{assign var=vsr_OUTRIGHT_cost value=$vsr.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vsr_OUTRIGHT_selling value=$vsr.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vsr_CONSIGN_cost value=$vsr.$cid.$vid.CONSIGN.cost_price}
	{assign var=vsr_CONSIGN_selling value=$vsr.$cid.$vid.CONSIGN.selling_price}

	<!--------GRN ADJUSTMENT PRICE----------->
	{assign var=vgrn_OUTRIGHT_cost value=$vgrn.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vgrn_OUTRIGHT_selling value=$vgrn.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vgrn_CONSIGN_cost value=$vgrn.$cid.$vid.CONSIGN.cost_price}
	{assign var=vgrn_CONSIGN_selling value=$vgrn.$cid.$vid.CONSIGN.selling_price}

	<!--------ADJUSTMENT (ARMS) PRICE----------->
	{assign var=vadj_OUTRIGHT_cost value=$vadj.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vadj_OUTRIGHT_selling value=$vadj.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vadj_CONSIGN_cost value=$vadj.$cid.$vid.CONSIGN.cost_price}
	{assign var=vadj_CONSIGN_selling value=$vadj.$cid.$vid.CONSIGN.selling_price}

	<!--------ACTUAL STOCK PRICE----------->
	{assign var=vas_OUTRIGHT_cost value=$vaos_OUTRIGHT_cost+$vsr_OUTRIGHT_cost+$vgrn_OUTRIGHT_cost+$vadj_OUTRIGHT_cost}
	{assign var=vas_OUTRIGHT_selling value=$vaos_OUTRIGHT_selling+$vsr_OUTRIGHT_selling+$vgrn_OUTRIGHT_selling+$vadj_OUTRIGHT_selling}
	{assign var=vas_CONSIGN_cost value=$vaos_CONSIGN_cost+$vsr_CONSIGN_cost+$vgrn_CONSIGN_cost+$vadj_CONSIGN_cost}
	{assign var=vas_CONSIGN_selling value=$vaos_CONSIGN_selling+$vsr_CONSIGN_selling+$vgrn_CONSIGN_selling+$vadj_CONSIGN_selling}

	<!--------RETURN STOCK PRICE----------->
	{assign var=vrs_OUTRIGHT_cost value=$vrs.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vrs_OUTRIGHT_selling value=$vrs.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vrs_CONSIGN_cost value=$vrs.$cid.$vid.CONSIGN.cost_price}
	{assign var=vrs_CONSIGN_selling value=$vrs.$cid.$vid.CONSIGN.selling_price}

	<!--------TRANSFER STOCK PRICE----------->
	{assign var=vts_OUTRIGHT_cost value=$vts.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vts_OUTRIGHT_selling value=$vts.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vts_CONSIGN_cost value=$vts.$cid.$vid.CONSIGN.cost_price}
	{assign var=vts_CONSIGN_selling value=$vts.$cid.$vid.CONSIGN.selling_price}

	<!--------INTER DEPT TRANSFER PRICE----------->
	{assign var=vidt_OUTRIGHT_cost value=$vidt.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vidt_OUTRIGHT_selling value=$vidt.$cid.$vid.OUTRIGHT.selling_price}

	<!--------PROMOTION AMOUNT PRICE----------->
	{assign var=vpa_OUTRIGHT_selling value=$vpa.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vpa_CONSIGN_selling value=$vpa.$cid.$vid.CONSIGN.selling_price}

	<!--------PROMOTION AMOUNT PRICE----------->
	{assign var=vpca_OUTRIGHT_selling value=$vpca.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vpca_CONSIGN_selling value=$vpca.$cid.$vid.CONSIGN.selling_price}

	<!--------REBATE PRICE----------->
	{assign var=vr_OUTRIGHT_selling value=$vr.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=vr_CONSIGN_selling value=$vr.$cid.$vid.CONSIGN.selling_price}

	<!--------ACTUAL SALES PRICE----------->
	{assign var=vacs_OUTRIGHT_cost value=$vacs.$cid.$vid.OUTRIGHT.cost_price}
	{assign var=vacs_OUTRIGHT_selling value=$vacs.$cid.$vid.OUTRIGHT.selling_price}

	{* assign var=vacs_CONSIGN_cost value=$vacs.$cid.$vid.CONSIGN.cost_price *}

	{assign var=vacs_CONSIGN_selling value=$vacs.$cid.$vid.CONSIGN.selling_price}
	{assign var=vacs_cgp value=$vacs.$cid.$vid.CONSIGN.gp}

	{if $vacs_CONSIGN_selling eq 0 or $vacs_CONSIGN_selling eq ''}
		{assign var=vacs_CONSIGN_selling value=0}
	{/if}

	{assign var=vacs_CONSIGN_cost value=$vacs_cgp/100*$vacs_CONSIGN_selling}
	{assign var=vacs_CONSIGN_cost value=$vacs_CONSIGN_selling-$vacs_CONSIGN_cost}

	<!--------CLOSING STOCK PRICE----------->
	{assign var=vcs_OUTRIGHT_cost value=$vas_OUTRIGHT_cost-$vrs_OUTRIGHT_cost+$vidt_OUTRIGHT_cost-$vacs_OUTRIGHT_cost}
	{assign var=vcs_OUTRIGHT_selling value=$vas_OUTRIGHT_selling-$vrs_OUTRIGHT_selling+$vidt_OUTRIGHT_selling-$vpa_OUTRIGHT_selling-$vpca_OUTRIGHT_selling-$vacs_OUTRIGHT_selling+$vr_OUTRIGHT_selling}

	<!--------OTHER INCOME PRICE----------->
	{assign var=voi_OUTRIGHT_selling value=$voi.$cid.$vid.OUTRIGHT.selling_price}
	{assign var=voi_CONSIGN_selling value=$voi.$cid.$vid.CONSIGN.selling_price}

	<!--------PROFIT MARGIN PRICE----------->
	{assign var=vpm_OUTRIGHT_selling value=$vacs_OUTRIGHT_selling-$vacs_OUTRIGHT_cost+$vr_OUTRIGHT_selling+$voi_OUTRIGHT_selling}
	{assign var=vpm_CONSIGN_selling value=$vacs_CONSIGN_selling-$vacs_CONSIGN_cost+$vr_CONSIGN_selling+$voi_CONSIGN_selling}

	<!--------AVERAGE STOCK PRICE----------->
	{assign var=vav_OUTRIGHT_selling value=$vaos_OUTRIGHT_cost+$vcs_OUTRIGHT_cost}
	{assign var=vav_OUTRIGHT_selling value=$vav_OUTRIGHT_selling/2}

	{* set title for vendor*}
	{if $type eq 'outright' && $v.OUTRIGHT.descrip}
		{assign var=vendor_desc value=$v.OUTRIGHT.descrip}
	{else}
		{assign var=vendor_desc value=$v.CONSIGN.descrip}
	{/if}
	{assign var=vendor_title value="Vendor: $vendor_desc"}
		
	{assign var=vcol1 value="$col1|$vendor_title"}
	{assign var=vcol2 value="$col2|$vendor_title"}
	{assign var=vcol3 value="$col3|$vendor_title"}
	{assign var=vcol4 value="$col4|$vendor_title"}
	{assign var=vcol5 value="$col5|$vendor_title"}
	{assign var=vcol6 value="$col6|$vendor_title"}
	{assign var=vcol7 value="$col7|$vendor_title"}
	{assign var=vcol8 value="$col8|$vendor_title"}
	{assign var=vcol9 value="$col9|$vendor_title"}
	{assign var=vcol10 value="$col10|$vendor_title"}
	{assign var=vcol11 value="$col11|$vendor_title"}
	{assign var=vcol12 value="$col12|$vendor_title"}
	{assign var=vcol13 value="$col13|$vendor_title"}
	{assign var=vcol14 value="$col14|$vendor_title"}
	{assign var=vcol15 value="$col15|$vendor_title"}
	{assign var=vcol16 value="$col16|$vendor_title"}
	{assign var=vcol17 value="$col17|$vendor_title"}
	{assign var=vcol18 value="$col18|$vendor_title"}
	{assign var=vcol19 value="$col19|$vendor_title"}
	{assign var=vcol20 value="$col20|$vendor_title"}
	{assign var=vcolgp value="$colgp|$vendor_title"}
	{assign var=vcolper value="$colper|$vendor_title"}
	{assign var=vcoladjgp value="$coladjgp|$vendor_title"}

{if $type eq 'outright' && $v.OUTRIGHT.descrip}

	{if $miss_vendor.$cid.$vid.OUTRIGHT}
		{assign var=disable_vendor_input value="true"}
	{else}
		{assign var=disable_vendor_input value=""}
	{/if}

	<!-------------------------DEPARTMENT OUTRIGHT------------------------------>
	<tr class="vendors dept_{$cid}_ven_{$vid}" funct="OUTRIGHT,{$rid},{$cid},{$vid}">
		<th rowspan=2><a onclick="ajax_load_sku_items('OUTRIGHT',{$cid},{$vid})">{$v.OUTRIGHT.descrip}</a><br />(Outright)</th>
		<th>CP</th>
		<!---------SYSTEM OPENING STOCK OUTRIGHT GP---------->
		<td class="keyin r" id="vos_o_cost,{$cid},{$vid}" title="{$vcol1}">{$vos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vos_OUTRIGHT_selling ne 0 or $vos_OUTRIGHT_selling ne ''}
		    {assign var=vos_gp value=$vos_OUTRIGHT_selling-$vos_OUTRIGHT_cost}
			{assign var=vos_gp value=$vos_gp/$vos_OUTRIGHT_selling*100}
		{else}
		    {assign var=vos_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vos_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vos_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OPENING STOCK OUTRIGHT GP---------->
		<td class="keyin r" id="vcos_o_cost,{$cid},{$vid}" title="{$vcol2}">{$vcos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vcos_OUTRIGHT_selling ne 0 or $vcos_OUTRIGHT_selling ne ''}
		    {assign var=vcos_gp value=$vcos_OUTRIGHT_selling-$vcos_OUTRIGHT_cost}
			{assign var=vcos_gp value=$vcos_gp/$vcos_OUTRIGHT_selling*100}
		{else}
		    {assign var=vcos_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vcos_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vcos_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK TAKE VARIANCE OUTRIGHT GP---------->
		<td class="keyin r" id="vstv_o_cost,{$cid},{$vid}" title="{$vcol3}">{$vstv_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vstv_OUTRIGHT_selling ne 0 or $vstv_OUTRIGHT_selling ne ''}
		    {assign var=vstv_gp value=$vstv_OUTRIGHT_selling-$vstv_OUTRIGHT_cost}
			{assign var=vstv_gp value=$vstv_gp/$vstv_OUTRIGHT_selling*100}
		{else}
		    {assign var=vstv_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vstv_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vstv_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK CHECK OUTRIGHT GP---------->
		<td class="keyin r" title="{$vcol4}">{$vstc_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vstc_OUTRIGHT_selling ne 0 or $vstc_OUTRIGHT_selling ne ''}
		    {assign var=vstc_gp value=$vstc_OUTRIGHT_selling-$vstc_OUTRIGHT_cost}
			{assign var=vstc_gp value=$vstc_gp/$vstc_OUTRIGHT_selling*100}
		{else}
		    {assign var=vstc_gp value=0}
		{/if}
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>{$vstc_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------CSA OPENING VARIANCE VARIANCE OUTRIGHT GP---------->
		<td class="keyin r" title="{$vcol5}"><a onclick="ajax_load_before_stock_check('OUTRIGHT',{$cid},{$vid})">{$vcov_OUTRIGHT_cost|number_format:2|ifzero:'-'}</a></td>

		{if $vcov_OUTRIGHT_selling ne 0 or $vcov_OUTRIGHT_selling ne ''}
		    {assign var=vcov_gp value=$vcov_OUTRIGHT_selling-$vcov_OUTRIGHT_cost}
			{assign var=vcov_gp value=$vcov_gp/$vcov_OUTRIGHT_selling*100}
		{else}
		    {assign var=vcov_gp value=0}
		{/if}
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>{$vcov_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL OPENING STOCK OUTRIGHT GP---------->
		<td class="keyin r" id="vaos_o_cost,{$cid},{$vid}" title="{$vcol6}">{$vaos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vaos_OUTRIGHT_selling ne 0 or $vaos_OUTRIGHT_selling ne ''}
		    {assign var=vaos_gp value=$vaos_OUTRIGHT_selling-$vaos_OUTRIGHT_cost}
			{assign var=vaos_gp value=$vaos_gp/$vaos_OUTRIGHT_selling*100}
		{else}
		    {assign var=vaos_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vaos_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vaos_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK RECEIVE OUTRIGHT GP---------->
		<td class="keyin r" id="vsr_o_cost,{$cid},{$vid}" title="{$vcol7}">{$vsr_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vsr_OUTRIGHT_selling ne 0 or $vsr_OUTRIGHT_selling ne ''}
		    {assign var=vsr_gp value=$vsr_OUTRIGHT_selling-$vsr_OUTRIGHT_cost}
			{assign var=vsr_gp value=$vsr_gp/$vsr_OUTRIGHT_selling*100}
		{else}
		    {assign var=vsr_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vsr_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vsr_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------GRN ADJUSTMENT OUTRIGHT GP---------->
		<td class="r" title="{$vcol8}">
		
		{if $disable_input || $disable_vendor_input}
			{$vgrn_OUTRIGHT_cost|number_format:2|ifzero:'-'}
		{else}
			{input class="r vgrn_o_cost_$cid" id="vgrn_o_cost,$cid,$vid" size="10" name="grn[$cid][$vid][OUTRIGHT][cost]" onchange="this.value=round(this.value,2);vendor_changes('OUTRIGHT',$rid,$cid,$vid);" value=$vgrn_OUTRIGHT_cost|number_format:2|ifzero:''}
		{/if}
		</td>

		{if $vgrn_OUTRIGHT_selling ne 0 or $vgrn_OUTRIGHT_selling ne ''}
		    {assign var=vgrn_gp value=$vgrn_OUTRIGHT_selling-$vgrn_OUTRIGHT_cost}
			{assign var=vgrn_gp value=$vgrn_gp/$vgrn_OUTRIGHT_selling*100}
		{else}
		    {assign var=vgrn_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vgrn_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vgrn_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ADJUSTMENT OUTRIGHT GP---------->
		<td class="keyin r" id="vadj_o_cost,{$cid},{$vid}" title="{$vcol9}">{$vadj_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>
		
		{if $vadj_OUTRIGHT_selling ne 0 or $vadj_OUTRIGHT_selling ne ''}
		    {assign var=vadj_gp value=$vadj_OUTRIGHT_selling-$vadj_OUTRIGHT_cost}
			{assign var=vadj_gp value=$vadj_gp/$vadj_OUTRIGHT_selling*100}
		{else}
		    {assign var=vadj_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vadj_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vadj_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL STOCK OUTRIGHT GP---------->
		<td class="r" id="vas_o_cost,{$cid},{$vid}" title="{$vcol10}">{$vas_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>
		
		{if $vas_OUTRIGHT_selling ne 0 or $vas_OUTRIGHT_selling ne ''}
		    {assign var=vas_gp value=$vas_OUTRIGHT_selling-$vas_OUTRIGHT_cost}
			{assign var=vas_gp value=$vas_gp/$vas_OUTRIGHT_selling*100}
		{else}
		    {assign var=vas_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vas_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vas_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------RETURN STOCK OUTRIGHT GP---------->
		<td class="keyin r" id="vrs_o_cost,{$cid},{$vid}" title="{$vcol11}">{$vrs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vrs_OUTRIGHT_selling ne 0 or $vrs_OUTRIGHT_selling ne ''}
		    {assign var=vrs_gp value=$vrs_OUTRIGHT_selling-$vrs_OUTRIGHT_cost}
			{assign var=vrs_gp value=$vrs_gp/$vrs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vrs_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vrs_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vrs_gp|number_format:2|ifzero:'-':$percent}</td>

{*		<!---------TRANSFER STOCK OUTRIGHT GP---------->
		<td class="keyin r" id="vts_o_cost,{$cid},{$vid}">{$vts_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $vts_OUTRIGHT_selling ne 0 or $vts_OUTRIGHT_selling ne ''}
		    {assign var=vts_gp value=$vts_OUTRIGHT_selling-$vts_OUTRIGHT_cost}
			{assign var=vts_gp value=$vts_gp/$vts_OUTRIGHT_selling*100}
		{else}
		    {assign var=vts_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vts_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vts_gp|number_format:2|ifzero:'-':$percent}</td>
*}

		<!---------IDT OUTRIGHT GP---------->
		<td class="r" title="{$vcol12}">
 		{if $disable_input || $disable_vendor_input}
			{$vidt_OUTRIGHT_cost|number_format:2|ifzero:'-'}
		{else}
			{input class="r vidt_o_cost_$cid" id="vidt_o_cost,$cid,$vid" size="10" name="idt[$cid][$vid][OUTRIGHT][cost]" onchange="this.value=round(this.value,2);vendor_changes('OUTRIGHT',$rid,$cid,$vid);" value=$vidt_OUTRIGHT_cost|number_format:2|ifzero:''}
		{/if}
		</td>

		{if $vidt_OUTRIGHT_selling ne 0 or $vidt_OUTRIGHT_selling ne ''}
		    {assign var=vidt_gp value=$vidt_OUTRIGHT_selling-$vidt_OUTRIGHT_cost}
			{assign var=vidt_gp value=$vidt_gp/$vidt_OUTRIGHT_selling*100}
		{else}
		    {assign var=vidt_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vidt_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vidt_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PROMOTION AMOUNT OUTRIGHT GP---------->
		<td rowspan=2 class="keyin r" id="vpa_o_selling,{$cid},{$vid}"  title="{$vcol13}">{$vpa_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
		
		{if $vacs_OUTRIGHT_selling ne 0 or $vacs_OUTRIGHT_selling ne ''}
			{assign var=vpa_gp value=$vpa_OUTRIGHT_selling/$vacs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vpa_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vpa_o_gp,{$cid},{$vid}" title="{$vcolper}" {$background}>{$vpa_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PRICE CHANGE AMOUNT OUTRIGHT GP---------->
		<td rowspan=2 class="keyin r" id="vpca_o_selling,{$cid},{$vid}"  title="{$vcol14}">{$vpca_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
		
		{if $vacs_OUTRIGHT_selling ne 0 or $vacs_OUTRIGHT_selling ne ''}
			{assign var=vpca_gp value=$vpca_OUTRIGHT_selling/$vacs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vpca_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vpca_o_gp,{$cid},{$vid}" title="{$vcolper}" {$background}>{$vpca_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL SALES OUTRIGHT GP---------->
		<td class="keyin r" id="vacs_o_cost,{$cid},{$vid}"  title="{$vcol15}">{$vacs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>
		
		{if $vacs_OUTRIGHT_selling ne 0 or $vacs_OUTRIGHT_selling ne ''}
		    {assign var=vacs_gp value=$vacs_OUTRIGHT_selling-$vacs_OUTRIGHT_cost}
			{assign var=vacs_gp value=$vacs_gp/$vacs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vacs_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vacs_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vacs_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------REBATE OUTRIGHT GP---------->
		{*<td rowspan=2 class='hide'>
 		{if $disable_input || $disable_vendor_input}
			{$vr_OUTRIGHT_selling|number_format:2|ifzero:'-'}
		{else}
			{input class="r" class="r vr_o_selling_$cid" id="vr_o_selling,$cid,$vid" size="10" name="rebate[$cid][$vid][OUTRIGHT][selling]" onchange="this.value=round(this.value,2);vendor_changes('OUTRIGHT',$rid,$cid,$vid);" value=$vr_OUTRIGHT_selling|number_format:2|ifzero:''}
		{/if}
		</td>*}
		{*
		{if $vacs_OUTRIGHT_selling ne 0 or $vacs_OUTRIGHT_selling ne ''}
		    {assign var=vr_gp value=$vacs_OUTRIGHT_selling-$vacs_OUTRIGHT_cost+$vr_OUTRIGHT_selling}
			{assign var=vr_gp value=$vr_gp/$vacs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vr_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vr_o_gp,{$cid},{$vid}" title="{$vcoladjgp}" {$background}>{$vr_gp|number_format:2|ifzero:'-':$percent}</td>	
		*}

		<!---------CLOSING STOCK OUTRIGHT GP---------->
		<td class="r"  title="{$vcol16}">
 		{if $disable_input}
			{$vcs_OUTRIGHT_cost|number_format:2|ifzero:'-'}
		{else}
			<span class="vcs_o_cost_{$cid}" id="vcs_o_cost,{$cid},{$vid}">{$vcs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</span>
			{input class="r hide r_vcs_o_cost_$cid" id="r_vcs_o_cost,$cid,$vid" size="10" name="closing[$cid][$vid][OUTRIGHT][cost]" readonly=true value=$vcs_OUTRIGHT_cost}
		{/if}
		</td>

		{if $vcs_OUTRIGHT_selling ne 0 or $vcs_OUTRIGHT_selling ne ''}
		    {assign var=vcs_gp value=$vcs_OUTRIGHT_selling-$vcs_OUTRIGHT_cost}
			{assign var=vcs_gp value=$vcs_gp/$vcs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vcs_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vcs_o_gp,{$cid},{$vid}" title="{$vcolgp}" {$background}>{$vcs_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OTHER INCOME OUTRIGHT ---------->
		<td rowspan=2 class="r"  title="{$vcol17}">
 		{if $disable_input || $disable_vendor_input}
			{$voi_OUTRIGHT_selling|number_format:2|ifzero:'-'}
		{else}
			{input class="r voi_o_selling_$cid" id="voi_o_selling,$cid,$vid" size="10" name="other[$cid][$vid][OUTRIGHT][selling]" onchange="this.value=round(this.value,2);vendor_changes('OUTRIGHT',$rid,$cid,$vid);" value=$voi_OUTRIGHT_selling|number_format:2|ifzero:''}
		{/if}
		</td>

		<!---------PROFIT MARGIN OUTRIGHT ---------->
		<td rowspan=2 class="r" id="vpm_o_selling,{$cid},{$vid}" title="{$vcol18}">{$vpm_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		{if $vacs_OUTRIGHT_selling ne 0 or $vacs_OUTRIGHT_selling ne ''}
			{assign var=vpm_gp value=$vpm_OUTRIGHT_selling/$vacs_OUTRIGHT_selling*100}
		{else}
		    {assign var=vpm_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="vpm_o_gp,{$cid},{$vid}" title="{$vcoladjgp}" {$background}>{$vpm_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------AVERAGE STOCK OUTRIGHT ---------->
		<td rowspan=2 class="r" id="vav_o_selling,{$cid},{$vid}" title="{$vcol19}">{$vav_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<!---------TURN DAYS OUTRIGHT ---------->
		{if $vacs_OUTRIGHT_cost ne 0 && $vacs_OUTRIGHT_cost ne ''}
			{assign var=vtd_OUTRIGHT_selling value=$vav_OUTRIGHT_selling/$vacs_OUTRIGHT_cost*$d}
		{else}
		    {assign var=vtd_OUTRIGHT_selling value=0}
		{/if}

		<td rowspan=2 class="r" id="vtd_o_selling,{$cid},{$vid}"  title="{$vcol20}">{$vtd_OUTRIGHT_selling|number_format:0|ifzero:'-'}</td>
	</tr>
	<tr class="vendors dept_{$cid}_ven_{$vid}" funct="OUTRIGHT,{$rid},{$cid},{$vid}">
		<th>SP</th>
		<td class="keyin r" id="vos_o_selling,{$cid},{$vid}"  title="{$vcol1}">{$vos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="keyin r" id="vcos_o_selling,{$cid},{$vid}" title="{$vcol2}">{$vcos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="keyin r" id="vstv_o_selling,{$cid},{$vid}" title="{$vcol3}">{$vstv_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="keyin r" title="{$vcol4}">{$vstc_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="keyin r" title="{$vcol5}"><a onclick="ajax_load_before_stock_check('OUTRIGHT',{$cid},{$vid})">{$vcov_OUTRIGHT_selling|number_format:2|ifzero:'-'}</a></td>

		<td class="keyin r" id="vaos_o_selling,{$cid},{$vid}" title="{$vcol6}">{$vaos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="keyin r" id="vsr_o_selling,{$cid},{$vid}" title="{$vcol7}">{$vsr_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" title="{$vcol8}">
  		{if $disable_input || $disable_vendor_input}
			{$vgrn_OUTRIGHT_selling|number_format:2|ifzero:'-'}
		{else}
			{input class="r vgrn_o_selling_$cid" id="vgrn_o_selling,$cid,$vid" size="10" name="grn[$cid][$vid][OUTRIGHT][selling]" onchange="this.value=round(this.value,2);vendor_changes('OUTRIGHT',$rid,$cid,$vid);" value=$vgrn_OUTRIGHT_selling|number_format:2|ifzero:''}
		{/if}
		</td>
		<td class="keyin r" id="vadj_o_selling,{$cid},{$vid}" title="{$vcol9}">{$vadj_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="vas_o_selling,{$cid},{$vid}" title="{$vcol10}">{$vas_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="keyin r" id="vrs_o_selling,{$cid},{$vid}" title="{$vcol11}">{$vrs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

{*		<td class="r" id="vts_o_selling,{$cid},{$vid}">{$vts_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>  *}
		<td class="r" title="{$vcol12}">
   		{if $disable_input || $disable_vendor_input}
			{$vidt_OUTRIGHT_selling|number_format:2|ifzero:'-'}
		{else}
			{input class="r vidt_o_selling_$cid" id="vidt_o_selling,$cid,$vid" size="10" name="idt[$cid][$vid][OUTRIGHT][selling]" onchange="this.value=round(this.value,2);vendor_changes('OUTRIGHT',$rid,$cid,$vid);" value=$vidt_OUTRIGHT_selling|number_format:2|ifzero:''}
		{/if}

		</td>
		<td class="keyin r" id="vacs_o_selling,{$cid},{$vid}" title="{$vcol15}">{$vacs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" title="{$vcol16}">
   		{if $disable_input}
			{$vcs_OUTRIGHT_selling|number_format:2|ifzero:'-'}
		{else}
			<span class="vcs_o_selling_{$cid}" id="vcs_o_selling,{$cid},{$vid}">{$vcs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</span>

			{input class="r hide r_vcs_o_selling_$cid" id="r_vcs_o_selling,$cid,$vid" size="10" name="closing[$cid][$vid][OUTRIGHT][selling]"  readonly=true value=$vcs_OUTRIGHT_selling}

		{/if}
		</td>
 	</tr>
 	
 	{if !$disable_input}
		<span class="hide" id="r_vos_o_cost,{$cid},{$vid}">{$vos_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vcos_o_cost,{$cid},{$vid}">{$vcos_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vstv_o_cost,{$cid},{$vid}">{$vstv_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vstc_o_cost,{$cid},{$vid}">{$vstc_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vcov_o_cost,{$cid},{$vid}">{$vcov_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vaos_o_cost,{$cid},{$vid}">{$vaos_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vsr_o_cost,{$cid},{$vid}">{$vsr_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vrs_o_cost,{$cid},{$vid}">{$vrs_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vpa_o_selling,{$cid},{$vid}">{$vpa_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vadj_o_cost,{$cid},{$vid}">{$vadj_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vas_o_cost,{$cid},{$vid}">{$vas_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vpca_o_selling,{$cid},{$vid}">{$vpca_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vacs_o_cost,{$cid},{$vid}">{$vacs_OUTRIGHT_cost}</span>
		<span class="hide" id="r_vos_o_selling,{$cid},{$vid}">{$vos_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vcos_o_selling,{$cid},{$vid}">{$vcos_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vstv_o_selling,{$cid},{$vid}">{$vstv_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vaos_o_selling,{$cid},{$vid}">{$vaos_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vsr_o_selling,{$cid},{$vid}">{$vsr_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vadj_o_selling,{$cid},{$vid}">{$vadj_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vas_o_selling,{$cid},{$vid}">{$vas_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vrs_o_selling,{$cid},{$vid}">{$vrs_OUTRIGHT_selling}</span>
		<span class="hide" id="r_vacs_o_selling,{$cid},{$vid}">{$vacs_OUTRIGHT_selling}</span>
	{/if}

{elseif $type eq 'consign' && $v.CONSIGN.descrip}
	<!-------------------------DEPARTMENT CONSIGN------------------------------>
	<tr class="vendors dept_{$cid}_ven_{$vid}" funct="CONSIGN,{$rid},{$cid},{$vid}">
		<th rowspan=2><a  onclick="ajax_load_sku_items('CONSIGN',{$cid},{$vid})">{$v.CONSIGN.descrip}</a><br>(Consignment)</th>
		<th>CP</th>
		<td class="r" title="{$vcol1}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol2}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol3}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol4}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol5}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol6}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol7}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol8}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol9}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol10}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$vcol11}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>

		<!---------IDT CONSIGN GP---------->
		<td class="r" title="{$vcol12}">{* input class="r vidt_c_cost_$cid" id="vidt_c_cost,$cid,$vid" size="10" name="idt[$cid][$vid][CONSIGN][cost]" onchange="this.value=round(this.value,2);vendor_changes('CONSIGN',$rid,$cid,$vid);" value=$vidt_CONSIGN_cost|number_format:2|ifzero:'0' *}&nbsp;</td>

		{if $vidt_CONSIGN_selling ne 0 or $vidt_CONSIGN_selling ne ''}
			{assign var=vidt_cgp value=$vidt_CONSIGN_selling-$vidt_CONSIGN_cost}
			{assign var=vidt_cgp value=$vidt_cgp/$vidt_CONSIGN_selling*100}
		{else}
		    {assign var=vidt_cgp value=0}
		{/if}
		<td rowspan=2 class="r" id="vidt_c_gp,{$cid},{$vid}" title="{$vcolper}" {$background}>&nbsp;{* $vidt_cgp|number_format:2|ifzero:'-':$percent *}</td>

		<!---------PROMOTION AMOUNT CONSIGN GP---------->
		<td rowspan=2 class="keyin r" id="vpa_c_selling,{$cid},{$vid}" title="{$vcol13}">{$vpa_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
		
		{if $vacs_CONSIGN_selling ne 0 or $vacs_CONSIGN_selling ne ''}
			{assign var=vpa_cgp value=$vpa_CONSIGN_selling/$vacs_CONSIGN_selling*100}
		{else}
		    {assign var=vpa_cgp value=0}
		{/if}
		<td rowspan=2 class="r" id="vpa_c_gp,{$cid},{$vid}" title="{$vcolgp}"  {$background}>{$vpa_cgp|number_format:2|ifzero:'-':$percent}</td>

		<td rowspan=2 class="r" title="{$vcol14}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolper}" {$background}>&nbsp;</td>

		<!---------ACTUAL SALES CONSIGN GP---------->
		<td class="r vacs_c_cost_{$cid}" id="vacs_c_cost,{$cid},{$vid}" title="{$vcol15}">{$vacs_CONSIGN_cost|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>
   		{if $disable_input}
			{$vacs_cgp|number_format:2|ifzero:'-':$percent}
		{else}
			{input class="r vacs_c_gp_$cid" id="vacs_c_gp,$cid,$vid" size="10" name="actual[$cid][$vid][CONSIGN][gp]" onchange="this.value=round(this.value,2);vendor_changes('CONSIGN',$rid,$cid,$vid);" value=$vacs_cgp|number_format:2|ifzero:'':$percent}
		{/if}

		</td>

		<!---------REBATE CONSIGN GP---------->
		{*<td rowspan=2 class="r hide">
   		{if $disable_input}
			{$vr_CONSIGN_selling|number_format:2|ifzero:'-'}
		{else}
			{input class="r vr_c_selling_$cid" id="vr_c_selling,$cid,$vid" size="10" name="rebate[$cid][$vid][CONSIGN][selling]" onchange="this.value=round(this.value,2);vendor_changes('CONSIGN',$rid,$cid,$vid);" value=$vr_CONSIGN_selling|number_format:2|ifzero:''}
		{/if}
		</td>*}
		{*
		{if $vacs_CONSIGN_selling ne 0 or $vacs_CONSIGN_selling ne ''}
		    {assign var=vr_cgp value=$vacs_CONSIGN_selling-$vacs_CONSIGN_cost+$vr_CONSIGN_selling}
			{assign var=vr_cgp value=$vr_cgp/$vacs_CONSIGN_selling*100}
		{else}
		    {assign var=vr_cgp value=0}
		{/if}
		<td rowspan=2 class="r" id="vr_c_gp,{$cid},{$vid}" title="{$vcoladjgp}" {$background}>{$vr_cgp|number_format:2|ifzero:'-':$percent}</td>	*}

		<td class="r" title="{$vcol16}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$vcolgp}" {$background}>&nbsp;</td>

		<!---------OTHER INCOME CONSIGN GP---------->
		<td rowspan=2 class="r" title="{$vcol17}">
   		{if $disable_input}
			{$voi_CONSIGN_selling|number_format:2|ifzero:'-'}
		{else}
			{input class="r voi_c_selling_$cid" id="voi_c_selling,$cid,$vid" size="10" name="other[$cid][$vid][CONSIGN][selling]" onchange="this.value=round(this.value,2);vendor_changes('CONSIGN',$rid,$cid,$vid);" value=$voi_CONSIGN_selling|number_format:2|ifzero:''}
		{/if}
		</td>

		<!---------PROFIT MARGIN CONSIGN GP---------->
		<td rowspan=2 class="r" id="vpm_c_selling,{$cid},{$vid}" title="{$vcol18}">{$vpm_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

		{if $vacs_CONSIGN_selling ne 0 or $vacs_CONSIGN_selling ne ''}
			{assign var=vpm_cgp value=$vpm_CONSIGN_selling/$vacs_CONSIGN_selling*100}
		{else}
		    {assign var=vpm_cgp value=0}
		{/if}
		<td rowspan=2 class="r" id="vpm_c_gp,{$cid},{$vid}" title="{$vcoladjgp}" {$background}>{$vpm_cgp|number_format:2|ifzero:'-':$percent}</td>

		<!---------AVERAGE STOCK CONSIGN GP---------->
		<td rowspan=2 class="r" title="{$vcol19}">&nbsp;</td>

		<td rowspan=2 class="r" title="{$vcol20}">&nbsp;</td>
	</tr>
	<tr class="vendors dept_{$cid}_ven_{$vid}" funct="CONSIGN,{$rid},{$cid},{$vid}">
		<th>SP</th>
		<td class="r" title="{$vcol1}">&nbsp;</td>
		<td class="r" title="{$vcol2}">&nbsp;</td>
		<td class="r" title="{$vcol3}">&nbsp;</td>
		<td class="r" title="{$vcol4}">&nbsp;</td>
		<td class="r" title="{$vcol5}">&nbsp;</td>
		<td class="r" title="{$vcol6}">&nbsp;</td>
		<td class="r" title="{$vcol7}">&nbsp;</td>
		<td class="r" title="{$vcol8}">&nbsp;</td>
		<td class="r" title="{$vcol9}">&nbsp;</td>
		<td class="r" title="{$vcol10}">&nbsp;</td>
		<td class="r" title="{$vcol11}">&nbsp;</td>
		<td class="r" title="{$vcol12}">{* input class="r vidt_c_selling_$cid" id="vidt_c_selling,$cid,$vid" size="10" name="idt[$cid][$vid][CONSIGN][selling]" onchange="this.value=round(this.value,2);vendor_changes('CONSIGN',$rid,$cid,$vid);" value=$vidt_CONSIGN_selling|number_format:2|ifzero:'0' *}&nbsp;</td>
		<td class="keyin r" id="vacs_c_selling,{$cid},{$vid}" title="{$vcol15}">{$vacs_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" title="{$vcol16}">&nbsp;</td>
	</tr>
	
 	{if !$disable_input}
		<span class="hide" id="r_vpa_c_selling,{$cid},{$vid}">{$vpa_CONSIGN_selling}</span>
		<span class="hide" id="r_vacs_c_selling,{$cid},{$vid}">{$vacs_CONSIGN_selling}</span>
	{/if}
	
{/if}
{/foreach}
