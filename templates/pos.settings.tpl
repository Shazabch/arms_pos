{*
11/20/2009 1:07:09 PM edward
- add fixed selling price check boxes

3/16/2010 5:13:04 PM edward
- disable Print advance in close counter

1/26/2011 9:50:35 AM yinsee
- add image for pos_receipt_image

1/27/2011 3:36:13 PM Alex
- set time control=>date format to Y-m-d and add checking on date and time

3/24/2011 11:09:47 AM Andy
- Add Membership Card Prefix. (need $config['membership_use_card_prefix'])

6/28/2011 3:32:45 PM Alex
- add show_last_points_in_receipt

6/29/2011 12:45:55 PM Andy
- Hide "Cash Redemption Have Point" and "Disable Multiple Promotion".

9/7/2011 3:14:52 PM Alex
- add receipt header and footer
 
10/06/2011 4:09:00 Kee Kee
- add print username in receipt who allow to goods return
- add show future promotion in price check

10/06/2011 12:00:26 PM Kee Kee
- Add preset receipt footer function(Customixe receipt footer)
- Print counter version in receipt footer

10/25/2011 2:26:00 PM Kee Kee
- Add "Allow_OPEN_CODE" on pos settings.

10/28/2011 5:36:00 PM Kee Kee
- Add "Allow scan sales agent code" on pos settings

11/09/2011 9:06:00 PM Kee Kee
- Separate receipt header and footer settings from "scan and print" tab to "Receipt header and footer"

11/09/2011 9:52:00 PM Kee Kee
- add "show membership expired date"

11/30/2011 11:43: AM Kee Kee
- Hide "Allow scan sales agent code" and "Allow_OPEN_CODE"

12/05/2011 5:07:00 PM Kee Kee
- Show "Allow Open Code"

12/19/2011 11:31:00 AM Kee Kee
- Change "Allow Open Code" to "Allow Invalid SKU Sold"

12/20/2011 10:17:00 AM Kee Kee
- Add "Type of scan sales agent

1/10/2012 11:31:32 AM Justin
- Added hidden field to capture prefix sa code from config.

1/16/2012 5:42:00 PM Kee Kee
- Added "Allow do deposit with cross branch"

2/08/2012 5:10:00 PM Kee Kee
- show sales agent setting with "$config.masterfile_enable_sa"
- hide invalid item settings and Deposit settings

3/12/2012 4:56:00 PM Kee Kee
- show return policy can be cross branch

3/14/2012 4:54:11 PM Alex
- change coupon and voucher prefix to uppercase

07/06/2012 9:57:00 AM Kee Kee
- Add prefix barcode unit price & unit code

07/10/2012 11:04:00 AM Kee Kee
- Add Weight Fraction and description for weight scale prefix barcode

7/24/2012 5:49 PM Andy
- Add "Allow cash refund".
- Add Payment settings group.

7/25/2012 3:02 PM Kee Kee
- Change "Always Print 2 Line" to "Receipt Row Format"
- Change "Always Print 2 Line" selection
- Add "Group Voucher amount in receipt"

7/25/2012 4:22 PM Kee Kee
- Change "Receipt Row Format" to "Item Row Format"

7/30/2012 10:08 AM Kee Kee
- Add "Control Mprice List"

08/07/2012 10:18 AM Kee Kee
- Add "Allow adjust point"
- Add "Reason adjust point list"

08/22/2012 10:53 AM Kee Kee
- Add "Print Cash Domination, Cash Advance, Cash Currency Advance & Top Up Cash Format"

09/11/2012 11:01 AM Kee Kee
- Add "Mutliple Quantity with weight scale code"

09/12/2012 10:26 AM Kee Kee
- Add Control Counter Date

09/14/2012 4:38 PM Kee Kee
- Add Control Search Item in counter

11/26/2012 10:14 AM Kee Kee
- Add Control to prompt multiple BOM

12/10/2012 5:00 PM Kee Kee
- Add Hide amount in Hold Bill Slot
- Add "Extra Payment" base on $config['counter_collection_extra_payment_type']

12/17/2012 10:32 AM Kee Kee
- Add Hide "OK" button in invalid item message box 

12/17/2012 11:14 AM Kee Kee
- Set Customize payment Title in receipt header
- Set Signature in receipt footer For Customize payment

12/18/2012 10:34 AM Kee Kee
- Add Check "Last Settlement" in customize payment

01/10/2012 11:38 AM Kee Kee
- Add "Print Actual quantity" for weight scale barcode item

01/11/2013 5:44 PM Kee Kee
- Add Receipt Format for paper which is more than 80 column

01/17/2013 10:21 AM Kee Kee
- remove trace error in javascript

01/30/2013 9:44 AM Kee Kee
- Add checking for "Barcode Prefix" settings.

1/31/2013 3:20 PM Justin
- Enhanced to do checking for barcode prefix to disallow user trying to put same code in different prefix setup.

02/20/2013 11:20 AM Kee Kee
- Add control unhold bill start time

02/26/2013 11:06 AM Kee Kee
- Add control "allow_sales_order"

03/01/2013 4:40 PM Kee Kee
- Add "quantity_decimal"

03/12/2013 10:37 AM Kee Kee
- Add new receipt format in Item row format "Print item without item's total price"

03/13/2013 9:53 AM Kee Kee
- Change receipt format from "Print item without item's total price" to "Print full item description and exclude barcode"

03/15/2013 4:07 PM Kee Kee
- Add "Deduct member point" when has receipt discount settings

05/21/2013 10:34 PM Kee Kee
- Add "Print BOM Item Detail" in receipt

06/19/2013 10:15 AM Kee Kee
- barcode unit price and barcode price change to can allow empty.
- add remark after the input box, if empty will use 2x.
- add consignment barcode prefix code to control consignment barcode

6/20/2013 5:43 PM Justin
- Bug fixed on some settings does not choose its own value after saved it as Yes.

6/24/2013 9:52 AM Kee Kee
- Add "Open drawer on new cashier shift"

6/26/2013 5:25 PM Kee Kee
- Add "Print hold bill"

07/01/2013 3:23 PM Kee Kee
- Add "Allow Invalid Card No" into membership 

07/01/2013 3:40 PM Kee Kee
- Add $config['membership_type'] into hidden fields

07/02/2013 10:25 AM Kee Kee
- Add $config['membership_valid_cardno '] into hidden fields

07/24/2013 9:39 AM Kee Kee
- Add "Currency Symbol","Rounding","Currency Format" into Currency Tab with $config['currency_settings']

8/20/2013 1:48 PM Justin
- Added new option "Use Own Image".
- Enhanced to support upload pos counter image by branch.

9/23/2013 4:08 PM Kee Kee
- Added "Print Category/Item Sales in Cash Domination report"

9/24/2013 4:18 PM Kee Kee
- Added new setting "Allow price with decimal"

9/25/2013 2:58PM Kee Kee
- Added config.membership_module into hidden fields
- Added Do not prompt for member/race control (when config.membership_module is off)

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

12/30/2013 4:37 Pm Kee Kee
- Added "Allow use ART No as Barcode"

03/05/2014 9:30 AM Kee Kee
- Added "Use running no as receipt no"

03/20/2014 2:10 PM Kee Kee
- Add "Create/Checkout Document"

3/21/2014 1:28 PM Justin
- Modified the wording from "Check" to "Cheque".

4/18/2014 2:12 PM Andy
- Limit currency name to maximum 3 character.

8/29/2014 9:39 PM Kee Kee
- Added Control scan member with Nric/Name
- Added Control view member Purchase history

2/10/2014 4:33 PM Ding Ren
- Added Service Charges

17/10/2014 12:14 PM Ding Ren
- Added Calculate Service Charges before receipt discount

6/11/2014 5:26 PM Ding Ren
- Add Goods ReturnReason Settings

03/26/2015 11:26 AM Ding Ren
- Print GST summary together with Cash Denomination

04/24/2015 11:06 PM Kee Kee
- Remove check receipt header image height

08/27/2015 14:51 PM Kee Kee
- Added help link beside "Item Row Format"

11/12/2015 10:33 AM Andy
- Remove "Deduct member point" and "Allow to create/checkout/view office document in counter".

4/29/2016 14:42 Qiu Ying
- Cutoff time enhance to allow minutes control

5/5/2016 17:05 Qiu Ying
- Print Deleted Items together with Cash Denomination
- Print Cash Advance together with Cash Denomination

05/31/2016 14:30 Edwin
- Hide Currency Format(Decimal Price) and set default decimal places to 2.
- Show guideline link on Receipt Header.

6/24/2016 10:25 Qiu Ying
- Add description for Last Settlement
- Print Other Payments & Credit Cards Variance together with Cash Denomination
- Print Cancelled Receipts together with Cash Denomination

6/28/2016 1:11 PM Andy
- Rename Top Up to Cash In.

7/5/2016 13:55 Qiu Ying
- Print Receipts Discount together with Cash Denomination

9/5/2016 17:33 Qiu Ying
- Hide "Allow do return policy from other branch receipt"

11/08/2016 9:46 AM Kee Kee
- Add "Barcode & Item Description in same row"

11/21/2016 2:20 PM Andy
- Hide Foreign Currency Settings.
- Add notification word for Item Row Format.

11/16/2016 10:52 AM Andy
- Change "Allow Cash Refund" default value from " No" to "Cash refund with privilege".

01/06/2017 11:14 AM Kee Kee
- Added "unit_code_weight_fraction" setting and set default value with 100g 

02/06/2017 6:32 PM Kee Kee
- Hide "Currency" tab

02/08/2017 11:18 AM Kee Kee
- Fixed "Receipt Header and Footer" tab failed to hide after Hide Currency Tab

2/21/2017 11:38 AM Andy
- Enhanced to prompt notification when user enable "Check Decimal Qty".

2/24/2017 5:24 PM Zhi Kai
- Adding "Preset Receipt Remark" in order to be sync to Counter.

4/5/2017 11:04 AM Chong Meng
- Added "Skip print "Missed Point" on receipt".

4/14/2017 10:25 AM Kee Kee
- Added "Hide Payment Information Screen button" control setting under Payment Settings

4/25/2017 9:01 AM Khausalya
- Enhanced changes from RM to use config setting. 

5/18/2017 5:09 PM Chong Meng
- Added function show_available_version
- Added notification image beside "Hide Payment Information Screen button" under Payment Settings
- Added notification image beside "Skip print "Missed Point" on receipt" under Membership Settings.

5/19/2017 10:30 AM Chong Meng
- Added parameta into function show_available_version to accept version numbers.

6/13/2017 9:38 AM Khausalya
- Fixed "after remove customize receipt footer it still show out"

7/13/2017 9:05 AM Kee Kee
- Added "Company Logo" under Display Settings

08/02/2017 11:07 AM Kee Kee
- Added "Skip Print Sales Agent together with Cash Denomination" under Print Settings

09/14/2017 16:46 PM Kee Kee
- Added "Denomination Summary Format" under Print Settings

07/11/2017 15:45 PM Kee Kee
- Added "Cash Advance Default Reason" under Security

19/12/2017 13:59 PM Kee Kee
- Fixed save wrong company logo setting name. should be save pos_company_logo_size instead of pos_company_logo

29/12/2017 15:33 PM Kee Kee
- Change [?] link from kb.arms.my to helpdesk

10/01/2018 16:15 PM Kee Kee
- Change Item Row Format Name "Barcode, Item Description, price & quantity in same row (suitable for receipt which is >= 75)" to "Letter/A4"

11/01/2018 16:00 PM Kee Kee
- Added barcode_unit_code_mcode_length & barcode_price_code_mcode_length

12/01/2018 15:25 PM Kee Kee
- Added Prompt To Suggest Print Full Tax Invoice & Minimum Amount Prompt To Suggest Print Full Tax Invoice

2/8/2018 5:39 PM Justin
- Enhanced to hide "Prompt To Suggest Print Full Tax Invoice" and "Minimum Amount Prompt To Suggest Print Full Tax Invoice" when the selected branch is not under GST registered.

2/14/2018 5:37 PM Andy
- Move print full tax invoice settings to "Scan and Print".
- Modify the require v193 to v194.

3/23/2018 12:13 PM Andy
- Fixed Barcode Unit Code Weight Fraction bug.

04/18/2018 5:37 PM Brandon
- Add upload top right banner function. 

6/20/2018 3:15 PM Andy
- Enhanced to able to override foreign currency rate by branch.

1/17/2019 11:48 AM Andy
- Fixed Unit and Price Code sample "CD", changed to "D".

2/12/2019 03:00 PM Justin
- Added new setting "Enable Category & Promotion Discount".

3/8/2019 4:43 PM Andy
- Added eWallet POS Settings.

3/25/2019 10:40 AM Justin
- Added new settings "Hide POS Date Popup during POS Program Startup".

4/4/2019 1:34 PM Justin
- Bug fixed on eWallet list does not showing in proper alignment.

5/2/2019 11:25 AM William
- Add remark to indicate 21/29 is 13 digits and 22/23 is 18 digits.

7/4/2019 11:06 AM Justin
- Enhanced to have new settings for Self Checkout.

9/6/2019 1:56 PM Justin
- Enhanced to disable the function "Enable Category & Promotion Discount".

10/9/2019 5:24 PM Justin
- Enhanced to have new settings "Do not print membership information on receipt".

12/11/2019 2:30 PM Andy
- Added Dual Screen Slide Show Help.

5/8/2020 1:48 PM Justin
- Enhanced to show the resolution size guidelines for all the image attachments under Display tab.

06/30/2020 04:00 PM Sheila
- Updated button css.

9/23/2020 3:12 PM William
- Enhanced to highlight custom payment type column.
- Enhanced "Customer Display Welcome Message 1" and "Customer Display Welcome Message 2" only allow enter alphanumeric.

20/2/2020 12:22 PM Andy
- Removed "Print BOM Item Detail".

10/19/2020 1:28 PM Shane
- "Do not prompt for member / race during payment" setting is now always shown regardless of Membership module is on or off.

12/30/2020 11:57 AM Shane
- Added "Receipt Header Format" setting

12/31/2020 4:12 PM Shane
- Added "Switch off Day End Time for All Branches" (HQ only) and "Apply Day End Time" setting under Time Control > POS Backend

1/7/2021 1:26 PM Shane
- Added "Use Own Design", "POS Counter Design", "POS Counter Theme Color" and "Is POS Backend Mode" settings

1/14/2021 1:49 PM Shane
- Added new tab "POS Backend"
- Moved settings "Switch off Day End Time for All Branches", "Apply Day End Time", and "Is POS Backend Mode" to POS Backend tab
- Add checking to "tab_sel" function, if tb+i exists only then set the display style.

1/19/2021 3:44 PM Shane
- Added "Show RSP and RSP Discount" and "Print RSP and RSP Discount" settings.

1/21/2021 10:53 AM Shane
- Added "Show other types of code at pop ups" setting.

1/26/2021 2:12 PM Shane
- Added explanation for "POS Backend Mode", "Switch off Day End Time for All Branches", and "Apply Day End Time" settings.

2/1/2021 5:00 PM Shane
- Added upload image function for POS Backend.

2/10/2021 2:15 PM Shane
- Added "Show Member Type and Patient Medical Record information after scanning member" setting.

2/15/2021 5:41 PM Shane
- Added audio file upload function.

2/18/2021 12:48 PM Shane
- Added checking for audio file upload to accept .mp3 and .wav only.

2/26/2021 2:53 PM Shane
- Added version info for POS Counter Design.

3/12/2021 6:04 PM Shane
- Added "Skip Print Receipt Printed Date and Time", "Print Receipt No as Receipt's Invoice No", and "Print Receipt No with Prefix" function.

4/12/2021 4:04 PM Shane
- Removed "POS Backend Mode" in POS Settings. Moved to Counter Setup module.

4/14/2021 11:47 AM Shane
- Added "Force Privilege Override Popup" settings.
- Added "Auto run Sync in Background" setting.

4/19/2021 11:25 AM Shane
- Added "Always Prompt multiple UOM when scanning Parent Item only" setting.
*}


{include file=header.tpl}
{literal}
<style>
.sh
{
    background-color:#ff9;
}

.stdframe.active
{
 	background-color:#fea;
	border: 1px solid #f93;
}
</style>
{/literal}

