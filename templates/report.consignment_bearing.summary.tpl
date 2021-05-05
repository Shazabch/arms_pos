{*
7/6/2011 6:11:33 PM Alex
- add new table for showing pwp and gwp
9/14/2011 2:37:35 PM Alex
- round all number before calculation
9/26/2011 2:35:49 PM Alex
- group amount column
12/5/2011 11:56:17 AM Alex
- add checking while dividing
12/12/2011 11:16:46 AM Alex
- change total nett profit % calculation same as nett sales
- hide column price type if no data 
- fix missing "by Amount" or "by Discount" column
12/14/2011 10:03:58 AM Alex
- hide last total column if no data

2/20/2020 1:49 PM William
- Added checking to avoid error message show.
- Fixed bug column of table display not correct. 
*}

<table class="report_table" id="report_tbl">
	<!---------------Set Column Color------------------------------------------------>
	<col class="summary_top bg_grey">
	{foreach from=$head_not_nett_code item=c}
		<col class="summary_top bg_lyellow">
	{/foreach}
	
	<col class="summary_top bg_pink">
	
	{foreach from=$head_not_nett item=disc}
		<col class="summary_top bg_yellow">
	{/foreach}

    {foreach name=col_nett from=$head_nett item=n_disc}
		<col class="summary_top bg_lblue">
	{/foreach}

	{if $head_amount}
		<col class="summary_top bg_lblue">
	{/if}

	{foreach from=$head_mprice item=n_mprice}
		<col class="summary_top bg_lblue">
	{/foreach}
	
	{if $head_not_nett || $head_nett || $head_amount || $head_mprice}
		<col class="summary_top bg_pink">
	{/if}
	<tr class="header">
		<th rowspan=3>
			{if $smarty.request.branch_id eq 'All'}
				Branches
			{elseif $smarty.request.month eq 'All'}
			    Month
			{else}
			    Day
			{/if}
		</th>
		{foreach from=$head_not_nett_code item=c}
			<th rowspan=3>{$c}</th>
		{/foreach}

		<th rowspan=3>Total</th>

		{foreach from=$head_not_nett item=disc}
			<th rowspan=3>Disc {$disc}</th>
		{/foreach}


		{if $smarty.foreach.col_nett.total > 0 || $head_amount > 0}
			{if $smarty.foreach.col_nett.total  > 0}
				{assign var=nett_col_span value=$smarty.foreach.col_nett.total}
			{/if}
			{if $head_amount}
				{assign var=nett_col_span value=$nett_col_span+1}
			{/if}
			<th colspan="{$nett_col_span}">Nett Sales</th>
		{/if}
		
		{foreach from=$head_mprice item=n_mprice}
			<th rowspan=3>{$n_mprice} (Amt)</th>
		{/foreach}

		{if $head_not_nett || $smarty.foreach.col_nett.total > 0 || $head_amount > 0 || $head_mprice}
			<th rowspan=3>Total</th>
		{/if}
	</tr>
	{if $smarty.foreach.col_nett.total > 0 || $head_amount}
		<tr class="header">
			{if $smarty.foreach.col_nett.total > 0 }
				<th colspan="{$smarty.foreach.col_nett.total}">By Discount (%)</th>
			{/if}
			{if $head_amount}
				<th rowspan="2">By Amount</th>
			{/if}
		</tr>
		<tr class="header">
	        {foreach from=$head_nett item=n_disc}
				<th>{$n_disc}</th>
			{/foreach}

