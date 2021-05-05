{*
1/2/2012 10:49 AM Andy
- Add get sku id in ajax sku autocomplete.
- Change sku auto complete callback to pass additinal 1 option.
*}
{if !$prefix}{assign var=prefix value="vp-"}{/if}

<script type="text/javascript">
{literal}
var SKU_AUTOCOMPLETE = Class.create();
SKU_AUTOCOMPLETE.prototype = {
	f: undefined,
	prefix:undefined,
	obj_autocomplete: undefined,
	initialize: function(f, prefix, add_callback){   // constructor accept prefix
		var THIS = this;
		
		this.f = f;
		this.prefix = prefix;
		
		this.reset_sku_autocomplete();	// initial function
		
		
		$A(this.f[prefix+'search_type']).each(function(inp){	// put event to every search type
			$(inp).observe('change', function(){
				THIS.reset_sku_autocomplete();
			});
		});
		
		// event when user click add button
		var btn_add_autocomplete = $(prefix+'btn_add_autocomplete');
		if(btn_add_autocomplete && add_callback){
			btn_add_autocomplete.observe('click', function(){
				var tmp_params = {
					'sid': $(prefix+'sku_item_id').value,
					'sku_item_code': $(prefix+'sku_item_code').value,
					'sku_id': $(prefix+'sku_id').value
				}
				add_callback($(prefix+'sku_item_id').value, tmp_params);	// call back function with sku item id
			});
		}
		
		return this;
	},
	reset_sku_autocomplete:function(){
		var THIS = this;
		var prefix = this.prefix;
		
		// get search type
		var type = getRadioValue(this.f[prefix+'search_type']);
		
		// construct params
		var param_str = "a=ajax_search_sku&type="+type;

		if(type==5){    // handheld
		    // clear the autocomplete event handler and variable
		}else{  // normal sku autocomplete
		    $(prefix+'autocomplete_sku').show();
	        if (this.obj_autocomplete != undefined)
			{
			    this.obj_autocomplete.options.defaultParams = param_str;
			}
			else
			{
				this.obj_autocomplete = new Ajax.Autocompleter(prefix+"autocomplete_sku", prefix+"autocomplete_sku_choices", "vp.ajax_autocomplete.php", {
					parameters:param_str, 
					paramName: "value",
					indicator: prefix+'span_autocomplete_loading',
					afterUpdateElement: function (obj, li) {
					    s = li.title.split(",");
					    
					    if (!s[0] || s[0]==0){
					        obj.value='';
					    }else{
							$(prefix+'sku_item_id').value = s[0];
							$(prefix+'sku_item_code').value = s[1];
							$(prefix+'sku_id').value = s[2];
						}
					}
				});
			}
			this.clear_autocomplete();
		}
	},
	clear_autocomplete: function(){
		var prefix = this.prefix;
		
		$(prefix+'sku_item_id').value = '';
		$(prefix+'sku_item_code').value = '';
		$(prefix+'autocomplete_sku').value = '';
		$(prefix+'autocomplete_sku').focus();
	}
}
{/literal}
</script>

<table>
	<tr>
		<th>Search SKU</th>
		<td>
			{assign var=tmp_var value="`$prefix`sku_item_id"}
			<input id="{$tmp_var}" name="{$tmp_var}" size="3" type="hidden" value="{$smarty.request.$tmp_var}" />
			
			{assign var=tmp_var value="`$prefix`sku_item_code"}
			<input id="{$tmp_var}" name="{$tmp_var}" size="13" type="hidden" value="{$smarty.request.$tmp_var}" />
			
			{assign var=tmp_var value="`$prefix`sku_id"}
			<input id="{$tmp_var}" name="{$tmp_var}" size="13" type="hidden" value="{$smarty.request.$tmp_var}" />

			{assign var=tmp_var value="`$prefix`autocomplete_sku"}			
			<input id="{$tmp_var}" name="{$tmp_var}" size="50" onclick="this.select()" style="font-size:14px;width:500px;" value="{$smarty.request.$tmp_var}" />
			
			{if !$no_add_button}
				<input id="{$prefix}btn_add_autocomplete" type=button value="{$_add_value|default:'Add'}" />
			{/if}
			
			<span id="{$prefix}span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
			<div id="{$prefix}autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
		</td>
		<td><!--<input type=submit value="Find">--></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>
			{assign var=tmp_var value="`$prefix`search_type"}
			<input type="radio" name="{$tmp_var}" value="1" {if !$smarty.request.$tmp_var || $smarty.request.$tmp_var eq '1'}checked {/if} > MCode &amp; {$config.link_code_name}
			<input type="radio" name="{$tmp_var}" value="2" {if $smarty.request.$tmp_var eq '2' || (!$smarty.request.$tmp_var and $config.consignment_modules)}checked {/if} /> Article No
			<input type="radio" name="{$tmp_var}" value="3" {if $smarty.request.$tmp_var eq '3'}checked {/if}> ARMS Code
			<input type="radio" name="{$tmp_var}" value="4" {if $smarty.request.$tmp_var eq '4'}checked {/if}> Description
		</td>
	</tr>
</table>