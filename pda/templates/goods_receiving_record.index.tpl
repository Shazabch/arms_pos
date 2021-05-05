{*
3/29/2011 5:08:14 PM Andy
- Add can search debtor.

10/3/2011 5:11:43 PM Justin
- Fixed the search vendor not working properly.

10/4/2011 5:56:32 PM Justin
- Removed all the "*" as to indicate required fields (backend will return error msg).
- Resized Vendor & Dept drop-down list to fit into PDA window.
- Added new field "PO No" to allow search by PO No and auto insert Dept and Vendor when PO No is existed.

9/6/2012 5:46 PM Justin
- Enhanced vendor searching function to be more flexible.

1/24/2013 11:38 AM Fithri
- enhance to disable save/confirm buttons while user clicked on it

2/25/2014 4:41 PM Justin
- Bug fixed the label of "searching..." never disappear while info not found.
- Enhanced the search document able to search by PO or DO.

04/11/2020 10:21AM Rayleen
- Modified page style/layout. 
	-Add h1 in titles and modified breadcrumbs (Dasboard>SubMenu)
	-Remove class small and added cellspacing and cellpadding in GRR list

11/09/2020 4:49 PM Sheila
- removed hardcoded width of textfields
*}
{include file='header.tpl'}

<script>

{literal}
function submit_form(){
	document.f_a.submit_btn.disabled = true;
	document.f_a.submit();
}

function search_vendor(event){
	var k = event.keyCode;
	var desc = $.trim(document.f_a['search_vendor_desc'].value).toLowerCase();
	if(desc=='')    return false;

	if(k==13){  // enter
		var opt = undefined;

		var opt_length = document.f_a['vendor_id'].length;
		desc_length = desc.length;

		for(var i=1; i<opt_length; i++){    // loop options, skip the first
			var vd_desc = $(document.f_a['vendor_id'].options[i]).attr('vd_desc').toLowerCase();
			if(desc_length == 1){
				if(vd_desc.indexOf(desc)==0){ // if found contain the search string
					opt = document.f_a['vendor_id'].options[i];   // grap this and break
					break;
				}
			}else{
				if(vd_desc.indexOf(desc)>=0){ // if found contain the search string
					opt = document.f_a['vendor_id'].options[i];   // grap this and break
					break;
				}
			}
		}
		
		if(opt == undefined){
			for(var i=1; i<opt_length; i++){    // loop options, skip the first
				var vd_desc = $(document.f_a['vendor_id'].options[i]).text().toLowerCase();
				var vd_desc_split = vd_desc.split(" - ", 2);
				if(desc_length == 1){
					if(vd_desc_split[0].indexOf(desc)==0){ // if found contain the search string
						opt = document.f_a['vendor_id'].options[i];   // grap this and break
						break;
					}
				}else{
					if(vd_desc_split[0].indexOf(desc)>=0){ // if found contain the search string
						opt = document.f_a['vendor_id'].options[i];   // grap this and break
						break;
					}
				}
			}
		}
		
		if(opt){ // got row found
            $(opt).attr('selected', true);
		}else{ // no data found
			alert(desc+' not foundnot found in Vendor list');
		}
	}
}

