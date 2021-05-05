{*
11/12/2013 3:20 PM Fithri
- add missing indicator for compulsory field

8/7/2018 11:28 AM Justin
- Bug fixed on fraction can be saved as zero.

1/7/2019 3:47 PM Andy
- Enhanced to not allow users to edit uom if got sku in-used.
*}

{include file=header.tpl}
{literal}
<script>
var lastn = '';

function loaded()
{
	document.getElementById('bmsg').innerHTML = 'Click Update to save changes';
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
	document.f_b.id.value = 0;
	document.f_b.a.value = 'a';
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
	if (document.f_b['fraction'].value == "" || document.f_b['fraction'].value <= 0)
	{
		alert('You must enter a Fraction Amount greater than Zero.');
		return false;
	}

	return true;
}

</script>
{/literal}

<h1>UOM Master File</h1>
{if $sessioninfo.privilege.MST_UOM}
<div><a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd UOM</a> (Alt+A)</div>
{/if}

<br>

<ul>
	<li>UOM cannot be modified when got SKU in-used.</li>
</ul>

<br />
{include file=masterfile_uom_table.tpl}

<div class="ndiv" id="ndiv" style="position:absolute;left:150;top:150;display:none;">
<div class="blur"><div class="shadow"><div class="content">

<div class=small style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ndiv'))" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

<form method=post name=f_b target=_irs onSubmit="return check_b()">
<div id=bmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="a">
<input type=hidden name=id value="">
<table id="tb" >
<tr>
<td><b>Code</b></td>
<td><input onBlur="uc(this)" name=code size=10 maxlength=6> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
</tr><tr>
<td><b>Description</b></td>
<td><input name=description size=50> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
</tr><tr>
<td valign=top><b>Fraction</b></td>
<td><input name=fraction size=20 onchange="float(mf(this));"> <img src="ui/rq.gif" align="absbottom" title="Required Field"></td>
</tr>
<tr>
<td align=center colspan=2>
<br>
<div id=abtn style="display:none;">
<input type=submit value="Add"> <input type=button value="Cancel" onclick="f_b.reset(); hidediv('ndiv');">
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