{*
			{foreach from=$head_amount item=n_prof}
				<th>{$n_prof}</th>
			{/foreach}
*}
		</tr>
	{else}
		<!---trick colspan--->
		<tr></tr>
		<tr></tr>
	{/if}

	{foreach from=$date key=d item=m_or_d}

		{assign var=t_selling value=0}
		{assign var=t_disc value=0}
		{assign var=t_n_disc value=0}
		{assign var=t_a_disc value=0}
		{assign var=t_m_disc value=0}
		{assign var=total_day value=0}

		<tr>
			<td class="bold">{$m_or_d}</td>
			{foreach from=$head_not_nett_code item=c}
				<td class='r'>{$not_nett.$d.$c.selling|number_format:2|ifzero:'-'}</td>
				{assign var=t_selling value=$t_selling+$not_nett.$d.$c.selling|round2}
			{/foreach}
			
			<td class='r'>{$t_selling|number_format:2|ifzero:'-'}</td>
			
			{foreach from=$head_not_nett item=disc}
				<td class='r'>{$not_nett.$d.disc.$disc.discount|number_format:2|ifzero:'-'}</td>
				{assign var=t_disc value=$t_disc+$not_nett.$d.disc.$disc.discount|round2}
			{/foreach}

            {foreach from=$head_nett item=n_disc}
				<td class='r'>{$nett.$d.disc.$n_disc.nett_sales|number_format:2|ifzero:'-'}</td>
				{assign var=t_n_disc value=$t_n_disc+$nett.$d.disc.$n_disc.nett_sales|round2}
			{/foreach}
{*
            {foreach from=$head_amount item=n_prof}
				<td class='r'>{$amount.$d.disc.$n_prof.nett_sales|number_format:2|ifzero:'-'}</td>
				{assign var=t_a_disc value=$t_a_disc+$amount.$d.disc.$n_prof.nett_sales|round2}
			{/foreach}
*}
			{if $head_amount}
				<td class='r'>{$amount.$d.disc.nett_sales|number_format:2|ifzero:'-'}</td>
				{assign var=t_a_disc value=$t_a_disc+$amount.$d.disc.nett_sales|round2}
			{/if}				
            {foreach from=$head_mprice item=n_mprice}
				<td class='r'>{$mprice.$d.$n_mprice.nett_sales|number_format:2|ifzero:'-'}</td>
				{assign var=t_m_disc value=$t_m_disc+$mprice.$d.$n_mprice.nett_sales|round2}
			{/foreach}

			{if $head_not_nett || $head_nett || $head_amount || $head_mprice}
				<td class='r'>
					{assign var=total_day value=$t_selling+$t_n_disc+$t_a_disc+$t_m_disc-$t_disc}
					{$total_day|number_format:2|ifzero:'-'}
				</td>
			{/if}
			{assign var=total_month value=$total_month+$total_day}
		</tr>
	{/foreach}

	<tr class="header">
	    <th>Total</th>
		{foreach from=$head_not_nett_code item=c}
			<td class='r'>{$not_nett_total.$c.selling|number_format:2|ifzero:'-'}</td>
			{assign var=total_t_selling value=$total_t_selling+$not_nett_total.$c.selling|round2}
		{/foreach}
		
		<td class='r'>{$total_t_selling|number_format:2|ifzero:'-'}</td>

		{foreach from=$head_not_nett item=disc}
			<td class='r'>{$not_nett_total.$disc.discount|number_format:2|ifzero:'-'}</td>
		{/foreach}
        {foreach from=$head_nett item=n_disc}
            <td class='r'>{$nett_total.$n_disc.nett_sales|number_format:2|ifzero:'-'}</td>
		{/foreach}
		{if $head_amount}
        	<td class='r'>{$amount_total.nett_sales|number_format:2|ifzero:'-'}</td>
        {/if}
		{foreach from=$head_mprice item=n_mprice}
			<td class='r'>{$mprice_total.$n_mprice.nett_sales|number_format:2|ifzero:'-'}</td>
		{/foreach}
		{if $head_not_nett || $head_nett || $head_amount || $head_mprice}
	    	<td class='r'>{$total_month|number_format:2|ifzero:'-'}</td>
		{/if}
	</tr>
</table>