<link rel="stylesheet" type="text/css" media="all" href="js/jscalendar/calendar-blue.css" title="calendar-blue" />
<script type="text/javascript" src="js/jscalendar/calendar.js"></script>
<script type="text/javascript" src="js/jscalendar/lang/calendar-en.js"></script>
<script type="text/javascript" src="js/jscalendar/calendar-setup.js"></script>


<script type="text/javascript">
var foreign_currency_decimal_points = int('{$config.foreign_currency_decimal_points}');

{literal}
function del_img(n, action)
{
	if (confirm('Are you sure?'))
	{
		document.forms['f_'+n].a.value=action;
		document.forms['f_'+n].submit();
	}
}

function upload_img(n, action)
{
	if (confirm('Are you sure?'))
	{
		if(n != "pos_dual_screen_image" && n != "branch_pos_dual_screen_image"){
			$('img_'+n).width = 16;
			$('img_'+n).src = '/ui/clock.gif';
		}
		
		document.forms['f_'+n].a.value = action;
		document.forms['f_'+n].submit();
	}
}

function upload_audio(n){
	if (confirm('Are you sure?'))
	{		
		document.forms['f_'+n].a.value = 'upload_audio';
		document.forms['f_'+n].submit();
	}
}

function upload_audio_callback(fname,target){
	var content = '<audio controls><source src="'+target+'"></audio><br>'+target+'<div style="float:right;width:20px"><img src="/ui/icons/delete.png" onclick="del_audio(\''+fname+'\');" align=absmiddle></div>';
	$('div_audio_'+fname).update(content);
	document.forms['f_'+fname].a.value = 'upload_audio';
	alert('Audio file uploaded.');
}

function del_audio(n)
{
	if (confirm('Are you sure?'))
	{
		document.forms['f_'+n].a.value = 'del_audio';
		document.forms['f_'+n].submit();
	}
}

function del_audio_callback(fname){
	var content = '<input type="file" accept="audio/*" name="'+fname+'" onchange="upload_audio(\''+fname+'\');">';
	$('div_audio_'+fname).update(content);
	document.forms['f_'+fname].a.value = 'upload_audio';
	alert('Audio file deleted.');
}

function check_setting(ff)
{
	if (ff.elements['form[cust_display_line1]'].value.length > 20)
	{
		alert('Customer display welcome message #1 cannot more than 20 charater.');
		ff.elements['form[cust_display_line1]'].focus();
		return false;
	}

	if (ff.elements['form[cust_display_line2]'].value.length > 20)
	{
		alert('Customer display welcome message #2 cannot more than 20 charater.');
		ff.elements['form[cust_display_line2]'].focus();
		return false;
	}

	//check date
	var date_from=ff.elements['form[sales_date_adjustment][date_from]'];
	var date_to=ff.elements['form[sales_date_adjustment][date_to]'];

	if ((date_to.value && !date_from.value)){
		alert("Missing date from data");
		return false
	}else if (date_from.value && !date_to.value){
        alert("Missing date to data");
		return false
	}

    if (!checkdatetime(date_from,'date')){
        tab_sel(2);
		return false;
	}

	if (!checkdatetime(date_to,'date')){
        tab_sel(2);
		return false;
    }

    var date_from_v = new Date(date_from.value);
    var date_to_v = new Date(date_to.value);

    if ( date_from_v > date_to_v ){
        tab_sel(2);
		alert("Date to cannot more than date from");
		return false;
	}

	//check time
	var time_from = ff.elements['form[sales_date_adjustment][time_from]'];
	var time_to = ff.elements['form[sales_date_adjustment][time_to]'];

	if ((time_to.value && !time_from.value)){
		alert("Missing time from data");
		return false
	}else if (time_from.value && !time_to.value){
        alert("Missing time to data");
		return false
	}

	if (!checkdatetime(time_from,'time')){
		tab_sel(2);
		return false;
    }

	if (!checkdatetime(time_to,'time')){
        tab_sel(2);
		return false;
	}

	if (date_from.value == date_to.value && time_from.value && time_to.value){
	    var time_from_v = time_from.value.split(':');
	    var time_to_v = time_to.value.split(':');

	    if (time_from_v[0] > time_to_v[0] ){
	        tab_sel(2);
			alert("Time to cannot more than time from");
			return false;
		}else if (time_from_v[0] == time_to_v[0]){
			if (time_from_v[1] > time_to_v[1]){
		        tab_sel(2);
				alert("Time to cannot more than time from");
				return false;
			}
		}
	}

	//scan settings
	if (int($('use_arms_coupon_id').value)>0){
		if (!$('barcode_coupon_prefix_id').value){
			alert("Missing barcode coupon prefix.");
			return false;
		}
	}

	if (int($('use_arms_voucher_id').value)>0){
		if (!$('barcode_voucher_prefix_id').value){
			alert("Missing barcode voucher prefix.");
			return false;
		}
	}

	//Check preset receipt footer date and time format
	for(i=0;i<4;i++)
	{
		//check date
		var date_from=ff.elements['form[preset_receipt_footer][receipt_footer]['+i+'][date_from]'];
		var date_to=ff.elements['form[preset_receipt_footer][receipt_footer]['+i+'][date_to]'];

		if ((date_to.value && !date_from.value)){
			alert("Missing date from data");
			return false
		}else if (date_from.value && !date_to.value){
			alert("Missing date to data");
			return false
		}

		if (!checkdatetime(date_from,'date')){
			tab_sel(0);
			return false;
		}

		if (!checkdatetime(date_to,'date')){
			tab_sel(0);
			return false;
		}

		var date_from_v = new Date(date_from.value);
		var date_to_v = new Date(date_to.value);

		if ( date_from_v > date_to_v ){
			tab_sel(0);
			alert("Date to cannot more than date from");
			return false;
		}

		//check time
		var time_from = ff.elements['form[preset_receipt_footer][receipt_footer]['+i+'][time_from]'];
		var time_to = ff.elements['form[preset_receipt_footer][receipt_footer]['+i+'][time_to]'];

		if ((time_to.value && !time_from.value)){
			alert("Missing time from data");
			return false
		}else if (time_from.value && !time_to.value){
			alert("Missing time to data");
			return false
		}

		if (!checkdatetime(time_from,'time')){
			tab_sel(0);
			return false;
		}

		if (!checkdatetime(time_to,'time')){
			tab_sel(0);
			return false;
		}

		if (date_from.value == date_to.value && time_from.value && time_to.value){
			var time_from_v = time_from.value.split(':');
			var time_to_v = time_to.value.split(':');

			if (time_from_v[0] > time_to_v[0] ){
				tab_sel(0);
				alert("Time to cannot more than time from");
				return false;
			}else if (time_from_v[0] == time_to_v[0]){
				if (time_from_v[1] > time_to_v[1]){
					tab_sel(0);
					alert("Time to cannot more than time from");
					return false;
				}
			}
		}
	}
	
	//check customer display message
	var text_format = /^[a-z\d\-_\s]+$/i;
	if(ff.elements['form[cust_display_line1]'].value != '' && !(text_format.test(ff.elements['form[cust_display_line1]'].value))){
		alert("Customer Display Welcome Message #1 only allow enter alphanumeric.");
		ff.elements['form[cust_display_line1]'].focus();
		return false;
	}
	if(ff.elements['form[cust_display_line2]'].value != '' && !(text_format.test(ff.elements['form[cust_display_line2]'].value))){
		alert("Customer Display Welcome Message #2 only allow enter alphanumeric.");
		ff.elements['form[cust_display_line2]'].focus();
		return false;
	}
	
	// foreign currency
	var foreign_currency_override_list = $(ff).getElementsBySelector("input.inp_foreign_currency_override");
	if(foreign_currency_override_list){
		for(var i=0,len=foreign_currency_override_list.length; i<len; i++){
			var inp_override = foreign_currency_override_list[i];
			
			// user got override
			if(inp_override.checked){
				var currency_code = $(inp_override).readAttribute('currency_code');
				var inp_rate = ff['form[foreign_currency_rate]['+currency_code+']'];
				
				if(inp_rate.value <= 0){
					alert('Foreign Currency ('+currency_code+') Rate cannot be zero.');
					return false;
				}
			}
		}
	}
	
	return true;
}

function do_save()
{
	$('use_day_end_time').disabled = false;
	$('day_end_time_hour').disabled = false;
	$('day_end_time_minute').disabled = false;
	
	if (check_setting(document.f_a))
	document.f_a.submit();
}

function tab_sel(n){
	var i = 0;
	while($('lst'+i)!=undefined)
	{
		if (i==n)
		{
		    $('lst'+i).className='active';
		    if($('tb'+i)!=undefined){
		    	$('tb'+i).style.display='';
		    }
		}
		else
		{
		    $('lst'+i).className='';
		    if($('tb'+i)!=undefined){
			    $('tb'+i).style.display='none';
			}
		}
		i++;
	}
}

function delrow(obj){
	Element.remove(obj.parentNode);
	return false;
}

function addrow(obj){
	var row_clone = $('rowtemplate').cloneNode(true);
	row_clone.style.display = '';
	$('current_ul').appendChild(row_clone);
	return false;
}

function checkdatetime(ele,type){

	var text=ele.value.trim();

	if (type == "date"){
		//check date
		var a = text.split("-");

		if (text){
			if(isNumeric(text) && text.length=='6'){
				var	year=text.slice(0,2);
				var	month=text.slice(2,4);
				var	day=text.slice(4,6);
				var	year='20'+year;

				var	cmonth=int(month)-1;

				if( day<=daysInMonth(cmonth, year) && month<13 && day>0 && month>0){
					ele.value=year+'-'+month+'-'+day;
					if (checkdatetime(ele,'date'))  return true;
				}
				else{
					alert('Invalid date format.');
					ele.value='';
					ele.focus();
					return false;
				}
			}else if(text.length>='8' && a.length==3){

				var	year=a[0];
				var	month=a[1];
				var	day=a[2];

				var	cmonth=int(month)-1;

				if( day<=daysInMonth(cmonth, year) && month<13 && day>0 && month>0){
		       	    if (month.length==1) month="0"+month;
		       	    if (day.length==1) day="0"+day;

					var input_date=new Date(ele.value);

					ele.value=year+"-"+month+"-"+day;
					return true;
				}else{
					alert('Invalid date format.');
					ele.value='';
					ele.focus();
					return false;
				}
			}else{
				alert('Invalid date format.');
				ele.value='';
				ele.focus();
				return false;
			}
		}else{
			ele.value='';
			return true;
		}
	}else{
	    //check time
		if(text){
			if (isNumeric(text) && text.length == 4){
				var	hour=text.slice(0,2);
				var	minute=text.slice(2,4);
				text = hour+":"+minute;
				ele.value=text;
			}

			var a = text.split(":");

		    if (a.length==2 && isNumeric(a[0]) && isNumeric(a[1])){
				var	hour=a[0];
				var	minute=a[1];
	            if (hour > 24 || minute > 59 || (hour==24 && minute>0)){
	                alert('Invalid time format.');
					ele.value='';
					ele.focus();
					return false;
				}else{
					return true;
				}
			}else{
                alert('Invalid time format.');
				ele.value='';
				ele.focus();
				return false;
			}
 		}else{
			ele.value='';
			return true;
		}
	}
}

function init_calendar(){
	// initial calendar
	Calendar.setup({
		    inputField     :    "inp_date_from",     // id of the input field
		    ifFormat       :    "%Y-%m-%e",      // format of the input field
		    button         :    "img_date_from",  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});

	Calendar.setup({
	    inputField     :    "inp_date_to",     // id of the input field
	    ifFormat       :    "%Y-%m-%e",      // format of the input field
	    button         :    "img_date_to",  // trigger for the calendar (button ID)
	    align          :    "Bl",           // alignment (defaults to "Bl")
	    singleClick    :    true
	});
	
	for(i=0;i<4;i++)
	{
		Calendar.setup({
		    inputField     :    "prf_date_from_"+i,     // id of the input field
		    ifFormat       :    "%Y-%m-%d",      // format of the input field
		    button         :    "img_date_from_"+i,  // trigger for the calendar (button ID)
		    align          :    "Bl",           // alignment (defaults to "Bl")
		    singleClick    :    true
		});
		
		Calendar.setup({
			inputField     :    "prf_date_to_"+i,     // id of the input field
			ifFormat       :    "%Y-%m-%d",      // format of the input field
			button         :    "img_date_to_"+i,  // trigger for the calendar (button ID)
			align          :    "Bl",           // alignment (defaults to "Bl")
			singleClick    :    true
		});		
	}
}

function toggle_arms_code(type){

	if (int($('use_arms_'+type+'_id').value)>0)
	{
	    $('arms_'+type+'_id').show();
		if(type=="coupon"){
			$('limit_arms_coupon').show();
		}
	}
	else{
	   $('arms_'+type+'_id').hide();
	   if(type=="coupon")
	   {
			$('limit_arms_coupon').hide();
		}
	}
}

function show_preset_receipt_footer(obj)
{
	if(obj.value==1)
		$('preset_receipt_footer').style.display = '';
	else
		$('preset_receipt_footer').style.display = 'none';
}

function change_print_hold_bill(obj)
{
	if(obj.value==1)
		$('print_hb').style.display = '';
	else
		$('print_hb').style.display = 'none';
}

function change_min_amt(obj)
{
	if(obj.value==1)
		$('min_amt').style.display = '';
	else
		$('min_amt').style.display = 'none';
}

function show_rows(id)
{
	id = id+1;
	classname = 'prf'+id;
	$(classname).style.display = '';
}

function hide_rows(id)
{
	var ff = document.f_a;		
	classname = 'prf'+id;
	
	$("prf_date_from_" + id).value = "";
	$("prf_date_to_" + id).value = "";
	$("prf_time_from_" + id).value = "";
	$("prf_time_to_" + id).value = "";
	ff['form[preset_receipt_footer][receipt_footer]['+id+'][receipt_amount]'].value="0";	
	ff['form[preset_receipt_footer][receipt_footer]['+id+'][receipt_footer_description]'].value="";	
	ff['form[preset_receipt_footer][receipt_footer]['+id+'][member_control]'].selectedIndex="0";
		
	$(classname).style.display = 'none';
}

function sales_agent(obj)
{
	var scan_sa_val = obj.value;
	if(scan_sa_val==2 || scan_sa_val==3)
	{
		$('type_of_scan_sales_agent').style.display = "";
	}
	else
	{
		$('type_of_scan_sales_agent').style.display = "none";
	}
}

function change_to_uppercase(ele){
	ele.value = ele.value.toUpperCase(); 
}

function show_receipt_header_customize_payment(obj)
{	
	if(obj.value==1)
		$('cp_receipt_header_footer').style.display = '';
	else
		$('cp_receipt_header_footer').style.display = 'none';
}

function check_barcode_prefix(obj){
	if(obj.value == "") return false;
	
	var code1 = document.f_a['form[barcode_unit_code_prefix]'].value;
	var code2 = document.f_a['form[barcode_price_code_prefix]'].value;
	var code3 = document.f_a['form[barcode_price_n_unit_code_prefix]'].value;
	var code4 = document.f_a['form[barcode_total_price_n_unit_code_prefix]'].value;
	if(obj.name != "form[barcode_unit_code_prefix]"){
		if(obj.name != "form[barcode_price_code_prefix]"){
			if(obj.name != "form[barcode_price_n_unit_code_prefix]"){
				code4 = "";
			}else code3 = "";
		}else code2 = "";
	}else code1 = "";
	
	if((code1 != "" && obj.value == code1) || (code2 != "" && obj.value == code2)  || (code3 != "" && obj.value == code3)  || (code4 != "" && obj.value == code4)){
		alert("Barcode Prefix ["+obj.value+"] is existed.");
		obj.value = "";
	}
}

function add_new_ds_image(img_path, img_name, div_path, del_method,is_branch_upload){
	var new_img = $('cron_ds_image').cloneNode(true).innerHTML;
	new_img = new_img.replace(/__LOCATION/g, img_path);
	new_img = new_img.replace(/__ID/g, img_name);
	new_img = new_img.replace(/__DELETE/g, del_method);
	new_img = new_img.replace(/display:none;/g, "");
	if(is_branch_upload){
		new_img = new_img.replace(/__BRANCH/g, "branch");
	}else new_img = new_img.replace(/__BRANCH_/g, "");

	new Insertion.Bottom($(div_path), new_img);
	
	alert("Image uploaded.");
}

