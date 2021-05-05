{*
REVISION HISTORY
================
11/2/2007 4:39:04 PM gary
- add do priting option for do and invoice.

11/20/2009 12:46:32 PM Andy
- Fix invoice No 'undefined' bug

7/7/2010 6:02:19 PM Andy
- Change system search variable.

11/8/2010 1:53:32 PM Alex
- add branch searching

11/29/2011 04:31:00 PM Andy
- Add when printing if found config "do_printing_allow_hide_date" will show option to allow user to tick "Don't Show DO Date".

1/13/2012 5:52:43 PM Justin
- Added to show new print option "Print DO (Size & Color)" when found config "do_sz_clr_print_template".

4/23/2012 1:31:42 PM Alex
- change print dialog to template do.print_dialog.tpl

3/4/2013 5:39 PM Justin
- Enhanced to prompt user report menu while tick on "Paid" and have new print option "receipt" while config "do_generate_receipt_no" is turned on.

1/13/2015 4:24 PM Andy
- Change invoice markup to default zero.

6/16/2015 5:00 PM Eric
- Hide the invoice markup box when is gst DO

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

5/28/2018 2:00 PM HockLee
- Added batch processing links.

9/25/2018 5:35 PM Andy
- Remove DO print receipt feature.
- Enhanced DO Printing to use shared templates.

10/11/2018 3:37 PM Andy
- Fixed DO Checkout auto popup for print cannot click on print invoice.

8/5/2019 10:46 AM Justin
- Added new module "DO Multiple Checkout".

3/26/2020 3:55 PM William
- Enhanced to show saved id after modify do checkout info.

06/25/2020 3:42 PM Sheila
- Updated button css

7/15/2020 5:02 PM William
 - Enhanced "Credit Sales DO" checkout list can mark as paid and key in (Payment Date, payment type and Remark).
 
8/5/2020 9:00 AM William
- Added checking to do paid payment type selection.

8/10/2020 2:13 PM William
- Remove "-- Please Select --" option.

9/15/2020 3:34 PM William
- Bug fixed function "curtain_clicked" write 2 times.
*}

{include file=header.tpl}
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
var phpself = '{$smarty.server.PHP_SELF}';
var g_do_id;
var g_bid;
var do_invoice_separate_number = {if $config.do_invoice_separate_number}true{else}false{/if};
var do_generate_receipt_no = '{$config.do_generate_receipt_no|default:0}';

{literal}
var do_inv_no = {};

function do_print(id, bid,is_checkout,markup,is_under_gst){
	DO_PRINT.do_print(id,bid,is_checkout,markup,is_under_gst);
}

function curtain_clicked(){
	$('print_dialog').style.display = 'none';
	$('div_paid_status').style.display = 'none';
	curtain(false);
}

</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

{literal}
<script>
function list_sel(n,s){
	var i;
	for(i=0;i<=2;i++)
	{
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	$('do_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==0) pg +='&search='+ $('search').value ;
	
	new Ajax.Updater('do_list', 'do_checkout.php', {
		parameters: encodeURI('a=ajax_load_do_list&t='+n+pg),
		evalScripts: true
		});
}

function update_paid_status(){
	if (confirm('Are you sure?')){
		$('btn_upd_paid').disabled = true;
		new Ajax.Request('do.php'+'?a=ajax_update_paid',{
			parameters: $(document.f_paid).serialize(),
			onComplete: function(msg){
				if(msg.responseText=='OK'){
					var id = document.f_paid['id'].value;
					var bid = document.f_paid['bid'].value;
					if(document.f_paid['paid'].checked == true){
						$('img_paid_status_'+id+'_'+bid).src= "ui/approved.png";
					}else{
						$('img_paid_status_'+id+'_'+bid).src= "ui/icons/cancel.png";
					}
					$('div_paid_status').hide();
					curtain(false);
					alert("Update Success");
				}
				else alert(msg.responseText);
				$('span_list_loading').hide();
				$('btn_upd_paid').disabled = false;
			}
		});
	}
}

function show_paid(id, branch_id){
	new Ajax.Request('do.php',{
		parameters:{ 
			a: 'ajax_show_paid_status',
			id: id, 
			bid: branch_id
		},
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};

			try{
				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] == 1){ // success
					curtain(true);
					$('div_paid_status').update(ret['html']);
					center_div($('div_paid_status').show());
				}else{
					if(ret['error']){
						alert(ret['error']);
					}else{
						alert(str);
					}						
				}
			}catch(ex){
				alert(str);
			}
		}
	});
}

