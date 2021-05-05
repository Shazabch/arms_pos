{if !$skip_header}
{include file='header.print.tpl'}
<script type="text/javascript">
	document.title = '{$filename}';
</script>
<body onload="window.print()">
{/if}
{literal}
<style>
.sample {
  width: 289mm;
  height: 200mm;
  border:0px solid black;
}

.left{
  float: left;
  padding-top: 9mm;
  width: 40mm;
}

.right{
  float: left;
  padding-top:8mm;
  width: 80mm;
}

.clear{
  clear: both;
}


.not_for_sale{
	font-family: arial;
	font-size:15pt;
	background-color:black;
	color:white;
	margin-left:10px;
	padding:5px 5px;
}

.promotion{
	font-family: arial;
	font-size:22pt;
}

.currency{
	font-family: arial;
	font-size:13pt;
	margin-left:38mm;
}

.amount{
	font-family: impact;
	font-size:52pt;
	margin-left:38mm;
}

.barcode {
	font-family: "MRV Code39extMA", verdana, calibri;
	font-size: 10pt;
	margin-left:38mm;
}

.sample td{
	/*border:1px dotted #000;*/
}

</style>
{/literal}

{assign var="img" value="data:image/png;base64,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"}

{section name=page loop=$pages.sheet}
<div class=printarea>
  <div class="sample">
    <table height="100%" style="border:0px solid black;border-collapse:collapse;float: right;">
      <tr style="height:66mm;">
        <td valign="top" style="width:146mm;">
          <div class="left" style="padding-left:14mm;padding-top:7mm;">

          </div>
          <div class="right" style="padding-top:6mm">
            <span class="currency"><b>RM</b></span><br/>
            <span class="amount">{$voucher[page][0][0].voucher_value|number_format:2:".":","}</span><br/>
            <span class=barcode>*{$voucher[page][0][0].barcode_voucher_prefix}{$voucher[page][0][0].secur_barcode}*</span>
          </div>
          <div class="clear"></div>
        </td>
        <td valign="top" style="width:140mm;">
          <div class="left" style="padding-left:10mm;padding-top:7mm;">
            
          </div>
          <div class="right" style="padding-top:6mm">
            <span class="currency"><b>RM</b></span><br/>
            <span class="amount">{$voucher[page][0][1].voucher_value|number_format:2:".":","}</span><br/>
            <span class=barcode>*{$voucher[page][0][1].barcode_voucher_prefix}{$voucher[page][0][1].secur_barcode}*</span>
          </div>
          <div class="clear"></div>
        </td>
      </tr>
      <tr style="height:68mm;">
        <td valign="top">
          <div class="left" style="padding-left:14mm;">
            
          </div>
          <div class="right">
            <span class="currency"><b>RM</b></span><br/>
            <span class="amount">{$voucher[page][1][0].voucher_value|number_format:2:".":","}</span><br/>
            <span class=barcode>*{$voucher[page][1][0].barcode_voucher_prefix}{$voucher[page][1][0].secur_barcode}*</span>
          </div>
          <div class="clear"></div>
        </td>
        <td valign="top">
          <div class="left" style="padding-left:10mm;">

          </div>
          <div class="right">
            <span class="currency"><b>RM</b></span><br/>
            <span class="amount">{$voucher[page][1][1].voucher_value|number_format:2:".":","}</span><br/>
            <span class=barcode>*{$voucher[page][1][1].barcode_voucher_prefix}{$voucher[page][1][1].secur_barcode}*</span>
          </div>
          <div class="clear"></div>
        </td>
      </tr>
      <tr style="height:66mm;">
        <td valign="top">
          <div class="left" style="padding-left:14mm;">
            
          </div>
          <div class="right">
            <span class="currency"><b>RM</b></span><br/> 
            <span class="amount">{$voucher[page][2][0].voucher_value|number_format:2:".":","}</span><br/>
            <span class=barcode>*{$voucher[page][2][0].barcode_voucher_prefix}{$voucher[page][2][0].secur_barcode}*</span>
          </div>
          <div class="clear"></div>
        </td>
        <td valign="top">
          <div class="left" style="padding-left:10mm;">
            
          </div>
          <div class="right">
            <span class="currency"><b>RM</b></span><br/>     
            <span class="amount">{$voucher[page][2][1].voucher_value|number_format:2:".":","}</span><br/>            
            <span class=barcode>*{$voucher[page][2][1].barcode_voucher_prefix}{$voucher[page][2][1].secur_barcode}*</span>
          </div>
          <div class="clear"></div>
        </td>
      </tr>
    </table>
  </div>
</div>
{/section}
