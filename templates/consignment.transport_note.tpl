{*
9/3/2010 10:43:18 AM Andy
- Fix javascript error.
- make transport note print to blank page to have preview for user first.

1/20/2011 4:42:19 PM Andy
- Add feature to deliver to "open".

7/8/2011 4:37:52 PM Andy
- Add automatically select transporter at transport note when change branch selection.
*}

{include file=header.tpl}
{if !$no_header_footer}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function check_before_submit(){
    var deliver_type = getRadioValue(document.f_a['deliver_type']);
    
    if(deliver_type=='branch'){ // deliver to branch
        if(document.f_a['branch_id'].value==''){
			alert('Please select branch.');
			document.f_a['branch_id'].focus();
			return false;
		}
	}else{  // deliver to open
		if(document.f_a['open[name]'].value==''){
			alert('Please enter open name.');
			document.f_a['open[name]'].focus();
			return false;
		}
		if(document.f_a['open[address]'].value==''){
			alert('Please enter open address.');
			document.f_a['open[address]'].focus();
			return false;
		}
	}
	
	if(document.f_a['do_no'].value==''){
		alert('Please key in DO NO.');
		document.f_a['do_no'].focus();
		return false;
	}
	if(document.f_a['transporter_id'].value==''){
		alert('Please select transporter.');
		document.f_a['transporter_id'].focus();
		return false;
	}
	if(int($('span_total_ctn').innerHTML)<=0){
        alert('Please enter carton size.');
		document.f_a['carton_s'].focus();
		return false;
	}
}

function carton_changed(ele){
	miz(ele);
	var total_carton = 0;
	$$('input.inp_carton').each(function(inp){
        total_carton += int(inp.value);
	});
	$('span_total_ctn').update(total_carton);
}

function show_search_transporter(){
	$$('#tbl_transporter_list tr.db_row').each(function(ele){
			$(ele).show();
		});

	curtain(true);
	center_div($('div_search_transporter').show());
	document.f_search_transporter['code'].focus();
}

function filter_transporter(){
    var str = document.f_search_transporter['code'].value.trim().toLowerCase();
	if(str==''){
        $$('#tbl_transporter_list tr.db_row').each(function(ele){
			$(ele).show();
		});
		return false;
	}

	$$('#tbl_transporter_list tr.db_row').each(function(ele){
	    var code = $(ele).getElementsBySelector('.db_code')[0].innerHTML.toLowerCase();
	    if(code.indexOf(str)>=0)    $(ele).show();
	    else    $(ele).hide();
	});
}

function choose_this_transporter(ele){
	var transporter_id = $(ele).getElementsBySelector('.db_id')[0].innerHTML;
	document.f_a['transporter_id'].value = transporter_id;
    default_curtain_clicked();
}

function deliver_to_changed(){
	var deliver_type = getRadioValue(document.f_a['deliver_type']);
	
	if(deliver_type=='open'){
		$('div_deliver_branch').hide();
		$('div_deliver_open').show();
	}else{
        $('div_deliver_open').hide();
        $('div_deliver_branch').show();
	}
}

function branch_changed(){
	var selectedIndex = document.f_a['branch_id'].selectedIndex;
	var option = document.f_a['branch_id'].options[selectedIndex];
	
	var transporter_id = int($(option).readAttribute('transporter_id'));
	if(transporter_id){
		document.f_a['transporter_id'].value = transporter_id;
	}
}
{/literal}
</script>
{/if}

<h1>{$PAGE_TITLE}</h1>

<div id="div_search_transporter" style="display:none;position:absolute;z-index:10000;width:500px;height:450px;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding:0 !important;" class="curtain_popup">
	<div id="div_search_transporter_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;">
		<span style="float:left;">Available Transporter Details</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_search_transporter_content" style="padding:2px;">
	    <form name="f_search_transporter" onSubmit="filter_transporter();return false;">
	        <b>Filter by Code:</b>
	        <input type="text" size="30" name="code" />
	        <input type="submit" value="Refresh" />
	    </form>
		<div style="height:350px;border:1px solid grey;overflow-x:hidden;overflow-y:auto;">
		<table width="100%" id="tbl_transporter_list">
			<tr style="background:#ffc;" id="tr_header_transporter_list">
			    <th width="30">&nbsp;</th>
			    <th width="80">Code</th>
			    <th>Company Name</th>
			</tr>
			<tbody style="background:#fff;">
			{foreach from=$transporters item=r name=f}
			    <tr onmouseover="this.bgColor='#ffffcc';" onmouseout="this.bgColor='';" class="clickable db_row" onClick="choose_this_transporter(this);">
			        <td>{$smarty.foreach.f.iteration}.
			            <span class="db_id" style="display:none;">{$r.id}</span>
					</td>
			        <td class="db_code">{$r.code}</td>
			        <td>{$r.company_name}
			            <span class="db_name" style="display:none;">{$r.company_name}</span>
					</td>
			    </tr>
			{/foreach}
			</tbody>
		</table>
		</div>
		<p align="center">
			<input type="button" value="Close" name="close" onClick="default_curtain_clicked();" />
		</p>

	</div>
