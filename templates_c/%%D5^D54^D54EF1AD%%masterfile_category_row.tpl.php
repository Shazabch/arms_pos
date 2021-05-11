<?php /* Smarty version 2.6.18, created on 2021-05-10 16:52:32
         compiled from masterfile_category_row.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('function', 'config_load', 'masterfile_category_row.tpl', 15, false),array('modifier', 'default', 'masterfile_category_row.tpl', 23, false),array('modifier', 'number_format', 'masterfile_category_row.tpl', 34, false),array('modifier', 'ifzero', 'masterfile_category_row.tpl', 34, false),array('block', 'repeat', 'masterfile_category_row.tpl', 26, false),)), $this); ?>

<?php echo smarty_function_config_load(array('file' => "site.conf"), $this);?>

<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_CATEGORY']): ?>
<td bgcolor=<?php echo $this->_config[0]['vars']['TB_ROWHEADER']; ?>
 width=60 nowrap>
&nbsp;<a href="javascript:void(ed(<?php echo $this->_tpl_vars['category_row']['id']; ?>
))"><img src=ui/ed.png title="Edit" border=0></a>
<a href="javascript:void(act(<?php echo $this->_tpl_vars['category_row']['id']; ?>
,<?php if ($this->_tpl_vars['category_row']['active']): ?>0))"><img src=ui/deact.png title="Deactivate" border=0><?php else: ?>1))"><img src=ui/act.png title="Activate" border=0><?php endif; ?></a>
<a href="javascript:void(move(<?php echo $this->_tpl_vars['category_row']['id']; ?>
,'<?php echo $this->_tpl_vars['category_row']['tree_str']; ?>
', '<?php echo $this->_tpl_vars['category_row']['level']; ?>
', '<?php echo $this->_tpl_vars['category_row']['root_id']; ?>
'))"><img src=ui/move.png title="Move" border=0></a>
</td>
<?php endif; ?>
<td width=50><?php echo ((is_array($_tmp=@$this->_tpl_vars['category_row']['code'])) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>
</td>
<?php echo '<td>'; ?><?php $this->_tag_stack[] = array('repeat', array('n' => ($this->_tpl_vars['category_row']['level']-1))); $_block_repeat=true;smarty_block_repeat($this->_tag_stack[count($this->_tag_stack)-1][1], null, $this, $_block_repeat);while ($_block_repeat) { ob_start(); ?><?php echo '<img src=ui/pixel.gif width=24 height=1 align=absmiddle>'; ?><?php $_block_content = ob_get_contents(); ob_end_clean(); $_block_repeat=false;echo smarty_block_repeat($this->_tag_stack[count($this->_tag_stack)-1][1], $_block_content, $this, $_block_repeat); }  array_pop($this->_tag_stack); ?><?php echo '<img src=ui/tree_'; ?><?php if ($this->_sections['i']['last']): ?><?php echo 'e'; ?><?php else: ?><?php echo 'm'; ?><?php endif; ?><?php echo '.png align=absmiddle><a href="javascript:void(toggle_sub('; ?><?php echo $this->_tpl_vars['category_row']['id']; ?><?php echo '))">'; ?><?php echo $this->_tpl_vars['category_row']['description']; ?><?php echo '</a> <span style="color:#999999;" id="span_child_count-'; ?><?php echo $this->_tpl_vars['category_row']['id']; ?><?php echo '">('; ?><?php echo $this->_tpl_vars['category_row']['child_count']; ?><?php echo ')</span>'; ?><?php if ($this->_tpl_vars['sessioninfo']['privilege']['MST_CATEGORY']): ?><?php echo '<img src="ui/add_child.png" style="cursor:pointer" onclick="add('; ?><?php echo $this->_tpl_vars['category_row']['id']; ?><?php echo ',\''; ?><?php echo $this->_tpl_vars['category_row']['tree_str']; ?><?php echo '\',\''; ?><?php echo $this->_tpl_vars['category_row']['level']+1; ?><?php echo '\')" align=absmiddle title="create Sub-category">'; ?><?php endif; ?><?php echo '</td>'; ?>

<td width=50 align=right><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['category_row']['area'])) ? $this->_run_mod_handler('number_format', true, $_tmp, 2) : number_format($_tmp, 2)))) ? $this->_run_mod_handler('ifzero', true, $_tmp, "&nbsp;") : smarty_modifier_ifzero($_tmp, "&nbsp;")); ?>
</td>
<?php $_from = $this->_tpl_vars['sku_type']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['v']):
?>
<td width=50 align=center>
	<?php if ($this->_tpl_vars['category_row']['level'] <= 2): ?>
		<?php if ($this->_tpl_vars['category_row']['min_sku_photo'][$this->_tpl_vars['k']] == -1): ?>
			&nbsp;
		<?php elseif ($this->_tpl_vars['category_row']['min_sku_photo'][$this->_tpl_vars['k']] == 0): ?>
			<img src="/ui/deact.png" />
		<?php elseif ($this->_tpl_vars['category_row']['min_sku_photo'][$this->_tpl_vars['k']] > 0): ?>
			<img src="/ui/approved.png" /> <?php echo ((is_array($_tmp=@$this->_tpl_vars['category_row']['min_sku_photo'][$this->_tpl_vars['k']])) ? $this->_run_mod_handler('default', true, $_tmp, "&nbsp;") : smarty_modifier_default($_tmp, "&nbsp;")); ?>

		<?php endif; ?>
	<?php endif; ?>
</td>
<?php endforeach; endif; unset($_from); ?>
<td width=50 align=center><?php if ($this->_tpl_vars['category_row']['grn_po_qty']): ?><img src=/ui/approved.png><?php else: ?>&nbsp;<?php endif; ?></td>
<td width=50 align=center><?php if ($this->_tpl_vars['category_row']['grn_get_weight']): ?><img src=/ui/approved.png><?php else: ?>&nbsp;<?php endif; ?></td>