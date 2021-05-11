<?php /* Smarty version 2.6.18, created on 2021-05-10 16:55:38
         compiled from admin.config_manager.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'count', 'admin.config_manager.tpl', 184, false),array('function', 'var_export', 'admin.config_manager.tpl', 203, false),)), $this); ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'header.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<style>
<?php echo '
.section_name{
	background-color: #efefef;
	cursor:s-resize;
}
.section_name td{
    border-bottom:1px solid #999;
}
.error_msg{
	color: red;
}
.error_row{
	background: #ffd0f0;
}
.config_not_set{
	background-color:grey;
	color: #fff;
}
.config_row td{
      border-bottom: 1px solid #999;
}
'; ?>

</style>

<script type="text/javascript">
var phpself = '<?php echo $_SERVER['PHP_SELF']; ?>
';
var arr_config = [];
<?php $_from = $this->_tpl_vars['config_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section_name'] => $this->_tpl_vars['section']):
?>
    <?php $_from = $this->_tpl_vars['section']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['config_name'] => $this->_tpl_vars['config_data']):
?>
        arr_config.push('<?php echo $this->_tpl_vars['config_name']; ?>
');
    <?php endforeach; endif; unset($_from); ?>
<?php endforeach; endif; unset($_from); ?>

<?php echo '
function change_row_config_editable(ele, config_name){
	var c = ele.checked;
	
	$(\'div_setting-\'+config_name).getElementsBySelector("input", "textarea", "select").each(function(inp){
		$(inp).disabled = !c;
	});
}

function save_config(){
	if(!confirm(\'Are you sure?\'))   return false;
	
	// change button to prevent user click it again
	var btn_save = $(\'btn_save\');
	btn_save.value = \'Saving...\';
	btn_save.disabled = true;
	
	// construct params
	clear_config_error();
	var params = $(document.f_a).serialize();
	
	new Ajax.Request(phpself, {
		parameters: params,
		onComplete: function(e){
		    try{
		        // try to decode json
                eval(\'var json = \'+e.responseText);
                if(json[\'ok\']){
					alert(\'Save successfully.\');
					// reload window
					//window.location.reload(0);
				}else if(json[\'error\']){  // got error
					var err = json[\'error\'];
					for(var i=0; i<err.length; i++){
						var config_name = err[i][\'config_name\'];
						var error_msg = err[i][\'error_msg\'];
						$(\'div_setting_error-\'+config_name).update(error_msg);
						$(\'tr_config_row-\'+config_name).addClassName(\'error_row\');
					}
					alert(\'Save Failed! Please correct the error and save again.\')
				}
			}catch(ex){
			    // failed to decode json
				alert(e.responseText);
			}
			
			// enable back the button
			btn_save.disabled = false;
			btn_save.value = \'Save\';
		}
	})
}

function clear_config_error(){
	// remove row error color
	$$(\'#tbl_config tr.config_row\').invoke(\'removeClassName\', \'error_row\');
	// remove row error message
	$$(\'#tbl_config div.error_msg\').invoke(\'update\', \'\');
}

function toggle_all_config(show){
	// image location
	var img_src = \'ui/\'+(show ? \'collapse\' : \'expand\')+\'.gif\';

	// change image src
	$$(\'#tbl_config img.img_section\').each(function(img){
		img.src = img_src;
	});
	
	// show/hide tbody
	$$(\'#tbl_config tbody.tbody_config\').invoke((show ? \'show\' : \'hide\'));
}

function searh_cf_name(){
	// clear highlight row color
	$$(\'#tbl_config tr.config_row\').invoke(\'removeClassName\', \'highlight_row\');
	
	// check search
	var cf_name = $(\'inp_search_cf_name\').value.trim();
	if(!cf_name)    return; // empty
	
	// try to get the <tr> row element
	var tr = $(\'tr_config_row-\'+cf_name);

	if(!tr){    // row not found
		alert(\'Config Name \\\'\'+cf_name+\'\\\' cannot be found.\');
		return false;
	}else{  // row found
	    // get parent tbody
	    var parent_tbody = $(tr).parentNode;
	    var section_name = $(parent_tbody).id.split(\'-\')[1];

		// change section img
		$(\'img_section-\'+section_name).src=\'ui/collapse.gif\';
	    $(parent_tbody).show(); // show whole tbody
	    
	    // show highlight color
		$(tr).addClassName(\'highlight_row\').scrollTo();
	}
}

function initial_config_autocomplete(){
	var a = new Autocompleter.Local(\'inp_search_cf_name\', \'div_search_cf_name_list\', arr_config);
}
'; ?>

</script>
<h1><?php echo $this->_tpl_vars['PAGE_TITLE']; ?>
</h1>

<form name="f_search_cf" onSubmit="searh_cf_name();return false;">
	<b>Config Name: </b>
	<input type="text" id="inp_search_cf_name" style="width:400px;" />
	<input type="submit" value="Find" />
	<div id="div_search_cf_name_list" class="autocomplete" style="display:none;height:150px !important;width:400px !important;overflow:auto !important;z-index:100"></div>
</form>

<a href="javascript:void(toggle_all_config(1));">Expand All</a> |
<a href="javascript:void(toggle_all_config(0));">Collapse All</a>

<form name="f_a" method="post">
	<input type="hidden" name="a" value="save_config" onSubmit="return false;" />
	
<div style="border:0px solid black;">
<table width="100%" cellpadding="4" cellspacing="1" border="0" style="padding:2px" id="tbl_config">
	<thead bgcolor="#ffee99">
	    <tr>
	        <th>Config Name</th>
	        <th>Value in config.php</th>
	        <th>Active <a href="javascript:void(alert('If no tick, it will follow config.php'));"><img src="/ui/icons/information.png" align="absmiddle" border="0" /></a><br />(override config.php)</th>
	        <th>Settings</th>
	        <th>Sample Value</th>
	        <th>Default Value</th>
	    </tr>
	</thead>
	<?php $_from = $this->_tpl_vars['config_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['section_name'] => $this->_tpl_vars['section']):
?>
	    <tr class="section_name" id="tr_section_row-<?php echo $this->_tpl_vars['section_name']; ?>
">
	        <td colspan="6" onClick="togglediv('tbody_config_section-<?php echo $this->_tpl_vars['section_name']; ?>
', 'img_section-<?php echo $this->_tpl_vars['section_name']; ?>
');">
				<h4 style="margin:0">
				    <img src="ui/expand.gif" border="0" title="Expand/Collapse" class="img_section" id="img_section-<?php echo $this->_tpl_vars['section_name']; ?>
" />
					<?php echo $this->_tpl_vars['section_name']; ?>

					(<?php echo smarty_function_count(array('var' => $this->_tpl_vars['section']), $this);?>
)
				</h4>
			</td>
	    </tr>
	    <tbody style="background-color:#fff;display:none;" id="tbody_config_section-<?php echo $this->_tpl_vars['section_name']; ?>
" class="tbody_config">
	        <?php $_from = $this->_tpl_vars['section']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['config_name'] => $this->_tpl_vars['config_data']):
?>
	            <?php $this->assign('is_disabled', 'disabled'); ?>
	            <?php if ($this->_tpl_vars['config_master'][$this->_tpl_vars['config_name']]['active']): ?><?php $this->assign('is_disabled', ''); ?><?php endif; ?>
	                    
	            <tr id="tr_config_row-<?php echo $this->_tpl_vars['config_name']; ?>
" class="config_row thover">
	                <td><b class="b_config_name"><?php echo $this->_tpl_vars['config_name']; ?>
</b>
                        <?php if ($this->_tpl_vars['config_data']['description']): ?><br /><span style="color:#6f6f6f;"><?php echo $this->_tpl_vars['config_data']['description']; ?>
</span><?php endif; ?>
					</td>
					
					<!-- Value in config.php -->
	                <td  valign="top" class="<?php if (! isset ( $this->_tpl_vars['default_config'][$this->_tpl_vars['config_name']] )): ?>config_not_set<?php endif; ?>">
	                    <?php if (isset ( $this->_tpl_vars['default_config'][$this->_tpl_vars['config_name']] )): ?>
	                        &nbsp;
		                    <?php if ($this->_tpl_vars['config_data']['type'] == 'array'): ?>
		                        <pre><?php echo smarty_var_export(array('var' => $this->_tpl_vars['default_config'][$this->_tpl_vars['config_name']]), $this);?>
</pre>
	                        <?php else: ?>
	                            <?php echo $this->_tpl_vars['default_config'][$this->_tpl_vars['config_name']]; ?>

		                    <?php endif; ?>
	                    <?php else: ?>
	                        <i>-- Not set --</i>
	                    <?php endif; ?>
	                </td>
	                
	                <td style="text-align:center;">
	                		                	<input type="hidden" name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][config_name]" value="<?php echo $this->_tpl_vars['config_name']; ?>
" />
	                	
	                							<input type="checkbox" name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][active]" value="1" onChange="change_row_config_editable(this, '<?php echo $this->_tpl_vars['config_name']; ?>
');" <?php if (! $this->_tpl_vars['is_disabled']): ?>checked <?php endif; ?> />
					</td>
					
					<!-- Settings -->
	                <td  valign="top">
	                    <div id="div_setting-<?php echo $this->_tpl_vars['config_name']; ?>
">
	                        <input type="hidden" name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][type]" value="<?php echo $this->_tpl_vars['config_data']['type']; ?>
" <?php echo $this->_tpl_vars['is_disabled']; ?>
 />
		                    <!-- Radio -->
		                    <?php if ($this->_tpl_vars['config_data']['type'] == 'radio'): ?>
		                        <?php $_from = $this->_tpl_vars['config_data']['value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['v'] => $this->_tpl_vars['label']):
?>
		                            <input type="radio" name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][value]" value="<?php echo $this->_tpl_vars['v']; ?>
" <?php if ($this->_tpl_vars['config_master'][$this->_tpl_vars['config_name']]['value'] == $this->_tpl_vars['v']): ?>checked <?php endif; ?> <?php echo $this->_tpl_vars['is_disabled']; ?>
 /> <?php echo $this->_tpl_vars['label']; ?>

		                        <?php endforeach; endif; unset($_from); ?>
							<?php elseif ($this->_tpl_vars['config_data']['type'] == 'array'): ?>
							    <!-- Array -->
							    <textarea name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][value]" style="width:200px;height:200px;" <?php echo $this->_tpl_vars['is_disabled']; ?>
><?php echo $this->_tpl_vars['config_master'][$this->_tpl_vars['config_name']]['value']; ?>
</textarea>
							<?php elseif ($this->_tpl_vars['config_data']['type'] == 'str'): ?>
							    <!-- Str -->
							    <input name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][value]" style="width:200px;" value="<?php echo $this->_tpl_vars['config_master'][$this->_tpl_vars['config_name']]['value']; ?>
" <?php echo $this->_tpl_vars['is_disabled']; ?>
 />
							 <?php elseif ($this->_tpl_vars['config_data']['type'] == 'select'): ?>
							    <!-- Select -->
							    <select name="config_master[<?php echo $this->_tpl_vars['config_name']; ?>
][value]" <?php echo $this->_tpl_vars['is_disabled']; ?>
>
							        <?php $_from = $this->_tpl_vars['config_data']['value']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['v'] => $this->_tpl_vars['label']):
?>
							            <option value="<?php echo $this->_tpl_vars['v']; ?>
" <?php if ($this->_tpl_vars['config_master'][$this->_tpl_vars['config_name']]['value'] == $this->_tpl_vars['v']): ?>selected <?php endif; ?>><?php echo $this->_tpl_vars['label']; ?>
</option>
							        <?php endforeach; endif; unset($_from); ?>
							    </select>
		                    <?php endif; ?>
	                    </div>
	                    <div id="div_setting_error-<?php echo $this->_tpl_vars['config_name']; ?>
" class="error_msg"></div>
	                </td>
	                
	                <!-- Sample -->
	                <td valign="top">
	                    <?php if ($this->_tpl_vars['config_data']['default_info']['sample']): ?>
	                        <pre>
	                        <?php echo $this->_tpl_vars['config_data']['default_info']['sample']; ?>

	                        </pre>
						<?php else: ?>&nbsp;
	                    <?php endif; ?>
	                </td>
	                
	                	                <td valign="top" class="<?php if (! $this->_tpl_vars['config_data']['default_info']['default']): ?>config_not_set<?php endif; ?>">
	                	<?php if ($this->_tpl_vars['config_data']['default_info']['default']): ?>
	                		 <pre>
	                        <?php echo $this->_tpl_vars['config_data']['default_info']['default']; ?>

	                        </pre>
						<?php else: ?>
							<i>-- No default value --</i>
	                	<?php endif; ?>
	                </td>
	            </tr>
	        <?php endforeach; endif; unset($_from); ?>
	    </tbody>
	<?php endforeach; endif; unset($_from); ?>
</table>
</div>
</form>

<div style="position:fixed;bottom:0;background:#ddd;width:100%;text-align:center;left:0;padding:3px;opacity:0.8;">
		<input type="button" id="btn_save" value="Save" onClick="save_config();" />
</div>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'footer.tpl', 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script>
initial_config_autocomplete();
</script>