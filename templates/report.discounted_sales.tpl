{*
5/14/2010 11:48:21 AM Andy
- Modified some words
- column width change to 15%

5/6/2011 2:07:12 PM Justin
- Fixed the missing of choice for view type once report printed.

5/25/2011 4:56:38 PM Alex
- exclude normal sales

10/14/2011 2:28:43 PM Alex
- Modified the Ctn and Pcs round up to base on config set.

11/16/2011 3:09:21 PM Andy
- Fix toggle "Use GRN" checkbox error.

11/24/2011 2:33:53 PM Andy
- Change "Use GRN" popup information message.

4/3/2013 2:35 PM Fithri
- excluded all the non-sales branch from report (follow config)

12/17/2013 4:16 PM Justin
- Enhanced to take away the choice of "all" branches.

4/28/2014 11:51 AM Fithri
- add option to filter out inactive SKU items

5/27/2014 3:27 PM Fithri
- enhance all reports that have brand information to have brand group filter.

11/28/2014 11:44 AM Andy
- Add a legend to let user know the Discount Amount is included item discount and receipt discount.

12/14/2015 3:01 PM Andy
- Enhanced to discount legend to let user know it is gst inclusived.

06/30/2020 10:41 AM Sheila
- Updated button css.

10/15/2020 4:59 PM William
- Change GST word to Tax
*}

{include file=header.tpl}

{if !$no_header_footer}
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
.positive{
	font-weight: bold;
	color:green;
}
.negative{
    font-weight: bold;
	color:red;
}
.weekend{
	color:red;
}
</style>
{/literal}

<script>
{literal}
function chk_vd_filter(){
	var allow_use_grn = true;
	if(document.f_a['branch_id']){
		if(document.f_a['branch_id'].value.indexOf('bg')>=0 || !document.f_a['branch_id'].value)	allow_use_grn = false;
	}
	
	if(!$('vendor_id').value)	allow_use_grn = false;

	if(allow_use_grn){
		$('use_grn').disabled=false;
	}
	else{
		$('use_grn').checked=false;	
		$('use_grn').disabled=true;	
	}
}

