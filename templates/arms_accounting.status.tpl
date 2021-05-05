{*
5/20/2019 1:40 PM Andy
- Added Account Receivable Integration.

06/25/2020 11:26 AM Sheila
- Updated button css
*}
{include file='header.tpl'}

<style>

{literal}
.status_color-2{
	color:blue;
}
.status_color-3{
	color:red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var ACC_STATUS = {
	tab_num: 1,
	page_num: 0,
	initialise: function(){
		// Auto load
		this.list_sel('ap', 1);
		this.list_sel('cs', 1);
		this.list_sel('ar', 1);
	},
	// function when user click on search info
	toggle_search_info: function (acc_type){
		if(acc_type == 'ap'){
			alert("Search Batch / GRR No. / GRR Document Invoice No. / Accounting Document No.");
		}else if(acc_type == 'cs'){
			alert("Search Batch / Invoice No. / Accounting Document No.");
		}else if(acc_type == 'ar'){
			alert("Search Batch / Invoice No. / Accounting Document No.");
		}
	},
	// function when user change tab
	list_sel: function(acc_type, selected_tab){
		var batch_list = $('batch_list-'+acc_type);
		if(!batch_list) return;
		var search_str = '';
		
		if(selected_tab==0){	// 0 = Search
			var tmp_search_str = $('inp_item_search-'+acc_type).value.trim();

			if(tmp_search_str==''){
				//alert('Cannot search empty string');
				return;
			}else 	search_str = tmp_search_str;
		}
		if(typeof(selected_tab)!='undefined'){
			this.tab_num = selected_tab;
			this.page_num = 0;
		}

		var all_tab = $$('#div_container-'+acc_type+' .tab .a_tab');
		for(var i=0;i<all_tab.length;i++){
			$(all_tab[i]).removeClassName('active');
		}
		$('lst'+this.tab_num+'-'+acc_type).addClassName('active');

		$(batch_list).update(_loading_);
		var params = {
			a: 'ajax_list_sel',
			acc_type: acc_type,
			tab_num: this.tab_num,
			page_num: this.page_num
		}
		if(search_str)	params['search_str'] = search_str;
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    				
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$(batch_list).update(ret['html']);
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
			    $(batch_list).update(err_msg);
			}
		});
	},
	// function when user change page
	page_change: function(acc_type){
		var ele = $('sel_page-'+acc_type);
		this.page_num = ele.value;
		this.list_sel(acc_type);
	},
	// function when user press something in search input
	search_input_keypress: function(acc_type, event){
		if (event == undefined) event = window.event;
		if(event.keyCode==13){  // enter
			this.list_sel(acc_type, 0);	// 0 = Search
		}
	}
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{* AP *}
<div class="stdframe" id="div_container-ap">
	<h3>Account Payable</h3>
	
	<div class="tab" style="height:25px;white-space:nowrap;">
		&nbsp;&nbsp;&nbsp;
		<a class="a_tab active" href="javascript:ACC_STATUS.list_sel('ap', 1)" id="lst1-ap">New / Error Batch</a>
		<a class="a_tab" href="javascript:ACC_STATUS.list_sel('ap', 2)" id="lst2-ap">Processing</a>
		<a class="a_tab" href="javascript:ACC_STATUS.list_sel('ap', 3)" id="lst3-ap">Done</a>
		<a class="a_tab" id="lst0-ap">Search [<span class="link" onclick="ACC_STATUS.toggle_search_info('ap');">?</span>] <input id="inp_item_search-ap" onKeyPress="ACC_STATUS.search_input_keypress('ap', event);" /> <input class="btn-primary" type="button" value="Go" onClick="ACC_STATUS.list_sel('ap', 0);" /></a>
		
		<span id="span_list_loading_ap" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
	
	<div id="batch_list-ap" style="border:1px solid #000;background-color: #fff; padding:2px;">
		No Data
	</div>
</div>

<br />
<br />

{* Cash Sales *}
<div class="stdframe" id="div_container-cs">
	<h3>Cash Sales</h3>
	
	<div class="tab" style="height:25px;white-space:nowrap;">
		&nbsp;&nbsp;&nbsp;
		<a class="a_tab active" href="javascript:ACC_STATUS.list_sel('cs', 1)" id="lst1-cs">New / Error Batch</a>
		<a class="a_tab" href="javascript:ACC_STATUS.list_sel('cs', 2)" id="lst2-cs">Processing</a>
		<a class="a_tab" href="javascript:ACC_STATUS.list_sel('cs', 3)" id="lst3-cs">Done</a>
		<a class="a_tab" id="lst0-cs">Search [<span class="link" onclick="ACC_STATUS.toggle_search_info('cs');">?</span>] <input id="inp_item_search-cs" onKeyPress="ACC_STATUS.search_input_keypress('cs', event);" /> <input class="btn-primary" type="button" value="Go" onClick="ACC_STATUS.list_sel('cs', 0);" /></a>
		
		<span id="span_list_loading_ap" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
	
	<div id="batch_list-cs" style="border:1px solid #000;background-color: #fff; padding:2px;">
		No Data
	</div>
</div>

<br />
<br />

{* AR *}
<div class="stdframe" id="div_container-ar">
	<h3>Account Receivable</h3>
	
	<div class="tab" style="height:25px;white-space:nowrap;">
		&nbsp;&nbsp;&nbsp;
		<a class="a_tab active" href="javascript:ACC_STATUS.list_sel('ar', 1)" id="lst1-ar">New / Error Batch</a>
		<a class="a_tab" href="javascript:ACC_STATUS.list_sel('ar', 2)" id="lst2-ar">Processing</a>
		<a class="a_tab" href="javascript:ACC_STATUS.list_sel('ar', 3)" id="lst3-ar">Done</a>
		<a class="a_tab" id="lst0-ar">Search [<span class="link" onclick="ACC_STATUS.toggle_search_info('ar');">?</span>] <input id="inp_item_search-ar" onKeyPress="ACC_STATUS.search_input_keypress('ar', event);" /> <input class="btn-primary" type="button" value="Go" onClick="ACC_STATUS.list_sel('ar', 0);" /></a>
		
		<span id="span_list_loading_ap" style="background:yellow;padding:2px 5px;display:none;"><img src="/ui/clock.gif" align="absmiddle" /> Processing...</span>
	</div>
	
	<div id="batch_list-ar" style="border:1px solid #000;background-color: #fff; padding:2px;">
		No Data
	</div>
</div>

<script>
	ACC_STATUS.initialise();
</script>
{include file='footer.tpl'}