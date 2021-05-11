<?php /* Smarty version 2.6.18, created on 2021-05-10 16:52:49
         compiled from masterfile_category.open.discount.input.tpl */ ?>

<?php if (! $this->_tpl_vars['member_col']): ?><?php $this->assign('member_col', 'nonmember'); ?><?php endif; ?>
<?php if (! $this->_tpl_vars['type_col']): ?><?php $this->assign('type_col', 'global'); ?><?php endif; ?>

<?php if ($this->_tpl_vars['is_edit'] && $this->_tpl_vars['sessioninfo']['privilege']['CATEGORY_DISCOUNT_EDIT']): ?>
	<input type="text" name="category_disc_by_branch[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['member_col']; ?>
][<?php echo $this->_tpl_vars['type_col']; ?>
]" value="<?php echo $this->_tpl_vars['b_cat_disc'][$this->_tpl_vars['member_col']][$this->_tpl_vars['type_col']]; ?>
" size="5" maxlength="6" id="inp-cat_disc_value-<?php echo $this->_tpl_vars['member_col']; ?>
-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['type_col']; ?>
" onChange="cat_disc_value_changed(this);" <?php if (! $this->_tpl_vars['editable']): ?>disabled <?php endif; ?> class="inp_category_disc-<?php echo $this->_tpl_vars['bid']; ?>
" /> %
<?php else: ?>
	<?php if (isset ( $this->_tpl_vars['b_cat_disc'][$this->_tpl_vars['member_col']][$this->_tpl_vars['type_col']] ) && $this->_tpl_vars['b_cat_disc'][$this->_tpl_vars['member_col']][$this->_tpl_vars['type_col']] !== ''): ?>
		<?php echo $this->_tpl_vars['b_cat_disc'][$this->_tpl_vars['member_col']][$this->_tpl_vars['type_col']]; ?>
 %
		<input type="hidden" name="category_disc_by_branch[<?php echo $this->_tpl_vars['bid']; ?>
][<?php echo $this->_tpl_vars['member_col']; ?>
][<?php echo $this->_tpl_vars['type_col']; ?>
]" value="<?php echo $this->_tpl_vars['b_cat_disc'][$this->_tpl_vars['member_col']][$this->_tpl_vars['type_col']]; ?>
" id="inp-cat_disc_value-<?php echo $this->_tpl_vars['member_col']; ?>
-<?php echo $this->_tpl_vars['bid']; ?>
-<?php echo $this->_tpl_vars['type_col']; ?>
" />
	<?php endif; ?>
<?php endif; ?>