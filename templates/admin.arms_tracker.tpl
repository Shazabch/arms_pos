{*
REVISION HISTORY
================
2/29/2008 5:20:16 PM gary
- add searching function.
*}
{include file=header.tpl}
{literal}
<style>
.box
{
 	float:left;
	width:50%;
	/*overflow:scroll;*/
}
.box div.b
{
	margin:2px;
	background:#eef;
	border:2px solid #dde;
	padding:5px;
}
.box ul
{
	list-style-type:none;
	border-top:1px solid #ccd;
	padding:0;
	margin:0;
	height:500px;
	overflow:auto;
}
.box li
{
	padding:2px 0;margin:0;
	border-bottom:1px solid #ccd;
}
.box li:hover
{
	background:#fff;
}
.box textarea, .box
{
	font:10px Arial;
}
</style>

<script>
function done_addpic(id,html)
{
	$('pics['+id+']').innerHTML = html;
}

function addpic(td_obj,id)
{
	$('attach_popup').innerHTML = '<form onsubmit="hidediv(\'attach_popup\')" target=_ifaddpic method=post enctype="multipart/form-data"><input type=hidden name=id value="'+id+'"><input type=hidden name=a value=add_pic><h4>Screenshot/XLS/PDF Attachment</h4>Select files to upload:<br>1.<input type=file name="screenshot[]"><br>2.<input type=file name="screenshot[]"><br>3.<input type=file name="screenshot[]"><br><input type=submit value="Submit"><input type=reset value="Close" onclick="hidediv(\'attach_popup\')"></form><iframe width=1 height=1 style="visibility:hidden" name=_ifaddpic></iframe>';
	showdiv('attach_popup');
	Position.clone(td_obj, $('attach_popup'), {setHeight: false, setWidth:false});
	
}

function cancel_this(id){
	document.f_a.item.value=id;
	document.f_a.a.value='delete_item';
	document.f_a.submit();
}


function verify(id,el){
	if (!confirm('mark as verified by you?')) return false;
	new Ajax.Request('{/literal}{$smarty.server.PHP_SELF}{literal}',
	{
    	parameters:'a=verify&id='+id,
    	onComplete:function() { el.src = '/ui/lock.png'; }
	} );
	//do_refresh();
}

function solve(id,el){
    if (!confirm('mark as solved by you?')) return false;
	new Ajax.Request('{/literal}{$smarty.server.PHP_SELF}{literal}',
	{
    	parameters:'a=solve&id='+id,
    	onComplete:function() { el.src = '/ui/approved.png'; }
	} );
	//do_refresh();
}

var td_obj;
var g_id;

function edit_this(obj,id)
{
	g_id=id;
	td_obj=$('td_'+id);
	$('edit_value').value =td_obj.innerHTML.replace(/^&nbsp;/,'');
	Position.clone(td_obj, $('edit_popup'), {setHeight: false, setWidth:false});
	Position.clone(td_obj, $('edit_value'), {setHeight: false, setWidth:false});
	Element.show('edit_popup');
	$('edit_value').select();
	$('edit_value').focus();
}

function save_popup(){
	Element.hide('edit_popup');
	//td_obj.innerHTML=$('edit_value').value;	
	var new_value=$('edit_value').value;
	if(td_obj.innerHTML!=new_value && new_value!=''){		
		td_obj.innerHTML=new_value;
		var newp = td_obj;
		new Ajax.Updater(newp,'admin.arms_tracker.php?id='+g_id+'&value='+escape(new_value)+'&'+Form.serialize(document.f_a)+'&a=save_edit',{onComplete:function(){update_table(newp)}});
	}
}

function update_table(cell){
	new Effect.Highlight(cell);
}

var counter=0;

