{*
6/16/2020 2:37 PM William
- Enhanced to added new filter "Age Group".

7/8/2020 4:44 PM William
- Change custom report to allow view on sub branch.
- Enhanced to show note when have member point earn data column.
*}
{include file="header.tpl"}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>


{if !$no_header_footer}
<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';
var date_type = '{$form.page_filter.special.date}';
{literal}

var CUSTOM_REPORT = {
	initialize: function(){
		var THIS = this;
		this.f = document.f_a;
	},
	check_form : function(){
		if(!check_required_field(this.f))	return false;
		
		//checking empty sku type when not allow select all
		var sku_type = this.f['sku_type'];
		if(typeof(sku_type) != 'undefined'){
			if(!sku_type.value && sku_type.options[sku_type.selectedIndex].text != '-- All --'){
				alert('Please select SKU Type.');
				return false;
			}
		}
		
		//checking empty brand when not allow select all
		var brand_id = this.f['brand_id'];
		if(typeof(brand_id) != 'undefined'){
			if(!brand_id.value && brand_id.options[brand_id.selectedIndex].text == '-- Please Select --'){
				alert('Please select Brand.');
				return false;
			}
		}
		
		//checking empty branch id when not allow select all
		var branch_id = this.f['branch_id'];
		if(typeof(branch_id) != 'undefined'){
			if(!branch_id.value && branch_id.options[branch_id.selectedIndex].text != '-- All --'){
				alert('Please select Branch.');
				return false;
			}
		}
		
		//checking empty member when not allow select all
		var member = this.f['member'];
		if(typeof(member) != 'undefined'){
			if(!member.value && member.options[member.selectedIndex].text != '-- All --'){
				alert('Please select Member/Non-Member.');
				return false;
			}
		}
		
		//checking empty race when not allow select all
		var race = this.f['race'];
		if(typeof(race) != 'undefined'){
			if(!race.value && race.options[race.selectedIndex].text != '-- All --'){
				alert('Please select Race.');
				return false;
			}
		}
		
		//checking empty vendor id when not allow select all
		var vendor_id = this.f['vendor_id'];
		if(typeof(vendor_id) != 'undefined'){
			if(!vendor_id.value && vendor_id.options[vendor_id.selectedIndex].text != '-- All --'){
				alert('Please select Vendor.');
				return false;
			}
		}
		
		// checking if has category filter
		var category_id = this.f['category_id'];
		if(typeof(category_id) != 'undefined'){
			if(category_id.value =='' && this.f['all_category'].checked == false){
				alert('Please select Category.');
				return false;
			}
		}
		
		// checking if has sku filter
		var sku_code_list = this.f['sku_code_list'];
		if(typeof(sku_code_list) != 'undefined'){
			if(sku_code_list.length <= 0){
				alert('Please search and select at least 1 SKU.');
				return false;
			}
		}
		
		// checking if has age group filter
		var age_group = this.f['age_group'];
		if(typeof(age_group) != 'undefined'){
			if(!age_group.value && age_group.options[age_group.selectedIndex].text != '-- All --'){
				alert('Please search Age Group.');
				return false;
			}
		}
		return true;
	}, 
	submit_form: function(t){
		this.f['export_excel'].value = '';
		
		if(!this.check_form())	return false;
		
		if(t == 'excel'){  // export excel file
			this.f['export_excel'].value = 'excel';
		}
		
		this.f.submit();
	}
};
{/literal}
</script>
{/if}

{if $err}
	The following error(s) has occured:
	<ul class="errmsg">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<h1>{$PAGE_TITLE}</h1>