<!-------------------------------Bottom Table-------------------------------------------------->
<p>
<h3>By Discount (%)</h3>
<table class="report_table rpt_type">
	<tr class="header">
	    <th colspan=3>&nbsp;</th>
	    <th class="detail_width">Total Selling (Amt)</th>
	    <th class="detail_width">Total Nett Selling (Amt)</th>
	    <th class="detail_width">Profit (Amt)</th>
	    <th class="detail_width">Profit (%)</th>
	    <th class="detail_width">Discount (Amt)</th>
	    <th class="detail_width">Supplier Bear</th>
	    <th class="detail_width">Nett Profit (Amt)</th>
	    <th class="detail_width">Nett Profit % Before Bear</th>
	    <th class="detail_width">Nett Profit % After Bear</th>
	</tr>
	{assign var=bottom_profit value=0}
	{assign var=bottom_sub_bear value=0}

 	{assign var=count value=0}
	{assign var=total_selling value=0}
	{assign var=total_profit value=0}
	{assign var=total_profit_p value=0}
	{assign var=total_discount value=0}
	{assign var=total_sup_bear value=0}
	{assign var=total_nett_prof value=0}
	{assign var=total_nett_prof_b value=0}
	{assign var=total_nett_prof_a value=0}

	{foreach from=$code item=c}
		{assign var=subtotal_selling value=0}
		{assign var=subtotal_disc value=0}
		{assign var=subtotal_profit value=0}
		{assign var=subtotal_nett_profit value=0}
		{assign var=subtotal_sup_bear value=0}
        
		{assign var=count value=$count+1}
	<tbody class="price_type_{$count}">
		{assign var=code_row value=$bearing_row.$c+$nett_sales_row.$c+1}
		<tr>
		    <td class="bold" rowspan={$code_row}>{$c}</td>
			<td class="bold">Non-Promo</td>
			{assign var=other value="[Other]"}
			{assign var=sup_percentage value="0%"}
			{assign var=other_nett_profit_a_c value=0}

			{assign var=other_selling_c value=$not_nett_c.$c.$other.$sup_percentage.selling|round2}
			{assign var=other_disc_c value=$not_nett_c.$c.$other.$sup_percentage.disc|round2}

			{assign var=other_nett_selling_c value=$other_selling_c-$other_disc_c}
			{assign var=other_profit_c value=$not_nett_c.$c.$other.$sup_percentage.profit|round2}

			{assign var=other_bear_c value=$not_nett_c.$c.$other.$sup_percentage.sup_bear|round2}

			{assign var=other_nett_profit_c value=$other_profit_c+$other_bear_c-$other_disc_c}

			{assign var=other_nett_profit_b_c value=$other_profit_c-$other_disc_c}

			{if $other_selling_c ne 0}
				{assign var=other_nett_profit_b_c value=$other_nett_profit_b_c/$other_selling_c*100}
			{else}
				{assign var=other_nett_profit_b_c value=0}
			{/if}

			<!-----------Subtotal------------->
			{assign var=subtotal_selling value=$subtotal_selling+$other_selling_c}
			{assign var=subtotal_disc value=$subtotal_disc+$other_disc_c}
			{assign var=subtotal_profit value=$subtotal_profit+$other_profit_c}
			{assign var=subtotal_nett_profit value=$subtotal_nett_profit+$other_nett_profit_c}
			{assign var=subtotal_bear value=$subtotal_bear+$other_bear_c}

			<!------end-------->
			<td  class="bold">Disc [Other]</td>
			<td class="r">{$other_selling_c|default:0|number_format:2|ifzero:'-'}</td>
			<td class="r">{$other_nett_selling_c|default:0|number_format:2|ifzero:'-'}</td>
			<td class="r">{$other_profit_c|default:0|number_format:2|ifzero:'-'}</td>
			<td class="r">{$not_nett_c.$c.$other.$sup_percentage.profit_p|default:0|number_format:2|ifzero:'-':'%'}</td>
			<td class="r">{$other_disc_c|default:0|number_format:2|ifzero:'-'}</td>
			{assign var=subtotal_nett_selling value=$subtotal_selling-$subtotal_disc}

			<td>
				<span>[0%]</span>
				<span class="right">{$other_bear_c|default:0|number_format:2|ifzero:'-'}</span>    
			</td>
			<td class="r">{$other_nett_profit_c|default:0|number_format:2|ifzero:'-'}</td>
			<td class="r">{$other_nett_profit_b_c|default:0|number_format:2|ifzero:'-':'%'}</td>
			<td class="r">{$other_nett_profit_a_c|default:0|number_format:2|ifzero:'-':'%'}</td>
		</tr>

		<!-------------Bearing----------------->
		{if $s_not_nett_head.$c}
			<tr>
			    <td class="bold" rowspan="{$bearing_row.$c}">Bearing</td>
				{foreach name=bearing from=$s_not_nett_head.$c key=disc item=sup_bear}
					{foreach name=sup_b from=$sup_bear item=sup_bear_p}

					{assign var=not_selling_c value=$not_nett_c.$c.$disc.$sup_bear_p.selling|round2}
					{assign var=not_disc_c value=$not_nett_c.$c.$disc.$sup_bear_p.disc|round2}

					{assign var=not_nett_selling_c value=$not_selling_c-$not_disc_c}

					{assign var=not_profit_c value=$not_nett_c.$c.$disc.$sup_bear_p.profit|round2}
					{assign var=not_bear_c value=$not_nett_c.$c.$disc.$sup_bear_p.sup_bear|round2}

					{assign var=not_nett_profit_c value=$not_profit_c+$not_bear_c-$not_disc_c}

					{if $not_nett_selling_c ne 0}
					{assign var=not_nett_profit_b_c value=$not_profit_c-$not_disc_c}
					{assign var=not_nett_profit_b_c value=$not_nett_profit_b_c/$not_nett_selling_c*100}
					{assign var=not_nett_profit_a_c value=$not_nett_profit_c/$not_nett_selling_c*100}
					{/if}
					
 					{if !$smarty.foreach.bearing.first || !$smarty.foreach.sup_b.first}
						</tr>
						<tr>
					{/if}

					<!-----------Subtotal------------->
					{assign var=subtotal_selling value=$subtotal_selling+$not_selling_c}
					{assign var=subtotal_disc value=$subtotal_disc+$not_disc_c}
					{assign var=subtotal_profit value=$subtotal_profit+$not_profit_c}
					{assign var=subtotal_nett_profit value=$subtotal_nett_profit+$not_nett_profit_c}
					{assign var=subtotal_sup_bear value=$subtotal_sup_bear+$not_bear_c}

					<!------end-------->
			        <td class="bold">Disc {$disc}</td>
			        <td class="r">{$not_selling_c|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$not_nett_selling_c|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$not_profit_c|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$not_nett_c.$c.$disc.$sup_bear_p.profit_p|number_format:2|ifzero:'-':'%'}</td>
			        <td class="r">{$not_disc_c|number_format:2|ifzero:'-'}</td>
			        <td>
						<span>[{$sup_bear_p}]</span>
						<span class="right">{$not_bear_c|number_format:2|ifzero:'-'}</span>
					</td>
			        <td class="r">{$not_nett_profit_c|number_format:2|ifzero:'-'}</td>
			        <td class="r">{$not_nett_profit_b_c|number_format:2|ifzero:'-':'%'}</td>

					{assign var=selling_bear value=$not_selling_c|round2}
					{assign var=disc_bear value=$not_disc_c|round2}
					{assign var=true_selling_bear value=$selling_bear-$disc_bear}
					{assign var=profit_bear value=$not_nett_profit_c|round2}

					{if $true_selling_bear ne 0}
						{assign var=not_nett_profit_a_c value=$profit_bear/$true_selling_bear*100}
					{/if}
			        <td class="r">{$not_nett_profit_a_c|number_format:2|ifzero:'-':'%'}</td>

					{/foreach}
				{/foreach}
			</tr>
		{/if}
		
		<!----------------Nett Sales----------------------------->
		{if $s_nett_head.$c}
			<tr>
			    <td class="bold" rowspan="{$nett_sales_row.$c}">Nett Sales</td>
				{foreach name=nett_sales from=$s_nett_head.$c key=net_bear item=other}
					{foreach name=nett_sales2 from=$other item=profit_per}

					{assign var=nett_selling_c value=$nett_c.$c.$net_bear.$profit_per.selling|round2}
					{assign var=nett_disc_c value=$nett_c.$c.$net_bear.$profit_per.disc|round2}

					{assign var=nett_nett_selling_c value=$nett_selling_c-$nett_disc_c}

					{assign var=nett_profit_c value=$nett_c.$c.$net_bear.$profit_per.profit|round2}
					{assign var=nett_bear_c value=$nett_c.$c.$net_bear.$profit_per.sup_bear|round2}

					{assign var=nett_nett_profit_c value=$nett_profit_c+$nett_bear_c}

					{if !$smarty.foreach.nett_sales.first || !$smarty.foreach.nett_sales2.first}
						</tr>
			 			<tr>
					{/if}

					<!-----------Subtotal------------->
					{assign var=subtotal_selling value=$subtotal_selling+$nett_selling_c}
					{assign var=subtotal_disc value=$subtotal_disc+$nett_disc_c}
					{assign var=subtotal_profit value=$subtotal_profit+$nett_profit_c}
					{assign var=subtotal_nett_profit value=$subtotal_nett_profit+$nett_nett_profit_c}
					{assign var=subtotal_sup_bear value=$subtotal_sup_bear+$nett_bear_c}

					<!------end-------->
					<td class="bold">Disc {$net_bear}</td>
	                <td class="r">{$nett_selling_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$nett_nett_selling_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$nett_profit_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$nett_c.$c.$net_bear.$profit_per.profit_p|number_format:2|ifzero:'-':'%'}</td>
	                <td class="r">{$nett_disc_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$nett_bear_c|number_format:2|ifzero:'-'}</td>
		            <td class="r">{$nett_nett_profit_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$nett_c.$c.$net_bear.$profit_per.profit_p|number_format:2|ifzero:'-':'%'}</td>
	                <td class="r">{$nett_c.$c.$net_bear.$profit_per.profit_p|number_format:2|ifzero:'-':'%'}</td>
	                {/foreach}
				{/foreach}
			</tr>
		{/if}
			<tr class="subtotal">
			    <td colspan=3 class="bold">{$c} Subtotal</td>
			    <td class="r">{$subtotal_selling|number_format:2|ifzero:'-'}</td>

			    {assign var=subtotal_nett_selling value=$subtotal_selling-$subtotal_disc}
			    <td class="r">{$subtotal_nett_selling|number_format:2|ifzero:'-'}</td>
			    <td class="r">{$subtotal_profit|number_format:2|ifzero:'-'}</td>
			    
			    {if $subtotal_selling ne 0}
				  {if $subtotal_profit > 0}
					{assign var=subtotal_profit_p value=$subtotal_profit/$subtotal_nett_selling*100}
				  {/if}
				  {if $subtotal_nett_profit > 0}
				    {assign var=subtotal_nett_prof_b value=$subtotal_nett_profit/$subtotal_nett_selling*100}
				  {/if}
				  {if $subtotal_nett_profit > 0}
				    {assign var=subtotal_nett_prof_a value=$subtotal_nett_profit/$subtotal_nett_selling*100}
				  {/if}
				{/if}
			    <td class="r">{$subtotal_profit_p|number_format:2|ifzero:'-':'%'}</td>
			    <td class="r">{$subtotal_disc|number_format:2|ifzero:'-'}</td>
			    <td class="r">{$subtotal_sup_bear|number_format:2|ifzero:'-'}</td>
			    <td class="r">{$subtotal_nett_profit|number_format:2|ifzero:'-'}</td>
			    <td class="r">{$subtotal_nett_prof_b|number_format:2|ifzero:'-':'%'}</td>
		
			    <td class="r">{$subtotal_nett_prof_a|number_format:2|ifzero:'-':'%'}</td>
			</tr>
		</tbody>

		<!-----------Total------------->
		{assign var=total_selling value=$total_selling+$subtotal_selling}
		{assign var=total_discount value=$total_discount+$subtotal_disc}
		{assign var=total_profit value=$total_profit+$subtotal_profit}
		{assign var=total_sup_bear value=$total_sup_bear+$subtotal_sup_bear}
		{assign var=total_nett_prof value=$total_nett_prof+$subtotal_nett_profit}

		<!------end-------->

	{/foreach}
	

	<tr class="header">
	    <td colspan=3 class="bold r">Total</td>
	    <td class="r">{$total_selling|number_format:2|ifzero:'-'}</td>
	    {assign var=total_nett_selling value=$total_selling-$total_discount}
	    <td class="r">{$total_nett_selling|number_format:2|ifzero:'-'}</td>
	    <td class="r">{$total_profit|number_format:2|ifzero:'-'}</td>
	    
	    {if $total_selling ne 0}
			{assign var=total_profit_p value=$total_profit/$total_nett_selling*100}
		    {assign var=total_nett_prof_b value=$total_nett_prof/$total_nett_selling*100}
		    {assign var=total_nett_prof_a value=$total_nett_prof/$total_nett_selling*100}
		{/if}
	    <td class="r">{$total_profit_p|number_format:2|ifzero:'-':'%'}</td>
	    <td class="r">{$total_discount|number_format:2|ifzero:'-'}</td>
	    <td class="r">{$total_sup_bear|number_format:2|ifzero:'-'}</td>
	    <td class="r">{$total_nett_prof|number_format:2|ifzero:'-'}</td>
	    <td class="r">{$total_nett_prof_b|number_format:2|ifzero:'-':'%'}</td>
	    <td class="r">{$total_nett_prof_a|number_format:2|ifzero:'-':'%'}</td>
	</tr>
