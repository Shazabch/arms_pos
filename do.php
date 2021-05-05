<?php
/*
6/5/2008 3:10:06 PM yinsee
- join sku_items.packing_uom_id instead of sku.uom_id

6/9/2008 12:26:59 PM yinsee
- add support for GRN Barcode (GMARK)

6/16/2008 5:12:35 PM yinsee
- fixed barcode qty setting for multiple branch

6/19/2008 1:51:58 PM yinsee
- add printing un-checkout do (use do.print.tpl)

2008/6/23 15:51:48 yinsee
- do output change to 15 line

7/1/2008 2:33:36 PM yinsee
- send PAGE_SIZE to do.print.tpl

9/11/2008 5:59:03 PM yinsee
- add DO Price as price indicator 
- DO Price is taken from last do-item (unonfirmed are included, active DO only)

9/18/2008 6:23:18 PM yinsee
- change price-indicator=Cost to take from sku_items_cost

10/22/2008 4:54:40 PM yinsee
- search po number too

12/22/2008 2:37:55 PM jeff
- add $config[do_print_invoice_item_grouping] to group sku when print invoice

2/16/2009 4:50:00 PM Andy
- add function load_branches_group

3/10/2009 11:38:00 AM Andy
- add config[do_print_item_per_page] , config[do_alt_print_template] checking

3/31/2009 10:00:00 AM Andy
- add sku multiple add feature

3/31/2009 1:33:23 PM yinsee
- if select last-grn or last-do cost, use master-cost if not found 

4/17/2009 10:18 AM Andy
- save price type on add item
	- table modified
	    - alter table do_items add price_type text
	    - alter table tmp_do_items add price_type text
	    
4/21/2009 4:54:00 PM Andy
- DO Transfer Type
	- table modified
	    - alter table do add do_type char(20)

5/7/2009 4:51:50 PM yinsee
- if scan GRN barcode allow duplicate entry

5/8/2009 5:01:00 PM Andy
- Add Receive Qty for grn
- table modified
	- alter table do add total_qty int
	- alter table do add total_rcv int
	- alter table do_items add rcv_pcs int
	- alter table tmp_do_items add rcv_pcs int
	
5/27/2009 2:15:00 PM Andy
- Add get do by do_no
- seprate do_no and invoice no
	- table modified
	    - alter table do add inv_no char(10) after do_no
        - alter table do add unique(inv_no)
- Add print account copy and store copy for invoice

6/10/2009 11:06 AM Jeff
- Add Store > DO Checkout print DO without DO Privilege.

6/23/2009 5:33 PM Andy
- add checking on $config['do_checkout_invoice_alt_print_template'] to allow custom print

3/7/2009 3:55:13 PM yinsee
- sort po import by sequence

7/10/2009 3:43:46 PM Andy
- add get invoice no by ajax

7/15/2009 3:53:38 PM Andy
- get branch stock balance

8/6/2009 3:08:13 PM Andy
- add new do type 'credit sales'
- function do_ajax_add_item add collect master sku packing_uom_id as master_uom_id

8/12/2009 12:34:24 PM Andy
- DO Module changed to 3 type 'transfer'(default),'open','credit_sales'

8/24/2009 5:25:41 PM Andy
- Fix price indicator bug, use sku items master cost if no cost history
 
10/7/2009 10:14 AM
- add function do_print2, now able to print draft and proforma

11/3/2009 3:05:34 PM Andy
- add config['do_print_combine_same_item'] to combine same item on printing

11/5/2009 4:49:16 PM Andy
- add invoice discount. per sheet and per item
- fix bug after reset cannot auto approve
11/9/2009 1:12:13 PM edward
- add change owner function_chown() & checking load do list by user level and id.

11/10/2009 11:40:49 AM Andy
- edit combine function in printing (will not combine if different invoice discount or price or uom_fraction, even same item)
- include invoice discount in invoice printing

11/11/2009 12:30:30 PM edward
-  change all log type from Delivery ORDER to DELIVERY ORDER
-  add report prefix for all log

11/12/2009 5:38:47 PM Andy
- fix alternative print bug for do_checkout

11/13/2009 11:15:16 AM edward
- sprintf %.2f total_amount for log_br

11/16/2009 9:57:37 AM Andy
- fix confirm bug when split but price type
- fix few bug in consignment mode

11/18/2009 2:48:34 PM Andy
- Fix invoice no run out of sequence

11/19/2009 10:37:46 AM Andy
- check create from PO if option is 3, let user to select multiple branch
- change no inv no bug

11/23/2009 10:07:27 AM Andy
- add only can print invoice if already checkout

12/7/2009 6:12:31 PM Andy
- Fix price type split no profile % bug.
- Fix last update time bug

12/9/2009 1:31:52 PM Andy
- add function renumber_inv_no to fix inv no bug, need to run manually by http

12/11/2009 10:08:26 AM Andy
- Fix DO create from PO sometime cannot get deliver to branch id

12/14/2009 10:49:03 AM Andy
- add checking on config to allow duplicate DO Item
- Fix clear temp DO items problem, now DO should able to open multiple page.

12/21/2009 12:11:48 PM Andy
- Add additional feature to allow user using customized templates and row num in Cash Bill DO

12/24/2009 4:10:42 PM Andy
- Make DO searching to include search for invoice no

1/15/2010 9:43:50 AM Andy 
- Add config to manage different last page row num in DO and Invoice

1/18/2010 3:24:12 PM Andy
- Add paid feature for cash sales DO

2/22/2010 12:27:54 PM Andy
- Fix price indicator bugs

3/29/2010 4:16:05 PM Andy
- add config to check whether to add "profit xx%" for DO in consignment mode

4/20/2010 2:40:19 PM Andy
- Add search & print multiple DO

5/10/2010 3:07:36 PM Andy
- Add DO Markup.
- Fix DO split by price type bugs, if direct approve, only one of it will approve.

5/14/2010 11:17:42 AM Andy
- Add Sales Person Name in Credit/Cash Sales DO. (need config)

5/17/2010 1:50:22 PM Andy
- Add DO auto split by price type can automatically insert DO Discount base on branch trade discount. (need config)
- DO Markup can now be use as DO Discount as well.

5/26/2010 11:36:41 AM Andy
- Add print sales order data.(customer PO & batch code)

5/31/2010 2:54:17 PM Andy
- CN/DN/Invoice/DO (Markup/Discount) now can implement new consignment discount format.
- Fix javascript and smarty rounding bugs.
- Add checking for allowed future/passed DO Date limit.

5/31/2010 4:12:47 PM Alex
- add config['upper_date_limit'] and config['lower_date_limit']

6/17/2010 3:10:10 PM Justin
- Added a table called open_do_items
- Created a set of functions to indicate the different types between SKU Item and Open Item.
- Added a new button to allow user to execute to create a Open Item while editing/creating a particular DO.
- Solved the duplications error while creating Open Item.
- Added 1 new field (description) under tmp_open_item. 
- Added Department drop-down list selection and able to add/update into database based on the config if it is set.
- Added config for DO - $config['do_add_open_item']. This config will enable/disable the Open Item feature on DO.
- Added the checking function not to filter out Open Item from hidden under report printing if found config is set.

6/22/2010 12:25:06 PM Justin
- Added the function to call out Matrix table by color and size.
- Updated the mainteance check to check the version (13).

7/2/2010 4:11:39 PM Alex
- Add $config['document_page_size'] to set limit items per page and fix search bugs

7/7/2010 1:27:24 PM Andy
- DO create from PO, copy the PO department_id if got config.

7/15/2010 6:02:51 PM Andy
- Make DO cannot create other branch sales order.

9/7/2010 6:16:32 PM Andy
- DO multiple print change to no need check user permission.

11/3/2010 10:33:26 AM Alex
- fix bugs on pcs_allocation while inserting into tmp and loading

11/8/2010 1:49:44 PM Alex
- add branch searching

11/9/2010 11:43:19 AM Andy
- Add checking for canceled/deleted and prevent it to be edit.

11/10/2010 10:04:15 AM Alex
- split do while deliver multiple branch based on trade discount type

11/12/2010 6:13:50 PM Alex
- add branch searching for consignment modules only

11/25/2010 10:38:02 AM Andy
- Fix cash sales DO when confirm it will failed to save or even missing.

3/27/2011 3:31:46 PM Justin
- Added S/N feature.
- Applied all changes to existing functions that required for S/N feature.

4/8/2011 3:03:18 PM Justin
- Added cost field on do items.
- Added new function to find last cost from sku item cost history.

5/27/2011 5:26:11 PM Justin
- Added new field "use_address_deliver_to" and "address_deliver_to".
- Modified all the insertion and updates to capture both of these fields.
- Amended remark to include the Discount Code.

5/31/2011 1:03:30 PM Justin
- Rename the "grn_batch_items" into "sku_batch_items".

6/6/2011 5:20:54 PM Justin
- Added to validate with serial no only when config found.

6/20/2011 12:25:21 PM Justin
- Modified the Multi add to show notification for batch no.
- Added the missing update of foreign cost price for open item.

6/22/2011 9:44:31 AM Alex
- add get latest cost at save_multi_add()

6/22/2011 12:36:39 PM Alex
- add checking $config['do_item_allow_duplicate'] at multi_add()

6/23/2011 10:31:29 AM Justin
- Modified the remark to use "," instead of "\n" when inserting new info.
- Added DO Checkout print out to "use do_checkout_print_item_per_page" if found.

6/24/2011 4:03:06 PM Andy
- Make all branch default sort by sequence, code.

7/5/2011 10:38:12 PM Justin
- Fixed the missing of selling price while print report for multiple branches.
- Fixed the missing of deliver to address while print report for multiple branches.

7/6/2011 11:19:34 AM Andy
- Change split() to use explode()

8/5/2011 5:20:41 PM Andy
- Change DO Invoice Discount format.
- Enhance DO confirm split by price type will sort by price type.

8/10/2011 2:50:16 PM Andy
- Add DO create from sales order will also map the total discount and row discount.

8/19/2011 9:57:32 AM Justin
- Added the missing checking that using config for price type while saving DO by price type.

8/23/2011 10:44:12 AM Andy
- Move check maintenance version to do.include.php

9/19/2011 3:48:56 PM Andy
- Add new format to create DO from data collector.

9/20/2011 5:34:30 PM Andy
- Add retrieve debtor information when print credit sales DO.

10/3/2011 5:55:43 PM Justin
- Modified the round up for cost to base on config.
- Modified the Ctn and Pcs round up to base on config set.
- Applied when get item list, pick up sku item's doc_allow_decimal.

10/21/2011 4:28:32 PM Justin
- Fixed the bugs where system calculate the foreign amount wrongly.

10/25/2011 11:38:43 AM Justin
- Added to update rounding amount for total amount and invoice amount.

10/31/2011 7:15:43 PM Justin
- Added to pick up selling price from deliver's branch for consignment customer that using config "cm_use_deliver_branch_sp".

11/18/2011 3:24:55 PM Andy
- Add save/show DO price type. (only if all items in DO having same price type and is consignment mode).

11/29/2011 04:37:00 PM Andy
- Add to hide DO Date when found user got tick "Don't Show DO Date".

11/30/2011 10:17:09 AM Andy
- Fix split by price type bugs.

12/6/2011 11:45:32 AM Justin
- Enhanced to have add/update sa info to do and do items.

12/15/2011 10:48:43 AM Justin
- Fixed bug that system unable to pick up the actual currency code.
- Fixed bug that system never reset foreign cost price for item if it is not foreign type.

1/13/2012 6:01:43 PM Justin
- Added an ability to print Size and Color while found user has ticked it.

1/20/2012 - 11:28:43 AM Justin
- Removed the function to retrieve all size & color.
- Modified the size & color report only shows all related items only.

2/8/2012 4:56:43 PM Justin
- Reverted back to use existing UOM ID = 1 and UOM fraction = 1 since it is not usable.
- Added to pick up masterfile UOM Code.

2/14/2012 10:35:43 AM Justin
- Fixed the bugs where foreign cost price insert as zero whenever the DO is having exchange rate.

2/27/2012 4:22:20 PM Andy
- Add log when delete/cancel DO.

3/12/2012 2:38:29 PM Alex
- use get_grn_barcode_info to get the scan barcorde

4/2/2012 4:37:44 PM Alex
- fix bugs on saving sku items latest cost into do => save_tmp_items

4/6/2012 4:47:48 PM Alex
- add user notification list 

4/25/2012 11:17:40 AM Alex
- add master_uom_code data => save_multi_add() 

4/25/2012 6:41:33 PM Alex
- change get price type and selling price based on do date

5/2/2012 11:40:20 AM Andy
- Fix price indicator when get mprice should order by added desc.

5/16/2012 10:24:34 AM Justin
- Added new validation to check do date whether it is over than transaction end date while in consignment mode for both branches deliver from and to.

5/25/2012 10:18:23 AM Justin
- Fixed bug of comparing 0000-00-00 transaction end date with current date and show error.

7/23/2012 4:21 PM Andy
- Add checkbox for Credit Sales DO to able to ignore last credit sales cost and use cost from price indicator.

7/26/2012 11:32 AM Andy
- Add will check user department privilege when add search/add item (need config)

7/30/2012 10:16 AM Andy
- Fix price indicator missing once DO split by price type or after approved split by multiple branch.
- Fix DO create by data collector no cost.

8/7/2012 5:57 PM Justin
- Enhanced to accept new grn barcode scanning format.

8/14/2012 3:54:21 PM Fithri
- bypass privilege check when user coming from home page (pm.php)

11/23/2012 2:17:00 PM Fithri
- after monthly report has been printed, user cannot do further edit (reject, approval or submit) on that month - for consignment only

12/14/2012 2:17:00 PM Fithri
- remove config checking on scan barcode

1/17/2013 3:06 PM Justin
- Enhanced to capture inactive debtor info.

1/24/2013 4:38 PM Justin
- Bug fixed on system cannot capture the Exchange Rate during items that imported from data collector.
- Bug fixed on system capture the wrong price during data collector.

3/1/2013 5:20 PM Justin
- Enhanced to retrieve master UOM fraction for enable/disable UOM field base on config set.

3/4/2013 5:42 PM Justin
- Enhanced to have new printing option "receipt".
- Enhanced the search engine can search by DO receipt no.
- Enhanced the paid update function include receipt no auto generate feature while found config is set.

4/12/2013 4:33 PM Andy
- Enhance when user save DO, it will remove the DO Amount need update flag.

5/16/2013 2:16 PM Andy
- Enhance when user change debtor, it will ask user whether want to change price indicator to debtor default mprice type.
- Enhance default printing format to compatible with additional description.

5/22/2013 3:45 PM Justin
- Bug fixed on cost always capture as 0 whenever use Create from PO.
- Bug fixed on price indicate - PO Cost has calculated wrongly due to the decimal points.
- Bug fixed on do create from upload file and po is picking up zero stock balance.

5/31/2013 4:41 PM Andy
- Fix print invoice cannot group item.
- Fix an error message when got more than 1 page.

7/3/2013 11:32 AM Fithri
- pm notification standardization

7/9/2013 11:00 AM Fithri
- fix wrong DO id when sends PM

7/9/2013 2:42 PM Andy
- Fix multiple add item get wrong price at consignment customer.

7/29/2013 2:28 PM Andy
- Change module to use get_pm_recipient_list2() and send_pm2() in order to compatible with latest Approval Flow Settings.

8/1/2013 2:37 PM Fithri
- bugfix : add checking for items in temp table before save/confirm in case the document is open in more than one tab/window
- use $_SESSION to prevent clash of new document id if it is created at the same second

10/2/2013 5:02 PM Justin
- Bug fixed on import from PO that creates PHP errors and some other bugs.

10/22/2013 1:33 PM Justin
- Bug fixed on multiple delivery branches checking while create DO from PO.
- Bug fixed on checking for delivery branch that must also check for po option.

11/14/2013 4:16 PM Justin
- Enhanced to auto group SKU items if no have diffences (need config).

12/24/2013 4:16 PM Andy
- Enhance to accept mcode from data collector. (default import format)

12/27/2013 4:46 PM Fithri
- DO Import from Data Collector, user can choose field delimiter

1/29/2014 4:15 PM Justin
- Enhanced to add new import format for data collector.

3/6/2014 11:51 AM Justin
- Enhanced to load custom template for while printing S/N list.

3/31/2014 4:38 PM Justin
- Enhanced to check inactive SKU when scan barcode.

4/22/2014 5:06 PM Justin
- Enhanced to have filter on mprice type by user.

5/13/2014 3:37 PM Justin
- Enhanced to show Serial No to show in one page instead of printing at new page (need config).
- Enhanced to have warranty period to display on one column.

5/22/2014 11:43 AM Justin
- Enhanced import from PO function to auto select delivery branch base on the PO while config turn on.

5/26/2014 10:55 AM Justin
- Enhanced to have new price indicator "Masterfile - HQ Selling".

5/28/2014 11:43 AM Justin
- Enhanced import from data collector to detect and auto pickup 12 characters of sku item code when found it is more than 12 digits.

6/3/2014 12:00 PM Fithri
- able to set report logo by branch (use config)

6/9/2014 9:47 AM Justin
- Bug fixed on S/N unable to show out while printing DO transfer report.

6/23/2014 5:19 PM Justin
- Enhanced to have new feature that can add Serial No by range.

7/1/2014 10:31 AM Justin
- Enhanced to show parent artno on report and group of sales agent (enhancements for Tiles City).

7/7/2014 3:22 PM Fithri
- enhance the search box can search by 'Deliver To'

7/9/2014 11:29 AM Justin
- Enhanced to pickup warranty type and periods by SKU items.

10/1/2014 10:41 AM Justin
- Bug fixed on HQ Selling always rounded up to RM1 method.

10/24/2014 3:07 PM Justin
- Enhanced to have new feature that can add by parent & child.

2/9/2015 3:37 PM Andy
- GST Enhancements.

3/6/2015 10:26 AM Andy
- Fix if use price from "PO Cost", does not need to calculate price before tax.

3/18/2015 2:30 PM Justin
- Bug fixed on serial no always print the same for different DO items.

3/23/2015 12:25 PM Andy
- Fix DO items in printing grouping calculation.

3/27/2015 5:51 PM Andy
- Change to no need to get nett price if the branch is not gst registered.

3/31/2015 4:57 PM Andy
- Fix consignment mode should allow check GST.

2:41 PM 4/10/2015 Andy
- Fix artno_mcode not to get empty artno.

4/17/2015 11:34 AM Andy
- Fix error when create from sales order.

4/18/2015 12:09 PM Andy
- Enhanced to load GST summary when print DO.

4/29/2015 10:09 AM Andy
- Enhanced to have "Display Cost" features.

6/3/2015 2:32 PM Andy
- Fix special exemption GST.
- Enhanced to have function recalculate_do_amt.

6/4/2015 11:17 AM Andy
- Fix foreign calculation.

6/25/2015 12:16 PM Andy
- Fix a bug where DO and branch deliver to address is not working.

7/13/2015 5:59 PM Andy
- Remove DO print receipt feature.

7/13/2015 6:01 PM Justin
- Enhanced to check and show error message if user trying to add existing S/N from other branch.

7/27/2015 9:26 AM Joo Chia
- Show page with error message if print DO with total quantity zero.

7/30/2015 10:21 AM Joo Chia
- Load branch group to let user to select from tpl.

9/25/2015 5:34 AM DingRen
- hide RSP colummn on do print by follow config

10/16/2015 12:44 PM Andy
- Enhanced to store branch_group_id in $branch_group header array.

03/08/2016 10:20 Edwin
- Enhanced to check HQ whether have license to access module.

04/07/2016 14:00 Edwin
- Enhanced on show parent stock balance when config.show_parent_stock_balance is enabled.

07/01/2016 15:30 Edwin
- Enhanced on user able to view although they don't have official module.

08/02/2016 17:00 Edwin
- Enhanced on add printed status in "Approve" and "Checkout" tab.

3/29/2017 4:01 PM Justin
- Enhanced to allow user to key in extra qty instead of prompt error message while found item is existed.
- Enhanced to moves all the data validation to new function "data_validate".
- Added itemise qty checking which will not allow user to save the DO if it is zero.

4/10/2017 3:51 PM Justin
- Enhanced to have Picking List report.

4/13/2017 10:14 AM Justin
- Bug fixed on "Create DO from Sales Order" having mysql error.

4/19/2017 1:54 PM Khausalya 
- Enhanced changes from RM to use config setting. 

5/29/2017 3:44 PM Justin
- Enhanced to update DO items to have BOM information.

6/1/2017 4:28 PM Justin
- Bug fixed on create DO from sales order did not filter off "Exported to POS".

6/23/2017 9:38 AM Justin
- Enhanced to disable SKU not allowed to add when it is BOM Package SKU.

6/23/2017 2:08 PM Justin
- Enhanced to pick up cost from the same day as DO date.

7/19/2017 8:58 AM Qiu Ying
- Bug fixed on error show when import file by using Create from Data Collector input

7/27/2017 2:01 PM Justin
- Enhance to show export button when it is Transfer DO and under Save or Waiting for Approval.
- Bug fixed on DO items create from data collector which will not able to select the item GST information.

8/11/2017 10:04 AM Andy
- Fixed artno / mcode missing when add new item.

8/11/2017 5:04 PM Andy
- Fixed artno / mcode missing when using multiple add, add matrix and add parent child.

2017-09-07 14:07 PM Qiu Ying
- Enhanced to have default DO Size & Color Print Template

2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character

11/2/2017 10:13 AM Justin
- Enhanced to have Special Exemption Relief Claus Remark.
- Bug fixed on DO date will capture as null while create DO from Sales Order.

12/5/2017 9:23 AM Justin
- Enhanced to have "Use different Deliver To" which will allow user to key in delivery company name and address.

6/8/2018 2:12 PM Andy
- Fixed mprice wrong.

6/29/2018 2:52 PM Andy
- Fixed credit sales DO cost price error.

5/28/2017 4.00 pm Kuan Yeh
- Added proforma invoice printing 

6/1/2018 2:00 PM HockLee
- Created new function do_create_from_sales_order_by_batch() for batch processing.
- Addes print packing list in do_print2().

9/3/2018 6:00 PM HockLee
- Fixed bugs print_do_packing_list in do_print2().

8/24/2018 5:35 PM Andy
- Enhanced Credit Sales DO to have Debtor Price feature.

9/21/2018 3:11 PM Andy
- Fixed "Use Debtor Price" din't automatically tick when debtor is selected.
- Fixed Price Indicator show wrong.

9/19/2018 2:28 PM Andy
- Add Print Size & Color Invoice Format.

10/1/2018 4:15 PM Andy
- Fixed Print DO to use share function to load required data.

11/23/2018 2:50 PM Andy
- Add Create Multiple DO from CSV. (Transfer DO Only)

12/6/2018 4:47 PM Andy
- Fixed system cannot accept duplicate item upload from csv.

1/10/2019 2:25 PM Justin
- Enhanced to block zero or negative stock balance when adding DO items (need config).

1/31/2019 3:52 PM Andy
- Enhanced Print DO to able to select "Print Item Sequence".

2/12/2019 5:56 PM Andy
- move create_do_from_tmp() to become doManager->createDOFromTMP().
- move get_item_price() to become doManager->getItemPrice().

2/26/2019 1:52 PM Justin
- Bug fixed on old code couldn't show when adding new DO item.

3/13/2019 3:31 PM Andy
- Added "Print DO (Group by same Color)".

3/14/2019 4:02 PM Andy
- Enhanced to have "Invoice Amount Adjust".
- Fixed Print DO after grouping_item() all item_no become 1.

6/18/2019 4:42 PM William
- Pick up "vertical_logo" and "vertical_logo_no_company_name" from Branch for logo and hide company name setting.
- Pick up "setting_value" from system_settings for logo setting.

8/8/2019 11:59 AM Justin
- Enhanced to create approval cycle using appCore DO Manager.
- Enhanced to create DO by price type using appCore DO Manager.
- Enhanced to auto approve and send PM using appCore DO Manager.

8/23/2019 1:27 PM William
- Enhanced to added new column "custom_col" for new config "do_custom_column".

9/26/2019 4:31 PM Justin
- Bug fixed on DO confirmed and split by price type will then generate an empty DO and prompt inproper error message to user.

10/3/2019 3;39 PM William
- Enhanced do to add confirm_timestamp to do when first confirm do.

10/18/2019 5:01 PM Andy
- Fixed item quantity grouping bug.

11/19/2019 10:31 AM William
- Fixed bug total amount value wrong.

11/27/2019 2:17 PM William
- Enhanced to display sku item photo to do when config "do_show_photo" is active and tick yes.
- Fixed bug when do module change the url "view" to "open" and the item is not able to edit, system will show error message and redirect to home page.

12/6/2019 2:34 PM William
- Change error message "This DO belongs to other branch" to use language "You cannot edit other branch DO"

12/20/2019 1:25 PM William
- Fixed bug do cannnot edit when config "consignment modules" is active.

1/20/2020 4:20 PM William
- Enhanced to don't group same SKU items into one item when config "arms_marketplace_settings" is active.

2/14/2020 3:24 PM William
- Enhanced to Change log type "DO" to "DELIVERY ORDER".

3/13/2020 10:17 AM Justin
- Bug fixed on Serial No validation doesn't running at all.

3/30/2020 3:45 PM Justin
- Enhanced to do Serial No validation only when  user confirm the DO.

1/4/2020 11:24 PM William
- Bug fixed do item no show image when use matrix, multi_add to add do items.

4/15/2020 1:14 PM William
- Enhanced to block confirm and reset when got config "monthly_closing" and document date has closed.
- Enhanced to block create and save when got config "monthly_closing_block_document_action" and document date has closed.

7/15/2020 4:03 PM William
- Enhanced "Credit Sales DO" checkout list can mark as paid and key in (Payment Date, payment type and Remark).

8/5/2020 9:30 AM William
- Enhanced to show additional_payment_type when the config "do_paid_addition_payment_type" is active.

8/10/2020 3:09 PM William
- Enhanced to move Others to as default payment_type value.

10/30/2020 5:21 PM William
- Enhanced to pass variable "invoice_has_discount" to custom printing.

12/15/2020 11:58 AM William
- Enhanced to add new print option "SKU Code Column" to print do/invoice.

12/16/2020 5:06 PM Andy
- Enhanced to check config.do_disable_print_additional_desc to no print sku additional description in picking list, DO and Invoice.

8/3/2021 3:15 PM Ian
- Modified $sql to add selection for rsp & rsp price
- Added Function "export_approved_do" for DO item export to csv
*/

include("include/common.php");
//$con = new sql_db('gmark-hq.arms.com.my:4001','arms','4383659','armshq');
//$con = new sql_db('jwt-uni.dyndns.org','arms','4383659','armshq');

if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
if (!privilege('DO') && $_REQUEST['a'] != 'print'&&!preg_match('/^pm.php/',basename($_SERVER['HTTP_REFERER']))) js_redirect(sprintf($LANG['NO_PRIVILEGE'], 'DO', BRANCH_CODE), "/index.php");
if($_REQUEST['page']=='credit_sales'&&!$config['do_allow_credit_sales'])	js_redirect(sprintf($LANG['DO_CREDIT_SALES_NOT_ALLOWED'], 'DO', BRANCH_CODE), "/index.php");
if (BRANCH_CODE == 'HQ' && $config['arms_go_modules'] && !$config['arms_go_enable_official_modules'] && $_REQUEST['a'] != 'view') js_redirect($LANG['NEED_ARMS_GO_HQ_LICENSE'], "/index.php");
include("do.include.php");

$smarty->assign("PAGE_TITLE", "DO");
init_selection();

$con->sql_query("select * from branch where active=1 order by sequence, code");

while($r = $con->sql_fetchrow()){
	$branches[$r['id']] = $r;
}

$smarty->assign("branches", $branches);

if($_REQUEST['do_no'] != ''&&(!$_REQUEST['id']&&!$_REQUEST['branch_id'])){
	$con->sql_query("select id,branch_id from do where do_no=".ms($_REQUEST['do_no'])." and do_type='transfer'") or die(mysql_error());
	$id = $con->sql_fetchfield('id');
	$branch_id = $con->sql_fetchfield('branch_id');
}else{
    $branch_id = mi($_REQUEST['branch_id']);
	if ($branch_id ==0){
		$branch_id = $sessioninfo['branch_id'];
	}
	$id = intval($_REQUEST['id']);
}


if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
	
		case 'create_from_po':
			$new_id = do_create_from_po($id, $branch_id);
			if($new_id){
				$smarty->assign("from_po",1);
				do_open($new_id, $branch_id);
			}
			exit;
			
		case 'create_from_upload_file':
			$new_id = do_create_from_upload_file($id, $branch_id);
			do_open($new_id, $branch_id);
			exit;
		case 'chown':
			do_chown($id, $branch_id);
			exit;
		case 'open':
			// delete old tmp items
			$con->sql_query("delete from tmp_do_items where (do_id>1000000000 and do_id<".strtotime('-1 day').") and user_id = $sessioninfo[id]");
			$con->sql_query("delete from tmp_generate_do where added<".ms(date('Y-m-d', strtotime('-1 day'))));
			$con->sql_query("delete from tmp_generate_do_items  where added<".ms(date('Y-m-d', strtotime('-1 day'))));
		   //$con->sql_query("delete from tmp_do_items where (do_id<>$id) and user_id = $sessioninfo[id]");
				//change owner
			
		case 'refresh':
		    /*if($_REQUEST['do_type']=='transfer'){
				do_open_transfer($id,$_REQUEST['branch_id']);
				exit;
			}*/
			save_tmp_items();
			do_open($id, $branch_id);
			exit;

		case 'view':
			do_view($id, $branch_id);
			exit;
		
		case 'print':
			do_print($id, $branch_id);
			exit;

		case 'confirm':
		case 'save':
			do_save($id, $branch_id, ($_REQUEST['a']=='confirm'));
			exit;			

		case 'delete':
			do_delete($id, $branch_id);
			exit;		

		case 'ajax_load_do_list':
		    load_do_list();
		    exit;
		    
		case 'ajax_refresh_cost':
			do_ajax_refresh_cost($id, $branch_id);
			exit;
					
		case 'ajax_add_grn_barcode_item':
			do_ajax_add_grn_barcode_item($id, $branch_id);
			exit;
				
		case 'ajax_add_item':
			do_ajax_add_item($id, $branch_id);
			exit;
	
		case 'ajax_delete_item':
			$delete_id_list = $_REQUEST['delete_id_list'];
	
			if(!is_array($delete_id_list) || !$delete_id_list)	fail($LANG['DO_NO_ITEM_SELECTED']);
			
			foreach($delete_id_list as $do_item_id){
				$con->sql_query("delete from tmp_do_items where id=".mi($do_item_id)." and user_id=".mi($sessioninfo['id']));
			}
			
			print "$branch_id, $id, OK";		
			exit;
		
		case 'update_zero_amount':
			$con->sql_query("update do set total_amount = (select sum((ctn*fraction+pcs)*cost_price) from do_items 
			left join uom on uom_id = uom.id
			where do_items.branch_id=do.branch_id and do_items.do_id=do.id)
			where do.total_amount=0") or die(mysql_error());
			print $con->sql_affectedrows()." records updated.";
			exit;
		case 'multi_add':
		    multi_add();
		    exit;
		case 'save_multi_add':
		    save_multi_add();
		    exit;
		case 'change_do_branch':
		    change_do_branch();
		    exit;
		case 'change_user_list':
		    change_user_list(true, $_REQUEST);
		    exit;
        /*case 'confirm_transfer':
            do_save_transfer($id,$_REQUEST['old_branch_id'], true);
            exit;
		case 'save_transfer':
			do_save_transfer($id,$_REQUEST['old_branch_id'], false);
			exit;*/
        case 'ajax_refresh_cost2':
			do_ajax_refresh_cost2($id, $branch_id);
			exit;
		case 'ajax_get_invoice_no':
		    ajax_get_invoice_no($id, $branch_id);
		    exit;
		case 'do_reset':
			if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($_REQUEST['do_date'],$branch_id)) {
				$errm['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
				do_view($id, $branch_id);
				exit;
			}
		    $fail = do_reset($id,$branch_id);
			if ($fail && $_REQUEST['page_type'] == "checkout"){
				$id=mi($_REQUEST['id']);
				$branch_id=mi($_REQUEST['branch_id']);
				$form=load_do_header($id, $branch_id);
				$smarty->assign("do_items", load_do_items($id, $branch_id,$form));
	    		$smarty->assign("form", $form);
	    		$smarty->assign("readonly", 1);
	    		$smarty->assign('do_type',$form['do_type']);
				$smarty->display("do_checkout.open.tpl");
			}
			else	do_view($id,$branch_id);
		exit;
		    exit;
		/*case 'change_credit_sales_price':
		    change_credit_sales_price($branch_id);
		    exit;*/
		case 'ajax_get_credit_sales_item_cost_history':
		    ajax_get_credit_sales_item_cost_history();
		    exit;
		case 'ajax_check_po_no':
		    ajax_check_po_no();
		    exit;
		case 'renumber_inv_no':
		    renumber_inv_no();
		    exit;
		case 'ajax_show_paid_status':
			ajax_show_paid_status();
			exit;
		case 'ajax_update_paid':
		    ajax_update_paid();
		    exit;
		case 'do_create_from_sales_order':
		    $params = do_create_from_sales_order();
		    $smarty->assign('from_sales_order', 1);
		    do_open($params['id'], $params['branch_id']);
		    exit;
		case 'do_create_from_sales_order_by_batch':
		    do_create_from_sales_order_by_batch();
		    exit;
		case 'ajax_search_batch_code':
		    ajax_search_batch_code();
		    exit;
		case 'ajax_search_do_for_multiple_print':
		    ajax_search_do_for_multiple_print();
		    exit;
		case 'multiple_print':
		    multiple_print();
		    exit;
		case 'ajax_get_sales_person_name':
		    ajax_get_sales_person_name();
		    exit;
		case 'ajax_add_size_color':
		    do_ajax_add_size_color($id, $branch_id);
		    exit;
		case 'find_member_info':
			if($_REQUEST['type'] && $_REQUEST['value']){
				$con->sql_query("select * from membership where nric = ".ms($_REQUEST['value']));

				if($con->sql_numrows() > 0){
					$membership = $con->sql_fetchrow();
					if($membership['address']) $address[] = $membership['address'];
					if($membership['postcode']) $address[] = $membership['postcode'];
					if($membership['city']) $address[] = $membership['city'];
					if($membership['state']) $address[] = $membership['state'];
					print $membership['name']."|".join(", ", $address)."|".$membership['phone_3']."|".$membership['email'];
				}
			}
			exit;
		case 'all_update_sheet_price_type':
			all_update_sheet_price_type();
			exit;
		case 'update_debtor_credit_sales_price':
			update_debtor_credit_sales_price();
			exit;
		case 'check_tmp_item_exists':
			check_tmp_item_exists();
			exit;
		case 'ajax_show_sn_by_range':
			ajax_show_sn_by_range();
			exit;
		case 'ajax_load_parent_child':
			ajax_load_parent_child();
			exit;
		case 'ajax_parent_child_add':
			ajax_parent_child_add();
			exit;
		case 'auto_update_do_all_amt':
			auto_update_do_all_amt($_REQUEST['branch_id'], $_REQUEST['id']);
			exit;
		case 'recalculate_do_amt':
			recalculate_do_amt();
			exit;
		case 'do_export':
			do_export();
			exit;
		case 'open_upload_csv':
			open_upload_csv();
			exit;
		case 'download_sample_do_csv':
			download_sample_do_csv();
			exit;
		case 'ajax_generate_multi_do':
			ajax_generate_multi_do();
			exit;
		case 'export_approved_do':
			export_approved_do($id, $branch_id);
			exit;
		default:
		    print "<h1>Unhandled Request</h1>";
		    print_r($_REQUEST);
		    exit;
	    
	}
}
$smarty->display("do.home.tpl");
exit;