function del_ds_img(obj, n, fname, action){
	if(confirm('Are you sure?')){
		obj.remove();
		document.forms['f_'+n].ds_img.value=fname;
		document.forms['f_'+n].a.value=action;
		document.forms['f_'+n].submit();
	}
}

function use_own_image_clicked(obj){
	if(obj.value == 1){ // use own image
		$("div_hq_image").hide();
		$("div_branch_image").show();
	}else{ // use HQ image
		$("div_hq_image").show();
		$("div_branch_image").hide();
	}
}

function pb_use_own_image_clicked(obj){
	if(obj.value == 1){ // use own image
		$("div_pb_hq_image").hide();
		$("div_pb_branch_image").show();
	}else{ // use HQ image
		$("div_pb_hq_image").show();
		$("div_pb_branch_image").hide();
	}
}

function use_own_design_clicked(obj){
	if(obj.value == 1){ // use own design
		$("tr_pos_counter_design").show();
		$("tr_pos_counter_color_theme").show();
	}else{ // use HQ design
		$("tr_pos_counter_design").hide();
		$("tr_pos_counter_color_theme").hide();
	}
}

function use_day_end_time_clicked(obj){
	if(obj.value == 1){ // use day end time
		$("div_day_end_time").show();
		$("div_day_end_time").style.display = 'inline-block';
	}else{ // do not use day end time
		$("div_day_end_time").hide();
	}
}

function switch_off_day_end_time(obj){
	if(obj.checked){
		$('use_day_end_time').disabled = true;
		$('day_end_time_hour').disabled = true;
		$('day_end_time_minute').disabled = true;
	}else{
		$('use_day_end_time').disabled = false;
		$('day_end_time_hour').disabled = false;
		$('day_end_time_minute').disabled = false;
	}
}

function grrs_add_row(){
    var new_tr = $('temp_grrs_row').cloneNode(true).innerHTML;

    new Insertion.Bottom($('reason_settings'), new_tr);
}

function grrs_remove_row(obj){
     if(obj == undefined) return;

     Element.remove(obj.parentNode.parentNode);
}

function prr_add_row(){
	var new_tr = $('temp_prr_row').cloneNode(true).innerHTML;
	
	new Insertion.Bottom($('receipt_remarks'), new_tr);
}

function prr_remove_row(obj){
	if(obj == undefined) return;
	
	Element.remove (obj.parentNode.parentNode);
}

function check_decimal_qty_status(){
	if (document.f_a["form[check_decimal_qty]"].value==1){
		show_check_decimal_notification();
	}
}

function show_check_decimal_notification(){
	alert("When you turn on this setting, all sku will default not allow to key in decimal qty. But you can setup which SKU is allow to key in decimal qty in SKU Masterfile.\n\nPlease go to:\n\nSKU Listing > Find > Edit SKU > Allow Decimal Qty\nTick the Counter option. ");
}

function show_available_version(ap_v,apb_v){
	if(ap_v && apb_v)
		alert("Available for ARMS POS V."+ap_v+" / ARMS POS BETA V"+apb_v+" and above. ");
	else if(ap_v)
		alert("Available for ARMS POS V."+ap_v+" and above. ");
	else if(apb_v)
		alert("Available for ARMS POS BETA V"+apb_v+" and above. ");
}

function foreign_currency_override_changed(code){
	var c = document.f_a['form[foreign_currency_override]['+code+']'].checked;
	
	document.f_a['form[foreign_currency_rate]['+code+']'].readOnly = !c;
}

function foreign_currency_rate_changed(code){
	var inp = document.f_a['form[foreign_currency_rate]['+code+']'];
	inp.value = float(round(inp.value, foreign_currency_decimal_points));
}

function show_ewallet_help(){
	alert('Please contact your ARMS Account Manager to request for this feature.');
}

function show_help(type){
	if(type=="main_background"){
		alert("Standard Recommended: 800x600\n- This image applies to the screen resolution of 800x600 only.\n- Screen resolution higher than 800x600 will have it's own menu view.");
	}else if(type=="main_login"){
		alert("Standard Recommended: 800x600\n- Can use higher image size If POS counter screen resolution is larger than 800x600.");
	}else if(type=="main_banner"){
		alert("Standard Recommended: 100x100\n");
	}else if(type=="receipt_header"){
		alert("Currently Image support size width between 200px - 588px and height between 48px - 100px.\n1) For ARMS-80IV Printer only support not more than 565px and height is 84px.\n2) For Epson Printer can become 288x48 for 48 column.\n3) For ARMS Printer can accept Image 588x100 for 100 column.");
	}else if(type=="company_logo"){
		alert("Standard Recommended: 400x300\n- Available for ARMS POS V.192 / ARMS POS BETA V311 and above. ");
	}else if(type=="slide_show"){
		alert("Standard Recommended: 550x550\n- If screen resolution from POS counter is lower than 1024x768, recommended to use lower size such as 300x300.");
	}else if(type=="top_right_banner"){
		alert("Standard Recommended: 400x31\n- Available for ARMS POS V.195 and above.");
	}else if(type=="pb_menu_body_image"){
		alert("Standard Recommended: 1366x642\n- Available for ARMS POS V.209 and above.");
	}else if(type=="pos_top_right_banner_backend"){
		alert("Standard Recommended: 306x100\n- Available for ARMS POS V.209 and above.");
	}

}

function show_notification_message(type){
	if(type=='pos_backend_mode'){
		alert("Yes: POS Counter will get a menu screen (POS Backend Menu).\n\nNo : POS Counter will not get a menu screen.");
	}else if(type=='switch_off_day_end_time'){
		alert("Checked: End of Day can be processed at any time for ALL branches. (This option only available at HQ)\n\nUnchecked: Each branch can only run End of Day process according to its own \"Apply Day End Time\"");
	}else if(type=='apply_day_end_time'){
		alert("Yes: End of Day can only be processed after the specified time for this Branch.\n\nNo : End of Day can be processed at any time for this Branch.");
	}else if(type=='use_audio'){
		alert("If checked (ticked) Use Audio and there is no audio file uploaded, system will use the default audio.")
	}
}
</script>
{/literal}
<table style="display:none;">
	<tbody id="temp_grrs_row" class="temp_grrs_row">
		<tr>
			<td><img src="/ui/closewin.png" align="absmiddle" onClick="grrs_remove_row(this);" class="clickable" title="Delete this row" /></td>
			<td><input type="text" name="form[grr_settings][code][]" class="grrs_code"></td>
			<td><input type="text" name="form[grr_settings][description][]" size="60"></td>
		</tr>
	</tbody>
	<tbody id="temp_prr_row" class="temp_prr_row">
		<tr>
			<td><img src="/ui/closewin.png" align="absmiddle" onClick="prr_remove_row(this);" class="clickable" title="Delete this row" /></td>
			<td><input type="text" name="form[table_resit_remark][]" class="prr_code"></td>
		</tr>
	</tbody>
</table>

{assign var=msg value=$smarty.request.msg}
<p align=center><font color=red>{$msg}</font></p>

