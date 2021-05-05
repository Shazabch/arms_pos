{*
8/5/2010 6:07:22 PM Andy
- SKU Group Drop "GRN cutoff date", "report usage", "department" and "allowed user list".

7/25/2011 11:00:32 AM Justin
- Fixed the URL's exceed problem.

11/27/2012 1:43 PM Andy
- Redesign sku group details popup.

12/12/2012 4:56:00 PM Fithri
- multiple user view/edit
- owner can share sku with other user
- dont allow delete item from sku group if the item got sales

1/11/2012 3:58 PM Andy
- Add return how many item has been added into list.
- Add prompt of how many item was inserted and duplicated.
- Add a checkbox to auto select all sku item checkbox.

1/18/2013 4:05 PM Justin
- Enhanced to show mcode on the error message when sku items is duplicated.

3/19/2015 11:30 AM Andy
- Enhanced to capture total item count when user press 'save' button.

06/26/2020 1:10 PM Sheila
- Updated button css.
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

{literal}
<style>
#supp_popup{
   left: 0px;
   top: 0px;
   background:#000;
   filter:alpha(opacity=70);
   opacity: 0.7;
   -moz-opacity: 0.7;
   width:0;
   height:0;
   position:absolute;
   z-index:1000;
}
#sku_table{
	left:150px;
	top:50px;
	position:absolute;
	width:700px;
	height:440px;
	background:white;
	z-index:4000;
	padding:5px;
}
.calendar, .calendar table {
	z-index:100000;
}
</style>
{/literal}
<script type="text/javascript">
var lastn = '';
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
	resetlist();
	document.f_a.changed_fields.value = '';
	document.f_a.code.focus();
}

