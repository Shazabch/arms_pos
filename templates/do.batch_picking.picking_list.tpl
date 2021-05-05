{*
5/18/2018 5:24PM HockLee
- Create Picking List by batch.

8/30/2018 4:00PM HockLee
- Fixed title error.
*}
{include file='header.tpl'}
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var CHECKOUT_PICKING_LIST = {
	initialize: function(){
		// initial calendar
		this.init_calendar();
	},

	init_calendar: function(){
	    // sales date from
        Calendar.setup({
			inputField     :    "inp_date_range_from",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_from",
			align          :    "Bl",
			singleClick    :    true
		});
		
		// sales date to
		Calendar.setup({
			inputField     :    "inp_date_range_to",
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_date_range_to",
			align          :    "Bl",
			singleClick    :    true
		});
	},

}

function print_picking_list_by_batch(){
	if(document.f_picking_list_by_batch['batch_code'].value.trim()==''){
		alert('Please enter Batch Code');
		return false;
	}

	if(document.f_picking_list_by_batch['date_range_from'].value.trim()==''){
		alert('Please select sales date from.');
		return false;
	}
	if(document.f_picking_list_by_batch['date_range_to'].value.trim()==''){
		alert('Please select sales date to.');
		return false;
	}
	
	document.f_picking_list_by_batch.submit();
}

var batch_code_autocomplete = undefined;

function reset_batch_code_autocomplete(){
	var param_str = "a=ajax_search_batch_code&";
	batch_code_autocomplete = new Ajax.Autocompleter("inp_batch_code", "div_autocomplete_batch_code_choices", phpself, {parameters:param_str, paramName: "value",
	indicator: 'span_loading_batch_code',
	afterUpdateElement: function (obj, li) {
	    s = li.title;
	    $('span_loading_batch_code').hide();
	}});
}

function print_picking_list_by_item(batch_code, date_from, date_to){
	window.open(phpself+'?a=print_picking_list_by_item&batch_code='+batch_code+'&date_range_from='+date_from+'&date_range_to='+date_to);
}

function print_picking_list_by_do(batch_code, date_from, date_to){
	window.open(phpself+'?a=print_picking_list_by_do&batch_code='+batch_code+'&date_range_from='+date_from+'&date_range_to='+date_to);
}

function goBack() {
    location.href = "/do.php?page=credit_sales";
}

</script>

<style>
.text_right{ 
    text-align: right; 
}

.text_center{ 
    text-align: center; 
}
</style>
{/literal}

<h1>{$PAGE_TITLE}</h1>

<form name="f_picking_list_by_batch" class="stdframe" method="post">
	<input type="hidden" name="a" value="show_do" />

	<table border="0" cellspacing="0" cellpadding="4" width="100%">
		<tr>
			<th width=15% align="left">Batch Code</th>
			<td>
				<input type="text" name="batch_code" value="{$batch_code}" id="inp_batch_code" /><img src="ui/rq.gif" align="absbottom" title="Required Field">
	            <span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
				<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
			</td>
		</tr>
		<tr>
			<th align="left">DO Date</th>
			<td>
				<b>from </b> 
				<input name="date_range_from" id="inp_date_range_from" size="10" maxlength="10"  value="{$smarty.request.date_range_from|default:$smarty.now-604800|date_format:"%Y-%m-%d"}" />
	    		<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_from" style="cursor: pointer;" title="Select Date" />
			
				<b>to </b> 
				<input name="date_range_to" id="inp_date_range_to" size="10" maxlength="10"  value="{$smarty.request.date_range_to|default:$smarty.now|date_format:"%Y-%m-%d"}" />
	   			<img align="absmiddle" src="ui/calendar.gif" id="img_date_range_to" style="cursor: pointer;" title="Select Date" />
			</td>
		</tr>
		<tr>
			<td colspan="3">				
				<p id="p_submit_btn" align="center">
				<input type="button" value="Submit" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="print_picking_list_by_batch();" />
				<input type="button" value="Back" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="goBack();" />
				</p>
			</td>
		</tr>
	</table>
