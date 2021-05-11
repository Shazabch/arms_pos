<?php /* Smarty version 2.6.18, created on 2021-05-10 17:42:30
         compiled from masterfile_vendor.vendor_portal.branch_bonus_row.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'number_format', 'masterfile_vendor.vendor_portal.branch_bonus_row.tpl', 14, false),array('modifier', 'ifzero', 'masterfile_vendor.vendor_portal.branch_bonus_row.tpl', 14, false),array('modifier', 'default', 'masterfile_vendor.vendor_portal.branch_bonus_row.tpl', 19, false),)), $this); ?>

<tr id="tr_branch_bonus_row-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" class="tr_branch_bonus_row">
	<td align="center">
		<img src="ui/del.png" title="Delete" class="clickable" onClick="VENDOR_PORTAL.delete_branch_bonus_row_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
','<?php echo $this->_tpl_vars['row_no']; ?>
');" />
	</td>
	<td nowrap align="center">
		<input type="text" size="10" name="sales_bonus_by_step[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['y']; ?>
][<?php echo $this->_tpl_vars['m']; ?>
][<?php echo $this->_tpl_vars['row_no']; ?>
][amt_from]" value="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['bonus_data']['amt_from'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2, ".", "") : number_format($_tmp, 2, ".", "")))) ? $this->_run_mod_handler('ifzero', true, $_tmp, '') : smarty_modifier_ifzero($_tmp, '')); ?>
" onChange="mfz(this);" class="inp_sales_bonus_by_step-amt_from required" title="Bonus Amount From" />
	</td>
	
		<td align="center">
		<input type="text" size="3" name="sales_bonus_by_step[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['y']; ?>
][<?php echo $this->_tpl_vars['m']; ?>
][<?php echo $this->_tpl_vars['row_no']; ?>
][bonus_per]" value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['bonus_data']['bonus_per'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2, ".", "") : number_format($_tmp, 2, ".", "")))) ? $this->_run_mod_handler('ifzero', true, $_tmp, '') : smarty_modifier_ifzero($_tmp, '')); ?>
" onChange="mfz(this);" title="Bonus %" />
	</td>
	
		<td>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>&nbsp;</th>
				<th>Type</th>
				<th>Info</th>
				<th>%</th>
			</tr>
			<tbody id="tbody_branch_bonus_row_breakdown-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
">
				<?php $_from = $this->_tpl_vars['bonus_data']['bonus_per_by_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fbonus_per_by_type'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fbonus_per_by_type']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['bonus_per_by_type']):
        $this->_foreach['fbonus_per_by_type']['iteration']++;
?>
					<?php $this->assign('type_row_no', $this->_foreach['fbonus_per_by_type']['iteration']); ?>
					
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_bonus_row.breakdown_percent_row.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php endforeach; endif; unset($_from); ?>
			</tbody>			
		</table>
		<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="VENDOR_PORTAL.add_branch_bonus_row_more_percent_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');">+</button>
				<button onClick="VENDOR_PORTAL.add_branch_bonus_row_more_percent_copy_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');" id="btn_add_branch_bonus_row_more_percent_copy-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" class="btn_add_branch_bonus_row_more_percent_copy">Copy</button>
	
				<button onClick="VENDOR_PORTAL.add_branch_bonus_row_more_percent_paste_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');" class="btn_add_branch_bonus_row_more_percent_paste">Paste</button>
	
		<span id="span_branch_bonus_row_breakdown_loading-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</td>
</tr>