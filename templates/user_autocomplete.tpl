{*
11/1/2019 10:38 AM Andy
- Enhanced to work after ajax call.

06/25/2020 11:26 AM Sheila
- Updated button css
*}

<script>
{literal}
USER_AUTOCOMPLETE = {
	callback: undefined,
	initialize: function(params){
		if(params == undefined)	params = {};
		if(params['callback'])	this.callback = params['callback'];
		this.reset_autocomplete();
	},
	reset_autocomplete: function(){
		var THIS = this;
		new Ajax.Autocompleter('inp_search_username','div_autocomplete_username','ajax_autocomplete.php?a=ajax_search_user',
			{
				'paramName': 'search_username',
				indicator: 'span_user_autocomplete_loading',
				afterUpdateElement:function(sel,li) {
					var uid = li.title;
					var username = $(li).readAttribute('u');
					$('inp_selected_user_id').value = uid;
					$('inp_selected_username').value = username;
				}
			}
		);
	},
	// function when user click on button add
	add_autocomplete_user_clicked: function(){
		var user_id = $('inp_selected_user_id').value;
		var username = $('inp_selected_username').value;
		
		if(this.callback){
			this.callback(user_id, username);
		}
	},
	// function to focus on search user input
	focus_inp_search_username: function(){
		$('inp_search_username').focus();
	}
};
{/literal}
</script>

<input type="text" readonly id="inp_selected_username" style="width:60px;"/>
<input type="text" id="inp_search_username" style="width:200px;" placeholder="Please search username" onFocus="this.select();" value=""  />
<input type="hidden" id="inp_selected_user_id" value="" />
{if $btn_add}
	<input class="btn btn-primary" type="button" value="{$btn_add_label|default:'OK'}" id="btn_add_user" onClick="USER_AUTOCOMPLETE.add_autocomplete_user_clicked();" />
{/if}
<br />
<span id="span_user_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>

<br />
<div id="div_autocomplete_username" class="autocomplete" style="display:none;height:150px !important;width:200px !important;overflow:auto !important;z-index:100"></div>