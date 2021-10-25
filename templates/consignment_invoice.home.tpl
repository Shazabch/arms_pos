{*
5/6/2009 10:42:00 AM Andy
- add export invoice

8/4/2009 2:14:23 PM Andy
- add reset status mark

2/8/2010 4:52:59 PM Andy
- Add new consignment type: consignment over invoice

5/31/2010 2:54:17 PM Andy
- Disable Cosignment Lost/Over Invoice
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.

6/9/2010 4:29:08 PM Andy
- Printing target change to print to a new page. request be samuel.
- Add loading icons to notify user when progress in running.

11/8/2010 11:24:45 AM Andy
- Change consignment invoice, CN and DN when export ubs, prevent it to have negative month.
- Change word "Cancelled" to "Canceled".

11/15/2010 10:26:22 AM Alex
- add branch searching

11/30/2010 11:45:41 AM Andy
- Fix export ubs bugs: if financial month equal to or greater than 10 will cause negative month.

4/3/2012 4:47:45 PM Alex
- add reset button to reset exported ubs

3/24/2014 5:56 PM Justin
- Modified the wording from "Canceled" to "Cancelled".

5/8/2015 11:52 AM Andy
- Remove the junk code related to checkout and markup.
- Change the word "Print Consignment Invoice" to "Print Invoice".
- Add print summary.
- Remove invoice type filter on multiple print.
*}

{include file=header.tpl}

{literal}
<style>
#div_multiple_print{
    background-color:#FFFFFF;
	background-image:url(/ui/ndiv.jpg);
	background-repeat:repeat-x;
	padding: 0 !important;
}
#div_multiple_print_header{
    border:2px ridge #CE0000;
	color:white;
	background-color:#CE0000;
	padding:2px;
	cursor:default;
}

