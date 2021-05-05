{*
4/14/2011 10:19:23 AM Alex
- change Goods Receive Adjustment to GRN / GRA Adjustment

5/30/2011 5:20:43 PM Alex
- change stock take variance as (stock check - opening stock)
- the finalized row unable to edit data 

6/13/2011 12:05:14 PM Alex
- change stock take variance as (stock check - opening stock) => Fresh market

6/30/2011 10:42:58 AM Alex
- Turn days use 0 number format

7/15/2011 11:44:00 AM Alex
- remove % symbol in excel mode to avoid auto convert to numberic in microsoft excel

7/27/2011 10:46:30 AM Alex
- rename "GRN / GRA Adjustment" => "GRN / GRA Adjustment / Write Off"
- rename "Other Income" => "Other Income / Rebate"
- hide "Rebate"
- change calculation "(Average Opening Stock + Closing Stock) / 2"

8/2/2011 9:54:51 AM Alex
- add IBT name at GRN / GRA Adjustment / Write Off /

11/15/2011 3:12:51 PM Alex
- remove "Rebate"

12/7/2011 3:20:21 PM Alex
- add show previous month finalize status

2/8/2012 11:20:52 AM Alex
- add calculcation for stock check

3/5/2012 3:42:34 PM Alex
- add title for each column

3/7/2012 3:48:23 PM Alex
- add title for gp % and other column

3/12/2012 5:57:18 PM Alex
- add formula calculation

3/13/2012 10:13:39 AM Alex
- remove adj (%) column after actual sales

3/19/2012 3:59:10 PM Alex
- fix stock check data to compatible with previous cache

4/24/2012 11:36:28 AM Alex
- add note not allow unfinalize and unreview

4/26/2012 4:37:56 PM Alex
- add notify msg of user last finalize timestamp

3/24/2014 5:27 PM Justin
- Modified the wording from "Finalize" to "Finalise".

	{assign var=os_total_cost value=$os_OUTRIGHT_cost+$os_CONSIGN_cost+$os_FRESH_cost}
	{assign var=os_total_selling value=$os_OUTRIGHT_selling+$os_CONSIGN_selling+$os_FRESH_selling}
	{assign var=sr_total_cost value=$sr_OUTRIGHT_cost+$sr_CONSIGN_cost+$sr_FRESH_cost}
	{assign var=sr_total_selling value=$sr_OUTRIGHT_selling+$sr_CONSIGN_selling+$sr_FRESH_selling}
	{assign var=grn_total_cost value=$grn_OUTRIGHT_cost+$grn_CONSIGN_cost+$grn_FRESH_cost}
	{assign var=grn_total_selling value=$grn_OUTRIGHT_selling+$grn_CONSIGN_selling+$grn_FRESH_selling}
	{assign var=adj_total_cost value=$adj_OUTRIGHT_cost+$adj_CONSIGN_cost+$adj_FRESH_cost}
	{assign var=adj_total_selling value=$adj_OUTRIGHT_selling+$adj_CONSIGN_selling+$adj_FRESH_selling}
	{assign var=stv_total_cost value=$stv_OUTRIGHT_cost+$stv_CONSIGN_cost+$stv_FRESH_cost}
	{assign var=stv_total_selling value=$stv_OUTRIGHT_selling+$stv_CONSIGN_selling+$stv_FRESH_selling}
	{assign var=rs_total_cost value=$rs_OUTRIGHT_cost+$rs_CONSIGN_cost+$rs_FRESH_cost}
	{assign var=rs_total_selling value=$rs_OUTRIGHT_selling+$rs_CONSIGN_selling+$rs_FRESH_selling}
	{assign var=ts_total_cost value=$ts_OUTRIGHT_cost+$ts_CONSIGN_cost+$ts_FRESH_cost}
	{assign var=ts_total_selling value=$ts_OUTRIGHT_selling+$ts_CONSIGN_selling+$ts_FRESH_selling}
	{assign var=pa_total_selling value=$pa_OUTRIGHT_selling+$pa_CONSIGN_selling+$pa_FRESH_selling}
	{assign var=acs_total_cost value=$acs_OUTRIGHT_cost+$acs_CONSIGN_cost+$acs_FRESH_cost}
	{assign var=acs_total_selling value=$acs_OUTRIGHT_selling+$acs_CONSIGN_selling+$acs_FRESH_selling}

	{assign var=dept_os_total_cost value=$dept_os_OUTRIGHT_cost+$dept_os_CONSIGN_cost+$dept_os_FRESH_cost}
	{assign var=dept_os_total_selling value=$dept_os_OUTRIGHT_selling+$dept_os_CONSIGN_selling+$dept_os_FRESH_selling}
	{assign var=dept_sr_total_cost value=$dept_sr_OUTRIGHT_cost+$dept_sr_CONSIGN_cost+$dept_sr_FRESH_cost}
	{assign var=dept_sr_total_selling value=$dept_sr_OUTRIGHT_selling+$dept_sr_CONSIGN_selling+$dept_sr_FRESH_selling}
	{assign var=dept_grn_total_cost value=$dept_grn_OUTRIGHT_cost+$dept_grn_CONSIGN_cost+$dept_grn_FRESH_cost}
	{assign var=dept_grn_total_selling value=$dept_grn_OUTRIGHT_selling+$dept_grn_CONSIGN_selling+$dept_grn_FRESH_selling}
	{assign var=dept_adj_total_cost value=$dept_adj_OUTRIGHT_cost+$dept_adj_CONSIGN_cost+$dept_adj_FRESH_cost}
	{assign var=dept_adj_total_selling value=$dept_adj_OUTRIGHT_selling+$dept_adj_CONSIGN_selling+$dept_adj_FRESH_selling}
	{assign var=dept_stv_total_cost value=$dept_stv_OUTRIGHT_cost+$dept_stv_CONSIGN_cost+$dept_stv_FRESH_cost}
	{assign var=dept_stv_total_selling value=$dept_stv_OUTRIGHT_selling+$dept_stv_CONSIGN_selling+$dept_stv_FRESH_selling}
	{assign var=dept_rs_total_cost value=$dept_rs_OUTRIGHT_cost+$dept_rs_CONSIGN_cost+$dept_rs_FRESH_cost}
	{assign var=dept_rs_total_selling value=$dept_rs_OUTRIGHT_selling+$dept_rs_CONSIGN_selling+$dept_rs_FRESH_selling}
	{assign var=dept_ts_total_cost value=$dept_ts_OUTRIGHT_cost+$dept_ts_CONSIGN_cost+$dept_ts_FRESH_cost}
	{assign var=dept_ts_total_selling value=$dept_ts_OUTRIGHT_selling+$dept_ts_CONSIGN_selling+$dept_ts_FRESH_selling}
	{assign var=dept_pa_total_selling value=$dept_pa_OUTRIGHT_selling+$dept_pa_CONSIGN_selling+$dept_pa_FRESH_selling}
	{assign var=dept_acs_total_cost value=$dept_acs_OUTRIGHT_cost+$dept_acs_CONSIGN_cost+$dept_acs_FRESH_cost}
	{assign var=dept_acs_total_selling value=$dept_acs_OUTRIGHT_selling+$dept_acs_CONSIGN_selling+$dept_acs_FRESH_selling}
*}

{if $previous_finalized !==''}
	{if ($previous_finalized == 'F')}	<font color="Green">Previous month report had been finalised.</font>
	{elseif ($previous_finalized == 'N')}	<font color="Red">Previous month report haven't finalised.</font>
	{/if}
	<br />
{/if}

{if $created_timestamp}
	This report was generated at {$created_timestamp}<br />
{/if}

{if $recent_activity_msg}
	<font color="Blue">{$recent_activity_msg}</font> <br />
{/if}

{if !$can_unfinalize}
	<font color="Red">Note: *Next month report was once finalised. This month report is unable to unfinalise or unreview.</font> <br />
{/if}


{if $table}
<h2>{$report_title}</h2>
<span style="background-color:#ff0;border:1px solid black">&nbsp;&nbsp;&nbsp;&nbsp;</span> = Stock Take
<p>

