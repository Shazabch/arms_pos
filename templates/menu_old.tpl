{*
Revision History
================
4 Apr 2007 - yinsee
- 	check for $config.sku_application_require_multics to enable/disable multics code import menu

6 Apr 2007 - gary
-   add "Shift Record" menu under "Office"

4/20/07 3:37:01 PM  yinsee
- change branch_code=='GURUN' to level>=9999 for "Update Multics DAT files"

5/9/2007 6:46:04 PM   yinsee
- add menu for sku summary

10/1/2007 2:37:50 PM gary
- add right control for update profile for users_activate

11/22/2007 11:17:20 AM gary
- change title of GRA VENDOR SUMMARY to GRA LISTING.

5/23/2008 12:42:31 PM yinsee
- add Membership Termination

9/3/2008 5:31:14 PM yinsee
- membership / performance reports access control 

2008-9-23 - Andy
- Add Master file SKU Group

9/24/2008 11:50:20 AM yinsee
- rename REPORT_* to PIVOT_* privilege
- add DO summary

2008-9-24 4:00:00 PM Andy
- Add Sales Target under Administrator

2008-9-29 2:23 PM Andy
- Add POS Live under Front End

2008-10-22 5:05 PM Andy
- Add Sub Menu "SKU Report"

2008-11-07 5:15:00 PM Andy
- Daily Brand Sales/Daily Veendor Sales/Department Monthly Sales/Daily category sales pull from old sales report to New Sales report

2008-11-28 4:06:00 PM Andy
- Add POS Dropdown under front end and add report Transaction Detail to this dropdown

2008-12-02 4:04:00 PM Andy
- add Cashier Performance Report under Pos Report

2008-12-04 3:06:00 PM Andy
- add privilege checking for POS_REPORT

2008-12-08 5:23:00 PM Andy
- add Counter Collection below Cost Report under front end->pos report

12/27/2008 5:42:58 PM yinsee
- add go to branch
- curtain now call "default_curtain_clicked()" onclick

12/30/2008 5:39:46 PM yinsee
- add "add membership" (check by membership_allow_add_at_backend)

4/21/2009 4:48:31 PM yinsee
- group stock check report

10/1/2009 4:00 PM Andy
- Add DO Request and DO Request Process menu

12/4/2009 3:03:52 PM Andy
- Add Membership Redemption Menu

12/15/2009 11:10:46 AM Andy
- Add Stock Balance Summary

1/14/2010 10:26:53 AM edward
- Add MEMBERSHIP_ADD & MEMBERSHIP_VERIFY privilege checking

3/17/2010 10:36:00 AM edward
- add $config.counter_collection_server to open counter collection in new window

4/21/2010 1:13:16 PM Andy
- add counter collection vs category sales report under pos report

5/11/2010 5:52:27 PM Andy
- "Stock Check" rename to "Import Stock Take"
- "Stock Take Handheld" rename to "Stock Take".
- Stock Take move to under "Store".
- Split add/edit and import/reset stock take.

5/12/2010 9:43:25 AM Andy
- Add "DO Summary By Day / Month under Office->DO"

05/25/2010 06:02:43 PM yinsee
- rearrange the Administrator menu (grouping users, import/export)

5/31/2010 3:09:27 PM Andy
- Fix a bugs cause administrator menu not enough long.

6/4/2010 9:40:49 AM Andy
- Allow user with only privilege "USERS_ACTIVATE" can enter update users.

6/4/2010 1:21:48 PM Alex
- Add Settings menu

6/15/2010 9:59:22 AM Andy
- Temporary open back "POS Transaction Import" module.

6/17/2010 9:42:41 AM Andy
- Fix Store menu sometime too short problem.

6/23/2010 10:04:30 AM Andy
- Add server maintenance (Archive Database) feature.

8/5/2010 6:17:19 PM Andy
- Add SKU monitroing group and sku monitoring 2 at menu.

8/11/2010 2:26:41 PM Justin
- Added new report menu called "Redemption Points History Report"

8/12/2010 3:12:42 PM yinsee
- add REPORTS_SKU permission for SKU Reports

8/12/2010 4:49:40 PM Andy
- Fix checking wrong sessioninfo for sku reports permission.

8/13/2010 10:16:53 AM Andy
- Add Fresh Market Write-off menu and Fresh Market Stock Take menu.

8/27/2010 10:46:04 AM Justin
- Added Membership Redemption Approval and Redemption Verification.
- Both need to see config['membership_redemption_use_approval'] and privileges.

9/1/2010 3:15:50 PM Andy
- Add Bank Interest Master File.

9/3/2010 3:24:49 PM Andy
- Add new menu "Fresh Market" and move all fresh market module to become its submenu, need privilege "FM" to access.

10/28/2010 5:02:38 PM Justin
- Changed all the config for enhanced Membership Redemption become membership_redemption_use_enhanced.

11/8/2010 10:13:36 AM Andy
- Change to load menu immediately after the page load finish, no need to wait for images.

11/16/2010 11:17:46 AM Alex
- add stock copy

11/23/2010 11:18:00 AM Alex
- add coupon

11/29/2010 11:53:30 AM Andy
- Add new menu link to fresh market sales report, need privilege to access.

12/14/2010 4:00:35 PM Andy
- Add new menu "Import Selling Price".

1/4/2011 2:00:28 PM Andy
- Fix wrong privilege checking on CN/DN.

1/4/2011 2:10:38 PM Andy
- Add New Report: Credit Note Summary and Debit Note Summary

1/7/2011 3:41:57 PM Justin
- Added S/N IBT Validation tab at Office - Delivery Order.
- Added S/N Listing tab at Masterfile - SKU.
- Added S/N Summary Report tab at Reports - SKU.

1/12/2011 3:20:36 PM Andy
- Add New Module: Import POS Sales

1/13/2011 1:34:37 PM Justin
- Redirect POS Live and Sales Live to trigger data from Data Center if found config.

1/17/2011 12:27:46 PM Andy
- add custom_reports

1/28/2011 6:12:27 PM Andy
- New Report: Consignment Branch Sales by Price Type Report

2/10/2011 9:44:15 AM Alex
- add Daily Counter Collection Report at POS Report

2/23/2011 10:27:04 AM Justin
- Added the config check for report of membership point history.

2/23/2011 4:50:13 PM Andy
- Change report title: "Stock Balance Report" to "Stock Balance Report by Department".

2/25/2011 2:57:30 PM Andy
- Change to immediately initialize menu dropdown once menu element is loaded.

3/14/2011 9:47:01 AM Andy
- New Report: Stock Reorder Report

3/18/2011 10:47:51 AM Alex
- Added Vouncher reports menu.

3/23/2011 4:31:25 PM Justin
- Add new menu "Update Price Type".

3/29/2011 5:36:29 PM Justin
- Renamed the php file name (Branch Sales by Price Type Report).

3/30/2011 11:26:12 AM Andy
- New Module: Config Manager & Privilege Manager (can only access by user 1 and HQ)

3/31/2011 4:57:34 PM Andy
- Temporary hide Server Maintenance.

4/12/2011 10:30:05 AM Alex
- add checking on pivot to hide sales report, officer report and management report if found no exist.

5/25/2011 9:52:46 AM Andy
- Add new report: GRN Distribution Report
- Add new module "Supermarket Code"

5/26/2011 9:52:46 AM Justin
- Added config "enable_sn_bn" to enable/disable all Serial and Batch No modules and reports.

5/30/2011 9:52:46 AM Justin
- Added "Forex" tab into Consignment section.

6/1/2011 10:18:25 AM Andy
- Change "Master File Bank Interest" only available when there is a config to turn on SKU Monitoring 2.

6/6/2011 3:20:17 PM Andy
- Add new module "Web Bridge"->"AP Trans".

6/13/2011 5:28:02 PM Alex
- grouping report into consignment report
- hide report if not consignment modules

6/20/2011 2:38:26 PM Andy
- Remove "Export Weighing Scale Items" module from menu if it is consignment mode.

6/22/2011 5:38:21 PM Justin
- Added new menu for "Serial No Expiry Report".
- Renamed the hyperlink for Serial No Summary Report and its name become "Serial No Activation Report".

6/23/2011 4:00:21 PM  Justin
- Hidden GRN Account Verification menu if found using GRN Future.

7/8/2011 5:18:21 PM Andy
- New Report: Counter Collection Detail Report

8/1/2011 5:44:05 PM Andy
- Add config to sku monitoring report.

8/15/2011 12:19:53 PM Alex
- add masterfile_sku.price_list.php

8/25/2011 3:46:12 PM Andy
- Add checking privilege "PO_REPORT" for PO Qty Performance.
- Add checking for report privilege to show report dropdown in menu.

8/24/2011 2:44:04 PM Andy
- New Module: Cashier Setup

9/12/2011 12:04:19 PM Andy
- Add menu "Change Batch" under "Stock Take".

10/12/2011 4:03:53 PM Andy
- Change title "Daily Counter Collection Report" to "Daily Counter Collection Cash Domination".
- Comment out "Serial No - IBT Validation".

10/25/2011 3:37:53 PM Andy
- Add "Counter Collection Details Report" at POS Report.

12/14/2011 12:05:32 PM Justin
- Added new menu list for Sales Agent.

12/14/2011 1:31:01 PM Andy
- Fix Branches list cannot be open.

12/21/2011 5:51:00 PM Kee Kee
- change "Lock Price Report" to "Temp Price Report"

12/27/2011 5:18:04 PM Andy
- Add new module "AR Trans".
- Add new module "CC Trans".

1/9/2012 5:38:43 PM Justin
- Renamed the name "Block / Unblock SKU (CSV)" into "Block / Unblock SKU in PO (CSV)".
- Changed the width for "Administrator" tab from 160 to 180.

1/20/2012 5:06:43 PM Justin
- Added new menu link "Sales Agent Commission Calculation Report" and "Sales Agent Performance Report".

2/3/2012 9:36:43 AM Justin
- Added new report link "Sales Agent Commission Statement by Company Report".

2/15/2012 11:09:35 AM Alex
- change checking privilege on FRONT-END contents

2/20/2012 10:46:54 AM Justin
- Added new pos reports "Cross Branch Deposit", "Cancel Deposit" and "Deposit In/Out".

3/2/2012 12:06:32 PM Justin
- Added new SKU report "New SKU Sales Monitoring Report".

3/5/2012 12:00:49 PM Andy
- Add New Module: AP Trans Settings.
- Add New Module: AR Trans Settings.
- Add New Module: CC Trans Settings.

3/23/2012 9:59:12 AM Andy
- Fix checking on SKU Monitoring Report.

4/5/2012 12:07:12 PM Justin
- Added new masterfile report "Return Policy Item Returned Report" and "Return Policy Pending Item Report".

4/5/2012 3:18:04 PM Andy
- Add new report "Sales Order Monitor Report".

4/18/2012 10:30:17 AM Andy
- New POS Report: Trade In Report

4/18/2012 5:00:48 PM Andy
- Remove "Stock Balance Report by Day" from menu. This report now only for consignment use.
- Reconstruct "POS Report" to have submenu base on their report type.
- New Module: Manage Trade In Write-Off

5/10/2012 10:45:34 AM Justin
- Fixed wording error.

5/22/2012 10:50:23 AM Justin
- Added new reports Membership Expiration and Renewal Reports.

5/28/2012 1:52 PM yinsee
- change Home menu to Home > Dashboard for iPad usability issue

6/1/2012 11:56 AM dingren
- New Module eForm

6/13/2012 11:50:12 AM Justin
- Added to hide Transfer DO & SKU Application while current logged on branch was franchise.

7/10/2012 5:10 PM Andy
- Add Vendor Portal Menu.
- Add new report in vendor portal "Sales Report by Day"

7/12/2012 4:07 PM Andy
- Add "Go to branch" feature for Vendor Portal.
- Add "Change Selling Price" Module for Vendor Portal.

7/19/2012 10:13 AM Andy
- New POS Report: SKU Transaction Details Report.

7/19/2012 5:04 PM Andy
- New Module: Vendor Price List.

7/23/2012 2:04 PM Justin
- New Report: GRA Disposal Report.

7/24/2012 2:10 PM Andy
- New Report "Sales Report by Week" and "Sales Report by Month".

8/1/2012 4:57 PM Andy
- New Vendor Report: Sales Summary by Day.

8/6/2012 5:28 PM Andy
- New Module "Purchase Agreement".

8/7/2012 9:56 PM DingRen
- New Module Delivery for member.

8/27/2012 10:55 AM Justin
- New report "Stock Balance Detail by Day".

8/27/2012 11:15 AM Andy
- Remove Multic PO Cost module.

9/19/2012 11:47:00 AM Fithri
- Add change batch for fresh market stock take

9/26/2012 3:14 PM Andy
- Add new module "Counter Collection CO2". need config "counter_collection_enable_co2_module".

10/3/2012 4:36 PM Andy
- New vendor report "Consignee Daily Sales Report".

10/17/2012 5:26 PM Andy
- Add new pos report "POS Return Item Report".

10/22/2012 2:45 PM Andy
- Hide vendor report "Sales Summary by Day".

11/15/2012 11:57 AM Justin
- Enhanced to add new privilege "MEMBERSHIP_REDEEM_RPT" for redemption reports.

2:07 PM 11/15/2012 Justin
- Added new reports "Membership Fees Collection Summary Report" and "Membership Daily Collection Report".

11/23/2012 10:14 AM Andy
- Add $smarty.server.DOCUMENT_ROOT prefix for file_exists checking to fix some of the menu not show when in custom report other other sub-folder.
- Remove Counter Collection Details Report from common pos report.

3:40 PM 11/28/2012 Justin
- Enhanced to move all membership reports into a tab.
- Added a new report "Membership Points Detail Report"

12/7/2012 5:21 PM Justin 
- Added new menu link "Stock Take - Zerolize Negative Stocks" (available for consignment customers only).

12/14/2012 3:55 PM Andy
- Add new report "DO Request Rejected Report".

12/18/2012 12:22 PM Justin
- Bug fixed on membership menu hard to click.

12/27/2012 5:21 PM Andy
- Add new vendor portal module "Repacking".

1/2/2013 5:58 PM Justin
- Add new vendor portal module "GRN".

1/3/2013 5:58 PM Fithri
- Add new vendor portal module "Disposal".

1/7/2013 3:34 PM Fithri
- Add new vendor portal module "Stock Take".

1/15/2013 PM Andy
- Add new module "Membership Staff Card". (need config membership_enable_staff_card)

1/22/2013 2:21 PM Andy
- Add new report "Repacking Report".

11:29 AM 1/23/2013 Justin
- Reconstructed "Branch" to have submenu.
- Added new module "Branch Additional Selling Price".

2/1/2013 3:56 PM Fithri
- mix and match promotion change to no need config, always have for all customer

3/7/2013 4:28 PM Andy
- Remove Category Markup Module from menu.

4/2/2013 5:50 PM Andy
- Add debtor portal menu.

4/8/2013 5:15 PM Andy
- Rename "Inactive Users Report" to "No-Activity Users Report".

4/25/2013 2:50 PM Andy
- Add new module "SKU Application Revise List".

4/29/2013 10:21 AM Andy
- Change to only allow user id =1 to add/edit/delete pivot report.

5/28/2013 3:07 PM Justin
- Bug fixed on Shift Record module does not check file exists or not.

5/30/2013 2:57 PM Justin
- Enhanced to have new privilege checking "MST_SALES_AGENT" for Sales Agent.
- Disabled  the "Go to Branch" feature for Debtor Portal.

6/20/2013 4:35 PM Justin
- Added new menu "Import Member Points".

7/10/2013 11:20 AM Andy
- Add new custom menu. (need config custom_extra_menu)

07/23/2013 03:42 PM Justin
- Added new reports Serial No Status and Return Reports.

08/01/2013 05:33 PM Justin
- Enhanced the "Import Member Points" to must be equal to system admin or higher while can access.

08/02/2013 03:16 PM Justin
- Enhanced the menu to auto hide Office and Store and left SKU Application there while 2 of these config is turned on.

8/6/2013 10:29 AM Justin
- Added new menu "Import Members".

8/21/2013 4:10 PM Andy
- Remove module "Deposit Cancellation".
- Add new module "Deposit Listing".
- Rename "In/Out Deposit Report" to "Daily Deposit In/Out Report".

9/5/2013 11:44 AM Justin
- Enhanced to show out Store and Office while at sub branch.

2:23 PM 9/12/2013 Justin
- Enhanced to add config checking "membership_control_counter_adjust_point" for Membership Delivery module.

10/11/2013 11:55 AM Justin
- Added new menu "Reset Data".

10/29/2013 5:40 PM Fithri
- added new report - SKU Sales Report

11/5/2013 11:45 AM Fithri
- change all term "Cash Domination" to "Cash Denomination"

11/15/2013 3:50 PM Fithri
- fix spelling mistake (Marketing Tools)

3/24/2014 5:56 PM Justin
- Modified the wording from "Color" to "Colour".

4/18/2014 4:45 PM Justin
- Added new menu "PO Reorder Qty by Branch".

4/29/2014 11:01 AM Justin
- Enhanced to enable back "Counter Collection Detail Report".

5/15/2014 2:15 PM Justin
- Enhanced to have full menu of SKU for AMRS-GO customers.

5/29/2014 1:30 PM Fithri
- Change Consignment Monthly Report to only check for privilege CON_MONTHLY_REPORT.
- Enhance the main consignment report menu to check all sub-menu privilege.

7/11/2014 10:54 AM Justin
- Enhanced to have Import SKU (need config, privilege and super admin) under Administrator.

9/9/2014 11:10 AM Justin
- Enhanced to have Masterfile GST and GST Settings.

1/28/2015 1:31 PM Justin
- Enhanced to have GST Price Wizard.
- Add config checking for Masterfile GST & GST Settings.

02/03/2015 2.15PM dingren
- add new menu Account Export
- add new menu Account Export Setting

3/19/2015 10:07 AM Andy
- Move GST Setting to Administrator.
- Add checking user level 9999 to allow access GST Setting.

4/2/2015 3:05 PM Andy
- New GST Report: Counter Sales GST Report

12:26 PM 4/6/2015 Andy
- Add new "Old Reports" section 

4/8/2015 10:13 AM Andy
- Fix the old report link.

2:47 PM 4/30/2015 Andy
- New GST Report: "Receipt Summary GST Report".

05/05/2015 10.17AM dingren
- add new menu Account Export for arms go module
- add new menu Account Export Settings for arms go module

2:47 PM 4/30/2015 Justin
- New Sales Agent Report: "Sales Agent Daily Details Report".

8/3/2015 11:25 AM Joo Chia
- Add new menu Debit Note.

8/28/2015 5:13 PM Andy
- Rename "Masterfile GST Settings" to "GST Settings".
- Rename "Masterfile GST" to "Masterfile GST Tax Code".

9/1/2015 5:54 PM Andy
- Added new module "Credit Note". (only for retailer modue)

11/3/2015 2:18 PM DingRen
- add po, do, so, adjustment approval link
- add Consignment Invoice, Consignment CN, Consignment DN approval link

11/11/2015 2:18 PM DingRen
- Change CN, GRN and GRA into submenu
- add GRA approval link, add GRN approval link

12/07/2015 11:00 AM Qiu Ying
- add Import Vendor and Import Brand link

12/08/2015 11:00 AM Qiu Ying
- add Set UP link

12/16/2015 14:10 AM Qiu Ying
- add Service Charge link

12/23/2015 12:37 PM Andy
- Fix SKU Import privilege checking.

1/12/2016 4:36 PM Andy
- Fix vendor portal Go to branch not working.

2/25/2016 9:44 AM Andy
- Fix Import SKU menu access checking.

05/12/2016 11:00 Edwin
- Add MPrice Sales Report

05/20/2016 14:30 Edwin
- Add Transaction Details with Item Listing

05/31/2016 15:00 Edwin
- Removed Return Policy in Master Files menu.

06/28/2016 17:00 Edwin
- Add GST Credit Note Report

07/21/2016 16:00 Edwin
- Rename file from 'admin.import_members' to 'admin.preactivate_member_cards'.

07/27/2016 16:00 Edwin
- Add Import Debtor and Import UOM

07/28/2016 16:30 Edwin
- Add E-Journal

8/1/2016 5:37 PM Andy
- Fixed Import Debtor & Import UOM privilege checking.

08/02/2016 16:30 Edwin
- Add Audit Log

09/21/2016 10:44 Kee Kee
- Change "Account Export" to "Account & GAF Export"
- Change "Account Export Setting" to "Account & GAF Export Setting"

9/26/2016 17:13 Qiu Ying
- Add SKU Stock Balance Listing (Download)

10/7/2016 4:30 PM Andy
- Hide Temp Price Report.

10/19/2016 13:46 PM Qiu Ying
- Hide Reset Data

11/9/2016 12:08 PM Andy
- Hide 'Import POS Sales' module.
- Added new module 'Update SKU Master Cost'.

11/14/2016 2:00 PM Andy
- Enhanced 'Update SKU Master Cost' module to need system admin + privilege to access.

11/30/2016 13:45 Qiu Ying
- Add new module "Custom Accounting Export"

12/2/2016 9:53 AM Andy
- Hide menu POS DB.

12/13/2016 5:00 PM Andy
- Enhanced "Office" menu to show when got ACCOUNT_EXPORT privilege.

1/11/2017 5:24 PM Andy
- Add New Membership Report: Membership Issued Points Report

1/13/2017 3:46 PM Andy
- Add New SKU Report: Multi Branch Stock Balance.

2/7/2017 10.56 AM Zhi Kai
- Change 'CN' to 'Credit Note'
- Change 'CN Approval' to 'Credit Note Approval'

2/9/2017 10:19 AM Andy
- Change "SKU Items GP" to "SKU Items Gross Profit Report".
- Change the title of 'Masterfile SKU Price List' to 'SKU Price List'. 

#3/23/2017 16:04 Qiu Ying
#- Add New SKU Report: Multi Branch Sales Report.

3/31/2017 2:34 PM Zhi Kai
-Add new module 'Membership Overview'.

4/20/2017 10:25 AM Justin
- Enhanced to have privilege checking for "Sales Order Create/Edit" and "Sales Order Report".

5/12/2017 10:26 AM Justin
- Enhanced to comment out "Change Selling Price" from Vendor Portal.

5/12/2017 10:26 AM Justin
- Added new module "Deactivate SKU by CSV".

5/31/2017 3:57 PM Justin
- Bug fixed on the privilege checking for Deactivate SKU by CSV.

6/6/2017 1:59 PM Justin
- Enhanced to un-comment the "Change Selling Price" from Vendor Portal.

6/6/2017 11:14 AM Qiu Ying
- Enhanced to rearrange report menu

6/13 2017 1:45 PM Khausalya
- Removed config checking in import SKU, vendor, brand

6/14/2017 10:53 AM Andy
- Change to allow access "Import SKU" if user is system admin or got privilege ALLOW_IMPORT_SKU.
- Fixed wrong checking of privilege "BRAND_IMPORT", should be "ALLOW_IMPORT_BRAND".
- Fixed wrong checking of privilege "VENDOR_IMPORT", should be "ALLOW_IMPORT_VENDOR".

2017-08-28 09:19 AM Qiu Ying
- Enhanced to add new POS report "Abnormal Clocking Report"

9/11/2017 5:48 PM Justin
- added new menu "Broken Size & Color by Branch Report".

9/12/2017 9:42 AM Andy
- Added New Module "Matrix IBT Process".

10/10/2017 11:04 AM Andy
- Add checking for Vendor Portal Simplified Mode.

11/7/2017 3:18 PM Justin
- Enhanced to have new report "Sales Report by Day by Receipt" and "Daily SKU Stock Balance Report" for Vendor Portal while using config "vp_simplified_mode".

11/10/2017 9:48 AM Justin
- Bug fixed on Custom Account Export doesn't show out while in ARMS GO mode.

11/13/2017 1:38 PM Justin
- Added new POS report "Cash Advance Report".

1/30/2018 1:59 PM Andy
- Hide Report "Abnormal Clocking Log".

2/9/2018 10:37 AM Justin
- Added new SKU report "Closing Stock Report" & "Closing Stock by SKU Report".

3/8/2018 1:58 PM Justin
- Added new module "DO Preparation".

3/28/2018 4:12 PM Andy
- Added new module "Foreign Currency".

7/13/2018 3:30PM HockLee
- Added new module "Transporter v2".

8/21/2018 11:28 AM Andy
- Added new module "Debtor Price List".

9/4/2018 1:25 PM Andy
- Enhanced "Print Full Tax Invoice" to able to print non-gst transaction.

9/18/2018 5:45 PM Andy
- Change Front End GST Report to become available if got config.

10/10/2018 3:34 PM Andy
- Added new PO Report "SKU Purchase History".

10/26/2018 5:47 PM Justin
- Added new modules "Update SKU Brand by CSV" and "Update SKU Vendor by CSV".

10/30/2018 5:30 PM Andy
- Added new SKU Report "Brand Sales by Price Type and Discount Report".

10/31/2018 2:22 PM Justin
- Added new module "Update SKU Stock Reorder Min & Max Qty by CSV".

11/23/2018 3:20 PM Justin
- Added new module "Quotation Cost".
- Bug fixed on some modules can only accessed be from HQ but still able to access from sub branches.

12/4/2018 2:20 PM Justin
- Added new report "SKU Receiving History".

12/10/2018 4:16 PM Andy
- Added new module "Suite Device".

12/13/2018 10:11 AM Justin
- Changed report name.

12/19/2018 05:46 PM Justin
- Added new module "Update SKU Category by CSV".

12/27/2018 06:01 PM Justin
- Added new menu "Install Barcode Font".

1/14/2019 11:55 AM Justin
- Added new module "Update SKU Category Discount by CSV".

3/5/2019 2:58 PM Andy
- Rename "Stock Balance Detail by Day Report" to "Stock Balance Detail by SKU Report".

4/10/2019 3:03 PM Andy
- Added new module "ARMS Accounting Integration".

5/8/2019 3:42 PM Andy
- Added new module "OS Trio Accounting Integration".

5/21/2019 5:57 PM Andy
- Added new module "Cycle Count".

6/24/2019 2:26 PM William
- Rename "Upload Logo" title to "Edit Logo Settings".

7/2/2019 4:55 PM Andy
- Added new module "Membership Mobile Advertisement Setup".

7/16/2019 10:49 AM William
- Added new module "Stock Take Inquiry".

8/7/2019 10:17 AM William
- Added new module "CN Summary".

8/20/2019 1:55 PM Andy
- Added new module "Notice Board Setup".

8/21/2019 1:47 PM Justin
- Added new menu "Marketplace".
- Added new module "Marketplace - Manage SKU" and "Marketplace - Settings".

9/25/2019 5:01 PM Andy
- Added new module "Membership Package".

11/14/2019 4:32 PM Justin
- Enhanced to group all the sales agent reports into a reports tab.
- Enhanced to have new module "Sales Agent - KPI Table".
- Enhanced to have new module "Sales Agent - Position Table".
- Enhanced to have new module on Sales Agent portal "Rate KPI".

11/21/2019 2:36 PM Andy
- Added menu "Go to Marketplace".

12/16/2019 2:52 PM Justin
- Added new module "Counter Setup Information".

12/26/2019 9:02 AM William
- Added new modue "Sku Transaction Type Filter".

12/27/2019 11:08 AM Andy
- Rename "Monthly Shift Assignments" to "Shift Assignments".
- Added new Time Attendance module "Holiday" and "Leave".

2/5/2020 10:31 AM William
- Added new module "Time Attendance Dashboard".
- Added new module "Time Attendance Settings".

2/19/2020 2:46 PM Andy
- Added new module "SKU Tag".

3/2/2020 11:44 AM William
- Enhanced enable branch HQ can access "Go to Marketplace".

3/17/2020 3:54 PM Andy
- Remove menu "Go to Marketplace".

3/5/2020 3:17 PM William
- Added new module "Custom Report".

4/6/2020 1:26 PM William
- Added new module "Import / Update Debtor Price by CSV".

19/03/2020 Sheila
- fixed navigation css

3/19/2020 11:34 AM Justin
- Added new modules "Membership Credit Promotion" and "Membership Credit Settings".

04/08/2020 4:07 PM Sheila
- Modified layout to compatible with new UI.

4/29/2020 1:07 PM William
- Added new module "Monthly Closing".

5/27/2020 9:02 AM William
- Added new module "Monthly Closing Stock Balance Report by Department" and "Monthly Closing Stock Balance Summary".

7/8/2020 4:21 PM William
- Change custom report to allow view on sub branch.

8/6/2020 12:02 PM William
- Bug fixed custom report menu show when no report.

8/21/2020 4:04 PM William
- Enhanced to added new module "Batch No Transaction Details Report".

10/9/2020 2:58 PM William
- Added new module "Tax Settings" and "Tax Listing".

11/9/2020 6:32 PM Shane
- Added new module "POS Announcement".

11/12/2020 2:32 PM William
- Added new module "Popular Items Listing Setup".
- Added new module "POS Device Management".

12/10/2020 3:42 PM William
- Added new module "DO Summary By Items".

12/31/2020 1:07 PM Andy
- Added new module "Speed99 Integration Status".

1/8/2021 12:46 PM Rayleen
- Add new module "Update SKU Info by CSV".

1/21/2021 9:00 AM William
- Added new module "SKU Sales & Purchase Profit Margin Special Calculation Report".

02/18/2021 10:49:46 AM Rayleen
- Add new sub menu "Price Checker".

02/19/2021 2:32 PM Rayleen
- Add new sub modules "User Application E-form"

03/02/2021 1:32 PM Rayleen
- Add checking "USERS_EFORM" for User Application E-form Module

04/02/2021 2:00 PM Sn Rou
- Add POS Monitoring Menu in Front End.

4/29/2021 1:47 PM Andy
- Added new module "Komaiso Integration Status".
*}


