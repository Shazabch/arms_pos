{include file='header.tpl'}

<script>
var phpself = '{$smarty.server.PHP_SELF}';

{literal}
function check_file(){
	var filename = document.f_a['pos_file'].value;
	// only accept csv file
	if(filename.indexOf('.sql3')<0){
		alert('Please select valid ARMS POS file');
		return false;
	}
	return true;
}

function check_form(){
	if(!check_file())   return false;   // check file extension

	// ask final confirmation
	if(!confirm('Are you sure?'))    return false;

	return true;    // no problem found
}

function check_import(){
	if(!document.f_b)   return false;
	
    if(document.f_b['history_filename'].value=='')   return false;

	// ask final confirmation
	if(!confirm('Are you sure? This operation cannot be undo!'))    return false;

	return true;    // no problem found
}
{/literal}
</script>

<h1>{$PAGE_TITLE}</h1>

{if $err}
The following error(s) has occured:
	<ul class="err" style="color:red;">
		{foreach from=$err item=e}
			<li> {$e}</li>
		{/foreach}
	</ul>
{/if}

<form name="f_a" method="post" enctype="multipart/form-data" class="stdframe" onSubmit="return check_form();">
    <input type="hidden" name="analyze" value="1" />
	<b>ARMS POS File:</b>
	<input type="file" name="pos_file" size="60" />
	<input type="submit" value="Analyze" />
</form>

{if $data}
    <br />
	<form name="f_b" method="post" class="stdframe" onSubmit="return check_import();">
	    <input type="hidden" name="import_pos" value="1" />
	    
	    <h3>Analyze Information</h3>
	    <table>
	        <tr>
	            <td><b>Filename</b></td>
	            <td>
	                {$data.file_info.name}
	                <input type="hidden" name="history_filename" value="{$data.file_info.history_filename}" />
	            </td>
	        </tr>
	        <tr>
	            <td><b>Size</b></td>
	            <td>
	                {$data.file_info.size} bytes
	            </td>
	        </tr>
	        <tr>
	            <td><b>Branch</b></td>
	            <td>{$data.sales_info.branch_code|default:'-'}</td>
	        </tr>
	        <tr>
	            <td><b>Counter</b></td>
	            <td>{$data.sales_info.counter_name|default:'-'}</td>
	        </tr>
	        <tr>
	            <td><b>Date</b></td>
	            <td>{$data.sales_info.date|default:'-'}</td>
	        </tr>
	        <tr>
	            <td><b>Total POS</b></td>
	            <td>{$data.pos.count|number_format}</td>
	        </tr>
	        <tr>
	            <td><b>Total Items</b></td>
	            <td>{$data.pos_items.count|number_format}</td>
	        </tr>
	    </table>
	    <input type="submit" value="Import" />
	    <span style="color:red;">Note: The previous POS will be delete before insert</span>
	</form>
{/if}

{if $import_data}
    <br />
	{if $import_data.success}
	    <b>Branch: </b>{$import_data.sales_info.branch_code|default:'-'}, &nbsp;
	    <b>Counter: </b>{$import_data.sales_info.counter_name|default:'-'}, &nbsp;
	    <b>Date: </b>{$import_data.sales_info.date|default:'-'}
	    <br />
	    <img src="/ui/icons/tick.png" align="absmiddle" /> Import Successfully ({$import_data.filename})
	{/if}
{/if}
{include file='footer.tpl'}
