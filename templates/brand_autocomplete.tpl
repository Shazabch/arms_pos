<script>
var ext = '{$ext}';
var parent_form = eval("{$parent_form|default:'document.f_a'}"); 

BRAND_AUTOCOMPLETE_MAIN{$ext} {literal} = {
	form_element: undefined,
	inp_brand_id: undefined,
	inp_search_brand_autocomplete: undefined,
	div_search_brand_autocomplete_choices: undefined,
	div_brand_autocomplete_description: undefined,
	span_brand_autocomplete_loading: undefined,
	inp_add_brand_autocomplete: undefined,
	brand_autocomplete: undefined,
	callback: undefined,
	initialize: function(params, callback){
		if(!params){
			alert('Please provide the default params');
			return false;
		}
		
		if(!callback){
			alert('Please provide the callback function');
			return false;
		}
		// store all element
		this.form_element = parent_form;
		this.inp_brand_id = $('inp_brand_id'+ext);
		this.inp_search_brand_autocomplete = $('inp_search_brand_autocomplete'+ext);
		this.div_search_brand_autocomplete_choices = $('div_search_brand_autocomplete_choices'+ext);
		this.div_brand_autocomplete_description = $('div_brand_autocomplete_description'+ext);
		this.span_brand_autocomplete_loading = $('span_brand_autocomplete_loading'+ext);
		this.inp_add_brand_autocomplete = $('inp_add_brand_autocomplete'+ext);
		
		// store the caller default params
		this.initial_params = params;
		
		// store the caller callback
		this.callback = callback;
		
		// initial autocomplete
		this.reset_autocomplete();
		
		var THIS = this;
		
		// add click event to "add" button 
		$(this.inp_add_brand_autocomplete).observe('click', function(){
			THIS.add_clicked();
		});
		return this;
	},
	// reset category autocomplete
	reset_autocomplete: function(){
	    $(this.inp_search_brand_autocomplete).value = '';
	    
	    if(!this.brand_autocomplete){
	    	// store own object so can call self in other function
	    	var THIS = this;
	    	
	    	// first time, set all default params
		    var params = {
				a: 'ajax_search_brand'
			};
			
			// haste params and make query string
            var params = $H(params).toQueryString();
			
			// initial ajax autocomplete
	        this.brand_autocomplete = new Ajax.Autocompleter(this.inp_search_brand_autocomplete, this.div_search_brand_autocomplete_choices, 'ajax_autocomplete.php', {
		        parameters: params,
				paramName: "brand",
				indicator: 'span_brand_autocomplete_loading'+ext,
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

					// no brand id
		            if (s[0]==''){
				        obj.value='';
				        return;
				    }

					// callback
					THIS.brand_selected(s[0]);
				}
			});
		}
	},
	// user selected brand
	brand_selected: function(brand_id){
		if(!brand_id){
			return false;
		}
		
		this.inp_brand_id.value = brand_id;
		$(this.div_brand_autocomplete_description).update(this.inp_search_brand_autocomplete.value);
	},
	// function to reset searched value
	reset: function(){
		// clear the search box
		this.inp_search_brand_autocomplete.value = '';
		// clear the selected value
		this.inp_brand_id.value = '';
		// clear the description
		$(this.div_brand_autocomplete_description).update('');
	},
	// user click "add" category
	add_clicked: function(){
		var brand_id = int(this.inp_brand_id.value);
		
		this.callback(brand_id);
	},
	// function to show/hide add button
	show_add_button: function(show){
		if(show)	$(this.inp_add_brand_autocomplete).show();
		else	$(this.inp_add_brand_autocomplete).hide();
	},
	// function to focus and select the search box
	select_search_box: function(){
		this.inp_search_brand_autocomplete.select();
	},
	// function will return current selected brand id
	get_selected_brand_id: function(){
		return int(this.inp_brand_id.value);
	}
}
{/literal}
</script>

<table width="100%">
    <!-- Brand -->
    <tr>
        <td width="100"><b>Search Brand</b></td>
        <td>
        	<input type="hidden" id="inp_brand_id{$ext}" name="category_id" >
            <input type="text" id="inp_search_brand_autocomplete{$ext}" name="search_cat_autocomplete" size="30" style="font-size:14px;width:400px;" onClick="this.select();" />
            <input type="button" id="inp_add_brand_autocomplete{$ext}" value="{$add_value|default:'Add'}" />
            <div id="div_search_brand_autocomplete_choices{$ext}" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
        </td>
    </tr>
    <tr>
    	<td>&nbsp;</td>
    	<td>
    		<div id="div_brand_autocomplete_description{$ext}" style="color:blue;">
    		</div>
    	</td>
    </tr>
</table>
<div style="height:20px;margin-top:-20px;">
	<span id="span_brand_autocomplete_loading{$ext}" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
</div>