<?php /* Smarty version 2.6.18, created on 2021-05-10 17:42:30
         compiled from category_autocomplete2.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'category_autocomplete2.tpl', 8, false),)), $this); ?>

<script type="text/javascript">
var ext = '<?php echo $this->_tpl_vars['ext']; ?>
';
var parent_form = eval("<?php echo ((is_array($_tmp=@$this->_tpl_vars['parent_form'])) ? $this->_run_mod_handler('default', true, $_tmp, 'document.f_a') : smarty_modifier_default($_tmp, 'document.f_a')); ?>
"); 

CAT_AUTOCOMPLETE_MAIN<?php echo $this->_tpl_vars['ext']; ?>
 <?php echo ' = {
	form_element: undefined,
	inp_category_id: undefined,
	inp_search_cat_autocomplete: undefined,
	div_search_cat_autocomplete_choices: undefined,
	div_category_autocomplete_description: undefined,
	span_category_autocomplete_loading: undefined,
	inp_add_cat_autocomplete: undefined,
	cat_autocomplete: undefined,
	callback: undefined,
	initialize: function(params, callback){
		if(!params){
			alert(\'Please provide the default params\');
			return false;
		}
		
		if(!callback){
			alert(\'Please provide the callback function\');
			return false;
		}
		// store all element
		this.form_element = parent_form;
		this.inp_category_id = $(\'inp_category_id\'+ext);
		this.inp_search_cat_autocomplete = $(\'inp_search_cat_autocomplete\'+ext);
		this.div_search_cat_autocomplete_choices = $(\'div_search_cat_autocomplete_choices\'+ext);
		this.div_category_autocomplete_description = $(\'div_category_autocomplete_description\'+ext);
		this.span_category_autocomplete_loading = $(\'span_category_autocomplete_loading\'+ext);
		this.inp_add_cat_autocomplete = $(\'inp_add_cat_autocomplete\'+ext);
		
		// store the caller default params
		this.initial_params = params;
		
		// store the caller callback
		this.callback = callback;
		
		// initial autocomplete
		this.reset_category_autocomplete();
		
		var THIS = this;
		
		// add click event to "add" button 
		$(this.inp_add_cat_autocomplete).observe(\'click\', function(){
			THIS.add_clicked();
		});
		return this;
	},
	// reset category autocomplete
	reset_category_autocomplete: function(){
	    $(this.inp_search_cat_autocomplete).value = \'\';
	    
	    if(!this.cat_autocomplete){
	    	// store own object so can call self in other function
	    	var THIS = this;
	    	
	    	// first time, set all default params
		    var params = {
				a: \'ajax_search_category\'
			};
			if(this.initial_params[\'no_findcat_expand\'])	params[\'no_findcat_expand\'] = this.initial_params[\'no_findcat_expand\'];
			if(this.initial_params[\'max_level\'])	params[\'max_level\'] = this.initial_params[\'max_level\'];
			if(this.initial_params[\'min_level\'] != undefined)	params[\'min_level\'] = int(this.initial_params[\'min_level\']);
			if(this.initial_params[\'skip_dept_filter\'])	params[\'skip_dept_filter\'] = 1;
			
			// haste params and make query string
            var params = $H(params).toQueryString();
			
			// initial ajax autocomplete
	        this.cat_autocomplete = new Ajax.Autocompleter(this.inp_search_cat_autocomplete, this.div_search_cat_autocomplete_choices, \'ajax_autocomplete.php\', {
		        parameters: params,
				paramName: "category",
				indicator: \'span_category_autocomplete_loading\'+ext,
				afterUpdateElement: function (obj, li) {
				    s = li.title.split(",");

					// no category id
		            if (s[0]==\'\'){
				        obj.value=\'\';
				        return;
				    }

					// callback
					THIS.cat_selected(s[0]);
				}
			});
		}
	},
	// user selected category
	cat_selected: function(cat_id){
		if(!cat_id){
			return false;
		}
		
		this.inp_category_id.value = cat_id;
		$(this.div_category_autocomplete_description).update(this.inp_search_cat_autocomplete.value);
		// callback to caller function
		//this.callback(cat_id);
	},
	// function to reset searched value
	reset: function(){
		// clear the search box
		this.inp_search_cat_autocomplete.value = \'\';
		// clear the selected value
		this.inp_category_id.value = \'\';
		// clear the description
		$(this.div_category_autocomplete_description).update(\'\');
	},
	// user click "add" category
	add_clicked: function(){
		var cat_id = int(this.inp_category_id.value);
		
		this.callback(cat_id);
	},
	// function to show/hide add button
	show_add_button: function(show){
		if(show)	$(this.inp_add_cat_autocomplete).show();
		else	$(this.inp_add_cat_autocomplete).hide();
	},
	// function to focus and select the search box
	select_search_box: function(){
		this.inp_search_cat_autocomplete.select();
	},
	// function will return current selected category id
	get_selected_cat_id: function(){
		return int(this.inp_category_id.value);
	}
}
'; ?>

</script>

<table width="100%">
    <!-- Category -->
    <tr>
        <td width="100"><b>Search Category</b></td>
        <td>
        	<input type="hidden" id="inp_category_id<?php echo $this->_tpl_vars['ext']; ?>
" name="category_id" >
            <input type="text" id="inp_search_cat_autocomplete<?php echo $this->_tpl_vars['ext']; ?>
" name="search_cat_autocomplete" size="30" style="font-size:14px;width:400px;" onClick="this.select();" />
            <input type="button" id="inp_add_cat_autocomplete<?php echo $this->_tpl_vars['ext']; ?>
" value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['add_value'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Add') : smarty_modifier_default($_tmp, 'Add')); ?>
" />
            <div id="div_search_cat_autocomplete_choices<?php echo $this->_tpl_vars['ext']; ?>
" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
        </td>
    </tr>
    <tr>
    	<td>&nbsp;</td>
    	<td>
    		<div id="div_category_autocomplete_description<?php echo $this->_tpl_vars['ext']; ?>
" style="color:blue;">
    		</div>
    	</td>
    </tr>
</table>
<div style="height:20px;margin-top:-20px;">
	<span id="span_category_autocomplete_loading<?php echo $this->_tpl_vars['ext']; ?>
" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
</div>