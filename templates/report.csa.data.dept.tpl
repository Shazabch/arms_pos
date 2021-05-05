{*
6/30/2011 11:01:36 AM Alex
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

2/8/2012 11:20:52 AM Alex
- add calculcation for stock check

3/5/2012 3:42:34 PM Alex
- add title for each column

3/7/2012 3:48:23 PM Alex
- add title for gp % and other column

3/13/2012 10:13:39 AM Alex
- remove adj (%) column after actual sales
*}

{* if $dept_os_OUTRIGHT_cost or $dept_os_OUTRIGHT_selling or $dept_sr_OUTRIGHT_cost or $dept_sr_OUTRIGHT_selling or
	$dept_grn_OUTRIGHT_cost or $dept_grn_OUTRIGHT_selling or $dept_adj_OUTRIGHT_cost or $dept_adj_OUTRIGHT_selling or
	$dept_stv_OUTRIGHT_cost or $dept_stv_OUTRIGHT_selling or $dept_rs_OUTRIGHT_cost or $dept_rs_OUTRIGHT_selling or
	$dept_ts_OUTRIGHT_cost or $dept_ts_OUTRIGHT_selling or $dept_pa_OUTRIGHT_selling or $dept_acs_OUTRIGHT_cost or
	$dept_acs_OUTRIGHT_selling *}

	<!----------------------------TOTAL EACH DEPARTMENT OUTRIGHT----------------------------->
	<tbody class="storewide_outright">
	<tr class="dept_outright">
		{assign var=line_desc value=$root.r_descrip}
		<th rowspan=2>{$line_desc}<br>(Outright)</th>
		
			{* set title for each column*}
			{assign var=line_title value="Line: $line_desc"}
			{assign var=col1 value="$til1|$line_title"}
			{assign var=col2 value="$til2|$line_title"}
			{assign var=col3 value="$til3|$line_title"}
			{assign var=col4 value="$til4|$line_title"}
			{assign var=col5 value="$til5|$line_title"}
			{assign var=col6 value="$til6|$line_title"}
			{assign var=col7 value="$til7|$line_title"}
			{assign var=col8 value="$til8|$line_title"}
			{assign var=col9 value="$til9|$line_title"}
			{assign var=col10 value="$til10|$line_title"}
			{assign var=col11 value="$til11|$line_title"}
			{assign var=col12 value="$til12|$line_title"}
			{assign var=col13 value="$til13|$line_title"}
			{assign var=col14 value="$til14|$line_title"}
			{assign var=col15 value="$til15|$line_title"}
			{assign var=col16 value="$til16|$line_title"}
			{assign var=col17 value="$til17|$line_title"}
			{assign var=col18 value="$til18|$line_title"}
			{assign var=col19 value="$til19|$line_title"}
			{assign var=col20 value="$til20|$line_title"}
			{assign var=colgp value="$tilgp|$line_title"}
			{assign var=colper value="$tilper|$line_title"}
			{assign var=coladjgp value="$tiladjgp|$line_title"}
		<th>CP</th>
		<!---------SYSTEM OPENING STOCK DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_os_o_cost,{$rid}" title="{$col1}">{$dept_os_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_os_OUTRIGHT_selling ne 0 or $dept_os_OUTRIGHT_selling ne ''}
		    {assign var=os_OUTRIGHT_gp value=$dept_os_OUTRIGHT_selling-$dept_os_OUTRIGHT_cost}
			{assign var=os_OUTRIGHT_gp value=$os_OUTRIGHT_gp/$dept_os_OUTRIGHT_selling*100}
		{else}
		    {assign var=os_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_os_o_gp,{$rid}" title="{$colgp}" {$background}>{$os_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OPENING STOCK DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_cos_o_cost,{$rid}" title="{$col2}">{$dept_cos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cos_OUTRIGHT_selling ne 0 or $dept_cos_OUTRIGHT_selling ne ''}
		    {assign var=cos_OUTRIGHT_gp value=$dept_cos_OUTRIGHT_selling-$dept_cos_OUTRIGHT_cost}
			{assign var=cos_OUTRIGHT_gp value=$cos_OUTRIGHT_gp/$dept_cos_OUTRIGHT_selling*100}
		{else}
		    {assign var=cos_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_cos_o_gp,{$rid}" title="{$colgp}" {$background}>{$cos_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK TAKE VARIANCE DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_stv_o_cost,{$rid}" title="{$col3}">{$dept_stv_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_stv_OUTRIGHT_selling ne 0 or $dept_stv_OUTRIGHT_selling ne ''}
		    {assign var=stv_OUTRIGHT_gp value=$dept_stv_OUTRIGHT_selling-$dept_stv_OUTRIGHT_cost}
			{assign var=stv_OUTRIGHT_gp value=$stv_OUTRIGHT_gp/$dept_stv_OUTRIGHT_selling*100}
		{else}
		    {assign var=stv_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_stv_o_gp,{$rid}" title="{$colgp}" {$background}>{$stv_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK CHECK DEPT OUTRIGHT GP---------->
		<td class="r" title="{$col4}">{$dept_stc_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_stc_OUTRIGHT_selling ne 0 or $dept_stc_OUTRIGHT_selling ne ''}
		    {assign var=stc_OUTRIGHT_gp value=$dept_stc_OUTRIGHT_selling-$dept_stc_OUTRIGHT_cost}
			{assign var=stc_OUTRIGHT_gp value=$stc_OUTRIGHT_gp/$dept_stc_OUTRIGHT_selling*100}
		{else}
		    {assign var=stc_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" title="{$colgp}" {$background}>{$stc_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------CSA OPENING VARIANCE DEPT OUTRIGHT GP---------->
		<td class="r" title="{$col5}">{$dept_cov_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cov_OUTRIGHT_selling ne 0 or $dept_cov_OUTRIGHT_selling ne ''}
		    {assign var=cov_OUTRIGHT_gp value=$dept_cov_OUTRIGHT_selling-$dept_cov_OUTRIGHT_cost}
			{assign var=cov_OUTRIGHT_gp value=$cov_OUTRIGHT_gp/$dept_cov_OUTRIGHT_selling*100}
		{else}
		    {assign var=cov_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" title="{$colgp}" {$background}>{$cov_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL OPENING STOCK TAKE DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_aos_o_cost,{$rid}" title="{$col6}">{$dept_aos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_aos_OUTRIGHT_selling ne 0 or $dept_aos_OUTRIGHT_selling ne ''}
		    {assign var=aos_OUTRIGHT_gp value=$dept_aos_OUTRIGHT_selling-$dept_aos_OUTRIGHT_cost}
			{assign var=aos_OUTRIGHT_gp value=$aos_OUTRIGHT_gp/$dept_aos_OUTRIGHT_selling*100}
		{else}
		    {assign var=aos_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_aos_o_gp,{$rid}" title="{$colgp}" {$background}>{$aos_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK RECEIVE DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_sr_o_cost,{$rid}" title="{$col7}">{$dept_sr_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_sr_OUTRIGHT_selling ne 0 or $dept_sr_OUTRIGHT_selling ne ''}
		    {assign var=sr_OUTRIGHT_gp value=$dept_sr_OUTRIGHT_selling-$dept_sr_OUTRIGHT_cost}
			{assign var=sr_OUTRIGHT_gp value=$sr_OUTRIGHT_gp/$dept_sr_OUTRIGHT_selling*100}
		{else}
		    {assign var=sr_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_sr_o_gp,{$rid}" title="{$colgp}" {$background}>{$sr_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------GRN PENDING DEPT OUTRIGHT GP---------->
		<td class="r dept_grn_o_cost" id="dept_grn_o_cost,{$rid}" title="{$col8}">{$dept_grn_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_grn_OUTRIGHT_selling ne 0 or $dept_grn_OUTRIGHT_selling ne ''}
		    {assign var=grn_OUTRIGHT_gp value=$dept_grn_OUTRIGHT_selling-$dept_grn_OUTRIGHT_cost}
			{assign var=grn_OUTRIGHT_gp value=$grn_OUTRIGHT_gp/$dept_grn_OUTRIGHT_selling*100}
		{else}
		    {assign var=grn_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_grn_o_gp,{$rid}" title="{$colgp}" {$background}>{$grn_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ADJUSTMENT DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_adj_o_cost,{$rid}" title="{$col9}">{$dept_adj_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_adj_OUTRIGHT_selling ne 0 or $dept_adj_OUTRIGHT_selling ne ''}
		    {assign var=adj_OUTRIGHT_gp value=$dept_adj_OUTRIGHT_selling-$dept_adj_OUTRIGHT_cost}
			{assign var=adj_OUTRIGHT_gp value=$adj_OUTRIGHT_gp/$dept_adj_OUTRIGHT_selling*100}
		{else}
		    {assign var=adj_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_adj_o_gp,{$rid}" title="{$colgp}" {$background}>{$adj_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL STOCK DEPT OUTRIGHT GP---------->
		<td class="r dept_as_o_cost" id="dept_as_o_cost,{$rid}" title="{$col10}">{$dept_as_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_as_OUTRIGHT_selling ne 0 or $dept_as_OUTRIGHT_selling ne ''}
		    {assign var=as_OUTRIGHT_gp value=$dept_as_OUTRIGHT_selling-$dept_as_OUTRIGHT_cost}
			{assign var=as_OUTRIGHT_gp value=$as_OUTRIGHT_gp/$dept_as_OUTRIGHT_selling*100}
		{else}
		    {assign var=as_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_as_o_gp,{$rid}" title="{$colgp}" {$background}>{$as_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------RETURN STOCK DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_rs_o_cost,{$rid}" title="{$col11}">{$dept_rs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_rs_OUTRIGHT_selling ne 0 or $dept_rs_OUTRIGHT_selling ne ''}
		    {assign var=rs_OUTRIGHT_gp value=$dept_rs_OUTRIGHT_selling-$dept_rs_OUTRIGHT_cost}
			{assign var=rs_OUTRIGHT_gp value=$rs_OUTRIGHT_gp/$dept_rs_OUTRIGHT_selling*100}
		{else}
		    {assign var=rs_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_rs_o_gp,{$rid}" title="{$colgp}" {$background}>{$rs_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------TRANSFER STOCK DEPT OUTRIGHT GP---------->
{*		<td class="r" id="dept_ts_o_cost,{$rid}">{$dept_ts_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_ts_OUTRIGHT_selling ne 0 or $dept_ts_OUTRIGHT_selling ne ''}
		    {assign var=ts_OUTRIGHT_gp value=$dept_ts_OUTRIGHT_selling-$dept_ts_OUTRIGHT_cost}
			{assign var=ts_OUTRIGHT_gp value=$ts_OUTRIGHT_gp/$dept_ts_OUTRIGHT_selling*100}
		{else}
		    {assign var=ts_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_ts_o_gp,{$rid}" title="{$colgp}" {$background}>{$ts_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>  *}

		<!---------IDT DEPT OUTRIGHT GP---------->
		<td class="r dept_idt_o_cost" id="dept_idt_o_cost,{$rid}" title="{$col12}">{$dept_idt_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_idt_OUTRIGHT_selling ne 0 or $dept_idt_OUTRIGHT_selling ne ''}
		    {assign var=idt_OUTRIGHT_gp value=$dept_idt_OUTRIGHT_selling-$dept_idt_OUTRIGHT_cost}
			{assign var=idt_OUTRIGHT_gp value=$idt_OUTRIGHT_gp/$dept_idt_OUTRIGHT_selling*100}
		{else}
		    {assign var=idt_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_idt_o_gp,{$rid}" title="{$colgp}" {$background}>{$idt_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PROMOTION AMOUNT DEPT OUTRIGHT GP---------->
		<td rowspan=2 class="r" id="dept_pa_o_selling,{$rid}" title="{$col13}">{$dept_pa_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_OUTRIGHT_selling ne 0 or $dept_acs_OUTRIGHT_selling ne ''}
			{assign var=pa_OUTRIGHT_gp value=$dept_pa_OUTRIGHT_selling/$dept_acs_OUTRIGHT_selling*100}
		{else}
		    {assign var=pa_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pa_o_gp,{$rid}" title="{$colper}" {$background}>{$pa_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PRICE CHANGE AMOUNT DEPT OUTRIGHT GP---------->
		<td rowspan=2 class="r" id="dept_pca_o_selling,{$rid}" title="{$col14}">{$dept_pca_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_OUTRIGHT_selling ne 0 or $dept_acs_OUTRIGHT_selling ne ''}
			{assign var=pca_OUTRIGHT_gp value=$dept_pca_OUTRIGHT_selling/$dept_acs_OUTRIGHT_selling*100}
		{else}
		    {assign var=pca_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pca_o_gp,{$rid}" title="{$colper}" {$background}>{$pca_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL SALES DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_acs_o_cost,{$rid}" title="{$col15}">{$dept_acs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_OUTRIGHT_selling ne 0 or $dept_acs_OUTRIGHT_selling ne ''}
		    {assign var=acs_OUTRIGHT_gp value=$dept_acs_OUTRIGHT_selling-$dept_acs_OUTRIGHT_cost}
			{assign var=acs_OUTRIGHT_gp value=$acs_OUTRIGHT_gp/$dept_acs_OUTRIGHT_selling*100}
		{else}
		    {assign var=acs_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_acs_o_gp,{$rid}" title="{$colgp}" {$background}>{$acs_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------REBATE DEPT OUTRIGHT GP---------->
		{*<td rowspan=2 class="r hide dept_r_o_selling" id="dept_r_o_selling,{$rid}">{$dept_r_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>*}
		{*
		{if $dept_acs_OUTRIGHT_selling ne 0 or $dept_acs_OUTRIGHT_selling ne ''}
		    {assign var=r_OUTRIGHT_gp value=$dept_acs_OUTRIGHT_selling-$dept_acs_OUTRIGHT_cost+$dept_r_OUTRIGHT_selling}
			{assign var=r_OUTRIGHT_gp value=$r_OUTRIGHT_gp/$dept_acs_OUTRIGHT_selling*100}
		{else}
		    {assign var=r_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_r_o_gp,{$rid}" title="{$coladjgp}" {$background}>{$r_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>*}

		<!---------CLOSING STOCK DEPT OUTRIGHT GP---------->
		<td class="r" id="dept_cs_o_cost,{$rid}" title="{$col16}">{$dept_cs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cs_OUTRIGHT_selling ne 0 or $dept_cs_OUTRIGHT_selling ne ''}
		    {assign var=cs_OUTRIGHT_gp value=$dept_cs_OUTRIGHT_selling-$dept_cs_OUTRIGHT_cost}
			{assign var=cs_OUTRIGHT_gp value=$cs_OUTRIGHT_gp/$dept_cs_OUTRIGHT_selling*100}
		{else}
		    {assign var=cs_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_cs_o_gp,{$rid}" title="{$colgp}" {$background}>{$cs_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OTHER INCOME DEPT OUTRIGHT ---------->
		<td rowspan=2 class="r dept_oi_o_selling" id="dept_oi_o_selling,{$rid}" title="{$col17}">{$dept_oi_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<!---------PROFIT MARGIN DEPT OUTRIGHT ---------->
		<td rowspan=2 class="r" id="dept_pm_o_selling,{$rid}" title="{$col18}">{$dept_pm_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_OUTRIGHT_selling ne 0 or $dept_acs_OUTRIGHT_selling ne ''}
			{assign var=pm_OUTRIGHT_gp value=$dept_pm_OUTRIGHT_selling/$dept_acs_OUTRIGHT_selling*100}
		{else}
		    {assign var=pm_OUTRIGHT_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pm_o_gp,{$rid}" title="{$coladjgp}" {$background}>{$pm_OUTRIGHT_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------AVERAGE STOCK DEPT OUTRIGHT ---------->

		<td rowspan=2 class="r" id="dept_av_o_selling,{$rid}" title="{$col19}">{$dept_av_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<!---------TURN DAYS DEPT OUTRIGHT ---------->
		{if $dept_acs_OUTRIGHT_cost ne 0 or $dept_acs_OUTRIGHT_cost ne ''}
			{assign var=dept_td_OUTRIGHT_selling value=$dept_av_OUTRIGHT_selling/$dept_acs_OUTRIGHT_cost*$d}
		{else}
		    {assign var=dept_td_OUTRIGHT_selling value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_td_o_selling,{$rid}" title="{$col20}">{$dept_td_OUTRIGHT_selling|number_format:0|ifzero:'-'}</td>

	</tr>
	<tr class="dept_outright">
		<th>SP</th>
		<td class="r" id="dept_os_o_selling,{$rid}" title="{$col1}">{$dept_os_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_cos_o_selling,{$rid}" title="{$col2}">{$dept_cos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_stv_o_selling,{$rid}" title="{$col3}">{$dept_stv_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_stv_o_selling,{$rid}" title="{$col4}">{$dept_stc_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_stv_o_selling,{$rid}" title="{$col5}">{$dept_cov_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_aos_o_selling,{$rid}" title="{$col6}">{$dept_aos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_sr_o_selling,{$rid}" title="{$col7}">{$dept_sr_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r dept_grn_o_selling" id="dept_grn_o_selling,{$rid}" title="{$col8}">{$dept_grn_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_adj_o_selling,{$rid}" title="{$col9}">{$dept_adj_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_as_o_selling,{$rid}" title="{$col10}">{$dept_as_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_rs_o_selling,{$rid}" title="{$col11}">{$dept_rs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

{*		<td class="r" id="dept_ts_o_selling,{$rid}">{$dept_ts_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td> *}
		<td class="r dept_idt_o_selling" id="dept_idt_o_selling,{$rid}" title="{$col12}">{$dept_idt_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_acs_o_selling,{$rid}" title="{$col15}">{$dept_acs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

		<td class="r" id="dept_cs_o_selling,{$rid}" title="{$col16}">{$dept_cs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	</tr>
	</tbody>
	
{*
		<span class="hide" id="r_dept_os_o_cost,{$rid}">{$dept_os_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_cos_o_cost,{$rid}">{$dept_cos_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_stv_o_cost,{$rid}">{$dept_stv_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_stv_o_cost,{$rid}">{$dept_stc_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_stv_o_cost,{$rid}">{$dept_cov_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_aos_o_cost,{$rid}">{$dept_aos_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_sr_o_cost,{$rid}">{$dept_sr_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_adj_o_cost,{$rid}">{$dept_adj_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_as_o_cost,{$rid}">{$dept_as_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_rs_o_cost,{$rid}">{$dept_rs_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_pa_o_selling,{$rid}">{$dept_pa_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_pca_o_selling,{$rid}">{$dept_pca_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_acs_o_cost,{$rid}">{$dept_acs_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_cs_o_cost,{$rid}">{$dept_cs_OUTRIGHT_cost}</span>
		<span class="hide" id="r_dept_os_o_selling,{$rid}">{$dept_os_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_cos_o_selling,{$rid}">{$dept_cos_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_stv_o_selling,{$rid}">{$dept_stv_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_aos_o_selling,{$rid}">{$dept_aos_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_sr_o_selling,{$rid}">{$dept_sr_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_adj_o_selling,{$rid}">{$dept_adj_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_as_o_selling,{$rid}">{$dept_as_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_rs_o_selling,{$rid}">{$dept_rs_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_acs_o_selling,{$rid}">{$dept_acs_OUTRIGHT_selling}</span>
		<span class="hide" id="r_dept_cs_o_selling,{$rid}">{$dept_cs_OUTRIGHT_selling}</span>
*}
{* /if *}

{if $dept_pa_CONSIGN_selling or $dept_acs_CONSIGN_cost or $dept_oi_CONSIGN_selling or $dept_pm_CONSIGN_selling or
	$dept_acs_CONSIGN_selling}

	<!----------------------------TOTAL EACH DEPARTMENT CONSIGN----------------------------->
	<tbody class="storewide_consign">
	<tr class="dept_consign">
		<th rowspan=2>{$line_desc}<br>(Consignment)</th>
		<th>CP</th>
		<td class="r" title="{$col1}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col2}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col3}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col4}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col5}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col6}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col7}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col8}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col9}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col10}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>
		<td class="r" title="{$col11}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------IDT DEPT CONSIGN GP---------->
		<td class="r dept_idt_c_cost" id="dept_idt_c_cost,{$rid}" title="{$col12}">&nbsp;</td>

		{if $dept_idt_CONSIGN_selling ne 0 or $dept_idt_CONSIGN_selling ne ''}
		    {assign var=idt_CONSIGN_gp value=$dept_idt_CONSIGN_selling-$dept_idt_CONSIGN_cost}
			{assign var=idt_CONSIGN_gp value=$idt_CONSIGN_gp/$dept_idt_CONSIGN_selling*100}
		{else}
		    {assign var=idt_CONSIGN_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_idt_c_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------PROMOTION AMOUNT DEPT CONSIGN GP---------->
		<td rowspan=2 class="r" id="dept_pa_c_selling,{$rid}" title="{$col13}">{$dept_pa_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_CONSIGN_selling ne 0 or $dept_acs_CONSIGN_selling ne ''}
			{assign var=pa_CONSIGN_gp value=$dept_pa_CONSIGN_selling/$dept_acs_CONSIGN_selling*100}
		{else}
		    {assign var=pa_CONSIGN_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pa_c_gp,{$rid}" title="{$colper}" {$background}>{$pa_CONSIGN_gp|number_format:2|ifzero:'-':$percent}</td>

		<td rowspan=2 class="r" title="{$col14}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" title="{$colper}" {$background}>&nbsp;</td>

		<!---------ACTUAL SALES DEPT CONSIGN GP---------->
		<td class="r dept_acs_c_cost" id="dept_acs_c_cost,{$rid}" title="{$col15}">{$dept_acs_CONSIGN_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_CONSIGN_selling ne 0 or $dept_acs_CONSIGN_selling ne ''}
		    {assign var=acs_CONSIGN_gp value=$dept_acs_CONSIGN_selling-$dept_acs_CONSIGN_cost}
			{assign var=acs_CONSIGN_gp value=$acs_CONSIGN_gp/$dept_acs_CONSIGN_selling*100}
		{else}
		    {assign var=acs_CONSIGN_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_acs_c_gp,{$rid}" title="{$colgp}" {$background}>{$acs_CONSIGN_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------REBATE DEPT CONSIGN GP---------->
		{*<td rowspan=2 class="r hide dept_r_c_selling" id="dept_r_c_selling,{$rid}">{$dept_r_CONSIGN_selling|number_format:2|ifzero:'-'}</td>*}
		{*
		{if $dept_acs_CONSIGN_selling ne 0 or $dept_acs_CONSIGN_selling ne ''}
		    {assign var=r_CONSIGN_gp value=$dept_acs_CONSIGN_selling-$dept_acs_CONSIGN_cost+$dept_r_CONSIGN_selling}
			{assign var=r_CONSIGN_gp value=$r_CONSIGN_gp/$dept_acs_CONSIGN_selling*100}
		{else}
		    {assign var=r_CONSIGN_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_r_c_gp,{$rid}" title="{$coladjgp}" {$background}>{$r_CONSIGN_gp|number_format:2|ifzero:'-':$percent}</td>*}

		<td class="r" title="{$col16}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------OTHER INCOME DEPT CONSIGN ---------->
		<td rowspan=2 class="r dept_oi_c_selling" id="dept_oi_c_selling,{$rid}" title="{$col17}">{$dept_oi_CONSIGN_selling|number_format:2|ifzero:'-':$percent}</td>

		<!---------PROFIT MARGIN DEPT CONSIGN ---------->
		<td rowspan=2 class="r" id="dept_pm_c_selling,{$rid}" title="{$col18}">{$dept_pm_CONSIGN_selling|number_format:2|ifzero:'-':$percent}</td>

		{if $dept_acs_CONSIGN_selling ne 0 or $dept_acs_CONSIGN_selling ne ''}
			{assign var=pm_CONSIGN_gp value=$dept_pm_CONSIGN_selling/$dept_acs_CONSIGN_selling*100}
		{else}
		    {assign var=pm_CONSIGN_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pm_c_gp,{$rid}" title="{$coladjgp}" {$background}>{$pm_CONSIGN_gp|number_format:2|ifzero:'-':$percent}</td>

		<td rowspan=2 class="r" title="{$col19}">&nbsp;</td>
		<td rowspan=2 class="r" title="{$col20}">&nbsp;</td>
	</tr>
	<tr class="dept_consign">
		<th>SP</th>
		<td class="r" title="{$col1}">&nbsp;</td>
		<td class="r" title="{$col2}">&nbsp;</td>
		<td class="r" title="{$col3}">&nbsp;</td>
		<td class="r" title="{$col4}">&nbsp;</td>
		<td class="r" title="{$col5}">&nbsp;</td>
		<td class="r" title="{$col6}">&nbsp;</td>
		<td class="r" title="{$col7}">&nbsp;</td>
		<td class="r" title="{$col8}">&nbsp;</td>
		<td class="r" title="{$col9}">&nbsp;</td>
		<td class="r" title="{$col10}">&nbsp;</td>
		<td class="r" title="{$col11}">&nbsp;</td>
		<td class="r dept_idt_c_selling" id="dept_idt_c_selling,{$rid}" title="{$col12}">{$dept_idt_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
		<td class="r dept_acs_c_selling" id="dept_acs_c_selling,{$rid}" title="{$col15}">{$dept_acs_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
		<td class="r"  title="{$col16}">&nbsp;</td>
	</tr>

{*
		<span class="hide" id="r_dept_acs_c_selling,{$rid}">{$dept_acs_CONSIGN_selling}</span>
*}

	</tbody>
{/if}
	<!----------------------------TOTAL EACH DEPARTMENT FRESH----------------------------->
{if $fm.$rid}
	{if $dept_os_FRESH_cost or $dept_os_FRESH_selling or $dept_sr_FRESH_cost or $dept_sr_FRESH_selling or
		$dept_adj_FRESH_cost or $dept_adj_FRESH_selling or $dept_stv_FRESH_cost or $dept_stv_FRESH_selling or
		$dept_rs_FRESH_cost or $dept_rs_FRESH_selling or $dept_ts_FRESH_cost or $dept_ts_FRESH_selling or
		$dept_pa_FRESH_selling or $dept_acs_FRESH_cost or $dept_acs_FRESH_selling}

	<tr class="dept_fresh">
		<th rowspan=2>{$root.r_descrip}<br>(Fresh Market)</th>
		<th>CP</th>
		<!---------OPENING STOCK DEPT FRESH GP---------->
		<td class="r">{$dept_os_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_os_FRESH_selling ne 0 or $dept_os_FRESH_selling ne ''}
		    {assign var=os_FRESH_gp value=$dept_os_FRESH_selling-$dept_os_FRESH_cost}
			{assign var=os_FRESH_gp value=$os_FRESH_gp/$dept_os_FRESH_selling*100}
		{else}
		    {assign var=os_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$os_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK TAKE VARIANCE DEPT FRESH GP---------->
		<td class="r">{$dept_stv_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_stv_FRESH_selling ne 0 or $dept_stv_FRESH_selling ne ''}
		    {assign var=stv_FRESH_gp value=$dept_stv_FRESH_selling-$dept_stv_FRESH_cost}
			{assign var=stv_FRESH_gp value=$stv_FRESH_gp/$dept_stv_FRESH_selling*100}
		{else}
		    {assign var=stv_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$stv_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK CHECK DEPT FRESH GP---------->
		<td class="r">{$dept_stc_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_stc_FRESH_selling ne 0 or $dept_stc_FRESH_selling ne ''}
		    {assign var=stc_FRESH_gp value=$dept_stc_FRESH_selling-$dept_stc_FRESH_cost}
			{assign var=stc_FRESH_gp value=$stc_FRESH_gp/$dept_stc_FRESH_selling*100}
		{else}
		    {assign var=stv_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$stc_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------CSA OPENING VARIANCE DEPT FRESH GP---------->
		<td class="r">{$dept_cov_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cov_FRESH_selling ne 0 or $dept_cov_FRESH_selling ne ''}
		    {assign var=cov_FRESH_gp value=$dept_cov_FRESH_selling-$dept_cov_FRESH_cost}
			{assign var=cov_FRESH_gp value=$cov_FRESH_gp/$dept_cov_FRESH_selling*100}
		{else}
		    {assign var=cov_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$cov_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK RECEIVE DEPT FRESH GP---------->
		<td class="r">{$dept_sr_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_sr_FRESH_selling ne 0 or $dept_sr_FRESH_selling ne ''}
		    {assign var=sr_FRESH_gp value=$dept_sr_FRESH_selling-$dept_sr_FRESH_cost}
			{assign var=sr_FRESH_gp value=$sr_FRESH_gp/$dept_sr_FRESH_selling*100}
		{else}
		    {assign var=sr_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$sr_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------GRN PENDING DEPT FRESH GP---------->
		<td class="r">{$dept_grn_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_grn_FRESH_selling ne 0 or $dept_grn_FRESH_selling ne ''}
		    {assign var=grn_FRESH_gp value=$dept_grn_FRESH_selling-$dept_grn_FRESH_cost}
			{assign var=grn_FRESH_gp value=$grn_FRESH_gp/$dept_grn_FRESH_selling*100}
		{else}
		    {assign var=grn_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$grn_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ADJUSTMENT DEPT FRESH GP---------->
		<td class="r">{$dept_adj_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_adj_FRESH_selling ne 0 or $dept_adj_FRESH_selling ne ''}
		    {assign var=adj_FRESH_gp value=$dept_adj_FRESH_selling-$dept_adj_FRESH_cost}
			{assign var=adj_FRESH_gp value=$adj_FRESH_gp/$dept_adj_FRESH_selling*100}
		{else}
		    {assign var=adj_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$adj_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL STOCK DEPT FRESH GP---------->
		<td class="r">{$dept_as_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_as_FRESH_selling ne 0 or $dept_as_FRESH_selling ne ''}
		    {assign var=as_FRESH_gp value=$dept_as_FRESH_selling-$dept_as_FRESH_cost}
			{assign var=as_FRESH_gp value=$as_FRESH_gp/$dept_as_FRESH_selling*100}
		{else}
		    {assign var=as_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$as_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------RETURN STOCK DEPT FRESH GP---------->
		<td class="r">{$dept_rs_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_rs_FRESH_selling ne 0 or $dept_rs_FRESH_selling ne ''}
		    {assign var=rs_FRESH_gp value=$dept_rs_FRESH_selling-$dept_rs_FRESH_cost}
			{assign var=rs_FRESH_gp value=$rs_FRESH_gp/$dept_rs_FRESH_selling*100}
		{else}
		    {assign var=rs_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$rs_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------TRANSFER STOCK DEPT FRESH GP---------->
{*		<td class="r">{$dept_ts_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_ts_FRESH_selling ne 0 or $dept_ts_FRESH_selling ne ''}
		    {assign var=ts_FRESH_gp value=$dept_ts_FRESH_selling-$dept_ts_FRESH_cost}
			{assign var=ts_FRESH_gp value=$ts_FRESH_gp/$dept_ts_FRESH_selling*100}
		{else}
		    {assign var=ts_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$ts_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>  *}

		<!---------PROMOTION AMOUNT DEPT FRESH GP---------->
		<td rowspan=2 class="r">{$dept_pa_FRESH_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
			{assign var=pa_FRESH_gp value=$dept_pa_FRESH_selling/$dept_acs_FRESH_selling*100}
		{else}
		    {assign var=pa_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$pa_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL SALES DEPT FRESH GP---------->
		<td class="r">{$dept_acs_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
		    {assign var=acs_FRESH_gp value=$dept_acs_FRESH_selling-$dept_acs_FRESH_cost}
			{assign var=acs_FRESH_gp value=$acs_FRESH_gp/$dept_acs_FRESH_selling*100}
		{else}
		    {assign var=acs_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$acs_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------REBATE DEPT FRESH GP---------->
		{*<td rowspan=2 class="r hide">{$dept_r_FRESH_selling|number_format:2|ifzero:'-'}</td>*}
		{*
		{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
		    {assign var=r_FRESH_gp value=$dept_acs_FRESH_selling-$dept_acs_FRESH_cost+$dept_r_FRESH_selling}
			{assign var=r_FRESH_gp value=$r_FRESH_gp/$dept_acs_FRESH_selling*100}
		{else}
		    {assign var=r_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$r_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>*}

		<!---------CLOSING STOCK DEPT FRESH GP---------->
		<td class="r">{$dept_cs_FRESH_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cs_FRESH_selling ne 0 or $dept_cs_FRESH_selling ne ''}
		    {assign var=cs_FRESH_gp value=$dept_cs_FRESH_selling-$dept_cs_FRESH_cost}
			{assign var=cs_FRESH_gp value=$cs_FRESH_gp/$dept_cs_FRESH_selling*100}
		{else}
		    {assign var=cs_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$cs_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OTHER INCOME DEPT FRESH ---------->
		<td rowspan=2 class="r">{$dept_oi_FRESH_selling|number_format:2|ifzero:'-'}</td>

		<!---------PROFIT MARGIN DEPT FRESH ---------->
		<td rowspan=2 class="r">{$dept_pm_FRESH_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
			{assign var=pm_FRESH_gp value=$dept_pm_FRESH_selling/$dept_acs_FRESH_selling*100}
		{else}
		    {assign var=pm_FRESH_gp value=0}
		{/if}
		<td rowspan=2 class="r" {$background}>{$pm_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------AVERAGE STOCK DEPT FRESH ---------->
		{assign var=dept_av_FRESH_selling value=$dept_aos_FRESH_cost+$dept_cs_FRESH_costs}
		{assign var=dept_av_FRESH_selling value=$dept_av_FRESH_selling/2}

		<td rowspan=2 class="r">{$dept_av_FRESH_selling|number_format:2|ifzero:'-'}</td>

		<!---------TURN DAYS DEPT FRESH ---------->
		{if $dept_acs_FRESH_cost ne 0 or $dept_acs_FRESH_cost ne ''}
			{assign var=dept_td_FRESH_selling value=$dept_av_FRESH_selling/$dept_acs_FRESH_cost*$d}
		{else}
		    {assign var=dept_td_FRESH_selling value=0}
		{/if}
		<td rowspan=2 class="r">{$dept_td_FRESH_selling|number_format:0|ifzero:'-'}</td>

	</tr>
	<tr class="dept_fresh">
		<th>SP</th>
		<td class="r">{$dept_os_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_sr_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_stv_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_stc_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_cov_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_grn_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_adj_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_as_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_rs_FRESH_selling|number_format:2|ifzero:'-'}</td>
{*		<td class="r">{$dept_ts_FRESH_selling|number_format:2|ifzero:'-'}</td>  *}
		<td class="r">{$dept_acs_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td class="r">{$dept_cs_FRESH_selling|number_format:2|ifzero:'-'}</td>
	</tr>
	{/if}
{/if}

{* if $dept_os_total_cost or $dept_os_total_selling or $dept_sr_total_cost or $dept_sr_total_selling or $dept_grn_total_cost or
		$dept_grn_total_selling or $dept_adj_total_cost or $dept_adj_total_selling or $dept_stv_total_cost or
		$dept_stv_total_selling or $dept_rs_total_cost or $dept_rs_total_selling or $dept_ts_total_cost or
		$dept_ts_total_selling or $dept_pa_total_selling or $dept_acs_total_cost or $dept_acs_total_selling *}

	<tr class="dept_total">
		<th rowspan=2>
			{$line_desc}<br>(Total)
		</th>
		<th>CP</th>
		<!---------SYSTEM OPENING STOCK DEPT TOTAL GP---------->
		<td class="r" id="dept_os_t_cost,{$rid}" title="{$col1}">{$dept_os_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_os_total_selling ne 0 or $dept_os_total_selling ne ''}
		    {assign var=os_dept_gp value=$dept_os_total_selling-$dept_os_total_cost}
			{assign var=os_dept_gp value=$os_dept_gp/$dept_os_total_selling*100}
		{else}
		    {assign var=os_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_os_t_gp,{$rid}" title="{$colgp}" {$background}>{$os_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OPENING STOCK DEPT TOTAL GP---------->
		<td class="r" id="dept_cos_t_cost,{$rid}" title="{$col2}">{$dept_cos_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cos_total_selling ne 0 or $dept_cos_total_selling ne ''}
		    {assign var=cos_dept_gp value=$dept_cos_total_selling-$dept_cos_total_cost}
			{assign var=cos_dept_gp value=$cos_dept_gp/$dept_cos_total_selling*100}
		{else}
		    {assign var=cos_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_cos_t_gp,{$rid}" title="{$colgp}" {$background}>{$cos_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK TAKE VARIANCE DEPT TOTAL GP---------->
		<td class="r" id="dept_stv_t_cost,{$rid}" title="{$col3}">{$dept_stv_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_stv_total_selling ne 0 or $dept_stv_total_selling ne ''}
		    {assign var=stv_dept_gp value=$dept_stv_total_selling-$dept_stv_total_cost}
			{assign var=stv_dept_gp value=$stv_dept_gp/$dept_stv_total_selling*100}
		{else}
		    {assign var=stv_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_stv_t_gp,{$rid}" title="{$colgp}" {$background}>{$stv_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK CHECK DEPT TOTAL GP---------->
		<td class="r" title="{$col4}">{$dept_stc_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_stc_total_selling ne 0 or $dept_stc_total_selling ne ''}
		    {assign var=stc_dept_gp value=$dept_stc_total_selling-$dept_stc_total_cost}
			{assign var=stc_dept_gp value=$stc_dept_gp/$dept_stc_total_selling*100}
		{else}
		    {assign var=stc_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" title="{$colgp}" {$background}>{$stc_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------CSA OPENING VARIANCE DEPT TOTAL GP---------->
		<td class="r" title="{$col5}">{$dept_cov_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cov_total_selling ne 0 or $dept_cov_total_selling ne ''}
		    {assign var=cov_dept_gp value=$dept_cov_total_selling-$dept_cov_total_cost}
			{assign var=cov_dept_gp value=$cov_dept_gp/$dept_cov_total_selling*100}
		{else}
		    {assign var=cov_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" title="{$colgp}" {$background}>{$cov_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL OPENING STOCK DEPT TOTAL GP---------->
		<td class="r" id="dept_aos_t_cost,{$rid}" title="{$col6}">{$dept_aos_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_aos_total_selling ne 0 or $dept_aos_total_selling ne ''}
		    {assign var=aos_dept_gp value=$dept_aos_total_selling-$dept_aos_total_cost}
			{assign var=aos_dept_gp value=$aos_dept_gp/$dept_aos_total_selling*100}
		{else}
		    {assign var=aos_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_aos_t_gp,{$rid}" title="{$colgp}" {$background}>{$aos_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------STOCK RECEIVE DEPT TOTAL GP---------->
		<td class="r" id="dept_sr_t_cost,{$rid}" title="{$col7}">{$dept_sr_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_sr_total_selling ne 0 or $dept_sr_total_selling ne ''}
		    {assign var=sr_dept_gp value=$dept_sr_total_selling-$dept_sr_total_cost}
			{assign var=sr_dept_gp value=$sr_dept_gp/$dept_sr_total_selling*100}
		{else}
		    {assign var=sr_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_sr_t_gp,{$rid}" title="{$colgp}" {$background}>{$sr_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------GRN PENDING DEPT TOTAL GP---------->
		<td class="r" id="dept_grn_t_cost,{$rid}" title="{$col8}">{$dept_grn_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_grn_total_selling ne 0 or $dept_grn_total_selling ne ''}
		    {assign var=grn_dept_gp value=$dept_grn_total_selling-$dept_grn_total_cost}
			{assign var=grn_dept_gp value=$grn_dept_gp/$dept_grn_total_selling*100}
		{else}
		    {assign var=grn_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_grn_t_gp,{$rid}" title="{$colgp}" {$background}>{$grn_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ADJUSTMENT DEPT TOTAL GP---------->
		<td class="r" id="dept_adj_t_cost,{$rid}" title="{$col9}">{$dept_adj_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_adj_total_selling ne 0 or $dept_adj_total_selling ne ''}
		    {assign var=adj_dept_gp value=$dept_adj_total_selling-$dept_adj_total_cost}
			{assign var=adj_dept_gp value=$adj_dept_gp/$dept_adj_total_selling*100}
		{else}
		    {assign var=adj_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_adj_t_gp,{$rid}" title="{$colgp}" {$background}>{$adj_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL STOCK DEPT TOTAL GP---------->
		{assign var=dept_as_total_cost value=$dept_as_OUTRIGHT_cost+$dept_as_CONSIGN_cost+$dept_as_FRESH_cost}
		{assign var=dept_as_total_selling value=$dept_as_OUTRIGHT_selling+$dept_as_CONSIGN_selling+$dept_as_FRESH_selling}
		<td class="r dept_as_t_cost" id="dept_as_t_cost,{$rid}" title="{$col10}">{$dept_as_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_as_total_selling ne 0 or $dept_as_total_selling ne ''}
		    {assign var=as_dept_gp value=$dept_as_total_selling-$dept_as_total_cost}
			{assign var=as_dept_gp value=$as_dept_gp/$dept_as_total_selling*100}
		{else}
		    {assign var=as_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_as_t_gp,{$rid}" title="{$colgp}" {$background}>{$as_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------RETURN STOCK DEPT TOTAL GP---------->
		<td class="r" id="dept_rs_t_cost,{$rid}" title="{$col11}">{$dept_rs_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_rs_total_selling ne 0 or $dept_rs_total_selling ne ''}
		    {assign var=rs_dept_gp value=$dept_rs_total_selling-$dept_rs_total_cost}
			{assign var=rs_dept_gp value=$rs_dept_gp/$dept_rs_total_selling*100}
		{else}
		    {assign var=rs_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_rs_t_gp,{$rid}" title="{$colgp}" {$background}>{$rs_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------TRANSFER STOCK DEPT TOTAL GP---------->
{*		<td class="r" id="dept_ts_t_cost,{$rid}">{$dept_ts_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_ts_total_selling ne 0 or $dept_ts_total_selling ne ''}
		    {assign var=ts_dept_gp value=$dept_ts_total_selling-$dept_ts_total_cost}
			{assign var=ts_dept_gp value=$ts_dept_gp/$dept_ts_total_selling*100}
		{else}
		    {assign var=ts_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_ts_t_gp,{$rid}" title="{$colgp}" {$background}>{$ts_dept_gp|number_format:2|ifzero:'-':$percent}</td>  *}

		<!---------IDT DEPT TOTAL GP---------->
		<td class="r" id="dept_idt_t_cost,{$rid}" title="{$col12}">{$dept_idt_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_idt_total_selling ne 0 or $dept_idt_total_selling ne ''}
		    {assign var=idt_dept_gp value=$dept_idt_total_selling-$dept_idt_total_cost}
			{assign var=idt_dept_gp value=$idt_dept_gp/$dept_idt_total_selling*100}
		{else}
		    {assign var=idt_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_idt_t_gp,{$rid}" title="{$colgp}" {$background}>{$idt_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PROMOTION AMOUNT DEPT TOTAL GP---------->
		<td rowspan=2 class="r" id="dept_pa_t_selling,{$rid}" title="{$col13}">{$dept_pa_total_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_total_selling ne 0 or $dept_acs_total_selling ne ''}
			{assign var=pa_dept_gp value=$dept_pa_total_selling/$dept_acs_total_selling*100}
		{else}
		    {assign var=pa_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pa_t_gp,{$rid}" title="{$colper}" {$background}>{$pa_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PRICE CHANGE AMOUNT DEPT TOTAL GP---------->
		<td rowspan=2 class="r" id="dept_pca_t_selling,{$rid}" title="{$col14}">{$dept_pca_total_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_total_selling ne 0 or $dept_acs_total_selling ne ''}
			{assign var=pca_dept_gp value=$dept_pca_total_selling/$dept_acs_total_selling*100}
		{else}
		    {assign var=pca_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pca_t_gp,{$rid}" title="{$colper}" {$background}>{$pca_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL SALES DEPT TOTAL GP---------->
		<td class="r" id="dept_acs_t_cost,{$rid}" title="{$col15}">{$dept_acs_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_total_selling ne 0 or $dept_acs_total_selling ne ''}
		    {assign var=acs_dept_gp value=$dept_acs_total_selling-$dept_acs_total_cost}
			{assign var=acs_dept_gp value=$acs_dept_gp/$dept_acs_total_selling*100}
		{else}
		    {assign var=acs_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_acs_t_gp,{$rid}" title="{$colgp}" {$background}>{$acs_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------REBATE DEPT TOTAL GP---------->
		{*<td rowspan=2 class="r hide" id="dept_r_t_selling,{$rid}">{$dept_r_total_selling|number_format:2|ifzero:'-'}</td>*}
		{*
		{if $dept_acs_total_selling ne 0 or $dept_acs_total_selling ne ''}
		    {assign var=r_dept_gp value=$dept_acs_total_selling-$dept_acs_total_cost+$dept_r_total_selling}
			{assign var=r_dept_gp value=$r_dept_gp/$dept_acs_total_selling*100}
		{else}
		    {assign var=r_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_r_t_gp,{$rid}" title="{$coladjgp}" {$background}>{$r_dept_gp|number_format:2|ifzero:'-':$percent}</td>*}

		<!---------CLOSING STOCK TOTAL GP---------->
		<td class="r" id="dept_cs_t_cost,{$rid}" title="{$col16}">{$dept_cs_total_cost|number_format:2|ifzero:'-'}</td>

		{if $dept_cs_total_selling ne 0 or $dept_cs_total_selling ne ''}
		    {assign var=cs_dept_gp value=$dept_cs_total_selling-$dept_cs_total_cost}
			{assign var=cs_dept_gp value=$cs_dept_gp/$dept_cs_total_selling*100}
		{else}
		    {assign var=cs_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_cs_t_gp,{$rid}" title="{$colgp}" {$background}>{$cs_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------OTHER INCOME TOTAL---------->
		<td rowspan=2 class="r" id="dept_oi_t_selling,{$rid}" title="{$col17}">{$dept_oi_total_selling|number_format:2|ifzero:'-'}</td>

		<!---------PROFIT MARGIN TOTAL ---------->
		<td rowspan=2 class="r" id="dept_pm_t_selling,{$rid}" title="{$col18}">{$dept_pm_total_selling|number_format:2|ifzero:'-'}</td>

		{if $dept_acs_total_selling ne 0 or $dept_acs_total_selling ne ''}
			{assign var=pm_dept_gp value=$dept_pm_total_selling/$dept_acs_total_selling*100}
		{else}
		    {assign var=pm_dept_gp value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_pm_t_gp,{$rid}" title="{$coladjgp}" {$background}>{$pm_dept_gp|number_format:2|ifzero:'-':$percent}</td>

		<!---------AVERAGE STOCK TOTAL---------->
		<td rowspan=2 class="r" id="dept_av_t_selling,{$rid}" title="{$col19}">{$dept_av_total_selling|number_format:2|ifzero:'-'}</td>

		<!---------TURN DAYS TOTAL---------->
		{if $dept_acs_total_cost ne 0 or $dept_acs_total_cost ne ''}
			{assign var=dept_td_total_selling value=$dept_av_total_selling/$dept_acs_total_cost*$d}
		{else}
		    {assign var=dept_td_total_selling value=0}
		{/if}
		<td rowspan=2 class="r" id="dept_td_t_selling,{$rid}" title="{$col20}">{$dept_td_total_selling|number_format:0|ifzero:'-'}</td>

	</tr>
	<tr class="dept_total">
		<th>SP</th>
		<td class="r" id="dept_os_t_selling,{$rid}" title="{$col1}">{$dept_os_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_cos_t_selling,{$rid}" title="{$col2}">{$dept_cos_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_stv_t_selling,{$rid}" title="{$col3}">{$dept_stv_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" title="{$col4}">{$dept_stc_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" title="{$col5}">{$dept_cov_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_aos_t_selling,{$rid}" title="{$col6}">{$dept_aos_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_sr_t_selling,{$rid}" title="{$col7}">{$dept_sr_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_grn_t_selling,{$rid}" title="{$col8}">{$dept_grn_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_adj_t_selling,{$rid}" title="{$col9}">{$dept_adj_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_as_t_selling,{$rid}" title="{$col10}">{$dept_as_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_rs_t_selling,{$rid}" title="{$col11}">{$dept_rs_total_selling|number_format:2|ifzero:'-'}</td>
{*		<td class="r" id="dept_ts_t_selling,{$rid}" >{$dept_ts_total_selling|number_format:2|ifzero:'-'}</td>    *}
		<td class="r" id="dept_idt_t_selling,{$rid}" title="{$col12}">{$dept_idt_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_acs_t_selling,{$rid}" title="{$col15}">{$dept_acs_total_selling|number_format:2|ifzero:'-'}</td>
		<td class="r" id="dept_cs_t_selling,{$rid}" title="{$col16}">{$dept_cs_total_selling|number_format:2|ifzero:'-'}</td>
	</tr>
{* /if *}
	{include file=report.csa.data.fresh.tpl type=dept}