#div_multiple_print_content{
    padding:2px;
}
</style>
{/literal}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var selected_n = 1;
var selected_s;
{literal}
function list_sel(n,s){
	selected_n = n;
	selected_s = s;
	
	var i;
	for(i=0;i<=6;i++){
		if (i==n)
		    $('lst'+i).className='active';
		else
		    $('lst'+i).className='';
	}
	var url_file='consignment_invoice.php';
	$('invoice_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = 's='+s;
	if (n==6) pg +='&str='+ $('search_bid').value ;

	new Ajax.Updater('invoice_list', url_file, {
		parameters: 'a=ajax_load_invoice_list&t='+n+'&'+pg,
		evalScripts: true
		});
}

function ci_print(id,bid){
	document.f_print.id.value=id;
	document.f_print.branch_id.value=bid;
	
	curtain(true);
	
	show_print_dialog();
}

function show_print_dialog()
{
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok()
{
	$('print_dialog').style.display = 'none';
	document.f_print.a.value='print';
	//document.f_print.target = 'ifprint';
	//document.f_print.target = 'xifprint';
	document.f_print.target = '_blank';
	document.f_print.submit();	
	curtain(false);
}

function curtain_clicked(){
	$('print_dialog').style.display = 'none';
	$('div_export_inv').hide();
	curtain(false);
}

function ci_export_pos(ci_id,branch_id,ele){
    var img = $(ele).getElementsBySelector('img');

	// check if already updating, stop user to call multiple request on same sheet
	if(img[0].src.indexOf('clock')>0){
		alert('Please wait, POS Exporting...');
		return false;
	}
	
	if(img[0].title == 'Invoice Exported'){
		var str = 'This invoice already exported, are you sure to export it again?';
	}else{
		var str = 'Click OK to confirm and start export this invoice to pos transaction.';
	}
	if(!confirm(str))   return false;
	
	// update image to clock
	img[0].src = '/ui/clock.gif';
	
	new Ajax.Request(phpself,{
		method: 'get',
		parameters: {
			a: 'export_pos',
			ci_id: ci_id,
			branch_id: branch_id
		},
		onComplete: function(e){
			if(e.responseText=='OK'){
			    alert('Invoice Exported.');
                img[0].src = 'ui/icons/package_green.png';
				img[0].title = 'Invoice Exported';
			}else{
				alert(e.responseText);
				if(img[0].title == 'Invoice Exported'){
                    img[0].src = 'ui/icons/package_green.png';
					img[0].title = 'Invoice Exported';
				}else{
                    img[0].src = 'ui/icons/package_go.png';
					img[0].title = 'Export to Pos Transaction';
				}
			}
		}
	});
}

function export_inv(){
	curtain(true);
	check_and_change_financial_year();
	$('div_export_inv').show();
	center_div('div_export_inv');
}

function toggle_export_ubs_status(cid){
	var img = $('img,export_ubs_flag,'+cid);
	var update_to_status;

    // check if already updating, stop user to call multiple request on same sheet
	if(img.src.indexOf('clock')>0){
		alert('Please wait, updating...');
		return false;
	}
	
	if(img.src.indexOf('flag_green.png')>0){
		update_to_status = '1';
	}else{
        update_to_status = '0';
	}
	
	// update image to clock
	img.src = '/ui/clock.gif';
	
	new Ajax.Request(phpself,{
		parameters:{
		    a: 'toggle_export_ubs_status',
			id: cid,
			update_to_status: update_to_status
		},
		onComplete: function(msg){
  			 if(msg.responseText=='OK'){
                if(update_to_status==1){
					img.src = '/ui/icons/flag_red.png';
				}else{
			        img.src = '/ui/icons/flag_green.png';
				}
			}else{
			    alert(msg.responseText);
                if(update_to_status==0){
					img.src = '/ui/icons/flag_red.png';
				}else{
			        img.src = '/ui/icons/flag_green.png';
				}
			}
		}
	});
}

function refresh_list_after_ubs_export(){
	if(selected_n==4){
		list_sel(selected_n,selected_s);
	}
}

function show_multiple_print(){
	curtain(true);
	center_div($('div_multiple_print').show());
}

function search_inv_no(){
	// check parameters
	if(document.f_multiple_print['inv_no_from'].value.trim()==''){
		alert('Please key in Invoice No from');
		document.f_multiple_print['inv_no_from'].focus();
		return false;
	}
	if(document.f_multiple_print['inv_no_to'].value.trim()==''){
		alert('Please key in Invoice No to');
		document.f_multiple_print['inv_no_to'].focus();
		return false;
	}
	/*if(!document.f_multiple_print['sales'].checked&&!document.f_multiple_print['lost'].checked&&!document.f_multiple_print['over'].checked){
        alert('Please at least choose one of the invoice type');
        return false;
	}*/
	
	$('btn_search_multiple_print').disabled = true;
	$('btn_start_multiple_print').disabled = true;
	
	$('div_multiple_print_list').update(_loading_);
	new Ajax.Updater('div_multiple_print_list', phpself+'?a=ajax_search_inv_no',{
		parameters: $(document.f_multiple_print).serialize(),
		evalScripts: true,
		onComplete: function(e){
			$('btn_search_multiple_print').disabled = false;
		}
	});
}

function create_financial_year_month_value(m, y){
	if(m<10)    m = '0'+m;
	return y+''+m;
}

function check_and_change_financial_year(){
	var selected_m = int(document.f_ex['month'].value);
	var selected_y = int(document.f_ex['year'].value);
	var new_ym = create_financial_year_month_value(selected_m, selected_y);
	
	var f_m = int(document.f_ex['financial_month'].value);
	var f_y = selected_y;
	var current_selected_f_ym = create_financial_year_month_value(f_m, f_y);

	if(current_selected_f_ym>new_ym){
		f_y--;
		current_selected_f_ym = create_financial_year_month_value(f_m, f_y);
	}
	document.f_ex['financial_year'].value = f_y;
}
</script>
{/literal}
<!-- print dialog -->
<div id=print_dialog style="background:#fff;border:3px solid #000;width:250px;position:absolute; padding:10px; display:none;">
<form name=f_print method=get>
<div style="float:right;">
<img src=ui/print64.png hspace=10 align=left> 
</div>
<div style="text-align:center;">
<h3>Print Options</h3>
<input type=hidden name=a>
<input type=hidden name=id>
<input type=hidden name=branch_id>

<div style="text-align:left;">
	<ul style="list-style:none;">
		<li> <input type="checkbox" name="print_ci" checked /> Print Invoice</li>
		<li> <input type="checkbox" name="print_summary" /> Print Summary</li>
	</ul>

</div>
<p align=center><input type=button value="Print" onclick="print_ok()"> <input type=button value="Cancel" onclick="curtain_clicked()"></p>


</div>
</form>

</div>
<!--end print dialog-->

<!-- Export Invoice -->
<div id="div_export_inv" style="background:#fff;border:3px solid #000;width:300px;position:absolute; padding:10px; display:none;z-index:10000;">
<h1>{if ($sessioninfo.privilege.CON_RESET_UBS)}Reset / {/if}Export to UBS</h1>

<form name="f_ex" target="ifprint" method="post">
{*<input type="hidden" name="a" value="export_inv" />*}

<table>
	<tr>
	    <td><b>Branch</b></td>
	    <td>
	        <select name="branch_id" style="width:200px;">
	            <option value="">-- All --</option>
	            {foreach from=$branches key=bid item=b}
			    {if !$branch_group.have_group.$bid}
			    	<option value="{$bid}">{$b.code} - {$b.description}</option>
			    {/if}
			{/foreach}
			{foreach from=$branch_group.header key=bgid item=bg}
			    <optgroup label="{$bg.code}">
			        {foreach from=$branch_group.items.$bgid key=bid item=b}
			            <option value="{$bid}">{$b.code} - {$b.description}</option>
			        {/foreach}
			    </optgroup>
			{/foreach}
	        </select>
	    </td>
	</tr>
	<tr>
	    <td><b>Month / Year</b></td>
	    <td>
	        <select name="month" onChange="check_and_change_financial_year();">
				{foreach from=$months key=k item=m}
				    <option value="{$k}">{$m}</option>
				{/foreach}
			</select>
			<select name="year" onChange="check_and_change_financial_year();">
				{foreach from=$ci_year_list item=c}
				    <option value="{$c.y}">{$c.y}</option>
				{/foreach}
			</select>
		</td>
	</tr>
	<tr>
	    <td><b>Financial Year (MM/YYYY)</b></td>
	    <td>
			<input type="text" name="financial_month" size="3" value="{$financial_date.m}" readonly />
			 /
			<input type="text" name="financial_year" size="5" value="{$financial_date.y}" readonly />
		</td>
	</tr>
</table>

<p>
	{if ($sessioninfo.privilege.CON_RESET_UBS)}<button style="float:left;" name=a value="reset_inv">Reset</button>{/if}
	<button style="float:right;" name=a value="export_inv">Export</button>
</p>
</form>
</div>
<!-- End of Export Invoice-->

<!-- multiple print popups -->
{include file='consignment_invoice.multiple_print.tpl'}

<iframe width=1 height=1 style="visibility:hidden" name=ifprint></iframe>


<h1>{$PAGE_TITLE}</h1>

<div id=show_last>
	{if $smarty.request.t eq 'save'}
	<img src=/ui/approved.png align=absmiddle> Invoice saved as ID#{$smarty.request.save_id}<br>
	{elseif $smarty.request.t eq 'cancel'}
	<img src=/ui/cancel.png align=absmiddle> Invoice ID#{$smarty.request.save_id} was cancelled<br>
	{elseif $smarty.request.t eq 'delete'}
	<img src=/ui/cancel.png align=absmiddle> Invoice ID#{$smarty.request.save_id} was deleted<br>
	{elseif $smarty.request.t eq 'confirm'}
	<img src=/ui/approved.png align=absmiddle> Invoice ID#{$smarty.request.save_id} confirmed.
	{elseif $smarty.request.t eq 'approve'}
	<img src=/ui/approved.png align=absmiddle> Invoice ID#{$smarty.request.save_id} was Fully Approved.
	{elseif $smarty.request.t eq 'reset'}
	<img src=/ui/notify_sku_reject.png align=absmiddle> Invoice ID#{$smarty.request.save_id} was reset.
	{/if}
</div>

<ul>
	<li> <img src=ui/new.png align=absmiddle>
	<a href="{$smarty.server.PHP_SELF}?a=open&id=0">Create New Invoice</a></li>
	
	{*<li> <img src=ui/new.png align=absmiddle>
	<a href="{$smarty.server.PHP_SELF}?a=open&id=0&type=lost">Create New Lost Invoice</a></li>
	
	<li> <img src=ui/new.png align=absmiddle>
	<a href="{$smarty.server.PHP_SELF}?a=open&id=0&type=over">Create New Over Invoice</a></li>*}
	
	<li> <img src=ui/new.png align=absmiddle>
	<a href="javascript:export_inv();">{if ($sessioninfo.privilege.CON_RESET_UBS)}Reset / {/if}Export Invoice to UBS</a></li>
	
	<li> <img src="ui/icons/printer.png" align=absmiddle>
	<a href="javascript:void(show_multiple_print());">Print Multiple Invoice </a></li>

</ul>

<br>


<form onsubmit="list_sel(0,pono.value);return false;">
<div class=tab style="height:25px;white-space:nowrap;">
&nbsp;&nbsp;&nbsp;
<a href="javascript:list_sel(1)" id=lst1 class=active>Saved Invoice</a>
<a href="javascript:list_sel(2)" id=lst2>Waiting for Approval</a>
<a href="javascript:list_sel(5)" id=lst5>Rejected</a>
<a href="javascript:list_sel(3)" id=lst3>Cancelled/Terminated</a>
<a href="javascript:list_sel(4)" id=lst4>Approved</a>
<a name=find_po id=lst0>Find Invoice <input name=pono> <input type=submit value="Go"></a>
{if $BRANCH_CODE eq 'HQ' && $config.consignment_modules}
	<a id=lst6>
		Branch
		<select name="branch_id" id="search_bid">
		    {foreach from=$branches item=b}
		        <option value="{$b.id}" {if $smarty.request.branch_id eq $b.id}selected {/if}>{$b.code}</option>
			{/foreach}
		</select>
		<input type=button onclick="list_sel(6);" value="Go">
	</a>
{/if}
</div>
</form>
<div id="invoice_list" >
</div>
{include file=footer.tpl}

<script>
list_sel(1);
{literal}
new Draggable('div_multiple_print',{ handle: 'div_multiple_print_header'});
{/literal}
</script>
