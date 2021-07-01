<?php /* Smarty version 2.6.18, created on 2021-07-01 16:55:33
         compiled from adjustment.top_include.tpl */ ?>

<span class="small">
<?php if ($this->_tpl_vars['adj_tab'] == 'setting'): ?>
    [Change Setting]
<?php else: ?>
    <a href="adjustment.php?a=show_setting">[Change Setting]</a>
<?php endif; ?>

<?php if (! $this->_tpl_vars['adj_tab'] || $this->_tpl_vars['adj_tab'] == 'scan_item'): ?>
	[Scan Item]
<?php else: ?>
	<a href="adjustment.php">[Scan Item]</a>
<?php endif; ?>

<?php if ($this->_tpl_vars['adj_tab'] == 'view_items'): ?>
    [View Items List]
<?php else: ?>
    <a href="adjustment.php?a=view_items">[View Items List]</a>
<?php endif; ?>
</span>