{*
REVISION HISTORY
=================
3/5/2008 11:52:06 AM gary
- change old po link to new po link.

04/20/2020 06:00 PM Sheila
- Modified layout to compatible with new UI.

*}
{include file=header.tpl}
{literal}
<script>
var reject_obj=null;
var no_delete=null;
var no_branch=null;

function show_items()
{
	$('request').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';
	new Ajax.Updater('request', 'po_request.approval.php',
		{
			parameters: Form.serialize(document.f_a)+'&a=show_items',
			evalScripts: true	
		}
	);
}

function cancel_popup(){
    curtain(false);
	Element.hide('reject_popup');
}

function do_delete_one(b,n){
	no_delete = n;
	no_branch=b;
	document.f_p.a.value='delete_one';
	center_div('reject_popup');
	curtain(true);
	Element.show('reject_popup');
}

function close_popup(val){
	Element.hide('reject_popup');
	curtain(false);
	if($('reject_comment').value!=''){
	    if(val=='delete'){
			document.f_p.reject_comment.value=$('reject_comment').value;
			document.f_p.a.value='delete';
			document.f_p.branch.value=no_branch;
			document.f_p.submit();
		}
		if(val=='delete_one'){
		    document.f_p.reject_comment.value=$('reject_comment').value;
			document.f_p.delete_id.value=no_delete;
			document.f_p.branch.value=no_branch;
			document.f_p.a.value='delete_one';
			document.f_p.submit();
		}
	}

	else{
		alert('Please provide reject comment.');
	}
}

function do_delete(){
	document.f_p.a.value='delete';
	curtain(true);
	center_div('reject_popup');
	Element.show('reject_popup');
}

function do_approve(){
	if(!confirm('Are you sure want to approve all these items?')){
        return;
	}
	curtain(false);
	document.f_p.a.value='approve';
	document.f_p.submit();
}

function CheckAll(obj)
{
	if(obj.checked==0)obj.checked=1;
	else obj.checked=0;
	count = document.f_p.elements.length;
    for (i=0; i < count; i++)
	{
    if(document.f_p.elements[i].checked == 1)
    	{document.f_p.elements[i].checked = 0; }
    else {document.f_p.elements[i].checked = 1;}
	}
}

</script>
{/literal}
{if $msg}<p align=center style="color:#00f">{$msg}</p>{/if}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>


<div id=reject_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;padding:5px;width:250;height:120">
<p align=center>
Are You Sure Want To Delete? <br>
Please Enter Your Reject Comment :
<p align=center><input id=reject_comment size=30>
<br>
<p align=center>
<input type=button value="OK" onclick="close_popup(document.f_p.a.value);">&nbsp;&nbsp;&nbsp;
<input type=button value="Cancel" onclick="cancel_popup();">
</div>


{if $smarty.request.t eq 'complete'}
<p>
<b>The following PO was generated:</b> {$smarty.request.po}<br>
<img src=/ui/act.png align=absmiddle> <!--a href="/purchase_order.php"--><a href="/po.php">goto Purchase Order screen</a>
</p>
{/if}

<div class="card mx-3">
	<div class="card-body">
		<div class=stdframe >
			<!-- sku search -->
			<form name=f_a>
			<div class="row">
			<div class="col-md-4">
				<b class="form-label">Department</b>
				<select class="form-control" name=department_id onchange="show_items()">
				{section name=i loop=$dept}
				<option value="{$dept[i].id}" {if $smarty.request.department_id eq $dept[i].id}selected{/if}>{$dept[i].description}</option>
				{/section}
				</select>
			</div>
			
			<div class="col-md-4">
				{if $BRANCH_CODE eq 'HQ'}
			<b class="form-label">Branch</b> 
			<select class="form-control" name=branch_id onchange="show_items()">
			<option value=0>-- All --</option>
			{section name=i loop=$branches}
			<option value="{$branches[i].id}" {if $smarty.request.branch_id eq $branches[i].id}selected{/if}>{$branches[i].code}</option>
			{/section}
			</select>
			{/if}
			</div>
			<div class="col-md-4">
				<input class="btn btn-primary mt-4" type=button value="Show PO Request" onclick="show_items()"></td>
			
			</div>	
		
			</div>
			</form>
			</div>
			
	</div>
</div>

<div class="alert alert-primary mx-3 rounded">
	<div id=request></div>
</div>
{include file=footer.tpl}

<script>
show_items();
</script>
