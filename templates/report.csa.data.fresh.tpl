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

3/5/2012 3:42:34 PM Alex
- add title for each column

3/7/2012 3:48:23 PM Alex
- add title for gp % and other column

3/13/2012 10:13:39 AM Alex
- remove adj (%) column after actual sales

4/6/2012 11:03:40 AM Alex
- show item list

*}

{if $type eq cat}
	{if $fresh.$cid}

		<!--------FRESH MARKET WEIGHT (OUTRIGHT)----------->
		<tbody class="fresh_{$rid}">
		<tr class="vendors fresh" funct="FRESH,{$cid},{$rid}">
			<th rowspan=2><a onclick="ajax_load_sku_items('FRESH',{$cid})" >FRESH MARKET WEIGHT</a><br>(Outright)</th>
			<th rowspan=2>CP</th>

			<!---------SYSTEM OPENING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="os_f_selling,{$cid}" title="{$col1}">{$os_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="os_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------OPENING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="cos_f_selling,{$cid}" title="{$col2}">{$cos_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="cos_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------STOCK TAKE VARIANCE DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="stv_f_selling,{$cid}" title="{$col3}">{$stv_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="stv_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------STOCK CHECK DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" title="{$col4}">{$stc_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------CSA OPENING VARIANCE DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" title="{$col5}"><a <a onclick="ajax_load_before_stock_check('FRESH',{$cid},{$vid})">{$cov_FRESH_selling|number_format:2|ifzero:'-'}</a></td>

			<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------ACTUAL OPENING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="aos_f_selling,{$cid}" title="{$col6}">{$aos_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="aos_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------STOCK RECEIVE DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="sr_f_selling,{$cid}" title="{$col7}">{$sr_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="sr_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------GRN PENDING DEPT FRESH GP---------->
			<td rowspan=2 class="r" title="{$col8}">
			{if $disable_input}
				{$grn_FRESH_selling|number_format:2|ifzero:'-'}
			{else}
				{input class="r grn_f_selling_$rid" id="grn_f_selling,$cid" size="10" name="fgrn[$cid][FRESH][selling]" onchange="this.value=round(this.value,2);fresh_changes($cid,$rid);" value=$grn_FRESH_selling|number_format:2|ifzero:''}
			{/if}
			</td>

			<td rowspan=2 class="r" id="grn_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------ADJUSTMENT DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="adj_f_selling,{$cid}" title="{$col9}">{$adj_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="adj_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------ACTUAL STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r as_f_selling" id="as_f_selling,{$cid}" title="{$col10}">{$as_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="as_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------RETURN STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="rs_f_selling,{$cid}" title="{$col11}">{$rs_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="rs_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------IDT DEPT FRESH GP---------->
			<td rowspan=2 class="r" title="{$col12}">
			{if $disable_input}
				{$idt_FRESH_selling|number_format:2|ifzero:'-'}
			{else}
				{input  class="r idt_f_selling_$rid" id="idt_f_selling,$cid" size="10" name="fidt[$cid][FRESH][selling]" onchange="this.value=round(this.value,2);fresh_changes($cid,$rid);" value=$idt_FRESH_selling|number_format:2|ifzero:''}
			{/if}
			</td>
			<td rowspan=2 class="r" id="idt_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------PROMOTION AMOUNT DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="pa_f_selling,{$cid}" title="{$col13}">{$pa_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_FRESH_selling ne 0 or $acs_FRESH_selling ne ''}
				{assign var=pa_FRESH_gp value=$pa_FRESH_selling/$acs_FRESH_selling*100}
			{else}
			    {assign var=pa_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pa_f_gp,{$cid}" title="{$colper}" {$background}>{$pa_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PRICE CHANGE AMOUNT DEPT FRESH GP---------->
			<td rowspan=2 class="keyin r" id="pca_f_selling,{$cid}" title="{$col14}">{$pca_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_FRESH_selling ne 0 or $acs_FRESH_selling ne ''}
				{assign var=pca_FRESH_gp value=$pca_FRESH_selling/$acs_FRESH_selling*100}
			{else}
			    {assign var=pca_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pca_f_gp,{$cid}" title="{$colper}" {$background}>{$pca_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL SALES DEPT FRESH GP---------->
			<td class="r" id="acs_f_cost,{$cid}" title="{$col15}">{$acs_FRESH_cost|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="acs_f_gp,{$cid}" title="{$colgp}" {$background}>{$acs_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------REBATE DEPT FRESH GP---------->
			{*<td rowspan=2 class="r hide">
			{if $disable_input}
				{$r_FRESH_selling|number_format:2|ifzero:'-'}
			{else}
				{input  class="r r_f_selling_$rid" id="r_f_selling,$cid" size="10" name="frebate[$cid][FRESH][selling]" onchange="this.value=round(this.value,2);fresh_changes($cid,$rid);" value=$r_FRESH_selling|number_format:2|ifzero:''}
			{/if}
			</td>*}
			{*
			{if $acs_FRESH_selling ne 0 or $acs_FRESH_selling ne ''}
			    {assign var=r_FRESH_gp value=$acs_FRESH_selling-$acs_FRESH_cost+$r_FRESH_selling}
				{assign var=r_FRESH_gp value=$r_FRESH_gp/$acs_FRESH_selling*100}
			{else}
			    {assign var=r_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="r_f_gp,{$cid}" title="{$coladjgp}" {$background}>{$r_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>*}

			<!---------CLOSING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r" title="{$col16}">
			{if $disable_input}
				{$cs_FRESH_selling|number_format:2|ifzero:'-'}
			{else}
				{input class="r cs_f_selling_$rid" id="cs_f_selling,$cid" size="10" name="fclosing[$cid][FRESH][selling]" onchange="this.value=round(this.value,2);fresh_changes($cid,$rid);" value=$cs_FRESH_selling|number_format:2|ifzero:''}
			{/if}
			</td>
			<td rowspan=2 class="r" id="cs_f_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------OTHER INCOME DEPT FRESH ---------->
			<td rowspan=2 class="r" title="{$col17}">
			{if $disable_input}
				{$oi_FRESH_selling|number_format:2|ifzero:'-'}
			{else}
			{input class="r oi_f_selling_$rid" id="oi_f_selling,$cid" size="10" name="fother[$cid][FRESH][selling]" onchange="this.value=round(this.value,2);fresh_changes($cid,$rid);" value=$oi_FRESH_selling|number_format:2|ifzero:''}
			{/if}
			</td>
			<!---------PROFIT MARGIN DEPT FRESH ---------->
			<td rowspan=2 class="r" id="pm_f_selling,{$cid}" title="{$col18}">{$pm_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_FRESH_selling ne 0 or $acs_FRESH_selling ne ''}
				{assign var=pm_FRESH_gp value=$pm_FRESH_selling/$acs_FRESH_selling*100}
			{else}
			    {assign var=pm_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pm_f_gp,{$cid}" title="{$coladjgp}" {$background}>{$pm_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------AVERAGE STOCK DEPT FRESH ---------->
			{assign var=av_FRESH_selling value=$aos_FRESH_selling+$cs_FRESH_selling}
			{assign var=av_FRESH_selling value=$av_FRESH_selling/2}

			<td rowspan=2 class="r" id="av_f_selling,{$cid}" title="{$col19}">{$av_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<!---------TURN DAYS DEPT FRESH ---------->
			{if $acs_FRESH_selling ne 0 or $acs_FRESH_selling ne ''}
				{assign var=td_FRESH_selling value=$av_FRESH_selling/$acs_FRESH_selling*$d}
			{else}
			    {assign var=td_FRESH_selling value=0}
			{/if}
			<td rowspan=2 class="r" id="td_f_selling,{$cid}" title="{$col20}">{$td_FRESH_selling|number_format:0|ifzero:'-'}</td>

		</tr>
		<tr class="fresh" funct="FRESH,{$cid},{$rid}">
			<td class="keyin r" id="acs_f_selling,{$cid}" title="{$col15}">{$acs_FRESH_selling|number_format:2|ifzero:'-'}</td>
		</tr>
		</tbody>

	{/if}

{else}

	{if $dept_got_fresh}
		<!--------FRESH MARKET WEIGHT (Total)----------->
		<tbody class="t_fresh">
		<tr class="total_fresh">
			<th rowspan=2>FRESH MARKET WEIGHT<br>(Total)</th>
			<th rowspan=2>CP</th>

			<!---------SYSTEM OPENING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_os_f_selling,{$rid}" title="{$col1}">{$t_os_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_os_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------OPENING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_cos_f_selling,{$rid}" title="{$col2}">{$t_cos_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_cos_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------STOCK TAKE VARIANCE DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_stv_f_selling,{$rid}" title="{$col3}">{$t_stv_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_stv_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------STOCK CHECK DEPT FRESH GP---------->
			<td rowspan=2 class="r" title="{$col4}">{$t_stc_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------CSA OPENING VARIANCE DEPT FRESH GP---------->
			<td rowspan=2 class="r" title="{$col5}">{$t_cov_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------ACTUAL OPENING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_aos_f_selling,{$rid}" title="{$col6}">{$t_aos_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_aos_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------STOCK RECEIVE DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_sr_f_selling,{$rid}" title="{$col7}">{$t_sr_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_sr_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------GRN PENDING DEPT FRESH GP---------->
			<td rowspan=2 class="r t_grn_f_selling" id="t_grn_f_selling,{$rid}" title="{$col8}">{$t_grn_FRESH_selling|number_format:2|ifzero:''}</td>

			<td rowspan=2 class="r" id="t_grn_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------ADJUSTMENT DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_adj_f_selling,{$rid}" title="{$col9}">{$t_adj_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_adj_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------ACTUAL STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_as_f_selling,{$rid}" title="{$col10}">{$t_as_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_as_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------RETURN STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_rs_f_selling,{$rid}" title="{$col11}">{$t_rs_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_rs_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------IDT DEPT FRESH GP---------->
			<td rowspan=2 class="r t_idt_f_selling" id="t_idt_f_selling,{$rid}" title="{$col12}">{$t_idt_FRESH_selling|number_format:2|ifzero:''}</td>

			<td rowspan=2 class="r" id="t_idt_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------PROMOTION AMOUNT DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_pa_f_selling,{$rid}" title="{$col13}">{$t_pa_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $t_acs_FRESH_selling ne 0 or $t_acs_FRESH_selling ne ''}
				{assign var=t_pa_FRESH_gp value=$t_pa_FRESH_selling/$t_acs_FRESH_selling*100}
			{else}
			    {assign var=t_pa_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="t_pa_f_gp,{$rid}" title="{$colper}" {$background}>{$t_pa_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PRICE CHANGE AMOUNT DEPT FRESH GP---------->
			<td rowspan=2 class="r" id="t_pca_f_selling,{$rid}" title="{$col14}">{$t_pca_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $t_acs_FRESH_selling ne 0 or $t_acs_FRESH_selling ne ''}
				{assign var=t_pca_FRESH_gp value=$t_pca_FRESH_selling/$t_acs_FRESH_selling*100}
			{else}
			    {assign var=t_pca_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="t_pca_f_gp,{$rid}" title="{$colper}" {$background}>{$t_pca_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL SALES DEPT FRESH GP---------->
			<td class="r" id="t_acs_f_cost,{$rid}" title="{$col15}">{$t_acs_FRESH_cost|number_format:2|ifzero:'-'}</td>

			<td rowspan=2 class="r" id="t_acs_f_gp,{$rid}" title="{$colgp}" {$background}>{$t_acs_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------REBATE DEPT FRESH GP---------->
			{*<td rowspan=2 class="r hide t_r_f_selling" id="t_r_f_selling,{$rid}">{$t_r_FRESH_selling|number_format:2|ifzero:''}</td>*}
			{*
			{if $t_acs_FRESH_selling ne 0 or $t_acs_FRESH_selling ne ''}
			    {assign var=t_r_FRESH_gp value=$t_acs_FRESH_selling-$t_acs_FRESH_cost+$t_r_FRESH_selling}
				{assign var=t_r_FRESH_gp value=$t_r_FRESH_gp/$t_acs_FRESH_selling*100}
			{else}
			    {assign var=t_r_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="t_r_f_gp,{$rid}" title="{$coladjgp}" {$background}>{$t_r_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>*}

			<!---------CLOSING STOCK DEPT FRESH GP---------->
			<td rowspan=2 class="r t_cs_f_selling" id="t_cs_f_selling,{$rid}" title="{$col16}">{$t_cs_FRESH_selling|number_format:2|ifzero:''}</td>

			<td rowspan=2 class="r" id="t_cs_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------OTHER INCOME DEPT FRESH ---------->
			<td rowspan=2 class="r t_oi_f_selling" id="t_oi_f_selling,{$rid}" title="{$col17}">{$t_oi_FRESH_selling|number_format:2|ifzero:''}</td>

			<!---------PROFIT MARGIN DEPT FRESH ---------->
			<td rowspan=2 class="r" id="t_pm_f_selling,{$rid}" title="{$col18}">{$t_pm_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $t_acs_FRESH_selling ne 0 or $t_acs_FRESH_selling ne ''}
				{assign var=t_pm_FRESH_gp value=$t_pm_FRESH_selling/$t_acs_FRESH_selling*100}
			{else}
			    {assign var=t_pm_FRESH_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="t_pm_f_gp,{$rid}" title="{$coladjgp}" {$background}>{$t_pm_FRESH_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------AVERAGE STOCK DEPT FRESH ---------->
			{assign var=t_av_FRESH_selling value=$t_aos_FRESH_selling+$t_cs_FRESH_selling}
			{assign var=t_av_FRESH_selling value=$t_av_FRESH_selling/2}

			<td rowspan=2 class="r" id="t_av_f_selling,{$rid}" title="{$col19}">{$t_av_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<!---------TURN DAYS DEPT FRESH ---------->
			{if $t_acs_FRESH_selling ne 0 or $t_acs_FRESH_selling ne ''}
				{assign var=t_td_FRESH_selling value=$t_av_FRESH_selling/$t_acs_FRESH_selling*$d}
			{else}
			    {assign var=t_td_FRESH_selling value=0}
			{/if}
			<td rowspan=2 class="r" id="t_td_f_selling,{$rid}" title="{$col20}">{$t_td_FRESH_selling|number_format:0|ifzero:'-'}</td>

		</tr>
		<tr class="total_fresh">
			<td class="r" id="t_acs_f_selling,{$rid}" title="{$col15}">{$t_acs_FRESH_selling|number_format:2|ifzero:'-'}</td>
		</tr>
		</tbody>

		<tbody class="dept_fresh_{$rid}">
		<tr class="dept_fresh">
			<th rowspan=2>
				{$root.r_descrip}<br>Grand Total Profit
			</th>
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

			<!---------IDT FRESH GP---------->
			<td class="r dept_idt_f_cost_{$rid}" id="dept_idt_f_cost,{$rid}" title="{$col12}">&nbsp;</td>
			<td rowspan=2 class="r" id="dept_idt_f_gp,{$rid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------PROMOTION AMOUNT FRESH GP---------->
			<td rowspan=2 class="r" id="dept_pa_f_selling,{$rid}" title="{$col13}">&nbsp;</td>
			<td rowspan=2 class="r" id="dept_pa_f_gp,{$rid}" title="{$colper}" {$background}>&nbsp;</td>

			<!---------PRICE CHANGE AMOUNT FRESH GP---------->
			<td rowspan=2 class="r" title="{$col14}">&nbsp;</td>
			<td rowspan=2 class="r" title="{$colper}" {$background}>&nbsp;</td>

			<!---------ACTUAL SALES FRESH GP---------->
			<td class="r dept_acs_f_cost_{$rid}" id="dept_acs_f_cost,{$rid}" title="{$col15}">{$dept_acs_FRESH_cost|number_format:2|ifzero:'-'}</td>

			{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
			    {assign var=acs_fgp value=$dept_acs_FRESH_selling-$dept_acs_FRESH_cost}
				{assign var=acs_fgp value=$acs_fgp/$dept_acs_FRESH_selling*100}
			{else}
			    {assign var=acs_fgp value=0}
			{/if}
			<td rowspan=2 class="r" id="dept_acs_f_gp,{$rid}" title="{$colgp}" {$background}>{$acs_fgp|number_format:2|ifzero:'-':$percent}</td>

			<!---------REBATE FRESH GP---------->
			{*<td rowspan=2 class="r hide dept_r_f_selling_{$rid}" id="dept_r_f_selling,{$rid}">{$dept_r_FRESH_selling|number_format:2|ifzero:'-'}</td>*}
			{*
			{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
			    {assign var=r_fgp value=$dept_acs_FRESH_selling-$dept_acs_FRESH_cost+$dept_r_FRESH_selling}
				{assign var=r_fgp value=$r_cgp/$dept_acs_FRESH_selling*100}
			{else}
			    {assign var=r_fgp value=0}
			{/if}
			<td rowspan=2 class="r" id="dept_r_f_gp,{$rid}" title="{$coladjgp}" {$background}>{$r_fgp|number_format:2|ifzero:'-':$percent}</td>*}

			<td class="r" title="{$col16}">&nbsp;</td>
			<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------OTHER INCOME FRESH GP---------->
			<td rowspan=2 class="r dept_oi_f_selling_{$rid}" id="dept_oi_f_selling,{$rid}" title="{$col17}">{$dept_oi_FRESH_selling|number_format:2|ifzero:'-'}</td>

			<!---------PROFIT MARGIN FRESH GP---------->
			<td rowspan=2 class="r" id="dept_pm_f_selling,{$rid}" title="{$col18}">{$dept_pm_FRESH_selling|number_format:2|ifzero:'-'}</td>

			{if $dept_acs_FRESH_selling ne 0 or $dept_acs_FRESH_selling ne ''}
				{assign var=pm_fgp value=$dept_pm_FRESH_selling/$dept_acs_FRESH_selling*100}
			{else}
			    {assign var=pm_fgp value=0}
			{/if}
			<td rowspan=2 class="r" id="dept_pm_f_gp,{$rid}" title="{$coladjgp}" {$background}>{$pm_fgp|number_format:2|ifzero:'-':$percent}</td>

			<!---------AVERAGE STOCK FRESH GP---------->
			<td rowspan=2 class="r" title="{$col19}">&nbsp;</td>

			<td rowspan=2 class="r" title="{$col20}">&nbsp;</td>
		</tr>
		<tr class="dept_fresh">
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
			<td class="r dept_idt_f_selling_{$rid}" id="dept_idt_f_selling,{$rid}" title="{$col12}">&nbsp;</td>
			<td class="r dept_acs_f_selling_{$rid}" id="dept_acs_f_selling,{$rid}" title="{$col15}">{$dept_acs_FRESH_selling|number_format:2|ifzero:'-'}</td>
			<td class="r" title="{$col16}">&nbsp;</td>
		</tr>
		</tbody>

	{/if}
{/if}