</div>

<iframe name="ifprint" style="width:1px;height:1px;visibility:hidden;"></iframe>

<form name="f_a" onSubmit="return check_before_submit();" target="_blank">
	<input type="hidden" name="a" value="print_transport_note" />
	<table>
	    <tr>
	        <td valign="top"><b>Deliver To</b></td>
	        <td>
	            <input type="radio" name="deliver_type" value="branch" checked onChange="deliver_to_changed();" /> Branch&nbsp;&nbsp;&nbsp;&nbsp;
	            <input type="radio" name="deliver_type" value="open" onChange="deliver_to_changed();" /> Open&nbsp;&nbsp;&nbsp;&nbsp;
	            <br />
	            <div id="div_deliver_branch">
		            <select name="branch_id" onChange="branch_changed();">
						<option value="">-- Please Select --</option>
						{foreach from=$branches key=bid item=b}
						    {if !$branches_group.have_group.$bid}
						    	<option value="{$bid}" {if $smarty.request.branch_id eq $bid}selected {/if} transporter_id="{$b.transporter_id}">{$b.code} - {$b.description}</option>
							{/if}
						{/foreach}
						{if $branch_group.header}
					    	{foreach from=$branch_group.header key=bgid item=bg}
					    	    <optgroup label="{$bg.code}">
					    	    {foreach from=$branch_group.items.$bgid item=r}
					    	        <option value="{$r.branch_id}" {if $smarty.request.branch_id eq $r.branch_id}selected {/if} transporter_id="{$b.transporter_id}">{$r.code} - {$r.description}</option>
					    	    {/foreach}
					    	    </optgroup>
					    	{/foreach}
						{/if}
					</select>
				</div>
				<div id="div_deliver_open" style="display:none;">
				    <table>
				        <tr>
				            <td>Name</td>
				            <td><input type="text" name="open[name]" style="width:400px" /></td>
				        </tr>
				        <tr>
				            <td valign="top">Address</td>
				            <td>
				                <textarea name="open[address]" style="width:400px" rows="5" ></textarea>
				            </td>
				        </tr>
				    </table>
				</div>
	        </td>
	    </tr>
	    <tr>
	        <td><b>DO No.</b></td>
	        <td><input type="text" name="do_no" size="30" /></td>
	    </tr>
	    <tr>
	        <td><b>Transporter</b></td>
	        <td>
	            <select name="transporter_id">
					<option value="">-- Please Select --</option>
					{foreach from=$transporters item=r}
					    <option value="{$r.id}">{$r.code} - {$r.company_name}</option>
					{/foreach}
				</select>
				<img src="/ui/icons/magnifier.png" align="absmiddle" title="Search by Transorter Code" class="clickable" onClick="show_search_transporter();" />
			</td>
	    </tr>
	</table>

<br />
<fieldset style="width:200px;">
	<legend><b>Carton Size:</b></legend>
	<table width="100%">
	    <tr>
			<td><b>S</b></td>
			<td align="center">: <input type="text" name="carton_s" class="inp_carton" size="10" onChange="carton_changed(this)" /></td>
		</tr>
		<tr>
			<td><b>M</b></td>
			<td align="center">: <input type="text" name="carton_m" class="inp_carton" size="10" onChange="carton_changed(this);" /></td>
		</tr>
		<tr>
			<td><b>L</b></td>
			<td align="center">: <input type="text" name="carton_l" class="inp_carton" size="10" onChange="carton_changed(this);" /></td>
		</tr>
		<tr>
			<td><b>XL</b></td>
			<td align="center">: <input type="text" name="carton_xl" class="inp_carton" size="10" onChange="carton_changed(this);" /></td>
		</tr>
		<tr>
		    <td colspan="2" style="border-top:1px dashed black;line-height:3px;">&nbsp;</td>
		</tr>
		<tr>
			<td><b>Total</b></td>
			<td align="center"><span id="span_total_ctn">0</span> Ctn(s)</td>
		</tr>
	</table>
	<div class="r"><button><img src="ui/icons/printer.png" align="absmiddle" /> Print</button>
	</div>
</fieldset>
</form>
{include file=footer.tpl}

<script>
{literal}
new Draggable('div_search_transporter',{ handle: 'div_search_transporter_header'});
{/literal}
</script>
