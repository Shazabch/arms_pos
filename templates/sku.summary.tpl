{include file=header.tpl}
{literal}
<!-- calendar stylesheet -->
<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />

<!-- main calendar program -->
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>

<!-- language for the calendar -->
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>

<!-- the following script defines the Calendar.setup helper function, which makes
   adding a calendar a matter of 1 or 2 lines of code. -->
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>

<script>
function show_child(id)
{
	// reactivate the auto-completer with child of the category
	setTimeout('category_autocompleter.options.defaultParams = "child='+id+'";category_autocompleter.activate()',250);
}

function sel_category(obj,have_child)
{
	var str = new String(obj.value);
	str.replace('<span class=sh>', '');
	str.replace('</span>', '');
	document.f_a.category_tree.value = str;
	$('str_cat_tree').innerHTML = str;
	obj.value = str.substr(str.lastIndexOf(">")+2, str.length);
}

function init_sortable()
{
	$('pivot').rows[$('pivot').rows.length-1].className = 'sortbottom';
	$('pivot').className = 'tb sortable';
    ts_makeSortable($('pivot'));
    $('can_sort').innerHTML = "<p><font color=blue>This table is sortable. Click on a column header to sort.</font></p>";
}

function showsub(cat_id,cat_name)
{
	document.f_a.category_id.value = cat_id;
	document.f_a.category.value = cat_name;
	document.f_a.category_tree.value = document.f_a.category_tree.value + ' > ' + cat_name;
	document.f_a.submit();
}

