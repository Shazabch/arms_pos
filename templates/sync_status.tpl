{include file="header.tpl" no_menu_templates=1}

<style>
{literal}
.critical{
	color:red;
	font-weight:bold;
}

.server_err {
	background-color: yellow;
	color: red;
}
{/literal}
</style>

<script type="text/javascript">
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var SYNC_STATUS = {
	server_reload_timer: {'main':{}, 'slave':{}},
	server_ajax_list: {'main':{}, 'slave':{}},
	server_abort_timer: {'main':{}, 'slave':{}},
	counter_reload_timer: undefined,
	initialize: function(){
		// reload all main server
		this.reload_all_server('main');
		
		// reload all slave server
		this.reload_all_server('slave');
		
		// reload counter
		this.add_reload_counter_timer();
	},
	// function to reload main server
	reload_all_server: function(server_type){
		var server_list = $$('#tbl_server-'+server_type+' tr.tr_server');
		
		for(var i=0,len=server_list.length; i<len; i++){
			// get branch id
			var server_info = this.get_server_info_by_ele(server_list[i]);
			//alert(bid)
			// load the branch
			this.load_server(server_type, server_info['server_key']);
		}
	},
	// get branch id by element
	get_server_info_by_ele: function(ele){
		var parent_ele = ele

		while(parent_ele){    // loop parebt until it found the div contain group id
		    if(parent_ele.tagName.toLowerCase()=='tr'){
                if($(parent_ele).hasClassName('tr_server')){    // found the div
					break;  // break the loop
				}
			}
			// still not found, continue to get from parent node
            parent_ele = parent_ele.parentNode;
		}
		
		if(!parent_ele) return 0;
		var tmp = parent_ele.id.split('-');
		var ret = {
			'server_type': tmp[1],
			'server_key': tmp[2]
		}
		return ret;
	},
	// core function to load main server status
	load_server: function(server_type, server_key){
		var THIS = this;	
		var params = {
			a: 'load_server_info',
			server_type: server_type,
			server_key: server_key
		};
		$('span_server_error-'+server_type+'-'+server_key).hide();
		$('span_server_status-'+server_type+'-'+server_key).hide();
		$('span_reload_server-'+server_type+'-'+server_key).hide();
		$('span_server_status_loading-'+server_type+'-'+server_key).show();
		
		// clear timer
		if(this.server_reload_timer[server_type][server_key])	clearTimeout(this.server_reload_timer[server_type][server_key]);
		
		// set if afet 1 minute no return, abort the ajax and reset the timer
		this.server_abort_timer[server_type][server_key] = setTimeout(function(){
			// stop the ajax
			THIS.stop_reload_server(server_type, server_key);
			// add the timer to reload again
			//THIS.add_reload_server_timer(server_type, server_key);
		}, 60000);
		
		this.server_ajax_list[server_type][server_key] = new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

				// add timer to reload 
				THIS.add_reload_server_timer(server_type, server_key);
						
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                	var tr_server = $('tr_server-'+server_type+'-'+server_key);
	                	tr_server.outerHTML = ret['html'];	// replace html code
	                	
	                	// clear the ajax object
						THIS.server_ajax_list[server_type][server_key] = undefined;
						
						// clear the timer to abort ajax
						if(THIS.server_abort_timer[server_type][server_key]){
							clearTimeout(THIS.server_abort_timer[server_type][server_key]);
							THIS.server_abort_timer[server_type][server_key] = undefined;
						}
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    //alert(err_msg);
			    if(!err_msg)	err_msg = "No Respond";
			    THIS.stop_reload_server(server_type, server_key);
			    //$('td_server_status-'+server_type+'-'+server_key).update(err_msg);
			}
		});
	},
	// function to create timer to reload server
	add_reload_server_timer: function(server_type, server_key){
		var THIS = this;
		
		// clear timer
		if(this.server_reload_timer[server_type][server_key])	clearTimeout(this.server_reload_timer[server_type][server_key])
		
		this.server_reload_timer[server_type][server_key] = setTimeout(function(){
			THIS.load_server(server_type, server_key);
		}, 30000);
	},
	// function when user click to reload server
	reload_server_clicked: function(server_type, server_key){
		this.load_server(server_type, server_key);
	},
	// function when user clock to stop reload server
	stop_reload_server_clicked: function(server_type, server_key){
		this.stop_reload_server(server_type, server_key);
	},
	// core function to stop ajax to reload server
	stop_reload_server: function(server_type, server_key){
		// clear the abort timer
		if(this.server_abort_timer[server_type][server_key]){
			clearTimeout(this.server_abort_timer[server_type][server_key]);
			this.server_abort_timer[server_type][server_key] = undefined;	
		}
		
		// abort the ajax
		if(this.server_ajax_list[server_type][server_key]){
			$(this.server_ajax_list[server_type][server_key]).abort();
			this.server_ajax_list[server_type][server_key] = undefined;
		}
		
		// hide loading icon
		$('span_server_status_loading-'+server_type+'-'+server_key).hide();
		
		// show reload button
		$('span_reload_server-'+server_type+'-'+server_key).show();
		
		// show error icon
		$('span_server_error-'+server_type+'-'+server_key).show();
		
		// highlight row
		$('tr_server-'+server_type+'-'+server_key).addClassName('server_err');
		
		// show error msg
		$('span_info-'+server_type+'-'+server_key).update('Failed to load content.');
		
		// add the timer to reload again
		this.add_reload_server_timer(server_type, server_key);
	},
	// functon when user click reload counter status
	reload_counter_status_clicked: function(){
		this.reload_counter_status();
	},
	// function to reload counter status
	reload_counter_status: function(){
		var THIS = this;
		
		// hide the reload button
		$('btn_reload_counter').hide();
		// show icon loading
		$('span_loading_counter').update(_loading_);
		
		
		var params = {
			a: 'load_counter_status'
		};
		
		new Ajax.Request(phpself, {
			parameters: params,
			onComplete: function(msg){
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
			    
			    // set timer to reload later
				THIS.add_reload_counter_timer();
				
				// change the icon to done
				$('span_loading_counter').update('<img src="ui/approved.png" align="absmiddle" /> Done.');
				// after 1s
				setTimeout(function(){
					// show the button for reload
					$('btn_reload_counter').show();
					// clear the loading icon or text
					$('span_loading_counter').update('');
				},1000);
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
	                	$('div_counter_status_content').update(ret['html']);
	                	
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}
	
			    // prompt the error
			    //alert(err_msg);
			    if(!err_msg)	err_msg = "No Respond";
			    
			    $('div_counter_status_content').update(err_msg);
			}
		});
	},
	// function to add counter reload timer
	add_reload_counter_timer: function(){
		var THIS = this;
		
		if(this.counter_reload_timer){
			clearTimeout(this.counter_reload_timer);
			this.counter_reload_timer = undefined;
		}
		
		// reload after 1min
		this.counter_reload_timer = setTimeout(function(){
			THIS.reload_counter_status();
		}, 60000);
	}
};
{/literal}
</script>

