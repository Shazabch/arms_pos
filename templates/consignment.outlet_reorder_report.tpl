{include file="header.tpl"}
{literal}
<style>
  .form_branches{
    list-style: none; 
  }
  .form_branches li{
    float: left;
  }
</style>
{/literal}
<script>
  var phpself = '{$smarty.server.PHP_SELF}';
  {literal}    
  function refresh(s){    
    var href=document.location.href;
    var regex = new RegExp( "\\?s=[^&]*&?", "gi");
    href = href.replace(regex,'');
    regex = new RegExp( "\\&s=[^&]*&?", "gi");
    href = href.replace(regex,'');
    
    if(href.indexOf("?")>0){
      href=href+"&s="+s;
    }
    else{
      href=href+"?s="+s;
    }    
    regex=null
    document.location.href=href;
  }
  
  function load_region_branch(region,selected){
    if(region!=""){
      $('loading_id').update(_loading_);
      
      new Ajax.Request(phpself+"?a=load_branch",{
          parameters:{
              region: region,
              selected:selected
          },
          onComplete: function(e){
            $('loading_id').update(e.responseText);
          }
      });
    }
    else{
      $('loading_id').update('');
    }
  }
   
  function data_validate(){    
    if(document.f_a.region!=undefined && document.f_a.region.value==""){
      alert("Please select region");
      return false;
    }
    
    var chk_arr =  document.getElementsByName("b[]");
    var chklength = chk_arr.length;
    var checked=0;
    
    for(k=0;k< chklength;k++){
        if(chk_arr[k].checked) checked++;
    } 
  
    if(checked<=0){
      alert("Please select branch");
      return false;
    }
    return true;
  }
  
  function find(){
    if(!data_validate()) return;
    document.f_a.target="";
    document.f_a.print.value=0;
    document.f_a.submit();
  }
  
  function print(){
    document.f_a.target="_blank";
    document.f_a.print.value=1;
    document.f_a.submit();
  }
  
  {/literal}

 
</script>
<h1>{$PAGE_TITLE}</h1>
<form name=f_a class=noprint style="line-height:24px" method=get onsubmit="return data_validate();">
  <div class=stdframe style='background:#fff;'>
  <input type=hidden name=a value=find>
    <input type=hidden name=print value=0>
    {if $config.masterfile_branch_region}
    <b>Region</b> <select name="region" onchange="load_region_branch(this.value);">
    <option value="">-- Select --</option>
    {foreach from=$config.masterfile_branch_region key=region_code item=rg}
      <option value="{$region_code}" {if $smarty.request.region eq $region_code}selected="selected"{/if}>{$rg.name}</option>
    {/foreach}
      <option value="no_region" {if $smarty.request.region eq 'no_region'}selected="selected"{/if}>Other</option>
    </select>
    <br/>
    {/if}
    <span id='loading_id'></span>
    <b>Brand</b> {dropdown all="-- All --" name=brand_id values=$brands key='id' value='description' selected=$smarty.request.brand_id}
&nbsp;&nbsp;&nbsp;&nbsp;<b>Vendor</b> {dropdown all="-- All --" name=vendor_id values=$vendors key='id' value='description' selected=$smarty.request.vendor_id}
&nbsp;&nbsp;&nbsp;&nbsp;<b>SKU Type</b>
<select name="sku_type">
    <option value=''>-- All --</option>
    {foreach from=$sku_type item=r}
        <option value="{$r.sku_type}" {if $smarty.request.sku_type eq $r.sku_type}selected {/if}>{$r.sku_type}</option>
    {/foreach}
</select>
    <br/>
    <input type="checkbox" value="1" name="con_split_artno" {if $smarty.request.con_split_artno eq '1'}checked="checked"{/if}/><b>Split artno</b>
    <input type="checkbox" value="1" name="have_balance" {if $smarty.request.have_balance eq '1'}checked="checked"{/if}/><b>Item with balance left only</b>
    <input type=button onclick="find();" value="Find">
  </div>
</form>
<br/><br/>
{if $total_to_print > 0}
<h3>Total page to print: {$total_to_print} <button style="font:bold 20px Arial; background-color:#09c; color:#fff;" onclick="print();">Print</button></h3>

<br/>

{$pagination}
{include file="consignment.outlet_reorder_report.page.tpl"}
{$pagination}
{else}
No Record found.
{/if}

{include file="footer.tpl"}
<script>
  {if $smarty.request.region}
  load_region_branch('{$smarty.request.region}','{if $smarty.request.b}{","|implode:$smarty.request.b}{/if}');
  {/if}
</script>