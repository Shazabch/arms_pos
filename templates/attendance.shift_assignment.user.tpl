{*
12/31/2019 10:32 AM Andy
- Enhanced to highlight the day if it is Holiday.
- Enhanced to show "Leave".
- Added feature to auto de-select holiday and weekend.
*}

{include file='header.tpl'}

<style>
{literal}
td.td_day_no_use{
	background-color: #ddd;
}

div.div_day_num{
	background-color: #fff;
	width: 50px;
	line-height: 25px;
	font-weight: bold;
	border: 1px solid black;
	background-color: #eee;
	margin: 5px;
}

#div_top_right_legend {
	position:fixed;
	background:#ff9;
	opacity:0.6;
	top:60px;
	right: 10;
	padding:5px 10px;
	z-index:10000;
	border: 3px outset grey;
}

.sunday{
	color: red;
}

.div_got_ph{
	border: 3px solid red !important;
}

.div_ph_details{	
	margin-top: 5px;
	padding: 3px;
	border: 1px solid red;
	color: red;
	font-weight: bold;
	background-color: #fff;
}

.div_leave_details{
	margin-top: 5px;
	padding: 3px;
	border: 1px solid blue;
	color: blue;
	font-weight: bold;
	background-color: #fff;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

var shift_color_list = [];
{foreach from=$shift_list key=shift_id item=shift}
	shift_color_list['{$shift_id}'] = '{$shift.shift_color}';
{/foreach}

{literal}
var SHIFT_USER = {
	f_a: undefined,
	f_b: undefined,
	initialize: function(){
		this.f_a = document.f_a;
		this.f_b = document.f_b;
		
	},
	// function when user tick / un-tick all month
	toggle_all_month: function(){
		var c = this.f_a['show_all_m'].checked;
		
		$(this.f_a).getElementsBySelector('input.chx_show_m').each(function(inp){
			inp.checked = c;
		});
		
		for(var i=1; i<=12; i++){
			this.check_month(i);
		}
	},
	// function to check month
	check_month: function(m){
		var c = this.f_a['show_m['+m+']'].checked;
		
		if(c){
			$('div_month-'+m).show();
		}else{
			$('div_month-'+m).hide();
		}		
	},
	// function when user changed day shift
	day_shift_changed: function(m, date){
		var shift_id = this.f_b['user_shift['+date+']'].value;
		var td_day_shift = $('td_day_shift-'+date);
		
		if(shift_id>0){
			$(td_day_shift).style.backgroundColor = '#'+shift_color_list[shift_id];
		}else{
			$(td_day_shift).style.backgroundColor = '';
		}
	},
	// function when user changed column shift
	col_shift_changed: function(m, d){
		var shift_id = $('sel_col_shift-'+m+'-'+d).value;		
		$$('#tbl_month_shift-'+m+' select.sel_day_col_shift-'+m+'-'+d).each(function(sel){
			sel.value = shift_id;
			sel.onchange();
		});
	},
	// function when user changed row shift
	row_shift_changed: function(m, w){
		var shift_id = $('sel_row_shift-'+m+'-'+w).value;	
		$$('#tbl_month_shift-'+m+' select.sel_day_row_shift-'+m+'-'+w).each(function(sel){
			sel.value = shift_id;
			sel.onchange();
		});
	},
	// function when user changed month shift
	month_shift_changed: function(m){
		var shift_id = $('sel_month_shift-'+m).value;
		$$('#tbl_month_shift-'+m+' select.sel_day_month_shift-'+m).each(function(sel){
			sel.value = shift_id;
			sel.onchange();
		});
	},
	// function when user toggle legend
	toggle_legend: function(){
		var img = $('img_toggle_legend');
		
		if(img.src.indexOf('collapse')>0){
			// hide
			$('div_shift_legend').hide();
			img.src = '/ui/expand.gif';
		}else{
			// show
			$('div_shift_legend').show();
			img.src = '/ui/collapse.gif';
		}
	},
	// function when user click on save button
	save_month_clicked: function(m){
		// show wait popup
		GLOBAL_MODULE.show_wait_popup();
				
		var THIS = this;
		var params = $(this.f_b).serialize()+'&save_m='+m;
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				GLOBAL_MODULE.hide_wait_popup();
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						// Update HTML
						alert('Save Successfully.');
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			}
		});
	},
	// function when user click on button deselect holiday
	deselect_ph_clicked: function(m){
		$$('#tbl_month_shift-'+m+' select.sel_day_month_shift-'+m).each(function(sel){
			var got_ph = int($(sel).readAttribute('got_ph'));
			if(got_ph){
				// change shift_id to zero
				sel.value = 0;
				sel.onchange();
			}			
		});
	},
	// function when user click on button deselect weekend
	deselect_weekend_clicked: function(m){
		var d_list = [6,7];
		for(var i=0; i<d_list.length; i++){
			var d = d_list[i];
			var sel = $('sel_col_shift-'+m+'-'+d);
			sel.value = 0;
			sel.onchange();
		}
	}
}
{/literal}
</script>


