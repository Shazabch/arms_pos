{if $smarty.request.calendar_only}{assign var=no_menu_templates value=1}{/if}
{include file='header.tpl'}

<style>
{literal}
.day_header_column{
	width: 30px;
}
.td_marketing_plan, .td_festival_date{
	line-height:5px;
    border-right: 0 solid transparent !important;
	border-bottom: 1px solid black !important;
}
.last_promo_day{
	border-right: 1px solid black !important;
}
.tbl_marketing tr td{
	line-height: 15px;
}
.td_over_day{
	background: #c0c0c0;
}
.td_marketing_plan .div_promo_first_day, .legend_promotion{
    position:absolute;
	background:#ff9;
	line-height: 13px;
	opacity:0.8;
	padding-right:3px;
}

.td_festival_date .div_promo_first_day, .legend_festival{
    position:absolute;
	background:#f00;
	color:#fff;
	font-weight:bold;
	line-height: 13px;
	opacity:0.8;
	padding-right:3px;
}
{/literal}
</style>

<script>
var allow_edit = '{$allow_edit}';
var phpself = '{$smarty.server.PHP_SELF}';


{literal}
var MARKETING_PLAN_CALENDAR_MODULE = {
	initialize: function(){
	    // submit event
		$('#refresh_calendar').live('click', function(){
			MARKETING_PLAN_CALENDAR_MODULE.btn_submit_clicked();
		});
		
		// initial dialog
		MARKETING_PLAN_INFO_DIALOG_MODULE.initialize($('#div_marketing_plan_info_dialog'));
		
		// event when mouse enter the marketing plan column
		$('table.tbl_marketing td.td_marketing_plan').live('click', function(){
            MARKETING_PLAN_INFO_DIALOG_MODULE.mouse_enter_column(this);
		});
	},
	check_form: function(){ // function to validate all params
		if(document.f_a['marketing_plan_id'].value==''){ // no year selected
			custom_alert.alert('Please select marketing plan.');
			return false;
		}
		return true;
	},
	btn_submit_clicked: function(){ // submit clicked
		if(this.check_form()){  // validate form first
			document.f_a.submit();
		}
	}
};

var MARKETING_PLAN_INFO_DIALOG_MODULE = {
    dialog: undefined,
	initialize: function(div){
        this.dialog = $(div).dialog({
			autoOpen: false,    // default dont popup
			minWidth: 200, // set the width
			width:400,
			minHeight: 200,    // set the height
			height:200,
			closeOnEscape: false,    // whether user press escape can close
			modal: false,    // if set to true, will auto create an overlay curtain behind this div
			resizable: true   // disable the popup from resize
		});
		return this;
	},
	mouse_enter_column: function(ele){
	    var promotion_plan_id = $(ele).attr('promotion_plan_id');   // get sop_marketing_plan_id
	    var pos = $(ele).position();    // get element position
	    
	    var tr_marketing_plan_row = $('#tr_promotion_plan_row-'+promotion_plan_id);
	    var title = $(tr_marketing_plan_row).find("td.title").text();
	    var status = $(tr_marketing_plan_row).attr('status');
	    var approved = $(tr_marketing_plan_row).attr('approved');
	    
		//var plan_status = 'Draft';
		//if(status==1 && approved==0)    plan_status = 'Waiting for Approval';
		//else if(status==1 && approved==1)    plan_status = 'Approved';
		var dialog_top_pos = pos.top-$(document).scrollTop();   // must minus the document scroll top to get the correct top position
		
	    var details_html = $(tr_marketing_plan_row).find("td.details_html").html();
		$(this.dialog).dialog( "option", "position", [pos.left, dialog_top_pos])   // move dialog to the td position
		            .dialog('option', 'title', title)
		            .html(details_html)
					.dialog('open');    // show the dialog
	}
}
{/literal}
</script>

<div id="div_marketing_plan_info_dialog" style="display:none;"></div>

<h1>{$PAGE_TITLE} {if $marketing_plan}- {$marketing_plan.title}{/if}</h1>

