{*
12/14/2011 10:07:59 AM Alex
- reconstruct to same as summary mode

*}

{foreach from=$code item=c}

{assign var=total_month value=0}

{if $smarty.request.code_type eq "All"}
<h3>Price Type: {$c}</h3>
{/if}

	<table class="report_table" id="report_tbl">
	<col class="bg_grey">
	<col class="bg_lyellow">
	{if $head_not_nett.$c.non_promo}
		<col class="bg_lyellow">
	{/if}
	{if $head_not_nett.$c.promo}
		<col class="bg_yellow">
	{/if}
	{foreach name="bearing" from=$head_not_nett.$c.promo item=disc}
		<col class="bg_yellow">
	{/foreach}
    {foreach name="nett_sales" from=$head_nett.$c.promo item=n_disc}
		<col class="bg_lblue">
	{/foreach}
	{if $head_amount.$c.promo}
		<col class="bg_lblue">
	{/if}

	<col class="bg_pink">

	{assign var=bearing_row value=$smarty.foreach.bearing.total+1}
	{assign var=nett_sales_row value=$smarty.foreach.nett_sales.total}
	{if $head_amount.$c.promo}
		{assign var=nett_sales_row value=$nett_sales_row+1}
	{/if}
		<tr class="header">
			<th width="80px" rowspan="3">
				{if $smarty.request.branch_id eq 'All'}
					Branches
				{elseif $smarty.request.month eq 'All'}
				    Month
				{else}
				    Day
				{/if}
			</th>
			<th {if $head_not_nett.$c.non_promo}colspan="2" {/if}>Non-Promo</th>
			{if $head_not_nett.$c.promo}
				<th colspan="{$bearing_row}">Bearing </th>
			{/if}
			{if $head_nett.$c.promo || $head_amount.$c.promo}
				<th colspan="{$nett_sales_row}">Net Sales </th>
			{/if}
			<th class="detail_width" rowspan="3">Total</th>
		</tr>
		<tr>
		    <th class="detail_width" rowspan="2">Selling</th>
   			{foreach from=$head_not_nett.$c.non_promo item=other_disc}
				<th class="detail_width" rowspan="2">Disc {$other_disc}</th>
			{/foreach}
			{if $head_not_nett.$c.promo}
	            <th class="detail_width" rowspan="2">Selling</th>
				{foreach from=$head_not_nett.$c.promo item=disc}
					<th class="detail_width" rowspan="2">Disc {$disc}</th>
				{/foreach}
			{/if}
			{if $smarty.foreach.nett_sales.total > 0 }
				<th colspan="{$smarty.foreach.nett_sales.total}">By Discount (%)</th>
			{/if}
			{if $head_amount.$c.promo}
				<th rowspan="2">By Amount</th>
			{/if}			
		</tr>
	{if $smarty.foreach.nett_sales.total > 0 || $head_amount}
		<tr>
	        {foreach from=$head_nett.$c.promo item=n_disc}
				<th>{$n_disc}</th>
			{/foreach}
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
			{assign var=amt_selling value=0}
			{assign var=total_day value=0}

			<tr>
				<td class="bold">{$m_or_d}</td>
                {assign var=tnp_selling value=$not_nett.$d.$c.np_selling}
				<td class='r'>{$tnp_selling|number_format:2|ifzero:'-'}</td>
	   			{foreach from=$head_not_nett.$c.non_promo item=other_disc}
					<td class='r'>{$not_nett.$d.$c.disc.$other_disc.discount|number_format:2|ifzero:'-'}</td>
       				{assign var=t_disc value=$t_disc+$not_nett.$d.$c.disc.$other_disc.discount}
				{/foreach}
                {assign var=t_selling value=$not_nett.$d.$c.selling}
				{if $head_not_nett.$c.promo}
					<td class='r'>{$t_selling|number_format:2|ifzero:'-'}</td>
	
					{foreach from=$head_not_nett.$c.promo item=disc}
						<td class='r'>{$not_nett.$d.$c.disc.$disc.discount|number_format:2|ifzero:'-'}</td>
	    				{assign var=t_disc value=$t_disc+$not_nett.$d.$c.disc.$disc.discount}
					{/foreach}
				{/if}
	            {foreach from=$head_nett.$c.promo item=n_disc}
					<td class='r'>{$nett.$d.$c.disc.$n_disc.nett_sales|number_format:2|ifzero:'-'}</td>
				{*	<td class='r'>{$nett.$d.$c.disc.$n_disc.cost|number_format:2|ifzero:'-'}</td>	*}
					{assign var=t_n_disc value=$t_n_disc+$nett.$d.$c.disc.$n_disc.nett_sales}
				{/foreach}
				{if $head_amount.$c.promo}
					<td class='r'>{$amount.$d.$c.disc.nett_sales|number_format:2|ifzero:'-'}</td>
					{assign var=amt_selling value=$amt_selling+$amount.$d.$c.disc.nett_sales}
				{/if}
				<td class='r'>
					{assign var=total_day value=$tnp_selling+$t_selling+$amt_selling+$t_n_disc-$t_disc}
					{$total_day|number_format:2|ifzero:'-'}
				</td>

				{assign var=total_month value=$total_month+$total_day}
			</tr>
		{/foreach}
		
		<tr class="header">
		    <td class="bold">Total</td>
		    <td class='r'>{$not_nett_total.$c.np_selling|number_format:2|ifzero:'-'}</td>
   			{foreach from=$head_not_nett.$c.non_promo item=other_disc}
				<td class='r'>{$not_nett_total.$c.$other_disc.discount|number_format:2|ifzero:'-'}</td>
			{/foreach}
			{if $head_not_nett.$c.promo}
				<td class='r'>{$not_nett_total.$c.selling|number_format:2|ifzero:'-'}</td>
				{foreach from=$head_not_nett.$c.promo item=disc}
					<td class='r'>{$not_nett_total.$c.$disc.discount|number_format:2|ifzero:'-'}</td>
				{/foreach}
			{/if}
	        {foreach from=$head_nett.$c.promo item=n_disc}
                <td class='r'>{$nett_total.$c.$n_disc.nett_sales|number_format:2|ifzero:'-'}</td>
			{*	<td class='r'>{$nett_total.$c.$n_disc.cost|number_format:2|ifzero:'-'}</td>	*}
			{/foreach}
			{if $head_amount.$c.promo}
				<td class='r'>{$amount_total.$c.nett_sales|number_format:2|ifzero:'-'}</td>
			{/if}
		    <td class='r'>{$total_month|number_format:2|ifzero:'-'}</td>
		
		</tr>
		
		
	</table>

{/foreach}
