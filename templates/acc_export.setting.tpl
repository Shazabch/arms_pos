{*
2016-03-07 10:40 AM Kee Kee
- Added legend to notify that SageUBS & Inter Application (IA) has removed from accounting software list

2016-11-02 11:58 AM Kee Kee
- Change "Customer, Account Payble/Receiver Settings" to "Customer, Purchase/Sales Account Settings"
- Change Table Header "Name" to "Description"

3/19/2018 5:48 PM Andy
- Enhance Other Setting to have "Remark".

06/25/2020 10:44 AM Sheila
- Updated button css
*}
{include file=header.tpl}
{literal}
<style>
  .table{
    border-collapse: collapse;
  }
</style>

{/literal}

<h1>{$PAGE_TITLE} - Setting</h1>
<form id="data-form" method=post class=form nama="f_a">

  <input type="hidden" name="load" value="1"/>
  {*if $smarty.const.BRANCH_CODE!='HQ'}
  <b>Use own Branch setting</b> <select name="use_own_branch">
    <option value="0">No</option>
    <option value="1">Yes</option>
  </select><br/>
  {/if*}
  <b>Format</b> <select name="export_type" onchange="submit_form();">
    {foreach from=$accountings key=class item=a}
    <option {if $form.export_type eq $class}selected="selected"{/if}>{$class}</option>
    {/foreach}
  </select>

  <button class="btn btn-primary" type="submit">Load</button><br/>
  <span style="white-space:normal;color:#ff0000;">Please note that SageUBS and IA Accounting Software is no longer under our Accounting Software Export Support List.<br/>For any remaining Accounting Software listed, their compatibility is only verified until the 1st of July 2016.<br/>ARMS Software will not be held liable for any compatibility issues resulting from any accounting software upgrades from that period after.
</span><br/>
</form>

{if $msg}
<font color="red">{$msg}</font>
{/if}