/*function print_receipt(){
	$('main_print_menu').hide();
	$('receipt_no_print_menu').show();
	document.f_print['print_receipt'].checked = true;
	document.f_print['print_do'].checked = false;
	if(document.f_print['print_sz_clr'] != undefined) document.f_print['print_sz_clr'].checked = false;
	if(document.f_print['no_show_date'] != undefined) document.f_print['no_show_date'].checked = false;
	if(document.f_print['acc_copy'] != undefined) document.f_print['acc_copy'].checked = false;
	if(document.f_print['store_copy'] != undefined) document.f_print['store_copy'].checked = false;
	
	show_print_dialog();
}*/

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

function print_assignment_note_by_batch(){
	if(document.f_print_assignment_note_by_batch['batch_code'].value.trim()==''){
		alert('Please enter Batch Code');
		return false;
	}

	var batch = document.f_print_assignment_note_by_batch['batch_code'].value;
	var a = document.f_print_assignment_note_by_batch['a'].value;
	
	window.open(phpself+'?a='+a+'&batch_code='+batch);
}
</script>
{/literal}

<!-- update paid popup -->
<div id="div_paid_status" style="background:#fff;border:3px solid #000;width:350px;position:absolute; padding:10px; display:none;z-index:10000;">
{include file="do.paid_update.tpl"}
</div>
<!-- end update paid popup -->

<div id="show_last">
{if $smarty.request.r eq 'save'}
<img src="/ui/approved.png" align="absmiddle"> DO Checkout saved as ID#{$smarty.request.save_id}<br>
{/if}
</div>

<ul>
	{if $config.enable_reorder_integration}
		<li><img src=ui/new.png align=absmiddle> <a href="?a=do_checkout_by_batch">DO Checkout by Batch</a></li>
		<li> <img src="ui/new.png" align="absmiddle"><a href="javascript:void(togglediv('print_assignment_note_by_batch'));"> Print Assignment Note by Batch</a>
			<div id="print_assignment_note_by_batch" class="stdframe" style="display:none;">
				<form name="f_print_assignment_note_by_batch">
					<input type="hidden" name="a" value="print_assignment_note_by_batch" />
					<b>Batch Code</b> <input type="text" name="batch_code" id="inp_batch_code" /> <img src="ui/rq.gif" align="absbottom" title="Required Field">
					<span id="span_loading_batch_code" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span><br>
					<div id="div_autocomplete_batch_code_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
					<input class="btn btn-primary" type="button" value="Print" onclick="print_assignment_note_by_batch();" />
				</form>
			</div>
		</li>
	{/if}
	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/do.multi_confirm_checkout.php")}
		<li>
			<img src="ui/table_multiple.png" align="absmiddle"> <a href="/do.multi_confirm_checkout.php?process_type=checkout" target="_blank">DO Multiple Checkout</a>
		</li>
	{/if}
</ul>
<br>
<!-- print dialog -->
{include file="do.print_dialog.tpl"}
<!--end print dialog-->
<form name=f_a onsubmit="list_sel(0,0);return false;">
<input type=hidden name=a value=''>
<input type=hidden name=do_no value=''>
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved</a>
<a href="javascript:list_sel(2)" id=lst2>Completed</a>
<a name=find_po id=lst0>Find DO / Branch <input id=search name=pono> <input class="btn-primary" type=submit value="Go"></a>
<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
</div>
</form>
<div id=do_list style="border:1px solid #000">
</div>

<iframe id=ifprint width=1 height=1 style="visibility:hidden"></iframe>
{include file=footer.tpl}

<script>
	{if $smarty.request.t eq 'confirm'}
		list_sel(2);
		{if $confirmed_do}
			if (confirm('Click OK to print DO {$smarty.request.do_no}')) 	do_print('{$confirmed_do.id}','{$confirmed_do.branch_id}', {if $confirmed_do.checkout}true{else}false{/if}, '{$confirmed_do.invoice_markup}', '{$confirmed_do.is_under_gst}');
		{/if}
	{else}
		list_sel(1);
	{/if}

	{if $config.enable_reorder_integration}
		reset_batch_code_autocomplete();
	{/if}
	DO_PRINT.initialise();
</script>
