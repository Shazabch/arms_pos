{*
06/23/2016 11:00 Edwin
- Enhanced on show total amount of the report

5/22/2018 2:30 pm Kuan Yeh
- Bug fixed of logo shown on excel export  
- Bug fixed on method shown on excel export

06/30/2020 02:55 PM Sheila
- Updated button css.

*}

{include file=header.tpl}
{if !$no_header_footer}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
{literal}
<style>
option.bg{
	font-weight:bold;
	padding-left:10px;
}

option.bg_item{
	padding-left:20px;
}
</style>
{/literal}
<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>
<script src="js/jquery-1.7.2.min.js"></script>
<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var JQ = {};
JQ = jQuery.noConflict(true);

JQ(document).ready(function(){

    JQ('#type').on('change',function(){
        switch(JQ(this).val()){
            case 'Purchase':
                JQ('#input_tax').show();
                JQ('#output_tax').hide();
            break;
            case 'Sales':
                JQ('#input_tax').hide();
                JQ('#output_tax').show();
            break;
        }
    });
    JQ('#type').trigger("change");
});

function toggle_div(id,obj){

	if(obj.src.indexOf('expand')>0){
		obj.src = '/ui/collapse.gif';
		JQ('#'+id).show();
	}else{
		obj.src = '/ui/expand.gif';
		JQ('#'+id).hide();
	}
}

function expand_collapse_all(mode){
	if (mode=='expand') {
		JQ('.expand_collapse').show();
		JQ('.expand_collapse_img').attr('src','/ui/collapse.gif');
	}
	else{
		JQ('.expand_collapse').hide();
		JQ('.expand_collapse_img').attr('src','/ui/expand.gif');
	}

}

function show_loading() {

	JQ('#result-table').html('<img src=/ui/clock.gif align=absmiddle> Loading...');
	return true;
}

{/literal}
</script>

{/if}

<h1>{$PAGE_TITLE}</h1>
{if $err}
The following error(s) has occured:

<ul class=err>
{foreach from=$err item=e}
<li> {$e}
{/foreach}
{/if}
{if !$no_header_footer}
<form method=post class=form name="f_a">
    <input type=hidden name=submit value=1>
{if $BRANCH_CODE eq 'HQ'}
<b>Branch</b> <select name="branch_id">
{foreach from=$branches key=bid item=b}
    <option value="{$bid}" {if $form.branch_id eq $bid}selected {/if}>{$b.code} - {$b.description}</option>
{/foreach}
</select>&nbsp;&nbsp;&nbsp;&nbsp;
<br/>
{else}
<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}"/>
{/if}
<b>Type</b>
<select name="type" id="type">
    <option value="Purchase" {if $form.type eq "Purchase"}selected{/if}>Purchase</option>
    <option value="Sales" {if $form.type eq "Sales"}selected{/if}>Sales</option>
</select>
<br/>
<b>Tax Code</b>
<select id="input_tax" style="display:none;" name="input_tax">
    <option value="0">All</option>
    {foreach from=$input_tax item=i}
    <option value="{$i.id}" {if $form.input_tax eq $i.id}selected{/if}>{$i.code}</option>
    {/foreach}
</select>
<select id="output_tax" style="display:none;" name="output_tax">
    <option value="0">All</option>
    {foreach from=$output_tax item=i}
    <option value="{$i.id}" {if $form.output_tax eq $i.id}selected{/if}>{$i.code}</option>
    {/foreach}
</select>
<br/>
<b>From</b>
<input size=10 type=text name=date_from value="{$form.date_from}" id="date_from">
<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<b>To</b> <input size=10 type=text name=date_to value="{$form.date_to}" id="date_to">
<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
&nbsp;&nbsp;&nbsp;&nbsp;
<br/>
<p>
<button class="btn btn-primary" name="show_report" onClick="return show_loading()">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<div id="result-table"><p align=center>--No Data--</p></div>{/if}
{else}

