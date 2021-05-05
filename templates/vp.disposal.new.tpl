{*
1/16/2013 9:39:00 AM Fithri
- add column to show items category (level 3)
- can sort by category or description

1/18/2013 10:50 AM Justin
- Enhanced to hide department dropdown list and become hidden field.

1/21/2013 10:59 AM Justin
- Bug fixed on showing wrong info of adjustment status.
- Enhanced to have pre-confirm feature.
- Modified the confirmation message.

2/4/2013 10:13 AM Fithri
- Disposal type, to select between Disposal or Return

2/4/2013 5:02 PM Fithri
- Bugfix : Total qty set to use global qty decimal point
*}

{if !$form.approval_screen}
{include file=header.tpl}
{else}
<hr noshade size=2>
{/if}
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
#div_type_list ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#div_type_list ul li:hover {
	background:#ff9;
}

#div_type_list ul li.current {
	background:#9ff;
}

#div_type_list:hover ul {
	visibility:visible;
}

.c0 {
	background:#eff;
}
.c1 {
	background:#efa;
}
.csunday {
	color:#f00;
}
</style>
{/literal}
<script>

// update autocompleter parameters when vendor_id or dept_id changed
var phpself = '{$smarty.server.PHP_SELF}';
var date_now = '{$smarty.now|date_format:"%Y-%m-%d"}';
var global_qty_decimal_points = '{$config.global_qty_decimal_points}';
var is_confirmed = false;

{literal}

var DSP = {
	initialize: function(){
		Calendar.setup({
			inputField     :    "added1",
			ifFormat       :    "%Y-%m-%d",
			button         :    "t_added1",
			align          :    "Bl",
			singleClick    :    true
		});
	},
	do_save: function(){
		document.f_a.a.value='save';
		document.f_a.submit();
	},
	do_confirm: function(){
		if (confirm('Are you sure want to confirm?')){
			document.f_a.a.value = "confirm";
			document.f_a.target = "";
			document.f_a.submit();
		}
	},
	do_delete: function(){
		document.f_a.reason.value = '';
		var p = prompt('Enter reason to Delete :');
		if (p.trim()=='' || p==null) return;
		document.f_a.reason.value = p;
		if (confirm('Delete this Disposal?')){
			document.f_a.a.value = "delete";
			document.f_a.submit();
		}
	},
	refresh_table: function (){
		if (is_confirmed) Form.enable(f_a);
		document.f_a.a.value = 'refresh_table';
		var params = document.f_a.serialize();
		if (is_confirmed) Form.disable(f_a);
		new Ajax.Updater("docs_items",phpself,{
			parameters: params,
			onComplete: function(e){
				if (is_confirmed) Form.disable(f_a);
				document.f_a.a.value = ''; //reset the action
				document.f_a.sort_by.disabled = false;
			}
		});
	},
	calc_total: function(){
		var total = 0;
		var e = $('tbl_item').getElementsByClassName('n qty');
		for(var i=0;i<e.length;i++){
			total += float(e[i].value);
		}
		$('total_qty_n').innerHTML=round(total,global_qty_decimal_points);
	},
	
	pre_confirm_stage: function(type){
		if(type == 'confirm'){
			//this.f['preconfirm'].hide();
			//this.f['confirm'].show();
			//this.f['back'].show();
			$('pre_confirm').hide();
			$('final_confirm').show();
			
			var total_rows = $('tbl_item').getElementsByClassName("titem");
			var total_rows_length = total_rows.length;
			
			if(total_rows_length > 0){
				$A(total_rows).each(
					function (r,idx){
						var ri = r.id.split("_");
						var id = ri[1];
						var qty = document.f_a['n_qty['+id+']'].value;
						
						if(qty == 0 || !qty) $('titem_'+id).hide();
					}
				);
				this.reset_row_no(1);
			}
		}else{
			$('pre_confirm').show();
			$('final_confirm').hide();

			var total_rows = $('tbl_item').getElementsByClassName("titem");
			var total_rows_length = total_rows.length;
			
			if(total_rows_length > 0){
				$A(total_rows).each(
					function (r,idx){
						var ri = r.id.split("_");
						var id = ri[1];
						var qty = document.f_a['n_qty['+id+']'].value;
						
						if(qty == 0 || !qty) $('titem_'+id).show();
					}
				);
				this.reset_row_no(0);
			}
		}
	},
	
	reset_row_no: function(filter_hidden){
		var total_rows = $('tbl_item').getElementsByClassName("titem");
		var total_rows_length = total_rows.length;
		var row_count = 0;
		
		if(total_rows_length > 0){
			$A(total_rows).each(
				function (r,idx){
					var ri = r.id.split("_");
					var id = ri[1];
					var qty = document.f_a['n_qty['+id+']'].value;
					
					if(qty > 0 && filter_hidden){
						row_count++;
						$('no_'+id).update(row_count+".");
					}else if(filter_hidden == 0){
						row_count++;
						$('no_'+id).update(row_count+".")
					}
				}
			);
		}
	}
};


</script>
{/literal}

