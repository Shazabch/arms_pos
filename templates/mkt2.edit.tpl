{include file=header.tpl}
{literal}
<style>
.positive{
	color:blue;
	text-align:right;
}
.negative{
	color:red;
	text-align:right;
}
.zero{
	color:green;
	text-align:right;
}
.t_positive{
	color:blue;
	text-align:right;
	font-weight:bold;
}
.t_negative{
	color:red;
	text-align:right;
	font-weight:bold;
}
.t_zero{
	color:green;
	text-align:right;
	font-weight:bold;
}
.st_block td.numpositive,.st_block th.numpositive {
	border-right:2px solid #000;
	color:blue;
	text-align:right;
}

.st_block td.numnegative,.st_block th.numnegative {
	border-right:2px solid #000;
	color:red;
	text-align:right;
}

.st_block td.numzero,.st_block th.numzero {
	border-right:2px solid #000;
	color:green;
	text-align:right;
}


.st_block td.t_numpositive,.st_block th.t_numpositive {
	border-right:2px solid #000;
	color:blue;
	text-align:right;
	font-weight:bold;
}

.st_block td.t_numnegative,.st_block th.t_numnegative {
	border-right:2px solid #000;
	color:red;
	text-align:right;
	font-weight:bold;
}

.st_block td.t_numzero,.st_block th.t_numzero {
	border-right:2px solid #000;
	color:green;
	text-align:right;
	font-weight:bold;
}


