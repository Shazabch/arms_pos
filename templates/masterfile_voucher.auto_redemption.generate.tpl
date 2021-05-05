{*
10/14/2019 11:49 AM William
- Bug fix voucher code limit digit as 7.
- Add new button "Auto" to generate voucher code and remove default auto load voucher code.

10/18/2019 2:48 PM William
- Added new checking to avoid voucher code save negative code.

06/29/2020 11:05 AM Sheila
- Updated button css.
*}
{include file='header.tpl'}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var VOUCHER_AUTO_GENERATE = {
	initialize: function(){
		var THIS = this;
		
		// Add new batch
		$('btn_add_new_batch').observe('click', function(){
			THIS.add_new_batch();
		});
		
		// init event for batch
		$$('#tbody_batch_list tr.tr_date_row').each(function(tr){	// loop for each batch
			var row_index = tr.id.split("-")[1];
			
			// add event to row
			THIS.init_batch_row_event(row_index);
		});
		
		// init event to confirm button
		$('btn_confirm').observe('click', function(){
			THIS.confirm_clicked();
		});
		
		// init event to "All" branch checkbox
		$('inp_all_branch').observe('change', function(){
			THIS.toggle_all_branch_checkbox();
		});
		
		// event to toggle use all voucher
		$('chx_toggle_use_all').observe('change', function(){
			THIS.toggle_use_all_voucher();
		});
		
		$('btn_get_new_code').observe('click',function(){
			THIS.auto_get_new_code();
		});
	},
	init_calendar: function(batch_no){
		Calendar.setup({
			inputField     :    "inp_batch_date_from-"+batch_no,
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_batch_date_from-"+batch_no,
			align          :    "Bl",
			singleClick    :    true
		});
		
		Calendar.setup({
			inputField     :    "inp_batch_date_to-"+batch_no,
			ifFormat       :    "%Y-%m-%d",
			button         :    "img_batch_date_to-"+batch_no,
			align          :    "Bl",
			singleClick    :    true
		});
	},
	// function to add new batch
	add_new_batch: function(){
		var THIS = this;
		
		// clone obj
		var new_row = cloneEle($('tr_date_row-0'));
	
		// get new row index
		var new_row_index = this.get_new_row_index();
		
		// change id to new row index
		var new_id = 'tr_date_row-'+new_row_index
		new_row.id = new_id;
		
		// convert to html
		var new_html = toHTML(new_row);
		
		// date from
		new_html = new_html.replace(/batch_date_from\[0\]/g,'batch_date_from['+new_row_index+']');
		new_html = new_html.replace(/inp_batch_date_from-0/g,'inp_batch_date_from-'+new_row_index);
		new_html = new_html.replace(/img_batch_date_from-0/g,'img_batch_date_from-'+new_row_index);
		
		// date to
		new_html = new_html.replace(/batch_date_to\[0\]/g,'batch_date_to['+new_row_index+']');
		new_html = new_html.replace(/inp_batch_date_to-0/g,'inp_batch_date_to-'+new_row_index);
		new_html = new_html.replace(/img_batch_date_to-0/g,'img_batch_date_to-'+new_row_index);
		
		// add into page
		new Insertion.Bottom('tbody_batch_list', new_html);
		
		// put event to all element 
		this.init_batch_row_event(new_row_index);
		
		// re-number the batch number
		this.renumber_batch_no();		
	},
	init_batch_row_event: function(row_index){
		var THIS = this;
		
		// put calendar event
		this.init_calendar(row_index);
		
		// give event to remove button and show it
		if(row_index>0){
			var img_remove_batch = $$('#tr_date_row-'+row_index+' img.img_remove_batch')[0];
			$(img_remove_batch).observe('click', function(){
				THIS.remove_batch_clicked(this);
			}).show();
		}
	},
	// function to return new row index
	get_new_row_index: function(){
		var curr_max_row_index = 0;
		
		$$('#tbody_batch_list tr.tr_date_row').each(function(tr){
			var id_info = tr.id.split("-");
			var row_index = int(id_info[1]);
			
			if(row_index > curr_max_row_index) 	curr_max_row_index = row_index;
		});
		return curr_max_row_index+1;
	},
	// function to re-number batch no
	renumber_batch_no: function(){
		var row_no = 1;
		
		$$('#tbody_batch_list tr.tr_date_row span.span_no').each(function(span){
			$(span).update(row_no+'.');
			row_no++;
		});
	},
	// get row index by passing elemnt
	get_row_index_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parent until it found the tr contain row index
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_date_row')){    // found the tr
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;

		var row_num = parent_ele.id.split('-')[1];
		return row_num;
	},
	// function when user click remove batch
	remove_batch_clicked: function(img){
		// get row index of this image
		var row_index = this.get_row_index_by_ele(img);

		// remove the row
		$('tr_date_row-'+row_index).remove();
		
		// re-number batch
		this.renumber_batch_no();
	},
	// function to validate form
	check_form: function(){
		var f = document.f_a;
		
		// format
		if(f['print_format'].length<=0){
			alert('No Printing Format Available');
			return false;
		}

		// check got tick voucher or not
		var voucher_use_list = $(f).getElementsBySelector("input.voucher_use");
		
		var checked_count = 0;
		for(var i=0; i<voucher_use_list.length; i++){
			if(voucher_use_list[i].checked){
				checked_count++;
				break;
			}	
		}
		
		// no voucher is selected
		if(checked_count<=0){
			alert('Please select at least one voucher type');
			return false;
		}
		
		// code start
		if(f['code_start'].value.trim()==''){
			alert('Please key in Voucher Code Start At');
			f['code_start'].focus();
			return false;
		}
		if(f['code_start'].value < 0){
			alert('Not allow to key in nagative voucher code.');
			f['code_start'].focus();
			return false;
		}
		
		// check batch date
		batch_tr_list = $$('#tbody_batch_list tr.tr_date_row');
		for(var i=0; i<batch_tr_list.length; i++){
			var tr = batch_tr_list[i];
			var row_index = tr.id.split("-")[1];
			
			if($('inp_batch_date_from-'+row_index).value.trim()==''){
				alert('Please key in batch date from');
				$('inp_batch_date_from-'+row_index).focus();
				return false;
			}

			if($('inp_batch_date_to-'+row_index).value.trim()==''){
				alert('Please key in date to');
				$('inp_batch_date_to-'+row_index).focus();
				return false;
			}
		}
		
		return true;
	},
	// function when user click "confirm & print"
	confirm_clicked: function(){
		if(!this.check_form())	return false;
		if(!confirm('Are you sure?'))	return false;
		
		document.f_a.submit();
	},
	// function when user toggle "all" branch
	toggle_all_branch_checkbox: function(){
		var c = $('inp_all_branch').checked;
		
		var interbranch_list = $(document.f_a).getElementsBySelector('input.interbranch');
		for(var i=0; i<interbranch_list.length; i++){
			interbranch_list[i].checked = c;
		}
	},
	// function when user toggle use all voucher
	toggle_use_all_voucher: function(){
		var c = $('chx_toggle_use_all').checked;
		
		$(document.f_a).getElementsBySelector("input.voucher_use").each(function(inp){
			inp.checked = c;
		});
	},
	// function to automatically get new voucher code
	auto_get_new_code: function(){
		$('btn_get_new_code').disabled = true;
				
		var params = {
			a: 'ajax_get_new_voucher_code'
		};
		
		new Ajax.Request(phpself, {
			method:'post',
			parameters: params,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
				$('btn_get_new_code').disabled = false;
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['new_code']){ // success
						// Redirect to main page
						$('code_start').value = ret['new_code'];
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
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
	<ul style="color:red;">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" class="stdframe" onSubmit="return false;" method="post">
	<input type="hidden" name="a" value="generate_voucher" />
	
	<table width="100%">
		<!-- Voucher Settings -->
		<tr>
			<td colspan="2">
				<h4>Voucher Settings</h4>
			</td>
		</tr>
		<tr>
			<td width="150" valign="top">
				<b>Available Voucher Value</b>
			</td>
			<td>
				<table class="report_table" style="background-color:white;">
					<tr class="header">
						<th>Use<br /><input type="checkbox" id="chx_toggle_use_all" /></th>
						<th>Voucher <br />Value</th>
						<th>Points <br />per Voucher[<a href="javascript:void(alert('Not Set = Free Voucher'))">?</a>]</th>
						<th>Limit [<a href="javascript:void(alert('Limit Voucher Qty per member and per batch'))">?</a>]</th>
					</tr>
					{foreach from=$voucher_value_list key=k item=r}
						{if $r.info.allowed}
							<tr>
								<!-- Use -->
								<td align="center">
									<input type="hidden" name="voucher_value[{$k}]" value="{$r.info.voucher_value}" />
									<input type="hidden" name="max_qty[{$k}]" value="{$r.info.max_qty}" />
									<input type="hidden" name="points_use[{$k}]" value="{$r.info.points_use}" />
									<input type="checkbox" name="voucher_use[{$k}]" value="1" class="voucher_use" {if $form.voucher_use.$k}checked {/if} />
								</td>
								
								<!-- Voucher Value -->
								<td class="r">
									{$r.info.voucher_value}
								</td>
								
								<!-- Points -->
								<td class="r">
									{$r.info.points_use}
								</td>
								
								<!-- Max Qty -->
								<td class="r">
									{$r.info.max_qty|ifzero:'-'}
								</td>
							</tr>
						{/if}
					{/foreach}
				</table>
			</td>
		</tr>
		<tr>	
			<td>
				<b>Maximum Points Use</b>
			</td>
			
			<td>
				<input type="text" name="max_points_use" size="10" value="{$form.max_points_use}" onChange="miz(this);" /> (Leave empty may cause the system to drain out all points from member)
			</td>
		</tr>
		
		<!-- Print Settings -->
		<tr>
			<td colspan="2">
				<h4>Print Settings</h4>
			</td>
		</tr>
		<tr>
			<td><b>Code Start at</b></td>
			<td>
				<input type="text" name="code_start" id="code_start" size="10" maxlength="7" value="{$form.code_start|default:''}" onChange="miz(this);" />
				<input id="btn_get_new_code" type="button" value="Auto" />
				<img src="ui/rq.gif" align="absmiddle" />
			</td>
		</tr>
		<tr>
			<td><b>Format</b></td>
			<td>
				<select name="print_format">
					{foreach from=$config.voucher_member_redeem_print_template key=k item=r}
						<option value="{$k}" {if $form.print_format eq $k}selected {/if}>{$r.description}</option>
					{/foreach}
				</select>
				{if count($config.voucher_member_redeem_print_template)<=0}
					<span style="color:red;">Error: No Format Available</span>
				{/if}
			</td>
		</tr>
		
		<!-- Activation Settings -->
		<tr>
			<td colspan="2"><h4>Activation Settings</h4></td>
		</tr>
		<tr>
		    <td><b>Interbranch</b></td>
		    <td colspan=2 id="branch_check_id">
				<input type="checkbox" id="inp_all_branch"> <label for="all_branch_id">All</label> &nbsp;&nbsp;
				{assign var=a value=$form.interbranch}
				{foreach from=$branches key=bid item=b}
					{assign var=bcode value=$b.code}
					{if $bcode==$BRANCH_CODE}<img src="ui/checked.gif">{/if}
					<input {if $bcode==$BRANCH_CODE}style="display:none;" {else}class="interbranch" {/if} type="checkbox" name="interbranch[{$bid}]" id="interbranch_{$bid}" value="{$bid}" {if $bcode==$BRANCH_CODE || $form.interbranch.$bid} checked {/if} > <label for="interbranch_{$bid}">{$bcode}</label> &nbsp;&nbsp;
				{/foreach}
			</td>
		</tr>
		{if $config.voucher_show_advanced_options}
			<tr>
				<td valign="top"><b>More Options</b></td>
				<td colspan=2>
					<input type="checkbox" name="disallow_disc_promo" value="1" {if $form.disallow_disc_promo}checked {/if} > Disallow to use with discounts/promotions <br />
					<input type="checkbox" name="disallow_other_voucher" value="1" {if $form.disallow_other_voucher}checked {/if} > Disallow to use with other vouchers
				</td>
			</tr>
		{/if}
		
		<!-- Batch Settings -->
		<tr>
			<td colspan="2"><h4>Batch Settings</h4></td>
		</tr>
		<tr>
			<td colspan="2">
				<table class="tb" cellspacing="0" cellpadding="2">
					<tr>
						<th>Batch</th>
						<th>Date From</th>
						<th>Date To</th>
					</tr>
					<tbody id="tbody_batch_list">
						{foreach from=$form.batch_date_from key=k item=batch_date_from name=f_batch}
							{assign var=row_index value=$smarty.foreach.f_batch.index}
							{assign var=batch_date_to value=$form.batch_date_to.$k}
							
							<tr id="tr_date_row-{$row_index}" class="tr_date_row">
								<td class="r" nowrap="nowrap">
									<img src="ui/remove16.png" align="absmiddle" class="img_remove_batch clickable" style="display:none;" /> 
									<span class="span_no">{$smarty.foreach.f_batch.iteration}.</span>
								</td>
								<td nowrap="nowrap">
									<input type="text" size="10" name="batch_date_from[{$row_index}]" id="inp_batch_date_from-{$row_index}" value="{$batch_date_from}" />
									<img align="absmiddle" src="ui/calendar.gif" id="img_batch_date_from-{$row_index}" style="cursor: pointer;" title="Select Date" />
								</td>
								<td nowrap="nowrap">
									<input type="text" size="10" name="batch_date_to[{$row_index}]" id="inp_batch_date_to-{$row_index}" value="{$batch_date_to}" />
									<img align="absmiddle" src="ui/calendar.gif" id="img_batch_date_to-{$row_index}" style="cursor: pointer;" title="Select Date" />
								</td>
							</tr>
						{/foreach}
					</tbody>					
				</table>
				<br />
				<input type="button" value="Add New Batch" id="btn_add_new_batch" />
			</td>
		</tr>
	</table>
</form>

<p align="center">
	<span style="color:red;">Warning: Once confirm, the member points will be deduct and cannot be recover.</span><br />
	<input class="btn btn-success" type=button value="Confirm & Print" id="btn_confirm">
</p>


<script type="text/javascript">
	VOUCHER_AUTO_GENERATE.initialize();
</script>

{include file='footer.tpl'}
