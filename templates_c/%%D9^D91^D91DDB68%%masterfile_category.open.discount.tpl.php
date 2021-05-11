<?php /* Smarty version 2.6.18, created on 2021-05-10 16:52:49
         compiled from masterfile_category.open.discount.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'count', 'masterfile_category.open.discount.tpl', 14, false),)), $this); ?>
<?php if ($this->_tpl_vars['config']['consignment_modules']): ?><?php $this->assign('hide_branches', 1); ?><?php endif; ?>

<div id="div_category_discount_container-member" style="padding:2px;">
	<table class="report_table">
		<tr class="header">
			<th rowspan="2">&nbsp;</th>
			<th <?php if (! $this->_tpl_vars['hide_branches']): ?>colspan="<?php echo smarty_function_count(array('var' => $this->_tpl_vars['branch_list']), $this);?>
"<?php endif; ?>>Discount %
				<?php if (! $this->_tpl_vars['hide_branches']): ?>
					(set on individual branch to override "All")
				<?php endif; ?>
			</th>
		</tr>
		<tr class="header">
			<th>All
				<input type="checkbox" name="category_disc_by_branch[0][set_override]" value="1" <?php if ($this->_tpl_vars['form']['category_disc_by_branch']['0']['set_override']): ?>checked <?php endif; ?> title="Override" onChange="category_discount_branch_override_changed(0);" id="inp_category_disc_override-0" <?php if (! $this->_tpl_vars['is_edit'] || ! $this->_tpl_vars['sessioninfo']['privilege']['CATEGORY_DISCOUNT_EDIT']): ?>disabled <?php endif; ?> />
			</th>
			<?php if (! $this->_tpl_vars['hide_branches']): ?>
				<?php $_from = $this->_tpl_vars['branch_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['b']):
?>
					<?php if ($this->_tpl_vars['bid'] > 1): ?>
						<th><?php echo $this->_tpl_vars['b']['code']; ?>

							<input type="checkbox" name="category_disc_by_branch[<?php echo $this->_tpl_vars['bid']; ?>
][set_override]" value="1" <?php if ($this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']]['set_override']): ?>checked <?php endif; ?> title="Override" onChange="category_discount_branch_override_changed('<?php echo $this->_tpl_vars['bid']; ?>
');" id="inp_category_disc_override-<?php echo $this->_tpl_vars['bid']; ?>
" <?php if (! $this->_tpl_vars['is_edit'] || ! $this->_tpl_vars['sessioninfo']['privilege']['CATEGORY_DISCOUNT_EDIT']): ?>disabled <?php endif; ?> />
						</th>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
			<?php endif; ?>
		</tr>

		<!-- Member -->
		<tr>
			<td><b>Member</b></td>
			<td nowrap>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.input.tpl', 'smarty_include_vars' => array('bid' => 0,'member_col' => 'member','is_edit' => $this->_tpl_vars['is_edit'],'b_cat_disc' => $this->_tpl_vars['form']['category_disc_by_branch']['0'],'editable' => $this->_tpl_vars['form']['category_disc_by_branch']['0']['set_override'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</td>
			<?php if (! $this->_tpl_vars['hide_branches']): ?>
				<?php $_from = $this->_tpl_vars['branch_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['b']):
?>
					<?php if ($this->_tpl_vars['bid'] > 1): ?>
						<td nowrap>
							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.input.tpl', 'smarty_include_vars' => array('bid' => $this->_tpl_vars['bid'],'member_col' => 'member','is_edit' => $this->_tpl_vars['is_edit'],'b_cat_disc' => $this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']],'editable' => $this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']]['set_override'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						</td>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
			<?php endif; ?>
		</tr>
		
		<!-- Non-member -->
		<tr>
			<td><b>Non-Member</b></td>
			<td nowrap>
				<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.input.tpl', 'smarty_include_vars' => array('bid' => 0,'is_edit' => $this->_tpl_vars['is_edit'],'b_cat_disc' => $this->_tpl_vars['form']['category_disc_by_branch']['0'],'editable' => $this->_tpl_vars['form']['category_disc_by_branch']['0']['set_override'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
			</td>
			<?php if (! $this->_tpl_vars['hide_branches']): ?>
				<?php $_from = $this->_tpl_vars['branch_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['b']):
?>
					<?php if ($this->_tpl_vars['bid'] > 1): ?>
						<td nowrap>
							<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.input.tpl', 'smarty_include_vars' => array('bid' => $this->_tpl_vars['bid'],'is_edit' => $this->_tpl_vars['is_edit'],'b_cat_disc' => $this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']],'editable' => $this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']]['set_override'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
						</td>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
			<?php endif; ?>
		</tr>
		
		<!-- member type -->
		<?php $_from = $this->_tpl_vars['config']['membership_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['fmt'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['fmt']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['member_type'] => $this->_tpl_vars['mtype_desc']):
        $this->_foreach['fmt']['iteration']++;
?>
			<?php if (is_numeric ( $this->_tpl_vars['member_type'] )): ?>
				<?php $this->assign('mt', $this->_tpl_vars['mtype_desc']); ?>
			<?php else: ?>
				<?php $this->assign('mt', $this->_tpl_vars['member_type']); ?>
			<?php endif; ?>
			<?php if (($this->_foreach['fmt']['iteration'] <= 1)): ?>
				<tr>
					<td colspan="<?php if ($this->_tpl_vars['hide_branches']): ?>2<?php else: ?><?php echo smarty_function_count(array('var' => $this->_tpl_vars['branch_list'],'offset' => 2), $this);?>
<?php endif; ?>">Member Type (Leave empty will follow member)</td>
				</tr>
			<?php endif; ?>
			
			<tr>
				<td><b><?php echo $this->_tpl_vars['mtype_desc']; ?>
</b></td>
				<td nowrap>
					<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.input.tpl', 'smarty_include_vars' => array('bid' => 0,'is_edit' => $this->_tpl_vars['is_edit'],'member_col' => 'member','type_col' => $this->_tpl_vars['mt'],'b_cat_disc' => $this->_tpl_vars['form']['category_disc_by_branch']['0'],'editable' => $this->_tpl_vars['form']['category_disc_by_branch']['0']['set_override'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
				</td>
				<?php if (! $this->_tpl_vars['hide_branches']): ?>
					<?php $_from = $this->_tpl_vars['branch_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['b']):
?>
						<?php if ($this->_tpl_vars['bid'] > 1): ?>
							<td nowrap>
								<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => 'masterfile_category.open.discount.input.tpl', 'smarty_include_vars' => array('bid' => $this->_tpl_vars['bid'],'is_edit' => $this->_tpl_vars['is_edit'],'member_col' => 'member','type_col' => $this->_tpl_vars['mt'],'b_cat_disc' => $this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']],'editable' => $this->_tpl_vars['form']['category_disc_by_branch'][$this->_tpl_vars['bid']]['set_override'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
							</td>
						<?php endif; ?>
					<?php endforeach; endif; unset($_from); ?>
				<?php endif; ?>
			</tr>
		<?php endforeach; endif; unset($_from); ?>
	</table>
</div>