function view_type_check(){
	if($('date_from').value > $('date_to').value){
		alert('Date Start cannot be late than Date End');
		return false;
	}
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
</ul>
{/if}

{if !$no_header_footer}
<form method="post" class="form" name="f_a" onSubmit="return view_type_check();">
<p>
	<b>Date</b> <input size=10 type=text name=date_from value="{$smarty.request.date_from}{$form.from}" id="date_from">
	<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date From">
	&nbsp;&nbsp;&nbsp;&nbsp;
	
	<b>To</b> <input size=10 type=text name=date_to value="{$smarty.request.date_to}{$form.to}" id="date_to">
	<img align=absmiddle src="ui/calendar.gif" id="t_added2" style="cursor: pointer;" title="Select Date To">
	&nbsp;&nbsp;&nbsp;&nbsp;

	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>
		<select name="branch_id" onChange="chk_vd_filter();">
		    {foreach from=$branches item=b}
			
				{if $config.sales_report_branches_exclude}
				{if in_array($b.code,$config.sales_report_branches_exclude)}
				{assign var=skip_this_branch value=1}
				{else}
				{assign var=skip_this_branch value=0}
				{/if}
				{/if}
			
				{if !$skip_this_branch}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
				{/if}
				
		    {/foreach}
		    {if $branch_group.header}
		        <optgroup label="Branch Group">
					{foreach from=$branch_group.header item=r}
					    {capture assign=bgid}bg,{$r.id}{/capture}
						<option value="bg,{$r.id}" {if $smarty.request.branch_id eq $bgid}selected {/if}>{$r.code}</option>
					{/foreach}
				</optgroup>
			{/if}
		</select>
	{/if}
</p>
<p>
	<b>Vendor</b>
	<select name="vendor_id" id="vendor_id" onChange="chk_vd_filter();">
	    <option value="">-- All --</option>
	    {foreach from=$vendor item=r}
	        <option value="{$r.id}" {if $smarty.request.vendor_id eq $r.id}selected {/if}>{$r.description}</option>
	    {/foreach}
	</select>&nbsp;&nbsp;
	<input type=checkbox id=use_grn name=use_grn {if $smarty.request.use_grn}checked{/if} {if $smarty.request.vendor_id == ''}disabled{/if}> <label for="use_grn"><b>Use GRN</b></label> [<a href="javascript:void(0)" onclick="alert('{$LANG.USE_GRN_INFO|escape:javascript}')">?</a>]
	&nbsp;&nbsp;
</p>
<p>
	<b>Department</b>
	<select name="department_id">
		<option value=0>-- All --</option>
		{foreach from=$departments item=dept}
		<option value="{$dept.id}" {if $smarty.request.department_id eq $dept.id}selected{/if}>{$dept.description}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;

	<b>Brand</b>
	<select name="brand_id">
	<option value='' {if $smarty.request.brand_id eq ''}selected{/if}>-- All --</option>
	{if $brand_group}
		<optgroup label="Brand Group">
		{foreach from=$brand_group key=k item=b}
		<option value="{$k}" {if $smarty.request.brand_id eq $k}selected{/if}>{$b}</option>
		{/foreach}
		</optgroup>
	{/if}
		<option value=0 {if $smarty.request.brand_id eq '0'}selected{/if}>UN-BRANDED</option>
		<optgroup label="Brand">
		{foreach from=$brand item=b}
		<option value="{$b.id}" {if $smarty.request.brand_id eq $b.id}selected{/if}>{$b.description}</option>
		{/foreach}
		</optgroup>
	</select> &nbsp;&nbsp;&nbsp;&nbsp;

	<b>SKU Type</b>
	<select name="sku_type">
		<option value="">-- All --</option>
		{foreach from=$sku_type item=t}
		<option value="{$t.code}" {if $smarty.request.sku_type eq $t.code}selected {/if}>{$t.description}</option>
		{/foreach}
	</select>
</p>

<p>
	<b>Price Type</b>
	<select name="price_type">
		<option value="">-- All --</option>
		{foreach from=$price_type item=p}
			<option value="{$p.type}" {if $smarty.request.price_type eq $p.type}selected {/if}>{$p.type}</option>
		{/foreach}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	<b>View By</b>
	<input type="radio" name="view_type" value="detail" {if !$smarty.request.view_type or $smarty.request.view_type eq 'detail'}checked {/if} /> Detail
	<input type="radio" name="view_type" value="summary" {if $smarty.request.view_type eq 'summary'}checked{/if} /> Summary&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" id="show_normal_id" name="show_normal" value="1" {if $smarty.request.show_normal}checked{/if} >  <label for="show_normal_id"><b>Show normal sales</b></label>&nbsp;&nbsp;&nbsp;&nbsp;
	<label><input type="checkbox" name="exclude_inactive_sku" value="1" {if $smarty.request.exclude_inactive_sku}checked{/if} /><b>Exclude inactive SKU</b></label>
	
</p>
<p>
* Report view in Maximum 30 days.<br />
* Discount Amount = Item Discount + Receipt Discount. {if $config.enable_gst || $config.enable_tax}(Inclusive Tax){/if}
</p>
<p>
<input type="hidden" name="submit" value="1" />
<button class="btn btn-primary" name="show_report">{#SHOW_REPORT#}</button>
{if $sessioninfo.privilege.EXPORT_EXCEL eq '1'}
<button class="btn btn-primary" name="output_excel">{#OUTPUT_EXCEL#}</button>
{/if}
</p>
</form>
<script>chk_vd_filter();</script>
{/if}

{if !$table}
{if $smarty.request.submit && !$err}<p align=center>-- No data --</p>{/if}
{else}
	{if $smarty.request.view_type eq 'detail'}
		{* detail start here *}
		{assign var=col_span value=4}
		{foreach from=$table item=sku key=s name=si_loop}
			{assign var=loop_count value=$smarty.foreach.si_loop.iteration}
			{if $loop_count%$record_chop==0}
				</table>
			{/if}
			{if $loop_count%$record_chop==0 || $loop_count==1}
				<h2>{$report_title}&nbsp;&nbsp;&nbsp;&nbsp;Page: {$loop_count/$record_chop+1|number_format:0}</h2>
				<table class="report_table small_printing" width="100%">
					<tr class="header">
					    <th width="10%" rowspan=2 nowrap>ARMS Code</th>
					    <th width="5%" rowspan=2 nowrap>Article No</th>
					    <th width="15%" rowspan=2 nowrap>Description</th>
					    <th width="5%" rowspan=2 nowrap>Price Type</th>
					    {foreach from=$percentage item=perc key=p}
					    	{if $perc == 0}
					    		{assign var=perc value="Normal"}
					    	{else}
					    		{assign var=perc value=$perc|number_format:2}
							{/if}
					    	<th colspan=3 nowrap>{$perc}{if $perc != "Normal"}%{/if}</th>
						{/foreach}
					    <th width="10%" colspan=3 nowrap>Total</th>
					</tr>
					<tr class="header">
					  	{foreach from=$percentage item=perc key=p}
					    	<th nowrap>Sales</th>
					    	<th nowrap>Discount Amount</th>
					    	<th nowrap>Quantity</th>
						{/foreach}
				    	<th nowrap width="7%">Sales</th>
				    	<th nowrap width="7%">Discount Amount</th>
				    	<th nowrap width="4%">Quantity</th>
					</tr>
			{/if}
				    <tr>
				        <td align=center>{$table.$s.sku_item_code}</td>
				        <td>{$table.$s.artno}</td>
				        <td>{$table.$s.description}</td>
				        <td align=center>{$table.$s.price_type}</td>
					  	{foreach from=$percentage item=perc key=p}
					    	<td align="right" nowrap>{$table.$s.$p.amount|number_format:2|ifzero:'-'}</td>
					    	<td align="right" nowrap>{$table.$s.$p.disc_amt|number_format:2|ifzero:'-'}</td>
					    	<td align="right" nowrap>{$table.$s.$p.qty|qty_nf|ifzero:'-'}</td>
						{/foreach}
						<th class="r" bgcolor="#e0ffff">{$row_total.$s.amount|number_format:2|ifzero:'-'}</th>
						<th class="r" bgcolor="#e0ffff">{$row_total.$s.disc_amt|number_format:2|ifzero:'-'}</th>
						<th class="r" bgcolor="#e0ffff">{$row_total.$s.qty|qty_nf|ifzero:'-'}</th>
					</tr>
		{/foreach}
	{else}
		{* summary start here *}
		{assign var=col_span value=3}
		{foreach from=$header_list item=brands key=v name=vd_loop}
			{assign var=loop_count value=$smarty.foreach.vd_loop.iteration}
			{if $loop_count%$record_chop==0 || $loop_count==1}
				</table>
				{if $loop_count%$record_chop==0 || $loop_count==1}
					<h2>{$report_title}&nbsp;&nbsp;&nbsp;&nbsp;Page: {$loop_count/$record_chop+1|number_format:0}</h2>
					<table class="report_table small_printing" width="100%">
						<tr class="header">
							<th width="15%" rowspan=2 nowrap>Vendor</th>
							<th width="10%" rowspan=2 nowrap>Brand</th>
						    <th width="5%" rowspan=2 nowrap>Price Type</th>
						    {foreach from=$percentage item=perc key=p}
						    	{if $perc == 0}
						    		{assign var=perc value="Normal"}
						    	{else}
						    		{assign var=perc value=$perc|number_format:2}
								{/if}
						    	<th colspan=3 nowrap>{$perc}{if $perc != "Normal"}%{/if}</th>
							{/foreach}
						    <th width="10%" colspan=3 nowrap>Total</th>
						</tr>
						<tr class="header">
						  	{foreach from=$percentage item=perc key=p}
						    	<th nowrap>Sales</th>
						    	<th nowrap>Discount Amount</th>
						    	<th nowrap>Quantity</th>
							{/foreach}
					    	<th nowrap width="7%">Sales</th>
					    	<th nowrap width="7%">Discount Amount</th>
					    	<th nowrap width="4%">Quantity</th>
						</tr>
				{/if}
			{/if}
			{foreach from=$brands item=pricetype key=b}
				{foreach from=$pricetype item=pricet key=pt name=pt_loop}
						<tr>
							{if !$last_vd || $last_vd != $table.$v.$b.$pt.vendor_description}
								<td rowspan={$vd_count.$v} align=center>{$table.$v.$b.$pt.vendor_description}</td>
								<td rowspan={$brand_count.$v.$b}>{if $table.$v.$b.$pt.brand}{$table.$v.$b.$pt.brand}{else}UNBRANDED{/if}</td>
							{else}
								{if $last_brand != $table.$v.$b.$pt.brand}
									<td rowspan={$brand_count.$v.$b}>{if $table.$v.$b.$pt.brand}{$table.$v.$b.$pt.brand}{else}UNBRANDED{/if}</td>
								{/if}
							{/if}
							{assign var=last_vd value=$table.$v.$b.$pt.vendor_description}
							{assign var=last_brand value=$table.$v.$b.$pt.brand}
							{assign var=last_pt value=$table.$v.$b.$pt.price_type}
							<td align=center>{$table.$v.$b.$pt.price_type}</td>
							{foreach from=$percentage item=perc key=p}
								<td align="right" nowrap>{$table.$v.$b.$pt.$p.amount|number_format:2|ifzero:'-'}</td>
								<td align="right" nowrap>{$table.$v.$b.$pt.$p.disc_amt|number_format:2|ifzero:'-'}</td>
								<td align="right" nowrap>{$table.$v.$b.$pt.$p.qty|qty_nf}</td>
							{/foreach}
							<th class="r" bgcolor="#e0ffff">{$row_total.$v.$b.$pt.amount|number_format:2|ifzero:'-'}</th>
							<th class="r" bgcolor="#e0ffff">{$row_total.$v.$b.$pt.disc_amt|number_format:2|ifzero:'-'}</th>
							<th class="r" bgcolor="#e0ffff">{$row_total.$v.$b.$pt.qty|qty_nf}</th>
						</tr>
				{/foreach}
			{/foreach}
		{/foreach}
	{/if}
		<tr class="header">
	        <th class="r" colspan={$col_span}>Total</th>
		  	{foreach from=$percentage item=perc key=p}
		    	<th class="r" nowrap>{$col_total.$p.amount|number_format:2|ifzero:'-'}</th>
		    	<th class="r" nowrap>{$col_total.$p.disc_amt|number_format:2|ifzero:'-'}</th>
		    	<th class="r" nowrap>{$col_total.$p.qty|qty_nf|ifzero:'-'}</th>
			{/foreach}
			<th class="r">{$grand_total.amount|number_format:2|ifzero:'-'}</th>
			<th class="r">{$grand_total.disc_amt|number_format:2|ifzero:'-'}</th>
			<th class="r">{$grand_total.qty|qty_nf|ifzero:'-'}</th>
		</tr>
</table>
{/if}

{if !$no_header_footer}
{literal}
<script type="text/javascript">


    Calendar.setup({
        inputField     :    "date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

    Calendar.setup({
        inputField     :    "date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });

</script>
{/literal}
{/if}

{include file=footer.tpl}
