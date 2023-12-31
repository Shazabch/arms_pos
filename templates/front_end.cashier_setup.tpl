{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';
var search_str = '';

{literal}
function toggle_act(uid){
	var img = $('img_user_act-'+uid);	// get the image object
	var old_img_src = img.src;
	
	// check is it under process
	if(img.src.indexOf('clock')>=0){
		alert('Please wait...');
		return false;
	}
	
	// get the new status need to change
	var status = img.src.indexOf('deact')>=0 ? 0 : 1;
	var new_img_src = status ? '/ui/deact.png' : '/ui/act.png'; 
	
	// update img as loading icon
	img.src = '/ui/clock.gif';
	
	var params = {
		a: 'ajax_update_user_status',
		uid: uid,
		status: status
	};
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(msg){
			var str = msg.responseText.trim();
			var ret = {};
		    var err_msg = '';
			
		    try{
                ret = JSON.parse(str); // try decode json object
                if(ret['ok']){ // success
                    img.src = new_img_src;
                    return;
				}else{  // save failed
					if(ret['failed_reason'])	err_msg = ret['failed_reason'];
					else    err_msg = str;
				}
			}catch(ex){ // failed to decode json, it is plain text response
				err_msg = str;
			}

		    // prompt the error
		    alert(err_msg);
		    img.src = old_img_src;
		}
	});
}

function list_sel(t){
	var bid = 0;
	
	if(t == 1){
		if($('sel_filter_bid'))	bid = $('sel_filter_bid').value;	// filter branch
	}
	
	if(t==-1){
		var tmp_search_str = $('inp_user_search').value.trim();

		if(tmp_search_str==''){
			//alert('Cannot search empty string');
			return;
		}else 	search_str = tmp_search_str;
	}
	
	$$('#div_tab a.a_tab').invoke('removeClassName', 'active');
	$('lst'+t).addClassName('active');
	
	var params = {
		a: 'load_cashier_list',
		branch_id: bid,
		t: t,
		search_str: search_str
	};
	
	$('div_container').update(_loading_);
	new Ajax.Updater('div_container', phpself, {
		parameters: params
	});
}

function search_input_keypress(event){
	if (event == undefined) event = window.event;
	if(event.keyCode==13){  // enter
		list_sel(-1);
	}
}

{/literal}
</script>
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


	<div class="card mx-3">
		<div class="card-body">
			<a href="?a=open"><img src="/ui/icons/user_add.png" border="0" align="absmiddle" /> Add New Cashier</a>
		</div>
	</div>


<div>
	<div id="div_tab" class="tab row mx-3 mb-3" style="white-space:nowrap;">
		
		<a href="javascript:void(list_sel(1))"  id="lst1" class="a_tab btn btn-outline-primary btn-rounded">Cashier list</a>
		&nbsp;<a href="javascript:void(list_sel(2))" id="lst2" class="a_tab btn btn-outline-primary btn-rounded">Draft</a>
		&nbsp;<a href="javascript:void(list_sel(3))" id="lst3" class="a_tab btn btn-outline-primary btn-rounded">Waiting for Approval</a>
	
		<div class="form-inline">
			<a class="a_tab" id="lst-1"><b>&nbsp;Find</b> <input class="form-control" id="inp_user_search" onKeyPress="search_input_keypress(event);" /> <input class="btn btn-primary" type="button" value="Go" onClick="list_sel(-1);" /></a>
		</div>
		<span id="span_list_loading" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
	<div id="div_container"  class="stdframe">
	
				{include file='front_end.cashier_setup.list.tpl'}
			
	</div>
</div>


	

{include file='footer.tpl'}