</form>

{if $no_data_msg}
<h2>{$no_data_msg}</h2>
{/if}

{if $do}
	{assign var=arr_count value=$do|@count}
	{assign var=count value=$arr_count*2}
	{if $checkout eq 1}<h5>* this batch has been checkout.</h5>{/if}
	<h2>Picking List by Item</h2>
	<table class="report_table" width="100%">
		<tr class="header">
			<th>No.</th>
			<th>ARMS Code</th>
			<th>MCode<br />ArtNo<br />{$config.link_code_name|default:'Link Code'}</th>
			<th>SKU Description</th>
			<th>Location</th>
			<th>UOM</th>
			<th>UOM Qty</th>
			<th>Qty in PCS</th>			
		</tr>
		{assign var=item_no value=0}
		{foreach from=$group_item item=grp_item key=sku_item}

		{assign var=item_no value=$item_no+1}
		<tr>
			<td>{$item_no}</td>
			<td>{$grp_item.arms_code}</td>
			<td>
				{$grp_item.mcode|default:'-'}<br />
				{$grp_item.artno|default:'-'}<br />
				{$grp_item.old_code|default:'-'}
			</td>
			<td>{$grp_item.description}</td>
			<td>{$grp_item.location}</td>
			<td>
				{assign var=dup value=null}
				{foreach from=$grp_item.uom_fra item=fraction}
				{assign var="fra" value="_"|explode:$fraction}
					{if $fra[1] eq 1 && $grp_item.pcs ne 0}					
						{if $dup ne $fra[0]}
							{$fra[0]}<br>
						{/if}
						{assign var=key value=1}					
					{else}
						{$fra[0]}<br>
						{assign var=key value=0}					
					{/if}
					{assign var=dup value=$fra[0]}
				{/foreach}
				{if $key eq 0}
				{if $grp_item.pcs ne 0}PCS{/if}
				{/if}
			</td>
			<td class="text_right">
				{assign var=dup_ctn value=null}
				{foreach from=$grp_item.uom_ctn item=uom_ctn}
					{if $dup_ctn ne $uom_ctn}
						{if $uom_ctn ne 0}{$uom_ctn}</br>{/if}
					{/if}
					{assign var=dup_ctn value=$uom_ctn}
				{/foreach}
				{if $grp_item.pcs eq 0}{else}{$grp_item.pcs}{/if}
			</td>
			<td class="text_right">
				{$grp_item.total_qty}				
			</td>			
		</tr>				

		{/foreach}
		<tr class="text_right">
			<th colspan='7'>Total</th>
			<td>{$grand_total.total}</td>
		</tr>
	</table>
	<br>

	<form name="f_picking" method="post">
		<center>
			<input type="button" value="Print" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="print_picking_list_by_item('{$batch_code}','{$from}','{$to}');" />
		</center>
	</form>
	<br>

	<h2>Picking List by DO</h2>
	<table class="report_table" width="100%">
		<tr class="header">
			<th>No.</th>
			<th>ARMS Code</th>
			<th>MCode<br />ArtNo<br />{$config.link_code_name|default:'Link Code'}</th>
			<th>SKU Description</th>
			<th>Location</th>
			<th>UOM</th>
			<th colspan='6'>DO</th>		
		</tr>
		{assign var=item_no value=0}
		{foreach from=$do_det item=do_item key=do_no}
			{foreach from=$do_item item=do_info key=uom}	
				{assign var=item_no value=$item_no+1}
				<tr>
					<td>{$item_no}</td>
					<td>{$do_info.sku_item_code}</td>
					<td>
						{$do_info.mcode|default:'-'}<br />
						{$do_info.artno|default:'-'}<br />
						{$do_info.link_code|default:'-'}
					</td>
					<td>{$do_info.description}</td>
					<td>{$do_info.location}</td>
					<td>{$do_info.uom_code}</td>
					<td>
						<table width="100%" class="report_table">					
							{assign var=do_count value=$do_info.do|@count}
							{assign var=cnt value=0}
							{assign var=row_no value=5}
							{section name=d_cnt start=0 loop=$do_count+1}
								{assign var=num value=$smarty.section.d_cnt.index}					
								{if $num%$row_no eq 1}
									{assign var=cnt value=$cnt+1}
								{/if}
							{/section}
							{assign var=cnt2 value=$cnt+1}
							{assign var=ind2 value=0}
							{assign var=ind_qty1 value=0}					
							{assign var=number value=0}					
							{section name=foo1 loop=$cnt}
							{assign var=number value=$number+1}
								<tr style="background-color:f0fa85;">
									{assign var=d_no value=0}
									{if $ind2 eq 0}
										{section name=aa start=0 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}								
										{if $ind < $row_no}
										<th colspan='2' style="width:65px">{$do_info.do[$ind].do_no}</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind2 value=$ind}
										{break}
										{/if}
										{/section}
									{else}								
										{section name=aa start=$ind2 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}										
										{assign var=time value=$number*$row_no}								
										{if $ind < $time}
										<th colspan='2' style="width:65px">{$do_info.do[$ind].do_no}</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind2 value=$ind}
										{/if}
										{/section}
									{/if}							

									<!-- N/A -->							
									{assign var=loop value=$row_no}
									{assign var=looop value=$loop-$d_no}
									{section name=foo start=0 loop=$looop}
									<th colspan='2' style="width:65px">N/A</th>
									{/section}
								</tr>
								<tr style="background-color:f0fa85;">
									{assign var=d_no value=0}

									{if $ind_unit eq 0}
										{section name=aa start=0 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}								
										{if $ind < $row_no}
										<th>Ctn</th>
										<th>Pcs</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_unit value=$ind}
										{break}
										{/if}
										{/section}
									{else}								
										{section name=aa start=$ind_unit loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}										
										{assign var=time value=$number*$row_no}								
										{if $ind < $time}
										<th>Ctn</th>
										<th>Pcs</th>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_unit value=$ind}
										{/if}
										{/section}
									{/if}							

									<!-- N/A -->							
									{assign var=loop value=$row_no}
									{assign var=looop value=$loop-$d_no}
									{section name=foo start=0 loop=$looop}
									<th>Ctn</th>
									<th>Pcs</th>
									{/section}
								</tr>
								<tr class="text_right">
									{assign var=d_no value=0}
									{if $ind_qty1 eq 0}
										{section name=aa start=0 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}								
										{if $ind < $row_no}
										<td>{$do_info.do[$ind].ctn}</td>
										<td>{$do_info.do[$ind].pcs}</td>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_qty1 value=$ind}
										{break}
										{/if}
										{/section}
									{else}		
										{section name=aa start=$ind_qty1 loop=$do_count}
										{assign var=ind value=$smarty.section.aa.index}										
										{assign var=time value=$number*$row_no}								
										{if $ind < $time}
										<td>{$do_info.do[$ind].ctn}</td>
										<td>{$do_info.do[$ind].pcs}</td>
										{assign var=d_no value=$d_no+1}
										{else}
										{assign var=ind_qty1 value=$ind}
										{/if}
										{/section}
									{/if}							

									<!-- N/A -->							
									{assign var=loop value=$row_no}
									{assign var=looop value=$loop-$d_no}
									{section name=foo start=0 loop=$looop}
									<th class="text_center">-</th>
									<th class="text_center">-</th>
									{/section}
								</tr>
							{/section}
						</table>
					</td>
				</tr>
			{/foreach}
		{/foreach}		
	</table>
	<br>

	<form name="f_picking" method="post">
		<center>
			<input type="button" value="Print" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="print_picking_list_by_do('{$batch_code}','{$from}','{$to}');" />
		</center>
	</form>
{/if}

<script>
	CHECKOUT_PICKING_LIST.initialize();
	reset_batch_code_autocomplete();
</script>

{include file='footer.tpl'}