<div id="div_top_right_legend">
	<div>
		<img src="/ui/collapse.gif" class="clickable" id="img_toggle_legend" onClick="SHIFT_USER.toggle_legend();" title="Show / Hide Legend" />
	</div>
	
	<div id="div_shift_legend">
		<table>
			{foreach from=$shift_list key=shift_id item=shift}
				<tr>
					<td width="20" valign="top">
						<div style="height:15px;background-color: {$shift.shift_color}"></div>
					</td>
					<td nowrap>{$shift.code} - {$shift.description|escape}</td>
				</tr>
			{/foreach}
		</table>
	</div>
</div>

<h1>{$PAGE_TITLE} - {$user.u}</h1>


<p>
	<input type="button" value="<< Go Back to Shift ({$y})" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='{$smarty.server.PHP_SELF}?y={$y}&branch_id={$bid}'" />
</p>

<div class="stdframe">
	<form name="f_a" onSubmit="return false;">
		<b>Select Month:</b>
		<span>
			<input type="checkbox" name="show_all_m" value="1" {if !isset($smarty.request.show_m) or $smarty.request.show_all_m}checked {/if} onChange="SHIFT_USER.toggle_all_month();" /> All
		</span>&nbsp;&nbsp;&nbsp;&nbsp;
		{foreach from=$appCore->monthsList key=m item=m_label}
			<span>
				<input type="checkbox" id="chx_show_m-{$m}" class="chx_show_m" name="show_m[{$m}]" value="{$m}" {if !isset($smarty.request.show_m) or $smarty.request.show_m.$m}checked {/if} onChange="SHIFT_USER.check_month('{$m}');" />
				<label for="chx_show_m-{$m}">{$m_label}</label>
			</span>&nbsp;&nbsp;&nbsp;&nbsp;
		{/foreach}
	</form>
</div>

<ul>
	<li> User can still clock in at time attendance while the shift was not assigned.
		<ul>
			<li> But the report will not calculate their shift status.</li>
			<li> Updating shift here will update to user time attendance shift if their time attendance shift was not assigned.</li>
		</ul>
	</li>
</ul>

