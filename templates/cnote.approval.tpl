{include file='header.tpl'}

{literal}
<style>
#tab_sel {
	border:1px solid #ccc;
	width:500px;
	padding:4px;
	background:#fff url('/ui/findcat_expand.png') right center no-repeat;
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
	width:508px;
	height:300px;
	overflow:auto;
}
#tab_sel ul li {
	cursor:pointer;
	display:block;
	margin:0;padding:4px;
}

#tab_sel ul li.active {
	background-color: #fcf;
}

#tab_sel ul li:hover {
	background:#ff9
}

#tab_sel:hover ul {
	visibility:visible;
}

</style>
{/literal}

<script>

var phpself = '{$smarty.server.PHP_SELF}';
{literal}
var CNOTE_APPROVAL = {
	initialize: function(){
		// auto select first
		this.auto_select_tab();
	},
	// function to select a cnote
	auto_select_tab: function(){
		// get selected li
		var selected_li = undefined;
		var li_cn_list = $$('#tab li.li_cn');
		
		// no more item
		if(li_cn_list.length<=0){
			alert('Congratulation! You have completed all approval jobs.\nTake a break ;)');
			document.location = '/home.php';
			return;
		}
		
		// loop each li to get which is selected
		for(var i=0,len=li_cn_list.length; i<len; i++){
			if($(li_cn_list[i]).hasClassName('active')){
				selected_li = li_cn_list[i];
				break;
			}
		}
		
		// no li is selected
		if(!selected_li){
			// auto select the first
			var ids = li_cn_list[0].id.split('-');
			this.select_cn(ids[1], ids[2]);
		}
	},
	// function when user click on a cnote 
	cn_row_clicked: function(bid, cn_id){
		this.select_cn(bid, cn_id);
	},
	// function to select cnote
	select_cn: function(bid, cn_id){
		var li = $('li_cn-'+bid+'-'+cn_id);
		if(!li)	return;
		
		// remove active from all li
		$$('#tab li.li_cn').each(function(ele){
			$(ele).removeClassName('active');
		});
		
		// mark this as selected
		$(li).addClassName('active');
		$('sel_name').update(li.innerHTML);
		
		$('div_container').update(_loading_);
		
		var params = {
			'a': 'ajax_load_cn',
			'branch_id': bid,
			'id': cn_id
		};
		
		// ajax_add_item_row
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			evalScripts: true,
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok'] && ret['html']){ // success
						$('div_container').update(ret['html']);
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
			    $('div_container').update(err_msg);
			}
		});
	},
	// function when user click cancel/terminate
	do_cancel: function (){
		this.submit_approval('cancel');
	},
	// function when user click reject
	do_reject: function (){
		this.submit_approval('reject');
	},
	// function when user click approve
	do_approve: function (){
		this.submit_approval('approve');
	},
	// core function to submit approval
	submit_approval: function(type){
		if(!type)	return;
		if(type != 'approve' && type != 'reject' && type != 'cancel'){
			alert('Invalid Action');
			return false;
		}
		
		var THIS = this;
		var reason = '';
		if(type == 'cancel'){
			var p = prompt('Enter reason to terminate/Cancel:');
			if (p==null || p.trim()=='') return;
			
			if (!confirm('Press OK to Terminate.'))	return;
			reason = p;
		}else if(type == 'reject'){
			var p = prompt('Enter reason to reject:');
			if (p==null || p.trim()=='') return;
			
			if (!confirm('Press OK to Reject.'))	return;
			reason = p;
		}else{
			if (!confirm('Press OK to Approve.'))	return;
		}
		
		var bid = document.f_a['branch_id'].value;
		var cn_id = document.f_a['id'].value;
		
		var params = {
			'a': 'ajax_save_cn_approval',
			'branch_id': bid,
			'id': cn_id,
			'type': type,
			'reason': reason
		};
		this.toggle_processing_form(true);
		
		// ajax_add_item_row
		new Ajax.Request(phpself, {
			parameters: params,
			method: 'post',
			
			onComplete: function(msg){
				var str = msg.responseText.trim();
				var ret = {};
			    var err_msg = '';

			    try{
	                ret = JSON.parse(str); // try decode json object
	                if(ret['ok']){ // success
						THIS.toggle_processing_form(false);
						// remove li
						$('li_cn-'+bid+'-'+cn_id).remove();
						$('div_container').update(_loading_);
						// select next
						THIS.auto_select_tab();
		                return;
					}else{  // save failed
						if(ret['failed_reason'])	err_msg = ret['failed_reason'];
						else    err_msg = str;
					}
				}catch(ex){ // failed to decode json, it is plain text response
					err_msg = str;
				}

			    // prompt the error
			    alert(err_msg);
				THIS.toggle_processing_form(false);
			}
		});
	},
	// function to show/hide form loading
	toggle_processing_form: function(is_show){
		if(is_show){
			center_div($('div_wait_popup').show());
			curtain(true,'curtain2');
		}else{
			$('div_wait_popup').hide();
			curtain(false,'curtain2');
		}
	},

};
{/literal}
</script>


<h1>{$PAGE_TITLE}</h1>

<div style="float:left;padding:4px;"><b>Select to approve</b></div>

<div style="float:left" id="tab_sel">
	<span id="sel_name">-</span>
		<ul id="tab">
			{foreach from=$cn_list item=r}
				{strip}
					<li onclick="CNOTE_APPROVAL.cn_row_clicked('{$r.branch_id}', '{$r.id}');" id="li_cn-{$r.branch_id}-{$r.id}" class="li_cn">
						 &nbsp;
						 {$r.cn_no} &nbsp;
						 (Branch: {$r.branch_name}, Date: {$r.cn_date}, Created: {$r.user_name})
					 </li>
				{/strip}
			{/foreach}
		</ul>
	</span>
</div>

<br style="clear:both">
<br>

<div id="div_container"></div>

<script>CNOTE_APPROVAL.initialize();</script>
{include file='footer.tpl'}