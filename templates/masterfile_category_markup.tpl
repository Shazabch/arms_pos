{include file=header.tpl}
<script>
var phpself = '{$smarty.server.PHP_SELF}';
var branch_id = '{$smarty.request.branch}';
var branch_code = '{$BRANCH_CODE}';
</script>

{literal}
<style>
.input_markup{
	width:60px;
	text-align:right;
}
.c{
	text-align:center;
}
</style>

<script>
function toggleSub(ele,tree_str,cat_id){
	// find whether child already load or not
	var child_class_startWith = tree_str+'(';
	var child_found = false;
	var all_tr = $('cat_table').getElementsByTagName('tr');
	
	var changeTo = ele.title;
	var level = countChar('(',tree_str);
	
	for(var i=0; i<all_tr.length; i++){
		if(all_tr[i].className.indexOf(child_class_startWith)>=0){
		    child_found = true;
		    if(changeTo=='Expand'){
                if(countChar('(',all_tr[i].className)==level+1){
	                all_tr[i].style.display = '';
				}
			}else{
			    if(countChar('(',all_tr[i].className)>=level+1){
			        all_tr[i].style.display = 'none';
			        var link = all_tr[i].getElementsByTagName('a');
			        if(link.length>0)	link[0].title = 'Expand';
			    }
			}
		}
	}

	if(changeTo=='Expand'){
		ele.title = 'Collapse';
	}else{
	    ele.title = 'Expand';
	}
		
	if(!child_found){
        new Ajax.Request(phpself,
		{
		    method: 'post',
		    parameters:{
				a: 'load_child',
				root_id: cat_id
			},
			onComplete:function(e){
				if(e.responseText.indexOf('Error: ')>=0){
					alert(e.responseText);
					return;
				}
				new Insertion.After(ele.parentNode.parentNode, e.responseText);
			}
		});
	}
}

function countChar(c,str){
	var count = 0;
	for(var i=0; i<str.length; i++){
		if(str[i]==c){
			count ++;
		}
	}
	return count;
}

function updateField(ele,cat_id){
    miz(ele);
	var value = ele.value;
	var bid = ele.title;
	
	ele.disabled = true;
	new Ajax.Request(phpself,
	{
	    method: 'post',
	    parameters:{
			a: 'updateField',
			cat_id: cat_id,
			bid: bid,
			value: value
		},
		onComplete:function(e){
			if(e.responseText.indexOf('Error: ')>=0){
				alert(e.responseText);
				ele.disabled = false;
				return;
			}
			ele.disabled = false;
			if(int(value)==0)   return;
			var parent_tr = ele.parentNode.parentNode;
			var tree_str = parent_tr.className;
			
			//var inputs = $$('#'+tree_str+' input[class=input_markup]');
			var inputs = parent_tr.getElementsByTagName('input');
			var start_change = false;
			for(var i=0;i<inputs.length; i++){
				if(inputs[i].className=='input_markup'){
				    if(start_change){
						if(int(inputs[i].value)==0){
						    inputs[i].value = value;
                            updateField(inputs[i],cat_id);
						}else{
							break;
						}
					}
					if(inputs[i].title==bid){
						start_change = true;
					}
				}
			}
		}
	});
}
</script>
{/literal}
<h1>{$PAGE_TITLE}</h1>
<ul>
<li> Click on Category to expand
</ul>
<!--{*
<form name="form_markup" method="post">
<input type="hidden" name="a" value="start_load">
<div class=stdframe style="background:#fff;">

{if $BRANCH_CODE eq 'HQ'}
<b>Branch: </b>
<select name="branch">
	{foreach from=$branch_list item=r}
	    <option value="{$r.id}" {if $smarty.request.branch eq $r.id} selected {/if}>{$r.code}</option>
	    {if $smarty.request.branch eq $r.id}
			{assign var=bcode value=$r.code}
	    {/if}
	{/foreach}
</select>&nbsp;
{/if}

<input type="submit" name="submits" value="Refresh">
</div>
</form>
*}-->
<br />
{if !$category}
{if isset($smarty.request.submits)}-- No Data --{/if}
-- No Data --
{else}
{assign var=my_branch value=$sessioninfo.branch_id}
<table border=0 width=100% cellpadding=0 cellspacing=0 id="cat_table">
<tr height=30 bgcolor={#TB_COLHEADER#}>
	<th>Category</th>
	{if $BRANCH_CODE eq 'HQ'}
	    {foreach from=$branch_list key=bid item=b}
            <th>{$b.code}</th>
	    {/foreach}
	{else}
	    <th>{$BRANCH_CODE}</th>
	{/if}
</tr>
{foreach from=$category key=cid item=r name=topf}
<tr class="{$r.tree_str}({$cid})"  onmouseout="this.bgColor='';" onmouseover="this.bgColor='#ffffcc';">
	<td>
	{if $smarty.foreach.topf.last}
		<img align="absmiddle" src="ui/tree_e.png"/>
	{else}
		<img align="absmiddle" src="ui/tree_m.png"/>
	{/if}
	<a href="javascript:" onClick="toggleSub(this,'{$r.tree_str}({$cid})','{$cid}');" title="Expand">{$r.description}
	</a>
	</td>
	{if $BRANCH_CODE eq 'HQ'}
	    {foreach from=$branch_list key=bid item=b}
            <td class="c" title="{$bid}"><input type="text" name="markup[{$bid}][{$cid}]" class="input_markup" onChange="updateField(this,'{$cid}');" title="{$bid}" value="{$markup.$bid.$cid.markup}" /></td>
	    {/foreach}
	{else}
	    <td class="c" title="{$my_branch}"><input type="text" name="markup[{$my_branch}][{$cid}]" class="input_markup" onChange="updateField(this,'{$cid}');" title="{$my_branch}" value="{$markup.$my_branch.$cid.markup}" /></td>
	{/if}
</tr>
	<!--{*{include file=masterfile_category_markup.cat.tpl}*}-->
{/foreach}
</table>
{/if}
{include file=footer.tpl}
