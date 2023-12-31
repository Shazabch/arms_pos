{*
3/7/2011 3:04:11 PM Alex
- created by me

4/15/2011 10:18:59 AM Alex
- add department, brand and vendor filter

4/20/2011 11:28:13 AM Alex
- change use counter name, will found duplicate key if use all branch and counter id

3/7/2014 5:34 PM Justin
- Enhanced to have new feature that print prefix receipt no if found config set.

2017-08-24 09:24 AM Qiu Ying
- Bug fixed on the "Loading..." message is always shown when the user has no privilege on the vendor or brand

06/26/20 04:35 PM Sheila
- Updated button css
*}
{include file=header.tpl}
{if !$no_header_footer}

<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>

var dept_id = '{$smarty.request.department_id}';
var rtype = '{$smarty.request.r_type}';
var vendorid = '{$smarty.request.vendor_id}';
var brandid = '{$smarty.request.brand_id}';

</script>

<script>
{literal}

function trans_detail(counter_id,date,pos_id,branch_id)
{
	curtain(true);
	center_div('div_item_details');
    $('div_item_details').show();
	$('div_item_content').update(_loading_+' Please wait...');

	new Ajax.Updater('div_item_content','counter_collection.php',
	{
	    method: 'post',
	    parameters:{
			a: 'item_details',
			counter_id: counter_id,
			pos_id: pos_id,
			branch_id: branch_id,
			date: date
		}
	});
}

function curtain_clicked()
{
	curtain(false);
	hidediv('div_item_details');
}

function get_counter_name(val){
	var branch_id=val;

  var counter_name=document.f_a['counter_name'].value;
	$('counter_name_id').update(_loading_);

	new Ajax.Updater('counter_name_id', 'report.coupon.details.php',
		{
		    method: 'post',
		    parameters:{
  			a: 'get_counter_name',
  			branch_id: branch_id,
  			counter_name: counter_name
		}
	});
}

function ajax_load_brand_vendor(ele){
    var dept_id=ele.value;
	
	if(dept_id != ""){
		$('loading').update(_loading_);
	}

 	// insert new row
	new Ajax.Request("report.coupon.transaction.php",{
		method:'post',
		parameters: {
			a: 'ajax_load_brand_vendor',
				dept_id: dept_id,
				r_type: rtype,
				vendor_id: vendorid,
				brand_id: brandid

		},
	    evalScripts: true,
		onFailure: function(m) {
	        $('loading').update("");
			alert(m.responseText);
		},
		onSuccess: function (m) {
		},
		onComplete: function(m){

			var option = eval("("+m.responseText+")");
	        $('loading').update("");
            $('brand_id').update(option.brand);
            $('vendor_id').update(option.vendor);

            toggle_brand_vendor($('r_type_id').value);
		}
	});
}

function toggle_brand_vendor(r_type){

	if (r_type == "vendor"){
		$('vendor').style.display="";
	    $('brand').style.display="none";
	}
	else if (r_type == "brand"){
        $('vendor').style.display="none";
		$('brand').style.display="";
	}else{
        $('vendor').style.display="none";
		$('brand').style.display="none";
	}
}
</script>

<style>
#div_item_details{
	border: 3px solid rgb(0, 0, 0);
	padding: 10px;
 	background:rgb(255, 255, 255) none repeat scroll 0% 0%;
	position:absolute;
	z-index:10000;
}
</style>
{/literal}
{/if}

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
<ul class=err>
{foreach from=$err item=e}
<li style="color:red;"> {$e} </li>
{/foreach}
</ul>
{/if}

{if !$no_header_footer}
<!-- Item Details -->
<div id="div_item_details" style="display:none;width:600px;height:400px;">
	<div style="float:right;"><img onclick="curtain_clicked();" src="/ui/closewin.png" /></div>
	<div id="div_item_content">
	</div>
</div>
<!-- End of Item Details-->

