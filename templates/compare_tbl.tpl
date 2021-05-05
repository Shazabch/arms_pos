{include file='header.tpl'}

<style>
{literal}
.div_server{
	border: 1px solid black;
	float:left;
	width:800px;
	height:400px;
	margin-right:5px;
	margin-bottom:5px;
	overflow:auto;
}

.tbl_err{
	color: red;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}

var COMPARE_TBL = {
	form_element: undefined,
	tbl_toggle_all_flag: {},
	initialize: function(){
		this.form_element = document.f_a;
	},
	// event when user click add table to check
	add_checking_tbl: function(){
		this.move_check_tbl('tbl_list[]', 'selected_tbl_list[]');
	},
	// event when user click remove table from check
	remove_checking_tbl: function(){
		this.move_check_tbl('selected_tbl_list[]', 'tbl_list[]');
	},
	// main function to handle move table to check
	move_check_tbl: function(from_tbl, to_tbl){
		if(this.form_element[from_tbl].selectedIndex<0)	return false;	// nothing is selected
		
		var opt_to_move = [];
		
		// search for selected option
		for(var i=0; i<this.form_element[from_tbl].length; i++){
			var opt = this.form_element[from_tbl].options[i];
			if(opt.selected){
				opt_to_move.push(opt);
			
			}
		}
		
		// move selected option
		if(opt_to_move.length>0){
			for(var i=0; i<opt_to_move.length; i++){
				if(to_tbl=='tbl_list[]'){
					// put in correct group
					if($(opt_to_move[i]).hasClassName('grp_tbl')){
						$('optgroup_grp_tbl').appendChild(opt_to_move[i]);
					}else{
						$('optgroup_normal_tbl').appendChild(opt_to_move[i]);
					}
				}else{
					this.form_element[to_tbl].appendChild(opt_to_move[i]);
				}
			}
		}
	},
	// function to select/unselect all table in list
	toggle_all_tbl: function(tbl){
		var selected = (this.tbl_toggle_all_flag[tbl]) ? false : true;
	
		for(var i=0; i<this.form_element[tbl].length; i++){
			this.form_element[tbl].options[i].selected = selected;
		}
		
		if(selected)	this.tbl_toggle_all_flag[tbl] = true;
		else	this.tbl_toggle_all_flag[tbl] = false;
	},
	// function to toggle all server checkbox
	toggle_inp_server: function(ele){
		var c = ele.checked;
		
		$(this.form_element).getElementsBySelector('input.inp_server').each(function(chx){
			chx.checked = c;
		});
	},
	// function when user click start check
	start_check: function(){
		// check server selected
		var selected_server = [];
		
		$(this.form_element).getElementsBySelector('input.inp_server').each(function(chx){
			if(chx.checked)	selected_server.push(chx.value);
		});
		
		if(selected_server.length<1){
			alert('Please select at least 1 server');
			return false;
		}
		
		if(this.form_element['selected_tbl_list[]'].length<=0){
			alert('Please select at least 1 table to check');
			return false;
		}
		
		$('div_all_result').update('');
		
		toggle_select_all_opt(this.form_element['selected_tbl_list[]'], true);
		
		for(var i=0; i<selected_server.length; i++){
			this.check_server(selected_server[i]);
		}
	},
	// function to check each server
	check_server: function(server_name){
		var div_server = document.createElement('div');
		var div_id = 'div_server-'+server_name;
		$(div_server).addClassName('div_server').update(server_name+' '+_loading_).id = div_id;
		
		$('div_all_result').appendChild(div_server);
		
		var params = $(this.form_element).serialize()+'&a=check_server&server='+server_name;
		
		new Ajax.Updater(div_id, phpself, {
			parameters: params,
			method: 'post'
		});
	}
};

{/literal}
</script>
{if $all_tbl}

<form name="f_a" class="stdframe" onSubmit="return false;">
	<table>
		<tr>
			<th>Please select table to check</th>
			<th>&nbsp;</th>
			<th>Selected table to check</th>
		</tr>
		<tr>
			<td>
				<select name="tbl_list[]" multiple size="20" style="width:300px;">
					<optgroup label="Normal Table" id="optgroup_normal_tbl">
						{foreach from=$all_tbl item=tbl_name}
							<option value="{$tbl_name}" class="normal_tbl">{$tbl_name}</option>
						{/foreach}
					</optgroup>
					<optgroup label="Group Table" id="optgroup_grp_tbl">
						{foreach from=$group_tbl item=tbl_name}
							<option value="grp_tbl-{$tbl_name}" class="grp_tbl">{$tbl_name}</option>
						{/foreach}
					</optgroup>
				</select>
			</td>
			<td>
				<input type="button" value=">>" onClick="COMPARE_TBL.add_checking_tbl();" /><br /><br />
				<input type="button" value="<<" onClick="COMPARE_TBL.remove_checking_tbl();" />
			</td>
			<td>
				<select name="selected_tbl_list[]" multiple size="20" style="width:300px;">
				</select>
			</td>
		</tr>
		<tr>
			<td>
				<input type="button" value="Select/Un-select all" onClick="COMPARE_TBL.toggle_all_tbl('tbl_list[]');" />
			</td>
			<td>&nbsp;</td>
			<td>
				<input type="button" value="Select/Un-select all" onClick="COMPARE_TBL.toggle_all_tbl('selected_tbl_list[]');" />
			</td>
		</tr>
	</table>
	<p>
		<b><u>Server List</u></b><br />
		<input type="checkbox" onChange="COMPARE_TBL.toggle_inp_server(this);" ><b>All</b>
		{foreach from=$server_list key=b item=server}
			{if $server.type =='hq' || $server.type == 'branch' || $server.type == 'test'}
				<input id="inp_server-{$b}" type="checkbox" class="inp_server" value="{$b}" /> <label for="inp_server-{$b}">{$b}</label>
			{/if}
		{/foreach}
	</p>
	<p>
		<input type="button" value="Start Check" onClick="COMPARE_TBL.start_check();" />
		<input type="checkbox" name="show_only_problem_table" value="1" /> <b>Show only table with problem</b> 
	</p>
</form>
<script>COMPARE_TBL.initialize();</script>

<br />

<div id="div_all_result">
</div>
{/if}

{include file='footer.tpl'}