{if $excel_mode}
	{assign var=background value="bgcolor='#CCCCCC'"}
{/if}
<table class="report_table" id="report_tbl">
<tr class="header">
	<th rowspan=3 width="100px">Department</th>
	<th>CP</th>
	<th rowspan=2>System <br>Stock Opening</th>	
	{assign var=til1 value="System Stock opening"}
	<th rowspan=3 {$background}>GP (%)</th>
	{assign var=tilgp value="GP (%)"}
	<th rowspan=2>Opening Stock</th>
	{assign var=til2 value="Opening Stock"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Stock Take<br>Variance</th>
	{assign var=til3 value="Stock Take variance"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2 nowrap>{if $stc_date}({$stc_date})<br />{/if}Stock<br />Check</th>
	{assign var=til4 value="Stock Check"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2 nowrap>Stocks<br />Before<br />Stock Check</th>
	{assign var=til5 value="Stocks Before Stock Check"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>({$opening_date})<br /> Actual Opening<br>Stock</th>
	{assign var=til6 value="Actual Opening Stock"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Good Received<br>(GRN)</th>
	{assign var=til7 value="Good Received (GRN)"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>GRN / GRA<br>Adjustment / Write Off / IBT</th>
	{assign var=til8 value="GRN / GRA / Adjustment / Write Off / IBT"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Adjustment<br>(ARMS)</th>
	{assign var=til9 value="Adjustment (ARMS)"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Actual<br>Stock</th>
	{assign var=til10 value="Actual Stock"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Return<br>Stock</th>
	{assign var=til11 value="Return Stock"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Inter Dept Transfer</th>
	{assign var=til12 value="Inter Dept Transfer"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Promotion<br>Amount</th>
	{assign var=til13 value="Promotion Amount"}
	<th rowspan=2 {$background}>(%)</th>
	{assign var=tilper value="(%)"}
	<th rowspan=2>Price Change Amount</th>
	{assign var=til14 value="Price Change Amount"}
	<th rowspan=2 {$background}>(%)</th>
	<th rowspan=2>Actual<br>Sales</th>
	{assign var=til15 value="Actual Sales"}
	<th rowspan=3 {$background}>GP (%)</th>
	{*<th class="hide" rowspan=2>Rebate<br>+/-</th>*}
	{*<th rowspan=3 {$background}>ADJ<br>GP (%)</th>*}
	{assign var=tiladjgp value="ADJ GP (%)"}
	<th rowspan=2>Closing<br>Stock</th>
	{assign var=til16 value="Closing Stock"}
	<th rowspan=3 {$background}>GP (%)</th>
	<th rowspan=2>Other Income / Rebate</th>
	{assign var=til17 value="Other Income / Rebate"}
	<th rowspan=2>Profit<br>Margin</th>
	{assign var=til18 value="Profit Margin"}
	<th rowspan=2 {$background}>ADJ<br>GP (%)</th>
	<th rowspan=2>Average<br>Stock</th>
	{assign var=til19 value="Average Stock"}
	<th rowspan=2>Turn<br>Days</th>
	{assign var=til20 value="Turn Days"}
</tr>
<tr class="header">
	<th>SP</th>
</tr>
<tr class="header">
	<th nowrap>&nbsp;</th>
	<th nowrap>A</th>
	<th nowrap>B</th>
	<th nowrap class="stock_take">C=D-B</th>
	<th nowrap class="stock_take">D</th>
	<th nowrap class="stock_take">E=F-D</th>
	<th nowrap>F=B / F=D+E</th>
	<th nowrap>G</th>
	<th nowrap>H</th>
	<th nowrap>I</th>
	<th nowrap>J=F+G+H+I</th>
	<th nowrap>K</th>
	<th nowrap>L</th>
	<th nowrap>M</th>
	<th nowrap>N=M / Q(selling)X100</th>
	<th nowrap>O</th>
	<th nowrap>P=O / Q(selling)X100</th>
	<th nowrap>Q</th>
	<th nowrap>R=J-K+L-Q</th>
	<th nowrap>S</th>
	<th nowrap>T=Q(selling)-Q(cost)+S</th>
	<th nowrap>U=T / Q(selling)</th>
	<th nowrap>V=[R(cost)+F(cost)] / 2</th>
	<th nowrap>W=V / Q(cost) X (days of month)</th>
</tr>




{assign var=d value=$days}
{assign var=got_fresh value=0}

{if !$excel_mode}
{assign var=percent value="%"}
{/if}

{foreach from=$r_dept key=rid item=root}

<tr class="dept_title">
    <td colspan=40>
        {if $privilege.REPORTS_CSA_REVIEW || $privilege.REPORTS_CSA_FINALIZE}
			<input class="left" type="checkbox" onchange="tick_dept(this,'{$rid}')"
			{if $main_reviewed.$rid} checked {/if}
			{if !$privilege.REPORTS_CSA_REVIEW || !$main_finalized.$rid || ($main_reviewed.$rid && !$can_unfinalize)} disabled {/if} >
		{/if}
		{if !$excel_mode}
			<img class="left" src="/ui/icons/bullet_purple.png" alt="{$root.r_descrip}"><b>{$root.r_descrip}</b>
		{/if}
	</td>
</tr>


{assign var=dept_got_fresh value=0}

	<!--------System OPENING STOCK----------->
	{assign var=dept_os_CONSIGN_cost value=0}
	{assign var=dept_os_CONSIGN_selling value=0}
	{assign var=dept_os_OUTRIGHT_cost value=0}
	{assign var=dept_os_OUTRIGHT_selling value=0}
	{assign var=t_os_FRESH_cost value=0}
	{assign var=t_os_FRESH_selling value=0}
	{assign var=dept_os_total_cost value=0}
	{assign var=dept_os_total_selling value=0}

	<!--------OPENING STOCK----------->
	{assign var=dept_cos_CONSIGN_cost value=0}
	{assign var=dept_cos_CONSIGN_selling value=0}
	{assign var=dept_cos_OUTRIGHT_cost value=0}
	{assign var=dept_cos_OUTRIGHT_selling value=0}
	{assign var=t_cos_FRESH_cost value=0}
	{assign var=t_cos_FRESH_selling value=0}
	{assign var=dept_cos_total_cost value=0}
	{assign var=dept_cos_total_selling value=0}

	<!--------STOCK TAKE VARIANCE----------->
	{assign var=dept_stv_CONSIGN_cost value=0}
	{assign var=dept_stv_CONSIGN_selling value=0}
	{assign var=dept_stv_OUTRIGHT_cost value=0}
	{assign var=dept_stv_OUTRIGHT_selling value=0}
	{assign var=t_stv_FRESH_cost value=0}
	{assign var=t_stv_FRESH_selling value=0}
	{assign var=dept_stv_total_cost value=0}
	{assign var=dept_stv_total_selling value=0}

	<!--------STOCK CHECK----------->
	{assign var=dept_stc_CONSIGN_cost value=0}
	{assign var=dept_stc_CONSIGN_selling value=0}
	{assign var=dept_stc_OUTRIGHT_cost value=0}
	{assign var=dept_stc_OUTRIGHT_selling value=0}
	{assign var=t_stc_FRESH_cost value=0}
	{assign var=t_stc_FRESH_selling value=0}
	{assign var=dept_stc_total_cost value=0}
	{assign var=dept_stc_total_selling value=0}
	
	<!--------CSA OPENING VARIANCE----------->
	{assign var=dept_cov_CONSIGN_cost value=0}
	{assign var=dept_cov_CONSIGN_selling value=0}
	{assign var=dept_cov_OUTRIGHT_cost value=0}
	{assign var=dept_cov_OUTRIGHT_selling value=0}
	{assign var=t_cov_FRESH_cost value=0}
	{assign var=t_cov_FRESH_selling value=0}
	{assign var=dept_cov_total_cost value=0}
	{assign var=dept_cov_total_selling value=0}
	
	<!--------ACTUAL OPENING STOCK----------->
	{assign var=dept_aos_CONSIGN_cost value=0}
	{assign var=dept_aos_CONSIGN_selling value=0}
	{assign var=dept_aos_OUTRIGHT_cost value=0}
	{assign var=dept_aos_OUTRIGHT_selling value=0}
	{assign var=t_aos_FRESH_cost value=0}
	{assign var=t_aos_FRESH_selling value=0}
	{assign var=dept_aos_total_cost value=0}
	{assign var=dept_aos_total_selling value=0}
	
	<!--------STOCK RECEIVE----------->
	{assign var=dept_sr_CONSIGN_cost value=0}
	{assign var=dept_sr_CONSIGN_selling value=0}
	{assign var=dept_sr_OUTRIGHT_cost value=0}
	{assign var=dept_sr_OUTRIGHT_selling value=0}
	{assign var=t_sr_FRESH_cost value=0}
	{assign var=t_sr_FRESH_selling value=0}
	{assign var=dept_sr_total_cost value=0}
	{assign var=dept_sr_total_selling value=0}

	<!--------GRN PENDING----------->
	{assign var=dept_grn_CONSIGN_cost value=0}
	{assign var=dept_grn_CONSIGN_selling value=0}
	{assign var=dept_grn_OUTRIGHT_cost value=0}
	{assign var=dept_grn_OUTRIGHT_selling value=0}
	{assign var=t_grn_FRESH_cost value=0}
	{assign var=t_grn_FRESH_selling value=0}
	{assign var=dept_grn_total_cost value=0}
	{assign var=dept_grn_total_selling value=0}

	<!--------ADJUSTMENT----------->
	{assign var=dept_adj_CONSIGN_cost value=0}
	{assign var=dept_adj_CONSIGN_selling value=0}
	{assign var=dept_adj_OUTRIGHT_cost value=0}
	{assign var=dept_adj_OUTRIGHT_selling value=0}
	{assign var=t_adj_FRESH_cost value=0}
	{assign var=t_adj_FRESH_selling value=0}
	{assign var=dept_adj_total_cost value=0}
	{assign var=dept_adj_total_selling value=0}

	<!--------ACTUAL STOCK----------->
	{assign var=dept_as_CONSIGN_cost value=0}
	{assign var=dept_as_CONSIGN_selling value=0}
	{assign var=dept_as_OUTRIGHT_cost value=0}
	{assign var=dept_as_OUTRIGHT_selling value=0}
	{assign var=t_as_FRESH_cost value=0}
	{assign var=t_as_FRESH_selling value=0}
	{assign var=dept_as_total_cost value=0}
	{assign var=dept_as_total_selling value=0}

	<!--------RETURN STOCK----------->
	{assign var=dept_rs_CONSIGN_cost value=0}
	{assign var=dept_rs_CONSIGN_selling value=0}
	{assign var=dept_rs_OUTRIGHT_cost value=0}
	{assign var=dept_rs_OUTRIGHT_selling value=0}
	{assign var=t_rs_FRESH_cost value=0}
	{assign var=t_rs_FRESH_selling value=0}
	{assign var=dept_rs_total_cost value=0}
	{assign var=dept_rs_total_selling value=0}

	<!--------IDT----------->
	{assign var=dept_idt_CONSIGN_cost value=0}
	{assign var=dept_idt_CONSIGN_selling value=0}
	{assign var=dept_idt_OUTRIGHT_cost value=0}
	{assign var=dept_idt_OUTRIGHT_selling value=0}
	{assign var=t_idt_FRESH_cost value=0}
	{assign var=t_idt_FRESH_selling value=0}
	{assign var=dept_idt_total_cost value=0}
	{assign var=dept_idt_total_selling value=0}
	
	<!--------Transfer STOCK----------->
	{assign var=dept_ts_CONSIGN_cost value=0}
	{assign var=dept_ts_CONSIGN_selling value=0}
	{assign var=dept_ts_OUTRIGHT_cost value=0}
	{assign var=dept_ts_OUTRIGHT_selling value=0}
	{assign var=t_ts_FRESH_cost value=0}
	{assign var=t_ts_FRESH_selling value=0}
	{assign var=dept_ts_total_cost value=0}
	{assign var=dept_ts_total_selling value=0}

	<!--------PROMOTION AMOUNT----------->
	{assign var=dept_pa_CONSIGN_selling value=0}
	{assign var=dept_pa_OUTRIGHT_selling value=0}
	{assign var=t_pa_FRESH_selling value=0}
	{assign var=dept_pa_total_selling value=0}

	<!--------PRICE CHANGE AMOUNT----------->
	{assign var=dept_pca_CONSIGN_selling value=0}
	{assign var=dept_pca_OUTRIGHT_selling value=0}
	{assign var=t_pca_FRESH_selling value=0}
	{assign var=dept_pca_total_selling value=0}

	<!--------ACTUAL Sales----------->
	{assign var=dept_acs_CONSIGN_cost value=0}
	{assign var=dept_acs_CONSIGN_selling value=0}
	{assign var=dept_acs_OUTRIGHT_cost value=0}
	{assign var=dept_acs_OUTRIGHT_selling value=0}
	{assign var=t_acs_FRESH_cost value=0}
	{assign var=t_acs_FRESH_selling value=0}
	{assign var=dept_acs_total_cost value=0}
	{assign var=dept_acs_total_selling value=0}

	<!--------REBATE----------->
	{assign var=dept_r_CONSIGN_selling value=0}
	{assign var=dept_r_OUTRIGHT_selling value=0}
	{assign var=t_r_FRESH_selling value=0}
	{assign var=dept_r_total_selling value=0}

	<!--------CLOSING STOCK----------->
	{assign var=dept_cs_CONSIGN_cost value=0}
	{assign var=dept_cs_CONSIGN_selling value=0}
	{assign var=dept_cs_OUTRIGHT_cost value=0}
	{assign var=dept_cs_OUTRIGHT_selling value=0}
	{assign var=t_cs_FRESH_cost value=0}
	{assign var=t_cs_FRESH_selling value=0}
	{assign var=dept_cs_total_cost value=0}
	{assign var=dept_cs_total_selling value=0}

	<!--------OTHER INCOME----------->
	{assign var=dept_oi_CONSIGN_selling value=0}
	{assign var=dept_oi_OUTRIGHT_selling value=0}
	{assign var=t_oi_FRESH_selling value=0}
	{assign var=dept_oi_total_selling value=0}

	<!--------PROFIT MARGIN----------->
	{assign var=dept_pm_CONSIGN_selling value=0}
	{assign var=dept_pm_OUTRIGHT_selling value=0}
	{assign var=t_pm_FRESH_selling value=0}
	{assign var=dept_pm_total_selling value=0}

	<!--------AVERAGE STOCK----------->
	{assign var=dept_av_OUTRIGHT_selling value=0}
	{assign var=t_av_FRESH_selling value=0}
	{assign var=dept_av_total_selling value=0}

	<!--------TURN DAYS----------->
	{assign var=dept_td_OUTRIGHT_selling value=0}
	{assign var=t_td_FRESH_selling value=0}
	{assign var=dept_td_total_selling value=0}

	{foreach from=$c_dept.$rid key=cid item=child}
	
		<!--------RESET INPUT DATA----------->

		<!--------GRN ADJUSTMENT PRICE----------->
		{assign var=grn_OUTRIGHT_cost value=0}
		{assign var=grn_OUTRIGHT_selling value=0}
		{assign var=grn_CONSIGN_cost value=0}
		{assign var=grn_CONSIGN_selling value=0}

		<!--------INTER DEPT TRANSFER PRICE----------->
		{assign var=idt_OUTRIGHT_cost value=0}
		{assign var=idt_OUTRIGHT_selling value=0}
		{assign var=idt_CONSIGN_cost value=0}
		{assign var=idt_CONSIGN_selling value=0}

		<!--------ACTUAL SALES CONSIGN PRICE----------->
		{assign var=acs_CONSIGN_cost value=0}
		{assign var=acs_CONSIGN_selling value=0}

		<!--------REBATE PRICE----------->
		{assign var=r_OUTRIGHT_selling value=0}
		{assign var=r_CONSIGN_selling value=0}

		<!--------OTHER INCOME PRICE----------->
		{assign var=oi_OUTRIGHT_selling value=0}
		{assign var=oi_CONSIGN_selling value=0}

		<!--------INPUT DATA----------->

		{foreach from=$vendor.$cid key=vid item=v}
			<!--------GRN ADJUSTMENT PRICE----------->
			{assign var=grn_OUTRIGHT_cost value=$grn_OUTRIGHT_cost+$vgrn.$cid.$vid.OUTRIGHT.cost_price}
			{assign var=grn_OUTRIGHT_selling value=$grn_OUTRIGHT_selling+$vgrn.$cid.$vid.OUTRIGHT.selling_price}
			{assign var=grn_CONSIGN_cost value=$grn_CONSIGN_cost+$vgrn.$cid.$vid.CONSIGN.cost_price}
			{assign var=grn_CONSIGN_selling value=$grn_CONSIGN_selling+$vgrn.$cid.$vid.CONSIGN.selling_price}
			
			<!--------INTER DEPT TRANSFER PRICE----------->
			{assign var=idt_OUTRIGHT_cost value=$idt_OUTRIGHT_cost+$vidt.$cid.$vid.OUTRIGHT.cost_price}
			{assign var=idt_OUTRIGHT_selling value=$idt_OUTRIGHT_selling+$vidt.$cid.$vid.OUTRIGHT.selling_price}
			{assign var=idt_CONSIGN_cost value=$idt_CONSIGN_cost+$vidt.$cid.$vid.CONSIGN.cost_price}
			{assign var=idt_CONSIGN_selling value=$idt_CONSIGN_selling+$vidt.$cid.$vid.CONSIGN.selling_price}

			<!--------ACTUAL SALES CONSIGN PRICE----------->
			{assign var=vacs_CONSIGN_selling value=$vacs.$cid.$vid.CONSIGN.selling_price}
			{assign var=vacs_cgp value=$vacs.$cid.$vid.CONSIGN.gp}

			{if $vacs_CONSIGN_selling eq 0 or $vacs_CONSIGN_selling eq ''}
				{assign var=vacs_CONSIGN_selling value=0}
			{/if}

			{assign var=vacs_CONSIGN_cost value=$vacs_cgp/100*$vacs_CONSIGN_selling}
			{assign var=vacs_CONSIGN_cost value=$vacs_CONSIGN_selling-$vacs_CONSIGN_cost}

			{assign var=acs_CONSIGN_cost value=$acs_CONSIGN_cost+$vacs_CONSIGN_cost}

			<!--------REBATE PRICE----------->
			{assign var=r_OUTRIGHT_selling value=$r_OUTRIGHT_selling+$vr.$cid.$vid.OUTRIGHT.selling_price}
			{assign var=r_CONSIGN_selling value=$r_CONSIGN_selling+$vr.$cid.$vid.CONSIGN.selling_price}

			<!--------OTHER INCOME PRICE----------->
			{assign var=oi_OUTRIGHT_selling value=$oi_OUTRIGHT_selling+$voi.$cid.$vid.OUTRIGHT.selling_price}
			{assign var=oi_CONSIGN_selling value=$oi_CONSIGN_selling+$voi.$cid.$vid.CONSIGN.selling_price}

	    {/foreach}

		<!--------SYSTEM OPENING STOCK PRICE----------->
		{assign var=os_OUTRIGHT_cost value=$os.$cid.OUTRIGHT.cost_price}
		{assign var=os_OUTRIGHT_selling value=$os.$cid.OUTRIGHT.selling_price}
		{assign var=os_CONSIGN_cost value=$os.$cid.CONSIGN.cost_price}
		{assign var=os_CONSIGN_selling value=$os.$cid.CONSIGN.selling_price}

		<!--------OPENING STOCK PRICE----------->
		{assign var=cos_OUTRIGHT_cost value=$cos.$cid.OUTRIGHT.cost_price}
		{assign var=cos_OUTRIGHT_selling value=$cos.$cid.OUTRIGHT.selling_price}
		{assign var=cos_CONSIGN_cost value=$cos.$cid.CONSIGN.cost_price}
		{assign var=cos_CONSIGN_selling value=$cos.$cid.CONSIGN.selling_price}

		<!--------STOCK TAKE VARIANCE PRICE----------->
		{*
		{if $stv.$cid.OUTRIGHT}	
			{assign var=stv_OUTRIGHT_cost value=$os_OUTRIGHT_cost-$cos_OUTRIGHT_cost}
			{assign var=stv_OUTRIGHT_selling value=$os_OUTRIGHT_selling-$cos_OUTRIGHT_selling}
			{assign var=stv_CONSIGN_cost value=$os_CONSIGN_cost-$cos_CONSIGN_cost}
			{assign var=stv_CONSIGN_selling value=$os_CONSIGN_selling-$os_CONSIGN_selling}
		{else}
			{assign var=stv_OUTRIGHT_cost value=0}
			{assign var=stv_OUTRIGHT_selling value=0}
			{assign var=stv_CONSIGN_cost value=0}
			{assign var=stv_CONSIGN_selling value=0}
		{/if}
		*}

		{assign var=stv_OUTRIGHT_cost value=$stv.$cid.OUTRIGHT.cost_price}
		{assign var=stv_OUTRIGHT_selling value=$stv.$cid.OUTRIGHT.selling_price}
		{assign var=stv_CONSIGN_cost value=$stv.$cid.CONSIGN.cost_price}
		{assign var=stv_CONSIGN_selling value=$stv.$cid.CONSIGN.selling_price}

		<!--------STOCK CHECK PRICE----------->
		{if $stc_date || $stv_OUTRIGHT_cost != '' || $stv_OUTRIGHT_selling != ''}
			{assign var=stc_OUTRIGHT_cost value=$cos_OUTRIGHT_cost+$stv_OUTRIGHT_cost}
			{assign var=stc_OUTRIGHT_selling value=$cos_OUTRIGHT_selling+$stv_OUTRIGHT_selling}
			{assign var=stc_CONSIGN_cost value=$cos_CONSIGN_cost+$stv_CONSIGN_cost}
			{assign var=stc_CONSIGN_selling value=$cos_CONSIGN_selling+$stv_CONSIGN_selling}
		{/if}
		
		<!--------CSA OPENING VARIANCE----------->
		{assign var=cov_OUTRIGHT_cost value=$cov.$cid.OUTRIGHT.cost_price}
		{assign var=cov_OUTRIGHT_selling value=$cov.$cid.OUTRIGHT.selling_price}
		{*assign var=cov_CONSIGN_cost value=$cov.$cid.CONSIGN.cost_price}
		{assign var=cov_CONSIGN_selling value=$cov.$cid.CONSIGN.selling_price*}

		<!--------ACTUAL OPENING STOCK PRICE----------->
		{assign var=aos_OUTRIGHT_cost value=$cos_OUTRIGHT_cost+$stv_OUTRIGHT_cost+$cov_OUTRIGHT_cost}
		{assign var=aos_OUTRIGHT_selling value=$cos_OUTRIGHT_selling+$stv_OUTRIGHT_selling+$cov_OUTRIGHT_selling}
		{*assign var=aos_CONSIGN_cost value=$cos_CONSIGN_cost+$stv_CONSIGN_cost+$cov_CONSIGN_cost}
		{assign var=aos_CONSIGN_selling value=$cos_CONSIGN_selling+$stv_CONSIGN_selling+$cov_CONSIGN_selling*}

		<!--------STOCK RECEIVE PRICE----------->
		{assign var=sr_OUTRIGHT_cost value=$sr.$cid.OUTRIGHT.cost_price}
		{assign var=sr_OUTRIGHT_selling value=$sr.$cid.OUTRIGHT.selling_price}
		{*assign var=sr_CONSIGN_cost value=$sr.$cid.CONSIGN.cost_price}
		{assign var=sr_CONSIGN_selling value=$sr.$cid.CONSIGN.selling_price*}

		<!--------ADJUSTMENT PRICE----------->
		{assign var=adj_OUTRIGHT_cost value=$adj.$cid.OUTRIGHT.cost_price}
		{assign var=adj_OUTRIGHT_selling value=$adj.$cid.OUTRIGHT.selling_price}
		{assign var=adj_CONSIGN_cost value=$adj.$cid.CONSIGN.cost_price}
		{assign var=adj_CONSIGN_selling value=$adj.$cid.CONSIGN.selling_price}

		<!--------ACTUAL STOCK PRICE----------->
		{assign var=as_OUTRIGHT_cost value=$aos_OUTRIGHT_cost+$sr_OUTRIGHT_cost+$grn_OUTRIGHT_cost+$adj_OUTRIGHT_cost}
		{assign var=as_OUTRIGHT_selling value=$aos_OUTRIGHT_selling+$sr_OUTRIGHT_selling+$grn_OUTRIGHT_selling+$adj_OUTRIGHT_selling}
		{assign var=as_CONSIGN_cost value=$aos_CONSIGN_cost+$sr_CONSIGN_cost+$grn_CONSIGN_cost+$adj_CONSIGN_cost}
		{assign var=as_CONSIGN_selling value=$aos_CONSIGN_selling+$sr_CONSIGN_selling+$grn_CONSIGN_selling+$adj_CONSIGN_selling}

		<!--------RETURN STOCK PRICE----------->
		{assign var=rs_OUTRIGHT_cost value=$rs.$cid.OUTRIGHT.cost_price}
		{assign var=rs_OUTRIGHT_selling value=$rs.$cid.OUTRIGHT.selling_price}
		{assign var=rs_CONSIGN_cost value=$rs.$cid.CONSIGN.cost_price}
		{assign var=rs_CONSIGN_selling value=$rs.$cid.CONSIGN.selling_price}

		<!--------TRANSFER STOCK PRICE----------->
		{assign var=ts_OUTRIGHT_cost value=$ts.$cid.OUTRIGHT.cost_price}
		{assign var=ts_OUTRIGHT_selling value=$ts.$cid.OUTRIGHT.selling_price}
		{assign var=ts_CONSIGN_cost value=$ts.$cid.CONSIGN.cost_price}
		{assign var=ts_CONSIGN_selling value=$ts.$cid.CONSIGN.selling_price}

		<!--------PROMOTION AMOUNT PRICE----------->
		{assign var=pa_OUTRIGHT_selling value=$pa.$cid.OUTRIGHT.selling_price}
		{assign var=pa_CONSIGN_selling value=$pa.$cid.CONSIGN.selling_price}

		<!--------PRICE CHANGE AMOUNT PRICE----------->
		{assign var=pca_OUTRIGHT_selling value=$pca.$cid.OUTRIGHT.selling_price}
		{* assign var=pca_CONSIGN_selling value=$pca.$cid.CONSIGN.selling_price *}

		<!--------ACTUAL SALES PRICE----------->
		{assign var=acs_OUTRIGHT_cost value=$acs.$cid.OUTRIGHT.cost_price}
		{assign var=acs_OUTRIGHT_selling value=$acs.$cid.OUTRIGHT.selling_price}

		{assign var=acs_CONSIGN_selling value=$acs.$cid.CONSIGN.selling_price}

		<!--------CLOSING STOCK PRICE----------->
		{assign var=cs_OUTRIGHT_cost value=$as_OUTRIGHT_cost-$rs_OUTRIGHT_cost+$idt_OUTRIGHT_cost-$acs_OUTRIGHT_cost}
		{assign var=cs_OUTRIGHT_selling value=$as_OUTRIGHT_selling-$rs_OUTRIGHT_selling+$idt_OUTRIGHT_selling-$pa_OUTRIGHT_selling-$pca_OUTRIGHT_selling-$acs_OUTRIGHT_selling+$r_OUTRIGHT_selling}

		<!--------PROFIT MARGIN PRICE----------->
		{assign var=pm_OUTRIGHT_selling value=$acs_OUTRIGHT_selling-$acs_OUTRIGHT_cost+$r_OUTRIGHT_selling+$oi_OUTRIGHT_selling}
		{assign var=pm_CONSIGN_selling value=$acs_CONSIGN_selling-$acs_CONSIGN_cost+$r_CONSIGN_selling+$oi_CONSIGN_selling}

		<!--------AVERAGE STOCK PRICE----------->
		{assign var=av_OUTRIGHT_selling value=$aos_OUTRIGHT_cost+$cs_OUTRIGHT_cost}
		{assign var=av_OUTRIGHT_selling value=$av_OUTRIGHT_selling/2}

		<!--------TURN DAYS PRICE----------->
		{if $acs_OUTRIGHT_cost ne 0 or $acs_OUTRIGHT_cost ne ''}
			{assign var=td_OUTRIGHT_selling value=$av_OUTRIGHT_selling/$acs_OUTRIGHT_cost*$d}
		{else}
		    {assign var=td_OUTRIGHT_selling value=0}
		{/if}

											<!--------FRESH MARKET WEIGHT----------->
		<!--------SYSTEM OPENING STOCK PRICE----------->
		{assign var=os_FRESH_selling value=$fos.$cid.FRESH.selling_price}

		<!--------OPENING STOCK PRICE----------->
		{assign var=cos_FRESH_selling value=$fcos.$cid.FRESH.selling_price}

		<!--------STOCK TAKE VARIANCE PRICE----------->
		{*
		{if $fstv.$cid.FRESH}
			{assign var=stv_FRESH_cost value=$os_FRESH_cost-$cos_FRESH_cost}
			{assign var=stv_FRESH_selling value=$os_FRESH_selling-$cos_FRESH_selling}
		{else}
			{assign var=stv_FRESH_cost value=0}
			{assign var=stv_FRESH_selling value=0}
		{/if}

		{assign var=stv_FRESH_selling value=$fstv.$cid.FRESH.selling_price}
		*}
		
		{assign var=stv_FRESH_cost value=$fstv.$cid.FRESH.cost_price}
		{assign var=stv_FRESH_selling value=$fstv.$cid.FRESH.selling_price}

		<!--------STOCK CHECK PRICE----------->
		{assign var=stc_FRESH_selling value=$cos_FRESH_selling+$stv_FRESH_selling}

		<!--------CSA OPENING VARIANCE PRICE----------->
		{assign var=cov_FRESH_selling value=$fcov.$cid.FRESH.selling_price}
				
		<!--------ACTUAL OPENING STOCK PRICE----------->
		{assign var=aos_FRESH_selling value=$cos_FRESH_selling+$stv_FRESH_selling}

		<!--------STOCK RECEIVE PRICE----------->
		{assign var=sr_FRESH_selling value=$fsr.$cid.FRESH.selling_price}

		<!--------GRN ADJUSTMENT PRICE----------->
		{assign var=grn_FRESH_selling value=$fgrn.$cid.FRESH.selling_price}

		<!--------ADJUSTMENT PRICE----------->
		{assign var=adj_FRESH_selling value=$fadj.$cid.FRESH.selling_price}

		<!--------ACTUAL STOCK PRICE----------->
		{assign var=as_FRESH_selling value=$aos_FRESH_selling+$sr_FRESH_selling+$grn_FRESH_selling+$adj_FRESH_selling}

		<!--------RETURN STOCK PRICE----------->
		{assign var=rs_FRESH_selling value=$frs.$cid.FRESH.selling_price}

		<!--------IDT PRICE----------->
		{assign var=idt_FRESH_selling value=$fidt.$cid.FRESH.selling_price}

		<!--------PROMOTION AMOUNT PRICE----------->
		{assign var=pa_FRESH_selling value=$fpa.$cid.FRESH.selling_price}

		<!--------PRICE CHANGE AMOUNT PRICE----------->
		{assign var=pca_FRESH_selling value=$fpca.$cid.FRESH.selling_price}

		<!--------CLOSING STOCK PRICE----------->
	    {assign var=cs_FRESH_selling value=$fcs.$cid.FRESH.selling_price}

		<!--------ACTUAL SALES PRICE----------->
		{assign var=acs_FRESH_selling value=$facs.$cid.FRESH.selling_price}

		{assign var=acs_FRESH_gp value=$acs_FRESH_selling-$aos_FRESH_selling-$sr_FRESH_selling-$grn_FRESH_selling+$cs_FRESH_selling}
		{if $acs_FRESH_selling ne 0 or $acs_FRESH_selling ne ''}
			{assign var=acs_FRESH_gp value=$acs_FRESH_gp/$acs_FRESH_selling*100 }
		{else}
			{assign var=acs_FRESH_gp value=0 }
		{/if}
		{assign var=acs_FRESH_cost value=100-$acs_FRESH_gp}
		{assign var=acs_FRESH_cost value=$acs_FRESH_selling*$acs_FRESH_cost/100}

		<!--------REBATE PRICE----------->
		{assign var=r_FRESH_selling value=$fr.$cid.FRESH.selling_price}

		<!--------OTHER INCOME PRICE----------->
		{assign var=oi_FRESH_selling value=$foi.$cid.FRESH.selling_price}

		<!--------PROFIT MARGIN PRICE----------->
		{assign var=pm_FRESH_selling value=$acs_FRESH_selling-$acs_FRESH_cost+$r_FRESH_selling+$oi_FRESH_selling}

		<!--------AVERAGE STOCK PRICE----------->
		{assign var=av_FRESH_selling value=$aos_FRESH_selling+$cs_FRESH_selling}
		{assign var=av_FRESH_selling value=$av_FRESH_selling/2}

		<!--------TURN DAYS PRICE----------->
		{if $acs_FRESH_cost ne 0 or $acs_FRESH_cost ne ''}
			{assign var=td_FRESH_selling value=$av_FRESH_selling/$acs_FRESH_cost*$d}
		{else}
		    {assign var=td_FRESH_selling value=0}
		{/if}

		<!---------Disable input------------------------>
        {if $open_mode eq 'view' || $finaliz.$rid.$cid || $reviewed.$rid.$cid || (!$dept_confirm.$rid.$cid && $privilege.REPORTS_CSA_FINALIZE && !$privilege.REPORTS_CSA_CONFIRM) || ($dept_confirm.$rid.$cid && $privilege.REPORTS_CSA_CONFIRM && !$privilege.REPORTS_CSA_FINALIZE)}
			{assign var=disable_input value="true"}
		{else}
		    {assign var=disable_input value=""}
		{/if}

		{* if $os_OUTRIGHT_cost and $os_OUTRIGHT_selling or $sr_OUTRIGHT_cost or $sr_OUTRIGHT_selling or $grn_OUTRIGHT_cost or
			$grn_OUTRIGHT_selling or $adj_OUTRIGHT_cost or $adj_OUTRIGHT_selling or $stv_OUTRIGHT_cost or $stv_OUTRIGHT_selling or
			$rs_OUTRIGHT_cost or $rs_OUTRIGHT_selling or $pa_OUTRIGHT_selling or $acs_OUTRIGHT_cost or $acs_OUTRIGHT_selling *}
   		<tbody id="dept_scroll_{$cid}" class="dept_outright_{$rid}">
		<!-------------------------DEPARTMENT OUTRIGHT------------------------------>
		<tr class="outright">
			<th rowspan=2>
				{if $check_vendor.$cid.OUTRIGHT && !$excel_mode}
					<img id='exp_col_o_{$cid}' align="left" src="/ui/expand.gif" onclick="show_vendor(this,'outright_{$cid}')">
				{/if}
				{assign var=dept_desc value=$child.c_descrip}
				{$dept_desc}<br />(Outright)
				{assign var=dept_title value="Department: $dept_desc"}
			</th>
			{* set title for each column*}
			{assign var=col1 value="$til1|$dept_title"}
			{assign var=col2 value="$til2|$dept_title"}
			{assign var=col3 value="$til3|$dept_title"}
			{assign var=col4 value="$til4|$dept_title"}
			{assign var=col5 value="$til5|$dept_title"}
			{assign var=col6 value="$til6|$dept_title"}
			{assign var=col7 value="$til7|$dept_title"}
			{assign var=col8 value="$til8|$dept_title"}
			{assign var=col9 value="$til9|$dept_title"}
			{assign var=col10 value="$til10|$dept_title"}
			{assign var=col11 value="$til11|$dept_title"}
			{assign var=col12 value="$til12|$dept_title"}
			{assign var=col13 value="$til13|$dept_title"}
			{assign var=col14 value="$til14|$dept_title"}
			{assign var=col15 value="$til15|$dept_title"}
			{assign var=col16 value="$til16|$dept_title"}
			{assign var=col17 value="$til17|$dept_title"}
			{assign var=col18 value="$til18|$dept_title"}
			{assign var=col19 value="$til19|$dept_title"}
			{assign var=col20 value="$til20|$dept_title"}
			{assign var=colgp value="$tilgp|$dept_title"}
			{assign var=colper value="$tilper|$dept_title"}
			{assign var=coladjgp value="$tiladjgp|$dept_title"}
			
			<th>CP</th>
			<!---------SYSTEM OPENING STOCK OUTRIGHT GP---------->
			<td class="r" id="os_o_cost,{$cid}" title="{$col1}">{$os_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $os_OUTRIGHT_selling ne 0 or $os_OUTRIGHT_selling ne ''}
			    {assign var=os_gp value=$os_OUTRIGHT_selling-$os_OUTRIGHT_cost}
				{assign var=os_gp value=$os_gp/$os_OUTRIGHT_selling*100}
			{else}
			    {assign var=os_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="os_o_gp,{$cid}" title="{$colgp}" {$background}>{$os_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------OPENING STOCK OUTRIGHT GP---------->
			<td class="r" id="cos_o_cost,{$cid}" title="{$col2}">{$cos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $cos_OUTRIGHT_selling ne 0 or $cos_OUTRIGHT_selling ne ''}
			    {assign var=cos_gp value=$cos_OUTRIGHT_selling-$cos_OUTRIGHT_cost}
				{assign var=cos_gp value=$cos_gp/$cos_OUTRIGHT_selling*100}
			{else}
			    {assign var=cos_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="cos_o_gp,{$cid}" title="{$colgp}" {$background}>{$cos_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------STOCK TAKE VARIANCE OUTRIGHT GP---------->
			<td class="r" id="stv_o_cost,{$cid}" title="{$col3}">{$stv_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $stv_OUTRIGHT_selling ne 0 or $stv_OUTRIGHT_selling ne ''}
			    {assign var=stv_gp value=$stv_OUTRIGHT_selling-$stv_OUTRIGHT_cost}
				{assign var=stv_gp value=$stv_gp/$stv_OUTRIGHT_selling*100}
			{else}
			    {assign var=stv_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="stv_o_gp,{$cid}" title="{$colgp}" {$background}>{$stv_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------STOCK CHECK OUTRIGHT GP---------->
			<td class="r" title="{$col4}">{$stc_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $stc_OUTRIGHT_selling ne 0 or $stc_OUTRIGHT_selling ne ''}
			    {assign var=stc_gp value=$stc_OUTRIGHT_selling-$stc_OUTRIGHT_cost}
				{assign var=stc_gp value=$stc_gp/$stc_OUTRIGHT_selling*100}
			{else}
			    {assign var=stc_gp value=0}
			{/if}
			<td rowspan=2 class="r" title="{$colgp}" {$background}>{$stc_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------CSA OPENING VARIANCE OUTRIGHT GP---------->
			<td class="r" title="{$col5}">{$cov_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $cov_OUTRIGHT_selling ne 0 or $cov_OUTRIGHT_selling ne ''}
			    {assign var=cov_gp value=$cov_OUTRIGHT_selling-$cov_OUTRIGHT_cost}
				{assign var=cov_gp value=$cov_gp/$cov_OUTRIGHT_selling*100}
			{else}
			    {assign var=cov_gp value=0}
			{/if}
			<td rowspan=2 class="r" title="{$colgp}" {$background}>{$cov_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL OPENING STOCK OUTRIGHT GP---------->
			<td class="r" id="aos_o_cost,{$cid}" title="{$col6}">{$aos_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>
            
			{if $aos_OUTRIGHT_selling ne 0 or $aos_OUTRIGHT_selling ne ''}
			    {assign var=aos_gp value=$aos_OUTRIGHT_selling-$aos_OUTRIGHT_cost}
				{assign var=aos_gp value=$aos_gp/$aos_OUTRIGHT_selling*100}
			{else}
			    {assign var=aos_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="aos_o_gp,{$cid}" title="{$colgp}" {$background}>{$aos_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------STOCK RECEIVE OUTRIGHT GP---------->
			<td class="r" id="sr_o_cost,{$cid}" title="{$col7}">{$sr_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $sr_OUTRIGHT_selling ne 0 or $sr_OUTRIGHT_selling ne ''}
			    {assign var=sr_gp value=$sr_OUTRIGHT_selling-$sr_OUTRIGHT_cost}
				{assign var=sr_gp value=$sr_gp/$sr_OUTRIGHT_selling*100}
			{else}
			    {assign var=sr_gp value=0}
			{/if}
			<td rowspan=2 class="r" title="{$colgp}" id="sr_o_gp,{$cid}" {$background}>{$sr_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------GRN ADJUSTMENT OUTRIGHT GP---------->
			<td class="r grn_o_cost_{$rid}" id="grn_o_cost,{$cid}" title="{$col8}">{$grn_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $grn_OUTRIGHT_selling ne 0 or $grn_OUTRIGHT_selling ne ''}
			    {assign var=grn_gp value=$grn_OUTRIGHT_selling-$grn_OUTRIGHT_cost}
				{assign var=grn_gp value=$grn_gp/$grn_OUTRIGHT_selling*100}
			{else}
			    {assign var=grn_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="grn_o_gp,{$cid}" title="{$colgp}" {$background}>{$grn_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ADJUSTMENT OUTRIGHT GP---------->
			<td class="r" id="adj_o_cost,{$cid}" title="{$col9}">{$adj_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $adj_OUTRIGHT_selling ne 0 or $adj_OUTRIGHT_selling ne ''}
			    {assign var=adj_gp value=$adj_OUTRIGHT_selling-$adj_OUTRIGHT_cost}
				{assign var=adj_gp value=$adj_gp/$adj_OUTRIGHT_selling*100}
			{else}
			    {assign var=adj_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="adj_o_gp,{$cid}" title="{$colgp}" {$background}>{$adj_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL STOCK OUTRIGHT GP---------->
			<td class="r as_o_cost_{$rid}" id="as_o_cost,{$cid}" title="{$col10}">{$as_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $as_OUTRIGHT_selling ne 0 or $as_OUTRIGHT_selling ne ''}
			    {assign var=as_gp value=$as_OUTRIGHT_selling-$as_OUTRIGHT_cost}
				{assign var=as_gp value=$as_gp/$as_OUTRIGHT_selling*100}
			{else}
			    {assign var=as_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="as_o_gp,{$cid}" title="{$colgp}" {$background}>{$as_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------RETURN STOCK OUTRIGHT GP---------->
			<td class="r" id="rs_o_cost,{$cid}" title="{$col11}">{$rs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $rs_OUTRIGHT_selling ne 0 or $rs_OUTRIGHT_selling ne ''}
			    {assign var=rs_gp value=$rs_OUTRIGHT_selling-$rs_OUTRIGHT_cost}
				{assign var=rs_gp value=$rs_gp/$rs_OUTRIGHT_selling*100}
			{else}
			    {assign var=rs_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="rs_o_gp,{$cid}" title="{$colgp}" {$background}>{$rs_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------TRANSFER STOCK OUTRIGHT GP---------->
{*			<td class="r" id="ts_o_cost,{$cid}">{$ts_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $ts_OUTRIGHT_selling ne 0 or $ts_OUTRIGHT_selling ne ''}
			    {assign var=ts_gp value=$ts_OUTRIGHT_selling-$ts_OUTRIGHT_cost}
				{assign var=ts_gp value=$ts_gp/$ts_OUTRIGHT_selling*100}
			{else}
			    {assign var=ts_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="ts_o_gp,{$cid}" {$background}>{$ts_gp|number_format:2|ifzero:'-':$percent}</td>    *}

			<!---------IDT OUTRIGHT GP---------->
			<td class="r idt_o_cost_{$rid}" id="idt_o_cost,{$cid}" title="{$col12}">{$idt_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $idt_OUTRIGHT_selling ne 0 or $idt_OUTRIGHT_selling ne ''}
			    {assign var=idt_gp value=$idt_OUTRIGHT_selling-$idt_OUTRIGHT_cost}
				{assign var=idt_gp value=$idt_gp/$idt_OUTRIGHT_selling*100}
			{else}
			    {assign var=idt_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="idt_o_gp,{$cid}" title="{$colgp}" {$background}>{$idt_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PROMOTION AMOUNT OUTRIGHT GP---------->
			<td rowspan=2 class="r" id="pa_o_selling,{$cid}" title="{$col13}">{$pa_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_OUTRIGHT_selling ne 0 or $acs_OUTRIGHT_selling ne ''}
				{assign var=pa_gp value=$pa_OUTRIGHT_selling/$acs_OUTRIGHT_selling*100}
			{else}
			    {assign var=pa_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pa_o_gp,{$cid}" title="{$colper}" {$background}>{$pa_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PRICE CHANGE AMOUNT OUTRIGHT GP---------->
			<td rowspan=2 class="r" id="pca_o_selling,{$cid}" title="{$col14}">{$pca_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_OUTRIGHT_selling ne 0 or $acs_OUTRIGHT_selling ne ''}
				{assign var=pca_gp value=$pca_OUTRIGHT_selling/$acs_OUTRIGHT_selling*100}
			{else}
			    {assign var=pca_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pca_o_gp,{$cid}" title="{$colper}" {$background}>{$pca_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL SALES OUTRIGHT GP---------->
			<td class="r" id="acs_o_cost,{$cid}" title="{$col15}">{$acs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>

			{if $acs_OUTRIGHT_selling ne 0 or $acs_OUTRIGHT_selling ne ''}
			    {assign var=acs_gp value=$acs_OUTRIGHT_selling-$acs_OUTRIGHT_cost}
				{assign var=acs_gp value=$acs_gp/$acs_OUTRIGHT_selling*100}
			{else}
			    {assign var=acs_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="acs_o_gp,{$cid}" title="{$colgp}" {$background}>{$acs_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------REBATE OUTRIGHT GP---------->
			{*<td rowspan=2 class="r hide r_o_selling_{$rid}" id="r_o_selling,{$cid}">{$r_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>*}
			{*
			{if $acs_OUTRIGHT_selling ne 0 or $acs_OUTRIGHT_selling ne ''}
			    {assign var=r_gp value=$acs_OUTRIGHT_selling-$acs_OUTRIGHT_cost+$r_OUTRIGHT_selling}
				{assign var=r_gp value=$r_gp/$acs_OUTRIGHT_selling*100}
			{else}
			    {assign var=r_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="r_o_gp,{$cid}" title="{$coladjgp}" {$background}>{$r_gp|number_format:2|ifzero:'-':$percent}</td>
			*}
			<!---------CLOSING STOCK OUTRIGHT GP---------->
			<td class="r" id="cs_o_cost,{$cid}" title="{$col16}">{$cs_OUTRIGHT_cost|number_format:2|ifzero:'-'}</td>
			
			{if $cs_OUTRIGHT_selling ne 0 or $cs_OUTRIGHT_selling ne ''}
			    {assign var=cs_gp value=$cs_OUTRIGHT_selling-$cs_OUTRIGHT_cost}
				{assign var=cs_gp value=$cs_gp/$cs_OUTRIGHT_selling*100}
			{else}
			    {assign var=cs_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="cs_o_gp,{$cid}" title="{$colgp}" {$background}>{$cs_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------OTHER INCOME OUTRIGHT ---------->
			<td rowspan=2 class="r oi_o_selling_{$rid}" id="oi_o_selling,{$cid}" title="{$col17}">{$oi_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<!---------PROFIT MARGIN OUTRIGHT ---------->
			<td rowspan=2 class="r" id="pm_o_selling,{$cid}" title="{$col18}">{$pm_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_OUTRIGHT_selling ne 0 or $acs_OUTRIGHT_selling ne ''}
				{assign var=pm_gp value=$pm_OUTRIGHT_selling/$acs_OUTRIGHT_selling*100}
			{else}
			    {assign var=pm_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pm_o_gp,{$cid}" title="{$coladjgp}" {$background}>{$pm_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------AVERAGE STOCK OUTRIGHT ---------->
			<td rowspan=2 class="r" id="av_o_selling,{$cid}" title="{$col19}">{$av_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<!---------TURN DAYS OUTRIGHT ---------->
			<td rowspan=2 class="r" id="td_o_selling,{$cid}" title="{$col20}">{$td_OUTRIGHT_selling|number_format:0|ifzero:'-'}</td>
		</tr>
		<tr class="outright">
			<th>SP</th>
			<td class="r" id="os_o_selling,{$cid}" title="{$col1}">{$os_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="cos_o_selling,{$cid}" title="{$col2}">{$cos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="stv_o_selling,{$cid}" title="{$col3}">{$stv_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" title="{$col4}">{$stc_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" title="{$col5}">{$cov_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="aos_o_selling,{$cid}" title="{$col6}">{$aos_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="sr_o_selling,{$cid}" title="{$col7}">{$sr_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r grn_o_selling_{$rid}" id="grn_o_selling,{$cid}" title="{$col8}">{$grn_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="adj_o_selling,{$cid}" title="{$col9}">{$adj_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="as_o_selling,{$cid}" title="{$col10}">{$as_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="rs_o_selling,{$cid}" title="{$col11}">{$rs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

{*			<td class="r" id="ts_o_selling,{$cid}">{$ts_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>   *}
			<td class="r idt_o_selling_{$rid}" id="idt_o_selling,{$cid}" title="{$col12}">{$idt_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
			<td class="r" id="acs_o_selling,{$cid}" title="{$col15}">{$acs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="cs_o_selling,{$cid}" title="{$col16}">{$cs_OUTRIGHT_selling|number_format:2|ifzero:'-'}</td>
		</tr>
		</tbody>
		
	 	{if !$disable_input}
			<span class="hide" id="r_os_o_cost,{$cid}">{$os_OUTRIGHT_cost}</span>
			<span class="hide" id="r_cos_o_cost,{$cid}">{$cos_OUTRIGHT_cost}</span>
			<span class="hide" id="r_stv_o_cost,{$cid}">{$stv_OUTRIGHT_cost}</span>
			<span class="hide" >{$stc_OUTRIGHT_cost}</span>
			<span class="hide" >{$cov_OUTRIGHT_cost}</span>			
            <span class="hide" id="r_aos_o_cost,{$cid}">{$aos_OUTRIGHT_cost}</span>
            <span class="hide" id="r_sr_o_cost,{$cid}">{$sr_OUTRIGHT_cost}</span>
            <span class="hide" id="r_adj_o_cost,{$cid}">{$adj_OUTRIGHT_cost}</span>
		    <span class="hide" id="r_as_o_cost,{$cid}">{$as_OUTRIGHT_cost}</span>
		    <span class="hide" id="r_rs_o_cost,{$cid}">{$rs_OUTRIGHT_cost}</span>
			<span class="hide" id="r_pa_o_selling,{$cid}">{$pa_OUTRIGHT_selling}</span>
			<span class="hide" id="r_pca_o_selling,{$cid}">{$pca_OUTRIGHT_selling}</span>
			<span class="hide" id="r_acs_o_cost,{$cid}">{$acs_OUTRIGHT_cost}</span>
			<span class="hide" id="r_cs_o_cost,{$cid}">{$cs_OUTRIGHT_cost}</span>
			<span class="hide" id="r_os_o_selling,{$cid}">{$os_OUTRIGHT_selling}</span>
			<span class="hide" id="r_cos_o_selling,{$cid}">{$cos_OUTRIGHT_selling}</span>
			<span class="hide" id="r_stv_o_selling,{$cid}">{$stv_o_selling}</span>
			<span class="hide" id="r_aos_o_selling,{$cid}">{$aos_OUTRIGHT_selling}</span>
			<span class="hide" id="r_sr_o_selling,{$cid}">{$sr_OUTRIGHT_selling}</span>
			<span class="hide" id="r_adj_o_selling,{$cid}">{$adj_OUTRIGHT_selling}</span>
			<span class="hide" id="r_as_o_selling,{$cid}">{$as_OUTRIGHT_selling}</span>
			<span class="hide" id="r_rs_o_selling,{$cid}">{$rs_OUTRIGHT_selling}</span>
			<span class="hide" id="r_acs_o_selling,{$cid}">{$acs_OUTRIGHT_selling}</span>
			<span class="hide" id="r_cs_o_selling,{$cid}">{$cs_OUTRIGHT_selling}</span>
		{/if}

		{if !$excel_without_vendor}
			{if $check_vendor.$cid.OUTRIGHT}
			<tbody id="outright_{$cid}" class="voutright" style="display:none;">
				{include file=report.csa.vendor.tpl type=outright}
			</tbody>
			{/if}
		{/if}
		{* /if *}

		<!-------------------------DEPARTMENT CONSIGN------------------------------>
		{if $pa_CONSIGN_selling or $oi_CONSIGN_selling or $pm_CONSIGN_selling or $acs_CONSIGN_selling}
		<tbody class="dept_consign_{$rid}">
		<tr class="consign">
			<th rowspan=2>
				{if $check_vendor.$cid.CONSIGN && !$excel_mode}
					<img id='exp_col_c_{$cid}' align="left" src="/ui/expand.gif" onclick="show_vendor(this,'consign_{$cid}')">
				{/if}
				{$child.c_descrip}<br>(Consignment)
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

			<!---------IDT CONSIGN GP---------->
			<td class="r idt_c_cost_{$rid}" id="idt_c_cost,{$cid}" title="{$col12}">&nbsp;</td>
			<td rowspan=2 class="r" id="idt_c_gp,{$cid}" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------PROMOTION AMOUNT CONSIGN GP---------->
			<td rowspan=2 class="r" id="pa_c_selling,{$cid}" title="{$col13}">{$pa_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_CONSIGN_selling ne 0 or $acs_CONSIGN_selling ne ''}
				{assign var=pa_cgp value=$pa_CONSIGN_selling/$acs_CONSIGN_selling*100}
			{else}
			    {assign var=pa_cgp value=0}
			{/if}
			<td rowspan=2 class="r" id="pa_c_gp,{$cid}" title="{$colper}" {$background}>{$pa_cgp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PRICE CHANGE AMOUNT CONSIGN GP---------->
			<td rowspan=2 class="r" title="{$col14}">&nbsp;</td>
			<td rowspan=2 class="r" title="{$colper}" {$background}>&nbsp;</td>

			<!---------ACTUAL SALES CONSIGN GP---------->
			<td class="r acs_c_cost_{$rid}" id="acs_c_cost,{$cid}" title="{$col15}">{$acs_CONSIGN_cost|number_format:2|ifzero:'-'}</td>

			{if $acs_CONSIGN_selling ne 0 or $acs_CONSIGN_selling ne ''}
			    {assign var=acs_cgp value=$acs_CONSIGN_selling-$acs_CONSIGN_cost}
				{assign var=acs_cgp value=$acs_cgp/$acs_CONSIGN_selling*100}
			{else}
			    {assign var=acs_cgp value=0}
			{/if}
			<td rowspan=2 class="r" id="acs_c_gp,{$cid}" title="{$colgp}" {$background}>{$acs_cgp|number_format:2|ifzero:'-':$percent}</td>

			<!---------REBATE CONSIGN GP---------->
			{*<td rowspan=2 class="r hide r_c_selling_{$rid}" id="r_c_selling,{$cid}">{$r_CONSIGN_selling|number_format:2|ifzero:'-'}</td>*}
			{*
			{if $acs_CONSIGN_selling ne 0 or $acs_CONSIGN_selling ne ''}
			    {assign var=r_cgp value=$acs_CONSIGN_selling-$acs_CONSIGN_cost+$r_CONSIGN_selling}
				{assign var=r_cgp value=$r_cgp/$acs_CONSIGN_selling*100}
			{else}
			    {assign var=r_cgp value=0}
			{/if}
			<td rowspan=2 class="r" id="r_c_gp,{$cid}" title="{$coladjgp}" {$background}>{$r_cgp|number_format:2|ifzero:'-':$percent}</td>
			*}
			<td class="r" title="{$col16}">&nbsp;</td>
			<td rowspan=2 class="r" title="{$colgp}" {$background}>&nbsp;</td>

			<!---------OTHER INCOME CONSIGN GP---------->
			<td rowspan=2 class="r oi_c_selling_{$rid}" id="oi_c_selling,{$cid}" title="{$col17}">{$oi_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

			<!---------PROFIT MARGIN CONSIGN GP---------->
			<td rowspan=2 class="r" id="pm_c_selling,{$cid}" title="{$col18}">{$pm_CONSIGN_selling|number_format:2|ifzero:'-'}</td>

			{if $acs_CONSIGN_selling ne 0 or $acs_CONSIGN_selling ne ''}
				{assign var=pm_cgp value=$pm_CONSIGN_selling/$acs_CONSIGN_selling*100}
			{else}
			    {assign var=pm_cgp value=0}
			{/if}
			<td rowspan=2 class="r" id="pm_c_gp,{$cid}" title="{$coladjgp}" {$background}>{$pm_cgp|number_format:2|ifzero:'-':$percent}</td>

			<!---------AVERAGE STOCK CONSIGN GP---------->
			<td rowspan=2 class="r" title="{$col19}">&nbsp;</td>

			<td rowspan=2 class="r" title="{$col20}">&nbsp;</td>
		</tr>
		<tr class="consign">
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
			<td class="r idt_c_selling_{$rid}" id="idt_c_selling,{$cid}" title="{$col12}">&nbsp;</td>
			<td class="r acs_c_selling_{$rid}" id="acs_c_selling,{$cid}" title="{$col15}">{$acs_CONSIGN_selling|number_format:2|ifzero:'-'}</td>
			<td class="r" title="{$col16}">&nbsp;</td>
		</tr>
		</tbody>
		
	 	{if !$disable_input}
		 	<span class="hide" id="r_acs_c_selling,{$cid}">{$acs_CONSIGN_selling}</span>
	 	{/if}
		
		{if !$excel_without_vendor}
			{if $check_vendor.$cid.CONSIGN}
				<tbody id="consign_{$cid}" class="vconsign" style="display:none;">
					{include file=report.csa.vendor.tpl type=consign}
				</tbody>
			{/if}
		{/if}
		{/if}

		<!-------------------------DEPARTMENT TOTAL------------------------------>

 		{assign var=os_total_cost value=$os_OUTRIGHT_cost+$os_CONSIGN_cost}
 		{assign var=os_total_selling value=$os_OUTRIGHT_selling+$os_CONSIGN_selling}
 		{assign var=cos_total_cost value=$cos_OUTRIGHT_cost+$cos_CONSIGN_cost}
 		{assign var=cos_total_selling value=$cos_OUTRIGHT_selling+$cos_CONSIGN_selling}
 		{assign var=stv_total_cost value=$stv_OUTRIGHT_cost+$stv_CONSIGN_cost}
 		{assign var=stv_total_selling value=$stv_OUTRIGHT_selling+$stv_CONSIGN_selling}
 		{assign var=stc_total_cost value=$stc_OUTRIGHT_cost+$stc_CONSIGN_cost}
 		{assign var=stc_total_selling value=$stc_OUTRIGHT_selling+$stc_CONSIGN_selling}
 		{assign var=cov_total_cost value=$cov_OUTRIGHT_cost+$cov_CONSIGN_cost}
 		{assign var=cov_total_selling value=$cov_OUTRIGHT_selling+$cov_CONSIGN_selling}
 		{assign var=aos_total_cost value=$aos_OUTRIGHT_cost+$aos_CONSIGN_cost}
 		{assign var=aos_total_selling value=$aos_OUTRIGHT_selling+$aos_CONSIGN_selling}
 		{assign var=sr_total_cost value=$sr_OUTRIGHT_cost+$sr_CONSIGN_cost}
 		{assign var=sr_total_selling value=$sr_OUTRIGHT_selling+$sr_CONSIGN_selling}
 		{assign var=grn_total_cost value=$grn_OUTRIGHT_cost+$grn_CONSIGN_cost}
 		{assign var=grn_total_selling value=$grn_OUTRIGHT_selling+$grn_CONSIGN_selling}
 		{assign var=adj_total_cost value=$adj_OUTRIGHT_cost+$adj_CONSIGN_cost}
 		{assign var=adj_total_selling value=$adj_OUTRIGHT_selling+$adj_CONSIGN_selling}
 		{assign var=rs_total_cost value=$rs_OUTRIGHT_cost+$rs_CONSIGN_cost}
 		{assign var=rs_total_selling value=$rs_OUTRIGHT_selling+$rs_CONSIGN_selling}
 		{assign var=idt_total_cost value=$idt_OUTRIGHT_cost+$idt_CONSIGN_cost}
 		{assign var=idt_total_selling value=$idt_OUTRIGHT_selling+$idt_CONSIGN_selling}
 		{assign var=ts_total_cost value=$ts_OUTRIGHT_cost+$ts_CONSIGN_cost}
 		{assign var=ts_total_selling value=$ts_OUTRIGHT_selling+$ts_CONSIGN_selling}
 		{assign var=pa_total_selling value=$pa_OUTRIGHT_selling+$pa_CONSIGN_selling}
 		{assign var=pca_total_selling value=$pca_OUTRIGHT_selling}
 		{assign var=acs_total_cost value=$acs_OUTRIGHT_cost+$acs_CONSIGN_cost}
 		{assign var=acs_total_selling value=$acs_OUTRIGHT_selling+$acs_CONSIGN_selling}

		{* if $os_total_cost or $os_total_selling or $sr_total_cost or $sr_total_selling or $grn_total_cost or $grn_total_selling or
			$adj_total_cost or $adj_total_selling or $stv_total_cost or $stv_total_selling or $rs_total_cost or
			$rs_total_selling or $ts_total_cost or $ts_total_selling or $pa_total_selling or $acs_total_cost or $acs_total_selling *}
		<tbody class="dept_total_{$rid}">
		<tr class="total">
			<th rowspan=2>
				<div style="position:relative;padding-left:15px;padding-top:5px;">
	
					<input class="left_top check_{$rid}" type="checkbox" name="reviewed[{$rid}][{$cid}]" value=1
						{if $reviewed.$rid.$cid} checked {/if}
						{if !$privilege.REPORTS_CSA_REVIEW || !$finaliz.$rid.$cid || ($reviewed.$rid.$cid && !$can_unfinalize)} disabled {/if} >
					{if ($reviewed.$rid.$cid && !$can_unfinalize)}
						<input type="hidden" name="reviewed[{$rid}][{$cid}]" value=1>
					{/if}
					<span>{$child.c_descrip}</span>
				</div>
				{if !$excel_mode}
					{if $dept_confirm.$rid.$cid eq '1'}
						<img class="left" src="/ui/approved.png" title="Confirmed" alt="Confirmed">
					{else}
					    <img class="left" src="/ui/approved_grey.png" title="Not Confirmed" alt="Not Confirmed">
					{/if}
					{if $finaliz.$rid.$cid}
						<img class="left" src="/ui/icons/star.png" title="Finalised" alt="Finalised">
					{else}
						<img class="left" src="/ui/icons/stop.png" title="Not Finalised" alt="Not Finalised">
					{/if}
				{/if}
				<span>(Total)</span>

			</th>
	 		<th>CP</th>
			<!---------SYSTEM STOCK OPENING COST or SELLING TOTAL---------->
			<td class="r" id="os_t_cost,{$cid}" title="{$col1}">{$os_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------SYSTEM OPENING STOCK TOTAL GP---------->
			{if $os_total_selling ne 0 or $os_total_selling ne ''}
			    {assign var=os_total_gp value=$os_total_selling-$os_total_cost}
				{assign var=os_total_gp value=$os_total_gp/$os_total_selling*100}
			{else}
			    {assign var=os_total_gp value=0}
			{/if}

			<td rowspan=2 class="r" id="os_t_gp,{$cid}" title="{$colgp}" {$background}>{$os_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------OPENING STOCK COST or SELLING TOTAL---------->
			<td class="r" id="cos_t_cost,{$cid}" title="{$col2}">{$cos_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------OPENING STOCK TOTAL GP---------->
			{if $cos_total_selling ne 0 or $cos_total_selling ne ''}
			    {assign var=cos_total_gp value=$cos_total_selling-$cos_total_cost}
				{assign var=cos_total_gp value=$cos_total_gp/$cos_total_selling*100}
			{else}
			    {assign var=cos_total_gp value=0}
			{/if}

			<td rowspan=2 class="r" id="cos_t_gp,{$cid}" title="{$colgp}" {$background}>{$cos_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------STOCK TAKE VARIANCE COST or SELLING TOTAL---------->
			<td class="r" id="stv_t_cost,{$cid}" title="{$col3}">{$stv_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------STOCK TAKE VARIANCE TOTAL GP---------->
			{if $stv_total_selling ne 0 or $stv_total_selling ne ''}
			    {assign var=stv_total_gp value=$stv_total_selling-$stv_total_cost}
				{assign var=stv_total_gp value=$stv_total_gp/$stv_total_selling*100}
			{else}
			    {assign var=stv_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="stv_t_gp,{$cid}" title="{$colgp}" {$background}>{$stv_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------STOCK CHECK COST or SELLING TOTAL---------->
			<td class="r" title="{$col4}">{$stc_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------STOCK CHECK TOTAL GP---------->
			{if $stc_total_selling ne 0 or $stc_total_selling ne ''}
			    {assign var=stc_total_gp value=$stc_total_selling-$stc_total_cost}
				{assign var=stc_total_gp value=$stc_total_gp/$stc_total_selling*100}
			{else}
			    {assign var=stc_total_gp value=0}
			{/if}

			<td rowspan=2 class="r" title="{$colgp}" {$background}>{$stc_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------CSA OPENING VARIANCE COST or SELLING TOTAL---------->
			<td class="r" title="{$col5}">{$cov_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------CSA OPENING VARIANCE TOTAL GP---------->
			{if $cov_total_selling ne 0 or $cov_total_selling ne ''}
			    {assign var=cov_total_gp value=$cov_total_selling-$cov_total_cost}
				{assign var=cov_total_gp value=$cov_total_gp/$cov_total_selling*100}
			{else}
			    {assign var=cov_total_gp value=0}
			{/if}

			<td rowspan=2 class="r" title="{$colgp}" {$background}>{$cov_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL OPENING STOCK COST or SELLING TOTAL---------->
			<td class="r" id="aos_t_cost,{$cid}" title="{$col6}">{$aos_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------ACTUAL OPENING STOCK TOTAL GP---------->
			{if $aos_total_selling ne 0 or $aos_total_selling ne ''}
			    {assign var=aos_total_gp value=$aos_total_selling-$aos_total_cost}
				{assign var=aos_total_gp value=$aos_total_gp/$aos_total_selling*100}
			{else}
			    {assign var=aos_total_gp value=0}
			{/if}

			<td rowspan=2 class="r" id="aos_t_gp,{$cid}" title="{$colgp}" {$background}>{$aos_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------STOCK RECEIVE COST or SELLING TOTAL---------->
			<td class="r" id="sr_t_cost,{$cid}" title="{$col7}">{$sr_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------STOCK RECEIVE TOTAL GP---------->
			{if $sr_total_selling ne 0 or $sr_total_selling ne ''}
			    {assign var=sr_total_gp value=$sr_total_selling-$sr_total_cost}
				{assign var=sr_total_gp value=$sr_total_gp/$sr_total_selling*100}
			{else}
			    {assign var=sr_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="sr_t_gp,{$cid}" title="{$colgp}" {$background}>{$sr_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------GRN PENDING COST or SELLING TOTAL---------->
			<td class="r grn_t_cost_{$rid}" id="grn_t_cost,{$cid}" title="{$col8}">{$grn_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------GRN PENDING TOTAL GP---------->
			{if $grn_total_selling ne 0 or $grn_total_selling ne ''}
			    {assign var=grn_total_gp value=$grn_total_selling-$grn_total_cost}
				{assign var=grn_total_gp value=$grn_total_gp/$grn_total_selling*100}
			{else}
			    {assign var=grn_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="grn_t_gp,{$cid}" title="{$colgp}" {$background}>{$grn_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ADJUSTMENT COST or SELLING TOTAL---------->
			<td class="r" id="adj_t_cost,{$cid}" title="{$col9}">{$adj_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------ADJUSTMENT TOTAL GP---------->
			{if $adj_total_selling ne 0 or $adj_total_selling ne ''}
			    {assign var=adj_total_gp value=$adj_total_selling-$adj_total_cost}
				{assign var=adj_total_gp value=$adj_total_gp/$adj_total_selling*100}
			{else}
			    {assign var=adj_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="adj_t_gp,{$cid}" title="{$colgp}" {$background}>{$adj_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL STOCK COST or SELLING TOTAL---------->
	 		{assign var=as_total_cost value=$as_OUTRIGHT_cost+$as_CONSIGN_cost}
   	 		{assign var=as_total_selling value=$as_OUTRIGHT_selling+$as_CONSIGN_selling}
			<td class="r as_t_cost_{$rid}" id="as_t_cost,{$cid}" title="{$col10}">{$as_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------ACTUAL STOCK TOTAL GP---------->
			{if $as_total_selling ne 0 or $as_total_selling ne ''}
			    {assign var=as_total_gp value=$as_total_selling-$as_total_cost}
				{assign var=as_total_gp value=$as_total_gp/$as_total_selling*100}
			{else}
			    {assign var=as_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="as_t_gp,{$cid}" title="{$colgp}" {$background}>{$as_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------RETURN STOCK COST or SELLING TOTAL---------->
			<td class="r" id="rs_t_cost,{$cid}" title="{$col11}">{$rs_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------RETURN STOCK TOTAL GP---------->
			{if $rs_total_selling ne 0 or $rs_total_selling ne ''}
			    {assign var=rs_total_gp value=$rs_total_selling-$rs_total_cost}
				{assign var=rs_total_gp value=$rs_total_gp/$rs_total_selling*100}
			{else}
			    {assign var=rs_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="rs_t_gp,{$cid}" title="{$colgp}" {$background}>{$rs_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------TRANSFER STOCK COST or SELLING TOTAL---------->
{*			<td class="r" id="ts_t_cost,{$cid}">{$ts_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------TRANSFER STOCK TOTAL GP---------->
			{if $ts_total_selling ne 0 or $ts_total_selling ne ''}
			    {assign var=ts_total_gp value=$ts_total_selling-$ts_total_cost}
				{assign var=ts_total_gp value=$ts_total_gp/$ts_total_selling*100}
			{else}
			    {assign var=ts_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="ts_t_gp,{$cid}" {$background}>{$ts_total_gp|number_format:2|ifzero:'-':$percent}</td>  *}

			<!---------IDT COST or SELLING TOTAL---------->
			<td class="r idt_t_cost_{$rid}" id="idt_t_cost,{$cid}" title="{$col12}">{$idt_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------IDT TOTAL GP---------->
			{if $idt_total_selling ne 0 or $idt_total_selling ne ''}
			    {assign var=idt_total_gp value=$idt_total_selling-$idt_total_cost}
				{assign var=idt_total_gp value=$idt_total_gp/$idt_total_selling*100}
			{else}
			    {assign var=idt_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="idt_t_gp,{$cid}" title="{$colgp}" {$background}>{$idt_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PROMOTION AMOUNT SELLING TOTAL---------->
			<td rowspan=2 class="r" id="pa_t_selling,{$cid}" title="{$col13}">{$pa_total_selling|number_format:2|ifzero:'-'}</td>

			<!---------PROMOTION AMOUNT TOTAL GP---------->
			{if $acs_total_selling ne 0 or $acs_total_selling ne ''}
				{assign var=pa_total_gp value=$pa_total_selling/$acs_total_selling*100}
			{else}
			    {assign var=pa_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pa_t_gp,{$cid}" title="{$colper}" {$background}>{$pa_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------PRICE CHANGE AMOUNT SELLING TOTAL---------->
			<td rowspan=2 class="r" id="pca_t_selling,{$cid}" title="{$col14}">{$pca_total_selling|number_format:2|ifzero:'-'}</td>

			<!---------PRICE CHANGE AMOUNT TOTAL GP---------->
			{if $acs_total_selling ne 0 or $acs_total_selling ne ''}
				{assign var=pca_total_gp value=$pca_total_selling/$acs_total_selling*100}
			{else}
			    {assign var=pca_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pca_t_gp,{$cid}" title="{$colper}" {$background}>{$pca_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------ACTUAL SALES COST or SELLING TOTAL---------->
			<td class="r" id="acs_t_cost,{$cid}" title="{$col15}">{$acs_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------ACTUAL SALES TOTAL GP---------->
			{if $acs_total_selling ne 0 or $acs_total_selling ne ''}
			    {assign var=acs_total_gp value=$acs_total_selling-$acs_total_cost}
				{assign var=acs_total_gp value=$acs_total_gp/$acs_total_selling*100}
			{else}
			    {assign var=acs_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="acs_t_gp,{$cid}" title="{$colgp}" {$background}>{$acs_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------REBATE COST or SELLING TOTAL---------->
   	 		{assign var=r_total_selling value=$r_OUTRIGHT_selling+$r_CONSIGN_selling}
			{*<td rowspan=2 class="r hide r_t_selling_{$rid}" id="r_t_selling,{$cid}">{$r_total_selling|number_format:2|ifzero:'-'}</td>*}
			{*
			<!---------REBATE TOTAL GP---------->
			{if $acs_total_selling ne 0 or $acs_total_selling ne ''}
			    {assign var=r_total_gp value=$acs_total_selling-$acs_total_cost+$r_total_selling}
				{assign var=r_total_gp value=$r_total_gp/$acs_total_selling*100}
			{else}
			    {assign var=r_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="r_t_gp,{$cid}" title="{$coladjgp}" {$background}>{$r_total_gp|number_format:2|ifzero:'-':$percent}</td>
			*}
			<!---------CLOSING STOCK COST or SELLING TOTAL---------->
	 		{assign var=cs_total_cost value=$cs_OUTRIGHT_cost+$cs_CONSIGN_cost}
   	 		{assign var=cs_total_selling value=$cs_OUTRIGHT_selling+$cs_CONSIGN_selling}
			<td class="r" id="cs_t_cost,{$cid}" title="{$col16}">{$cs_total_cost|number_format:2|ifzero:'-'}</td>

			<!---------CLOSING STOCK TOTAL GP---------->
			{if $cs_total_selling ne 0 or $cs_total_selling ne ''}
			    {assign var=cs_total_gp value=$cs_total_selling-$cs_total_cost}
				{assign var=cs_total_gp value=$cs_total_gp/$cs_total_selling*100}
			{else}
			    {assign var=cs_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="cs_t_gp,{$cid}" title="{$colgp}" {$background}>{$cs_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------OTHER INCOME COST or SELLING TOTAL---------->
   	 		{assign var=oi_total_selling value=$oi_OUTRIGHT_selling+$oi_CONSIGN_selling}
			<td rowspan=2 class="r oi_t_selling_{$rid}" id="oi_t_selling,{$cid}" title="{$col17}">{$oi_total_selling|number_format:2|ifzero:'-'}</td>

			<!---------PROFIT MARGIN COST or SELLING TOTAL---------->
   	 		{assign var=pm_total_selling value=$pm_OUTRIGHT_selling+$pm_CONSIGN_selling}
			<td rowspan=2 class="r" id="pm_t_selling,{$cid}" title="{$col18}">{$pm_total_selling|number_format:2|ifzero:'-'}</td>

			<!---------PROFIT MARGIN TOTAL GP---------->
			{if $acs_total_selling ne 0 or $acs_total_selling ne ''}
				{assign var=pm_total_gp value=$pm_total_selling/$acs_total_selling*100}
			{else}
			    {assign var=pm_total_gp value=0}
			{/if}
			<td rowspan=2 class="r" id="pm_t_gp,{$cid}" title="{$coladjgp}" {$background}>{$pm_total_gp|number_format:2|ifzero:'-':$percent}</td>

			<!---------AVERAGE STOCK SELLING TOTAL---------->
   	 		{assign var=av_total_selling value=$aos_total_cost+$cs_total_cost}
  	 		{assign var=av_total_selling value=$av_total_selling/2}
			<td rowspan=2 class="r" id="av_t_selling,{$cid}" title="{$col19}">{$av_total_selling|number_format:2|ifzero:'-'}</td>

			<!---------TURN DAYS SELLING TOTAL---------->
			{if $acs_total_cost ne 0 or $acs_total_cost ne ''}
	   	 		{assign var=td_total_selling value=$av_total_selling/$acs_total_cost*$d}
			{else}
			    {assign var=td_total_selling value=0}
			{/if}
			<td rowspan=2 class="r" id="td_t_selling,{$cid}" title="{$col20}">{$td_total_selling|number_format:0|ifzero:'-'}</td>
		</tr>
		<tr class="total">
			<th>SP</th>
			<td class="r" id="os_t_selling,{$cid}" title="{$col1}">{$os_total_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="cos_t_selling,{$cid}" title="{$col2}">{$cos_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r" id="stv_t_selling,{$cid}" title="{$col3}">{$stv_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r" title="{$col4}">{$stc_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r" title="{$col5}">{$cov_total_selling|number_format:2|ifzero:'-'}</td>

			<td class="r" id="aos_t_selling,{$cid}" title="{$col6}">{$aos_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r" id="sr_t_selling,{$cid}" title="{$col7}">{$sr_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r grn_t_selling_{$rid}" id="grn_t_selling,{$cid}" title="{$col8}">{$grn_total_selling|number_format:2|ifzero:'-'}</td>
  			<td class="r" id="adj_t_selling,{$cid}" title="{$col9}">{$adj_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r" id="as_t_selling,{$cid}" title="{$col10}">{$as_total_selling|number_format:2|ifzero:'-'}</td>

  			<td class="r" id="rs_t_selling,{$cid}" title="{$col11}">{$rs_total_selling|number_format:2|ifzero:'-'}</td>

{*			<td class="r" id="ts_t_selling,{$cid}">{$ts_total_selling|number_format:2|ifzero:'-'}</td>  *}
 			<td class="r idt_t_selling_{$rid}" id="idt_t_selling,{$cid}" title="{$col12}">{$idt_total_selling|number_format:2|ifzero:'-'}</td>
 			<td class="r" id="acs_t_selling,{$cid}" title="{$col15}">{$acs_total_selling|number_format:2|ifzero:'-'}</td>

 			<td class="r" id="cs_t_selling,{$cid}" title="{$col16}">{$cs_total_selling|number_format:2|ifzero:'-'}</td>
		</tr>
		</tbody>
		
	 	{if !$disable_input}
			<span class="hide" id="r_os_t_cost,{$cid}">{$os_total_cost}</span>
			<span class="hide" id="r_cos_t_cost,{$cid}">{$cos_total_cost}</span>
			<span class="hide" id="r_stv_t_cost,{$cid}">{$stv_total_cost}</span>
			<span class="hide" id="r_stc_t_cost,{$cid}">{$stc_total_cost}</span>
			<span class="hide" id="r_cov_t_cost,{$cid}">{$cov_total_cost}</span>
			<span class="hide" id="r_aos_t_cost,{$cid}">{$aos_total_cost}</span>
			<span class="hide" id="r_sr_t_cost,{$cid}">{$sr_total_cost}</span>
			<span class="hide" id="r_adj_t_cost,{$cid}">{$adj_total_cost}</span>
			<span class="hide" id="r_as_t_cost,{$cid}">{$as_total_cost}</span>
			<span class="hide" id="r_rs_t_cost,{$cid}">{$rs_total_cost}</span>
			<span class="hide" id="r_pa_t_selling,{$cid}">{$pa_total_selling}</span>
			<span class="hide" id="r_pca_t_selling,{$cid}">{$pca_total_selling}</span>
			<span class="hide" id="r_acs_t_cost,{$cid}">{$acs_total_cost}</span>
			<span class="hide" id="r_cs_t_cost,{$cid}">{$cs_total_cost}</span>
			<span class="hide" id="r_os_t_selling,{$cid}">{$os_total_selling}</span>
			<span class="hide" id="r_cos_t_selling,{$cid}">{$cos_total_selling}</span>
			<span class="hide" id="r_stv_t_selling,{$cid}">{$stv_total_selling}</span>
			<span class="hide" id="r_aos_t_selling,{$cid}">{$aos_total_selling}</span>
			<span class="hide" id="r_sr_t_selling,{$cid}">{$sr_total_selling}</span>
			<span class="hide" id="r_adj_t_selling,{$cid}">{$adj_total_selling}</span>
			<span class="hide" id="r_as_t_selling,{$cid}">{$as_total_selling}</span>
			<span class="hide" id="r_rs_t_selling,{$cid}">{$rs_total_selling}</span>
			<span class="hide" id="r_acs_t_selling,{$cid}">{$acs_total_selling}</span>
			<span class="hide" id="r_cs_t_selling,{$cid}">{$cs_total_selling}</span>
		{/if}
		{* /if *}

		<!--------Indicator to show fresh----------->
		{if $fresh.$cid}
			{assign var=got_fresh value=1}
			{assign var=dept_got_fresh value=1}
		{/if}

		{include file=report.csa.data.fresh.tpl type=cat}

		<!--------Calculate total----------->
		<!--------TOTAL SYSTEM OPENING STOCK----------->
		{assign var=dept_os_OUTRIGHT_cost value=$dept_os_OUTRIGHT_cost+$os_OUTRIGHT_cost}
		{assign var=dept_os_OUTRIGHT_selling value=$dept_os_OUTRIGHT_selling+$os_OUTRIGHT_selling}

		{* assign var=dept_os_CONSIGN_cost value=$dept_os_CONSIGN_cost+$os_CONSIGN_cost}
		{assign var=dept_os_CONSIGN_selling value=$dept_os_CONSIGN_selling+$os_CONSIGN_selling *}

		{assign var=t_os_FRESH_selling value=$t_os_FRESH_selling+$os_FRESH_selling}

		<!--------TOTAL OPENING STOCK----------->
		{assign var=dept_cos_OUTRIGHT_cost value=$dept_cos_OUTRIGHT_cost+$cos_OUTRIGHT_cost}
		{assign var=dept_cos_OUTRIGHT_selling value=$dept_cos_OUTRIGHT_selling+$cos_OUTRIGHT_selling}

		{* assign var=dept_cos_CONSIGN_cost value=$dept_cos_CONSIGN_cost+$cos_CONSIGN_cost}
		{assign var=dept_cos_CONSIGN_selling value=$dept_cos_CONSIGN_selling+$cos_CONSIGN_selling *}

		{assign var=t_cos_FRESH_selling value=$t_cos_FRESH_selling+$cos_FRESH_selling}

		<!--------TOTAL STOCK TAKE VARIANCE----------->
		{assign var=dept_stv_OUTRIGHT_cost value=$dept_stv_OUTRIGHT_cost+$stv_OUTRIGHT_cost}
		{assign var=dept_stv_OUTRIGHT_selling value=$dept_stv_OUTRIGHT_selling+$stv_OUTRIGHT_selling}

		{* assign var=dept_stv_CONSIGN_cost value=$dept_stv_CONSIGN_cost+$stv_CONSIGN_cost }
		{assign var=dept_stv_CONSIGN_selling value=$dept_stv_CONSIGN_selling+$stv_CONSIGN_selling *}

		{assign var=t_stv_FRESH_selling value=$t_stv_FRESH_selling+$stv_FRESH_selling}

		<!--------TOTAL STOCK CHECK----------->
		{assign var=dept_stc_OUTRIGHT_cost value=$dept_stc_OUTRIGHT_cost+$stc_OUTRIGHT_cost}
		{assign var=dept_stc_OUTRIGHT_selling value=$dept_stc_OUTRIGHT_selling+$stc_OUTRIGHT_selling}

		{* assign var=dept_stc_CONSIGN_cost value=$dept_stc_CONSIGN_cost+$stc_CONSIGN_cost }
		{assign var=dept_stc_CONSIGN_selling value=$dept_stc_CONSIGN_selling+$stc_CONSIGN_selling *}

		{assign var=t_stc_FRESH_selling value=$t_stc_FRESH_selling+$stc_FRESH_selling}

		<!--------TOTAL CSA OPENING VARIANCE----------->
		{assign var=dept_cov_OUTRIGHT_cost value=$dept_cov_OUTRIGHT_cost+$cov_OUTRIGHT_cost}
		{assign var=dept_cov_OUTRIGHT_selling value=$dept_cov_OUTRIGHT_selling+$cov_OUTRIGHT_selling}

		{* assign var=dept_cov_CONSIGN_cost value=$dept_cov_CONSIGN_cost+$cov_CONSIGN_cost}
		{assign var=dept_cov_CONSIGN_selling value=$dept_cov_CONSIGN_selling+$cov_CONSIGN_selling *}

		{assign var=t_cov_FRESH_selling value=$t_cov_FRESH_selling+$cov_FRESH_selling}

		<!--------TOTAL ACTUAL OPENING STOCK----------->
		{assign var=dept_aos_OUTRIGHT_cost value=$dept_aos_OUTRIGHT_cost+$aos_OUTRIGHT_cost}
		{assign var=dept_aos_OUTRIGHT_selling value=$dept_aos_OUTRIGHT_selling+$aos_OUTRIGHT_selling}

		{* assign var=dept_aos_CONSIGN_cost value=$dept_aos_CONSIGN_cost+$aos_CONSIGN_cost}
		{assign var=dept_aos_CONSIGN_selling value=$dept_aos_CONSIGN_selling+$aos_CONSIGN_selling *}

		{assign var=t_aos_FRESH_selling value=$t_aos_FRESH_selling+$aos_FRESH_selling}

		<!--------TOTAL STOCK RECEIVE----------->
		{assign var=dept_sr_OUTRIGHT_cost value=$dept_sr_OUTRIGHT_cost+$sr_OUTRIGHT_cost}
		{assign var=dept_sr_OUTRIGHT_selling value=$dept_sr_OUTRIGHT_selling+$sr_OUTRIGHT_selling}

		{* assign var=dept_sr_CONSIGN_cost value=$dept_sr_CONSIGN_cost+$sr_CONSIGN_cost }
		{assign var=dept_sr_CONSIGN_selling value=$dept_sr_CONSIGN_selling+$sr_CONSIGN_selling *}

		{assign var=t_sr_FRESH_selling value=$t_sr_FRESH_selling+$sr_FRESH_selling}

		<!--------TOTAL grn pending----------->
		{assign var=dept_grn_OUTRIGHT_cost value=$dept_grn_OUTRIGHT_cost+$grn_OUTRIGHT_cost}
		{assign var=dept_grn_OUTRIGHT_selling value=$dept_grn_OUTRIGHT_selling+$grn_OUTRIGHT_selling}

		{* assign var=dept_grn_CONSIGN_cost value=$dept_grn_CONSIGN_cost+$grn_CONSIGN_cost }
		{assign var=dept_grn_CONSIGN_selling value=$dept_grn_CONSIGN_selling+$grn_CONSIGN_selling *}

		{assign var=t_grn_FRESH_selling value=$t_grn_FRESH_selling+$grn_FRESH_selling}

		<!--------TOTAL ADJUSTMENT----------->
		{assign var=dept_adj_OUTRIGHT_cost value=$dept_adj_OUTRIGHT_cost+$adj_OUTRIGHT_cost}
		{assign var=dept_adj_OUTRIGHT_selling value=$dept_adj_OUTRIGHT_selling+$adj_OUTRIGHT_selling}

		{* assign var=dept_adj_CONSIGN_cost value=$dept_adj_CONSIGN_cost+$adj_CONSIGN_cost }
		{assign var=dept_adj_CONSIGN_selling value=$dept_adj_CONSIGN_selling+$adj_CONSIGN_selling *}

		{assign var=t_adj_FRESH_selling value=$t_adj_FRESH_selling+$adj_FRESH_selling}

		<!--------TOTAL ACTUAL STOCK----------->
		{assign var=dept_as_OUTRIGHT_cost value=$dept_as_OUTRIGHT_cost+$as_OUTRIGHT_cost}
		{assign var=dept_as_OUTRIGHT_selling value=$dept_as_OUTRIGHT_selling+$as_OUTRIGHT_selling}

		{* assign var=dept_as_CONSIGN_cost value=$dept_as_CONSIGN_cost+$as_CONSIGN_cost }
		{assign var=dept_as_CONSIGN_selling value=$dept_as_CONSIGN_selling+$as_CONSIGN_selling *}

		{assign var=t_as_FRESH_selling value=$t_as_FRESH_selling+$as_FRESH_selling}

		<!--------TOTAL RETURN STOCK----------->
		{assign var=dept_rs_OUTRIGHT_cost value=$dept_rs_OUTRIGHT_cost+$rs_OUTRIGHT_cost}
		{assign var=dept_rs_OUTRIGHT_selling value=$dept_rs_OUTRIGHT_selling+$rs_OUTRIGHT_selling}

		{* assign var=dept_rs_CONSIGN_cost value=$dept_rs_CONSIGN_cost+$rs_CONSIGN_cost }
		{assign var=dept_rs_CONSIGN_selling value=$dept_rs_CONSIGN_selling+$rs_CONSIGN_selling *}

		{assign var=t_rs_FRESH_selling value=$t_rs_FRESH_selling+$rs_FRESH_selling}

		<!--------TOTAL TRANSFER STOCK----------->
		{assign var=dept_ts_OUTRIGHT_cost value=$dept_ts_OUTRIGHT_cost+$ts_OUTRIGHT_cost}
		{assign var=dept_ts_OUTRIGHT_selling value=$dept_ts_OUTRIGHT_selling+$ts_OUTRIGHT_selling}

		{* assign var=dept_ts_CONSIGN_cost value=$dept_ts_CONSIGN_cost+$ts_CONSIGN_cost }
		{assign var=dept_ts_CONSIGN_selling value=$dept_ts_CONSIGN_selling+$ts_CONSIGN_selling *}

		{assign var=t_ts_FRESH_selling value=$t_ts_FRESH_selling+$ts_FRESH_selling}

		<!--------TOTAL IDT STOCK----------->
		{assign var=dept_idt_OUTRIGHT_cost value=$dept_idt_OUTRIGHT_cost+$idt_OUTRIGHT_cost}
		{assign var=dept_idt_OUTRIGHT_selling value=$dept_idt_OUTRIGHT_selling+$idt_OUTRIGHT_selling}

		{assign var=dept_idt_CONSIGN_cost value=$dept_idt_CONSIGN_cost+$idt_CONSIGN_cost }
		{assign var=dept_idt_CONSIGN_selling value=$dept_idt_CONSIGN_selling+$idt_CONSIGN_selling}

		{assign var=t_idt_FRESH_selling value=$t_idt_FRESH_selling+$idt_FRESH_selling}

		<!--------TOTAL PROMOTION AMOUNT----------->
		{assign var=dept_pa_OUTRIGHT_selling value=$dept_pa_OUTRIGHT_selling+$pa_OUTRIGHT_selling}

		{assign var=dept_pa_CONSIGN_selling value=$dept_pa_CONSIGN_selling+$pa_CONSIGN_selling}

		{assign var=t_pa_FRESH_selling value=$t_pa_FRESH_selling+$pa_FRESH_selling}

		<!--------TOTAL PRICE CHANGE AMOUNT----------->
		{assign var=dept_pca_OUTRIGHT_selling value=$dept_pca_OUTRIGHT_selling+$pca_OUTRIGHT_selling}

		{* assign var=dept_pca_CONSIGN_selling value=$dept_pca_CONSIGN_selling+$pca_CONSIGN_selling *}

		{assign var=t_pca_FRESH_selling value=$t_pca_FRESH_selling+$pca_FRESH_selling}

		<!--------TOTAL ACTUAL SALES----------->
		{assign var=dept_acs_OUTRIGHT_cost value=$dept_acs_OUTRIGHT_cost+$acs_OUTRIGHT_cost}
		{assign var=dept_acs_OUTRIGHT_selling value=$dept_acs_OUTRIGHT_selling+$acs_OUTRIGHT_selling}

		{assign var=dept_acs_CONSIGN_cost value=$dept_acs_CONSIGN_cost+$acs_CONSIGN_cost }
		{assign var=dept_acs_CONSIGN_selling value=$dept_acs_CONSIGN_selling+$acs_CONSIGN_selling}

		{assign var=t_acs_FRESH_selling value=$t_acs_FRESH_selling+$acs_FRESH_selling}

		<!--------TOTAL REBATE----------->
		{assign var=dept_r_OUTRIGHT_selling value=$dept_r_OUTRIGHT_selling+$r_OUTRIGHT_selling}

		{assign var=dept_r_CONSIGN_selling value=$dept_r_CONSIGN_selling+$r_CONSIGN_selling}

		{assign var=t_r_FRESH_selling value=$t_r_FRESH_selling+$r_FRESH_selling}

		<!--------TOTAL CLOSING STOCK----------->
		{assign var=dept_cs_OUTRIGHT_cost value=$dept_cs_OUTRIGHT_cost+$cs_OUTRIGHT_cost}
		{assign var=dept_cs_OUTRIGHT_selling value=$dept_cs_OUTRIGHT_selling+$cs_OUTRIGHT_selling}

		{assign var=dept_cs_CONSIGN_cost value=$dept_cs_CONSIGN_cost+$cs_CONSIGN_cost }
		{assign var=dept_cs_CONSIGN_selling value=$dept_cs_CONSIGN_selling+$cs_CONSIGN_selling}

		{assign var=t_cs_FRESH_selling value=$t_cs_FRESH_selling+$cs_FRESH_selling}

		<!--------TOTAL OTHER INCOME----------->
		{assign var=dept_oi_OUTRIGHT_selling value=$dept_oi_OUTRIGHT_selling+$oi_OUTRIGHT_selling}

		{assign var=dept_oi_CONSIGN_selling value=$dept_oi_CONSIGN_selling+$oi_CONSIGN_selling}

		{assign var=t_oi_FRESH_selling value=$t_oi_FRESH_selling+$oi_FRESH_selling}

		<!--------TOTAL PROFIT MARGIN----------->
		{assign var=dept_pm_OUTRIGHT_selling value=$dept_pm_OUTRIGHT_selling+$pm_OUTRIGHT_selling}

		{assign var=dept_pm_CONSIGN_selling value=$dept_pm_CONSIGN_selling+$pm_CONSIGN_selling}

		{assign var=t_pm_FRESH_selling value=$t_pm_FRESH_selling+$pm_FRESH_selling}
	{/foreach}
	
	<!----------------------------TOTAL EACH DEPARTMENT OUTRIGHT----------------------------->
	{assign var=dept_av_OUTRIGHT_selling value=$dept_aos_OUTRIGHT_cost+$dept_cs_OUTRIGHT_cost}
	{assign var=dept_av_OUTRIGHT_selling value=$dept_av_OUTRIGHT_selling/2}

	<!----------------------------TOTAL EACH DEPARTMENT TOTAL----------------------------->
	{assign var=dept_os_total_cost value=$dept_os_OUTRIGHT_cost+$dept_os_CONSIGN_cost}
	{assign var=dept_os_total_selling value=$dept_os_OUTRIGHT_selling+$dept_os_CONSIGN_selling}
	{assign var=dept_cos_total_cost value=$dept_cos_OUTRIGHT_cost+$dept_cos_CONSIGN_cost}
	{assign var=dept_cos_total_selling value=$dept_cos_OUTRIGHT_selling+$dept_cos_CONSIGN_selling}
	{assign var=dept_stc_total_cost value=$dept_stc_OUTRIGHT_cost+$dept_stc_CONSIGN_cost}
	{assign var=dept_stc_total_selling value=$dept_stc_OUTRIGHT_selling+$dept_stc_CONSIGN_selling}
	{assign var=dept_cov_total_cost value=$dept_cov_OUTRIGHT_cost+$dept_cov_CONSIGN_cost}
	{assign var=dept_cov_total_selling value=$dept_cov_OUTRIGHT_selling+$dept_cov_CONSIGN_selling}
	{assign var=dept_aos_total_cost value=$dept_aos_OUTRIGHT_cost+$dept_aos_CONSIGN_cost}
	{assign var=dept_aos_total_selling value=$dept_aos_OUTRIGHT_selling+$dept_aos_CONSIGN_selling}
	{assign var=dept_stv_total_cost value=$dept_stv_OUTRIGHT_cost+$dept_stv_CONSIGN_cost}
	{assign var=dept_stv_total_selling value=$dept_stv_OUTRIGHT_selling+$dept_stv_CONSIGN_selling}
	{assign var=dept_sr_total_cost value=$dept_sr_OUTRIGHT_cost+$dept_sr_CONSIGN_cost}
	{assign var=dept_sr_total_selling value=$dept_sr_OUTRIGHT_selling+$dept_sr_CONSIGN_selling}
	{assign var=dept_grn_total_cost value=$dept_grn_OUTRIGHT_cost+$dept_grn_CONSIGN_cost}
	{assign var=dept_grn_total_selling value=$dept_grn_OUTRIGHT_selling+$dept_grn_CONSIGN_selling}
	{assign var=dept_adj_total_cost value=$dept_adj_OUTRIGHT_cost+$dept_adj_CONSIGN_cost}
	{assign var=dept_adj_total_selling value=$dept_adj_OUTRIGHT_selling+$dept_adj_CONSIGN_selling}
	{assign var=dept_rs_total_cost value=$dept_rs_OUTRIGHT_cost+$dept_rs_CONSIGN_cost}
	{assign var=dept_rs_total_selling value=$dept_rs_OUTRIGHT_selling+$dept_rs_CONSIGN_selling}
	{assign var=dept_ts_total_cost value=$dept_ts_OUTRIGHT_cost+$dept_ts_CONSIGN_cost}
	{assign var=dept_ts_total_selling value=$dept_ts_OUTRIGHT_selling+$dept_ts_CONSIGN_selling}
	{assign var=dept_idt_total_cost value=$dept_idt_OUTRIGHT_cost+$dept_idt_CONSIGN_cost}
	{assign var=dept_idt_total_selling value=$dept_idt_OUTRIGHT_selling+$dept_idt_CONSIGN_selling}
	{assign var=dept_pa_total_selling value=$dept_pa_OUTRIGHT_selling+$dept_pa_CONSIGN_selling}
	{assign var=dept_pca_total_selling value=$dept_pca_OUTRIGHT_selling}
	{assign var=dept_acs_total_cost value=$dept_acs_OUTRIGHT_cost+$dept_acs_CONSIGN_cost}
	{assign var=dept_acs_total_selling value=$dept_acs_OUTRIGHT_selling+$dept_acs_CONSIGN_selling}
	{assign var=dept_r_total_selling value=$dept_r_OUTRIGHT_selling+$dept_r_CONSIGN_selling}
	{assign var=dept_cs_total_cost value=$dept_cs_OUTRIGHT_cost+$dept_cs_CONSIGN_cost}
	{assign var=dept_cs_total_selling value=$dept_cs_OUTRIGHT_selling+$dept_cs_CONSIGN_selling}
	{assign var=dept_oi_total_selling value=$dept_oi_OUTRIGHT_selling+$dept_oi_CONSIGN_selling}
	{assign var=dept_pm_total_selling value=$dept_pm_OUTRIGHT_selling+$dept_pm_CONSIGN_selling}
	{assign var=dept_av_total_selling value=$dept_aos_total_cost+$dept_cs_total_cost}
	{assign var=dept_av_total_selling value=$dept_av_total_selling/2}

	<!----------------------------CALC FRESH MARKET WEIGHT----------------------------->
	{assign var=t_acs_FRESH_gp value=$t_acs_FRESH_selling-$t_aos_FRESH_selling-$t_sr_FRESH_selling-$t_grn_FRESH_selling+$t_cs_FRESH_selling}
	{if $t_acs_FRESH_selling ne 0 or $t_acs_FRESH_selling ne ''}
		{assign var=t_acs_FRESH_gp value=$t_acs_FRESH_gp/$t_acs_FRESH_selling*100 }
	{else}
		{assign var=t_acs_FRESH_gp value=0 }
	{/if}
	{assign var=t_acs_FRESH_cost value=100-$t_acs_FRESH_gp}
	{assign var=t_acs_FRESH_cost value=$t_acs_FRESH_selling*$t_acs_FRESH_cost/100}

	<!--------AVERAGE STOCK PRICE----------->
	{assign var=t_av_FRESH_selling value=$t_aos_FRESH_selling+$t_cs_FRESH_selling}
	{assign var=t_av_FRESH_selling value=$t_av_FRESH_selling/2}

	<!--------TURN DAYS PRICE----------->
	{if $t_acs_FRESH_cost ne 0 or $t_acs_FRESH_cost ne ''}
		{assign var=td_FRESH_selling value=$t_av_FRESH_selling/$t_acs_FRESH_cost*$d}
	{else}
	    {assign var=td_FRESH_selling value=0}
	{/if}

	<!----------------------------DEPT FRESH MARKET WEIGHT----------------------------->
	{assign var=dept_acs_FRESH_cost value=$dept_acs_total_cost+$t_acs_FRESH_cost}
	{assign var=dept_acs_FRESH_selling value=$dept_acs_total_selling+$t_acs_FRESH_selling}
	{assign var=dept_r_FRESH_selling value=$dept_r_total_selling+$t_r_FRESH_selling}
	{assign var=dept_oi_FRESH_selling value=$dept_oi_total_selling+$t_oi_FRESH_selling}
	{assign var=dept_pm_FRESH_selling value=$dept_pm_total_selling+$t_pm_FRESH_selling}

	{include file=report.csa.data.dept.tpl}


	<!----------------------------CALC TOTAL STOREWIDE----------------------------->
	<!--------TOTAL STOREWIDE SYSTEM OPENING STOCK----------->
	{assign var=storewide_os_OUTRIGHT_cost value=$storewide_os_OUTRIGHT_cost+$dept_os_OUTRIGHT_cost}
	{assign var=storewide_os_OUTRIGHT_selling value=$storewide_os_OUTRIGHT_selling+$dept_os_OUTRIGHT_selling}
	{assign var=storewide_os_FRESH_selling value=$storewide_os_FRESH_selling+$t_os_FRESH_selling}

	<!--------TOTAL STOREWIDE OPENING STOCK----------->
	{assign var=storewide_cos_OUTRIGHT_cost value=$storewide_cos_OUTRIGHT_cost+$dept_cos_OUTRIGHT_cost}
	{assign var=storewide_cos_OUTRIGHT_selling value=$storewide_cos_OUTRIGHT_selling+$dept_cos_OUTRIGHT_selling}
	{assign var=storewide_cos_FRESH_selling value=$storewide_cos_FRESH_selling+$t_cos_FRESH_selling}

	<!--------TOTAL STOREWIDE ACTUAL OPENING STOCK----------->
	{assign var=storewide_aos_OUTRIGHT_cost value=$storewide_aos_OUTRIGHT_cost+$dept_aos_OUTRIGHT_cost}
	{assign var=storewide_aos_OUTRIGHT_selling value=$storewide_aos_OUTRIGHT_selling+$dept_aos_OUTRIGHT_selling}
	{assign var=storewide_aos_FRESH_selling value=$storewide_aos_FRESH_selling+$t_aos_FRESH_selling}

	<!--------TOTAL STOREWIDE STOCK RECEIVE----------->
	{assign var=storewide_sr_OUTRIGHT_cost value=$storewide_sr_OUTRIGHT_cost+$dept_sr_OUTRIGHT_cost}
	{assign var=storewide_sr_OUTRIGHT_selling value=$storewide_sr_OUTRIGHT_selling+$dept_sr_OUTRIGHT_selling}
	{assign var=storewide_sr_FRESH_selling value=$storewide_sr_FRESH_selling+$t_sr_FRESH_selling}
	
	<!--------TOTAL STOREWIDE GRN PENDING----------->
	{assign var=storewide_grn_OUTRIGHT_cost value=$storewide_grn_OUTRIGHT_cost+$dept_grn_OUTRIGHT_cost}
	{assign var=storewide_grn_OUTRIGHT_selling value=$storewide_grn_OUTRIGHT_selling+$dept_grn_OUTRIGHT_selling}
	{assign var=storewide_grn_FRESH_selling value=$storewide_grn_FRESH_selling+$t_grn_FRESH_selling}

	<!--------TOTAL STOREWIDE ADJUSTMENT----------->
	{assign var=storewide_adj_OUTRIGHT_cost value=$storewide_adj_OUTRIGHT_cost+$dept_adj_OUTRIGHT_cost}
	{assign var=storewide_adj_OUTRIGHT_selling value=$storewide_adj_OUTRIGHT_selling+$dept_adj_OUTRIGHT_selling}
	{assign var=storewide_adj_FRESH_selling value=$storewide_adj_FRESH_selling+$t_adj_FRESH_selling}

	<!--------TOTAL STOREWIDE STOCK TAKE VARIANCE----------->
	{assign var=storewide_stv_OUTRIGHT_cost value=$storewide_stv_OUTRIGHT_cost+$dept_stv_OUTRIGHT_cost}
	{assign var=storewide_stv_OUTRIGHT_selling value=$storewide_stv_OUTRIGHT_selling+$dept_stv_OUTRIGHT_selling}
	{assign var=storewide_stv_FRESH_selling value=$storewide_stv_FRESH_selling+$t_stv_FRESH_selling}

	<!--------TOTAL STOREWIDE STOCK CHECK----------->
	{assign var=storewide_stc_OUTRIGHT_cost value=$storewide_stc_OUTRIGHT_cost+$dept_stc_OUTRIGHT_cost}
	{assign var=storewide_stc_OUTRIGHT_selling value=$storewide_stc_OUTRIGHT_selling+$dept_stc_OUTRIGHT_selling}
	{assign var=storewide_stc_FRESH_selling value=$storewide_stc_FRESH_selling+$t_stc_FRESH_selling}

	<!--------TOTAL STOREWIDE CSA OPENING VARIANCE----------->
	{assign var=storewide_cov_OUTRIGHT_cost value=$storewide_cov_OUTRIGHT_cost+$dept_cov_OUTRIGHT_cost}
	{assign var=storewide_cov_OUTRIGHT_selling value=$storewide_cov_OUTRIGHT_selling+$dept_cov_OUTRIGHT_selling}
	{assign var=storewide_cov_FRESH_selling value=$storewide_cov_FRESH_selling+$t_cov_FRESH_selling}
	
	<!--------TOTAL STOREWIDE ACTUAL STOCK----------->
	{assign var=storewide_as_OUTRIGHT_cost value=$storewide_as_OUTRIGHT_cost+$dept_as_OUTRIGHT_cost}
	{assign var=storewide_as_OUTRIGHT_selling value=$storewide_as_OUTRIGHT_selling+$dept_as_OUTRIGHT_selling}
	{assign var=storewide_as_FRESH_selling value=$storewide_as_FRESH_selling+$t_as_FRESH_selling}
	
	<!--------TOTAL STOREWIDE RETURN STOCK----------->
	{assign var=storewide_rs_OUTRIGHT_cost value=$storewide_rs_OUTRIGHT_cost+$dept_rs_OUTRIGHT_cost}
	{assign var=storewide_rs_OUTRIGHT_selling value=$storewide_rs_OUTRIGHT_selling+$dept_rs_OUTRIGHT_selling}
	{assign var=storewide_rs_FRESH_selling value=$storewide_rs_FRESH_selling+$t_rs_FRESH_selling}
	
	<!--------TOTAL STOREWIDE IDT----------->
	{assign var=storewide_idt_OUTRIGHT_cost value=$storewide_idt_OUTRIGHT_cost+$dept_idt_OUTRIGHT_cost}
	{assign var=storewide_idt_OUTRIGHT_selling value=$storewide_idt_OUTRIGHT_selling+$dept_idt_OUTRIGHT_selling}
	{assign var=storewide_idt_FRESH_selling value=$storewide_idt_FRESH_selling+$t_idt_FRESH_selling}

	<!--------TOTAL STOREWIDE PROMOTION AMOUNT----------->
	{assign var=storewide_pa_OUTRIGHT_selling value=$storewide_pa_OUTRIGHT_selling+$dept_pa_OUTRIGHT_selling}
	{assign var=storewide_pa_CONSIGN_selling value=$storewide_pa_CONSIGN_selling+$dept_pa_CONSIGN_selling}

	<!--------TOTAL STOREWIDE PRICE CHANGE AMOUNT----------->
	{assign var=storewide_pca_OUTRIGHT_selling value=$storewide_pca_OUTRIGHT_selling+$dept_pca_OUTRIGHT_selling}
	{* assign var=storewide_pca_CONSIGN_selling value=$storewide_pca_CONSIGN_selling+$dept_pca_CONSIGN_selling *}
	{assign var=storewide_pca_FRESH_selling value=$storewide_pca_FRESH_selling+$t_pca_FRESH_selling}

	<!--------TOTAL STOREWIDE ACTUAL SALES----------->
	{assign var=storewide_acs_OUTRIGHT_cost value=$storewide_acs_OUTRIGHT_cost+$dept_acs_OUTRIGHT_cost}
	{assign var=storewide_acs_OUTRIGHT_selling value=$storewide_acs_OUTRIGHT_selling+$dept_acs_OUTRIGHT_selling}

	{assign var=storewide_acs_CONSIGN_cost value=$storewide_acs_CONSIGN_cost+$dept_acs_CONSIGN_cost}
	{assign var=storewide_acs_CONSIGN_selling value=$storewide_acs_CONSIGN_selling+$dept_acs_CONSIGN_selling}

	{assign var=storewide_acs_FRESH_selling value=$storewide_acs_FRESH_selling+$t_acs_FRESH_selling}

	<!--------TOTAL STOREWIDE REBATE----------->
	{assign var=storewide_r_OUTRIGHT_selling value=$storewide_r_OUTRIGHT_selling+$dept_r_OUTRIGHT_selling}
	{assign var=storewide_r_CONSIGN_selling value=$storewide_r_CONSIGN_selling+$dept_r_CONSIGN_selling}
	{assign var=storewide_r_FRESH_selling value=$storewide_r_FRESH_selling+$t_r_FRESH_selling}

	<!--------TOTAL STOREWIDE CLOSING STOCK----------->
	{assign var=storewide_cs_OUTRIGHT_cost value=$storewide_cs_OUTRIGHT_cost+$dept_cs_OUTRIGHT_cost}
	{assign var=storewide_cs_OUTRIGHT_selling value=$storewide_cs_OUTRIGHT_selling+$dept_cs_OUTRIGHT_selling}
	{assign var=storewide_cs_FRESH_selling value=$storewide_cs_FRESH_selling+$t_cs_FRESH_selling}
	
	<!--------TOTAL OTHER INCOME----------->
	{assign var=storewide_oi_OUTRIGHT_selling value=$storewide_oi_OUTRIGHT_selling+$dept_oi_OUTRIGHT_selling}
	{assign var=storewide_oi_CONSIGN_selling value=$storewide_oi_CONSIGN_selling+$dept_oi_CONSIGN_selling}
	{assign var=storewide_oi_FRESH_selling value=$storewide_oi_FRESH_selling+$t_oi_FRESH_selling}

	<!--------TOTAL PROFIT MARGIN----------->
	{assign var=storewide_pm_OUTRIGHT_selling value=$storewide_pm_OUTRIGHT_selling+$dept_pm_OUTRIGHT_selling}
	{assign var=storewide_pm_CONSIGN_selling value=$storewide_pm_CONSIGN_selling+$dept_pm_CONSIGN_selling}
	{assign var=storewide_pm_FRESH_selling value=$storewide_pm_FRESH_selling+$t_pm_FRESH_selling}

{/foreach}
	
	<!--------TOTAL STOREWIDE ACTUAL SALES FOR FRESH MARKET ONLY----------->
{assign var=acs_storewide_fgp value=$storewide_acs_FRESH_selling-$storewide_aos_FRESH_selling-$storewide_sr_FRESH_selling-$storewide_grn_FRESH_selling+$storewide_cs_FRESH_selling}
{if $storewide_acs_FRESH_selling ne 0 or $storewide_acs_FRESH_selling ne ''}
	{assign var=acs_storewide_fgp value=$acs_storewide_fgp/$storewide_acs_FRESH_selling*100 }
{else}
	{assign var=acs_storewide_fgp value=0 }
{/if}
{assign var=storewide_acs_FRESH_cost value=100-$acs_storewide_fgp}
{assign var=storewide_acs_FRESH_cost value=$storewide_acs_FRESH_selling*$storewide_acs_FRESH_cost/100}

{include file=report.csa.data.storewide.tpl}

</table>
</p>
{*
{foreach from=$r_dept key=rid item=root}
	{foreach from=$c_dept.$rid key=cid item=child}

		{if $check_vendor.$cid.CONSIGN}
			<script>category_type_total('CONSIGN',{$rid},{$cid});</script>
		{/if}
	{/foreach}
{/foreach}
*}
{else}
	-- No Data --
{/if}

{literal}




<script>



/*        TDs = $$('.report_table input');

        for (var i=0; i<TDs.length; i++) {
                var temp = TDs[i];
                if (temp.value.substr(0,1) == '-') temp.className = "nv";
        }
	*/
</script>
{/literal}
