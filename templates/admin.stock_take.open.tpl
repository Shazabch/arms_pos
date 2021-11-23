{*
7/23/2010 4:59:47 PM Andy
- Add single server mode and hq can create stock take for branch.
- Fix stock take item list if open multiple tab will cause bugs.

9/7/2010 6:10:32 PM ALex
- add sku scan at main page and stock count sheet no.

6/27/2012 5:11 PM Andy
- Fix "multiple add" got bugs at branch.
*}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

submit_form = function(action){
	if(action=='close'){
        if(!confirm('Are you sure?'))   return false;
        
        var se_time = document.f_a['ses_time'].value;
        new Ajax.Request(phpself,{
    			parameters: {a:'reset_session',v:se_time}
    		});
		hidediv('div_stock_take_details');
        default_curtain_clicked();
        return false;
	}else if(action=='save'){
		if(!document.f_a['dat'].value.trim())
   		{
			alert('Please enter Date.');
			return false;
		}else if(!document.f_a['loc'].value.trim())
		{
	    	alert('Please enter Location.');
			return false;
	    }
	    else if(!document.f_a['shelf'].value.trim())
		{
	    	alert('Please enter Shelf.');
			return false;
	    }
	    else if(!document.f_a['sku'].value.trim())
			{
			  if(!$('handheld').checked)
			  {
			      alert('Please enter Code.');
			      return false;
	      }
	    }else if(!document.f_a['sku_scan'].value.trim())
	    {
	        if($('handheld').checked)
	  		  {
	  		      alert('Please enter Code.');
	  		      return false;
	        }
	    }
	    else if(!document.f_a['qty'].value.trim())
	    {
	      alert('Please enter Quantity.');
				return false;
	    }

			//if(!confirm('Are you sure?'))   return false;

	      $$('.btn_save').each(function(ele){
				ele.disable().value = 'Saving...';
		  });

	      var date = document.f_a['dat'].value;
	      var loc = document.f_a['loc'].value;
	      var shelf = document.f_a['shelf'].value;

	      new Ajax.Request(phpself,{
				parameters: document.f_a.serialize(),
				onComplete: function(e){
					var msg = e.responseText.trim();
					if(msg!='Invalid Code Entered'){
						document.f_a['qty'].value='0';
						$('sku_scan').value='';

						document.f_a['ses_time'].value = msg;
					}else{
						alert(msg);
					}
				  	load_scan_item();
					$$('.btn_save').each(function(ele){
						ele.enable().value = 'Add';
					});
					document.f_a['sku_scan'].focus();

					if(date==document.tbl['date'].value&&loc==document.tbl['location'].value&&shelf==document.tbl['shelf'].value){
	                    reload_table();
					}
				}
		})
	}
}

{/literal}
</script>
<br />
<form method=post name=f_a onSubmit="return false;">
<input type=hidden name=a value="save">
<input name="sku_item_id" size=3 type=hidden>
<input name="sku_item_code" size=13 type=hidden>
<input type="hidden" name="id" value="{$form.sku_item_id}" />
<input type="hidden" name="ses_time"/>

<table width="100%">
{if $BRANCH_CODE eq 'HQ' and $config.single_server_mode}
<tr>
    <td><b class="form-label">Branch</b></td><td>
    <select class="form-control" name=bran>
    {foreach from=$branches item=r}
    <option value="{$r.id}" {if $smarty.request.bran eq $r.id or $bid eq $r.id}selected {/if}>{$r.code}</option>
    {/foreach}
    </select></td>
</tr>
{else}
	<input type="hidden" name="bran" value="{$sessioninfo.branch_id}" />
{/if}
<tr>
	<td><b class="form-label">Date<SPAN class="text-danger"> *</SPAN></b></td>
	<td>
		<div class="form-inline">
			<input class="form-control" name="dat" id="added1" size=22 value="{$dat}" readonly> 
	&nbsp;<img align=absmiddle src="ui/calendar.gif" id="t_addeded" style="cursor: pointer;" title="Select Date">
		</div>
	</td>
</tr>
<tr>
	<td nowrap><b class="form-label">Location<span class="text-danger"> *</span></b></td>
	<td><input class="form-control" type="text" name="loc" onchange="this.value=this.value.toUpperCase();" value="{$loc}" /></td>
</tr>
<tr>
	<td><b class="form-label">Shelf<span class="text-danger"> *</span></b></td>
	<td>
		<input class="form-control" type="text" name="shelf" onchange="this.value=this.value.toUpperCase();" value="{$shelf}" />
	    
	</td>
</tr>
   {include file="admin.stock_take.autocomplete.tpl"}
<tbody id="arms">
</tbody>
<tr>
	<td valign="top">
		<b class="form-label">Quantity</b></td>
	<td>
		<div class="form-inline">
			<input class="form-control" type="text" size=10 name="qty" value="{$form.qty}" onKeyPress="checkkey(event,'1')">&nbsp;&nbsp;
	&nbsp;<input cla type=button value="Add" class="btn_save btn btn-primary" onclick="submit_form('save');" />
	&nbsp;<input type=button value="Multiple Add" class="btn btn-danger" id="btn_multiple_save" onclick="show_stock_take_direct_add_multiple();" />
		</div>
	<div id=div_loading></div></td>
</tr>
<!--<tr>
	<td colspan=4 align=center><br>
		<input type=button value="Save" id="btn_save" onclick="submit_form('save');" />
		<input type=button value="Close" onclick="submit_form('close');" />
	</td>
</tr>-->
</table>

<div align=center>

	<div class="mt-2" id="details_display" style="overflow: auto;height: 200px;width: 600px;">
	{include file="admin.stock_take.scan_item.tpl"}
	</div>
	<input type=button class="btn btn-danger" value="Close" onclick="submit_form('close');" />
</div>
</form>

<script>
init_calendar();
</script>

<script>
document.f_a['qty'].value='0';
reset_sku_autocomplete();
</script>
