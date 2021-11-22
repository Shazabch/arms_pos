{*
7/18/2011 3:04:11 PM Justin
- Added running total after total row.

10/24/2011 6:37:43 PM Justin
- Changed the word "Running Total" become "Accumulated Total".

1/4/2016 1:48 PM Qiu Ying
- Changed to 2 decimal places

8/3/2016 4:20 PM Andy
- Fix location description empty bug.

8/3/2018 3:55 PM Andy
- Hide Accumulated Total when view Member Buying Power.

10/5/2020 4:18 PM Shane
- Added Sales Qty tab
*}
{capture assign=deptcount}{count var=$members}{/capture}
<div style="margin-top:-3em;margin-left:540px;position:absolute;white-space:nowrap;"><b class="form-label">Last updated: {$smarty.now|date_format:'%I:%M:%S %p'}</b></div>
<div style="margin-top:-1.5em;margin-left:450px;position:absolute;white-space:nowrap;"></div>
<table cellpadding=2 cellspacing=2  width="100%">
	<thead class="bg-gray-100">
		<tr >
			{foreach from=$members key=mm item=member}
				{if $mm == 'Non Member'}
					{foreach from=$member item=m}
						{assign var=non_member value=$non_member+1}
					{/foreach}
				{else if $mm == 'Member'}
					{foreach from=$member item=t}
						{assign var=is_member value=$is_member+1}
					{/foreach}
				{/if}
			{/foreach}
			<th rowspan=2 nowrap>{if $curr_lvl != $level_priv}{$lvl_desc}<br><a href="javascript:list_member('{$tab_slctd}', '{$prev_lvl}', '{$member_lvl2_bid}')">Back</a>{/if}</th>
			{foreach from=$members key=mm item=member}
				{if $mm == 'Non Member'}
					{assign var=nomember value=$mm}
					<th nowrap colspan={$non_member+1}>{$mm}</th>
				{else}
					{assign var=ismember value=$mm}
					<th nowrap colspan={$is_member+1}>{$mm}</th>
				{/if}
			{/foreach}
			<th width="50" rowspan=2 nowrap>Total</th>
		</tr>
		<tr >
			{foreach from=$members key=mm item=member}
				{foreach from=$member item=m}
					<th width="50" nowrap>{$m}</th>
				{/foreach}
				<th width="50" nowrap>Sub Total</th>
			{/foreach}
		</tr>
	</thead>
	{if $tab_slctd == 3 || $tab_slctd == 5}
		{assign var=decimal_places value="0"}
	{else}
		{assign var=decimal_places value="2"}
	{/if}
	{foreach from=$col_list item=type key=t}
		{assign var=type_desc value=$type}
		{if !$type_desc}{assign var=type_desc value='Untitled'}{/if}
	<tr>
		<th class="text-primary" nowrap style="width:100px;max-width:100px">
			<div style="float:left;background-color:{$type|random_color};width:5px;height:5px;margin:4px;border:1px solid black;"></div>
			{if $next_lvl != '' && $member_row_total.$mm.$type.amt != ''}
				<a href="javascript:list_member('{$tab_slctd}', '{$next_lvl}', '{$t}')">{$type_desc}</a>
			{else}
				{if $curr_lvl == 3 && $member_row_total.$mm.$type.amt != '' && $tab_slctd == 1}
					<a href="javascript:tran_details('{$member_lvl2_bid|default:$smarty.request.branch_id}', '{$t}')">{$type_desc}</a>
				{else}
					{$type_desc}
				{/if}
			{/if}
		</th>
			{assign var=mc value=0}
			{foreach from=$members key=mm item=member}
				{foreach from=$member item=m name=mc}
					{assign var=mc value=$mc+1}
					<td align=right {if $mc%2 == 0} bgcolor="#EEF6FF,#DEE6FF"{/if} nowrap>{$member_data.$type.$mm.$m.amt|number_format:$decimal_places|ifzero:"-"}</td>
					{if $max_figure < $member_data.$type.$mm.$m.amt}{assign var=max_figure value=$member_data.$type.$mm.$m.amt}{/if}
				{/foreach}
				<th class="bg-gray-100" nowrap>{if $member_row_total.$mm.$type.count}{$member_row_total.$mm.$type.amt/$member_row_total.$mm.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
			{/foreach}
			<th class="bg-gray-100" nowrap>{if $member_row_grand_total.$type.count}{$member_row_grand_total.$type.amt/$member_row_grand_total.$type.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
	</tr>
	{/foreach}
	<tr class="bg-gray-100">
		<th nowrap>Total</th>
		{foreach from=$members key=mm item=member}
			{foreach from=$member item=m}
				<th align=right nowrap>{if $member_col_total.$mm.$m.count}{$member_col_total.$mm.$m.amt/$member_col_total.$mm.$m.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
			{/foreach}
			<th align=right rowspan="2" nowrap>{if $member_sub_grand_total.$mm.count}{$member_sub_grand_total.$mm.amt/$member_sub_grand_total.$mm.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
		{/foreach}
		<th width="50" align=right rowspan="2" nowrap>{if $member_grand_total.count}{$member_grand_total.amt/$member_grand_total.count|number_format:$decimal_places|ifzero:"-"}{else}-{/if}</th>
	</tr>
	<tr class="bg-gray-100" style="{if $tab_slctd == 4}display:none;{/if}">
		<th nowrap>Accumulated Total</th>
		{foreach from=$members key=mm item=member}
			{foreach from=$member item=m}
				{if $member_col_total.$mm.$m.count}
					{assign var=running_ttl value=$running_ttl+$member_col_total.$mm.$m.amt/$member_col_total.$mm.$m.count}
				{/if}
				<th align=right nowrap>{$running_ttl|number_format:$decimal_places|ifzero:"-"}</th>
			{/foreach}
		{/foreach}
	</tr>
</table>

<input type=hidden name="member_lvl2_bid" id="member_lvl2_bid" value="{$member_lvl2_bid}">
{literal}
<script>
member_chart_data_pc1 = {
	    "title": {
	        "text": "Total Sales by Races"
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
				  		{foreach from=$members key=mm item=member name=mb}
				  			{foreach from=$member item=m name=mc}
				  			  {if $mm == 'Non Member'}
								{assign var=member_set value="NM$m"}
							  {else}
							    {assign var=member_set value="M$m"}
							  {/if}
							  "{$member_set|random_color}"
							  {if !$smarty.foreach.mc.last},{/if}
							{/foreach}
							{if !$smarty.foreach.mb.last},{/if}
						{/foreach}
					],
		  "start-angle": 0, 
	      "values":[
			  		{foreach from=$members key=mm item=member name=mb}
			  			{foreach from=$member item=m name=mc}
			  				{if $mm == 'Non Member'}
								{assign var=m_color_set value="NM$m"}
								{assign var=member_set value="NM"}
							{else}
							    {assign var=m_color_set value="M$m"}
							    {assign var=member_set value="M"}
							{/if}
		      				{ldelim}
							  	{if $member_col_total.$mm.$m.amt}
							  "label": "{$member_set} ({$m})", "value": int("{if $member_col_total.$mm.$m.count}{$member_col_total.$mm.$m.amt/$member_col_total.$mm.$m.count|number_format:$decimal_places}{else}-{/if}"), "colour": "{$m_color_set|random_color}", "tip":"{$mm} ({$m})\n{$m_amt_type}{if $member_col_total.$mm.$m.count}{$member_col_total.$mm.$m.amt/$member_col_total.$mm.$m.count|number_format:$decimal_places}{else}-{/if}"{else}"value":""{/if}{rdelim}
							{if !$smarty.foreach.mc.last},{/if}
						{/foreach}
						{if !$smarty.foreach.mb.last},{/if}
			  		{/foreach}
				   ]
	    {rdelim}
	  ] 	
{literal}
};

member_chart_data_pc2 = {
{/literal}
	    "title": {ldelim}
	        "text": "Member Sales by {$member_level_type}"
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
								{assign var=m_set_type value=$type}
							{else}
								{assign var=m_set_type value="Untitled"}
							{/if}
							"{$m_set_type|random_color}"
							{if !$smarty.foreach.col_type.last},{/if}
				  		{/foreach}
					],
		  "start-angle": 0, 
	      "values":[
			  		{foreach from=$col_list item=type key=t name=col_type}
					  	{if $type}
							{assign var=m_set_type value=$type}
						{else}
							{assign var=m_set_type value="Untitled"}
						{/if}
			  			{ldelim}{if $member_row_total.$ismember.$type.amt}
							"label": "{$m_set_type}", "value": int("{if $member_row_total.$ismember.$type.count}{$member_row_total.$ismember.$type.amt/$member_row_total.$ismember.$type.count|number_format:$decimal_places}{else}-{/if}"), "colour": "{$m_set_type|random_color}", "tip":"{$m_set_type}\n{$m_amt_type}{if $member_row_total.$ismember.$type.count}{$member_row_total.$ismember.$type.amt/$member_row_total.$ismember.$type.count|number_format:$decimal_places}{else}-{/if}"{else}"value":""{/if}{rdelim}
						{if !$smarty.foreach.col_type.last},{/if}
			  		{/foreach}
				   ]
	    {rdelim}
	  ] 	
{literal}
};

member_chart_data_pc3 = {
{/literal}
	    "title": {ldelim}
	        "text": "Non Member Sales by {$member_level_type}"
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
								{assign var=m_set_type value=$type}
							{else}
								{assign var=m_set_type value="Untitled"}
							{/if}
							"{$m_set_type|random_color}"
							{if !$smarty.foreach.col_type.last},{/if}
				  		{/foreach}
					],
		  "start-angle": 0, 
	      "values":[
			  		{foreach from=$col_list item=type key=t name=col_type}
					  	{if $type}
							{assign var=nm_set_type value=$type}
						{else}
							{assign var=nm_set_type value="Untitled"}
						{/if}
			  			{ldelim}{if $member_row_total.$nomember.$type.amt}
							"label": "{$nm_set_type}", "value": int("{if $member_row_total.$nomember.$type.count}{$member_row_total.$nomember.$type.amt/$member_row_total.$nomember.$type.count|number_format:$decimal_places}{else}-{/if}"), "colour": "{$nm_set_type|random_color}", "tip":"{$nm_set_type}\n{$m_amt_type}{if $member_row_total.$nomember.$type.count}{$member_row_total.$nomember.$type.amt/$member_row_total.$nomember.$type.count|number_format:$decimal_places}{else}-{/if}"{else}"value":""{/if}{rdelim}
						{if !$smarty.foreach.col_type.last},{/if}
			  		{/foreach}
				   ]
	    {rdelim}
	  ] 	
{literal}
};

update_member_chart();

</script>
{/literal}
