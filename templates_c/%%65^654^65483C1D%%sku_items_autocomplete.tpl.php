<?php /* Smarty version 2.6.18, created on 2021-05-10 17:42:30
         compiled from sku_items_autocomplete.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'sku_items_autocomplete.tpl', 72, false),array('modifier', 'escape', 'sku_items_autocomplete.tpl', 337, false),)), $this); ?>

<script type="text/javascript">
var sku_autocomplete = undefined;
var skip_dept_filter = '<?php echo $this->_tpl_vars['skip_dept_filter']; ?>
';
var fresh_market_filter = '<?php echo $this->_tpl_vars['fresh_market_filter']; ?>
';
var sku_parent_form = eval("<?php echo ((is_array($_tmp=@$this->_tpl_vars['parent_form'])) ? $this->_run_mod_handler('default', true, $_tmp, 'document.f_a') : smarty_modifier_default($_tmp, 'document.f_a')); ?>
");
var enable_handheld = '<?php echo $this->_tpl_vars['enable_handheld']; ?>
';
var show_qty_input = '<?php echo $this->_tpl_vars['show_qty_input']; ?>
';
var show_more_info = '<?php echo $this->_tpl_vars['show_more_info']; ?>
';
var block_list='<?php echo $this->_tpl_vars['block_list']; ?>
';
var is_parent_only = '<?php echo $this->_tpl_vars['is_parent_only']; ?>
';
var dept_filter_name = '<?php echo $this->_tpl_vars['dept_filter_name']; ?>
';
var no_show_varieties = '<?php echo $this->_tpl_vars['no_show_varieties']; ?>
';
var under_parent_div = eval('<?php echo $this->_tpl_vars['under_parent_div']; ?>
');
var doc_block_type='<?php echo $this->_tpl_vars['doc_block_type']; ?>
';

<?php echo '
reset_sku_autocomplete = function(no_clear)
{
	var type = getRadioValue(sku_parent_form.search_type);
	var show_variaties = no_show_varieties ? 0 : 1;
	var param_str = "a=ajax_search_sku&show_varieties="+show_variaties+"&type="+type;
	if(dept_filter_name && sku_parent_form[dept_filter_name])    param_str += \'&dept_id=\'+sku_parent_form[dept_filter_name].value;  // add dept filter
	if(skip_dept_filter)    param_str += \'&skip_dept_filter=1\'; // skip department filter
	if(fresh_market_filter)   param_str += \'&fresh_market_filter=\'+fresh_market_filter;    // only get fresh market sku
	if (block_list)    param_str +="&block_list=1";
	if(is_parent_only)  param_str += \'&is_parent_only=1\';   // only get parent sku
	if(doc_block_type)  param_str += \'&doc_block_type=\'+doc_block_type;   // check block item in GRN

	if(type==5){    // handheld
	    // clear the autocomplete event handler and variable
	    $(\'autocomplete_sku\').hide();
	    $(\'inp_sku_handheld\').show();
	    sku_handheld.reset_status();
	}else{  // normal sku autocomplete
	    if($(\'inp_sku_handheld\'))   $(\'inp_sku_handheld\').hide();
	    $(\'autocomplete_sku\').show();
        if (sku_autocomplete != undefined)
		{
		    sku_autocomplete.options.defaultParams = param_str;
		}
		else
		{
			sku_autocomplete = new Ajax.Autocompleter("autocomplete_sku", "autocomplete_sku_choices", "ajax_autocomplete.php", {parameters:param_str, paramName: "value",
			indicator: \'span_autocomplete_loading\',
			afterUpdateElement: function (obj, li) {
			    s = li.title.split(",");
			    
			    if (!s[0] || s[0]==0){
			        obj.value=\'\';
			    }else{
					$(\'sku_item_id\').value = s[0];
					$(\'sku_item_code\').value = s[1];
					var doc_allow_decimal = sku_parent_form[\'inp_dad,\'+s[0]].value;
					if(sku_parent_form[\'sku_autocomplete_qty\'] != undefined){
						if(doc_allow_decimal == 1){
							sku_parent_form[\'sku_autocomplete_qty\'].onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
						}else{
							sku_parent_form[\'sku_autocomplete_qty\'].onchange = function(){ mi(this); };
							sku_parent_form[\'sku_autocomplete_qty\'].value = int(sku_parent_form[\'sku_autocomplete_qty\'].value);
						}
					}
					
					if(show_qty_input){
						$(\'inp_autocomplete_qty\').focus();
					}
					
					if(show_more_info){
						ajax_get_more_info(s[0]);//must have this function in own script
					}
				}
			}});
			if($(\'autocomplete_sku_indicator\')){
	            sku_autocomplete.options.indicator = \'autocomplete_sku_indicator\';
			}
		}
		
		if(!no_clear)	clear_autocomplete();
	}
}

clear_autocomplete = function(){
	$(\'sku_item_id\').value = \'\';
	$(\'sku_item_code\').value = \'\';
	$(\'autocomplete_sku\').value = \'\';
	$(\'autocomplete_sku\').focus();
	if($(\'inp_autocomplete_qty\'))   $(\'inp_autocomplete_qty\').value = \'\';
}

open_multi_add = function(){
    var type = getRadioValue(sku_parent_form.search_type);
    if(type==5)	return false;    // handheld no multiple add

	var v = $(\'autocomplete_sku\').value.trim();
	if(v==\'\')   return false;   // empty search value
	
	var skip_sku_item_id = [];
	if(typeof(check_skip_sku_item_id) != "undefined"){
		skip_sku_item_id = check_skip_sku_item_id();
	}
	
	var obj_params = {
		\'a\': \'ajax_search_sku\',
		\'type\': getRadioValue(sku_parent_form.search_type),
		\'hide_print\': 1,
		\'show_multiple\': 1,
		\'value\': v,
	};

	if(dept_filter_name && sku_parent_form[dept_filter_name])    obj_params[\'dept_id\'] = sku_parent_form[dept_filter_name].value;  // add dept filter
	if(skip_dept_filter)    obj_params[\'skip_dept_filter\'] = 1; // skip department filter
	if(block_list)    obj_params[\'block_list\'] = 1;
	if(fresh_market_filter)   obj_params[\'fresh_market_filter\'] = fresh_market_filter;    // only get fresh market sku
	if(is_parent_only)  obj_params[\'is_parent_only\'] = 1;   // only get parent sku
	if(doc_block_type)  obj_params[\'doc_block_type\'] = doc_block_type;   // check block item in GRN
	
	if(skip_sku_item_id.length>0)	obj_params[\'skip_sku_item_id[]\'] = skip_sku_item_id;
		
	new Ajax.Updater(\'div_multiple_add_popup_content\',\'ajax_autocomplete.php\',{
		parameters: obj_params,
		method: \'post\',
		evalScripts: true
	});
	
	curtain(true);
	$(\'div_multiple_add_popup_content\').update(_loading_);
	center_div($(\'div_multiple_add_popup\').show());
	
	if(under_parent_div){
		$(\'div_multiple_add_popup\').style.left = (int($(\'div_multiple_add_popup\').style.left) - int(under_parent_div.style.left))+\'px\';
		$(\'div_multiple_add_popup\').style.top = (int($(\'div_multiple_add_popup\').style.top) - int(under_parent_div.style.top))+\'px\';
	}
}

multiple_window_close = function(){
	if(typeof(handle_multiple_window_close)!=\'undefined\'){
        handle_multiple_window_close();
	}else{
		if(window.alternative_multiple_window_close)	alternative_multiple_window_close();
        else	default_curtain_clicked();
	}
}

sku_handheld = {
	status: 0,  // 0 = ready, 1 = loading
	sku_item_id: 0,
	phpfile: \'ajax_autocomplete.php\',
	loading_indicator: undefined,
	ele_input: undefined,
	ele_qty: undefined,
	initial: function(ele_input){  // constructor, must pass in 1 input for enter code
		this.ele_input = ele_input;
		if($(\'inp_autocomplete_qty\'))	this.ele_qty = $(\'inp_autocomplete_qty\');
		
		$(this.ele_input).observe(\'keypress\', this.check_input);    // attach event listener to input enter code
		if(this.ele_qty)	$(this.ele_qty).observe(\'keypress\', this.check_qty_input);    // attach event listener to input enter code
		this.loading_indicator = $(\'span_autocomplete_loading\');
		if($(\'autocomplete_sku_indicator\')) this.loading_indicator = $(\'autocomplete_sku_indicator\');
	},
	show_loading_indicator: function(show){
		if(!this.loading_indicator) return;
		if(show)    $(this.loading_indicator).show();
		else    $(this.loading_indicator).hide();
	},
    check_input: function(event){   // event to handle when handheld receive character
		var kc = event.keyCode;
		if(kc==13){ // enter
			sku_handheld.send_code_to_check();
		}
	},
	check_qty_input: function(event){   // event to handle when user entered qty
    	var kc = event.keyCode;
    	if(kc==13){ // enter
			add_autocomplete(); // your script must have this function to handle event
		}
	},
	reset_status: function(){
		this.status = 0;
		this.sku_item_id = 0;
		$(\'sku_item_id\').value = \'\';
		this.ele_input.value = \'\';
		this.ele_input.focus();
		if(this.ele_qty)    this.ele_qty.value = \'\';
		this.show_loading_indicator(false);
	},
	send_code_to_check: function(){
		var str = this.ele_input.value.trim();  // get the value entered
		if(str==\'\') return;
		
		this.sku_item_id = 0;   // reset the selected sku item id
		this.status = 1; // mark status as loading
		sku_handheld.show_loading_indicator(true);  // show loading icons
		
		var param_str = "value="+str;
		if(dept_filter_name && sku_parent_form[dept_filter_name])    param_str += \'&dept_id=\'+sku_parent_form[dept_filter_name].value;  // add dept filter
		if (block_list)    param_str +="&block_list=1";
		if(skip_dept_filter)    param_str += \'&skip_dept_filter=1\'; // skip department filter
		if(fresh_market_filter)   param_str += \'&fresh_market_filter=\'+fresh_market_filter;    // only get fresh market sku
		if(is_parent_only)  param_str += \'&is_parent_only=1\';   // handheld will convert to use parent if found scanned code is child
		if(doc_block_type)  param_str += \'&doc_block_type=\'+doc_block_type;   // check block item in GRN
	
		ajax_request(this.phpfile+\'?a=ajax_search_sku_by_handheld\', {
			parameters:param_str,
			onComplete: function(e){
				var msg = e.responseText.trim();
				if(msg==\'no\'){  // no match found
					alert(\'No match found for"\'+str+\'"\');
                    sku_handheld.reset_status();
				}else{
					var sku = JSON.parse(msg);
					sku_handheld.sku_item_id = int(sku[\'id\']);
					sku_parent_form[\'sku_item_id\'].value = sku_handheld.sku_item_id;
					sku_handheld.ele_input.value = sku[\'description\'];
					if(sku_handheld.ele_qty)	sku_handheld.ele_qty.focus();
					sku_handheld.show_loading_indicator(false);
					
					if(sku[\'doc_allow_decimal\'] == 1){
						sku_parent_form[\'sku_autocomplete_qty\'].onchange = function(){ this.value = float(round(this.value, global_qty_decimal_points)); };
					}else{
						sku_parent_form[\'sku_autocomplete_qty\'].onchange = function(){ mi(this); };
						sku_parent_form[\'sku_autocomplete_qty\'].value = int($(\'inp_stock_take_direct_add_handheld_qty\').value);
					}
					
					if(sku[\'change_from_child_to_parent\']){ // prompt notice to user
					    new Effect.Opacity($(\'div_sku_autocomplete_popup_notification\'),{duration:0.2, to:1});
						$(\'div_sku_autocomplete_popup_notification\').update(\'Item automatically change to use parent SKU.\').show();
						setTimeout("hide_div_sku_autocomplete_popup_notification()", 3000);
					}
				}
			}
		});
	}  
}

hide_div_sku_autocomplete_popup_notification = function(){
    new Effect.Opacity($(\'div_sku_autocomplete_popup_notification\'),{duration:0.2, to:0,
		afterFinish: function() { $(\'div_sku_autocomplete_popup_notification\').hide(); }
	});
}
'; ?>

</script>

<div id=history_popup style="padding:5px;border:1px solid #000;overflow:hidden;width:300px;height:300px;position:absolute;background:#fff;display:none;">
<div style="text-align:right"><img src="/ui/closewin.png" onclick="Element.hide('history_popup')"></div>
<div id=history_popup_content></div>
</div>

<!-- multiple add div -->
<div id="div_multiple_add_popup" class="curtain_popup" style="position:absolute;z-index:10000;width:600px;height:450px;display:none;border:2px solid #CE0000;background-color:#FFFFFF;background-image:url(/ui/ndiv.jpg);background-repeat:repeat-x;padding: 0 !important;">
	<div id="div_multiple_add_popup_header" style="border:2px ridge #CE0000;color:white;background-color:#CE0000;padding:2px;cursor:default;"><span style="float:left;">Multiple Add SKU</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="multiple_window_close();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_multiple_add_popup_content" style="padding:2px;"></div>
</div>
<!-- end of multiple add div -->

<!-- Div for notification -->
<div id="div_sku_autocomplete_popup_notification" style="position:absolute;z-index:10100;width:400;line-height:20px;background:yellow;text-align:center;vertical-align:middle;left:200;top:200;display:none;">
</div>
<!-- End of Div for notification -->

<table>
<tr>
	<th>Search SKU</th>
	<td>
		<input id="sku_item_id" name="sku_item_id" size=3 type=hidden value="<?php echo $_REQUEST['sku_item_id']; ?>
">
		<input id="sku_item_code" name="sku_item_code" size=13 type=hidden value="<?php echo $_REQUEST['sku_item_code']; ?>
">
		<input id="autocomplete_sku" name="sku" size=50 onclick="this.select()" style="font-size:14px;width:500px;" value="<?php echo ((is_array($_tmp=$_REQUEST['sku'])) ? $this->_run_mod_handler('escape', true, $_tmp) : smarty_modifier_escape($_tmp)); ?>
">
		<?php if ($this->_tpl_vars['enable_handheld']): ?>
			<input id="inp_sku_handheld" name="sku_handheld" onclick="this.select()" style="font-size:14px;width:500px;" />
		<?php endif; ?>
		<?php if ($this->_tpl_vars['show_qty_input']): ?>
			<span id="span_autocomplete_qty">
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<b>Qty</b>
				<input id="inp_autocomplete_qty" name="sku_autocomplete_qty" onclick="this.select()" style="font-size:14px;width:50px;" />
			</span>
		<?php endif; ?>
		
		<?php if (! $this->_tpl_vars['no_add_button']): ?>
			<input type=button value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['_add_value'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Add') : smarty_modifier_default($_tmp, 'Add')); ?>
" onclick='add_autocomplete()'>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['_multiple_add_value']): ?>
			<input class="btn btn-primary" type=button value="<?php echo ((is_array($_tmp=@$this->_tpl_vars['_multiple_add_value'])) ? $this->_run_mod_handler('default', true, $_tmp, 'Multiple Add') : smarty_modifier_default($_tmp, 'Multiple Add')); ?>
" onclick='multiple_add_autocomplete()'>
		<?php endif; ?>
		<?php if ($this->_tpl_vars['show_parent']): ?>
			<input type=checkbox name="show_parent" id="show_parent_id" value="1" <?php if ($_REQUEST['show_parent'] == '1'): ?> checked <?php endif; ?>  >
			<label for=show_parent_id><b>Show parent</b></label>
		<?php endif; ?>		
		<?php if ($this->_tpl_vars['multiple_add']): ?>
		    <input class="btn btn-primary" type=button value="Multiple Add" onclick="open_multi_add()" id="btn_sku_autocomplete_multiple_add" />
		<?php endif; ?>
		<span id="span_autocomplete_loading" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
		<div id="autocomplete_sku_choices" class="autocomplete" style="display:none;height:150px !important;width:500px !important;overflow:auto !important;z-index:100"></div>
	</td>
	<td><!--<input type=submit value="Find">--></td>
</tr>
<tr>
	<td>&nbsp;</td>
	<td>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="1" <?php if (! $_REQUEST['search_type'] || $_REQUEST['search_type'] == '1'): ?>checked <?php endif; ?> > <label for="rad_st_1">MCode &amp; <?php echo $this->_tpl_vars['config']['link_code_name']; ?>
</label>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="2" <?php if ($_REQUEST['search_type'] == '2' || ( ! $_REQUEST['search_type'] && $this->_tpl_vars['config']['consignment_modules'] )): ?>checked <?php endif; ?>> <label for="rad_st_2">Article No</label>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="3" <?php if ($_REQUEST['search_type'] == '3'): ?>checked <?php endif; ?>> <label for="rad_st_3">ARMS Code</label>
		<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="4" <?php if ($_REQUEST['search_type'] == '4'): ?>checked <?php endif; ?>> <label for="rad_st_4">Description</label>
		<?php if ($this->_tpl_vars['enable_handheld']): ?>
			<input onchange="reset_sku_autocomplete()" type=radio name="search_type" value="5"> <label for="rad_st_5">Handheld</label>
		<?php endif; ?>
	</td>
</tr>
</table>


<script>
<?php echo '
new Draggable(\'div_multiple_add_popup\',{ handle: \'div_multiple_add_popup_header\'});

'; ?>

<?php if ($this->_tpl_vars['enable_handheld']): ?>
    sku_handheld.initial($('inp_sku_handheld'));
<?php endif; ?>
</script>
