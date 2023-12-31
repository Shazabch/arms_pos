{*
7/18/2011 3:04:11 PM Justin
- Added running total after total row.

10/24/2011 6:37:43 PM Justin
- Changed the word "Running Total" become "Accumulated Total".

1/4/2016 1:48 PM Qiu Ying
- Changed to 2 decimal places

8/3/2016 4:20 PM Andy
- Fix location description empty bug.

8/2/2018 2:07 PM Andy
- Fixed Buying Power Accumulated Total calculation error.
- Hide Accumulated Total when view Hourly Buying Power.

10/5/2020 4:18 PM Shane
- Added Sales Qty tab
*}
{capture assign=hrcount}{count var=$hour}{/capture}
<div style="margin-top:-3em;margin-left:530px;position:absolute;white-space:nowrap;"><b class="form-label " >Last updated: {$smarty.now|date_format:'%I:%M:%S %p'}</b></div>
<div style="margin-top:-1.5em;margin-left:450px;position:absolute;white-space:nowrap;"></div>
<div class="table-responsive mt-3">
	<table cellpadding=2 cellspacing=2 class="" width="100%">
		<thead class="bg-gray-100 fs-08" style="height: 25px;">
			<tr >
				<th nowrap>{if $curr_lvl != $level_priv}{$lvl_desc}<br><a href="javascript:list_hourly('{$tab_slctd}', '{$prev_lvl}', '{$hourly_lvl2_bid}')">Back</a>{/if}</th>
				{foreach from=$hour key=h item=hrs}
					<th nowrap>{$hrs}</th>
				{/foreach}
				<th nowrap>Total</th>
			</tr>
		</thead>
		{if $tab_slctd == 3 || $tab_slctd == 5}
			{assign var=decimal_places value="0"}
		{else}
			{assign var=decimal_places value="2"}
		{/if}
		{foreach from=$col_list item=type key=t}
		<tr>
			{assign var=type_desc value=$type}
			{if !$type_desc}{assign var=type_desc value='Untitled'}{/if}
			<th class="text-primary" nowrap>
				<div style="float:left;background-color:{$type|random_color};width:5px;height:5px;margin:4px;border:1px solid black;"></div>
				{if $next_lvl != '' && $hourly_row_total.$type.amt != ''}<a href="javascript:list_hourly('{$tab_slctd}', '{$next_lvl}', '{$t}')">{$type_desc}</a>{else}{if $curr_lvl == 3 && $hourly_row_total.$type.amt != '' && $tab_slctd == 1}<a href="javascript:tran_details('{$hourly_lvl2_bid|default:$smarty.request.branch_id}', '{$t}')">{$type_desc}</a>{else}{$type_desc}{/if}{/if}
			</th>
				{foreach from=$hour key=h item=hrs name=hc}
					<td align=right {if $smarty.foreach.hc.iteration%2 == 0} bgcolor="#EEF6FF,#DEE6FF"{/if} nowrap>{$hourly_data.$type.$h.amt|number_format:$decimal_places|ifzero:"-"}<br></td>
					{if $max_figure < $hourly_data.$type.$h.amt}{assign var=max_figure value=$hourly_data.$type.$h.amt}{/if}
				{/foreach}
			<th align=right bgcolor="#FFF0FF" nowrap>{if $hourly_row_total.$type.count}{$hourly_row_total.$type.amt/$hourly_row_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
		</tr>
		{/foreach}
		<tr class="bg-gray-100">
			<th nowrap>Total</th>
			{foreach from=$hour key=h item=hrs}
				{assign var=total_hrs_count value=$total_hrs_count+1}
				<th align=right nowrap>{if $hourly_col_total.$h.count}{$hourly_col_total.$h.amt/$hourly_col_total.$h.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
			{/foreach}
			<th align=right rowspan="2" nowrap>{$hourly_grand_total|number_format:$decimal_places|ifzero:"-"}</th>
		</tr>
		<tr class="bg-gray-100" style="{if $tab_slctd == 4}display:none;{/if}">
			<th nowrap>Accumulated Total</th>
			{assign var=acc_trans_count value=1}
			{foreach from=$hour key=h item=hrs}
				<th align=right nowrap>
					{if $hourly_col_total.$h.count}
						{assign var=running_ttl value=$running_ttl+$hourly_col_total.$h.amt}
						{assign var=acc_trans_count value=$hourly_col_total.$h.acc_trans_count}
					{/if}
					{$running_ttl/$acc_trans_count|number_format:$decimal_places|ifzero:"-"}
					
				</th>
			{/foreach}
		</tr>
	</table>
</div>
<input type=hidden name="hourly_lvl2_bid" id="hourly_lvl2_bid" value="{$hourly_lvl2_bid}">
<input type=hidden name="hourly_tab_slctd" id="hourly_tab_slctd" value="{$tab_slctd}">
<input type=hidden name="hourly_curr_lvl" id="hourly_curr_lvl" value="{$curr_lvl}">
{literal}
<script>
hour_chart_data = {
	  "bg_colour":'#ffffff',
	  "y_legend":{
	    "text": "Sales Amt / No of transaction",
	    "style": "{color: #736AFF; font-size: 12px;}"
	  },
	  
{/literal}
	  "elements":[
	  {foreach from=$col_list name=b item=type key=t}
	  	{if $type}
			{assign var=h_set_type value=$type}
		{else}
			{assign var=h_set_type value="Untitled"}
		{/if}
	    {ldelim}
	      "type":"line",
	      "alpha":0.5,
		  "colour":"{$h_set_type|random_color}",
	      "offset":6,
	      "values": [
			  		{foreach from=$hour key=h item=hrs name=hc}
			  			{if $hourly_data.$type.$h.amt != ''}
			  				{ldelim}"value":{$hourly_data.$type.$h.amt}, "tip":  "{$h_set_type}<br>{$h_amt_type}{$hourly_data.$type.$h.amt|number_format:$decimal_places}"{rdelim}
			  			{/if}
						{if !$smarty.foreach.hc.last},{/if}
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
	    "max": "{$total_hrs_count-1}",
	    "labels": {ldelim} "rotate":-15,
	      "labels": [
		  {foreach from=$hour name=h item=hrs}
		  "{$hrs}"{if !$smarty.foreach.h.last},{/if}
		  {/foreach}]
	    {rdelim}
	   {rdelim},
	   	
{literal}
  "tooltip":{
    "text": "My Tip<br>#top#,#bottom# = #val#"
  }
	}; 

update_hourly_chart();

</script>
{/literal}
