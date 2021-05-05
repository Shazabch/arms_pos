{*
1/4/2013 5:24 PM Justin
- Enhanced to have new feature that allows user to edit cash domination.

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

3/27/2015 6:20 PM Justin
- Enhanced to have GST info.
*}

{include file=header.tpl}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var type_list = [];
{foreach from=$config.membership_cardtype key=ct item=item}
	type_list.push('{$ct}');
{/foreach}
{literal}

function reload_list()
{
	var jax = new Ajax.Updater(
		"udiv", phpself,
		{
			method: 'get',
			parameters: "a=ajax_refresh_table&"+Form.serialize(document.f_d),
			onLoading: function() { $('udiv').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...' },
		});
}

function do_print()
{
	/*y = document.f_d.Date_Year.value;
	m = document.f_d.Date_Month.value;
	d = document.f_d.Date_Day.value;
	dt = y+'-'+m+'-'+d;*/
	_irs.document.location = phpself+"?a=print&"+Form.serialize(document.f_d);
}

function cd_menu_dialog(id, bid, cid){
	// set table, branch and counter ids
	document.f_cd['id'].value = id;
	document.f_cd['bid'].value = bid;
	document.f_cd['cid'].value = cid;
	
	// get info by type, cashier and time
	var type = $('type_'+id+'_'+bid+'_'+cid).value;
	var cashier = $('cashier_'+id+'_'+bid+'_'+cid).value;
	var time = $('time_'+id+'_'+bid+'_'+cid).value;
	var coh = $('coh_'+id+'_'+bid+'_'+cid).value;
	var ori_coh = $('ori_coh_'+id+'_'+bid+'_'+cid).value;
	$('ori_coh').update("(Original: "+round(ori_coh, 2)+")");
	// set info for type, cashier and time
	$('cd_msg_type').update(type);
	$('cd_msg_cashier').update(cashier);
	$('cd_msg_time').update(time);

	// get cash on hand
	document.f_cd['coh'].value = round(coh, 2);
	// loop and place the current cash domination by card type
	$A(type_list).each(function(ct){
		var ct_inv = $('card_'+ct+'_'+id+'_'+bid+'_'+cid).value;
		document.f_cd['card['+ct+']'].value = ct_inv;
		var ori_ct_inv = $('ori_card_'+ct+'_'+id+'_'+bid+'_'+cid).value;
		$('ori_card_inv_'+ct).update("(Original: "+ori_ct_inv+")");
	});

	$('cd_menu_dialog').show();
	
	
	center_div('cd_menu_dialog');
	curtain(true);
}

function cd_save(){
	if(!confirm("Are you sure want to update?")) return;

	new Ajax.Request(phpself, {
		method:'post',
		parameters: Form.serialize(document.f_cd)+'&a=ajax_update_cash_domination',
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			//alert(m.responseText);
		},
		onComplete: function(m) {
			var str = m.responseText.trim();
			var ret = {};
			var err_msg = '';
					
			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok']){ // success
					alert("Successfully updated.");
					reload_list();
					curtain_clicked();
					return;
				}else{  // save failed
					if(ret['failed_reason']) err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}
			
			if(!err_msg) err_msg = 'No Respond from server.';
			// prompt the error
			alert(err_msg);
		}
	});
}

function cd_cancel(){
	document.f_cd['id'].value = "";
	document.f_cd['bid'].value = "";
	document.f_cd['cid'].value = "";
	curtain_clicked();
}

function curtain_clicked(){
	$('cd_menu_dialog').hide();
	curtain(false);
}
{/literal}
</script>
{literal}
<style>
.tbh {
	font-weight:bold;
	border-top:1px solid #999;
	border-bottom:1px solid #999;
	background-color:#fe7;
}

.tbr {
	font-weight:bold;
	border-bottom:1px solid #ccc;
	background-color:#ffd;
}
.tbe {
	font-weight:bold;
	border-top:1px solid #ccc;
	border-bottom:1px solid #ccc;
	background-color:#efc;
}

.tbmr {
	font-weight:bold;
	border-bottom:2px solid #000;
	background-color:#fef;
}

.tbst {
	font-weight:bold;
	border-bottom:2px solid #000;
	background-color:#cef;
}

