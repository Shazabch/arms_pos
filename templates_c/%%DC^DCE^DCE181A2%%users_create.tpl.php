<?php /* Smarty version 2.6.18, created on 2021-05-07 18:20:24
         compiled from users_create.tpl */ ?>
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script type="text/javascript">
//get config "reserve_login_id" array value
var reserve_login_id ='';
<?php if ($this->_tpl_vars['reserve_val']): ?>
	var reserve_login_id = <?php echo $this->_tpl_vars['reserve_val']; ?>
;
<?php endif; ?>
<?php echo '
function check_a()
{
	if (check_login()) {
        if (empty(document.f_a.newuser, \'You must enter a username\'))
		{
			return false;
		}
		if(reserve_login_id != \'\'){
			var newuser = document.f_a[\'newuser\'].value.toLowerCase();
			for (var i = 0;i < reserve_login_id.length; i++) {
				if(newuser.startsWith(reserve_login_id[i].toLowerCase()) == true){
					alert(\'The username is not allow to start with "\'+reserve_login_id[i]+\'".\');
					document.f_a[\'newuser\'].value = \'\';
					document.f_a[\'newuser\'].focus();
					return false;
				}
			}
		}
		if (!document.f_a.template.checked)
		{
			if (empty(document.f_a.ic_no, \'You must enter IC No\'))
			{
				return false;
			}
			if (empty(document.f_a.fullname, \'You must enter Full Name\'))
			{
				return false;
			}
			if (empty(document.f_a.position, \'You must enter Position\'))
			{
				return false;
			}
			if (empty(document.f_a.newlogin, \'You must enter a Login ID\'))
			{
				return false;
			}
			if (empty(document.f_a.newpassword, \'You must enter a password\'))
			{
				return false;
			}
			if (document.f_a.newpassword.value != document.f_a.newpassword2.value)
			{
				alert(\'Password does not match with confirmation password.\');
				document.f_a.newpassword2.value = \'\';
				document.f_a.newpassword2.focus();
				return false;
			}
			/*
			if (empty(document.f_a.newemail, \'You must enter an email\'))
			{
				return false;
			}
			*/

			// if got nric field
			if(document.f_a[\'ic_no\']){
				if(document.f_a[\'ic_no\'].value.trim()==\'\'){
					alert(\'Please enter IC\');
					document.f_a[\'ic_no\'].focus();
					return false;
				}
			}
			if(reserve_login_id != \'\'){
				var login_id = document.f_a[\'newlogin\'].value.toLowerCase();
				for (var n = 0;n < reserve_login_id.length; n++) {
					if(login_id.startsWith(reserve_login_id[n].toLowerCase()) == true){
						alert(\'The Login ID is not allow to start with "\'+reserve_login_id[n]+\'".\');
						document.f_a[\'newlogin\'].value = \'\';
						document.f_a[\'newlogin\'].focus();
						return false;
					}
				}
			}
		}

		document.f_a.submitbtn.disabled = true;
		return true;
	}
	return false;
}

function checkallrow(p, r, v)
{
	var x = document.f_a.getElementsByTagName(\'input\');
	for (var i=0;i<x.length;i++)
	{
		if (x[i].type.indexOf(\'checkbox\') >= 0 && x[i].name.indexOf(p + \'[\') == 0 && x[i].name.indexOf(\'[\' + r + \']\') > p.length+2)
		{
			x[i].checked = v;
		}
	}
}

function checkallcol(p, c, v)
{
	var x = document.f_a.getElementsByTagName(\'input\');
	for (var i=0;i<x.length;i++)
	{
		if (x[i].type.indexOf(\'checkbox\') >= 0 && x[i].name.indexOf(p + \'[\' + c + \']\') >= 0)
		{
			x[i].checked = v;
		}
	}
}

function shide(v)
{
	if (v)
	{
	    $$(\'#top_form .hide_by_temp\').each(function(ele,index){
			$(ele).hide();
		});

		document.getElementById(\'v1\').style.visibility = \'hidden\';
/*		document.getElementById(\'v2\').style.visibility = \'hidden\';
		document.getElementById(\'v3\').style.visibility = \'hidden\';
		document.getElementById(\'v4\').style.visibility = \'hidden\';
*/
	}
	else
	{
	    $$(\'#top_form .hide_by_temp\').each(function(ele,index){
			$(ele).show();
		});

		document.getElementById(\'v1\').style.visibility = \'visible\';
/*		document.getElementById(\'v2\').style.visibility = \'visible\';
		document.getElementById(\'v3\').style.visibility = \'visible\';
		document.getElementById(\'v4\').style.visibility = \'visible\';
*/
	}
}

function stoggle(v)
{
	document.f_a.use_template.checked=(v!=0);

	if (v==0)
		document.getElementById(\'custompriv\').style.display=\'\';
	else
		document.getElementById(\'custompriv\').style.display=\'none\';
}


function ctoggle(v)
{
	stoggle(v);

}

var branch_email_suffx = [\'\', \'_bl\', \'_dg\', \'_gr\', \'_kg\'];
function uname_blur(u)
{
	lc(u);
}

// Clone Selected Column
function clone_selected_col()
{
	sc_bid = document.getElementById(\'sc\').value;
	dc_bid = document.getElementById(\'dc\').value;

	if (sc_bid == dc_bid)
	{
		alert("Can\'t Clone From Same Source To Same Destination Branch");
	}
	else
	{
		if (confirm("Clone selected branch privileges?")) 
		{							
			var sc_value = document.f_a.getElementsByClassName("inp_priv-" + sc_bid);
			var dc_value = document.f_a.getElementsByClassName("inp_priv-" + dc_bid);
			
			for (var j = 0; j < dc_value.length; j++)
			{
				dc_value[j].checked = false;
			}
			
			for (var i = 0, len=sc_value.length; i < len; i++)
			{				
				// Get Source Privilege Code
				var priv_code = sc_value[i].getAttribute("priv_code");
				// Get Target Input
				var target_inp = document.f_a[\'user_privilege[\'+dc_bid+\'][\'+priv_code+\']\'];
				
				if (target_inp){
					target_inp.checked = sc_value[i].checked;
				}
			}
		}
	}
}

function toggle_all_check(obj, type, class_name){
	if (type=="departments"){
		$$("#departments_id ."+class_name).each(function (ele,index){
			ele.checked=obj.checked;
		});

	}else if (type=="vendors"){
		$$("#vendors_id ."+class_name).each(function (ele,index){
			ele.checked=false;
		});

		// if Vendors All Is Checked	
		$("vendors_id").style.display = (obj.checked) ? "none" : "";	
	}else if (type=="brands"){
		$$("#brands_id ."+class_name).each(function (ele,index){
			ele.checked=false;
		});
		
		// if Brands All Is Checked
		$("brands_id").style.display = (obj.checked) ? "none" : "";				
	}else if (type=="regions"){
		$$("#regions ."+class_name).each(function (ele,index){
			ele.checked=obj.checked;
		});
	}
}
'; ?>

</script>


<?php if ($this->_tpl_vars['show_add_user']): ?>
<h1>Create Profile</h1>
<div class=errmsg>
<?php if ($this->_tpl_vars['errmsg']['a']): ?><ul><?php $_from = $this->_tpl_vars['errmsg']['a']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?><li><?php echo $this->_tpl_vars['m']; ?>
<?php endforeach; endif; unset($_from); ?></ul><?php endif; ?>
<?php if ($this->_tpl_vars['msg']['a']): ?><ul class=msg><?php $_from = $this->_tpl_vars['msg']['a']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['m']):
?><li><?php echo $this->_tpl_vars['m']; ?>
<?php endforeach; endif; unset($_from); ?></ul><?php endif; ?>
</div>
<div class="stdframe" style="margin-bottom:20px;">
<form method=post name=f_a onsubmit="return check_a()">
<input type=hidden name=a value="a">
<table id="top_form">
<tr>
	<td colspan=2><input id=as_template type=checkbox name=template value=1 <?php if ($_REQUEST['template']): ?>checked<?php endif; ?> onClick="shide(this.checked)"> <b><label for="as_template">Create as template</label></b></td>
</tr>
<tr>
	<td><b>Username</b></td>
	<td><input name=newuser size=20 maxlength=50 value="<?php echo $_REQUEST['newuser']; ?>
" onBlur="uname_blur(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"><span id=v1>only a-z, 0-9 and underscore '_' allowed, minimum <?php echo $this->_tpl_vars['MIN_USERNAME_LENGTH']; ?>
 characters</span></td>
</tr>
<?php if ($this->_tpl_vars['config']['enable_suite_device']): ?>
<tr>
	<td><b>Fnb Username</b></td>
	<td><input name="fnb_username" size=20 maxlength=50 value="<?php echo $_REQUEST['fnb_username']; ?>
"> (For Fnb Cashier use)</td>
</tr>
<?php endif; ?>
<?php if ($this->_tpl_vars['config']['user_profile_need_ic']): ?>
	<tr class="hide_by_temp">
	    <td><b>IC No.</b></td>
	    <td>
			<input name="ic_no" size="50" maxlength="20" value="<?php echo $_REQUEST['ic_no']; ?>
" />
            <img src="ui/rq.gif" align="absbottom" title="Required Field" />
		</td>
	</tr>
<?php endif; ?>

<?php if (! $this->_tpl_vars['config']['consignment_modules']): ?>
<tr class="hide_by_temp">
	<td><b>Login Barcode</b></td>
	<td><input name=barcode size=26 maxlength=16 value="<?php echo $_REQUEST['barcode']; ?>
"> (For POS Counter use) <span id=v1>only numeric <?php if ($this->_tpl_vars['MIN_BARCODE_LENGTH']): ?>, minimum <?php echo $this->_tpl_vars['MIN_BARCODE_LENGTH']; ?>
 digit<?php endif; ?></span></td>
</tr>
<?php endif; ?>
<tr class="hide_by_temp">
	<td><b>Full Name</b></td>
	<td><input name=fullname size=50 maxlength=100 value="<?php echo $_REQUEST['fullname']; ?>
" onBlur="uc(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Position</b></td>
	<td><input name=position size=50 maxlength=100 value="<?php echo $_REQUEST['position']; ?>
" onBlur="uc(this)"> <img src=ui/rq.gif align=absbottom title="Required Field"></td>
</tr>
<tr class="hide_by_temp">
	<td><b>User Department</b></td>
	<td><input name=user_dept size=50 maxlength=100 value="<?php echo $_REQUEST['user_dept']; ?>
" onBlur="uc(this)"></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Location</b></td>
	<td>
		<select name=default_branch_id onchange="uname_blur(newuser)">
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['branches']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<option value=<?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['id']; ?>
 <?php if ($_REQUEST['default_branch_id'] == $this->_tpl_vars['branches'][$this->_sections['i']['index']]['id']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['branches'][$this->_sections['i']['index']]['code']; ?>
</option>
		<?php endfor; endif; ?>
		</select>
	</td>
</tr>
<tr class="hide_by_temp">
	<td valign=top><b>SKU Department</b></td>
	<td id="departments_id">
	<div style="padding-bottom:10px;">
	<input type=checkbox id="dept_all_id" onclick="toggle_all_check(this,'departments','departments')">
	<label for="dept_all_id"><b>All departments</b></label>

	<?php $this->assign('root', ''); ?>
	<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['departments']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
	<?php if ($this->_tpl_vars['root'] != $this->_tpl_vars['departments'][$this->_sections['i']['index']]['root']): ?>
	<?php $this->assign('root', ($this->_tpl_vars['departments'][$this->_sections['i']['index']]['root'])); ?>
	</div>
	<div id="root[<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['root_id']; ?>
]" style="padding-bottom:10px;">
	<b><?php echo $this->_tpl_vars['root']; ?>
</b><br>
	<input type=checkbox id="dept_<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['root_id']; ?>
_id" class="departments" onclick="toggle_all_check(this,'departments','root_<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['root_id']; ?>
')"><label for="dept_<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['root_id']; ?>
_id">All</label>
	<?php endif; ?>
	<?php $this->assign('id', ($this->_tpl_vars['departments'][$this->_sections['i']['index']]['id'])); ?>
	<span style="white-space: nowrap"><input type=checkbox id=dept<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['id']; ?>
 class="departments root_<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['root_id']; ?>
" name=departments[<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['id']; ?>
] <?php if ($_REQUEST['departments'][$this->_tpl_vars['id']]): ?>checked<?php endif; ?>><label for=dept<?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['id']; ?>
><?php echo $this->_tpl_vars['departments'][$this->_sections['i']['index']]['description']; ?>
</label></span>
	<?php endfor; endif; ?>
	</div>
	</td>
</tr>
<tr class="hide_by_temp">
	<td valign=top><b>Vendors</b></td>
	<td id="vendors_all_id">
		<input type=checkbox id="vendors_all_id" onclick="toggle_all_check(this,'vendors','vendors')"> 
		<label for="vendors_all_id">All</label><br>	
	</td>
</tr>
<tr class="hide_by_temp">
	<td></td>
	<td id="vendors_id">
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['vendors']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php $this->assign('id', ($this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id'])); ?>
		<input type=checkbox class="vendors" name=vendors[<?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['id']; ?>
] <?php if ($_REQUEST['vendors'][$this->_tpl_vars['id']]): ?>checked<?php endif; ?>> <?php echo $this->_tpl_vars['vendors'][$this->_sections['i']['index']]['description']; ?>
<br>
		<?php endfor; endif; ?>
		</div>
		<div style="float:left">
			&nbsp;&nbsp;<b>Note:</b> All vendors remain unticked will be considered have privilege on all vendors
		</div>
	</td>
</tr>
<tr class="hide_by_temp">
	<td valign=top><b>Brands</b></td>
	<td id="brands_all_id">
		<input type=checkbox id="brands_all_id" onclick="toggle_all_check(this,'brands','brands')"> 
		<label for="brands_all_id">All</label><br>	
	</td>
</tr>
<tr class="hide_by_temp">
	<td></td>
	<td id="brands_id">
		<div style="height:200px;width:400px;overflow:auto;background:#fff;border:1px solid #ccc;padding:4px;float:left">
		<input type=checkbox class="brands" name=brands[0] <?php if ($_REQUEST['brands'][0]): ?>checked<?php endif; ?>> Unbranded<br>

		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['brands']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<?php $this->assign('id', ($this->_tpl_vars['brands'][$this->_sections['i']['index']]['id'])); ?>
		<input type=checkbox class="brands" name=brands[<?php echo $this->_tpl_vars['brands'][$this->_sections['i']['index']]['id']; ?>
] <?php if ($_REQUEST['brands'][$this->_tpl_vars['id']]): ?>checked<?php endif; ?>> <?php echo $this->_tpl_vars['brands'][$this->_sections['i']['index']]['description']; ?>
<br>
		<?php endfor; endif; ?>
		</div>
		<div style="float:left">
			&nbsp;&nbsp;<b>Note:</b> All brands remain unticked will be considered have privilege on all brands
		</div>
	</td>
</tr>
<?php if ($this->_tpl_vars['config']['consignment_modules'] && $this->_tpl_vars['config']['masterfile_branch_region']): ?>
	<tr>
		<td valign=top><b>Regions</b></td>
		<td id="regions">
		<div style="padding-bottom:10px;">
			<input type="checkbox" id="region_all_id" onclick="toggle_all_check(this,'regions','regions')">
			<label for="region_all_id"><b>All Regions</b></label>
			<?php $_from = $this->_tpl_vars['config']['masterfile_branch_region']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['code'] => $this->_tpl_vars['r']):
?>
				<input type="checkbox" class="regions" name="regions[<?php echo $this->_tpl_vars['code']; ?>
]" <?php if ($_REQUEST['regions'][$this->_tpl_vars['code']]): ?>checked <?php endif; ?>> 
				<b><?php echo $this->_tpl_vars['r']['name']; ?>
</b>
			<?php endforeach; endif; unset($_from); ?>
		</div>
		</td>
	</tr>
<?php endif; ?>
<tr class="hide_by_temp">
	<td><b>User Level</b></td><td>
	<select name=level>
	<?php $_from = $this->_tpl_vars['user_level']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['n'] => $this->_tpl_vars['level']):
?>
	<option value=<?php echo $this->_tpl_vars['level']; ?>
 <?php if ($_REQUEST['level'] == $this->_tpl_vars['level']): ?>selected<?php endif; ?>><?php echo $this->_tpl_vars['n']; ?>
</option>
	<?php endforeach; endif; unset($_from); ?>
	</select>
	</td>
</tr>
<tr class="hide_by_temp">
	<td><b>Login ID</b></td>
	<td><input name=newlogin size=20 maxlength=16 value="<?php echo $_REQUEST['newlogin']; ?>
"> <span id=v4><img src=ui/rq.gif align=absbottom title="Required Field"></span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Password</b></td>
	<td><input name=newpassword type=password size=20 value="<?php echo $_REQUEST['newpassword']; ?>
"> <span id=v2><img src=ui/rq.gif align=absbottom title="Required Field"> (password should consists of numbers and alphabates, with at least <?php echo $this->_tpl_vars['MIN_PASSWORD_LENGTH']; ?>
 character)</span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Retype Password</b></td>
	<td><input name=newpassword2 type=password size=20 value="<?php echo $_REQUEST['newpassword2']; ?>
">  <span id=v5><img src=ui/rq.gif align=absbottom title="Required Field"></span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Email</b></td>
	<td><input name=newemail size=20 value="<?php echo $_REQUEST['newemail']; ?>
" onBlur="lc(this)"><span id=v3></span></td>
</tr>
<tr class="hide_by_temp">
	<td><b>Discount Limit</b>
		[<a href="javascript:void(alert('- This limit will apply to item discount and receipt discount.\n- Discount can be key in by price or by percentage.'))">?</a>]
	</td>
	<td>
		<input name=disc_limit size=1 maxlength="3" value="<?php echo $_REQUEST['disc_limit']; ?>
"> % 
		<?php if ($this->_tpl_vars['config']['user_profile_show_item_discount_only_allow_percent']): ?>
			&nbsp;&nbsp;&nbsp;&nbsp;
			<input type="checkbox" name="item_disc_only_allow_percent" <?php if ($this->_tpl_vars['user']['item_disc_only_allow_percent']): ?>checked <?php endif; ?> value="1" />
			Force this user to only allow discount by percentage for Item Discount
		<?php endif; ?>
	</td>
</tr>
<tr <?php if (! $this->_tpl_vars['mprice_list']): ?>style="display:none"<?php endif; ?>>
<td><b>Allow Mprice</b></td>
<td>
	<ul style="list-style:none; margin:0; padding:0;">	
		<?php $this->assign('mp', $_REQUEST['allow_mprice']); ?>
		<li style="float:left; padding-right:10px; margin:0;"><input type="checkbox" type="margin-left:0;" name="allow_mprice[not_allow]" onclick="check_user_profile_allow_mprice_list(this)" <?php if ($this->_tpl_vars['mp']['not_allow']): ?>checked<?php endif; ?>> Not Allow</li>
		<?php $_from = $this->_tpl_vars['mprice_list']; if (!is_array($_from) && !is_object($_from)) { settype($_from, 'array'); }if (count($_from)):
    foreach ($_from as $this->_tpl_vars['val']):
?>
		<li class="user_profile_mprice_list" <?php if ($this->_tpl_vars['mp']['not_allow']): ?>style="display:none;float:left; padding-right:10px; margin:0;"<?php else: ?>style="float:left; padding-right:10px; margin:0;"<?php endif; ?>  ><input type="checkbox" style="margin-left:0;" name="allow_mprice[<?php echo $this->_tpl_vars['val']; ?>
]" <?php if ($this->_tpl_vars['mp'][$this->_tpl_vars['val']]): ?>checked<?php endif; ?> /> <?php echo $this->_tpl_vars['val']; ?>
</li>
		<?php endforeach; endif; unset($_from); ?>
	
	</ul>
</td>
</tr>
<tr>
	<td><b>Privilege</b></td>
	<td>
		<input type=checkbox id=as_usetpl name=use_template onClick="ctoggle(this.checked)"> <label for="as_usetpl">Use Template</label> &nbsp;&nbsp;&nbsp;
		<select name=template_id onChange="stoggle(this.value)">
		<option value=0>----------</option>
		<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['templates']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
		<option value=<?php echo $this->_tpl_vars['templates'][$this->_sections['i']['index']]['id']; ?>
><?php echo $this->_tpl_vars['templates'][$this->_sections['i']['index']]['u']; ?>
</option>
		<?php endfor; endif; ?>
		</select>
	</td>
</tr>
</table>
<table>

<div id=custompriv style="padding:10px;width:100%;overflow:auto;">
<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "user_privilege_table.tpl", 'smarty_include_vars' => array('user_privilege' => $_REQUEST['user_privilege'])));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
</div>

<p align=center><input class="btn btn-primary" name=submitbtn type=submit value="Add"></p>
</form>
</div>
<?php endif; ?>

<div style="visibility:hidden"><iframe name=_irs width=1 height=1 frameborder=0></iframe></div>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "footer.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>
<script>
shide($('as_template').checked);
</script>