<?php /* Smarty version 2.6.18, created on 2021-07-26 16:50:30
         compiled from adjustment.top_include.tpl */ ?>
<?php if ($this->_tpl_vars['adj_tab'] == 'setting'): ?>
    <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="fas fa-cog"></i> <?php echo $this->_tpl_vars['LNG']['CHANGE_SETTING']; ?>
</a>
<?php else: ?>
    <a href="adjustment.php?a=show_setting" class="btn btn-info btn-sm animated fadeInDown"><i class="fas fa-cog"></i> <?php echo $this->_tpl_vars['LNG']['CHANGE_SETTING']; ?>
</a>
<?php endif; ?>

<?php if (! $this->_tpl_vars['adj_tab'] || $this->_tpl_vars['adj_tab'] == 'scan_item'): ?>
    <a class="btn btn-light btn-sm disabled animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> <?php echo $this->_tpl_vars['LNG']['SCAN_ITEM']; ?>
</a>
<?php else: ?>
	<a href="adjustment.php" class="btn btn-indigo btn-sm animated fadeInDown"><i class="mdi mdi-barcode-scan"></i> <?php echo $this->_tpl_vars['LNG']['SCAN_ITEM']; ?>
</a>
<?php endif; ?>

<?php if ($this->_tpl_vars['adj_tab'] == 'view_items'): ?>
    <a class="btn btn-light btn-sm animated fadeInDown disabled"><i class="fas fa-th-list"></i> <?php echo $this->_tpl_vars['LNG']['VIEW_ITEMS_LIST']; ?>
</a>
<?php else: ?>
    <a href="adjustment.php?a=view_items" class="btn btn-success btn-sm animated fadeInDown"><i class="fas fa-th-list"></i> <?php echo $this->_tpl_vars['LNG']['VIEW_ITEMS_LIST']; ?>
</a>
<?php endif; ?>