function do_add_new(p,total){
	control_row('a');
	//alert(counter);
	var no=float(total)+counter;
	//alert(no);
	var row_html='<td align=center><img src=/ui/remove16.png title="Cancel" onclick="Element.remove(this.parentNode.parentNode);control_row(\'r\');"></td><td align=left colspan=6><input type=hidden name=new_p['+no+'] value='+p+'><select name=new_type['+no+'] size=6>{/literal}{section name=i loop=$type}<option value="{$type[i].type}" {if $selected_type==$type[i].type}selected{/if}>{$type[i].type|upper}</option>{/section}{literal}</select><textarea name="new_value['+no+']" cols=90 rows=5></textarea><b>Screenshot Attachment</b><br>1.<input type=file name="new_screenshot['+no+'][]"><br>2.<input type=file name="new_screenshot['+no+'][]"><br>3.<input type=file name="new_screenshot['+no+'][]"><br>';
	new Insertion.After('tr_'+p, row_html);
}

function control_row(action){
	if(action=='a'){
		counter++;	
	}
	else{
		counter--;	
	}

	if(counter>0){
		Element.show('bsubmit');		
	}
	else{
		Element.hide('bsubmit');	
	}
}

function do_save_all(){
	document.f_a.a.value='save_all';
	document.f_a.submit();
}

function do_refresh(){
	document.f_a.submit();
}

function do_move_up(p,id){
	new Ajax.Updater('tbl_tracker', 'admin.arms_tracker.php',
	{
		parameters: 'ajax=1&'+Form.serialize(document.f_a)+'&a=move_up&priority='+p+'&id='+id,
		evalScripts: true
	});
}

function do_move_down(p,id){
	new Ajax.Updater('tbl_tracker', 'admin.arms_tracker.php',
	{
		parameters: 'ajax=1&'+Form.serialize(document.f_a)+'&a=move_down&priority='+p+'&id='+id,
		evalScripts: true
	});
}

function do_change_type(id){
	var pt=$('item_type_'+id).value;
	new Ajax.Updater('tbl_tracker', 'admin.arms_tracker.php',
	{
		parameters: 'ajax=1&'+Form.serialize(document.f_a)+'&a=change_type&id='+id+'&pass_type='+pt,
		evalScripts: true
	});
}

function do_sort(strSort){
  var form = document.f_a;

  if(strSort == form.s_field.value){
    if(form.s_arrow.value == 'ASC'){
     form.s_arrow.value = 'DESC';
    }
    else{
      form.s_arrow.value = 'ASC';
    }
  }
  else{
    form.s_arrow.value = 'ASC';
    form.s_field.value = strSort;
  }
	new Ajax.Updater('tbl_tracker', 'admin.arms_tracker.php',
	{
		parameters: 'ajax=1&'+Form.serialize(document.f_a)+'&a=ajax_sort_list',
		evalScripts: true
	});
}

function do_move(type){
	//alert(type);
	Element.show('adjust_popup');
	center_div('adjust_popup');	
	curtain(true);
}

function mdn(src)
{
    if (src.selectedIndex == -1)
	{
		alert('Select an item to move');
		return;
	}
	if (src.selectedIndex < src.options.length-1)
	{
	    // move it
		var t = src.options[src.selectedIndex+1].text;
		var v = src.options[src.selectedIndex+1].value;
		src.options[src.selectedIndex+1].text = src.options[src.selectedIndex].text;
		src.options[src.selectedIndex+1].value = src.options[src.selectedIndex].value;
		src.options[src.selectedIndex].text = t;
		src.options[src.selectedIndex].value = v;
        src.selectedIndex++;
	}

}

function mup(src)
{
    if (src.selectedIndex == -1)
	{
		alert('Select an item to move');
		return;
	}
	if (src.selectedIndex > 0)
	{
	    // move it
		var t = src.options[src.selectedIndex-1].text;
		var v = src.options[src.selectedIndex-1].value;
		src.options[src.selectedIndex-1].text = src.options[src.selectedIndex].text;
		src.options[src.selectedIndex-1].value = src.options[src.selectedIndex].value;
		src.options[src.selectedIndex].text = t;
		src.options[src.selectedIndex].value = v;
        src.selectedIndex--;
	}

}
function check_b()
{
	sel1 = document.f_b.elements["sel_approvals[]"];

	if (sel1.options.length <= 0 && !$('move_approvals').disabled)
	{
		alert('You must select at least one user for approval');
		return false;
	}	

	for (i=0;i<sel1.options.length;i++)
	{
	    sel1.options[i].selected = true;
	}
	return true;
}

