<?php /* Smarty version 2.6.18, created on 2021-05-11 18:24:32
         compiled from notifications_left_sidebar.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'number_format', 'notifications_left_sidebar.tpl', 55, false),array('modifier', 'urlencode', 'notifications_left_sidebar.tpl', 74, false),array('modifier', 'escape', 'notifications_left_sidebar.tpl', 74, false),array('modifier', 'string_format', 'notifications_left_sidebar.tpl', 316, false),)), $this); ?>

<!-- membership notification -->
<?php if ($this->_tpl_vars['membership']): ?>
<h5>
<i class="icofont-users-alt-2 icofont"></i> <a href="membership.php?t=verify">Membership Verification</a></h5>
<div class=ntc>There are records to be verified.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['membership']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li> <a href="membership.php?t=verify&branch_id=<?php echo $this->_tpl_vars['membership'][$this->_sections['i']['index']]['branch_id']; ?>
"><?php echo $this->_tpl_vars['membership'][$this->_sections['i']['index']]['branch_code']; ?>
</a> (<?php echo $this->_tpl_vars['membership'][$this->_sections['i']['index']]['count']; ?>
)
<?php endfor; endif; ?>
</ul>
<?php endif; ?>

<!-- membership blocked -->
<?php if ($this->_tpl_vars['membership_blocked']): ?>
<h5>
<i class="icofont-ui-block icofont"></i>
<a href="membership.php?t=verify">Blocked Membership</a></h5>
<div class=ntc>The following membership are blocked.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['membership_blocked']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li> <a href="membership.listing.php?branch_id=<?php echo $this->_tpl_vars['membership_blocked'][$this->_sections['i']['index']]['branch_id']; ?>
"><?php echo $this->_tpl_vars['membership_blocked'][$this->_sections['i']['index']]['branch_code']; ?>
</a> (<?php echo $this->_tpl_vars['membership_blocked'][$this->_sections['i']['index']]['count']; ?>
)
<?php endfor; endif; ?>
</ul>
<?php endif; ?>

<!-- Membership Summary -->
<?php if ($this->_tpl_vars['sessioninfo']['privilege']['MEMBERSHIP_SUMM'] && $this->_tpl_vars['membership_summary']): ?>
	<h5>
		<i class="icofont-users-alt-2 icofont"></i>
	Membership Summary</h5>
	<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	    <li> <a href="membership.listing.php">Total Member</a> (<?php echo ((is_array($_tmp=$this->_tpl_vars['membership_summary']['total'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)</li>
	    <li> <a href="membership.listing.php?verified=1&blocked=0&terminated=0">Total Verified Member</a> (<?php echo ((is_array($_tmp=$this->_tpl_vars['membership_summary']['verified'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)</li>
	    <li> <a href="membership.listing.php?verified=0&blocked=0&terminated=0">Total Unverified Member</a> (<?php echo ((is_array($_tmp=$this->_tpl_vars['membership_summary']['unverified'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)</li>
	    <li> <a href="membership.listing.php?blocked=1&terminated=0">Total Blocked Member</a> (<?php echo ((is_array($_tmp=$this->_tpl_vars['membership_summary']['blocked'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)</li>
	    <li> <a href="membership.listing.php?terminated=1">Total Terminated Member</a> (<?php echo ((is_array($_tmp=$this->_tpl_vars['membership_summary']['terminated'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)</li>
	</ul>
<?php endif; ?>

<!-- redemption notification -->
<?php if ($this->_tpl_vars['membership_redemption']): ?>
<h5>
<i class="icofont-gift-box icofont"></i>
<a href="membership.redemption_history.php?t=1&do_verify=1">Redemption Verification</a></h5>
<div class=ntc>Following redemption item from different branches require verify.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<br>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['membership_redemption']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<?php $this->assign('md_redir', "membership.redemption_history.php?do_verify=1"); ?>
<li> <a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['membership_redemption'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=<?php echo ((is_array($_tmp=$this->_tpl_vars['md_redir'])) ? $this->_run_mod_handler('escape', true, $_tmp, 'html') : smarty_modifier_escape($_tmp, 'html')); ?>
"><?php echo $this->_tpl_vars['membership_redemption'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['membership_redemption'][$this->_sections['i']['index']]['count']; ?>
)</a>
<?php endfor; endif; ?>
</ul>
<?php endif; ?>

<?php if ($this->_tpl_vars['membership_item_cfrm']): ?>
<h5>
<i class="icofont-gift-box icofont"></i>
<a href="membership.redemption_item_approval.php">Redemption Item Approval</a></h5>
<div class=ntc>Following redemption item require to approval.</div>
<ul style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<br>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['membership_item_cfrm']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li> <a href="membership.redemption_item_approval.php?branch_id=<?php echo $this->_tpl_vars['membership_item_cfrm'][$this->_sections['i']['index']]['branch_id']; ?>
"><?php echo $this->_tpl_vars['membership_item_cfrm'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['membership_item_cfrm'][$this->_sections['i']['index']]['count']; ?>
)</a>
<?php endfor; endif; ?>
</ul>
<?php endif; ?>

<!-- SKU approval notification -->
<?php if ($this->_tpl_vars['sku_approvals']): ?>
<h5>
SKU Applications</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="masterfile_sku_approval.php">You have <?php echo $this->_tpl_vars['sku_approvals']; ?>
 SKU Application waiting for approval.</a></p>
</div>
<?php endif; ?>

<!-- DO approval notification -->

<!-- ADJUSTMENT approval notification -->

<!-- MKT3 approval notification -->
<?php if ($this->_tpl_vars['mkt3_approvals']): ?>
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> MKT3</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="mkt3_approval.php">You have <?php echo $this->_tpl_vars['mkt3_approvals']; ?>
 MKT3 waiting for approval.</a></p>
</div>
<?php endif; ?>

<!-- MKT5 approval notification -->
<?php if ($this->_tpl_vars['mkt5_approvals']): ?>
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> MKT5</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="mkt5_approval.php">You have <?php echo $this->_tpl_vars['mkt5_approvals']; ?>
 MKT5 waiting for approval.</a></p>
</div>
<?php endif; ?>

<!-- MKT1 approval notification -->
<?php if ($this->_tpl_vars['mkt1_approvals']): ?>
<h5><img src=/ui/notify_sku_new.png align=absmiddle border=0> MKT1</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p><a href="mkt1_approval.php">You have <?php echo $this->_tpl_vars['mkt1_approvals']; ?>
 MKT1 waiting for approval.</a></p>
</div>
<?php endif; ?>

<!-- PO approval notification -->
<?php if ($this->_tpl_vars['po_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Purchase Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have PO waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['po_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li>
<a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['po_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=po_approval.php">
<?php echo $this->_tpl_vars['po_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['po_approvals'][$this->_sections['i']['index']]['count']; ?>
)
</a>
<!--<li><a href="login.php?server=<?php echo $this->_tpl_vars['po_approvals'][$this->_sections['i']['index']]['code']; ?>
&redir=po_approval.php">NEW <?php echo $this->_tpl_vars['po_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['po_approvals'][$this->_sections['i']['index']]['count']; ?>
)
</a>-->
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['promotion_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Promotion</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Promotion waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['promotion_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li>
<a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['promotion_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=promotion_approval.php">
<?php echo $this->_tpl_vars['promotion_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['promotion_approvals'][$this->_sections['i']['index']]['count']; ?>
)
</a>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- ADJ approval notification -->
<?php if ($this->_tpl_vars['adj_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Adjustment</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Adjustment waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['adj_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<?php if ($this->_tpl_vars['config']['adjustment_branch_selection']): ?>
	<?php if (BRANCH_CODE == 'HQ'): ?>
		<li><a href="adjustment_approval.php?branch_id=<?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['branch_id']; ?>
"><?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['count']; ?>
)</a></li>
	<?php else: ?>
		<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=adjustment_approval.php?branch_id=<?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['branch_id']; ?>
"><?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['count']; ?>
)</a></li>
	<?php endif; ?>
<?php else: ?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=adjustment_approval.php"><?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['adj_approvals'][$this->_sections['i']['index']]['count']; ?>
)
	</a></li>
<?php endif; ?>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- DO approval notification -->
<?php if ($this->_tpl_vars['do_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Delivery Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Delivery Order waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['do_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<?php if ($this->_tpl_vars['config']['consignment_modules']): ?>
    <li><a href="do_approval.php?branch_id=<?php echo $this->_tpl_vars['do_approvals'][$this->_sections['i']['index']]['branch_id']; ?>
"><?php echo $this->_tpl_vars['do_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['do_approvals'][$this->_sections['i']['index']]['count']; ?>
)</a></li>
<?php else: ?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['do_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=do_approval.php"><?php echo $this->_tpl_vars['do_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['do_approvals'][$this->_sections['i']['index']]['count']; ?>
)</a></li>
<?php endif; ?>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- Sales Order approval notification -->
<?php if ($this->_tpl_vars['so_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Sales Order</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Sales Order waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['so_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['so_approvals'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=sales_order_approval.php"><?php echo $this->_tpl_vars['so_approvals'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['so_approvals'][$this->_sections['i']['index']]['count']; ?>
)
	</a></li>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- DO Request notification -->
<?php if ($this->_tpl_vars['do_request']): ?>
<h5>
<i class="icofont-tags icofont"></i> DO Request</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have DO Request waiting for process.<br>
<ul>
<?php $_from = $this->_tpl_vars['do_request']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
	<li><a href="do_request.process.php?branch_id=<?php echo $this->_tpl_vars['r']['branch_id']; ?>
"><?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<?php echo $this->_tpl_vars['r']['item_count']; ?>
)</a></li>
<?php endforeach; endif; unset($_from); ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- CI approval notification -->
<?php if ($this->_tpl_vars['ci_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Consignment Invoice</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Consignment Invoice waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['ci_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['ci_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=consignment_invoice_approval.php"><?php echo $this->_tpl_vars['ci_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['ci_approvals'][$this->_sections['i']['index']]['count']; ?>
)
</a>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- GRN account notification -->
<?php if ($this->_tpl_vars['grn_account_verify']): ?>
<h5>
<i class="icofont-tags icofont"></i> GRN (Account Verification)</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have GRN waiting for verification.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['grn_account_verify']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['grn_account_verify'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=goods_receiving_note_approval.account.php"><?php echo $this->_tpl_vars['grn_account_verify'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['grn_account_verify'][$this->_sections['i']['index']]['count']; ?>
)
</a>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- GRN approval notification -->
<?php if ($this->_tpl_vars['grn_confirmations'] || $this->_tpl_vars['grn_approvals']): ?>
	<h5>
		<i class="icofont-tags icofont"></i> GRN</h5>
	<?php if ($this->_tpl_vars['grn_confirmations']): ?>
		<p><h5><i class="icofont-check-circled icofont"></i>Confirmation:</h5></p>
		<div class=ntc>The following GRN requires your confirmation.</div>
		<?php $_from = $this->_tpl_vars['grn_count_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }$this->_foreach['grn_count'] = array('total' => count($_from), 'iteration' => 0);
if ($this->_foreach['grn_count']['total'] > 0):
    foreach ($_from as $this->_tpl_vars['r'] => $this->_tpl_vars['count']):
        $this->_foreach['grn_count']['iteration']++;
?>
			<?php if ($this->_tpl_vars['r'] == 'doc_pending'): ?>
				Document Pending
			<?php elseif ($this->_tpl_vars['r'] == 'acc_verify'): ?>
				Account Verification
			<?php else: ?>
				SKU Manage
			<?php endif; ?>
			: <?php echo $this->_tpl_vars['count']; ?>
 record(s)
			<?php if (! ($this->_foreach['grn_count']['iteration'] == $this->_foreach['grn_count']['total'])): ?><br /><?php endif; ?>
		<?php endforeach; endif; unset($_from); ?>
		<div style="border:1px solid #ccc;padding:5px;height:200px;overflow:auto;">
		<?php $_from = $this->_tpl_vars['grn_confirmations']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['row'] => $this->_tpl_vars['grn']):
?>
			<div style="border-bottom:1px solid #eee"> 
				<a href="/goods_receiving_note.php?a=open&id=<?php echo $this->_tpl_vars['grn']['grn_id']; ?>
&branch_id=<?php echo $this->_tpl_vars['grn']['branch_id']; ?>
&action=<?php echo $this->_tpl_vars['grn']['action']; ?>
">
					<?php echo $this->_tpl_vars['grn']['report_prefix']; ?>
<?php echo ((is_array($_tmp=$this->_tpl_vars['grn']['grn_id'])) ? $this->_run_mod_handler('string_format', true, $_tmp, "%05d") : smarty_modifier_string_format($_tmp, "%05d")); ?>
 - 
					<?php if ($this->_tpl_vars['grn']['action'] == 'verify'): ?>
						SKU Manage
					<?php elseif ($this->_tpl_vars['grn']['action'] == 'grr_edit'): ?>
						Pending Document
					<?php else: ?>
						Account Verification
					<?php endif; ?>
				</a>
				<br>
				<font color=#666666 class=small>Received Date : <?php echo $this->_tpl_vars['grn']['rcv_date']; ?>
<br></font>
			</div>
		<?php endforeach; endif; unset($_from); ?>
		</div>
	<?php endif; ?>
	<?php if ($this->_tpl_vars['grn_approvals']): ?>
		<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
		<p><h5>Approval:</h5></p>
		<p>
		You have GRN waiting for approval.<br>
		<ul>
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['grn_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
			<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['grn_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=goods_receiving_note_approval.php"><?php echo $this->_tpl_vars['grn_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['grn_approvals'][$this->_sections['i']['index']]['count']; ?>
)
			</a></li>
		<?php endfor; endif; ?>
		</ul>
		</p>
		</div>
	<?php endif; ?>
<?php endif; ?>

<!-- Credit Note approval notification -->
<?php if ($this->_tpl_vars['cn_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Credit Note</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Credit Note waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['cn_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['cn_approvals'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=consignment.credit_note.approval.php"><?php echo $this->_tpl_vars['cn_approvals'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['cn_approvals'][$this->_sections['i']['index']]['count']; ?>
)
	</a></li>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- Credit Note approval notification -->
<?php if ($this->_tpl_vars['dn_approvals']): ?>
<h5><img src=/ui/notify_po_new.png align=absmiddle border=0> Debit Note</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Debit Note waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['dn_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['dn_approvals'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=consignment.debit_note.approval.php"><?php echo $this->_tpl_vars['dn_approvals'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['dn_approvals'][$this->_sections['i']['index']]['count']; ?>
)
	</a></li>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- Purchase Agreement approval notification -->
<?php if ($this->_tpl_vars['purchase_agreement_approvals']): ?>
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> Purchase Agreement</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
	You have Purchase Agreement waiting for approval.<br>
	<ul>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['purchase_agreement_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
		<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['purchase_agreement_approvals'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=po.po_agreement.approval.php"><?php echo $this->_tpl_vars['purchase_agreement_approvals'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['purchase_agreement_approvals'][$this->_sections['i']['index']]['count']; ?>
)
		</a></li>
	<?php endfor; endif; ?>
	</ul>
	</p>
	</div>
<?php endif; ?>

<!-- Un-finalized POS notification -->
<?php if ($this->_tpl_vars['unfinalized_pos']): ?>
<h5>
<i class="icofont-tags icofont"></i> Non-finalised POS</h5>
The following sales need to be finalised.
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
    <ul style="list-style:none;">
	<?php $_from = $this->_tpl_vars['unfinalized_pos']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['r']):
?>
	    <li>
	        <?php if ($this->_tpl_vars['r']['data_count'] > 1): ?>
			<img src="/ui/expand.gif" title="Show/Close Details" onClick="togglediv('ul_ufp_<?php echo $this->_tpl_vars['bid']; ?>
', this);" class="clickable" /> <?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['data_count'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)
			<ul style="list-style:none;display:none;" id="ul_ufp_<?php echo $this->_tpl_vars['bid']; ?>
">
			    <?php $_from = $this->_tpl_vars['r']['date']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['d']):
?>
			        <?php echo ''; ?><?php ob_start(); ?><?php echo ''; ?><?php if ($this->_tpl_vars['config']['counter_collection_server']): ?><?php echo ''; ?><?php echo $this->_tpl_vars['config']['counter_collection_server']; ?><?php echo '/counter_collection.php?remote=1&date_select='; ?><?php echo $this->_tpl_vars['d']; ?><?php echo ''; ?><?php else: ?><?php echo 'login.php?server='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['r']['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&redir='; ?><?php echo $this->_tpl_vars['config']['counter_collection_server']; ?><?php echo '/counter_collection.php?date_select='; ?><?php echo $this->_tpl_vars['d']; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('target_url', ob_get_contents());ob_end_clean(); ?><?php echo ''; ?>

					 <?php if ($this->_tpl_vars['config']['counter_collection_server']): ?>
					    <li><a href="javascript:void(open_from_dc('<?php echo $this->_tpl_vars['target_url']; ?>
','<?php echo $this->_tpl_vars['sessioninfo']['id']; ?>
','<?php echo $this->_tpl_vars['bid']; ?>
', 'Counter Collection'));"><?php echo $this->_tpl_vars['d']; ?>
</a></li>
					 <?php else: ?>
			        	<li><a href="<?php echo $this->_tpl_vars['target_url']; ?>
"><?php echo $this->_tpl_vars['d']; ?>
</a></li>
			        <?php endif; ?>
			    <?php endforeach; endif; unset($_from); ?>
			</ul>
			<?php else: ?>
				<?php echo ''; ?><?php ob_start(); ?><?php echo ''; ?><?php if ($this->_tpl_vars['config']['counter_collection_server']): ?><?php echo ''; ?><?php echo $this->_tpl_vars['config']['counter_collection_server']; ?><?php echo '/counter_collection.php?remote=1&date_select='; ?><?php echo $this->_tpl_vars['r']['date']['0']; ?><?php echo ''; ?><?php else: ?><?php echo 'login.php?server='; ?><?php echo ((is_array($_tmp=$this->_tpl_vars['r']['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?><?php echo '&redir='; ?><?php echo $this->_tpl_vars['config']['counter_collection_server']; ?><?php echo '/counter_collection.php?date_select='; ?><?php echo $this->_tpl_vars['r']['date']['0']; ?><?php echo ''; ?><?php endif; ?><?php echo ''; ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('target_url', ob_get_contents());ob_end_clean(); ?><?php echo ''; ?>

			    <?php if ($this->_tpl_vars['config']['counter_collection_server']): ?>
			        <li><?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<a href="javascript:void(open_from_dc('<?php echo $this->_tpl_vars['target_url']; ?>
','<?php echo $this->_tpl_vars['sessioninfo']['id']; ?>
','<?php echo $this->_tpl_vars['bid']; ?>
', 'Counter Collection'));"><?php echo $this->_tpl_vars['r']['date']['0']; ?>
</a>)</li>
			    <?php else: ?>
			    	<li><?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<a href="<?php echo $this->_tpl_vars['target_url']; ?>
"><?php echo $this->_tpl_vars['r']['date']['0']; ?>
</a>)</li>
			    <?php endif; ?>
			<?php endif; ?>
		</li>
	<?php endforeach; endif; unset($_from); ?>
	</ul>
</div>
<?php endif; ?>

<!-- Invalid SKU notification -->
<?php if ($this->_tpl_vars['invalid_sku']): ?>
<h5>
<i class="icofont-tags icofont"></i> Invalid SKU</h5>
The following date got invalid SKU.
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
    <ul style="list-style:none;">
	<?php $_from = $this->_tpl_vars['invalid_sku']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['bid'] => $this->_tpl_vars['r']):
?>
	    <li>
	        <?php if ($this->_tpl_vars['r']['data_count'] > 1): ?>
			<img src="/ui/expand.gif" title="Show/Close Details" onClick="togglediv('ul_is_<?php echo $this->_tpl_vars['bid']; ?>
', this);" class="clickable" /> <?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['data_count'])) ? $this->_run_mod_handler('number_format', true, $_tmp) : number_format($_tmp)); ?>
)
			<ul style="list-style:none;display:none;" id="ul_is_<?php echo $this->_tpl_vars['bid']; ?>
">
			    <?php $_from = $this->_tpl_vars['r']['date']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['d']):
?>
			        <?php echo ''; ?><?php ob_start(); ?><?php echo 'pos.invalid_sku.php?branch_id='; ?><?php echo $this->_tpl_vars['bid']; ?><?php echo '&date_select='; ?><?php echo $this->_tpl_vars['d']; ?><?php echo '&a=refresh_data'; ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('target_url', ob_get_contents());ob_end_clean(); ?><?php echo ''; ?>


		        	<li><a href="<?php echo $this->_tpl_vars['target_url']; ?>
"><?php echo $this->_tpl_vars['d']; ?>
</a></li>

			    <?php endforeach; endif; unset($_from); ?>
			</ul>
			<?php else: ?>
				<?php echo ''; ?><?php ob_start(); ?><?php echo 'pos.invalid_sku.php?branch_id='; ?><?php echo $this->_tpl_vars['bid']; ?><?php echo '&date_select='; ?><?php echo $this->_tpl_vars['r']['date']['0']; ?><?php echo '&a=refresh_data'; ?><?php $this->_smarty_vars['capture']['default'] = ob_get_contents();  $this->assign('target_url', ob_get_contents());ob_end_clean(); ?><?php echo ''; ?>


			   	<li><?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<a href="<?php echo $this->_tpl_vars['target_url']; ?>
"><?php echo $this->_tpl_vars['r']['date']['0']; ?>
</a>)</li>
			<?php endif; ?>
		</li>
	<?php endforeach; endif; unset($_from); ?>
	</ul>
</div>
<?php endif; ?>

<!-- Future Change Price approval -->
<?php if ($this->_tpl_vars['fp_approvals']): ?>
<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> Batch Price Change</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have Batch Price Change waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['fp_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['fp_approvals'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=masterfile_sku_items.future_price_approval.php"><?php echo $this->_tpl_vars['fp_approvals'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['fp_approvals'][$this->_sections['i']['index']]['count']; ?>
)</a></li>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- e-Form approval -->
<?php if ($this->_tpl_vars['ed_approvals']): ?>
<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> e-Form</h5>
<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
<p>
You have e-Form waiting for approval.<br>
<ul>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['ed_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
	<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['ed_approvals'][$this->_sections['i']['index']]['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=eform.approval.php"><?php echo $this->_tpl_vars['ed_approvals'][$this->_sections['i']['index']]['branch_code']; ?>
 (<?php echo $this->_tpl_vars['ed_approvals'][$this->_sections['i']['index']]['count']; ?>
)</a></li>
<?php endfor; endif; ?>
</ul>
</p>
</div>
<?php endif; ?>

<!-- GRA approval -->
<?php if ($this->_tpl_vars['gra_approvals']): ?>
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0"> GRA</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
	You have GRA waiting for approval.<br>
	<ul>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['gra_approvals']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
$this->_sections['i']['show'] = true;
$this->_sections['i']['max'] = $this->_sections['i']['loop'];
$this->_sections['i']['step'] = 1;
$this->_sections['i']['start'] = $this->_sections['i']['step'] > 0 ? 0 : $this->_sections['i']['loop']-1;
if ($this->_sections['i']['show']) {
    $this->_sections['i']['total'] = $this->_sections['i']['loop'];
    if ($this->_sections['i']['total'] == 0)
        $this->_sections['i']['show'] = false;
} else
    $this->_sections['i']['total'] = 0;
if ($this->_sections['i']['show']):

            for ($this->_sections['i']['index'] = $this->_sections['i']['start'], $this->_sections['i']['iteration'] = 1;
                 $this->_sections['i']['iteration'] <= $this->_sections['i']['total'];
                 $this->_sections['i']['index'] += $this->_sections['i']['step'], $this->_sections['i']['iteration']++):
$this->_sections['i']['rownum'] = $this->_sections['i']['iteration'];
$this->_sections['i']['index_prev'] = $this->_sections['i']['index'] - $this->_sections['i']['step'];
$this->_sections['i']['index_next'] = $this->_sections['i']['index'] + $this->_sections['i']['step'];
$this->_sections['i']['first']      = ($this->_sections['i']['iteration'] == 1);
$this->_sections['i']['last']       = ($this->_sections['i']['iteration'] == $this->_sections['i']['total']);
?>
		<li><a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['gra_approvals'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=goods_return_advice.approval.php"><?php echo $this->_tpl_vars['gra_approvals'][$this->_sections['i']['index']]['code']; ?>
 (<?php echo $this->_tpl_vars['gra_approvals'][$this->_sections['i']['index']]['count']; ?>
)
		</a></li>
	<?php endfor; endif; ?>
	</ul>
	</p>
	</div>
<?php endif; ?>

<!-- Stucked Docs -->
<?php if ($this->_tpl_vars['stucked_docs']): ?>
	<h5>
		<i class="icofont-tags icofont"></i> Stucked Document Approvals</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
	Due to inactive users(s), these documents approval is currently stucked.<br />
	<ul>
	<?php $_from = $this->_tpl_vars['stucked_docs']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['k'] => $this->_tpl_vars['i']):
?>
		<li><a href="stucked_document_approvals.php?m=<?php echo $this->_tpl_vars['k']; ?>
" target="_blank"><?php echo $this->_tpl_vars['i']['desc']; ?>
</a> (<?php echo $this->_tpl_vars['i']['count']; ?>
)</li>
	<?php endforeach; endif; unset($_from); ?>
	</ul>
	</p>
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['cnote_approvals']): ?>
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0" /> Credit Note</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
		You have Credit Note waiting for approval.<br>
		<ul>
			<?php $_from = $this->_tpl_vars['cnote_approvals']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
				<li>
					<a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=cnote.approval.php"><?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<?php echo $this->_tpl_vars['r']['count']; ?>
)
					</a>
				</li>
			<?php endforeach; endif; unset($_from); ?>
		</ul>
	</p>
	</div>
<?php endif; ?>

<?php if ($this->_tpl_vars['cycle_count_approvals']): ?>
	<h5><img src="/ui/notify_po_new.png" align="absmiddle" border="0" /> Cycle Count</h5>
	<div style="margin-bottom:10px; border-bottom: 1px solid #eee;">
	<p>
		You have Cycle Count waiting for approval.<br>
		<ul>
			<?php $_from = $this->_tpl_vars['cycle_count_approvals']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['r']):
?>
				<li>
					<a href="login.php?server=<?php echo ((is_array($_tmp=$this->_tpl_vars['r']['branch_code'])) ? $this->_run_mod_handler('urlencode', true, $_tmp) : urlencode($_tmp)); ?>
&redir=admin.cycle_count.approval.php"><?php echo $this->_tpl_vars['r']['branch_code']; ?>
 (<?php echo $this->_tpl_vars['r']['count']; ?>
)
					</a>
				</li>
			<?php endforeach; endif; unset($_from); ?>
		</ul>
	</p>
	</div>
<?php endif; ?>