<h1>{$report_title}</h1>
{if !$no_header_footer}
<div id="result-table">
<p>
	{if !$no_header_footer}
		<a href="javascript:void(expand_collapse_all('expand'))"><img src="/ui/expand.gif" width="10" title="Expand All" class="clickable"> Expand All </a>&nbsp;|&nbsp;
		<a href="javascript:void(expand_collapse_all('collapse'))"><img src="/ui/collapse.gif" width="10" title="Collapse All" class="clickable"> Collapse All </a>
	{/if}
</p>
{/if}

<table width="100%" cellpadding="4" cellspacing="1" border="0" class="report_table" id="tbl_do">
	<tr class="header">
		<th>Date</th>
		<th>GST Code</th>
		<th>Amount before GST</th>
		<th>GST Amount</th>
		<th>Amount after GST</th>
	</tr>
    {foreach from=$table key=k item=i}
		<tbody>
		<tr bgcolor='{cycle values="#ffffff,#eeeeee"}'>
			<td style="text-align: center;">
				{$i.date}
				{if !$no_header_footer}
					<img class="expand_collapse_img" src="/ui/expand.gif" onclick="javascript:void(toggle_div('{$k}',this));" align=absmiddle>
				{/if}
			</td>
			<td style="text-align: center;">{$i.code}</td>
			<td style="text-align: right;">{$i.amount|number_format:2}</td>
			<td style="text-align: right;">{$i.gst_amount|number_format:2}</td>
			<td style="text-align: right;">
				{assign var="amount_after_gst" value=$i.amount+$i.gst_amount}
				{$amount_after_gst|number_format:2}
			</td>
		</tr>
		</tbody>
		<tbody class="expand_collapse" id="{$k}" style="display: none;">
			{foreach from=$i.items key=t item=j}
			<tr bgcolor="#dfedfe">
				<td></td>
				<td>{$t}</td>
				<td style="text-align: right;">{$j.amount|number_format:2}</td>
				<td style="text-align: right;">{$j.gst_amount|number_format:2}</td>
				<td style="text-align: right;">
					{assign var="amount_after_gst" value=$j.amount+$j.gst_amount}
					{$amount_after_gst|number_format:2}
				</td>
			</tr>
			{/foreach}
		</tbody>
    {/foreach}
	<tr class="header">
		<th align="right" colspan='2'>Total</th>
		<th align="right">{$total.total.amount|number_format:2}</th>
		<th align="right">{$total.total.gst_amount|number_format:2}</th>
		{assign var=total_amount_after_gst value=$total.total.amount+$total.total.gst_amount}
		<th align="right">{$total_amount_after_gst|number_format:2}</th>
	</tr>
	{foreach from=$total.tax_code key=k item=i}
		<tr class="header">
			<th align="right" colspan='2'>{$k}</th>
			<th align="right">{$i.amount|number_format:2}</th>
			<th align="right">{$i.gst_amount|number_format:2}</th>
			{assign var=amount_after_gst value=$i.amount+$i.gst_amount}
			<th align="right">{$amount_after_gst|number_format:2}</th>
		</tr>
	{/foreach}
</table>
{if !$no_header_footer}
</div>
{/if}
{/if}

{include file=footer.tpl}

{if !$no_header_footer}
	{literal}
		<script type="text/javascript">
  			Calendar.setup({
      		inputField     :    "date_from",     // id of the input field
      		ifFormat       :    "%Y-%m-%d",      // format of the input field
      		button         :    "t_added1",  // trigger for the calendar (button ID)
      		align          :    "Bl",           // alignment (defaults to "Bl")
      		singleClick    :    true,
      		onClose        :    function(cal){
        	JQ('#data-form').find('select').removeAttr('style');
        	cal.hide();
      		}
  			});

 			 Calendar.setup({
      		inputField     :    "date_to",     // id of the input field
      		ifFormat       :    "%Y-%m-%d",      // format of the input field
      		button         :    "t_added2",  // trigger for the calendar (button ID)
      		align          :    "Bl",           // alignment (defaults to "Bl")
      		singleClick    :    true,
      		onClose        :    function(cal){
        	JQ('#data-form').find('select').removeAttr('style');
        	cal.hide();
      		}
  			});
		</script>
	{/literal}
{/if}
