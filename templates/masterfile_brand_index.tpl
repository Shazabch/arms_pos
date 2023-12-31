{*
9/20/2010 5:31:07 PM Andy
- Add link to view brand discont table.

12/13/2011 11:34:49 AM Andy
- Add checkbox to allow force update to all SKU cost when change discount rate.

12/16/2011 3:30:54 PM Justin
- Added sort by header feature when reload table.

5/2/2012 9:29:54 AM Andy
- Add can filter by "All".
- Add show loading process icon when reload vendor list.
- Add can export to CSV.

10/23/2013 9:47 AM Fithri
- records is now displayed in pages, 20 per page
- re-arrange default filters behaviours

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

	document.f_b.reset();
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
	/*if (empty(document.f_b.code, 'You must enter Code'))
	{
		return false;
	}*/
	if (empty(document.f_b.description, 'You must enter Description'))
	{
		return false;
	}
	return true;
}

function reload_table(load_page)
{
	if (load_page) lp = '&pg='+$('pg').value;
	else lp = '';
	params = 'a=ajax_reload_table&'+Form.serialize($('search_form'))+lp;
	//alert(params);return;
	$('span_loading_brand_list').show();
	new Ajax.Updater("udiv", "masterfile_brand.php",{
	    parameters: params,
	    evalScripts: true,
		onComplete: function(m){
			ts_makeSortable($('brand_tbl'));
		}
	});
	return false;
}

function do_find(f,obj)
{
	uc(obj);
	var v = new String(obj.value);
	if (v=='')
	{
		alert('Search field is empty');
		obj.focus();
		return;
	}
	new Ajax.Updater("udiv", "masterfile_brand.php",{
	    parameters: 'a=ajax_reload_table&search='+f+'&'+Form.Element.serialize(obj),
	    evalScripts: true
	});
}

function showtd(id,n)
{
	var did = document.f_d.department_id.value;
	document.getElementById('tmsg').innerHTML = '<img src=ui/clock.gif align=absmiddle> Loading...';
	showdiv('ddiv')
	if (n != undefined) $('td_name').innerHTML = n;
	document.f_d.reset();
	_irs.document.location = '?a=load_td&id='+id+'&department_id='+did;
}

function tdloaded()
{
	document.getElementById('tmsg').innerHTML = '';
}
</script>
{/literal}
<div class="breadcrumb-header justify-content-between">
	<div class="my-auto">
		<div class="d-flex">
			<h4 class="content-title mb-0 my-auto ml-4 text-primary">Brand Master File</h4><span class="text-muted mt-1 tx-13 ml-2 mb-0"></span>
		</div>
	</div>
</div>
<div class="card mx-3">
	<div class="card-body">
		<ul style="list-style-type: none;">
			{if $sessioninfo.privilege.MST_BRAND}
				<li>
					<a accesskey="A" href="javascript:void(add())"><img src=ui/new.png title="New" align=absmiddle border=0></a> <a href="javascript:void(add())"><u>A</u>dd Brand</a> (Alt+A)
				</li>
			{/if}
			<li>
				<a href="report.brand_discount_table.php" target="_blank">
					<img src="ui/new.png" title="Enter Report" align="absmiddle" border="0" />
					Brand Discount Table
				</a>
			</li>
			<li>
				<a href="?a=export_brand" target="_blank">
					<img src="ui/new.png" title="Export As CSV" align="absmiddle" border="0" />
					Export As CSV
				</a>		
			</li>
		</ul>
	</div>
</div>

<div class="card mx-3">
	<div class="card-body">
		<form name="search_form" id="search_form" onsubmit="return reload_table()">
			<p>
				<div class="row">
					
			<div class="col-md-3">
				<b class="form-label">Description :</b> 
			<input type="text" class="form-control" name="desc" size="15" />
		
			</div>
			
			<div class="col-md-3">
				<b class="form-label">Status :</b> 
				<select class="form-control" name="status">
					<option value="">All</option>
					<option value="1">Active</option>
					<option value="0">Inactive</option>
				</select>
		
			</div>
		
		<div class="col-md-3">
			<b class="form-label">Starts With :</b> 
			<select class="form-control" name="starts_with">
				<option value="">All</option>
				<option value="A">A</option>
				<option value="B">B</option>
				<option value="C">C</option>
				<option value="D">D</option>
				<option value="E">E</option>
				<option value="F">F</option>
				<option value="G">G</option>
				<option value="H">H</option>
				<option value="I">I</option>
				<option value="J">J</option>
				<option value="K">K</option>
				<option value="L">L</option>
				<option value="M">M</option>
				<option value="N">N</option>
				<option value="O">O</option>
				<option value="P">P</option>
				<option value="Q">Q</option>
				<option value="R">R</option>
				<option value="S">S</option>
				<option value="T">T</option>
				<option value="U">U</option>
				<option value="V">V</option>
				<option value="W">W</option>
				<option value="X">X</option>
				<option value="Y">Y</option>
				<option value="Z">Z</option>
				<option value="others">Others</option>
			</select>
		</div>
		<div class="col-md-3">
			<input type="button" class="btn btn-primary  mt-4" value="Search" onclick="reload_table()" />
		</div>
				</div>
			</p>
			</form>
	</div>
