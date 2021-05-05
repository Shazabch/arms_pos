{*
*}
{include file=header.tpl}
{literal}
<style>
a{
	cursor:pointer;
}
</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function curtain_clicked(){
	if($('div_sa_position_table').style.display == ""){
		SALES_AGENT_POSITION_SETUP_MODULE.sa_position_table_fade();
	}else{
	}
	curtain(false);
}

var SALES_AGENT_POSITION_SETUP_MODULE = {
	curr_position_id: undefined,
	form_element: undefined,
	initialize : function(){
		// event when user click "add"
		$('add_btn').observe('click', function(){
            SALES_AGENT_POSITION_SETUP_MODULE.validate('add');
		});

		// even when user click "cancel" and "close"
		$('cancel_btn').observe('click', function(){
            SALES_AGENT_POSITION_SETUP_MODULE.sa_position_table_fade();
		});
		$('close_btn').observe('click', function(){
            SALES_AGENT_POSITION_SETUP_MODULE.sa_position_table_fade();
		});

		// even when user click "edit"
		$('restore_btn').observe('click', function(){
			SALES_AGENT_POSITION_SETUP_MODULE.position_edit(0, "", 1);
		});

		// event when user click "update"
		$('update_btn').observe('click', function(){
            SALES_AGENT_POSITION_SETUP_MODULE.validate('update');
		});
		
		new Draggable('div_sa_position_table');
		center_div('div_sa_position_table');
	},
	sa_position_table_appear : function(type){
		if(type == "add"){
			$('bmsg').update("Complete below form and click Add");
			$('abtn').show();
			$('ebtn').hide();
			document.f_b.reset();
			document.f_b.id.value = 0;
		}else{
			$('bmsg').update("Edit and click Update");
			$('abtn').hide();
			$('ebtn').show();
		}
		$('err_msg').update();
		hidediv('err_msg');

		showdiv('div_sa_position_table');
		center_div('div_sa_position_table');
		curtain(true);
	},
	sa_position_table_fade : function(){
		curtain(false);
		Effect.SlideUp('div_sa_position_table', {
			duration: 0.2,
			afterFinish: function() {
				$('bmsg').update();
			}
		});
	},
	validate : function(prs_type){
		if (empty(document.f_b.code, 'You must enter Code')) return false;

		if(prs_type == "add") SALES_AGENT_POSITION_SETUP_MODULE.position_add();
		else SALES_AGENT_POSITION_SETUP_MODULE.position_update();
	},
	position_add : function(){
		this.form_element = document.f_b;
		var prm = $(this.form_element).serialize();
		
		var params = {
		    a: 'add'
		};
		prm += '&'+$H(params).toQueryString();

		var THIS = this;
		ajax_request(phpself, {
			parameters: prm,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){ // success
						alert("Position ["+document.f_b.code.value.trim()+"] has been added.");
						THIS.reload_position_list();
						curtain_clicked();
					}else{  // load sa info failed
						if(ret['failed_msg'])	err_msg = ret['failed_msg'];
						else err_msg = str;
					}
				}catch(ex){
					err_msg = str;
				}
				
				$('err_msg').update(err_msg);
				Effect.Appear('err_msg', {
					duration: 0.5
				});
			},
						
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},
	position_update : function(){
		this.form_element = document.f_b;
		var prm = $(this.form_element).serialize();

		var params = {
		    a: 'update'
		};
		prm += '&'+$H(params).toQueryString();

		var THIS = this;
		ajax_request(phpself, {
			parameters: prm,
			method: 'post',			
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok']){ // success
						alert("Position ["+document.f_b.code.value.trim()+"] has been updated.");
						THIS.reload_position_list();
						curtain_clicked();
					}else{  // load sa info failed
						if(ret['failed_msg'])	err_msg = ret['failed_msg'];
						else err_msg = str;
					}
				}catch(ex){
					err_msg = str;
				}
				
				$('err_msg').update(err_msg);
				Effect.Appear('err_msg', {
					duration: 0.5
				});
			},
			
			onFailure: function(msg){
				alert(msg.responseText.trim());
			}
		});
	},

	position_edit : function(position_id, is_restore){
		if(is_restore && !confirm("Are you sure want to restore?")) return;
		else if(is_restore && position_id == 0){
			position_id = this.curr_position_id;
		}
		document.f_b.reset();
		document.f_b.id.value = position_id;
		this.curr_position_id = position_id;
		
		var THIS = this;
		ajax_request(phpself, {
			parameters:{
				a: 'edit',
				position_id: position_id
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['sa_position_info']){ // success
					if(document.f_b){
						document.f_b.id.value = ret['sa_position_info']['id'];
						document.f_b.code.value = ret['sa_position_info']['code'];
						document.f_b.description.value = ret['sa_position_info']['description'];
						if(is_restore == 0) SALES_AGENT_POSITION_SETUP_MODULE.sa_position_table_appear('edit');
						return;
					}else err_msg = "Failed to load edit form!";
				}else{  // load sa info failed
					if(ret['failed_msg'])	err_msg = ret['failed_msg'];
					else err_msg = str;
				}

				alert(err_msg);
			}
		});

		document.f_b.a.value = 'update';
		document.f_b.description.focus();
	},
	// function to reload sa list
	reload_position_list: function(){
		$('inp_reload_position').disabled = true;
		$('span_loading_position_list').show();
		
		var params = $(document.f_a).serialize();
	
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						$('div_position_list').update(ret['html']);
					}else{
						if(ret['error']){
							alert(ret['error']);
						}else{
							alert(str);
						}
					}
				}catch(ex){
					alert(str);
				}
				
				$('inp_reload_position').disabled = false;
				$('span_loading_position_list').hide();
			}
		});
	},
	
	position_activation : function(id, status){
		if(status == 0 && !confirm("Are you sure want to deactivate this Position?")) return;

		var params = {
		    a: 'activation',
			position_id: id,
			value: status
		};
		//prm += '&'+$H(params).toQueryString();

		var THIS = this;
		ajax_request(phpself, {
			parameters: params,
			method: 'post',
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};

				try{
					ret = JSON.parse(str); // try decode json object
					if(ret['ok'] == 1){ // success
						THIS.reload_position_list();
					}else{
						if(ret['error']){
							alert(ret['error']);
						}else{
							alert(str);
						}
					}
				}catch(ex){
					alert(str);
				}
				
				$('inp_reload_position').disabled = false;
				$('span_loading_position_list').hide();
			}
		});
	},
}
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