.tbtrc {
	font-weight:bold;
	border-top:1px solid #999;
	border-bottom:1px solid #999;
	background-color:#ae1;
	line-height: 2em;
}

.tbt {
	font-weight:bold;
	border-top:1px solid #999;
	border-bottom:1px solid #999;
	background-color:#fe4;
	line-height: 2em;
}

#cd_menu_dialog{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
}

#cd_menu_dialog_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#cd_menu_dialog_contents{
	padding:12px;
}

#cd_msg_type, #cd_msg_cashier, #cd_msg_time{
	font-size: 12px;
	font-weight: bold;
	color: blue;
}
</style>

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
function init_calendar()
{
	Calendar.setup({
	    inputField     :    "sdate",     // id of the input field
	    ifFormat       :    "%e/%m/%Y",      // format of the input field
	    button         :    "bdate",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
}
</script>
{/literal}

<form name="f_cd" onsubmit="return false;">
	<div id="cd_menu_dialog" style="position:absolute;z-index:10000;width:400px;display:none;border:2px solid #CE0000;overflow-y:auto;">
		<div id="cd_menu_dialog_header"><span style="float:left;">Edit Cash Denomination</span>
			<span style="float:right;">
				<img src="/ui/closewin.png" align="absmiddle" onClick="curtain_clicked();" class="clickable"/>
			</span>
			<div style="clear:both;"></div>
		</div>
		<div id="cd_menu_dialog_contents">
			<table width="100%">
				<tr>
					<th align="left" width="30%">Type:</th>
					<td><span id="cd_msg_type"></span></td>
				</tr>
				<tr>
					<th align="left">Cashier:</th>
					<td><span id="cd_msg_cashier"></span></td>
				</tr>
				<tr>
					<th align="left">Time:</th>
					<td><span id="cd_msg_time"></span></td>
				</tr>
				<tr>
					<td colspan="2">&nbsp;
						<input type="hidden" name="id" value="" />
						<input type="hidden" name="bid" value="" />
						<input type="hidden" name="cid" value="" />
					</td>
				</tr>
				<tr>
					<th colspan="2" align="left">COH & Cards Inventory:</th>
				</tr>
				<tr>
					<th align="left">Cash on Hand</th>
					<td>
						<input type="text" name="coh" id="coh" class="r" size="12" onclick="if(this.value) this.select();" onchange="mf(this);" value=""> <span id="ori_coh"></span>
					</td>
				</tr>				
				{foreach from=$config.membership_cardtype key=ct item=ct_info}
					<tr>
						<th align="left">{$ct_info.description}</th>
						<td>
							<input type="text" name="card[{$ct}]" id="card_{$ct}" class="r" size="12" onclick="if(this.value) this.select();" onchange="mi(this);" value=""> <span id="ori_card_inv_{$ct}"></span>
						</td>
					</tr>
				{/foreach}
				<tr>
					<td colspan="2" align="right">
						<br />
						<button onclick="cd_save();">Save</button>&nbsp;
						<button onclick="cd_cancel();">Cancel</button>
					</td>
				</tr>
			</table>
		</div>
	</div>
</form>

<h1>Membership Counter Report</h1>
<p>
<form name="f_d">
{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name="branch_id" onChange="reload_list()">
	<option value="">All</option>
	{section name=i loop=$branches}
	<option value="{$branches[i].id}" {if $smarty.request.branch_id eq $branches[i].id}selected{/if}>{$branches[i].code}</option>
	{/section}
	</select> &nbsp;&nbsp;
{/if}

<b>Date</b> <input size=10 id=sdate name=date value="{$smarty.now|date_format:"%d/%m/%Y"}"> <img align=absbottom src="ui/calendar.gif" id="bdate" style="cursor: pointer;" title="Select Date"> &nbsp;&nbsp;
<script>
init_calendar();
</script>
<input type=button value="Reload" onclick="reload_list()"> <input type=button value="Print" onclick="do_print()">
</form>
</p>

<div id="udiv">
{php}show_table();{/php}
</div>

{literal}
<script>
new Draggable('cd_menu_dialog',{ handle: 'cd_menu_dialog_header'});
</script>
{/literal}

{include file=footer.tpl}

<div style="visibility:hidden"><iframe name=_irs width=1 height=1></div>