function ed(n)
{
	document.getElementById('abtn').style.display = 'none';
	document.getElementById('ebtn').style.display = '';
	document.getElementById('bmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	showdiv('div_sku_group_popup');
	document.f_a.id.value = n;
	_irs.document.location = '?a=e&id='+n;
	lastn = n;

	document.f_a.a.value = 'u';
	document.f_a.code.focus();
}

function add()
{
    //clear_sku_from_list();
    
	showdiv('div_sku_group_popup');
	document.f_a.sku_group_id.value = 0;
	document.f_a.branch_id.value = '';
	document.f_a.a.value = 'save';
	document.f_a.code.value = '';
	document.f_a.description.value = '';
	document.f_a.code.focus();
	
	clear_sku_item_list();
	
	$('supp_popup').style.height = document.body.clientHeight +'px';
    $('supp_popup').style.width = document.body.clientWidth +'px';
	$('supp_popup').show();
}

function act(n, s)
{
	_irs.document.location = '?a=v&id='+n+'&v='+s;
}

function checkForm()
{
	if (empty(document.f_a.code, 'You must enter Code'))
	{
		return false;
	}
	if (empty(document.f_a.description, 'You must enter Description'))
	{
		return false;
	}

	/*if ($('sku_code_list').length <= 0)
	{
		alert('You must select at least add a SKU item into this group');
		return false;
	}*/
	var tr_sku_item_code_row_list = $$('#tbody_sku_item_code_row_list tr.tr_sku_item_code_row');
	if(tr_sku_item_code_row_list.length<=0){
		alert('You must add at least 1 SKU item into this group');
		return false;
	}

	return true;
}

function mv(src, dst)
{
	if (src.selectedIndex == -1) return;
	dst.options[dst.options.length] = new Option(src.options[src.selectedIndex].text, src.options[src.selectedIndex].value)
	n = src.selectedIndex;
	src.options[src.selectedIndex] = null;
	if (src.options.length-1 >= n)
		src.selectedIndex = n;
	else
		src.selectedIndex = src.options.length-1;
}

function mvall(src, dst)
{
	while (src.options.length>0)
	{
		dst.options[dst.options.length] = new Option(src.options[0].text, src.options[0].value)
		src.options[0] = null;
	}
}

function mvshare(src, dst)
{
	var i = 0;
	while (i<src.options.length)
	{
	    if (src.options[i].selected) {
	        dst.appendChild(src.options[i]);
		}
		else {
		    i++;
		}
	}
}

function select_all_share()
{
	src = $('share_list');
	var i = 0;
	while (i<src.options.length)
	{
		src.options[i].selected = true;
		i++;
	}
}

function clshare(src, dst)
{
	var i = 0;
	while (i<src.options.length) dst.appendChild(src.options[i]);
}

// reset the src list according to _brands field
function resetlist()
{
	str = document.f_a._brands.value;
	sel = document.f_a.sel_brand;
	src = document.f_a.src_brand;

	sel.options.length=0;
	src.options.length=0;

	if (str != '')
	{
		// populate to both lists base on selected brands
		for (i=0; i<brand_list.length-1;i++)
		{
			if (str.indexOf('|'+brand_list[i][0]+'|') >= 0)
				sel.options[sel.options.length] = new Option(brand_list[i][1], brand_list[i][0]);
			else
				src.options[src.options.length] = new Option(brand_list[i][1], brand_list[i][0]);
		}
	}
	else
	{
		// no selection, default all to src
		for (i=0; i<brand_list.length-1;i++)
			src.options[src.options.length] = new Option(brand_list[i][1], brand_list[i][0]);
	}
}

function submitForm(){
	$('btn_save').disabled=true;
	var qualify = checkForm();
	
	if(!qualify){
	    $('btn_save').disabled=false;
		return false;
	}
	
	/*for(var i=0; i<$('sku_code_list').length; i++){
        $('sku_code_list').options[i].selected = true;
	}*/
	
	// get item count
	total_item_count = 0;
	$$('#div_sku_group tr.tr_sku_item_code_row').each(function(inp){
		total_item_count++;
	});
	document.f_a['total_item_count'].value = total_item_count;
	
 	new Ajax.Request(phpself,
	{
		method: 'post',
		parameters: Form.serialize(document.f_a),
		evalScripts: true,
		onComplete: function(e) {
		    $('btn_save').disabled=false;
		    
		    if(e.responseText.indexOf('Error:') >= 0){
		        //$('sku_code_list').selectedIndex = -1;
				alert(e.responseText);
				return;
			}
			
		    $('div_table').update(e.responseText);

            document.f_a.reset();
			hidediv('div_sku_group_popup');
			hidediv('supp_popup');
		}
	});
}

function submitFormShare(){
	$('btn_save_share').disabled=true;
	select_all_share();
	
 	new Ajax.Request(phpself,
	{
		method: 'post',
		parameters: Form.serialize(document.f_b),
		evalScripts: true,
		onComplete: function(e) {
		    $('btn_save_share').disabled=false;
		    
		    if(e.responseText.indexOf('Error:') >= 0){
		        //$('sku_code_list').selectedIndex = -1;
				alert(e.responseText);
				return;
			}
			
		    $('div_table').update(e.responseText);

            document.f_b.reset();
			hidediv('div_share_popup');
			hidediv('supp_popup');
		}
	});
}

function deleteGroup(sku_group_id,branch_id){
	if(confirm('Click OK to delete this group')){
	    var p = $H({
			a: 'delete',
			sku_group_id: sku_group_id,
			branch_id: branch_id
		});
	
        new Ajax.Request(phpself+'?'+p.toQueryString(),
		{
			onComplete: function(e) {
			    if(e.responseText.indexOf('Error:') >= 0){
					alert(e.responseText);
					return;
				}
				
				$('div_table').update(e.responseText);
			}
		});
	}
}

function editGroup(sku_group_id,branch_id){
    //clear_sku_from_list();
    
    var p = $H({
		a: 'edit',
		sku_group_id: sku_group_id,
		branch_id: branch_id
	});

    new Ajax.Request(phpself+'?'+p.toQueryString(),
	{
		onComplete: function(e) {
			if(e.responseText.indexOf('Error:') >= 0){
				alert(e.responseText);
				return;
			}

			$('div_sku_group').update(e.responseText);
			
			showdiv('div_sku_group_popup');
			document.f_a.sku_group_id.value = sku_group_id;
			document.f_a.branch_id.value = branch_id;
			document.f_a.a.value = 'save';
			document.f_a.code.focus();

   			$('supp_popup').style.height = document.body.clientHeight +'px';
		    $('supp_popup').style.width = document.body.clientWidth +'px';
			$('supp_popup').show();
		}
	});
}

function editShare(sku_group_id,branch_id){
    
    var p = $H({
		a: 'edit_share',
		sku_group_id: sku_group_id,
		branch_id: branch_id
	});

    new Ajax.Request(phpself+'?'+p.toQueryString(),
	{
		onComplete: function(e) {
			if(e.responseText.indexOf('Error:') >= 0){
				alert(e.responseText);
				return;
			}

			$('div_share').update(e.responseText);
			
			showdiv('div_share_popup');
			document.f_b.sku_group_id.value = sku_group_id;
			document.f_b.branch_id.value = branch_id;
			document.f_b.a.value = 'save_edit_share';
			document.f_b.code.focus();

   			$('supp_popup').style.height = document.body.clientHeight +'px';
		    $('supp_popup').style.width = document.body.clientWidth +'px';
			$('supp_popup').show();
		}
	});
}

function closeDiv(){
    document.f_a.reset();
	hidediv('div_sku_group_popup');
	hidediv('div_share_popup');
	hidediv('supp_popup');
}

function toggle_sku_item_group_select_all(){
	var tr_sku_item_code_row_list = $$('#tbody_sku_item_code_row_list tr.tr_sku_item_code_row');
	if(tr_sku_item_code_row_list.length<=0)	return;	// no item
	
	// get first row (that is not disabled)
	for(var i = 0; i < tr_sku_item_code_row_list.length; i++){
		var code = tr_sku_item_code_row_list[i].id.split('-')[1];
		if (!$('inp_sku_item_code_row-'+code).disabled) break;
	}
	var c = !$('inp_sku_item_code_row-'+code).checked;	// get that checkbox, and opposite its setting
	
	for(var i = 0; i < tr_sku_item_code_row_list.length; i++){
		code = tr_sku_item_code_row_list[i].id.split('-')[1];
		
		if (!$('inp_sku_item_code_row-'+code).disabled) $('inp_sku_item_code_row-'+code).checked = c;
	}
}

function delete_sku_item_group_clicked(){
	var tr_sku_item_code_row_list = $$('#tbody_sku_item_code_row_list tr.tr_sku_item_code_row');
	if(tr_sku_item_code_row_list.length<=0){
		alert('No item to delete.');
		return false;
	}
	
	var delete_list = [];
	for(var i = 0; i < tr_sku_item_code_row_list.length; i++){
		var code = tr_sku_item_code_row_list[i].id.split('-')[1];
		if($('inp_sku_item_code_row-'+code).checked){
			delete_list.push(tr_sku_item_code_row_list[i]);
		}
	}
	
	if(delete_list.length <= 0){
		alert('Please tick at least 1 item to delete.');
		return false;
	}
	
	if(!confirm('Are you sure?'))	return false;
	
	var siclist = '';
	for(var i=0; i<delete_list.length; i++){
		sic = delete_list[i].id.split('-')[1];
		if ($('cannot_delete_'+sic).value == '1') {
			siclist += (sic+'\n');
		}
		else $(delete_list[i]).remove();
	}
	if (siclist) alert(siclist+'\nThese item(s) has sales. Not allowed to delete.');
}

function add_sku_item_by_code(code_list){
	if(!code_list || !code_list.length)	return;
	
	var new_code_list = [];
	var current_code_list = [];
	var code = '';
	var duplicated_code_list = [];
	
	// filter out duplicated code
	var tr_sku_item_code_row_list = $$('#tbody_sku_item_code_row_list tr.tr_sku_item_code_row');
	for(var i = 0; i < tr_sku_item_code_row_list.length; i++){
		code = tr_sku_item_code_row_list[i].id.split('-')[1];
		
		current_code_list.push($('inp_sku_item_code_row-'+code).value);
	}

	if(current_code_list.length>0){
		for(var i=0; i<code_list.length; i++){
			code = code_list[i];
			
			if(!in_array(code, current_code_list)){
				new_code_list.push(code);	// code need to add
			}else{
				duplicated_code_list.push(code);	// code duplicated
			}
		}
	}else	new_code_list = code_list;
	
	var dup_str = '';
	var mcode = '';
	if(duplicated_code_list.length>0){
		dup_str = "\n\nItems already in the list\n===================================";
		for(var i=0; i<duplicated_code_list.length; i++){
			mcode = $('span-mcode-'+duplicated_code_list[i]).innerHTML;
			dup_str += "\n"+duplicated_code_list[i]+" / "+mcode;
		}
	}
	
	if(new_code_list.length<=0){
		alert("No new item to add."+dup_str);
		return false;
	}
	//var sku_group_id = document.f_a.sku_group_id.value;
	//var branch_id = document.f_a.branch_id.value;
	
	var params = {
		a: 'ajax_add_sku_item_group_item',
		'code_list[]': new_code_list
	};
	$('span_sku_item_group_list_loading').show();
	
	new Ajax.Request(phpself, {
		type:'post',
		parameters: params,
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
		    var err_msg = '';
			$('span_sku_item_group_list_loading').hide();
				    				
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok'] && ret['html']){ // success
					new Insertion.Bottom('tbody_sku_item_code_row_list', ret['html']);
					alert(ret['added_item_count']+' Item(s) Added.'+dup_str);
	                return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

			if(!err_msg)	err_msg = 'No Respond from server.';
		    // prompt the error
		    alert(err_msg);
		}
	});
	
}

