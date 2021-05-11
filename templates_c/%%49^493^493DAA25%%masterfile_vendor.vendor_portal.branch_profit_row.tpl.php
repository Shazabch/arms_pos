<?php /* Smarty version 2.6.18, created on 2021-05-10 17:42:30
         compiled from masterfile_vendor.vendor_portal.branch_profit_row.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'default', 'masterfile_vendor.vendor_portal.branch_profit_row.tpl', 20, false),array('modifier', 'number_format', 'masterfile_vendor.vendor_portal.branch_profit_row.tpl', 20, false),array('modifier', 'ifzero', 'masterfile_vendor.vendor_portal.branch_profit_row.tpl', 20, false),)), $this); ?>

<tr id="tr_branch_profit_row-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" class="tr_branch_profit_row" valign="top">
	<td align="center">
		<img src="ui/del.png" title="Delete" class="clickable" onClick="VENDOR_PORTAL.delete_branch_profit_row_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');" />
	</td>
	<td nowrap align="center">
		<input type="text" size="10" id="inp_profit_date_to-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" name="sales_report_profit_by_date[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['row_no']; ?>
][date_to]" value="<?php echo $this->_tpl_vars['profit_data']['date_to']; ?>
" readonly class="inp_profit_date_to required" title="Report Profit Date To" />
		<img align="absmiddle" src="ui/calendar.gif" id="img_profit_date_to-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" style="cursor: pointer;" title="Select Date" />
	</td>
	
		<td align="center">
		<input type="text" size="3" name="sales_report_profit_by_date[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['row_no']; ?>
][profit_per]" value="<?php echo ((is_array($_tmp=((is_array($_tmp=((is_array($_tmp=@$this->_tpl_vars['profit_data']['profit_per'])) ? $this->_run_mod_handler('default', true, $_tmp, 0) : smarty_modifier_default($_tmp, 0)))) ? $this->_run_mod_handler('number_format', true, $_tmp, 2, ".", "") : number_format($_tmp, 2, ".", "")))) ? $this->_run_mod_handler('ifzero', true, $_tmp, '') : smarty_modifier_ifzero($_tmp, '')); ?>
" onChange="mfz(this);" class="required" title="Report Profit %" />
	</td>
	
		<td>
		<table width="100%" class="report_table">
			<tr class="header">
				<th>&nbsp;</th>
				<th>Type</th>
				<th>Info [<a href="javascript:void(alert('SKU: ARMS Code/MCode/ArtNo\nCATEGORY: Description '))">?</a>]</th>
				<th>%</th>
			</tr>
			<tbody id="tbody_branch_profit_row_breakdown-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
">
				<?php $_from = $this->_tpl_vars['profit_data']['profit_per_by_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fprofit_per_by_type'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fprofit_per_by_type']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['profit_per_by_type']):
        $this->_foreach['fprofit_per_by_type']['iteration']++;
?>
					<?php $this->assign('type_row_no', $this->_foreach['fprofit_per_by_type']['iteration']); ?>
					
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_profit_row.breakdown_percent_row.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				<?php endforeach; endif; unset($_from); ?>
			</tbody>			
		</table>
		<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="VENDOR_PORTAL.add_branch_profit_row_more_percent_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');">+</button>
		<button onClick="VENDOR_PORTAL.add_branch_profit_row_more_percent_copy_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');" id="btn_add_branch_profit_row_more_percent_copy-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" class="btn_add_branch_profit_row_more_percent_copy">Copy</button>
		<button onClick="VENDOR_PORTAL.add_branch_profit_row_more_percent_paste_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['row_no']; ?>
');" class="btn_add_branch_profit_row_more_percent_paste">Paste</button>
		
		<span id="span_branch_profit_row_breakdown_loading-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['row_no']; ?>
" style="padding:2px;background:yellow;display:none;"><br /><img src="ui/clock.gif" align="absmiddle" /> Loading...</span>
	</td>
</tr>