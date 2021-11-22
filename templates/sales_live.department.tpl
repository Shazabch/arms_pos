{*
7/18/2011 3:04:11 PM Justin
- Added running total after total row.

10/24/2011 6:37:43 PM Justin
- Changed the word "Running Total" become "Accumulated Total".

12/30/2011 10:28:43 AM Justin
- Fixed the category shows empty description.

8/10/2012 10:03 AM Justin
- Bug fixed on showing empty amount and description.

1/4/2016 1:48 PM Qiu Ying
- Changed to 2 decimal places
- Add columns (Service Charges, Mix & Match Discount and Receipt Discount)

8/16/2016 10:07 AM Andy
- Enhanced to check Over and Deposit.

4/18/2017 11:44 AM Justin
- Enhanced to allow user can go into sub-category until last level.

8/2/2018 5:52 PM Andy
- Hide Accumulated Total when view Department Count & Buying Power.
- Fixed Department count calculation error.

10/5/2020 4:18 PM Shane
- Added Sales Qty tab
*}
{capture assign=deptcount}{count var=$department}{/capture}
<div style="margin-top:-3em;margin-left:540px;position:absolute;white-space:nowrap;"><b class="form-label">Last updated: {$smarty.now|date_format:'%I:%M:%S %p'}</b></div>
<div style="margin-top:-1.5em;margin-left:450px;position:absolute;white-space:nowrap;"></div>
<div class="table-responsive">
	<table cellpadding=2 cellspacing=2  width="100%">
		<thead class="bg-gray-100 fs-08" style="height: 25px;">
			<tr >
				<th nowrap>{if $curr_lvl != $level_priv || $cat_level >= 2}{if $lvl_desc}{$lvl_desc}<br>{/if}{if $cat_desc}{$cat_desc}<br>{/if}<a href="javascript:list_department('{$tab_slctd}', '{$level_priv}', '{$department_lvl2_bid}', '{if $department_lvl2_bid}{$col_level_id}{/if}', '{if $department_lvl2_bid}{$cat_level-1}{/if}')">Back</a>{/if}</th>
				{foreach from=$department key=d item=dept}
					<th nowrap colspan={if $tab_slctd <= 2}2{else}1{/if}>{if $dept && $category_have_subcat.$d}<a href="javascript:list_department('{$tab_slctd}', '{$curr_lvl}', '{$lid|default:$department_lvl2_bid}', '{$d}', '{$cat_level}')">{$dept|default:'Uncategorised'}</a>{else}{$dept|default:'Uncategorised'}{/if}</th>
				{/foreach}
				<th nowrap>{if $tab_slctd != 3}Items Total{else}Total{/if}</th>
				{if $tab_slctd != 3 && $tab_slctd != 5}
					<th nowrap>Service Charges</th>
					<th nowrap>Discount</th>
					<th nowrap>Mix & Match Disc</th>
					<th nowrap>Rounding</th>
					<th nowrap>Over</th>
					{if $got_deposit}
						<th nowrap>Deposit Received</th>
						<th nowrap>Deposit Used + Refund</th>
					{/if}
					<th nowrap>Total</th>
				{/if}
			</tr>
		</thead>
		{if $tab_slctd == 3 || $tab_slctd == 5}
			{assign var=decimal_places value="0"}
		{else}
			{assign var=decimal_places value="2"}
		{/if}
		{foreach from=$col_list item=type key=t}
		{if !$type}
			{assign var=type value="Untitled"}
		{/if}
		<tr>
			<th class="text-primary" nowrap>
				<div style="float:left;background-color:{$type|random_color};width:5px;height:5px;margin:4px;border:1px solid black;"></div>
				{if $next_lvl != ''  && $dept_total.$type.amt != ''}<a href="javascript:list_department('{$tab_slctd}', '{$next_lvl}', '{$t}', '{$col_level_id}', '{$cat_level-1}')">{if $type}{$type}{else}Untitled{/if}</a>{else}{if $curr_lvl == 3 && $dept_total.$type.amt != '' && $tab_slctd == 1}<a href="javascript:tran_details('{$department_lvl2_bid|default:$smarty.request.branch_id}', '{$t}', '{$col_level_id}')">{$type}</a>{else}{$type}{/if}{/if}
			</th>
			{foreach from=$department key=d item=dept name=dc}
				<td align=right {if $tab_slctd >2}{if $smarty.foreach.dc.iteration%2 == 0} bgcolor="#EEF6FF,#DEE6FF"{/if}{else}bgcolor="#EEF6FF,#DEE6FF"{/if}>{$dept_data.$d.$type|number_format:$decimal_places|ifzero:"-"}</td>
				{if $tab_slctd <= 2}
					{if $dept_row_total.$type.amt != 0}
						{assign var=row_total value=$dept_row_total.$type.amt}
						{assign var=contri value=$dept_data.$d.$type/$row_total}
					{else}
						{assign var=contri value=0}
					{/if}
					<td align=right>{$contri*100|number_format:1|ifzero:"-"}{if $contri*100|number_format!=0}%{/if}</td>
				{/if}
				{if $max_figure < $dept_data.$d.$type}{assign var=max_figure value=$dept_data.$d.$type}{/if}
			{/foreach}
			<th class="bg-gray-100" nowrap>
				{if $dept_row_total.$type.count}
					{$dept_row_total.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}
				{else}-{/if}
			</th>
			{assign var=grand_total value=$grand_total+$row_total}
			{if $tab_slctd != 3 && $tab_slctd != 5}
				<td align=right nowrap bgcolor="#EEF6FF,#DEE6FF">{if $dept_row_total.$type.count}{$dept_service_charges_total.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
				<td align=right nowrap>{if $dept_row_total.$type.count}{$dept_discount.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
				<td align=right nowrap bgcolor="#EEF6FF,#DEE6FF">{if $dept_row_total.$type.count}{$dept_mm_disc.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
				<td align=right nowrap>{if $dept_row_total.$type.count}{$dept_rounding.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
				<td align=right nowrap>{if $dept_row_total.$type.count}{$dept_over.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
				{if $got_deposit}
					<td align=right nowrap>{if $dept_row_total.$type.count}{$dept_deposit_rcv.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
					<td align=right nowrap>{if $dept_row_total.$type.count}{$dept_deposit_used.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</td>
				{/if}
				<th class="bg-gray-100">{if $dept_row_total.$type.count}{$dept_total.$type.amt/$dept_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
				
			{/if}
		</tr>
		{/foreach}
		<tr class="bg-gray-100">
			<th nowrap>Total</th>
			{foreach from=$department key=d item=dept}
				<th align=right nowrap>{if $dept_col_total.$d.count}{$dept_col_total.$d.amt/$dept_col_total.$d.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
				{if $tab_slctd <= 2}
					{if $dept_col_total.$d.amt != 0}
						{assign var=total_col_contri value=$dept_col_total.$d.amt/$dept_grand_total}
					{else}
						{assign var=total_col_contri value=0}
					{/if}
					<th align=right nowrap>{$total_col_contri*100|number_format:1|ifzero:"-"}{if $total_col_contri*100|number_format!=0}%{/if}</th>
				{/if}
			{/foreach}
			<th align=right rowspan="2" nowrap>{$dept_grand_total|number_format:$decimal_places|ifzero:"-"}</th>
			{if $tab_slctd != 3 && $tab_slctd != 5}
				{if $temp}
					{foreach from=$temp item=items}
						<th align=right nowrap>{$items|number_format:$decimal_places|ifzero:"-"}</th>
					{/foreach}
				{else}
					<th align=right nowrap>-</th>
					<th align=right nowrap>-</th>
					<th align=right nowrap>-</th>
					<th align=right nowrap>-</th>
				{/if}
				<th align=right nowrap rowspan="2">{$total|number_format:$decimal_places|ifzero:"-"}</th>
			{/if}
		</tr>
		<tr class="bg-gray-100" style="{if $tab_slctd == 3 || $tab_slctd == 4}display:none;{/if}">
			<th nowrap>Accumulated Total</th>
			{foreach from=$department key=d item=dept}
				{if $dept_col_total.$d.count}
					{assign var=running_ttl_amt value=$running_ttl_amt+$dept_col_total.$d.amt}
					{if $tab_slctd <= 2 && $dept_col_total.$d.amt ne 0}
						
					{/if}
				{/if}
				<th align=right nowrap>{$running_ttl_amt|number_format:$decimal_places|ifzero:"-"}</th>
				{if $tab_slctd <= 2}
					{if $dept_col_total.$d.amt != 0}
						{assign var=running_ttl_contri value=$running_ttl_contri+$dept_col_total.$d.amt/$dept_grand_total*100}
					{/if}
					<th align=right nowrap>{$running_ttl_contri|number_format:1|ifzero:"-"}{if $running_ttl_contri|number_format!=0}%{/if}</th>
				{/if}
			{/foreach}
			{if $tab_slctd != 3 && $tab_slctd != 5}
				{foreach from=$temp_acc item=items}
					<th align=right nowrap>{$items|number_format:$decimal_places|ifzero:"-"}</th>
				{/foreach}
			{/if}
		</tr>
	</table>
</div>
<input type=hidden name="department_lvl2_bid" id="department_lvl2_bid" value="{$department_lvl2_bid}">

{literal}
<script>
department_chart_data = {
	  "bg_colour":'#ffffff',
	  "y_legend":{
	    "text": "Sales Amt / No of transaction",
	    "style": "{color: #736AFF; font-size: 12px;}"
	  },
	  
{/literal}
	  "elements":[
	  {foreach from=$col_list name=b item=type key=t}
	  	{if $type}
			{assign var=d_set_type value=$type}
		{else}
			{assign var=d_set_type value="Untitled"}
		{/if}
	    {ldelim}
	      "type":"bar",
	      "alpha":0.7,
		  "colour":"{$d_set_type|random_color}",
	      "font-size":8,
	      "offset":6,
	      "values":[
			  		{foreach from=$department key=d item=dept name=dc}
			  			{ldelim}
							{if $dept_data.$d.$d_set_type != ''}
								"top":{$dept_data.$d.$d_set_type}, "tip": "{$d_set_type}<br>{$d_amt_type}{$dept_data.$d.$d_set_type|number_format:$decimal_places}"
			      			{else}
			      				"top":0, "tip": "{$d_set_type}<br>{$d_amt_type}0"
			      			{/if}
		      			{rdelim}
						{if !$smarty.foreach.dc.last},{/if}
			  		{/foreach}
				   ]
	    {rdelim}{if !$smarty.foreach.b.last},{/if}
	    {/foreach}
	  ],
	
	  "y_axis":{ldelim}
	    "stroke":      4,
	    "tick_length": 3,
	    "colour":      "#d000d0",
	    "grid_colour": "#00ff00",
	    "offset":      0,
	    "steps" : 	   {if $max_figure<10}10{else}{$max_figure+100-$max_figure%100|intval}{/if}/5,
	    "max":         {if $max_figure<10}10{else}{$max_figure+100-$max_figure%100|intval}{/if}
	  {rdelim},
	  
	  
	  "x_axis":{ldelim}
	    "stroke":       1,
	    "tick_height":  10,
	    "colour":      "#d000d0",
	    "grid_colour": "#00ff00",
	    "labels": {ldelim} "rotate":0,
	      "labels": [
		  {foreach from=$department name=d item=dept}
		  "{cycle values="<br><br>,<br>"}{$dept} "{if !$smarty.foreach.d.last},{/if}
		  {/foreach}]
	    {rdelim}
	   {rdelim}
	   	
{literal}

	}; 

update_department_chart();

</script>
{/literal}