function clear_sku_item_list(){
	// filter out duplicated code
	/*var tr_sku_item_code_row_list = $$('#tbody_sku_item_code_row_list tr.tr_sku_item_code_row');
	for(var i = 0; i < tr_sku_item_code_row_list.length; i++){
		$(tr_sku_item_code_row_list[i]).remove();
	}*/
	$('tbody_sku_item_code_row_list').update('');
}

function toggle_all_items(){
	var c = $('inp_toggle_all_items').checked;	// get checked or not
	
	var tr_sku_item_code_row_list = $$('#tbody_sku_item_code_row_list tr.tr_sku_item_code_row');	// get all the tr
	
	for(var i=0; i<tr_sku_item_code_row_list.length; i++){	// loop the tr
		var sku_item_code = tr_sku_item_code_row_list[i].id.split('-')[1];	// get sku item code
		
		var inp_sku_item_code_row = $('inp_sku_item_code_row-'+sku_item_code);	// get the delete checkbox
		if(!inp_sku_item_code_row.disabled){
			inp_sku_item_code_row.checked = c;
		}
	}
}

{/literal}
var brand_list = new Array(
{section name=i loop=$brands}
Array('{$brands[i].id}','{$brands[i].description|escape:"javascript"}'),
{/section}
0);
{literal}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