function search_document(event){
	var k = event.keyCode;
	var doc_no = $.trim(document.f_a['doc_no'].value).toUpperCase();
	var doc_type = $('input[name=doc_type]:checked').val();

	if(doc_no=='' || k!=13) return;

	$('#loading_area').text("Searching...");
	$.get("goods_receiving_record.php", { a: "search_document", doc_no: doc_no, doc_type: doc_type },
		function(data){
			if(data.err_msg!=undefined) alert(data.err_msg);
			else{
				if(data.department_id!=undefined){
					var opt = undefined;

					var opt_length = document.f_a['department_id'].length;
					//var i = $(document.f_a['vendor_id']).children();  // get all options
					for(var i=1; i<opt_length; i++){    // loop options, skip the first
						if(document.f_a['department_id'].options[i].value==data.department_id){ // if found contain the search string
							opt = document.f_a['department_id'].options[i];   // grap this and break
							break;
						}
					}
					
					if(opt){ // got row found
						$(opt).attr('selected', true);
					}
				}else alert("No department was found for this "+doc_type+".");
				
				if(doc_type=="PO"){
					if(data.vendor_id!=undefined){
						var opt = undefined;

						var opt_length = document.f_a['vendor_id'].length;
						//var i = $(document.f_a['vendor_id']).children();  // get all options
						for(var i=1; i<opt_length; i++){    // loop options, skip the first
							if(document.f_a['vendor_id'].options[i].value==data.vendor_id){ // if found contain the search string
								opt = document.f_a['vendor_id'].options[i];   // grap this and break
								break;
							}
						}
						
						if(opt){ // got row found
							$(opt).attr('selected', true);
						}
					}else alert("No vendor was found for this "+doc_type+".");
				}else{
					$(document.f_a['vendor_id'].options[0]).attr('selected', true);
				}
			}
			$('#loading_area').text("");
		}, "json");
}
{/literal}
</script>
<h1>
Setting - {if $form.id}(GRR#{$form.id}){else}New GRR{/if}
</h1>
<span class="breadcrumbs"><a href="home.php">Dashboard</a> > <a href="home.php?a=menu&id={$module_name|lower}">{$module_name}</a> {if $form.find_grr} > <a href="goods_receiving_record.php?a=open&find_grr={$form.find_grr}">Back to Search</a> {/if}</span>
<div style="margin-bottom:10px;"></div>

{if $form.id&&$form.branch_id}{include file='goods_receiving_record.top_include.tpl'}<br><br>{/if}
{if $err}
	<ul style="color:red;">
	    {foreach from=$err item=e}
	        <li>{$e}</li>
	    {/foreach}
	</ul>
{/if}

{if $form.id}
    {assign var=branch_id value=$form.branch_id}
{else}
    {assign var=branch_id value=$sessioninfo.branch_id}
{/if}

<div id="test_div"></div>
<div class="stdframe" style="background:#fff;">
<form name="f_a" method="post" onSubmit="return false;">
<input type="hidden" name="a" value="save_setting" />
<input type="hidden" name="id" value="{$form.id}" />
<input type="hidden" name="branch_id" value="{$branch_id}" />
<table width="100%" border="0" cellspacing="0" cellpadding="4">
	<tr>
	    <th align="left">Search<br />PO/DO</th>
	    <td>
			<input type="text" id="doc_no" name="doc_no" value="" onKeyUp="search_document(event);" size="10" />
			<span id="loading_area"></span><br />
			<input type="radio" name="doc_type" value="PO" checked />PO&nbsp;
			<input type="radio" name="doc_type" value="DO" />DO
		</td>
	</tr>
	<tr>
	    <th align="left">Received Date</th>
	    <td>
			<input type="text" id="rcv_date" name="rcv_date" value="{$form.rcv_date|default:$smarty.now|date_format:"%Y-%m-%d"}" size="9" /> <span class="small">(YYYY-MM-DD)</span>
		</td>
	</tr>
    <tr>
	    <th align="left">Vendor</th>
	    <td>
	        <select name="vendor_id">
	            <option value="">-- Please Select --</option>
	            {foreach from=$vendor key=did item=r}
	                <option value="{$did}" {if $form.vendor_id eq $did}selected {/if} vd_desc="{$r.description|escape:'html'}">{$r.code} - {$r.description}</option>
	            {/foreach}
	        </select>
	    </td>
	</tr>
	<tr>
	    <th align="left">Search Vendor</th>
	    <td><input type="text" name="search_vendor_desc" id="search_vendor_desc" onKeyUp="search_vendor(event);" />
	</tr>
	<tr>
		<th align="left">Department</th>
		<td colspan=6>
			<select name="department_id">
			<option value=0>-- Select Department --</option>
			{section name=i loop=$dept}
			<option value={$dept[i].id} {if $form.department_id == $dept[i].id}selected{/if}>{$dept[i].description}</option>
			{/section}
			</select>
		</td>
	</tr>
	<tr>
		<th align="left"><b>Lorry No.</th>
		<td>
			<input class="txt-width" name="transport" onchange="ucz(this)" value="{$form.transport}" size=10 maxlength=10>
		</td>
	</tr>
	<tr>
		<th align="left"><b>Received By</th>
		<td>
			<select name="rcv_by">
			{section name=i loop=$rcv}
				<option value="{$rcv[i].id}" {if ((!$form.rcv_by && $rcv[i].id eq $sessioninfo.id) || ($form.rcv_by && $rcv[i].id eq $form.rcv_by))}selected{/if}>{$rcv[i].u}</option>
			{/section}
			</select>
		</td>
	</tr>
</table>
<p align="center">
	<input type="button" name="submit_btn" value="Save" onclick="submit_form();" />
	{if $smarty.request.id && $smarty.request.t}
		<img src="../ui/icons/accept.png" align="absmiddle" title="Required Field"> GRR#{$smarty.request.id} 
		{if $smarty.request.t eq 'insert'}
			inserted
		{else}
			updated
		{/if}
	{/if}
</p>
</form>
</div>
{include file='footer.tpl'}
