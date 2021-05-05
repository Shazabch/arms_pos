{*
06/25/2020 11:26 AM Sheila
- Updated button css
*}

{include file='header.tpl'}

<style>
{literal}
.tbl_shift_details td, .tbl_shift_details th{
	border: 0 !important;
	padding: 0 !important;
	margin: 0 !important;
}

div.div_user_month{
	border: 1px solid white;
}

div.div_user_month:hover{
	border: 1px solid black;
	cursor: pointer;
	background-color: #eee;
}

div.div_user_month_empty img.img_add_shift{
	visibility:hidden;
	padding: 5px;
	border: 1px solid white;
}
div.div_user_month_empty:hover img.img_add_shift{
	visibility: visible !important;
	border: 1px solid black;
	cursor: pointer;
	background-color: #eee;
}
{/literal}
</style>

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
var SHIFT_ASSIGNMENT = {
	f: undefined,
	initialize: function(){
		this.f = document.f_a;
		
		// Auto load for branch
		if(this.f['branch_id'].value){
			this.reload_branch_shift();
		}
	},
	// function when user changed year
	year_changed: function(){		
		this.reload_branch_shift();
	},
	// function when user want to create for new year
	new_year_clicked: function(){
		var new_y = prompt("Please enter year. (YYYY)");
		if(new_y == undefined)	return;
		new_y = int(new_y);
		if(new_y < 2010){
			alert('Invalid Year');
			return;
		}
		
		// Check duplicate
		this.f['y'].value = new_y;	// change to select this value
		if(this.f['y'].value == new_y){
			this.f['y'].onchange();
			alert('Year '+new_y+' already have, auto selected.');
			return;
		}
		
		var option = document.createElement("option");
		option.text = new_y;
		option.value = new_y;
		this.f['y'].add(option);
		this.f['y'].value = new_y;
		this.f['y'].onchange();
	},
	// core function to load branch shift
	reload_branch_shift: function(){
		var branch_id = this.f['branch_id'].value;
		if(!branch_id){
			alert('Please Select Branch');
			return;
		}
		
		var y = int(this.f['y'].value);
		if(y<2010){
			alert('Invalid Year');
			return;
		}
		
		$('div_branch_shift').update(_loading_).show();
		
		var THIS = this;
		var params = $(this.f).serialize();
		
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			evalScript: true,
			onComplete: function(msg){			    
			    // insert the html at the div bottom
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';
		
			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						// Update HTML
						$('div_branch_shift').update(ret['html']);
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
				$('div_branch_shift').update(err_msg);
			}
		});	
	},
	// core function to init user autocomplete
	init_user_autocomplete: function(){
		var THIS = this;
		USER_AUTOCOMPLETE.initialize({
			'callback': function(user_id, username){
				THIS.edit_user_clicked(user_id, username);
			}
		});
	},
	// function when user click on button add user
	edit_user_clicked: function(user_id, username){
		if(!user_id){
			alert('Please search user first.');
			$('inp_search_username').focus();
			return;
		}
		document.location = '?a=open_shift_user&branch_id='+document.f_branch_shift['branch_id'].value+'&y='+document.f_branch_shift['y'].value+'&user_id='+user_id;
	},
	// function when user click on edit user month
	edit_user_month_clicked: function(user_id, m){
		document.location = '?a=open_shift_user&branch_id='+document.f_branch_shift['branch_id'].value+'&y='+document.f_branch_shift['y'].value+'&user_id='+user_id+'&show_m['+m+']='+m;
	}
};
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

<form name="f_a" onSubmit="return false;">
	<input type="hidden" name="a" value="ajax_load_branch_shift" />
	
	<table>
		<tr>
			{if $BRANCH_CODE eq 'HQ'}
				<td><b>Branch: </b></td>
				<td>
					<select name="branch_id">
						<option value="">-- Please Select --</option>
						{foreach from=$branch_list key=bid item=b}
							<option value="{$bid}" {if $bid eq $smarty.request.branch_id}selected {/if}>{$b.code}</option>
						{/foreach}
					</select>
				</td>
			{else}
				<td>
					<input type="hidden" name="branch_id" value="{$sessioninfo.branch_id}" />
				</td>
			{/if}
		</tr>
		
		<tr>
			<td><b>Year: </b></td>
			<td>
				<select name="y" onChange="SHIFT_ASSIGNMENT.year_changed();">
					{foreach from=$year_list item=y}
						<option value="{$y}" {if $smarty.request.y eq $y}selected {/if}>{$y}</option>
					{/foreach}
				<select>
				<input class="btn btn-success" type="button" value="New" onClick="SHIFT_ASSIGNMENT.new_year_clicked();" />
				<input class="btn btn-primary" type="button" value="Refresh" onClick="SHIFT_ASSIGNMENT.reload_branch_shift();" />
			</td>
		</tr>
	</table>
	
	
	
	
</form>

<div id="div_branch_shift">

</div>

<script>SHIFT_ASSIGNMENT.initialize();</script>
{include file='footer.tpl'}