<div><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd SKU Group</a> (Alt+A)</div>

<div id=supp_popup style="display:none"></div>
<div id="sku_table" style="display:none"></div>
<div id="div_dummy" style="display:none;"></div>
<br>
<div id="div_table">{include file=masterfile_sku_group_table.tpl}</div>

{*<div class="ndiv" id="ndiv" style="position:absolute;left:150px;top:150px;display:none;z-index:2000;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div class=small style="position:absolute; right:10; text-align:right;">
					<a href="javascript:void(closeDiv())" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" /></a><br><u>C</u>lose (Alt+C)
				</div>
	
				<br />
				<form method=post name=f_a target=_irs onSubmit="return false;">
					<input type=hidden name=a value="">
					<input type=hidden name="sku_group_id" value="">
					<input type=hidden name="branch_id" value="">
					<p>
						<div id="div_sku_group">{include file="sku_items_autocomplete_multiple_sku_group.tpl"}</div>
						<div align="right">
							<input type="button" value="Save" onClick="submitForm();" id="btn_save">
							<input type="button" value="Close" onclick="f_a.reset(); hidediv('ndiv');hidediv('supp_popup');">
						</div>
					</p>
				</form>
			</div>
		</div>
	</div>
</div>*}

<div class="ndiv" id="div_sku_group_popup" style="position:absolute;left:150;top:150;display:none;z-index:2000;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div style="height:20px;background-color:#6883C6;position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_sku_group_popup_header">
					<div class=small style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(closeDiv())"><img src=ui/closewin.png border=0 align=absmiddle></a></div>
					SKU Group Information
				</div>
								
				<div id="div_sku_group_popup_content" style="margin-top:20px;min-width:400px;min-height:400px;">
					<form method=post name="f_a" target="_irs" onSubmit="return false;">
						<input type="hidden" name="a" value="">
						<input type="hidden" name="sku_group_id" value="">
						<input type="hidden" name="branch_id" value="">
						<input type="hidden" name="total_item_count" value="">
						
						<p>
							<div id="div_sku_group">{include file="sku_items_autocomplete_multiple_sku_group.tpl"}</div>
							<div align="right">
								<input class="btn btn-primary" type="button" value="Save" onClick="submitForm();" id="btn_save">
								<input class="btn btn-primary" type="button" value="Close" onclick="closeDiv();">
							</div>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="display:none"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>