<h1>{$PAGE_TITLE}</h1>
{if $BRANCH_CODE eq 'HQ'}
<form name=f method="post">
<b>Branch</b>
<select name=branch_id onchange="f.submit();">
{foreach from=$branch item=b}
<option value="{$b.id}" {if $form.branch_id == $b.id}selected{/if}>{$b.code}</option>
{/foreach}
</select>
</form>
{/if}
<form name=f_a method=post onsubmit="return check_setting(this);">
<input name=branch_id value="{$smarty.request.branch_id}" type=hidden>
<input name=a value=save type=hidden>
<input id=pos_main_bg_size name=form[pos_main_bg_size] value="{$form.pos_main_bg_size}" type=hidden>
<input id=pos_login_bg_size name=form[pos_login_bg_size] value="{$form.pos_login_bg_size}" type=hidden>
<input id=pos_main_banner_size name=form[pos_main_banner_size] value="{$form.pos_main_banner_size}" type=hidden>
<input id=pos_receipt_image_size name=form[pos_receipt_image_size] value="{$form.pos_receipt_image_size}" type=hidden>
<input id=pos_company_logo_size name=form[pos_company_logo_size] value="{$form.pos_company_logo_size}" type=hidden>
<input id=pos_top_right_banner_size name=form[pos_top_right_banner_size] value="{$form.pos_top_right_banner_size}" type=hidden>
<input id=pb_menu_body_image_size name=form[pb_menu_body_image_size] value="{$form.pb_menu_body_image_size}" type=hidden>
<input id=pos_top_right_banner_backend_size name=form[pos_top_right_banner_backend_size] value="{$form.pos_top_right_banner_backend_size}" type=hidden>
<input id=branch_pos_main_bg_size name=form[branch_pos_main_bg_size] value="{$form.branch_pos_main_bg_size}" type=hidden>
<input id=branch_pos_login_bg_size name=form[branch_pos_login_bg_size] value="{$form.branch_pos_login_bg_size}" type=hidden>
<input id=branch_pos_main_banner_size name=form[branch_pos_main_banner_size] value="{$form.branch_pos_main_banner_size}" type=hidden>
<input id=branch_pos_receipt_image_size name=form[branch_pos_receipt_image_size] value="{$form.branch_pos_receipt_image_size}" type=hidden>
<input id=branch_pos_company_logo_size name=form[branch_pos_company_logo_size] value="{$form.branch_pos_company_logo_size}" type=hidden>
<input id=branch_pos_top_right_banner_size name=form[branch_pos_top_right_banner_size] value="{$form.branch_pos_top_right_banner_size}" type=hidden>
<input id=branch_pb_menu_body_image_size name=form[branch_pb_menu_body_image_size] value="{$form.branch_pb_menu_body_image_size}" type=hidden>
<input id=branch_pos_top_right_banner_backend_size name=form[branch_pos_top_right_banner_backend_size] value="{$form.branch_pos_top_right_banner_backend_size}" type=hidden>
<input id=membership_module name=form[membership_module] value="{$config.membership_module}" type=hidden>
{if $config.membership_use_card_prefix}
	<input id=sa_code_prefix name=form[sa_code_prefix] value="{$config.masterfile_sa_code_prefix}" type=hidden>
{/if}
{if $config.membership_type}
	{foreach from=$config.membership_type key=key item=item}
	<input name="form[membership_type][{$key}]" value="{$item}" type=hidden>
	{/foreach}
{/if}
{if $config.membership_valid_cardno}
	<input name="form[membership_valid_cardno]" value="{$config.membership_valid_cardno}" type=hidden>
{/if}
<br>
<div class=tab style="height:21px;white-space:nowrap;">
<a href="javascript:tab_sel(0)" id=lst0>Scan and Print</a>
<a href="javascript:tab_sel(1)" id=lst1>Membership</a>
<a href="javascript:tab_sel(2)" id=lst2>Time Control</a>
<a href="javascript:tab_sel(3)" id=lst3>Display</a>
<a href="javascript:tab_sel(4)" id=lst4>Security</a>
<a href="javascript:tab_sel(5)" id=lst5>Receipt Header and Footer</a>
<a href="javascript:tab_sel(6)" {if !$config.foreign_currency} style="display: none;" {/if} id="lst6">Currency</a>
<a href="javascript:tab_sel(7)" id=lst7 {if !$config.pos_settings_pos_backend_tab} style="display:none;" {/if}>POS Backend</a>
<a href="javascript:tab_sel(8)" id=lst8>Audio</a>
</div>
<div style="border:1px solid #000;padding:10px;">
	<div id=tb0>
		<h3>Scan Settings</h3>
		<table border=0>
		<tr>
			<td bgcolor="#ffcc99" colspan="2"><b>Weighing Scale 13 Digits</b></td>
		</tr>
		<tr>
			<td><b>Barcode Unit Code Prefix</b><br /><span style="color:grey;">Barcode Unit Code Prefix is empty, counter will auto set value with 21</span></td>
			<td>
			<input name=form[barcode_unit_code_prefix] value="{$form.barcode_unit_code_prefix}" onchange="check_barcode_prefix(this);">
			&nbsp;<b>Weight Fraction</b>&nbsp;<select name=form[unit_code_weight_fraction] value="{$form.unit_code_weight_fraction}">
			<option value="100" {if !$form.unit_code_weight_fraction || $form.unit_code_weight_fraction eq "100"}selected{/if}>100g</option>
			<option value="1000" {if $form.unit_code_weight_fraction eq "1000"}selected{/if}>Kilogram(KG)</option>
				
		    </select> 
			&nbsp;<input type="checkbox" name="form[print_actual_quantiy][unit_code]" {if $form.print_actual_quantiy.unit_code}checked{/if}> Print actual quantity</td>
		</tr>
		<!--tr>
			<td><b>Enable Category & Promotion Discount <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(196);" /></b><br /><span style="color:grey;">Enable to run discounted price from category or promotion for Unit Code</span></td>
			<td>
			<input type="checkbox" name="form[enable_weigh_item_discount][unit_code]" {if $form.enable_weigh_item_discount.unit_code}checked{/if}></td>
		</tr-->
		<tr>
			<td><b>Unit Code PLU/MCode Length</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(194,314);" /> [<a href="https://armshelp.freshdesk.com/support/solutions/articles/22000218554-barcode-unit-price-code-format" target="_blank"><span style="color:#ff000;">?</span></a>]</td>
			<td>
				<select name=form[barcode_unit_code_mcode_length] value="{$form.barcode_unit_code_mcode_length}">
					<option value="4" {if $form.barcode_unit_code_mcode_length eq "4"}selected{/if}>4 (FF CCCC WWWWWW D)</option>
					<option value="5" {if !$form.barcode_unit_code_mcode_length || $form.barcode_unit_code_mcode_length eq "5"}selected{/if}>5 (FF CCCCC WWWWW D)</option>
					<option value="6" {if $form.barcode_unit_code_mcode_length eq "6"}selected{/if}>6 (FF CCCCCC WWWW D)</option>
				</select>				
			</td>
		</tr>
		<tr>
			<td><b>Barcode Price Code Prefix</b><br /><span style="color:grey;">Barcode Price Code Prefix is empty, counter will auto set value with 29</span></td>
			<td>
			<input name=form[barcode_price_code_prefix] value="{$form.barcode_price_code_prefix}" onchange="check_barcode_prefix(this);">
			&nbsp;<input type="checkbox" name="form[print_actual_quantiy][price_code]" {if $form.print_actual_quantiy.price_code}checked{/if}> Print actual quantity</td>
		</tr>
		<tr>
			<td><b>Price Code PLU/MCode Length</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(194,314);" /> [<a href="https://armshelp.freshdesk.com/support/solutions/articles/22000218554-barcode-unit-price-code-format" target="_blank"><span style="color:#ff000;">?</span></a>]</td>
			<td>
				<select name=form[barcode_price_code_mcode_length] value="{$form.barcode_price_code_mcode_length}">
					<option value="4" {if $form.barcode_price_code_mcode_length eq 4}selected{/if}>4 (FF CCCC PPPPPP D)</option>
					<option value="5" {if !$form.barcode_price_code_mcode_length || $form.barcode_price_code_mcode_length eq 5}selected{/if}>5 (FF CCCCC PPPPP D)</option>
					<option value="6" {if $form.barcode_price_code_mcode_length eq 6}selected{/if}>6 (FF CCCCCC PPPP D)</option>
				</select>
			</td>
    </tr>
		<tr><td colspan="3"><hr></td></tr>
		<tr>
			<td bgcolor="#ffcc99" colspan="2"><b>Weighing Scale 18 Digits</b></td>
		</tr>
		<tr>
			<td><b>Barcode Unit Price & Unit Code Prefix</b><br /><span style="color:grey;">Barcode Unit Price & Unit Code Prefix is empty, counter will auto set value with 23</span></td>
			<td>
			<input name=form[barcode_price_n_unit_code_prefix] value="{$form.barcode_price_n_unit_code_prefix}" onchange="check_barcode_prefix(this);">
			&nbsp;<input type="checkbox" name="form[print_actual_quantiy][unit_code_unit_price]" {if $form.print_actual_quantiy.unit_code_unit_price}checked{/if}> Print actual quantity</td>
		</tr>
		<tr>
			<td><b>Barcode Price & Unit Code Prefix</b><br /><span style="color:grey;">Barcode Unit Price & Unit Code Prefix is empty, counter will auto set value with 22</span></td>
			<td>
			<input name=form[barcode_total_price_n_unit_code_prefix] value="{$form.barcode_total_price_n_unit_code_prefix}" onchange="check_barcode_prefix(this);">			
			&nbsp;<input type="checkbox" name="form[print_actual_quantiy][unit_code_price_code]" {if $form.print_actual_quantiy.unit_code_price_code}checked{/if}> Print actual quantity</td>
		</tr>
		<tr>
			<td><b>Weight Fraction</b></td>
			<td>
				<select name=form[weight_fraction] value="{$form.weight_fraction}">
					<option value="1000">Kilogram(KG)</option>
					<option value="100" {if $form.weight_fraction eq "100"}selected{/if}>Gram</option>
				</select> 
			</td>
		</tr>
		<tr><td colspan="3" style="color:grey;">If item's scale type is set "weighted", the "Barcode Unit Price & Unit Code" and "Barcode Price & Unit Code" quantity will calculate with "Weight Fraction".</td></tr>
		<tr><td colspan="3"><hr></td></tr>
		<tr>
			<td><b>Mutliple Quantity with Weight Scale Barcode</b></td>
			<td>
				<select name=form[multiple_quantity] value="{$form.multiple_quantity}">
					<option value="0" {if $form.multiple_quantity eq "0" || !$form.multiple_quantity}selected{/if}>Not Allow</option>
					<option value="1" {if $form.multiple_quantity eq "1"}selected{/if}>Allow for PCS quantity</option>
					<option value="2" {if $form.multiple_quantity eq "2"}selected{/if}>Allow for weighted quantity</option>
					<option value="3" {if $form.multiple_quantity eq "3"}selected{/if}>Allow both</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Barcode consignment item prefix</b><br/>This kind of barcode item's quantity always will become 1</td>
			<td><input name=form[barcode_consignment_item_prefix] value="{$form.barcode_consignment_item_prefix}"></td>			
		</tr>
		<tr>
			<td><b>Consignment item mcode length</b></td>
			<td><input name=form[consignment_item_mcode_length] value="{$form.consignment_item_mcode_length}"></td>		
		</tr>
		<tr>
		    <td><b>Use ARMS Coupon</b></td>
		    <td>
		    <select id="use_arms_coupon_id" name=form[use_arms_coupon] onchange="toggle_arms_code('coupon');">
				<option value=1 {if $form.use_arms_coupon}selected{/if}>Yes</option>
				<option value=0 {if !$form.use_arms_coupon}selected{/if}>No</option>
		    </select>
			</td>
		</tr>
		<tr id='arms_coupon_id'>
		    <td bgcolor='#ffcc99'><b>Barcode Coupon Prefix</b></td>
			<td>
			<input id='barcode_coupon_prefix_id' onchange="change_to_uppercase(this)" name=form[barcode_coupon_prefix] value="{$form.barcode_coupon_prefix|default:'CP'}">
			</td>
		</tr>		
		<tr id="limit_arms_coupon">
		    <td bgcolor='#ffcc99'><b>Limit use coupon per receipt</b><br/></td>
			<td>
			<input id='limit_use_copon_per_receipt' name=form[limit_use_copon_per_receipt] value="{$form.limit_use_copon_per_receipt|default:0}">
			</td>
		</tr>
		<tr>
		    <td><b>Use ARMS Voucher</b></td>
		    <td>
		    <select id="use_arms_voucher_id" name=form[use_arms_voucher] onchange="toggle_arms_code('voucher');" >
				<option value=1 {if $form.use_arms_voucher}selected{/if}>Yes</option>
				<option value=0 {if !$form.use_arms_voucher}selected{/if}>No</option>
		    </select>
			</td>
		</tr>
		<tr id='arms_voucher_id'>
		    <td bgcolor='#ffcc99'><b>Barcode Voucher Prefix</b></td>
			<td>
			<input id='barcode_voucher_prefix_id' onchange="change_to_uppercase(this)" name=form[barcode_voucher_prefix] value="{$form.barcode_voucher_prefix|default:'VC'}">
			</td>
		</tr>
		<tr>
		    <td><b>Batch No Days Notify</b></td>
			<td>
			<input name=form[batch_no_days_notify] value="{$form.batch_no_days_notify|default:0}">
			</td>
		</tr>		
		<tr>
		<td><b>Prompt multiple UOM</b></td>
		<td>
		<select name=form[multiple_uom]>
		<option value=1 {if $form.multiple_uom}selected{/if}>Yes</option>
		<option value=0 {if !$form.multiple_uom}selected{/if}>No</option>
		</select>
		</td>
		</tr>
		<tr>
		<td><b>Always Prompt multiple UOM when scanning Parent Item only</b><br>This only works when Prompt multiple UOM setting is turned on</td>
		<td>
		<select name=form[multiuom_scan_parent_only]>
		<option value=1 {if $form.multiuom_scan_parent_only}selected{/if}>Yes</option>
		<option value=0 {if !$form.multiuom_scan_parent_only}selected{/if}>No</option>
		</select>
		</td>
		</tr>
		<tr>
		<td><b>Check Decimal Quantity</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_check_decimal_notification();" /></td>
		<td>
		<select name="form[check_decimal_qty]" onchange="check_decimal_qty_status();">
		<option value="1" {if $form.check_decimal_qty}selected{/if}>Yes</option>
		<option value="0" {if !$form.check_decimal_qty}selected{/if}>No</option>
		
		</select>
		</td>
		</tr>
		<!--tr>
		<td><b>Fixed Selling Price</b></td>
		<td>
		{foreach from=$config.sku_multiple_selling_price key=k item=f}
		<b>{$f}</b>
		<select name="form[fixed_price][{$f}]">
		<option value=1>Yes</option>
		
  		<option value=0 {if !$form.fixed_price.$f}selected{/if}>No</option>
		</select>

        {/foreach}
        </td>
		</tr-->
		<tr>
		<td><b>Show future promotion in price check</b></td>
		<td>
			<select name="form[future_promotion]">
			<option value=1 {if $form.future_promotion}selected{/if}>Yes</option>
			<option value=0 {if !$form.future_promotion}selected{/if}>No</option>			
			</select>
		</td>
		</tr>
		<tr>
		<td><b>Allow Invalid SKU Sold</b></td>
		<td>
			<select name="form[allow_invalid_sku]">
			<option value="1" {if $form.allow_invalid_sku}selected{/if}>Yes</option>
			<option value="0" {if !$form.allow_invalid_sku}selected{/if}>No</option>
			</select>
		</td>
		</tr>
		<tr {if !$config.masterfile_enable_sa}style="display:none;"{/if}>
		<td><b>Allow scan sales agent code</b></td>
		<td>
			<select name="form[allow_scan_sales_agent]" onclick="sales_agent(this)">
			<option value="0" {if !$form.allow_scan_sales_agent or !$config.masterfile_enable_sa}selected{/if}>No sales agent</option>
			<option value="1" {if $form.allow_scan_sales_agent eq 1 && $config.masterfile_enable_sa}selected{/if}>Scan sales agent by receipt</option>
			<option value="2" {if $form.allow_scan_sales_agent eq 2 && $config.masterfile_enable_sa}selected{/if}>Scan sales agent by item</option>
			<option value="3" {if $form.allow_scan_sales_agent eq 3 && $config.masterfile_enable_sa}selected{/if}>Scan sales agent by item or receipt</option>
			</select>
		</td>
		</tr>
		<tr id="type_of_scan_sales_agent" {if $form.allow_scan_sales_agent neq 2 && $form.allow_scan_sales_agent neq 3 or !$config.masterfile_enable_sa}style="display:none;"{/if}>
		<td><b>Type of scan sales agent code<br/>(only for item)</b></td>
		<td>
			<select name="form[type_of_scan_sales_agent]">
			<option value="1" {if $form.type_of_scan_sales_agent eq 1}selected{/if}>Scan sales agent code then scan item barcode</option>
			<option value="0" {if !$form.type_of_scan_sales_agent}selected{/if}>Scan item barcode then scan sales agent code</option>			
			</select>
		</td>
		</tr>
		<tr>
			<td><b>Control cashier to search item in counter</b></td>
			<td>
				<select name="form[control_cashier_serach_item]">
					<option value="1" {if $form.control_cashier_serach_item}selected{/if}>Yes</option>
					<option value="0" {if !$form.control_cashier_serach_item}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Prompt multiple BOM</b></td>
			<td>
				<select name=form[multiple_bom]>
				<option value=1 {if $form.multiple_bom}selected{/if}>Yes</option>
				<option value=0 {if !$form.multiple_bom}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Hide receipt amount in hold bill slot</b></td>
			<td>
				<select name=form[hide_amount_hold_bill_slot]>
				<option value=1 {if $form.hide_amount_hold_bill_slot}selected{/if}>Yes</option>
				<option value=0 {if !$form.hide_amount_hold_bill_slot}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Hide "OK" button in invalid item message box</b></td>
			<td>
				<select name=form[hide_button_in_invalid_item]>
				<option value=1 {if $form.hide_button_in_invalid_item}selected{/if}>Yes</option>
				<option value=0 {if !$form.hide_button_in_invalid_item}selected{/if}>No</option>
				</select>
			</td>
		</tr>		
		<tr>
			<td><b>Unhold bill reset start time</b></td>
			<td>
				<select name=form[unhold_bill_reset_stime]>
				<option value=1 {if $form.unhold_bill_reset_stime}selected{/if}>Yes</option>
				<option value=0 {if !$form.unhold_bill_reset_stime}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Allow Sales Order</b></td>
			<td>
				<select name=form[allow_sales_order]>
				<option value=1 {if $form.allow_sales_order}selected{/if}>Yes</option>
				<option value=0 {if !$form.allow_sales_order}selected{/if}>No</option>
				</select>
			</td>
		</tr>		
		<tr>
			<td><b>Item's quantity decimal</b></td>
			<td>
				<select name=form[quantity_decimal]>
				<option value=2 {if $form.quantity_decimal eq 2}selected{/if}>2</option>				
				<option value=3 {if !$form.quantity_decimal or $form.quantity_decimal eq "3"}selected{/if}>3</option>
				<option value=4 {if $form.quantity_decimal eq 4}selected{/if}>4</option>
				</select>
			</td>
		</tr>		
		<tr>
			<td><b>Enter price without decimal</b></td>
			<td>
				<select name=form[price_decimal]>
				<option value=0 {if !$form.price_decimal}selected{/if}>No</option>				
				<option value=1 {if $form.price_decimal eq 1}selected{/if}>Yes</option>
				</select>
				Eg: Enter "350" = {$config.arms_currency.symbol} 3.50
			</td>			
		</tr>		
		<tr>
			<td><b>Allow use ART No as Barcode</b></td>
			<td>
				<select name=form[artno_as_barcode]>
				<option value=0 {if !$form.artno_as_barcode}selected{/if}>No</option>				
				<option value=1 {if $form.artno_as_barcode eq 1}selected{/if}>Yes</option>
				</select>				
			</td>			
		</tr>
		<tr>
			<td><b>Use running no as receipt_no</b></td>
			<td>
				<select name=form[use_running_no_as_receipt_no]>
				<option value=0 {if !$form.use_running_no_as_receipt_no}selected{/if}>No</option>				
				<option value=1 {if $form.use_running_no_as_receipt_no eq 1}selected{/if}>Yes</option>
				</select>				
			</td>
		</tr>
        <tr>
			<td><b>Service Charges</b></td>
			<td>
				<input type="text" name="form[service_charges]" value="{$form.service_charges}"/>%
			</td>
		</tr>
        <tr>
			<td><b>Calculate Service Charges before receipt discount</b></td>
			<td>
				<input type="checkbox" name="form[service_charges_before_rdisc]" value="1" {if $form.service_charges_before_rdisc}checked{/if}/>
			</td>			
		</tr>
		<tr>
		  <td valign="top"><b>Preset Receipt Remark</b></td>
		  <td>
			<fieldset>
				<table width="100%" id="prr_tbl">
					<tr class="header">
						<td width="3%">&nbsp;</td>
						<td width="80%"><b>Title</b></td>
					</tr>
					<tbody id="receipt_remarks">
					{foreach from=$form.table_resit_remark key=rid item=title}
						<tr>
							<td><img src="/ui/closewin.png" align="absmiddle" onclick="prr_remove_row(this);"class="clickable" title="Delete this row"/></td>
							<td><input type="text" name="form[table_resit_remark][]" value="{$title}" class="prr_code"></td>
						</tr>
					{/foreach}
					</tbody>
				</table>
				<input type="button" value="Add Row" onclick="prr_add_row(this);"/>
			</fieldset>
		  </td>
		</tr>
			
        <tr>
          <td valign="top"><b>Goods Return Reason Settings</b></td>
          <td>
            <fieldset>
                <table width="100%" id="grrs_tbl">
                    <tr class="header">
                        <td width="3%">&nbsp;</td>
                        <td width="27%"><b>Code</b></td>
                        <td width="70%"><b>Description</b></td>
                    </tr>
                    <tbody id="reason_settings">
                    {foreach from=$form.grr_settings.code key=rid item=grrs_code}
                        <tr>
                            <td><img src="/ui/closewin.png" align="absmiddle" onClick="grrs_remove_row(this);" class="clickable" title="Delete this row" /></td>
                            <td><input type="text" name="form[grr_settings][code][]" value="{$grrs_code}" class="grrs_code"></td>
                            <td><input type="text" name="form[grr_settings][description][]" value="{$form.grr_settings.description.$rid}" size="60"></td>
                        </tr>
                    {/foreach}
                    </tbody>
                </table>
                <input type="button" value="Add Row" onclick="grrs_add_row(this);" />
            </fieldset>
          </td>
        </tr>
		<tr>
		  <td valign="top"><b>Hide POS Date Popup during POS Program Startup</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(197);" /></td>
		  <td>
				<input type="checkbox" name="form[hide_startup_pos_date_popup]" value="1" {if $form.hide_startup_pos_date_popup}checked{/if}/>
		  </td>
		</tr>
		<tr>
			<td><b>Show RSP and RSP Discount</b></td>
			<td>
				<select name="form[pos_show_rsp]">
				<option value="1" {if $form.pos_show_rsp}selected{/if}>Yes</option>
				<option value="0" {if !$form.pos_show_rsp}selected{/if}>No</option>
				</select>
			</td>			
		</tr>
		<tr>
			<td><b>Show other types of code at pop ups</b></td>
			<td>
				<table border="0">
					<tr>
						<td>
							<label>
								<input type="checkbox" name="form[pos_show_other_code][mcode]" value="mcode" {if $form.pos_show_other_code.mcode}checked{/if}/> MCode
							</label>
						</td>
						<td>
							<label>
								<input type="checkbox" name="form[pos_show_other_code][artno]" value="artno" {if $form.pos_show_other_code.artno}checked{/if}/> Art No.
							</label>
						</td>
					</tr>
					<tr>
						<td>
							<label>
								<input type="checkbox" name="form[pos_show_other_code][link_code]" value="link_code" {if $form.pos_show_other_code.link_code}checked{/if}/> Old Code / Link Code
							</label>
						</td>
						<td>
							<label>
								<input type="checkbox" name="form[pos_show_other_code][barcode]" value="barcode" {if $form.pos_show_other_code.barcode}checked{/if}/> Scan Code
							</label>
						</td>
					</tr>
				</table>
			</td>			
		</tr>

		<tr>
		  <td valign="top"><b>Auto run Sync in Background</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(210);" /></td>
		  <td>
				<input type="checkbox" name="form[auto_run_sync_bat]" value="1" {if $form.auto_run_sync_bat}checked{/if}/>
		  </td>
		</tr>

		<tr>
			<td colspan="2">
				<h3>Payment Settings</h3>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<b>Set payment type in counter</b>
				{if $config.counter_collection_extra_payment_type}
				<br/><br/>
				<table>
					<tr>
						<td><div style="height: 20px; width: 20px; display: block; background-color: #ffa042;"></div></td>
						<td valign="bottom"><b>Custom Payment</b>
							<br />
							<span class="small">(POS Counter will only able to show maximum of 6 custom payment.)</span>
						</td>
					</tr>
				</table>
				{/if}
			</td>
			{assign var=pt value=$form.payment_type}
			{assign var=n value=1}
			<td>
				<table>
					<tr>
						<td nowrap><b>Credit Card</b></td>						
						<td nowrap><input type="radio" name="form[payment_type][credit_card]" value="1" {if !isset($pt.credit_card) or $pt.credit_card}checked{/if}> Yes
						<input type="radio" name="form[payment_type][credit_card]" value="0" {if isset($pt.credit_card) and !$pt.credit_card}checked{/if}> No 
						</td>
						<td nowrap style="padding-left:20px;"><b>Voucher</b></td>
						<td nowrap><input type="radio" name="form[payment_type][voucher]" value="1" {if !isset($pt.voucher) or $pt.voucher}checked{/if}> Yes
						<input type="radio" name="form[payment_type][voucher]" value="0" {if isset($pt.voucher) and !$pt.voucher}checked{/if}> No 
						</td>
					</tr>
					<tr>
						<td nowrap><b>Coupon</b></td>
						<td nowrap><input type="radio" name="form[payment_type][coupon]" value="1" {if !isset($pt.coupon) or $pt.coupon}checked{/if}> Yes
						<input type="radio" name="form[payment_type][coupon]" value="0" {if isset($pt.coupon) and !$pt.coupon}checked{/if}> No 
						</td>					
						<td nowrap style="padding-left:20px;"><b>Cheque</b></td>
						<td nowrap><input type="radio" name="form[payment_type][check]" value="1" {if !isset($pt.check) or $pt.check}checked{/if}> Yes
						<input type="radio" name="form[payment_type][check]" value="0" {if isset($pt.check) and !$pt.check}checked{/if}> No 
						</td>
					</tr>					
					<tr>
						<td nowrap><b>Debit</b></td>
						<td nowrap><input type="radio" name="form[payment_type][debit]" value="1" {if isset($pt.debit) and $pt.debit}checked{/if}> Yes
						<input type="radio" name="form[payment_type][debit]" value="0" {if !isset($pt.debit) or !$pt.debit}checked{/if}> No 					
						</td>
						
						{if $config.counter_collection_extra_payment_type}
							{foreach from=$config.counter_collection_extra_payment_type item=ptype}
								{assign var=ptype_lower value=$ptype|lower}
								{assign var=n value=$n+1}
								<td nowrap bgcolor="#ffa042" {if ($n%2)==0}style="padding-left:20px;"{/if}><b>{$ptype}</b></td>
								<td nowrap ><input type="radio" name="form[payment_type][{$ptype_lower}]" value="1" {if $pt.$ptype_lower}checked{/if}> Yes
								<input type="radio" name="form[payment_type][{$ptype_lower}]" value="0" {if !$pt.$ptype_lower}checked{/if}> No 								
								</td>		
								{if ($n%2)==0}
								</tr>
								<tr>
								{/if}
							{/foreach}
						{else}
					</tr>
						{/if}
				</table>
			</td>
		</tr>
		{if $config.counter_collection_extra_payment_type}
		<tr>
			<td><b>Last Settlement</b><br/>The payment amount cannot be changed when paid as last settlement</td>
			<td>
			<table>
			<tr>
			{foreach from=$config.counter_collection_extra_payment_type item=ptype}		
			{assign var=pt value=$form.last_settlement}
				<td nowrap><input type="checkbox" name="form[last_settlement][{$ptype}]" value="1" {if $pt.$ptype}checked{/if}>{$ptype}</td>
			{/foreach}
			</tr>
			</table>
			</td>
		</tr>
		{/if}
		<tr>
			<td><b>Allow receive deposit from other branch</b></td>
			<td>
				<select name="form[allow_cross_branch]">
				<option value="1" {if $form.allow_cross_branch}selected{/if}>Yes</option>
				<option value="0" {if !$form.allow_cross_branch}selected{/if}>No</option>
				</select>
			</td>			
		</tr>
		
		<!--<tr>
			<td><b>Allow do return policy from other branch receipt</b></td>
			<td>
				<select name="form[allow_cross_branch_return_policy]">
				<option value="1" {if $form.allow_cross_branch_return_policy}selected{/if}>Yes</option>
				<option value="0" {if !$form.allow_cross_branch_return_policy}selected{/if}>No</option>
				</select>
			</td>			
		</tr>-->
		
		<tr>
			<td><b>Allow cash refund</b></td>
			<td>
				<select name="form[return_amount]">
					<option value="2" {if !isset($form.return_amount) or $form.return_amount eq 2}selected{/if}>Cash refund with privilege</option>
					<option value="1" {if $form.return_amount eq 1}selected{/if}>Cash refund without privilege</option>
					<option value="0" {if isset($form.return_amount) and !$form.return_amount}selected{/if}>No</option>
				</select>
			</td>
			
		</tr>
		<tr>
			<td><b>Hide Payment Information Screen button</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(191,310);" /></td>
				<td>
				<select name="form[hide_payment_information_screen_button]">
				<option value="1" {if $form.hide_payment_information_screen_button}selected{/if}>Yes</option>
				<option value="0" {if !$form.hide_payment_information_screen_button}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		
		{if $ewallet_list}
			<tr>
				<td><b>Allow eWallet</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(196);" /></td>
				<td>
					<table>
						{assign var=n value=0}
						{foreach from=$ewallet_list key=ewallet_type item=r}
							{if !$r.hide}
								{assign var=n value=$n+1}
								<td nowrap {if ($n%2)==0}style="padding-left:20px;"{/if}>
									<b>{$r.desc}</b>
									{if $r.is_debug}
										<span style="color:red;">(Testing Mode On)</span>
									{/if}
									{if !$r.enabled}
										<img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_ewallet_help();" />
									{/if}
								</td>
								<td nowrap>
									<input type="radio" name="form[ewallet_type][{$ewallet_type}]" value="1" {if $form.ewallet_type.$ewallet_type}checked{/if} {if !$r.enabled}disabled {/if} /> Yes
									<input type="radio" name="form[ewallet_type][{$ewallet_type}]" value="0" {if !$form.ewallet_type.$ewallet_type}checked{/if} {if !$r.enabled}disabled {/if} /> No 
								</td>
								{if ($n%2)==0}
									</tr>
									<tr>
								{/if}
							{/if}
						{/foreach}
					</table>
				</td>
			</tr>
		{/if}
		</table>

		<h3>Print Settings</h3>
		<table border=0>
		<tr>
		<td><b>Receipt Header Format</b></td>
		<td>
			<select name=form[print_header_format]>
			<option value=0 {if !$form.print_header_format}selected{/if}>4 Lines</option>
			<option value=1 {if $form.print_header_format eq 1}selected{/if}>2 Lines</option>
			</select>
			<br />
			<span class="small">This effect how the Invoice No, Counter, Cashier, and Invoice Date/Time to be displayed at the receipt header.</span>
		</td>
		</tr>	
		<tr>
		<td><b>Item Row Format&nbsp;[<a href="https://armshelp.freshdesk.com/support/solutions/articles/22000208931-receipt-format" target="_blank"><span style="color:#ff000;">?</span></a>]</b></td>
		<td>
			<select name=form[print_qty_one]>
			<option value=0 {if !$form.print_qty_one}selected{/if}>Standard</option>
			<option value=1 {if $form.print_qty_one eq 1}selected{/if}>Standard (Always Print 2 Line)</option>
			<option value=2 {if $form.print_qty_one eq 2}selected{/if}>Barcode & Item Description Separate Row</option>
			<option value=7 {if $form.print_qty_one eq 7}selected{/if}>Barcode & Item Description Separate Row (Type 2)</option>
			{*<option value=3 {if $form.print_qty_one eq 3}selected{/if}>Print receipt without barcode</option>*}
			<option value=4 {if $form.print_qty_one eq 4}selected{/if}>Letter/A4 Format</option>
			<option value=5 {if $form.print_qty_one eq 5}selected{/if}>Print full item description and exclude barcode</option>
			<option value=6 {if $form.print_qty_one eq 6}selected{/if}>Barcode & Item Description in same row</option>
			</select>
			<br />
			<span class="small">Depends on your POS Counter Version, some format may not be supported. <br />In case the selected format is not supported by your POS Counter, the Standard or Standard 2 Line will be used.</span>
		</td>
		</tr>
		<tr>
		<td><b>Print username who allow goods return</b></td>
		<td>
		<select name="form[print_return_by]">		
		<option value=0 {if !$form.print_return_by}selected{/if}>No</option>
		<option value=1 {if $form.print_return_by}selected{/if}>Yes</option>
		</select>
		</td>
		</tr>		
		<tr>
		<td><b>Print username who allow trade in</b></td>
		<td>
		<select name="form[print_trade_in_by]">
		<option value=0 {if !$form.print_trade_in_by}selected{/if}>No</option>
		<option value=1 {if $form.print_trade_in_by}selected{/if}>Yes</option>
		</select>
		</td>
		</tr>
		<tr>
		<td><b>Print counter version in receipt footer</b></td>
		<td>
		<select name="form[print_counter_version]">
		<option value=0 {if !$form.print_counter_version}selected{/if}>No</option>
		<option value=1 {if $form.print_counter_version}selected{/if}>Yes</option>
		</select>
		</td>
		</tr>
		<!--<tr>
		<td><b>Print advance in close counter</b></td>
		<td>
		<select name=form[print_advance]>
		<option value=1>Yes</option>
		<option value=0 {if !$form.print_advance}selected{/if}>No</option>
		</select>
		</td>
		</tr>-->
		<tr>
			<td><b>Print Cash Denomination, Cash Advance, <br/>Cash Currency Advance or Cash In Format</b></td>
			<td>
				<select name="form[print_cash_report_format]">
				<option value=0 {if !$form.print_cash_report_format}selected{/if}>Print 1 copy without signature</option>
				<option value=1 {if $form.print_cash_report_format eq 1}selected{/if}>Print 1 copy with signature</option>
				<option value=2 {if $form.print_cash_report_format eq 2}selected{/if}>Print 2 copy with signature</option>
				</select>
			</td>
		</tr>
		<tr>
		<td><b>Print variance in close counter</b></td>
		<td>
		<select name=form[print_variance]>
		<option value=0 {if !$form.print_variance}selected{/if}>No</option>
		<option value=1 {if $form.print_variance}selected{/if}>Yes</option>
		</select>
		</td>
		</tr>
		<tr>
		<td><b>Printing / Item Grouping</b></td>
		<td>
		<select name=form[items_sum_qty] onchange="change_print_hold_bill(this)">
		<option value=0 {if !$form.items_sum_qty}selected{/if}>Print on each scanning</option>
		<option value=1 {if $form.items_sum_qty}selected{/if}>Print at end of transaction</option>
		</select>
		</td>
		</tr>
		<tr id="print_hb" {if !$form.items_sum_qty}style="display:none"{/if}>
			<td><b>Print receipt items when hold bill</b></td>
			<td>
				<select name=form[print_hold_bill]>
					<option value=1 {if $form.print_hold_bill}selected{/if}>No</option>
					<option value=0 {if !$form.print_hold_bill}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Group Voucher Amount in Receipt</b></td>
			<td>
				<select name=form[group_voucher_amount]>
					<option value=0 {if !$form.group_voucher_amount}selected{/if}>No</option>
					<option value=1 {if $form.group_voucher_amount}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		{*<tr>
			<td><b>Print BOM Item Detail<br/> only for item which bom type is normal</b></td>
			<td>
				<select name=form[print_bom_item_detail]>
					<option value=0 {if !$form.print_bom_item_detail}selected{/if}>No</option>
					<option value=1 {if $form.print_bom_item_detail}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>*}
		<tr>
			<td><b>Print Category/Item Sales together with Cash Denomination</b></td>
			<td>
				<select name=form[print_cat_item_sales_cd]>
					<option value=0 {if !$form.print_cat_item_sales_cd}selected{/if}>No</option>
					<option value=1 {if $form.print_cat_item_sales_cd eq 1}selected{/if}>Print Item Sales together with cash denomination</option>
					<option value=2 {if $form.print_cat_item_sales_cd eq 2}selected{/if}>Print Category Sales together with cash denomination</option>
					<option value=3 {if $form.print_cat_item_sales_cd eq 3}selected{/if}>Print Item and Category Sales together with cash denomination</option>
				</select>
			</td>
		</tr>
        {if $config.enable_gst}
        <tr>
			<td><b>Print GST summary together with Cash Denomination</b></td>
			<td>
				<select name=form[print_gst_summary_cd]>
					<option value=0 {if !$form.print_gst_summary_cd}selected{/if}>No</option>
                    <option value=1 {if $form.print_gst_summary_cd eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
        {/if}
		<tr>
			<td><b>Print Deleted Items together with Cash Denomination</b></td>
			<td>
				<select name=form[print_deleted_items_cd]>
					<option value=0 {if !$form.print_deleted_items_cd}selected{/if}>No</option>
                    <option value=1 {if $form.print_deleted_items_cd eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Print Cash Advance together with Cash Denomination</b></td>
			<td>
				<select name=form[print_cash_advance_cd]>
					<option value=0 {if !$form.print_cash_advance_cd}selected{/if}>No</option>
                    <option value=1 {if $form.print_cash_advance_cd eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Print Other Payments &amp; Credit Cards Variance<br/> together with Cash Denomination</b></td>
			<td>
				<select name=form[print_other_payment_cd]>
					<option value=0 {if !$form.print_other_payment_cd}selected{/if}>No</option>
                    <option value=1 {if $form.print_other_payment_cd eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Print Cancelled Receipts together with Cash Denomination</b></td>
			<td>
				<select name=form[print_cancel_receipt_cd]>
					<option value=0 {if !$form.print_cancel_receipt_cd}selected{/if}>No</option>
                    <option value=1 {if $form.print_cancel_receipt_cd eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Print Receipts Discount together with Cash Denomination</b></td>
			<td>
				<select name=form[print_receipt_discount_cd]>
					<option value=0 {if !$form.print_receipt_discount_cd}selected{/if}>No</option>
                    <option value=1 {if $form.print_receipt_discount_cd eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr {if !$config.masterfile_enable_sa}style="display:none;"{/if}>
			<td><b>Skip Print Sales Agent together with Cash Denomination</b>&nbsp;<img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(192,311);" /></td>
			<td>
				<select name=form[skip_print_sa_report]>
					<option value=0 {if !$form.skip_print_sa_report}selected{/if}>No</option>
                    <option value=1 {if $form.skip_print_sa_report}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Denomination Summary Format [<a href="https://armshelp.freshdesk.com/support/solutions/articles/22000214825-denomination-summary-report" target="_blank"><span style="color:#ff000;">?</span></a>]</b>&nbsp;<img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(192,311);" /></td>
			<td>
				<select name=form[denomination_summary_format]>
					<option value=0 {if !$form.denomination_summary_format}selected{/if}>Detail</option>
                    <option value=1 {if $form.denomination_summary_format}selected{/if}>Simplified</option>
				</select>
			</td>
		</tr>
		{if $form.branch_is_under_gst}
			<tr>
				<td><b>Prompt To Suggest Print Full Tax Invoice</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(194,314);" /></td>
				<td>
					<select name=form[promt_to_ask_print_full_tax_inv] onchange="change_min_amt(this)">
						<option value = 0 {if !$form.promt_to_ask_print_full_tax_inv}selected{/if}>No</option>
						<option value = 1 {if $form.promt_to_ask_print_full_tax_inv}selected{/if}>Yes</option>
					</select>
				</td>
			</tr>
			<tr id="min_amt" {if !$form.promt_to_ask_print_full_tax_inv}style="display:none"{/if}>
				<td><b>Minimum Amount Prompt To Suggest Print Full Tax Invoice </b></td>
				<td>
					<input name=form[min_amt_prompt_to_print_full_tax_inv] value="{$form.min_amt_prompt_to_print_full_tax_inv|default:500}">
				</td>
			</tr>
		{/if}

		<tr>
			<td><b>Print RSP and RSP Discount</b></td>
			<td>
				<select name="form[print_rsp]">
				<option value="1" {if $form.print_rsp}selected{/if}>Yes</option>
				<option value="0" {if !$form.print_rsp}selected{/if}>No</option>
				</select>
			</td>			
		</tr>

		<tr>
			<td><b>Skip Print Receipt Printed Date and Time</b></td>
			<td>
				<select name="form[skip_receipt_printed_datetime]">
				<option value="1" {if $form.skip_receipt_printed_datetime}selected{/if}>Yes</option>
				<option value="0" {if !$form.skip_receipt_printed_datetime}selected{/if}>No</option>
				</select>
			</td>			
		</tr>

		<tr>
			<td><b>Print Receipt No as Receipt's Invoice No</b></td>
			<td>
				<select name="form[print_receipt_no_as_invoice_no]">
				<option value="1" {if $form.print_receipt_no_as_invoice_no}selected{/if}>Yes</option>
				<option value="0" {if !$form.print_receipt_no_as_invoice_no}selected{/if}>No</option>
				</select>
			</td>			
		</tr>

		<tr>
			<td><b>Print Invoice No with Prefix</b></td>
			<td><input name=form[receipt_no_prefix] value="{$form.receipt_no_prefix}"></td>		
		</tr>
		</table>
		
		<h3>Self Checkout Settings <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(199);" /></td></h3>
		<table border=0>
			<tr>
				<td>
					<b>No Activity Timeout Period</b>
				</td>
				<td>
					<input type="text" name="form[sco_timeout_period]" value="{$form.sco_timeout_period}" class="r" maxlength="3" size="3" onchange="mi(this);" /> <b>Second(s)</b>
				</td>
			</tr>
			<tr>
				<td>
					<b>Weight Tolerance</b>
				</td>
				<td>
					<input type="text" name="form[sco_weight_scale_var_perc]" value="{$form.sco_weight_scale_var_perc}" class="r" maxlength="6" size="6" onchange="mf(this);" /> <b>%</b>
				</td>
			</tr>
			<tr>
				<td rowspan="3">
					<b>Alarm Settings</b>
				</td>
				<td>
					<input type="text" name="form[sco_alarm_times]" value="{$form.sco_alarm_times}" class="r" maxlength="3" size="6" onchange="mi(this);" /> <b>Time(s) for Alarm Alerting</b>
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="form[sco_alarm_duration]" value="{$form.sco_alarm_duration}" class="r" maxlength="5" size="6" onchange="mf(this);" /> <b>Second(s) for Alarm Alerting Duration</b>
				</td>
			</tr>
			<tr>
				<td>
					<input type="text" name="form[sco_alarm_interval]" value="{$form.sco_alarm_interval}" class="r" maxlength="5" size="6" onchange="mf(this);" /> <b>Second(s) to trigger next Alarm Alerting</b>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<h3>Force Privilege Override Popup <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(210);" /></h3>
				</td>
			</tr>
			{foreach from=$force_override_privilege key=inp_name item=inp_label}
				<tr>
				  	<td valign="top"><b>{$inp_label}</b></td>
				  	<td>
						<input type="checkbox" name="form[{$inp_name}]" value="1" {if $form.$inp_name}checked{/if}/>
				  	</td>
				</tr>
			{/foreach}
		</table>
	</div>
	<div id=tb1>
		<h3>Membership Settings</h3>
		<table border=0>
		
		<tr>
			<td><b>Do not prompt for member / race during payment</b></td>
			<td>
				<select name=form[blok_prompt_for_member_race_payment]>
					<option value=0 {if !$form.blok_prompt_for_member_race_payment}selected{/if}>No</option>
					<option value=1 {if $form.blok_prompt_for_member_race_payment}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>		
		
		<tr>
		<td><b>Race</b></td>
		<td>
		<input name=form[race] value="{$form.race}">
		</td>
		</tr>

		<tr>
		<td><b>Reward Point</b></td>
		<td>
		{$config.arms_currency.symbol} <input size=3 name=form[unit_point] value="{$form.unit_point}"> = 1 point
		</td>
		</tr>
		{*<tr>
			<td><b>Deduct member point</b><br/>when has receipt discount in transaction.</td>
			<td>
				<select name="form[deduct_mem_point]">
					<option value="0" {if !$form.deduct_mem_point}selected{/if}>No Deduct</option>
					<option value="1" {if $form.deduct_mem_point eq 1}selected{/if}>Deduct by global point</option>
					<option value="2" {if $form.deduct_mem_point eq 2}selected{/if}>Deduct by ratio</option>
				</select>
			</td>
		</tr>*}
		{*<tr>
			<td><b>Cash Redemption Have Point</b></td>
			<td>
			<select name=form[cash_redemption_have_point]>
			<option value=1 {if $form.cash_redemption_have_point}selected{/if}>Yes</option>
			<option value=0 {if !$form.cash_redemption_have_point}selected{/if}>No</option>
			</select>
			</td>
		</tr>

		<tr>
			<td><b>Disable Multiple Promotion</b></td>
			<td>
			<select name=form[disable_multiple_promotion]>
			<option value=1 {if $form.disable_multiple_promotion}selected{/if}>Yes</option>
			<option value=0 {if !$form.disable_multiple_promotion}selected{/if}>No</option>
			</select>
			</td>
		</tr>*}

		<tr>
		<td><b>Show Last Points in Receipt</b></td>
		<td>
		<select name=form[show_last_points_in_receipt]>
		<option value=1 {if $form.show_last_points_in_receipt}selected {/if} >Yes</option>
		<option value=0 {if !$form.show_last_points_in_receipt}selected {/if} >No</option>
		</select>
		</td>
		</tr>
		<tr>
		<td><b>Show Member expired date in receipt</b></td>
		<td>
		<select name=form[show_member_expired_date_in_receipt]>
		<option value=1 {if $form.show_member_expired_date_in_receipt}selected {/if} >Yes</option>
		<option value=0 {if !$form.show_member_expired_date_in_receipt}selected {/if} >No</option>
		</select>		
		</td>
		</tr>
		{if $config.membership_use_card_prefix}
		    <tr>
		        <td><b>Membership Card Prefix</b></td>
		        <td><input type="text" name="form[membership_card_prefix]" value="{$form.membership_card_prefix}" maxlength="16" /></td>
		    </tr>
		{/if}
		<tr>
			<td><b>Allow invalid member no as new member no</b></td>
			<td>
				<select name="form[membership_allow_invalid_card_no]">
					<option value=1 {if $form.membership_allow_invalid_card_no}selected{/if}>Yes</option>
					<option value=0 {if !$form.membership_allow_invalid_card_no}selected{/if}>No</option>
				</select>
			</td>
		</tr>
		{*<tr>
		<td><b>Redeem Point with Amount</b></td>
		<td>
		<input size=3 name=form[redeem_point] value="{$form.redeem_point}"> point = {$config.arms_currency.symbol} 1
		</td>
		</tr>*}
		{if $config.membership_control_counter_adjust_point}
		<tr>
		<td><b>Allow Adjust Point</b></td>
		<td><select name=form[allow_adjust_member_point]>
			<option value="1" {if $form.allow_adjust_member_point}Selected{/if}>Yes</option>
			<option value="0" {if !$form.allow_adjust_member_point}Selected{/if}>No</option>
		</select></td>
		</tr>
		<tr>
		<td valign="top"><b>Set Adjust Member Point Reason List</b><br /> Press enter to next line for each new reason.<br />"Delivery" & "Birthday" is default value in reason list.</td>
		<td><textarea name=form[set_adjust_member_point_reason] cols="50" rows="5">{$form.set_adjust_member_point_reason}</textarea></td>
		</tr>
		{/if}
		<tr>
			<td><b>Allow scan member with nric or name</b></td>
			<td>
				<select name="form[scan_mem_nric_name]">
					<option value=0 {if !$form.scan_mem_nric_name}selected{/if}>No</option>
					<option value=1 {if $form.scan_mem_nric_name}selected{/if}>Yes</option>					
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Allow view member purchase history</b></td>
			<td>
				<select name="form[view_member_purchase_history]">
					<option value=0 {if !$form.view_member_purchase_history}selected{/if}>No</option>
					<option value=1 {if $form.view_member_purchase_history}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Skip print "Missed Point" on receipt</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(191,310);" /></td>
			<td>
				<select name="form[print_receipt_hide_missed_point]">
					<option value=0 {if !$form.print_receipt_hide_missed_point}selected{/if}>No</option>
                    <option value=1 {if $form.print_receipt_hide_missed_point eq 1}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Do not print membership information on receipt</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(201);" /></td>
			<td>
				<select name="form[print_receipt_hide_membership_info]">
					<option value=0 {if !$form.print_receipt_hide_membership_info}selected{/if}>No</option>
					<option value=1 {if $form.print_receipt_hide_membership_info}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		{if $config.membership_pmr}
		<tr>
			<td><b>Show Member Type and {$config.membership_pmr_name} information<br>after scanning member</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(209);" /></td>
			<td>
				<select name="form[show_membership_pmr]">
					<option value=0 {if !$form.show_membership_pmr}selected{/if}>No</option>
					<option value=1 {if $form.show_membership_pmr}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		{/if}
		</table>
	</div>
	<div id=tb2>
		<h3>Time Control</h3>
		<table border=0>
		<tr>
		<td><b>Sales Date Adjustment</b></td>
		<td>
		<b>Date from</b> <input id="inp_date_from" onChange="checkdatetime(this,'date');" name="form[sales_date_adjustment][date_from]" value="{$form.sales_date_adjustment.date_from|date_format:"%Y-%m-%d"}" size="10">
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_from" style="cursor: pointer;" title="Select Date" />
		<b>to</b> <input id="inp_date_to" onchange="checkdatetime(this,'date');" name="form[sales_date_adjustment][date_to]" value="{$form.sales_date_adjustment.date_to|date_format:"%Y-%m-%d"}" size=10>
		<img align="absmiddle" src="ui/calendar.gif" id="img_date_to" style="cursor: pointer;" title="Select Date" />
		(yyyy-mm-dd)
		</tr>
		</tr>
		<td></td>
		<td>
		<b>Time from</b> <input onchange="checkdatetime(this,'time');" name=form[sales_date_adjustment][time_from] value="{$form.sales_date_adjustment.time_from}" size=10>
		<b>to</b> <input onchange="checkdatetime(this,'time');" name=form[sales_date_adjustment][time_to] value="{$form.sales_date_adjustment.time_to}" size=10> (hh:mm)
		</td>
		</tr>
		<tr>
		<td><b>Cut-off time</b></td>
		<td>
		<select name=form[hour_start]>
		{section name=i loop=24}
		<option value="{$smarty.section.i.iteration-1}" {if $form.hour_start == ($smarty.section.i.iteration-1)}selected{/if}>{$smarty.section.i.iteration-1}</option>
		{/section}
		</select>
		<b>:</b>
		<select name=form[minute_start]>
		{section name=i loop=60}
		<option value="{$smarty.section.i.iteration-1}" {if $form.minute_start == ($smarty.section.i.iteration-1)}selected{/if}>{$smarty.section.i.iteration-1|string_format:"%02d"}</option>
		{/section}
		</select>
		</td>
		</tr>
		<tr>
			<td><b>Start Counter Date will fall in which date</b></td>
			<td>
				<select name=form[sales_record_cut_off] value="{$form.sales_record_cut_off}">
					<option value="0" {if !$form.sales_record_cut_off}selected{/if}>Use Previous Date</option>
					<option value="1" {if $form.sales_record_cut_off eq 1}selected{/if}>Use Current Date</option>
					<option value="2" {if $form.sales_record_cut_off eq 2}selected{/if}>Use User Select Date</option>
				</select>
			</td>
		</tr>
		</table>
	</div>
	<div id=tb3>
		<h3>Display Settings</h3>
		<table border=0>
		{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
		<tr>
		<td><b>Use Own Design</b></td>
		<td>
			<select name="form[use_own_design]" onchange="use_own_design_clicked(this)">
				<option value=0 {if !$form.use_own_design}selected{/if}>No</option>
				<option value=1 {if $form.use_own_design eq 1}selected{/if}>Yes</option>
			</select>
		</td>
		</tr>
		{/if}
		<tr id="tr_pos_counter_design" {if !$form.use_own_design && $form.branch_id > 1}style="display: none;"{/if}>
		<td><b>POS Counter Design</b><br>(Only available for resolution above 800x600)</td>
		<td nowrap="" style="vertical-align: middle;">
			{assign var=count_option value=0}
			{foreach from=$design_mode_option key=version_no item=dmo_array}
				{foreach from=$dmo_array key=vals item=img_src}
					{assign var=count_option value=$count_option+1}
					{if $vals == '0'}
						{assign var=display_label value='DEFAULT'}
					{else}
						{assign var=display_label value=$vals}
					{/if}
					<div class="item-box" style="vertical-align: top;display: inline-block;text-align: center;">
						<label>
							<input style="float:none;margin: 0px;width: 20px;position:relative;top:-100px;" type="radio" name="form[design_mode]" value="{$vals}" {if $form.design_mode eq $vals}checked{/if}>
							<img style="border:2px solid black;max-width:100%;max-height:200px;height:250px" src="{$img_src}">
						</label>
						<span class="caption" style="display: block;">
							<strong>{$display_label}</strong>
							{if $vals != '0'}
								<img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version({$version_no});" />
							{/if}
						</span>
					</div>
					{if $count_option == 2}
						<br>
						{assign var=count_option value=0}
					{/if}
				{/foreach}
			{/foreach}
		</td>
		</tr>
		<tr id="tr_pos_counter_color_theme" {if !$form.use_own_design && $form.branch_id > 1}style="display: none;"{/if}>
		<td><b>POS Counter Theme Color</b><br>(Only available for resolution above 800x600)</td>
		<td nowrap="" style="vertical-align: middle;">
			{assign var=count_option value=0}
			{foreach from=$design_theme_color_option key=vals item=img_src}
				{assign var=count_option value=$count_option+1}
				<div class="item-box" style="vertical-align: top;display: inline-block;text-align: center;">
					<label>
						<input style="float:none;margin: 0px;width: 20px;position:relative;top:-100px;" type="radio" name="form[color_theme_name]" value="{$vals}" {if $form.color_theme_name eq $vals}checked{/if}>
						<img style="border:2px solid black;max-width:100%;max-height:200px;height:250px" src="{$img_src}">
						<span class="caption" style="display: block;"><strong>{$vals|upper}</strong></span>
					</label>
				</div>
				{if $count_option == 2}
					<br>
					{assign var=count_option value=0}
				{/if}
			{/foreach}
		</td>
		</tr>
		<tr>
		<td rowspan=2><b>Customer Display Welcome Message</b></td>
		<td>#1 <input maxlength=20 name=form[cust_display_line1] value="{$form.cust_display_line1}" size=20></td>
		</tr>
		<tr>
		<td>#2 <input maxlength=20 name=form[cust_display_line2] value="{$form.cust_display_line2}" size=20></td>
		</tr>
		{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
			<tr>
				<td><b>Use Own Image</b></td>
				<td>
					<select name="form[pos_use_own_image]" onchange="use_own_image_clicked(this);">
						<option value="0" {if !$form.pos_use_own_image}selected{/if}>No</option>				
						<option value="1" {if $form.pos_use_own_image eq 1}selected{/if}>Yes</option>				
					</select>
				</td>
			</tr>
		{/if}
		</table>
		</form><br>

		{*if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}<p>Only HQ allow to change the images</p>{/if*}
		<div id="div_hq_image" {if $form.pos_use_own_image}style="display:none;"{/if}>
			<table cellspacing=20>
			<tr valign=top>
			<td>
			<b>Main Background <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('main_background');" /></b><br>
			<img width=200 id=img_pos_main_bg src="{$form.pos_main_bg|default:"/ui/pixel.gif"}">
			{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
			<form name=f_pos_main_bg method=post enctype=multipart/form-data target=if>
			<input name=a value=fupload type=hidden>
			<input name=img value="pos_main_bg" type=hidden>
			<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_main_bg', 'del_img');" align=absmiddle></div>
			<input name=pos_main_bg type=file size=1 onchange="upload_img('pos_main_bg', 'fupload');">
			</form>
			{/if}
			</td>
			<td>
			<b>Main Login <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('main_login');" /></b><br>
			<img width=200 id=img_pos_login_bg src="{$form.pos_login_bg|default:"/ui/pixel.gif"}">
			{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
			<form name=f_pos_login_bg method=post enctype=multipart/form-data target=if>
			<input name=a value=fupload type=hidden>
			<input name=img value="pos_login_bg" type=hidden>
			<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_login_bg', 'del_img');" align=absmiddle></div>
			<input name=pos_login_bg type=file size=1 onchange="upload_img('pos_login_bg', 'fupload');">
			</form>
			{/if}
			</td>
			<td>
			<b>Main Banner <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('main_banner');" /></b><br>
			<img width=200 id=img_pos_main_banner src="{$form.pos_main_banner|default:"/ui/pixel.gif"}">
			{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
			<form name=f_pos_main_banner method=post enctype=multipart/form-data target=if>
			<input name=a value=fupload type=hidden>
			<input name=img value="pos_main_banner" type=hidden>
			<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_main_banner', 'del_img');" align=absmiddle></div>
			<input name=pos_main_banner type=file size=1 onchange="upload_img('pos_main_banner', 'fupload');">
			</form>
			{/if}
			</td>
			<td>
			<b>Receipt Header <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('receipt_header');" /></b><br>
			<img width=200 id=img_pos_receipt_image src="{$form.pos_receipt_image|default:"/ui/pixel.gif"}">
			{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
			<form name=f_pos_receipt_image method=post enctype=multipart/form-data target=if>
			<input name=a value=fupload type=hidden>
			<input name=img value="pos_receipt_image" type=hidden>
			<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_receipt_image', 'del_img');" align=absmiddle></div>
			<input name=pos_receipt_image type=file size=1 onchange="upload_img('pos_receipt_image', 'fupload');">
            <div>Once uploaded, <a href="https://armshelp.freshdesk.com/support/solutions/articles/22000208930-change-receipt-feed-lines" target="_blank">click me</a> and follow step 7 to complete the settings.</div>
			</form>
			{/if}
			</td>
			<td>
			<b>Company Logo <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('company_logo');" /></b><br>
			Show in New Login Screen<br>
			<img width=200 id=img_pos_company_logo src="{$form.pos_company_logo|default:"/ui/pixel.gif"}">
			{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
			<form name=f_pos_company_logo method=post enctype=multipart/form-data target=if>
			<input name=a value=fupload type=hidden>
			<input name=img value="pos_company_logo" type=hidden>
			<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_company_logo', 'del_img');" align=absmiddle></div>
			<input name=pos_company_logo type=file size=1 onchange="upload_img('pos_company_logo', 'fupload');">
			</form>
			{/if}
			</td>
			</tr>
			<tr>
				<td>
					<b>Slide Show for Dual Screen <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('slide_show');" /></b><br>
					<div id="cron_ds_image">
						<div style="float:left; border: solid 1px #999; padding:5; display:none; margin-bottom: 5px;">
							<img width="200" id="img_pos_dual_screen_image___ID" src="__LOCATION"><br />
							<img src="/ui/del.png" align="absmiddle" onclick="del_ds_img(this.parentNode, '__BRANCH_pos_dual_screen_image', '__ID', '__DELETE')"> Delete
						</div>&nbsp;&nbsp;
					</div>
					<div id="div_ds_image">
					{foreach from=$form.dsi_list key=fname item=dsi_location}
						<div style="float:left; border: solid 1px #999; padding:5;">
							<img width="200" id="img_pos_dual_screen_image_{$fname}" src="{$dsi_location}"><br />
							{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
								<img src="/ui/del.png" align="absmiddle" onclick="del_ds_img(this.parentNode, 'pos_dual_screen_image', '{$fname}', 'del_img');"> Delete
							{/if}
						</div>
					{/foreach}
					</div>
					{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
						<div>
							<form name="f_pos_dual_screen_image" method="post" enctype="multipart/form-data" target="if">
							<input name="a" value="fupload" type="hidden">
							<input name="img" value="pos_dual_screen_image" type="hidden">
							<input name="ds_img" value="" type="hidden">
							<!--div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_dual_screen_image', 'del_img');" align=absmiddle></div-->
							<input name="pos_dual_screen_image" type="file" size="1" onchange="upload_img('pos_dual_screen_image', 'fupload');">
							</form>
						</div>
					{/if}
				</td>
				
				<!-- Top Right Banner setting -->
				<td>
					<b>Top Right Banner <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('top_right_banner');" /></b><br>
					<img width=200 id=img_pos_top_right_banner src="{$form.pos_top_right_banner|default:"/banner.png"}">
					{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
						<div>
							<form name="f_pos_top_right_banner" method="post" enctype="multipart/form-data" target="if">
							<input name="a" value="fupload" type="hidden">
							<input name="img" value="pos_top_right_banner" type="hidden">
							<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_top_right_banner', 'del_img');" align=absmiddle></div>
							<input name="pos_top_right_banner" type="file" size="1" onchange="upload_img('pos_top_right_banner', 'fupload');">
							</form>
						</div>
					{/if}
				</td>
				
			</tr>
			</table>
		</div>
		{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
			<div id="div_branch_image" {if !$form.pos_use_own_image}style="display:none;"{/if}>
				<table cellspacing=20>
				<tr valign=top>
				<td>
				<b>Main Background <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('main_background');" /></b></b><br>
				<img width=200 id=img_branch_pos_main_bg src="{$form.branch_pos_main_bg|default:"/ui/pixel.gif"}">
				{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				<form name=f_branch_pos_main_bg method=post enctype=multipart/form-data target=if>
				<input name=a value=branch_fupload type=hidden>
				<input name=img value="branch_pos_main_bg" type=hidden>
				<input name=branch_id value="{$form.branch_id}" type=hidden>
				<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_main_bg', 'branch_del_img');" align=absmiddle></div>
				<input name=branch_pos_main_bg type=file size=1 onchange="upload_img('branch_pos_main_bg', 'branch_fupload');">
				</form>
				{/if}
				</td>
				<td>
				<b>Main Login <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('main_login');" /></b><br>
				<img width=200 id=img_branch_pos_login_bg src="{$form.branch_pos_login_bg|default:"/ui/pixel.gif"}">
				{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				<form name=f_branch_pos_login_bg method=post enctype=multipart/form-data target=if>
				<input name=a value=branch_fupload type=hidden>
				<input name=img value="branch_pos_login_bg" type=hidden>
				<input name=branch_id value="{$form.branch_id}" type=hidden>
				<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_login_bg', 'branch_del_img');" align=absmiddle></div>
				<input name=branch_pos_login_bg type=file size=1 onchange="upload_img('branch_pos_login_bg', 'branch_fupload');">
				</form>
				{/if}
				</td>
				<td>
				<b>Main Banner <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('main_banner');" /></b><br>
				<img width=200 id=img_branch_pos_main_banner src="{$form.branch_pos_main_banner|default:"/ui/pixel.gif"}">
				{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				<form name=f_branch_pos_main_banner method=post enctype=multipart/form-data target=if>
				<input name=a value=branch_fupload type=hidden>
				<input name=img value="branch_pos_main_banner" type=hidden>
				<input name=branch_id value="{$form.branch_id}" type=hidden>
				<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_main_banner', 'branch_del_img');" align=absmiddle></div>
				<input name=branch_pos_main_banner type=file size=1 onchange="upload_img('branch_pos_main_banner', 'branch_fupload');">
				</form>
				{/if}
				</td>
				<td>
				<b>Receipt Header <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('receipt_header');" /></b><br>
				<img width=200 id=img_branch_pos_receipt_image src="{$form.branch_pos_receipt_image|default:"/ui/pixel.gif"}">
				{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				<form name=f_branch_pos_receipt_image method=post enctype=multipart/form-data target=if>
				<input name=a value=branch_fupload type=hidden>
				<input name=img value="branch_pos_receipt_image" type=hidden>
				<input name=branch_id value="{$form.branch_id}" type=hidden>
				<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_receipt_image', 'branch_del_img');" align=absmiddle></div>
				<input name=branch_pos_receipt_image type=file size=1 onchange="upload_img('branch_pos_receipt_image', 'branch_fupload');">
				</form>
				{/if}
				</td>
				<td>
				<b>Company Logo <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('company_logo');" /></b><br>Show in New Login Screen<br />
				<img width=200 id=img_branch_pos_company_logo src="{$form.branch_pos_company_logo|default:"/ui/pixel.gif"}">
				{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				<form name=f_branch_pos_company_logo method=post enctype=multipart/form-data target=if>
				<input name=a value=branch_fupload type=hidden>
				<input name=img value="branch_pos_company_logo" type=hidden>
				<input name=branch_id value="{$form.branch_id}" type=hidden>
				<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_company_logo', 'branch_del_img');" align=absmiddle></div>
				<input name=branch_pos_company_logo type=file size=1 onchange="upload_img('branch_pos_company_logo', 'branch_fupload');">
				</form>
				{/if}
				</td>
				</tr>
				<tr valign=top>
					<td>
						<b>Slide Show for Dual Screen <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('slide_show');" /></b><br>
						<div id="div_branch_ds_image">
						{foreach from=$form.branch_dsi_list key=fname item=dsi_location}
							<div style="float:left; border: solid 1px #999; padding:5; margin-bottom: 5px;">
								<img width="200" id="img_pos_dual_screen_image_{$fname}" src="{$dsi_location}"><br />
								<img src="/ui/del.png" align="absmiddle" onclick="del_ds_img(this.parentNode, 'branch_pos_dual_screen_image', '{$fname}', 'branch_del_img');"> Delete&nbsp;&nbsp;
							</div>
						{/foreach}
						</div>
						{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
							<div>
								<form name="f_branch_pos_dual_screen_image" method="post" enctype="multipart/form-data" target="if">
								<input name="a" value="branch_fupload" type="hidden">
								<input name="img" value="branch_pos_dual_screen_image" type="hidden">
								<input name="ds_img" value="" type="hidden">
								<input name="branch_id" value="{$form.branch_id}" type="hidden">
								<!--div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_dual_screen_image');" align=absmiddle></div-->
								<input name="pos_dual_screen_image" type="file" size="1" onchange="upload_img('branch_pos_dual_screen_image', 'branch_fupload');">
								</form>
							</div>
						{/if}
					</td>
					
					
					<!-- Top Right Banner setting -->
					<td>
						<b>Top Right Banner <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('top_right_banner');" /></b><br>
						<img width=200 id=img_branch_pos_top_right_banner src="{$form.branch_pos_top_right_banner|default:"/banner.png"}">
						{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
							<div>
								<form name="f_branch_pos_top_right_banner" method="post" enctype="multipart/form-data" target="if">
								<input name="a" value="branch_fupload" type="hidden">
								<input name="img" value="branch_pos_top_right_banner" type="hidden">
								<!--<input name="ds_img" value="" type="hidden">-->
								<input name="branch_id" value="{$form.branch_id}" type="hidden">
								<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_top_right_banner', 'branch_del_img');" align=absmiddle></div>
								<input name="branch_pos_top_right_banner" type="file" size="1" onchange="upload_img('branch_pos_top_right_banner', 'branch_fupload');">
								</form>
							</div>
						{/if}
					</td>
					
				</tr>
				</table>
			</div>
		{/if}
	<iframe name=if {if !strstr($smarty.server.HTTP_HOST,'maximus')}style="width:1px;height:1px;visibility:hidden"{/if}></iframe>
	</div>

	<div id=tb4>
		<h3>Security Settings</h3>
		<table border=0>
		<tr>
		<td><b>Current user can do cash advance</b></td>
		<td>
		<select name=form[pos_cashier_limited]>
		<option value=0 {if !$form.pos_cashier_limited}selected{/if}>Yes</option>
		<option value=1 {if $form.pos_cashier_limited}selected{/if}>No</option>
		</select>
		</td>
		</tr>
		<tr>
			<td><b>Open drawer on new cashier shift</b></td>
			<td>
				<select name=form[open_drawer_new_shift]>
					<option value=0 {if !$form.open_drawer_new_shift}selected{/if}>No</option>
					<option value=1 {if $form.open_drawer_new_shift}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>
		<tr>
			<td><b>Cash Advance Default Reason</b></td>
			<td>
				<select name=form[ca_default_reason]>
					{foreach from=$config.pos_cash_advance_reason_list item=reason_value}
					<option value="{$reason_value}" {if $form.ca_default_reason eq $reason_value}selected{/if}>{$reason_value}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		{*<tr>
			<td><b>Allow to create/checkout/view office document in counter</b></td>
			<td>
				<select name=form[create_view_backend]> 
					<option value=0 {if !$form.create_view_backend}selected{/if}>No</option>
					<option value=1 {if $form.create_view_backend}selected{/if}>Yes</option>
				</select>
			</td>
		</tr>*}
		</table>
	</div>
	<div id=tb7 {if !$config.pos_settings_pos_backend_tab} style="display:none;" {/if}>
		<h3>POS Backend</h3>
		<table border=0>
			{*<tr>
				<td><b>POS Backend Mode</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_notification_message('pos_backend_mode');" /></td></td>
				<td>
				<select name=form[is_pos_backend]>
				<option value=0 {if !$form.is_pos_backend}selected{/if}>No</option>
				<option value=1 {if $form.is_pos_backend}selected{/if}>Yes</option>
				</select>
				</td>
			</tr>*}
			{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				{if $form.off_dayend}
					<tr>
						<td></td>
						<td style="color: red;">All branches' Day End Time is switched off by HQ.</td>
					</tr>
				{/if}
			{else}
				<tr>
					<td><b>Switch off Day End Time for All Branches</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_notification_message('switch_off_day_end_time');" /></td>
					<td><label><input type="checkbox" name="form[off_dayend]" onchange="switch_off_day_end_time(this);" {if $form.off_dayend}checked{/if} value="1"> Swtich Off</label></td>
				</tr>
			{/if}
			
			<tr>
				<td><b>Apply Day End Time</b> <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_notification_message('apply_day_end_time');" /></td>
				<td>
				<select id="use_day_end_time" name="form[use_day_end_time]" onchange="use_day_end_time_clicked(this);" {if $form.off_dayend}disabled{/if}>
					<option value="0" {if !$form.use_day_end_time}selected{/if}>No</option>				
					<option value="1" {if $form.use_day_end_time eq 1}selected{/if}>Yes</option>
				</select>

				<div id="div_day_end_time" {if $form.use_day_end_time}style="display: inline-block;"{else}style="display: none;"{/if}>
					<select id="day_end_time_hour" name=form[day_end_time_hour] {if $form.off_dayend}disabled{/if}>
					{section name=i loop=24}
					<option value="{$smarty.section.i.iteration-1}" {if $form.day_end_time_hour == ($smarty.section.i.iteration-1)}selected{/if}>{$smarty.section.i.iteration-1}</option>
					{/section}
					</select>
					<b>:</b>
					<select id="day_end_time_minute" name=form[day_end_time_minute] {if $form.off_dayend}disabled{/if}>
					{section name=i loop=60}
					<option value="{$smarty.section.i.iteration-1}" {if $form.day_end_time_minute == ($smarty.section.i.iteration-1)}selected{/if}>{$smarty.section.i.iteration-1|string_format:"%02d"}</option>
					{/section}
					</select>
				</div>
				</td>
			</tr>
			{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
				<tr>
					<td><b>Use Own Image</b></td>
					<td>
						<select name="form[pos_pb_use_own_image]" onchange="pb_use_own_image_clicked(this);">
							<option value="0" {if !$form.pos_pb_use_own_image}selected{/if}>No</option>				
							<option value="1" {if $form.pos_pb_use_own_image eq 1}selected{/if}>Yes</option>				
						</select>
					</td>
				</tr>
			{/if}
		</table>

		<div id="div_pb_hq_image" {if $form.pos_pb_use_own_image}style="display:none;"{/if}>
			<table cellspacing=20>
				<tr valign=top>
					<td>
					<b>POS Backend Menu Background <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('pb_menu_body_image');" /></b><br>
					<img width=200 id=img_pb_menu_body_image src="{$form.pb_menu_body_image|default:"/ui/pixel.gif"}">
					{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
					<form name=f_pb_menu_body_image method=post enctype=multipart/form-data target=if>
					<input name=a value=fupload type=hidden>
					<input name=img value="pb_menu_body_image" type=hidden>
					<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pb_menu_body_image', 'del_img');" align=absmiddle></div>
					<input name=pb_menu_body_image type=file size=1 onchange="upload_img('pb_menu_body_image', 'fupload');">
					</form>
					{/if}
					</td>
					<td>
					<b>POS Backend Top Right Banner <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('pos_top_right_banner_backend');" /></b><br>
					<img width=200 id=img_pos_top_right_banner_backend src="{$form.pos_top_right_banner_backend|default:"/ui/pixel.gif"}">
					{if !$config.single_server_mode || ($BRANCH_CODE eq 'HQ' && $sessioninfo.branch_id eq $form.branch_id)}
					<form name=f_pos_top_right_banner_backend method=post enctype=multipart/form-data target=if>
					<input name=a value=fupload type=hidden>
					<input name=img value="pos_top_right_banner_backend" type=hidden>
					<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('pos_top_right_banner_backend', 'del_img');" align=absmiddle></div>
					<input name=pos_top_right_banner_backend type=file size=1 onchange="upload_img('pos_top_right_banner_backend', 'fupload');">
					</form>
					{/if}
					</td>
				</tr>
			</table>
		</div>

		{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
			<div id="div_pb_branch_image" {if !$form.pos_pb_use_own_image}style="display:none;"{/if}>
				<table cellspacing=20>
					<tr valign=top>
						<td>
						<b>POS Backend Menu Background <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('pb_menu_body_image');" /></b></b><br>
						<img width=200 id=img_branch_pb_menu_body_image src="{$form.branch_pb_menu_body_image|default:"/ui/pixel.gif"}">
						{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
						<form name=f_branch_pb_menu_body_image method=post enctype=multipart/form-data target=if>
						<input name=a value=branch_fupload type=hidden>
						<input name=img value="branch_pb_menu_body_image" type=hidden>
						<input name=branch_id value="{$form.branch_id}" type=hidden>
						<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pb_menu_body_image', 'branch_del_img');" align=absmiddle></div>
						<input name=branch_pb_menu_body_image type=file size=1 onchange="upload_img('branch_pb_menu_body_image', 'branch_fupload');">
						</form>
						{/if}
						</td>
						<td>
						<b>POS Backend Top Right Banner <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_help('pos_top_right_banner_backend');" /></b><br>
						<img width=200 id=img_branch_pos_top_right_banner_backend src="{$form.branch_pos_top_right_banner_backend|default:"/ui/pixel.gif"}">
						{if $config.single_server_mode && ($BRANCH_CODE ne 'HQ' || $form.branch_id > 1)}
						<form name=f_branch_pos_top_right_banner_backend method=post enctype=multipart/form-data target=if>
						<input name=a value=branch_fupload type=hidden>
						<input name=img value="branch_pos_top_right_banner_backend" type=hidden>
						<input name=branch_id value="{$form.branch_id}" type=hidden>
						<div style="float:left;width:20px"><img src="/ui/icons/delete.png" onclick="del_img('branch_pos_top_right_banner_backend', 'branch_del_img');" align=absmiddle></div>
						<input name=branch_pos_top_right_banner_backend type=file size=1 onchange="upload_img('branch_pos_top_right_banner_backend', 'branch_fupload');">
						</form>
						{/if}
						</td>
					</tr>
				</table>
			</div>
		{/if}
	</div>
	
	<div id="tb6" {if !$config.foreign_currency} display:none; {/if}>
		{*<h3>Currency</h3>
		
		<table>
		{if $config.currency_settings}
			<tr><td><b>Currency Symbol</b><br/>Only allow key in 3 character</td><td><input maxlength="3" name="form[currency_symbol]" value="{if $form.currency_symbol}{$form.currency_symbol}{else}{$config.arms_currency.symbol}{/if}"></td></tr>
			<tr><td><b>Rounding</b></td><td><input name="form[currency_rounding]" value="{if $form.currency_rounding neq ""}{$form.currency_rounding}{else}0.05{/if}"></td></tr>
			<tr style="display:none;"><td><b>Currency Format(Decimal Price)</b></td>
				<td><input type="hidden" name="form[currency_format]" value=0>
					<select name="form[currency_format]">
						<option value=0 selected>2 Decimal</option>
						<option value=1>3 Decimal</option>
						<option value=2>No Decimal</option>
					</select>
				</td>
			</tr>
		{/if}
		<tr><td>
		</table>
		
		<ul class=tight id="current_ul">
		<li><span style="display:inline-block;width:150px;"><b>Currency Name <br />(Max 3 character)</b></span><span style="display:inline-block;width:150px;"><b>Exchange Rate (to {$config.arms_currency.symbol}1)</b></span></a></li>
		{foreach from=$form.currency key=c item=r}
		<li><input name="form[currency_name][]"  onchange="uc(this)" style="width:150px" value="{$c}" maxlength="3" /> <input name="form[currency_rate][]" onchange="mfz(this,3)" style="width:150px" value="{$r|string_format:'%.3f'}"> <button onclick="return delrow(this);"><img src=/ui/icons/delete.png title="Delete"></button></li>
		{/foreach}
		<li style="display:none" id=rowtemplate><input name="form[currency_name][]"  onchange="uc(this)" style="width:150px" maxlength="3" /> <input name="form[currency_rate][]" onchange="mfz(this,3)" style="width:150px"> <button onclick="return delrow(this);"><img src=/ui/icons/delete.png title="Delete"></button></li>
		</ul>
		<button onclick="return addrow();">Add</button>
		*}
		
		<h3>Foreign Currency</h3>
		<table class="report_table">
			<tr class="header">
				<th>Code</th>
				<th>
					Global Exchange Rate <br />
					({$config.arms_currency.code} to Foreign Currency)
				</th>
				<th>
					Branch Override Exchange Rate
				</th>
				
			</tr>
			{foreach from=$foreign_currency_data.list key=code item=fc}
				<tr>
					<td><b>{$code}</b></td>
					<td nowrap class="base_rate_highlight">
						1 {$config.arms_currency.code} = {$fc.base_rate|number_format:$config.foreign_currency_decimal_points} {$code}
					</td>
					
					<td nowrap>
						<input type="checkbox" name="form[foreign_currency_override][{$code}]" value="1" {if $form.foreign_currency_override.$code}checked {/if} onChange="foreign_currency_override_changed('{$code}');" title="Override" class="inp_foreign_currency_override" currency_code="{$code}" />
						1 {$config.arms_currency.code} = 
						<input type="text" style="width:100px;text-align:right;" name="form[foreign_currency_rate][{$code}]" value="{$form.foreign_currency_rate.$code}" {if !$form.foreign_currency_override.$code}readonly {/if} onChange="foreign_currency_rate_changed('{$code}');" />
						{$code}
					</td>
				</tr>
			{/foreach}
		</table>
	</div>

	<div id=tb5>
	<table border=0>
		<tr>
			<td valign="top">
				<b>Receipt Header</b> 
				<br>(lines will be auto-centered during print-out)
			</td>
			<td>
				<textarea name="form[receipt_header]" rows=5 cols=40 wrap="HARD">{$form.receipt_header}</textarea>
			</td>
		</tr>
		<tr>
			<td valign="top">
				<b>Receipt Footer</b> 
				<br>(lines will be auto-centered during print-out)
			</td>
			<td>
				<textarea name="form[receipt_footer]" rows=5 cols=40 wrap="HARD">{$form.receipt_footer}</textarea>
			</td>
		</tr>
		<tr>
			<td><b>Customize receipt footer</b><br />receipt footer will show with date range.</td>
			<td valign="top">		
				<select name="form[preset_receipt_footer][option]" onchange="show_preset_receipt_footer(this)">
				<option value=1 {if $form.preset_receipt_footer.option}selected{/if}>Yes</option>
				<option value=0 {if !$form.preset_receipt_footer.option}selected{/if}>No</option>
				</select>
			</td>
		</tr>		
	</table>
	<table border=0 id="preset_receipt_footer" {if !$form.preset_receipt_footer.option}style="display:none;"{/if}>
	{section name=i loop=4}
	{assign var=j value=$smarty.section.i.index}
	{assign var=df value=$form.preset_receipt_footer.receipt_footer.$j.date_from}
	{assign var=dt value=$form.preset_receipt_footer.receipt_footer.$j.date_to}
	{assign var=tf value=$form.preset_receipt_footer.receipt_footer.$j.time_from}
	{assign var=tt value=$form.preset_receipt_footer.receipt_footer.$j.time_to}
	{assign var=mc value=$form.preset_receipt_footer.receipt_footer.$j.member_control}
	{assign var=rfd value=$form.preset_receipt_footer.receipt_footer.$j.receipt_footer_description}
	{assign var=ra value=$form.preset_receipt_footer.receipt_footer.$j.receipt_amount}
	<tr id="prf{$j}" {if $j>0 && $df eq "" && $dt eq "" && $tf eq "" && $tt eq "" && $rfd eq ""}style="display:none"{/if}>
		<td>
			<table border=0>
				{if $j>0}
					<tr><td colspan="3"><hr style="border:none; border-top:1px dotted #A5ACB2"></td></tr>
				{/if}
				<tr>
					<td><b>Date</b><br />(yyyy-mm-dd)</td>
					<td valign="top">
						<input name="form[preset_receipt_footer][receipt_footer][{$j}][date_from]" id="prf_date_from_{$j}" value="{$df}" />
						<img align="absmiddle" src="ui/calendar.gif" id="img_date_from_{$j}" style="cursor: pointer;" title="Select Date" />
						<b>To</b>
						<input name="form[preset_receipt_footer][receipt_footer][{$j}][date_to]" id="prf_date_to_{$j}" value="{$dt}" />
						<img align="absmiddle" src="ui/calendar.gif" id="img_date_to_{$j}" style="cursor: pointer;" title="Select Date" />
					</td>			
				</tr>
				<tr>
					<td><b>Time</b><br />(hh:mm)</td>
					<td valign="top">
					<input name="form[preset_receipt_footer][receipt_footer][{$j}][time_from]" id="prf_time_from_{$j}" value="{$tf}">
					<b>To</b>
					<input name="form[preset_receipt_footer][receipt_footer][{$j}][time_to]" id="prf_time_to_{$j}" value="{$tt}">
					</td>
				</tr>
				<tr>
					<td><b>Member</b></td>
					<td valign="top">
						<select name="form[preset_receipt_footer][receipt_footer][{$j}][member_control]">
							<option value="all" {if $mc eq 'all' || !$mc}selected{/if}>All</option>
							<option value="non_member" {if $mc eq 'non_member'}selected{/if}>Non Member</option>
							<option value="member" {if $mc eq 'member'}selected{/if}>Member</option>
							{foreach from=$config.membership_type key=member_type item=membertype_desc}
								{if is_numeric($member_type)}
									{assign var=mt value=$membertype_desc}
								{else}
									{assign var=mt value=$member_type}
								{/if}
								<option value="{$mt}" {if $mc eq $mt}selected{/if}>&nbsp;&nbsp;&nbsp;{$membertype_desc}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td><b>Receipt Amount</b>(>=)</td>
					<td valign="top">
					<input name="form[preset_receipt_footer][receipt_footer][{$j}][receipt_amount]" value="{if !is_numeric($ra) || !$ra}0{else}{$ra}{/if}">
					</td>
				</tr>
				<tr>
					<td valign="top">
						<b>Receipt Footer</b>
						<br/>(lines will be auto-centered during print-out)
					</td>
					<td><textarea cols="40" rows="5" name="form[preset_receipt_footer][receipt_footer][{$j}][receipt_footer_description]" wrap="HARD">{$rfd}</textarea></td>
					<td {if $j eq 3}style="display:none"{/if}><img align="absmiddle" src="ui/add.png" id="add_row{$j}" style="cursor:pointer;" title="Add Receipt Footer" onclick="show_rows({$j})"></td>
					<td {if $j eq 0}style="display:none"{/if}><img align="absmiddle" src="ui/cancel.png" id="add_row{$j}" style="cursor:pointer;" title="Add Receipt Footer" onclick="hide_rows({$j})"></td>
				</tr>						
			</table>
		</td>
	</tr>
	{/section}
</table>
	{if $config.counter_collection_extra_payment_type}
	<table>
		<tr>
		<td><b>Receipt Header or Receipt Footer for customize Payment </b><br />Receipt Header & Receipt Footer will be show on receipt when counter receive customize payment</td>
		<td valign="top">		
				<select name="form[receipt_header_footer_extra_payment][option]" onchange="show_receipt_header_customize_payment(this)">
				<option value=1 {if $form.receipt_header_footer_extra_payment.option}selected{/if}>Yes</option>
				<option value=0 {if !$form.receipt_header_footer_extra_payment.option}selected{/if}>No</option>
				</select>
		</td>
		</tr>				
	</table>
	<table id="cp_receipt_header_footer" {if !$form.receipt_header_footer_extra_payment.option}style="display:none;"{/if}>
		{foreach from=$config.counter_collection_extra_payment_type item=item}
		<tr>
			<td valign="top"><b>{$item}</b><br/>(lines will be auto-centered during print-out)</td>
			<td>
				<table>
				<tr>
				<td>Receipt Header</td>
				<td>
					{assign var=cp_receipt_header value=$form.receipt_header_footer_extra_payment}
					{assign var=cp_receipt_header_payment value=$cp_receipt_header.$item}
					<textarea cols="40" rows="1" name="form[receipt_header_footer_extra_payment][{$item}][receipt_header]" wrap="HARD">{if $cp_receipt_header_payment.receipt_header}{$cp_receipt_header_payment.receipt_header}{/if}</textarea>
				</td>	
				<td>&nbsp;</td>
				<td>Signature in receipt footer</td>
				<td>
					<select name="form[receipt_header_footer_extra_payment][{$item}][receipt_footer]">
					<option value=1 {if $cp_receipt_header_payment.receipt_footer}selected{/if}>Yes</option>
					<option value=0 {if !$cp_receipt_header_payment.receipt_footer}selected{/if}>No</option>
					</select>
				</td>
				</tr>
				</table>
			</td>
		</tr>
		{/foreach}
	</table>
	{/if}
	</div>

	<div id=tb8>
		<h3>POS Counter Audio <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_available_version(209);" /></h3>
		<table border=0>
			<tr>
				<th>Audio Display Point</th>
				<th>Use Audio <img src="/ui/icons/information.png" align="absmiddle" class="clickable" onClick="show_notification_message('use_audio');" /></th>
				<th>Audio File</th>
			</tr>
			{if $audio_list}
				{foreach from=$audio_list key=input_name item=input_label}
				{assign var=inp value="use_audio_`$input_name`"}
				<tr>
					<td>{$input_label}</td>
					<td><input type="checkbox" name="form[{$inp}]" {if $form.$inp} checked {/if} value="1"></td>
					<td>
						<form name=f_{$input_name} method=post enctype=multipart/form-data target=if>
							<input name=a value=upload_audio type=hidden>
							<input name=audio_file value="{$input_name}" type=hidden>
							<input name=branch_id value="{$form.branch_id}" type=hidden>
							<div id="div_audio_{$input_name}">
								{if $form.$input_name}
									<audio controls>
										<source src="{$form.$input_name}">
									</audio><br>
									{$form.$input_name}
									<div style="float:right;width:20px"><img src="/ui/icons/delete.png" onclick="del_audio('{$input_name}');" align=absmiddle></div>
								{else}
									<input type="file" accept=".mp3,.wav" name="{$input_name}" onchange="upload_audio('{$input_name}');">
								{/if}
							</div>
						</form>
					</td>
				</tr>
				{/foreach}
			{else}
				<tr><td colspan="3"><i>No Audio list available.</i></td></tr>
			{/if}
		</table>
	</div>
</div>
<p align=center><input class="btn btn-success" type=button value="Save" color:#fff;" onclick="do_save();"></p>

{include file=footer.tpl}
<script>
tab_sel(0);
init_calendar();
toggle_arms_code('coupon');toggle_arms_code('voucher');
</script>