{if !$no_header_footer}
<form name="f_a" onSubmit="return false" class="stdframe" method="post">
	<input type="hidden" name="report_id" value="{$form.id}" />
	<input type="hidden" name="export_excel" />
	<input type="hidden" name="show_report" value="1" />
	
	{* Category *}
	{if $form.page_filter.special.filter_type eq 'category'}
		<p>{include file='category_autocomplete.tpl' all=$config.allow_all_sku_branch_for_selected_reports}</p>
	{/if}
		
	<p>
		{* Date *}
		{if $form.page_filter.special.date}
			{if $form.page_filter.special.date eq 'single_date'}
				{* Single Date *}
				<span>
					<b>Date</b>
					<input type="text" name="date" value="{$smarty.request.date}" id="added1" size="12" class="required" title="Date" /> <img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">
				</span>&nbsp;&nbsp;
			{elseif $form.page_filter.special.date eq 'date_range'}
				{* Date Range *}
				<span>
					<b>Date From </b>
					<input type="text" name="date_from" value="{$smarty.request.date_from}" id="inp_date_from" size="12" class="required" title="Date" /> <img align=absmiddle src="ui/calendar.gif" id="t_date_from" style="cursor: pointer;" title="Select Date">
					<b>To </b>
					<input type="text" name="date_to" value="{$smarty.request.date_to}" id="inp_date_to" size="12" class="required" title="Date" /> <img align=absmiddle src="ui/calendar.gif" id="t_date_to" style="cursor: pointer;" title="Select Date">
				</span>&nbsp;&nbsp;
			{elseif $form.page_filter.special.date eq 'ymd'}
				{* YEAR / MONTH *}
				{if $form.page_filter.special.ymd.year}
					<span>
					<b>Year</b>
					<select name="y">
						{foreach from=$year_list item=y}
							<option value="{$y}" {if $smarty.request.y eq $y}selected {/if}>{$y}</option>
						{/foreach}
					</select>&nbsp;&nbsp;
					
					{if $form.page_filter.special.ymd.month}
						<b>Month</b>
						<select name="m">
							{foreach from=$months key=m item=m_label}
								<option value="{$m}" {if $smarty.request.m eq $m}selected {/if}>{$m_label}</option>
							{/foreach}
						</select>&nbsp;&nbsp;
					{/if}
					</span>&nbsp;&nbsp;
				{/if}
			{/if}
		{/if}
		
		{* SKU type *}
		{if $form.page_filter.normal.sku_type.active}
			<b>SKU Type</b>
			<select name="sku_type">
				<option value="">{if $form.page_filter.normal.sku_type.allow_all}-- All --{else}-- Please Select --{/if}</option>
				{foreach from=$sku_type_list item=r}
					<option value="{$r.code}" {if $smarty.request.sku_type eq $r.code}selected {/if}>{$r.code}</option>
				{/foreach}
			</select> &nbsp;&nbsp;
		{/if}
		
		{* Brand *}
		{if $form.page_filter.normal.brand.active}
			<b>Brand</b>
			<select name="brand_id">
				<option value="{if $form.page_filter.normal.brand.allow_all}all{/if}">{if $form.page_filter.normal.brand.allow_all}-- All --{else}-- Please Select --{/if}</option>
				<option value="0" {if isset($smarty.request.brand_id) and $smarty.request.brand_id eq '0'}selected {/if}>UN-BRANDED</option>
				{foreach from=$brand_list key=brand_id item=r}
					<option value="{$brand_id}" {if $smarty.request.brand_id eq $brand_id}selected {/if}>{$r.description}</option>
				{/foreach}
			</select> &nbsp;&nbsp;
		{/if}
	</p>
		
	<p>
		{* Branch *}
		{if $form.page_filter.normal.branch.active}
			{if $BRANCH_CODE eq 'HQ'}
				<b>Branch</b>
				<select name="branch_id">
				<option value="">{if $form.page_filter.normal.branch.allow_all}-- All --{else}-- Please Select --{/if}</option>
				{foreach from=$branch key=bid item=b}
					<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
				{/foreach}
				</select> &nbsp;&nbsp;
			{else}
				<input name="branch_id" type="hidden" value="{$sessioninfo.branch_id}" />
			{/if}
		{/if}
		
		{* Vendor *}
		{if $form.page_filter.normal.vendor.active}
			<b>Vendor</b>
			<select name="vendor_id">
				<option value="">{if $form.page_filter.normal.vendor.allow_all}-- All --{else}-- Please Select --{/if}</option>
				{foreach from=$vendor_list key=vendor_id item=r}
					<option value="{$vendor_id}" {if $smarty.request.vendor_id eq $vendor_id}selected {/if}>{$r.description}</option>
				{/foreach}
			</select> &nbsp;&nbsp;
		{/if}
		
		{* Member *}
		{if $form.page_filter.normal.member.active}
			<b>Member / Non-Member</b>
			<select name="member">
				<option value="">{if $form.page_filter.normal.member.allow_all}-- All --{else}-- Please Select --{/if}</option>
				<option value="1" {if $smarty.request.member eq '1'}selected {/if}>Member</option>
				<option value="2" {if $smarty.request.member eq '2'}selected {/if}>Non-Member</option>
			</select> &nbsp;&nbsp;
		{/if}
		
		{* Race *}
		{if $form.page_filter.normal.race.active}
			<b>Race</b>
			<select name="race">
				<option value="">{if $form.page_filter.normal.race.allow_all}-- All --{else}-- Please Select --{/if}</option>
				{foreach from=$race_list key=k item=v}
					<option value="{$k}" {if $smarty.request.race eq $k}selected {/if}>{$v}</option>
				{/foreach}
			</select> &nbsp;&nbsp;
		{/if}
		
		{* Age Group *}
		{if $form.page_filter.normal.age_group.active}
			<b>Age Group</b>
			<select name="age_group">
				<option value="">{if $form.page_filter.normal.age_group.allow_all}-- All --{else}-- Please Select --{/if}</option>
				{foreach from=$age_group_list.range.desc key=k item=r2}
					<option value="{$r2}" {if $smarty.request.age_group eq $r2}selected {/if}>{$r2}</option>
				{/foreach}
				<option value="{$age_group_list.other}" {if $smarty.request.age_group eq $age_group_list.other}selected {/if}>{$age_group_list.other}</option>
				<option value="N/A" {if $smarty.request.age_group eq 'N/A'}selected {/if}>N/A</option>
			</select>
		{/if}
	</p>
	
	{* SKU *}
	{if $form.page_filter.special.filter_type eq 'sku'}
		<p>
			<div id="sku_items_autocomplete">{include file="sku_items_autocomplete_multiple.tpl"}</div>
		</p>
	{/if}
	
	<p>
		<input type="button" value="Show Report" onClick="{if $form.page_filter.special.filter_type eq 'sku'}passArrayToInput();{/if}CUSTOM_REPORT.submit_form();" />
		{if $sessioninfo.privilege.EXPORT_EXCEL}
			<button onClick="{if $form.page_filter.special.filter_type eq 'sku'}passArrayToInput();{/if}CUSTOM_REPORT.submit_form('excel');"><img src="/ui/icons/page_excel.png" align="absmiddle"> Export</button>
		{/if}
	</p>
</form>
<script type="text/javascript">
{literal}

if(date_type == 'single_date'){
    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
}else if(date_type == 'date_range'){
    Calendar.setup({
        inputField     :    "inp_date_from",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_date_from",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
	
    Calendar.setup({
        inputField     :    "inp_date_to",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_date_to",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
    });
}

CUSTOM_REPORT.initialize();
{/literal}
</script>
{/if}

{if $data.has_member_point_earn}
<h3>Note: When report have "Member Points Earn", users department checking will not be filtered.</h3>
{/if}

{if $data}
	<br />
	{include file="custom_report.report_table.tpl"}
{/if}

{include file="footer.tpl"}
