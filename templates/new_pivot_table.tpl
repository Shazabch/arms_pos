{*
9/4/2007 5:05:50 PM yinsee
- add format:/(.*)/ to scriptaculous Sortable object to handle underscore names
*}
{include file=header.tpl}
{literal}
<style>
.clickable { cursor: pointer; }
.chover { background-color:#ff9; }
ul.dragable { margin:0; padding:0; list-style-type: none; }
ul.dragable li { margin: 1px; padding: 2px; border:1px solid #999; white-space: nowrap; background-color:#fff;}

ul#l_cols, ul#l_rows, ul#l_data, ul#l_pg
{
    margin:0; padding:0; list-style-type: none;
}
ul.droplist li
{
	float:left; margin: 1px; padding:2px; cursor:pointer; border:1px solid #000; white-space: nowrap; background-color:#fff;
}
</style>

<script>
function preview_table()
{
	if ($('previewbtn').value == 'Preview Pivot')
	{
        $('previewbtn').value = 'Edit Pivot';
		$('droptarget').style.display = 'none';
		$('preview').style.display = '';
		$('preview').innerHTML = '<img src=/ui/clock.gif align=absmiddle> Loading...';

	    new Ajax.Updater(
			'preview', '{/literal}{$smarty.server.PHP_SELF}{literal}', {
				parameters: 'a=new&sa=preview_table&'+Sortable.serialize('l_pg') +'&'+Sortable.serialize('l_cols') +'&' + Sortable.serialize('l_rows') + '&' + Sortable.serialize('l_data')
			}
		);
	}
	else
	{
		$('previewbtn').value = 'Preview Pivot';
		$('droptarget').style.display = '';
		$('preview').style.display = 'none';
	}
}


function add_row(li)
{
	add_it(li.innerHTML.trim(), $('l_rows'), 'r');
}

function add_col(li)
{
	add_it(li.innerHTML.trim(), $('l_cols'), 'c');
}

function add_pg(li)
{
	add_it(li.innerHTML.trim(), $('l_pg'), 'pg');
}

function add_data(li)
{
	if (li.innerHTML.trim()=='MarkOn_Pct')
    	add_it(li.innerHTML.trim(), $('l_data'), 'd');
	else
		add_it($('newdata_type').value + '(' + li.innerHTML.trim() + ')', $('l_data'), 'd');
}

function add_it(str, ul, prefix)
{
	var new_node = document.createElement("li");
	new_node.innerHTML = str;
	//new_node.id = prefix+"_"+str;
	new_node.id = str;

	ul.appendChild(new_node);

	Sortable.destroy(ul);
	Sortable.create(ul, {format:/(.*)/, zindex:100, constraint:false});
}

function delete_it(li)
{
	ul = li.parentNode;
	Element.remove(li);

	Sortable.destroy(ul);
	Sortable.create(ul, {format:/(.*)/, zindex:100, constraint:false});
}

function save_new_table()
{
    $('fa').id.value = 0;
    save_table();
}


function save_table()
{
	var sz = Form.serialize('fa');
	Form.disable('fa');
   	new Ajax.Request(
		'{/literal}{$smarty.server.PHP_SELF}{literal}',
	   	{
	   		method: 'post',
			parameters: 'a=new&sa=save&' + Sortable.serialize('l_pg') +'&'+ Sortable.serialize('l_cols') +'&' + Sortable.serialize('l_rows') + '&' + Sortable.serialize('l_data') + '&' + sz,
	   		onSuccess: function(m) {
	   		    alert('Your Pivot Table has been saved.\n ID#'+m.responseText);
	   		    document.location = "{/literal}{$smarty.server.PHP_SELF}{literal}?a=edit&id="+m.responseText;
			   },
	   		onFailure: function() {
	   		    alert('System encountered a problem when saving, please try again.\n'+m.responseText);
	    	    Form.enable('fa');
			   }
		}
	);

}

function show_formula_help()
{
	alert('Sum (SUM)\n Total sum of selected Field\n \n Min (MIN)\n Minimum value from the set of values for selected Field\n \n Max (MAX)\n Maximum value from the set of values for selected Field\n \n Average (AVG)\n Average value from the set of values for selected Field\n \n Count (COUNT)\n Return the total transaction count that matches the row/column criteria. The count result is always the same regarding the field you choose, when the columns and rows are not changed.\n \n  Unique Count (UNIQUE)\n Count the number of unique value in the set of values. For example, The COUNT of (a,b,c,c,d) is 5 but the Unique Count is only 4.\n ');
}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
    <div class="my-auto">
        <div class="d-flex">
            <h4 class="content-title mb-0 my-auto ml-4 text-primary">{$title}</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
        </div>
    </div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
			<form>
			<b class="form-label">Load Existing Pivot</b> <input class="form-control" type=hidden name=a value="edit">
			<select class="form-control" name=id>
			<option value="">-- Select a Pivot Table to modify --</option>
			{section name=i loop=$pivots}
			<option value={$pivots[i].id}>{$pivots[i].title}</option>
			{/section}
			</select>
			<input type=submit class="btn btn-info mt-2" value="Load">
			<input type=button class="btn btn-primary mt-2" value="New Table" onclick="document.location='{$smarty.server.PHP_SELF}?a=new'">
			<input type=button class="btn btn-danger mt-2" value="Delete Selected" onclick="if (confirm('Are you sure?')) document.location='{$smarty.server.PHP_SELF}?a=delete&id='+form.id.value">
			</form>
			</div>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<div class="stdframe" >
			<form id=fa style="padding:5px 0">
				<div class="row">
				<div class="col">
					<input name=id type=hidden value="{$form.id|default:0}">
					<b class="form-label">Name the table</b> 
					<input class="form-control" name=title size=50 maxlength=50 value="{$form.title|default:'Pivot Table'}"> 
				</div>
				<div class="col">
					<b class="form-label">Report Grouping</b> 
					<div class="mt-3">
						<input type=radio name=rpt_group value='Sales' {if $form.rpt_group eq 'Sales'}checked{/if}> Sales 
					<input type=radio name=rpt_group value='Officer' {if $form.rpt_group eq 'Officer'}checked{/if}> Officer 
					<input type=radio name=rpt_group value='Management' {if $form.rpt_group eq 'Management'}checked{/if}> Management
					</div>
				</div>
				
				<div class="col">
					<input type=button class="btn btn-primary mt-4" value="Save" onclick="save_table()">
				<input id=save_as class="btn btn-primary mt-4" type=button value="Save As" onclick="save_new_table()" {if $form.id==0}style="display:none"{/if}>
				<input id="previewbtn" class="btn btn-info mt-4" type=button value="Preview Pivot" onclick="preview_table()">
				</div>
				</div>
			</form>
			
			<table class=" table mb-0 text-md-nowrap  table-hover"><tr>
			<td valign=top>
				<div id=droptarget>
				<table cellspacing=4 cellpadding=0>
				<tr>
					<td style="border:1px solid #999;">
						<div id=drop_pg style="width:150px;height:50px;background:url(images/pv_page.jpg);">
						<ul id=l_pg class="droplist">
				{foreach name=i from=$form.l_pg item=ll}
						<li id="{$ll}">{$ll}</li>
				{/foreach}
						</ul>
						</div>
					</td>
			
				</tr>
				<tr>
					<td id=drop_delete align=center style="border:1px solid #999;">
					<font color=red>-drop here to remove-</font>
					</td>
					<td style="border:1px solid #999;">
						<div id=drop_col style="width:300px; height:100px;background:url(images/pv_col.jpg);">
						<ul id=l_cols class="droplist">
				{foreach name=i from=$form.l_cols item=ll}
						<li id="{$ll}">{$ll}</li>
				{/foreach}
						</ul>
						</div>
					</td>
				</tr><tr>
					<td style="border:1px solid #999;">
						<div id=drop_row style="width:150px; height:350px;background:url(images/pv_row.jpg);">
						<ul id=l_rows class="droplist">
				{foreach name=i from=$form.l_rows item=ll}
						<li id="{$ll}">{$ll}</li>
				{/foreach}
						</ul>
						</div>
					</td>
					<td style="border:1px solid #999;">
						<div id=drop_data style="width:300px; height:350px;background:url(images/pv_data.jpg);">
						  <ul id=l_data class="droplist">
				{foreach name=i from=$form.l_data item=ll}
						<li id="{$ll}">{$ll}</li>
				{/foreach}
						</ul>
						</div>
					</td>
				</tr>
				</table>
				</div>
			
				<div id=preview style="width:452px; height:510px; overflow:scroll; padding:5px; border:2px solid black; display:none;">
				</div>
			
			</td><td valign=top>
			<!--- the field window -->
			<h4 class="form-label">Fields</h4>
			<div class="alert alert-primary" style="max-width: 300px;">
				Drag a field into the table on your left
			</div>
			
			<ul class="dragable" id=fields style="padding:2px;">
			{foreach from=$fields item=field}
			<li id="f[{$field}]" style="float:left; width:100px; cursor:pointer; " onmouseover="this.style.background='#d6e0ff'" onmouseout="this.style.background='#fff'">{$field}</li>
			{/foreach}
			</ul>
			
			<br style="clear:both">
			<p align=center>
			<div class="form-inline">
				<b class="form-label">
					Data Formula:&nbsp;
				</b> <select class="form-control" id=newdata_type>
				<option value="SUM">Sum</option>
				<option value="COUNT">Count</option>
				<option value="UNIQUE">Unique Count</option>
				<option value="MIN">Min</option>
				<option value="MAX">Max</option>
				<option value="AVG">Average</option>
				</select>&nbsp; [<a href="javascript:void(show_formula_help())">Help</a>]
			</div>
			</p>
			
			</td>
			</tr></table>
			
			</div>
	</div>
</div>

<br><br>
{include file=footer.tpl}
{literal}


<script>

{/literal}
{foreach from=$fields item=field}
if ($('f[{$field}]') != undefined) new Draggable('f[{$field}]', {literal}{zindex:1000, revert:true, ghosting:true}{/literal});
{/foreach}
{literal}
//new Draggable('fields');
Droppables.add('drop_pg', {hoverclass: 'chover', containment:'fields', zindex:10, onDrop: add_pg})
Droppables.add('drop_col', {hoverclass: 'chover', containment:'fields', zindex:10, onDrop: add_col})
Droppables.add('drop_row', {hoverclass: 'chover', containment:'fields', zindex:10, onDrop: add_row})
Droppables.add('drop_data', {hoverclass: 'chover', containment:'fields', zindex:10, onDrop: add_data})
Droppables.add('drop_delete', {hoverclass: 'chover', containment:['l_pg', 'l_rows','l_cols','l_data'], zindex:10, onDrop: delete_it})
Sortable.create("l_pg", {format:/(.*)/, zindex:100, constraint:false});
Sortable.create("l_rows", {format:/(.*)/, zindex:100, constraint:false});
Sortable.create("l_cols", {format:/(.*)/, zindex:100, constraint:false});
Sortable.create("l_data", {format:/(.*)/, zindex:100, constraint:false});
</script>
{/literal}

