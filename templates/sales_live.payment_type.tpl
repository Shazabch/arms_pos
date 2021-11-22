{*
7/18/2011 3:04:11 PM Justin
- Added running total after total row.

10/24/2011 6:37:43 PM Justin
- Changed the word "Running Total" become "Accumulated Total".

3/29/2012 10:44:14 AM Justin
- Replaced the existing payment type to use custom one instead of using pos_config.
- Added to pickup Mix & Match discount amount.

3/21/2014 4:14 PM Justin
- Enhanced to show custom payment type label if found it is set.

8/3/2016 4:20 PM Andy
- Fix location description empty bug.
*}
{capture assign=hrcount}{count var=$payment_type}{/capture}
<div style="margin-top:-3em;margin-left:530px;position:absolute;white-space:nowrap;"><b class="form-label">Last updated: {$smarty.now|date_format:'%I:%M:%S %p'}</b></div>
<table cellpadding=2 cellspacing=2 border=0  width="100%">
	<thead class="bg-gray-100 fs-08" style="height: 25px;">
		<tr >
			<th nowrap>{if $curr_lvl != $level_priv}{$lvl_desc}<br><a href="javascript:list_payment_type('{$tab_slctd}', '{$prev_lvl}', '{$payment_type_lvl2_bid}')">Back</a>{/if}</th>
			{foreach from=$payment_type key=p item=pt}
				<th nowrap>{$pos_config.payment_type_label.$pt|default:$pt}</th>
			{/foreach}
			<th nowrap>Total</th>
		</tr>
	</thead>
	{foreach from=$col_list item=type key=t}
	<tr>
		{assign var=type_desc value=$type}
		{if !$type_desc}{assign var=type_desc value='Untitled'}{/if}
		<th class="text-primary" nowrap>
			<div style="float:left;background-color:{$type|random_color};width:5px;height:5px;margin:4px;border:1px solid black;"></div>
			{if $next_lvl != '' && $paytype_row_total.$type.amt != ''}<a href="javascript:list_payment_type('{$tab_slctd}', '{$next_lvl}', '{$t}')">{$type_desc}</a>{else}{if $curr_lvl == 3 && $paytype_row_total.$type.amt != '' && $tab_slctd == 1}<a href="javascript:tran_details('{$payment_type_lvl2_bid|default:$smarty.request.branch_id}', '{$t}')">{$type_desc}</a>{else}{$type_desc}{/if}{/if}
		</th>
			{foreach from=$payment_type key=p item=pt name=ptc}
				<td align=right {if $smarty.foreach.ptc.iteration%2 == 0} bgcolor="#EEF6FF,#DEE6FF"{/if} nowrap>{$paytype_data.$type.$pt.amt|number_format:$number_format|ifzero:"-"}</td>
				{if $max_figure < $paytype_data.$type.$pt.amt}{assign var=max_figure value=$paytype_data.$type.$pt.amt}{/if}
			{/foreach}
		<th align=right bgcolor="#FFF0FF" nowrap>{$paytype_row_total.$type.amt|number_format:$number_format|ifzero:"-"}</th>
	</tr>
	{/foreach}
	<tr class="bg-gray-100">
		<th nowrap>Total</th>
		{foreach from=$payment_type key=p item=pt}
			<th align=right nowrap>{$paytype_col_total.$pt.amt|number_format:$number_format|ifzero:"-"}</th>
		{/foreach}
		<th align=right rowspan="2" nowrap>{$paytype_grand_total.amt|number_format:$number_format|ifzero:"-"}</th>
	</tr>
	<tr class="bg-gray-100">
		<th nowrap>Accumulated Total</th>
		{foreach from=$payment_type key=p item=pt}
			{assign var=running_ttl value=$running_ttl+$paytype_col_total.$pt.amt}
			<th align=right nowrap>{$running_ttl|number_format:$number_format|ifzero:"-"}</th>
		{/foreach}
	</tr>
</table>
<input type=hidden name="payment_type_lvl2_bid" id="payment_type_lvl2_bid" value="{$payment_type_lvl2_bid}">
{literal}
<script>

ptype_chart_data_pc1 = {
	    "title": {
	        "text": "Total Sales by Payment Type"
	    }, 
	  	"bg_colour": "#ffffff",
{/literal}
	  "elements":[
	    {ldelim}
	      "type":"pie",
          "alpha": 0.5,
		  "animate":[{ldelim}"type":"bounce","distance":5{rdelim},{ldelim}"type":"fade"{rdelim}],
          "border": 2, 
	      "colour": [
			  		 {foreach from=$payment_type key=p item=pt name=pt_type}
						  "{$pt|random_color}"
						{if !$smarty.foreach.pt_type.last},{/if}
					 {/foreach}
					],
		  "start-angle": 0, 
	      "values":[
					{foreach from=$payment_type key=p item=pt name=pt_type}
						{ldelim}{if $paytype_col_total.$pt.amt}
						  "label": "{$pos_config.payment_type_label.$pt|default:$pt}", "value": int("{$paytype_col_total.$pt.amt|number_format:$number_format}"), "colour": "{$pt|random_color}", "tip":"{$pos_config.payment_type_label.$pt|default:$pt}\n{$pt_amt_type}{$paytype_col_total.$pt.amt|number_format:$number_format}"{else}"value":""{/if}{rdelim}
						{if !$smarty.foreach.pt_type.last},{/if}
					{/foreach}
				   ]
	    {rdelim}
	  ] 	
{literal}
};

ptype_chart_data_pc2 = {
{/literal}
	    "title": {ldelim}
	        "text": "Total Sales by {$ptype_level_type}"
	    {rdelim}, 
	  	"bg_colour": "#ffffff",

	  "elements":[
	    {ldelim}
	      "type":"pie",
          "alpha": 0.5,
		  "animate":[{ldelim}"type":"bounce","distance":5{rdelim},{ldelim}"type":"fade"{rdelim}],
          "border": 2, 
	      "colour": [
				  		{foreach from=$col_list item=type key=t name=col_type}
				    		  	{if $type}
								{assign var=pt_set_type value=$type}
							{else}
								{assign var=pt_set_type value="Untitled"}
							{/if}
							"{$pt_set_type|random_color}"
							{if !$smarty.foreach.col_type.last},{/if}
				  		{/foreach}
					],
		  "start-angle": 0, 
	      "values":[
			  		{foreach from=$col_list item=type key=t name=col_type}
			  		  	{if $type}
							{assign var=pt_set_type value=$type}
						{else}
							{assign var=pt_set_type value="Untitled"}
						{/if}
			  			{ldelim}{if $paytype_row_total.$type.amt}
							"label": "{$pt_set_type}", "value": int("{$paytype_row_total.$type.amt|number_format:$number_format}"), "colour": "{$pt_set_type|random_color}", "tip":"{$pt_set_type}\n{$pt_amt_type}{$paytype_row_total.$type.amt|number_format:$number_format}"{else}"value":""{/if}{rdelim}
						{if !$smarty.foreach.col_type.last},{/if}
			  		{/foreach}
				   ]
	    {rdelim}
	  ] 	
{literal}
};

update_ptype_chart();

</script>
{/literal}