{if $err}
	<div class="ui-state-error ui-corner-all" style="padding: 0 .7em;">
	    <p><span class="ui-icon ui-icon-alert" style="float: left; margin-right: .3em;"></span> Following errors has occur
	        <ul>
			{foreach from=$err item=e}
				<li> {$e}</li>
			{/foreach}
			</ul>
		</p>
	</div><br />
{/if}

{if !$smarty.request.calendar_only}
<div class="stdframe ui-corner-all">
<form name="f_a" method="get">
	<input type="hidden" name="show" value="1" />
	
	<b>Marketing plan</b>
	<select name="marketing_plan_id">
     	<option value="">-- Please Select --</option>
	    {foreach from=$marketing_plan_list item=r}
	        <option value="{$r.id}" {if $smarty.request.marketing_plan_id eq $r.id}selected {/if}>{$r.title}</option>
	    {/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<select name="branch_id">
	    <option value="">-- All --</option>
	    {foreach from=$branches key=bid item=b}
	        {if $YMP_HQ_EDIT or sop_check_privilege('SOP_YMP_EDIT', $bid)}
	            <option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if}>{$b.code}</option>
	        {/if}
	    {/foreach}
	</select>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="checkbox" name="show_festival" value="1" {if $smarty.request.show_festival or !$smarty.request.show}checked {/if} /> <b>Show Festival Date</b>
	&nbsp;&nbsp;&nbsp;&nbsp;
	<input type="button" value="Refresh" id="refresh_calendar" />
</form>
</div>
{/if}