<form name="f_a" method=get class="form">
	<p>
	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id"  id="branch_id" onchange='get_counter_name(this.value)'>
			<option value="all" {if $smarty.request.branch_id eq 'All'}selected {/if}>--All--</option>
			{foreach from=$branches key=id item=branch}
			<option value="{$branch.id}" {if $smarty.request.branch_id eq $branch.id}selected{/if}>{$branch.code}</option>
			{/foreach}
		</select> &nbsp;&nbsp;
	{/if}

	<b>Counter</b>
	<span id="counter_name_id">
	<select name="counter_name" >
		{if !$counters}
		    <option value=''>No Data</option>
		{else}
		    {foreach name=counter_total from=$counters item=c}
		    {/foreach}

			{if $smarty.foreach.counter_total.total >1 }
			    <option value='all' {if $smarty.request.counter_name eq "all"} selected {/if} >-- All --</option>
			{/if}
		  {foreach from=$counters key=cn  item=c}
		      <option value="{$c.network_name}" {if $smarty.request.counter_name eq $cn}selected {/if} >{$c.network_name}</option>
		  {/foreach}
	  {/if}
	</select>
	</span>&nbsp;&nbsp;

	<b>Department</b>
	<select name="dept_id" onchange="ajax_load_brand_vendor(this)" id="dept_id">
		{if $dept_descrip}
			{foreach from=$dept_descrip key=did item=ddesc}
				<option value="{$did}" {if $smarty.request.dept_id eq $did} Selected {/if} >{$ddesc}</option>
			{/foreach}
		{else}
			<option value="">-- No Data --</option>
		{/if}
	</select>&nbsp;&nbsp;

    <b>Type</b>
	<select name="r_type" id="r_type_id" onchange="toggle_brand_vendor(this.value)";>
	    <option value="all" {if $smarty.request.r_type eq 'all'} selected {/if}>All</option>
	    <option value="brand" {if $smarty.request.r_type eq 'brand'} selected {/if}>Brand</option>
	    <option value="vendor" {if $smarty.request.r_type eq 'vendor'} selected {/if}>Vendor</option>
	</select>&nbsp;&nbsp;

	<span id=brand style="display:none;">
        <b>Brand</b>
        <select name="brand_id" id="brand_id">
        </select>&nbsp;&nbsp;
	</span>

	<span id=vendor style="display:none;">
        <b>Vendor</b>
        <select name="vendor_id" id="vendor_id">
        </select>&nbsp;&nbsp;
	</span>

	<span id="loading"></span>

	<b>Status</b>
	<select name="status">
		<option value="all" {if $smarty.request.status eq 'all'} Selected {/if} >All</option>
		<option value="normal" {if $smarty.request.status eq 'normal'} Selected {/if} >Normal</option>
		<option value="abnormal" {if $smarty.request.status eq 'abnormal'} Selected {/if} >Abnormal</option>
	</select>
    &nbsp;&nbsp;

	<b>POS Date From</b>
	<input type="text" name="from_date" value="{$form.from_date}" id="added1" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;

	<b>To</b>
	<input type="text" name="to_date" value="{$form.to_date}" id="added2" readonly="1" size=12> <img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date">
	&nbsp;&nbsp;

	<br>
	<b>Coupon Code: </b><input name="search_code" value="{$smarty.request.search_code}">

	</p>
	<p>
	<button class="btn btn-primary" name=a value=show_report >{#SHOW_REPORT#}</button>&nbsp;&nbsp;
	{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
		<button class="btn btn-primary" name=a value=output_excel >{#OUTPUT_EXCEL#}</button>
	{/if}
	</p>
</form>

{/if}

<h2>{$report_title}</h2>

{if $detail}
	{foreach from=$top_head key=bid item=b_code}
		<h3>Branch: {$b_code}</h3>

		{foreach from=$mid_head.$bid key=count_id item=count_name}
			{assign var=total_used value=0}
			{assign var=total_amount value=0}

			<h4>Counter: {$count_name}</h4>
			<table class="report_table">
				<tr class=header>
					<th>Date</th>
					<th>Receipt No</th>
					<th>Coupon Code</th>
					<th>Coupon Used</th>
					<th>Amount</th>
					<th>Status</th>
					<th>Approved by</th>
				</tr>

				{foreach from=$detail.$bid.$count_id key=date item=dcrdata}
				    {foreach from=$dcrdata key=receipt_no item=crdata}
				        {foreach from=$crdata key=coupon_code  item=data}
							<tr {if $data.status ne "OK"}style="color:red;"{/if}>
							    <td>{$date}</td>
							    <td><a onclick="trans_detail('{$count_id}','{$date}','{$data.pos_id}','{$bid}');" href="javascript:void(0)">{receipt_no_prefix_format branch_id=$bid counter_id=$count_id receipt_no=$receipt_no}</a></td>
							    <td>{$coupon_code}</td>
							    <td class="r">{$data.used}</td>
							    <td class="r">{$data.amount|ifzero|number_format:2}</td>
							    <td>{$data.status}</td>
							    <td>{$data.approved_by|ifzero:"-"}</td>
							</tr>
							{assign var=total_used value=$total_used+$data.used}
							{assign var=total_amount value=$total_amount+$data.amount}
						{/foreach}
				    {/foreach}
				{/foreach}
				<tr class=header>
				    <th colspan='3'>Total</th>
				    <td class="r">{$total_used}</td>
				    <td class="r">{$total_amount|number_format:2}</td>
				    <td>&nbsp;</td>
				    <td>&nbsp;</td>
				</tr>
			</table>
		{/foreach}
	{/foreach}

{else}
	{if $table}
	    -- No Data --
	{/if}
{/if}
{if !$no_header_footer}
{literal}
<script type="text/javascript">
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
if ($('branch_id')) get_counter_name($('branch_id').value);
ajax_load_brand_vendor($('dept_id'));
</script>
{/literal}
{/if}
{include file=footer.tpl}