var category_autocompleter;
function init()
{
	category_autocompleter = new Ajax.Autocompleter("autocomplete_category", "autocomplete_category_choices", "ajax_autocomplete.php?a=ajax_search_category&min_level=-1", {
	afterUpdateElement: function (obj,li)
	{
	    this.defaultParams = '';
		var s = li.title.split(',');
		document.f_a.category_id.value = s[0];
		sel_category(obj,s[1]);
	}});

    Calendar.setup({
        inputField     :    "added1",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added1",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });

    Calendar.setup({
        inputField     :    "added2",     // id of the input field
        ifFormat       :    "%Y-%m-%d",      // format of the input field
        button         :    "t_added2",  // trigger for the calendar (button ID)
        align          :    "Bl",           // alignment (defaults to "Bl")
        singleClick    :    true
		//,
        //onUpdate       :    load_data
    });
}
</script>

<style>
.negative { color: #f00; }
.r0 { background-color: #fcc; }
.r1 { background-color: #f99; }
.g0 { background-color: #cfc; }
.g1 { background-color: #9f9; }
</style>
{/literal}
<h1>{$PAGE_TITLE}</h1>
<div class=stdframe style='background:#fff;'>
<form name=f_a>
<input type=hidden name=a value="show">
	<b>Category</b>
	<input type=radio name=s1 value=0 {if $smarty.request.s1 == 0}checked{/if} onclick="Element.hide('csel');category_id.value=0;"> All
	<input type=radio name=s1 value=1 {if $smarty.request.s1 == 1}checked{/if} onclick="Element.show('csel');category.focus();"> Selected
	<span id=csel {if !$smarty.request.s1}style='display:none'{/if}>
	<input readonly name=category_id size=1 value="{$smarty.request.category_id}">
	<input type=hidden name=category_tree value="{$smarty.request.category_tree}">
	<input id=autocomplete_category name=category value="{$smarty.request.category|default:"Enter keyword to search"}" onfocus=this.select() size=50><br />

	<span id=str_cat_tree class=small style="color:#00f;margin-left:170px;">{$smarty.request.category_tree}&nbsp;</span>
	<div id=autocomplete_category_choices class=autocomplete style="width:600px !important"></div>
	</span>
	<br>
	{if $BRANCH_CODE eq 'HQ'}
	<b>Branch</b>
	<select name=branch_id>
	{section name=i loop=$branches}
	<option value="{$branches[i].id}" {if $smarty.request.branch_id eq $branches[i].id}selected{/if}>{$branches[i].code}</option>
	{/section}
	</select>&nbsp;&nbsp;&nbsp;&nbsp;
	{/if}
	
	<b>Date From</b> <input name=from value="{$smarty.request.from}" id=added1 size=12 />
	<img align=absmiddle src=ui/calendar.gif id=t_added1 style="cursor: pointer;" title="Select Date"/>
	&nbsp; <b>To</b> <input type=text name=to value="{$smarty.request.to}" id=added2 size=12 />

	<img align=absmiddle src=ui/calendar.gif id=t_added2 style="cursor: pointer;" title="Select Date"/>
	</p>

	<input type=submit value="Show Report" onclick="this.disabled=1;this.value='Please wait...'">
</form>
</div>

{if 1 || $table}
<p>
* calculation based on last GRN cost
</p>

<table border=0 cellpadding=4 cellspacing=0 class="tb">
<tr bgcolor=#ffee99>
	<th>Department</th>
	<th>Opening Qty (Pcs)</th>
	<th>Opening Value (RM)</th>
	<th>GRN (Pcs)</th>
	<th>GRN Cost (RM)</th>
	<th>GRA (Pcs)</th>
	<!--th>GRA Cost (RM)</th-->
	<th>POS (Pcs)</th>
	<th>Selling (RM)</th>
	<th>Balance (Pcs)</th>
	<th>Balance Value (RM)</th>
	<th>GP (RM)</th>
	<th>GP(%)</th>
</tr>

{foreach from=$table key=cat_id item=row}
{assign var=opening value=$row.pre_grn.qty-$row.pre_gra.qty-$row.pre_pos.qty}
{assign var=balance value=$opening+$row.cur_grn.qty-$row.cur_gra.qty-$row.cur_pos.qty}
{assign var=opening_cost value=$row.pre_grn.cost-$row.pre_gra.cost-$row.pre_pos.cost}
{assign var=balance_cost value=$opening_cost+$row.cur_grn.cost-$row.cur_gra.cost-$row.cur_pos.cost}
{assign var=gp value=$row.cur_pos.selling-$row.cur_pos.cost}
{assign var=gp_pct value=$gp/$row.cur_pos.cost}

{assign var=total_open value=$total_open+$opening}
{assign var=total_gra value=$total_gra+$row.cur_gra.qty}
{assign var=total_grn value=$total_grn+$row.cur_grn.qty}
{assign var=total_pos value=$total_pos+$row.cur_pos.qty}
{assign var=totalc_gra value=$totalc_gra+$row.cur_gra.cost}
{assign var=totalc_grn value=$totalc_grn+$row.cur_grn.cost}
{assign var=totals_pos value=$totalc_pos+$row.cur_pos.selling}
{assign var=totalc_pos value=$totalc_pos+$row.cur_pos.cost}
{assign var=total_bal value=$total_bal+$balance}
{assign var=totalc_open value=$totalc_open+$opening_cost}
{assign var=totalc_bal value=$totalc_bal+$balance_cost}
{assign var=total_gp value=$total_gp+$gp}
<tr>
	<td>{if $row.have_subcat}
		<a href="javascript:void(showsub({$cat_id},'{$row.description|escape:'javascript'}'))">{$row.description}</a>
		{else}
		{$row.description}
		{/if}
		</td>
	<td align=right class=g0>{$opening|number_format}</td>
	<td align=right class=g0>{$opening_cost|number_format:2}</td>
	<td align=right class=r0>{$row.cur_grn.qty|number_format}</td>
	<td align=right class=r0>{$row.cur_grn.cost|number_format:2}</td>
	<td align=right class=g0>{$row.cur_gra.qty|number_format}</td>
	<!--td align=right class=g0>{$row.cur_gra.cost|number_format:2}</td-->
	<td align=right class=r0>{$row.cur_pos.qty|number_format}</td>
	<td align=right class=r0>{$row.cur_pos.selling|number_format:2}</td>
	<td align=right class=g0>{$balance|number_format}</td>
	<td align=right class=g0>{$balance_cost|number_format:2}</td>
	<td align=right class="{if $gp<0}negative{/if}">{$gp|number_format:2}</td>
	<td align=right class="{if $gp<0}negative{/if}">{$gp_pct*100|number_format:2}%</td>
	<!--td align=right>{$row.cur_pos.cost|number_format:2}</td-->
</tr>
{/foreach}
<tr bgcolor=#ffee99>
<th>Total</th>
<td align=right class=g1>{$total_open|number_format}</td>
<td align=right class=g1>{$totalc_open|number_format:2}</td>
<td align=right class=r1>{$total_grn|number_format}</td>
<td align=right class=r1>{$totalc_grn|number_format:2}</td>
<td align=right class=g1>{$total_gra|number_format}</td>
<!--td align=right class=g1>{$totalc_gra|number_format:2}</td-->
<td align=right class=r1>{$total_pos|number_format}</td>
<td align=right class=r1>{$totals_pos|number_format:2}</td>
<td align=right class=g1>{$total_bal|number_format}</td>
<td align=right class=g1>{$totalc_bal|number_format:2}</td>
<td align=right class="{if $total_gp<0}negative{/if}">{$total_gp|number_format:2}</td>
<td align=right class="{if $total_gp<0}negative{/if}">{$total_gp/$totalc_pos*100|number_format:2}%</td>
<!--td align=right>{$totalc_pos|number_format:2}</td-->
</tr>
</table>
{/if}

{include file=footer.tpl}
<script>
init();
</script>