</table>
</p>

{assign var=bottom_profit value=$bottom_profit+$total_profit}
{assign var=bottom_sub_bear value=$bottom_sub_bear+$total_sup_bear}

{if $amount_sales_row}
<p>
	<h3>By Amount</h3>
	<table class="report_table rpt_type">
		<tr class="header">
		    <th colspan=3>&nbsp;</th>
		    <th class="detail_width">Total Selling (Amt)</th>
		    <th class="detail_width">Total Nett Selling (Amt)</th>
		    <th class="detail_width">Profit (Amt)</th>
		    <th class="detail_width">Profit (%)</th>
		    <th class="detail_width">Discount (Amt)</th>
		    <th class="detail_width">Supplier Bear</th>
		    <th class="detail_width">Nett Profit (Amt)</th>
		    <th class="detail_width">Nett Profit % Before Bear</th>
		    <th class="detail_width">Nett Profit % After Bear</th>
		</tr>

 	{assign var=count value=0}
	{assign var=total_selling value=0}
	{assign var=total_profit value=0}
	{assign var=total_profit_p value=0}
	{assign var=total_discount value=0}
	{assign var=total_sup_bear value=0}
	{assign var=total_nett_prof value=0}
	{assign var=total_nett_prof_b value=0}
	{assign var=total_nett_prof_a value=0}
	
	{foreach from=$amount_sales_row key=c item=a_rowspan}
		{assign var=subtotal_selling value=0}
		{assign var=subtotal_disc value=0}
		{assign var=subtotal_profit value=0}
		{assign var=subtotal_nett_profit value=0}
		{assign var=subtotal_sup_bear value=0}

		{assign var=count value=$count+1}
		<tbody class="price_type_{$count}">
			<tr>
				<th rowspan="{$a_rowspan}">{$c}</th>
				<th rowspan="{$a_rowspan}">Nett Sales</th>
				{foreach name=amount_sales from=$s_amount_head.$c key=other item=net_bear}
				
					{assign var=amount_selling_c value=$amount_c.$c.$net_bear.selling|round2}
					{assign var=amount_disc_c value=$amount_c.$c.$net_bear.disc|round2}
					{assign var=amount_amount_selling_c value=$amount_selling_c-$amount_disc_c}
					{assign var=amount_profit_c value=$amount_c.$c.$net_bear.profit|round2}
					{assign var=amount_bear_c value=$amount_c.$c.$net_bear.sup_bear|round2}
					{assign var=amount_nett_profit_c value=$amount_profit_c+$amount_bear_c}
				    {assign var=amount_nett_prof_b value=$amount_profit_c-$amount_disc_c}
				    {if $amount_amount_selling_c ne 0}
					    {assign var=amount_nett_prof_b value=$amount_nett_prof_b/$amount_amount_selling_c*100}
					{/if}
					{if !$smarty.foreach.amount_sales.first}
						</tr>
			 			<tr>
					{/if}
	
					<!-----------Subtotal------------->
					{assign var=subtotal_selling value=$subtotal_selling+$amount_selling_c}
					{assign var=subtotal_disc value=$subtotal_disc+$amount_disc_c}
					{assign var=subtotal_profit value=$subtotal_profit+$amount_profit_c}
					{assign var=subtotal_nett_profit value=$subtotal_nett_profit+$amount_nett_profit_c}
					{assign var=subtotal_sup_bear value=$subtotal_sup_bear+$amount_bear_c}
	
					<!------end-------->
					<td class="bold">Profit {$net_bear}</td>
	                <td class="r">{$amount_selling_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$amount_amount_selling_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$amount_profit_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$amount_c.$c.$net_bear.profit_p|number_format:2|ifzero:'-':'%'}</td>
	                <td class="r">{$amount_disc_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$amount_bear_c|number_format:2|ifzero:'-'}</td>
		            <td class="r">{$amount_nett_profit_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$amount_c.$c.$net_bear.profit_p|number_format:2|ifzero:'-':'%'}</td>
	                <td class="r">{$amount_c.$c.$net_bear.profit_p|number_format:2|ifzero:'-':'%'}</td>
				{/foreach}
			</tr>		
	
			<!-----------Total------------->
			{assign var=total_selling value=$total_selling+$subtotal_selling}
			{assign var=total_discount value=$total_discount+$subtotal_disc}
			{assign var=total_profit value=$total_profit+$subtotal_profit}
			{assign var=total_sup_bear value=$total_sup_bear+$subtotal_sup_bear}
			{assign var=total_nett_prof value=$total_nett_prof+$subtotal_nett_profit}
			<!------end-------->
		{/foreach}
		
	</tbody>
		<tr class="header">
		    <td colspan=3 class="bold r">Total</td>
		    <td class="r">{$total_selling|number_format:2|ifzero:'-'}</td>
		    {assign var=total_nett_selling value=$total_selling-$total_discount|round2}
		    <td class="r">{$total_nett_selling|number_format:2|ifzero:'-'}</td>
		    <td class="r">{$total_profit|number_format:2|ifzero:'-'}</td>
		    
		    {if $total_selling ne 0}
				{assign var=total_profit_p value=$total_profit/$total_nett_selling*100}
			    {assign var=total_nett_prof_b value=$total_profit}
			    {assign var=total_nett_prof_b value=$total_nett_prof_b/$total_nett_selling*100}
			    {assign var=total_nett_prof_a value=$total_nett_prof/$total_nett_selling*100}
			{/if}
		    <td class="r">{$total_profit_p|number_format:2|ifzero:'-':'%'}</td>
		    <td class="r">{$total_discount|number_format:2|ifzero:'-'}</td>
		    <td class="r">{$total_sup_bear|number_format:2|ifzero:'-'}</td>
		    <td class="r">{$total_nett_prof|number_format:2|ifzero:'-'}</td>	    
		    <td class="r">{$total_nett_prof_b|number_format:2|ifzero:'-':'%'}</td>
		    <td class="r">{$total_nett_prof_a|number_format:2|ifzero:'-':'%'}</td>
		</tr>
	</table>