function do_save($do_id, $branch_id, $is_confirm){
	global $con, $LANG, $smarty, $sessioninfo, $config, $appCore;
	//... validate, save, check confirm, send pm, set approval.....
	if(!is_new_id($do_id)){
        check_must_can_edit($branch_id, $do_id);   // check available
	}

	// for consignment customer that using exchange rate only...
	if(is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency'])){
		//print $_REQUEST['do_branch_id'];
		$con->sql_query("select region from branch where id = ".mi($_REQUEST['do_branch_id']));
		$to_branch_info = $con->sql_fetchrow();
		$con->sql_freeresult();

		$currency_code = $config['masterfile_branch_region'][$to_branch_info['region']]['currency'];
		
		if(!$currency_code || $currency_code == $config["arms_currency"]["symbol"]){
			$_REQUEST['exchange_rate'] = 1;
			$_REQUEST['sub_total_foreign_inv_amt'] = 0;
			$_REQUEST['total_foreign_inv_amt'] = 0;
			$_REQUEST['total_foreign_amount'] = 0;
		}
	}

	$form=$_REQUEST;

	save_tmp_items();
	
	// used for serial number checking
	$form['is_confirm'] = $is_confirm;
	$errm = data_validate($form);
	unset($form['is_confirm']);

	//check is_month_closed
	if($config['monthly_closing']){
		$is_month_closed = $appCore->is_month_closed($form['do_date']);
		if($is_month_closed && ($is_confirm || $config['monthly_closing_block_document_action'])){
			$errm['top'][] = $LANG['MONTH_DOCUMENT_IS_CLOSED'];
		}
	}

	//print_r($form);exit;
	if(!$errm && $is_confirm){
		// check approval via doManager.php
		$prms = array();
		$prms['dept_id'] = $form['dept_id'];
		$prms['branch_id'] = $branch_id;
		$prms['doc_amt'] = $form['total_amount'];
		if(isset($form['total_inv_amt'])) $prms['doc_amt'] = $form['total_inv_amt'];
		$prms['approval_history_id'] = $form['approval_history_id'];
		$ret = $appCore->doManager->doApprovalHandler($prms, $form);
		$errm = $ret['errm'];
		$form['approval_history_id'] = $ret['approval_history_id'];
		$last_approval = $ret['last_approval'];
		$direct_approve_due_to_less_then_min_doc_amt = $ret['direct_approve_due_to_less_then_min_doc_amt'];
		unset($ret);
	}
		

	if($errm){
		change_user_list(false,$form);
		$smarty->assign("errm", $errm);
		do_open($do_id,$branch_id);
		exit;
	}
	else{
	    if($config['do_auto_split_by_price_type']&&$is_confirm){
			$prms = array();
			$prms['do_id'] = $do_id;
			$prms['branch_id'] = $branch_id;
			$prms['do_info'] = $form;
			$prms['use_tmp_tbl'] = true;
			$rs = $appCore->doManager->priceTypeHandler($prms);
			
			// found the do items are being splitted by price type, redirect user back to do page
			if(!$rs['split_failed'] && $rs['do_id_list']){
				header("Location: /do.php?page=$form[do_type]&t=".($last_approval?'approve':'confirm')."&save_id=".join(",",$rs['do_id_list']));
				exit;
			}
			
			// found having issue while split price type
			if($rs['errm']){
				$smarty->assign("errm", $rs['errm']);
				do_open($do_id,$branch_id);
				exit;
			}
			
			// get remark, do markup and markup type
			if(isset($rs['remark'])) $form['remark'] = $rs['remark'];
			if(isset($rs['do_markup'])) $form['do_markup'] = $rs['do_markup'];
			if(isset($rs['markup_type'])) $form['markup_type'] = $rs['markup_type'];
			unset($prms, $rs);
		}
		
		if ($is_confirm) $form['status'] = 1;
	    if ($last_approval) $form['approved'] = 1;
	
		$form['id']=$do_id;
		$form['branch_id']=$branch_id;
		$form['last_update'] = 'CURRENT_TIMESTAMP';
		$form['added'] = 'CURRENT_TIMESTAMP';
		$form['user_id'] = $sessioninfo['id'];
				
		$do_branch_id_list = array();
		
		if($form['create_type']==2){
			$form['open_info'] = serialize($form['open_info']);
			$form['deliver_branch']='';
		}
		else{
			
			if($form['do_type']=='transfer'){
				$do_branch_id_list = array();
				
				if(is_array($form['deliver_branch']) && $form['deliver_branch']){
					foreach($form['deliver_branch'] as $tmp_do_bid){
						$do_branch_id_list[] = $tmp_do_bid;
					}
				}else{
					if($form['do_branch_id']>0)	$do_branch_id_list[] = $form['do_branch_id'];
				}
				
				//serailize user list
				$form['allowed_user'] = serialize($form['allowed_user']);
			}
		
		    if(count($form['deliver_branch'])==1){
				$form['do_branch_id']=$form['deliver_branch']['0'];
				$form['deliver_branch']='';
				save_tmp_items($form['do_branch_id']);
			}
			else{
				$form['deliver_branch'] = serialize($form['deliver_branch']);
			}
			$form['open_info']='';
		}	
		if(unserialize($form['deliver_branch']))    $is_deliver_multiple_branch = true;
		if(!$form['exchange_rate']) $form['exchange_rate'] = 1;

		// check currency mode
		if($form['do_type'] == 'transfer' && $config['consignment_modules'] && $config['masterfile_branch_region'] && $config['consignment_multiple_currency'] && $form['exchange_rate']>1){
			$is_currency_mode = true;
			
			if($form['price_indicate']==1)	$use_cost_indicate = true;
		}

		if (is_new_id($do_id)){
			if(!$form['create_type'])$form['create_type']=1;
			
			//$con->sql_query("insert into do " . mysql_insert_by_field($form, array('branch_id', 'user_id', 'dept_id', 'status', 'approved', 'do_date', 'added', 'deliver_branch','total_pcs', 'total_ctn', 'total_amount', 'remark','approval_history_id', 'do_branch_id', 'po_no', 'create_type', 'open_info','price_indicate','do_type','debtor_id','discount','total_inv_amt')));
			
			$temp = array('branch_id'=>$form['branch_id'], 'user_id'=>$form['user_id'], 'dept_id'=>$form['dept_id'], 'status'=>$form['status'], 'approved'=>$form['approved'], 'do_date'=>$form['do_date'],
			'added'=>$form['added'], 'deliver_branch'=>$form['deliver_branch'],'total_pcs'=>$form['total_pcs'], 'total_ctn'=>$form['total_ctn'], 'total_amount'=>$form['total_amount'], 
			'total_round_amt'=>$form['total_round_amt'], 'total_foreign_amount'=>$form['total_foreign_amount'], 'remark'=>$form['remark'],'approval_history_id'=>$form['approval_history_id'],
			'do_branch_id'=>$form['do_branch_id'], 'po_no'=>$form['po_no'], 'create_type'=>$form['create_type'], 'open_info'=>$form['open_info'],'price_indicate'=>$form['price_indicate'],
			'do_type'=>$form['do_type'],'debtor_id'=>$form['debtor_id'],'discount'=>$form['discount'],'total_inv_amt'=>$form['total_inv_amt'], 'total_round_inv_amt'=>$form['total_round_inv_amt'], 
			'total_foreign_inv_amt'=>$form['total_foreign_inv_amt'], "do_markup"=>$form['do_markup'],"sales_person_name"=>$form['sales_person_name'],"markup_type"=>$form['markup_type'], 
			"use_address_deliver_to"=>$form['use_address_deliver_to'], "address_deliver_to"=>$form['address_deliver_to'], "exchange_rate"=>$form['exchange_rate'],"allowed_user"=>$form['allowed_user'],"delivery_debtor_id"=>$form['delivery_debtor_id'],"batch_code"=>$form['batch_code']);
			
			$temp['no_use_credit_sales_cost'] = $form['no_use_credit_sales_cost'];
			
			if($form['create_type']==4){    // create from sales order
				$temp['ref_tbl'] = $form['ref_tbl'];
				$temp['ref_no'] = $form['ref_no'];
			}
			
			if($config['masterfile_enable_sa'] && $form['do_sa']) $temp['mst_sa'] = serialize($form['do_sa']);
			else $temp['mst_sa'] = "";
			
			// gst
			//if($config['enable_gst']){
				$temp["is_under_gst"] = $form["is_under_gst"];
			//}
			$temp['sub_total_gross_amt'] = $form['sub_total_gross_amt'];
			$temp['sub_total_gst_amt'] = $form['sub_total_gst_amt'];
			$temp['sub_total_amt'] = $form['sub_total_amt'];
			
			$temp['do_total_gross_amt'] = $form['do_total_gross_amt'];
			$temp['do_total_gst_amt'] = $form['do_total_gst_amt'];
			$temp['is_special_exemption'] = $form['is_special_exemption'];
			if($temp['is_special_exemption']) $temp['special_exemption_rcr'] = $form['special_exemption_rcr'];
			else $temp['special_exemption_rcr'] = "";
			
			$temp['inv_total_gross_amt'] = $form['inv_total_gross_amt'];
			$temp['inv_total_gst_amt'] = $form['inv_total_gst_amt'];
			$temp['inv_sheet_gst_discount'] = $form['inv_sheet_gst_discount'];
			
			$temp['inv_sub_total_gross_amt'] = $form['inv_sub_total_gross_amt'];
			$temp['inv_sub_total_gst_amt'] = $form['inv_sub_total_gst_amt'];
			
			$temp['inv_sheet_gross_discount_amt'] = $form['inv_sheet_gross_discount_amt'];
			$temp['inv_sheet_discount_amt'] = $form['inv_sheet_discount_amt'];
			
			$temp['sub_total_inv_amt'] = $form['sub_total_inv_amt'];
			$temp['sub_total_foreign_inv_amt'] = $form['sub_total_foreign_inv_amt'];
			
			$temp['inv_sheet_foreign_discount_amt'] = $form['inv_sheet_foreign_discount_amt'];
			if($form['do_type'] == 'credit_sales')	$temp['use_debtor_price'] = $form['use_debtor_price'];
			
			if(isset($form['inv_sheet_adj_amt']))	$temp['inv_sheet_adj_amt'] = mf($form['inv_sheet_adj_amt']);
			if($is_confirm){
				$temp['confirm_timestamp'] = 'CURRENT_TIMESTAMP';
			}
			$con->sql_query("insert into do ".mysql_insert_by_field($temp));

			$form['id'] = $con->sql_nextid();
		}
		else{
			//$con->sql_query("update do set " . mysql_update_by_field($form, array('dept_id', 'status', 'approved', 'do_date', 'deliver_branch','total_ctn', 'total_pcs', 'total_amount', 'remark','approval_history_id', 'po_no', 'open_info','price_indicate','do_branch_id','debtor_id','discount','total_inv_amt','last_update'))." where branch_id=$branch_id and id=$do_id");
		    $temp = array('dept_id'=>$form['dept_id'], 'status'=>$form['status'], 'approved'=>$form['approved'], 'do_date'=>$form['do_date'], 'deliver_branch'=>$form['deliver_branch'],'total_ctn'=>$form['total_ctn'], 'total_pcs'=>$form['total_pcs'], 'total_amount'=>$form['total_amount'], 'total_round_amt'=>$form['total_round_amt'], 'total_foreign_amount'=>$form['total_foreign_amount'], 'remark'=>$form['remark'],'approval_history_id'=>$form['approval_history_id'], 'po_no'=>$form['po_no'], 'open_info'=>$form['open_info'],'price_indicate'=>$form['price_indicate'],'do_branch_id'=>$form['do_branch_id'],'debtor_id'=>$form['debtor_id'],'discount'=>$form['discount'],'total_inv_amt'=>$form['total_inv_amt'], 'total_round_inv_amt'=>$form['total_round_inv_amt'], 'total_foreign_inv_amt'=>$form['total_foreign_inv_amt'], 'last_update'=>$form['last_update'], "do_markup"=>$form['do_markup'],"sales_person_name"=>$form['sales_person_name'], "markup_type"=>$form['markup_type'],"use_address_deliver_to"=>$form['use_address_deliver_to'], "address_deliver_to"=>$form['address_deliver_to'], "exchange_rate"=>$form['exchange_rate'],"allowed_user"=>$form['allowed_user'],"delivery_debtor_id"=>$form['delivery_debtor_id'],"batch_code"=>$form['batch_code']);
		    $temp['sub_total_inv_amt'] = $form['sub_total_inv_amt'];
			$temp['sub_total_foreign_inv_amt'] = $form['sub_total_foreign_inv_amt'];
			$temp['no_use_credit_sales_cost'] = $form['no_use_credit_sales_cost'];
			
		    if($form['create_type']==4){    // create from sales order
				$temp['ref_tbl'] = $form['ref_tbl'];
				$temp['ref_no'] = $form['ref_no'];
			}

			if($config['masterfile_enable_sa'] && $form['do_sa']) $temp['mst_sa'] = serialize($form['do_sa']);
			else $temp['mst_sa'] = "";

			// gst
			//if($config['enable_gst']){
				$temp["is_under_gst"] = $form["is_under_gst"];
				$temp["inv_total_gross_amt"] = $form["inv_total_gross_amt"];
				$temp["inv_total_gst_amt"] = $form["inv_total_gst_amt"];
				$temp["inv_sheet_gst_discount"] = $form["inv_sheet_gst_discount"];
			//}
			
			$temp['amt_need_update'] = 0;
			
			$temp['sub_total_gross_amt'] = $form['sub_total_gross_amt'];
			$temp['sub_total_gst_amt'] = $form['sub_total_gst_amt'];
			$temp['sub_total_amt'] = $form['sub_total_amt'];
			
			$temp['inv_sub_total_gross_amt'] = $form['inv_sub_total_gross_amt'];
			$temp['inv_sub_total_gst_amt'] = $form['inv_sub_total_gst_amt'];
			
			$temp['inv_sheet_gross_discount_amt'] = $form['inv_sheet_gross_discount_amt'];
			$temp['inv_sheet_discount_amt'] = $form['inv_sheet_discount_amt'];
			
			$temp['do_total_gross_amt'] = $form['do_total_gross_amt'];
			$temp['do_total_gst_amt'] = $form['do_total_gst_amt'];
			$temp['is_special_exemption'] = $form['is_special_exemption'];
			if($temp['is_special_exemption']) $temp['special_exemption_rcr'] = $form['special_exemption_rcr'];
			else $temp['special_exemption_rcr'] = "";
			
			$temp['inv_sheet_foreign_discount_amt'] = $form['inv_sheet_foreign_discount_amt'];
			if($form['do_type'] == 'credit_sales')	$temp['use_debtor_price'] = $form['use_debtor_price'];
			
			if(isset($form['inv_sheet_adj_amt']))	$temp['inv_sheet_adj_amt'] = mf($form['inv_sheet_adj_amt']);
			
			// Check if first time confirm not exist, then update confirm_timestamp
			$q1 = $con->sql_query("select confirm_timestamp from do where id=".mi($do_id)." and branch_id=".mi($branch_id)." and confirm_timestamp > 0");
			$num_row = $con->sql_numrows($q1);
			$con->sql_freeresult($q1);
			if(!$num_row > 0 && $is_confirm){
				$temp['confirm_timestamp'] = 'CURRENT_TIMESTAMP';
			}
            $con->sql_query("update do set ".mysql_update_by_field($temp)." where branch_id=$branch_id and id=$do_id");
		}
		if($form['do_markup'])	$form['do_markup_arr'] = explode("+", $form['do_markup']);
		if($form['markup_type']=='down'){
	        $form['do_markup_arr'][0] *= -1;
	        $form['do_markup_arr'][1] *= -1;
		}

		//copy tmp table to do_items table
		$q1=$con->sql_query("select di.*,uom.fraction as uom_fraction
		from tmp_do_items di
		left join uom on uom.id=di.uom_id
where di.do_id=$do_id and di.branch_id=$branch_id and di.user_id=$sessioninfo[id] order by di.id") or die(mysql_error());
		$first_id = 0;
		$first_oi_id = 0;
		$total_amt = 0;
        $inv_amt = 0;
		
		$currency_discount_params = array();
        if($is_currency_mode){
			if($use_cost_indicate)	$currency_multiply = 1;
			else	$currency_multiply = $form['exchange_rate'];
			
			$currency_discount_params = array('currency_multiply'=>$currency_multiply);
			$currency_multiply_rate = 1/$form['exchange_rate'];
			
			if($use_cost_indicate)	$foreign_currency_discount_params['currency_multiply'] = $currency_multiply_rate;
		}
		
		if(count($do_branch_id_list)>1){
			$currency_discount_params['discount_by_value_multiply'] = count($do_branch_id_list);
			
			if($is_currency_mode){
				$foreign_currency_discount_params['discount_by_value_multiply'] = $currency_discount_params['discount_by_value_multiply'];
			}
		}
		//print_r($currency_discount_params);
		
		while($r=$con->sql_fetchassoc($q1)){
		    $amt_ctn = 0;
		    $amt_pcs = 0;
			$gross_amt = 0;
			$gst_amt = 0;
		    $row_amt = 0;
			$gross_inv_amt;
			$inv_gst_amt = 0;
		    $inv_discount_amt = 0;
			$row_inv_amt = 0;
		    $row_ctn = 0;
			$row_pcs = 0;
			$row_qty = 0;
			
			$r['do_id'] = $upd['do_id']=$form['id'];
			$upd['branch_id']=$r['branch_id'];
			$upd['artno_mcode']=$r['artno_mcode'];
			$upd['po_cost']=$r['po_cost'];
			$upd['cost']=$r['cost'];
			$upd['cost_price']=$r['cost_price'];
			if(!$currency_code || $currency_code == $config["arms_currency"]["symbol"]) $upd['foreign_cost_price']=0;
			else $upd['foreign_cost_price']=$r['foreign_cost_price'];
			$upd['selling_price']=$r['selling_price'];
			$upd['uom_id']=$r['uom_id'];
			$upd['ctn']=$r['ctn'];
			$upd['pcs']=$r['pcs'];
			$upd['ctn_allocation']=$r['ctn_allocation'];
			$upd['pcs_allocation']=$r['pcs_allocation'];
			$upd['selling_price_allocation']=$r['selling_price_allocation'];
			$upd['price_type']=$r['price_type'];
			$upd['price_no_history'] = $r['price_no_history'];
			$upd['item_discount'] = $r['item_discount'];
			if($config['do_custom_column']){
				$upd['custom_col'] = $r['custom_col'];
			}
			$upd['serial_no'] = $r['serial_no'];
			$upd['dtl_sa'] = $r['dtl_sa'];

			$upd['gst_id'] = $r['gst_id'];
			$upd['gst_code'] = $r['gst_code'];
			$upd['gst_rate'] = $r['gst_rate'];
			
			$upd['item_discount_amount'] = $r['item_discount_amount'];
			$upd['item_discount_amount2'] = $r['item_discount_amount2'];
			
			$upd['inv_line_gross_amt'] = $r['inv_line_gross_amt'];
			$upd['inv_line_gst_amt'] = $r['inv_line_gst_amt'];
			$upd['inv_line_amt'] = $r['inv_line_amt'];
			
			$upd['inv_line_gross_amt2'] = $r['inv_line_gross_amt2'];
			$upd['inv_line_gst_amt2'] = $r['inv_line_gst_amt2'];
			$upd['inv_line_amt2'] = $r['inv_line_amt2'];
			
			$upd['line_gross_amt'] = $r['line_gross_amt'];
			$upd['line_gst_amt'] = $r['line_gst_amt'];
			$upd['line_amt'] = $r['line_amt'];
			
			$upd['display_cost_price_is_inclusive'] = $r['display_cost_price_is_inclusive'];
			$upd['display_cost_price'] = $r['display_cost_price'];

			if($r['sku_item_id'] != 0){
				if($config['do_auto_group_items'] && !$form['is_under_gst'] && !$config['arms_marketplace_settings']){
					$ex_di_info = array();
					$is_existed_di = false;
					if(isset($existed_si[$r['sku_item_id']])){
						foreach($existed_si[$r['sku_item_id']] as $dummy=>$di){
							if($r['cost'] != $di['cost'] || $r['cost_price'] != $di['cost_price'] || $r['uom_fraction'] != $di['uom_fraction'] || $r['item_discount'] != $di['item_discount']) continue;
							else{
								$ex_di_info = $di;
								break;
							}
						}
					}
				}
				
				if(!$ex_di_info){
					$upd['sku_item_id']=$r['sku_item_id'];
					unset($upd['sku_item_code']);
					unset($upd['description']);
					$upd['stock_balance1'] = $r['stock_balance1'];
					$upd['stock_balance2'] = $r['stock_balance2'];
					$upd['stock_balance2_allocation'] = $r['stock_balance2_allocation'];
                    $upd['parent_stock_balance1'] = $r['parent_stock_balance1'];
					$upd['parent_stock_balance2'] = $r['parent_stock_balance2'];
                    $upd['parent_stock_balance2_allocation'] = $r['parent_stock_balance2_allocation'];
					//$upd['price_indicate'] = $form['i_price_indicate'][$r['id']];
					$upd['price_indicate'] = $r['price_indicate'];
					$upd['bom_id'] = $r['bom_id'];
					$upd['bom_ref_num'] = $r['bom_ref_num'];
					$upd['bom_qty_ratio'] = $r['bom_qty_ratio'];
					
					$con->sql_query("insert into do_items ".mysql_insert_by_field($upd)) or die(mysql_error());
					$r['id'] = $con->sql_nextid();
					
				}else{
					$is_existed_di = true;
					$upd = $serial_no_list = array();
					$ex_serial_no_list = unserialize($ex_di_info['serial_no']);
					$curr_serial_no_list = unserialize($r['serial_no']);
				
					if($is_deliver_multiple_branch){
						if(!is_array($ex_di_info['ctn_allocation']))	$ex_di_info['ctn_allocation'] = unserialize($ex_di_info['ctn_allocation']);
						if(!is_array($ex_di_info['pcs_allocation']))	$ex_di_info['pcs_allocation'] = unserialize($ex_di_info['pcs_allocation']);
						$ctn_allocation = unserialize($r['ctn_allocation']);
						$pcs_allocation = unserialize($r['pcs_allocation']);
						$deliver_branch = unserialize($form['deliver_branch']);
						foreach($deliver_branch as $dummy=>$bid){
							$ex_di_info['ctn_allocation'][$bid] += $ctn_allocation[$bid];
							$existed_si[$r['sku_item_id']][$ex_di_info['id']] = $ex_di_info;
							$upd['ctn_allocation'][$bid] = $existed_si[$r['sku_item_id']][$ex_di_info['id']]['ctn_allocation'][$bid];
							
							$ex_di_info['pcs_allocation'][$bid] += $pcs_allocation[$bid];
							$existed_si[$r['sku_item_id']][$ex_di_info['id']] = $ex_di_info;
							$upd['pcs_allocation'][$bid] = $existed_si[$r['sku_item_id']][$ex_di_info['id']]['pcs_allocation'][$bid];
							
							if($ex_serial_no_list[$bid] || $curr_serial_no_list[$bid]){
								$tmp_ex_serial_no_list = $ex_serial_no_list[$bid];
								$tmp_curr_serial_no_list = $curr_serial_no_list[$bid];
								$existed_si[$r['sku_item_id']][$ex_di_info['id']]['serial_no'][$bid] = $serial_no_list[$bid] = trim($tmp_ex_serial_no_list."\n".$tmp_curr_serial_no_list);
							}
						}
						
						$upd['ctn_allocation'] = serialize($upd['ctn_allocation']);
						$upd['pcs_allocation'] = serialize($upd['pcs_allocation']);
					}else{
						$existed_si[$r['sku_item_id']][$ex_di_info['id']]['ctn'] += $r['ctn'];
						$upd['ctn'] = $existed_si[$r['sku_item_id']][$ex_di_info['id']]['ctn'];
						
						$existed_si[$r['sku_item_id']][$ex_di_info['id']]['pcs'] += $r['pcs'];
						$upd['pcs'] = $existed_si[$r['sku_item_id']][$ex_di_info['id']]['pcs'];
						if($ex_serial_no_list || $curr_serial_no_list){
							$ex_serial_no_list = join("\n", $ex_serial_no_list);
							$curr_serial_no_list = join("\n", $curr_serial_no_list);
							$existed_si[$r['sku_item_id']][$ex_di_info['id']]['serial_no'][$form['do_branch_id']] = $serial_no_list[$form['do_branch_id']] = trim($ex_serial_no_list."\n".$curr_serial_no_list);
						}
					}
					if($serial_no_list) $upd['serial_no'] = serialize($serial_no_list);
					
					$con->sql_query("update do_items set ".mysql_update_by_field($upd)." where id = ".mi($ex_di_info['id'])." and branch_id = ".mi($ex_di_info['branch_id'])." and do_id = ".mi($ex_di_info['do_id']));
				}
				
				if ($first_id==0) $first_id = $r['id'];
			}else{
				unset($upd['sku_item_id']);
				unset($upd['stock_balance1']);
				unset($upd['stock_balance2']);
				unset($upd['stock_balance2_allocation']);
                unset($upd['parent_stock_balance1']);
				unset($upd['parent_stock_balance2']);
				unset($upd['parent_stock_balance2_allocation']);
				unset($upd['cost']);
				unset($upd['serial_no']);
				unset($upd['bom_id']);
				unset($upd['bom_ref_num']);
				unset($upd['bom_qty_ratio']);
				$upd['description']=$r['description'];
				$con->sql_query("insert into do_open_items ".mysql_insert_by_field($upd)) or die(mysql_error());
				if ($first_oi_id==0) $first_oi_id = $con->sql_nextid();
			}
			
			if(!$is_existed_di) $existed_si[$r['sku_item_id']][$r['id']] = $r;
		}

		// check if the do_open_items or do_items is equals to 0 record
		if($first_id != 0) $do_item = "and id<$first_id";
		if($first_oi_id != 0) $do_open_item = "and id<$first_oi_id";

		if ($first_id>0 || $first_oi_id > 0){
			if(!is_new_id($do_id)){
				$con->sql_query("delete from do_items where branch_id=$branch_id and do_id=$do_id $do_item") or die(mysql_error());
				$con->sql_query("delete from do_open_items where branch_id=$branch_id and do_id=$do_id $do_open_item") or die(mysql_error());
			}

			$con->sql_query("delete from tmp_do_items where do_id=$do_id and branch_id = $branch_id and user_id = $sessioninfo[id]") or die(mysql_error());
		}
		else{
			die("System error: Insert do_items failed. Please do not open multiple DO page, close all other opened DO page and try again. If problem still exists please contact ARMS technical support.");
		}
		
	 	if($form['create_type']==4){    // create from sales order
			$q1 = $con->sql_query("select * from sales_order where order_no=".ms($form['ref_no']));
			$sales_order = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
		
	 	    update_sales_order_do_qty($sales_order['id'], $sales_order['branch_id'], $sales_order);
		}
		
		if($config['consignment_modules']){
			update_do_sheet_price_type($form['branch_id'], $form['id']);
		}
		
		//recalculate do items amount
		auto_update_do_all_amt($form['branch_id'], $form['id']);
		if ($is_confirm){
			$formatted=sprintf("%05d",$form[id]);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$r=$con->sql_fetchrow();
			
	        log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Confirmed: (ID#".$r['report_prefix'].$formatted.", Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
		    if ($last_approval)	{
		    	if($direct_approve_due_to_less_then_min_doc_amt)	$_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;
		    	do_approval($form['id'], $branch_id, $form['status'], true);
			}
			else {
				$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
				$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'do');
				send_pm2($to, "Delivery Order Approval (ID#$form[id])", "do.php?page=$form[do_type]&a=view&id=$form[id]&branch_id=$branch_id", array('module_name'=>'do'));
			}
		}
		else{
		    $formatted=sprintf("%05d",$form[id]);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$r=$con->sql_fetchrow();
			
	        log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Saved: (ID#".$r['report_prefix'].$formatted." ,Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
		}
 	}
	header("Location: /do.php?page=$form[do_type]&t=$form[a]&save_id=$form[id]");

	exit;
}

function do_open($id, $branch_id){
	global $con, $LANG, $sessioninfo, $smarty, $config, $gst_list, $appCore;
	
	$form = $_REQUEST;// keep the passed header

	//is new DO
	if ($id==0){
		$id=time();
		if($id <= $_SESSION['do_last_create_time']) {$id = $_SESSION['do_last_create_time']+1;}
		$_SESSION['do_last_create_time'] = $id;
		$form['id']=$id;
	}

	//if the action is open and is not a NEW DO
	if ($form['a']=='open' && !is_new_id($id)){
		//get Existing DO header
		$form=load_do_header($id, $branch_id);	
		if(!$form){
		    $smarty->assign("url", "/do.php");
		    $smarty->assign("title", "Delivery Order");
		    $smarty->assign("subject", sprintf($LANG['DO_NOT_FOUND'], $id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		
		// check DO permission
		if ($form['user_id']!= $sessioninfo['id'] && $sessioninfo['level']<9999){
			//if(checking department)
		    $smarty->assign("url", "/do.php");
		    $smarty->assign("title", "Delivery Order");
		    $smarty->assign("subject", sprintf($LANG['DO_NO_ACCESS'], $id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		//if the DO oredi submit and not the reject DO, goto view only.
		elseif($form['status'] && $form['status']!=2){
			do_view($id, $branch_id);
			exit;
		}
		
		//check saved DO
		if(!$config['consignment_modules'] && $form['status'] == 0 && $branch_id != $sessioninfo['branch_id']){
			$err_msg = sprintf($LANG['CANNOT_EDIT_OTHER_BRANCH_MODULE'], "DO");
			js_redirect($err_msg, "/index.php");
		}
		
		//check reject do
		if($form['status'] == 2 && $form['user_id']!= $sessioninfo['id']){
			$err_msg = "This DO is only can edit by onwner.";
			js_redirect($err_msg, "/index.php");
		}
		
		copy_to_tmp($id, $branch_id);
	}
	//IF THE DO IS NEW or refresh
	else{
		if(!isset($form['branch_id']))	$form['branch_id'] = $branch_id;
		$form['id']=$id;
		if(!$form['do_date']) $form['do_date'] = date("Y-m-d");
		//if the DO is create from PO or upload file
		if($form['create_type']>1)
			$form['do_branch_name']=get_branch_code($form['do_branch_id']);

        if(!$form['do_branch_id']){
            if(count($_REQUEST['deliver_branch'])<2){
				$form['do_branch_id'] = $_REQUEST['deliver_branch'][0];
				unset($_REQUEST['deliver_branch']);
				unset($form['deliver_branch']);
			}
		}
		
		//get user list
		change_user_list(false,$form);
		
		// call this function to unset mprice type that is not available to this user
		sku_multiple_selling_price_handler();
		
		//print_r($form);exit;
		// check gst status
		//print_r($form);
		if($config['enable_gst'] && $form['do_date']){
			$form['is_under_gst'] = check_do_gst_status($form);
		}
	}
	
	// load special exemption relief claus remark if it is new DO or existing DO but does not have the remark (edit mode)
	if($config['enable_gst'] && $form['is_under_gst'] && !$form['special_exemption_rcr']){
		$q1 = $con->sql_query("select * from gst_settings where setting_name = 'special_exemption_relief_claus_remark'");
		$sercr_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		$form['special_exemption_rcr'] = $sercr_info['setting_value'];
		if(!$form['special_exemption_rcr']) $form['special_exemption_rcr'] = $config['se_relief_claus_remark'];
	}
	
	if($form['do_type']=='transfer' && $config['enable_gst'] && !$config['consignment_modules']){
		// load gst interbranch
		$gst_interbranch = array();
		$q1 = $con->sql_query("select * from gst_interbranch where branch_id_1=$branch_id or branch_id_2=$branch_id");
		while($r = $con->sql_fetchassoc($q1)){
			if($r['branch_id_1']>0 && $r['branch_id_1'] != $branch_id){
				$gst_interbranch[$r['branch_id_1']] = $r;
			}elseif($r['branch_id_2']>0 && $r['branch_id_2'] != $branch_id){
				$gst_interbranch[$r['branch_id_2']] = $r;
			}
		}
		$con->sql_freeresult($q1);
		//print_r($gst_interbranch);
		$smarty->assign('gst_interbranch', $gst_interbranch);
	}
	
	// load branch group
	$brn_grp_list = array();
	$q1 = $con->sql_query("select bg.*, group_concat(bgi.branch_id separator ',') as grp_items from branch_group bg 
							left join branch_group_items bgi on bg.id=bgi.branch_group_id group by bg.id");
	
	while($r = $con->sql_fetchassoc($q1)){
		$brn_grp_list[] = $r;
	}
	$con->sql_freeresult($q1);
	//print_r ($brn_grp_list);
	$smarty->assign('brn_grp_list', $brn_grp_list);
  	
	//print_r($form);
	if($form['do_sa']) $form['mst_sa'] = $form['do_sa'];
	$smarty->assign("do_items", load_do_items($id, $branch_id, $form, true));

	load_branches_group();
	if($form['active']===0)    $smarty->assign("readonly", 1);
	if($form['po_no'])  $smarty->assign('from_po',1);
	
	//print_r($gst_list);
	$smarty->assign('gst_list', $gst_list);
	$smarty->assign("form", $form);

	if($form['do_type']=='transfer')    $smarty->display("do.transfer.new.tpl");
	elseif($form['do_type']=='credit_sales')	$smarty->display("do.credit_sales.new.tpl");
	else $smarty->display("do.new.tpl");
}


function do_view($id, $branch_id){
	global $smarty, $LANG, $errm, $config;
	//get Existing DO header
	$form=load_do_header($id, $branch_id);			
	if(!$form){
	    $smarty->assign("url", "/do.php");
	    $smarty->assign("title", "Delivery Order");
	    $smarty->assign("subject", sprintf($LANG['DO_NOT_FOUND'], $id));
	    $smarty->display("redir.tpl");
	    exit;
	}
	load_branches_group();
	$smarty->assign("readonly", 1);
	$do_items = load_do_items($id, $branch_id,$form);
	
	$smarty->assign("do_items", $do_items);
	$smarty->assign("form", $form);
	$smarty->assign("errm", $errm);
	//print_r($do_items);
	
	if($_REQUEST['do_type']=='transfer'||$form['do_type']=='transfer'){
        $smarty->display("do.transfer.new.tpl");
	}
	elseif($_REQUEST['do_type']=='credit_sales'||$form['do_type']=='credit_sales'){
	    $smarty->display("do.credit_sales.new.tpl");
	}else	$smarty->display("do.new.tpl");
}

function do_print($id, $branch_id){
	global $con, $smarty, $config;

	$type_postfix_list = array('transfer'=>'','credit_sales'=>'/D','open'=>'/C');
	
	$markup = mf($_REQUEST['markup']);
	$is_draft = mi($_REQUEST['is_draft']);
	$is_proforma = mi($_REQUEST['is_proforma']);
    $hide_RSP=0;

	// Got Set Markup
	if (isset($_REQUEST['print_invoice'])&&isset($_REQUEST['markup']))
	{
		// save the markup
		$con->sql_query("update do set invoice_markup = $markup  where id = $id and branch_id = $branch_id");
	}
	$smarty->assign("markup", $markup);
	
	// Load Form
	$form=load_do_header($id, $branch_id);
	
	// Load Items
	$do_items = load_do_items($id, $branch_id,$form);
	
	// Load all required data to append into object
	load_do_print_required_data($form, $do_items);
	
	$smarty->assign("form", $form);
	$smarty->assign('no_show_date', $_REQUEST['no_show_date']);
	
	if($is_draft||$is_proforma){
		$smarty->assign('is_draft',$is_draft);
		$smarty->assign('is_proforma',$is_proforma);
		$print_branch_id = $_REQUEST['print_branch_id'];
		if($print_branch_id){
			foreach($print_branch_id as $do_bid){
				$smarty->assign("to_branch", $form['to_branch_list'][$do_bid]);
				
				do_print2($id, $branch_id, $form, $do_items, $do_bid);
			}
		}else{
			do_print2($id, $branch_id, $form, $do_items, $form['do_branch_id']);
		} 
	}else{
		do_print2($id, $branch_id, $form, $do_items, $form['do_branch_id']);
	}
	
}

//change owner
function do_chown($do_id, $branch_id){
	global $con, $sessioninfo, $LANG;
	$form=$_REQUEST;

	$q1=$con->sql_query("select id from user
    left join user_privilege on user.id=user_privilege.user_id
    where user_privilege.branch_id=$sessioninfo[branch_id] and user.u=".ms($form['new_owner']));
	$r1=$con->sql_fetchrow($q1);
	if($r1)
		$con->sql_query("update do set user_id=$r1[0] where id=$do_id and branch_id=$branch_id");

    if($r1 && $con->sql_affectedrows()>0){
        $con->sql_query("update do_items set user_id=$r1[0] where do_id=$do_id and branch_id=$branch_id");

		printf($LANG['DO_CHOWN_SUCCESS'], $form['new_owner']);
	}
	else{
        printf($LANG['DO_CHOWN_FAILED'], $form['new_owner']);
	}
}

function do_print2($id, $branch_id, $form, $do_items, $do_branch_id){
	global $con, $smarty, $config, $sessioninfo, $LANG, $sort_print_item_sequence;
	
	$already_group = false;
	$type_postfix_list = array('transfer'=>'','credit_sales'=>'/D','open'=>'/C');
	$size_color_invoice = mi($_REQUEST['size_color_invoice']);
	$sort_print_item_sequence = trim($_REQUEST['print_item_sequence']);
	
	if($form['ref_tbl']=='sales_order'&&$form['ref_no']){ // from sales order
		$con->sql_query("select * from sales_order where order_no=".ms($form['ref_no']));
		$smarty->assign('sales_order_data', $con->sql_fetchrow());
	}
	
	$default_item_per_page = $config['do_print_item_per_page']>0?$config['do_print_item_per_page']:30;
	if ($config['report_logo_by_branch']['do'][get_branch_code($branch_id)]) $smarty->assign("alt_logo_img", $config['report_logo_by_branch']['do'][get_branch_code($branch_id)]);
	
	if (!$form['checkout'])
	{
		$default_item_per_lastpage = $default_item_per_page - 5;
	}
	else
	{
		$default_item_per_lastpage = $default_item_per_page - 15;
	}

	if($form['is_under_gst']){
		// generate gst summary
		$form['gst_summary'] = generate_do_gst_summary($form, $do_items);
		
	}
	
	//get admin logo system_settings and branch logo setting 
	$system_settings = array();
	$setting_list = array('logo_vertical', 'verticle_logo_no_company_name');
	foreach($setting_list as $setting_name){
		$q1 = $con->sql_query("select setting_value from system_settings where setting_name=".ms($setting_name));
		$r = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		$system_settings[$setting_name] = $r['setting_value'];
	}
	$qry1 = $con->sql_query("select is_vertical_logo,vertical_logo_no_company_name from branch where id=$branch_id");
	$r1 = $con->sql_fetchassoc($qry1);
	$con->sql_freeresult($qry1);
	if($r1['is_vertical_logo'] == 1){
		$system_settings['verticle_logo_no_company_name'] = $r1['vertical_logo_no_company_name'];
		$system_settings['logo_vertical'] = $r1['is_vertical_logo'];
	}
	$smarty->assign("system_settings",$system_settings);
	

	// check pcs allocation
	if($form['deliver_branch']){
		
		// fix printing ctn and pcs
		if($do_items){
			foreach($do_items as $key=>$di){
				if($di['ctn_allocation']){
				
					$do_items[$key]['ctn'] = $di['ctn_allocation'][$do_branch_id];
				}
				if($di['pcs_allocation']){
					
					$do_items[$key]['pcs'] = $di['pcs_allocation'][$do_branch_id];
				}
				if($di['selling_price_allocation']){
					$do_items[$key]['selling_price'] = $di['selling_price_allocation'][$do_branch_id];
				}
			}
		}
	}
	// hide zero qty
	if($do_items){
		foreach($do_items as $key=>$di){
			if(!$di['pcs']&&!$di['ctn']&&!$di['oi'])	unset($do_items[$key]);
		}
	}
	
	if($config['do_print_combine_same_item']&&$do_items){	// combine same item
	    // will not combine if different invoice discount or price or uom_fraction
		$do_items = grouping_item($do_items);
		$already_group = true;
	}
	
	if($sort_print_item_sequence){
		uasort($do_items, 'sort_print_do_items');
	}

	$default_totalpage = 1 + ceil((count($do_items)-$default_item_per_lastpage)/$default_item_per_page);
	
	if(!isset($_REQUEST['print_col']))	$_REQUEST['print_col'] = $config['do_print_col_list'];

	$copy[] = 'normal';
	if($_REQUEST['acc_copy'])   $copy[] = 'Account Copy';
	if($_REQUEST['store_copy'])   $copy[] = 'Store Copy';

	$need_generate = true;
	$do_invoice_items = array();

	foreach($copy as $cp_type){
	    $use_same_setting = false;
	    $item_per_page = $default_item_per_page;
        $item_per_lastpage = $default_item_per_lastpage;

        if($_REQUEST['print_invoice']&&$form['checkout']){
          if($config['do_checkout_invoice_print_item_per_page']){
            $item_per_page = $config['do_checkout_invoice_print_item_per_page']; 
           
          }
          if($config['do_checkout_invoice_print_item_per_last_page']){
            $item_per_lastpage = $config['do_checkout_invoice_print_item_per_last_page'];
          }
        }

		
		
        $totalpage = 1 + ceil((count($do_items)-$item_per_lastpage)/$item_per_page);
       
        
        if($config['do_cash_sales_alt_print_item_per_page']&&$form['do_type']=='open'){
          $item_per_page = $config['do_cash_sales_alt_print_item_per_page'];
          if($config['do_cash_sales_alt_print_item_per_lastpage'])   $item_per_lastpage = $config['do_cash_sales_alt_print_item_per_lastpage'];
          else    $item_per_lastpage = $item_per_page-5;
          $totalpage = 1 + ceil((count($do_items)-$item_per_lastpage)/$item_per_page);
          $use_same_setting = true;
      	}
      	
	    $smarty->assign("copy_type",$cp_type);

       // print invoice
        if($_REQUEST['print_invoice']&&$form['checkout']){
			if($size_color_invoice && !$use_same_setting){	// Size Color Format
			  // Item per page
			  $item_per_page = 10;
			  if($config['do_size_color_invoice_print_item_per_page']>0)	$item_per_page = mi($config['do_size_color_invoice_print_item_per_page']);
			  
			  $item_per_lastpage = $item_per_page;
			  if($config['do_size_color_invoice_print_item_per_lastpage'])   $item_per_lastpage = $config['do_size_color_invoice_print_item_per_lastpage'];
			}
			
	        if($need_generate){
			    // check whether got inv_no
			    if($_REQUEST['selected_inv_no']&&$config['do_invoice_separate_number']&&!$form['inv_no']){
			        $con->sql_query("select report_prefix, ip from branch where id=$branch_id");
					$report_prefix = $con->sql_fetchrow();
					$type_postfix = $type_postfix_list[$form['do_type']];
					$selected_inv_no = $report_prefix[0] . sprintf("%05d", mi($_REQUEST['selected_inv_no'])).$type_postfix;
					// try whether user key in invoice no is available or not
					$success = $con->sql_query("update do set inv_no=".ms($selected_inv_no)." where id=$id and branch_id = $branch_id",false,false);
					if($success)    $form['inv_no'] = $selected_inv_no;
					$smarty->assign("form", $form);
				}
				    
		        if(!$form['inv_no']){
				    $form['inv_no'] = assign_inv_no($id,$branch_id);      
				}	
	
				$form['inv_printed'] = 1;
				$con->sql_query("update do set inv_printed=1 where id=$id and branch_id = $branch_id and inv_printed=0") or die(mysql_error());
		        $smarty->assign("form", $form);
		                
				if ($config['do_print_invoice_item_grouping'] || $size_color_invoice){
					$grouping_params = array();
					if($size_color_invoice)	$grouping_params['group_same_clr'] = 1;
	
					// group same do_items by sku_item_code and cost_price added by andy at 2008-12-19
					
					
					if($already_group && !$size_color_invoice)  $do_invoice_items = $do_items;
					else    $do_invoice_items = grouping_item($do_items, $grouping_params);
				}else{
					$do_invoice_items = $do_items;
		        }
		
		        $total_invoice_page = 1 + ceil((count($do_invoice_items)-$item_per_lastpage)/$item_per_page);
		        $need_generate = false;
			}
			
			// start print invoice
			$item_index = -1;
			$item_no = -1;
			$page = 1;
			
			$page_item_list = array();
			$page_item_info = array();
			$invoice_has_discount = false;
			foreach($do_invoice_items as $r){	// loop for each item
				if($item_index+1>=$item_per_page){
					$page++;
					$item_index = -1;
				}
				
				$item_no++;
				$item_index++;
				$r['item_no'] = $item_no;
				
				if($config['do_show_sn_in_one_page'] && $r['serial_no']){
					if($form['do_type'] == "transfer") $r['warranty_period'] = "-";
					else $r['warranty_period'] = $r['serial_no']['we'][0]." ".$r['serial_no']['we_type'][0]."(s)";
				}
				if($r['item_discount'] > 0) $invoice_has_discount = true;
				$page_item_list[$page][$item_index] = $r;	// add item to this page

				if(!$size_color_invoice){	// Size Color Format dont have additional description and serial number
					if($config['sku_enable_additional_description'] && $r['additional_description'] && !$config['do_disable_print_additional_desc']){
						foreach($r['additional_description'] as $desc){
							if($item_index+1>=$item_per_page){
								$page++;
								$item_index = -1;
							}
					
							$item_index++;
							$desc_row = array();
							$desc_row['description'] = $desc;
							
							$page_item_list[$page][$item_index] = $desc_row;
							
							$page_item_info[$page][$item_index]['not_item'] = 1;
						}
					}
					
					if($config['do_show_sn_in_one_page'] && $r['serial_no']){		
						if($item_index+1>=$item_per_page){
							$page++;
							$item_index = -1;
						}
						
						$item_index++;
						$sn_info = array();
						
						if($form['do_type'] == "transfer") $sn_list =  explode("\n", $r['serial_no'][$form['do_branch_id']]);
						else $sn_list = $r['serial_no']['sn'];
						
						$sn_info['description'] = "Serial No: ".join(", ", $sn_list);
						
						$page_item_list[$page][$item_index] = $sn_info;
						$page_item_info[$page][$item_index]['not_item'] = 1;
						$page_item_info[$page][$item_index]['no_crop'] = 1;
					}
				}
			}
		
			// fix last page
			if(count($page_item_list[$page]) > $item_per_lastpage){	// last page item too many
				
				// add one more page and put no item in last page
				$page++;
				$page_item_list[$page] = array();
			}
			
			$totalpage = count($page_item_list);
			
			if($size_color_invoice){
				// Size and Color Invoice
				$inv_tpl = "do_checkout.print_color_size_invoice.tpl";
			}else{
				// Normal Invoice
				$inv_tpl = "do_checkout.print_invoice.tpl";
				if($form['do_type']=='open' && $config['do_checkout_invoice_cash_sales_alt_print_template']){
					$inv_tpl = $config['do_checkout_invoice_cash_sales_alt_print_template'];
				}elseif($config['do_checkout_invoice_alt_print_template']){
					$inv_tpl = $config['do_checkout_invoice_alt_print_template'];
				}
			}
			
			
			foreach($page_item_list as $page => $item_list){
				$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
				
				$smarty->assign("PAGE_SIZE", $this_page_num);
				$smarty->assign("is_lastpage", ($page >= $totalpage));
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("start_counter",$item_list[0]['item_no']);
				$smarty->assign('extra_empty_row', $this_page_num-count($item_list));
				//print_r($item_list);
				$smarty->assign('invoice_has_discount', $invoice_has_discount);
				$smarty->assign("do_items", $item_list);
				$smarty->assign("page_item_info", $page_item_info[$page]);

				$smarty->display($inv_tpl);
				$smarty->assign("skip_header",1);
			}
			
			if ($item_index <0){
				display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['PRINT_ZERO_QTY'], "DO"));
			}
			
		}
		
		
		// end of print invoice
		if(!$use_same_setting){
			$item_per_page = $default_item_per_page;
			$item_per_lastpage = $default_item_per_lastpage;

			if($form['checkout']){
				if($config['do_checkout_print_item_per_page']){
					$item_per_page = $config['do_checkout_print_item_per_page'];
				}
				if($config['do_checkout_print_item_per_last_page']){
					$item_per_lastpage = $config['do_checkout_print_item_per_last_page'];
				}
			}else{
				if($config['do_print_item_per_last_page'])  $item_per_lastpage = $config['do_print_item_per_last_page'];
			}
			
			$totalpage = 1 + ceil((count($do_items)-$item_per_lastpage)/$item_per_page);
			
		}
		
		// print DO
		$print_do_list = array();
		if($_REQUEST['print_do']) $print_do_list[0]['print_do'] = 1;
		if($_REQUEST['print_do_picking_list']) $print_do_list[1]['print_do_picking_list'] = 1;
		if($_REQUEST['print_do_packing_list']) $print_do_list[1]['print_do_packing_list'] = 1;
		if($_REQUEST['print_do_assignment_note']) $print_do_list[1]['print_do_assignment_note'] = 1;
		if($_REQUEST['print_pro_invoice']) $print_do_list[1]['print_pro_invoice']=1;   //added in list

		ksort($print_do_list); // ensure the "print_do" always the first to loop so it can get the page settings
		
		if(count($print_do_list) > 0){
			foreach($print_do_list as $dummy=>$p){
				if($p['print_do_picking_list']){
					$item_per_page= $config['do_picking_list_print_item_per_page'] ? $config['do_picking_list_print_item_per_page']:30;
					$item_per_lastpage = $config['do_picking_list_print_item_lastpage']>0 ? $config['do_picking_list_print_item_lastpage'] : $item_per_page-10;
					
					$totalpage = 1 + ceil((count($do_items)-$item_per_lastpage)/$item_per_page);
				}
				
				$item_index = -1;
				$item_no = -1;
				$page = 1;
				
				$page_item_list = array();
				$page_item_info = array();
				
				if(isset($do_items)){
					foreach($do_items as $r){	// loop for each item
						if($item_index+1>=$item_per_page){
							$page++;
							$item_index = -1;
						}
						
						$item_no++;
						$item_index++;
						$r['item_no'] = $item_no;
						
						if($config['do_show_sn_in_one_page'] && $r['serial_no']){
							if($form['do_type'] == "transfer") $r['warranty_period'] = "-";
							else $r['warranty_period'] = $r['serial_no']['we'][0]." ".$r['serial_no']['we_type'][0]."(s)";
						}
						
						$page_item_list[$page][$item_index] = $r;	// add item to this page
						
						if(!$p['print_do_packing_list']){
							if($config['sku_enable_additional_description'] && $r['additional_description'] && !$config['do_disable_print_additional_desc']){
								foreach($r['additional_description'] as $desc){
									if($item_index+1>=$item_per_page){
										$page++;
										$item_index = -1;
									}
							
									$item_index++;
									$desc_row = array();
									$desc_row['description'] = $desc;
									
									$page_item_list[$page][$item_index] = $desc_row;
									
									$page_item_info[$page][$item_index]['not_item'] = 1;
								}
							}
						}
						
						if($config['do_show_sn_in_one_page'] && $r['serial_no']){			
							if($item_index+1>=$item_per_page){
								$page++;
								$item_index = -1;
							}
							
							$item_index++;
							$sn_info = array();
							
							if($form['do_type'] == "transfer") $sn_list = explode("\n", $r['serial_no'][$form['do_branch_id']]);
							else $sn_list = $r['serial_no']['sn'];
							
							$sn_info['description'] = "Serial No: ".join(", ", $sn_list);
							
							$page_item_list[$page][$item_index] = $sn_info;
							$page_item_info[$page][$item_index]['not_item'] = 1;
							$page_item_info[$page][$item_index]['no_crop'] = 1;
						}
					}
				}
				
				$con->sql_query("update do set do_printed=1 where id=$id and branch_id = $branch_id and do_printed=0") or die(mysql_error());
				
				// fix last page
				if(count($page_item_list[$page]) > $item_per_lastpage){	// last page item too many
					
					// add one more page and put no item in last page
					$page++;
					$page_item_list[$page] = array();
				}
				
				$totalpage = count($page_item_list);
				
				foreach($page_item_list as $page => $item_list){
					$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
					
					$smarty->assign("PAGE_SIZE", $this_page_num);
					$smarty->assign("is_lastpage", ($page >= $totalpage));
					$smarty->assign("page", "Page $page of $totalpage");
					$smarty->assign("start_counter",$item_list[0]['item_no']);
					$smarty->assign('extra_empty_row', $this_page_num-count($item_list));

					foreach($item_list as $key => $value){
						$total_carton[$value['id']] = $value['pack_carton'];
						$smarty->assign("total_carton", $total_carton);
					}
					
					$smarty->assign("do_items", $item_list);
					$smarty->assign("page_item_info", $page_item_info[$page]);
									
				   // print do 	
				   if($p['print_do']){
						if ($form['checkout'])
						{
							if($form['do_type']=='open'&&$config['do_checkout_cash_sales_alt_print_template'])   $smarty->display($config['do_checkout_cash_sales_alt_print_template']);
							elseif($config['do_checkout_alt_print_template'])    $smarty->display($config['do_checkout_alt_print_template']);
							else $smarty->display("do_checkout.print.tpl");
						}
						else{
							if($form['do_type']=='open'&&$config['do_cash_sales_alt_print_template'])   $smarty->display($config['do_cash_sales_alt_print_template']);
							elseif($config['do_alt_print_template'])    $smarty->display($config['do_alt_print_template']);
							else    $smarty->display("do.print.tpl");
						}
					}
					
					// print picking 
					if($p['print_do_picking_list']){
					if($config['do_picking_list_alt_print_template'])
						{
							$smarty->display($config['do_picking_list_alt_print_template']);
						}else{
							$smarty->display("do.print_picking_list.tpl");
						}
					}
					
					// print packing list
					if($p['print_do_packing_list']){
						$smarty->assign("form", $form);
						$smarty->display("do.print_packing_list.tpl");
					}

					// print proforma invoice 	
					if($p['print_pro_invoice']){
						$smarty->assign("form", $form);
						if($config['do_proforma_alt_print_template']){ 
							$smarty->display($config['do_proforma_alt_print_template']);
						}else{
							$smarty->display("do_checkout.print_invoice.tpl");
						}
					}
				
									
					$smarty->assign("skip_header",1);
				}
			
				if ($item_index <0){
					display_redir($_SERVER['PHP_SELF'], "Delivery Order", sprintf($LANG['PRINT_ZERO_QTY'], "DO"));
				}
			}
			
		
		}
		// end of print DO
		// print receipt
	
	}

	if($_REQUEST['print_sz_clr'] && $do_items){
		$item_per_lastpage = $item_per_page = $config['do_size_color_print_item_per_page']?$config['do_size_color_print_item_per_page']:30;
		$item_no = 0;
		$item_index = 0;
		$page = 1;
		$tmp_sku_id = "";
		$tmp_do_items = $parent = $sz_clr_list = $parent_info = array();
		$do_size_color_print_template = $config['do_sz_clr_print_template']?$config['do_sz_clr_print_template']:"do.sz_clr.print.tpl";
		
		foreach($do_items as $key=>$di){
			if(!$di['size'] || !$di['color']) continue;
			$tmp_do_items[$di["sku_id"]][] = $di;
			$arrange_by = "color";
			
			if($config["enable_one_color_matrix_ibt"]){
				$arrange_by = "size";
			}
			
			if(!isset($parent[$di["sku_id"]][$di[$arrange_by]])){
				$parent[$di["sku_id"]][$di[$arrange_by]] = 0;
			}
		}
		
		if($tmp_do_items){
			foreach($tmp_do_items as $key=>$item){
				if($tmp_sku_id != $key){
					$tmp_sku_id = $key;
					
					$item_index += count($parent[$key]) + 1;
					
					if($item_index>$item_per_page){
						$page++;
						$item_index = count($parent[$key]) + 1;
					}
					$item_no++;
				}
				
				foreach($item as $key1=>$di){
					$sz_clr_list[$page][$di['sku_id']]['size'][$di['size']] = $di['size'];
					$sz_clr_list[$page][$di['sku_id']]['color'][$di['color']] = $di['color'];

					if(!$parent_info[$di['sku_id']]){
						$q1 = $con->sql_query("select *
											   from sku_items
											   where is_parent = 1 and sku_id = ".mi($di['sku_id'])." limit 1");
						$parent_info[$di['sku_id']] = $con->sql_fetchrow($q1);
						$con->sql_freeresult($q1);
					}

					$row_type=$di['size'];
					$col_type=$di['color'];
					if($config["enable_one_color_matrix_ibt"]){
						$row_type=$di['color'];
						$col_type=$di['size'];
					}
					
					$sz_clr_list[$page][$di['sku_id']]['item_no'] = $item_no;
					$sz_clr_list[$page][$di['sku_id']]['sku_item_code'] = $parent_info[$di['sku_id']]['sku_item_code'];
					$sz_clr_list[$page][$di['sku_id']]['description'] = $parent_info[$di['sku_id']]['description'];
					$sz_clr_list[$page][$di['sku_id']]['mcode'] = $parent_info[$di['sku_id']]['mcode'];
					$sz_clr_list[$page][$di['sku_id']]['artno'] = $parent_info[$di['sku_id']]['artno'];
					$sz_clr_list[$page][$di['sku_id']]['list'][$col_type][$row_type][$di['uom_code']]['ctn'] += $di['ctn'];
					$sz_clr_list[$page][$di['sku_id']]['list'][$col_type][$row_type][$di['uom_code']]['pcs'] += $di['pcs'];
					$sz_clr_list[$page][$di['sku_id']]['list'][$col_type][$row_type][$di['uom_code']]['ctn_pcs'] += ($di['ctn']*$di['uom_fraction'])+$di['pcs'];
					$sz_clr_list[$page][$di['sku_id']][$di['size']][$di['uom_code']]['ctn'] += $di['ctn'];
					$sz_clr_list[$page][$di['sku_id']][$di['size']][$di['uom_code']]['pcs'] += $di['pcs'];
					$sz_clr_list[$page][$di['sku_id']][$di['color']][$di['uom_code']]['ctn'] += $di['ctn'];
					$sz_clr_list[$page][$di['sku_id']][$di['color']][$di['uom_code']]['pcs'] += $di['pcs'];
					$sz_clr_list[$page][$di['sku_id']][$di['uom_code']]['total']['ctn'] += $di['ctn'];
					$sz_clr_list[$page][$di['sku_id']][$di['uom_code']]['total']['pcs'] += $di['pcs'];
					$sz_clr_list[$page][$di['sku_id']]['uom_list'][$di['uom_code']] = $di['uom_code'];
					$sz_clr_list[$page][$di['sku_id']]['total_pcs'] += ($di['ctn']*$di['uom_fraction'])+$di['pcs'];
				}
			}
		}else{
			$smarty->assign("err", "No items have size and color in this DO");
			$smarty->display($do_size_color_print_template);
		}
		unset($parent, $parent_info, $tmp_do_items);
		if($sz_clr_list){
			if(count($sz_clr_list[$page]) > $item_per_lastpage){
				$page++;
				$sz_clr_list[$page] = array();
			}
			
			$totalpage = count($sz_clr_list);
			
			foreach($sz_clr_list as $page => $item_list){
				$this_page_num = ($page < $totalpage) ? $item_per_page : $item_per_lastpage;
				$smarty->assign("PAGE_SIZE", $this_page_num);
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("sz_clr_items", $item_list);
				$smarty->display($do_size_color_print_template);
			}
			unset($sz_clr_list);
		}
	}
	
	if(!$config['do_show_sn_in_one_page'] && $config['enable_sn_bn'] && $do_items){
		foreach($do_items as $key=>$di){
			if($di['serial_no'][$do_branch_id] && $form['do_type'] == "transfer"){
				$do_items[$key]['sn'][$do_branch_id] = explode("\n", $di['serial_no'][$do_branch_id]);
				$max_sn += count($do_items[$key]['sn'][$do_branch_id]);
			}elseif($form['do_type'] != "transfer"){
				$max_sn += count($di['serial_no']['sn']);
			}
		}
		
		if($config['do_print_sn_template']) $tpl = $config['do_print_sn_template'];
		else $tpl = "do.sn.print.tpl";
		
		if($max_sn){
			$totalpage = 1 + ceil(($max_sn-$item_per_lastpage)/$item_per_page);
			for($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
				$smarty->assign("PAGE_SIZE", ($page < $totalpage)?$item_per_page:$item_per_lastpage);
				$smarty->assign("is_lastpage", ($page >= $totalpage));
				$smarty->assign("page", "Page $page of $totalpage");
				$smarty->assign("start_counter", $i);
				$smarty->assign("do_items", array_slice($do_items,$i,$item_per_page));
				$smarty->display($tpl);
			}
		}
	} 
	
	if($_REQUEST['print_group_same_clr'] && $do_items){
		$grouping_params = array();
		$grouping_params['group_same_clr'] = 1;
	
		$tmp_do_items = grouping_item($do_items, $grouping_params);
		
		//print_r($tmp_do_items);
		
		// Item per page
		$item_per_page = 10;
		if($config['do_group_same_clr_print_item_per_page']>0)	$item_per_page = mi($config['do_group_same_clr_print_item_per_page']);

		$item_per_lastpage = $item_per_page;
		if($config['do_group_same_clr_print_item_per_lastpage'])   $item_per_lastpage = $config['do_group_same_clr_print_item_per_lastpage'];
		
		$tpl = "do.group_same_clr.print.tpl";
		if($config['do_print_group_same_clr_template']) $tpl = $config['do_print_group_same_clr_template'];
		
		$totalpage = 1 + ceil((count($tmp_do_items)-$item_per_lastpage)/$item_per_page);
		for($i=0,$page=1;$page<=$totalpage;$i+=$item_per_page,$page++){
			$smarty->assign("PAGE_SIZE", ($page < $totalpage)?$item_per_page:$item_per_lastpage);
			$smarty->assign("is_lastpage", ($page >= $totalpage));
			$smarty->assign("page", "Page $page of $totalpage");
			$smarty->assign("start_counter", $i);
			$smarty->assign("do_items", array_slice($tmp_do_items,$i,$item_per_page));
			$smarty->display($tpl);
		}
	}
	
	//show print log
	$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
    $r=$con->sql_fetchrow();
    $formatted=sprintf("%05d",$id);

	if($form['status']==0){
    	$log_contents = $r['report_prefix'].$formatted."(DD)";
    }elseif($form['status']==1 && $form['approved']==0){
		$log_contents = $r['report_prefix'].$formatted."(PD)";
	}else{
		$log_contents = $form['do_no'];
	}

    log_br($sessioninfo['id'], 'DELIVERY ORDER', $id, "Print : ".$log_contents);           
}

function copy_to_tmp($do_id, $branch_id){
	global $con, $sessioninfo;
	//delete ownself DO items in tmp table
	$con->sql_query("delete from tmp_do_items where do_id=$do_id and branch_id = $branch_id and user_id = $sessioninfo[id]");

	//copy do_items to tmp table
	$q1 = $con->sql_query("insert into tmp_do_items
                          (do_id, branch_id, user_id, sku_item_id, artno_mcode, po_cost, cost, cost_price, foreign_cost_price, selling_price, uom_id, ctn, pcs,
                          ctn_allocation, pcs_allocation, selling_price_allocation, price_type, rcv_pcs, stock_balance1, stock_balance2, stock_balance2_allocation,
                          parent_stock_balance1, parent_stock_balance2, parent_stock_balance2_allocation, price_no_history, item_discount, serial_no, price_indicate,
                          dtl_sa, gst_id, gst_code, gst_rate,item_discount_amount, item_discount_amount2, inv_line_gross_amt, inv_line_gst_amt, inv_line_amt,
                          inv_line_gross_amt2, inv_line_gst_amt2, inv_line_amt2, line_gross_amt, line_gst_amt, line_amt, display_cost_price_is_inclusive, display_cost_price, bom_id, bom_ref_num, bom_qty_ratio, custom_col)
                          select
                          $do_id, branch_id, $sessioninfo[id], sku_item_id, artno_mcode, po_cost, cost, cost_price, foreign_cost_price, selling_price, uom_id, ctn, pcs,
                          ctn_allocation, pcs_allocation, selling_price_allocation, price_type, rcv_pcs, stock_balance1, stock_balance2, stock_balance2_allocation,
                          parent_stock_balance1, parent_stock_balance2, parent_stock_balance2_allocation, price_no_history, item_discount, serial_no, price_indicate,
                          dtl_sa, gst_id, gst_code, gst_rate, item_discount_amount, item_discount_amount2, inv_line_gross_amt, inv_line_gst_amt, inv_line_amt,
                          inv_line_gross_amt2, inv_line_gst_amt2, inv_line_amt2, line_gross_amt, line_gst_amt, line_amt, display_cost_price_is_inclusive, display_cost_price, bom_id, bom_ref_num, bom_qty_ratio, custom_col
                          from do_items where do_id=$do_id and branch_id=$branch_id order by id");

	// copy open item to tmp table
	$q2 = $con->sql_query("insert into tmp_do_items
                          (do_id, branch_id, user_id, sku_item_id, description, artno_mcode, po_cost, cost_price, foreign_cost_price, selling_price, uom_id, ctn, pcs,
                          ctn_allocation, pcs_allocation, selling_price_allocation, price_type, rcv_pcs, price_no_history, item_discount, dtl_sa, gst_id, gst_code,
                          gst_rate, item_discount_amount, item_discount_amount2, inv_line_gross_amt, inv_line_gst_amt, inv_line_amt, inv_line_gross_amt2, inv_line_gst_amt2,
                          inv_line_amt2, line_gross_amt, line_gst_amt, line_amt, display_cost_price_is_inclusive, display_cost_price, custom_col)
                          select
                          $do_id, branch_id, $sessioninfo[id], '0', description, artno_mcode, po_cost, cost_price, foreign_cost_price, selling_price, uom_id, ctn, pcs,
                          ctn_allocation, pcs_allocation, selling_price_allocation, price_type, rcv_pcs, price_no_history, item_discount, dtl_sa, gst_id, gst_code,
                          gst_rate, item_discount_amount, item_discount_amount2, inv_line_gross_amt, inv_line_gst_amt, inv_line_amt, inv_line_gross_amt2, inv_line_gst_amt2,
                          inv_line_amt2, line_gross_amt, line_gst_amt, line_amt, display_cost_price_is_inclusive, display_cost_price, custom_col
                          from do_open_items where do_id=$do_id and branch_id=$branch_id order by id");
}

function do_create_from_upload_file($do_id, $branch_id){
 	global $con, $sessioninfo, $smarty, $config;
 	
 	//print_r($_REQUEST);exit;
	if ($do_id==0) {
		$do_id = time();		
		if($do_id <= $_SESSION['do_last_create_time']) {$do_id = $_SESSION['do_last_create_time']+1;}
		$_SESSION['do_last_create_time'] = $do_id;
	}
	$form=$_REQUEST;
	if(!isset($form['branch_id']))	$form['branch_id'] = $branch_id;
	
	$do_branch_id = $form['do_branch_id'];
	
	if(!$do_branch_id && ($form["page"] == "open" || $form["page"] == "credit_sales"))	$do_branch_id = $branch_id;
	
	if($config['enable_gst']){
		// check whether this do date is under gst
		$form['is_under_gst'] = check_do_gst_status($form);
	}
	$file_name=$_FILES['files']['tmp_name'];
	$import_format = $form['import_format'] ? $form['import_format'] : 1;
	
	$handle = fopen($file_name, "r");	
	$delimeter = trim($_REQUEST['delimiter']);
	if (!$delimeter) $delimeter = '|';
	/*
	$delimeter = ",";
	if($import_format==1)	$delimeter = "|";
	*/
	$errm = array();
	while ($line = fgetcsv($handle,4096,$delimeter)){
		$code = '';
		$r1 = array();
		
		if($import_format==1){	// default
			$code = $sku_item_code = trim($line[0]);
			$link_code = trim($line[1]);
			$qty = trim($line[2]);
			
			if ($sku_item_code=='' && $link_code){
				$q0=$con->sql_query("select id, sku_item_code from sku_items where link_code=".ms($link_code)." limit 1");
				$r0= $con->sql_fetchrow($q0);
				$con->sql_freeresult($q0);
				if (!$r0){
					$invalid_link_code[]=$link_code;
					continue;
				}
				else
					$sku_item_code = $r0['sku_item_code'];
			}
			
			if(preg_match('/^2[0-9]*$/', $sku_item_code) && strlen($sku_item_code) > 12){
				$sku_item_code_12 = substr($sku_item_code, 0, 12);
				$filter = " or sku_item_code = ".ms($sku_item_code_12);
			}
			
			$q1=$con->sql_query("select id from sku_items where (sku_item_code=".ms($sku_item_code)." or mcode=".ms($sku_item_code).$filter.")");
			$r1= $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			
			// item not in SKU_ITEMS
			if (!$r1){
				$invalid_sku_item_code[]=$sku_item_code;
				continue;
			}
		}elseif($import_format==2){	// use grn barcode
			$code = trim($line[0]);
			if (preg_match("/^00/", $code))	// form ARMS' GRN barcoder
			{
				$sku_item_id=mi(substr($code,0,8));
				$qty = mi(substr($code,8,4));
				$sql = "select id from sku_items where id = ".mi($sku_item_id);
			}
			else	// from ATP GRN Barcode, try to search the link-code 
			{
				$linkcode=substr($code,0,7);
				$qty = mi(substr($code,7,5));
				$sql = "select id from sku_items where link_code = ".ms($linkcode);
			}
			$q1 = $con->sql_query($sql);
			$r1 = $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
		}elseif($import_format==3){ // standard import
			$code = trim($line[0]);
			$qty = trim($line[1]);
			
			if(preg_match('/^2[0-9]*$/', $code) && strlen($code) > 12){
				$code_12 = substr($code, 0, 12);
				$filter = " or sku_item_code = ".ms($code_12);
			}
			
			$q1=$con->sql_query("select id from sku_items where (sku_item_code=".ms($code)." or mcode=".ms($code)." or link_code=".ms($code).$filter.")");
			$r1= $con->sql_fetchrow($q1);
			$con->sql_freeresult($q1);
			
			// item not in SKU_ITEMS
			if (!$r1){
				$invalid_sku_item_code[]=$code;
				continue;
			}
		}
		
		// item not in SKU_ITEMS
		if (!$r1){
			$errm['item'][]= "$code is invalid.";
			continue;
		}
			
		//get the same items from tmp_do_items to total up the qty.			
		$q2=$con->sql_query("select id, pcs from tmp_do_items where do_id=$do_id and branch_id=$branch_id and sku_item_id=$r1[id] and user_id=$sessioninfo[id]");
		$r2= $con->sql_fetchrow($q2);	
		$con->sql_freeresult();
			
		if($r2){
			// existing item
			$pcs=$qty+$r2['pcs'];	
			$con->sql_query("update tmp_do_items set pcs=".mf($pcs)." where id=$r2[id] and user_id=$sessioninfo[id]") or die(mysql_error());	
		}
		else{				
			// new item	
			$q3=$con->sql_query("select sku_items.id as sku_item_id, if(sku_items.artno is null or sku_items.artno='',sku_items.mcode, sku_items.artno) as artno_mcode, uom.id as uom_id, uom.fraction as uom_fraction  
from sku_items 
left join sku on sku_id = sku.id
left join uom on sku_items.packing_uom_id = uom.id
where sku_items.id=$r1[id]");
		    $item = $con->sql_fetchassoc($q3);
			$con->sql_freeresult($q3);
			
			$output_gst = array();
			if($config['enable_gst'] && $form['is_under_gst']){
				$output_gst = get_sku_gst("output_tax", $item['sku_item_id']);
				if($output_gst){
					$item['gst_id'] = $output_gst['id'];
					$item['gst_code'] = $output_gst['code'];
					$item['gst_rate'] = $output_gst['rate'];
				}
			}
			
			$tmp=get_item_price($item['sku_item_id'], $do_branch_id, $form['price_indicate'],$form, $output_gst);
			$item = array_merge($item, $tmp);
			
			$tmp_sell=get_item_selling($item['sku_item_id'], $form['deliver_branch'], $do_branch_id, get_do_date($do_id,$branch_id));
			$item = array_merge($item, $tmp_sell);
			
			$tmp_do_date = date("Y-m-d", strtotime($form['do_date']." +1 day"));
			$tmp_cost = get_sku_item_cost_selling($branch_id, $item['sku_item_id'], $tmp_do_date, array("cost"));
			if($tmp_cost) $item = array_merge($item, $tmp_cost);
	
			$item['pcs']=mf($qty);					    	
			//$item['price_indicate'][$sku_item_id]=$form['price_indicate'];
			//print_r($item);
			
			// stock balance
			$sql = $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".mi($branch_id)." and sku_item_id=".mi($item['sku_item_id']));
			$item['stock_balance1'] = $con->sql_fetchfield('qty');
			$con->sql_freeresult($sql);
			
			// stock balance 2
			$sql = $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".mi($do_branch_id)." and sku_item_id=".mi($item['sku_item_id']));
			$item['stock_balance2'] = $con->sql_fetchfield('qty');
			$con->sql_freeresult($sql);
			
			insert_tmp_item($item, $do_id);
		}
	}
	
	if($form['do_type'] == 'transfer' && $config['consignment_modules'] && $config['masterfile_branch_region'] && $config['consignment_multiple_currency']){
		$sql = $con->sql_query("select id, code, description, report_prefix, deliver_to, region from branch where id = ".mi($do_branch_id));
		$branch_info = $con->sql_fetchassoc($sql);
		$con->sql_freeresult($sql);
		$currency_code = $config['masterfile_branch_region'][$branch_info['region']]['currency'];
		
		if(trim($currency_code)){
			$r['currency_code'] = strtoupper(trim($currency_code));
			$sql1 = $con->sql_query("select exchange_rate from consignment_forex where currency_code = ".ms($r['currency_code']));
			$cf = $con->sql_fetchassoc($sql1);
			$con->sql_freeresult($sql1);
			
			if($cf) $_REQUEST['exchange_rate'] = $cf['exchange_rate'];
		}
	}
		
	if(count($invalid_link_code)>0)
		$smarty->assign("errm_link_code", $invalid_link_code);
	if(count($invalid_sku_item_code)>0)
		$smarty->assign("errm_sku_item_code", $invalid_sku_item_code);	
	if($errm)	$smarty->assign('errm', $errm);
	return $do_id;	
}

function do_create_from_po($id, $branch_id){
	global $con, $LANG, $smarty, $config, $sessioninfo;
	
	if ($id==0) {
		$id = time();
		if($id <= $_SESSION['do_last_create_time']) {$id = $_SESSION['do_last_create_time']+1;}
		$_SESSION['do_last_create_time'] = $id;
	}
	$form=$_REQUEST;
	if(!isset($form['branch_id']))	$form['branch_id'] = $branch_id;
	
	$po_no=ms(trim($form['po_no']));
	
	$po_multi_deliver_to = array();
	if(!$config['do_create_and_use_branch_from_po']) $po_filter = "and po_option = 3";
	$con->sql_query("select * from po where po_no = $po_no $po_filter");
	if ($con->sql_numrows()>0){
		$r = $con->sql_fetchrow();
		$deliver_to  = unserialize($r['deliver_to']);
		if(count($deliver_to) > 1){
			$po_multi_deliver_to = unserialize($r['deliver_to']);
			$bcount = count($r['deliver_to']);
		}else{
			$po_multi_deliver_to = array($r['po_branch_id']);
			$bcount = 1;
		}
	}else{
        $po_multi_deliver_to = array($_REQUEST['po_do_branch_id']);
        $bcount = 1;
	}
	unset($_REQUEST['do_branch_id']);
	unset($form['do_branch_id']);
	//$smarty->assign('po_multi_deliver_to',$po_multi_deliver_to);
 	$_REQUEST['deliver_branch'] = $form['deliver_branch'] = $po_multi_deliver_to;
	//$_REQUEST['po_multi_deliver_to'] = $po_multi_deliver_to;
	$_REQUEST['do_branch_id'] = $_REQUEST['po_do_branch_id'];
	
	//$smarty->assign('deliver_to',$deliver_to);
	
	if($config['enable_gst']){
		$form['is_under_gst'] = check_do_gst_status($form);
	}
	
	$dopo = array();
    $q0=$con->sql_query("select * from do where po_no=$po_no and active and status<>4");
	while($r0 = $con->sql_fetchassoc($q0)){
		$dopo[] = $r0['do_branch_id'];
	}
	$con->sql_freeresult($q0);
	$smarty->assign("do_branch_id",$dopo);
	
	//if($dopo && count($dopo) == $bcount)
	if(false)
		js_redirect(sprintf($LANG['DO_USED_PO'],$form['po_no']), "/do.php?page=$form[do_type]&");
	else{
        $q1=$con->sql_query("select * from po where po_no=$po_no and approved=1");
		$r1 = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($config['do_approval_by_department']){   // copy department id if got config DO approval by dept
			$_REQUEST['dept_id'] = $r1['department_id'];
		}

		if (!$r1)
			js_redirect(sprintf($LANG['GRR_PO_NOT_FOUND'],$po_no), "/do.php");
		elseif (!$r1['active'])
			js_redirect(sprintf($LANG['GRR_PO_INACTIVE'],$po_no), "/do.php");
		elseif ($r1['delivered'] && !$r1['partial_delivery'])
			js_redirect(sprintf($LANG['DO_PO_DELIVERED']), "/do.php");
		/*elseif ($bcount > 1 && $r1['po_branch_id'] && $r1['po_branch_id'] != $_REQUEST['po_do_branch_id'])
			js_redirect(sprintf($LANG['DO_PO_BRANCH_DIFFERENT'], "Delivery Branch not existed in PO"), "/do.php");*/
		elseif (!$config['do_create_and_use_branch_from_po'] && $bcount > 1 && $form['po_do_branch_id'] && !in_array($_REQUEST['po_do_branch_id'], $form['deliver_branch']))
			js_redirect(sprintf($LANG['DO_PO_BRANCH_DIFFERENT'], "Delivery Branch not existed in PO"), "/do.php");
		elseif (!$config['do_create_and_use_branch_from_po'] && $_REQUEST['po_do_branch_id'] == $sessioninfo['branch_id'])
			js_redirect(sprintf($LANG['DO_PO_BRANCH_DIFFERENT'], "Cannot delivery to same branch"), "/do.php");
		else{
			$po_id=$r1['id'];
			$po_branch_id=$r1['branch_id'];
			
			$item=array();
        	$q2=$con->sql_query("select sku_item_id, artno_mcode, order_uom_id as uom_id, qty as ctn, qty_loose as pcs, order_price as po_cost, uom.fraction as uom_fraction
			,qty_loose_allocation as pcs_allocation, qty_allocation as ctn_allocation
from po_items 
left join uom on uom.id=po_items.order_uom_id  
where po_id=$po_id and branch_id=$po_branch_id
order by po_items.id");
			while ($item = $con->sql_fetchassoc($q2)){
				// get gst
				$output_gst = array();
				if($config['enable_gst'] && $form['is_under_gst']){
					$output_gst = get_sku_gst("output_tax", $sku_item_id);
						if($output_gst){
							$item['gst_id'] = $output_gst['id'];
							$item['gst_code'] = $output_gst['code'];
							$item['gst_rate'] = $output_gst['rate'];
						}
				}
			
				$item['price_indicate'] = $form['price_indicate'];
				$tmp=get_item_price($item['sku_item_id'],$branch_id, $item['price_indicate'],$form, $output_gst);
				if(!$tmp['cost_price']) $item['cost_price']=$item['po_cost'];
				else $item['cost_price']=$tmp['cost_price']*$item['uom_fraction'];

				if(isset($tmp['display_cost_price_is_inclusive']))	$item['display_cost_price_is_inclusive'] = $tmp['display_cost_price_is_inclusive'];
				if(isset($tmp['display_cost_price']))	$item['display_cost_price'] = $tmp['display_cost_price'];
				
				//print_r($form);
				//die("get_item_selling($item[sku_item_id], $form[deliver_branch], $form[do_branch_id])");
				
				//$tmp_sell=get_item_selling($item['sku_item_id'], $form['deliver_branch'], $form['do_branch_id']);
				$tmp_sell=get_item_selling($item['sku_item_id'], "", $form['deliver_branch'], get_do_date($id,$branch_id));
				if($tmp_sell) $item = array_merge($item, $tmp_sell);

				$tmp_do_date = date("Y-m-d", strtotime($form['do_date']." +1 day"));
				$tmp_cost = get_sku_item_cost_selling($branch_id, $item['sku_item_id'], $tmp_do_date, array("cost"));	
				if($tmp_cost) $item['cost'] = $tmp_cost['cost'];
				unset($tmp_cost);
				
				// stock balance
				$sql = $con->sql_query("select sku_item_id, qty from sku_items_cost where branch_id=".mi($branch_id)." and sku_item_id=".mi($item['sku_item_id']));
				$item['stock_balance1'] = $con->sql_fetchfield('qty');
				$con->sql_freeresult($sql);
				
				// stock balance 2
				$sql = $con->sql_query("select sku_item_id, qty, branch_id from sku_items_cost where branch_id in (".join(",", $form['deliver_branch']).") and sku_item_id=".mi($item['sku_item_id']));
				
				while($sb = $con->sql_fetchassoc($sql)){
					if(!$config['do_create_and_use_branch_from_po'] || count($form['deliver_branch']) == 1){
						$item['stock_balance2'] = $sb['qty'];
					}else{
						$item['stock_balance2_allocation'][$sb['branch_id']] = $sb['qty'];
					}
				}
				$con->sql_freeresult($sql);
				
				$ctn_allocation = unserialize($item['ctn_allocation']);
				$pcs_allocation = unserialize($item['pcs_allocation']);
				if(!$config['do_create_and_use_branch_from_po'] || count($form['deliver_branch']) == 1){
					if($ctn_allocation[$_REQUEST['po_do_branch_id']]) $item['ctn'] = $ctn_allocation[$_REQUEST['po_do_branch_id']];
					if($pcs_allocation[$_REQUEST['po_do_branch_id']]) $item['pcs'] = $pcs_allocation[$_REQUEST['po_do_branch_id']];
				}else{
					if($ctn_allocation) $item['ctn_allocation'] = serialize($ctn_allocation);
					$item['pcs_allocation'] = $pcs_allocation;
				}
				insert_tmp_item($item, $id);
			}
			$con->sql_freeresult($q2);
		}
	}
	
	if(!$config['do_create_and_use_branch_from_po']){
		unset($_REQUEST['deliver_branch'], $form['deliver_branch']);
	}
	
	//print_r($form);
	return $id;
}

function do_delete($id, $branch_id){
	global $con, $sessioninfo;
	
	check_must_can_edit($branch_id, $id);   // check available
	
	$form = $_REQUEST;
	
    if(!$type) $type='delete';
    if(!$status) $status=4;    
	$reason=ms($form['reason']);
	
	$upd = array();
	$upd['cancelled_by'] = $sessioninfo['id'];
	$upd['reason'] = $form['reason'];
	$upd['status'] = $status;
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	
    $con->sql_query("update do set ".mysql_update_by_field($upd)." where id=$id and branch_id=$branch_id");
    
    $con->sql_query("delete from tmp_do_items where do_id=$id and branch_id=$branch_id and user_id=$sessioninfo[id]");

	log_br($sessioninfo['id'], 'DELIVERY ORDER', $id, "DO Cancel Branch ID#$branch_id, ID#$id");

	if($form['create_type']==4){
        $con->sql_query("select * from sales_order where order_no=".ms($form['ref_no']));
		$sales_order = $con->sql_fetchrow();
		$con->sql_freeresult();
		if($sales_order){
            update_sales_order_do_qty($sales_order['id'], $sales_order['branch_id'], $sales_order);
		}
	}	
    header("Location: /do.php?page=$form[do_type]&t=$type&save_id=$id&save_do_no=$form[do_no]&");
}

function do_ajax_refresh_cost($do_id, $branch_id){
	global $con, $smarty, $config, $gst_list;
	
	$form = $_REQUEST;
	$price_indicate=$form['price_indicate'];
	
	save_tmp_items();
	
	// use deliver to cost
	if($config['consignment_modules'] && $config['cm_use_deliver_branch_sp'])	$sp_bid = $_REQUEST['do_branch_id'] ? $_REQUEST['do_branch_id'] : $branch_id;
	else $sp_bid = $branch_id; // use own cost
	
	$q1 = $con->sql_query("select tdi.*, uom.fraction as uom_fraction
                          from tmp_do_items tdi
                          left join uom on uom.id=tdi.uom_id
                          where tdi.do_id=$do_id and tdi.branch_id = $branch_id");
	while($r1=$con->sql_fetchassoc($q1)){	
		// get gst
		$use_gst = array();
		// GST
		if($config['enable_gst'] && $form['is_under_gst']){
			if($_REQUEST['is_special_exemption']){
				$use_gst = get_special_exemption_gst();
				$is_special_exemption = true;
			}else{
				$output_gst = get_sku_gst("output_tax", $sku_item_id);
				if($output_gst){
					$use_gst = $output_gst;
				}
			}
			
			if(!$use_gst){
				$use_gst = $gst_list[0];
			}
			
			$r1['gst_id'] = $use_gst['id'];
			$r1['gst_code'] = $use_gst['code'];
			$r1['gst_rate'] = $use_gst['rate'];	
		}
		
		$update=get_item_price($r1['sku_item_id'], $sp_bid, $price_indicate,$form, $use_gst, $is_special_exemption);

		$new_display_cost = $update['display_cost_price']*$r1['uom_fraction'];
		$new_cost_price=$update['cost_price']*$r1['uom_fraction'];
		$new_foreign_cost_price=$update['foreign_cost_price']*$r1['uom_fraction'];
		if(!$new_foreign_cost_price) $new_foreign_cost_price = $new_cost_price;
		$upd['cost_price'] = round($new_cost_price, $config['global_cost_decimal_points']);
		$upd['foreign_cost_price'] = round($new_foreign_cost_price, $config['global_cost_decimal_points']);
		$upd['display_cost_price_is_inclusive'] = $update['display_cost_price_is_inclusive'];
		$upd['display_cost_price'] = round($new_display_cost, $config['global_cost_decimal_points']);
		$con->sql_query("update tmp_do_items set ".mysql_update_by_field($upd)." where id = $r1[id] and branch_id=$branch_id");
	}
	$con->sql_freeresult($q1);

	$smarty->assign('do_type',$_REQUEST['do_type']);
	$smarty->assign('show_discount',$_REQUEST['show_discount']);
	$do_items = load_do_items($do_id, $branch_id,$form, true);
	//print_r($do_items);
	$smarty->assign("do_items", $do_items);
	$smarty->assign("form", $form);
	$smarty->display("do.new.sheet.tpl");
}

function do_ajax_add_grn_barcode_item($id, $branch_id){
	do_ajax_add_item($id, $branch_id, true);
}

function do_ajax_add_item($id, $branch_id, $is_grn_barcode=false, $r=''){
	global $con, $smarty, $LANG, $config, $sessioninfo, $gst_list, $appCore;
	
	if($config['enable_gst'])	construct_gst_list();
	
	if($r) $form = $r;
	else $form=$_REQUEST;
	
	$do_branch_id = mi($form['do_branch_id']);
	$is_open_item = $_REQUEST['oi'];
	
	if($is_open_item) $sku_item_id = 0;
	
	if (!$is_grn_barcode){
		$sku_item_id=mi($form['sku_item_id']);
		if($r) $qty_pcs=$form['qty_pcs'];
		else $qty_pcs='';
	}else{
		$grn_barcode = trim($_REQUEST['grn_barcode']);
		if ($grn_barcode){
			$sku_info = get_grn_barcode_info($grn_barcode,true);

			if ($sku_info['sku_item_id']){
				// is inactive item
				$q1 = $con->sql_query("select active from sku_items where id = ".mi($sku_info['sku_item_id'])." limit 1");
				$tmp_si_info = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);

				if(!$tmp_si_info['active']){
					fail($LANG['PO_ITEM_IS_INACTIVE']);
				}
			
				$sku_item_id = $sku_info['sku_item_id'];
				if(!$form['deliver_branch']) $qty_pcs = mf($sku_info['qty_pcs']);
				$selling_price = mf($sku_info['selling_price']);
				if(isset($sku_info['new_cost_price'])) $cost_price = $sku_info['new_cost_price'];
			}
		}
	}
	
	if(!$r){
		save_tmp_items();
	}
	
	// bom progress goes here
	$bom_ref_num = time();
	$is_bom_package = false;
	$si_info_list = array();
	
	// if got config bom additional type
	if(!$is_open_item && $config['sku_bom_additional_type']){
		// check is bom package or not
		$q1 = $con->sql_query("select si.*, if(si.artno is null or si.artno='',si.mcode, si.artno) as artno_mcode, sku.is_bom
							   from sku_items si
							   join sku on sku.id=si.sku_id
							   where si.id=".mi($sku_item_id));
		$bom_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if($bom_info['is_bom'] && $bom_info['bom_type']=='package'){
			$is_bom_package = true;
			$bom_ref_num++;
			
			$q1 = $con->sql_query("select bi.sku_item_id as sid,bi.qty from bom_items bi where bi.bom_id=".mi($sku_item_id)." order by bi.sku_item_id");
			$first_bom = 1;
			while($r1 = $con->sql_fetchassoc($q1)){
				$tmp_sid = mi($r1['sid']);
				$pcs = $r1['qty'] * $config['sku_bom_package_default_qty_in_docs'];
				
				$si_info_list['list'][] = array(
					'sid'=> $tmp_sid,
					'pcs'=> $pcs,
					'bom_id' => $sku_item_id,
					'bom_ref_num' => $bom_ref_num,
					'bom_qty_ratio' => $r1['qty'],
					'first_bom' => $first_bom
				);
				
				$first_bom = 0;
			}
			$con->sql_freeresult($q1);
		}
	}
	
	if(!$is_bom_package){
		$si_info_list['list'][] = array(
			'sid'=>$sku_item_id,
			'pcs'=>$qty_pcs
		);
	}

	$smarty->assign('do_type',$_REQUEST['do_type']);
	$smarty->assign('bid',$_REQUEST['bid']);
	$smarty->assign('show_discount',$_REQUEST['show_discount']);
	$smarty->assign("form", $form);
	
	if(!$is_open_item){ // it is valid sku item
		if(!$si_info_list['list'])	fail($LANG['DO_NO_ITEM_SELECTED']);	// no item?
		
		foreach($si_info_list['list'] as $tmp_di){
			$item = $filter = $err_msg = array();
			$sid = $tmp_di['sid'];
			$qty_pcs = $tmp_di['pcs'];
			$filter[] = "sku_items.id=".mi($sid);
			if($config['do_must_check_dept'])	$filter[] = " c.department_id in (".join(",",array_keys($sessioninfo['departments'])).")";
			
			$filter = "where ".join(' and ', $filter);
			$q1=$con->sql_query("select sku_items.sku_item_code, sku_items.description as description, sku_items.sku_id,
								if(sku_items.artno is null or sku_items.artno='',sku_items.mcode, sku_items.artno) as artno_mcode, 
								sku_items.id as sku_item_id, 1 as uom_id, 1 as uom_fraction, u.code as master_uom_code, sku_items.link_code,
								u.fraction as master_uom_fraction, sku_items.packing_uom_id as master_uom_id, sku.have_sn, 
								sku_items.doc_allow_decimal, sku_items.sn_we, sku_items.sn_we_type, sku_items.artno, sku_items.mcode, sic.qty as sb_qty,
								sku_items.use_rsp, if(sip.price is null, sku_items.rsp_discount, sip.rsp_discount) as rsp_discount,sku_items.rsp_price
								from sku_items
								left join sku_items_price sip on sip.sku_item_id =sku_items.id and sip.branch_id= ".mi($sessioninfo['branch_id'])."
								left join sku on sku_id = sku.id
								left join uom u on u.id = sku_items.packing_uom_id
								left join category c on c.id=sku.category_id
								left join sku_items_cost sic on sic.sku_item_id = sku_items.id and sic.branch_id = ".mi($sessioninfo['branch_id'])."
								$filter");
			$item = $con->sql_fetchassoc($q1);
			$item['bom_id'] = $tmp_di['bom_id'];
			$item['bom_ref_num'] = $tmp_di['bom_ref_num'];
			$item['bom_qty_ratio'] = $tmp_di['bom_qty_ratio'];
			$con->sql_freeresult($q1);
			
			// need to load the BOM original sku information for further usage
			if($item['bom_ref_num'] && $tmp_di['first_bom']){
				$item['is_first_bom'] = 1;
				$item['bom_parent_si_code'] = $bom_info['sku_item_code'];
				$item['bom_parent_si_desc'] = $bom_info['description'];
				$item['bom_parent_si_artno_mcode'] = $bom_info['artno_mcode'];
			}
			
			if(ceil($qty_pcs) != $qty_pcs && !$item['doc_allow_decimal']){ // is decimal points qty
				$err_msg[] = "* SKU Item [".$item['sku_item_code']."] is not decimal points item, whereas qty auto set to empty.";
				$qty_pcs = "";
			}
			
			if (!$item) {
				fail(sprintf($LANG['DO_INVALID_ITEM'], $sid));
			}
			
			// found got turn on config to check and prevent to add zero or negative stock balance
			if($config['do_block_zero_stock_bal_items'] && $item['sb_qty'] <= 0){
				fail(sprintf($LANG['DO_ZERO_STOCK_BAL_ITEM'], $item['sku_item_code']));
			}
			
			if ($form['deliver_branch'])
			{
				foreach($form['deliver_branch'] as $bid)
				{
					if(!$r){
						$item['pcs_allocation'][$bid] = $qty_pcs;
					}
					
					// stock balance 2
					$sql = "select sku_item_id,qty from sku_items_cost where branch_id=".mi($bid)." and sku_item_id=".mi($sid);
					$con->sql_query($sql) or die(mysql_error());
					$item['stock_balance2_allocation'][$bid]=$con->sql_fetchfield('qty');
					
					//parent stock balance 2
					if($config['show_parent_stock_balance']) {
						$sql2 = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
												from sku_items si
												left join sku_items_cost sic on sic.branch_id=".mi($bid)." and sic.sku_item_id=si.id
												left join uom on uom.id=si.packing_uom_id
												where si.sku_id=".mi($item['sku_id']));
					
						$parent_stock_balance2_allocation = 0;
						while($data = $con->sql_fetchassoc($sql2)) {
							$parent_stock_balance2_allocation += $data['parent_stock_balance'];
						}
						$item['parent_stock_balance2_allocation'][$bid] = $parent_stock_balance2_allocation;
						$con->sql_freeresult($sql2);
					}
				}	
			}else{
				// single branch
				$item['pcs'] = $qty_pcs;
				
				// stock balance 2
				$sql = "select sku_item_id,qty from sku_items_cost where branch_id=".mi($do_branch_id)." and sku_item_id=".mi($sid);
				$con->sql_query($sql) or die(mysql_error());
				$item['stock_balance2']=$con->sql_fetchfield('qty');
				
				//parent stock balance 2
				if($config['show_parent_stock_balance']) {
					$sql2 = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
											from sku_items si
											left join sku_items_cost sic on sic.branch_id=".mi($do_branch_id)." and sic.sku_item_id=si.id
											left join uom on uom.id=si.packing_uom_id
											where si.sku_id=".mi($item['sku_id']));
				
					$parent_stock_balance2 = 0;
					while($data= $con->sql_fetchassoc($sql2)) {
						$parent_stock_balance2 += $data['parent_stock_balance'];
					}
					$item['parent_stock_balance2'] = $parent_stock_balance2;
					$con->sql_freeresult($sql2);
				}
			}
			$item['price_indicate'][$sid]=$form['price_indicate'];

			if ($_REQUEST['do_type'] == 'credit_sales')	$price_indicator="Credit Sales DO";	
			$item['price_indicator']=$config['sku_multiple_selling_price'][$form['price_indicate']] ? $config['sku_multiple_selling_price'][$form['price_indicate']] : $price_indicator;
			$item['price_indicate']=$form['price_indicate'];
			
			// GST
			if($config['enable_gst'] && $form['is_under_gst']){
				if($_REQUEST['is_special_exemption']){
					$use_gst = get_special_exemption_gst();
					$is_special_exemption = true;
				}else{
					$output_gst = get_sku_gst("output_tax", $sid);
					if($output_gst){
						$use_gst = $output_gst;
					}
				}
				
				if(!$use_gst){
					$use_gst = $gst_list[0];
				}
				
				$item['gst_id'] = $use_gst['id'];
				$item['gst_code'] = $use_gst['code'];
				$item['gst_rate'] = $use_gst['rate'];
			}
			
			if($config['consignment_modules'] && $config['cm_use_deliver_branch_sp']){
				$sp_bid = $do_branch_id;
			}else $sp_bid = $branch_id;
			
			$tmp=get_item_price($sid, $sp_bid, $form['price_indicate'],$form, $use_gst, $is_special_exemption);
			
			$item = array_merge($item, $tmp);

			$tmp_sell=get_item_selling($sid, $form['deliver_branch'], $form['do_branch_id'], $form['do_date'], $selling_price);
			//print_r($tmp_sell);

			$item = array_merge($item, $tmp_sell);

			$tmp_do_date = date("Y-m-d", strtotime($form['do_date']." +1 day"));
			$tmp_cost = get_sku_item_cost_selling($branch_id, $sid, $tmp_do_date, array("cost"));
			if($tmp_cost) $item = array_merge($item, $tmp_cost);

			if(isset($cost_price)){
				if(!$cost_price){
					$err_msg[] = "* Unable to find Trade Discount for SKU Item [".$item['sku_item_code']."], whereas cost auto set to empty.";
				}
				$item['cost_price'] = $cost_price;
			}

			$ret=insert_tmp_item($item,$id,true); // allow duplicate if GRN Barcode

			// need to check bom items????
			if ($ret==-2){
				fail(sprintf($LANG['DO_MAX_ITEM_CANT_ADD'], $config['do_set_max_items']));
			}elseif($ret['item_existed']){
				$tmp_item = array();
				$tmp_item[0]['item_existed'] = 1;
				$tmp_item[0]['item_id'] = $ret['id'];
				$tmp_item[0]['bom_ref_num'] = $ret['bom_ref_num'];
				print json_encode($tmp_item);
				exit;
				//fail(sprintf($LANG['DO_ITEM_ALREADY_IN_PO']));
			}

			$item['selling_price_allocation']=unserialize($item['selling_price_allocation']);
			$item['price_type']=unserialize($item['price_type']);
			$item['stock_balance2_allocation'] = unserialize($item['stock_balance2_allocation']);
			$item['pcs_allocation'] = unserialize($item['pcs_allocation']);

			// stock balance 1
			$sql = "select sku_item_id,qty from sku_items_cost where branch_id=".mi($branch_id)." and sku_item_id=".mi($sid);
			$con->sql_query($sql) or die(mysql_error());
			$item['stock_balance1']=$con->sql_fetchfield('qty');
			
			//parent stock balance 1
			if($config['show_parent_stock_balance']) {
				$sql2 = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
										from sku_items si
										left join sku_items_cost sic on sic.branch_id=".mi($branch_id)." and sic.sku_item_id=si.id
										left join uom on uom.id=si.packing_uom_id
										where si.sku_id=".mi($item['sku_id']));
			
				$parent_stock_balance1 = 0;
				while($data2 = $con->sql_fetchassoc($sql2)) {
					$parent_stock_balance1 += $data2['parent_stock_balance'];
				}
				$item['parent_stock_balance1'] = $parent_stock_balance1;
				$con->sql_freeresult($sql2);
			}

			if($config['enable_sn_bn']){
				// batch no expired notification
				if(!$config['batch_no_days_notify']) $config['batch_no_days_notify'] = 30;

				$sql = $con->sql_query("select batch_no, expired_date from sku_batch_items where sku_item_id = ".mi($sid)." and branch_id = ".mi($branch_id));
				$sbi = $con->sql_fetchassoc($sql);
				$con->sql_freeresult($sql);

				if($sbi['batch_no']){
					$time_left = 0;
					$bn_notify = "";
					$curr_time = strtotime(date("Y-m-d"));
					$batch_expired_time = strtotime($sbi['expired_date']);

					if($curr_time >= $batch_expired_time){
						$days_expired = mi(($curr_time-$batch_expired_time)/86400);
						if($days_expired == 0) $days_expired = "today";
						else $days_expired .= " day(s)";
						$bn_notify = sprintf($LANG['DO_BN_NOTIFY'], "has expired by ".$days_expired, $sbi['batch_no'], $sbi['expired_date']);
					}

					$notify_date = date("Y-m-d", mktime(0,0,0,date("m"), date("d")+$config['batch_no_days_notify'], date("Y")));
					$time_left = strtotime($notify_date) - $batch_expired_time;
					$days_remain = mi(($batch_expired_time-$curr_time)/86400);

					if(!$bn_notify && $time_left > 0){
						$bn_notify = sprintf($LANG['DO_BN_NOTIFY'], "will expire in ".$days_remain." day(s)", $sbi['batch_no'], $sbi['expired_date']);
					}
					
					$item['description'] .= " (Batch No: ".$sbi['batch_no']."  Expired Date: ".$sbi['expired_date'].")";
				}
			}
			if($config['do_show_photo']){
				$sku_item_photo = $appCore->skuManager->getSKUItemPhotos($item['sku_item_id']);
				if(count($sku_item_photo['photo_list'])> 0){
					$item['photo'] = $sku_item_photo['photo_list'][0];
				}
			}
			$smarty->assign("item", $item);	
			
			if(!$form['currency_code'] || $form['currency_code'] == $config["arms_currency"]["symbol"]) $smarty->assign("hide_currency_field", 1);
			
			$arr = array();
			if($form['do_no'] || $form['do_branch_id'] || $form['open_info']['name']||$form['do_type']=='credit_sales')
				$tpl = $smarty->fetch("do.new.do_row.single_branch.tpl");
			else
				$tpl = $smarty->fetch("do.new.do_row.tpl");

			$temp = array();
			$temp['rowdata'] = "<tr id=titem".$item['id'].">".$tpl."</tr>";
			
			// if found have serial no
			if($item['have_sn'] != 0) $temp['sn'] = $smarty->fetch("do.sn.new.tpl");

			// if found got batch no notify
			if($bn_notify) $temp['bn_notify'] = $bn_notify;

			if($err_msg) $temp['error'] = join("\n", $err_msg);
			
			$row_item[] = $temp;
		}
	}else{ // it is open item
		$item = array();
		$use_gst = $gst_list[0];
		$item['oi'] = $is_open_item;
		
		$item['gst_id'] = $use_gst['id'];
		$item['gst_code'] = $use_gst['code'];
		$item['gst_rate'] = $use_gst['rate'];
		
		$ret=insert_tmp_item($item, $id, true); // allow duplicate if GRN Barcode
		
		$smarty->assign("item", $item);
		
		if($form['do_no'] || $form['do_branch_id'] || $form['open_info']['name'] || $form['do_type']=='credit_sales')
			$tpl = $smarty->fetch("do.new.do_row.single_branch.tpl");
		else
			$tpl = $smarty->fetch("do.new.do_row.tpl");
		
		$temp = array();
		$temp['rowdata'] = "<tr id=titem".$item['id'].">".$tpl."</tr>";
		
		$row_item[] = $temp;
	}
				    	
	//print "get_item_price($sku_item_id, $branch_id, $form[price_indicate]);";
	//exit;

	print json_encode($row_item);
	//$arr[] = array("id" => $item['id'], "rowdata" => $rowdata);			    	
	//header('Content-Type: text/xml');
	//print array_to_xml($arr);
}

function get_item_price($sku_item_id, $branch_id, $price_indicate,$form, $output_gst = array(), $is_special_exemption = false){
	global $appCore;
	
	return $appCore->doManager->getItemPrice($sku_item_id, $branch_id, $price_indicate, $form, $output_gst, $is_special_exemption);
}

function insert_tmp_item(&$r,$do_id, $check=false){
	global $con, $sessioninfo, $branch_id, $config;	

	$r['do_id']=$do_id;
	$r['branch_id']=$branch_id;
	$r['user_id']=$sessioninfo['id'];
	$r['selling_price_allocation']=serialize($r['selling_price_allocation']);
	$r['price_type']=serialize($r['price_type']);
	$r['selling_price']=doubleval($r['selling_price']);
	$r['stock_balance2_allocation'] = serialize($r['stock_balance2_allocation']);
	$r['pcs_allocation'] = serialize($r['pcs_allocation']);
	//$r['price_indicate'] = serialize($r['price_indicate']);
	
	// check total items if created PO
	if($config['do_set_max_items'] && $check){
		$con->sql_query("select count(*) from tmp_do_items where do_id = $r[do_id] and branch_id = $r[branch_id] and user_id = $sessioninfo[id]");
		$t = $con->sql_fetchrow();
		if ($t[0] >= $config['do_set_max_items']){
	     	return -2;
		}		
	}
	
	if($check && $r['sku_item_id'] != 0){
	    if(!$config['do_item_allow_duplicate']){
			$di_info = array();
            $q1 = $con->sql_query("select * from tmp_do_items where do_id = $r[do_id] and user_id = $sessioninfo[id] and sku_item_id=$r[sku_item_id] limit 1");
			$di_info = $con->sql_fetchassoc($q1);
			$di_info['item_existed'] = 1;
			$con->sql_freeresult($q1);

        	if($di_info['id']){
				return $di_info;
			}
		}
	}
	
    $con->sql_query("insert into tmp_do_items " . mysql_insert_by_field($r, array('do_id', 'branch_id', 'user_id', 'sku_item_id', 'artno_mcode','uom_id','cost_price','foreign_cost_price','ctn','pcs',
	'po_cost', 'cost', 'selling_price_allocation', 'selling_price','price_type','stock_balance1','stock_balance2','stock_balance2_allocation','price_no_history','pcs_allocation','ctn_allocation',
	'price_indicate', 'gst_id', 'gst_code', 'gst_rate','display_cost_price_is_inclusive','display_cost_price', 'bom_id', 'bom_ref_num', 'bom_qty_ratio')));

 	$r['id'] = $con->sql_nextid();
 	return $r['id'];
}

function save_tmp_items($alter_to_one_branch=0){
	global $con, $branch_id, $config;
	$form=$_REQUEST;
	
	if($form['uom_id']){
		foreach($form['uom_id'] as $k=>$v){
			$update = array();
			if($alter_to_one_branch){
				$update['ctn']=mf($form['qty_ctn'][$k][$alter_to_one_branch]);
				$update['pcs']=mf($form['qty_pcs'][$k][$alter_to_one_branch]);
				$update['selling_price']=doubleval($form['selling_price'][$k][$alter_to_one_branch]);
				//$update['price_type']=$form['price_type'][$k][$alter_to_one_branch];
				$update['selling_price_allocation']='';
				$update['ctn_allocation']='';
				$update['pcs_allocation']='';
			}
			else{
				if($form['deliver_branch']){
					$update['ctn_allocation']=serialize($form['qty_ctn'][$k]);
					$update['pcs_allocation']=serialize($form['qty_pcs'][$k]);
					//$update['selling_price_allocation']=serialize($form['selling_price'][$k]);
				}
				else{
					$update['ctn']=mf($form['qty_ctn'][$k]);
					$update['pcs']=mf($form['qty_pcs'][$k]);
					//$update['selling_price']=doubleval($form['selling_price'][$k]);
					$update['ctn_allocation']='';
					$update['pcs_allocation']='';
				}			
			}
			if($form['doi_description'][$k]){
				$update['description']=$form['doi_description'][$k];
				$update['artno_mcode']=$form['artno_mcode'][$k];
			}
			$update['po_cost']=doubleval($form['po_cost'][$k]);	
			$update['cost']=$form['cost'][$k];
			$update['cost_price']=$form['cost_price'][$k];
			if($_REQUEST['exchange_rate'] != 1) $update['foreign_cost_price']=$form['foreign_cost_price'][$k];
			else $update['foreign_cost_price']=$form['cost_price'][$k];
			$update['uom_id']=$form['uom_id'][$k];
			$update['price_type'] =serialize($form['price_type'][$k]);
			$update['stock_balance1'] = $form['stock_balance1'][$k];
			$update['stock_balance2'] = $form['stock_balance2'][$k];
			$update['stock_balance2_allocation'] = serialize($form['stock_balance2_allocation'][$k]);
            $update['parent_stock_balance1'] = $form['parent_stock_balance1'][$k];
			$update['parent_stock_balance2'] = $form['parent_stock_balance2'][$k];
            $update['parent_stock_balance2_allocation'] = serialize($form['parent_stock_balance2_allocation'][$k]);
			$update['price_no_history'] = $form['price_no_history'][$k];
			$update['item_discount'] = $form['item_discount'][$k];
			if($config['do_custom_column']){
				$update['custom_col'] = serialize($form['custom_col'][$k]);
			}
			//$update['price_indicate'] = $form['price_indicate'];
			$update['price_indicate'] = $form['i_price_indicate'][$k];
			
			if($form['sn'][$k]){
				if($form['do_type'] == "transfer"){
					$update['serial_no'] = serialize($form['sn'][$k]);
				}else{
					$sn['nric'] = $form['sn_nric'][$k];
					$sn['name'] = $form['sn_name'][$k];
					$sn['address'] = $form['sn_address'][$k];
					$sn['email'] = $form['sn_email'][$k];
					$sn['cn'] = $form['sn_cn'][$k];
					$sn['we'] = $form['sn_we'][$k];
					$sn['we_type'] = $form['sn_we_type'][$k];
					$sn['sn'] = $form['sn'][$k];
					$update['serial_no'] = serialize($sn);
				}
			}

			if($config['masterfile_enable_sa'] && $form['di_sa'][$k]){
				$di_sa = array();
				$di_sa[$form['di_sa'][$k]] = $form['di_sa'][$k];
				$update['dtl_sa'] = serialize($di_sa);
				unset($di_sa);
			}else $update['dtl_sa'] = "";
			
			// gst
			if($config['enable_gst']){
				$update["gst_id"] = $form["gst_id"][$k];
				$update["gst_code"] = $form["gst_code"][$k];
				$update["gst_rate"] = $form["gst_rate"][$k];
			}
			
			$update['item_discount_amount'] = $form['item_discount_amount'][$k];
			$update['item_discount_amount2'] = $form['item_discount_amount2'][$k];
			
			$update['inv_line_gross_amt'] = $form['inv_line_gross_amt'][$k];
			$update['inv_line_gst_amt'] = $form['inv_line_gst_amt'][$k];
			$update['inv_line_amt'] = $form['inv_line_amt'][$k];
			
			$update['inv_line_gross_amt2'] = $form['inv_line_gross_amt2'][$k];
			$update['inv_line_gst_amt2'] = $form['inv_line_gst_amt2'][$k];
			$update['inv_line_amt2'] = $form['inv_line_amt2'][$k];
			
			$update['line_gross_amt'] = $form['line_gross_amt'][$k];
			$update['line_gst_amt'] = $form['line_gst_amt'][$k];
			$update['line_amt'] = $form['line_amt'][$k];
			
			$update['display_cost_price_is_inclusive'] = $form['display_cost_price_is_inclusive'][$k];
			$update['display_cost_price'] = $form['display_cost_price'][$k];
			$update['bom_id'] = $form['bom_id'][$k];
			$update['bom_ref_num'] = $form['bom_ref_num'][$k];
			$update['bom_qty_ratio'] = $form['bom_qty_ratio'][$k];
			
			$con->sql_query("update tmp_do_items set " . mysql_update_by_field($update) . " where id=$k and branch_id=$branch_id");			
		}
	}
}

function load_do_list($t = 0){
	global $con, $sessioninfo, $smarty, $config;
	$where = '';

	if (!$t) $t = intval($_REQUEST['t']);
		 	if(BRANCH_CODE != 'HQ'){
    	$where = "(do.do_branch_id=$sessioninfo[branch_id] or do.branch_id=$sessioninfo[branch_id] ) and ";
	}


  	if($sessioninfo['level']>=9999)
		$owner_check="";
	else
		$owner_check="user_id=$sessioninfo[id] and ";
	$where .= $owner_check;
	
	$start = intval($_REQUEST['s']);
	switch ($t){
	    case 0:
			if ($_REQUEST['search']==''){
				print "<p align=center>I won't search empty string</p>";
				exit;
			}
	        $where .= ' do.active=1 and (do.id = ' . mi($_REQUEST['search']) . ' or do.do_no like '.ms('%'.replace_special_char($_REQUEST['search'])).' or do.po_no like '.ms('%'.replace_special_char($_REQUEST['search'])).' or do.inv_no like '.ms('%'.replace_special_char($_REQUEST['search'])).' or b2.code='.ms($_REQUEST['search']);

			$_REQUEST['search'] = trim($_REQUEST['search']);
			if ($_REQUEST['do_type'] == 'credit_sales') {
				$where .= 'or debtor.code like '.ms('%'.replace_special_char($_REQUEST['search']).'%').' or debtor.description like '.ms('%'.replace_special_char($_REQUEST['search']).'%');
			}
			else {
				$where .= 'or b2.code like '.ms('%'.replace_special_char($_REQUEST['search']).'%');
				$where .= 'or do.open_info like '.ms('%'.replace_special_char($_REQUEST['search']).'%');
				
				$q4 = $con->sql_query('select id from branch where code like '.ms('%'.replace_special_char($_REQUEST['search']).'%'));
				while ($r4 = $con->sql_fetchassoc($q4)) {
					$where .= 'or do.deliver_branch like '.ms('%"'.mi($r4['id']).'"%'); //search within a serialized array
				}
			}
			
			if($config['do_generate_receipt_no']){
				$where .= ' or do.do_receipt_no = '.mi($_REQUEST['search']).')';
			}else $where .= ')';
            $smarty->assign('show_do_printed', 1);
            $smarty->assign('show_inv_printed', 1);
	        break;

		case 1: // show saved DO
		 
         $where .= "do.status = 0 and not do.approved and do.active ";
        	break;

		case 2: // show waiting for approval (and Keep In View)
		    $where .= "(do.status = 1 or do.status = 3) and do.approved = 0 and do.active = 1";
		    break;

		case 3: // show inactive
		   $where .= "(do.status =4 or do.status=5) and do.active = 1";
		    break;

		case 4: // show approved
		    $where .= "do.approved=1 and do.active = 1 and do.checkout = 0";
            $smarty->assign('show_do_printed', 1);
		    break;

		case 5: // show rejected
		    $where .= "do.status = 2 and do.approved = 0 and do.active = 1";
		    break;
		    
		case 6: // show checkout
		    $where .= "do.approved = 1 and do.active = 1 and do.checkout = 1";
		    break;

   		case 7: // search branch
		    if (BRANCH_CODE == "HQ" && $config['consignment_modules']){
				$where .= "do.do_branch_id=".$_REQUEST['search']." or do.deliver_branch like '%\"".replace_special_char($_REQUEST['search'])."\";%'" ;
			}
		    break;

	}

	if (isset($_REQUEST['sz']))
		$sz = intval($_REQUEST['sz']);
	else{
		if (isset($config['document_page_size'])) $sz=$config['document_page_size'];
			else	$sz = 25;
	}

	
	if(isset($_REQUEST['do_type'])){
	    $do_type = $_REQUEST['do_type'];
		$where .= " and do_type=".ms($do_type);
	}
	
	$con->sql_query("select count(*) from do left join branch b2 on do.do_branch_id = b2.id left join debtor on do.debtor_id = debtor.id where $where");
	
	$r = $con->sql_fetchrow();
	$total = $r[0];

	if ($total > $sz){
	    if ($start > $total) $start = 0;
		// create pagination
		$pg = "<b>Goto Page</b> <select onchange=\"list_sel($t,this.value)\">";
		for ($i=0,$p=1;$i<$total;$i+=$sz,$p++){
			$pg .= "<option value=$i";
			if ($i == $start){
				$pg .= " selected";
			}
			$pg .= ">$p</option>";
		}
		$pg .= "</select>";
		$smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
	}
	
	/*
	if($t==4 || $t==6 || $t==0){
		$where.=" group by do.do_no ";
	}
	$q2=$con->sql_query("select do.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1, b2.code as branch_name_2, bah.approvals, user.u as user_name, grr.id as grr_id, grn.id as grn_id, grr_items.id as grr_items_id 
from do 
left join category on do.dept_id = category.id 
left join branch on do.branch_id = branch.id 
left join branch b2 on do.do_branch_id = b2.id 
left join user on user.id = do.user_id 
left join branch_approval_history bah on bah.id = do.approval_history_id and bah.branch_id = do.branch_id 
left join grr_items on grr_items.doc_no = do.do_no and grr_items.branch_id=do.do_branch_id
left join grr on grr.id=grr_items.grr_id and grr.branch_id=do.do_branch_id 
left join grn on grn.grr_id=grr.id and grn.branch_id=do.do_branch_id 
where $where 
order by do.last_update desc limit $start, $sz");
*/

	$q2=$con->sql_query("select do.*, category.description as dept_name, branch.report_prefix as branch_prefix, branch.code as branch_name_1, b2.code as branch_name_2, bah.approvals, user.u as user_name,bah.approval_order_id, 
debtor.code as debtor_code, debtor.description as debtor_description 
from do 
left join category on do.dept_id = category.id 
left join branch on do.branch_id = branch.id 
left join branch b2 on do.do_branch_id = b2.id 
left join user on user.id = do.user_id 
left join branch_approval_history bah on bah.id = do.approval_history_id and bah.branch_id = do.branch_id 
left join debtor on do.debtor_id = debtor.id  
where $where
order by do.last_update desc limit $start, $sz");
	$debtors = $smarty->get_template_vars('debtor');

	while ($r2= $con->sql_fetchrow($q2)){
 		$r2['open_info'] = unserialize($r2['open_info']);	
		$r2['deliver_branch']=unserialize($r2['deliver_branch']);
		if($r2['deliver_branch']){
			foreach ($r2['deliver_branch'] as $k=>$v){
				$q3=$con->sql_query("select code from branch where id=$v");
				$r3 = $con->sql_fetchrow($q3);
				$r2['d_branch']['id'][$k]=$v;
				$r2['d_branch']['name'][$k]=$r3['code'];
			}
		}

		if($r2['debtor_id'] && !$debtors[$r2['debtor_id']]){
			$sql = $con->sql_query("select * from debtor where id = ".mi($r2['debtor_id']));
			$debtor_info = $con->sql_fetchassoc($sql);
			
			if($debtor_info){
				$debtors[$debtor_info['id']] = $debtor_info;
			}
		}
		
		$temp2[]=$r2;
	}
	$do_list=$temp2;
	
	if($debtors) $smarty->assign("debtor", $debtors);
	$smarty->assign("do_type", $do_type);
	$smarty->assign("do_list", $do_list);
	$smarty->display("do.list.tpl");	
}

function load_branches_group($id=0){
	global $con,$smarty;
	// check whether select all or specified group
	if($id>0){
		$where = "where id=".mi($id);
		$where2 = "where bgi.branch_group_id=".mi($id);
	}
	// load header
	$con->sql_query("select * from branch_group $where");
	while($r = $con->sql_fetchassoc()){
		$branches_group['header'][$r['id']] = $r;
	}
	$con->sql_freeresult();
	

	// load items
	$con->sql_query("select bgi.*,branch.code from branch_group_items bgi left join branch on bgi.branch_id=branch.id $where2");
	while($r = $con->sql_fetchrow()){
        $branches_group['items'][$r['branch_group_id']][$r['branch_id']] = $r;
        $branches_group['have_group'][$r['branch_id']] = $r['branch_id'];
	}
	$smarty->assign('branches_group',$branches_group);
	return $branches_group;
}

function multi_add(){
    global $con, $smarty, $sessioninfo, $config;
    
    if($_REQUEST['deliver_branch']){$_SESSION['do_deliver_branch'] = $_REQUEST['deliver_branch'];
	}
    else{    $_SESSION['do_deliver_branch'] = '';}
    	
    $do_id = mi($_REQUEST['do_id']);
    
    $sku_item_id_list = $_REQUEST['sku_item_id_list'];
    $branch_id = mi($sessioninfo['branch_id']);
    
    if (!$config['do_item_allow_duplicate']){
		$filter="and si.id not in (select sku_item_id from tmp_do_items where do_id=$do_id and user_id=$sessioninfo[id])";	
	}
    
    if ($sku_item_id_list){
	        $sql = "select si.id,si.sku_item_code,si.mcode,si.link_code,si.description,si.artno,if(sip.price is null,si.selling_price,sip.price) as price, si.doc_allow_decimal, sic.qty,u.code as master_uom_code,
			if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as discount_code, sku.is_bom, si.bom_type
	from sku_items si
	left join sku_items_price sip on si.id=sip.sku_item_id and sip.branch_id=$branch_id
	left join sku_items_cost sic on si.id=sic.sku_item_id and sic.branch_id=$branch_id
	left join sku on sku.id=si.sku_id
	left join uom u on u.id = si.packing_uom_id
	where si.id in (".join(',',$sku_item_id_list).") $filter
	order by si.description";

		$con->sql_query($sql) or die(mysql_error());

		while($r = $con->sql_fetchrow()){
			$items[$r['id']] = $r;
		}
		$con->sql_freeresult();
	}
	
	if(is_array($config['masterfile_branch_region']) && is_array($config['consignment_multiple_currency'])){
		$con->sql_query("select region from branch where id = ".mi($_REQUEST['do_branch_id']));
		$branch_info = $con->sql_fetchrow();
		$con->sql_freeresult();

		$smarty->assign('currency_code', $config['masterfile_branch_region'][$branch_info['region']]['currency']);
	}
	
	$smarty->assign('items',$items);
	$smarty->display('do.multi_add.tpl');
}

function save_multi_add(){
   	global $con, $smarty, $LANG, $config,$sessioninfo, $branch_id, $gst_list, $appCore;
    
    $form = $_REQUEST;
	
	// load gst list
	if($config['enable_gst'])	construct_gst_list();
	
	//$branch_id = mi($sessioninfo['branch_id']);
    $branch_id = ($form['branch_id']>0)? mi($form['branch_id']) : mi($sessioninfo['branch_id']);
    $do_branch_id = mi($form['do_branch_id']);
    
    if($_SESSION['do_deliver_branch'] && trim(join('',$_SESSION['do_deliver_branch']))!=''){
        $form['deliver_branch'] = $_SESSION['do_deliver_branch'];
	}
    
    $id = $form['id'];
    
    $sid_list = $_REQUEST['sid'];
    
	if(!$sid_list){
	    print "<script>alert('".jsstring($LANG['DO_INVALID_ITEM'])."');</script>";
		exit;
	}
	
	init_selection();
	//print_r($form);

	foreach($sid_list as $sku_item_id){
	    $q1=$con->sql_query("select sku_items.sku_item_code, sku_items.description as description, 
							if(sku_items.artno is null or sku_items.artno='',sku_items.mcode, sku_items.artno) as artno_mcode,
							sku_items.id as sku_item_id, 1 as uom_id, 1 as uom_fraction,u.code as master_uom_code, sku.have_sn, sku_items.doc_allow_decimal, u.id as master_uom_id, u.fraction as master_uom_fraction, sku_items.artno, sku_items.mcode, sic.qty as sb_qty,
							sku_items.use_rsp, if(sip.price is null, sku_items.rsp_discount, sip.rsp_discount) as rsp_discount,sku_items.rsp_price
							from sku_items
							left join sku_items_price sip on sip.sku_item_id =sku_items.id and sip.branch_id= ".mi($sessioninfo['branch_id'])."
							left join sku on sku_id = sku.id							
							left join uom u on u.id = sku_items.packing_uom_id
							left join sku_items_cost sic on sic.sku_item_id = sku_items.id and sic.branch_id = ".mi($sessioninfo['branch_id'])."
							where sku_items.id=$sku_item_id");
		$item = $con->sql_fetchrow($q1);
		if (!$item) {
			print "<script>alert('".jsstring($LANG['DO_INVALID_ITEM'])."');</script>";
			exit;
		}
		
		// found got turn on config to check and prevent to add zero or negative stock balance
		if($config['do_block_zero_stock_bal_items'] && $item['sb_qty'] <= 0){
			print "<script>alert('".jsstring(sprintf($LANG['DO_ZERO_STOCK_BAL_ITEM'], $item['sku_item_code']))."');</script>";
			exit;
		}
		
		if ($form['deliver_branch'])
		{
			foreach($form['deliver_branch'] as $bid)
			{
				$item['pcs_allocation'][$bid] = $form['qty_pcs'][$sku_item_id][$bid];
				
				// stock balance 2
				$sql = "select sku_item_id,qty from sku_items_cost where branch_id=$bid and sku_item_id=$sku_item_id";
				$con->sql_query($sql) or die(mysql_error());
				$item['stock_balance2_allocation'][$bid]=$con->sql_fetchfield('qty');
			}
		}
		else{
		    $item['pcs'] = $form['qty_pcs'][$sku_item_id];
			
			 // stock balance 2
			$sql = "select sku_item_id,qty from sku_items_cost where branch_id=$do_branch_id and sku_item_id=$sku_item_id";
			$con->sql_query($sql) or die(mysql_error());
			$item['stock_balance2']=$con->sql_fetchfield('qty');
		}
		
     //  print "get_item_price($sku_item_id, $branch_id, $form[price_indicate]);";
	//exit;
		// use deliver to cost
		if($config['consignment_modules'] && $config['cm_use_deliver_branch_sp'])	$sp_bid = $do_branch_id ? $do_branch_id : $branch_id;
		else $sp_bid = $branch_id; // use own cost
	
		$output_gst = array();
		if($config['enable_gst'] && $form['is_under_gst']){
			if($_REQUEST['is_special_exemption']){
				$use_gst = get_special_exemption_gst();
				$is_special_exemption = true;
			}else{
				$output_gst = get_sku_gst("output_tax", $sku_item_id);
				if($output_gst){
					$use_gst = $output_gst;
				}
			}
			
			if(!$use_gst){
				$use_gst = $gst_list[0];
			}
			
			$item['gst_id'] = $use_gst['id'];
			$item['gst_code'] = $use_gst['code'];
			$item['gst_rate'] = $use_gst['rate'];	
			
		}
		
		$tmp=get_item_price($sku_item_id, $sp_bid, $form['price_indicate'],$form, $use_gst, $is_special_exemption);
		$item = array_merge($item, $tmp);

		//print "get_item_selling($sku_item_id, $form[deliver_branch], $form[do_branch_id]);";
		
		$tmp_sell=get_item_selling($sku_item_id, $form['deliver_branch'], $form['do_branch_id'],$form['do_date']);
		$item = array_merge($item, $tmp_sell);

		$tmp_do_date = date("Y-m-d", strtotime($form['do_date']." +1 day"));
		$tmp_cost = get_sku_item_cost_selling($branch_id, $sku_item_id, $tmp_do_date, array("cost"));	
		if($tmp_cost) $item = array_merge($item, $tmp_cost);

		$item['price_indicate']=$config['sku_multiple_selling_price'][$form['price_indicate']]?$_REQUEST['price_indicate']:'';
		$ret=insert_tmp_item($item,$id);
		if ($ret==-2){
		    print "<script>alert('".jsstring($LANG['DO_MAX_ITEM_CANT_ADD'])."')</script>";
			//fail(sprintf($LANG['DO_MAX_ITEM_CANT_ADD'], $config['do_set_max_items']));
			exit;
		}
		
		$item['selling_price_allocation']=unserialize($item['selling_price_allocation']);
		$item['price_type']=unserialize($item['price_type']);
        $item['stock_balance2_allocation'] = unserialize($item['stock_balance2_allocation']);
        $item['pcs_allocation'] = unserialize($item['pcs_allocation']);
        
        // stock balance 1
		$sql = "select sku_item_id,qty from sku_items_cost where branch_id=$branch_id and sku_item_id=$sku_item_id";
		$con->sql_query($sql) or die(mysql_error());
		$item['stock_balance1']=$con->sql_fetchfield('qty');

		if($config['enable_sn_bn']){
			// batch no expired notification
			if(!$config['batch_no_days_notify']) $config['batch_no_days_notify'] = 30;

			$sql = $con->sql_query("select batch_no, expired_date from sku_batch_items where sku_item_id = ".mi($sku_item_id)." and branch_id = ".mi($branch_id));
			$sbi = $con->sql_fetchrow($sql);

			if($sbi['batch_no']){
				$time_left = 0;
				$bn_notify = "";
				$curr_time = strtotime(date("Y-m-d"));
				$batch_expired_time = strtotime($sbi['expired_date']);

				if($curr_time >= $batch_expired_time){
					$days_expired = mi(($curr_time-$batch_expired_time)/86400);
					if($days_expired == 0) $days_expired = "today";
					else $days_expired .= " day(s)";
					$bn_notify = sprintf($LANG['DO_BN_NOTIFY'], "has expired by ".$days_expired, $sbi['batch_no'], $sbi['expired_date']);
				}

				$notify_date = date("Y-m-d", mktime(0,0,0,date("m"), date("d")+$config['batch_no_days_notify'], date("Y")));
				$time_left = strtotime($notify_date) - $batch_expired_time;
				$days_remain = mi(($batch_expired_time-$curr_time)/86400);

				if(!$bn_notify && $time_left > 0){
					$bn_notify = sprintf($LANG['DO_BN_NOTIFY'], "will expire in ".$days_remain." day(s)", $sbi['batch_no'], $sbi['expired_date']);
				}
				
				$item['description'] .= " (Batch No: ".$sbi['batch_no']."  Expired Date: ".$sbi['expired_date'].")";
			}
		}
		
		if ($_REQUEST['do_type'] == 'credit_sales')	$price_indicator="Credit Sales DO";	
		$item['price_indicator']=$config['sku_multiple_selling_price'][$form['price_indicate']]? $config['sku_multiple_selling_price'][$form['price_indicate']] : $price_indicator;
		$tmp_do_date = date("Y-m-d", strtotime($form['do_date']." +1 day"));
		$tmp_cost = get_sku_item_cost_selling($branch_id, $sku_item_id, $tmp_do_date, array("cost"));		
        $item['cost']=$tmp_cost['cost'];
		unset($tmp_cost);
		
		//get sku first image when config do_show_photo is active
		if($config['do_show_photo']){
			$sku_item_photo = $appCore->skuManager->getSKUItemPhotos($sku_item_id);
			if(count($sku_item_photo['photo_list'])> 0){
				$item['photo'] = $sku_item_photo['photo_list'][0];
			}
		}

		$smarty->assign("do_type", $_REQUEST['do_type']);
		$smarty->assign("item", $item);
		$smarty->assign("form", $form);
	    $smarty->assign('show_discount',$_REQUEST['show_discount']);

		if(!$form['currency_code'] || $form['currency_code'] == $config["arms_currency"]["symbol"]) $smarty->assign("hide_currency_field", 1);
		
		$arr = array();
		/*if($form['do_no'] || $form['do_branch_id'] || $form['open_info']['name'])
			$rowdata = $smarty->fetch("do.new.do_row.single_branch.tpl");
		else
			$rowdata = $smarty->fetch("do.new.do_row.tpl");*/
		// print "<tr bgcolor=\"#ffee99\" onmouseover=\"this.bgColor='#ffffcc';\" onmouseout=\"this.bgColor='';\"  id=\"titem$item[id]\" >";

		if($form['deliver_branch']) $tpl = $smarty->fetch("do.new.do_row.tpl");
		else $tpl = $smarty->fetch("do.new.do_row.single_branch.tpl");

		//print "</tr>";

		$temp = array();
		$temp['rowdata'] = "<tr bgcolor=\"#ffee99\" onmouseover=\"this.bgColor='#ffffcc';\" onmouseout=\"this.bgColor='';\"  id=\"titem$item[id]\"".$item['id'].">".$tpl."</tr>";
		
		// if found have serial no
		if($item['have_sn'] != 0) $temp['sn'] = $smarty->fetch("do.sn.new.tpl");

		// if found got batch no notify
		if($bn_notify) $temp['bn_notify'] = $bn_notify;

		$row_item[] = $temp;
	}
	print json_encode($row_item);
}

function save_by_price_type($do_id, $branch_id, $form, &$errm, &$return_discount_code, $last_approval = false){
    global $con, $LANG, $smarty, $sessioninfo, $config;

    if($form['create_type']==2){
		$form['open_info'] = serialize($form['open_info']);
		$form['deliver_branch']='';
  		return true;
	}
	else{
	    if(!($form['deliver_branch'])){
//			$form['do_branch_id']=$form['deliver_branch']['0'];
			$deliver_branch[]=$form['do_branch_id'];
//			save_tmp_items($form['do_branch_id']);
		}
		else{
      		// return if is multiple branch and not last approval
//    		if(is_array($form['deliver_branch']) && !$last_approval)	return 1;
     		
			//$form['deliver_branch'] = serialize($form['deliver_branch']);
			$deliver_branch=$form['deliver_branch'];
		}
		$form['deliver_branch']='';
		$form['open_info']='';
	}
	
	// check currency mode
	if($form['do_type'] == 'transfer' && $config['consignment_modules'] && $config['masterfile_branch_region'] && $config['consignment_multiple_currency'] && $form['exchange_rate']>1){
		$is_currency_mode = true;
		
		if($form['price_indicate']==1)	$use_cost_indicate = true;
	}

	//store to tmp form
	$tmp_form=$form;

	if($is_currency_mode){
		if($use_cost_indicate)	$currency_multiply = 1;
		else	$currency_multiply = $form['exchange_rate'];
		
		$currency_discount_params = array('currency_multiply'=>$currency_multiply);
		$currency_multiply_rate = 1/$form['exchange_rate'];
		
		if($use_cost_indicate)	$foreign_currency_discount_params['currency_multiply'] = $currency_multiply_rate;
	}
	
	foreach ($deliver_branch as $deliver_branch_id){
        $form['do_branch_id'] = mi($deliver_branch_id);
        unset($form['allowed_user']);
        $form['allowed_user'][$deliver_branch_id] = $tmp_form[$form['do_branch_id']];
        $form['allowed_user'] = serialize($form['allowed_user']);
		$do_branch_id = mi($deliver_branch_id);
		
		if($config['do_split_auto_add_do_discount']&&$do_branch_id){  // find discount table
			$con->sql_query("select btd.*,tdt.code
	from branch_trade_discount btd
	left join trade_discount_type tdt on tdt.id=btd.trade_discount_id
	where btd.branch_id=".mi($do_branch_id));
			while($r = $con->sql_fetchrow()){
				$branch_trade_discount[$r['code']] = $r['value'];
			}
			$con->sql_freeresult();
			$form['default_do_markup'] = $form['do_markup'];
		}

		// separate items into different discount type
		$sql = "select tmp.*,uom.fraction,if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as discount_code, sku.default_trade_discount_code
		from tmp_do_items tmp
		left join sku_items_price sip on sip.sku_item_id=tmp.sku_item_id and sip.branch_id=$do_branch_id
		left join sku_items si on si.id=tmp.sku_item_id
		left join sku on sku.id=si.sku_id
		left join uom on tmp.uom_id=uom.id
		where do_id=$do_id and tmp.branch_id=$branch_id and tmp.user_id=$sessioninfo[id] order by id";
	  //if($do_id=2838) print "$sql <br>";

		$con->sql_query($sql) or die(mysql_error());

		while($r = $con->sql_fetchassoc()){
		    $pcs_allocation = unserialize($r['pcs_allocation']);
   		    $ctn_allocation = unserialize($r['ctn_allocation']);

			if($config['sku_always_show_trade_discount'] && !$r['discount_code']) $r['discount_code'] = $r['default_trade_discount_code'];
		    
			//Multiple Branch
		    if($pcs_allocation || $ctn_allocation){
		        $price_type = array();

				//calc pcs
			    $pcs=$pcs_allocation[$do_branch_id];
                $price_type_info[$r['discount_code']]['total_pcs'] += $pcs;
                $r['pcs']=$pcs;
                $r['pcs_allocation']='';    //set empty, avoid save into do_items
                
                $price_type[$do_branch_id] = $r['discount_code'];
                
				//calc ctn
				$ctn = $ctn_allocation[$do_branch_id];
                $price_type_info[$r['discount_code']]['total_ctn'] += $ctn;
                $r['ctn']=$ctn;
                $r['ctn_allocation']='';    //set empty, avoid save into do_items

				$r['price_type'] = $price_type;
				
				//get selling price
	   		    $selling_price_allocation = unserialize($r['selling_price_allocation']);
	            $r['selling_price'] = $selling_price_allocation[$do_branch_id];
	            $r['selling_price_allocation']='';

				//stock balance 2
	   		    $stock_balance2_allocation = unserialize($r['stock_balance2_allocation']);
	   		    $r['stock_balance2'] = $stock_balance2_allocation[$do_branch_id];
	   		    $r['stock_balance2_allocation']='';
				
				$row_qty = $ctn*$r['fraction']+$pcs;
			}else{
			    //single branch
	            $price_type_info[$r['discount_code']]['total_pcs'] += $r['pcs'];
				$price_type_info[$r['discount_code']]['total_ctn'] += $r['ctn'];
				
				$r['price_type'] = array(intval($do_branch_id)=>$r['discount_code']);

				$row_qty = $r['ctn']*$r['fraction']+$r['pcs'];
			}
						
			$row_amt = $r['line_amt'];
			$inv_amt = $r['inv_line_amt2'];

			$return_discount_code = $r['discount_code'];
			$price_type_info[$r['discount_code']]['total_inv_amt'] += $inv_amt;
			$price_type_info[$r['discount_code']]['total_amount'] += $row_amt;
			$price_type_info[$r['discount_code']]['total_qty'] += $row_qty;
			$items_type[$r['discount_code']][] = $r;
		}
		//exit;
		
		if(count($price_type_info)<=1)  return 1;

		$form['branch_id']=$branch_id;
		$form['last_update'] = 'CURRENT_TIMESTAMP';
		$form['added'] = 'CURRENT_TIMESTAMP';
		$form['user_id'] = $sessioninfo['id'];

	    // always split and confirm
		$is_confirm = true;
		if ($is_confirm) $form['status'] = 1;

	    $default_remark = $form['remark'];
	    $count = 0;

		ksort($items_type);	// sort by price type
		
		foreach($items_type as $discount_code=>$items){
	        // check and create branch_approval_history data
		    $params = array();
		    $params['type'] = 'DO';
		    $params['reftable'] = 'DO';
		    $params['user_id'] = $sessioninfo['id'];

		   	if($config['do_approval_by_department']){
				$params['dept_id'] = $form['dept_id'];
			}

		    if($config['consignment_modules']){
	          //$astat = check_and_create_branch_approval('DO',1, 'DO','',true,$branch_id);
	          $params['branch_id'] = 1;
	          $params['save_as_branch_id'] = $branch_id;
			}else{
	          //$astat = check_and_create_branch_approval('DO',$branch_id, 'DO');
	          $params['branch_id'] = $branch_id;
			}
			//if($form['approval_history_id']) $params['curr_flow_id'] = $form['approval_history_id']; // use back the same id if already have
			
			$params['doc_amt'] = $price_type_info[$discount_code]['total_amount'];
			if(isset($price_type_info[$discount_code]['total_inv_amt']))	$params['doc_amt'] = $price_type_info[$discount_code]['total_inv_amt'];	// use invoice amt if got
		
			$astat = check_and_create_approval2($params, $con);

	  	  	if (!$astat) $errm['top'][] = $LANG['DO_NO_APPROVAL_FLOW'];
	  		else{
	  			 $form['approval_history_id'] = $astat[0];
	     		 if ($astat[1] == '|'){
	     		 	$last_approval = true;
	     		 	if($astat['direct_approve_due_to_less_then_min_doc_amt'])	$direct_approve_due_to_less_then_min_doc_amt = 1;	// direct approve because no qualify for min doc amt
	     		 }
	  		}
	  		if ($last_approval) $form['approved'] = 1;

	        if(!$form['create_type'])$form['create_type']=1;

	        // get discount percent
	        $sql = "select btd.*,tdt.code from branch_trade_discount btd
	left join trade_discount_type tdt on tdt.id=btd.trade_discount_id
	where btd.branch_id=".mi($do_branch_id)." and tdt.code=".ms($discount_code);
			$con->sql_query($sql) or die(mysql_error());
			$discount_percent = $con->sql_fetchfield('value');
			if($config['do_remark_add_profit']&&$do_branch_id){
				if($default_remark) $default_remark .= ", ";
				$form['remark'] = $default_remark."** PROFIT $discount_percent% (".$discount_code.") **";
			}

			$form['sheet_price_type'] = $discount_code;
			$form['total_pcs'] = $price_type_info[$discount_code]['total_pcs'];
			$form['total_ctn'] = $price_type_info[$discount_code]['total_ctn'];
			$form['total_qty'] = $price_type_info[$discount_code]['total_qty'];
			$form['total_amount'] = $price_type_info[$discount_code]['total_amount'];
			$form['sub_total_inv_amt'] = $form['total_inv_amt'] = $price_type_info[$discount_code]['total_inv_amt'];
			
			/*if($is_currency_mode){
				$form['total_foreign_amount'] = $price_type_info[$discount_code]['total_foreign_amount'];
				$form['sub_total_foreign_inv_amt'] = $form['total_foreign_inv_amt'] = $price_type_info[$discount_code]['total_foreign_inv_amt'];
			}else{
				$form['sub_total_foreign_inv_amt'] = 0;
				$form['total_foreign_inv_amt'] = 0;
				$form['total_foreign_amount'] = 0;
			}
	
			// invoice discount
			if($form['discount']){
				//$inv_discount_amt = $form['total_inv_amt']*($form['discount']/100);
				$inv_discount_amt = round(get_discount_amt($form['total_inv_amt'], $form['discount'], $currency_discount_params),2);
				$form['total_inv_amt'] -= $inv_discount_amt;
				
				// get currency total invoice amt
				if($is_currency_mode){
					$foreign_inv_discount_amt = round(get_discount_amt($form['sub_total_foreign_inv_amt'], $form['discount'], $foreign_currency_discount_params),2);
					$form['total_foreign_inv_amt'] -= $foreign_inv_discount_amt;
				}else $form['total_foreign_inv_amt'] = 0;
			}*/
			if($config['do_split_auto_add_do_discount']){
				if(!$form['default_do_markup']) $form['do_markup'] = $branch_trade_discount[$discount_code];
			}

			if($config['masterfile_enable_sa'] && $form['do_sa']) $form['mst_sa'] = serialize($form['do_sa']);
			else $form['mst_sa'] = "";
			
			$sql = "insert into do " . mysql_insert_by_field($form, array('branch_id', 'user_id', 'dept_id', 'status', 'approved', 'do_date', 'added', 'last_update','deliver_branch','total_pcs', 'total_ctn', 'total_qty', 
			'total_amount', 'remark','approval_history_id', 'do_branch_id', 'po_no', 'create_type', 'do_type','open_info','price_indicate','debtor_id','discount','total_inv_amt','do_markup','markup_type','exchange_rate',
			'sub_total_inv_amt','sub_total_foreign_inv_amt','total_foreign_inv_amt','total_foreign_amount','sheet_price_type', 'mst_sa','allowed_user','no_use_credit_sales_cost',
			'is_under_gst'));
			$con->sql_query($sql);
			//print "$sql<br />";
			$form['id'] = $con->sql_nextid();
            $do_id_arr[$form['id']]=$form['id'];

			foreach($items as $r){
	            $upd['do_id']=$form['id'];
				$upd['branch_id']=$r['branch_id'];
				$upd['sku_item_id']=$r['sku_item_id'];
				$upd['artno_mcode']=$r['artno_mcode'];
				$upd['po_cost']=$r['po_cost'];
				$upd['cost']=$r['cost'];
				$upd['cost_price']=$r['cost_price'];
				$upd['foreign_cost_price']=$r['foreign_cost_price'];
				$upd['selling_price']=$r['selling_price'];
				$upd['uom_id']=$r['uom_id'];
				$upd['ctn']=$r['ctn'];
				$upd['pcs']=$r['pcs'];
				$upd['ctn_allocation']=$r['ctn_allocation'];
				$upd['pcs_allocation']=$r['pcs_allocation'];
				$upd['selling_price_allocation']=$r['selling_price_allocation'];
				$upd['price_type']= serialize($r['price_type']);
				$upd['stock_balance1'] = $r['stock_balance1'];
				$upd['stock_balance2'] = $r['stock_balance2'];
				$upd['stock_balance2_allocation'] = $r['stock_balance2_allocation'];
				$upd['item_discount'] = $r['item_discount'];
				$upd['dtl_sa'] = $r['dtl_sa'];
				$upd['price_indicate'] = $r['price_indicate'];
				$upd['gst_id'] = $r['gst_id'];
				$upd['gst_code'] = $r['gst_code'];
				$upd['gst_rate'] = $r['gst_rate'];
				
				$upd['display_cost_price_is_inclusive'] = $r['display_cost_price_is_inclusive'];
				$upd['display_cost_price'] = $r['display_cost_price'];
				$upd['bom_id'] = $r['bom_id'];
				$upd['bom_ref_num'] = $r['bom_ref_num'];
				$upd['bom_qty_ratio'] = $r['bom_qty_ratio'];
				
				$sql = "insert into do_items ".mysql_insert_by_field($upd);
				$con->sql_query($sql) or die(mysql_error());
				if ($first_id==0) $first_id = $con->sql_nextid();
			}

			// recalculate
			auto_update_do_all_amt($form['branch_id'], $form['id']);

			if ($is_confirm){

			    $formatted=sprintf("%05d",$form[id]);
			    //select report prefix from branch
				$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
				$r=$con->sql_fetchrow();

		        log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Confirmed: (ID#".$r['report_prefix'].$formatted.", Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
		        //log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "DO Confirmed (ID#$form[id], Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:$form[total_amount])");

			    if ($last_approval) {
			    	if($direct_approve_due_to_less_then_min_doc_amt)	$_REQUEST['direct_approve_due_to_less_then_min_doc_amt'] = 1;
			    	do_approval($form['id'], $branch_id, $form['status'], true, false);
				}
				else {
					$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
					$to = get_pm_recipient_list2($form['id'],$form['approval_history_id'],0,'confirmation',$branch_id,'do');
					send_pm2($to, "Delivery Order Approval (ID#$form[id])", "do.php?page=$form[do_type]&a=view&id=$form[id]&branch_id=$branch_id", array('module_name'=>'do'));
				}

			}
			else{
				$formatted=sprintf("%05d",$form[id]);
			    //select report prefix from branch
				$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
				$r=$con->sql_fetchrow();

		        log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Saved: (ID#".$r['report_prefix'].$formatted." ,Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");
		        //log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "DO Saved (ID#$form[id], Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:$form[total_amount])");
		    }
		}
		//exit;

		//reset value;
		unset($inv_amt);
		unset($items_type);
		unset($price_type_info);
		unset($form);
		$form=$tmp_form;

   }

 	// delete old tmp do items
	$con->sql_query("delete from tmp_do_items where do_id=$do_id and branch_id = $branch_id and user_id = $sessioninfo[id]") or die(mysql_error());
	
	// hide original DO
	$con->sql_query("update do set active=0,status=4 where id=$do_id and branch_id=$branch_id") or die(mysql_error());
	//$con->sql_query("delete from do_items where branch_id=$branch_id and do_id=$do_id") or die(mysql_error());

	header("Location: /do.php?page=$form[do_type]&t=".($last_approval?'approve':'confirm')."&save_id=".join(",",$do_id_arr));
	exit;
}

function change_do_branch(){
    global $con, $config;

	$_REQUEST['sku_item_id'] = $_REQUEST['sku_item_id'];

	$sku_item_id_list = $_REQUEST['sku_item_id'];
	$branch_id = mi($_REQUEST['branch_id']);
	$from_branch_id = mi($_REQUEST['from_branch_id']);
	$do_date = $_REQUEST['do_date'];

	if(!$sku_item_id_list||!$branch_id) return;
	// selling price
	foreach ($sku_item_id_list as $sid){
        $con->sql_query("select si.id as sku_item_id, si.sku_id, round(if(sip.price is null, si.selling_price, sip.price),3) as selling_price,
                        if(sip.price is null,sku.default_trade_discount_code,sip.trade_discount_code) as trade_discount_code, sku.default_trade_discount_code
                        from sku_items si
                        left join sku_items_price_history sip on sip.sku_item_id=si.id and branch_id=$branch_id and sip.added < ".ms($do_date)."
                        left join sku on sku.id=si.sku_id
                        where si.id=$sid order by sip.added desc limit 1");
	
	 	while($r = $con->sql_fetchassoc()){
			$ret[$r['sku_item_id']]['selling_price'] = number_format($r['selling_price'], 2);
			$ret[$r['sku_item_id']]['price_type'] = $r['trade_discount_code'];
            
            if($config['show_parent_stock_balance']) {
                //parent stock balance 1
                $sql = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
                                        from sku_items si
                                        left join sku_items_cost sic on sic.branch_id=$from_branch_id and sic.sku_item_id=si.id
                                        left join uom on uom.id=si.packing_uom_id
                                        where si.sku_id=".$r['sku_id']);
                
                $parent_stock_balance1 = 0;
                while($data = $con->sql_fetchassoc($sql)) {
                    $parent_stock_balance1 += $data['parent_stock_balance'];
                }
                $ret[$r['sku_item_id']]['parent_stock_balance1']  = $parent_stock_balance1;
                $con->sql_freeresult($sql);
            
                //parent stock balance 2
                $sql2 = $con->sql_query("select (sic.qty*uom.fraction) as parent_stock_balance
                                        from sku_items si
                                        left join sku_items_cost sic on sic.branch_id=$branch_id and sic.sku_item_id=si.id
                                        left join uom on uom.id=si.packing_uom_id
                                        where si.sku_id=".$r['sku_id']);
                
                $parent_stock_balance2 = 0;
                while($data = $con->sql_fetchassoc($sql2)) {
                    $parent_stock_balance2 += $data['parent_stock_balance'];
                }
                $ret[$r['sku_item_id']]['parent_stock_balance2']  = $parent_stock_balance2;
                $con->sql_freeresult($sql2);
            }
		}
		$con->sql_freeresult();
	}
    
	// stock balance 2
	$con->sql_query("select sku_item_id,qty from sku_items_cost where branch_id=$branch_id and sku_item_id in (".join(',',$sku_item_id_list).")") or die(mysql_error());
	while($r = $con->sql_fetchassoc()){
		$ret[$r['sku_item_id']]['stock_balance2'] = mf($r['qty']);
	}
	$con->sql_freeresult();
	
	// stock balance 1
	$con->sql_query("select sku_item_id,qty from sku_items_cost where branch_id=$from_branch_id and sku_item_id in (".join(',',$sku_item_id_list).")") or die(mysql_error());
	while($r = $con->sql_fetchassoc()){
		$ret[$r['sku_item_id']]['stock_balance1'] = mf($r['qty']);
	}
	$con->sql_freeresult();
	
	$data['stock_balance'] = $ret;
	$data['user_list'] = change_user_list_process($_REQUEST);
	print json_encode($data);
}

/*function do_open_transfer($id,$branch_id){
    global $con, $LANG, $sessioninfo, $smarty;

	$form = $_REQUEST;// keep the passed header
	
	//is new DO
	if ($id==0){
		$id=time();
		$form['id']=$id;
	}
	
	//if the action is open and is not a NEW DO
	if ($form['a']=='open' && !is_new_id($id)){
		//get Existing DO header
		$form=load_do_header($id, $branch_id);
		if(!$form){
		    $smarty->assign("url", "/do.php");
		    $smarty->assign("title", "Delivery Order");
		    $smarty->assign("subject", sprintf($LANG['DO_NOT_FOUND'], $id));
		    $smarty->display("redir.tpl");
		    exit;
		}

		// check DO permission
		if ($form['user_id']!= $sessioninfo['id'] && $sessioninfo['level']<9999){
			//if(checking department)
		    $smarty->assign("url", "/do.php");
		    $smarty->assign("title", "Delivery Order");
		    $smarty->assign("subject", sprintf($LANG['DO_NO_ACCESS'], $id));
		    $smarty->display("redir.tpl");
		    exit;
		}
		//if the DO oredi submit and not the reject DO, goto view only.
		elseif($form['status'] && $form['status']!=2){
			//do_view($id, $branch_id);
			exit;
		}
		//print "here";
		
		copy_to_tmp($id, $branch_id);
	}
	//IF THE DO IS NEW
	else{
		$form['id']=$id;
	}
	if(!$form['old_branch_id'])	$form['old_branch_id'] = $branch_id;
	
	$_REQUEST['do_bid'] = $form['do_branch_id'];
	$smarty->assign("do_items", load_do_items2($id, $branch_id, true));
	//echo"<pre>";print_r($form);echo"</pre>";
	load_branches_group();
	if($form['active']===0)    $smarty->assign("readonly", 1);
	
	
	$smarty->assign("form", $form);
	$smarty->display("do.transfer.new.tpl");
}*/

/*function do_save_transfer($do_id, $old_branch_id, $is_confirm){
	global $con, $LANG, $smarty, $sessioninfo, $config;

	//... validate, save, check confirm, send pm, set approval.....
	$form=$_REQUEST;
	$branch_id = $_REQUEST['branch_id'];
	
	save_tmp_items2();

	//VALIDATE DATA
	$errm = array();
	if(!$form['uom_id']) $errm['top'] = sprintf($LANG['DO_EMPTY']);

	$arr=split("-",$form['do_date']);
	$yy=$arr[0];
	$mm=$arr[1];
	$dd=$arr[2];
	if(!checkdate($mm,$dd,$yy)){
	   	$errm['top'][] = $LANG['DO_INVALID_DATE'];
		$form['do_date']='';
	}

    if($form['create_type']==2){
		if($form['open_info']['name']=='')
	   		$errm['top'][] = $LANG['DO_OPEN_INFO_NAME_EMPTY'];
		if($form['open_info']['address']=='')
	   		$errm['top'][] = $LANG['DO_OPEN_INFO_ADDRESS_EMPTY'];
	}
	
	if(!$errm && $is_confirm){
	    if ($form['approval_history_id']){
	        $con->sql_query("update branch_approval_history set approvals = flow_approvals where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");

	        $con->sql_query("select id,approvals from branch_approval_history where id = ".mi($form['approval_history_id'])." and branch_id=$branch_id");
			$astat = $con->sql_fetchrow();
	        if ($astat[1] == '|') $last_approval = true;
		}else{
			$astat = check_and_create_branch_approval('DO',1, 'DO','',true,$branch_id);
			if (!$astat) $errm['top'][] = $LANG['DO_NO_APPROVAL_FLOW'];
			else{
				$form['approval_history_id'] = $astat[0];
	   			if ($astat[1] == '|') $last_approval = true;
			}
		}
	}
	
	if($errm){
		$smarty->assign("errm", $errm);
		do_open_transfer($do_id,$old_branch_id);
		exit;
	}
	else{
		if ($is_confirm) $form['status'] = 1;
	    if ($last_approval) $form['approved'] = 1;

		$form['id']=$do_id;
		$form['branch_id']=$branch_id;
		$form['last_update'] = 'CURRENT_TIMESTAMP';
		$form['added'] = 'CURRENT_TIMESTAMP';
		$form['user_id'] = $sessioninfo['id'];
        //$form['do_branch_id'] = 1;
		$form['do_type'] = 'transfer';

		if($form['create_type']==2){
		    $form['open_info'] = serialize($form['open_info']);
			$form['deliver_branch']='';
			$form['do_branch_id']='';
		}else   $form['open_info'] = '';
		
		if (is_new_id($do_id)){
			if(!$form['create_type'])$form['create_type']=1;
			
			$con->sql_query("insert into do " . mysql_insert_by_field($form, array('branch_id', 'user_id', 'dept_id', 'status', 'approved', 'do_date', 'added', 'deliver_branch','total_pcs', 'total_ctn', 'total_amount', 'remark','approval_history_id', 'do_branch_id', 'po_no', 'create_type', 'open_info','price_indicate','do_type','total_qty','total_rcv')));
			$form['id'] = $con->sql_nextid();
		}
		else{
		    if($old_branch_id==$branch_id){
                $con->sql_query("update do set " . mysql_update_by_field($form, array('dept_id', 'status', 'approved', 'do_date', 'deliver_branch','total_ctn', 'total_pcs', 'total_amount', 'remark','approval_history_id', 'po_no', 'open_info','price_indicate','do_branch_id','do_type','branch_id','total_qty','total_rcv'))."where branch_id=$old_branch_id and id=$do_id");
			}else{
                $con->sql_query("insert into do " . mysql_insert_by_field($form, array('branch_id', 'user_id', 'dept_id', 'status', 'approved', 'do_date', 'added', 'deliver_branch','total_pcs', 'total_ctn', 'total_amount', 'remark','approval_history_id', 'do_branch_id', 'po_no', 'create_type', 'open_info','price_indicate','do_type','total_qty','total_rcv')));
				$form['id'] = $con->sql_nextid();
				$con->sql_query("delete from do where id=$do_id and branch_id=$old_branch_id");
			}
		}

		//copy tmp table to do_items table
		$q1=$con->sql_query("select * from tmp_do_items
where do_id=$do_id and user_id=$sessioninfo[id] order by id") or die(mysql_error());
		$first_id = 0;
		while($r=$con->sql_fetchrow($q1)){
			$upd['do_id']=$form['id'];
			$upd['branch_id']=$branch_id;
			$upd['sku_item_id']=$r['sku_item_id'];
			$upd['artno_mcode']=$r['artno_mcode'];
			$upd['po_cost']=$r['po_cost'];
			$upd['cost_price']=$r['cost_price'];
			$upd['selling_price']=$r['selling_price'];
			$upd['uom_id']=$r['uom_id'];
			$upd['ctn']=$r['ctn'];
			$upd['pcs']=$r['pcs'];
			$upd['ctn_allocation']=$r['ctn_allocation'];
			$upd['pcs_allocation']=$r['pcs_allocation'];
			$upd['selling_price_allocation']=$r['selling_price_allocation'];
			$upd['price_type']=$r['price_type'];
			$upd['rcv_pcs']=$r['rcv_pcs'];
			$upd['stock_balance1'] = $r['stock_balance1'];
			$upd['stock_balance2'] = $r['stock_balance2'];
			$upd['stock_balance2_allocation'] = $r['stock_balance2_allocation'];
			
			$con->sql_query("insert into do_items ".mysql_insert_by_field($upd)) or die(mysql_error());
			if ($first_id==0) $first_id = $con->sql_nextid();
		}

		if ($first_id>0) {
			if(!is_new_id($do_id)){
				$con->sql_query("delete from do_items where branch_id=$old_branch_id and do_id=$do_id and id<$first_id") or die(mysql_error());
			}

			$con->sql_query("delete from tmp_do_items where do_id=$do_id and user_id = $sessioninfo[id]") or die(mysql_error());
		}
		else{
			die("System error: Insert do_items failed. Please contact ARMS technical support.");
		}

		if ($is_confirm){
	        log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "DO Confirmed (ID#$form[id], Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:$form[total_amount])");
		    if ($last_approval)
		    	do_approval($form['id'], $branch_id, $form['status'], true);
			else
				$con->sql_query("update branch_approval_history set ref_id=$form[id] where id=$form[approval_history_id] and branch_id = $branch_id");
		}
		else
	        log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "DO Saved (ID#$form[id], Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:$form[total_amount])");
	}
	if($form['a']=='confirm_transfer')  $t = 'confirm';
	elseif($form['a']=='save_transfer')  $t = 'save';
	header("Location: /do.php?page=$form[do_type]&t=$t&save_id=$form[id]");
	exit;
}*/

/*function save_tmp_items2(){
	global $con, $branch_id, $sessioninfo;
	$form=$_REQUEST;

	if($form['uom_id']){
		foreach($form['uom_id'] as $k=>$v){
		    
			$update = array();
			$update['ctn']=mi($form['qty_ctn'][$k]);
			$update['pcs']=mi($form['qty_pcs'][$k]);
			//$update['selling_price']=doubleval($form['selling_price'][$k]);
			$update['ctn_allocation']='';
			$update['pcs_allocation']='';
			$update['po_cost']=doubleval($form['po_cost'][$k]);
			$update['cost_price']=$form['cost_price'][$k];
			$update['uom_id']=$form['uom_id'][$k];
            $update['price_type'] =serialize($form['price_type'][$k]);
            $update['rcv_pcs'] = mi($form['rcv_pcs'][$k]);
            $update['stock_balance1'] = $form['stock_balance1'][$k];
            $update['stock_balance2'] = $form['stock_balance2'][$k];
            $update['stock_balance2_allocation'] = serialize($form['stock_balance2_allocation'][$k]);
            
			$con->sql_query("update tmp_do_items set " . mysql_update_by_field($update) . " where id=$k and user_id=".mi($sessioninfo['id'])) or die(mysql_error());
		}
	}
}*/

/*function do_ajax_refresh_cost2($do_id, $branch_id){
	global $con, $smarty, $sessioninfo;

	$form = $_REQUEST;
	$price_indicate=$form['price_indicate'];

	if($do_id)
		$con->sql_query("update do set price_indicate=".ms($price_indicate)." where id=$do_id and branch_id = $branch_id");

	save_tmp_items2();

	$q1 = $con->sql_query("select tdi.*, uom.fraction as uom_fraction
from tmp_do_items tdi
left join uom on uom.id=tdi.uom_id
where tdi.do_id=$do_id and user_id=$sessioninfo[id]");

	while($r1=$con->sql_fetchrow($q1)){
		$update=get_item_price($r1['sku_item_id'], $branch_id, $price_indicate,$form);
		$new_cost_price=$update['cost_price']*$r1['uom_fraction'];
		$con->sql_query("update tmp_do_items set cost_price='$new_cost_price' where id = $r1[id] and branch_id=$branch_id");
	}
	
	$_REQUEST['do_bid'] = $form['do_branch_id'];
	$smarty->assign('show_discount',$_REQUEST['show_discount']);
	$smarty->assign("do_items", load_do_items2($do_id, $branch_id, true));
	$smarty->assign("form", $form);
	$smarty->display("do.new.sheet.tpl");
}*/

function ajax_get_invoice_no($id, $branch_id){
	global $con;
	$type_postfix_list = array('transfer'=>'','credit_sales'=>'/D','open'=>'/C');
	
	$con->sql_query("select inv_no,do_type from do where id=$id and branch_id=$branch_id") or die(mysql_error());
	$inv_no = $con->sql_fetchfield(0);
	$do_type = $con->sql_fetchfield(1);
	
	$type_postfix = $type_postfix_list[$do_type];
	
	if(!$inv_no){
	    $con->sql_query("select report_prefix, ip from branch where id=$branch_id");
		$report_prefix = $con->sql_fetchrow();

	    $con->sql_query("select max(inv_no) from do where branch_id=".mi($branch_id)." and inv_no like '$report_prefix[0]%$type_postfix' and do_type=".ms($do_type)) or die(mysql_error());
    
		$inv_no = $con->sql_fetchfield(0);
	}else{
		$json['no_edit'] = 1;
	}

	if(!$inv_no)    $inv_no = 0;
	else{
	    list($inv_no,$dummy) = explode("/",$inv_no);
  		$inv_no = mi(substr($inv_no,-5));
	}
	
	if(!$json['no_edit'])   $inv_no++;
	
	$json['inv_no'] = $inv_no;
	print json_encode($json);
}

/*function change_credit_sales_price($branch_id){
	global $con, $config;

	$form = $_REQUEST;
	$sku_item_id_list = $_REQUEST['sku_item_id'];
	$ret = array();
	
	if($sku_item_id_list){
		foreach($sku_item_id_list as $sku_item_id){
			// get gst
			$output_gst = array();
			if($config['enable_gst'] && $form['is_under_gst']){
				$output_gst = get_sku_gst("output_tax", $sku_item_id);
					if($output_gst){
						$item['gst_id'] = $output_gst['id'];
						$item['gst_code'] = $output_gst['code'];
						$item['gst_rate'] = $output_gst['rate'];
					}
			}
            $tmp=get_item_price($sku_item_id, $branch_id, $form['price_indicate'],$form, $output_gst);
			$ret[$sku_item_id] = $tmp;
		}
	}
    
	print json_encode($ret);
}*/

function ajax_get_credit_sales_item_cost_history(){
	global $con, $smarty;
	
	$sku_item_id = mi($_REQUEST['sku_item_id']);
	$debtor_id = mi($_REQUEST['debtor_id']);
	$branch_id = mi($_REQUEST['branch_id']);
	
	if(!$sku_item_id||!$debtor_id||!$branch_id) die('Invalid Parameters');
	
	// debtor info
	$con->sql_query("select * from debtor where id=$debtor_id") or die(mysql_error());
	$debtor_info = $con->sql_fetchrow();
	
	// sku items info
	$con->sql_query("select * from sku_items where id=$sku_item_id") or die(mysql_error());
	$sku_item_info = $con->sql_fetchrow();
	
	// branch info
	$con->sql_query("select * from branch where id=$branch_id") or die(mysql_error());
	$branch_info = $con->sql_fetchrow();
	
	$sql = "select di.*,uom.code as uom_code,do.do_date,do.do_no,do.debtor_id,
(di.ctn*uom.fraction + di.pcs) as total_qty,di.cost_price*(di.ctn + di.pcs / uom.fraction) as total_cost
from do_items di
left join do on do.id=di.do_id and do.branch_id=di.branch_id
left join uom on uom.id=di.uom_id
where do.do_type='credit_sales' and do.active=1 and do.approved=1 and di.sku_item_id=$sku_item_id and do.debtor_id=$debtor_id
order by do.do_date desc,do.do_no desc,di.id desc";
	$con->sql_query($sql) or die(mysql_error());
	
	$smarty->assign('items',$con->sql_fetchrowset());
	$smarty->assign('debtor_info',$debtor_info);
	$smarty->assign('sku_item_info',$sku_item_info);
	$smarty->assign('branch_info',$branch_info);
	$smarty->display('do.credit_sales.item_cost_history.tpl');
}

function grouping_item($do_items, $params = array()){
	global $con;
	
	$group_same_clr = mi($params['group_same_clr']);
	
    // will not combine if different invoice discount or price or uom_fraction
	//print_r($do_items);
	$hash_key_list = array();
	$item_no = 0;
	foreach($do_items as $key=>$di){
		$sku_item_id = $di['sku_item_id'];
		$inv_discount = $di['item_discount'];
		$cost_price = $di['cost_price'];
		$uom_fraction = $di['uom_fraction'];
		$gst_id = mi($di['gst_id']);
		
		$hash_str = "D".$inv_discount."P".$cost_price."U".$uom_fraction;
		
		if($group_same_clr){	// Need to Group by Color & Size
			$hash_str .= "SKUID".$di['sku_id']."C:".$di['color'];
		}else{
			// sku_item_id + invoice + price + uom = key
			$hash_str .= "S".$sku_item_id;
		}
		
		// Append GST ID if got gst
		if($gst_id)	$hash_str .= "GID".$gst_id;
		
		$hash_key = md5($hash_str);
		
		if(!in_array($hash_key, $hash_key_list)){
			$do_items2[$hash_key] = $di;
			$do_items2[$hash_key]['item_no'] = $item_no++;
			$hash_key_list[] = $hash_key;
		}else{
			$do_items2[$hash_key]['ctn'] += $di['ctn'];
			$do_items2[$hash_key]['pcs'] += $di['pcs'];
			
			$do_items2[$hash_key]['line_gross_amt'] += $di['line_gross_amt'];
			$do_items2[$hash_key]['line_gst_amt'] += $di['line_gst_amt'];
			$do_items2[$hash_key]['line_amt'] += $di['line_amt'];
			
			$do_items2[$hash_key]['item_discount_amount'] += $di['item_discount_amount'];
			$do_items2[$hash_key]['item_discount_amount2'] += $di['item_discount_amount2'];
			
			$do_items2[$hash_key]['inv_line_gross_amt'] += $di['inv_line_gross_amt'];
			$do_items2[$hash_key]['inv_line_gst_amt'] += $di['inv_line_gst_amt'];
			$do_items2[$hash_key]['inv_line_amt'] += $di['inv_line_amt'];
			
			$do_items2[$hash_key]['inv_line_gross_amt2'] += $di['inv_line_gross_amt2'];
			$do_items2[$hash_key]['inv_line_gst_amt2'] += $di['inv_line_gst_amt2'];
			$do_items2[$hash_key]['inv_line_amt2'] += $di['inv_line_amt2'];
		}
		
		if($group_same_clr){
			if(!isset($do_items2[$hash_key]['parent_info'])){
				$do_items2[$hash_key]['size_list'] = array();
				$do_items2[$hash_key]['parent_info'] = array();
				
				// Get Parent Info
				$con->sql_query("select sku_item_code,artno, mcode, description
					from sku_items
					where sku_id=".mi($di['sku_id'])." and is_parent=1
					order by id
					limit 1");
				$do_items2[$hash_key]['parent_info'] = $con->sql_fetchassoc();
				$con->sql_freeresult();
			}	
			
			
			$do_items2[$hash_key]['size_list'][$di['size']]['ctn'] += $di['ctn'];
			$do_items2[$hash_key]['size_list'][$di['size']]['pcs'] += $di['pcs'];
		}
	}
	
	// fix smarty bug, hash key cannot loop using section in smarty
	if($do_items2){
		foreach($do_items2 as $r){
			$temp[] = $r;
		}
	}
	//print_r($do_items2);
	
	return $temp;
}

function ajax_check_po_no(){
	global $con, $smarty;
	
	$po_no = $_REQUEST['po_no'];
	
	$q2 = $con->sql_query("select do.*,branch.report_prefix as branch_prefix, branch.code as branch_name_1, b2.code as branch_name_2
	 from do
	left join branch on do.branch_id = branch.id
	left join branch b2 on do.do_branch_id = b2.id
	where do.active=1 and do.status<>4 and po_no=".ms($po_no)) or die(mysql_error());
	while ($r2= $con->sql_fetchrow($q2)){
 		$r2['open_info'] = unserialize($r2['open_info']);
		$r2['deliver_branch']=unserialize($r2['deliver_branch']);
		if($r2['deliver_branch']){
			foreach ($r2['deliver_branch'] as $k=>$v){
				$q3=$con->sql_query("select code from branch where id=$v");
				$r3 = $con->sql_fetchrow($q3);
				$r2['d_branch']['id'][$k]=$v;
				$r2['d_branch']['name'][$k]=$r3['code'];
			}
		}
		$used_do[]=$r2;
	}
	
	if(!$used_do)   die('OK');
	
	$smarty->assign('used_do',$used_do);
	$smarty->display('do.po_used_do.tpl');
}

function renumber_inv_no(){
	global $con, $config;
	
	if($config['do_invoice_separate_number'])   die('Config "do_invoice_separate_number" isset, cannot re-number');
	
	$con->sql_query("update do set inv_no=do_no where inv_no<>'' and inv_no<>do_no") or die(mysql_error());
	print $con->sql_affectedrows()." Updated.";
}

function ajax_show_paid_status(){
	global $con,$sessioninfo,$smarty,$config;
	
	// default payment list
	$payment_type = array("Others", "Cash", "Credit Card", "Bank Transfer", "TT from Oversea", "E-Wallet");
	
	$id = mi($_REQUEST['id']);
	$branch_id = mi($_REQUEST['bid']);
	if(BRANCH_CODE!='HQ'&&$sessioninfo['branch_id']!=$branch_id)  die("Error: Invalid Branch ID");
	
	$con->sql_query("select id, branch_id, paid, payment_date, payment_type, payment_remark from do where id=$id and branch_id=$branch_id");
	$form = $con->sql_fetchassoc();
	$con->sql_freeresult();
	if($form['payment_date'] == 0) $form['payment_date'] = date("Y-m-d");
	
	if($config['do_paid_addition_payment_type']){
		$additional_payment_type = $config['do_paid_addition_payment_type'];
		$payment_type = array_merge($payment_type, $additional_payment_type);
	}
	$payment_type = array_unique($payment_type);
	
	$smarty->assign("payment_type", $payment_type);
	$smarty->assign("form", $form);
	
	$ret = array();
	$ret['html'] = $smarty->fetch("do.paid_update.tpl");
	$ret['ok'] = 1;

	print json_encode($ret);
}

function ajax_update_paid(){
	global $con,$sessioninfo,$config;

	$id = mi($_REQUEST['id']);
	$branch_id = mi($_REQUEST['bid']);
	$paid = mi($_REQUEST['paid']);
	$paid_yes = ($paid==1? 'Yes':'No');
	if(BRANCH_CODE!='HQ'&&$sessioninfo['branch_id']!=$branch_id)  die("Error: Invalid Branch ID");

	if($config['do_generate_receipt_no']){
		$q1 = $con->sql_query("select do_receipt_no from do where branch_id = ".mi($branch_id)." and id = ".mi($id));
		$do_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		
		if(!$do_info['do_receipt_no']){
			$q1 = $con->sql_query("select max(do_receipt_no) as receipt_no from do where do_type in ('credit_sales','open') and branch_id = ".mi($branch_id));
			$receipt_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			
			if($receipt_info['receipt_no']) $upd['do_receipt_no'] = $receipt_info['receipt_no']+1;
			else $upd['do_receipt_no'] = 1;
		}
	}

	$upd['paid'] = $paid;
	if($paid == 1){
		$upd['payment_date'] = $_REQUEST['payment_date'];
		$upd['payment_type'] = $_REQUEST['payment_type'];
		$upd['payment_remark'] = $_REQUEST['payment_remark'];
	}
	$upd['last_update'] = 'CURRENT_TIMESTAMP';
	$con->sql_query("update do set ".mysql_update_by_field($upd)." where branch_id=$branch_id and id=$id");

	log_br($sessioninfo['id'], 'DELIVERY ORDER', $id, "Update paid status to '".($paid==1? 'Yes':'No')."' Branch ID#$branch_id, ID#$id");
	print "OK";
}

function do_create_from_sales_order(){
	global $con, $smarty, $sessioninfo, $LANG, $config;
	
	$order_no = $_REQUEST['order_no'];
	
	$filter = array();
	$filter[] = "status=1 and active=1 and approved=1";
	$filter[] = "order_no=".ms($order_no);
	//if(BRANCH_CODE!='HQ')   $filter[] = "branch_id=$sessioninfo[branch_id]";
	//$filter[] = "branch_id=$sessioninfo[branch_id]";
	$filter = "where ".join(' and ', $filter);
	
	$q1 = $con->sql_query("select * from sales_order $filter");
	$form = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	if(!$form){
        js_redirect(sprintf($LANG['SO_ORDER_CANNOT_CREATE_DO'], "'".$order_no."'"), "/do.php?page=credit_sales");
	}elseif($form['branch_id']!=$sessioninfo['branch_id']){
        js_redirect($LANG['SO_ORDER_CANNOT_CREATE_DIFF_BRANCH'], "/do.php?page=credit_sales");
	}elseif($form['exported_to_pos']){
		js_redirect(sprintf($LANG['SO_ORDER_ALREADY_EXPORT_TO_POS'], "'".$order_no."'"), "/do.php?page=credit_sales");
	}
	$new_do_id=time();
	if($new_do_id <= $_SESSION['do_last_create_time']) {$new_do_id = $_SESSION['do_last_create_time']+1;}
	$_SESSION['do_last_create_time'] = $new_do_id;
	$branch_id = $form['branch_id'];
	//$_REQUEST['debtor_id'] = $form['debtor_id'];
	if($form['selling_type']){	// Follow Price Indicator in Sales Order
		$_REQUEST['price_indicate'] = $form['selling_type'];
	}else{
		$_REQUEST['price_indicate'] = 2;	// Normal Selling
	}
	
	$_REQUEST['create_type'] = 4;
	$_REQUEST['ref_no'] = $order_no;
	$_REQUEST['is_special_exemption'] = $form['is_special_exemption'];
	$_REQUEST['special_exemption_rcr'] = $form['special_exemption_rcr'];
	$_REQUEST['use_debtor_price'] = mi($form['use_debtor_price']);
	
	// get items
	$q1 = $con->sql_query("select so.*,if(si.artno is null or si.artno='',si.mcode, si.artno) as artno_mcode, ifnull(uom.fraction,1) as uom_fraction, sic.qty as stock_balance
	from sales_order_items so
	left join sku_items si on so.sku_item_id=si.id
	left join uom on uom.id=so.uom_id
	left join sku_items_cost sic on sic.branch_id=so.branch_id and sic.sku_item_id=so.sku_item_id
	where so.branch_id=$form[branch_id] and so.sales_order_id=$form[id] order by id");
	$item_count = 0;
	while($r = $con->sql_fetchrow($q1)){
		$upd = array();
		$tmp = array();

		$tmp_do_date = date("Y-m-d", strtotime($form['order_date']." +1 day"));
		$tmp = get_sku_item_cost_selling($r['branch_id'], $r['sku_item_id'], $tmp_do_date, array("cost"));
		// check left qty
		$ctn = mf($r['ctn']);
		$pcs = mf($r['pcs']);
		$do_qty = mf($r['do_qty']);
		
		if($do_qty>0){
			if(($r['uom_fraction']>1)&&$ctn){   // uom fraction not 'EACH'
				$total_ctn_qty = $ctn*$r['uom_fraction'];
				if($do_qty>=$total_ctn_qty){
					$ctn = 0;
					$do_qty -= $total_ctn_qty;
				}else{
                    $total_ctn_qty -= $do_qty;
                    $do_qty = 0;
                    $ctn = mi($total_ctn_qty/$r['uom_fraction']);
                    $pcs += $total_ctn_qty-($ctn*$r['uom_fraction']);
				}
			}
			if($do_qty>0){
                $pcs -= $do_qty;
			}
		}
		
		if($ctn<=0&&$pcs<=0)    continue;
		
		$upd['do_id'] = $new_do_id;
		$upd['branch_id'] = $branch_id;
		$upd['user_id'] = $sessioninfo['id'];
		$upd['sku_item_id'] = $r['sku_item_id'];
		$upd['artno_mcode'] = $r['artno_mcode'];
		$upd['cost'] = $tmp['cost'];
		$upd['cost_price'] = $r['selling_price'];
		$upd['selling_price'] = $r['selling_price']/$r['uom_fraction'];
		$upd['uom_id'] = $r['uom_id'];
		$upd['ctn'] = $ctn;
		$upd['pcs'] = $pcs;
		$upd['stock_balance1'] = $r['stock_balance'];
		$upd['gst_id'] = $r['gst_id'];
		$upd['gst_code'] = $r['gst_code'];
		$upd['gst_rate'] = $r['gst_rate'];
		$upd['price_indicate'] = 'sales_order';
		
		if($r['item_discount'] && $config['do_credit_sales_have_discount'])	$upd['item_discount'] = $r['item_discount'];
		$con->sql_query("insert into tmp_do_items ".mysql_insert_by_field($upd));
		$item_count++;
	}
	
	if($item_count<=0){
        js_redirect(sprintf($LANG['SO_ORDER_ALREADY_FULLY_DELIVERED'], "'".$order_no."'"), "/do.php?page=credit_sales");
	}
	
	$smarty->assign('sales_order', $form);
	
	if($form['sheet_discount'] && $config['do_credit_sales_have_discount'])	$_REQUEST['discount'] = $form['sheet_discount'];
	
	return array('id'=>$new_do_id, 'branch_id'=>$branch_id);
}

function do_create_from_sales_order_by_batch(){
	global $con, $smarty, $sessioninfo, $LANG, $config;

	$batch_no = trim($_REQUEST['batch_code']);
	$branch_id = $sessioninfo['branch_id'];
	
	$filter_so = array();
	$filter_so[] = "branch_id=$branch_id";
	$filter_so[] = "status=1 and active=1 and approved=1";
	$filter_so[] = "batch_code=".ms($batch_no);
	$filter_so = "where ".join(' and ', $filter_so);
	$sql = "select order_no from sales_order $filter_so";

	$q_so = $con->sql_query($sql);

	if(!$con->sql_numrows($q_so)){
		js_redirect(sprintf($LANG['SO_ORDER_CANNOT_CREATE_DO_BY_BATCH'], "'".$batch_no."'"), "/do.php?page=credit_sales");
	}else{
		while($so = $con->sql_fetchassoc($q_so)){
			$order_no = $so['order_no'];
			
			$filter = array();
			$filter[] = "branch_id=$branch_id";
			$filter[] = "status=1 and active=1 and approved=1";
			$filter[] = "order_no=".ms($order_no);
			$filter = "where ".join(' and ', $filter);
			
			$q1 = $con->sql_query("select * from sales_order $filter");
			$form = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			if(!$form){
		        js_redirect(sprintf($LANG['SO_ORDER_CANNOT_CREATE_DO'], "'".$order_no."'"), "/do.php?page=credit_sales");
			}elseif($form['branch_id'] != $sessioninfo['branch_id']){
		        js_redirect($LANG['SO_ORDER_CANNOT_CREATE_DIFF_BRANCH'], "/do.php?page=credit_sales");
			}elseif($form['exported_to_pos']){
				js_redirect(sprintf($LANG['SO_ORDER_ALREADY_EXPORT_TO_POS'], "'".$order_no."'"), "/do.php?page=credit_sales");
			}

			$new_do_id = time();
			if($new_do_id <= $_SESSION['do_last_create_time']) {$new_do_id = $_SESSION['do_last_create_time']+1;}
			$_SESSION['do_last_create_time'] = $new_do_id;
			$branch_id = $form['branch_id'];
			//$_REQUEST['debtor_id'] = $form['debtor_id'];
			$_REQUEST['price_indicate'] = $form['selling_type'];
			$_REQUEST['create_type'] = 4;
			$_REQUEST['ref_no'] = $order_no;
			$_REQUEST['is_special_exemption'] = $form['is_special_exemption'];
			$_REQUEST['special_exemption_rcr'] = $form['special_exemption_rcr'];
			// get items
			$q1 = $con->sql_query("select so.*,if(si.artno is null or si.artno='',si.mcode, si.artno) as artno_mcode, ifnull(uom.fraction,1) as uom_fraction, sic.qty as stock_balance
			from sales_order_items so
			left join sku_items si on so.sku_item_id=si.id
			left join uom on uom.id=so.uom_id
			left join sku_items_cost sic on sic.branch_id=so.branch_id and sic.sku_item_id=so.sku_item_id
			where so.branch_id=$form[branch_id] and so.sales_order_id=$form[id] order by id");
			$item_count = 0;
			while($r = $con->sql_fetchrow($q1)){
				$upd = array();
				$tmp = array();

				$tmp_do_date = date("Y-m-d", strtotime($form['order_date']." +1 day"));
				$tmp = get_sku_item_cost_selling($r['branch_id'], $r['sku_item_id'], $tmp_do_date, array("cost"));
				// check left qty
				$ctn = mf($r['ctn']);
				$pcs = mf($r['pcs']);
				
				$do_qty = mf($r['do_qty']);
				
				if($do_qty > 0){
					if(($r['uom_fraction'] > 1) && $ctn){   // uom fraction not 'EACH'
						$total_ctn_qty = $ctn*$r['uom_fraction'];
						if($do_qty >= $total_ctn_qty){
							$ctn = 0;
							$do_qty -= $total_ctn_qty;
						}else{
		                    $total_ctn_qty -= $do_qty;
		                    $do_qty = 0;
		                    $ctn = mi($total_ctn_qty/$r['uom_fraction']);
		                    $pcs += $total_ctn_qty-($ctn*$r['uom_fraction']);
						}
					}
					if($do_qty > 0){
		                $pcs -= $do_qty;
					}
				}
				
				if($ctn <= 0 && $pcs <= 0)    continue;
				
				$upd['do_id'] = $new_do_id;
				$upd['branch_id'] = $branch_id;
				$upd['user_id'] = $sessioninfo['id'];
				$upd['sku_item_id'] = $r['sku_item_id'];
				$upd['artno_mcode'] = $r['artno_mcode'];
				$upd['cost'] = $tmp['cost'];
				$upd['cost_price'] = $r['selling_price'];
				$upd['selling_price'] = $r['selling_price']/$r['uom_fraction'];
				$upd['uom_id'] = $r['uom_id'];
				$upd['ctn'] = $ctn;
				$upd['pcs'] = $pcs;
				$upd['stock_balance1'] = $r['stock_balance'];
				$upd['gst_id'] = $r['gst_id'];
				$upd['gst_code'] = $r['gst_code'];
				$upd['gst_rate'] = $r['gst_rate'];
				
				if($r['item_discount'] && $config['do_credit_sales_have_discount'])	$upd['item_discount'] = $r['item_discount'];
				$con->sql_query("insert into tmp_do_items ".mysql_insert_by_field($upd));
				$item_count++;
			}
			$con->sql_freeresult($q1);
			
			if($item_count <= 0){
		        js_redirect(sprintf($LANG['SO_ORDER_ALREADY_FULLY_DELIVERED'], "'".$order_no."'"), "/do.php?page=credit_sales");
			}
			
			if($form['sheet_discount'] && $config['do_credit_sales_have_discount'])	$_REQUEST['discount'] = $form['sheet_discount'];
			
			$data = do_open_by_batch($new_do_id, $branch_id);

			$form += $data;

			$form['id'] = $new_do_id;
			$form['branch_id'] = $branch_id;
			$form['last_update'] = 'CURRENT_TIMESTAMP';
			$form['added'] = 'CURRENT_TIMESTAMP';
			$form['user_id'] = $sessioninfo['id'];
			$form['status'] = 0;
			$form['approved'] = 0;

			$do_branch_id_list = array();

		    if(count($form['deliver_branch']) == 1){
				$form['do_branch_id'] = $form['deliver_branch']['0'];
				$form['deliver_branch'] = '';
			}
			else{
				$form['deliver_branch'] = serialize($form['deliver_branch']);
			}
			$form['open_info'] = '';
			if(unserialize($form['deliver_branch'])){
				$is_deliver_multiple_branch = true;
			}
			if(!$form['exchange_rate']) $form['exchange_rate'] = 1;

			if (is_new_id($new_do_id)){
				if(!$form['create_type'])$form['create_type'] = 1;
				
				$temp = array('branch_id'=>$form['branch_id'], 'user_id'=>$form['user_id'], 'dept_id'=>$form['dept_id'], 'status'=>$form['status'], 'approved'=>$form['approved'], 'do_date'=>$form['do_date'],
				'added'=>$form['added'], 'deliver_branch'=>$form['deliver_branch'],'total_pcs'=>$form['total_pcs'], 'total_ctn'=>$form['total_ctn'], 'total_amount'=>$form['total_amount'], 
				'total_round_amt'=>$form['total_round_amt'], 'total_foreign_amount'=>$form['total_foreign_amount'], 'remark'=>$form['remark'],'approval_history_id'=>$form['approval_history_id'],
				'do_branch_id'=>$form['do_branch_id'], 'po_no'=>$form['po_no'], 'create_type'=>$form['create_type'], 'open_info'=>$form['open_info'],'price_indicate'=>$form['selling_type'],
				'do_type'=>$form['do_type'],'debtor_id'=>$form['debtor_id'],'discount'=>$form['discount'],'total_inv_amt'=>$form['total_amount'], 'total_round_inv_amt'=>$form['total_round_inv_amt'], 
				'total_foreign_inv_amt'=>$form['total_foreign_inv_amt'], "do_markup"=>$form['do_markup'],"sales_person_name"=>$form['sales_person_name'],"markup_type"=>$form['markup_type'], 
				"use_address_deliver_to"=>$form['use_address_deliver_to'], "address_deliver_to"=>$form['address_deliver_to'], "exchange_rate"=>$form['exchange_rate'],"allowed_user"=>$form['allowed_user'],"delivery_debtor_id"=>$form['delivery_debtor_id'], "integration_code"=>$form['integration_code'], "batch_code"=>$form['batch_code']);
				
				$temp['no_use_credit_sales_cost'] = $form['no_use_credit_sales_cost'];
				
				if($form['create_type']==4){    // create from sales order
					$temp['ref_tbl'] = 'sales_order';
					$temp['ref_no'] = $form['ref_no'];
				}

				$temp['markup_type'] = 'up';
				
				if($config['masterfile_enable_sa'] && $form['do_sa']) $temp['mst_sa'] = serialize($form['do_sa']);
				else $temp['mst_sa'] = "";

				// gst
				//if($config['enable_gst']){
					$temp["is_under_gst"] = $form["is_under_gst"];
				//}
				$temp['sub_total_gross_amt'] = $form['total_gross_amt'];
				$temp['sub_total_gst_amt'] = $form['total_gst_amt'];
				$temp['sub_total_amt'] = $form['total_amount'];
				
				$temp['do_total_gross_amt'] = $form['total_gross_amt'];
				$temp['do_total_gst_amt'] = $form['total_gst_amt'];
				$temp['is_special_exemption'] = $form['is_special_exemption'];
				if($temp['is_special_exemption']) $temp['special_exemption_rcr'] = $form['special_exemption_rcr'];
				else $temp['special_exemption_rcr'] = "";
				
				$temp['inv_total_gross_amt'] = $form['total_gross_amt'];
				$temp['inv_total_gst_amt'] = $form['total_gst_amt'];
				$temp['inv_sheet_gst_discount'] = $form['inv_sheet_gst_discount'];
				
				$temp['inv_sub_total_gross_amt'] = $form['total_gross_amt'];
				$temp['inv_sub_total_gst_amt'] = $form['total_gst_amt'];
				
				$temp['inv_sheet_gross_discount_amt'] = $form['inv_sheet_gross_discount_amt'];
				$temp['inv_sheet_discount_amt'] = $form['inv_sheet_discount_amt'];
				
				$temp['sub_total_inv_amt'] = $form['total_amount'];
				$temp['sub_total_foreign_inv_amt'] = $form['sub_total_foreign_inv_amt'];
				
				$temp['inv_sheet_foreign_discount_amt'] = $form['inv_sheet_foreign_discount_amt'];
				$temp['integration_code'] = $form['integration_code'];
				
				$con->sql_query("insert into do ".mysql_insert_by_field($temp));

				$form['id'] = $con->sql_nextid();
			}
			if($form['do_markup']){
				$form['do_markup_arr'] = explode("+", $form['do_markup']);
			}
			if($form['markup_type']=='down'){
		        $form['do_markup_arr'][0] *= -1;
		        $form['do_markup_arr'][1] *= -1;
			}

			//copy tmp table to do_items table
			$q1=$con->sql_query("select di.*,uom.fraction as uom_fraction
			from tmp_do_items di
			left join uom on uom.id=di.uom_id 
			where di.do_id=$new_do_id and di.branch_id=$branch_id and di.user_id=$sessioninfo[id] order by di.id") or die(mysql_error());
			$first_id = 0;
			$first_oi_id = 0;
			$total_amt = 0;
		    $inv_amt = 0;
			
			$currency_discount_params = array();
		    if($is_currency_mode){
				if($use_cost_indicate)	$currency_multiply = 1;
				else	$currency_multiply = $form['exchange_rate'];
				
				$currency_discount_params = array('currency_multiply'=>$currency_multiply);
				$currency_multiply_rate = 1/$form['exchange_rate'];
				
				if($use_cost_indicate)	$foreign_currency_discount_params['currency_multiply'] = $currency_multiply_rate;
			}
			
			if(count($do_branch_id_list)>1){
				$currency_discount_params['discount_by_value_multiply'] = count($do_branch_id_list);
				
				if($is_currency_mode){
					$foreign_currency_discount_params['discount_by_value_multiply'] = $currency_discount_params['discount_by_value_multiply'];
				}
			}
			//print_r($currency_discount_params);
			
			while($r=$con->sql_fetchassoc($q1)){
			    $amt_ctn = 0;
			    $amt_pcs = 0;
				$gross_amt = 0;
				$gst_amt = 0;
			    $row_amt = 0;
				$gross_inv_amt;
				$inv_gst_amt = 0;
			    $inv_discount_amt = 0;
				$row_inv_amt = 0;
			    $row_ctn = 0;
				$row_pcs = 0;
				$row_qty = 0;
				
				$upd['do_id']=$form['id'];
				$upd['branch_id']=$r['branch_id'];
				$upd['artno_mcode']=$r['artno_mcode'];
				$upd['po_cost']=$r['po_cost'];
				$upd['cost']=$r['cost'];
				$upd['cost_price']=$r['cost_price'];
				if(!$currency_code || $currency_code == $config["arms_currency"]["symbol"]) $upd['foreign_cost_price']=0;
				else $upd['foreign_cost_price']=$r['foreign_cost_price'];
				$upd['selling_price']=$r['selling_price'];
				$upd['uom_id']=$r['uom_id'];
				$upd['ctn']=$r['ctn'];
				$upd['pcs']=$r['pcs'];
				$upd['ctn_allocation']=$r['ctn_allocation'];
				$upd['pcs_allocation']=$r['pcs_allocation'];
				$upd['selling_price_allocation']=$r['selling_price_allocation'];
				$upd['price_type']=$r['price_type'];
				$upd['price_no_history'] = $r['price_no_history'];
				$upd['item_discount'] = $r['item_discount'];
				$upd['serial_no'] = $r['serial_no'];
				$upd['dtl_sa'] = $r['dtl_sa'];

				$upd['gst_id'] = $r['gst_id'];
				$upd['gst_code'] = $r['gst_code'];
				$upd['gst_rate'] = $r['gst_rate'];
				
				$upd['item_discount_amount'] = $r['item_discount_amount'];
				$upd['item_discount_amount2'] = $r['item_discount_amount2'];
				
				$upd['inv_line_gross_amt'] = $r['inv_line_gross_amt'];
				$upd['inv_line_gst_amt'] = $r['inv_line_gst_amt'];
				$upd['inv_line_amt'] = $r['inv_line_amt'];
				
				$upd['inv_line_gross_amt2'] = $r['inv_line_gross_amt2'];
				$upd['inv_line_gst_amt2'] = $r['inv_line_gst_amt2'];
				$upd['inv_line_amt2'] = $r['inv_line_amt2'];
				
				$upd['line_gross_amt'] = $r['line_gross_amt'];
				$upd['line_gst_amt'] = $r['line_gst_amt'];
				$upd['line_amt'] = $r['line_amt'];
				
				$upd['display_cost_price_is_inclusive'] = $r['display_cost_price_is_inclusive'];
				$upd['display_cost_price'] = $r['display_cost_price'];

				if($r['sku_item_id'] != 0){
					if($config['do_auto_group_items'] && !$form['is_under_gst'] && !$config['arms_marketplace_settings']){
						$ex_di_info = array();
						$is_existed_di = false;
						if(isset($existed_si[$r['sku_item_id']])){
							foreach($existed_si[$r['sku_item_id']] as $dummy=>$di){
								if($r['cost'] != $di['cost'] || $r['cost_price'] != $di['cost_price'] || $r['uom_fraction'] != $di['uom_fraction'] || $r['item_discount'] != $di['item_discount']) continue;
								else{
									$ex_di_info = $di;
									break;
								}
							}
						}
					}
					
					if(!$ex_di_info){
						$upd['sku_item_id']=$r['sku_item_id'];
						unset($upd['sku_item_code']);
						unset($upd['description']);
						$upd['stock_balance1'] = $r['stock_balance1'];
						$upd['stock_balance2'] = $r['stock_balance2'];
						$upd['stock_balance2_allocation'] = $r['stock_balance2_allocation'];
		                $upd['parent_stock_balance1'] = $r['parent_stock_balance1'];
						$upd['parent_stock_balance2'] = $r['parent_stock_balance2'];
		                $upd['parent_stock_balance2_allocation'] = $r['parent_stock_balance2_allocation'];
						$upd['price_indicate'] = $form['i_price_indicate'][$r['id']];
						$upd['bom_id'] = $r['bom_id'];
						$upd['bom_ref_num'] = $r['bom_ref_num'];
						$upd['bom_qty_ratio'] = $r['bom_qty_ratio'];
						
						$con->sql_query("insert into do_items ".mysql_insert_by_field($upd)) or die(mysql_error());
						$r['id'] = $con->sql_nextid();
					}
					
					if ($first_id == 0) $first_id = $r['id'];
				}
				
				if(!$is_existed_di) $existed_si[$r['sku_item_id']][] = $r;
			}
			$con->sql_freeresult($q1);

			// check if the do_open_items or do_items is equals to 0 record
			if($first_id != 0) $do_item = "and id<$first_id";
			if($first_oi_id != 0) $do_open_item = "and id<$first_oi_id";

			if ($first_id > 0 || $first_oi_id > 0){
				if(!is_new_id($new_do_id)){
					$con->sql_query("delete from do_items where branch_id=$branch_id and do_id=$new_do_id $do_item") or die(mysql_error());
					$con->sql_query("delete from do_open_items where branch_id=$branch_id and do_id=$new_do_id $do_open_item") or die(mysql_error());
				}

				$con->sql_query("delete from tmp_do_items where do_id=$new_do_id and branch_id = $branch_id and user_id = $sessioninfo[id]") or die(mysql_error());
			}
			else{
				die("System error: Insert do_items failed. Please do not open multiple DO page, close all other opened DO page and try again. If problem still exists please contact ARMS technical support.");
			}

			if($form['create_type'] == 4){    // create from sales order
				$q1 = $con->sql_query("select * from sales_order where branch_id = $branch_id and order_no=".ms($form['ref_no']));
				$sales_order = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
			
		 	    update_sales_order_do_qty($sales_order['id'], $sales_order['branch_id'], $sales_order);
			}

			if($config['consignment_modules']){
				update_do_sheet_price_type($form['branch_id'], $form['id']);
			}

			$formatted = sprintf("%05d",$form[id]);
		    //select report prefix from branch
			$con->sql_query("select report_prefix from branch where id = ".mi($branch_id));
			$r = $con->sql_fetchrow();
			
		    log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Saved: (ID#".$r['report_prefix'].$formatted." ,Pcs:$form[total_pcs], Ctn:$form[total_ctn], Amt:".sprintf("%.2f",$form['total_amount']).")");			
		}

		header("Location: /do.php?page=$form[do_type]&t=$form[a]&save_id=$form[id]");
	}

	$con->sql_freeresult($q_so);
}

function do_open_by_batch($id, $branch_id){
	global $con, $LANG, $sessioninfo, $smarty, $config, $gst_list, $appCore;
	
	$form = $_REQUEST;// keep the passed header

	//is new DO
	if ($id == 0){
		$id = time();
		if($id <= $_SESSION['do_last_create_time']) {$id = $_SESSION['do_last_create_time']+1;}
		$_SESSION['do_last_create_time'] = $id;
		$form['id'] = $id;
	}

	//IF THE DO IS NEW or refresh
	else{
		if(!isset($form['branch_id']))	$form['branch_id'] = $branch_id;
		$form['id'] = $id;
		if(!$form['do_date']) $form['do_date'] = date("Y-m-d");
		//if the DO is create from PO or upload file
		if($form['create_type'] > 1)
			$form['do_branch_name'] = get_branch_code($form['do_branch_id']);

        if(!$form['do_branch_id']){
            if(count($_REQUEST['deliver_branch']) < 2){
				$form['do_branch_id'] = $_REQUEST['deliver_branch'][0];
				unset($_REQUEST['deliver_branch']);
				unset($form['deliver_branch']);
			}
		}
		
		//get user list
		change_user_list(false,$form);
		
		// call this function to unset mprice type that is not available to this user
		sku_multiple_selling_price_handler();
		
		// check gst status
		if($config['enable_gst'] && $form['do_date']){
			$form['is_under_gst'] = check_do_gst_status($form);
		}
	}
	
	// load special exemption relief claus remark if it is new DO or existing DO but does not have the remark (edit mode)
	if($config['enable_gst'] && $form['is_under_gst'] && !$form['special_exemption_rcr']){
		$q1 = $con->sql_query("select * from gst_settings where setting_name = 'special_exemption_relief_claus_remark'");
		$sercr_info = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);

		$form['special_exemption_rcr'] = $sercr_info['setting_value'];
		if(!$form['special_exemption_rcr']) $form['special_exemption_rcr'] = $config['se_relief_claus_remark'];
	}
	
	// load branch group
	$brn_grp_list = array();
	$q1 = $con->sql_query("select bg.*, group_concat(bgi.branch_id separator ',') as grp_items from branch_group bg 
							left join branch_group_items bgi on bg.id=bgi.branch_group_id group by bg.id");
	
	while($r = $con->sql_fetchassoc($q1)){
		$brn_grp_list[] = $r;
	}
	$con->sql_freeresult($q1);
	//print_r ($brn_grp_list);
	$smarty->assign('brn_grp_list', $brn_grp_list);
  	
	//print_r($form);
	if($form['do_sa']) $form['mst_sa'] = $form['do_sa'];
	$smarty->assign("do_items", load_do_items($id, $branch_id, $form, true));
	
	load_branches_group();
	if($form['active']===0)    $smarty->assign("readonly", 1);
	if($form['po_no'])  $smarty->assign('from_po',1);

	return $form;
}

function ajax_search_batch_code(){
    global $con, $smarty, $sessioninfo;
    $branch_id = $sessioninfo['branch_id'];
    $v = trim($_REQUEST['value']);
    $LIMIT = 50;
    // call with limit
	$result1 = $con->sql_query("select distinct(batch_code) as batch_code from sales_order where branch_id = $branch_id and batch_code like ".ms('%'.replace_special_char($v).'%')." and status = 1 and approved = 1 and active = 1 order by batch_code limit ".($LIMIT+1));
    print "<ul>";
	if ($con->sql_numrows($result1) > 0)
	{

	    if ($con->sql_numrows($result1) > $LIMIT)
	    {
			print "<li><span class=informal>Showing first $LIMIT items...</span></li>";
		}

		// generate list.
		while ($r = $con->sql_fetchrow($result1))
		{
			$out .= "<li title=".htmlspecialchars($r['batch_code'])."><span>".htmlspecialchars($r['batch_code']);
			$out .= "</span>";
			$out .= "</li>";
		}
    }
    else
    {
       print "<li title=\"0\"><span class=informal>No Matches for $v</span></li>";
	}
	print $out;
    print "</ul>";
	exit;
}

function ajax_search_do_for_multiple_print(){
	global $con, $smarty, $sessioninfo;
	$filter = array();
	//print_r($_REQUEST);
	$search_type = trim($_REQUEST['search_type']);
	$no_from = trim($_REQUEST['no_from']);
	$no_to = trim($_REQUEST['no_to']);
	$do_type = $_REQUEST['do_type'];
	
	if(!$no_from||!$no_to)  die('No Data');
	if(BRANCH_CODE != 'HQ')	$filter[] = "do.branch_id=$sessioninfo[branch_id]";
  	//if($sessioninfo['level']<9999)	$filter[] = "do.user_id=$sessioninfo[id]";
  	
  	$filter[] = "do.active=1";
  	$filter[] = "do.do_type=".ms($do_type);
  	
  	if($search_type=='do_no'){
		$filter[] = "do.do_no between ".ms($no_from)." and ".ms($no_to);
	}elseif($search_type=='inv_no'){
        $filter[] = "do.inv_no between ".ms($no_from)." and ".ms($no_to);
	}else{  // search by id
		$do_id_from = mi(substr($no_from, -5));
		$do_id_to = mi(substr($no_to, -5));
		$branch_code1 = trim(substr($no_from, 0, -5));
		$branch_code2 = trim(substr($no_to, 0, -5));
		if($branch_code1!=$branch_code2)    die("Error: Cannot search different branch DO");
		$branch_code = $branch_code1;
		
		// select branch id by using branch code
		$con->sql_query("select id from branch where code=".ms($branch_code));
		$bid = mi($con->sql_fetchfield(0));
		$filter[] = "do.branch_id=$bid";
		$filter[] = "do.id between $do_id_from and $do_id_to";
		$filter[] = "do.approved=0 and do.checkout=0";
		
		if($search_type=='proforma_do'){
			$filter[] = "do.status=1";
		}else{
            $filter[] = "do.status=0";
		}
	}
	
	$con->sql_query("select id, report_prefix from branch");
	while($r = $con->sql_fetchrow()){
		$report_prefix[$r['id']] = $r['report_prefix'];
	}
	
	$filter = "where ".join(' and ', $filter);
	$sql = "select do.*,branch.code as do_branch_code,branch.description as do_branch_desc from do
	left join branch on branch.id=do.do_branch_id
	$filter";
	$con->sql_query($sql);
	while($r = $con->sql_fetchrow()){
		if(!$r['checkout']&&!$r['approved']){
			if($r['status']==1)	$r['is_proforma'] = 1;
			else    $r['is_draft'] = 1;
			$r['temp_do_no'] = $report_prefix[$r['branch_id']].sprintf("%05d", $r['id']);
		}
		$do_list[] = $r;
	}
	$smarty->assign('do_list', $do_list);
	$con->sql_freeresult();
	$smarty->display('do.multiple_print.list.tpl');
}

function multiple_print(){
    global $con, $sessioninfo, $smarty, $config;

	//print_r($_REQUEST);
	if(!$_REQUEST['print_do']&&!$_REQUEST['print_invoice']){
		print "<script>alert('Please tick Print DO or Print Invoice');</script>";
		exit;
	}
	$do_list = $_REQUEST['do_list'];
	if(!$do_list)   exit;
	
	foreach($do_list as $v){
	    $smarty->clear_all_assign();
	    $smarty->assign('sessioninfo', $sessioninfo);
	    $smarty->assign('config', $config);
	    
		list($bid, $id) = explode(",", $v);
		
		$form=load_do_header($id, $bid);
		if(!$form) continue;
		$do_items = load_do_items($id, $bid,$form);

		// Load all required data to append into object
		load_do_print_required_data($form, $do_items);
	
	    $smarty->assign("form", $form);
	    
		if($form['checkout']||$form['approved']){
            do_print2($id, $bid, $form, $do_items, $form['do_branch_id']);
		}else{
		    if($form['status']==1) $smarty->assign('is_proforma', 1);
			else    $smarty->assign('is_draft', 1 );
			
		    if($form['do_branch_id']||$form['do_type']=='open'||$form['do_type']=='credit_sales'){
                do_print2($id, $bid, $form, $do_items, $form['do_branch_id']);
			}elseif($form['deliver_branch']){
				foreach($form['deliver_branch'] as $do_bid){
					$smarty->assign("to_branch", $form['to_branch_list'][$do_bid]);
                    do_print2($id, $bid, $form, $do_items, $do_bid);
				}
			}
		}
	}
}

function ajax_get_sales_person_name(){
	global $con, $smarty;
	
	$v = trim($_REQUEST['value']);
	print "<ul>";
	if($v){
		$con->sql_query("select distinct(sales_person_name) from do where sales_person_name like ".ms('%'.replace_special_char($v).'%')." order by sales_person_name");
		while($r = $con->sql_fetchrow()){
			print "<li>".$r[0]."</li>";
		}
	}
	print "</ul>";
}

function do_ajax_add_size_color($id, $branch_id){
	global $con, $LANG;

	foreach ($_REQUEST['qty'] as $sku_item_id =>$quantity){
	    foreach ($quantity as $b_id => $qty){
	    	$find_char = strpos($_REQUEST['qty'][$sku_item_id][$b_id], "a");
	    	if($find_char == true){
				$qty = mi(str_replace("a", "", $_REQUEST['qty'][$sku_item_id][$b_id]));
			}
 
			if($qty > 0){
				if ($_REQUEST['deliver_branch'])
					$_REQUEST['qty_pcs'][$sku_item_id][$b_id] = $qty;
				else
					$_REQUEST['qty_pcs'][$sku_item_id] = $qty;

				$_REQUEST['sid'][$sku_item_id] = $sku_item_id;
			}
		}
	}
	
	save_multi_add();
}

function sn_validate(){
	global $con, $sessioninfo, $config, $LANG;
	$form=$_REQUEST;
	$err = array();

	if($form['sn']){
		foreach($form['sn'] as $id=>$bid_list){
			$sn_list = array();

			foreach($bid_list as $bid=>$dummy){
				$sku_item_id = $form['sn_sku_item_id'][$id];

				if($form['do_type'] == "transfer"){ // for transfer DO use
					if($form['deliver_branch']) $branch_code = get_branch_code($bid)." - ";
					$sn_list = explode("\n", $form['sn'][$id][$bid]);
					$duplicate_list = $db_sn_existed_list = $db_sn_ms_list = $db_sold_list = $db_inactive_list = $all_duplicate_list = $db_sn_list = $curr_sn_list = $db_sn_diff_located_branch_list = $db_sn_diff_si_list = array();
					$tmp_sn_list = $sn_list;
					$ttl_sn=$have_result=0;
			
					// check total S/N keyed in whether matched with rcv qty
					for($i=0; $i<count($sn_list); $i++){
						//$sn = preg_replace("/[^A-Za-z0-9]/","",trim($sn_list[$i]));
						$sn = trim($sn_list[$i]);
						if(!$sn) continue;
						$is_duplicated = "";
						for($j=0; $j<count($tmp_sn_list); $j++){
							//$tmp_sn = preg_replace("/[^A-Za-z0-9]/","",trim($tmp_sn_list[$j]));
							$tmp_sn = trim($tmp_sn_list[$j]);
							if($i == $j || !$tmp_sn) continue;
							if($sn == $tmp_sn) $is_duplicated = 1;
						}
						if($is_duplicated) $duplicate_list[$sn] = $sn; // found it is duplicated in the list
						$db_sn_list[$sn] = $sn; // to be use for the filter to S/N from database
						$curr_sn_list[] = $sn;
						$all_sn[$id][$bid][] = $sn;
						$ttl_sn++;
					}

					$r['ttl_sn'] = $ttl_sn;
			
					if($db_sn_list) $sn_list = join("', '", $db_sn_list);
			
					// check S/N against database
					$sql = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($sku_item_id)." and serial_no in ('".$sn_list."')");

					if($con->sql_numrows($sql)>0){
						while($r=$con->sql_fetchassoc($sql)){
							$db_sn_existed_list[$r['serial_no']] = $r['serial_no'];
							
							// check if the S/N is existed but located on other branch
							if($r['located_branch_id'] != $form['branch_id']){
								$db_sn_diff_located_branch_list[$r['serial_no']] = $r['serial_no'];
								continue;
							}
							
							if($r['status'] == 1) $db_sold_list[$r['serial_no']] = $r['serial_no'];
							elseif($r['active'] == 0) $db_inactive_list[$r['serial_no']] = $r['serial_no'];
						}
						
						// if found all S/N for this sku item not all existed
						if(count($db_sn_existed_list) != count($db_sn_list)){
							for($i=0; $i<count($curr_sn_list); $i++){
								if(!in_array($curr_sn_list[$i], $db_sn_existed_list)){
									$db_sn_ms_list[$curr_sn_list[$i]] = $curr_sn_list[$i];
								}
							}
						}
						$have_result = 1;
					}
					$con->sql_freeresult($sql);
					
					$sku_sn_list = array();
					foreach($db_sn_list as $tmp_sn){
						if(!$db_sn_existed_list[$tmp_sn]) $sku_sn_list[$tmp_sn] = $tmp_sn;
					}
					
					// search sn from other SKU items in order to provide error msg to user
					if($sku_sn_list){
						$sql = $con->sql_query("select * from pos_items_sn where serial_no in ('".join(",", $sku_sn_list)."')");
						
						if($con->sql_numrows($sql) > 0){
							while($r = $con->sql_fetchassoc($sql)){
								$db_sn_existed_list[$r['serial_no']] = $db_sn_diff_si_list[$r['serial_no']] = $r['serial_no'];
							}
							$con->sql_freeresult($sql);
							
							// if found all S/N for this sku item not all existed
							if(count($db_sn_existed_list) != count($db_sn_diff_si_list)){
								for($i=0; $i<count($curr_sn_list); $i++){
									if(!in_array($curr_sn_list[$i], $db_sn_existed_list)){
										$db_sn_ms_list[$curr_sn_list[$i]] = $curr_sn_list[$i];
									}else unset($db_sn_ms_list[$curr_sn_list[$i]]);
								}
							}
							$have_result = 1;
						}
					}
					
					if(!$have_result) $db_sn_ms_list = $db_sn_list; // straight treat all S/N for this sku item as not existed
					
					if(count($duplicate_list)>0){
						$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_DUPLICATE'], $branch_code, "<br />".join(", ", $duplicate_list));
					}
			
					if(count($db_sn_ms_list)>0){
						$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_INVALID'], $branch_code, "<br />".join(", ", $db_sn_ms_list));
					}

					if(count($db_sold_list)>0){
						$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_SOLD'], $branch_code, "<br />".join(", ", $db_sold_list));
					}

					if(count($db_inactive_list)>0){
						$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_INACTIVE'], $branch_code, "<br />".join(", ", $db_inactive_list));
					}
					if(count($db_sn_diff_located_branch_list)>0){
						$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_INVALID_BRANCH'], $branch_code, "<br />".join(", ", $db_sn_diff_located_branch_list));
					}
					if(count($db_sn_diff_si_list)>0){
						$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_INVALID_SKU'], $branch_code, "<br />".join(", ", $db_sn_diff_si_list));
					}
	
					// check between rcv qty and S/N qty matching or not
					if($form['b_sn_rcv_qty'][$id][$bid] != 0 && $ttl_sn == 0) $err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_EMPTY'], $branch_code);
					else{
						if($form['b_sn_rcv_qty'][$id][$bid] != $ttl_sn) $err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_INVALID_QTY'], $branch_code);
					}
	
					// this will proceed if found config set for allowing duplicated sku item
					if($config['do_item_allow_duplicate']){
						if(count($all_sn_by_si[$sku_item_id]) > 0){
							for($i=0; $i<count($curr_sn_list); $i++){
								if($all_sn_by_si[$sku_item_id][$bid]){
									$sn_by_si = explode(",", $all_sn_by_si[$sku_item_id][$bid]['sn_list']);
									if(in_array($curr_sn_list[$i], $sn_by_si)){
										$all_duplicate_list[$curr_sn_list[$i]] = $curr_sn_list[$i];
									}
								}
							}
						}
	
						if(count($all_duplicate_list)>0){
							$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_SKU_DUPLICATE'], $branch_code, join(", ", $all_duplicate_list));
						}
	
						if($all_sn_by_si[$sku_item_id][$bid]['sn_list']) $all_sn_by_si[$sku_item_id][$bid]['sn_list'] .= ",";
						$all_sn_by_si[$sku_item_id][$bid]['sn_list'] .= join(",", $db_sn_list);
					}
	
					if($all_sn[$id][$bid]) $_REQUEST['sn'][$id][$bid] = join("\n", $all_sn[$id][$bid]);
				}else{ // for credit and cash sales uses
					//$sn = preg_replace("/[^A-Za-z0-9]/","",trim($form['sn'][$id][$bid]));
					$sn = trim($form['sn'][$id][$bid]);
					$row = $bid+1;

					if(!$sn){
						//$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_EMPTY'], "Row [".$row."]");
						$sn_is_empty[$row] = $row;
						continue;
					}

					if(!$form['sn_nric'][$id][$bid] || !$form['sn_name'][$id][$bid] || !$form['sn_address'][$id][$bid]){
						//$err['sn'][$id][$bid][] = sprintf($LANG['DO_SN_INCOMPLETE'], "Row [".$row."]");
						$sn_is_incomplete[$row] = $row;
					}

					// check if existed in following sku item
					if($sn){
						foreach($form['sn'][$id] as $temp_bid=>$dummy){
							if($bid != $temp_bid && $sn == $form['sn'][$id][$temp_bid]){
								$duplicate_list[$sn] = $sn;
							}
						}
					}

					// check S/N against database
					$sql = $con->sql_query("select * from pos_items_sn where sku_item_id = ".mi($sku_item_id)." and serial_no = ".ms($sn));
					$pis = $con->sql_fetchassoc($sql);
					$con->sql_freeresult($sql);

					if(!$pis['serial_no']){ // is an invalid S/N
						$sql = $con->sql_query("select * from pos_items_sn where serial_no = ".ms($sn));
						$pis2 = $con->sql_fetchassoc($sql);
						$con->sql_freeresult($sql);
					
						if($pis2['sku_item_id'] && $pis2['sku_item_id'] != $sku_item_id){ // found got matched but at other SKU item
							$db_sn_diff_si_list[$sn] = $sn;
						}else $db_sn_ms_list[$sn] = $sn;
					}elseif($pis['located_branch_id'] != $sessioninfo['branch_id']){ // found got matched but located in other branch
						$db_sn_diff_located_branch_list[$sn] = $sn;
					}else{ // found it is existed in DB, check its status and active...
						if($pis['status'] == 1) $sn_sold_list[$sn] = $sn; // item sold
						elseif($pis['active'] == 0) $sn_inactive_list[$sn] = $sn; // it is not activated
					}

					if(count($all_sn_by_si[$sku_item_id]) > 0){
						$temp_sn = explode(",", $all_sn_by_si[$sku_item_id]['sn_list']);
						if(in_array($sn, $temp_sn)){
							$all_duplicate_list[$sn] = $sn;
						}
					}
					$temp_sn_list[$sn] = $sn;
				}
			}

			if($form['do_type'] != "transfer"){
				if(count($sn_is_empty) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_EMPTY'], "Row [".join(", ", $sn_is_empty)."]");
				if(count($sn_is_incomplete) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_INCOMPLETE'], "Row [".join(", ", $sn_is_incomplete)."]");
				if(count($duplicate_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_DUPLICATE'], "", "<br />".join(", ", $duplicate_list));
				if(count($db_sn_ms_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_INVALID'], "", "<br /> ".join(", ", $db_sn_ms_list));
				if(count($sn_sold_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_SOLD'], "", "<br /> ".join(", ", $sn_sold_list));
				if(count($sn_inactive_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_INACTIVE'], "", "<br /> ".join(", ", $sn_inactive_list));
				if(count($all_duplicate_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_SKU_DUPLICATE'], "", join(", ", $all_duplicate_list));
				if(count($temp_sn_list) > 0){
					if($all_sn_by_si[$sku_item_id]) $all_sn_by_si[$sku_item_id]['sn_list'] .= ",";
					$all_sn_by_si[$sku_item_id]['sn_list'] .= join(",", $temp_sn_list);
				}
				if(count($db_sn_diff_located_branch_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_INVALID_BRANCH'], "", "<br /> ".join(", ", $db_sn_diff_located_branch_list));
				if(count($db_sn_diff_si_list) > 0) $err['sn'][$id][] = sprintf($LANG['DO_SN_INVALID_SKU'], "", "<br /> ".join(", ", $db_sn_diff_si_list));
				$sn_is_empty = $sn_is_incomplete = $duplicate_list = $db_sn_ms_list = $sn_sold_list = $sn_inactive_list = $all_duplicate_list = $db_sn_diff_located_branch_list = $db_sn_diff_si_list = array();
			}
		}
	}

	return $err;
}

function all_update_sheet_price_type(){
	global $con, $config;
	
	if(!$config['consignment_modules'])	die('Only for consignment mode');
	
	$q1 = $con->sql_query("select branch_id,id, if((select id from do_items di where di.branch_id=do.branch_id and di.do_id=do.id limit 1)>0,1,0) as got_items
	from do where sheet_price_type=''
	having got_items=1");
	while($r = $con->sql_fetchassoc($q1)){
		update_do_sheet_price_type($r['branch_id'], $r['id']);
	}
	$con->sql_freeresult($q1);
	print "Done";
}

function update_debtor_credit_sales_price(){
	global $con, $smarty, $sessioninfo, $config, $gst_list;
	
	$form = $_REQUEST;
	//print_r($form);
	
	$ret = array();
	$si_data = array();
	
	if($form['cost_price']){
		foreach($form['cost_price'] as $item_id => $cost_price){
			
			$sid = mi($form['inp_sku_item_id'][$item_id]);
			if(!$sid)	continue;
			
			if(!$si_data[$sid]){
				// get gst
				/*$output_gst = array();
				if($config['enable_gst'] && $form['is_under_gst']){
					$output_gst = get_sku_gst("output_tax", $sku_item_id);
						if($output_gst){
							$item['gst_id'] = $output_gst['id'];
							$item['gst_code'] = $output_gst['code'];
							$item['gst_rate'] = $output_gst['rate'];
						}
				}*/
				
				$output_gst = array();
				if($config['enable_gst'] && $form['is_under_gst']){
					if($_REQUEST['is_special_exemption']){
						$use_gst = get_special_exemption_gst();
						$is_special_exemption = true;
					}else{
						$output_gst = get_sku_gst("output_tax", $sid);
						if($output_gst){
							$use_gst = $output_gst;
						}
					}
					
					if(!$use_gst){
						$use_gst = $gst_list[0];
					}
					
					$item['gst_id'] = $use_gst['id'];
					$item['gst_code'] = $use_gst['code'];
					$item['gst_rate'] = $use_gst['rate'];	
					
				}
				
				$si_data[$sid] = get_item_price($sid, $form['branch_id'], $form['price_indicate'], $form, $use_gst, $is_special_exemption);
			}
			
			$ret['item_info'][$item_id] = $si_data[$sid];
		}
	}
	
	$ret['ok'] = 1;
	print json_encode($ret);
}

function check_tmp_item_exists() {
	global $con, $sessioninfo;
	
	if ($_REQUEST['master_uom_id']) {
		$sql = "select count(*) as c from tmp_do_items where id in (".join(',',array_keys($_REQUEST['master_uom_id'])).") and branch_id = ".mi($_REQUEST['branch_id'])." limit 1";
		$con->sql_query($sql);
		if ($con->sql_fetchfield('c') == count($_REQUEST['master_uom_id'])) print 'OK';
		else print "Error saving document : Probably it is opened & saved before in other window/tab";
		exit;
	}
	else {
		print 'OK';
		exit;
	}
}

function ajax_show_sn_by_range(){
	global $con, $smarty, $sessioninfo;
	
	$form = $_REQUEST;

	if(!$form['sid']) die("No SKU ITEM ID found.");
	if(!$form['deliver_branch']) die("No Deliver Branch selected.");
	
	$q1 = $con->sql_query("select * from sku_items where id = ".mi($form['sid']));
	$si_info = $con->sql_fetchassoc($q1);
	$con->sql_freeresult($q1);
	
	// single branch
	if(count($form['deliver_branch']) == 1){
		$branch_code = get_branch_code($form['deliver_branch'][0]);
		$branch_id = $form['deliver_branch'][0];
		$smarty->assign("branch_code", $branch_code);
		$smarty->assign("branch_id", $branch_id);
	}else{
		$q1 = $con->sql_query("select * from branch where id in (".join(",", $form['deliver_branch']).") order by sequence, code");
		
		$deliver_branch = array();
		while($r = $con->sql_fetchassoc($q1)){
			$deliver_branch[$r['id']] = $r;
		}
		$con->sql_freeresult($q1);
		$smarty->assign("deliver_branch", $deliver_branch);
		$smarty->assign("branch_id", $form['branch_id']);
	}
	
	$smarty->assign("si_info", $si_info);
	$smarty->assign("item_id", $form['item_id']);
	
	$smarty->assign("is_add_sn_by_range", 1);
	
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch("do.sn.new.tpl");
	
	print json_encode($ret);
}

function ajax_parent_child_add(){
	global $con, $LANG;
	
	save_multi_add();
}

function recalculate_do_amt(){
	global $con, $config, $sessioninfo;
		
	$bid = mi($_REQUEST['branch_id']);
	if(!$config['single_server_mode']){
		$bid = $sessioninfo['branch_id'];
	}
	$from = $_REQUEST['from'];
	$to = $_REQUEST['to'];
	
	if(!$from || !$to)	die("No date from/to.");
	
	$filter = array();
	$filter[] = "do.active=1 and do.status=1";
	if($bid)	$filter[] = "do.branch_id=$bid";
	$filter[] = "do.do_date between ".ms($from)." and ".ms($to);
	
	$filter = "where ".join(' and ', $filter);
	
	$sql = "select branch_id,id from do $filter";
	print "$sql<br/>";
	$updated_count = 0;
	
	$q1 = $con->sql_query($sql);
	while($r = $con->sql_fetchassoc($q1)){
		auto_update_do_all_amt($r['branch_id'], $r['id']);
		$updated_count++;
	}
	$con->sql_freeresult($q1);
	
	print "Done. $updated_count updated.";
}

function data_validate(&$form){
	global $con, $config, $LANG, $sessioninfo, $branch_id;
	
	$errm = array();

	// check SN when user confirming the DO
	if($form['is_confirm']){
		$sn_errm = sn_validate();
		if($sn_errm){
			$errm['top'][] = sprintf($LANG['DO_SN_ERROR']);
			$errm['sn'] = $sn_errm['sn'];
		}
	}
	
	if(!$form['uom_id']) $errm['top'][] = sprintf($LANG['DO_EMPTY']);
	
	$arr= explode("-",$form['do_date']);
	$yy=$arr[0];
	$mm=$arr[1];
	$dd=$arr[2];
	if(!checkdate($mm,$dd,$yy)){
	   	$errm['top'][] = $LANG['DO_INVALID_DATE'];
		$form['do_date']='';
	}
	
	if ($config['consignment_global_disable_documents_after_monthly_report'] && $config['consignment_modules'] && is_monthly_report_printed($form['do_date'],$branch_id)) {
		$errm['top'][] = $LANG['CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED'];
	}

	$check_date = strtotime($form['do_date']);

	if (isset($config['upper_date_limit']) && $config['upper_date_limit'] >= 0){
		$upper_limit = $config['upper_date_limit'];
		$upper_date = strtotime("+$upper_limit day" , strtotime("now"));

		if ($check_date>$upper_date){
		   	$errm['top'][] = $LANG['DO_DATE_OVER_LIMIT'];
			$form['do_date']='';
		}

	}

	if (isset($config['lower_date_limit']) && $config['lower_date_limit'] >= 0){
		$lower_limit = $config['lower_date_limit'];
		$lower_date = strtotime("-1 day",strtotime("-$lower_limit day" , strtotime("now")));


		if ($check_date<$lower_date){
		   	$errm['top'][] = $LANG['DO_DATE_OVER_LIMIT'];
			$form['do_date']='';
		}
	}

	// check transaction end date
    if($config['consignment_modules']){
		// check deliver from
		if($form['branch_id']){
			$dl_fr_info = array();
			$q1 = $con->sql_query("select trans_end_date from branch where id = ".mi($form['branch_id']));
			$dl_fr_info = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);
			if($dl_fr_info['trans_end_date'] > 0){
				$trans_end_times = strtotime($dl_fr_info['trans_end_date']);
				if($check_date > $trans_end_times) $errm['top'][] = sprintf($LANG['MSTBRANCH_OVER_TRANS_END_DATE'], get_branch_code($form['branch_id']),"for Deliver From");
			}
		}
		
		// check deliver to
		if($form['do_branch_id']){
			$dl_fr_info = array();
			$q2 = $con->sql_query("select trans_end_date from branch where id = ".mi($form['do_branch_id']));
			$dl_fr_info = $con->sql_fetchassoc($q2);
			$con->sql_freeresult($q2);
			if($dl_fr_info['trans_end_date'] > 0){
				$trans_end_times = strtotime($dl_fr_info['trans_end_date']);
				if($check_date > $trans_end_times) $errm['top'][] = sprintf($LANG['MSTBRANCH_OVER_TRANS_END_DATE'], get_branch_code($form['do_branch_id']), "for Deliver To");
			}
		}
	}
	
	if ($config['do_approval_by_department'] && !$form['dept_id'])	$errm['top'][] = sprintf($LANG['DO_NO_DATA'],"department data");

	if($form['create_type']==2){
		if(isset($form['open_info']['name']) && $form['open_info']['name']=='')
	   		$errm['top'][] = $LANG['DO_OPEN_INFO_NAME_EMPTY'];
		if(isset($form['open_info']['address']) && $form['open_info']['address']=='')
	   		$errm['top'][] = $LANG['DO_OPEN_INFO_ADDRESS_EMPTY'];
	}elseif($form['create_type']==4){
		$q1 = $con->sql_query("select * from sales_order where order_no=".ms($form['ref_no']));
		$sales_order = $con->sql_fetchassoc($q1);
		$con->sql_freeresult($q1);
		if(!$sales_order)   $errm['top'][] = sprintf($LANG['SO_ORDER_NO_NOT_FOUND'], $form['ref_no']);
	}
	
	// check item qty that cannot equal to zero
	if($form['uom_id']){
		foreach($form['uom_id'] as $k=>$v){
			$curr_ctn = $curr_pcs = 0;
			if($form['deliver_branch']){
				foreach($form['deliver_branch'] as $d_bid){
					$curr_ctn+=mf($form['qty_ctn'][$k][$d_bid]);
					$curr_pcs+=mf($form['qty_pcs'][$k][$d_bid]);
				}
			}else{
				$curr_ctn=mf($form['qty_ctn'][$k]);
				$curr_pcs=mf($form['qty_pcs'][$k]);
			}
			
			if($curr_ctn<=0 && $curr_pcs<=0){
				$errm['top'][] = $LANG['DO_ITEM_ZERO_QTY'];
				break;
			}
		}
	}
	
	return $errm;
}

function do_export(){
	global $smarty, $con, $sessioninfo;
	
	$form = $_REQUEST;
	$formatted=sprintf("%05d",$form['id']);
	$form = load_do_header($form['id'], $form['branch_id']);
	$do_items = load_do_items($form['id'], $form['branch_id'], $form);
	$smarty->assign("form", $form);
	$smarty->assign("do_items", $do_items);
	
	//select report prefix from branch and branch info
	$branch_list = array();
	$q1 = $con->sql_query("select * from branch");
	
	while($r = $con->sql_fetchassoc($q1)){
		$branch_list[$r['id']] = $r;
	}
	$con->sql_freeresult($q1);
	$smarty->assign("branch_list", $branch_list);

	include_once("include/excelwriter.php");
	$filename = "do_export_".time().".xls";
	log_br($sessioninfo['id'], 'DELIVERY ORDER', $form['id'], "Export DO#ID".$branch_list[$form['branch_id']]['report_prefix'].$formatted." To Excel($filename)");
	Header('Content-Type: application/msexcel');
	Header('Content-Disposition: attachment;filename='.$filename);

	print ExcelWriter::GetHeader();
	$smarty->display("do.export.tpl");
	//print file_get_contents($filename);
	print ExcelWriter::GetFooter();
	exit;
}


function load_do_print_required_data(&$form, &$do_items){
	global $smarty, $con, $sessioninfo, $config;
	
	if($form['use_address_deliver_to']){
		$form['address_deliver_to'] = trim($form['address_deliver_to']);
		if(!$form['address_deliver_to'])	$form['address_deliver_to'] = $form['b2_deliver_to'];
	}
	
	// From Branch
	$con->sql_query("select * from branch where id = $form[branch_id]");
	$from_branch = $con->sql_fetchrow();
	$smarty->assign("from_branch", $from_branch);
	$con->sql_freeresult();
	
	$hide_RSP = 0;
	if($form['do_type'] == "transfer"){
		$con->sql_query("select * from branch where id =".mi($form['do_branch_id']));
		$to_branch = $con->sql_fetchrow();
		$con->sql_freeresult();
		
		if($form['do_type'] == "transfer" && $config['consignment_modules'] && $config['masterfile_branch_region'] && $to_branch['region']){
			$to_branch['currency_code'] = strtoupper($config['masterfile_branch_region'][$to_branch['region']]['currency']);
		}
		if(!$to_branch['currency_code']) $to_branch['currency_code'] = $config["arms_currency"]["symbol"];
		
		$smarty->assign("to_branch", $to_branch);

		if($form['deliver_branch']){
			foreach($form['deliver_branch'] as $do_bid){
				$con->sql_query("select * from branch where id = $do_bid");
				$tmp_to_branch = $con->sql_fetchrow();
				$con->sql_freeresult();
				
				if($form['do_type'] == "transfer" && $config['consignment_modules'] && $config['masterfile_branch_region'] && $tmp_to_branch['region']){
					$tmp_to_branch['currency_code'] = strtoupper($config['masterfile_branch_region'][$tmp_to_branch['region']]['currency']);
				}
				if(!$tmp_to_branch['currency_code']) $tmp_to_branch['currency_code'] = $config["arms_currency"]["symbol"];
				$form['to_branch_list'][$do_bid] = $tmp_to_branch;
			}
		}
		
				
        if($config['do_printing_hide_RSP_transfer']) $hide_RSP=1;
	}elseif($form['do_type'] == "credit_sales"){
		$con->sql_query("select * from debtor where id =".mi($form['debtor_id']));
		$to_debtor = $con->sql_fetchrow();
		$smarty->assign("to_debtor", $to_debtor);

        if($config['do_printing_hide_RSP_credit']) $hide_RSP=1;
	}
    else{
        if($config['do_printing_hide_RSP_cash']) $hide_RSP=1;
    }
	
    $smarty->assign("hide_RSP", $hide_RSP);
	
	// fix do no field
	if(!$form['approved']){
		if($form['status']==1)	$form['do_no'] = $from_branch['report_prefix'].sprintf('%05d',$form['id'])."(PD)";
		else 	$form['do_no'] = $from_branch['report_prefix'].sprintf('%05d',$form['id'])."(DD)";
	}
	
	// get sales agent code for printing purpose
	$sa_code_list = array();
	if($form['mst_sa']){
		$q1 = $con->sql_query("select * from sa where id in (".join(",", $form['mst_sa']).")");
		
		while($r = $con->sql_fetchassoc($q1)){
			if(!$sa_code_list[$r['code']]) $sa_code_list[$r['code']] = $r['code'];
		}
	}

	// get sales agent code from item and parent artno
	if(isset($do_items)){
		foreach($do_items as $key=>$r){
			if($r['dtl_sa']){
				$q1 = $con->sql_query("select * from sa where id in (".join(",", $r['dtl_sa']).")");
				
				while($r = $con->sql_fetchassoc($q1)){
					if(!$sa_code_list[$r['code']]) $sa_code_list[$r['code']] = $r['code'];
				}
			}
			
			if($r['sku_id']){
				$q1 = $con->sql_query("select * from sku_items where sku_id = ".mi($r['sku_id'])." and is_parent = 1 limit 1");
				$parent_info = $con->sql_fetchassoc($q1);
				$do_items[$key]['parent_artno'] = $parent_info['artno'];
			}
		}
	}

	if($sa_code_list){
		asort($sa_code_list);
		$form['sa_code_list'] = join(", ", $sa_code_list);
	}
}

function open_upload_csv(){
	global $con, $sessioninfo, $config, $smarty, $appCore;
	
	$do_type = trim($_REQUEST['do_type']);
	if($do_type != 'transfer'){
		display_redir($_SERVER['PHP_SELF'], "Delivery Order", "This module support Transfer DO only");
	}
	
	// Check if got upload file
	if(isset($_FILES['import_csv']) && $_FILES['import_csv']){
		// Create Folder to store csv file
		if (!is_dir("attachments"))	check_and_create_dir("attachments");
		$folder_path = "attachments/import_multiple_do";
		if (!is_dir($folder_path))	check_and_create_dir($folder_path);
		
		$err = array();
		$file = $_FILES['import_csv'];
		
		// File Upload failed
		if($file['error'] > 0){
			$err = "Please upload csv file again.";
		}
		
		// Check extension
		if(!$err){
			if(strtolower(substr($file['name'], -3))!='csv'){
				$err = "Please ensure the file extension is 'csv'.";
			}
		}
		
		if(!$err){
			$f = fopen($file['tmp_name'], "rt");
			if(!$f)	$err[] = "Failed to read the file.";
		}
		
		if($err){
			$smarty->assign('err', $err);
		}else{
			// copy as backup
			copy($file['tmp_name'], $folder_path."/file_".time().".csv");
			
			// skip first header row
			$r = fgetcsv($f);
			
			// Get Branch List
			$branch_list = $appCore->branchManager->getBranchesList(array('active'=>1));
			$bcode_to_bid_list = array();
			
			//print_r($branch_list);
			$data = array();
			$line_no = 1;
			$uomForEACH = $appCore->uomManager->getUOMForEach();
						
			// No Error
			while($r = fgetcsv($f)){
				$r2 = array();
				
				$line_no++;
				$err_msg = '';
				$sid = 0;
				
				$r2['to_branch_code'] = strtoupper(trim($r[0]));
				$r2['item_code'] = trim($r[1]);
				$r2['uom_code'] = strtoupper(trim($r[2]));
				$r2['ctn'] = mf($r[3]);
				$r2['pcs'] = mf($r[4]);
				
				if(!$r2['to_branch_code'])	continue;	// skip row if first column no data
				
				if(isset($bcode_to_bid_list[$r2['to_branch_code']])){
					$to_bid = $bcode_to_bid_list[$r2['to_branch_code']];
				}else{
					$to_bid = $bcode_to_bid_list[$r2['to_branch_code']] = get_branch_id($r2['to_branch_code']);
				}
				
				// Invalid Branch
				if(!$to_bid){
					$err_msg = "Invalid Branch Code";
				}else{
					if($to_bid == $sessioninfo['branch_id']){
						$err_msg = "Cannot deliver to own branch.";
					}
				}
				
				// No Item Code
				if(!$err_msg){
					if(!$r2['item_code']){
						$err_msg = "Empty Item Code";
					}
				}
				
				// Ctn and Pcs
				if(!$err_msg){
					if($r2['ctn'] < 0){
						$err_msg = "CTN cannot less than zero";
					}
					
					if(!$err_msg && $r2['pcs'] < 0){
						$err_msg = "PCS cannot less than zero";
					}
				}
				
				// UOM Code
				if(!$err_msg){
					if(!$r2['uom_code']){
						// Default EACH
						$r2['uom_id'] = $uomForEACH['id'];
						$r2['uom_fraction'] = 1;
						$r2['uom_code'] = $uomForEACH['code'];
					}else{
						$uom = $appCore->uomManager->getUOMbyCode($r2['uom_code']);
						if(!$uom){
							$err_msg = "Invalid DO UOM Code";
						}else{
							$r2['uom_id'] = $uom['id'];
							$r2['uom_fraction'] = $uom['fraction'];
						}
					}
					
					if(!$err_msg){
						// Check UOM Fraction
						if($r2['uom_fraction'] == 1 && $r2['ctn']>0){
							$err_msg = "DO UOM Fraction is 1, cannot assign Qty CTN";
						}
					}
				}
				
				// Search SKU
				if(!$err_msg){
					$params = array();
					$params['active'] = 1;
					$params['limit'] = 1;
					$sid_list = $appCore->skuManager->searchSKUbyCode($r2['item_code'], $params);
					if($sid_list)	$sid = $sid_list[0];
					
					if(!$sid)	$err_msg = "Item Not Found";
				}
				
				if(!$err_msg){
					// Select SKU Details
					if(!isset($data['si_info'][$sid])){
						$con->sql_query("select si.sku_item_code, si.mcode, si.artno, si.link_code, si.packing_uom_id, si.description, uom.code as packing_uom_code, uom.fraction as packing_uom_fraction
							from sku_items si
							left join uom on uom.id=si.packing_uom_id
							where si.id=".mi($sid));
						$data['si_info'][$sid] = $si = $con->sql_fetchassoc();
						$con->sql_freeresult();
					}
					
					// Check UOM
					if($si['packing_uom_fraction'] != 1 && $r2['uom_fraction'] != 1){
						$err_msg = "Packing UOM Fraction is not 1, cannot use DO UOM other than fraction 1";
					}
				}
				
				// Not Allow Duplicate Item
				if(!$err_msg && !$config['do_item_allow_duplicate']){
					// Find Duplicated SKU
					if($data['branch_list'][$to_bid]){
						foreach($data['branch_list'][$to_bid] as $b_item){
							if($b_item['sku_item_id'] == $sid){	// Same SKU already used
								$err_msg = "Duplicated SKU Found in same branch.";
								break;
							}
						}
					}
				}
				
				if($err_msg){	// This row got error
					$r2['error'] = $err_msg;
					
					// Store into error data
					$data['error_data'][$line_no] = $r2;
				}else{
					$r2['to_bid'] = $to_bid;
					$r2['sku_item_id'] = $sid;
					
					// Store data by branch
					$data['branch_list'][$to_bid][] = $r2;
				}
				
				
			}
			//print_r($data);
			$smarty->assign('data', $data);
			$smarty->assign('show_result', 1);
		}
	}
	
	$smarty->assign('do_type', $do_type);
	$smarty->display('do.open_upload_csv.tpl');
	
}

// DO csv sample
function download_sample_do_csv(){
	$do_type = 'transfer';
		
	$headers = array(
		 "DELIVER TO BRANCH",
		 "ITEM CODE",
		 "DO UOM",
		 "CTN",
		 "PCS"
	);
	
	$sample = array(
		array("GURUN", "280000050000", "EACH", "0", "3"),
		array("GURUN", "280000050001", "CTN4", "2", "1"),
		array("BALING", "280000090000", "EACH", "0", "10")
	);

	header("Content-type: application/msexcel");
	header("Content-Disposition: attachment; filename=sample_import_do_csv.csv");
	
	print join(",", array_values($headers)) . "\n";
	foreach($sample as $r) {
		$data = array();
		foreach($r as $d) {
			$data[] = $d;
		}
		print join(",", $data) . "\n";
	}
}

function ajax_generate_multi_do(){
	global $con, $sessioninfo, $config, $smarty, $appCore;
	
	$form = $_REQUEST;
	//print_r($form);
	
	$do_type = trim($form['do_type']);
	// check branch changed
	if($form['from_bid'] != $sessioninfo['branch_id']){
		die("Error: System detected your branch has changed, process failed.");
	}
	
	if($do_type != 'transfer'){
		die("This module support Transfer DO only");
	}
	
	$to_bid = mi($form['to_bid']);
	
	$do_data = array();
	if($form['item_list']){
		// Loop Branch
		foreach($form['item_list'] as $deliver_bid => $b_item_list){
			// Got choose only deliver to one branch.
			if($to_bid && $to_bid != $deliver_bid)	continue;
			
			// Loop SKU
			foreach($b_item_list as $row_no => $r){
				if(!$r['item_selected'])	continue;	// This item is not selected
								
				// Store into list first
				$do_data[$deliver_bid]['item_list'][] = $r;
			}
		}
	}
	
	// No item to generate
	if(!$do_data){
		die("Error: No item is selected.");
	}
	
	//print_r($do_data);
	$do_id_list = array();
	
	// Loop DO Branch
	foreach($do_data as $deliver_bid => $b_data){
		$tmp_generate_do = array();
		$tmp_generate_do['guid'] = $appCore->newGUID();
		$tmp_generate_do['branch_id'] = $sessioninfo['branch_id'];
		$tmp_generate_do['do_type'] = $do_type;
		$tmp_generate_do['do_branch_id'] = $deliver_bid;
		$tmp_generate_do['user_id'] = $sessioninfo['id'];
		$tmp_generate_do['added'] = 'CURRENT_TIMESTAMP';
		
		$con->sql_query("insert into tmp_generate_do ".mysql_insert_by_field($tmp_generate_do));
		
		// Loop SKU
		$sequence = 0;
		foreach($b_data['item_list'] as $r){
			$sequence++;
			
			$tmp_generate_do_items = array();
			$tmp_generate_do_items['guid'] = $appCore->newGUID();
			$tmp_generate_do_items['gen_do_guid'] = $tmp_generate_do['guid'];
			$tmp_generate_do_items['sku_item_id'] = $r['sid'];
			$tmp_generate_do_items['uom_id'] = $r['uom_id'];
			$tmp_generate_do_items['ctn'] = $r['ctn'];
			$tmp_generate_do_items['pcs'] = $r['pcs'];
			$tmp_generate_do_items['added'] = 'CURRENT_TIMESTAMP';
			$tmp_generate_do_items['sequence'] = $sequence;
			$con->sql_query("insert into tmp_generate_do_items ".mysql_insert_by_field($tmp_generate_do_items));
		}
		
		// Create DO
		$do_id = mi($appCore->doManager->createDOFromTMP($tmp_generate_do['guid']));
		
		$do_id_list[] = $do_id;
	}
	
	$smarty->assign('do_id_list', $do_id_list);
	$ret = array();
	$ret['ok'] = 1;
	$ret['html'] = $smarty->fetch('do.open_upload_csv.generate_result.tpl');
	
	print json_encode($ret);
	exit;
}

function sort_print_do_items($a, $b){
	global $sort_print_item_sequence;
	
	if($a[$sort_print_item_sequence] == $b[$sort_print_item_sequence])	return 0;
	return $a[$sort_print_item_sequence] > $b[$sort_print_item_sequence] ? 1 : -1;
}

//export approved DO items
function export_approved_do($id, $branch_id){
	global $appCore;
	
	$appCore->doManager->export_do($id, $branch_id);
	exit;
}
?>