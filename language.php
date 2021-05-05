<?php
/*
1/25/2016 1:49 PM Andy
- Change membership sms language.

2/22/2016 1:28 PM Qiu Ying
- Add "DO_CANNOT_CHECKOUT" and "DO_ALREADY_CHECKOUT" in DO

03/08/2016 10:20 Edwin
- Added "NEED_ARMS_GO_HQ_LICENSE" into common msg

04/01/2016 10:30 Edwin
- Added "GRN_APPROVAL_ALREADY_DONE" into GRN

06/30/2016 14:30 Edwin
- Added "LOGIN_ACCOUNT_LOCKED" into login.php
- Added "USERS_PROFILE_LOCKED" and USERS_PROFILE_UNLOCKED into user.php

10/14/2016 17:34 Qiu Ying
- Added "GRR_DELETED" in GRR

4/18/2017 2:25 PM Justin
- Added "SALES_TREND_AVG_INFO" for PO Quantity Performance.

4/19/2017 9:45 AM Khausalya
- Enhanced changes from RM to use config setting.

5/10/2017 11:03 AM Andy
- Added language "USE_HQ_GRN_INFO".

5/10/2017 16:56 Qiu Ying
- Added language "SKU_EXCEED_MAX_LENGTH"
- Added language "SKU_EXCEED_MAX_LENGTH_NON_ALPHABET"

5/29/2017 3:22 PM Justin
- Added language "DO_NO_ITEM_SELECTED"

6/1/2017 4:27 PM Justin
- Added languauge "SO_ORDER_ALREADY_EXPORT_TO_POS".

3/8/2018 10:42 AM HockLee
- Added language "PROMO_PROVIDE_ID".

3/26/2018 5:22 PM Justin
- Added language "SD_USE_GRN_INFO" and "SD_USE_HQ_GRN_INFO"

3/30/2018 4:13PM HockLee
- Added new language "MSTBRANCH_INTEGRATION_CODE_DUPLICATE".

4/5/2018 4:03 PM Justin
- Added new languages "FOREIGN_CURRENCY_INVALID_RATE" and "FOREIGN_CURRENCY_SAME_RATE".
- Added new languages "GRR_MULTIPLE_CURRENCY_RATE" and "GRR_DIFF_CURRENCY_WITH_PO".

5/16/2018 2:36 PM HockLee
- Added new language "SO_ORDER_CANNOT_CREATE_DO_BY_BATCH".

10/10/2018 5:31 PM Justin
- Added new language "GRR_OVERRIDE_PO_CANCEL_DATE".

10/26/2018 5:50 PM Justin
- Added new language "UPDATE_SKU_BRAND_VENDOR_NO_DATA".

11/28/2018 11:55 AM Justin
- Added new language "IMPORT_VENDOR_QC_NO_DATA", "IMPORT_VENDOR_QC_INVALID_VD" and "IMPORT_VENDOR_QC_NO_UPDATE".

12/7/2018 3:56 PM Justin
- Added new language "IMPORT_VENDOR_QC_INVALID_COST".

1/10/2019 2:27 PM Justin
- Added new language "DO_ZERO_STOCK_BAL_ITEM".

4/16/2019 5:17 PM Justin
- Added new languages "COUNTER_COLLECTION_EWALLET_CB_ERROR", "COUNTER_COLLECTION_EWALLET_CB_UNDO_DISABLED" and "COUNTER_COLLECTION_EWALLET_CB_NOT_ACTUAL_DATE". 

5/28/2019 4:27 PM William
- Added new language "SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ".
- Added new language "SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX".

6/14/2019 10:44 AM Andy
- Added new language for Cycle Count.

7/22/2019 3:25 PM Justin
- Added new language "EWALLET_API_PAYDIBS_PAYMENT_PROCESSED_FAILED".

8/8/2019 10:00 AM William
- Changed language file PROMO_LIMIT_ITEMS wrong word "maximus" to "maximum".

8/23/2019 3:42 PM Justin
- Added new language "DO_CHECKOUT_DRIVER_INFO_REQUIRED".

11/20/2019 5:12 PM Justin
- Added new languages "SA_KPI_SETUP_FIELD_EMPTY" and "SA_KPI_SETUP_EXISTED".

11/21/2019 9:56 AM William
- Added new language "MEMBERSHIP_EMAIL_IN_DATABASE".

1/24/2020 1:09 PM Justin
- Amended the language "SA_KPI_SETUP_EXISTED".

3/13/2020 10:52 AM Justin
- Improved the language "DO_SN_RESET_ERROR" to have more info.

4/15/2020 11:46 AM William
- Added new language "MONTH_DOCUMENT_IS_CLOSED".

6/9/2020 12:20 PM William
- Added new language "GRN_INACTIVE_SKU".

11/12/2020 12:10 PM Shane
- Added new language "ANNOUNCEMENT_TITLE_EMPTY","ANNOUNCEMENT_CONTENT_EMPTY","ANNOUNCEMENT_DELETED", "ANNOUNCEMENT_NOT_DELETED","ANNOUNCEMENT_CANCELLED", "ANNOUNCEMENT_NOT_CANCELLED","ANNOUNCEMENT_INVALID_DATE_FROM","ANNOUNCEMENT_INVALID_DATE_TO".

12/4/2020 1:23 PM Shane
- Added new language "ANNOUNCEMENT_INVALID_COPY", "ANNOUNCEMENT_COPY"

12/7/2020 5:34 PM Shane
- Modified "ANNOUNCEMENT_INVALID_COPY"
*/
/* ARMS lanugage file */
$LANG = array(
	// common msg
	"HQ_ONLY" => "This action is for HQ only.",
	"NEED_CONFIG" => "This module need turn on configuration to use.",
	"CANNOT_EDIT_OTHER_BRANCH_MODULE" => "You cannot edit other branch %s",
	"USER_LEVEL_NO_REACH" => "Your user level is not allow to perform this action",
	"USE_GRN_INFO" => "Using GRN items received from the selected vendor.\nConditions:\n- The last GRN vendor before the 'From' date is the selected vendor.\n or\n- There is GRN received from the selected vendor between the 'From' and 'To' date",
	"USE_HQ_GRN_INFO" => "Using GRN items received from HQ for the selected vendor.\nConditions:\n- HQ last GRN vendor before the 'From' date is the selected vendor.\n or\n- HQ got GRN received from the selected vendor between the 'From' and 'To' date",
	"SD_USE_GRN_INFO" => "Using GRN items received from the selected vendor.\nConditions:\n- The last GRN vendor before the 'Date' is the selected vendor.\n or\n- There is GRN received from the selected vendor during the 'Date'",
	"SD_USE_HQ_GRN_INFO" => "Using GRN items received from HQ for the selected vendor.\nConditions:\n- HQ last GRN vendor before the 'Date' is the selected vendor.\n or\n- HQ got GRN received from the selected vendor during the 'Date'",
	"DOC_ITEM_IS_BLOCKED" =>"This SKU is currently blocked in %s.",
	"ITEM_NOT_FOUND" => "Item Not Found",
	"ADD_ITEM_FAILED" => "Add item failed",
	"SMARTY_MODULE_NOT_FOUND" => "Smarty cannot be load.",
	"NO_ITEM_TO_PRINT" => "No item to print.",
	"NEED_ARMS_GO_HQ_LICENSE" => "ARMS-Go HQ License is required to access this module.",
	"DATE_CANNOT_OVER" => "%s Cannot Over %s",
	"DOCUMENT_SAVED_IN_OTHER_TAB" => "This document has been opened and saved in other tab. Please close this document first and re-open it if you wish to alter this document.",
	"SALES_TREND_AVG_INFO" => "The System will first compare 1M and 3M to obtain the Highest Value.\nOtherwise it will compare 6M and 12M instead for the Highest Value.",
	"INVALID_DATE_FORMAT" => "Invalid Date Format.",
	"INVALID_DATE_FORMAT2" => "Invalid [%s] Format.",
	"CURRENCY_RATE_ZERO" => "Currency Rate is zero, please check currency rate table with your admin.",
	"INVALID_BRANCH_ID" => "Invalid Branch ID [%s]",
	"INVALID_DATA" => "Invalid %s [%s]",
	"BASE_CURRENCY_CONVERT_NOTICE" => "<span class='converted_base_amt'>Base Currency Amount</span> converted from Foreign Currency is just an approximately calculation and may not be accurate.",
	"DATE_TO_FROM_ERROR" => "%s cannot ealier than %s.",
	"DATA_NOT_ALLOW_NEGATIVE" => "%s cannot be negative.",
	"MONTH_DOCUMENT_IS_CLOSED"=>"The month of this document already marked as closed, any modification on the stock will not be allowed, please request to your administrator if you would like to reopen the month for edit.",
	
	// popup msg
	"ACCESS_DENIED_NEED_LOGIN_OR_INTRANET" => "Access Denied. Please login or access from intranet.",
	"CONFIRM_EXIT" => "Exit now?",
	"CONFIRM_DISCARD_AND_CLOSE" => "Discard the changes and close?",
	"CONFIRM_SAVE_CHANGES" => "Save the changes and continue?",
	"TERMINAL_LOCKED_BY" => "This terminal is locked by someone else,\nyou cannot unlock it",
	"NO_CHANGES_MADE" => "No changes was made to the database",
	"THE_FOLLOWING_ERROR" => "<b>The following error has occured:</b>\n\n   - ",
	"PERMISSION_DENIED" => "Permission denied",
	"HQ_OFFLINE"=>" is currently unavailable, please try again later",
	
	// report erros
	"REPORT_NO_CATEGORY" => "Please key in category",
	"REPORT_NO_BRANCH_SELECTED" => "Invalid or no branch selected",
	"REPORT_NO_START_DATE_SELECTED" => "Invalid or no start date selected",
	"REPORT_NO_ITEMS_FOR_THIS_VENDOR" => "No item was found for this Vendor",
	"REPORT_CONFIG_NOT_FOUND" => "Required config for this report is missing / turned off.",
	
	// print errors
	"PRINT_ZERO_QTY" => "The %s has zero total quantity. Please key in at least 1 quantity.",
		
	// login.php
	"INVALID_LOGIN_TRY_AGAIN" => "Invalid login credentials. Please try again.",
	"INVALID_LOGIN_DISABLED" =>  "Invalid login credentials. Account locked.",
	"LOGIN_ACCOUNT_INACTIVE" => "This account is not actived.<br /> Please contact your MIS %sor System Admin to activate.",
    "LOGIN_ACCOUNT_LOCKED" => "This user's account has been block.",
	"NO_PRIVILEGE" => "You do not have the %s privilege for %s.",
	"YOU_HAVE_LOGGED_OUT" => "You have been logged out.",
	"CONFIRM_LOGOUT" => "Logout now?",
	"AUTO_LOGIN_FAILED" => "Autologin failed, please login manually",
	"REPORT_IS_HQ_ONLY" => "This report is for HQ only",
	"BRANCH_EXCLUDED" => "%s is being excluded to view for %s",
    "LOGIN_TNC_REQUIRED" => "You must agree to the Terms & Conditions in order to login.",

	// users.php
	"USERS_INVALID_NEW_USERNAME_EMPTY" => "New username column is empty.",
	"USERS_INVALID_NEW_USERNAME_USED" => "The username %s is already used.",
	"USERS_INVALID_NEW_USERNAME_PATTERN" => "Username %s is invalid.",
	"USERS_INVALID_LOGIN_ID" => "Login ID %s is already used.",
	"USERS_INVALID_NEW_PASSWORD_EMPTY" => "New password column is empty.",
	"USERS_INVALID_NEW_PASSWORD_PATTERN" => "Password format is invalid.",
	"USERS_INVALID_NEW_EMAIL_EMPTY" => "New email column is empty.",
	"USERS_INVALID_NEW_EMAIL_PATTERN" => "Email format is invalid.",
	"USERS_VALID_NEW_USER_ADDED" => "New user %s added.",
	"USERS_INVALID_CANT_ADD_MAX_USER_ALLOWED" => "Can't add new user because system have reached the maximum allowed of %d active users.",
	"USERS_PROFILE_UPDATED" => "Profile Updated.",
	"USERS_PROFILE_ACTIVATED" => "User account activated.",
	"USERS_PROFILE_DEACTIVATED" => "User account deactivated.",
    "USERS_PROFILE_LOCKED" => "User account locked.",
    "USERS_PROFILE_UNLOCKED" => "User account unlocked.",
	"USERS_PASSWORD_RESET" => "Password reset and account activated.",
	"USERS_IC_ALREADY_USED" => " The IC No: %s already used by other user (%s).",
	"USERS_INVALID_IC_EMPTY" => " The IC No. column is empty.",
	"USERS_NOT_FOUND" => "User Not Found.",
	"USERS_PASSWORD_DIFF" => "Password does not match with confirmation password",
	
	// membership.php
	"MEMBERSHIP_NRIC_IN_DATABASE" => "The NRIC is already in the database",
	"MEMBERSHIP_NRIC_NOT_IN_DATABASE" => "The NRIC is not in database",
	"MEMBERSHIP_CARD_NOT_IN_DATABASE" => "The $config[membership_cardname] you entered is not in database.",
	"MEMBERSHIP_CARD_IN_DATABASE" => "The $config[membership_cardname] Number you entered is already issued.\\nPlease reconfirm the card number.",
	"MEMBERSHIP_CARD_OR_NRIC_NOT_IN_DATABASE" => "The $config[membership_cardname] or NRIC you entered is not in database.",
	"MEMBERSHIP_NRIC_EMPTY" => "NRIC column is empty",
	"MEMBERSHIP_USE_APPLICATION_IF_NEW" => "Use the Application function if this is a new membership",
	"MEMBERSHIP_ABID_EMPTY" => "Applying Branch column is empty",
	"MEMBERSHIP_NAME_EMPTY" => "Full Name column is empty",
	"MEMBERSHIP_MTYPE_EMPTY" => "Member Type column is empty",
	"MEMBERSHIP_CARD_NO_EMPTY" => "$config[membership_cardname] number column is empty",
	"MEMBERSHIP_CARD_NO_EMPTY2" => "Member Card No is empty",
	"MEMBERSHIP_DEISGNATION_EMPTY" => "Designation is not selected",
	"MEMBERSHIP_GENDER_EMPTY" => "Gender is not selected",
	"MEMBERSHIP_DOB_EMPTY" => "The date of birth is incomplete",
	"MEMBERSHIP_DOB_INVALID" => "Invalid date of birth %s",
	"MEMBERSHIP_AGE_INVALID" => "Application must be older than or equal to %s years old",
	"MEMBERSHIP_MARITAL_STATUS_EMPTY" => "Marital status is not selected",
	"MEMBERSHIP_NATIONAL_EMPTY" => "National is not selected",
	"MEMBERSHIP_RACE_EMPTY" => "Race is not selected",
	"MEMBERSHIP_EDUCATION_LEVEL_EMPTY" => "Level of Education is not selected",
	"MEMBERSHIP_ADDRESS_EMPTY" => "Address is not selected",
	"MEMBERSHIP_POSTCODE_EMPTY" => "Post Code column is empty",
	"MEMBERSHIP_CITY_EMPTY" => "City column is empty",
	"MEMBERSHIP_STATE_EMPTY" => "State is not selected",
	"MEMBERSHIP_EMAIL_EMPTY" => "Email column is empty",
	"MEMBERSHIP_EMAIL_PATTERN_INVALID" => "Email is invalid",
	"MEMBERSHIP_EMAIL_IN_DATABASE" => "The email is already in the database",
	"MEMBERSHIP_OCCUPATION_EMPTY" => "Occupation is not selected",
	"MEMBERSHIP_INCOME_EMPTY" => "Income is not selected",
	"MEMBERSHIP_PHONE_HOME_EMPTY" => "Phone (Home) is empty",
	"MEMBERSHIP_PHONE_OFFICE_EMPTY" => "Phone (Office) is empty",
	"MEMBERSHIP_PHONE_MOBILE_EMPTY" => "Phone (Mobile) is empty",

	"MEMBERSHIP_INVALID_INPUT" => "There are invalid inputs, please check the error messages",
	"MEMBERSHIP_INVALID_CARD_NO" => "Invalid $config[membership_cardname] Card Number",
	"MEMBERSHIP_ATYPE_EMPTY" => "Application Type not selected",
	"MEMBERSHIP_CTYPE_EMPTY" => "Card Type not selected",

	"MEMBERSHIP_DATA_UPDATED" => "Membership data updated",
	"MEMBERSHIP_DATA_UPDATED_AND_APPROVED" => "Membership data approved",
	"MEMBERSHIP_ALL_INVENTORY_MUST_BE_ENTERED" => "Your inputs are Invalid",
	"MEMBERSHIP_COLLECT_CARD_AT_PAYMENT" => "Please collect card and pay at Payment counter",
	"MEMBERSHIP_NEW_APPLICATION_NOT_ALLOWED" => "The $config[membership_cardname] number is already issued, please try another card and report this to the $config[membership_cardname] person in charge",
	"MEMBERSHIP_ALREADY_HAVE_CARD" => "This member already have $config[membership_cardname], New application not allowed",
	"MEMBERSHIP_APPLY_NRIC_FOUND_PLEASE_GOTO_ISSUE_COUNTER" => "The NRIC exist in membership database. Please goto card issue counter.",
	"MEMBERSHIP_APPLY_NRIC_FOUND_ASK_CONTINUE_ISSUE" => "The NRIC exist in membership database.\n\nNRIC/Passport No: <b>%s</b>\nCard No:<b>%s</b>\n\nNext Expiry Date:<b>%s</b>\n\nContinue with renewal?",
	"PAYMENT_PAID_LESS_THAN_AMOUNT" => "Payment is less than charged amount",
	"MEMBERSHIP_NEW_CARD_NO" => "New $config[membership_cardname] No.",
	"MEMBERSHIP_CURRENT_CARD_NO" => "Current $config[membership_cardname] No.",
	"MEMBERSHIP_CARD_INFO" => "Current $config[membership_cardname] No.: %s\n\nIssue Date: %s\n\nExpiry Date: <b>%s</b>\n\nIssue Branch: %s",
	"MEMBERSHIP_SCAN_NRIC_NOT_FOUND" => "Cannot find IC Scan image at\n<span color=\"blue\">%s</span>",
	"MEMBERSHIP_SCAN_NRIC_FOUND" => "IC Scan image found at\n<span color=\"blue\">%s</span>",
	"MEMBERSHIP_BLOCKED" => "Membership with NRIC %s is now blocked",
	"MEMBERSHIP_UNBLOCKED" => "Membership with NRIC %s is now unblocked",
	"MEMBERSHIP_UPDATE" => "Update is not allow.",
	"MEMBERSHIP_SMS_INFO" => "*Alphabet characters (English/Malay)\n1 credit = 153 characters\n*When exceed 153 characters, additional credit(s) will be charge.\ne.g.: 154 characters = 2 credits\n\n*Chinese characters\n1 credit = 63 characters\n*When exceed 63 characters, additional credit(s) will be charge.\n\nWhen more than 1 credit, there will be 7 characters reserved for 'RM0.00' message.",
	"MEMBERSHIP_CB_ERROR" => "This card no %s contains points, cannot do cancel bill.",
	"MEMBERSHIP_DATE_INVALID" => "%s date [%s] is not a valid date or too old.",
	"MEMBERSHIP_DATE_RANGE_INVALID" => "Renewal date range between %s and %s is invalid.",
	"MEMBERSHIP_MEMBER_POINTS_CHANGED" => "Member [%s] has been marked as points recalculate.\n\nPlease check again in next 10 minutes.",
	"MEMBERSHIP_INVALID_CARD_UPGRADE" => "New Card No %s cannot be upgraded.",
	"MEMBERSHIP_PRINCIPAL_CARD_CONFIRM" => "%sAre you sure want to use this as Principal?",
	"MEMBERSHIP_PRINCIPAL_SUB_USED" => "Relation link failed.\nMember [%s] was being used by others as %s card",
	"MEMBERSHIP_INVALID_PRINCIPAL_UNLINK" => "Unable to unlink, found that card no [%s] contains negative points after unlink.",
	"MEMBERSHIP_MOBILE_PASS_MIN_CHAR" => "Password required minimum 6 characters.",
	"MEMBERSHIP_MOBILE_PASS_ALPHANUMERIC" => "Password must be alphabetical.",
	"MEMBERSHIP_NO_CARD_FOUND" => "No Card_no found for this member.",
	"MEMBERSHIP_GUID_EMPTY" => "Membership GUID invalid.",
	
	// Membership Package
	"MEMBERSHIP_PACKAGE_INVALID_UNIQUE_ID" => "Invalid Membership Package Unique ID",
	"MEMBERSHIP_PACKAGE_NOT_FOUND" => "Membership Package UNIQUE ID [%s] Not Found",
	"MEMBERSHIP_PACKAGE_NOT_ALLOW_EDIT" => "Membership Package Not Allow to Edit",
	"MEMBERSHIP_PACKAGE_NOT_ALLOW_CANCEL" => "Membership Package Unable to Cancel",
	"MEMBERSHIP_PACKAGE_NOT_ALLOW_OTHER_BRANCH" => "This Package is belongs to other branch.",
	"MEMBERSHIP_PACKAGE_INVALID_DATA" => "Package Found the invalid data [%s].",
	"MEMBERSHIP_PACKAGE_LINKED_SKU_USED" => "Package Linked SKU already used by other Package.",
	"MEMBERSHIP_PACKAGE_INVALID_PURCHASE_QTY" => "Package Purchase Quantity must be at least 1.",
	"MEMBERSHIP_PACKAGE_INVALID_PURCHASE_POS" => "Package Purchase POS Data is invalid.",
	"MEMBERSHIP_PACKAGE_INVALID_PURCHASE_GUID" => "Package Purchase GUID is invalid.",
	"MEMBERSHIP_PACKAGE_INVALID_REDEEM_ITEMS_GUID" => "Package Item GUID is invalid.",
	"MEMBERSHIP_PACKAGE_REDEEM_RATE_NOTHING" => "Redeem Item Nothing to Rate.",
	"MEMBERSHIP_PACKAGE_REDEEM_SA_INVALID" => "Redeem Item Sales Agent ID [%s] is invalid.",
	
	// Membership Coupon
	"MEMBERSHIP_REFER_YOURSELF" => "You cannot refer to yourself.",
	"MEMBERSHIP_REFERRAL_CODE_NOT_FOUND" => "The Referral Code is invalid.",
	"MEMBERSHIP_REFERRAL_CODE_USED" => "Already entered Referral Code.",
	
	// branch masterfile
	"MSTBRANCH_DATA_UPDATED" => "Branch Master File updated",
	"MSTBRANCH_NEW_RECORD_ADDED" => "New record added to Branch Master File",
	"MSTBRANCH_CODE_EMPTY" => "Code is empty",
	"MSTBRANCH_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTBRANCH_ADDRESS_EMPTY" => "Address is empty",
	"MSTBRANCH_DESCRIPTION_EMPTY" => "Company Name is empty",
	"MSTBRANCH_COMPANY_NO_EMPTY" => "Registration No is empty",
	"MSTBRANCH_EMAIL_EMPTY" => "Contact Email is empty",
	"MSTBRANCH_CONTACT_PERSON_EMPTY" => "Contact Person is empty",
	"MSTBRANCH_PHONE_1_EMPTY" => "Phone #1 is empty",
	"MSTBRANCH_INVALID_EMAIL" => "Contact Email is invalid",
 	"MSTBRANCH_DEPT_NO_EMPTY" => "Department is empty",
 	"MSTBRANCH_TERMS_NO_EMPTY" => "Terms is empty",
	"MSTBRANCH_OVER_TRANS_END_DATE"=>"The branch [%s] %s has reached the maximum transaction end date",
	"MSTBRANCH_GST_INVALID"=>"The GST Registration Number or Start Date is empty.",
 	"BRANCH_LOGO_EMPTY" => "Branch logo is empty",
 	"BRANCH_LOGO_INV_FORMAT" => "Branch logo is invalid format (JPG/JPEG only)",
 	"MSTBRANCH_LOGO_SIZE_EXCEEDED" => "Branch Image File Size is limited to a maximum of 5MB only.",
 	"MSTBRANCH_INTEGRATION_CODE_DUPLICATE" => "Integration Code %s already in-used.",

	// line masterfile
	"MSTLINE_DATA_UPDATED" => "Line Master File updated",
	"MSTLINE_NEW_RECORD_ADDED" => "New record added to Line Master File",
	"MSTLINE_CODE_EMPTY" => "Code is empty",
	"MSTLINE_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTLINE_AREA_EMPTY" => "Area is empty",
	"MSTLINE_DESCRIPTION_EMPTY" => "Description is empty",

	// department masterfile
	"MSTDEPT_DATA_UPDATED" => "Department Master File updated",
	"MSTDEPT_NEW_RECORD_ADDED" => "New record added to Department Master File",
	"MSTDEPT_CODE_EMPTY" => "Code is empty",
	"MSTDEPT_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTDEPT_AREA_EMPTY" => "Area is empty",
	"MSTDEPT_DESCRIPTION_EMPTY" => "Description is empty",

	// Category masterfile
	"MSTCAT_DATA_UPDATED" => "Category Master File updated",
	"MSTCAT_NEW_RECORD_ADDED" => "New record added to Category Master File",
	"MSTCAT_CODE_EMPTY" => "Code is empty",
	"MSTCAT_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTCAT_AREA_EMPTY" => "Area is empty",
	"MSTCAT_DESCRIPTION_EMPTY" => "Description is empty",
	"MSTCAT_LINE_CANNOT_USE_INHERIT" => "Line cannot use 'Inherit'",
	"GST_INPUT_TAX_EMPTY" => "Input Tax is empty",
	"GST_OUTPUT_TAX_EMPTY" => "Output Tax is empty",
	"GST_INCLUSIVE_TAX_EMPTY" => "Inclusive Tax is empty",
	
	// UOM masterfile
	"MSTUOM_DATA_UPDATED" => "UOM Master File updated",
	"MSTUOM_NEW_RECORD_ADDED" => "New record added to UOM Master File",
	"MSTUOM_CODE_EMPTY" => "Code is empty",
	"MSTUOM_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTUOM_FRACTION_EMPTY" => "Fraction is empty",
	"MSTUOM_DESCRIPTION_EMPTY" => "Description is empty",

	// Vendor masterfile
	"MSTVENDOR_DATA_UPDATED" => "Vendor Master File updated",
	"MSTVENDOR_NEW_RECORD_ADDED" => "New record added to Vendor Master File",
	"MSTVENDOR_CODE_EMPTY" => "Code is empty",
	"MSTVENDOR_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTVENDOR_INTERNAL_CODE_DUPLICATE" => "Internal Code %s already in-used",
	"MSTVENDOR_DESCRIPTION_EMPTY" => "Description is empty",
	"MSTVENDOR_COMPANY_NO_EMPTY" => "Company No is empty",
	"MSTVENDOR_TERM_EMPTY" => "Term is empty",
	"MSTVENDOR_CREDIT_LIMIT_EMPTY" => "Credit Limit is empty",
	"MSTVENDOR_BANK_ACCOUNT_EMPTY" => "Bank Account is empty",
	"MSTVENDOR_ADDRESS_EMPTY" => "Address is empty",
	"MSTVENDOR_CONTACT_PERSON_EMPTY" => "Contact Person is empty",
	"MSTVENDOR_EMAIL_EMPTY" => "Contact Email is empty",
	"MSTVENDOR_PHONE_1_EMPTY" => "Phone #1 is empty",
	"MSTVENDOR_INVALID_EMAIL" => "Contact Email is invalid",
	"MSTVENDOR_INVALID_VENDOR" => "Vendor ID is invalid",
	"MSTVENDOR_INVALID_BRANCH" => "Branch ID is invalid",
	"MSTVENDOR_TRADE_DISCOUNT_UPDATED" => "Trade discount table updated",
	"MSTVENDOR_IS_BLOCK" => "Current vendor is blocked",
	"MSTVENDOR_INVALID_GST_INFO" => "GST Type, Registration Number & Start Date cannot left empty.",
	
	// Brand masterfile
	"MSTBRAND_DATA_UPDATED" => "Brand Master File updated",
	"MSTBRAND_NEW_RECORD_ADDED" => "New record added to Brand Master File",
	"MSTBRAND_CODE_EMPTY" => "Code is empty",
	"MSTBRAND_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTBRAND_DESCRIPTION_EMPTY" => "Description is empty",
	"MSTBRAND_DESCRIPTION_DUPLICATE" => "Brand Description %s is already in-used",

	// Brand Group masterfile
	"MSTBRGROUP_DATA_UPDATED" => "Brand Group Master File updated",
	"MSTBRGROUP_NEW_RECORD_ADDED" => "New record added to Brand Group Master File",
	"MSTBRGROUP_CODE_EMPTY" => "Code is empty",
	"MSTBRGROUP_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTBRGROUP_DESCRIPTION_EMPTY" => "Description is empty",
	
	// SKU Group masterfile
    "MSTSKUGROUP_DATA_UPDATED" => "SKU Group Master File updated",
	"MSTSKUGROUP_NEW_RECORD_ADDED" => "New record added to SKU Group Master File",
	"MSTSKUGROUP_CODE_EMPTY" => "Code is empty",
	"MSTSKUGROUP_CODE_DUPLICATE" => "Code %s already in-used",
	"MSTSKUGROUP_DESCRIPTION_EMPTY" => "Description is empty",
	"MSTSKUGROUP_ITEM_EMPTY" => "No item found",


	
	// SKU Application
	"SKU_INVALID_CATEGORY" => "Category is invalid",
	"SKU_INVALID_VENDOR" => "Vendor is invalid",
	"SKU_INVALID_BRAND" => "Brand is invalid",
	"SKU_INVALID_TYPE" => "Sku type is invalid",
	"SKU_SELECT_BRAND_FOR_SOFTLINE" => "Please Select Brand for SOFTLINE category.",
	"SKU_SELECT_CATEGORY_AND_VENDOR" => "Please Select Category and Vendor.",
	"SKU_INVALID_PHOTO" => "Invalid Photo format %s (not JPG or PDF)",
	"SKU_INVALID_PHOTO_SIZE" => "Invalid Photo size %s",
	"SKU_MIN_PHOTO_REQUIRED" => "You must upload at least %d photos",
	"SKU_INVALID_ART_MCODE" => "Article No./MCode is invalid",
	"SKU_INVALID_DESCRIPTION" => "Description cannot left empty",
	"SKU_INVALID_RECEIPT_DESCRIPTION" => "Receipt Description cannot left empty",
	"SKU_INVALID_SELLING_PRICE" => "Selling Price is invalid",
	"SKU_INVALID_COST_PRICE" => "Cost Price is invalid",
	"SKU_SELLING_BELOW_COST" => "Selling Price is below Cost",
 	"SKU_INVALID_TRADE_DISCOUNT_TABLE" => "Trade Discount Table is invalid or incomplete",
 	"SKU_TRADE_DISCOUNT_VALUE_IS_ZERO" => "Trade Discount Table value cannot be zero.",
 	"SKU_UNBRAND_USE_BRAND_DISCOUNT_TABLE" => "Cannot use Brand discount table for UN-BRANDED SKU",
 	"SKU_INVALID_DEFAULT_TRADE_DISCOUNT_TABLE" => "No default Trade Discount Table type selected",
	"SKU_INVALID_LISTING_FEE" => "Invalid New SKU Listing Fee",
	"SKU_INVALID_LISTING_FEE_PACKAGE_COUNT" => "Invalid No. of SKU for Package Listing Fee",
	"SKU_NO_ITEM" => "No Items added",
	"SKU_MATRIX_INCOMPLETE" => "Product Matrix error at %s. Make sure all row and column headers are filled properly",
	"SKU_MATRIX_EMPTY" => "Product Matrix size must contain at least 2 columns or 2 rows",
	"SKU_MATRIX_INVALID_PRICE" => "Product Matrix Selling Price column is invalid",
	"SKU_MATRIX_INVALID_COST" => "Product Matrix Cost Price column is invalid",
	"SKU_MATRIX_SELLING_BELOW_COST" => "Product Matrix Selling Price is below Cost",
	"SKU_ARTNO_REPEATED" => "The artcile code %s is invalid or has been used by the same vendor in existing SKU",
	"SKU_MCODE_REPEATED" => "The MCode %s is invalid or has been used by in existing SKU",
	"SKU_CONTINUE_TO_NEXT_IN_PACKAGE" => "You have added an SKU with package listing fee.\\nClick OK to continue with next SKU item (%d of %d).",
	"SKU_CONTINUE_FROM_PREVIOUS_PACKAGE" => "You have added an SKU with package listing fee.\\nClick OK to continue with next SKU item (%d of %d).",
	"SKU_INVALID_DISCOUNT_TYPE_CONSIGN" => "Discount table is required for Consignment SKU",
	"SKU_USER_PROFILE_NO_DEPARTMENT" => "You do not have department selected under user-profile.<br />Please contact MIS.",
	"SKU_PACKAGE_VARIETY_COUNT_LESS" => "Variety does not match SKU Package variety count of %d",
	"SKU_INVALID_LINK_CODE" => "Please enter valid $config[link_code_name] for %s",
	"SKU_EMPTY_LINK_CODE" => "Please fill in all $config[link_code_name]",
	"SKU_ARTNO_USED" => "The Article No '%s' is invalid or already used %s",
	"SKU_MCODE_USED" => "The Manufacturer's Code '%s' is invalid or already used %s",
	"SKU_MCODE_INVALID_FORMAT_12_DIGITS" => "The Manufacturer's Code %s is invalid (12 digits mcode must start with '0' or '7')",
	"SKU_MCODE_INVALID_FORMAT" => "The Manufacturer's Code %s is invalid (must be 5, 6, 8, 12 or 13 digits, or other specific format your system is configured to)",
	"SKU_NO_ACCESS" => "You do not have access to SKU Application ID#%d",
	"SKU_NO_FLOW" => "The SKU Application ID#%d does not have approval flow.",
	"SKU_NOT_APPROVAL" => "You cannot approve / reject the SKU Application ID#%d",
	"SKU_NO_APPROVAL_FLOW" => "No Approval Flow created for this Department,<br />Please contact MIS now before you submit again",
	"SKU_REVISE_NOT_ALLOWED" => "The SKU Application ID#%s could not be revised at the time being.",
	"SKU_APPLICATION_NOT_EXIST" => "The SKU Application ID#%s does not exist.",
	"SKU_LINK_CODE_USED" => "The $config[link_code_name] %s is already used by another SKU item.",
	"SKU_LAST_APPROVAL_ENTER_RECEIPT_DESCRIPTION" => "You are the last approval, please enter the receipt description for each item",
	"SKU_BOM_EXIST" => "This BOM already exist. <a href=\"/bom.php?a=load_bom_details&id=%d\">Click Here</a> to open the BOM Master File.",	
	"SKU_ITEM_ALREADY_IN_BOM" => "Item already in this BOM.",
	"SKU_BOM_NO_ITEMS" => "This BOM/Hamper does not have item(s)",
	"SKU_BOM_ITEMS_INVALID_QTY" => "Qty of %s is invalid",
	"SKU_INVALID_MISC_COST" => "Misc Cost is invalid",
	"SKU_FIRST_ITEM_UOM_EACH" => "First Item must use UOM 'EACH'",
	"SKU_MCODE_REPEATED_WARNING" => "The Manufacturer's Code %s has been used in this SKU.",
	"SKU_PO_REORDER_QTY_MAX_LESS_THAN_MIN" => "PO reorder qty max cannot less than min %s",
	"SKU_PO_REORDER_QTY_MAX_LESS_THAN_MOQ" => "PO reorder qty max cannot less than MOQ %s",
	"SKU_PO_REORDER_QTY_MUST_EXIST_MIN_AND_MAX" => "PO reorder qty max and min must fill if exist MOQ",
	"SKU_CI_ARTNO_USED" => "The Article No had been used.",
	"SKU_INVALID_CATEGORY_DISCOUNT" => "Category Discount must be more than zero.",
	"SKU_INVALID_CATEGORY_POINT" => "Category Reward Point must be more than zero.",
	"SKU_EMPTY_REJECT_REASON" => "Reject reason is empty.",
	"SKU_CANT_REVISE_OTHER_BRANCH" => "Cant revise SKU apply by other branch.",
	"SKU_PO_REORDER_NOTIFY_PERSON_ERROR" => "PO reorder notify person set without min & max qty %s",
	"SKU_PDF_CONVERT_FAILED" => "Unable to convert PDF file %s into JPG",
	"SKU_GP_PER_LEGEND" => "GP% = Gross Profit / Selling Price",
    "SKU_MCODE_DUPLICATE"=>"Mcode [%s] is duplicated",
	
	// sku master
	"SKU_NOT_EXIST" => "The SKU does not exist",
	"SKU_NO_ITEMS" => "The SKU does not have item",
	"SKU_UPDATED" => "SKU database updated",
	"SKU_SEARCH_NOT_FOUND" => "No match found.",
	"SKU_COLOR_SIZE_DUPLICATE" => "Found 2 or more Sku Items got same %s colour and %s size",
	"SKU_IS_NON_RETURNABLE" => "The SKU is non-returnable",
	"SKU_ALLOW_DECIMAL_NOTIFICATION" => "In order for Counter to work, you need to turn on check decimal quantity in the POS setting.\n\nPlease go to:\n\nPOS Settings > Scan and Print > Check Decimal Quantity\nChange the setting to YES.",
	"SKU_EXCEED_MAX_LENGTH" => "Maximum character for alphabetical character is %s . Please shorten your receipt description.",
	"SKU_EXCEED_MAX_LENGTH_NON_ALPHABET" => "Maximum character for non alphabetical character is %s . Please shorten your receipt description.",

	// sku price change
	"SKU_PRICE_SAME_NO_CHANGE" => "%s - New price same as the current price. No changes was made.",
	"SKU_PRICE_UPDATED" => "%s - Price updated as %s.",
	"SKU_QPRICE_UPDATED" => "%s - Mutli-Qty Price updated.",
	"SKU_BLOCKED_BOM_PACKAGE" => "EDIT PRICE IS NOT ALLOWED FOR SKU BOM TYPE \'PACKAGE\'",
	
	// SKU Tag
	"SKU_TAG_ID_INVALID" => "Invalid SKU Tag ID.",
	"SKU_TAG_ID_DATA_NOT_FOUND" => "SKU Tag ID [%s] Not Found.",
	"SKU_TAG_NO_DATA_TO_SAVE" => "No SKU Tag Info.",
	"SKU_TAG_NO_CODE" => "SKU Tag Code is Required.",
	"SKU_TAG_NO_DESC" => "SKU Tag Description is Required.",
	"SKU_TAG_CODE_DUPLICATE" => "SKU Tag Code already used.",
	"SKU_TAG_NO_ITEM_TO_ADD" => "SKU Tag Failed to Add Item due to no sku was found.",
	"SKU_TAG_ITEM_NOT_FOUND" => "SKU [%s] not found.",
	"SKU_TAG_ITEM_ALRDY_EXISTS" => "SKU Item ID [%s] already in the list.",
	"SKU_TAG_ITEM_NOTHING_TO_UPDATE" => "SKU Tag Item Nothing to update.",
	
	// purchase orders
 	"PO_NOT_FOUND" => "Purchase Order ID#%d does not exist",
 	"PO_NO_ACCESS" => "You do not have access to Purchase Order ID#%d",
 	"PO_INVALID_VENDOR" => "Vendor is invalid",
 	"PO_INVALID_DEPARTMENT" => "Department is invalid",
 	"PO_INVALID_DATE" => "Invalid Date (%s)",
 	"PO_INVALID_PO_OPTION" => "You must select a PO Option",
 	"PO_INVALID_PO_DATE" => "Invalid PO Date",
 	"PO_INVALID_DELIVER_TO" => "Delivery Branches not selected",
  	"PO_FOC_TABLE_UPDATED" => "FOC cost sharing updated",
  	"PO_NO_APPROVAL_FLOW" => "No Approval Flow created for this Department,<br />Please contact MIS now before you submit again",
  	"PO_REQUEST_NO_APPROVAL_FLOW" => "No PO Request Flow created for this Department,<br />Please contact MIS now before you submit again",
	"SKU_SEARCH_ALL_WRONG_PASSWORD" => "Invalid Password, search denied",
	"PO_MAX_ITEM_CANT_SAVE" => "Maximum Items per PO is %d, you have %d. Delete some item and Save again.",
	"PO_ITEM_ALREADY_IN_PO" => "Item already in PO",
	"PO_MAX_ITEM_CANT_ADD" => "Can't add item, maximum Items per this PO is %d.",
	"PO_NOT_APPROVAL" => "You cannot approve / reject the PO ID#%d",
	"PO_APPROVAL_COMPLETED" => "Well done! You have finished all the PO Approval.",
	"PO_EMPTY" => "The PO does not contain any item",
	"PO_PRINT_ZERO_QTY" => "The PO have zero total quantity. Printing aborted.",
	"PO_INACTIVE" => "The PO ID#%d is inactive",
	"PO_CREATED" => "The PO ID#%d is created",
	"PO_ALREADY_CONFIRM_OR_APPROVED" => "The PO ID#%d is already confirmed or approved",
 	"PO_CHOWN_SUCCESS" => "PO Owner changed to %s",
 	"PO_CHOWN_FAILED" => "Cannot change owner to %s. Make sure the user have PO permission in this branch",
 	"PO_ITEM_IS_BLOCKED" =>"This SKU is currently not allowed for purchase.",
 	"PO_CONSIGN_ITEM_IS_BLOCKED" =>"Consignment SKU is currently not allowed for purchase.",
 	"PO_CANNOT_CANCEL_DELIVERED_PO" => "Not allow to cancel Delivered PO",
 	"PO_DUPLICATE_FOC" => "Not allow to add FOC items for the same FOC",
 	"PO_CONFIRM_TOTAL_QTY_IS_ZERO" => "PO not allow zero quantity",
 	"PO_NO_ITEM_SELECTED" => "No item selected",
    "PO_ALREADY_CANCELED_OR_DELETED" => "The PO ID#%d is already cancelled or deleted",
    "PO_NOT_ALLOW_OPEN_BUY_ITEM_WITH_PURCHASE_AGREEMENT" => "PO not allow to add item which got purchase agreement",
	"PO_NO_ITEM" => "PO must have at least one valid SKU item",
	"PO_CHECKOUT_GRA_REQUIRED" => "Please checkout all GRA before confirm this PO",
	"PO_ITEM_IS_INACTIVE" => "This item was inactive",
	"DO_PO_BRANCH_DIFFERENT" => "Invalid Delivery Branch between PO and DO [%s]",
    "PO_CURRENCY_CODE_NOTIFY" => "- Currency Code cannot be changed after saved.
- The document will not have GST if using foreign currency.",
	"PO_NO_CURRENCY_RATE_HISTORY" => "No Currency Rate History",
	
	// GRR module
	"GRR_SAVED" => "GRR saved as GRR%05d",
	"GRR_NOT_FOUND" => "GRR %s is invalid",
	"GRR_PO_NOT_FOUND" => "The PO %s is invalid or not approved",
	"GRR_PO_INACTIVE" => "The PO %s is inactive (Rejected or Cancelled)",
	"GRR_DOC_NO_DUPLICATE" => "The %s (%s) is already in GRR%05d",
	"GRR_VENDOR_DIFFERENT_FROM_PO" => "Vendor different from PO",
	"GRR_INVALID_GRR_NO" => "GRR%05d does not exist",
	"GRR_CANNOT_EDIT" => "GRR%05d cannot be edited",
	"GRR_PO_DELIVERED" => "PO already delivered (does not allow partial delivery)",
	"GRR_INVALID_RECEIVING_BRANCH" => "Invalid receiving branch for this PO",
	"GRR_INVALID_RECEIVE_DATE" => "Invalid receive date %s",
	"GRR_PO_FROM_DIFFERENT_DEPARTMENT" => "PO is from different department",
	"GRR_PO_CANNOT_RECEIVE_UPON_CANCEL_DATE" => "PO Cancellation Date is %s. Goods Receiving not allow for this PO.",
	"GRR_DATE_OVER_LIMIT" => "Receive Date is over limit or less than required date",
	"GRR_DATE_RESET_LIMIT" => "Receive Date is over reset date limit",
	"GRR_IBT_ERROR" => "GRR contains non-IBT and IBT from DO/PO",
	"GRR_INVALID_DOCUMENT" => "GRR must at least one document of Invoice, DO or Others.",
	"GRR_DOC_NO_EMPTY" => "Document No cannot be empty.",
	"GRR_ITEM_INCOMPLETE" => "%s cannot be empty.",
	"GRR_PO_REQUIRED" => "Required to have at least one PO for this Vendor.",
	"GRR_INACTIVE" => "GRR%05d is unable to delete due to it is been deleted.",
	"GRR_INVALID_RESET" => "GRR%05d is unable to reset due to it is been reset before.",
	"GRR_INVALID_DOC_DATE" => "The %s (%s) is having different dates",
	"GRR_EMPTY_DOC_DATE" => "The %s (%s) is having empty document date",
	"GRR_AMOUNT_VARIANCE" => "GRR %s amount contains variance, %s amount must equal to %s",
	"GRR_INVALID_VENDOR" => "Please select a Vendor",
	"GRR_INVALID_GST_AMT" => "Please enter GST amount for GST Code %s (%s&#37;)",
	"GRR_DELETED" => "Sorry, this GRR%05d has been deleted, you cannot use it to create GRN",
	"GRR_REQUIRES_INVOICE" => "Please key in at least one Invoice for this GRR",
	"GRR_CURRENCY_RATE_NOT_SET" => "Exchange rate for Currency Code [%s] is not setup properly, please configure it",
	"GRR_MULTIPLE_CURRENCY_RATE" => "GRR could not have more than one PO with multiple currency rates.",
	"GRR_DIFF_CURRENCY_WITH_PO" => "PO must use [%s] currency code in order to add into this GRR.",
	"GRR_TAX_PERCENT_INFO" => "- Require Masterfile Vendor Tax Register = Yes.\n- Cannot use this together with GST.\n- All GRN Items will multiply the Tax Percent to increase the cost.",
	"GRR_MORE_THAN_ONE_INV" => "Could not have more than one Invoice at GRR",
	"GRR_OVERRIDE_PO_CANCEL_DATE" => "PO Cancellation Date is %s.\nPlease provide username and password to add this PO.",
	
	// GRN module
	"GRN_NOT_FOUND" => "GRN ID#%d does not exist.",
	"GRN_QTY_ZERO" => "GRN Total QTY is zero",
	"GRN_NO_ITEM" => "GRN does not have valid SKU item",
	"GRN_EDIT_NOT_ALLOWED" => "The GRN is already Confirmed or Cancelled, opening for edit is not allowed",
	"GRN_NO_APPROVAL_FLOW" => "No Approval Flow created for this Department,<br />Please contact MIS now before you submit again",
	"GRN_NO_ACC_APPROVAL_FLOW" => "No Account Verification Approval Flow created for this Department,<br />Please contact MIS now before you submit again",
	"GRN_APPROVAL_COMPLETED" => "Well done! You have finished all the GRN Approval.",
    "GRN_NOT_APPROVAL" => "You cannot approve / reject the GRN ID#%d",
    "GRN_APPROVAL_ALREADY_DONE" => "The GRN ID#%d 's approval was already done",
    "GRN_ACCOUNT_VERIFICATION_COMPLETED" => "Well done! You have finished all the GRN Verification.",
    "GRN_BUYER_VERIFICATION_COMPLETED" => "Well done! You have finished all the GRN Verification.",
    "GRN_GRR_ITEM_USED" => "The GRR%05d is already used in GRN%05d.",
    "GRN_ACCOUNT_CHOWN_SUCCESS" => "GRN Account owner changed to %s",
    "GRN_ACCOUNT_CHOWN_FAILED" => "Cannot change owner to %s. Make sure the user have GRN Approval permission in this branch",
    "GRN_HAVE_DIFF_SP" => "GRN have different selling price, please key in all the remark fields",
	"GRN_CHOWN_SUCCESS" => "GRN Owner changed to %s",
 	"GRN_CHOWN_FAILED" => "Cannot change owner to %s. Make sure the user have GRN permission in this branch",
	"GRN_BOM_PACKAGE_EXISTED" => "BOM SKU items from [%s] existed on the list and cannot be add",
	"GRN_SCALE_ITEM_INVALID_DP" => "* SKU Item [%s] is not decimal points item, whereas qty auto set to empty.",
	"GRN_SCALE_ITEM_INVALID_TD" => "* Unable to find Trade Discount for SKU Item [%s], whereas cost auto set to empty.",
	"GRN_BOM_ITEM_EXISTED" => "SKU Item [%s] is BOM item, cannot add extra qty",
	"GRN_ITEM_OVER_PO_QTY" => "Qty for GRN item %s has reached the maximum amount of current PO qty (%s available in EACH)",
	"GRN_ITEM_OVER_PO_QTY_ITEM_REJECTED" => "GRN item %s could not be received due to this item not in PO",
	"GRN_BRANCH_CHANGED" => 'The GRN you are trying to edit now belongs to "%1$s", but we have detected that you have changed the working branch to "%2$s".
Please change your working branch back to "%1$s" first before you can save this GRN.',
	"GRN_SN_QTY_VARIANCE" => "SKU Item [%s] must receive equal to or more than %s pcs due to the S/N have been sold.",
	"GRN_INACTIVE_SKU" => "This SKU Item [%s] is inactive",

	// GRA module
	"GRA_NO_ITEMS" => "GRA does not have item",
	"GRA_INVALID_GRA_NO" => "GRA%05d could not be found",
	"GRA_SKU_NO_GRN_HISTORY" => "NO GRN History, Vendor Unknown",
	"GRA_CHECKLIST_EMPTY" => "Invalid Packing List #, please try again",
	"GRA_ITEM_NOT_SKU_INCOMPLETE" => "Please provide the full details of Item Not in SKU.",
	"GRA_ALREADY_CHECKOUT" => "GRA %s already checkout",
	"GRA_NO_APPROVAL_FLOW" => "No Approval Flow created for this Department,<br />Please contact MIS now before you submit again",
	"GRA_INVALID_ITEMS" => "Some items have been removed or assigned to other GRA, please click \"Refresh\" under selected SKU to get the actual items.",

	// MKT0
	"MKT_NOT_RUNNING_FROM_HQ" => "This module is only accessible from HQ",
	
	// MKT1
	"MKT1_NO_APPROVAL_FLOW" => "No Approval Flow created for MKT1,<br />Please contact MIS now before you submit again",
	"MKT3_NO_APPROVAL_FLOW" => "No Approval Flow created for MKT3,<br />Please contact MIS now before you submit again",
	"MKT5_NO_APPROVAL_FLOW" => "No Approval Flow created for MKT5,<br />Please contact MIS now before you submit again",
	
	"MKT_INVALID_ID" => "MKT%05d is invalid",
	"MKT_NO_BRANCHES_SELECTED" => "No branch selected",
	"MKT_NO_TITLE" => "No Title",
	"MKT_NO_EXPENSES" => "Expenses is empty",
	"MKT_NO_PERMISSION_TO_EDIT" => "Editing on MKT%05d is not allowed",
	"MKT_INVALID_OFFER_DATE_FROM_TO" => "Offer date range is invalid",
	"MKT3_NO_OFFERS" => "Offer Items table is empty",
	"MKT3_NO_BRANDS" => "Brand Discount table is empty",
 	"MKT3_NEED_AT_LEST_X_OFFERS" => "Proposed Offer Items not sufficient. Need at least %d",
 	"MKT3_NEED_AT_LEST_X_BRANDS" => "Proposed Brand not sufficient. Need at least %d",

	// MKT2
	"MKT2_DEPARTMENT_FILLED_BY_OTHER_USER" => "%s already filled by other user",
	
	// SHIFT RECORD
	"SR_NAME_OR_ID_EMPTY" => "Employee Name or Employee ID is Empty at row %d",
	"SR_NO_EDIT_PERMISSION" => "You do not have Edit permission",
	
	// approval flow
 	"MSTAPPROVAL_NEW_RECORD_ADDED" => "New record added to Approval Flow",
	"MSTAPPROVAL_DATA_UPDATED" => "Approval Flow updated",
 	"MSTAPPROVAL_BRANCH_DEPT_DUPLICATE" => "Approval Flow for the branch and department already exists",

	// mkt6 and mkt6_attachment
	"MUST_PROVIDED_NAME" => "Each attachment need to provided a name",

	//DO
	"DO_ITEM_ALREADY_IN_PO" => "Item already in DO list",
	"DO_NO_APPROVAL_FLOW" => "No Approval Flow created for DO, <br />Please contact MIS now before you submit again",
	"DO_EMPTY" => "The DO does not contain any item",
	"DO_USED_PO" => "The PO No %s have been used by another DO",
	"DO_PO_DELIVERED" => "PO already delivered.",
	"DO_PO_INVALID_BRANCH" => "PO from invalid branch, only allow same branch\'s PO.",
	"DO_INVALID_DATE" => "Invalid DO Date.",
	"DO_NOT_FOUND" => "DO ID#%d does not exist.",
 	"DO_NO_ACCESS" => "You do not have access to DO ID#%d",
	"DO_REMARK_EMPTY" => "Please enter DO Remark.",  
	"DO_INACTIVE" => "The DO ID#%d is inactive",	
	"DO_INVALID_ITEM" => "The item (%s) is invalid",
	"DO_ALREADY_CONFIRM_OR_APPROVED" => "The DO ID#%d is already confirmed or approved",
	"DO_OPEN_INFO_NAME_EMPTY" => "Please enter Company Name.",  
	"DO_OPEN_INFO_ADDRESS_EMPTY" => "Please enter Company Address.",  
	"DO_MAX_ITEM_CANT_ADD" => "Can't add item, maximum Items per this DO is %d.",
	"DO_CREDIT_SALES_NOT_ALLOWED" => "Credit Sales DO is not allow.",
	"DO_CHOWN_SUCCESS" => "DO Owner changed to %s",
 	"DO_CHOWN_FAILED" => "Cannot change owner to %s. Make sure the user have DO permission in this branch",
	"DO_ALREADY_CHECKOUT" => "DO %s already checkout.",
	"DO_DATE_OVER_LIMIT" => "DO Date is over limit or less than required date",
	"DO_DATE_RESET_LIMIT" => "Cannot reset DO that less than reset date limit",
    "DO_ALREADY_CANCELED_OR_DELETED" => "The DO ID#%d is already cancelled or deleted",
	"DO_NO_DATA"=>"Missing %s",
	"DO_CHECKOUT_INVALID_BARCODE"=>"Invalid Barcode or not existed on this DO [%s]",
    "DO_CANNOT_CHECKOUT"=>"The DO ID#%d cannot checkout.",
    "DO_ALREADY_CHECKOUT"=>"The DO ID#%d already checkout.",
    "DO_ITEM_ZERO_QTY"=>"Item(s) does not have any qty.",
    "DO_NO_ITEM_SELECTED" => "No item selected",
	"DO_DEBTOR_PRICE_NOTIFICATION" => "- Debtor Price will override Price Indicator and Credit Sales Cost.\n- If SKU don't have Debtor Price, it will follow back Price Indicator.",
    "DO_CHECKOUT_DRIVER_INFO_REQUIRED" => "Lorry No. and Driver Name is required",
	
  	//DO Serial No
  	"DO_SN_ERROR" => "You have encountered errors in Serial No Details.",
  	"DO_SN_RESET_ERROR" => "Unable to reset due to missing of S/N from branch delivered or the GRN has been created for this IBT DO.",
  	"DO_SN_DUPLICATE" => "%s Below is the duplicated S/N %s",
  	"DO_SN_INVALID" => "%s Below is the list of S/N not existed in database %s",
  	"DO_SN_SOLD" => "%s Below is the list of S/N that have been sold %s",
  	"DO_SN_INACTIVE" => "%s Below is the list of inactive S/N %s",
  	"DO_SN_SKU_DUPLICATE" => "%s Below is the duplicated S/N from same SKU item: <br /> %s",
  	"DO_SN_EMPTY" => "%s Does not having any S/N",
  	"DO_SN_INVALID_QTY" => "%s Numbers of S/N must equal to qty",
  	"DO_SN_INCOMPLETE" => "%s S/N detail is incomplete",
  	"SN_CONFIRMATION" => "Please decide S/N that contains errors before confirm",
  	"DO_SN_INVALID_BRANCH" => "%s Below is the list of S/N that existed but located on other branches %s",
  	"DO_SN_INVALID_SKU" => "%s Below is the list of S/N that existed but belongs to other SKU item %s",

	//DO Batch No
	"DO_BN_NOTIFY"=>"This item %s \n\nBatch No: %s \nExpired Date: %s\n\nContinue anyway?",

	//GRN Batch No / Serial No
	"SKU_BN_NO_ITEM"=>"There is no Batch No to confirm.",
	"SKU_BN_ERROR"=>"There is errors for trying to update Batch No.",
	"SKU_BN_EMPTY"=>"  * Having empty Batch No.",
	"SKU_BN_ED_EMPTY"=>"  * Having empty Expired Date.",
	"SKU_BN_INVALID_ED"=>"  * Expired Date overdue current date.",
	"SKU_BN_QTY_EMPTY"=>"  * Having zero or invalid Qty.",
	"SKU_BN_QTY_OVER"=>"  * Qty overdue.",
	"SKU_BN_QTY_MISMATCH"=>"  * Qty mismatch.",
	"SKU_BN_DUPLICATE"=>"  * %s is duplicated.",
	"SKU_SN_EXISTED"=>"  * The following S/N have been existed in database: %s",
	"SKU_SN_DIFF_BRANCH"=>"  * The following S/N is existed and located on other branches: %s",
	"SKU_SN_SOLD"=>"  * The following S/N is existed but it have been sold:<br />%s",
  	"SKU_GRN_SN_UNMATCHED" => "S/N below does not match with DO Transfer, please verify accordingly<br /> %s",
	
	//VENDOR PO REQUEST
	"VENDOR_PO_REQUEST_TICKET_EXPIRED" => "This ticket have been expired.",
	"VENDOR_PO_REQUEST_NO_ITEM" => "Please add at least 1 item to create PO",
	"VENDOR_PO_REQUEST_INVALID_TICKET" => "You have entered an invalid ticket number.<br />Please try again or consult our staff.",
	
	//PAYMENT VOUCHER
	"PAYMENT_VOUCHER_INVALID_ID" => "This payment voucher ID is invalid",	
	"PAYMENT_VOUCHER_VENDOR_MAINTENANCE_UPDATED" => "Vendor maintenance table updated",
	"PAYMENT_VOUCHER_INVALID_TPL" => "This cheque template does not exist, plaese contact MIS.",
	"PAYMENT_VOUCHER_INVALID_AMOUNT" => "Total amount of this cheque is negative or invalid, please edit again.",
	"PAYMENT_VOUCHER_NO_PRINT_ITEMS" => "No Items have been selected to print or the cheque # you not yet keyin.",
	"PAYMENT_VOUCHER_NO_ITEM" => "Please add at least 1 item to create Payment Voucher",
 	"PAYMENT_VOUCHER_USED_ITEMS" => "%s already used in Payment Voucher (%s).",
 	"PAYMENT_VOUCHER_NOT_ALLOW_REPRINT" => "You do not have privilege to re-print the cheque(s).",
 	"PAYMENT_VOUCHER_INVALID_USERNAME_OR_PASSWORD" => "Invalid Password or Username, You are not allowed to re-print the cheque(s).",	
	"PAYMENT_VOUCHER_INVALID_DOC_DATE" => "Invalid Doc Date Provided in %s .",
	"PAYMENT_VOUCHER_INVALID_PAYMENT_DATE" => "Invalid Payment Date.",
	"PAYMENT_VOUCHER_LOSING_CHEQUE" => "You have cheques that did not followed the sequence of cheque_no, please check again.", 
	"PAYMENT_VOUCHER_EXISTING_CHEQUE_NO" => "Cheque No. (%s) for %s already used in  another Payment Voucher.", 
	
	//ADJUSTMENT	
	"SKU_ITEM_ALREADY_IN_ADJUSTMENT" => "Item already in this Adjustment.", 
	"ADJUSTMENT_NO_ITEM" => "Please add at least 1 item to create Adjustment",
	"ADJUSTMENT_INVALID_DATE" => "Invalid Adjustment Date.", 
	"ADJUSTMENT_TYPE_EMPTY" => "Please provide Adjustment Type", 
 	"ADJUSTMENT_NOT_FOUND" => "Adjustment ID#%d does not exist",
 	"ADJUSTMENT_NO_ACCESS" => "You do not have access to Adjustment ID#%d", 
	"ADJUSTMENT_NO_APPROVAL_FLOW" => "No Approval Flow created for ADJUSTMENT, <br />Please contact MIS now before you submit again",
 	"ADJUSTMENT_INACTIVE" => "The Adjustment ID#%d is inactive",
	"ADJUSTMENT_ALREADY_CANCELED_OR_DELETED" => "The Adjustment ID#%d is already cancelled or deleted",
	"ADJUSTMENT_ALREADY_CONFIRM_OR_APPROVED" => "The Adjustment ID#%d is already confirmed or approved",
	"ADJUSTMENT_ITEMS_NO_QTY" => "You must enter qty for each Adjustment item.",
	"ADJUSTMENT_DATE_OVER_LIMIT" => "Adjustment Date is over limit or less than required date",
	"ADJUSTMENT_DATE_RESET_LIMIT" => "Cannot reset Adjustment that less than reset date limit",
	"ADJUSTMENT_FILE_SIZE_LIMIT" => "Attachment maximum file size 1 MB",
	"ADJUSTMENT_WORK_ORDER_NOT_ALLOW_EDIT" => "This adjustment was generated by Work Order, edit is not allow. Please modify Work Order instead.",
	
	// REPORT
	"REPORT_PLEASE_SELECT_CATEGORY" => "Please select a category",
		 	
	// PO Request
	"PO_REQUEST_ITEM_EXIST" => "The item you trying to add is already in PO Request (By: %s, Qty: %s)",


	// admin
	"ADMIN_COPY_SELLING_CANNOT_COPY_SAME_BRANCH" => "Cannot copy selling from same branch",	
	"ADMIN_SKU_MIGRATION_NO_DATA" => "No data found on the file",
	"ADMIN_VENDOR_NO_DATA" => "No data found on the file",
	"ADMIN_BRAND_NO_DATA" => "No data found on the file",
	"ADMIN_LOGO_EMPTY" => "Branch logo is empty",
 	"ADMIN_LOGO_INV_FORMAT" => "Logo image is not a valid format (JPG/JPEG only)",
 	"ADMIN_LOGO_SIZE_EXCEEDED" => "Logo Image File Size is limited to a maximum of 5MB only",
 	"ADMIN_LOGO_UNWRITABLE" => "Current logo image file is not writeable",
	
	//Front End Announcement
	"ANNOUNCEMENT_TITLE_EMPTY" => "Please enter Title",
	"ANNOUNCEMENT_CONTENT_EMPTY" => "Please enter Content",
	"ANNOUNCEMENT_DELETED" => "Announcement ID#%d deleted.",
	"ANNOUNCEMENT_NOT_DELETED" => "Announcement ID#%d not deleted.",
	"ANNOUNCEMENT_CANCELLED" => "Announcement ID#%d cancelled",
	"ANNOUNCEMENT_NOT_CANCELLED" => "Cannot cancel Announcement",
	"ANNOUNCEMENT_INVALID_DATE_FROM" => "Invalid date from",
	"ANNOUNCEMENT_INVALID_DATE_TO" => "Invalid date to",
	"ANNOUNCEMENT_INVALID_COPY" => "Sub branch cannot copy other branch announcement.",
	"ANNOUNCEMENT_COPY" => "Announcement copy from ID#%d (%s) to ID#%d (%s)",

	// Promotion
	
	"PROMOTION_SAVED" => "Promotion saved.",
	"PROMO_SELECT_SKU" => "Please select one or more SKU.",
	"PROMO_DUPLICATE_ITEM" => "Duplicate Item",
	"PROMO_ALREADY_CONFIRM_OR_APPROVED" => "The Promotion ID#%d is already confirmed or approved",	
	"PROMO_NOT_FOUND" => "Promotion ID#%d does not exist",
	"PROMO_INVALID_DATE_FROM" => "Invalid date from",
	"PROMO_INVALID_DATE_TO" => "Invalid date to",
	"PROMO_NO_APPROVAL_FLOW" => "No Approval Flow created,<br />Please contact MIS now before you submit again",
	"PROMO_NOT_APPROVAL" => "You cannot approve / reject the Promotion ID#%d",
	"PROMO_CANCELLED" => "Promotion ID#%d cancelled",
	"PROMO_NOT_CANCELLED" => "Cannot cancel Promotion",
	"PROMO_REVOKED" => "Promotion revoked from ID#%d to ID#%d",
	"PROMO_COPY" => "Promotion copy from ID#%d to ID#%d",
	"PROMO_NOT_COPY" => "Promotion unable to copy.",
	"PROMO_DELETED" => "Promotion ID#%d deleted.",
	"PROMO_NOT_DELETED" => "Promotion ID#%d not deleted.",
	"PROMO_CANCEL_PROMO_ITEM" => "New Promotion Created.",
	"PROMO_INVALID_COPY" => "Cannot copy other branch promotion.",
	"PROMO_GROUP_NOT_CANCEL" => "Promotion ID#%d group cannot cancel.",
	"PROMO_GROUP_CANCELLED_AND_COPIED" => "Promotion group cancelled and copy to new promotion from ID#%d to ID#%d",
	"PROMO_CB_NO_DISCOUNT" => "Missing amount discount for promotion items.",
	"PROMO_CB_CANNOT_PERCENTAGE" => "Percentage discount is not allow to be used in consignment bearing mode.",
	"PROMO_LIMIT_ITEMS" => "One promotion is limit to maximum %d item.",
	"PROMO_INVALID_QTY" => "%s Qty From cannot be higher than Qty To.",
	"PROMO_PROVIDE_ID" => "Please provide Promotion ID.",
	
    // POS Settings
	"POS_SETTINGS_UPDATED" => "POS Setting updated.",
	"POS_SETTINGS_IMG_UPLOADED" => "Image uploaded.",
	"POS_SETTINGS_INVALID_FORMAT" => "Only JPG/PNG/GIF allowed.",
	"POS_SETTINGS_UPLOAD_ERROR" => "Image upload error, please contact adminstrator.",
	"POS_SETTINGS_IMG_DELETED" => "Image Deleted.",
	"POS_SETTINGS_CANT_MOVE_FILE" => "Fail to overwrite image file, please contact adminstrator.",
	"POS_SETTINGS_INVALID_IMAGE_SIZE" => "Invalid image size. Please image of %s.",

	//Counter Collection
	"COUNTER_ITEM_DELETED" => "Item deleted.",
	"COUNTER_ITEM_NOT_DELETED" => "Item not deleted.",
	"COUNTER_ITEM_UPDATED" => "Item updated.",
	"COUNTER_ITEM_NOT_UPDATED" => "Item not updated.",
	"COUNTER_INVALID_ID" => "Invalid ID.",
	"COUNTER_COLLECTION_UPDATED"=>"Counter Collection updated.",
	"COUNTER_COLLECTION_NOT_UPDATED"=>"Counter Collection not updated.",
	"COUNTER_COLLECTION_INVAILD_RECEIPT"=>"Invalid Receipt No.",
	"COUNTER_COLLECTION_FINALIZED"=>"Counter Collection finalised",
	"COUNTER_COLLECTION_UNFINALIZED"=>"Counter Collection un-finalised.",
	"COUNTER_COLLECTION_RECEIPT_NOT_CANCELLED"=>"Receipt not cancelled.",
	"COUNTER_COLLECTION_RECEIPT_CANCELLED"=>"Receipt cancelled.",
	"COUNTER_COLLECTION_RECEIPT_UNCANCELLED"=>"Receipt undo cancelled.",
	"COUNTER_COLLECTION_RECEIPT_NO_CHANGES"=>"No changes.",
	"COUNTER_COLLECTION_FINALIZED_CANNOT_UPDATE"=>"Counter Collection finalised, cannot update.",
	"COUNTER_COLLECTION_NO_CASH_DOMINATION"=>"no close counter. Cannot finalise.",
	"COUNTER_NEED_DOMINATION_TO_FINALIZE" => "Counter %s need to do cash denomination before finalise",
	"COUNTER_COLLECTION_EWALLET_CB_ERROR" => "Unable to cancel bill with eWallet payment due to the %s.",
	"COUNTER_COLLECTION_EWALLET_CB_UNDO_DISABLED" => "Contains eWallet payment and does not support undo cancelled payment.",
	"COUNTER_COLLECTION_EWALLET_CB_NOT_ACTUAL_DATE" => "Unable to cancel Invoice which contain eWallet payment that are not on the present date.",
	
	// consignment invoice
	"CI_ITEM_ALREADY_IN_PO" => "Item already in Consignment Invoice list",
	"CI_NO_APPROVAL_FLOW" => "No Approval Flow created for Consignment Invoice, <br />Please contact MIS now before you submit again",
	"CI_EMPTY" => "The Consignment Invoice does not contain any item",
	"CI_INVALID_DATE" => "Invalid Consignment Invoice Date.",
	"CI_NOT_FOUND" => "Consignment Invoice ID#%d does not exist.",
 	"CI_NO_ACCESS" => "You do not have access to Consignment Invoice ID#%d",
	"CI_REMARK_EMPTY" => "Please enter Consignment Invoice Remark.",
	"CI_INACTIVE" => "The Consignment Invoice ID#%d is inactive",
	"CI_INVALID_ITEM" => "The item (%s) is invalid",
	"CI_ALREADY_CONFIRM_OR_APPROVED" => "The Consignment Invoice ID#%d is already confirmed or approved",
	"CI_OPEN_INFO_NAME_EMPTY" => "Please enter Company Name.",
	"CI_OPEN_INFO_ADDRESS_EMPTY" => "Please enter Company Address.",
	"CI_MAX_ITEM_CANT_ADD" => "Can't add item, maximum Items per this Consignment Invoice is %d.",
	"CI_INVALID_INVOICE" => "Invalid Consignment Invoice",
	"CI_ALREADY_CANCELED_OR_DELETED" => "The Consignment Invoice ID#%d is already cancelled or deleted",
	
	// consigment
	"CONSIGNMENT_FOREX_NO_CONFIG" => "Config \"consignment_multiple_currency\" not found",
	"CONSIGNMENT_FOREX_CONFIG_NO_CURRENCY" => "No Currency Code found in Config \"consignment_multiple_currency\"",
	"CONSIGNMENT_FOREX_ADDED" => "Added Currency Code [%s] Exchange Rate \"%s\"",
	"CONSIGNMENT_FOREX_UPDATED" => "Updated Currency Code [%s] Exchange Rate from \"%s\" to \"%s\", Currency Description from \"%s\" to \"%s\"",
	"CONSIGNMENT_MONTHLY_REPORT_ALREADY_PRINTED" => "Not allowed. Monthly report for the month already printed",
	
	//Membership Redemption
	"REDEMPTION_STATUS_UPDATED" => "Status updated",
	"REDEMPTION_POINT_UPDATED" => "Point updated",
	"REDEMPTION_POINT_NOT_UPDATED" => "Point not updated",
	"REDEMPTION_CASH_UPDATED" => "Cash updated",
	"REDEMPTION_CASH_NOT_UPDATED" => "Cash not updated",
	"REDEMPTION_RECEIPT_AMOUNT_UPDATED" => "Receipt amount updated",
	"REDEMPTION_RECEIPT_AMOUNT_NOT_UPDATED" => "Receipt amount not updated",
	"REDEMPTION_DELETED" => "Cash deleted",
	"REDEMPTION_NOT_DELETED" => "Cash not deleted",
	"REDEMPTION_STATUS_NOT_UPDATED" => "Status not updated",
	"REDEMPTION_DUPLICATE_SKUID_AND_POINT" => "Duplicate SKU Item and Points",
	"REDEMPTION_INVALID_ID" => "INVALID ID",
	"REDEMPTION_INVALID_VIEW"=>"You have no privilege to view this redemption",
	"REDEMPTION_INVALID_POINT"=>"Cannot set both Point and Receipt Amount to zero",
	"REDEMPTION_INVALID_DATA_PROVIDED"=>"Invalid Data Provided",
	"REDEMPTION_NO_ITEM_UPDATE"=>"No Item to update",
	"REDEMPTION_CANNOT_UPDATE_ITEM_IN_OTHER_BRANCH"=>"Cannot update items in other branch",
	"REDEMPTION_INVALID_DATE_PROVIDED"=>"Date - date start cannot be over date end",
	"REDEMPTION_SKU_NO_APPROVAL_FLOW" => "No Approval Flow created for this Branch, Please contact MIS now before you submit again",
	"REDEMPTION_DATE_OVERDUE"=>"is overdue the current date",
	"REDEMPTION_INVALID_CASH"=>"Cannot insert Cash as negative figure",
	"REDEMPTION_INVALID_STATUS"=>"Cannot set as Active with 0 Point, Cash and Receipt Amount",
	"REDEMPTION_EMPTY"=>"No Redemption to make",
	"REDEMPTION_SKU_UNAVAILABLE"=>"SKU [%s] is not available for this branch",
	"REDEMPTION_RECEIPT_DETAIL_REQUIRED"=>"Please Enter Full Receipt Details",
	"REDEMPTION_INVALID_RECEIPT"=>"Receipt No [%s] is invalid from date [%s] at Counter [%s]",
	"REDEMPTION_RECEIPT_USED"=>"Receipt No [%s] already used",
	"REDEMPTION_RECEIPT_AMT_UNMATCHED"=>"Receipt No [%s] have not enough amount to redemp item",
	"REDEMPTION_INVALID_RECEIPT_DATE"=>"Receipt No [%s] not today receipt. You must use today receipt to redeem this item",
	"REDEMPTION_RECEIPT_BEFORE_REDEEM_DATE"=>"Receipt No [%s] is before the redemption period. Redemption Start at %s",
	"REDEMPTION_RECEIPT_AFTER_REDEEM_DATE"=>"Receipt No [%s] is over the redemption period. Redemption End at %s",
	"REDEMPTION_VOUCHER_EMPTY"=>"Please assign all vouchers",
	"REDEMPTION_VOUCHER_DUPLICATE"=>"Voucher [%s] is duplicated",
	"REDEMPTION_VOUCHER_NOT_EXISTS"=>"Voucher [%s] is not exists",
	"REDEMPTION_VOUCHER_LENGTH_UNMATCHED" => "Voucher [%s] length unmatched",
	"REDEMPTION_VOUCHER_INVALID_AMT" => "Voucher [%s] having invalid amount",
	"REDEMPTION_VOUCHER_CLAIMED" => "Voucher [%s] has been claimed before",
	"REDEMPTION_VOUCHER_CANCELED" => "Voucher [%s] has marked as cancelled",
	"REDEMPTION_VOUCHER_NOT_PRINTED" => "Voucher [%s] had not been printed. Please print out before claim it",
	"REDEMPTION_VOUCHER_INVALID_BRANCH" => "Voucher [%s] not belong to your branch",
	"REDEMPTION_VOUCHER_ACTIVATED" => "Voucher [%s] has been activated before",
	"REDEMPTION_VOUCHER_NO_PRIVILEGE" => "You do not have privilege to activate vouchers",
	"REDEMPTION_INSUFFICIENT_POINTS" => "Insufficient points. Total need %s and you have left %s",
	"REDEMPTION_VOUCHER_EXPIRED" => "Voucher [%s] has been expired on %s",
	"REDEMPTION_VOUCHER_USED" => "Voucher [%s] has been used before",
	"REDEMPTION_EMPTY_RECEIPT_DATE_TO" => "Receipt Date To cannot left empty.",
	
	// SPBT Sales Order
	"INVALID_SO_ID" => "Invalid Order ID",
	"NO_ITEM_FOUND" => "No Item Found",
	"SO_ITEM_FOUND_DUPLICATE" => "Some item already in the list",
	"SO_EMPTY" => "Sales Order does not contain any item",
	"SO_INVALID_DATE" => "Invalid Sales Order Date.",
	"SO_NO_DEBTOR" => "Sales Order have no select deliver to",
	"SO_NO_APPROVAL_FLOW" => "No Approval Flow created for Sales Order, <br />Please contact MIS now before you submit again",
	"SO_NO_BATCH_CODE" => "Please enter batch code",
	"SO_ORDER_NO_NOT_FOUND" => "Sales Order %s not found",
	"SO_ALREADY_USED_IN_DO" => "Sales Order already used in DO",
	"SO_ALREADY_USED_IN_PO" => "Sales Order already used in PO, Please delete those related PO first.",
	"SO_ORDER_CANNOT_CREATE_DO" => "Cannot create DO from Sales Order %s, it is not found or not approved",
	"SO_ORDER_ALREADY_FULLY_DELIVERED" => "Sales Order %s already fully delivered, no more item to DO",
	"SO_DATE_OVER_LIMIT" => "Sales Order Date is over limit or less than required date",
	"SO_DATE_RESET_LIMIT" => "Cannot reset SO that less than reset date limit",
	"SO_ORDER_CANNOT_CREATE_DIFF_BRANCH" => "Cannot create other branch's Sales Order",
	"SO_ORDER_ALREADY_EXPORT_TO_POS" => "Cannot create DO from Sales Order %s, it is being exported to POS",
	"SO_ORDER_CANNOT_CREATE_DO_BY_BATCH" => "Cannot create DO from Sales Order Batch Code %s. It is not found, or has not been approved or has been delivered.",
	"SO_DEBTOR_PRICE_NOTIFICATION" => "- Debtor Price will override Price Indicator.\n- If SKU don't have Debtor Price, it will follow back Price Indicator.",
	
	// CN
	"CN_EMPTY" => "Credit Note does not contain any item",
	"CN_INVALID_DATE" => "Invalid Credit Note Date.",
	"CN_NO_INVOICE_TO" => "Credit Note have no select invoice to",
	"CN_NO_APPROVAL_FLOW" => "No Approval Flow created for Credit Note, <br />Please contact MIS now before you submit again",
	"INVALID_CN_ID" => "Invalid Credit Note ID",
	
	// DN
	"DN_EMPTY" => "Debit Note does not contain any item",
	"DN_INVALID_DATE" => "Invalid Debit Note Date.",
	"DN_NO_INVOICE_TO" => "debit Note have no select invoice to",
	"DN_NO_APPROVAL_FLOW" => "No Approval Flow created for debit Note, <br />Please contact MIS now before you submit again",
	"INVALID_DN_ID" => "Invalid debit Note ID",

	// CN/DN
	"CN_DN_NOT_FOUND" => "%s ID#%d does not exist.",
	"CN_DN_INACTIVE" => "The %s ID#%d is inactive",
	"CN_DN_ALREADY_CONFIRM_OR_APPROVED" => "The %s ID#%d is already confirmed or approved",
	"CN_DN_ALREADY_CANCELED_OR_DELETED" => "The %s ID#%d is already cancelled or deleted",
	
	// Consignment monthly report
	"MTHLY_RPT_EXISTED" => "Monthly Report already confirmed, cannot print again",
	
	//ajax_autocomplete.php
	"GRA_UNCHEKOUT_ITEMS" => "There are un-checkout GRA for this vendor. %s",
	"BOM_SKU_NOT_ALLOWED" => "Not allow to use BOM SKU.",
	"BOM_SKU_NOT_ALLOWED_NORMAL_ONLY" => "Not allow to use this BOM SKU. Only normal BOM is allowed.",
	"DO_ZERO_STOCK_BAL_ITEM" => "SKU item [%s] contains zero or negative stock balance.",
	
	//masterfile_consignment_bearing.php
	"MCB_DELETE" => "Delete successful",
	"MCB_UPDATE" => "Update successful",
	"MCB_MISSING_DATA" => "Invalid data. Missing %s",
	"MCB_ZERO_RATE" => "The branch(s) below got no rate. <br /> - %s",
	"MCB_DATA_EXIST" => "Data had existed. Click %s to view.",
	"MCB_DATA_NOT_EXIST"=> "Current data not exist or had been deleted by others.",
	"MCB_NO_PRICE_TYPE" => "Consignment item with no price type is not allow to be added.",
	"MCB_NO_DISCOUNT" => "Found 0 discount amount at row %s. All data must have discount amount.",
	"MCB_DUPLICATE_DATA" => "Found duplicate data at row %s. All Data must unique.",
	
	//CSA Report
	"CSA_SAVE" => "Save successful",
	"CSA_INVALID_FINALIZE" => "Unable to finalise.",
	"CSA_MISSING_DEPT_CONFIRM" => "%s has %s department(s) haven't confirmed: %s",
	"CSA_FINALIZE" => "Report is finalised.",
	"CSA_UNFINALIZE" => "Report is unfinalised.",
	"CSA_INVALID_UNFINALIZE" => "Unable to unfinalise.",
	"CSA_LATEST_FINALIZE" => "Found latest finalised report at year %s and month %s.",
	"CSA_UNFINALIZE_FULL_REVIEW" => "Related department(s) are fully reviewed.",
	"CSA_REVIEW" => "Report is reviewed.",
	"CSA_UNREVIEW" => "Report is unreviewed.",
	"CSA_INVALID_UNREVIEW" => "Unable to unreviewed. No department set to be unreviewed.",
	"CSA_CONFIRM" => "Report is confirmed.",
	"CSA_NOT_CONFIRM"=>"Unable to view unconfirmed report.",
	"CSA_NOT_GENERATE"=>"Current year and month is not generated yet.",
	"CSA_NOT_ALLOW_EDIT"=>"This report is not allow to be edited, confirmed, finalised or reviewed.",
	"CSA_STARTING_OPENING"=>"Successfully making this month as starting opening stock.",

	//stock copy
	"SC_INVALID_TRANSFER" => "Invalid transfer date on same branch and same date",
	"SC_SUCCESS" => "%s data transfered. Transfer completed",
    "SC_RESET" => "%s data deleted. Reset completed",
    
    //coupon
    "COUP_MISS_CODE" => "Missing coupon code.",
    "COUP_CODE_OVER_DIGIT" => "Invalid coupon code. Code must below 12 digits.",
    "COUP_MISS_DATA" => "Missing %s data.",
    "COUP_MISS_TIME" => "Missing time %s.",
    "COUP_INVALID_TIME_FORMAT" => "Invalid time %s format.",
    "COUP_BRAND_VENDOR_EXIST" => "Current %s had existed.",
    "COUP_HAD_PRINTED" => "Current coupon had been printed. Please refresh the page and try again.",
    "COUP_CODE_EXIST" => "Code had already existed.",
    "COUP_BRAND_VENDOR_ACTIVATED" => "Unable to activate. Selected coupon department and brand/vendor had been activated.",
    "COUP_NO_DATA" => "Current coupon is not existed.",
	"COUP_OVER_DATE" => "End date cannot more than starting date.",
	"COUP_MUST_0_5_CENT" => "The last digit of amount must be " . $config["arms_currency"]["symbol"] . " 0.05 or " . $config["arms_currency"]["symbol"] . " 0.10.",
	"COUP_LIMIT" => "Coupon %s must less than %s.",
	"COUP_EMPTY_SKU_ITEM" => "Please assign at least one or more SKU items.",
	"COUP_REF_PROG_ALL_ZERO" => "Cannot leave Referrer and Referee all data as zero.",

    //voucher
    "VOU_VALUE_INVALID" => "Invalid voucher value.",
    "VOU_CODE_OVER_DIGIT" => "Error: Invalid voucher code. Code must below 12 digits.",
    "VOU_MISS_DATA" => "Missing %s data.",
    "VOU_USE_SAME_TIME" => "Other user is using this module. Please try again later.",
    "VOU_CODE_CREATED" => "Batch no: %s, Code from %s to %s created for voucher value " . $config["arms_currency"]["symbol"] . " %s",
    "VOU_CODE_INVALID" => "Invalid number of voucher to be generated for voucher value " . $config["arms_currency"]["symbol"] . " %s",
    "VOU_CODE_FROM_TO" => "From code must lower than To code for voucher value " . $config["arms_currency"]["symbol"] . " %s.",
    "VOU_CODE_DUPLICATE" => "Found duplicate code for voucher value " . $config["arms_currency"]["symbol"] . " %s.",
    "VOU_CODE_EXIST" => "Current codes had existed for voucher value %s. Please try other codes.",
    "VOU_CODE_NO_EXIST" => "The code below doesn't exist.<br /> - %s",
    "VOU_CODE_NO_EXIST_V" => "Current Codes doesn't exist.",
	"VOU_CODE_NOT_BATCH_V" => "Some codes are not belong to this batch no: %s",
	"VOU_CODE_NOT_BRANCH" => "The code below not belong to your branch.<br /> - %s",
	"VOU_CODE_NOT_BRANCH_V" => "Some codes are not belong to your branch.",
	"VOU_BRANCH_PRINT_V" => "Not allowed to print the voucher at branch %s",
	"VOU_BRANCH_REPRINT_V" => "Not allowed to reprint the voucher at branch %s",
	"VOU_CODE_HAD_ACTIVATED" => "The code(s) below have been activated.<br /> - %s",
    "VOU_CODE_HAD_CANCELLED" => "The code(s) below have been cancelled.<br /> - %s",
	"VOU_CODE_VALUE_NOT_MATCH" => "The code(s) below do not match the voucher value.<br /> - %s",
    "VOU_CODE_HAD_CANCELLED_V" => "Some code(s) have been cancelled.",
    "VOU_CODE_NOT_PRINTED" => "The code below had not been printed. Please print out before activate.<br /> - %s",
    "VOU_CODE_ACTIVATE" => "%d codes activated.",
    "VOU_NO_CODE_ACTIVATE" => "No more codes to be activated.",
    "VOU_NO_BATCH_SELECT" => "No batch been selected to be activated.",
    "VOU_BATCH_ACTIVATE" => "Batch no %s had been activated.",
	"VOU_OVER_LIMIT" => "The total of voucher codes have over the limit for voucher value " . $config["arms_currency"]["symbol"] . " %s",
	"VOU_OVER_DATE" => "End date cannot more than starting date.",
	"VOU_ERR_NOT_EXIST" => "Code(s) not exist",
	"VOU_ERR_NO_CODE" => "There is no code to be generated.",
	"VOU_ERR_INVALID_CODE" => "Invalid Code",
	"VOU_ERR_AMOUNT_NOT_MATCH" => "Amount not match. Original amount: %s",
	"VOU_ERR_DUPLICATE" => "Duplicated",
	"VOU_ERR_USED_AFTER_CANCEL" => "Voucher cancelled after used",
	"VOU_ERR_USED_BEFORE_CANCEL" => "Voucher cancelled before used",
	"VOU_ERR_BEFORE_ACTIVATED_DATE" => "Voucher used before activated date",	
	"VOU_ERR_BEFORE_VALID_DATE" => "Voucher used before valid date",	
	"VOU_ERR_AFTER_EXPIRED" => "Voucher used after expired",
	"VOU_BRANCH_REQUIRED" => "Branch %s need to be ticked in order to activate following %s(s):<br />%s",
	"VOU_CODE_EXPIRED" => "The code(s) below have been expired",
	"VOU_CODE_EXCEEDED_MAX_NUMBER" => "System have reaches maximum for generating voucher codes, please contact ARMS support for further assistance.",
	
	//daily counter collection
    "CC_COUNTER_MISS" => "Missing counter name.",
    
    // frontend counter setup
    "MST_COUNTER_HIT_LIMIT" => "You already reach maximum counter limit",
    "MST_COUNTER_ADDED" => "New Counter added",
    "MST_COUNTER_NO_CHANGES_MADE" => "No changes was made to the counter",
    "MST_COUNTER_UPDATED" => "Counter updated",
    "MST_NETWORK_NAME_DUPLICATE" => "Network Name %s already in-used",
    "MST_NETWORK_NAME_EMPTY" => "Network Name is empty",
    
	// sales agent
	"SA_ID_INVALID" => "Sales Agent ID Invalid.",
	"SA_CODE_INVALID" => "Sales Agent Code %s is %s.",
	"SA_NAME_EMPTY" => "Name is empty",
	"SA_EMAIL_PATTERN_INVALID" => "Email format is invalid",
	"SA_INVALID_TICKET" => "The ticket is invalid",
	"SA_TICKET_EXPIRED" => "The ticket has been expired",
	
	// sales agent commission
	"SAC_INVALID_ID" => "failed to load Sales Agent Commission's ID",
	"SAC_ITEM_NOT_FOUND" => "failed to retrieve commission item for ID#%s",
	"SAC_INVALID_TITLE" => "Title is empty.",
	"SAC_NO_ITEM" => "No item were added in this Commission.",
	"SAC_ITEM_DATE_DUPLICATION" => "Date Start cannot be repeatedly",
	
	// pos verify code
	"VERIFY_NO_ID" => "Missing real sku item data in selected items list.",
	"VERIFY_NO_CODE" => "No code to be updated.",
	"VERIFY_UPDATE_SUC" => "Update POS successful.",
	
	// masterfile - return policy
	"MRP_TITLE_EMPTY" => "The Title was empty",
	"MRP_TITLE_EXISTED" => "The Title was existed",
	"MRP_DURATION_EMPTY" => "The %s Duration setup was empty",
	"MRP_DURATION_DUPLICATE" => "The %s Duration %s %s was duplicated on the list",
	"MRP_CONDITION_INVALID" => "The list cannot be combine between 'More Than' and 'Every'",
	"MRP_CONDITION_EVERY_INVALID" => "The %s setup cannot have more than one setting for %s Condition [Every]",
	"MRP_EXPIRY_DATE_EMPTY" => "The Expire Date was empty",
	
	"RPC_ITEM_EXISTED" => "The %s [%s] has been existed on the list.",
	
	"MST_FP_DATE_INVALID" => "Date is empty or cannot older than current time %s",
	"MST_FP_BRANCH_EMPTY" => "Please select one or more branch",
	"MST_FP_ITEM_EMPTY" => "Please add one or more items",
	"MST_FP_QPRICE_ITEM_EXISTED" => "The SKU Item %s with Min Qty: %s and Price: %s is duplicated",
	"MST_FP_QPRICE_ITEM_EMPTY" => "The SKU Item %s contains zero Min Qty or Price",
	"MST_FP_ITEM_EXISTED" => "The SKU Item %s from row[%s] cannot be repeated",
	"MST_FP_CSV_INVALID" => "Please upload a file and make sure it is CSV format",
	"MST_FP_NOT_FOUND" => "Batch Price Change or items not found.",
	
	// PURCHASE AGREEMENT
	"PURCHASE_AGREEMENT_NOT_FOUND" => "Purchase Agreement ID#%d does not exist",
	"PURCHASE_AGREEMENT_ALREADY_CONFIRM_OR_APPROVED" => "The Purchase Agreement ID#%d is already confirmed or approved",	
	"PURCHASE_AGREEMENT_INVALID_DATE_FROM" => "Invalid date from",
	"PURCHASE_AGREEMENT_INVALID_DATE_TO" => "Invalid date to",
	"PURCHASE_AGREEMENT_DELETED" => "Promotion ID#%d deleted.",
	"PURCHASE_AGREEMENT_NOT_DELETED" => "Promotion ID#%d not deleted.",
	"PURCHASE_AGREEMENT_NO_APPROVAL_FLOW" => "No Approval Flow created,<br />Please contact MIS now before you submit again",

	// stock take - zerolize negative stocks
	"ST_NS_NO_RECORD" => "Negative Stock not found for %s, no record generated.",
	"ST_NS_INVALID_INFO" => "Date, Location or Shelf is empty.",
	"ST_COST_INVALID" => "Current %s cost price from Stock Take is invalid<br />(compared to the Unit Cost Price %s from current Stock Take)",
	
	// vendor portal
	"VP_GRN_INVALID_DATE" => "Received Date is empty.",
	"VP_GRN_INVALID_QTY" => "Please enter at least one qty for an item.",
	"VP_GRN_INVALID_FORMAT" => "Please select a valid CSV file to import.",
	
	// debtor portal
	"DP_INVLID_LOGIN" => "You have entered an invalid ticket number.<br />Please try again or consult our staff.",
	
	// Offline Mode
	"OFFLINE_DATA_UNSYNCED" => "You still have pending documents from [%s] at offline mode, <br />please upload before login.",
	
	// GST Settings
	"GST_FIELD_EMPTY" => "%s cannot left empty.",
	"GST_CODE_DUPLICATED" => "The GST Code is duplicated.",
	"GST_DEACTIVATE_ERROR" => "Unable to deactivate this GST due to it being used under %s",
	"GST_DEACTIVATE_REACH_LIMIT" => "Unable to deactivate, must have at least one active GST for type [%s]",
	
	// GST Price Wizard
	"GST_PRICE_WIZARD_INVALID_CAT" => "Please select a category.",
	"GST_PRICE_WIZARD_REACHED_MAX_ITEMS" => "Reached Maximum %s items, please download and and import from Batch Price Change Manually.",
	"GST_PRICE_WIZARD_INVALID_MPRICE" => "Please select at least one or more MPrice.",
	
	// GST DNote
	"GST_DN_EXISTED" => "Found this document have external D/N, therefore ARMS D/N cannot be generated.",
	"GST_DN_NOTHING_TO_PRINT" => "No variances between Receiving and Account Correction for this GRN",
	
    // Account export
    "SET_RECEIPT_NO_PREFIX" => "%s-%s",
    "EXPORT_CSV_FILE_FORMART"=>"\"%s\"",
    "GST_RATE"=>"%d",
	
	// CNOTE
	"CNOTE_INVALID_FORM" => "Invalid Credit Note",
	"CNOTE_NEED_AT_LEAST_ONE_ITEM" => "Please add at least 1 item",
	"CNOTE_INVALID_CN_DATE" => "Invalid CN Date",
	"CNOTE_INVALID_INV_NO" => "Invalid Invoice No.",
	"CNOTE_INVALID_INV_DATE" => "Invalid Invoice Date.",
	"CNOTE_NEED_CUST_NAME" => "Please key in Customer Name.",
	"CNOTE_NO_APPROVAL_FLOW" => "No Approval Flow created for this Credit Note,\nPlease contact MIS now before you submit again",
	"CNOTE_NOT_ALLOW_TO_EDIT" => "You are not allow to edit this module.",
	"CNOTE_DATE_RESET_LIMIT" => "Cannot reset CN that less than reset date limit",
	"CNOTE_NOT_ALLOW_GENERATE_DATA" => "The CN is not allow to generate approval contain.",
	"CNOTE_NOT_ALLOW_PRINT" => "This CN is not allow to print.",
	
	// administrator - import stock take 
	"IMPORT_ST_INVALID_FM_ITEM" => "%s is a child SKU item from Fresh Market, only parent Fresh Market SKU is allowed to do Stock Take.",
	"IMPORT_ST_COST_DIFF" => "%s is having cost price variance within the import file,<br />system found that %s cost price from import file is set to %s and the unit cost price is not equal to %s.",
	"IMPORT_ST_COST_INVALID" => "%s is having cost price variance from Imported Stock Check (Location: %s, Shelf: %s),<br />current %s cost price from Imported Stock Check is set to %s (compared to the Cost Price %s from import file).",
	"IMPORT_ST_NO_PARENT_CHILD" => "%s contains other parent & child SKU items which did not have stock take within the import file,<br />stock take for the whole SKU family is required.",
	
	// Masterfile Vendor - Import / Export Payment Voucher Code by CSV
	"PVC_EXPORT_NO_BRANCH_SELECTED" => "No branch was selected",
	"PVC_EXPORT_NO_ACCT_CODE_SELECTED" => "No Account Code was selected",
	
	// WORK Order
	"WORK_ORDER_ITEM_DUPLICATED" => "Item already existed in document.",
	"WORK_ORDER_WEIGHT_ITEM_ONLY" => "Only SKU with Weight is allowed.",
	"WORK_ORDER_INVALID_FORM" => "Invalid Work Order",
	"WORK_ORDER_NOT_ALLOW_TO_EDIT" => "You are not allow to edit this module.",
	"WORK_ORDER_CANT_SAVE_DUE_TO_MODIFIED" => "This Work Order has been modified by other source, your save failed.",
	"WORK_ORDER_NEED_AT_LEAST_ONE_OUT_ITEM" => "Please add at least 1 item for Transfer Out",
	"WORK_ORDER_NEED_AT_LEAST_ONE_IN_ITEM" => "Please add at least 1 item for Transfer In",
	"WORK_ORDER_INVALID_DATE" => "Invalid Date",
	"WORK_ORDER_INVALID_RESET_ACTION" => "Invalid Reset Action",
	"WORK_ORDER_DATE_RESET_LIMIT" => "Cannot reset Work Order that less than reset date limit",
	
	// Foreign Currency Table
	"FOREIGN_CURRENCY_INVALID_RATE" => "Invalid Currency Rate [%s].",
	"FOREIGN_CURRENCY_INVALID_DATE" => "Invalid Currency Date [%s].",
	"FOREIGN_CURRENCY_INVALID_CODE" => "Invalid Currency Code [%s].",
	"FOREIGN_CURRENCY_SAME_RATE" => "No changes made, due to the same Currency Rate",
	"FOREIGN_CURRENCY_UPDATED" => "Currency code [%s], exchange rate has been updated from [%s] to [%s].",
	"FOREIGN_CURRENCY_UPDATED_RATE2" => "Currency code [%s], exchange rate 2 has been updated from [%s] to [%s].",
	"FOREIGN_CURRENCY_CODE_EMPTY" => "Currency code is empty.",
	"FOREIGN_CURRENCY_RATE_NOTICE" => "- Exchange Rate use to convert Foreign Currency to Base Currency.\n- Used by most of the back-end modules.",
	"FOREIGN_CURRENCY_BASE_RATE_NOTICE" => "- Exchange Rate use to convert Base Currency to Foreign Currency.\n- Used by Front-end POS Counter.",
	
	// Email Manageement
	"EMAIL_OBJ_CANNOT_EMPTY" => "Mailer Object cannot be null.",
	"EMAIL_OBJ_INVALID" => "Invalid Mailer Data.",
	"EMAIL_BRANCH_ID_INVALID" => "Email from Branch is Required.",
	"EMAIL_ID_INVALID" => "Invalid Email ID.",
	"EMAIL_NOT_FOUND" => "The Email data you request is not found.",
	"EMAIL_ALREADY_SENT_BEFORE" => "The email already sent.",
	"EMAIL_SENDING_UNKNOWN_ERROR" => "Unknown Error occured when try to send the email.",
	
	// SKU Update by Vendor / Brand
	"UPDATE_SKU_BRAND_VENDOR_NO_DATA" => "No data found on the file",
	
	// Import Vendor Quotation Cost
	"IMPORT_VENDOR_QC_NO_DATA" => "No data found on the file",
	"IMPORT_VENDOR_QC_INVALID_VD" => "Vendor %s does not exists",
	"IMPORT_VENDOR_QC_NO_UPDATE" => "SKU Code %s have nothing to update",
	"IMPORT_VENDOR_QC_INVALID_COST" => "Invalid Quotation Cost",
	
	// Update SKU Category Discount by CSV
	"UPDATE_SKU_CAT_DISC_INVALID_MEMBER_TYPE" => "Invalid Member Type: %s",
	"UPDATE_SKU_CAT_DISC_INVALID_TYPE" => "Invalid Category Discount Method [%s]",
	
	// Cycle Count
	"CYCLE_COUNT_INVALID_ID" => "Invalid Cycle Count ID: %s",
	"CYCLE_COUNT_NOT_FOUND" => "Cycle Count Not Found",
	"CYCLE_COUNT_NOT_ALLOW_OTHER_BRANCH" => "This Cycle Count is belongs to other branch.",
	"CYCLE_COUNT_NOT_ALLOW_TO_EDIT" => "This Cycle Count is not allow to edit.",
	"CYCLE_COUNT_NOT_ALLOW_TO_START" => "This Cycle Count status is incorrect, cannot be start.",
	"CYCLE_COUNT_APPROVAL_FLOW" => "No Approval Flow created for Cycle Count,\nPlease contact MIS now before you submit again",
	"CYCLE_COUNT_ONLY_PIC_CAN_PRINT" => "Only Allow to print by Assigned Stock Take Person",
	"CYCLE_COUNT_ONLY_PIC_CAN_START" => "Only Allow to Start by Assigned Stock Take Person",
	"CYCLE_COUNT_WRONG_STOCK_TAKE_BRANCH" => "Wrong Stock Take Branch.",
	"CYCLE_COUNT_NO_ITEM" => "No Item for this Cycle Count",
	"CYCLE_COUNT_CANNOT_SAVE_ST" => "Cycle Count Stock Take Failed to save due to incorrect status.",
	"CYCLE_COUNT_RESET_STATUS_WRONG" => "Cycle Count Failed to Reset due to incorrect status.",
	"CYCLE_COUNT_REOPEN_STATUS_WRONG" => "Cycle Count Failed to Re-open due to incorrect status.",
	"CYCLE_COUNT_REGEN_POS_QTY_STATUS_WRONG" => "Cycle Count Failed to Regenerate POS Qty due to incorrect status.",
	"CYCLE_COUNT_SEND_ST_STATUS_WRONG" => "Cycle Count Failed to Send to Store Stock Take due to incorrect status.",
	"CYCLE_COUNT_RECALL_ST_STATUS_WRONG" => "Cycle Count Failed to Recall from Store Stock Take due to incorrect status.",
	"CYCLE_COUNT_RECALL_ST_ALREADY_IMPORTED" => "Cycle Count Failed to Recall from Store Stock Take due to Stock Take already has been imported, Please reset the Stock Take first.",
	"CYCLE_COUNT_INVALID_CLONE_TYPE" => "Cycle Count Failed to clone due to invalid clone type.",
	"CYCLE_COUNT_INVALID_CLONE_DATE" => "Cycle Count Failed to clone due to invalid stock take date.",
	"CYCLE_COUNT_CLONE_FAILED" => "Cycle Count Failed to clone due to unknown error.",
	
	// eWallet API
	"EWALLET_API_PAYDIBS_PAYMENT_PROCESSED_FAILED" => "System attempted to process the Payment but encountered unexpected response.\nPlease record customer payment via eWallet app if payment has been completed,\nand forward to Paydibs customer service for claim purposes.",
	
	// Time Attendance
	"SHIFT_TABLE_GOT_DATA" => "Cant generate default shift due to already have data.",
	"SHIFT_ID_INVALID" => "Invalid Shift ID.",
	"SHIFT_CODE_INVALID" => "Invalid Shift Code.",
	"SHIFT_DESC_INVALID" => "Invalid Shift Description.",
	"SHIFT_START_INVALID" => "Invalid Shift Start Time.",
	"SHIFT_END_INVALID" => "Invalid Shift End Time.",
	"SHIFT_BREAK_1_INVALID" => "Invalid Shift Break 1 Time.",
	"SHIFT_BREAK_2_INVALID" => "Invalid Shift Break 2 Time.",
	"SHIFT_ID_NOT_FOUND" => "Shift ID [%s] Not Found.",
	"SHIFT_CODE_USED" => "Shift Code already used by other Shift.",
	"SHIFT_BRANCH_INVALID" => "Invalid Shift Branch.",
	"SHIFT_YEAR_INVALID" => "Invalid Shift Year.",
	"SHIFT_USER_INVALID" => "Invalid Shift User.",
	"SHIFT_MONTH_INVALID" => "Invalid Shift Month.",
	"SHIFT_CLOCK_BARCODE_EMPTY" => "Please enter user barcode.",
	"SHIFT_CLOCK_BARCODE_INVALID" => "Invalid User Barcode.",
	"SHIFT_CLOCK_USER_ID_INVALID" => "Invalid User ID.",
	"SHIFT_CLOCK_TIME_INVALID" => "Invalid Attendance Time.",
	"SHIFT_CLOCK_DATE_INVALID" => "Invalid Attendance Date.",
	"SHIFT_CLOCK_MAX_6_SCAN" => "Maximum 6 Scans Per Day for a user.",
	"SHIFT_SCAN_DATA_NOT_FOUND" => "Scan Data Time [%s] Not Found.",
	"PH_ID_NOT_FOUND" => "Public Holiday ID [%s] Not Found.",
	"PH_ID_INVALID" => "Invalid Public Holiday ID.",
	"PH_CODE_INVALID" => "Invalid Holiday Code.",
	"PH_DESC_INVALID" => "Invalid Holiday Description.",
	"PH_CODE_USED" => "Public Holiday Code already used by other.",
	"PH_YEAR_INVALID" => "Invalid Year.",
	"PH_YEAR_EXISTS" => "Year %s already existed.",
	"PH_LIST_EMPTY" => "Holiday List Not Found.",
	"PH_DATE_FROM_INVALID" => "Invalid Date Format for Holiday Code [%s].",
	"PH_DATE_TO_INVALID" => "Invalid Date Format for Holiday Code [%s].",
	"PH_DATE_FROM_TO_ERROR" => "Date To is ealier than Date From for Holiday Code [%s].",
	"PH_YEAR_TO_DIFF" => "[%s] Date %s is not in year %s.",
	"LEAVE_TABLE_GOT_DATA" => "Cant generate default leave due to already have data.",
	"LEAVE_ID_INVALID" => "Invalid Leave ID.",
	"LEAVE_ID_NOT_FOUND" => "Leave ID [%s] Not Found.",
	"LEAVE_CODE_INVALID" => "Invalid Holiday Code.",
	"LEAVE_DESC_INVALID" => "Invalid Holiday Description.",
	"LEAVE_CODE_USED" => "Leave Code already used by other.",
	"LEAVE_DATE_HAVE_DATA" => "The select date range already have other leave applied.",
	"USER_FP_NOT_FOUND" => "User Finger Print Not Set Up Yet.",
	
	// Sales Agent - KPI Table
	"SA_KPI_SETUP_FIELD_EMPTY" => "[%s] is empty.",
	"SA_KPI_SETUP_EXISTED" => "KPI [%s] for Position [%s] existed in database.",
	
	// Sales Agent - KPI Rating
	"SA_KPI_RATING_INVALID_SCORES" => "Contains negative scores",
	"SA_KPI_RATING_MAX_SCORES_EXCEEDED" => "exceeded maximum scores",
	
	// Membership Credit Promotion
	"MEMBERSHIP_CP_INVALID_UNIQUE_ID" => "Invalid Membership Credit Promotion Unique ID",
	"MEMBERSHIP_CP_NOT_FOUND" => "Membership Credit Promotion UNIQUE ID [%s] Not Found",
	"MEMBERSHIP_CP_NOT_ALLOW_EDIT" => "Membership Credit Promotion Not Allow to Edit",
	"MEMBERSHIP_CP_NOT_ALLOW_CANCEL" => "Membership Credit Promotion Unable to Cancel",
	"MEMBERSHIP_CP_NOT_ALLOW_OTHER_BRANCH" => "This Credit Promotion is belongs to other branch.",
	"MEMBERSHIP_CP_INVALID_DATA" => "Credit Promotion Found the invalid data [%s].",
	"MEMBERSHIP_CP_INVALID_EXPIRY_DATE" => "Expiry Date can only be set as future date.",
	
	// Membership Credit Settings
	"MEMBERSHIP_CS_INVALID_TOPUP_RATE" => "The settings for Top Up Rate was invalid %s",
);
?>
