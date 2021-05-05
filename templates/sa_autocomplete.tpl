{*
*}

<script>
{literal}
SA_AUTOCOMPLETE = {
	callback: undefined,
	initialize: function(params){
		if(params == undefined)	params = {};
		if(params['callback'])	this.callback = params['callback'];
		this.reset_autocomplete();
	},
	reset_autocomplete: function(){
		var THIS = this;
		new Ajax.Autocompleter('inp_search_sa_name','div_autocomplete_sa_name','ajax_autocomplete.php?a=ajax_search_sa',
			{
				'paramName': 'search_sa_name',
				indicator: 'span_sa_autocomplete_loading',
				afterUpdateElement:function(sel,li) {
					var sa_id = li.title;
					var sa_name = $(li).readAttribute('sa_name');
					$('inp_selected_sa_id').value = sa_id;
					$('display_selected_sa_id').value = sa_id;
					$('inp_selected_sa_name').value = sa_name;
				}
			}
		);
	},
	// function when user click on button add
	add_autocomplete_sa_clicked: function(){
		var sa_id = $('inp_selected_sa_id').value;
		var sa_name = $('inp_selected_sa_name').value;
		
		if(this.callback){
			this.callback(sa_id, sa_name);
		}
	},
	// function to focus on search user input
	focus_inp_search_sa_name: function(){
		$('inp_search_sa_name').focus();
	}
};
{/literal}
</script>

<input type="text" readonly id="display_selected_sa_id" style="width:60px;"/>
<input type="text" id="inp_search_sa_name" style="width:200px;" placeholder="Please search Sales Agent name" onFocus="this.select();" value=""  />
<input type="hidden" id="inp_selected_sa_id" value="" />
<input type="hidden" id="inp_selected_sa_name" value="" />
{if $btn_add}
	<input type="button" value="{$btn_add_label|default:'OK'}" id="btn_add_sa" onClick="SA_AUTOCOMPLETE.add_autocomplete_sa_clicked();" />
{/if}
<br />
<span id="span_sa_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>

<br />
<div id="div_autocomplete_sa_name" class="autocomplete" style="display:none;height:150px !important;width:200px !important;overflow:auto !important;z-index:100"></div>