<h1>Disposal {if $form.approved}({$form.report_prefix}{$form.id|string_format:"%05d"}){else}{if $form.id}(ID#{$form.id}){else}(New){/if}{/if}</h1>

{if $form.id}
	<h3>Status:
		{if $form.approved}
			Fully Approved
		{elseif $form.status == 5}
			Deleted
		{elseif $form.status == 4}
			Cancelled/Terminated
		{elseif $form.status == 2}
			Rejected
		{elseif $form.status == 1}
			In Approval Cycle
		{elseif $form.status == 0}
			Saved
		{/if}
	{/if}
</h3>

{include file=approval_history.tpl}

<form name="f_a" method="post">

<input type="hidden" name="a">
<input type="hidden" name="id" value="{$form.id}">
<input type="hidden" name="reason" value="">
<input type="hidden" name="dept_id" value="{$form.dept_id|default:0}">

<div class="stdframe" style="background:#fff;overflow:auto;">
<h4>General Information</h4>

{if $errm.top}
<div id="err"><div class="errmsg"><ul>
{foreach from=$errm.top item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{else}
<div id="err"></div>
{/if}

<table border="0" cellspacing="0" cellpadding="4">

<tr>
<th align="left" width="120">Date</th>
<td>
<input name="adjustment_date" id="added1" value="{$form.adjustment_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size=10  onchange="upper_lower_limit(this);"  maxlength=10  onclick="if(this.value)this.select();">
{if $form.status eq 0}<img align=absmiddle src="ui/calendar.gif" id="t_added1" style="cursor: pointer;" title="Select Date">{/if}
</td>
</tr>

<tr>
<th align="left" width="120">Type</th>
<td>
<select name="adjustment_type">
<option value="DISPOSAL" {if $form.adjustment_type eq 'DISPOSAL'}selected{/if}>DISPOSAL</option>
<option value="RETURN" {if $form.adjustment_type eq 'RETURN'}selected{/if}>RETURN</option>
</select>
{*
<input type="hidden" id="adjustment_type" name="adjustment_type" value="DISPOSAL" />DISPOSAL
<div id="type_2"></div>
*}
</td>
</tr>

<!--tr>
<td><b>Department</b></td>
<td>
	<select name="dept_id">
	{section name=i loop=$dept}
	<option value={$dept[i].id} {if $form.dept_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
	{/section}
	</select>
</td>
</tr-->

<tr>
<td><b>Remark</b></td>
<td>
<textarea rows="2" cols="68" name="remark" onchange="uc(this);">{$form.remark}</textarea>
</td>
</tr>

</table>
</div>



<br>


{if $errm.item}
<div id="err"><div class="errmsg"><ul>
{foreach from=$errm.item item=e}
<li> {$e}</li>
{/foreach}
</ul></div></div>
{/if}

<b>Sort by : </b>
<select name="sort_by" onchange="DSP.refresh_table();">
<option value="category">Category</option>
<option value="description">SKU Description</option>
</select>
<br /><br />

<div style="overflow:auto;">
<table width="100%" id="tbl_item" style="border:1px solid #999; padding:5px; background-color:#fe9" class="input_no_border body" cellspacing="1" cellpadding="1">
<thead class="small">
<tr height="24" bgcolor="#ffffff">
	<th rowspan="2" width="5%">#</th>
	<th nowrap rowspan="2" width="15%">ARMS Code</th>
	<th nowrap rowspan="2" width="10%">Article / MCode</th>
	<th nowrap rowspan="2" width="45%">SKU Description</th>
	<th nowrap rowspan="2" width="15%">Category</th>
	<th nowrap colspan="2" width="10%">Dispose Qty</th>
</tr>
</thead>

<tbody id="docs_items">
{include file=vp.disposal.new.row.tpl}
</tbody>

<tfoot id="tbl_footer">
<tr height=24 bgcolor=#ffffff>
	<th colspan="5" class="r">Total</th>
	<th class="r" id="total_qty_n"></th>
</tr>
</tfoot>

</table>
</div>

</form>

{if $form.approval_screen}
<form name="f_b" method="post">
<input type="hidden" name="a" value="approve">
<input type="hidden" name="comment" value="">
<input type="hidden" name="id" value="{$form.id}">
<input type="hidden" name="branch_id" value="{$form.branch_id}">
<input type="hidden" name="approvals" value="{$form.approvals}">
<input type="hidden" name="approval_history_id" value="{$form.approval_history_id}">
<input type="hidden" name="curr_date" value="{$form.adjustment_date}">
</form>
{/if}

<p id="pre_confirm" align="center">
	    
{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id)) and !$form.approval_screen and !$form.approved}
<input name="bsubmit" type="button" value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="javascript:DSP.do_save()" >
{/if}

{if (!$form.id || $form.status>0 || $form.approved) and !$form.approval_screen }
<input type="button" value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/vp.disposal.php'">
{/if}

{if $form.id and !$form.approved and !$form.status}
<input type="button" value="Delete" style="font:bold 20px Arial; background-color:#900; color:#fff;" onclick="javascript:DSP.do_delete()">
{/if}

{if (!$form.status or ($form.status==2 and $form.user_id==$sessioninfo.id)) and !$form.approval_screen and !$form.approved}
<input type="button" name="preconfirm" value="Confirm" style="font:bold 20px Arial; background-color:#090; color:#fff;" onclick="DSP.pre_confirm_stage('confirm');">
{/if}

</p>

<p align="center" id="final_confirm" style="display:none;">
	<input type="button" name="back" value="Back" style="font:bold 20px Arial; background-color:#950; color:#fff;" onclick="DSP.pre_confirm_stage('cancel');">
	<input type="button" name="confirm" value="Final Confirm" style="font:bold 20px Arial; background-color:#090; color:#fff;" onclick="DSP.do_confirm('confirm');">
</p>
{if !$form.approval_screen}
{include file=footer.tpl}
{/if}

<script>
DSP.initialize();
DSP.calc_total();
{if $form.status neq 0}
var is_confirmed = true;
Form.disable(f_a);
document.f_a.sort_by.disabled = false;
{/if}
</script>