{if $sessioninfo || $sa_session || $vp_session || $dp_session}
	{literal}
	<script type="text/javascript">

	//SuckerTree Vertical Menu (Aug 4th, 06)
	//By Dynamic Drive: http://www.dynamicdrive.com/style/

	var menuids=["suckertree"] //Enter id(s) of SuckerTree UL menus, separated by commas

	function buildsubmenus(){
		for (var i=0; i<menuids.length; i++){
			var ultags=document.getElementById(menuids[i]).getElementsByTagName("ul")
			for (var t=0; t<ultags.length; t++){
				ultags[t].style.zIndex = 1000;
				ultags[t].parentNode.onmouseover=function(){
				this.getElementsByTagName("ul")[0].style.display="block"
				}
				ultags[t].parentNode.onmouseout=function(){
				this.getElementsByTagName("ul")[0].style.display="none"
				}
			}
		}
	}

	if (window.addEventListener)
	window.addEventListener("DOMContentLoaded", buildsubmenus, false)
	else if (window.attachEvent)
	window.attachEvent("onload", buildsubmenus)
	</script>
	{/literal}
{/if}


<div id=goto_branch_popup class="curtain_popup" style="width:300px;height:100px;display:none;">
	<div style="text-align:right"><img src=/ui/closewin.png onclick="default_curtain_clicked()"></div>
	<h3>Select Branch to login</h3>
	<span id=goto_branch_list></span> <button onclick="goto_branch_select()">Login</button>
</div>

{if $sessioninfo}