function curtain_clicked(){	
	curtain(false);
	Element.hide('adjust_popup');
}

function do_save_moving(){
	if (check_b()){
		document.f_b.a.value='save_moving';
		document.f_b.submit();	
	}
}

/*
function do_search(){
	if (empty(document.f_a.search, "i won't search empty string. ")){
	    return false;
	}
	document.f_a.a.value='search';
	document.f_a.submit();	
}
*/
</script>
{/literal}

<h1>{$PAGE_TITLE}</h1>

<div id=attach_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;padding:10px;width:300px;height:150px;">
</div>


<div id=edit_popup style="display:none;position:absolute;z-index:100;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;width:732px;height:100px;">
<textarea id=edit_value name=edit_value onblur="save_popup();" cols=88 rows=5></textarea>
</div>

<div id=adjust_popup style="display:none;position:absolute;z-index:10000;background:#fff;border:2px solid #000;margin:-2px 0 0 -2px;width:800px;height:460px;">
<form method=post name=f_b>
<input type=hidden name=a>
<table class=tb border=0 cellspacing=0 cellpadding=2>
<tr>
<th colspan=2 align=left>Trackers</th>
</tr>
<tr>
<td>

<select name=sel_approvals[] multiple size=25 style="width:750px">
{if $all_issues}
{section name=i loop=$all_issues}
{assign var=n value=$smarty.section.i.iteration}
<option value="{$all_issues[i].id}">
{$all_issues[i].description}
</option>
{/section}
{/if}
</select>
</td>
<td valign=top>
<input type=button value="Up" onclick="mup(f_b.elements['sel_approvals[]'])"><br><br>
<input type=button value="Dn" onclick="mdn(f_b.elements['sel_approvals[]'])"><br><br>
</td>
</tr>

<tr>
<td colspan=2 align=center>
<input type=button value="Save" onclick="do_save_moving();">
&nbsp;&nbsp;&nbsp;
<input type=button value="Close" onclick="curtain_clicked();">
</td>
</tr>
</table>
</form>
</div>


<form name=f_a method=post enctype="multipart/form-data">
<input type=hidden name=a value=search>
<input type=hidden name=item>

<select name=type onchange="do_refresh();">
<option value="All">ALL</option>
{section name=i loop=$type}
<option value="{$type[i].type}" {if $selected_type==$type[i].type}selected{/if}>
{$type[i].type|upper}</option>
{/section}
<option value="verified" {if $selected_type=='verified'}selected{/if}>VERIFIED</option>
</select>
{if !$selected_type}
{assign var=selected_type value=ALL}
{/if}
&nbsp;&nbsp;&nbsp;

{if $selected_type eq 'ALL'}
<a href="javascript:void(do_move('{$selected_type|upper}'))">
<img src=/ui/icons/table_refresh.png align=absmiddle border=0 title="Move Priority" onclick="do_move('{$selected_type|upper}');"> Resort The Priority</a>
{/if}

&nbsp;&nbsp;&nbsp;&nbsp;
<input name=s size="18">
&nbsp;
<input type=submit id=search value="Search">

<br><br>

<table id=tbl_tracker class=tb border=0 cellspacing=0 cellpadding=2 width="100%">
{include file=admin.arms_tracker.items.tpl}
</table>

</form>

<p align=center>

<input id=bsubmit type=button value="Save New Tracker" style="display:none;font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save_all()" >

</p>

{include file=footer.tpl}
<script>
document.f_a.s.focus();
</script>