</p>

{assign var=bottom_profit value=$bottom_profit+$total_profit}
{assign var=bottom_sub_bear value=$bottom_sub_bear+$total_sup_bear}

{/if}

{if $mprice_sales_row}
{foreach from=$mprice_sales_row key=c item=mdata}
<p>
	<h3>By {$c}</h3>
	<table class="report_table rpt_type">
		<tr class="header">
		    <th class="detail_width">ARMS Code</th>
		    <th class="detail_width">Description</th>
		    <th class="detail_width">Total Qty</th>
		    <th class="detail_width">Total Cost (Amt)</th>
		    <th class="detail_width">Total Selling (Amt)</th>
		    <th class="detail_width">Profit (%)</th>
		    <th class="detail_width">Supplier Bear</th>
		    <th class="detail_width">Nett Profit (Amt)</th>
		    <th class="detail_width">Nett Profit % Before Bear</th>
		    <th class="detail_width">Nett Profit % After Bear</th>
		</tr>

 	{assign var=count value=0}
	{assign var=total_qty value=0}
	{assign var=total_cost value=0}
	{assign var=total_selling value=0}
	{assign var=total_profit value=0}
	{assign var=total_profit_p value=0}
	{assign var=total_sup_bear value=0}
	{assign var=total_nett_prof value=0}
	{assign var=total_nett_prof_b value=0}
	{assign var=total_nett_prof_a value=0}
	
	{foreach from=$mdata key=sid item=moredata}
		{assign var=count value=$count+1}
		<tbody class="price_type_{$count}">
	
			<tr>
				<th rowspan="{$mdata.rowspan}">{$moredata.sku_item_code}</th>
				<th rowspan="{$mdata.rowspan}">{$moredata.description}</th>
				{foreach name=mprice_sales from=$s_mprice_head.$c.$sid item=net_bear}
					
					{assign var=mprice_qty_c value=$mprice_c.$c.$sid.$net_bear.qty}
					{assign var=mprice_cost_c value=$mprice_c.$c.$sid.$net_bear.cost}
					{assign var=mprice_selling_c value=$mprice_c.$c.$sid.$net_bear.selling}
					{assign var=mprice_nett_profit_c value=$mprice_selling_c-$mprice_cost_c}
					{assign var=mprice_profit_p value=$mprice_nett_profit_c/$mprice_selling_c*100}
					{assign var=mprice_bear_c value=$mprice_c.$c.$sid.$net_bear.sup_bear}
					{assign var=mprice_nett_profit_a value=$mprice_nett_profit_c+$mprice_bear_c}
					{assign var=mprice_nett_profit_a value=$mprice_nett_profit_a/$mprice_selling_c*100}

					{if !$smarty.foreach.mprice_sales.first}
						</tr>
						<tr>
						<td></td>
						<td></td>
					{/if}
	
	                <td class="r">{$mprice_qty_c|ifzero:'-'}</td>
	                <td class="r">{$mprice_cost_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$mprice_selling_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$mprice_profit_p|number_format:2|ifzero:'-':'%'}</td>
	                <td class="r">{$mprice_bear_c|number_format:2|ifzero:'-'}</td>
		            <td class="r">{$mprice_nett_profit_c|number_format:2|ifzero:'-'}</td>
	                <td class="r">{$mprice_profit_p|number_format:2|ifzero:'-':'%'}</td>
	                <td class="r">{$mprice_nett_profit_a|number_format:2|ifzero:'-':'%'}</td>
	                               
	                <!--------Total Calculate--------->
					{assign var=total_qty value=$total_qty+$mprice_qty_c}
					{assign var=total_cost value=$total_cost+$mprice_cost_c}
					{assign var=total_selling value=$total_selling+$mprice_selling_c}
					{assign var=total_sup_bear value=$total_sup_bear+$mprice_bear_c}
	                <!-------------end--------->
	                
				{/foreach}
			</tr>	
		
		</tbody>
		{/foreach}
		<tr class="header">
		    <td colspan=2 class="bold r">Total</td>
		    <td class="r">{$total_qty|ifzero:'-'}</td>
		    <td class="r">{$total_cost|number_format:2|ifzero:'-'}</td>
		    <td class="r">{$total_selling|number_format:2|ifzero:'-'}</td>
		    
		    {if $total_selling ne 0}
				{assign var=total_profit_p value=$total_selling-$total_cost}
				{assign var=total_profit_p value=$total_profit_p/$total_selling*100}
			{/if}
		    <td class="r">{$total_profit_p|number_format:2|ifzero:'-':'%'}</td>
		    <td class="r">{$total_sup_bear|number_format:2|ifzero:'-'}</td>

		    {assign var=total_nett_prof value=$total_selling-$total_cost}
		    <td class="r">{$total_nett_prof|number_format:2|ifzero:'-'}</td>
		    
		    {assign var=total_nett_prof_b value=$total_nett_prof/$total_selling*100}
		    <td class="r">{$total_nett_prof_b|number_format:2|ifzero:'-':'%'}</td>

		    {assign var=total_nett_prof_a value=$total_nett_prof+$total_sup_bear}	
		    {assign var=total_nett_prof_a value=$total_nett_prof_a/$total_selling*100}
		    <td class="r">{$total_nett_prof_a|number_format:2|ifzero:'-':'%'}</td>
		</tr>
	</table>
