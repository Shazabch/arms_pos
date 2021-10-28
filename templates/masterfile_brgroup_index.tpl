{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field
*}

{include file=header.tpl}
{literal}
<script>
var lastn = '';

function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
	resetlist();
	document.f_b.changed_fields.value = '';
	document.f_b.code.focus();
}

function ed(n)
{
	document.getElementById('abtn').style.display = 'none';
	document.getElementById('ebtn').style.display = '';
	document.getElementById('bmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';

	showdiv('ndiv');
	document.f_b.id.value = n;
	_irs.document.location = '?a=e&id='+n;
	lastn = n;

	document.f_b.a.value = 'u';
	document.f_b.code.focus();
}

function add()
{
	showdiv('ndiv');
	document.getElementById('abtn').style.display = '';
	document.getElementById('ebtn').style.display = 'none';
	document.getElementById('bmsg').innerHTML = 'Enter the following and click ADD';
	document.f_b.reset();
	document.f_b._brands.value = '';
	document.f_b.id.value = 0;
	document.f_b.a.value = 'a';
	resetlist();
	document.f_b.code.focus();
}

function act(n, s)
{
	_irs.document.location = '?a=v&id='+n+'&v='+s;
}

function check_b()
{
	if (empty(document.f_b.code, 'You must enter Code'))
	{
		return false;
	}
	if (empty(document.f_b.description, 'You must enter Description'))
	{
		return false;
	}
	str = '';
	sel = document.f_b.sel_brand;
	if (sel.options.length <= 0)
	{
		alert('You must select at least a brand into this group');
		return false;
	}
	for (i=0;i<sel.options.length;i++)
	{
		str = str + sel.options[i].value + '|';
	}
	document.f_b._brands.value = str;
	return true;
}

function mv(src, dst)
{
	if (src.selectedIndex == -1) return;
	dst.options[dst.options.length] = new Option(src.options[src.selectedIndex].text, src.options[src.selectedIndex].value)
	n = src.selectedIndex;
	src.options[src.selectedIndex] = null;
	if (src.options.length-1 >= n)
		src.selectedIndex = n;
	else
		src.selectedIndex = src.options.length-1;
}

function mvall(src, dst)
{
	while (src.options.length>0)
	{
		dst.options[dst.options.length] = new Option(src.options[0].text, src.options[0].value)
		src.options[0] = null;
	}
}

// reset the src list according to _brands field
function resetlist()
{
	str = document.f_b._brands.value;
	sel = document.f_b.sel_brand;
	src = document.f_b.src_brand;

	sel.options.length=0;
	src.options.length=0;

	if (str != '')
	{
		// populate to both lists base on selected brands
		for (i=0; i<brand_list.length-1;i++)
		{
			if (str.indexOf('|'+brand_list[i][0]+'|') >= 0)
				sel.options[sel.options.length] = new Option(brand_list[i][1], brand_list[i][0]);
			else
				src.options[src.options.length] = new Option(brand_list[i][1], brand_list[i][0]);
		}
	}
	else
	{
		// no selection, default all to src
		for (i=0; i<brand_list.length-1;i++)
			src.options[src.options.length] = new Option(brand_list[i][1], brand_list[i][0]);
	}
}

{/literal}
var brand_list = new Array(
{section name=i loop=$brands}
Array('{$brands[i].id}','{$brands[i].description|escape:"javascript"}'),
{/section}
0);
{literal}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">
				Brand Group Master File
			</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
{if $sessioninfo.privilege.MST_BRANDGROUP}
<div class="card mx-3">
	<div class="card-body"><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Brand Group</a> (Alt+A)</div>
</div>
{/if}

<div class="card mx-3">
	<div class="card-body">
		{include file=masterfile_brgroup_table.tpl}
	</div>
</div>

<div class="ndiv" id="ndiv" style=" display: none; max-width: 550px;margin-left: 300px; bottom:100px; background-color: white;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small mt-2" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ndiv'))" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

<form method=post name=f_b target=_irs onSubmit="return check_b()">
<div id=bmsg class="ml-2 mt-3" style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="a">
<input type=hidden name=id value="">
<input type=hidden name=_brands value="">
<table id="tb" >
<tr>
<td><b class="form-label ml-2 mt-2">Code<span class="text-danger" title="Required Field"> *</span></b></td>
<td><input class="form-control mt-2" onBlur="uc(this)" name=code size=10 maxlength=6> </td>
</tr><tr>
<td><b class="form-label ml-2 mt-2">Description<span class="text-danger" title="Required Field"> *</span></b></td>
<td><input class="mt-2 form-control" name=description size=50> </td>
</tr><tr>
<td colspan=2><br>
<b class="form-label ml-2 mt-2">Select Brands to group</b><br>
<table class=small>
<tr>
<td>
&nbsp;&nbsp;&nbsp;<select class="form-control ml-5" name=sel_brand size=10 style="width:160px">
</select>
</td>
<td align=center>
<input type=button class="btn btn-dark btn-sm" value="<<" onclick="mv(src_brand, sel_brand)"><br><br>
<input type=button class="btn btn-dark btn-sm" value=">>" onclick="mv(sel_brand, src_brand)"><br><br>
&nbsp;&nbsp;<input type=button class="btn btn-primary " value="Reset" onclick="resetlist()">&nbsp;&nbsp;
<td>
&nbsp;&nbsp;&nbsp;<select class="form-control" name=src_brand size=10 style="width:160px">
</select>
</td>
</tr>
</table>
</td>
</tr><tr>
<td align=center colspan=2>
<br>
<div id=abtn style="display:none;">
<input type=submit class="btn  btn-primary mb-3" value="Add">
 <input type=button class="btn btn-danger mb-3" value="Cancel" onclick="f_b.reset(); hidediv('ndiv');">
</div>
<div id=ebtn style="display:none;">
<input type=submit value="Update"> <input type=button value="Restore" onclick="ed(lastn)"> <input type=button value="Close" onclick="f_b.reset(); hidediv('ndiv');">
</div>
</td>
</tr>
</table>

</form>
</div></div></div>

</div>

<div style="display:none"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>

<script>
init_chg(document.f_b);
new Draggable('ndiv');
</script>

{include file=footer.tpl}
