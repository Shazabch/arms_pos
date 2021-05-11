<?php /* Smarty version 2.6.18, created on 2021-05-07 18:20:54
         compiled from promotion.approval.tpl */ ?>

<?php $_smarty_tpl_vars = $this->_tpl_vars;
$this->_smarty_include(array('smarty_include_tpl_file' => "header.tpl", 'smarty_include_vars' => array()));
$this->_tpl_vars = $_smarty_tpl_vars;
unset($_smarty_tpl_vars);
 ?>

<script>
var promotion_approval_allow_reject_by_items = '<?php echo $this->_tpl_vars['config']['promotion_approval_allow_reject_by_items']; ?>
';
</script>

<?php echo '
<script>

function do_terminate(last_approver){
	document.f_c.approve_comment.value = \'\';
	var p = prompt(\'Enter reason to terminate:\');
	if (p.trim()==\'\' || p==null) return;
	document.f_c.approve_comment.value = p;
	if (confirm(\'Press OK to Terminate the Promption.\')){
	    document.f_c.a.value = "terminate_approval";
		document.f_c.submit();
	}
}

function do_kiv(){
	if (confirm(\'Press OK to KIV the Promotion.\')){
	    document.f_c.a.value = "kiv_approval";
	    document.f_c.submit();
	}
}

function do_approve(last_approver,type){
	
	if (promotion_approval_allow_reject_by_items) {
	
		//make sure each of them has reason
		rejected_item_cb = document.getElementsByClassName(\'rejected_item_cb\');
		for (var index = 0, len = rejected_item_cb.length; index < len; ++index) {
			var item = rejected_item_cb[index];
			if ($(item).checked) {
				if ($(item).next(\'span\').down().value.trim() == \'\') {
					alert(\'Please provide reason for each rejected item\');
					$(item).next(\'span\').down().focus();
					return;
				}
			}
		}
		
		//make not not all items were rejected
		if (type==\'discount\') item_count = document.getElementsByClassName(\'item_no\').length;
		if (type==\'mix&match\') item_count = document.getElementsByClassName(\'div_promo_group\').length;
		document.getElementsByClassName(\'rejected_item_cb\').each(function(item) {
			if ($(item).checked) item_count--;
		});
		if (item_count < 1) {
			alert(\'Cannot reject all promotion items\');
			return;
		}
	}
	//alert(\'ready to submit\');return;
	
    document.f_c.approve_comment.value=\'Approve\';
	if (confirm(\'Press OK to Approve the Promotion.\')){
	    document.f_c.a.value = "save_approval";
	    document.f_c.rejected_item_data.value = Form.serialize(document.f_a);
	    document.f_c.submit();
	}
}

function do_reject(last_approver){
	document.f_c.approve_comment.value = \'\';
	var p = prompt(\'Enter reason to reject:\');
	if (p.trim()==\'\' || p==null) return;
	document.f_c.approve_comment.value = p;
	if (confirm(\'Press OK to Reject the Promotion.\')){
	    document.f_c.a.value = "reject_approval";
	    document.f_c.submit();
	}
}


function select_tab(obj){
	if (obj == undefined){
		var lst = $(\'tab\').getElementsByTagName("LI");
		if (lst.length==0){
			alert(\'Congratulation! You have completed all approval jobs.\\nTake a break ;)\');
			document.location = \'/home.php\';
			return;
		}

		lst[0].className = "active";
		load_approval(lst[0]);
	}
	else{
		var lst = $(\'tab\').getElementsByTagName("LI");
		$A(lst).each( function(r,idx) {
			if (r.className == "active")
				r.className = \'\';
		});
		obj.className = "active";
		load_approval(obj);
	}
}

function load_approval(obj){
    var line = obj.title.split(",");
	id =line[0];
	branch_id = line[1];

	$(\'sel_name\').innerHTML = obj.innerHTML;

	$(\'loadpromo\').innerHTML = \'<img src=ui/clock.gif align=absmiddle> Loading...<br><img src=ui/pixel.gif height=500 width=1>\';

	new Ajax.Updater(\'loadpromo\', \'promotion_approval.php\', {
		parameters: \'a=ajax_load_promotion&id=\'+id+\'&branch_id=\'+branch_id+\'&\'+Form.serialize(document.f_on_behalf),
		evalScripts: true
		});
}

var context_info;

function hide_context_menu(){
	$(\'ul_menu\').onmouseout = undefined;
	$(\'ul_menu\').onmousemove = undefined;	 
	Element.hide(\'item_context_menu\');
}

function show_context_menu(obj, id, item_id, is_foc){
	context_info = { element: obj, id: id, sku_item_id: item_id, is_foc: is_foc};
	$(\'item_context_menu\').style.left = ((document.body.scrollLeft)+mx) + \'px\';
	$(\'item_context_menu\').style.top = ((document.body.scrollTop)+my) + \'px\';
	Element.show(\'item_context_menu\');
	
	$(\'ul_menu\').onmouseout = function() {
		context_info.timer = setTimeout(\'hide_context_menu()\', 100);
	}
	
	$(\'ul_menu\').onmousemove = function() {
		if (context_info.timer!=undefined) clearTimeout(context_info.timer);
		context_info.timer = undefined;
	}
	return false;
}

function get_item_po_history(id){
	center_div(\'price_history_popup\');
	Element.show(\'price_history_popup\');
	$(\'price_history_list_popup\').innerHTML = \'<img src=ui/clock.gif align=absmiddle> Loading...\';
	new Ajax.Updater(\'price_history_list_popup\',\'ajax_sku_popups.php\',{
		    parameters: \'a=sku_po_history&id=\'+id,
		    evalScripts:true
	});
}

function get_item_sales_trend(id){
	center_div(\'price_history_popup\');
	Element.show(\'price_history_popup\');
	$(\'price_history_list_popup\').innerHTML = \'<img src=ui/clock.gif align=absmiddle> Loading...\';
	new Ajax.Updater(\'price_history_list_popup\',\'ajax_sku_popups.php\',{
		    parameters: \'a=sku_sales_trend&id=\'+id,
		    evalScripts:true
	});
}

function reject_cb_clicked(e) {
	if (e.checked) $(e).next(\'span\').show();
	else $(e).next(\'span\').hide();
}
</script>

<style>
#tab_sel {
	border:1px solid #ccc;
	width:700px;
	padding:4px;
	background:#fff url(\'/ui/findcat_expand.png\') right center no-repeat;
}

#tab_sel ul {
	position:absolute;
	visibility:hidden;
	background:#fff;
	border:1px solid #ccc;
	border-top:none;
	list-style:none;
	margin:0;padding:0;
	margin-left:-5px;
	margin-top:5px;
	width:708px;
	height:300px;
	overflow:auto;
}
#tab_sel ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}
#tab_sel ul li:hover {
	background:#ff9
}

#tab_sel:hover ul {
	visibility:visible;
}

</style>
'; ?>


<h1><?php echo $this->_tpl_vars['PAGE_TITLE']; ?>
<?php if ($this->_tpl_vars['approval_on_behalf']): ?> (on behalf of <?php echo $this->_tpl_vars['approval_on_behalf']['on_behalf_of_u']; ?>
)<?php endif; ?></h1>

<div style="float:left;padding:4px;"><b>Select Promotion to approve</b></div>

<div style="float:left" id=tab_sel><span id=sel_name>-</span>
<ul id=tab>
<?php unset($this->_sections['i']);
$this->_sections['i']['name'] = 'i';
$this->_sections['i']['loop'] = is_array($_loop=$this->_tpl_vars['promotion']) ? count($_loop) : max(0, (int)$_loop); unset($_loop);
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
<?php echo '<li onclick="select_tab(this)" id="tab'; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['id']; ?><?php echo '" title="'; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['id']; ?><?php echo ','; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['branch_id']; ?><?php echo '">&nbsp;Promo#'; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['id']; ?><?php echo ' &nbsp;(Branch: '; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['branch_name']; ?><?php echo ', From: '; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['date_from']; ?><?php echo ' '; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['time_from']; ?><?php echo ' to '; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['date_to']; ?><?php echo ' '; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['time_to']; ?><?php echo ', Created By: '; ?><?php echo $this->_tpl_vars['promotion'][$this->_sections['i']['index']]['user_name']; ?><?php echo ')</li>'; ?>

<?php endfor; endif; ?>
</ul>
</span>
</div>

<br style="clear:both">
<br>

<form name="f_on_behalf">
	<?php if ($this->_tpl_vars['approval_on_behalf']): ?>
	<input type="hidden" name="on_behalf_of" value="<?php echo $this->_tpl_vars['approval_on_behalf']['on_behalf_of']; ?>
" />
	<input type="hidden" name="on_behalf_by" value="<?php echo $this->_tpl_vars['approval_on_behalf']['on_behalf_by']; ?>
" />
	<?php endif; ?>
</form>

<div id=loadpromo>
</div>

<script>
select_tab();
</script>
