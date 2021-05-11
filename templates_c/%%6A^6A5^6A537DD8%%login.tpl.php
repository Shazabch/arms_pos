<?php /* Smarty version 2.6.18, created on 2021-05-11 15:39:18
         compiled from login.tpl */ ?>
<?php require_once(SMARTY_CORE_DIR . 'core.load_plugins.php');
smarty_core_load_plugins(array('plugins' => array(array('modifier', 'string_format', 'login.tpl', 65, false),array('modifier', 'strtolower', 'login.tpl', 181, false),)), $this); ?>
<div class="login-bg no-bg-img">	
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<div align="center">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "front_end.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script type="text/javascript">

var single_server_mode = int('<?php echo $this->_tpl_vars['config']['single_server_mode']; ?>
');
var BRANCH_CODE = '<?php echo $this->_tpl_vars['BRANCH_CODE']; ?>
';
var hq_url = '<?php echo ((is_array($_tmp='hq')) ? $this->_run_mod_handler('string_format', true, $_tmp, $this->_tpl_vars['config']['no_ip_string']) : smarty_modifier_string_format($_tmp, $this->_tpl_vars['config']['no_ip_string'])); ?>
';
var curr_branch_at_hq = int('<?php echo $this->_tpl_vars['config']['branch_at_hq'][$this->_tpl_vars['BRANCH_CODE']]; ?>
');

<?php echo '
function do_branch_login(){
	if(!single_server_mode){	// is multi server
		var bcode = $(\'branch\').value;
		var action_path = \'\';
		
		if(bcode != BRANCH_CODE){	// login to other branch
			var opt = $(\'branch\').options[$(\'branch\').selectedIndex];	// get selected branch <option>
			var branch_url = $(opt).readAttribute(\'branch_url\');	// get original branch url
			var login_branch_at_hq = int($(opt).readAttribute(\'branch_at_hq\'));	// get whether this branch is at hq server
			
			if(BRANCH_CODE == \'HQ\' || curr_branch_at_hq){	// currently at HQ, or the branch in hq
				if(bcode == \'HQ\' || login_branch_at_hq){	// this branch should login to HQ, or is login to hq, no need to change server
					// nothing to change action path
				}else{
					action_path = branch_url;	// login to branch server
				}
			}else{	// currently at other branch which is not in hq server
				if(login_branch_at_hq){	// this branch should login to HQ
					action_path = hq_url;	// login to hq server
				}else{
					action_path = branch_url;	// login to branch server
				}
			}
			document.f_l.action = action_path+\'/login.php\';
		}
	}
	
	if(document.f_l[\'tnc\'].checked == false){
		alert("You must agree to the Terms & Conditions in order to login.");
		return false;
	}
	
	return true;
}

function do_vendor_login(){
	if(!single_server_mode){	// is multi server
		var bcode = $(\'sel_vp_branch\').value;
		var action_path = \'\';
		
		if(bcode != BRANCH_CODE){	// login to other branch
			var opt = $(\'sel_vp_branch\').options[$(\'sel_vp_branch\').selectedIndex];	// get selected branch <option>
			var branch_url = $(opt).readAttribute(\'branch_url\');	// get original branch url
			var login_branch_at_hq = int($(opt).readAttribute(\'branch_at_hq\'));	// get whether this branch is at hq server
			
			if(BRANCH_CODE == \'HQ\' || curr_branch_at_hq){	// currently at HQ, or the branch in hq
				if(bcode == \'HQ\' || login_branch_at_hq){	// this branch should login to HQ, or is login to hq, no need to change server
					// nothing to change action path
				}else{
					action_path = branch_url;	// login to branch server
				}
			}else{	// currently at other branch which is not in hq server
				if(login_branch_at_hq){	// this branch should login to HQ
					action_path = hq_url;	// login to hq server
				}else{
					action_path = branch_url;	// login to branch server
				}
			}

			document.f_b.action = action_path+\'/login.php\';
		}
	}
	
	return true;
}

function do_sa_login(){
	//alert($(\'ac\').value);
	document.f_c.submit();
}

function do_debtor_login(){
	document.f_d.submit();
}

'; ?>

</script>

<?php if ($this->_tpl_vars['config']['login_page_header']): ?>
	<div style="width: 60%; margin: 0px auto;" class="login-header">
		<table cellpadding="0" cellspacing="5" border="0">
			<tr>
				<?php $_from = $this->_tpl_vars['config']['login_page_header']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['dummy1'] => $this->_tpl_vars['r']):
?>
					<?php if ($this->_tpl_vars['r']['type'] == 'image'): ?>
						<td align="center" rowspan="<?php echo $this->_tpl_vars['header_info']['rowspan_count']; ?>
">
							<img src="<?php echo $this->_tpl_vars['r']['path']; ?>
" align="absmiddle" <?php if ($this->_tpl_vars['r']['width']): ?>width="<?php echo $this->_tpl_vars['r']['width']; ?>
"<?php endif; ?> <?php if ($this->_tpl_vars['r']['height']): ?>height="<?php echo $this->_tpl_vars['r']['height']; ?>
"<?php endif; ?> />
						</td>
					<?php elseif ($this->_tpl_vars['r']['type'] == 'text'): ?>
						<td valign="top" <?php if (! $this->_tpl_vars['header_info']['show_image_first']): ?>align="center"<?php endif; ?>><h4><?php echo $this->_tpl_vars['r']['html']; ?>
</h4></td>
					<?php endif; ?>
					<?php if ($this->_tpl_vars['r']['next_row']): ?>
						</tr>
						<tr>
					<?php endif; ?>
				<?php endforeach; endif; unset($_from); ?>
			</tr>
		</table>
	</div>
<?php endif; ?>

<br style="clear:both">
<?php if ($this->_tpl_vars['errmsg']): ?><font color="red"><?php echo $this->_tpl_vars['errmsg']; ?>
</font><?php endif; ?>
<form method="post" name="f_l" onSubmit="return do_branch_login();">
	<table cellpadding="0" cellspacing="10" border="0" class="tbl-shadow login-container" style="border:1px solid #ccc;width:250px;">
		<tr colspan="2"><th colspan="2"><h1>Please Login</h1></th></tr>
		<tr>
			<th align="left">Branch</th>
			<td>
				<select id="branch" class="form-control-2" name="login_branch">
				<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branch']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
					<?php $this->assign('bcode', $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']); ?>
					<option value="<?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
" <?php if ($this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'] == BRANCH_CODE): ?>selected<?php endif; ?> <?php if (! $this->_tpl_vars['config']['single_server_mode']): ?>branch_url="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)))) ? $this->_run_mod_handler('string_format', true, $_tmp, $this->_tpl_vars['config']['no_ip_string']) : smarty_modifier_string_format($_tmp, $this->_tpl_vars['config']['no_ip_string'])); ?>
" <?php if ($this->_tpl_vars['config']['branch_at_hq'][$this->_tpl_vars['bcode']]): ?>branch_at_hq="1"<?php endif; ?><?php endif; ?>><?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
</option>
				<?php endfor; endif; ?>
				</select>
			</td>
		</tr>
	<tr>
		<th align="left">Login ID</th><td><input name="u" class="form-control" size="20" type="password"></td>
	</tr>
	<tr>
		<th align="left">Password</th><td><input name="p" class="form-control" size="20" type="password"></td>
	</tr>
	<tr>
		<th align="left">&nbsp;</th><td><a href="password_reset.php" style="font-size: 12px;color: #32405b;">Forgot Password?</a></td>
	</tr>
	<tr>
		<th align="right"><input type="checkbox" name="tnc" value="1" checked /></th><td nowrap><span class="small agreement-link">I agree to the <a href="https://agreement.arms.my/5" class="login-link" style="font-size: smaller;color: dodgerred; text-decoration: underline" target="_blank">Terms & Conditions</a></span></td>
	</tr>
	<tr>
		<th colspan="2"><input class="btn btn-primary" type="submit" value="Login"></th>
	</tr>
	</table>
</form>

<?php if ($this->_tpl_vars['config']['po_allow_vendor_request']): ?>
<br>
<?php if ($this->_tpl_vars['errmsg2']): ?><font color="red"><?php echo $this->_tpl_vars['errmsg2']; ?>
</font><?php endif; ?>
<form method="post" name="f_b" onSubmit="return do_vendor_login();">
	<table cellpadding="0"  class="tbl-shadow login-container" cellspacing="10" border="0" style="border:1px solid #ccc;width:250px;">
		<tr colspan="2"><th colspan="2"><h1>Vendor Login</h1></th></tr>
		<tr>
			<th align="left">Branch</th>
			<td>
				<select id="sel_vp_branch" class="form-control-2" name="login_branch">
					<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branch']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
						<?php $this->assign('bcode', $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']); ?>
						<option value="<?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
" <?php if ($this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'] == BRANCH_CODE): ?>selected<?php endif; ?> <?php if (! $this->_tpl_vars['config']['single_server_mode']): ?>branch_url="<?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)))) ? $this->_run_mod_handler('string_format', true, $_tmp, $this->_tpl_vars['config']['no_ip_string']) : smarty_modifier_string_format($_tmp, $this->_tpl_vars['config']['no_ip_string'])); ?>
" <?php if ($this->_tpl_vars['config']['branch_at_hq'][$this->_tpl_vars['bcode']]): ?>branch_at_hq="1"<?php endif; ?><?php endif; ?>><?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
</option>
					<?php endfor; endif; ?>
				</select>
			</td>
		<tr>
			<td><b>Enter Ticket No.</b></td>
			<td><input name="ac" class="form-control" size="6" type="password"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input class="btn btn-primary" type="submit" value="Login" /></td>
		</tr>
	</table>
</form>
<?php endif; ?>

<?php if ($this->_tpl_vars['config']['masterfile_enable_sa']): ?>
<br>
<?php if ($this->_tpl_vars['errmsg3']): ?><font color="red"><?php echo $this->_tpl_vars['errmsg3']; ?>
</font><?php endif; ?>
<form method="post" name="f_c">
	<table cellpadding="0"  class="tbl-shadow login-container" cellspacing="10" border="0" style="border:1px solid #ccc;width:250px;">
		<tr colspan="2"><th colspan="2"><h1>Sales Agent Login</h1></th></tr>
		<tr>
			<td><b>Enter Ticket No.</b></td>
			<td><input name="sa_ticket" class="form-control" size="6" type="password"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input type="button" class="btn btn-primary" onclick="do_sa_login();" value="Login"></td>
		</tr>
	</table>
</form>
<?php endif; ?>

<?php if ($this->_tpl_vars['config']['enable_debtor_portal'] && $_REQUEST['dp'] == 1): ?>
<h1>Debtor Login</h1>
<?php if ($this->_tpl_vars['deb_login_err']): ?><font color="red"><?php echo $this->_tpl_vars['deb_login_err']; ?>
</font><?php endif; ?>
<form method="post" name="f_d">
	<table cellpadding="0"  class="tbl-shadow login-container" cellspacing="10" border="0" style="border:1px solid #ccc;width:250px;">
		<tr>
			<th align="left">Branch</th>
			<td>
				<select class="form-control" id="sel_dp_branch" <?php if ($this->_tpl_vars['config']['single_server_mode']): ?>name="login_branch"<?php else: ?>onchange="form.action=this.value+'/login.php'"<?php endif; ?>>
					<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branch']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
						<?php if ($this->_tpl_vars['config']['single_server_mode']): ?>
							<option value="<?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
" <?php if ($this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'] == BRANCH_CODE): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
</option>
						<?php else: ?>
							<option value="<?php if ($this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'] != BRANCH_CODE): ?><?php echo ((is_array($_tmp=((is_array($_tmp=$this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'])) ? $this->_run_mod_handler('strtolower', true, $_tmp) : strtolower($_tmp)))) ? $this->_run_mod_handler('string_format', true, $_tmp, $this->_tpl_vars['config']['no_ip_string']) : smarty_modifier_string_format($_tmp, $this->_tpl_vars['config']['no_ip_string'])); ?>
<?php endif; ?>" <?php if ($this->_tpl_vars['branch'][$this->_sections['i']['index']]['code'] == BRANCH_CODE): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['branch'][$this->_sections['i']['index']]['code']; ?>
</option>
						<?php endif; ?>
					<?php endfor; endif; ?>
				</select>
			</td>
		<tr>
			<td><b>Enter Ticket No.</b></td>
			<td><input class="form-control" name="debtor_key" size="6" type="password"></td>
		</tr>
		<tr>
			<td colspan="2" align="center"><input class="btn btn-primary" type="button" onclick="do_debtor_login();" value="Login"></td>
		</tr>
	</table>
</form>
<?php endif; ?>
</div>

<script type="text/javascript">

<?php echo '
document.f_l.u.focus();

/*var isFirefox = typeof InstallTrigger !== \'undefined\';   // Firefox 1.0+
if(isFirefox && typeof(jsPrintSetup) == \'undefined\'){
	$(\'div_install_jsprintsetup\').show();
}*/
'; ?>

</script>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>