.st_block {
	font-size:11px;
	border-left:1px solid #ccc;
	border-top:1px solid #ccc;
}
.st_block td, .st_block th {
	border-right:1px solid #ccc;
	border-bottom:1px solid #ccc;
}
.totalnum{
	text-align:left;
	font-weight:bold;
}
.total{
	text-align:left;
	color:green;
	font-weight:bold;
}
.st_block th { background:#fe9; padding:4px; }
.st_block .lastrow th { background:#f00; color:#fff;}
.st_block .title { background:#fe9; color:#00f;  }
.st_block input { border:1px solid #fff; margin:0;padding:0;font-size:11px; }
.st_block input:hover { border:1px solid #00f; }
.st_block input.focused { border:1px solid #fec; background:#ffe; }
.st_block td.sep,.st_block th.sep {
	border-right:2px solid #000;
}
.st_block td.totalsep,.st_block th.totalsep {
	border-right:2px solid #000;
	text-align:right;
	font-weight:bold;
}
input[disabled] { background:transparent; color:#000; }

</style>

<script>
// show and hide tables according to selected tab
function tb_sel(n)
{
	var tb = $('line_tables').getElementsByTagName('table');
	var i;
	for(i=0;i<tb.length;i++)
	{
		if (tb[i].id=='tb_'+n)
		{
			$('sel_'+tb[i].id).className='active';
			tb[i].style.display = '';
		}
		else
		{
		    $('sel_'+tb[i].id).className='';
		    tb[i].style.display = 'none';
		}
	}
}

function do_save()
{
	document.f_a.a.value='save';
	if(check_a()) document.f_a.submit();
}

//add-on on 11/04/07
function do_confirm(){
    /*var el = $('line_tables').getElementsByTagName('input');//call all the input elements
    var re1 = new RegExp('normal\\[total\\]\\[\\d+\\]');//chking the normal[total][''] input
    var re2 = new RegExp('sales\\[total\\]\\[\\d+\\]');//chking the sales[total][''] input
    var chk=0;
    var chk1=0;

	for(var i=0;i<el.length;i++)//loop all the elements
	{
	    if (re1.test(el[i].name))//validate the re1
	    {
			//get the variable inside array of re1
			var sid = (el[i].name.substring(el[i].name.lastIndexOf('[')));
			//compare the selected element value with the hidden type value
	        if (int(document.f_a.elements['target[normal]'+sid].value) != el[i].value)
	        {
	            alert('Total Normal : '+el[i].value+'\nTarget Normal : '+document.f_a.elements['target[normal]'+sid].value+'\nNormal for ' + document.f_a.elements['deptname'+sid].value + ' does not match');
	            return false;
			}
			else{
				chk=1;
			}
		}
		else if (re2.test(el[i].name))
	    {
	        var sid = (el[i].name.substring(el[i].name.lastIndexOf('[')));
	        if (int(document.f_a.elements['target[promo]'+sid].value) != el[i].value)
	        {
	            alert('Total Sales : '+el[i].value+'\nTarget Sales : '+document.f_a.elements['target[promo]'+sid].value+'\nSales for ' + document.f_a.elements['deptname'+sid].value + ' does not match');
	            return false;
			}
			else{
				chk1=1;
			}
		}
	}
	if(chk==1 && chk1==1){*/
	document.f_a.a.value='confirm';
	if(check_a()) document.f_a.submit();
	//}
}

/*function calc_row(dt,dept,line)
{
	var sstr = '['+dt+']['+dept+']';
	document.f_a.elements['amt'+sstr].value  = int(document.f_a.elements['sales'+sstr].value) - int(document.f_a.elements['normal'+sstr].value);
	document.f_a.elements['pct'+sstr].value  = round2(document.f_a.elements['amt'+sstr].value / document.f_a.elements['normal'+sstr].value * 100);

	calc_total(dept,line);
}

function calc_total(dept,line)
{
	var el = $('tb_'+line).getElementsByTagName('input');
	var re1 = new RegExp('normal\\[\\d+-\\d+-\\d+\\]\\['+dept+'\\]');
	var re2 = new RegExp('sales\\[\\d+-\\d+-\\d+\\]\\['+dept+'\\]');
	var re3 = new RegExp('amt\\[\\d+-\\d+-\\d+\\]\\['+dept+'\\]');

	var total1=0;
	var total2=0;
	var total3=0;

	for(var i=0;i<el.length;i++)
	{
	    if (re1.test(el[i].name))
		{
			total1 += int(el[i].value);
		}
	    else if (re2.test(el[i].name))
		{
			total2 += int(el[i].value);
		}
	    else if (re3.test(el[i].name))
		{
			total3 += int(el[i].value);
		}
	}
	document.f_a.elements['normal[total]['+dept+']'].value=total1;
	document.f_a.elements['sales[total]['+dept+']'].value=total2;
	document.f_a.elements['amt[total]['+dept+']'].value=total3;
	document.f_a.elements['pct[total]['+dept+']'].value=round2(total3/total1*100);
}
*/
function check_a()
{
	// nothing to check at the moment
	return true;
}

</script>
{/literal}

<h1>{$PAGE_TITLE} ({$form.current_branch}, MKT{$form.id|string_format:"%05d"})</h1>

{if $errm.top}
<div id=err><div class=errmsg><ul>
{foreach from=$errm.top item=e}
<li> {$e}
{/foreach}
</ul></div></div>
{/if}

<form name=f_a method=post>
<input type=hidden name=a value=save>
<input type=hidden name=id value={$form.id}>
<input type=hidden name=mkt0_id value={$form.mkt0_id}>
<input type=hidden name=branch_id value={$branch_id|default:$smarty.request.branch_id}>


<div class=stdframe style="background:#fff">
<table cellspacing=0 cellpadding=4 border=0 class=tl>
<tr>
	<td colspan=2 class=small>Created: {$form.added}, Last Update: {$form.last_update}</td>
</tr>
<tr>
	<th nowrap>Participating Branches</th>
	<td>
	{foreach from=$branches item=branch}
	{assign var=br value=$branch.code}
	{if $form.branches[$br]}<img src=/ui/checked.gif> 
	{if $branch_id == $branch.id}<font color=red>{/if}
	{$branch.code}
	{if $branch_id == $branch.id}</font>{/if}
	{/if}
	{/foreach}
	</td>
</tr>

<tr>
	<th>Promotion Title</th>
	<td>{$form.title}</td>
</tr><tr>
	<th nowrap>Promotion Period</th>
	<td>
		{$form.offer_from|date_format:'%d/%m/%Y'} to {$form.offer_to|date_format:'%d/%m/%Y'}
	</td>
</tr><tr>
	<th nowrap>Submit Due Date</th>
	<td>
		{$form.submit_due_date_1|date_format:'%d/%m/%Y'}
	</td>
</tr>
<tr>
	<th>Publish Date </th>
	<td>
{section name=x start=1 loop=6}
{assign var=x value=$smarty.section.x.iteration}
{if $form.publish_dates[$x]!=''}
<img align=absbottom src="ui/calendar.gif" id="b_p_date[{$x}]" title="Select Date">{$form.publish_dates[$x]}&nbsp;&nbsp;&nbsp;&nbsp;
{/if}
{/section}
	</td>
</tr>
<tr>
	<th nowrap>Attachments</th>
	<td>
		{foreach from=$form.attachments.name key=idx item=fn}
		{if $fn}<img src=/ui/icons/attach.png align=absmiddle> <a href="javascript:void(window.open('{$image_path}{$form.filepath[$idx]}'))">{$fn}</a> &nbsp;&nbsp; {/if}
		{/foreach}
	</td>
</tr><tr>
	<th nowrap>Promotion<br>Period Remark</th>
	<td>
		{$form.remark|nl2br}
	</td>
</tr>

</table>
</div>

<h3>Department Contribution</h3>
<div id=err><div class=errmsg><ul>
{foreach from=$warning item=w}
<li> {$w}
{/foreach}
</div></div>
<div class=tab style="height:25px;white-space:nowrap;">
{foreach from=$lines item=line key=line_str}
<a id=sel_tb_{$line.id} href="javascript:void(tb_sel({$line.id}));">{$line_str}</a>
{/foreach}
</div>
<div id=line_tables style="margin-top:-4px;">
{foreach key=line_id item=line from=$lines}
<table id=tb_{$line.id} class=st_block cellpadding=2 cellspacing=0 border=0 style="display:none;border:1px solid #000;"><input type=hidden name=get_tbl value={$line.id}>
<tr>
<th rowspan=2>Date</th>
{foreach from=$line.dept item=dept}
{assign var=dept_id value=$dept.id}
<th class=sep colspan=4>{$dept.description}
    <input type=hidden name=deptname[{$dept_id}] value="{$dept.description}">
<!--font color=blue>N : {$dept_sales.normal.$dept_id|number_format}</font><font color=black> /</font>
S : {$dept_sales.promo.$dept_id|number_format}<-->
</th>
{/foreach}
</tr>
<tr>
{foreach from=$line.dept item=dept}
{assign var=dept_id value=$dept.id}
<th>Normal</th>
<th>Target</th>
<th>Incr</th>
<th class=sep>%</th>
{/foreach}
</tr>
{assign var=normal_total value=0}
{assign var=sales_total value=0}
{assign var=amt_total value=0}
{assign var=pct_total value=0}

{foreach from=$dates item=date}
<tr>
<td class=totalnum>{$date|date_format:"%d-%b"}</td>

{foreach from=$line.dept item=dept}
{assign var=dept_id value=$dept.id}

{assign var=normal value=$form.$date.normal.$dept_id}
{assign var=sales value=$form.$date.sales.$dept_id}
{assign var=total value=$form.$date.total.$dept_id}

{if $form.$date.con.$dept_id>0}
{assign var=con value=$form.$date.con.$dept_id}
{else}
{assign var=con value=$form.zero.con.$dept_id}
{/if}

{assign var=c_normal value=$normal*$total*$con/10000}
{assign var=c_sales value=$sales*$total*$con/10000}
{assign var=c_amt value=$c_sales-$c_normal}
{if $c_normal}
{assign var=c_pct value=$c_amt/$c_normal*100}
{else}
{assign var=c_pct value=0}
{/if}

<td class="{if $c_normal<0}negative{elseif $c_normal==0}zero{else}positive{/if}">
{$c_normal|number_format:2}</td>
<td class="{if $c_sales<0}negative{elseif $c_sales==0}zero{else}positive{/if}">
{$c_sales|number_format:2}</td>
<td class="{if $c_amt<0}negative{elseif $c_amt==0}zero{else}positive{/if}">
{$c_amt|number_format:2}</td>
<td class="{if $c_pct<0}numnegative{elseif $c_pct==0}numzero{else}numpositive{/if}">
{$c_pct|number_format:2}</td>

{/foreach}
</tr>
{/foreach}

<tr>
<td class="total">Total</td>
{assign var=normal_total value=0}
{assign var=sales_total value=0}
{assign var=amt_total value=0}
{assign var=pct_total value=0}


{foreach from=$line.dept item=dept}
{assign var=dept_id value=$dept.id}

{foreach from=$dates item=date}
{assign var=normal value=$form.$date.normal.$dept_id}
{assign var=sales value=$form.$date.sales.$dept_id}
{assign var=total value=$form.$date.total.$dept_id}

{if $form.$date.con.$dept_id>0}
{assign var=con value=$form.$date.con.$dept_id}
{else}
{assign var=con value=$form.zero.con.$dept_id}
{/if}

{assign var=temp_n value=$normal*$total*$con/10000}
{assign var=normal_total value=$normal_total+$temp_n}

{assign var=temp_s value=$sales*$total*$con/10000}
{assign var=sales_total value=$sales_total+$temp_s}

{assign var=temp_a value=$temp_s-$temp_n}
{assign var=amt_total value=$amt_total+$temp_a}

{if $temp_n}
{assign var=temp_p value=$temp_a/$temp_n*100}
{assign var=pct_total value=$pct_total+$temp_p}
{else}
{assign var=pct_total value=$pct_total+0}
{/if}
{/foreach}

<td class="{if $normal_total<0}t_negative{elseif $normal_total==0}t_zero{else}t_positive{/if}">
{$normal_total|number_format:2}</td>
<td class="{if $sales_total<0}t_negative{elseif $sales_total==0}t_zero{else}t_positive{/if}">
{$sales_total|number_format:2}</td>
<td class="{if $amt_total<0}t_negative{elseif $amt_total==0}t_zero{else}t_positive{/if}">
{$amt_total|number_format:2}</td>
<td class="{if $pct_total<0}t_numnegative{elseif $pct_total==0}t_numzero{else}t_numpositive{/if}">
{$pct_total|number_format:2}</td>

<input type=hidden name=target[normal][{$dept_id}] value={$normal_total}>
<input type=hidden name=target[promo][{$dept_id}] value={$sales_total}>

{assign var=normal_total value=0}
{assign var=sales_total value=0}
{assign var=amt_total value=0}
{assign var=pct_total value=0}
{/foreach}
</tr>
</table>
{/foreach}
</div>
</form>

<p id=submitbtn align=center>

{if $smarty.request.a ne 'view'}
<!--input name=bsubmit type=button value="Save & Close" style="font:bold 20px Arial; background-color:#f90; color:#fff;" onclick="do_save()"-->
{/if}
<input type=button value="Close" style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="document.location='/mkt2.php?branch_id={$branch_id}'">

{if $smarty.request.a ne 'view' and $mkt2_privilege.MKT2_EDIT.$branch_id and $line.dept}
<input type=button value="Confirm" style="font:bold 20px Arial; background-color:#091; color:#fff;" onclick="do_confirm()">
{/if}
</p>



{include file=footer.tpl}
<script>
tb_sel({$departments[0].cat_root_id});
//Form.disable(document.f_a);
_init_enter_to_skip(document.f_a);
/*_init_focus_input_class('#expenses_table input');
_init_focus_input_class('#contribution_table input');*/
</script>

