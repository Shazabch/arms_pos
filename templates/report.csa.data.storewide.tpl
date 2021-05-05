{*
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
<!---------------------TOTAL STOREWIDE OUTRIGHT------------------------>
{if $storewide_os_OUTRIGHT_cost or $storewide_os_OUTRIGHT_selling or $storewide_sr_OUTRIGHT_cost or
	$storewide_sr_OUTRIGHT_selling or $storewide_grn_OUTRIGHT_cost or $storewide_grn_OUTRIGHT_selling or
	$storewide_adj_OUTRIGHT_cost or $storewide_adj_OUTRIGHT_selling or $storewide_stv_OUTRIGHT_cost or
	$storewide_stv_OUTRIGHT_selling or $storewide_rs_OUTRIGHT_cost or $storewide_rs_OUTRIGHT_selling or
	$storewide_ts_OUTRIGHT_cost or $storewide_ts_OUTRIGHT_selling or $storewide_pa_OUTRIGHT_selling or
	$storewide_acs_OUTRIGHT_cost or $storewide_acs_OUTRIGHT_selling }
<tr class="store_outright">
	<th rowspan=2>STOREWIDE<br>(Outright)</th>
	
	{* set title for each column*}
	{assign var=store_title value="STOREWIDE"}
	{assign var=col1 value="$til1|$store_title"}
	{assign var=col2 value="$til2|$store_title"}
	{assign var=col3 value="$til3|$store_title"}
	{assign var=col4 value="$til4|$store_title"}
	{assign var=col5 value="$til5|$store_title"}
	{assign var=col6 value="$til6|$store_title"}
	{assign var=col7 value="$til7|$store_title"}
	{assign var=col8 value="$til8|$store_title"}
	{assign var=col9 value="$til9|$store_title"}
	{assign var=col10 value="$til10|$store_title"}
	{assign var=col11 value="$til11|$store_title"}
	{assign var=col12 value="$til12|$store_title"}
	{assign var=col13 value="$til13|$store_title"}
	{assign var=col14 value="$til14|$store_title"}
	{assign var=col15 value="$til15|$store_title"}
	{assign var=col16 value="$til16|$store_title"}
	{assign var=col17 value="$til17|$store_title"}
	{assign var=col18 value="$til18|$store_title"}
	{assign var=col19 value="$til19|$store_title"}
	{assign var=col20 value="$til20|$store_title"}
	{assign var=colgp value="$tilgp|$store_title"}
	{assign var=colper value="$tilper|$store_title"}
	{assign var=coladjgp value="$tiladjgp|$store_title"}
	<th>CP</th>
	<!---------SYSTEM OPENING STOCK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_os_o_cost" title="{$col1}">{$storewide_os_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_os_OUTRIGHT_selling ne 0 or $storewide_os_OUTRIGHT_selling ne ''}
	    {assign var=os_storewide_gp value=$storewide_os_OUTRIGHT_selling-$storewide_os_OUTRIGHT_cost}
		{assign var=os_storewide_gp value=$os_storewide_gp/$storewide_os_OUTRIGHT_selling*100}
	{else}
	    {assign var=os_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_os_o_gp" title="{$colgp}" {$background}>{$os_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------OPENING STOCK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_cos_o_cost" title="{$col2}">{$storewide_cos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_cos_OUTRIGHT_selling ne 0 or $storewide_cos_OUTRIGHT_selling ne ''}
	    {assign var=cos_storewide_gp value=$storewide_cos_OUTRIGHT_selling-$storewide_cos_OUTRIGHT_cost}
		{assign var=cos_storewide_gp value=$cos_storewide_gp/$storewide_cos_OUTRIGHT_selling*100}
	{else}
	    {assign var=cos_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_cos_o_gp" title="{$colgp}" {$background}>{$cos_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------STOCK TAKE VARIANCE DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_stv_o_cost" title="{$col3}">{$storewide_stv_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_stv_OUTRIGHT_selling ne 0 or $storewide_stv_OUTRIGHT_selling ne ''}
	    {assign var=stv_storewide_gp value=$storewide_stv_OUTRIGHT_selling-$storewide_stv_OUTRIGHT_cost}
		{assign var=stv_storewide_gp value=$stv_storewide_gp/$storewide_stv_OUTRIGHT_selling*100}
	{else}
	    {assign var=stv_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_stv_o_gp" title="{$colgp}" {$background}>{$stv_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------STOCK CHECK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" title="{$col4}">{$storewide_stc_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_stc_OUTRIGHT_selling ne 0 or $storewide_stc_OUTRIGHT_selling ne ''}
	    {assign var=stc_storewide_gp value=$storewide_stc_OUTRIGHT_selling-$storewide_stc_OUTRIGHT_cost}
		{assign var=stc_storewide_gp value=$stc_storewide_gp/$storewide_stc_OUTRIGHT_selling*100}
	{else}
	    {assign var=stc_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" title="{$colgp}" {$background}>{$stc_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------CSA OPENING VARIANCE DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" title="{$col5}">{$storewide_cov_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_cov_OUTRIGHT_selling ne 0 or $storewide_cov_OUTRIGHT_selling ne ''}
	    {assign var=cov_storewide_gp value=$storewide_cov_OUTRIGHT_selling-$storewide_cov_OUTRIGHT_cost}
		{assign var=cov_storewide_gp value=$cov_storewide_gp/$storewide_cov_OUTRIGHT_selling*100}
	{else}
	    {assign var=cov_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" title="{$colgp}" {$background}>{$cov_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ACTUAL OPENING STOCK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_aos_o_cost" title="{$col6}">{$storewide_aos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_aos_OUTRIGHT_selling ne 0 or $storewide_aos_OUTRIGHT_selling ne ''}
	    {assign var=aos_storewide_gp value=$storewide_aos_OUTRIGHT_selling-$storewide_aos_OUTRIGHT_cost}
		{assign var=aos_storewide_gp value=$aos_storewide_gp/$storewide_aos_OUTRIGHT_selling*100}
	{else}
	    {assign var=aos_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_aos_o_gp" title="{$colgp}" {$background}>{$aos_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------STOCK RECEIVE DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_sr_o_cost" title="{$col7}">{$storewide_sr_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_sr_OUTRIGHT_selling ne 0 or $storewide_sr_OUTRIGHT_selling ne ''}
	    {assign var=sr_storewide_gp value=$storewide_sr_OUTRIGHT_selling-$storewide_sr_OUTRIGHT_cost}
		{assign var=sr_storewide_gp value=$sr_storewide_gp/$storewide_sr_OUTRIGHT_selling*100}
	{else}
	    {assign var=sr_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_sr_o_gp" title="{$colgp}" {$background}>{$sr_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------GRN PENDING DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_grn_o_cost" title="{$col8}">{$storewide_grn_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_grn_OUTRIGHT_selling ne 0 or $storewide_grn_OUTRIGHT_selling ne ''}
	    {assign var=grn_storewide_gp value=$storewide_grn_OUTRIGHT_selling-$storewide_grn_OUTRIGHT_cost}
		{assign var=grn_storewide_gp value=$grn_storewide_gp/$storewide_grn_OUTRIGHT_selling*100}
	{else}
	    {assign var=grn_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_grn_o_gp" title="{$colgp}" {$background}>{$grn_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ADJUSTMENT DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_adj_o_cost" title="{$col9}">{$storewide_adj_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_adj_OUTRIGHT_selling ne 0 or $storewide_adj_OUTRIGHT_selling ne ''}
	    {assign var=adj_storewide_gp value=$storewide_adj_OUTRIGHT_selling-$storewide_adj_OUTRIGHT_cost}
		{assign var=adj_storewide_gp value=$adj_storewide_gp/$storewide_adj_OUTRIGHT_selling*100}
	{else}
	    {assign var=adj_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_adj_o_gp" title="{$colgp}" {$background}>{$adj_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ACTUAL STOCK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_as_o_cost" title="{$col10}">{$storewide_as_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_as_OUTRIGHT_selling ne 0 or $storewide_as_OUTRIGHT_selling ne ''}
	    {assign var=as_storewide_gp value=$storewide_as_OUTRIGHT_selling-$storewide_as_OUTRIGHT_cost}
		{assign var=as_storewide_gp value=$as_storewide_gp/$storewide_as_OUTRIGHT_selling*100}
	{else}
	    {assign var=as_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_as_o_gp" title="{$colgp}" {$background}>{$as_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------RETURN STOCK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_rs_o_cost" title="{$col11}">{$storewide_rs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_rs_OUTRIGHT_selling ne 0 or $storewide_rs_OUTRIGHT_selling ne ''}
	    {assign var=rs_storewide_gp value=$storewide_rs_OUTRIGHT_selling-$storewide_rs_OUTRIGHT_cost}
		{assign var=rs_storewide_gp value=$rs_storewide_gp/$storewide_rs_OUTRIGHT_selling*100}
	{else}
	    {assign var=rs_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_rs_o_gp" title="{$colgp}" {$background}>{$rs_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------TRANSFER STOCK DEPT TOTAL OUTRIGHT GP---------->
{*	<td class="r" id="storewide_ts_o_cost">{$storewide_ts_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_ts_OUTRIGHT_selling ne 0 or $storewide_ts_OUTRIGHT_selling ne ''}
	    {assign var=ts_storewide_gp value=$storewide_ts_OUTRIGHT_selling-$storewide_ts_OUTRIGHT_cost}
		{assign var=ts_storewide_gp value=$ts_storewide_gp/$storewide_ts_OUTRIGHT_selling*100}
	{else}
	    {assign var=ts_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_ts_o_gp" title="{$colgp}" {$background}>{$ts_storewide_gp|number_format:2|ifzero:'-':$percent}</td>   *}

	<!---------IDT DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_idt_o_cost" title="{$col12}">{$storewide_idt_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_idt_OUTRIGHT_selling ne 0 or $storewide_idt_OUTRIGHT_selling ne ''}
	    {assign var=idt_storewide_gp value=$storewide_idt_OUTRIGHT_selling-$storewide_idt_OUTRIGHT_cost}
		{assign var=idt_storewide_gp value=$idt_storewide_gp/$storewide_idt_OUTRIGHT_selling*100}
	{else}
	    {assign var=idt_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_idt_o_gp" title="{$colgp}" {$background}>{$idt_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------PROMOTION AMOUNT DEPT TOTAL OUTRIGHT GP---------->
	<td rowspan=2 class="r" id="storewide_pa_o_selling" title="{$col13}">{$storewide_pa_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_OUTRIGHT_selling ne 0 or $storewide_acs_OUTRIGHT_selling ne ''}
		{assign var=pa_storewide_gp value=$storewide_pa_OUTRIGHT_selling/$storewide_acs_OUTRIGHT_selling*100}
	{else}
	    {assign var=pa_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pa_o_gp" title="{$colper}" {$background}>{$pa_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------PRICE CHANGE AMOUNT DEPT TOTAL OUTRIGHT GP---------->
	<td rowspan=2 class="r" id="storewide_pca_o_selling" title="{$col14}">{$storewide_pca_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_OUTRIGHT_selling ne 0 or $storewide_acs_OUTRIGHT_selling ne ''}
		{assign var=pca_storewide_gp value=$storewide_pca_OUTRIGHT_selling/$storewide_acs_OUTRIGHT_selling*100}
	{else}
	    {assign var=pca_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pca_o_gp" title="{$colper}" {$background}>{$pca_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ACTUAL SALES DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_acs_o_cost" title="{$col15}">{$storewide_acs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_OUTRIGHT_selling ne 0 or $storewide_acs_OUTRIGHT_selling ne ''}
	    {assign var=acs_storewide_gp value=$storewide_acs_OUTRIGHT_selling-$storewide_acs_OUTRIGHT_cost}
		{assign var=acs_storewide_gp value=$acs_storewide_gp/$storewide_acs_OUTRIGHT_selling*100}
	{else}
	    {assign var=acs_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_acs_o_gp" title="{$colgp}" {$background}>{$acs_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------REBATE DEPT TOTAL OUTRIGHT GP---------->
	{*<td rowspan=2 class="r hide" id="storewide_r_o_selling">{$storewide_r_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>*}
	{*
	{if $storewide_acs_OUTRIGHT_selling ne 0 or $storewide_acs_OUTRIGHT_selling ne ''}
	    {assign var=r_storewide_gp value=$storewide_acs_OUTRIGHT_selling-$storewide_acs_OUTRIGHT_cost+$storewide_r_OUTRIGHT_selling}
		{assign var=r_storewide_gp value=$r_storewide_gp/$storewide_acs_OUTRIGHT_selling*100}
	{else}
	    {assign var=r_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_r_o_gp" title="{$coladjgp}" {$background}>{$r_storewide_gp|number_format:2|ifzero:'-':$percent}</td>*}

	<!---------CLOSING STOCK DEPT TOTAL OUTRIGHT GP---------->
	<td class="r" id="storewide_cs_o_cost" title="{$col16}">{$storewide_cs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_cs_OUTRIGHT_selling ne 0 or $storewide_cs_OUTRIGHT_selling ne ''}
	    {assign var=cs_storewide_gp value=$storewide_cs_OUTRIGHT_selling-$storewide_cs_OUTRIGHT_cost}
		{assign var=cs_storewide_gp value=$cs_storewide_gp/$storewide_cs_OUTRIGHT_selling*100}
	{else}
	    {assign var=cs_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_cs_o_gp" title="{$colgp}" {$background}>{$cs_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------OTHER INCOME DEPT TOTAL OUTRIGHT ---------->
	<td rowspan=2 class="r" id="storewide_oi_o_selling" title="{$col17}">{$storewide_oi_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

	<!---------PROFIT MARGIN DEPT TOTAL OUTRIGHT ---------->
	<td rowspan=2 class="r" id="storewide_pm_o_selling" title="{$col18}">{$storewide_pm_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_OUTRIGHT_selling ne 0 or $storewide_acs_OUTRIGHT_selling ne ''}
		{assign var=pm_storewide_gp value=$storewide_pm_OUTRIGHT_selling/$storewide_acs_OUTRIGHT_selling*100}
	{else}
	    {assign var=pm_storewide_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pm_o_gp" title="{$coladjgp}" {$background}>{$pm_storewide_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------AVERAGE STOCK DEPT TOTAL OUTRIGHT ---------->
	{assign var=storewide_av_OUTRIGHT_selling value=$storewide_aos_OUTRIGHT_cost+$storewide_cs_OUTRIGHT_cost}
	{assign var=storewide_av_OUTRIGHT_selling value=$storewide_av_OUTRIGHT_selling/2}
	<td rowspan=2 class="r" id="storewide_av_o_selling" title="{$colgp}" title="{$col19}">{$storewide_av_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

	<!---------TURN DAYS DEPT TOTAL OUTRIGHT ---------->
	{if $storewide_acs_OUTRIGHT_cost ne 0 or $storewide_acs_OUTRIGHT_cost ne ''}
		{assign var=storewide_td_OUTRIGHT_selling value=$storewide_av_OUTRIGHT_selling/$storewide_acs_OUTRIGHT_cost*$d}
	{else}
	    {assign var=storewide_td_OUTRIGHT_selling value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_td_o_selling" title="{$colgp}" title="{$col20}">{$storewide_td_OUTRIGHT_selling|number_format:0|ifzero:'-'}</td>

</tr>
<tr class="store_outright">
	<th>SP</th>
	<td class="r" id="storewide_os_o_selling" title="{$col1}">{$storewide_os_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_cos_o_selling" title="{$col2}">{$storewide_cos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_stv_o_selling" title="{$col3}">{$storewide_stv_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" title="{$col4}">{$storewide_stc_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" title="{$col5}">{$storewide_cov_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_aos_o_selling" title="{$col6}">{$storewide_aos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_sr_o_selling" title="{$col7}">{$storewide_sr_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_grn_o_selling" title="{$col8}">{$storewide_grn_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_adj_o_selling" title="{$col9}">{$storewide_adj_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_as_o_selling" title="{$col10}">{$storewide_as_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_rs_o_selling" title="{$col11}">{$storewide_rs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
{*	<td class="r" id="storewide_ts_o_selling">{$storewide_ts_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>  *}
	<td class="r" id="storewide_idt_o_selling" title="{$col12}">{$storewide_idt_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_acs_o_selling" title="{$col15}">{$storewide_acs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_cs_o_selling" title="{$col16}">{$storewide_cs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
</tr>
{/if}

<!---------------------TOTAL STOREWIDE CONSIGN------------------------>
{if $storewide_pa_CONSIGN_selling or $storewide_acs_CONSIGN_cost or $storewide_acs_CONSIGN_selling}
<tr class="store_consign">
	<th rowspan=2>STOREWIDE<br>(Consignment)</th>
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

	<!---------IDT DEPT TOTAL CONSIGN GP---------->
	<td class="r" id="storewide_idt_c_cost" title="{$col12}">{$storewide_idt_CONSIGN_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_idt_CONSIGN_selling ne 0 or $storewide_idt_CONSIGN_selling ne ''}
	    {assign var=idt_storewide_cgp value=$storewide_idt_CONSIGN_selling-$storewide_idt_CONSIGN_cost}
		{assign var=idt_storewide_cgp value=$idt_storewide_cgp/$storewide_idt_CONSIGN_selling*100}
	{else}
	    {assign var=idt_storewide_cgp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_idt_c_gp" title="{$colgp}" {$background}>{$idt_storewide_cgp|number_format:2|ifzero:'-':$percent}</td>

	<!---------PROMOTION AMOUNT DEPT TOTAL CONSIGN GP---------->
	<td rowspan=2 class="r" id="storewide_pa_c_selling" title="{$col13}">{$storewide_pa_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_CONSIGN_selling ne 0 or $storewide_acs_CONSIGN_selling ne ''}
		{assign var=pa_storewide_cgp value=$storewide_pa_CONSIGN_selling/$storewide_acs_CONSIGN_selling*100}
	{else}
	    {assign var=pa_storewide_cgp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pa_c_gp" title="{$colper}" {$background}>{$pa_storewide_cgp|number_format:2|ifzero:'-':$percent}</td>

	<td rowspan=2 class="r" title="{$col14}">&nbsp;</td>
	<td rowspan=2 class="r" title="{$colper}" {$background}>&nbsp;</td>

	<!---------ACTUAL SALES DEPT TOTAL CONSIGN GP---------->
	<td class="r" id="storewide_acs_c_cost" title="{$col15}">{$storewide_acs_CONSIGN_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_CONSIGN_selling ne 0 or $storewide_acs_CONSIGN_selling ne ''}
	    {assign var=acs_storewide_cgp value=$storewide_acs_CONSIGN_selling-$storewide_acs_CONSIGN_cost}
		{assign var=acs_storewide_cgp value=$acs_storewide_cgp/$storewide_acs_CONSIGN_selling*100}
	{else}
	    {assign var=acs_storewide_cgp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_acs_c_gp" title="{$colgp}" {$background}>{$acs_storewide_cgp|number_format:2|ifzero:'-':$percent}</td>

	<!---------REBATE DEPT TOTAL CONSIGN GP---------->
	{*<td rowspan=2 class="r hide" id="storewide_r_c_selling">{$storewide_r_CONSIGN_selling|number_format:2|ifzero:'-'}</td>*}
	{*
	{if $storewide_acs_CONSIGN_selling ne 0 or $storewide_acs_CONSIGN_selling ne ''}
	    {assign var=r_storewide_cgp value=$storewide_acs_CONSIGN_selling-$storewide_acs_CONSIGN_cost+$storewide_r_CONSIGN_selling}
		{assign var=r_storewide_cgp value=$r_storewide_cgp/$storewide_acs_CONSIGN_selling*100}
	{else}
	    {assign var=r_storewide_cgp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_r_c_gp" title="{$coladjgp}" {$background}>{$r_storewide_cgp|number_format:2|ifzero:'-':$percent}</td>*}

	<td class="r" title="{$col16}">&nbsp;</td>
	<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

	<!---------OTHER INCOME DEPT TOTAL CONSIGN ---------->
	<td rowspan=2 class="r" id="storewide_oi_c_selling" title="{$col17}">{$storewide_oi_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

	<!---------PROFIT MARGIN DEPT TOTAL CONSIGN ---------->
	<td rowspan=2 class="r" id="storewide_pm_c_selling" title="{$col18}">{$storewide_pm_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_CONSIGN_selling ne 0 or $storewide_acs_CONSIGN_selling ne ''}
		{assign var=pm_storewide_cgp value=$storewide_pm_CONSIGN_selling/$storewide_acs_CONSIGN_selling*100}
	{else}
	    {assign var=pm_storewide_cgp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pm_c_gp" title="{$coladjgp}" {$background}>{$pm_storewide_cgp|number_format:2|ifzero:'-':$percent}</td>

	<td rowspan=2 class="r" title="{$col19}">&nbsp;</td>
	<td rowspan=2 class="r" title="{$col20}">&nbsp;</td>

</tr>
<tr class="store_consign">
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
	<td class="r" id="storewide_idt_c_selling" title="{$col12}">{$storewide_idt_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_acs_c_selling" title="{$col15}">{$storewide_acs_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" title="{$col16}">&nbsp;</td>
</tr>
{/if}

<!---------------------TOTAL STOREWIDE TOTAL------------------------>
	{assign var=storewide_os_total_cost value=$storewide_os_OUTRIGHT_cost+$storewide_os_CONSIGN_cost}
	{assign var=storewide_os_total_selling value=$storewide_os_OUTRIGHT_selling+$storewide_os_CONSIGN_selling}
	{assign var=storewide_cos_total_cost value=$storewide_cos_OUTRIGHT_cost+$storewide_cos_CONSIGN_cost}
	{assign var=storewide_cos_total_selling value=$storewide_cos_OUTRIGHT_selling+$storewide_cos_CONSIGN_selling}
	{assign var=storewide_stc_total_cost value=$storewide_stc_OUTRIGHT_cost+$storewide_stc_CONSIGN_cost}
	{assign var=storewide_stc_total_selling value=$storewide_stc_OUTRIGHT_selling+$storewide_stc_CONSIGN_selling}
	{assign var=storewide_cov_total_cost value=$storewide_cov_OUTRIGHT_cost+$storewide_cov_CONSIGN_cost}
	{assign var=storewide_cov_total_selling value=$storewide_cov_OUTRIGHT_selling+$storewide_cov_CONSIGN_selling}
	{assign var=storewide_aos_total_cost value=$storewide_aos_OUTRIGHT_cost+$storewide_aos_CONSIGN_cost}
	{assign var=storewide_aos_total_selling value=$storewide_aos_OUTRIGHT_selling+$storewide_aos_CONSIGN_selling}
	{assign var=storewide_sr_total_cost value=$storewide_sr_OUTRIGHT_cost+$storewide_sr_CONSIGN_cost}
	{assign var=storewide_sr_total_selling value=$storewide_sr_OUTRIGHT_selling+$storewide_sr_CONSIGN_selling}
	{assign var=storewide_grn_total_cost value=$storewide_grn_OUTRIGHT_cost+$storewide_grn_CONSIGN_cost}
	{assign var=storewide_grn_total_selling value=$storewide_grn_OUTRIGHT_selling+$storewide_grn_CONSIGN_selling}
	{assign var=storewide_adj_total_cost value=$storewide_adj_OUTRIGHT_cost+$storewide_adj_CONSIGN_cost}
	{assign var=storewide_adj_total_selling value=$storewide_adj_OUTRIGHT_selling+$storewide_adj_CONSIGN_selling}
	{assign var=storewide_stv_total_cost value=$storewide_stv_OUTRIGHT_cost+$storewide_stv_CONSIGN_cost}
	{assign var=storewide_stv_total_selling value=$storewide_stv_OUTRIGHT_selling+$storewide_stv_CONSIGN_selling}
	{assign var=storewide_rs_total_cost value=$storewide_rs_OUTRIGHT_cost+$storewide_rs_CONSIGN_cost}
	{assign var=storewide_rs_total_selling value=$storewide_rs_OUTRIGHT_selling+$storewide_rs_CONSIGN_selling}
	{* assign var=storewide_ts_total_cost value=$storewide_ts_OUTRIGHT_cost+$storewide_ts_CONSIGN_cost *}
	{assign var=storewide_ts_total_selling value=$storewide_ts_OUTRIGHT_selling+$storewide_ts_CONSIGN_selling+$storewide_ts_FRESH_selling}
	{assign var=storewide_idt_total_cost value=$storewide_idt_OUTRIGHT_cost+$storewide_idt_CONSIGN_cost}
	{assign var=storewide_idt_total_selling value=$storewide_idt_OUTRIGHT_selling+$storewide_idt_CONSIGN_selling}
	{assign var=storewide_pa_total_selling value=$storewide_pa_OUTRIGHT_selling+$storewide_pa_CONSIGN_selling}
	{assign var=storewide_pca_total_selling value=$storewide_pca_OUTRIGHT_selling+$storewide_pca_CONSIGN_selling}
	{assign var=storewide_acs_total_cost value=$storewide_acs_OUTRIGHT_cost+$storewide_acs_CONSIGN_cost+$storewide_acs_FRESH_cost}
	{assign var=storewide_acs_total_selling value=$storewide_acs_OUTRIGHT_selling+$storewide_acs_CONSIGN_selling+$storewide_acs_FRESH_selling}


<tr class="store_total">
	<th rowspan=2>STOREWIDE<br>(Total)</th>
	<th>CP</th>
	<!---------SYSTEM OPENING STOCK DEPT TOTAL GP---------->
	<td class="r" id="storewide_os_t_cost" title="{$col1}">{$storewide_os_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_os_total_selling ne 0 or $storewide_os_total_selling ne ''}
	    {assign var=os_storewide_total_gp value=$storewide_os_total_selling-$storewide_os_total_cost}
		{assign var=os_storewide_total_gp value=$os_storewide_total_gp/$storewide_os_total_selling*100}
	{else}
	    {assign var=os_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_os_t_gp" title="{$colgp}" {$background}>{$os_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------OPENING STOCK DEPT TOTAL GP---------->
	<td class="r" id="storewide_cos_t_cost" title="{$col2}">{$storewide_cos_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_cos_total_selling ne 0 or $storewide_cos_total_selling ne ''}
	    {assign var=cos_storewide_total_gp value=$storewide_cos_total_selling-$storewide_cos_total_cost}
		{assign var=cos_storewide_total_gp value=$cos_storewide_total_gp/$storewide_cos_total_selling*100}
	{else}
	    {assign var=cos_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_cos_t_gp" title="{$colgp}" {$background}>{$cos_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------STOCK TAKE VARIANCE DEPT TOTAL GP---------->
	<td class="r" id="storewide_stv_t_cost" title="{$col3}">{$storewide_stv_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_stv_total_selling ne 0 or $storewide_stv_total_selling ne ''}
	    {assign var=stv_storewide_total_gp value=$storewide_stv_total_selling-$storewide_stv_total_cost}
		{assign var=stv_storewide_total_gp value=$stv_storewide_total_gp/$storewide_stv_total_selling*100}
	{else}
	    {assign var=stv_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_stv_t_gp" title="{$colgp}" {$background}>{$stv_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------STOCK CHECK DEPT TOTAL GP---------->
	<td class="r" title="{$col4}">{$storewide_stc_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_stc_total_selling ne 0 or $storewide_stc_total_selling ne ''}
	    {assign var=stc_storewide_total_gp value=$storewide_stc_total_selling-$storewide_stc_total_cost}
		{assign var=stc_storewide_total_gp value=$stc_storewide_total_gp/$storewide_stc_total_selling*100}
	{else}
	    {assign var=stc_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" title="{$colgp}" {$background}>{$stc_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------CSA OPENING VARIANCE DEPT TOTAL GP---------->
	<td class="r" title="{$col5}">{$storewide_cov_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_cov_total_selling ne 0 or $storewide_cov_total_selling ne ''}
	    {assign var=cov_storewide_total_gp value=$storewide_cov_total_selling-$storewide_cov_total_cost}
		{assign var=cov_storewide_total_gp value=$cov_storewide_total_gp/$storewide_cov_total_selling*100}
	{else}
	    {assign var=cov_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" title="{$colgp}" {$background}>{$cov_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ACTUAL OPENING STOCK DEPT TOTAL GP---------->
	<td class="r" id="storewide_aos_t_cost" title="{$col6}">{$storewide_aos_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_aos_total_selling ne 0 or $storewide_aos_total_selling ne ''}
	    {assign var=aos_storewide_total_gp value=$storewide_aos_total_selling-$storewide_aos_total_cost}
		{assign var=aos_storewide_total_gp value=$aos_storewide_total_gp/$storewide_aos_total_selling*100}
	{else}
	    {assign var=aos_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_aos_t_gp" title="{$colgp}" {$background}>{$aos_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------STOCK RECEIVE DEPT TOTAL GP---------->
	<td class="r" id="storewide_sr_t_cost" title="{$col7}">{$storewide_sr_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_sr_total_selling ne 0 or $storewide_sr_total_selling ne ''}
	    {assign var=sr_storewide_total_gp value=$storewide_sr_total_selling-$storewide_sr_total_cost}
		{assign var=sr_storewide_total_gp value=$sr_storewide_total_gp/$storewide_sr_total_selling*100}
	{else}
	    {assign var=sr_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_sr_t_gp" title="{$colgp}" {$background}>{$sr_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------GRN PENDING DEPT TOTAL GP---------->
	<td class="r" id="storewide_grn_t_cost" title="{$col8}">{$storewide_grn_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_grn_total_selling ne 0 or $storewide_grn_total_selling ne ''}
	    {assign var=grn_storewide_total_gp value=$storewide_grn_total_selling-$storewide_grn_total_cost}
		{assign var=grn_storewide_total_gp value=$grn_storewide_total_gp/$storewide_grn_total_selling*100}
	{else}
	    {assign var=grn_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_grn_t_gp" title="{$colgp}" {$background}>{$grn_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ADJUSTMENT DEPT TOTAL GP---------->
	<td class="r" id="storewide_adj_t_cost" title="{$col9}">{$storewide_adj_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_adj_total_selling ne 0 or $storewide_adj_total_selling ne ''}
	    {assign var=adj_storewide_total_gp value=$storewide_adj_total_selling-$storewide_adj_total_cost}
		{assign var=adj_storewide_total_gp value=$adj_storewide_total_gp/$storewide_adj_total_selling*100}
	{else}
	    {assign var=adj_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_adj_t_gp" title="{$colgp}" {$background}>{$adj_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ACTUAL STOCK DEPT TOTAL GP---------->
	{assign var=storewide_as_total_cost value=$storewide_as_OUTRIGHT_cost+$storewide_as_CONSIGN_cost+$storewide_as_FRESH_cost}
	{assign var=storewide_as_total_selling value=$storewide_as_OUTRIGHT_selling+$storewide_as_CONSIGN_selling+$storewide_as_FRESH_selling}
	<td class="r" id="storewide_as_t_cost" title="{$col10}">{$storewide_as_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_as_total_selling ne 0 or $storewide_as_total_selling ne ''}
	    {assign var=as_storewide_total_gp value=$storewide_as_total_selling-$storewide_as_total_cost}
		{assign var=as_storewide_total_gp value=$as_storewide_total_gp/$storewide_as_total_selling*100}
	{else}
	    {assign var=as_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_as_t_gp" title="{$colgp}" {$background}>{$as_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------RETURN STOCK DEPT TOTAL GP---------->
	<td class="r" id="storewide_rs_t_cost" title="{$col11}">{$storewide_rs_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_rs_total_selling ne 0 or $storewide_rs_total_selling ne ''}
	    {assign var=rs_storewide_total_gp value=$storewide_rs_total_selling-$storewide_rs_total_cost}
		{assign var=rs_storewide_total_gp value=$rs_storewide_total_gp/$storewide_rs_total_selling*100}
	{else}
	    {assign var=rs_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_rs_t_gp" title="{$colgp}" {$background}>{$rs_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------TRANSFER STOCK DEPT TOTAL GP---------->
{*	<td class="r" id="storewide_ts_t_cost">{$storewide_ts_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_ts_total_selling ne 0 or $storewide_ts_total_selling ne ''}
	    {assign var=ts_storewide_total_gp value=$storewide_ts_total_selling-$storewide_ts_total_cost}
		{assign var=ts_storewide_total_gp value=$ts_storewide_total_gp/$storewide_ts_total_selling*100}
	{else}
	    {assign var=ts_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_ts_t_gp" title="{$colgp}" {$background}>{$ts_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td> *}

	<!---------IDT DEPT TOTAL GP---------->
	<td class="r" id="storewide_idt_t_cost" title="{$col12}">{$storewide_idt_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_idt_total_selling ne 0 or $storewide_idt_total_selling ne ''}
	    {assign var=idt_storewide_total_gp value=$storewide_idt_total_selling-$storewide_idt_total_cost}
		{assign var=idt_storewide_total_gp value=$idt_storewide_total_gp/$storewide_idt_total_selling*100}
	{else}
	    {assign var=idt_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_idt_t_gp" title="{$colgp}" {$background}>{$idt_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------PROMOTION AMOUNT DEPT TOTAL GP---------->
	<td rowspan=2 class="r" id="storewide_pa_t_selling" title="{$col13}">{$storewide_pa_total_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_total_selling ne 0 or $storewide_acs_total_selling ne ''}
		{assign var=pa_storewide_total_gp value=$storewide_pa_total_selling/$storewide_acs_total_selling*100}
	{else}
	    {assign var=pa_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="pa_storewide_t_gp" title="{$colper}" {$background}>{$pa_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------PRICE CHANGE AMOUNT DEPT TOTAL GP---------->
	<td rowspan=2 class="r" id="storewide_pca_t_selling" title="{$col14}">{$storewide_pca_total_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_total_selling ne 0 or $storewide_acs_total_selling ne ''}
		{assign var=pca_storewide_total_gp value=$storewide_pca_total_selling/$storewide_acs_total_selling*100}
	{else}
	    {assign var=pca_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pca_t_gp" title="{$colper}" {$background}>{$pca_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------ACTUAL SALES DEPT TOTAL GP---------->
	<td class="r" id="storewide_acs_t_cost" title="{$col15}">{$storewide_acs_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_total_selling ne 0 or $storewide_acs_total_selling ne ''}
	    {assign var=acs_storewide_total_gp value=$storewide_acs_total_selling-$storewide_acs_total_cost}
		{assign var=acs_storewide_total_gp value=$acs_storewide_total_gp/$storewide_acs_total_selling*100}
	{else}
	    {assign var=acs_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_acs_t_gp" title="{$colgp}" {$background}>{$acs_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------REBATE DEPT TOTAL GP---------->
	{*{assign var=storewide_r_total_selling value=$storewide_r_OUTRIGHT_selling+$storewide_r_CONSIGN_selling+$storewide_r_FRESH_selling}
	<td rowspan=2 class="r hide" id="storewide_r_t_selling">{$storewide_r_total_selling|number_format:2|ifzero:'-'}</td>*}
	{*
	{if $storewide_acs_total_selling ne 0 or $storewide_acs_total_selling ne ''}
	    {assign var=r_storewide_total_gp value=$storewide_acs_total_selling-$storewide_acs_total_cost+$storewide_acs_total_selling}
		{assign var=r_storewide_total_gp value=$acs_storewide_total_gp/$storewide_acs_total_selling*100}
	{else}
	    {assign var=r_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_r_t_gp" title="{$coladjgp}" {$background}>{$r_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>*}

	<!---------CLOSING STOCK DEPT TOTAL GP---------->
	{assign var=storewide_cs_total_cost value=$storewide_cs_OUTRIGHT_cost+$storewide_cs_CONSIGN_cost+$storewide_cs_FRESH_cost}
	{assign var=storewide_cs_total_selling value=$storewide_cs_OUTRIGHT_selling+$storewide_cs_CONSIGN_selling+$storewide_cs_FRESH_selling}
	<td class="r" id="storewide_cs_t_cost" title="{$col16}">{$storewide_cs_total_cost|number_format:2|ifzero:'-'}</td>

	{if $storewide_cs_total_selling ne 0 or $storewide_cs_total_selling ne ''}
	    {assign var=cs_storewide_total_gp value=$storewide_cs_total_selling-$storewide_cs_total_cost}
		{assign var=cs_storewide_total_gp value=$cs_storewide_total_gp/$storewide_cs_total_selling*100}
	{else}
	    {assign var=cs_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_cs_t_gp" title="{$colgp}" {$background}>{$cs_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------OTHER INCOME TOTAL GP---------->
	{assign var=storewide_oi_total_selling value=$storewide_oi_OUTRIGHT_selling+$storewide_oi_CONSIGN_selling+$storewide_oi_FRESH_selling}
	<td rowspan=2 class="r" id="storewide_oi_t_selling" title="{$col17}">{$storewide_oi_total_selling|number_format:2|ifzero:'-'}</td>

	<!---------PROFIT MARGIN TOTAL GP---------->
	{assign var=storewide_pm_total_selling value=$storewide_pm_OUTRIGHT_selling+$storewide_pm_CONSIGN_selling+$storewide_pm_FRESH_selling}
	<td rowspan=2 class="r" id="storewide_pm_t_selling" title="{$col18}">{$storewide_pm_total_selling|number_format:2|ifzero:'-'}</td>

	{if $storewide_acs_total_selling ne 0 or $storewide_acs_total_selling ne ''}
		{assign var=pm_storewide_total_gp value=$storewide_pm_total_selling/$storewide_acs_total_selling*100}
	{else}
	    {assign var=pm_storewide_total_gp value=0}
	{/if}
	<td rowspan=2 class="r" id="storewide_pm_t_gp" title="{$coladjgp}" {$background}>{$pm_storewide_total_gp|number_format:2|ifzero:'-':$percent}</td>

	<!---------AVERAGE STOCK TOTAL GP---------->
	{assign var=storewide_av_total_selling value=$storewide_aos_total_cost+$storewide_cs_total_cost}
	{assign var=storewide_av_total_selling value=$storewide_av_total_selling/2}
	<td rowspan=2 class="r" id="storewide_av_t_selling" title="{$col19}">{$storewide_av_total_selling|number_format:2|ifzero:'-'}</td>

	<!---------TURN DAYS TOTAL GP---------->
	{if $storewide_acs_total_cost ne 0 or $storewide_acs_total_cost ne ''}
		{assign var=storewide_td_total_selling value=$storewide_av_total_selling/$storewide_acs_total_cost*$d}
	{else}
	    {assign var=storewide_td_total_selling value=0}
	{/if}

	<td rowspan=2 class="r" id="storewide_td_t_selling" title="{$col20}">{$storewide_td_total_selling|number_format:0|ifzero:'-'}</td>

</tr>
<tr class="store_total">
	<th>SP</th>
	<td class="r" id="storewide_os_t_selling" title="{$col1}">{$storewide_os_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_cos_t_selling" title="{$col2}">{$storewide_cos_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_stv_t_selling" title="{$col3}">{$storewide_stv_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" title="{$col4}">{$storewide_stc_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" title="{$col5}">{$storewide_cov_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_aos_t_selling" title="{$col6}">{$storewide_aos_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_sr_t_selling" title="{$col7}">{$storewide_sr_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_grn_t_selling" title="{$col8}">{$storewide_grn_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_adj_t_selling" title="{$col9}">{$storewide_adj_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_as_t_selling" title="{$col10}">{$storewide_as_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_rs_t_selling" title="{$col11}">{$storewide_rs_total_selling|number_format:2|ifzero:'-'}</td>
{*	<td class="r" id="storewide_ts_t_selling">{$storewide_ts_total_selling|number_format:2|ifzero:'-'}</td> *}
	<td class="r" id="storewide_idt_t_selling" title="{$col12}">{$storewide_idt_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_acs_t_selling" title="{$col15}">{$storewide_acs_total_selling|number_format:2|ifzero:'-'}</td>
	<td class="r" id="storewide_cs_t_selling" title="{$col16}">{$storewide_cs_total_selling|number_format:2|ifzero:'-'}</td>
</tr>

{if $got_fresh}
	<tr>
	    <td colspan="40">&nbsp;</td>
	</tr>

	<!--------FRESH MARKET WEIGHT (TOTAL)----------->
	<tbody class="storewide_fresh">
	<tr class="total_fresh">
		<th rowspan=2>FRESH MARKET WEIGHT<br>(Grand Total)</th>
		<th rowspan=2>CP</th>

		<!---------SYSTEM OPENING STOCK DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_os_f_selling" title="{$col1}">{$storewide_os_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_os_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------OPENING STOCK DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_cos_f_selling" title="{$col2}">{$storewide_cos_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_cos_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------STOCK TAKE VARIANCE DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_stv_f_selling" title="{$col3}">{$storewide_stv_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_stv_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------STOCK CHECK DEPT FRESH GP---------->
		<td rowspan=2 class="r" title="{$col4}">{$storewide_stc_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------CSA OPENING VARIANCE DEPT FRESH GP---------->
		<td rowspan=2 class="r" title="{$col5}">{$storewide_cov_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------ACTUAL OPENING STOCK DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_aos_f_selling" title="{$col6}">{$storewide_aos_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_aos_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------STOCK RECEIVE DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_sr_f_selling" title="{$col7}">{$storewide_sr_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_sr_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------GRN PENDING DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_grn_f_selling" title="{$col8}">{$storewide_grn_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_grn_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------ADJUSTMENT DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_adj_f_selling" title="{$col9}">{$storewide_adj_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_adj_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------ACTUAL STOCK DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_as_f_selling" title="{$col10}">{$storewide_as_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_as_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------RETURN STOCK DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_rs_f_selling" title="{$col11}">{$storewide_rs_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_rs_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------IDT DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_idt_f_selling" title="{$col12}">{$storewide_idt_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_idt_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------PROMOTION AMOUNT DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_pa_f_selling" title="{$col13}">{$storewide_pa_FRESH_selling|number_format:2|ifzero:'-'}</td>

		{if $storewide_acs_FRESH_selling ne 0 or $storewide_acs_FRESH_selling ne ''}
			{assign var=pa_storewide_fgp value=$storewide_pa_FRESH_selling/$storewide_acs_FRESH_selling*100}
		{else}
		    {assign var=pa_storewide_fgp value=0}
		{/if}
		<td rowspan=2 class="r" id="storewide_pa_f_gp" title="{$colper}" {$background}>{$pa_storewide_fgp|number_format:2|ifzero:'-':$percent}</td>

		<!---------PRICE CHANGE AMOUNT DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_pca_f_selling" title="{$col14}">{$storewide_pca_FRESH_selling|number_format:2|ifzero:'-'}</td>

		{if $storewide_acs_FRESH_selling ne 0 or $storewide_acs_FRESH_selling ne ''}
			{assign var=pca_storewide_fgp value=$storewide_pca_FRESH_selling/$storewide_acs_FRESH_selling*100}
		{else}
		    {assign var=pca_storewide_fgp value=0}
		{/if}
		<td rowspan=2 class="r" id="storewide_pca_f_gp" title="{$colper}" {$background}>{$pca_storewide_fgp|number_format:2|ifzero:'-':$percent}</td>

		<!---------ACTUAL SALES DEPT FRESH GP---------->
		<td class="r" id="storewide_acs_f_cost" title="{$col15}">{$storewide_acs_FRESH_cost|number_format:2|ifzero:'-'}</td>

		<td rowspan=2 class="r" id="storewide_acs_f_gp" title="{$colgp}" {$background}>{$acs_storewide_fgp|number_format:2|ifzero:'-':$percent}</td>

		<!---------REBATE DEPT FRESH GP---------->
		{*<td rowspan=2 class="r hide" id="storewide_r_f_selling">{$storewide_r_FRESH_selling|number_format:2|ifzero:'-'}</td>*}
		{*
		{if $storewide_acs_FRESH_selling ne 0 or $storewide_acs_FRESH_selling ne ''}
		    {assign var=r_storewide_fgp value=$storewide_acs_FRESH_selling-$storewide_acs_FRESH_cost+$storewide_r_FRESH_selling}
			{assign var=r_storewide_fgp value=$r_storewide_fgp/$storewide_acs_FRESH_selling*100}
		{else}
		    {assign var=r_storewide_fgp value=0}
		{/if}
		<td rowspan=2 class="r" id="storewide_r_f_gp" title="{$coladjgp}" {$background}>{$r_storewide_fgp|number_format:2|ifzero:'-':$percent}</td>*}

		<!---------CLOSING STOCK DEPT FRESH GP---------->
		<td rowspan=2 class="r" id="storewide_cs_f_selling" title="{$col16}">{$storewide_cs_FRESH_selling|number_format:2|ifzero:'-'}</td>
		<td rowspan=2 class="r" id="storewide_cs_f_gp" title="{$colgp}" {$background}>&nbsp;</td>

		<!---------OTHER INCOME DEPT FRESH ---------->
		<td rowspan=2 class="r" id="storewide_oi_f_selling" title="{$col17}">{$storewide_oi_FRESH_selling|number_format:2|ifzero:'-'}</td>

		<!---------PROFIT MARGIN DEPT FRESH ---------->
		<td rowspan=2 class="r" id="storewide_pm_f_selling" title="{$col18}">{$storewide_pm_FRESH_selling|number_format:2|ifzero:'-'}</td>

		{if $storewide_acs_FRESH_selling ne 0 or $storewide_acs_FRESH_selling ne ''}
			{assign var=pm_storewide_fgp value=$pm_FRESH_selling/$storewide_acs_FRESH_selling*100}
		{else}
		    {assign var=pm_storewide_fgp value=0}
		{/if}
		<td rowspan=2 class="r" id="storewide_pm_f_gp" title="{$coladjgp}" {$background}>{$pm_storewide_fgp|number_format:2|ifzero:'-':$percent}</td>

		<!---------AVERAGE STOCK DEPT FRESH ---------->
		{assign var=storewide_av_FRESH_selling value=$storewide_aos_FRESH_selling+$storewide_cs_FRESH_selling}
		{assign var=storewide_av_FRESH_selling value=$storewide_av_FRESH_selling/2}

		<td rowspan=2 class="r" id="storewide_av_f_selling" title="{$col19}">{$storewide_av_FRESH_selling|number_format:2|ifzero:'-'}</td>

		<!---------TURN DAYS DEPT FRESH ---------->
		{if $storewide_acs_FRESH_selling ne 0 or $storewide_acs_FRESH_selling ne ''}
			{assign var=storewide_td_FRESH_selling value=$storewide_av_FRESH_selling/$storewide_acs_FRESH_selling*$d}
		{else}
		    {assign var=storewide_td_FRESH_selling value=0}
		{/if}
		<td rowspan=2 class="r" id="storewide_td_f_selling" title="{$col20}">{$storewide_td_FRESH_selling|number_format:0|ifzero:'-'}</td>

	</tr>
	<tr class="total_fresh">
		<td class="r" id="storewide_acs_f_selling" title="{$col15}">{$storewide_acs_FRESH_selling|number_format:2|ifzero:'-'}</td>
	</tr>
	</tbody>
{/if}