{if $normalAcc}
<form id="data-form" method=post class=form nama="f_a">
  <input type="hidden" name="a" value="save_setting"/>
  <input type="hidden" name="export_type" value="{$export_type}"/>

  {if $smarty.const.BRANCH_CODE!='HQ'}
  <b>Use own branch settings</b> <select name="use_own_settings" onchange="toggle_form()">
    <option value="0" {if !$use_own_settings}selected{/if}>No</option>
    <option value="1" {if $use_own_settings}selected{/if}>Yes</option>
  </select>
  <br/>
  {/if}
  <div id="data-form-details" {if $smarty.const.BRANCH_CODE!='HQ' && !$use_own_settings}style="display:none;"{/if}>

    <h4>Customer, Purchase/Sales Account Settings</h4>
    <table class="table" cellpadding="5" border="1">
      <tr>
        <th>Description</th>
        <th>Account Code</th>
        <th>Account Name</th>
      </tr>
      <tbody id="normalAcc">
      {foreach from=$normalAcc key=k item=acc}
      {if $acc.custom}
      <tr>
        <td>
          <a href="javascript:void(0);" onclick="remove_row(this);"><img border="0" title="Deactivate" src="ui/deact.png"></a>
          <input type="text" name="data[normal][custom][name][]" value="{$acc.key}"/>
        </td>
        <td>
          <input type="text" name="data[normal][custom][account_code][]" value="{$acc.account.account_code}"/>
        </td>
        <td><input type="text" name="data[normal][custom][account_name][]" value="{$acc.account.account_name}"/></td>
        <td></td>
      </tr>
      {else}
      <tr>
        <td>{$acc.name}</td>
        <td>
          <input type="hidden" name="data[normal][{$k}][name]" value="{$acc.name}"/>
          <input type="text" name="data[normal][{$k}][account_code]" value="{$acc.account.account_code}"/>
        </td>
        <td><input type="text" name="data[normal][{$k}][account_name]" value="{$acc.account.account_name}"/></td>
        <td>{if $acc.help}{$acc.help}{/if}</td>
      </tr>
      {/if}
      {/foreach}
      </tbody>
    </table>
    <button type="button" onclick="add_row('normalAcc');">Add Row</button>

    {if $gstAcc}
      <h4>GST Account Settings</h4>
      <table class="table" cellpadding="5" border="1">
        <tr>
          <th>Description</th>
          <th>Account Code</th>
          <th>Account Name</th>
        </tr>
        <tbody id="gstAcc">
        {foreach from=$gstAcc key=k item=acc}
        {if $acc.custom}
        <tr>
          <td>
            <a href="javascript:void(0);" onclick="remove_row(this);"><img border="0" title="Deactivate" src="ui/deact.png"></a>
            <input type="text" name="data[gst][custom][name][]" value="{$acc.key}"/>
          </td>
          <td>
            <input type="text" name="data[gst][custom][account_code][]" value="{$acc.account.account_code}"/>
          </td>
          <td><input type="text" name="data[gst][custom][account_name][]" value="{$acc.account.account_name}"/></td>
        </tr>
        {else}
        <tr>
          <td>{$acc.name}</td>
          <td>
            <input type="hidden" name="data[gst][{$k}][name]" value="{$acc.name}"/>
            <input type="text" name="data[gst][{$k}][account_code]" value="{$acc.account.account_code}"/>
          </td>
          <td><input type="text" name="data[gst][{$k}][account_name]" value="{$acc.account.account_name}"/></td>
        </tr>
        {/if}
        {/foreach}
        </tbody>
      </table>
      <button type="button" onclick="add_row('gstAcc');">Add Row</button>
    {/if}
    <br/>
    {if $otherAcc}
    <h4>Other Settings</h4>
    <table class="table" cellpadding="5" border="1">
      <tr>
        <th>Name</th>
        <th>Value</th>
        <th>Remark</th>
      </tr>
      {foreach from=$otherAcc key=k item=acc}
      <tr>
          <td>{$acc.name}</td>
          <td>
			<input type="hidden" name="data[other][{$k}][name]" value="{$acc.name}"/>
			{if $k eq 'job_as_branch_code'}
			<input type="checkbox" name="data[other][{$k}][data]" value="{$acc.data}"  {if $acc.data eq 1}checked{/if} />
			{else}
            <input type="text" name="data[other][{$k}][data]" value="{$acc.data}"/>
			{/if}
          </td>
		  
		  <td>{$acc.remark|nl2br|default:'-'}</td>
        </tr>
      {/foreach}
    </table>
    <br/>
    {/if}

  </div>
  <input type="submit" name="load" value="Save"/>
</form>
{/if}
{include file=footer.tpl}
{literal}
<script src="js/jquery-1.7.2.min.js"></script>
<script>
  var JQ = {};
  JQ = jQuery.noConflict(true);


  function submit_form() {
    document.getElementById("data-form").submit();
  }
  
  function toggle_form(){
    JQ('#data-form-details').toggle();
  }

  function add_row(table) {
    var new_row=JQ('#'+table+'-row').html();
    JQ("#"+table).append(new_row);
  }

  function remove_row(obj){
    if (confirm("Are you sure?")) {
      JQ(obj).closest('tr').remove();
    }
  }
</script>
{/literal}
<script type="text/template" id="normalAcc-row">
<tr>
  <td>
    <a href="javascript:void(0);" onclick="remove_row(this);"><img border="0" title="Deactivate" src="ui/deact.png"></a>
    <input type="text" name="data[normal][custom][name][]" value=""/></td>
  <td>
    <input type="text" name="data[normal][custom][account_code][]" value=""/>
  </td>
  <td><input type="text" name="data[normal][custom][account_name][]" value=""/></td>
  <td></td>
</tr>
</script>
<script type="text/template" id="gstAcc-row">
<tr>
  <td>
    <a href="javascript:void(0);" onclick="remove_row(this);"><img border="0" title="Deactivate" src="ui/deact.png"></a>
    <input type="text" name="data[gst][custom][name][]" value=""/>
  </td>
  <td>
    <input type="text" name="data[gst][custom][account_code][]" value=""/>
  </td>
  <td><input type="text" name="data[gst][custom][account_name][]" value=""/></td>
</tr>
</script>
