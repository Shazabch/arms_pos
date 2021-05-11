<?php /* Smarty version 2.6.18, created on 2021-05-10 17:42:30
         compiled from masterfile_vendor.vendor_portal.branch_bonus_table.tpl */ ?>

<div id="div_branch_bonus-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
">
	<table id="tbl_branch_bonus-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
" class="tbl_branch_bonus report_table" cellpadding="2" cellspacing="0">
		<tr class="tr_header2">
			<th width="50"><?php echo $this->_tpl_vars['m']; ?>
/<?php echo $this->_tpl_vars['y']; ?>
</th>
			<th width="120">Amount >=</th>
			<th width="50">% [<a href="javascript:void(alert('This % use for all category all sku.'))">?</a>]</th>
			<th>Other % [<a href="javascript:void(alert('This % can use to assign sepcified rate for certain category. \n* Please note global % will still be calculate and may cause overlaped result in total %.'))">?</a>]</th>
			<th><img src="ui/del.png" class="clickable" title="Delete Group" onClick="VENDOR_PORTAL.delete_branch_bonus_group_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
')" /></th>
		</tr>
		
		<tbody id="tbody_branch_bonus-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
">
			<?php $_from = $this->_tpl_vars['bonus_data_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fbonus'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fbonus']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['bonus_data']):
        $this->_foreach['fbonus']['iteration']++;
?>
				<?php $this->assign('row_no', $this->_foreach['fbonus']['iteration']); ?>
				
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "masterfile_vendor.vendor_portal.branch_bonus_row.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			<?php endforeach; endif; unset($_from); ?>
		</tbody>
	</table>
	<button style="background:#ece;border:1px solid #fff;border-right:1px solid #333; border-bottom:1px solid #333;" onClick="VENDOR_PORTAL.add_branch_bonus_row_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
');">+</button>
	
		<button onClick="VENDOR_PORTAL.add_branch_bonus_row_copy_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
');" id="btn_add_branch_bonus_row_copy-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
" class="btn_add_branch_bonus_row_copy">Copy</button>
	
		<button onClick="VENDOR_PORTAL.add_branch_bonus_row_paste_clicked('<?php echo $this->_tpl_vars['bid']; ?>
', '<?php echo $this->_tpl_vars['y']; ?>
', '<?php echo $this->_tpl_vars['m']; ?>
');" class="btn_add_branch_bonus_row_paste">Paste</button>
	
	<span id="span_branch_bonus_row_loading-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['y']; ?>
-<?php echo $this->_tpl_vars['m']; ?>
" style="padding:2px;background:yellow;display:none;"><img src="ui/clock.gif" align="absmiddle" /><br /> Loading...</span>
</div>