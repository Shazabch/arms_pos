{*
12/30/2010 4:33:31 PM Alex
- remove delete consignment function
- add able to list out multiple branch if HQ

3/15/2011 5:35:28 PM Alex
- add checking active status

12/19/2011 11:51:43 AM Justin
- Modified the CSS to use new id.
- Added sort by header feature after reload table.
*}

{include file=header.tpl}
{literal}
<style>
#trnsprt_tbl tr:nth-child(even){
	background-color:#eeeeee;
}

</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
{if $smarty.request.branch_id}
    {assign var=b_id value=$smarty.request.branch_id}
{else}
	{assign var=b_id value=$bid}
{/if}
var branch_id = '{$b_id}';
{literal}

function change_consignment_list(page){
	var pg="";
	
	if (page) pg+="&s="+page;


	new Ajax.Updater('consignment_list', 'masterfile_consignment_bearing.php?'+Form.serialize(document.f_list), {
		parameters: 'a=ajax_load_consignment_list'+pg,
		onComplete: function(m){
			ts_makeSortable($('trnsprt_tbl'));
		}
		});
}

function load_department(bid){

	new Ajax.Updater('department_id',phpself,{
		parameters:{
			a: 'ajax_load_department',
			branch_id: bid
		},
		onComplete: function(msg){
			if ($('department_id').value)
				load_r_type_vendor_brand($('department_id').value);
			else{
		        $('r_type').hide();
		        $('vendor').hide();
		        $('brand').hide();
			}
	        disenable_select();
		},
		evalScripts: true
	});
}

function load_r_type_vendor_brand(dept_id){

	if (dept_id == "All"){
		$('r_type').style.display="none";
		$('vendor').style.display="none";
		$('brand').style.display="none";
	}else{
        $('loading').update(_loading_);

     	// insert new row
		new Ajax.Request(phpself,{
			method:'post',
			parameters: {
				a: 'ajax_load_r_type_vendor_brand',
				dept_id: dept_id
			},
		    evalScripts: true,
			onFailure: function(m) {
				alert(m.responseText);
			},
			onSuccess: function (m) {

			},
			onComplete: function(m){

				var option = eval("("+m.responseText+")");
		        $('loading').update("");
	            $('r_type_id').update(option.r_type);
                $('brand_id').update(option.brand);
	            $('vendor_id').update(option.vendor);

				$('r_type').style.display="";
                toggle_brand_vendor($('r_type_id').value);
			}
		});
	}
}

function toggle_brand_vendor(r_type){

	if (r_type == "vendor"){
		$('vendor').style.display="";
	    $('brand').style.display="none";
	}
	else if (r_type == "brand"){
        $('vendor').style.display="none";
		$('brand').style.display="";
	}
	else{
        $('vendor').style.display="none";
        $('brand').style.display="none";
	}
	disenable_select();
}


function disenable_select(){
	$$(".disenable_select").each(function(ele,obj){
	    if ($(ele).style.display=="none")
			$(ele).getElementsByTagName("select")[0].disable();
		else
		    $(ele).getElementsByTagName("select")[0].enable();
	});
}

/*
function del_consignment(id){
	document.f_list.a.value="delete_consignment_items";
	document.f_list.id.value=id;
	document.f_list.submit();

}
*/

function act_consignment(group_id, act_deact){


	var tmp_img=$('activate_id_'+group_id).innerHTML;
	$('activate_id_'+group_id).innerHTML=_loading_;

	new Ajax.Request(phpself,{
		method:'post',
		parameters:{
			a: 'ajax_activate_deactivate',
			group_id: group_id,
			act_deact: act_deact
		},
		onComplete: function(msg){
		    if (msg.responseText != 'OK'){
				alert(msg.responseText);
				$('activate_id_'+group_id).innerHTML=tmp_img;
				return;
			}
			if (act_deact == '1'){
				// if activate, put deactivate img
				var img="<img src=/ui/deact.png onclick=\"if (confirm('Are you sure?')) act_consignment("+group_id+",'0');\" title='Deactivate' align=absmiddle border=0>";
			}else{
				// if deactivate, put activate img
				var img = "<img src=/ui/act.png onclick=\"if (confirm('Are you sure?')) act_consignment("+group_id+",'1');\" title='Activate' align=absmiddle border=0>";
			}
            $('activate_id_'+group_id).innerHTML=img;

		},
		evalScripts: true
	});
}


{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $errm.top}
	<div id=err><div class=errmsg><ul>
	{foreach from=$errm.top item=e}
	<li> {$e}
	{/foreach}
	</ul></div></div>
{/if}
{if $msg}
	{foreach from=$msg item=e}
	- <font color="green">{$e}</font>
	{/foreach}
{/if}
{if $sessioninfo.privilege.MST_CONTABLE_EDIT}
<p>
<ul>
<li> <a href="?a=new_consignment">Create New Table</a>
</ul>
</p>
{/if}

<form action="{$smarty.server.PHP_SELF}" name="f_list" method=post >
<input type="hidden" name=id>

<div style="border:1px solid #aaa;padding:5px">
	<b>Status</b>&nbsp;&nbsp;
	<select name="status" id="status_id">
    <option value="" {if $smarty.request.status eq ''}selected {/if} >All</option>
	<option value="1" {if $smarty.request.status eq '1'}selected {/if}>Active</option>
	<option value="0" {if $smarty.request.status eq '0'}selected {/if}>Inactive</option>
	</select>&nbsp;&nbsp;&nbsp;&nbsp;

	{if $BRANCH_CODE eq 'HQ'}
		<b>Branch</b>&nbsp;&nbsp;
		<select name="branch_id" id="branch_id" onchange="load_department(this.value)">
	    <option value="" >All</option>
		{section name=i loop=$branches}
		<option value="{$branches[i].id}" {if $b_id eq $branches[i].id}selected {/if}>{$branches[i].code}</option>
		{/section}
		</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
    <span class="disenable_select" id="department">
		<b>Department</b>
	    <select name="department_id" id="department_id" onchange="load_r_type_vendor_brand(this.value);">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>

    <span class="disenable_select" id="r_type" {if !$smarty.request.department_id or $smarty.request.department_id eq "All"} style="display:none;" {/if} >
	    <b>Type</b>
	    <select  name="r_type" id="r_type_id" onchange="toggle_brand_vendor(this.value);">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>

    <span class="disenable_select" id="vendor" {if !$smarty.request.r_type or $smarty.request.r_type eq "All" or $smarty.request.r_type eq "brand"}style="display:none;" {/if}>
	    <b>Vendor</b>
	    <select name="vendor_id" id="vendor_id">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>

    <span class="disenable_select" id="brand" {if !$smarty.request.r_type or $smarty.request.r_type eq "All" or $smarty.request.r_type eq "vendor"}style="display:none;" {/if}>
	    <b>Brand</b>
	    <select name="brand_id" id="brand_id">
	    </select>
	    &nbsp;&nbsp;&nbsp;&nbsp;
	</span>
    <span id="loading"></span>
    
    <input type="button" onclick="change_consignment_list();" value="Search">
</div>
</form>
<br>
{include file="masterfile_consignment_bearing.list.tpl"}



{include file=footer.tpl}
<script>
load_department(branch_id);
disenable_select();
</script>

<div style="display:none;"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>