</p>
		{assign var=bottom_gwp_pwp value=$bottom_gwp_pwp+$total_nett_prof}
{/foreach}

{/if}

<table>
	<tr>
	    <td width="100">&nbsp;</td>
	    <td>
			<table class="report_table">
				<tr>
					<th class='r' width="250">Profit</th>
					<td class='r' width="80">{$bottom_profit|number_format:2|ifzero:'-'}</td>
   				</tr>
				<tr>
					<th class='r'>Supplier Bearing</th>
					<td class='r'>{$bottom_sub_bear|number_format:2|ifzero:'-'}</td>
   				</tr>
				<tr>
					<th class='r'>Gifts / PWP</th>
                    <td class='r'>{$bottom_gwp_pwp|number_format:2|ifzero:'-'}</td>
   				</tr>
				<tr>
					<th class='r'>Short / Over</th>
					<td>&nbsp;</td>
   				</tr>
				<tr>
					<th class='r'>Net Profit</th>
					<td class='r'>&nbsp;</td>
   				</tr>
				<tr>
					<th class='r'>Net %</th>
					<td class='r'>&nbsp;</td>
   				</tr>
			</table>
		</td>
		<td width="100">&nbsp;</td>
	    <td>
			<table class="report_table">
				<tr>
					<th colspan=2 align="left"><u>For Account Purpose</u></th>
				</tr>
				<tr>
					<th align="left">Invoice</th>
					<td width="80">&nbsp;</td>
				</tr>
				<tr>
					<th align="left">Bearing</th>
					<td class='r'>&nbsp;</td>
				</tr>
				<tr>
					<th align="left">Short / Over</th>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<th align="left">Others</th>
					<td>&nbsp;</td>
				</tr>
				<tr>
					<th align="left">Amount To Pay</th>
					<td>&nbsp;</td>
				</tr>
			</table>
		</td>
	</tr>
</table>