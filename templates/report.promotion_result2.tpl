{if !$no_header_footer}
{include file=header.tpl}
	<script>
	var phpself = '{$smarty.server.PHP_SELF}';
	</script>
	
	{literal}
		<!-- calendar stylesheet -->
		<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
		<!-- main calendar program -->
		<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
		<!-- language for the calendar -->
		<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
		<!-- the following script defines the Calendar.setup helper function, which makes
		   adding a calendar a matter of 1 or 2 lines of code. -->
		<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
		<style>
		.c0 { background:#eff; }
		.c1 { background:#efa; }
		.csunday { color:#f00; }
		.report_table td{ font-size:10px; }
		</style>
		<script>
		var LOADING = '<img src=/ui/clock.gif align=absmiddle> ';

		function load_title()
		{
		    var year = $('year').value;
		    var month = $('month').value;
		    if ($('branch_id'))
				var bid = $('branch_id').value;
			else
			    var bid = '';
			    
			$('title').update(_loading_);
	      	new Ajax.Updater('title',phpself,{
	      	    parameters:{
	      			a: 'load_title',
	      			branch_id: bid,
	      			year: year,
	      			month: month,
	      			ajax:1
	      		},
	      		onComplete: function(msg){

	      		    $('title').innerHTML = msg.responseText;
	      		}
	      	});
	    }
	    
		function link_form(type){
			if(type=='output'){
			    document.f_a['a'].value='output_excel';
			    document.f_a.submit();
			}else{
			    document.f_a.action=phpself+'?show_report=1';
			    document.f_a['a'].value='show_report';
			    document.f_a.submit();
			}
		}
		</script>
	{/literal}

{/if}

	<h1>{$PAGE_TITLE}</h1>

	{if $err}
		The following error(s) has occured:
		<ul class=err>
		{foreach from=$err item=e}
		<li> {$e}
		{/foreach}
		</ul>
	{/if}
	{if !$no_header_footer}
		<iframe style="visibility:hidden" width=1 height=1 name=ifprint></iframe>
		<form method=post class=form name="f_a">
			<input type="hidden" name="ajax" value="1">
			<input type=hidden name=report_title value="{$report_title}">
			<input type="hidden" name="a" value="">

			<b>Year</b>
			<select name="year" id="year" onchange="load_title()">
				{foreach from=$years item=y}
				<option value={$y.year} {if $smarty.request.year eq $y.year} selected {/if}>{$y.year}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;

			<b>Month</b>
			<select name="month" id="month" onchange="load_title()">
				{foreach from=$months key=k item=m}
				<option value={$k} {if $smarty.request.month eq $k} selected {/if}>{$m}</option>
				{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;

		{if $BRANCH_CODE eq 'HQ'}
			<b>Branch</b> <select name="branch_id" id="branch_id" onchange="load_title()">
			<option value="all">-- All --</option>
			{foreach from=$branch item=r}
			<option value={$r.id} {if $smarty.request.branch_id eq $r.id}selected{/if}>{$r.code}</option>
			{/foreach}
			</select>&nbsp;&nbsp;&nbsp;&nbsp;
		{/if}
		
		<b class="form-label">Promotion Title</b>
		<span id=title>
			<select class="form-control" name="promo_title">
				<option value="all">-- All --</option>
				{foreach from=$promo_title item=r}
					{if $r.title ne ''}
						<option value="{$r.title}" {if $smarty.request.promo_title eq $r.title}selected {/if}>{$r.title}</option>
					{/if}
		    	{/foreach}
	    	</select>
		</span>
		&nbsp;&nbsp;&nbsp;&nbsp;
		<button onclick="link_form('show')" name="show_report">{#SHOW_REPORT#}</button>&nbsp;&nbsp;&nbsp;&nbsp;

		{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
	    	<button onclick="link_form('output')" name="output_excel">{#OUTPUT_EXCEL#}</button>
	    {/if}
	    
	    <ul>
	    	<li> Report minimum year is 2005</li>
		    {*{if $config.enable_mix_and_match_promotion}{/if}*}
			<li> This report does not include Mix & Match Promotion</li>
	    </ul>
		</form>
	{/if}

{if !$data}
	<p align=center>-- No data --</p>
{else}

<h2>{$report_title}</h2>
<table class=tb width=100% cellspacing=0 cellpadding=4>
<tr class="header">
  <th>Arms Code</th><th>Item</th>
  {if $single_branch}
    {foreach from=$get_date key=year item=r}
      {foreach from=$get_date.$year key=month item=r2}
        {assign var=j value=0}
        {foreach from=$get_date.$year.$month key=day item=r3}
          {if $day ne 'end_month'}
          {if $j=="0"}
          <th class="small">{$month}/{$year}<br>{$day}</th>
          {else}
          <th>{$day}</th>
          {/if}
          {assign var=d value=$day+1}
            {section name=i start=$day loop=$get_date.$year.$month.end_month}
            <th>{$d++}</th>
            {/section}
          
          {assign var=j value=$j+1}
        {/if}
        {/foreach}
      {/foreach}
    {/foreach}
  {else}
    {foreach from=$branch_name item=r}
    <th>{$r.code}</th>
    {/foreach}
  {/if}
  <th>Total Quantity</th>
  <th>Total Amount</th>
  {if $sessioninfo.privilege.SHOW_COST}
  <th>Total Cost</th>
  {/if}
  {if $sessioninfo.privilege.SHOW_REPORT_GP}
  <th>Total GP</th>
  <th>GP %</th>
  {/if}
</tr>

{foreach from=$sku_list key=sku_id item=r}
  {foreach from=$sku_list.$sku_id key=arms item=r2}
  {if $arms}
    {assign var=q_t value=0}
    {assign var=a_t value=0}
    {assign var=c_t value=0}
    {assign var=gp_percent value=0}
       <tr><td align=center nowrap>{$arms}</td><td nowrap>{$r2}</td>
       {if $branch_name}
          {foreach from=$branch_name item=r}
          {assign var=b_id value=$r.id}
          <td align=right>{if $data.$sku_id.$b_id.qty}{$data.$sku_id.$b_id.qty}{else}&nbsp;{/if}</td>
          
          {assign var=temp value=$data.$sku_id.$b_id.qty}
          {assign var=q_t value=$q_t+$temp}
          
          {assign var=temp_amt value=$data.$sku_id.$b_id.amount}
          {assign var=a_t value=$a_t+$temp_amt}
          
          {assign var=temp_cost value=$data.$sku_id.$b_id.cost}
          {assign var=c_t value=$c_t+$temp_cost}
          
          {/foreach}
       {else}
           {foreach from=$get_date key=year item=r}
              {foreach from=$get_date.$year key=month item=r2}
                {assign var=j value=0}
                {foreach from=$get_date.$year.$month key=day item=r3}
                  {if $day ne 'end_month'}
                  {if $j=="0"}
                    <td align=right>{if $data.$sku_id.$year.$month.$day.qty}{$data.$sku_id.$year.$month.$day.qty}{else}&nbsp;{/if}</td>
                    
                    {assign var=temp value=$data.$sku_id.$year.$month.$day.qty}
                    {assign var=q_t value=$q_t+$temp}
                    
                    {assign var=temp_amt value=$data.$sku_id.$year.$month.$day.amount}
                    {assign var=a_t value=$a_t+$temp_amt}
                    
                    {assign var=temp_cost value=$data.$sku_id.$year.$month.$day.cost}
                    {assign var=c_t value=$c_t+$temp_cost}
                    
                    {else}
                    <td align=right>{if $data.$sku_id.$year.$month.$day.qty}{$data.$sku_id.$year.$month.$day.qty}{else}&nbsp;{/if}</td>
                    {assign var=temp value=$data.$sku_id.$year.$month.$day.qty}
                    {assign var=q_t value=$q_t+$temp}
                    
                    {assign var=temp_amt value=$data.$sku_id.$year.$month.$day.amount}
                    {assign var=a_t value=$a_t+$temp_amt}
                    
                    {assign var=temp_cost value=$data.$sku_id.$year.$month.$day.cost}
                    {assign var=c_t value=$c_t+$temp_cost}
                    
                  {/if}
                    {assign var=d value=$day+1}
                    {section name=i start=$day loop=$get_date.$year.$month.end_month}

                    <td align=right>{if $data.$sku_id.$year.$month.$d.qty}{$data.$sku_id.$year.$month.$d.qty}{else}&nbsp;{/if}</td>
                    {assign var=temp value=$data.$sku_id.$year.$month.$d.qty}
                    {assign var=q_t value=$q_t+$temp}
                    
                    {assign var=temp_amt value=$data.$sku_id.$year.$month.$d.amount}
                    {assign var=a_t value=$a_t+$temp_amt}
                    
                    {assign var=temp_cost value=$data.$sku_id.$year.$month.$d.cost}
                    {assign var=c_t value=$c_t+$temp_cost}
                    {assign var=d value=$d+1}
                    {/section}
                  
                  {assign var=j value=$j+1}
                
                {/if}
                {/foreach}
              {/foreach}
          {/foreach}
        {/if}
        
        
        <td align=right>{$q_t}</td>
        <td align=right>{$a_t|number_format:2}</td>
        {if $sessioninfo.privilege.SHOW_COST}
			<td class='r'>{$c_t|number_format:2}</td>
		{/if}
        {if $sessioninfo.privilege.SHOW_REPORT_GP}
          <td align=right>{$a_t-$c_t|number_format:2}</td>
          {assign var=gp_percent value=$a_t-$c_t}
          <td align=right>{if $c_t ne '0'}{$gp_percent/$c_t*100|number_format:2}{else}{$gp_percent*100|number_format:2}{/if}</td>
        {/if}
       </tr>
       {assign var=t value=$t+1}
  {/if}
  {/foreach}
{/foreach}
		<tr>
		    <td colspan=2 class='r'>Total</td>

		{if $branch_name}
			{foreach from=$branch_name item=r}
				{assign var=b_id value=$r.id}
				{assign var=t_qty value=$total.$b_id.qty}
				{assign var=t_amount value=$total.$b_id.amount}
				{assign var=t_cost value=$total.$b_id.cost}
				<td class='r'>{if $t_qty}{$t_qty}{else}&nbsp;{/if}</td>
				
				
			    {assign var=total_qty value=$total_qty+$t_qty}
			    {assign var=total_amount value=$total_amount+$t_amount}
			    {assign var=total_cost value=$total_cost+$t_cost}
			{/foreach}
		{else}
           {foreach from=$get_date key=year item=r}
              {foreach from=$get_date.$year key=month item=r2}
                {assign var=j value=0}
                {foreach from=$get_date.$year.$month key=day item=r3}
                  {if $day ne 'end_month'}
                  {if $j=="0"}
					{assign var=t_qty value=$total.$year.$month.$day.qty}
					{assign var=t_amount value=$total.$year.$month.$day.amount}
					{assign var=t_cost value=$total.$year.$month.$day.cost}
                    <td class='r'>{if $t_qty}{$t_qty}{else}&nbsp;{/if}</td>
				    {assign var=total_qty value=$total_qty+$t_qty}
				    {assign var=total_amount value=$total_amount+$t_amount}
				    {assign var=total_cost value=$total_cost+$t_cost}

                  {/if}
	                {assign var=d value=$day+1}
					{section name=i start=$day loop=$get_date.$year.$month.end_month}
						{assign var=t_qty value=$total.$year.$month.$d.qty}
						{assign var=t_amount value=$total.$year.$month.$d.amount}
						{assign var=t_cost value=$total.$year.$month.$d.cost}

						<td class='r'>{if $t_qty}{$t_qty}{else}&nbsp;{/if}</td>
					    {assign var=total_qty value=$total_qty+$t_qty}
					    {assign var=total_amount value=$total_amount+$t_amount}
					    {assign var=total_cost value=$total_cost+$t_cost}
						{assign var=d value=$d+1}
					{/section}

                  {assign var=j value=$j+1}

                {/if}
                {/foreach}
              {/foreach}
          {/foreach}
		{/if}

           <td class='r'>{$total_qty}</td>
           <td class='r'>{$total_amount|number_format:2}</td>
           {if $sessioninfo.privilege.SHOW_COST}
			<td class='r'>{$total_cost|number_format:2}</td>
		   {/if}
           {if $sessioninfo.privilege.SHOW_REPORT_GP}
             <td class='r'>{$total_amount-$total_cost|number_format:2}</td>
             {assign var=gp_percent value=$total_amount-$total_cost}
             <td class='r'>{if $total_cost ne '0'}{$gp_percent/$total_cost*100|number_format:2}{else}{$gp_percent*100|number_format:2}{/if}</td>
           {/if}

		</tr>




</table>


{/if}
{if !$no_header_footer}

	{include file=footer.tpl}
{else}
	{include file=report_footer.landscape.tpl}
{/if}