<div>
	<a onclick="SALES_AGENT_POSITION_SETUP_MODULE.sa_position_table_appear('add');" style="cursor:pointer;"><img src="ui/icons/user_add.png" title="Create Position" align="absmiddle" border="0"> Create New Position</a> <span id="span_loading"></span><br /><br />
</div>

<div>
	
<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_reload_position_list" />
	
	<span>
		<b>Code or Description:</b>
		<input type="text" name="code_or_description" />
		&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<span>
		<b>Status:</b>
		<select name="status">
			<option value="">All</option>
			<option value="1">Active</option>
			<option value="0">Inactive</option>
		</select>
		&nbsp;&nbsp;&nbsp;&nbsp;
	</span>
	
	<input id="inp_reload_position" type="button" value="Search" onClick="SALES_AGENT_POSITION_SETUP_MODULE.reload_position_list();" />
</form>
<span id="span_loading_position_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	
</div>

<br />

<div id="div_position_list">
{include file="masterfile_sa.position_setup.list.tpl"}
</div>
<br>

<div class="ndiv" id="div_sa_position_table" style="position:absolute;width:400px;height:200px;display:none;z-index:10000;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small" style="position:absolute; right:10; text-align:right;"><a onclick="SALES_AGENT_POSITION_SETUP_MODULE.sa_position_table_fade();" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br><u>C</u>lose (Alt+C)</div>

<form method="post" name="f_b" onSubmit="return SALES_AGENT_POSITION_SETUP_MODULE.validate();">
	<div id="bmsg" style="padding:10 0 10 0px;"></div>
	<div id="err_msg" style="color:#CE0000; display:none; font-weight:bold;"></div>
	<input type="hidden" name="a" value="add">
	<input type="hidden" name="id" value="">
	<table>
		<tr>
			<td><b>Code</b></td>
			<td><input onBlur="uc(this)" name="code" size="20" maxlength="20"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
		</tr>
		<tr>
			<td><b>Description</b></td>
			<td>
				<textarea name="description" rows="5" cols="30"></textarea>
			</td>
		</tr>
	</table>
	<!-- bottom -->
	<div align="center" id="abtn" style="display:none;">
		<input type="button" value="Add" id="add_btn"> 
		<input type="button" value="Cancel" id="cancel_btn">
	</div>
	<div align="center" id="ebtn" style="display:none;">
		<input type="button" value="Update" id="update_btn"> 
		<input type="button" value="Restore" id="restore_btn"> 
		<input type="button" value="Close" id="close_btn">
	</div>
</form>
</div></div></div>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
SALES_AGENT_POSITION_SETUP_MODULE.initialize();
</script>

{include file=footer.tpl}