<div class="stdframe nav-font" style="height:24px;margin:0;padding:0;">
<div class="suckerdiv main-nav" style="min-width:780px;width:100%;">
<ul id=suckertree>
{*style="padding: 7px 13px 9px 15px !important;"*}
	<li><a href="#" ><i class="icofont-home icofont"></i>Home</a>
	<ul>
		<li><a href="home.php">Dashboard</a></li>
		<li><a href="javascript:void(goto_branch(1))">Go to branch...</a></li>
	</ul>

	{if $sessioninfo.privilege.USERS_ADD or $sessioninfo.privilege.USERS_MNG or $sessioninfo.privilege.USERS_ACTIVATE or $sessioninfo.privilege.MST_APPROVAL or $sessioninfo.privilege.POS_IMPORT or $sessioninfo.privilege.SKU_EXPORT or $sessioninfo.level>=9999}
	<li><a href="#" class="submenu"><i class="icofont-user icofont"></i>Administrator</a>
	<ul style="width:180px">
		{if $sessioninfo.privilege.USERS_MNG or $sessioninfo.privilege.USERS_ACTIVATE}
		<li><a href="#" class=submenu>Users</a>
			<ul>
			{if $sessioninfo.privilege.USERS_ADD}<li><a href="users.php?t=create">Create Profile</a>{/if}
			{if $sessioninfo.privilege.USERS_ACTIVATE}<li><a href="users.php?t=update">Update Profile</a>{/if}
			{if $sessioninfo.level==500 || $sessioninfo.level>=9999}
				<li><a href="admin.inactive_user.php"> No-Activity User Report</a>
			{/if}
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/users.application.php") and $sessioninfo.privilege.USERS_EFORM }
				<li><a href="#" class="submenu">User Application E-Form </a>
					<ul style="min-width:160px;">
						{if $config.single_server_mode or (!$config.single_server_mode and $sessioninfo.branch_id eq 1)}
							<li><a href="users.application.php?a=generate_code">Generate QR Code</a></li>
						{/if}
						<li><a href="users.application.php?a=application_list">Application List</a></li>
					</ul>
				</li>
			{/if}
			</ul>
		</li>
		{/if}
		{if $sessioninfo.privilege.MST_APPROVAL}<li><a href="approval_flow.php">Approval Flows</a>{/if}
		
		
		{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ'}
			<li><a href="#" class="submenu">Selling Price</a>
				<ul>
					<li><a href="admin.copy_selling.php">Copy Selling Price</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_selling.php")}
						<li><a href="admin.import_selling.php">Import Selling Price</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.update_price_type.php")}
						<li><a href="admin.update_price_type.php">Update Price Type</a></li>
					{/if}
				</ul>
			</li>
			
		{/if}
		{if $BRANCH_CODE eq 'HQ' and $sessioninfo.level>=9999 and ($sessioninfo.privilege.ADMIN_UPDATE_SKU_MASTER_COST and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.update_sku_master_cost.php"))}
			<li><a href="#" class="submenu">Cost Price</a>
				<ul>
					<li><a href="admin.update_sku_master_cost.php">Update SKU Master Cost</a></li>
				</ul>
			</li>
		{/if}
		
		{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ'}
			<li><a href="admin.sku_block.php">Block / Unblock SKU in PO (CSV)</a></li>
		{/if}
			
		{if $sessioninfo.privilege.SKU_EXPORT ||  $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT || $sessioninfo.privilege.ALLOW_IMPORT_SKU || $sessioninfo.privilege.ALLOW_IMPORT_VENDOR  || $sessioninfo.privilege.ALLOW_IMPORT_BRAND || $sessioninfo.privilege.ALLOW_IMPORT_DEBTOR || $sessioninfo.privilege.ALLOW_IMPORT_UOM || $sessioninfo.privilege.ALLOW_IMPORT_DEACTIVATE_SKU}
		<li><a href="#" class=submenu>{*<img src="/ui/icons/database_table.png" align=absmiddle border=0>*} Import / Export</a>
			<ul>
			{if $sessioninfo.privilege.SKU_EXPORT}
				<li><a href="admin.sku_export.php">Export SKU Items</a>
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.weightcode_export.php") and !$config.consignment_modules}
					<li><a href="admin.weightcode_export.php">Export Weighing Scale Items</a></li>
				{/if}
			{/if}
			{if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.export_points.php")}<li><a href="admin.export_points.php">Export Member Points</a></li>{/if}
			{/if}
			{if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
				{*<li><a href="admin.pos_transaction_import.php">Import POS Transaction</a></li>*}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_pos_sales.php")}
				    {*<li><a href="admin.import_pos_sales.php">Import POS Sales</a></li>*}
				{/if}
	  			<li><a href="admin.stockchk_import.php">Import Stock Take</a>
			{/if}
			{if $config.sku_application_require_multics && ($sess.level==500 || $sessioninfo.level>=9999)}
			<li><a href="admin.update_dat.php">Update Multics DAT files</a>
			{/if}
            {if $sessioninfo.level>=9999 || $sessioninfo.privilege.POS_IMPORT}
                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_member_points.php")}
                    <li><a href="admin.import_member_points.php">Import Member Points</a></li>
                {/if}
                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_members.php")}
                    <li><a href="admin.import_members.php">Import Members</a></li>
                {/if}
                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.preactivate_member_cards.php")}
                    <li><a href="admin.preactivate_member_cards.php">Pre-activate Member Cards</a></li>
                {/if}
            {/if}
			
			{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_SKU)}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_sku.php")}
                    <li><a href="admin.import_sku.php">Import SKU</a></li>
                {/if}
			{/if}
			
			{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_VENDOR)}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_vendor.php")}
                    <li><a href="admin.import_vendor.php">Import Vendor</a></li>
                {/if}
			{/if}
			
			{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_BRAND)}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_brand.php")}
                    <li><a href="admin.import_brand.php">Import Brand</a></li>
                {/if}
			{/if}
            
            {if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_DEBTOR) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_debtor.php")}
				<li><a href="admin.import_debtor.php">Import Debtor</a></li>
			{/if}
			{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_UOM) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.import_uom.php")}
                 <li><a href="admin.import_uom.php">Import UOM</a></li>
            {/if}
			{if $BRANCH_CODE eq 'HQ' && ($sessioninfo.level>=9999 || $sessioninfo.privilege.ALLOW_IMPORT_DEACTIVATE_SKU) && file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.deactivate_sku.php")}
                 <li><a href="admin.deactivate_sku.php">Deactivate SKU by CSV</a></li>
            {/if}
			</ul>
		</li>
		{/if}

		{if $sessioninfo.level>=9999 and $BRANCH_CODE eq 'HQ' and $config.show_tracker}
		    <li><a href="admin.arms_tracker.php">{*<img src="/ui/icons/lock.png" align=absmiddle border=0>*} ARMS Request Tracker</a>
		{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.monthly_closing.php") and $config.monthly_closing and $sessioninfo.privilege.ADMIN_MONTHLY_CLOSING}
			<li><a href="#" class="submenu">Monthly Closing</a>
				<ul>
					<li><a href="admin.monthly_closing.php">Monthly Closing</a></li>
					<li><a href="admin.monthly_closing.php?a=show_closed_month">Monthly Closing History</a></li>
				</ul>
			</li>
		{/if}
		{if $sessioninfo.level>=9999}
			<li><a href="admin.update_log.php">{*<img src="/ui/icons/script_save.png" align=absmiddle border=0>*} System Update log</a>
			<li><a href="sales_target.php">Sales Target</a>
			<li><a href="#" class=submenu>Settings</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.settings.php")}
					<li><a href="admin.settings.php?file=color.txt">Edit Colour</a>
					<li><a href="admin.settings.php?file=size.txt">Edit Size</a>
					{/if}
					{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.upload_config.php")}
					<li><a href="admin.upload_config.php">Upload Config CSV</a>
					{/if *}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.upload_logo.php")}
					<li><a href="admin.upload_logo.php">Edit Logo Settings</a>
					{/if}
				</ul>
			</li>
			{*
			{if $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.SERVER_MAINTENANCE}
			    <li><a href="#" class=submenu>Server Maintenance</a>
			        <ul>
			            {if file_exists("admin.server_maintenance.archive_database.php")}
			            <li><a href="admin.server_maintenance.archive_database.php">Archive Database</a></li>
			            <li><a href="admin.server_maintenance.archive_database.php?a=restore">Restore Database</a></li>
			            {/if}
			        </ul>
			    </li>
			{/if}
			*}
			{if $BRANCH_CODE eq 'HQ' and $sessioninfo.id eq 1}
			    <li><a href="#" class="submenu">Server Management</a>
			        <ul>
			            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.config_manager.php")}
			                <li><a href="admin.config_manager.php">Config Manager</a></li>
			            {/if}
			            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.privilege_manager.php")}
			                <li><a href="admin.privilege_manager.php">Privilege Manager</a></li>
			            {/if}
			            {*{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.reset_db.php")}
			                <li><a href="admin.reset_db.php">Reset Data</a></li>
			            {/if}*}
			        </ul>
				</li>
			{/if}
			{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst_settings.php") && $sessioninfo.level>=9999}
				<li><a href="masterfile_gst_settings.php">GST Settings</a></li>
			{/if}
		{/if}
		{if $config.enable_tax and $sessioninfo.level>=9999}
			<li><a href="#" class="submenu">Tax</a>
				<ul>
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.tax_settings.php") }
					<li><a href="admin.tax_settings.php">Tax Settings</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.tax_listing.php") }
					<li><a href="admin.tax_listing.php">Tax Listing</a></li>
				{/if}
				</ul>
			</li>
		{/if}
		{* Foreign Currency *}
		{if $config.foreign_currency and ($sessioninfo.privilege.ADMIN_FOREIGN_CURRENCY_RATE_UPDATE)}
			<li><a href="#" class="submenu">Foreign Currency</a>
				<ul>
					{if $sessioninfo.privilege.ADMIN_FOREIGN_CURRENCY_RATE_UPDATE and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.foreign_currency.rate.php")}
						<li><a href="admin.foreign_currency.rate.php">Currency Rate Table</a></li>
					{/if}
				</ul>
			</li>
		{/if}
			
		{*
		{if $BRANCH_CODE eq 'HQ' && $config.stock_copy && $sessioninfo.privilege.STOCK_COPY}
		    {if file_exists("admin.stock_copy.php")}
		    	<li><a href="admin.stock_copy.php">Stock Copy</a></li>
		    {/if}
		{/if}
		*}
	</ul>
	{/if}

    {if !$config.arms_go_modules || ($config.arms_go_modules && ($config.arms_go_enable_official_modules || (!$config.arms_go_enable_official_modules && $BRANCH_CODE ne 'HQ')))}
        {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL or $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_REQUEST or $sessioninfo.privilege.PO_FROM_REQUEST or $sessioninfo.privilege.PO_REPORT or $sessioninfo.privilege.GRN_APPROVAL or $sessioninfo.privilege.GRA or $sessioninfo.privilege.GRR_REPORT or $sessioninfo.privilege.GRN_REPORT or $sessioninfo.privilege.SHIFT_RECORD_VIEW or $sessioninfo.privilege.SHIFT_RECORD_EDIT or $sessioninfo.privilege.PAYMENT_VOUCHER or $sessioninfo.privilege.DO or $sessioninfo.privilege.ADJ or $sessioninfo.privilege.ACCOUNT_EXPORT or $sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS or $sessioninfo.privilege.SPEED99_INTEGRATION_STATUS or $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS}
        <li><a href="#" class=submenu><i class="icofont-ui-office icofont"></i>Office</a>
        <ul style="min-width:160px">
    
            {if $sessioninfo.privilege.ADJ}
            <li><a href="#" class=submenu> Adjustment</a>
            <ul>
                <li><a href="/adjustment.php"> Adjustment</a>
                {if $sessioninfo.privilege.ADJ_APPROVAL}
                    <li><a href="/adjustment_approval.php">Adjustment Approval</a></li>
                {/if}
                <li><a href="/adjustment.summary.php"> Adjustment Summary</a>
            </ul>
            {/if}	
    
            {if $sessioninfo.privilege.SHIFT_RECORD_VIEW or $sessioninfo.privilege.SHIFT_RECORD_EDIT && file_exists("`$smarty.server.DOCUMENT_ROOT`/shift_record.php")}
            <li><a href="/shift_record.php">Shift Record</a>
            {/if}
            
            {if $sessioninfo.privilege.PAYMENT_VOUCHER}
            {if BRANCH_CODE ne 'HQ'}
            <li><a href="/payment_voucher.php">Payment Voucher</a>
            {else}
            <li><a href="#" class=submenu>Payment Voucher</a>
            <ul>
                <li><a href="/payment_voucher.php">Payment Voucher</a>
                <li><a href="/payment_voucher.log_sheet.php">Cheque Issue Log Sheet</a>
            </ul>
            {/if}	    
            {/if}
            
            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL or $sessioninfo.privilege.SKU_REPORT}
            <li><a href="#" class=submenu>SKU</a>
            <ul>
            {if $sessioninfo.privilege.MST_SKU_APPLY && $sessioninfo.branch_type ne "franchise"}
                <li><a href="masterfile_sku_application.php">SKU Application</a></li>
                <li><a href="masterfile_sku_application.php?a=revise_list">SKU Application Revise List</a></li>
                
                {if !$config.menu_hide_bom_application}<li><a href="masterfile_sku_application_bom.php">Create BOM SKU</a></li>{/if}
            {/if}
            
            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL}<li><a href="masterfile_sku_application.php?a=list">SKU Application Status</a>{/if}
            {if $sessioninfo.privilege.SKU_REPORT}
            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
            {/if}
            {if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.future_price.php")}
                <li><a href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
            {/if}
            </ul>
            {/if}
			
			{* Old *}
			{* if $config.allow_sales_order}
                <li><a href="#" class=submenu>Sales Order</a>
                    <ul>
                        <li><a href="sales_order.php">Create / Edit Order</a></li>
                        {if $sessioninfo.privilege.SO_APPROVAL}
                            <li><a href="sales_order_approval.php">Sales Order Approval</a></li>
                        {/if}
                        <li><a href="report.spbt.php">Sales Order Report</a></li>
                        <li><a href="report.spbt_summary.php">Sales Order Summary Report</a></li>
                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/sales_order.monitor_report.php")}
                            <li><a href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
                        {/if}
                    </ul>
                </li>
            {/if *}

			{* New *}
            {if $config.allow_sales_order && ($sessioninfo.privilege.SO_EDIT || $sessioninfo.privilege.SO_APPROVAL || $sessioninfo.privilege.SO_REPORT)}
                <li><a href="#" class=submenu>Sales Order</a>
                    <ul>
                        {if $sessioninfo.privilege.SO_EDIT}
							<li><a href="sales_order.php">Create / Edit Order</a></li>
                        {/if}
                        {if $sessioninfo.privilege.SO_APPROVAL}
                            <li><a href="sales_order_approval.php">Sales Order Approval</a></li>
                        {/if}
                        {if $sessioninfo.privilege.SO_REPORT}
							<li><a href="report.spbt.php">Sales Order Report</a></li>
							<li><a href="report.spbt_summary.php">Sales Order Summary Report</a></li>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sales_order.monitor_report.php")}
								<li><a href="sales_order.monitor_report.php">Sales Order Monitor Report</a></li>
							{/if}
						{/if}
                    </ul>
                </li>
            {/if}
            <!-- DO -->
            {if $sessioninfo.privilege.DO}
            <li><a href="#" class=submenu>DO (Delivery Order)</a>
            <ul>
                {*<li><a href="do.php">Delivery Order</a>
                <li><a href="do.summary.php?p=do">DO Summary</a>
                <li><a href="do.summary.php?p=invoice">Invoice Summary</a>*}
                {if $sessioninfo.branch_type ne "franchise"}
                    <li><a href="do.php">Transfer DO</a></li>
                {/if}
                {if $config.do_allow_cash_sales}
                    <li><a href="do.php?page=open">Cash Sales DO</a></li>
                {/if}
                {if $config.do_allow_credit_sales}
                    <li><a href="do.php?page=credit_sales">Credit Sales DO</a></li>
                {/if}
				{if $sessioninfo.privilege.DO_PREPARATION}
					<li><a href="#" class="submenu">DO Preparation</a>
					<ul>
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/do.simple.php")}
							<li><a href="do.simple.php?do_type=transfer">Transfer DO</a></li>
							<li><a href="do.simple.php?do_type=open">Cash Sales DO</a></li>
							<li><a href="do.simple.php?do_type=credit_sales">Credit Sales DO</a></li>
						{/if}
					</ul>
				{/if}
                {if $sessioninfo.privilege.DO_APPROVAL}
                    <li><a href="do_approval.php">DO Approval</a></li>
                {/if}
                <li><a href="do.summary.php">DO Summary</a></li>
                <li><a href="report.do_summary.php">DO Summary By Day / Month</a></li>
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.do_summary_by_items.php")}
				<li><a href="report.do_summary_by_items.php">DO Summary By Items</a></li>
				{/if}
                <li><a href="do.report.php">Transfer Report</a></li>
                {if $sessioninfo.privilege.DO_REQUEST}
                    <li><a href="do_request.php">DO Request</a></li></li>
                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/do_request.rejected_report.php")}
                        <li><a href="do_request.rejected_report.php">DO Request Rejected Report</a></li></li>
                    {/if}
                {/if}
                {if $sessioninfo.privilege.DO_REQUEST_PROCESS}
                    <li><a href="do_request.process.php">Process DO Request</a></li>
                {/if}
                {*
                {if $config.enable_sn_bn && file_exists('masterfile_sku_items.serial_no.import_do_items.php')}
                <li><a href="masterfile_sku_items.serial_no.import_do_items.php">Serial No - IBT Validation</a></li>
                {/if}
                *}
				
				{if $config.enable_one_color_matrix_ibt and BRANCH_CODE eq 'HQ' and file_exists('do.matrix_ibt_process.php')}
					<li><a href="do.matrix_ibt_process.php">Matrix IBT Process</a></li>
				{/if}
            </ul>
            {/if}
			
    
            <!-- PO -->
            {if $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_REQUEST or $sessioninfo.privilege.PO_FROM_REQUEST or $sessioninfo.privilege.PO_REPORT or $sessioninfo.privilege.PO_REQUEST_APPROVAL}
            <li><a href="#" class=submenu>PO (Purchase Order)</a>
                <ul>
                    {if $sessioninfo.privilege.PO or $sessioninfo.privilege.PO_VIEW_ONLY}
                        <li><!--a href="purchase_order.php">Purchase Order</a-->
                        <a href="po.php">Purchase Order</a></li>
                    {/if}
                    {if $sessioninfo.privilege.PO_APPROVAL}
                        <li><a href="po_approval.php">PO Approval</a></li>
                    {/if}
                    {if $sessioninfo.privilege.PO_FROM_REQUEST}
                        <li><a href="po_request.process.php">Create PO from Request</a></li>
                    {/if}
                    {if $sessioninfo.privilege.PO_REQUEST}
                        <li><a href="po_request.request.php">PO Request</a></li>
                    {/if}
                    {if $sessioninfo.privilege.PO_REQUEST_APPROVAL}
                        <li><a href="po_request.approval.php">PO Request Approval</a></li>
                    {/if}
                    {if $sessioninfo.privilege.PO_TICKET && $config.po_allow_vendor_request}
                        <li><a href="vendor_po_request.php">Vendor PO Access</a></li>
                    {/if}
                    {if $sessioninfo.privilege.PO_REPORT}<li><a href="purchase_order.summary.php">PO Summary</a>{/if}
                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/po_qty_performance.php") and $sessioninfo.privilege.PO_REPORT}
                        <li><a href="po_qty_performance.php">PO Quantity Performance</a></li>
                    {/if}
                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_reorder.php") and $sessioninfo.privilege.PO and $sessioninfo.privilege.PO_REPORT}
                        <li><a href="report.stock_reorder.php">Stock Reorder Report</a></li>
                    {/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sku_purchase_history.php") and $sessioninfo.privilege.PO and $sessioninfo.privilege.PO_REPORT}
                        <li><a href="sku_purchase_history.php">SKU Purchase History</a></li>
                    {/if}
                </ul>
            </li>
            {/if}
            
            {if $config.enable_po_agreement and $sessioninfo.privilege.PO_SETUP_AGREEMENT and $BRANCH_CODE eq 'HQ' and file_exists("`$smarty.server.DOCUMENT_ROOT`/po.po_agreement.setup.php")}
                <li><a href="#" class="submenu">Purchase Agreement</a>
                    <ul>
                        <li><a href="po.po_agreement.setup.php">Add/Edit Purchase Agreement</a></li>
                    </ul>
                </li>
            {/if}
    
            {if $sessioninfo.privilege.GRN_APPROVAL && !$config.use_grn_future}<li><a href="goods_receiving_note_approval.account.php">GRN Account Verification</a>{/if}
            {if $sessioninfo.privilege.GRA}
                <li><a href="#" class="submenu">GRA (Goods Return Advice)</a>
                    <ul>
                        <li><a href="goods_return_advice.php">GRA</a></li>
                        {if $sessioninfo.privilege.GRA_APPROVAL}
                            <li><a href="/goods_return_advice.approval.php">GRA Approval</a></li>
                        {/if}
                    </ul>
            {/if}
    
            {if $sessioninfo.privilege.GRA_REPORT or $sessioninfo.privilege.GRR_REPORT or $sessioninfo.privilege.GRN_REPORT}
            <li><a href="#" class=submenu>GRR / GRN / GRA Reports</a>
            <ul>
            {if $sessioninfo.privilege.GRR_REPORT}
            <li><a href="goods_receiving_record.report.php">GRR Report</a>
            <li><a href="goods_receiving_record.status.php">GRR Status Report</a>
            {/if}
            {if $sessioninfo.privilege.GRN_REPORT}
                <li><a href="goods_receiving_note.summary.php">GRN Summary</a></li>
                <li><a href="goods_receiving_note.category_summary.php">GRN Summary by Category</a></li>
                {if file_exists("`$smarty.server.DOCUMENT_ROOT`/goods_receiving_note.distribution_report.php")}
                    <li><a href="goods_receiving_note.distribution_report.php">GRN Distribution Report</a></li>
                {/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_receiving_history.php")}
                    <li><a href="report.sku_receiving_history.php">SKU Receiving History</a></li>
				{/if}
            {/if}
            {if $sessioninfo.privilege.GRA_REPORT}
                <li><a href="goods_return_advice.listing_report.php">GRA Listing</a>
                <li><a href="goods_return_advice.summary_by_dept.php">GRA Summary by Department</a>
                <li><a href="goods_return_advice.summary_by_category.php">GRA Summary by Category</a>
            {/if}
            {if $config.gra_enable_disposal && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.goods_return_advice.disposal.php")}
                <li><a href="report.goods_return_advice.disposal.php">GRA Disposal Report</a>
            {/if}
            </ul>
            {/if}
            
			{if $sessioninfo.privilege.DN and !$config.consignment_modules and file_exists("`$smarty.server.DOCUMENT_ROOT`/dnote.php")}
              <li><a href="dnote.php">Debit Note</a>
            {/if}
			
			{if $sessioninfo.privilege.CN and !$config.consignment_modules and file_exists("`$smarty.server.DOCUMENT_ROOT`/cnote.php")}
              <li><a href="#" class="submenu">Credit Note</a>
                <ul>
                    <li><a href="cnote.php">Credit Note</a></li>
                    {if $sessioninfo.privilege.CN_APPROVAL}
                        <li><a href="/cnote.approval.php">Credit Note Approval</a></li>
                    {/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/cnote.summary.php")}
						<li><a href="cnote.summary.php">CN Summary</a></li>
					{/if}
                </ul>
            {/if}

            {* Vendor Portal Related *}
            {if $config.enable_vendor_portal and ($sessioninfo.privilege.REPORTS_REPACKING and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.repacking.php"))}
                <li><a href="#" class="submenu">Vendor Portal</a>
                    <ul>
                        {if $sessioninfo.privilege.REPORTS_REPACKING and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.repacking.php")}
                            <li><a href="report.repacking.php">Repacking Report</a></li>
                        {/if}
                    </ul>
                </li>
            {/if}

            {if $sessioninfo.privilege.ACCOUNT_EXPORT}
              <li><a href="acc_export.php">Account & GAF Export</a>
              <li><a href="acc_export.php?a=setting">Account & GAF Export Setting</a>
            {/if}
			
			{* Accounting Export*}
			{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING or $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP or $sessioninfo.privilege.CUSTOM_ACC_EXPORT}
				<li><a href="#" class="submenu">Custom Accounting Export</a>
					<ul>
						{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_and_gst_setting.php")}
							<li><a href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
						{/if}
						
						{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.setup_acc_export.php")}
							<li><a href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
						{/if}
						
						{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_export.php")}
							<li><a href="custom.acc_export.php">Custom Accounting Export</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			{* ARMS Accounting Integration *}
			{if $config.arms_accounting_api_setting and ($sessioninfo.privilege.ARMS_ACCOUNTING_SETTING or $sessioninfo.privilege.ARMS_ACCOUNTING_STATUS)}
				<li><a href="#" class="submenu">ARMS Accounting Integration &nbsp;</a>
					<ul>
						{if $sessioninfo.privilege.ARMS_ACCOUNTING_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/arms_accounting.setting.php")}
							<li><a href="arms_accounting.setting.php">Setting</a></li>
						{/if}
						{if $sessioninfo.privilege.ARMS_ACCOUNTING_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/arms_accounting.status.php")}
							<li><a href="arms_accounting.status.php">Integration Status</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			{* OS Trio Accounting Integration *}
			{if $config.os_trio_settings and ($sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS)}
				<li><a href="#" class="submenu">OS Trio Accounting Integration &nbsp;</a>
					<ul>
						{if $sessioninfo.privilege.OSTRIO_ACCOUNTING_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/ostrio_accounting.status.php")}
							<li><a href="ostrio_accounting.status.php">Integration Status</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			{* Speed99 Integration *}
			{if $config.speed99_settings and ($sessioninfo.privilege.SPEED99_INTEGRATION_STATUS)}
				<li><a href="#" class="submenu">Speed99 Integration &nbsp;</a>
					<ul>
						{if $sessioninfo.privilege.SPEED99_INTEGRATION_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/speed99.integration_status.php")}
							<li><a href="speed99.integration_status.php">Integration Status</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			{* Komaiso Integration *}
			{if $config.komaiso_settings  and $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS}
				<li><a href="#" class="submenu">Komaiso Integration &nbsp;</a>
					<ul>
						{if $sessioninfo.privilege.KOMAISO_INTEGRATION_STATUS and file_exists("`$smarty.server.DOCUMENT_ROOT`/komaiso.integration_status.php")}
							<li><a href="komaiso.integration_status.php">Integration Status</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php") and ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP or $sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN)}
				<li><a href="#" class="submenu">Time Attendance</a>
					<ul style="min-width: 160px;">

						{if $sessioninfo.privilege.ATTENDANCE_TIME_OVERVIEW and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.overview.php")}
							<li><a href="attendance.overview.php">Time Attendance Overview</a></li>
						{/if}
						{if $sessioninfo.privilege.ATTENDANCE_TIME_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.settings.php")}
							<li><a href="attendance.settings.php">Settings</a></li>
						{/if}
						
						{if ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_assignment.php"))}
							<li><a href="#" class="submenu">Shift</a>
								<ul>
									{if $sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")}
										<li><a href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
									{/if}
									{if $sessioninfo.privilege.ATTENDANCE_SHIFT_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_assignment.php")}
										<li><a href="attendance.shift_assignment.php">Shift Assignments</a></li>
									{/if}
								</ul>
							</li>
						{/if}
						
						{if ($sessioninfo.privilege.ATTENDANCE_PH_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_PH_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_assignment.php"))}
							<li><a href="#" class="submenu">Holiday</a>
								<ul>
									{if $sessioninfo.privilege.ATTENDANCE_PH_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_setup.php")}
										<li><a href="attendance.ph_setup.php">Holiday Setup</a></li>
									{/if}
									{if $sessioninfo.privilege.ATTENDANCE_PH_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.ph_assignment.php")}
										<li><a href="attendance.ph_assignment.php">Holiday Assignments</a></li>
									{/if}
								</ul>
							</li>
						{/if}
						
						{if ($sessioninfo.privilege.ATTENDANCE_LEAVE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_setup.php")) or ($sessioninfo.privilege.ATTENDANCE_LEAVE_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_assignment.php"))}
							<li><a href="#" class="submenu">Leave</a>
								<ul>
									{if $sessioninfo.privilege.ATTENDANCE_LEAVE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_setup.php")}
										<li><a href="attendance.leave_setup.php">Leave Table Setup</a></li>
									{/if}
									{if $sessioninfo.privilege.ATTENDANCE_LEAVE_ASSIGN and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.leave_assignment.php")}
										<li><a href="attendance.leave_assignment.php">Leave Assignments</a></li>
									{/if}
								</ul>
							</li>
						{/if}
						
						
						{if $sessioninfo.privilege.ATTENDANCE_USER_MODIFY and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.user_records.php")}
							<li><a href="attendance.user_records.php">User Attendance Records</a></li>
						{/if}
						
						{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.daily.php")}
							<li><a href="#" class="submenu">Reports</a>
								<ul>
									{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.daily.php")}
										<li><a href="attendance.report.daily.php">Daily Attendance Report</a></li>
									{/if}
									{if $sessioninfo.privilege.ATTENDANCE_CLOCK_REPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.report.monthly_ledger.php")}
										<li><a href="attendance.report.monthly_ledger.php">Monthly Attendance Ledger</a></li>
									{/if}
								</ul>
							</li>
						{/if}						
					</ul>
				</li>
			{/if}
        </ul>
        {/if}

        {if $sessioninfo.privilege.GRR or $sessioninfo.privilege.GRN or $sessioninfo.privilege.GRA_CHECKOUT or $sessioninfo.privilege.DO_CHECKOUT or $sessioninfo.privilege.STOCK_TAKE}
        <li><a href="#" class=submenu><i class="icofont-bank-alt icofont"></i>Store</a>
        <ul style="min-width: 160px;">
            {if $sessioninfo.privilege.GRR}<li><a href="goods_receiving_record.php">GRR (Goods Receiving Record)</a>{/if}
            {if $sessioninfo.privilege.GRN}
                <li><a href="#" class="submenu">GRN (Goods Receiving Note)</a>
                    <ul>
                        <li><a href="/goods_receiving_note.php">GRN</a></li>
                        {if $sessioninfo.privilege.GRN_APPROVAL}
                        <li><a href="/goods_receiving_note_approval.php">GRN Approval</a></li>
                        {/if}
                    </ul>
            {/if}
            {if $sessioninfo.privilege.GRA_CHECKOUT}<li><a href="goods_return_advice.checkout.php">GRA Checkout</a>{/if}
            {if $sessioninfo.privilege.DO_CHECKOUT}<li><a href="do_checkout.php">Delivery Order Checkout</a>{/if}
            {if $sessioninfo.privilege.STOCK_TAKE}
                <li><a href="#" class="submenu">Stock Take</a>
                    <ul>
                        <li><a href="admin.stock_take.php">Stock Take</a></li>
                        <li><a href="admin.stock_take.php?a=import_page">Import / Reset Stock Take</a></li>
                        <li><a href="admin.stock_take.php?a=change_batch">Change Batch</a></li>
                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.stock_take_zerolize_negative_stocks.php") && $config.consignment_modules}
                            <li><a href="admin.stock_take_zerolize_negative_stocks.php">Zerolize Negative Stocks</a></li>
                        {/if}
                    </ul>
                </li>
            {/if}
			{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_APPROVAL) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.assignment.php")}
				<li><a href="#" class="submenu">Cycle Count</a>
					<ul>
						{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT or $sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_ASSGN_EDIT) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.assignment.php")}
							<li><a href="admin.cycle_count.assignment.php">Cycle Count Assignment</a></li>
						{/if}
						{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_APPROVAL) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.approval.php")}
							<li><a href="admin.cycle_count.approval.php">Cycle Count Approval</a></li>
						{/if}
						{if ($sessioninfo.privilege.STOCK_TAKE_CYCLE_COUNT_SCHEDULE_LIST) and file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.cycle_count.schedule_list.php")}
							<li><a href="admin.cycle_count.schedule_list.php">Monthly Schedule List</a></li>
						{/if}
					</ul>
				</li>
			{/if}
        </ul>
        {/if}
    {else}
        <li><a href="#" class=submenu>Office</a>
        <ul style="width:160px">
            {if $sessioninfo.privilege.MST_SKU_APPLY && $sessioninfo.branch_type ne "franchise"}
                <li><a href="masterfile_sku_application.php">SKU Application</a></li>
                   {if !$config.menu_hide_bom_application}<li><a href="masterfile_sku_application_bom.php">Create BOM SKU</a></li>{/if}
            {/if}
            
            {if $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU_APPROVAL}<li><a href="masterfile_sku_application.php?a=list">SKU Application Status</a>{/if}
            {if $sessioninfo.privilege.SKU_REPORT}
            <!--li><a href="sku.summary.php">SKU Summary (Testing)</a></li-->
            <!--li><a href="sku.history.php">SKU History (Testing)</a></li-->
            {/if}
            {if $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.future_price.php")}
                <li><a href="masterfile_sku_items.future_price.php">Batch Selling Price Change</a></li>
            {/if}

            {if $sessioninfo.privilege.ACCOUNT_EXPORT}
              <li><a href="acc_export.php">Account Export</a>
              <li><a href="acc_export.php?a=setting">Account Export Setting</a>
            {/if}
			
			{* Accounting Export*}
			{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING or $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP or $sessioninfo.privilege.CUSTOM_ACC_EXPORT}
				<li><a href="#" class="submenu">Custom Accounting Export</a>
					<ul>
						{if $sessioninfo.privilege.CUSTOM_ACC_AND_GST_SETTING and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_and_gst_setting.php")}
							<li><a href="custom.acc_and_gst_setting.php">Custom Account & GST Setting</a></li>
						{/if}
						
						{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.setup_acc_export.php")}
							<li><a href="custom.setup_acc_export.php">Setup Custom Accounting Export</a></li>
						{/if}
						
						{if $sessioninfo.privilege.CUSTOM_ACC_EXPORT and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom.acc_export.php")}
							<li><a href="custom.acc_export.php">Custom Accounting Export</a></li>
						{/if}
					</ul>
				</li>
			{/if}
			
			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php") and ($sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP)}
				<li><a href="#" class="submenu">Time Attendance</a>
					<ul>
						{if $sessioninfo.privilege.ATTENDANCE_SHIFT_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/attendance.shift_table_setup.php")}
							<li><a href="attendance.shift_table_setup.php">Shift Table Setup</a></li>
						{/if}			
					</ul>
				</li>
			{/if}
        </ul>
    {/if}

	{if $sessioninfo.privilege.MASTERFILE}
	<li><a href="#" class=submenu><i class="icofont-book-alt icofont"></i>Master Files</a>
	<ul style="width:160px">
		<li><a href="#" class=submenu>Category</a>
	 		<ul>
			 	<li><a href="masterfile_category.php">Category Listing</a></li>
				{*<li><a href="masterfile_category_markup.php">Category Markup %</a></li>*}
	 		</ul>
		{if $sessioninfo.privilege.MST_SKU_UPDATE or $sessioninfo.privilege.MST_SKU_APPLY or $sessioninfo.privilege.MST_SKU}
		<li><a href="#" class=submenu>SKU</a>
			<ul>
				<li><a href="masterfile_sku.php">SKU Listing</a></li>
				{if $sessioninfo.privilege.MST_SKU_UPDATE_PRICE}<li><a href="masterfile_sku_items_price.php">Change Selling Price</a></li>{/if}
				{if !$config.menu_hide_bom_application}<li><a href="bom.php">BOM Editor</a></li>{/if}
				
				<li><a href="masterfile_sku_group.php">SKU Group</a></li>
				
				
				{if $BRANCH_CODE eq 'HQ' and $config.po_enable_ibt and $config.enable_sku_monitoring2 and $sessioninfo.privilege.MST_SKU_MORN_GRP and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_monitoring_group.php")}
				    <li><a href="masterfile_sku_monitoring_group.php">SKU Monitoring Group</a></li>
				{/if}
				{if $BRANCH_CODE eq 'HQ' and $config.enable_replacement_items and $sessioninfo.privilege.MST_SKU_RELP_ITEM and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_replacement_items.php")}
				    <li><a href="masterfile_replacement_items.php">Replacement Items</a></li>
				{/if}
				{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.serial_no.php")}
					<li><a href="masterfile_sku_items.serial_no.php">Serial No Listing</a></li>
				{/if}
				{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.batch_no_setup.php")}
					<li><a href="masterfile_sku_items.batch_no_setup.php">SKU Batch No Setup</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.price_list.php")}
					<li><a href="masterfile_sku.price_list.php">SKU Price List</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_items.po_reorder_qty_by_branch.php") && $sessioninfo.privilege.MST_PO_REORDER_QTY_BY_BRANCH}
					<li><a href="masterfile_sku_items.po_reorder_qty_by_branch.php">PO Reorder Qty by Branch</a></li>
				{/if}
				{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst.price_wizard.php") && ($sessioninfo.privilege.MST_SKU_UPDATE_PRICE || $sessioninfo.privilege.MST_SKU_UPDATE_FUTURE_PRICE)}
					<li><a href="masterfile_gst.price_wizard.php">GST Price Wizard</a></li>
				{/if}
				
				<li><a href="masterfile_sku_stock_balance_listing.php">SKU Stock Balance Listing (Download)</a></li>

				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku_tag.php") && $sessioninfo.privilege.MST_SKU_TAG}
					<li><a href="masterfile_sku_tag.php">SKU Tag</a></li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_brand_vendor.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
					<li><a href="masterfile_sku.update_brand_vendor.php?method=brand">Update SKU Brand by CSV</a></li>
					<li><a href="masterfile_sku.update_brand_vendor.php?method=vendor">Update SKU Vendor by CSV</a></li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_po_reorder_qty.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
					<li><a href="masterfile_sku.update_po_reorder_qty.php">Update SKU Stock Reorder Min & Max Qty by CSV</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_category.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
					<li><a href="masterfile_sku.update_category.php">Update SKU Category by CSV</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_category_discount.php") && $sessioninfo.privilege.MST_SKU_UPDATE && $sessioninfo.privilege.CATEGORY_DISCOUNT_EDIT}
					<li><a href="masterfile_sku.update_category_discount.php">Update SKU Category Discount by CSV</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sku.update_sku.php") && $sessioninfo.privilege.MST_SKU_UPDATE}
					<li><a href="masterfile_sku.update_sku.php">Update SKU Info by CSV</a></li>
				{/if}
			</ul>
		{/if}
		<li><a href="masterfile_uom.php">UOM</a>
		<li><a href="masterfile_brand.php">Brand</a>
		<li><a href="masterfile_brgroup.php">Brand Group</a>
		
		<li><a href="#" class="submenu">Vendor</a>
			<ul>
				<li><a href="masterfile_vendor.php">Add / Edit</a></li>
				{if $sessioninfo.privilege.MST_VENDOR_QUOTATION_COST and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_vendor.quotation_cost.php")}
					<li><a href="masterfile_vendor.quotation_cost.php">Quotation Cost</a></li>
				{/if}
			</ul>
		</li>
		
		{if $sessioninfo.privilege.MST_BRANCH}
		    <li><a href="#" class=submenu>Branch</a>
				<ul>
					<li><a href="masterfile_branch.php">Add / Edit</a></li>
					<li><a href="masterfile_branch_group.php">Branches Group</a></li>
					{if $config.masterfile_branch_enable_additional_sp && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_branch.additional_selling_price.php")}
						<li><a href="masterfile_branch.additional_selling_price.php">Branches Additional Selling Price</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		
		{if $sessioninfo.privilege.MST_DEBTOR or $sessioninfo.privilege.MST_DEBTOR_PRICE_LIST}
			<li><a href="#" class="submenu">Debtor</a>
				<ul>
					{if $sessioninfo.privilege.MST_DEBTOR and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_debtor.php")}
						<li><a href="masterfile_debtor.php">Add / Edit</a></li>
					{/if}
					
					{if $sessioninfo.privilege.MST_DEBTOR_PRICE_LIST and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_debtor_price.php")}
						<li><a href="masterfile_debtor_price.php">Debtor Price List</a></li>
					{/if}
					{if $sessioninfo.privilege.MST_DEBTOR_CSV_UPDATE_PRICE and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_import_debtor_price.php")}
						<li><a href="masterfile_import_debtor_price.php">Import / Update Debtor Price by CSV</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		{if $sessioninfo.privilege.MST_TRANSPORTER and $config.enable_transporter_masterfile}
		    <li><a href="masterfile_transporter.php">Transporter</a></li>
		{/if}
		{if $sessioninfo.privilege.MST_TRANSPORTER_v2 and $config.enable_reorder_integration}
		    <li><a href="#" class=submenu>Transporter v2</a>
				<ul style="min-width:160px;">
					<li><a href="masterfile_shipper.php?a=transporter">Transporter</a></li>
					<li><a href="masterfile_shipper.php?a=transporter_vehicle">Vehicle</a></li>
					<li><a href="masterfile_shipper.php?a=transporter_driver">Driver</a></li>
					<li><a href="masterfile_shipper.php?a=transporter_route_area">Route Area</a></li>
					<li><a href="#" class=submenu>Maintenance</a>
						<ul>
							<li><a href="masterfile_shipper.php?a=transporter_area">Area</a></li>
							<li><a href="masterfile_shipper.php?a=transporter_route">Route</a></li>							
							<li><a href="masterfile_shipper.php?a=transporter_type">Type</a></li>
							<li><a href="masterfile_shipper.php?a=transporter_vehicle_brand">Vehicle Brand</a></li>
							<li><a href="masterfile_shipper.php?a=transporter_vehicle_status">Vehicle Status</a></li>
							<li><a href="masterfile_shipper.php?a=transporter_vehicle_type">Vehicle Type</a></li>
						</ul>
					</li>
				</ul>
			</li>
		{/if}
		{if $config.use_consignment_bearing and $sessioninfo.privilege.MST_CONTABLE and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_consignment_bearing.php")}
		    <li><a href="masterfile_consignment_bearing.php">Consignment Bearing</a></li>
		{/if}
		{if $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.MST_BANK_INTEREST and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_bank_interest.php") and $config.enable_sku_monitoring2}
		    <li><a href="masterfile_bank_interest.php">Bank Interest</a></li>
		{/if}
		{if $sessioninfo.privilege.MST_COUPON}
		    <li><a href="#" class=submenu>Coupon</a>
				<ul>
				    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_coupon.php")}
						<li><a href="masterfile_coupon.php">
							{if $BRANCH_CODE eq 'HQ'}Create / Print{else}View{/if}
							</a>
						</li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.coupon.transaction.php")}
						<li><a href="report.coupon.transaction.php">Transaction Report</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.coupon.details.php")}
						<li><a href="report.coupon.details.php">Details Report</a></li>
					{/if}
				</ul>
			</li>
		{/if}

		{if $sessioninfo.privilege.MST_VOUCHER}
		    <li><a href="#" class=submenu>Voucher</a>
		        <ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.setup.php") and $sessioninfo.privilege.MST_VOUCHER_SETUP}
						<li><a href="masterfile_voucher.setup.php">Setup</a></li>
					{/if}
					
		            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.php")}
						<li><a href="masterfile_voucher.php">Listing</a></li>
					{/if}
                    {if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.register.php") and $sessioninfo.privilege.MST_VOUCHER_REGISTER and $BRANCH_CODE eq 'HQ'}
						<li><a href="masterfile_voucher.register.php">Registration</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.activate.php") and $sessioninfo.privilege.MST_VOUCHER_ACTIVATE}
						<li><a href="masterfile_voucher.activate.php">Activation</a></li>
					{/if}

					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.transaction.php")}<li><a href="report.voucher.transaction.php">Transaction Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.details.php")}<li><a href="report.voucher.details.php">Details Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.activation.php")}<li><a href="report.voucher.activation.php">Activation & Cancellation Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.collection.php")}<li><a href="report.voucher.collection.php">Account-receivable Report</a></li>{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.voucher.payment.php")}<li><a href="report.voucher.payment.php">Account-payable Report</a></li>{/if}
					
					{if $config.enable_voucher_auto_redemption and ((file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.setup.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_SETUP) or (file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.generate.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_GENERATE))}
						<li><a href="#" class=submenu>Auto Redemption</a>
					        <ul>
					        	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.setup.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_SETUP}
					        		<li><a href="masterfile_voucher.auto_redemption.setup.php">Setup</a></li>
					        	{/if}
					        	
					        	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_voucher.auto_redemption.generate.php") and $sessioninfo.privilege.MST_VOUCHER_AUTO_REDEMP_GENERATE}
					        		<li><a href="masterfile_voucher.auto_redemption.generate.php">Generate Voucher</a></li>
					        		<li><a href="masterfile_voucher.auto_redemption.generate.php?a=his_list">History Listing</a></li>
					        	{/if}
					        </ul>
					    </li>
					{/if}
				</ul>
			</li>
		{/if}
		{if $config.enable_supermarket_code and $config.consignment_modules and $BRANCH_CODE eq 'HQ' and $sessioninfo.privilege.MST_SUPERMARKET_CODE and file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_supermarket_code.php")}
			<li><a href="masterfile_supermarket_code.php">Supermarket Code</a></li>
		{/if}
		{if $config.masterfile_enable_sa && $sessioninfo.privilege.MST_SALES_AGENT}
			<li><a href="#" class=submenu>Sales Agent</a>
				<ul style="width:160px">
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.php")}
						<li><a href="masterfile_sa.php">Create / Edit</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa_commission.php")}
						<li><a href="masterfile_sa_commission.php">Commission Table</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.position_setup.php")}
						<li><a href="masterfile_sa.position_setup.php">Position Table</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.kpi_setup.php")}
						<li><a href="#" class=submenu>KPI</a>
							<ul>
								{if $sessioninfo.privilege.MST_SALES_AGENT_KPI_SETUP && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.kpi_setup.php")}
									<li><a href="masterfile_sa.kpi_setup.php">KPI Table</a></li>
								{/if}
								{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_sa.kpi_result.php")}
									<li><a href="masterfile_sa.kpi_result.php">KPI Result</a></li>
								{/if}
							</ul>
						</li>
					{/if}
					<li><a href="#" class=submenu>Reports</a>
						<ul>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.view_sa_commission.php")}
								<li><a href="report.view_sa_commission.php">View Sales Agent Commission</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_commission_calculation.php")}
								<li><a href="report.sa_commission_calculation.php">Sales Agent Commission Calculation Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_performance.php")}
								<li><a href="report.sa_performance.php">Sales Agent Performance Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_commission_statement_by_company.php")}
								<li><a href="report.sa_commission_statement_by_company.php">Sales Agent Commission Statement by Company Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sa_daily_details.php")}
								<li><a href="report.sa_daily_details.php">Sales Agent Daily Details Report</a></li>
							{/if}
						</ul>
					</li>
				</ul>
			</li>
		{/if}
		{*{if $config.masterfile_enable_return_policy}
			<li><a href="#" class=submenu>Return Policy</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_return_policy.php")}
						<li><a href="masterfile_return_policy.php">Create / Edit</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_return_policy_configure.php")}
						<li><a href="masterfile_return_policy_configure.php">Configure</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.rp_item_return.php")}
						<li><a href="report.rp_item_return.php">Return Policy Item Returned Report</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.rp_pending_item.php")}
						<li><a href="report.rp_pending_item.php">Return Policy Pending Item Report</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		*}
		{if $config.enable_gst && file_exists("`$smarty.server.DOCUMENT_ROOT`/masterfile_gst.php")}
			<li><a href="masterfile_gst.php">Masterfile GST Tax Code</a>
		{/if}
	</ul>
	{/if}

	{if $sessioninfo.privilege.MEMBERSHIP || $sessioninfo.privilege.RPT_MEMBERSHIP}
	<li><a href="#" class=submenu><i class="icofont-users-alt-2 icofont"></i>Membership</a>
	<ul style="min-width: 160px;">
		{if $config.membership_allow_add_at_backend}
		{if $sessioninfo.privilege.MEMBERSHIP_ADD}
			<li><a href="membership.php?a=add">Add New Member</a></li>
		{/if}
		{/if}
		{if $sessioninfo.privilege.MEMBERSHIP_EDIT or $sessioninfo.privilege.MEMBERSHIP_ADD}
			<li><a href="membership.php?t=update">Update Information</a></li>
		{/if}
		{if $sessioninfo.privilege.MEMBERSHIP_VERIFY}
			<li><a href="membership.php?t=verify">Verification</a></li>
		{/if}
		{if $sessioninfo.privilege.MEMBERSHIP_EDIT or $sessioninfo.privilege.MEMBERSHIP_ADD}
			<li><a href="membership.listing.php">Member Listing</a></li>
			<li><a href="membership.php?t=history">Check Points &amp; History</a></li>
		{/if}
	    {if $sessioninfo.privilege.MEMBERSHIP_TERMINATE}
			<li><a href="membership.terminate.php">Terminate</a></li>
		{/if}
		{if $sessioninfo.privilege.RPT_MEMBERSHIP}
		<li><a href="#" class=submenu>Membership Reports</a>
			<ul>
				<li><a href="report.mem_counter.php">Membership Counters Report</a></li>
				<li><a href="report.mem_verification.php">Membership Verification Report</a></li>
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.redemption_points_history.php") && $config.membership_redemption_module}
					<li><a href="report.redemption_points_history.php">Membership Points History Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_expiration.php")}
					<li><a href="report.membership_expiration.php">Membership Expiration Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_renewal.php")}
					<li><a href="report.membership_renewal.php">Membership Renewal Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_fees_collection_summary.php")}
					<li><a href="report.membership_fees_collection_summary.php">Membership Fees Collection Summary Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_daily_collection.php")}
					<li><a href="report.membership_daily_collection.php">Membership Daily Collection Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_points_detail.php")}
					<li><a href="report.membership_points_detail.php">Membership Points Detail Report</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.membership_issued_points.php")}
					<li><a href="report.membership_issued_points.php">Membership Issued Points Report</a></li>
				{/if}
			</ul>
		</li>
		{/if}
		{if $config.membership_redemption_module}
		<li><a href="#" class=submenu>Membership Redemption</a>
		    <ul>
		        {if $sessioninfo.privilege.MEMBERSHIP_SETREDEEM}
		        <li><a href="membership.redemption_setup.php">Redemption Item Setup</a></li>
		        {/if}
		        {if $sessioninfo.privilege.MEMBERSHIP_ITEM_CFRM && $config.membership_redemption_use_enhanced}
		        <li><a href="membership.redemption_item_approval.php">Redemption Item Approval</a></li>
		        {/if}
		        {if $sessioninfo.privilege.MEMBERSHIP_REDEEM}
		        <li><a href="membership.redemption.php">Make Redemption</a></li>
		        {/if}
		        {if $sessioninfo.privilege.MEMBERSHIP_REDEEM or $sessioninfo.privilege.MEMBERSHIP_CANCEL_RE}
		        <li><a href="membership.redemption_history.php">Redemption History</a></li>
		        {/if}
		        {if $sessioninfo.privilege.MEMBERSHIP_REDEEM_RPT}
					<li><a href="membership.redemption_summary.php">Redemption Summary</a></li>
					<li><a href="membership.redemption_ranking.php">Redemption Ranking</a></li>
		        {/if}
		    </ul>
		</li>
		{/if}
		{if $config.membership_control_counter_adjust_point}
			<li><a href="membership.delivery.php">Delivery</a></li>
		{/if}
        
        {if $config.membership_enable_staff_card}
        	{if $sessioninfo.privilege.MEMBERSHIP_STAFF}
	        	<li><a href="#" class=submenu>Membership Staff Card</a>
	        		<ul>
	        			{if $sessioninfo.privilege.MEMBERSHIP_STAFF_SET_QUOTA and file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.staff.setup_quota.php")}
		        			<li><a href="membership.staff.setup_quota.php">Setup Quota</a></li>
	        			{/if}
	        			{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.staff.usage_report.php")}
	        				<li><a href="membership.staff.usage_report.php">Quota Usage Report</a></li>
	        			{/if}
	        		</ul>
	        	</li>
        	{/if}
        {/if}
		
		{if $sessioninfo.privilege.MEMBERSHIP_OVERVIEW}
			<li><a href="#" class=submenu>Membership Overview</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.overview.general.php")}
						<li><a href="membership.overview.general.php">Composition</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.overview.sales.php")}
						<li><a href="membership.overview.sales.php">Sales</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		
		{if $config.membership_mobile_settings and ($sessioninfo.privilege.MEMBERSHIP_MOBILE_ADS_SETUP)}
			<li><a href="#" class="submenu">Mobile App</a>
				<ul>
					{if $sessioninfo.privilege.MEMBERSHIP_MOBILE_ADS_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.mobile_app.ads.php")}
						<li><a href="membership.mobile_app.ads.php">Advertisement Setup</a></li>
					{/if}
					{if $sessioninfo.privilege.MEMBERSHIP_MOBILE_NOTICE_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.mobile_app.notice_board.php")}
						<li><a href="membership.mobile_app.notice_board.php">Notice Board Setup</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.setup.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_SETUP or $sessioninfo.privilege.MEMBERSHIP_PACK_REDEEM or $sessioninfo.privilege.MEMBERSHIP_PACK_REPORT)}
		<li><a href="#" class="submenu">Membership Package</a>
		    <ul>
		        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.setup.php") and $sessioninfo.privilege.MEMBERSHIP_PACK_SETUP}
					<li><a href="membership.package.setup.php">Package Setup</a></li>
		        {/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.details.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_REDEEM)}
					<li><a href="membership.package.details.php?a=scan_member">Package Redemption</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.package.rating_report.php") and ($sessioninfo.privilege.MEMBERSHIP_PACK_REPORT)}
					<li><a href="membership.package.rating_report.php">Package Rating Analysis Report</a></li>
				{/if}
		    </ul>
		</li>
		{/if}
		
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.credit.promotion.php") and ($sessioninfo.privilege.MEMBERSHIP_CREDIT_PROMO or $sessioninfo.privilege.MEMBERSHIP_CREDIT_SETTINGS)}
		<li><a href="#" class="submenu">Membership Credit</a>
		    <ul>
		        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.credit.promotion.php") and $sessioninfo.privilege.MEMBERSHIP_CREDIT_PROMO}
					<li><a href="membership.credit.promotion.php">Credit Promotion</a></li>
		        {/if}
		        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/membership.credit.settings.php") and $sessioninfo.privilege.MEMBERSHIP_CREDIT_SETTINGS}
					<li><a href="membership.credit.settings.php">Credit Settings</a></li>
		        {/if}
		    </ul>
		</li>
		{/if}
	</ul>
	{/if}

    {if $config.enable_fresh_market_sku and $sessioninfo.privilege.FM}
    <li><a href="#" class="submenu"><i class="icofont-food-cart icofont"></i>Fresh Market</a>
        <ul style="min-width: 160px;">
            {if $sessioninfo.privilege.FM_WRITE_OFF and file_exists("`$smarty.server.DOCUMENT_ROOT`/adjustment.fresh_market_write_off.php")}
		        <li><a href="/adjustment.fresh_market_write_off.php"> SKU Write-Off</a></li>
		    {/if}
		    {if $sessioninfo.privilege.FM_STOCK_TAKE}
			<li><a href="#" class="submenu">Stock Take</a>
			    <ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/admin.fresh_market_stock_take.php")}
					    <li><a href="admin.fresh_market_stock_take.php"> Stock Take</a></li>
					    <li><a href="admin.fresh_market_stock_take.php?a=import_page"> Import / Reset Stock Take</a></li>
					    <li><a href="admin.fresh_market_stock_take.php?a=change_batch">Change Batch</a></li>
					{/if}
				</ul>
			</li>
			{/if}
			{if $sessioninfo.privilege.FM_REPORT}
			<li><a href="#" class="submenu">Report</a>
			    <ul>
			        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.fresh_market_stock_take.php")}
			    		<li><a href="report.fresh_market_stock_take.php"> Fresh Market Stock Take Report</a></li>
			    	{/if}
			    	{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.fresh_market_sales.php")}
			    		<li><a href="report.fresh_market_sales.php"> Fresh Market Sales Report</a></li>
			    	{/if}
			    </ul>
			</li>
			{/if}
        </ul>
    </li>
    {/if}
    
    <!-- Report -->
    {if !$config.consignment_modules}
    	{capture assign=report_dropdown_html}{strip}
			{include file=menu.reports.tpl}
			{if $sessioninfo.privilege.REPORTS_SKU}
				<li><a href="#" class="submenu">{*<img src="/ui/icons/box.png" align="absmiddle" border="0" />*} SKU Reports</a>
					<ul>
						{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.batch_no.php")}<li><a href="report.batch_no.php">Batch No Report</a></li>{/if}
						
						{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.batch_no_transaction_detail.php")}<li><a href="report.batch_no_transaction_detail.php">Batch No Transaction Details Report</a></li>{/if}
						
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.brand_sales_price_type_discount.php")}
							<li><a href="report.brand_sales_price_type_discount.php">Brand / Vendor Sales by Price Type and Discount Report</a></li>
						{/if}						
						
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.closing_stock_by_sku.php")}
							<li><a href="report.closing_stock_by_sku.php">Closing Stock by SKU Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.closing_stock.php")}
							<li><a href="report.closing_stock.php">Closing Stock Report</a></li>
						{/if}
						{if $config.enable_one_color_matrix_ibt && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.broken_size_clr_by_branch.php")}
							<li><a href="report.broken_size_clr_by_branch.php">Broken Size & Color by Branch Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.mprice_sales.php")}<li><a href="report.mprice_sales.php">MPrice Sales Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.multi_branch_sales.php")}
							<li><a href="report.multi_branch_sales.php">Multi Branch Sales Report</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.multi_branch_stock_balance.php")}
							<li><a href="report.multi_branch_stock_balance.php">Multi Branch Stock Balance</a></li>
						{/if}
						<li><a href="report.negative_stock.php">Negative Stock</a></li>
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.new_sku_sales_monitor.php")}<li><a href="report.new_sku_sales_monitor.php">New SKU Sales Monitoring Report</a></li>{/if}
						{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_activation.php")}<li><a href="report.sn_activation.php">Serial No Activation Report</a></li>{/if}
						{if $config.enable_sn_bn && file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_expiry.php")}<li><a href="report.sn_expiry.php">Serial No Expiry Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_return.php")}<li><a href="report.sn_return.php">Serial No Return Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sn_status.php")}<li><a href="report.sn_status.php">Serial No Status Report</a></li>{/if}
						<li><a href="report.sku_items_gp.php">SKU Items Gross Profit Report</a></li>
						{if $config.enable_sku_monitoring and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_monitoring.php")}
						<li><a href="report.sku_monitoring.php">SKU Monitoring</a></li>
						{/if}
						
						{if $config.enable_sku_monitoring2 and file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_monitoring2.php")}
							<li><a href="report.sku_monitoring2.php">SKU Monitoring 2</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_sales.php")}<li><a href="report.sku_sales.php">SKU Sales Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_sales_purchase_profit_margin.php")}<li><a href="report.sku_sales_purchase_profit_margin.php">SKU Sales & Purchase Profit Margin Special Calculation Report</a></li>{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.sku_trans_type_filter.php")}
						<li><a href="report.sku_trans_type_filter.php">SKU Transaction Type Filter</a></li>
						{/if}
						<li><a href="report.slow_moving_item.php">Slow Moving Items</a></li>
						<li><a href="report.stock_aging.php">Stock Aging Report</a></li>
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_balance_detail_by_day.php")}<li><a href="report.stock_balance_detail_by_day.php">Stock Balance Detail by SKU Report</a></li>{/if}
						<li><a href="report.stock_balance.php">Stock Balance Report by Department</a></li>
						<li><a href="report.stock_balance_summary.php">Stock Balance Summary</a></li>
					</ul>
				</li>
			{/if}

            {if $config.enable_gst and $sessioninfo.privilege.REPORTS_GST}
            <li><a href="#" class=submenu>GST Reports</a>
				<ul>
					<li><a href="report.gst_summary.php">GST Summary</a></li>
				</ul>
            {/if}
			
			{if $sessioninfo.privilege.REPORTS_CUSTOM_BUILDER_CREATE || ($sessioninfo.privilege.REPORTS_CUSTOM_VIEW && ($available_custom_report_list.group|@count > 0 || $available_custom_report_list.nogroup|@count > 0)) }
			<li><a href="#" class="submenu">Custom Report</a>
				<ul style="min-width:160px;">
					{if $sessioninfo.privilege.REPORTS_CUSTOM_BUILDER_CREATE and file_exists("`$smarty.server.DOCUMENT_ROOT`/custom_report.builder.php")}
						<li><a href="custom_report.builder.php">Report Builder</a></li>
					{/if}
					
					{if $sessioninfo.privilege.REPORTS_CUSTOM_VIEW}
						{* Group *}
						{foreach from=$available_custom_report_list.group key=custom_report_group_name item=custom_report_group_list}
							<li><a href="#" class="submenu">{$custom_report_group_name}</a>
								<ul >
									{foreach from=$custom_report_group_list item=r}
										<li><a href="custom_report.php?report_id={$r.id}">{$r.report_title}</a></li>
									{/foreach}
								</ul>
							</li>
						{/foreach}
						
						{* non-group *}
						{foreach from=$available_custom_report_list.nogroup item=r}
							<li><a href="custom_report.php?report_id={$r.id}">{$r.report_title}</a></li>
						{/foreach}
					{/if}
				</ul>
			</li>
			{/if}
			
			{if $config.show_old_report}
				{capture assign=report_html}{strip}
					{if $sessioninfo.privilege.REPORTS_SALES}
						<li><a href="sales_report.brand.php">{*<img src="/ui/print.png" align="absmiddle" border="0">*}Daily Brand Sales Report</a></li>
						<li><a href="sales_report.vendor.php">{*<img src="/ui/print.png" align="absmiddle" border="0">*}Daily Vendor Sales Report</a></li>
						<li><a href="sales_report.department.php">{*<img src="/ui/print.png" align="absmiddle" border="0">*}Department Monthly Sales Report</a></li>
					{/if}
				{/strip}{/capture}
				{if $report_html}
					<li><a href="#" class="submenu"> Old Reports</a>
						<ul>{$report_html}</ul>
					</li>
				{/if}
			{/if}
	
			{if isset($config.custom_report)}
				{include file=$config.custom_report}
			{/if}
	
			{if $sessioninfo.id eq 1 && $BRANCH_CODE eq 'HQ'}
				<li><a href="/pivot.php?a=new"><img src=/ui/ed.png align=absmiddle border=0>&nbsp; Create/Modify Reports</a>
			{/if}
	
			{if $sessioninfo.privilege.PIVOT_SALES and $pivots}
			<li><a href="#" class=submenu>Sales Reports</a>
				<ul>
					{section name=i loop=$pivots}
					{if $pivots[i].rpt_group eq 'Sales'}<li><a href="/pivot.php?a=load&id={$pivots[i].id}">{$pivots[i].title}</a></li>{/if}
					{/section}
				</ul>
			{/if}
	
			{if $sessioninfo.privilege.PIVOT_OFFICER and $pivots}
			<li><a href="#" class=submenu>Officer Reports</a>
				<ul>
					{section name=i loop=$pivots}
					{if $pivots[i].rpt_group eq 'Officer'}<li><a href="/pivot.php?a=load&id={$pivots[i].id}">{$pivots[i].title}</a></li>{/if}
					{/section}
				</ul>
			{/if}
			
			{if $sessioninfo.privilege.PIVOT_MANAGEMENT and $pivots}
			<li><a href="#" class=submenu>Management Reports</a>
				<ul>
					{section name=i loop=$pivots}
					{if $pivots[i].rpt_group eq 'Management'}<li><a href="/pivot.php?a=load&id={$pivots[i].id}">{$pivots[i].title}</a></li>{/if}
					{/section}
				</ul>
			{/if}
			
			{if $config.monthly_closing and $sessioninfo.privilege.REPORTS_MONTHLY_CLOSING}
				<li><a href="#" class=submenu>Monthly Closing</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_closing_stock_balance.php") }
					<li><a href="report.monthly_closing_stock_balance.php">Monthly Closing Stock Balance Report by Department</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.monthly_closing_stock_balance_summary.php") }
					<li><a href="report.monthly_closing_stock_balance_summary.php">Monthly Closing Stock Balance Summary</a></li>
					{/if}
				</ul>
			{/if}
	
			{if $sessioninfo.privilege.STOCK_CHECK_REPORT and !strstr($config.hide_from_menu,'STOCK_CHECK_REPORT')}
			<li><a href="#" class=submenu>Stock Take</a>
				<ul>
					<li><a href="pivot.stockchk.php?a=list">Customize Reports</a></li>
					<li><a href="report.stock_check.php">Stock Take Summary</a></li>
					<li><a href="report.stock_take_variance_by_dept.php">Stock Take Variance by Dept Report</a></li>
					<li><a href="report.stock_take_variance.php">Stock Take Variance Report</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.stock_take_inquiry.php")}
						<li><a href="report.stock_take_inquiry.php">Stock Take Inquiry</a></li>
					{/if}
				</ul>
			{/if}
			{if $sessioninfo.level>=500}
			{if $sessioninfo.privilege.PO_REPORT}
			<li><a href="vendor_po.summary.php">Vendor Purchase Ranking</a></li>
			{/if}
			{/if}

            

		{/strip}{/capture}
		
		{if $report_dropdown_html}
			<li><a href="#" class=submenu><i class="icofont-chart-histogram icofont"></i>Reports</a>
			<ul style="width:200px">
				{$report_dropdown_html}
			</ul>
			</li>
		{/if}
	{/if}
	<!-- End of Report -->
	{if $sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.POS_VERIFY_SKU or $sessioninfo.privilege.POS_REPORT}
		{assign var=pos_checking value=true}
	{/if}
	
	{if $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE or $sessioninfo.privilege.CC_DEPOSIT}
		{assign var=cc_checking value=true}
	{/if}
	
	{if $sessioninfo.privilege.FRONTEND_SETUP or $sessioninfo.privilege.PROMOTION or $pos_checking or $cc_checking or $sessioninfo.privilege.FRONTEND_PRINT_FULL_TAX_INVOICE}
	<li><a href="#" class=submenu><i class="icofont-document-folder icofont"></i>Front End</a>
    <ul style="width:160px">
    	{if $sessioninfo.privilege.FRONTEND_SETUP}
    	<li><a href="#" class=submenu>Settings</a>
			<ul>
				<li><a href="frontend.php">Counters Setup</a></li>
				{if $sessioninfo.level >=9999 && $sessioninfo.is_arms_user && file_exists("`$smarty.server.DOCUMENT_ROOT`/info.counter_configuration.php")}<li><a href="info.counter_configuration.php">Counter Setup Information</a>{/if}
		        <li><a href="pos.settings.php">POS Settings</a></li>
		        {if $sessioninfo.privilege.FRONTEND_SET_CASHIER and file_exists("`$smarty.server.DOCUMENT_ROOT`/front_end.cashier_setup.php")}
		        	<li><a href="front_end.cashier_setup.php">Cashier Setup</a></li>
		        {/if}
	        </ul>
        </li>
		{/if}
		
		{if $sessioninfo.privilege.PROMOTION}
		<li><a href="#" class=submenu>Promotion</a>
			<ul>
				<li><a href="promotion.php">Create / Edit</a></li>
				{if $sessioninfo.privilege.PROMOTION_APPROVAL}<li><a href="promotion_approval.php">Approval</a></li>{/if}
				<li><a href="report.promotion_summary.php">Promotion Summary</a></li>
				<li><a href="report.promotion_result.php">Promotion Result</a></li>
				{*{if $config.enable_mix_and_match_promotion}{/if}*}
				<li><a href="report.mix_n_match_promotion_result.php">Mix and Match Promotion Result</a></li>
			</ul>
		</li>
		{/if}
		{if $sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE}
	        {if $config.counter_collection_server}
	        	{if $sessioninfo.privilege.POS_BACKEND}
		        	<li><a href="javascript:void(open_from_dc('{$config.counter_collection_server}/sales_live.php?',{$sessioninfo.id},{$sessioninfo.branch_id}, 'Sales Live'))">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*} Sales Live</a></li>
					<li><a href="javascript:void(open_from_dc('{$config.counter_collection_server}/pos_live.php?',{$sessioninfo.id},{$sessioninfo.branch_id}, 'POS Live'))">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*} Pos Live</a></li>
				{/if}
				{if ($sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE)}
					<li><a href="javascript:void(open_cc('{$config.counter_collection_server}',{$sessioninfo.id},{$sessioninfo.branch_id}))">Counter Collection</a></li>
				{/if}
			{else}
				{if $sessioninfo.privilege.POS_BACKEND}
					<li><a href="pos_live.php">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*} POS Live</a></li>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_monitoring.php")}
						<li><a href="pos_monitoring.php">POS Monitoring (DEV)</a></li>
					{/if}
					<li><a href="sales_live.php">{*<img src="/ui/icons/chart_curve.png" align=absmiddle border=0>&nbsp;*} Sales Live</a></li>
				{/if}
				{if ($sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE)}
					<li><a href="counter_collection.php">Counter Collection</a></li>
				{/if}
	        {/if}
		<!--li><a href="collection_report.php">Collection Report</a></li-->
		{/if}
		{*if ($sessioninfo.privilege.POS_BACKEND or $sessioninfo.privilege.CC_FINALIZE or $sessioninfo.privilege.CC_UNFINALIZE or $sessioninfo.privilege.POS_VERIFY_SKU)}		
			{if file_exists('pos.invalid_sku.php')}
				<li><a href="pos.invalid_sku.php">Invalid SKU Sold</a></li>
			{/if}
		{/if*}
		
		<!-- Deposit -->
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.deposit_cancellation.php") and $sessioninfo.privilege.CC_DEPOSIT}
			<li><a href="#" class="submenu">Deposit</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.deposit_listing.php") and $sessioninfo.privilege.CC_DEPOSIT}
						<li><a href="pos.deposit_listing.php">Deposit Listing</a></li>
					{/if}
					{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.deposit_cancellation.php") and $sessioninfo.privilege.CC_DEPOSIT}
						<li><a href="pos.deposit_cancellation.php">Deposit Cancellation</a></li>
					{/if *}
				</ul>
			</li>
		{/if}
		
		<!-- Invalid SKU -->
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.invalid_sku.php") and $sessioninfo.privilege.POS_VERIFY_SKU}
			<li><a href="#" class="submenu">Invalid SKU</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.invalid_sku.php") and $sessioninfo.privilege.POS_VERIFY_SKU}
						<li><a href="pos.invalid_sku.php">Verify Invalid SKU</a></li>
					{/if}
				</ul>
			</li>
		{/if}
		
		<!-- Trade In -->
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.trade_in.write_off.php") and $sessioninfo.privilege.POS_TRADE_IN_WRITEOFF}
			<li><a href="#" class="submenu">Trade In</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.trade_in.write_off.php") and $sessioninfo.privilege.POS_TRADE_IN_WRITEOFF}
						<li><a href="pos.trade_in.write_off.php">Manage Trade In Write-Off</a></li>
					{/if}
				</ul>
			</li>
		{/if}
				
        {if $sessioninfo.privilege.POS_REPORT}
        <li><a href="#" class=submenu>POS Report</a>
            <ul style="min-width:160px;">
            	<li><a href="#" class="submenu">Transaction</a>
            		<ul>
						<li><a href="pos_report.tran_details.php">Transaction Details</a></li>
                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.tran_details_item_listing.php")}
							<li><a href="pos_report.tran_details_item_listing.php">Transaction Details with Item Listing</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.sku_tran_details.php")}
							<li><a href="pos_report.sku_tran_details.php">SKU Transaction Details</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.return_item.php")}
							<li><a href="pos_report.return_item.php">POS Return Items Report</a></li>
						{/if}
					</ul>
				</li>
				<li><a href="#" class="submenu">Cashier</a>
					<ul>
						<li><a href="pos_report.cashier_performance.php">Cashier Performance Report</a></li>
						<li><a href="pos_report.cashier_unnormal_behaviour.php">Cashier Abnormal Behaviour Report</a></li>
                        {if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cashier_variance.php")}
    						<li><a href="pos_report.cashier_variance.php">Cashier Variance Report</a></li>
                        {/if}
						{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.abnormal_clocking_log.php")}
							<li><a href="pos_report.abnormal_clocking_log.php">Abnormal Clocking Log</a></li>
						{/if *}
					</ul>
				</li>
				<li><a href="#" class="submenu">Counter Collection</a>
					<ul>
						<li><a href="pos_report.counter_collection_below_cost.php">Counter Collection below Cost Report</a></li>
						<li><a href="report.counter_collection_sales_vs_category_sales.php">Counter Collection Sales vs Category Sales</a></li>
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/report.daily_counter_collection.php")}
							<li><a href="report.daily_counter_collection.php">Daily Counter Collection Cash Denomination</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.payment_list.php")}
							<li><a href="pos_report.payment_list.php">Payment List Report</a></li>
						{/if}
						
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.counter_collection_details.php")}
							<li><a href="pos_report.counter_collection_details.php">Counter Collection Details Report</a></li>
						{/if}
						
						{if $config.counter_collection_enable_co2_module and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.counter_collection_co2.php")}
							<li><a href="pos_report.counter_collection_co2.php">Counter Collection CO2</a></li>
						{/if}

						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cash_advance.php")}
							<li><a href="pos_report.cash_advance.php">Cash Advance Report</a></li>
						{/if}
					</ul>
				</li>
				
				{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.temp_price_report.php")}
					<li><a href="#" class="submenu">Temp Price</a>
						<ul>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.temp_price_report.php")}
								<li><a href="pos_report.temp_price_report.php">Temp Price Report</a></li>
							{/if}
						</ul>
					</li>
				{/if *}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cross_branch_deposit.php") or file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cancel_deposit.php") or file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.in_out_deposit.php")}
					<li><a href="#" class="submenu">Deposit</a>
						<ul>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cross_branch_deposit.php")}
								<li><a href="pos_report.cross_branch_deposit.php">Cross Branch Deposit Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.cancel_deposit.php")}
								<li><a href="pos_report.cancel_deposit.php">Cancelled Deposit Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.in_out_deposit.php")}
								<li><a href="pos_report.in_out_deposit.php">Daily Deposit In/Out Report</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.trade_in.php")}
					<li><a href="#" class="submenu">Trade In</a>
						<ul>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.trade_in.php")}
								<li><a href="pos_report.trade_in.php">Trade In Report</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				
				{if $config.enable_gst}
					<li><a href="#" class="submenu">GST</a>
						<ul>
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.counter_sales_gst_report.php")}
								<li><a href="pos_report.counter_sales_gst_report.php">Counter Sales GST Report</a></li>
							{/if}
							{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.receipt_summary_gst_report.php")}
								<li><a href="pos_report.receipt_summary_gst_report.php">Receipt Summary GST Report</a></li>
							{/if}
                            {if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.gst_credit_note_report.php")}
								<li><a href="pos_report.gst_credit_note_report.php">GST Credit Note Report</a></li>
							{/if}
							{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.sku_gst_report.php")}
								<li><a href="pos_report.sku_gst_report.php">SKU GST Report</a></li>
							{/if *}
						</ul>
					</li>
				{/if}
				
				<li><a href="#" class="submenu">Service Charge</a>
					<ul>
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/pos_report.service_charge_summary.php")}
							<li><a href="pos_report.service_charge_summary.php">Service Charge Summary</a></li>
						{/if}
					</ul>
				</li>
			</ul>
		</li>
		{/if}
        {if $sessioninfo.privilege.FRONTEND_PRINT_FULL_TAX_INVOICE and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.print_full_tax_invoice.php")}
          <li><a href="pos.print_full_tax_invoice.php">Print Full Tax Invoice</a></li>
        {/if}
        {if $sessioninfo.privilege.FRONTEND_EJOURNAL and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.ejournal.php")}
            <li><a href="pos.ejournal.php">E-Journal</a></li>
        {/if}
        {if $sessioninfo.privilege.FRONTEND_AUDIT_LOG and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.audit_log.php")}
            <li><a href="pos.audit_log.php">Audit Log</a></li>
        {/if}
        {if $sessioninfo.privilege.FRONTEND_ANNOUNCEMENT and file_exists("`$smarty.server.DOCUMENT_ROOT`/front_end.announcement.php")}
		<li><a href="front_end.announcement.php">POS Announcement</a></li>
		{/if}
		{if $sessioninfo.privilege.FRONTEND_POPULAR_ITEMS_SETUP and file_exists("`$smarty.server.DOCUMENT_ROOT`/pos.popular_items_listing_setup.php")}
		<li><a href="pos.popular_items_listing_setup.php">Popular Items Listing Setup</a></li>
		{/if}
	</ul>
	</li>
	{/if}

	{if $config.enable_suite_device and ($sessioninfo.privilege.SUITE_MANAGE_DEVICE)}
		<li><a href="#" class=submenu><i class="icofont-contrast icofont"></i>Suite</a>
			<ul style="min-width:160px;">
				{if $sessioninfo.privilege.SUITE_MANAGE_DEVICE and file_exists("`$smarty.server.DOCUMENT_ROOT`/suite.manage_device.php")}
					<li><a href="#" class="submenu">Device</a>
						<ul>
							<li><a href="suite.manage_device.php">Suite Device Setup</a></li>
						</ul>
					</li>
				{/if}
				{if $sessioninfo.privilege.SUITE_POS_DEVICE_MANAGEMENT and file_exists("`$smarty.server.DOCUMENT_ROOT`/suite.pos_device_management.php")}
				<li><a href="suite.pos_device_management.php">POS Device Management</a></li>
				{/if}
			</ul>
		</li>
	{/if}
	
	{if $config.arms_marketplace_settings and ($config.arms_marketplace_settings.branch_code eq $BRANCH_CODE || $BRANCH_CODE eq 'HQ') and ($sessioninfo.privilege.MARKETPLACE_MANAGE_SKU or $sessioninfo.privilege.MARKETPLACE_SETTINGS or ($config.arms_marketplace_settings.branch_code eq $BRANCH_CODE and $sessioninfo.privilege.MARKETPLACE_LOGIN))}
		<li><a href="#" class="submenu"><i class="icofont-food-cart icofont"></i>Marketplace</a>
			<ul style="min-width:100px;">
				{* if file_exists("`$smarty.server.DOCUMENT_ROOT`/marketplace.home.php") and ($config.arms_marketplace_settings.branch_code eq $BRANCH_CODE or $BRANCH_CODE eq 'HQ') and $sessioninfo.privilege.MARKETPLACE_LOGIN}
					<li><a href="marketplace.home.php?a=goto_marketplace" target="_blank">Go to Marketplace</a></li>
				{/if *}
				
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/marketplace.settings.php") and $sessioninfo.privilege.MARKETPLACE_SETTINGS}
					<li><a href="marketplace.settings.php">Settings</a></li>
				{/if}
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/marketplace.manage_sku.php") and $sessioninfo.privilege.MARKETPLACE_MANAGE_SKU}
					<li><a href="marketplace.manage_sku.php">Manage SKU</a></li>
				{/if}
			</ul>
		</li>
	{/if}
	
	
	{if $sessioninfo.privilege.MKT}
	<li><a href="#" class=submenu>Marketing Tools</a>
	    <ul>
	        <li><a href="mkt_annual.php"><img src=/ui/icons/calendar.png align=absmiddle border=0> Annual Planner and Review</a>
			{if $BRANCH_CODE eq 'HQ'}
			<li><a href="mkt_settings.php"><img src=/ui/ed.png align=absmiddle border=0>&nbsp; Settings</a>
			<li><a href="mkt0.php"><img src=/ui/ed.png align=absmiddle border=0>&nbsp; Create New Offers</a>
			{/if}
			<li><a href="mkt_review_keyin.php"><img src=/ui/ed.png align=absmiddle border=0>&nbsp; Daily Sales Keyin</a>
			<li><a href="mkt1.php"><sup>1</sup> Branch Sales Target and Expenses</a>
			<li><a href="mkt2.php"><sup>2</sup> Department Target Review</a>
			<li><a href="mkt3.php"><sup>3</sup> Brand/Item Proposal (by Branch)</a>
			<li><a href="mkt4.php"><sup>4</sup> Brand/Item Planner (by HQ)</a>
			<li><a href="mkt5.php"><sup>5</sup> Publishing Planner (by HQ)</a>
			<!--li><a href="mkt_status.php"><sup>5.2</sup> Offer Publishing Planner (by HQ)</a-->
			<li><a href="mkt6.php"><sup>6</sup> A&amp;P Materials Review</a>
		</ul>
	</li>
	{/if}
	
	{if $config.enable_web_bridge and $sessioninfo.privilege.WB}
		<li><a href="#" class="submenu">Web Bridge</a>
			<ul style="min-width:160px;">
				{if ($sessioninfo.privilege.WB_AP_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.settings.php")) or ($sessioninfo.privilege.WB_AP_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.php"))}
					<li><a href="#" class="submenu">AP Trans</a>
						<ul>
							{if $sessioninfo.privilege.WB_AP_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.settings.php")}
								<li><a href="web_bridge.ap_trans.settings.php">Settings</a></li>
							{/if}
							{if $sessioninfo.privilege.WB_AP_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ap_trans.php")}
								<li><a href="web_bridge.ap_trans.php">AP Trans</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				{if ($sessioninfo.privilege.WB_AR_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.settings.php")) or ($sessioninfo.privilege.WB_AR_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.php"))}
					<li><a href="#" class="submenu">AR Trans</a>
						<ul>
							{if $sessioninfo.privilege.WB_AR_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.settings.php")}
								<li><a href="web_bridge.ar_trans.settings.php">Settings</a></li>
							{/if}				
							{if $sessioninfo.privilege.WB_AR_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.ar_trans.php")}
								<li><a href="web_bridge.ar_trans.php">AR Trans</a></li>
							{/if}
						</ul>
					</li>
				{/if}
				{if ($sessioninfo.privilege.WB_CC_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.settings.php")) or ($sessioninfo.privilege.WB_CC_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.php"))}
					<li><a href="#" class="submenu">CC Trans</a>
						<ul>
							{if $sessioninfo.privilege.WB_CC_TRANS_SETT and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.settings.php")}
								<li><a href="web_bridge.cc_trans.settings.php">Settings</a></li>
							{/if}
							{if $sessioninfo.privilege.WB_CC_TRANS and file_exists("`$smarty.server.DOCUMENT_ROOT`/web_bridge.cc_trans.php")}
								<li><a href="web_bridge.cc_trans.php">CC Trans</a></li>
							{/if}
						</ul>
					</li>
				{/if}
			</ul>
		</li>
	{/if}
	
	<li><a href="#" class=submenu><i class="icofont-money icofont"></i>Miscellaneous</a>
	<ul style="min-width:160px;">
		{if $sessioninfo.level>0}
		{if $sessioninfo.privilege.UPDATE_PROFILE}<li><a href="my_profile.php">Update My Profile</a>{/if}
		{if $sessioninfo.privilege.VIEWLOG}<li><a href="viewlog.php">View Logs</a>{/if}
		{if $sessioninfo.level >=1000 && (!$config.single_server_mode || $config.show_server_status)}<li><a href="server_status.php">Server Status</a>{/if}
		{/if}
		<li><a href="/login.php?logout=1" onclick="return confirm('{$LANG.CONFIRM_LOGOUT}')">Logout</a>
		{if $sessioninfo.level>=9999}
			{if $smarty.session.admin_session}
			<li><a href="/login.php?logout_as=1">Logout as {$sessioninfo.u}</a>
			{else}
			<li><a href="#" onclick="return login_as();">Login as...</a>
			{/if}
		{/if}
		<li><a href="/front_end.check_code.php" target=_fe>Check Code</a>
		<li><a href="/price_check" target=_fe>Price Checker</a>
		{*
		{if file_exists('PO_COST.DAT')}
		<li><a href="/misc.po_cost.php" target=_pc>Multics PO Cost</a>
		{/if}
		*}
		{if is_dir('db') and $sessioninfo.level>=9999}
			{*<li><a href="/misc.pos_db.php">POS db</a>*}
		{/if}
		{if file_exists("`$smarty.server.DOCUMENT_ROOT`/eform.php")}
	        <li><a href="#" class="submenu">eForm</a>          
	          <ul>
	            {if $sessioninfo.privilege.E_FORM_SETUP}<li><a href="/eform.setup.php">Setup eForm</a></li>{/if}
	            <li><a href="/eform.php">My eForms</a></li>
	            {if $sessioninfo.privilege.E_FORM_APPROVAL}
	            <li><a href="/eform.approval.php">eForm Approval</a></li>
	            {/if}
	          </ul>          
	        </li>
        {/if}
		<li><a href="./ui/3of9/mrvcode39extma.ttf">Download Barcode Font</a>
	</ul>
	{if $config.consignment_modules}
	<li><a href="#" class=submenu><i class="icofont-package icofont"></i>Consignment</a>
	    <ul style="width:160px">
	        {if $sessioninfo.privilege.CON_MONTHLY_REPORT}
	        	<li><a href="consignment.print_monthly_report.php">Print Monthly Report</a></li>
	        {/if}
	        
	        {if $sessioninfo.privilege.CON_MONTHLY_REPORT}
	        	<li><a href="consignment.monthly_report.php">Monthly Report</a></li>
	        {/if}
	        
	        {if $sessioninfo.privilege.CON_INVOICE}
	        	<li><a href="#" class=submenu>Invoice</a>
	        	    <ul>
	        			<li><a href="consignment_invoice.php">Invoice</a></li>
                        {if $sessioninfo.privilege.CI_APPROVAL}
                            <li><a href="/consignment_invoice_approval.php">Invoice Approval</a></li>
                        {/if}
	        			<li><a href="consignment_invoice.summary.php">Invoice Summary</a></li>
					</ul>
	        	</li>
	        {/if}
	        {if $sessioninfo.privilege.CON_VIEW_REPORT or $sessioninfo.privilege.REPORTS_SALES or $sessioninfo.privilege.REPORTS_SKU or ($sessioninfo.privilege.STOCK_CHECK_REPORT and !strstr($config.hide_from_menu,'STOCK_CHECK_REPORT'))}
		        <li><a href="#" class=submenu>Report</a>
		            <ul style="width:160px">
		            	{include file=menu.consignment_reports.tpl}
		            </ul>
		        </li>
	        {/if}
	        {if $config.enable_consignment_transport_note}
	        	<li><a href="consignment.transport_note.php"><i class="icofont-notepad icofont"></i>Transport Note</a></li>
	        {/if}
	        {if $sessioninfo.privilege.CON_DEBIT_NOTE or $sessioninfo.privilege.CON_CREDIT_NOTE}
	            <li><a href="#" class=submenu><i class="icofont-credit-card icofont"></i> Credit / Debit Note</a>
	                <ul>
	                    {if $sessioninfo.privilege.CON_CREDIT_NOTE and file_exists("`$smarty.server.DOCUMENT_ROOT`/consignment.credit_note.php")}
	                        <li><a href="consignment.credit_note.php">Credit Note</a></li>
	                    {/if}
                        {if $sessioninfo.privilege.CON_CN_APPROVAL}
                            <li><a href="/consignment.credit_note.approval.php">Credit Note Approval</a></li>
                        {/if}
	                    {if $sessioninfo.privilege.CON_DEBIT_NOTE and file_exists("`$smarty.server.DOCUMENT_ROOT`/consignment.debit_note.php")}
	                        <li><a href="consignment.debit_note.php">Debit Note</a></li>
	                    {/if}
                        {if $sessioninfo.privilege.CON_DN_APPROVAL}
                            <li><a href="/consignment.debit_note.approval.php">Debit Note Approval</a></li>
                        {/if}
	                    {if $sessioninfo.privilege.CON_CREDIT_NOTE and file_exists("`$smarty.server.DOCUMENT_ROOT`/consignment.credit_note.summary.php")}
	                        <li><a href="consignment.credit_note.summary.php">Credit Note Summary</a></li>
	                    {/if}
	                    {if $sessioninfo.privilege.CON_DEBIT_NOTE and file_exists("`$smarty.server.DOCUMENT_ROOT`/consignment.debit_note.summary.php")}
	                        <li><a href="consignment.debit_note.summary.php">Debit Note Summary</a></li>
	                    {/if}
	                </ul>
	            </li>
	        {/if}
			{if $config.consignment_multiple_currency && file_exists("`$smarty.server.DOCUMENT_ROOT`/consignment.forex.php")}
				<li><a href="consignment.forex.php"><i class="icofont-bank icofont"></i> Forex</a></li>
			{/if}
            {if $config.consignment_outlet_reorder_report && file_exists("`$smarty.server.DOCUMENT_ROOT`/consignment.outlet_reorder_report.php")}
				<li><a href="consignment.outlet_reorder_report.php"><i class="icofont-page icofont"></i> Outlet Reorder Report</a></li>
			{/if}
	    </ul>
	</li>
	{/if}

	{if $config.enable_sop and $sessioninfo.privilege.SOP and $BRANCH_CODE eq 'HQ'}
		<li><a href="/sop" class=submenu>SOP</a>
		</li>
	{/if}
	
	{if $config.custom_extra_menu and file_exists("`$smarty.server.DOCUMENT_ROOT`/templates/`$config.custom_extra_menu`")}
		{include file=$config.custom_extra_menu}
	{/if}
	
	{if $config.consignment_modules}
		<div style="float:right;"><button class="small" onClick="open_window('branches_list.php','branches_list','status=0,height=500,width=500,toolbar=0,location=0,menubar=0,directories=0,resizable=1,scrollbars=1');">Branches List</button></div>
	{/if}
</ul>
<br style="clear:both">
</div>
</div>

{literal}
<script>
//buildsubmenus();
</script>
{/literal}

{* Sales Agent Portal *}
{elseif $sa_session.id}
	<div class="stdframe nav-font" style="height:24px;margin:0;padding:0;">
		<div class="suckerdiv main-nav" style="min-width:780px;width:100%;">
			<ul id="suckertree">
				<li><a href="#" class="submenu">Sales Agent</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sa.report.view_sa_commission.php")}
						<li><a href="sa.report.view_sa_commission.php">View Sales Agent Commission</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sa.report.sa_commission_calculation.php")}
						<li><a href="sa.report.sa_commission_calculation.php">Sales Agent Commission Calculation Report</a></li>
					{/if}
				</ul>
				{if file_exists("`$smarty.server.DOCUMENT_ROOT`/sa.kpi_rating.php")}
					<li><a href="sa.kpi_rating.php">Rate KPI</a></li>
				{/if}
				<li><a href="/login.php?sa_logout=1" onclick="return confirm('{$LANG.CONFIRM_LOGOUT}')">Logout</a>
			</ul>
		</div>
	</div>
	
{* Vendor Portal *}
{elseif $vp_session.id}
	<div class="stdframe nav-font" style="height:24px;margin:0;padding:0;">
		<div class="suckerdiv main-nav" style="min-width:780px;width:100%;">
			<ul id="suckertree">
				<li><a href="#">Home</a>
					<ul>
						<li><a href="home.php">Dashboard</a></li>
						<li><a href="javascript:void(goto_branch(0))">Go to branch...</a></li>
					</ul>
				</li>
				<li><a href="#" class="submenu">Operations</a>
				<ul style="min-width:160px;">
					{if !$config.vp_simplified_mode}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.goods_receiving_note.php")}
							<li><a href="vp.goods_receiving_note.php">Goods Receiving Note</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.disposal.php")}
							<li><a href="vp.disposal.php">Disposal</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.repacking.php")}
							<li><a href="vp.repacking.php">Repacking</a></li>
						{/if}
					{/if}
					<li><a href="#" class=submenu>Stock Take</a>
					<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.stock_take.php")}
					<li><a href="vp.stock_take.php">New / Edit</a></li>
					{/if}
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.stock_take_variance_report.php")}
					<li><a href="vp.stock_take_variance_report.php">Stock Take Variance Report</a></li>
					{/if}
					</ul>
					</li>
				</ul>
				<li><a href="#" class="submenu">SKU</a>
				<ul>
					{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.price_list.php")}
						<li><a href="vp.price_list.php">Selling Price List</a></li>
					{/if}
					{if !$config.vp_simplified_mode}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.mst_sku.change_selling_price.php")}
							<li><a href="vp.mst_sku.change_selling_price.php">Change Selling Price</a></li>
						{/if}
					{/if}
				</ul>
				<li><a href="#" class="submenu">Report</a>
				<ul>
					{if !$config.vp_simplified_mode}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.sales_report_by_day.php")}
							<li><a href="vp.sales_report_by_day.php">Sales Report by Day</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.sales_report_by_month_week.php")}
							<li><a href="vp.sales_report_by_month_week.php?type=w">Sales Report by Week</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.sales_report_by_month_week.php")}
							<li><a href="vp.sales_report_by_month_week.php">Sales Report by Month</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.sales_summary_by_day.php")}
							{*<li><a href="vp.sales_summary_by_day.php">Sales Summary by Day</a></li>*}
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.hourly_sales_by_day.php")}
							<li><a href="vp.hourly_sales_by_day.php">Hourly Sales by Day</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.consignee_daily_sales_report.php")}
							<li><a href="vp.consignee_daily_sales_report.php">Consignee Daily Sales Report</a></li>
						{/if}
					{else}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.sales_report_by_day_by_receipt.php")}
							<li><a href="vp.sales_report_by_day_by_receipt.php">Sales Report by Day by Receipt</a></li>
						{/if}
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/vp.daily_sku_stock_balance.php")}
							<li><a href="vp.daily_sku_stock_balance.php">Daily SKU Stock Balance Report</a></li>
						{/if}
					{/if}
				</ul>
				<li><a href="/login.php?vp_logout=1" onclick="return confirm('{$LANG.CONFIRM_LOGOUT}')">Logout</a>
			</ul>
		</div>
	</div>

{* Debtor Portal *}
{elseif $dp_session.id}
	<div class="stdframe nav-font" style="height:24px;margin:0;padding:0;">
		<div class="suckerdiv main-nav" style="min-width:780px;width:100%;">
			<ul id="suckertree">
				<li><a href="#">Home</a>
					<ul>
						<li><a href="home.php">Dashboard</a></li>
						<!--li><a href="javascript:void(goto_branch(0))">Go to branch...</a></li-->
					</ul>
				</li>
				<li><a href="#" class="submenu">Operations</a>
					<ul style="min-width:160px;">
						{if file_exists("`$smarty.server.DOCUMENT_ROOT`/dp.sales_order.php")}
							<li><a href="dp.sales_order.php">Sales Order</a></li>
						{/if}
					</ul>
				</li>
				<li><a href="/login.php?dp_logout=1" onclick="return confirm('{$LANG.CONFIRM_LOGOUT}')">Logout</a>
			</ul>
		</div>
	</div>
{/if}