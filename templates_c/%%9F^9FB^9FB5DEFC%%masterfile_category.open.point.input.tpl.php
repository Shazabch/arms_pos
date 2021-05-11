<?php /* Smarty version 2.6.18, created on 2021-05-10 16:52:49
         compiled from masterfile_category.open.point.input.tpl */ ?>

<?php if (! $this->_tpl_vars['type_col']): ?><?php $this->assign('type_col', 'global'); ?><?php endif; ?>

<?php if ($this->_tpl_vars['is_edit'] && $this->_tpl_vars['sessioninfo']['privilege']['MEMBER_POINT_REWARD_EDIT']): ?>
	<input type="text" name="category_point_by_branch[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['type_col']; ?>
]" value="<?php echo $this->_tpl_vars['b_cat_point'][$this->_tpl_vars['type_col']]; ?>
" size="3" id="inp-category_point_value-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['type_col']; ?>
" onChange="category_point_value_changed(this);"  class="inp_category_point-<?php echo $this->_tpl_vars['bid']; ?>
" <?php if (! $this->_tpl_vars['editable']): ?>disabled <?php endif; ?> />
<?php else: ?>
	<?php echo $this->_tpl_vars['b_cat_point'][$this->_tpl_vars['type_col']]; ?>

	<input type="hidden" name="category_point_by_branch[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['type_col']; ?>
]" value="<?php echo $this->_tpl_vars['b_cat_point'][$this->_tpl_vars['type_col']]; ?>
" id="inp-category_point_value-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['type_col']; ?>
" />
<?php endif; ?>