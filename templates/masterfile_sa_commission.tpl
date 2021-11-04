{*
2/17/2017 4:10 PM Justin
- Bug fixed on commission activate/deactivate feature.

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character
*}

{include file=header.tpl}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var tab = '{$t}';
{literal}
function commission_list_sel(n,s){
	var i;
	for(i=0;i<=2;i++){
		if ($('lst'+i)!=undefined){
			if (i==n){
			    $('lst'+i).addClassName('selected');			
			}
			else{
				$('lst'+i).removeClassName('selected');
			}			    
		}
	}
	$('sac_list').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	var pg = '';
	if (s!=undefined) pg = '&s='+s;
	if (n==0) pg +='&search='+ $('search').value;
	
	new Ajax.Updater('sac_list', phpself, {
		parameters: encodeURI('a=ajax_load_commission_list&ajax=1&t='+n+pg),
		evalScripts: true
	});
	tab = n;
}

function curtain_clicked(){
	$('print_dialog').style.display = 'none';
	curtain(false);
}

function do_print(id,bid){
	document.f_print.id.value=id;
	document.f_print.branch_id.value=bid;
	curtain(true);
	show_print_dialog();
}

function show_print_dialog(){
	document.f_print.a.value='print';
	center_div('print_dialog');
	$('print_dialog').style.display = '';
	$('print_dialog').style.zIndex = 10000;
}

function print_ok(){
	$('print_dialog').style.display = 'none';
	document.f_print.a.value='print';
	//document.f_print.target = 'ifprint';
	//document.f_print.method='get';
	document.f_print.target = '_blank';
	document.f_print.submit();	
	curtain(false);
}

function ajax_toggle_commission_status(id, bid){
	var status = 0;
	var toggle_msg = "";

	if($('img_sac_status_'+id+'_'+bid).src.indexOf('/ui/deact.png')>=0){
		toggle_msg = "deactivate";
	}else{
		status = 1;
		toggle_msg = "activate";
	}

	if(!confirm("Are you sure want to "+toggle_msg+" this commission?")) return;

	var prms = "a=ajax_toggle_commission_status&id="+id+"&branch_id="+bid+"&status="+status;
	new Ajax.Request(phpself, {
		method:'post',
		parameters: prms,
		evalScripts: true,
		onFailure: function(m) {
			alert(m.responseText);
		},
		onSuccess: function(m) {
			if(m.responseText == "OK"){
				alert("Commission "+toggle_msg+"d successfully.");
			}else{
				alert(m.responseText);
			}
		},
		onComplete: function(m) {
			if(m.responseText == "OK"){
				if(tab != 0){
					$('commission_'+id+'_'+bid).remove();
				}else{
					if(status == 1){
						$('img_sac_status_'+id+'_'+bid).src = "ui/deact.png";
						$('span_sac_inactive_'+id+'_'+bid).hide();
					}else{
						$('img_sac_status_'+id+'_'+bid).src = "ui/act.png";
						$('span_sac_inactive_'+id+'_'+bid).show();
					}
				}
			}
		}
	});
}
{/literal}
</script>

<iframe width="1" height="1" style="visibility:hidden" name="ifprint"></iframe>

<div id="show_last">
{if $status eq 'save'}
<img src="/ui/approved.png" align="absmiddle"> Commission saved as ID#{$id|string_format:"%05d"}<br>
{elseif $status eq 'delete'}
<img src="/ui/cancel.png" align="absmiddle"> Commission ID#{$smarty.request.id|string_format:"%05d"} was deleted<br>
{elseif $status eq 'reset'}
<img src="/ui/notify_sku_reject.png" align="absmiddle"> Commission ID#{$smarty.request.save_id|string_format:"%05d"} was reset.
{/if}
</div>

<div class="card mx-3">
	<div class="card-body">
		<img src="ui/new.png" align="absmiddle"> 
<a href="{$smarty.server.PHP_SELF}?a=open_commission">Create New Commission</a>
	</div>
</div>

<br /><br />
<form onsubmit="commission_list_sel(0,0); return false;">
	<div >
		<div class="tab row mx-3" style="white-space:nowrap;">
			<div class="col">
				<a href="javascript:commission_list_sel(1)" id="lst1" class="btn btn-outline-primary btn-rounded">Saved Commission</a>
			&nbsp;&nbsp;<a href="javascript:commission_list_sel(2)" id="lst2" class="btn btn-outline-primary btn-rounded">Inactive Commission</a>
			</div>
			<div class="col">
				<div class="form-inline">
					<a name="find" id="lst0">Find Commission 
						<input id="search" class="form-control" name="find" value="{$smarty.request.search}"> 
						<input type="submit" class="btn btn-primary" value="Go"></a>
				</div>
			</div>
		</div>
	</div>
</form>
<div id="sac_list" >
		<div class="card mx-3">
			<div class="card-body">
				{include file="masterfile_sa_commission.list.tpl"}
			</div>
		</div>
</div>
{include file="footer.tpl"}