</div>

{include file=masterfile_brand_table.tpl}

<br>

<div class="ndiv" id="ndiv" style=" display: none; max-width: 550px;margin-left: 300px; bottom: 350px; background-color: white;">
<div class="blur"><div class="shadow"><div class="content">

<div class="small mt-2" style="position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ndiv'))" accesskey="C"><img src=ui/closewin.png border=0 align=absmiddle></a><br><u>C</u>lose (Alt+C)</div>

<form method=post name=f_b target=_irs onSubmit="return check_b()">
<div id=bmsg class="ml-2  mt-3" style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="a">
<input type=hidden name=id value="">
<table id="tb" >
<tr>
<td><b class="form-label ml-2 mt-2">Code</b> &nbsp;&nbsp;(Optional)</td>
<td><input class="form-control mt-2" onBlur="uc(this)" name=code size=10 maxlength=6></td>
</tr><tr>
<td><b class="form-label ml-2 mt-2">Description<span class="text-danger" title="Required Field"> *</span></b></td>
<td><input class="form-control mt-2" onBlur="uc(this)" name=description size=50> 
</tr>
<td align=center colspan=2>
<br>
<div id=abtn style="display:none;">
<input type=submit class="btn btn-primary mb-2" value="Add"> 
<input type=button class="btn btn-danger mb-2" value="Cancel" onclick="f_b.reset(); hidediv('ndiv');">
</div>
<div id=ebtn style="display:none;">
<input type=submit class="btn btn-primary mb-2" value="Update"> 
<input type=button class="btn btn-info mb-2" value="Restore" onclick="ed(lastn)"> 
<input type=button class="btn btn-danger mb-2" value="Close" onclick="f_b.reset(); hidediv('ndiv');">
</div>
</td></tr>
</table>
</form>
</div></div></div>
</div>

<!-- start vendor TRADE_DISCOUNT table -->
<div class="ndiv" id="ddiv" style="background-color: white;  position:absolute;margin-left:300;margin-top:50;display:none;">
<div class="blur"><div class="shadow"><div class="content" style="padding: 15px;">

<div class=small style="padding: ; position:absolute; right:10; text-align:right;"><a href="javascript:void(hidediv('ddiv'))" ><img src=ui/closewin.png border=0 align=absmiddle></a></div>

<form method=post name=f_d target=_irs>
<div id=tmsg style="padding:10 0 10 0px;"></div>
<input type=hidden name=a value="ad">
<input type=hidden name=brand_id value="">
<b class="form-label">Trade Discount Table for <span id=td_name>#</span></b>
<br>
<b class="form-label">Select Department</b> <select class="form-control" name=department_id onchange="showtd(brand_id.value)">
{section name=i loop=$department}
<option value={$department[i].id}>{$department[i].description}</option>
{/section}
</select>
<br>

{section name=b loop=$branches}
<h4 class="form-label">{$branches[b].code}</h4>
<table class=small>
<tr>
{section name=i loop=$skutype}
<td>{$skutype[i].code}</td>
{/section}
</tr>
<tr>
{section name=i loop=$skutype}
<td><input class="form-control" size=5 name="commission[{$skutype[i].code}][{$branches[b].id}]" {if !$sessioninfo.privilege.MST_BRAND}disabled{/if}></td>
{/section}
</tr>
</table>
{/section}

{if $sessioninfo.privilege.MST_BRAND and $sessioninfo.level>=9999}
<p>
	<div class="form-label form-inline">
		<input type="checkbox" name="force_update" value="1" /> <b>&nbsp;Force update SKU cost.</b><br />
	</div>
</p>
{/if}

<p align=center>
	{if $sessioninfo.privilege.MST_BRAND}
		<input class="btn btn-primary" type=submit value="Save">
	{/if}
	<input class="btn btn-danger" type=button value="Close" onclick="f_d.reset(); hidediv('ddiv');">
</p>

</form>
</div></div></div>
</div>

<div style="display:none"><iframe name=_irs width=500 height=400 frameborder=1></iframe></div>

<script>
init_chg(document.f_b);
new Draggable('ndiv');
new Draggable('ddiv');
</script>

{include file=footer.tpl}
