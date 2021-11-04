{*
1/24/2020 1:09 PM Justin
- Enhanced to allow user can create KPI for multiple Positions.
- Enhanced to show Position column while showing all positions.
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
	if($('div_sa_kpi_table').style.display == ""){
		SALES_AGENT_KPI_MODULE.sa_kpi_table_fade();
	}else{
	}
	curtain(false);
}

var SALES_AGENT_KPI_MODULE = {
	curr_kpi_id: undefined,
	curr_position_id: undefined,
	form_element: undefined,
	initialize : function(){
		// event when user click "add"
		$('add_btn').observe('click', function(){
            SALES_AGENT_KPI_MODULE.validate('add');
		});

		// even when user click "cancel" and "close"
		$('cancel_btn').observe('click', function(){
            SALES_AGENT_KPI_MODULE.sa_kpi_table_fade();
		});
		$('close_btn').observe('click', function(){
            SALES_AGENT_KPI_MODULE.sa_kpi_table_fade();
		});

		// even when user click "edit"
		$('restore_btn').observe('click', function(){
			SALES_AGENT_KPI_MODULE.kpi_edit(0, "", 1);
		});

		// event when user click "update"
		$('update_btn').observe('click', function(){
            SALES_AGENT_KPI_MODULE.validate('update');
		});
		
		new Draggable('div_sa_kpi_table');
		center_div('div_sa_kpi_table');
	},
	sa_kpi_table_appear : function(type){
		if(type == "add"){
			$('bmsg').update("Complete below form and click Add");
			$('abtn').show();
			$('ebtn').hide();
			document.f_b.reset();
			document.f_b.id.value = 0;
			$('div_upd_kpi').update('');
			$('div_upd_kpi').hide();
			$('div_add_kpi').show();
		}else{
			$('bmsg').update("Edit and click Update");
			$('abtn').hide();
			$('ebtn').show();
			$('div_upd_kpi').show();
			$('div_add_kpi').hide();
		}
		$('err_msg').update();
		hidediv('err_msg');

		showdiv('div_sa_kpi_table');
		center_div('div_sa_kpi_table');
		curtain(true);
	},
	sa_kpi_table_fade : function(){
		curtain(false);
		Effect.SlideUp('div_sa_kpi_table', {
			duration: 0.2,
			afterFinish: function() {
				$('bmsg').update();
			}
		});
	},
	validate : function(prs_type){
		if (empty(document.f_b.description, 'You must enter Description')) return false;
		if (empty(document.f_b.scores, 'You must enter Scores')) return false;

		if(prs_type == "add") SALES_AGENT_KPI_MODULE.kpi_add();
		else SALES_AGENT_KPI_MODULE.kpi_update();
	},
	kpi_add : function(){
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
						alert("KPI ["+document.f_b.description.value.trim()+"] has been added.");
						THIS.reload_kpi_list();
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
	kpi_update : function(){
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
						alert("KPI ["+document.f_b.description.value.trim()+"] has been updated.");
						THIS.reload_kpi_list();
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

	kpi_edit : function(kpi_id, position_id, is_restore){
		if(is_restore && !confirm("Are you sure want to restore?")) return;
		else if(is_restore && kpi_id == 0){
			kpi_id = this.curr_kpi_id;
			position_id = this.curr_position_id;
		}
		document.f_b.reset();
		document.f_b.id.value = kpi_id;
		this.curr_kpi_id = kpi_id;
		this.curr_position_id = position_id;
		
		var THIS = this;
		ajax_request(phpself, {
			parameters:{
				a: 'edit',
				kpi_id: kpi_id,
				position_id: position_id
			},
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
				var err_msg = '';

				ret = JSON.parse(str); // try decode json object
				if(ret['ok'] && ret['sa_kpi_info']){ // success
					if(document.f_b){
						document.f_b.id.value = ret['sa_kpi_info']['id'];
						document.f_b.position_id.value = ret['sa_kpi_info']['position_id'];
						document.f_b.description.value = ret['sa_kpi_info']['description'];
						document.f_b.additional_description.value = ret['sa_kpi_info']['additional_description'];
						document.f_b.scores.value = ret['sa_kpi_info']['scores'];
						$('div_upd_kpi').update(ret['sa_kpi_info']['position_desc']);
						if(is_restore == 0) SALES_AGENT_KPI_MODULE.sa_kpi_table_appear('edit');
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
	reload_kpi_list: function(){
		$('inp_reload_kpi').disabled = true;
		$('span_loading_kpi_list').show();
		
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
						$('div_kpi_list').update(ret['html']);
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
				
				$('inp_reload_kpi').disabled = false;
				$('span_loading_kpi_list').hide();
			}
		});
	},
	
	kpi_activation : function(id, position_id, status){
		if(status == 0 && !confirm("Are you sure want to deactivate this KPI?")) return;

		var params = {
		    a: 'activation',
			kpi_id: id,
			position_id: position_id,
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
						THIS.reload_kpi_list();
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
				
				$('inp_reload_kpi').disabled = false;
				$('span_loading_kpi_list').hide();
			}
		});
	},
}
</script>
{/literal}

<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>


<div class="card mx-3">
	<div class="card-body">
		<a onclick="SALES_AGENT_KPI_MODULE.sa_kpi_table_appear('add');" style="cursor:pointer;"><img src="ui/icons/user_add.png" title="Create KPI" align="absmiddle" border="0"> Create New KPI</a> <span id="span_loading"></span><br /><br />
	</div>
</div>

<div>
	
<div class="card mx-3">
	<div class="card-body">
		<form name="f_a" onSubmit="return false;">
			<input type="hidden" name="a" value="ajax_reload_kpi_list" />
		<div class="row">
			<div class="col">
				<span>
					<b class="form-label">Position:</b>
					<select class="form-control" name="position_id">
						<option value="">-- All --</option>
						{foreach from=$position_list key=position_id item=r}
							<option value="{$position_id}" {if $smarty.request.position_id eq $position_id}selected{/if}>{$r.code} - {$r.description}</option>
						{/foreach}
					</select>
				</span>
			</div>
			
			<div class="col">
				<span>
					<b class="form-label">Description:</b>
					<input class="form-control" type="text" name="description" />
				</span>
			</div>
			
			<div class="col">
				<span>
					<b class="from-label">Status:</b>
					<select class="form-control" name="status">
						<option value="">All</option>
						<option value="1">Active</option>
						<option value="0">Inactive</option>
					</select>
				</span>
			</div>
			
			<div class="col">
				<input id="inp_reload_kpi" class="btn btn-primary mt-4" type="button" value="Search" onClick="SALES_AGENT_KPI_MODULE.reload_kpi_list();" />
			</div>
		</div>
		</form>
	</div>
</div>
<span id="span_loading_kpi_list" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	
</div>

<br />

<div id="div_kpi_list">
<div class="card mx-3">
	<div class="card-body">
		{include file="masterfile_sa.kpi_setup.list.tpl"}
	</div>
</div>
</div>
<br>

<div class="ndiv" id="div_sa_kpi_table" style="position:absolute;width:500px;height:200px;display:none;z-index:10000;background-color: white;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small mt-2 ml-2" style="position:absolute; right:10; text-align:right;"><a onclick="SALES_AGENT_KPI_MODULE.sa_kpi_table_fade();" accesskey="C"><img src="ui/closewin.png" border="0" align="absmiddle" style="pointer:cursor;"></a><br><u>C</u>lose (Alt+C)</div>

<form method="post" name="f_b" onSubmit="return SALES_AGENT_KPI_MODULE.validate();">
	<div id="bmsg" class="mt-2 ml-2" style="padding:10 0 10 0px;"></div>
	<div id="err_msg" style="color:#CE0000; display:none; font-weight:bold;"></div>
	<input type="hidden" name="a" value="add">
	<input type="hidden" name="id" value=""> 
	<input type="hidden" name="position_id" value=""> 
	<table>
		<tr>
			<td valign="top"><b class="form-label">&nbsp;&nbsp;&nbsp;Position</b></td>
			<td>
				<div id="div_add_kpi">
					{foreach from=$position_list key=position_id item=r name=ps}
						<input type="checkbox" name="position_id_list[{$position_id}]" value="{$position_id}" />&nbsp;&nbsp;{$r.code} - {$r.description}
						{if !$smarty.foreach.ps.last}<br />{/if}
					{/foreach}
				</div>
				<div id="div_upd_kpi" style="display:none;"></div>
			</td>
		</tr>
	
		<tr>
			<td><b class="form-label">&nbsp;&nbsp;&nbsp;Description<span class="text-danger" title="Required Field"> *</span></b></td>
			<td><input class="form-control" onBlur="uc(this)" name="description" size="30" maxlength="40"></td>
	
		</tr>
		<tr>
			<td><b class="form-label">&nbsp;&nbsp;&nbsp;Additional Description</b></td>
			<td><textarea class="form-control" name="additional_description" rows="5" cols="30"></textarea></td>
		</tr>
		<tr>
			<td><b class="form-label">&nbsp;&nbsp;&nbsp;Max Scores<span class="text-danger" title="Required Field"> *</span></b></td>
			<td><input class="form-control" onBlur="mf(this)" name="scores" size="5" maxlength="5"> 
		</tr>
	</table>
	<!-- bottom -->
	<div align="center" id="abtn" style="display:none;">
		<input type="button" class="btn btn-primary mb-2 mt-2" value="Add" id="add_btn"> 
		<input type="button" class="btn btn-danger mb-2 mt-2"  value="Cancel" id="cancel_btn">
	</div>
	<div align="center" id="ebtn" style="display:none;">
		<input type="button" class="btn btn-primary mb-2 mt-2"  value="Update" id="update_btn"> 
		<input type="button" class="btn btn-info mb-2 mt-2"  value="Restore" id="restore_btn"> 
		<input type="button" class="btn btn-danger mb-2 mt-2"  value="Close" id="close_btn">
	</div>
</form>
</div></div></div>

</div>

<div style="display:none"><iframe name="_irs" width="500" height="400" frameborder="1"></iframe></div>

<script>
//init_chg(document.f_b);
SALES_AGENT_KPI_MODULE.initialize();
</script>

{include file=footer.tpl}
