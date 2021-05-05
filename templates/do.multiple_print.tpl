{*
4/20/2010 2:40:19 PM Andy
- Add search & print multiple DO

9/7/2010 4:14:56 PM Andy
- Make DO multiple print a preview to a new tab first.

6/23/2020 04:16 PM Sheila
- Updated button css
*}

<div id="div_multiple_print" class="curtain_popup" style="position:absolute;z-index:10000;width:700px;height:550px;display:none;border:2px solid #CE0000;">
	<div id="div_multiple_print_header"><span style="float:left;"><img src="ui/icons/printer.png" align="absmiddle" /> Multiple Printing</span>
		<span style="float:right;">
			<img src="/ui/closewin.png" align="absmiddle" onClick="default_curtain_clicked();" class="clickable"/>
		</span>
		<div style="clear:both;"></div>
	</div>
	<div id="div_multiple_print_content">
	    <form name="f_multiple_print" onSubmit="search_do_for_multiple_print();return false;">
	        <input type="hidden" name="do_type" value="{$do_type}" />
	        Search
	        <select name="search_type">
	            <option value="draft_do">Draft DO No (DD)</option>
	            <option value="proforma_do">Proforma DO No (PD)</option>
	            <option value="do_no">DO No</option>
	            <option value="inv_no">Invoice No</option>
	        </select>
			from <input type="text" name="no_from" size="10" /> to <input type="text" name="no_to" size="10" />
			<input type="submit" value="Refresh" id="btn_search_multiple_print" />
		</form>
		
		<form name="f_multiple_print_list" target="_blank" method="post">
		    <input type="hidden" name="a" value="multiple_print" />
		    
			<div style="background:white;height:400px;border:1px solid #cfcfcf;overflow-x:hidden;overflow-y:auto;" id="div_multiple_print_list">
			</div>
			<p align="center"><button class="btn btn-primary" id="btn_start_multiple_print" disabled><img src="ui/icons/printer.png" align="absmiddle" /> Print</button></p>
		</form>
	</div>
</div>