<div class="ndiv" id="div_share_popup" style="position:absolute;left:150;top:150;display:none;z-index:2000;">
	<div class="blur">
		<div class="shadow">
			<div class="content">
				<div style="height:20px;background-color:#6883C6;position:absolute;left:0;top:0;width:100%;color:white;font-weight:bold;padding:2px;" id="div_share_popup_header">
					<div class=small style="position:absolute; right:10; text-align:right;top:2px;"><a href="javascript:void(closeDiv())"><img src=ui/closewin.png border=0 align=absmiddle></a></div>
					Share SKU Group
				</div>
								
				<div id="div_share_popup_content" style="margin-top:20px;">
					<form method=post name="f_b" target="_irs1" onSubmit="return false;">
						<input type=hidden name=a value="">
						<input type=hidden name="sku_group_id" value="">
						<input type=hidden name="branch_id" value="">
						
						<table class="small">
						<tr>
						<td>User pool</td>
						<td rowspan="2" align="center">
						<input type=button value=">>" onclick="mvshare(document.f_b.user_list,document.f_b.share_list)">
						<br /><br />
						<input type=button value="<<" onclick="mvshare(document.f_b.share_list,document.f_b.user_list)">
						<br /><br />
						<input type=button value="Clear" onclick="clshare(document.f_b.share_list,document.f_b.user_list)">
						</td>
						<td>Share with</td>
						</tr>
						<tr>
						<td><select name=user_list[] id=user_list multiple size=20 style="width:160px"></select></td>
						<td><select name=share_list[] id=share_list multiple size=20 style="width:160px"></select></td>
						</tr>
						</table>
						
						<p>
							<div id="div_share"></div>
							<div align="center">
								<input type="button" value="Save" onClick="submitFormShare();" id="btn_save_share">
								<input type="button" value="Close" onclick="closeDiv();">
							</div>
						</p>
					</form>
				</div>
			</div>
		</div>
	</div>
</div>

<div style="display:none"><iframe name=_irs1 width=500 height=400 frameborder=1></iframe></div>

<script>
{literal}
//init_chg(document.f_a);
new Draggable('div_sku_group_popup', { handle: 'div_sku_group_popup_header'});
new Draggable('div_share_popup', { handle: 'div_share_popup_header'});
//reset_sku_autocomplete();
{/literal}
</script>

{include file=footer.tpl}
