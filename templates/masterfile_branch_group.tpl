{include file=header.tpl}
{literal}
<style>

</style>
{/literal}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
{literal}

function curtain_clicked(){
	$('ndiv').hide();
}

function add(){
	open(0,0,0);
}

function open(id){
    curtain(true);
	$('ndiv').show();
	//center_div('ndiv');
	$('div_branch_group_table').update(_loading_);
	new Ajax.Updater('div_branch_group_table',phpself,{
		parameters:{
			a: 'open',
			id: id
		}
	});
}

function insert_branch(){
	var s_index = $('select_branches').selectedIndex;
	if(s_index<0)  return false;
	
	var select = $('select_branches');
	for(var i=0; i<select.length; i++){
		if(select.options[i].selected){
            var opt = select.options[i];
            try {
				$('select_branches_list').add(opt,null); // standards compliant; doesn't work in IE
			}catch(ex) {
				$('select_branches_list').add(opt); // IE only
			}
			opt.selected = false;
			i--;
		}
	}
}

function remove_branch(){
	var s_index = $('select_branches_list').selectedIndex;
	if(s_index<0)  return false;

	var select = $('select_branches_list');
	for(var i=0; i<select.length; i++){
		if(select.options[i].selected){
            var opt = select.options[i];
            try {
				$('select_branches').add(opt,null); // standards compliant; doesn't work in IE
			}catch(ex) {
				$('select_branches').add(opt); // IE only
			}
			opt.selected = false;
			i--;
		}
	}
}

function save(){
	var code = document.form_branch_open.code.value.trim();
	var desc = document.form_branch_open.description.value.trim();
	if(code==''){
		alert('Please enter code.');
		document.form_branch_open.code.focus();
		return false;
	}
	
	if(desc==''){
		alert('Please enter name.');
		document.form_branch_open.description.focus();
		return false;
	}
	if($('select_branches_list').length<=1){
		alert('Please add at least 2 branches');
		return false;
	}
	
	var select = $('select_branches_list');
	for(var i=0; i<select.length; i++){
		select.options[i].selected = true;
	}
	$('btn_save').disabled = true;
	new Ajax.Request(phpself,{
		parameters: $('form_branch_open').serialize(),
		onLoading:function(){
            $('select_branches_list').selectedIndex=-1;
		},
		onComplete:function(e){
			if(e.responseText=='OK'){
				alert('Save successfully.');
				reload_table();
			    default_curtain_clicked();
			}else{  // error found
				alert(e.responseText);
			}
			$('btn_save').disabled = false;
		}
	});
}

function reload_table(){
	new Ajax.Updater('div_table',phpself+'?a=reload_table');
}

function deleteGroup(id){
	if(!confirm('Click OK to confirm delete.')) return;
	new Ajax.Updater('div_table',phpself,{
		parameters:{
            a: 'delete_group',
			id: id
		}
	});
}

function ucwords(ele){
	ele.value = ele.value.capitalize(true);
}
{/literal}
</script>


<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">{$PAGE_TITLE}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body"><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Branch Group</a> (Alt+A)</div>
</div>

<br>
<div class="card mx-3">
	<div class="card-body">
		<div id="div_table">{include file='masterfile_branch_group.table.tpl'}</div>
	</div>
</div>

<!-- Branch Group poppup -->
<div class="ndiv" id="ndiv" style="background-color:#ffffff; padding:10px; border:1px solid gray;border-radius:7px;position:absolute;left:350px;top:100px;display:none;z-index:10000;width:600px;">
<div class="shadow"><div class="content">

<div class="small mt-2" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(default_curtain_clicked())" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

<br />
<div id="div_branch_group_table"></div>

</div></div></div>
<!-- End of Branch Group poppup -->

{include file=footer.tpl}

<script>
{literal}
new Draggable('ndiv');
{/literal}
</script>