<form name="f_b" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_save_user_shift" />
	<input type="hidden" name="branch_id" value="{$bid}" />
	<input type="hidden" name="y" value="{$y}" />
	<input type="hidden" name="user_id" value="{$user_id}" />
	
	{foreach from=$appCore->monthsList key=m item=m_label}
		<div class="div_month" id="div_month-{$m}" style="{if isset($smarty.request.show_m) and !$smarty.request.show_m.$m}display:none;{/if}">
			<br />
			<div class="stdframe">
				<h3>[{$branch_list.$bid.code}] {$m_label} {$y}</h3>
				
				<input type="button" value="De-select Holiday" onClick="SHIFT_USER.deselect_ph_clicked('{$m}');" />
				<input type="button" value="De-select Weekend" onClick="SHIFT_USER.deselect_weekend_clicked('{$m}');" />
				<p>
					* Please take note system doesn't automatically help on deselect the replacement for holiday. Example: De-select Monday if Holiday is Sunday. You will have to de-select it manually.
				</p>
				<table class="report_table" width="100%" style="background-color: #fff;" id="tbl_month_shift-{$m}">
					<tr class="header">
						<th>
							<select id="sel_month_shift-{$m}" onChange="SHIFT_USER.month_shift_changed('{$m}');">
								<option value="0">---</option>
								{foreach from=$shift_list key=shift_id item=shift}
									<option value="{$shift_id}">{$shift.code}</option>
								{/foreach}
							</select>
						</th>
						
						{foreach from=$appCore->dayList key=d item=d_label}
							<th>
								<select id="sel_col_shift-{$m}-{$d}" onChange="SHIFT_USER.col_shift_changed('{$m}', '{$d}');">
									<option value="0">---</option>
									{foreach from=$shift_list key=shift_id item=shift}
										<option value="{$shift_id}">{$shift.code}</option>
									{/foreach}
								</select>
								<br />
								
								<span {if $d gte 7}class="sunday"{/if}>{$d_label}</span>
							</th>
						{/foreach}
						
						
					</tr>
					{foreach from=$data.date_list.$m key=w item=w_data}
						<tr>
							<td align="center">
								<select id="sel_row_shift-{$m}-{$w}" onChange="SHIFT_USER.row_shift_changed('{$m}', '{$w}');">
									<option value="0">---</option>
									{foreach from=$shift_list key=shift_id item=shift}
										<option value="{$shift_id}">{$shift.code}</option>
									{/foreach}
								</select>
							</td>
							
							{foreach from=$appCore->dayList key=d item=d_label}
									{if $w_data.$d}
										{assign var=date value=$w_data.$d.date}
										{assign var=selected_shift_id value=$w_data.$d.shift_id}
										{assign var=selected_leave_id value=$w_data.$d.leave_id}
										{assign var=ph_list value=$w_data.$d.ph_list}
										
										<td align="center" id="td_day_shift-{$date}" class="td_col_shift-{$m}-{$d} td_row_shift-{$m}-{$w}" style="{if $selected_shift_id}background-color: #{$shift_list.$selected_shift_id.shift_color}{/if}">
											<div>
												
												
												
												
												<div class="div_day_num {if $d gte 7}sunday{/if} {if $ph_list}div_got_ph{/if}">
													{$date|date_format:"%d"}
												</div>
												
												<select name="user_shift[{$date}]" onChange="SHIFT_USER.day_shift_changed('{$m}', '{$date}');" class="sel_day_col_shift-{$m}-{$d} sel_day_row_shift-{$m}-{$w} sel_day_month_shift-{$m}" {if $ph_list}got_ph="1"{/if}>
													<option value="0">---</option>
													{foreach from=$shift_list key=shift_id item=shift}
														<option value="{$shift_id}" {if $selected_shift_id eq $shift_id}selected {/if}>{$shift.code}</option>
													{/foreach}
												</select>												
											</div>
											
											{if $ph_list}
												<div class="div_ph_details">
													Holiday: 
													{foreach from=$ph_list item=ph_id name=fph}
														<span title="{$ph_data.ph_list.$ph_id.ph_description|escape}">{$ph_data.ph_list.$ph_id.ph_code}</span>
														{if !$smarty.foreach.fph.last}, {/if}
													{/foreach}
												</div>
											{/if}
												
											{if $selected_leave_id}
												<div class="div_leave_details">
													On Leave:
													<span title="{$leave_list.$selected_leave_id.description|escape}">{$leave_list.$selected_leave_id.code}</span>
												</div>
											{/if}
										</td>
									{else}
										<td align="center" class="td_day_no_use">
											-
										</td>
									{/if}
								
							{/foreach}
						</tr>
					{/foreach}
				</table>
				
				<p align="center">
					<input type="button" value="Save {$m_label} {$y}" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="SHIFT_USER.save_month_clicked('{$m}');" />
				</p>
			</div>
		</div>
	{/foreach}
</form>

<p>
	<input type="button" value="<< Go Back to Shift ({$y})" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='{$smarty.server.PHP_SELF}?y={$y}&branch_id={$bid}'" />
</p>

<script>SHIFT_USER.initialize();</script>
{include file='footer.tpl'}