<br />
{if $smarty.request.show and !$err}
	{if !$sop_calendar}
	    No Data
	{else}
	    {foreach from=$sop_calendar key=y item=marketing_plan_years}
	        {foreach from=$marketing_plan_years key=m item=marketing_plan_months}
	            {assign var=max_promo_row_count value=$sop_calendar_info.$y.$m.max_promo_row_count}
	            {assign var=max_festival_row_count value=$sop_calendar_info.$y.$m.max_festival_row_count}
	            
	        	<div style="overflow-x:auto;overflow-y:hidden;width:100%;">
	            <table class="tb tbl_marketing" cellspacing="0" cellpadding="0">
	                <tr>
	                    <th width="100" nowrap rowspan="{$max_promo_row_count+$max_festival_row_count+1}">{$months.$m} {$y}</th>
	                    {foreach from=$marketing_plan_months key=d item=marketing_plan_days}
	                        <th {if $marketing_plan_days.date|date_format:'%a' eq 'Sun'}style="color:red;"{/if}>
								<div class="day_header_column">
									{$d}<br />
                                    <span class="small">{$marketing_plan_days.date|date_format:'%a'}</span>
								</div>
							</th>
	                    {/foreach}
	                    {section loop=31 start=$d name=s}
                            <th class="day_header_column td_over_day">&nbsp;</th>
                        {/section}
	                </tr>
	                <!-- Festival row -->
	                {section loop=$max_festival_row_count name=row_loop}
	                    {assign var=row_id value=$smarty.section.row_loop.index}
	                    <tr>
	                        {foreach from=$marketing_plan_months key=d item=marketing_plan_days name=loop_d}
	                            {if $marketing_plan_days.festival.$row_id.festival_date_id}
	                                {assign var=festival_date_id value=$marketing_plan_days.festival.$row_id.festival_date_id}
	                                <td class="td_festival_date {if $smarty.foreach.loop_d.last or $festival_date_list.$festival_date_id.date_to eq $marketing_plan_days.date}last_promo_day{/if}" style="background: {$festival_date_list.$festival_date_id.calendar_color};" festival_date_id="{$festival_date_id}">

	                                    {if $festival_date_list.$festival_date_id.date_from eq $marketing_plan_days.date or $d eq 1}
	                                        <div class="div_promo_first_day ">{$festival_date_list.$festival_date_id.title}</div>
	                                    {/if}
	                                    &nbsp;
									</td>
	                            {else}
	                                <td>&nbsp;</td>
	                            {/if}
	                        {/foreach}
	                        {section loop=31 start=$d name=s}
	                            <td class="td_over_day">&nbsp;</td>
	                        {/section}
	                    </tr>
	                {/section}
	                
	                <!-- Promotion row -->
	                {section loop=$max_promo_row_count name=row_loop}
	                    {assign var=row_id value=$smarty.section.row_loop.index}
	                    <tr>
	                        {foreach from=$marketing_plan_months key=d item=marketing_plan_days name=loop_d}
	                            {if $marketing_plan_days.data.$row_id.promotion_plan_id}
	                                {assign var=promotion_plan_id value=$marketing_plan_days.data.$row_id.promotion_plan_id}
	                                <td class="td_marketing_plan clickable {if $smarty.foreach.loop_d.last or $promotion_plan_list.$promotion_plan_id.date_to eq $marketing_plan_days.date}last_promo_day{/if}" style="background: {$promotion_plan_list.$promotion_plan_id.calendar_color};" promotion_plan_id="{$promotion_plan_id}">
	                                    
	                                    {if $promotion_plan_list.$promotion_plan_id.date_from eq $marketing_plan_days.date or $d eq 1}
	                                        <div class="div_promo_first_day ">{$promotion_plan_list.$promotion_plan_id.title}</div>
	                                    {/if}
	                                    &nbsp;
									</td>
	                            {else}
	                                <td>&nbsp;</td>
	                            {/if}
	                        {/foreach}
	                        {section loop=31 start=$d name=s}
	                            <td class="td_over_day">&nbsp;</td>
	                        {/section}
	                    </tr>
	                {/section}
	            </table>
				</div><br />
	        {/foreach}
	    {/foreach}
	    
	    {if $promotion_plan_list}
		    <fieldset style="float:left;">
		        <legend class="legend_promotion ui-corner-all" style="position:static;padding:5px 10px;">Promotion Informations</legend>

		        <table class="tb" cellspacing="0" cellpadding="4">
		            <tr>
		                <th rowspan="2">Color</th>
		                <th rowspan="2">Marketing Plan Title</th>
		                <th colspan="2">Date</th>
		            </tr>
		            <tr>
		                <th>From</th>
		                <th>To</th>
		            </tr>
		            {foreach from=$promotion_plan_list key=promotion_plan_id item=r}
		                <tr id="tr_promotion_plan_row-{$promotion_plan_id}" active="{$r.active}">
		                    <td style="background:{$r.calendar_color};">&nbsp;</td>
		                    <td class="title">{$r.title|default:'-'}</td>
		                    <td class="date_from">{$r.date_from|default:'-'}</td>
		                    <td class="date_to">{$r.date_to|default:'-'}</td>
		                    <td class="details_html" style="display:none;">
								<p>Promotion start from {$r.date_from|default:'-'} to {$r.date_to|default:'-'}</p>
								<u><b>Description</b></u><br />
								{$r.description}
							</td>
		                </tr>
		            {/foreach}
		        </table>
		    </fieldset>
	    {/if}
	    
	    {if $festival_date_list}
		    <fieldset style="float:left;">
		        <legend class="legend_festival ui-corner-all" style="position:static;padding:5px 10px;">Festival Informations</legend>

		        <table class="tb" cellspacing="0" cellpadding="4">
		            <tr>
		                <th rowspan="2">Color</th>
		                <th rowspan="2">Festival Date Title</th>
		                <th colspan="2">Date</th>
		            </tr>
		            <tr>
		                <th>From</th>
		                <th>To</th>
		            </tr>
		            {foreach from=$festival_date_list key=festival_date_id item=r}
		                <tr id="tr_festival_date_row-{$festival_date_id}" active="{$r.active}">
		                    <td style="background:{$r.calendar_color};">&nbsp;</td>
		                    <td class="title">{$r.title|default:'-'}</td>
		                    <td class="date_from">{$r.date_from|default:'-'}</td>
		                    <td class="date_to">{$r.date_to|default:'-'}</td>
		                    <td class="details_html" style="display:none;">
								<p>Festival date start from {$r.date_from|default:'-'} to {$r.date_to|default:'-'}</p>
							</td>
		                </tr>
		            {/foreach}
		        </table>
		    </fieldset>
	    {/if}
	{/if}
{/if}

{include file='footer.tpl'}

<script>
{literal}
$(function(){
    MARKETING_PLAN_CALENDAR_MODULE.initialize();   // initial the module
});
{/literal}
</script>
