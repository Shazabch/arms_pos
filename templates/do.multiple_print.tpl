{*
4/20/2010 2:40:19 PM Andy
- Add search & print multiple DO

9/7/2010 4:14:56 PM Andy
- Make DO multiple print a preview to a new tab first.

6/23/2020 04:16 PM Sheila
- Updated button css
*}

  


<div class="modal" id="div_multiple_print">
	<div class="modal-dialog modal-lg modal-dialog-centered" role="document">
		<div class="modal-content ">
			<div class="modal-header bg-danger">
				<h6 class="modal-title text-center text-white" id="div_multiple_print_header">
					<img src="ui/icons/printer.png" align="absmiddle" /> Multiple Printing
				</h6><button aria-label="Close" class="close" data-dismiss="modal" type="button"><span aria-hidden="true" class="text-white">&times;</span></button>
			</div>
			<div class="modal-body" id="div_multiple_print_content">
				<form name="f_multiple_print" onSubmit="search_do_for_multiple_print();return false;">
					<div class="form-inline mt-2">
						<input type="hidden" name="do_type" value="{$do_type}" />
				&nbsp;	<b class="form-label">Search</b>&nbsp;
					<select class="form-control" name="search_type">
						<option value="draft_do">Draft DO No (DD)</option>
						<option value="proforma_do">Proforma DO No (PD)</option>
						<option value="do_no">DO No</option>
						<option value="inv_no">Invoice No</option>
					</select>
					&nbsp;<b class="form-label">from</b>&nbsp; <input class="form-control" type="text" name="no_from"  /> 
					&nbsp;<b class="form-label">to</b> &nbsp; <input class="form-control" type="text" name="no_to"  />
					
					<input type="submit" class="btn btn-primary  ml-2" value="Refresh" id="btn_search_multiple_print" />
				</div>
				</form>
				
				<form name="f_multiple_print_list" target="_blank" method="post">
					<input type="hidden" name="a" value="multiple_print" />
					
					<div style="background:white;height:400px;border:1px solid #cfcfcf;overflow-x:hidden;overflow-y:auto;" id="div_multiple_print_list">
					</div>
					<p align="center"><button class="btn btn-primary mt-2" id="btn_start_multiple_print" disabled><img src="ui/icons/printer.png" align="absmiddle" /> Print</button></p>
				</form>
			</div>
			
		</div>
	</div>
</div>