<h1>Server Status</h1>

{* Main Server *}
<div style="border-right:1px solid black;">
	<table width="100%" class="report_table" id="tbl_server-main">
		<thead>
			<tr class="header">
				<th width="100" rowspan="2">Branch Code</th>
				<th width="100" rowspan="2">Status</th>
				<th>Master</th>
				<th colspan="3">Slave</th>
				<th rowspan="2">Info</th>
			</tr>
			<tr class="header">
				<th>Position (logbin)</th>
				<th>Running</th>
				<th>Read position (logbin)</th>
				<th>Exec position (logbin)</th>
				
			</tr>
		</thead>
		
		
		<tbody id="tbody_server-main">
			{if $config.single_server_mode}
				{* Only Show HQ *}
				{include file="sync_status.server.tpl" server_type='main' server_key=1 server=$branch_list.1}
				
			{else}
				{* Need to show by branch *}
				{foreach from=$main_server_list key=bid item=b}
					{include file="sync_status.server.tpl" server_type='main' server_key=$bid server=$b}
				{/foreach}
			{/if}
		</tbody>
		
	</table>
</div>

{* Sock / Slave Server *}
{if $slave_server_list}
	<h1> Sock / Sync Server Status</h1>

	<div style="border-right:1px solid black;">
		<table width="100%" class="report_table" id="tbl_server-slave">
			<thead>
				<tr class="header">
					<th width="100" rowspan="2">Server IP</th>
					<th width="100" rowspan="2">Status</th>
					<th>Master</th>
					<th colspan="3">Slave</th>
					<th rowspan="2">Info</th>
				</tr>
				<tr class="header">
					<th>Position (logbin)</th>
					<th>Running</th>
					<th>Read position (logbin)</th>
					<th>Exec position (logbin)</th>
					
				</tr>
			</thead>
			
			<tbody id="tbody_server-slave">
				{* Loop each slave *}
				{foreach from=$slave_server_list item=server}
					{include file="sync_status.server.tpl" server_type='slave' server_key=$server.name}
				{/foreach}
			</tbody>
		</table>
	</div>
{/if}

{* Counter Status *}
<h1>Counter Status</h1>

<div id="div_counter_status">
	<div id="div_counter_status_control_panel">
		<button id="btn_reload_counter" onClick="SYNC_STATUS.reload_counter_status_clicked();">Reload</button>
		<span id="span_loading_counter"></span>
	</div>
	
	<div id="div_counter_status_content">
		{include file="sync_status.counter.tpl"}
	</div>
	
</div>

<script type="text/javascript">
	SYNC_STATUS.initialize();
</script>
{include file="footer.tpl"}
