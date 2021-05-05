<?php
/*
4/7/2017 8:48 AM Qiu Ying
- Bug fixed on sku description should not be shown because we are using group by receipt

6/12/2017 10:47 AM Qiu Ying
- Bug fixed on add tax rate in all data type

8/1/2017 11:10 AM Qiu Ying
- Enhanced to load pre-list templates
- Enhanced to add second tax code

8/4/2017 5:53 PM Andy
- Add maintenance version checking 326.

8/10/2017 10:15 AM Qiu Ying
- Bug fixed on dragging wrong data field in sage 50 purchase template

8/15/2017 09:35 AM Qiu Ying
- Bug fixed on adding second tax code in all format except single line payment data type

2017-08-23 11:00 AM Qiu Ying
- Enhanced to add auto count as prelist template

11/2/2017 5:24 PM Andy
- Revert to hide Row Format "Master No Repeat".
- Inactive Auto Count Preset Cash Sales Format.
- Added Data "Purchase Account Code" and "Purchase Account Name" for Purhcase Single Line and Two Row format.

*/

$maintenance->check(326);

$account_list = array(
	"cash" => "Cash", 
	"credit_card" => "Credit Card",
	"voucher" => "Voucher",
	"coupon" => "Coupon",
	"cheque" => "Cheque",
	"debit" => "Debit",
	"purchase" => "Purchase",
	"sales" => "Sales",
	"credit_sales" => "Credit Sales",
	"customer_code" => "Customer Code",
	"deposit" => "Deposit",
	"rounding" => "Rounding",
	"service_charge" => "Service Charge",
	"short" => "Short",
	"over" => "Over",
	"terms" => "Terms",
	"credit_term" => "Credit Term",
	"purchase_tax" => "Purchase Tax",
	"tax_gl_account" => "Tax GL Account Code",
	"sales_return" => "Sales Return",
	"purchase_return" => "Purchase Return",
	"goods_exchange_contra" => "Goods Exchange Contra",
	"cash_refund" => "Cash Refund"
);

if(isset($config["counter_collection_extra_payment_type"])){
	foreach($config["counter_collection_extra_payment_type"] as $item){
		$account_list[strtolower($item)] = $item;
	}
}

$data_field = array(
	"open_field" => array(
		"field_type" => "open_field",
		"title" => "User Defined Field",
		"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
		"field_label_type" => "open_field"
	), 
	/*"cancelled" => array(
		"field_type" => "cancelled",
		"title" => "Cancelled",
		"field_desc" => "This field indicate the status of the record",
		"field_active" => "F",
		"field_cancel" => "T",
		"field_label_type" => "cancel"
	),
	"transferable" => array(
		"field_type" => "transferable",
		"title" => "Transferable",
		"field_desc" => "This field indicate the status of the record",
		"field_active" => "T",
		"field_cancel" => "F",
		"field_label_type" => "cancel"
	),*/
	"inv_seq_num" => array(
		"field_type" => "inv_seq_num",
		"title" => "Invoice Sequence Number",
		"field_desc" => "The value in this field will auto incremented by 1 according to the Invoice No. The used of Leading Zero field is to indicate the number of occurences of the digit 0. (Example: Set Leading Zero value to 3, then you will get 001)",
		"field_value" => "0",
		"field_label_type" => "inv_seq_num",
		"default_value" => "1"
	),
	"seq_num" => array(
		"field_type" => "seq_num",
		"title" => "Sequence Number",
		"field_desc" => "The value in this field will auto incremented by 1. The used of Leading Zero field is to indicate the number of occurences of the digit 0. (Example: Set Leading Zero value to 3, then you will get 001)",
		"field_value" => "0",
		"field_label_type" => "seq_num",
		"default_value" => "1"
	),
	"batchno" => array(
		"field_type" => "batchno",
		"title" => "Batch No.",
		"field_desc" => "Batch No (Example: 1xxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "1xxxxxxx"
	),
	"date" => array(
		"field_type" => "date",
		"title" => "Date",
		"field_desc" => "The date format can be set in the Settings (Example: DD/MM/YYYY HH:MM).",
		"field_label_type" => "view"
	),
	"doc_no" => array(
		"field_type" => "doc_no",
		"title" => "Document No",
		"field_desc" => "Document No (Example: 1xxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "1xxxxxxx"
	),
	"ref_no" => array(
		"field_type" => "ref_no",
		"title" => "Reference No",
		"field_desc" => "Reference No (Example: 1xxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "1xxxxxxx"
	),
	"payment_description" => array(
		"field_type" => "payment_description",
		"title" => "Description",
		"field_desc" => "Description (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"selling_price_before_gst" => array(
		"field_type" => "selling_price_before_gst",
		"title" => "Amount Before GST",
		"field_desc" => "Amount Before GST (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"tax_amount" => array(
		"field_type" => "tax_amount",
		"title" => "Tax Amount",
		"field_desc" => "Tax Amount (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"selling_price_after_gst" => array(
		"field_type" => "selling_price_after_gst",
		"title" => "Amount After GST",
		"field_desc" => "Amount After GST (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"tax_code" => array(
		"field_type" => "tax_code",
		"title" => "Tax Code",
		"field_desc" => "Tax Code (Example: XX).",
		"field_label_type" => "view",
		"default_value" => "XX"
	),
	"tax_rate" => array(
		"field_type" => "tax_rate",
		"title" => "Tax Rate",
		"field_desc" => "Tax Rate (Example: 6).",
		"field_label_type" => "view",
		"default_value" => "6"
	),
	"customer_code" => array(
		"field_type" => "customer_code",
		"title" => "Customer Code",
		"field_desc" => "Customer Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"customer_name" => array(
		"field_type" => "customer_name",
		"title" => "Customer Name",
		"field_desc" => "Customer Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"account_code" => array(
		"field_type" => "account_code",
		"title" => "Account Code",
		"field_desc" => "Account Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"account_name" => array(
		"field_type" => "account_name",
		"title" => "Account Name",
		"field_desc" => "Account Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"tax_account_code" => array(
		"field_type" => "tax_account_code",
		"title" => "Tax Account Code",
		"field_desc" => "Tax Account Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"tax_account_name" => array(
		"field_type" => "tax_account_name",
		"title" => "Tax Account Name",
		"field_desc" => "Tax Account Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"branch_code" => array(
		"field_type" => "branch_code",
		"title" => "Branch Code",
		"field_desc" => "Branch Code (Example: XXX).",
		"field_label_type" => "view",
		"default_value" => "XXX"
	),
	"payment_amt1" => array(
		"field_type" => "payment_amt1",
		"title" => "Payment Amount",
		"field_desc" => "Payment Amount (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"payment_type1" => array(
		"field_type" => "payment_type1",
		"title" => "Payment Type",
		"field_desc" => "Payment Type (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"payment_amt2" => array(
		"field_type" => "payment_amt2",
		"title" => "Payment Amount 2",
		"field_desc" => "Payment Amount 2 (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"payment_type2" => array(
		"field_type" => "payment_type2",
		"title" => "Payment Type 2",
		"field_desc" => "Payment Type 2 (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"payment_amt3" => array(
		"field_type" => "payment_amt3",
		"title" => "Payment Amount 3",
		"field_desc" => "Payment Amount 3 (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"payment_type3" => array(
		"field_type" => "payment_type3",
		"title" => "Payment Type 3",
		"field_desc" => "Payment Type 3 (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"sub_total" => array(
		"field_type" => "sub_total",
		"title" => "Sub Total",
		"field_desc" => "Sub Total (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"total_tax_amount" => array(
		"field_type" => "total_tax_amount",
		"title" => "Total Tax Amount",
		"field_desc" => "Total Tax Amount (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"grand_total" => array(
		"field_type" => "grand_total",
		"title" => "Grand Total",
		"field_desc" => "Grand Total (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"rounding_adj" => array(
		"field_type" => "rounding_adj",
		"title" => "Rounding Adjustment",
		"field_desc" => "Rounding Adjustment (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"inv_no" => array(
		"field_type" => "inv_no",
		"title" => "Invoice Number",
		"field_desc" => "Invoice Number (Example: 1xxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "1xxxxxxx"
	),
	"foreign_currency_code" => array(
		"field_type" => "foreign_currency_code",
		"title" => "Foreign Currency Code",
		"field_desc" => "Foreign Currency Code (Example: XXX).",
		"field_label_type" => "view",
		"default_value" => "XXX"
	),
	"currency_rate" => array(
		"field_type" => "currency_rate",
		"title" => "Currency Rate",
		"field_desc" => "Currency Rate (Example: 6).",
		"field_label_type" => "view",
		"default_value" => "6"
	),
	"terms" => array(
		"field_type" => "terms",
		"title" => "Terms",
		"field_desc" => "Terms (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"uom" => array(
		"field_type" => "uom",
		"title" => "UOM",
		"field_desc" => "UOM (Example: XXX).",
		"field_label_type" => "view",
		"default_value" => "XXX"
	),
	"suomqty" => array(
		"field_type" => "suomqty",
		"title" => "SUOM Qty",
		"field_desc" => "SUOM Qty (Example: 1).",
		"field_label_type" => "view",
		"default_value" => "1"
	),
	"vendor_terms" => array(
		"field_type" => "vendor_terms",
		"title" => "Vendor Terms",
		"field_desc" => "Vendor Terms (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"department" => array(
		"field_type" => "department",
		"title" => "Department",
		"field_desc" => "Department (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"gl_code" => array(
		"field_type" => "gl_code",
		"title" => "GL Code",
		"field_desc" => "GL Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"gl_name" => array(
		"field_type" => "gl_name",
		"title" => "GL Name",
		"field_desc" => "GL Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"currency_code" => array(
		"field_type" => "currency_code",
		"title" => "Currency Code",
		"field_desc" => "Currency Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "XXX"
	),
	"credit_term" => array(
		"field_type" => "credit_term",
		"title" => "Credit Term Account Code",
		"field_desc" => "Credit Term Account Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"tax_gl_code" => array(
		"field_type" => "tax_gl_code",
		"title" => "Tax GL Account Code",
		"field_desc" => "Tax GL Account Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"tax_gl_name" => array(
		"field_type" => "tax_gl_name",
		"title" => "Tax GL Account Name",
		"field_desc" => "Tax GL Account Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"reason" => array(
		"field_type" => "reason",
		"title" => "Reason",
		"field_desc" => "Reason (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"reason_code" => array(
		"field_type" => "reason_code",
		"title" => "Reason Code",
		"field_desc" => "Reason Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"purchase_tax_code" => array(
		"field_type" => "purchase_tax_code",
		"title" => "Purchase Tax Account Code",
		"field_desc" => "Purchase Tax Account Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"purchase_tax_name" => array(
		"field_type" => "purchase_tax_name",
		"title" => "Purchase Tax Account Name",
		"field_desc" => "Purchase Tax Account Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"amount" => array(
		"field_type" => "amount",
		"title" => "Amount",
		"field_desc" => "Amount (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"debit" => array(
		"field_type" => "debit",
		"title" => "Debit",
		"field_desc" => "Debit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"credit" => array(
		"field_type" => "credit",
		"title" => "Credit",
		"field_desc" => "Crebit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"fx_amount" => array(
		"field_type" => "fx_amount",
		"title" => "FX Amount",
		"field_desc" => "FX Amount (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"fx_debit" => array(
		"field_type" => "fx_debit",
		"title" => "FX Debit",
		"field_desc" => "FX Debit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"fx_credit" => array(
		"field_type" => "fx_credit",
		"title" => "FX Credit",
		"field_desc" => "FX Credit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"fx_rate" => array(
		"field_type" => "fx_rate",
		"title" => "FX Rate",
		"field_desc" => "FX Rate (Example: 6).",
		"field_label_type" => "view",
		"default_value" => "6"
	),
	"taxable" => array(
		"field_type" => "taxable",
		"title" => "Taxable",
		"field_desc" => "Taxable (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"fx_taxable" => array(
		"field_type" => "fx_taxable",
		"title" => "FX Taxable",
		"field_desc" => "FX Taxable (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"total_amount" => array(
		"field_type" => "total_amount",
		"title" => "Total Amount",
		"field_desc" => "Total Amount (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"bill_type" => array(
		"field_type" => "bill_type",
		"title" => "Bill Type",
		"field_desc" => "Bill Type (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"qty" => array(
		"field_type" => "qty",
		"title" => "Quantity",
		"field_desc" => "Quantity (Example: 1).",
		"field_label_type" => "view",
		"default_value" => "1"
	),
	"desp2" => array(
		"field_type" => "desp2",
		"title" => "Description 2",
		"field_desc" => "Description 2 (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"desp3" => array(
		"field_type" => "desp3",
		"title" => "Description 3",
		"field_desc" => "Description 3 (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"second_tax_code" => array(
		"field_type" => "second_tax_code",
		"title" => "Second Tax Code",
		"field_desc" => "Second tax code can be set in Masterfile GST Tax Code Module. Masterfile GST's tax code will be used, if second tax code not set. (Example: XX).",
		"field_label_type" => "view",
		"default_value" => "XX"
	),
	"taxable_dr" => array(
		"field_type" => "taxable_dr",
		"title" => "Taxable Debit",
		"field_desc" => "Taxable Debit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"taxable_cr" => array(
		"field_type" => "taxable_cr",
		"title" => "Taxable Credit",
		"field_desc" => "Taxable Credit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"tax_dr" => array(
		"field_type" => "tax_dr",
		"title" => "Tax Debit",
		"field_desc" => "Tax Debit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"tax_cr" => array(
		"field_type" => "tax_cr",
		"title" => "Tax Credit",
		"field_desc" => "Tax Credit (Example: 99.99).",
		"field_label_type" => "view",
		"default_value" => "99.99"
	),
	"purchase_account_code" => array(
		"field_type" => "purchase_account_code",
		"title" => "Purchase Account Code",
		"field_desc" => "Account Code (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
	"purchase_account_name" => array(
		"field_type" => "purchase_account_name",
		"title" => "Purchase Account Name",
		"field_desc" => "Account Name (Example: ABCxxxxxxx).",
		"field_label_type" => "view",
		"default_value" => "ABCxxxxxxx"
	),
);

$row_format_field = array(
	"cash_sales" => array(
		"single_line" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"ref_no",
			"date",
			"tax_code",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"customer_code",
			"customer_name",
			"branch_code",
			"tax_rate",
			"payment_amt1",
			"payment_type1",
			"payment_amt2",
			"payment_type2",
			"payment_amt3",
			"payment_type3",
			"total_tax_amount",
			"sub_total",
			"grand_total",
			"rounding_adj",
			"batchno",
			"credit_term",
			"tax_gl_code",
			"tax_gl_name",
			"uom",
			"qty",
			"second_tax_code"
		),
		"two_row" => array(
			"open_field",
			"account_code",
			"account_name",
			"ref_no",
			"date",
			"tax_code",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"customer_code",
			"customer_name",
			"branch_code",
			"tax_rate",
			"terms",
			"total_tax_amount",
			"sub_total",
			"grand_total",
			"uom",
			"qty",
			"second_tax_code"
		),
		"no_repeat_master" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"ref_no",
			"date",
			"tax_code",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"customer_code",
			"customer_name",
			"branch_code",
			"tax_rate",
			"payment_amt1",
			"payment_type1",
			"payment_amt2",
			"payment_type2",
			"payment_amt3",
			"payment_type3",
			"total_tax_amount",
			"sub_total",
			"grand_total",
			"rounding_adj",
			"batchno",
			"credit_term",
			"tax_gl_code",
			"tax_gl_name",
			"uom",
			"qty",
			"second_tax_code",
			"credit",
			"debit",
			"taxable_dr",
			"taxable_cr",
			"tax_dr",
			"tax_cr"
		)
	),
	"payment" => array(
		"single_line" => array(
			"open_field",
			"account_code",
			"doc_no",
			"date",
			"selling_price_after_gst",
			"customer_code",
			"payment_description"
		)
	),
	"dn_purchase" => array(
		"single_line" => array(
			"open_field",
			"inv_no",
			"date",
			"customer_code",
			"customer_name",
			"currency_code",
			"currency_rate",
			"batchno",
			"credit_term",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"tax_code",
			"account_code",
			"account_name",
			"reason",
			"sub_total",
			"total_tax_amount",
			"grand_total",
			"purchase_tax_code",
			"purchase_tax_name",
			"uom",
			"qty",
			"branch_code",
			"tax_rate",
			"second_tax_code"
		),
		"two_row" => array(
			"open_field",
			"inv_no",
			"date",
			"customer_code",
			"customer_name",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"tax_code",
			"account_code",
			"account_name",
			"reason",
			"sub_total",
			"total_tax_amount",
			"grand_total",
			"terms",
			"uom",
			"qty",
			"tax_rate",
			"second_tax_code"
		),
		"ledger_format" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"inv_seq_num",
			"date",
			"batchno",
			"amount",
			"debit",
			"credit",
			"fx_amount",
			"fx_debit",
			"fx_credit",
			"fx_rate",
			"currency_code",
			"tax_code",
			"taxable",
			"fx_taxable",
			"seq_num",
			"total_amount",
			"bill_type",
			"tax_rate",
			"second_tax_code"
		)
	),
	"purchase" => array(
		"single_line" => array(
			"open_field",
			"purchase_account_code",
			"purchase_account_name",
			"customer_code",
			"inv_no",
			"date",
			"batchno",
			"vendor_terms",
			"sub_total",
			"total_tax_amount",
			"grand_total",
			"selling_price_before_gst",
			"tax_code",
			"tax_amount",
			"selling_price_after_gst",
			"gl_code",
			"customer_name",
			"gl_name",
			"ref_no",
			"department",
			"branch_code",
			"purchase_tax_code",
			"purchase_tax_name",
			"uom",
			"qty",
			"tax_rate",
			"second_tax_code"
		),
		"two_row" => array(
			"open_field",
			"purchase_account_code",
			"purchase_account_name",
			"customer_code",
			"inv_no",
			"date",
			"batchno",
			"vendor_terms",
			"sub_total",
			"total_tax_amount",
			"grand_total",
			"selling_price_before_gst",
			"tax_code",
			"tax_amount",
			"selling_price_after_gst",
			"gl_code",
			"customer_name",
			"gl_name",
			"uom",
			"qty",
			"tax_rate",
			"second_tax_code"
		),
		"ledger_format" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"inv_seq_num",
			"date",
			"batchno",
			"amount",
			"debit",
			"credit",
			"fx_amount",
			"fx_debit",
			"fx_credit",
			"tax_code",
			"taxable",
			"fx_taxable",	
			"seq_num",
			"total_amount",
			"bill_type",
			"tax_rate",
			"second_tax_code"
		)
	),
	"credit_sales" => array(
		"single_line" => array(
			"open_field",
			"inv_no",
			"date",
			"customer_code",
			"customer_name",
			"terms",
			"currency_rate",
			"account_code",
			"account_name",
			"uom",
			"qty",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"suomqty",
			"tax_code",
			"foreign_currency_code",
			"sub_total",
			"total_tax_amount",
			"grand_total",
			"tax_gl_code",
			"tax_gl_name",
			"batchno",
			"branch_code",
			"payment_type1",
			"payment_type2",
			"payment_type3",
			"payment_amt1",
			"payment_amt2",
			"payment_amt3",
			"tax_rate",
			"second_tax_code"
		),
		"two_row" => array(
			"open_field",
			"inv_no",
			"date",
			"customer_code",
			"customer_name",
			"terms",
			"currency_rate",
			"account_code",
			"account_name",
			"uom",
			"qty",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"suomqty",
			"tax_code",
			"tax_rate",
			"second_tax_code"
		),
		"ledger_format" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"inv_seq_num",
			"date",
			"ref_no",
			"batchno",
			"amount",
			"debit",
			"credit",
			"fx_amount",
			"fx_debit",
			"fx_credit",
			"fx_rate",
			"currency_code",
			"tax_code",
			"taxable",
			"seq_num",
			"fx_taxable",
			"bill_type",
			"desp2",
			"tax_rate",
			"second_tax_code"
		)
	),
	"cn_sales" => array(
		"single_line" => array(
			"open_field",
			"customer_code",
			"customer_name",
			"doc_no",
			"date",
			"currency_code",
			"currency_rate",
			"batchno",
			"terms",
			"total_tax_amount",
			"sub_total",
			"grand_total",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"tax_gl_code",
			"tax_gl_name",
			"reason_code",
			"ref_no",
			"account_code",
			"account_name",
			"tax_code",
			"tax_rate",
			"branch_code",
			"reason",
			"uom",
			"qty",
			"second_tax_code"
		),
		"two_row" => array(
			"open_field",
			"customer_code",
			"customer_name",
			"doc_no",
			"date",
			"currency_code",
			"currency_rate",
			"batchno",
			"terms",
			"total_tax_amount",
			"sub_total",
			"grand_total",
			"selling_price_before_gst",
			"tax_amount",
			"selling_price_after_gst",
			"tax_gl_code",
			"tax_gl_name",
			"reason_code",
			"ref_no",
			"account_code",
			"account_name",
			"tax_code",
			"tax_rate",
			"branch_code",
			"reason",
			"uom",
			"qty",
			"second_tax_code"
		),
		"ledger_format" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"inv_seq_num",
			"date",
			"ref_no",
			"batchno",
			"amount",
			"debit",
			"credit",
			"fx_amount",
			"fx_debit",
			"fx_credit",
			"fx_rate",
			"currency_code",
			"tax_code",
			"taxable",
			"seq_num",
			"fx_taxable",
			"bill_type",
			"desp2",
			"desp3",
			"tax_rate",
			"second_tax_code"
		)
	),
	"cash_sales_cn" => array(
		"ledger_format" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"inv_seq_num",
			"date",
			"batchno",
			"amount",
			"debit",
			"credit",
			"fx_amount",
			"fx_debit",
			"fx_credit",
			"fx_rate",
			"currency_code",
			"tax_code",
			"taxable",
			"fx_taxable",
			"seq_num",
			"total_amount",
			"bill_type",
			"tax_rate",
			"second_tax_code"
		)
	),
	"sales_n_cn" => array(
		"ledger_format" => array(
			"open_field",
			"account_code",
			"account_name",
			"doc_no",
			"inv_seq_num",
			"date",
			"batchno",
			"amount",
			"debit",
			"credit",
			"fx_amount",
			"fx_debit",
			"fx_credit",
			"fx_rate",
			"currency_code",
			"tax_code",
			"taxable",
			"fx_taxable",
			"seq_num",
			"total_amount",
			"bill_type",
			"ref_no",
			"tax_rate",
			"second_tax_code"
		)
	)
);

$data_type_option = array(
	"cash_sales" => "Cash Sales",
	"cn_sales" => "Sales Credit Note",
	"purchase" => "Purchase",
	"dn_purchase" => "Purchase Debit Note",
	"payment" => "Payment",
	"cash_sales_cn" => "Cash Sales & Credit Note",
	"sales_n_cn" => "Sales & Credit Note",
	"credit_sales" => "Credit Sales"
);

if($config['consignment_modules']){
	$data_type_list = array (
		"single_line" => array(
			"cn_sales",
			"dn_purchase",
			"credit_sales"
		),
		"two_row" => array(
			"cn_sales",
			"dn_purchase",
			"credit_sales"
		),
		"ledger_format" => array(
			"dn_purchase"
		)
	);
}else{
	$data_type_list = array (
		"single_line" => array(
			"cash_sales",
			"cn_sales",
			"purchase",
			"dn_purchase",
			"payment",
			"credit_sales"
		),
		"two_row" => array(
			"cash_sales",
			"cn_sales",
			"purchase",
			"dn_purchase",
			"credit_sales"
		),
		"ledger_format" => array(
			"cash_sales_cn",
			"sales_n_cn",
			"purchase",
			"dn_purchase"
		),
		"no_repeat_master" => array(
			"cash_sales"
		)
	);
}

$file_format_list = array (
	"txt" => "TXT",
	"csv" => "CSV"
);

function getList(){
	return $data_type_list;
}

function search_export_schedule($form){
	global $con;
	list($format_branch_id, $format_id) = explode("-",$form['export_format']);
	$con->sql_query($a="select * from custom_acc_export
					where active=1
					and branch_id=".ms($form['branch_id'])."
					and ((".ms($form['date_from'])." between date_from and date_to) or (".ms($form['date_to'])." between date_from and date_to))
					and format_id=".mi($format_id) . " and format_branch_id = " . mi($format_branch_id));
	return $con->sql_fetchassoc();
}

function create_export_schedule($form){
	global $con;
	$branch_id = $form['branch_id'];
	$upd=array();
	$upd['user_id']=$form['user_id'];
	$upd['branch_id']=$branch_id;
	$upd['date_from']=$form['date_from'];
	$upd['date_to']=$form['date_to'];
	list($upd['format_branch_id'], $upd["format_id"]) = explode("-",$form['export_format']);
	$upd['status']="Pending";
	$upd['active']=1;
	$upd['generate_on']="CURRENT_TIMESTAMP";

	$con->sql_query("insert into custom_acc_export ".mysql_insert_by_field($upd));
	$id=$con->sql_nextid();
	$ret['id'] = $id;
	$ret['branch_id'] = $branch_id;
	update_export_schedule($id,"batchno",generate_batchno($id, $form), $branch_id);

	return $ret;
}


function update_export_schedule($id,$field,$value=null, $branch_id){
	global $con, $error_file;

	if(is_array($field)){
		$upd=$field;
	}
	else{
		$upd=array();
		$upd[$field]=$value;
	}
	$con->sql_query("update custom_acc_export set ".mysql_update_by_field($upd)." where id=".mi($id) . " and branch_id = " . mi($branch_id),true,false);
}

function generate_batchno($id, $form){
	global $con;

	$branch_id=sprintf("%03s",$form['branch_id']);
	$number=sprintf("%05s",$id);
	$batchno=$branch_id."".$number;
	
	return $batchno;
}

function load_export_schedule($id, $branch_id){
	global $con;

	$con->sql_query("select cae.*, caef.title, caef.data_type, caef.file_format, 
	caef.delimiter, caef.row_format, caef.date_format, caef.time_format, caef.header_column, caef.data_column
	from custom_acc_export cae
	left join custom_acc_export_format caef on cae.format_id = caef.id and cae.format_branch_id = caef.branch_id
	where cae.id=".mi($id) . " and cae.branch_id= " . mi($branch_id));
	
	return $con->sql_fetchassoc();
}

function get_batchno($form){
	global $con;

	$result=array();

	$sql="select * from custom_acc_export
		where active=1
		and branch_id=".ms($form['branch_id'])."
		and format_id=".ms($form['format_id'])."
		and completed=1
		and ((".ms($form['date_from'])." BETWEEN date_from AND date_to) or (".ms($form['date_to'])." BETWEEN date_from AND date_to))
		order by end_time";
	$ret=$con->sql_query($sql);
	
	while($r=$con->sql_fetchassoc($ret)){
		$result[]=array('date_from'=>$r['date_from'],'date_to'=>$r['date_to'],'batchno'=>$r['batchno']);
	}
	$con->sql_freeresult();
	$result[]=array('date_from'=>$form['date_from'],'date_to'=>$form['date_to'],'batchno'=>$form['batchno']);
	return $result;
}

function load_account_file($branch_id = 1){
	global $con;
	$accountings = array();
	
	if($branch_id!=1){
		$ret=$con->sql_query("select count(*) from custom_acc_export_acc_setting where branch_id=".mi($branch_id));
		$count=$con->sql_fetchfield(0);

		if($count>0) $accountings['use_own_settings']=1;
		else $branch_id=1;
	}
	
	$ret=$con->sql_query("select * from custom_acc_export_acc_setting where branch_id=". mi($branch_id));

	while($r=$con->sql_fetchassoc($ret)){
		$accountings[$r['code']]['account_code']=$r['account_code'];
		$accountings[$r['code']]['account_name']=$r['account_name'];
	}
	$con->sql_freeresult();
	
	$ret=$con->sql_query("select caegs.*, gst.code from custom_acc_export_gst_setting caegs left join gst on gst.id = caegs.gst_id where caegs.branch_id =" . mi($branch_id));

	while($r=$con->sql_fetchassoc($ret)){
		$accountings[$r['code']]['key']=$r['code'];
		$accountings[$r['code']]['account_code']=$r['account_code'];
		$accountings[$r['code']]['account_name']=$r['account_name'];
	}
	$con->sql_freeresult();
	return $accountings;
}

function check_finalised_sales($branch_id, $date_from, $date_to){
	global $con;
	
	$is_finalised = true;
	$sql=$con->sql_query("select count(*) as num from pos
		left join pos_finalized pf on pos.branch_id = pf.branch_id and pos.date = pf.date
		where pos.branch_id = " . mi($branch_id) . " and pos.date >= " . ms($date_from) . " and pos.date <= " . ms($date_to) . "
		and finalized = 0 and cancel_status = 0");
	$count = $con->sql_fetchrow($sql);
	$con->sql_freeresult($sql);
	if($count["num"] > 0){
		$is_finalised = false;
	}
	unset($count);
	return $is_finalised;
}

function check_pending_task(){
	global $con;
	$is_running = false;
	$sql=$con->sql_query("select count(*) as num from custom_acc_export
					where active = 1 and started = 1 and completed = 0");
	$count = $con->sql_fetchrow($sql);
	$con->sql_freeresult($sql);
	if($count["num"] > 0){
		$is_running = true;
	}
	return $is_running;
}

function insert_prelist_templates(){
	global $con;
	
	$latest_ver = 4;
	$prelist_templates = array(
		"0" => Array
			(
				"id" => "1",
				"title" => "Million Cash Sales and Credit Note Template",
				"data_type" => "cash_sales_cn",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "ledger_format",
				"date_format" => "d/m/y",
				"time_format" => "H:i",
				"header_column" => Array
					(
						"0" => "accno",
						"1" => "doc_type",
						"2" => "doc_no",
						"3" => "seq",
						"4" => "doc_date",
						"5" => "refno",
						"6" => "refno2",
						"7" => "refno3",
						"8" => "desp",
						"9" => "desp2",
						"10" => "desp3",
						"11" => "desp4",
						"12" => "amount",
						"13" => "debit",
						"14" => "credit",
						"15" => "fx_amount",
						"16" => "fx_debit",
						"17" => "fx_credit",
						"18" => "fx_rate",
						"19" => "curr_code",
						"20" => "taxcode",
						"21" => "taxable",
						"22" => "fx_taxable",
						"23" => "link_seq",
						"24" => "billtype",
						"25" => "remark1",
						"26" => "remark2",
						"27" => "batchno",
						"28" => "projcode",
						"29" => "deptcode",
						"30" => "accmgr_id",
						"31" => "cheque_no"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"2" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "doc_type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "GL"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"4" => Array
									(
										"field_label_type" => "inv_seq_num",
										"field_type" => "inv_seq_num",
										"org_field_label" => "Invoice Sequence Number",
										"field_label" => "Invoice Sequence Number",
										"field_value" => "0"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "refno3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "amount",
										"org_field_label" => "Amount",
										"field_label" => "Amount"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "debit",
										"org_field_label" => "Debit",
										"field_label" => "Debit"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit",
										"org_field_label" => "Credit",
										"field_label" => "Credit"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_amount",
										"org_field_label" => "FX Amount",
										"field_label" => "FX Amount"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_debit",
										"org_field_label" => "FX Debit",
										"field_label" => "FX Debit"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_credit",
										"org_field_label" => "FX Credit",
										"field_label" => "FX Credit"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_rate",
										"org_field_label" => "FX Rate",
										"field_label" => "FX Rate"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_code",
										"org_field_label" => "Currency Code",
										"field_label" => "Currency Code"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "taxable",
										"org_field_label" => "Taxable",
										"field_label" => "Taxable"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_taxable",
										"org_field_label" => "FX Taxable",
										"field_label" => "FX Taxable"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "link_seq",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "bill_type",
										"org_field_label" => "Bill Type",
										"field_label" => "Bill Type"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "batchno",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "projcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "deptcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "accmgr_id",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "cheque_no",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0001"
			),

		"1" => Array
			(
				"id" => "2",
				"title" => "Million Sales & Credit Note Template",
				"data_type" => "sales_n_cn",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "ledger_format",
				"date_format" => "d/m/y",
				"time_format" => "H:i",
				"header_column" => Array
					(
						"0" => "accno",
						"1" => "doc_type",
						"2" => "doc_no",
						"3" => "seq",
						"4" => "doc_date",
						"5" => "refno",
						"6" => "refno2",
						"7" => "refno3",
						"8" => "desp",
						"9" => "desp2",
						"10" => "desp3",
						"11" => "desp4",
						"12" => "amount",
						"13" => "debit",
						"14" => "credit",
						"15" => "fx_amount",
						"16" => "fx_debit",
						"17" => "fx_credit",
						"18" => "fx_rate",
						"19" => "curr_code",
						"20" => "taxcode",
						"21" => "taxable",
						"22" => "fx_taxable",
						"23" => "link_seq",
						"24" => "billtype",
						"25" => "remark1",
						"26" => "remark2",
						"27" => "batchno",
						"28" => "projcode",
						"29" => "deptcode",
						"30" => "accmgr_id",
						"31" => "cheque_no"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"2" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "doc_type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "GL"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"4" => Array
									(
										"field_label_type" => "inv_seq_num",
										"field_type" => "inv_seq_num",
										"org_field_label" => "Invoice Sequence Number",
										"field_label" => "Invoice Sequence Number",
										"field_value" => "0"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "refno3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "amount",
										"org_field_label" => "Amount",
										"field_label" => "Amount"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "debit",
										"org_field_label" => "Debit",
										"field_label" => "Debit"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit",
										"org_field_label" => "Credit",
										"field_label" => "Credit"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_amount",
										"org_field_label" => "FX Amount",
										"field_label" => "FX Amount"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_debit",
										"org_field_label" => "FX Debit",
										"field_label" => "FX Debit"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_credit",
										"org_field_label" => "FX Credit",
										"field_label" => "FX Credit"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_rate",
										"org_field_label" => "FX Rate",
										"field_label" => "FX Rate"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_code",
										"org_field_label" => "Currency Code",
										"field_label" => "Currency Code"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "taxable",
										"org_field_label" => "Taxable",
										"field_label" => "Taxable"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_taxable",
										"org_field_label" => "FX Taxable",
										"field_label" => "FX Taxable"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "link_seq",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "bill_type",
										"org_field_label" => "Bill Type",
										"field_label" => "Bill Type"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "batchno",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "projcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "deptcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "accmgr_id",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "cheque_no",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0002"
			),

		"2" => Array
			(
				"id" => "3",
				"title" => "Million Purchase Template",
				"data_type" => "purchase",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "ledger_format",
				"date_format" => "d/m/y",
				"time_format" => "H:i",
				"header_column" => Array
					(
						"0" => "accno",
						"1" => "doc_type",
						"2" => "doc_no",
						"3" => "seq",
						"4" => "doc_date",
						"5" => "refno",
						"6" => "refno2",
						"7" => "refno3",
						"8" => "desp",
						"9" => "desp2",
						"10" => "desp3",
						"11" => "desp4",
						"12" => "amount",
						"13" => "debit",
						"14" => "credit",
						"15" => "fx_amount",
						"16" => "fx_debit",
						"17" => "fx_credit",
						"18" => "fx_rate",
						"19" => "curr_code",
						"20" => "taxcode",
						"21" => "taxable",
						"22" => "fx_taxable",
						"23" => "link_seq",
						"24" => "billtype",
						"25" => "remark1",
						"26" => "remark2",
						"27" => "batchno",
						"28" => "projcode",
						"29" => "deptcode",
						"30" => "accmgr_id",
						"31" => "cheque_no"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"2" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "doc_type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "GL"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"4" => Array
									(
										"field_label_type" => "inv_seq_num",
										"field_type" => "inv_seq_num",
										"org_field_label" => "Invoice Sequence Number",
										"field_label" => "Invoice Sequence Number",
										"field_value" => "0"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "refno3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "amount",
										"org_field_label" => "Amount",
										"field_label" => "Amount"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "debit",
										"org_field_label" => "Debit",
										"field_label" => "Debit"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit",
										"org_field_label" => "Credit",
										"field_label" => "Credit"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_amount",
										"org_field_label" => "FX Amount",
										"field_label" => "FX Amount"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_debit",
										"org_field_label" => "FX Debit",
										"field_label" => "FX Debit"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_credit",
										"org_field_label" => "FX Credit",
										"field_label" => "FX Credit"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "fx_rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "curr_code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MYR"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "taxable",
										"org_field_label" => "Taxable",
										"field_label" => "Taxable"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_taxable",
										"org_field_label" => "FX Taxable",
										"field_label" => "FX Taxable"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "link_seq",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "bill_type",
										"org_field_label" => "Bill Type",
										"field_label" => "Bill Type"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "batchno",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "projcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "deptcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "accmgr_id",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "cheque_no",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0003"
			),

		"3" => Array
			(
				"id" => "4",
				"title" => "Million Purchase Debit Note Template",
				"data_type" => "dn_purchase",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "ledger_format",
				"date_format" => "d/m/y",
				"time_format" => "H:i",
				"header_column" => Array
					(
						"0" => "accno",
						"1" => "doc_type",
						"2" => "doc_no",
						"3" => "seq",
						"4" => "doc_date",
						"5" => "refno",
						"6" => "refno2",
						"7" => "refno3",
						"8" => "desp",
						"9" => "desp2",
						"10" => "desp3",
						"11" => "desp4",
						"12" => "amount",
						"13" => "debit",
						"14" => "credit",
						"15" => "fx_amount",
						"16" => "fx_debit",
						"17" => "fx_credit",
						"18" => "fx_rate",
						"19" => "curr_code",
						"20" => "taxcode",
						"21" => "taxable",
						"22" => "fx_taxable",
						"23" => "link_seq",
						"24" => "billtype",
						"25" => "remark1",
						"26" => "remark2",
						"27" => "batchno",
						"28" => "projcode",
						"29" => "deptcode",
						"30" => "accmgr_id",
						"31" => "cheque_no"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"2" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "doc_type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "GL"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"4" => Array
									(
										"field_label_type" => "inv_seq_num",
										"field_type" => "inv_seq_num",
										"org_field_label" => "Invoice Sequence Number",
										"field_label" => "Invoice Sequence Number",
										"field_value" => "0"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "refno3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "desp4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "amount",
										"org_field_label" => "Amount",
										"field_label" => "Amount"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "debit",
										"org_field_label" => "Debit",
										"field_label" => "Debit"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit",
										"org_field_label" => "Credit",
										"field_label" => "Credit"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_amount",
										"org_field_label" => "FX Amount",
										"field_label" => "FX Amount"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_debit",
										"org_field_label" => "FX Debit",
										"field_label" => "FX Debit"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_credit",
										"org_field_label" => "FX Credit",
										"field_label" => "FX Credit"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_rate",
										"org_field_label" => "FX Rate",
										"field_label" => "FX Rate"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_code",
										"org_field_label" => "Currency Code",
										"field_label" => "Currency Code"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "taxable",
										"org_field_label" => "Taxable",
										"field_label" => "Taxable"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "fx_taxable",
										"org_field_label" => "FX Taxable",
										"field_label" => "FX Taxable"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "link_seq",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "bill_type",
										"org_field_label" => "Bill Type",
										"field_label" => "Bill Type"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "remark2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "batchno",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "projcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "deptcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "accmgr_id",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "cheque_no",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0004"
			),

		"4" => Array
			(
				"id" => "5",
				"title" => "TJH Cash Sales Template",
				"data_type" => "cash_sales",
				"file_format" => "txt",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "CustomerCode",
						"1" => "VoucherNo",
						"2" => "Reference",
						"3" => "Date",
						"4" => "Description",
						"5" => "Category",
						"6" => "SalesmanCode",
						"7" => "Tax",
						"8" => "Dept",
						"9" => "Job",
						"10" => "ItemAmount",
						"11" => "TaxAmount",
						"12" => "TotalAmount",
						"13" => "Index",
						"14" => "TaxRate",
						"15" => "PaymentType",
						"16" => "PaymentAmt",
						"17" => "PaymentType2",
						"18" => "PaymentAmt2",
						"19" => "PaymentType3",
						"20" => "PaymentAmt3",
						"21" => "Unit",
						"22" => "Quantity",
						"23" => "Type",
						"24" => "CashBillVoucherNo"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesmanCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Dept",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Index",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_rate",
										"org_field_label" => "Tax Rate",
										"field_label" => "Tax Rate"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_type1",
										"org_field_label" => "Payment Type",
										"field_label" => "Payment Type"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_amt1",
										"org_field_label" => "Payment Amount",
										"field_label" => "Payment Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_type2",
										"org_field_label" => "Payment Type 2",
										"field_label" => "Payment Type 2"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_amt2",
										"org_field_label" => "Payment Amount 2",
										"field_label" => "Payment Amount 2"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_type3",
										"org_field_label" => "Payment Type 3",
										"field_label" => "Payment Type 3"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_amt3",
										"org_field_label" => "Payment Amount 3",
										"field_label" => "Payment Amount 3"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "cashbill"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0005"
			),

		"5" => Array
			(
				"id" => "6",
				"title" => "TJH Sales Credit Notes Template",
				"data_type" => "cn_sales",
				"file_format" => "txt",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "CustomerCode",
						"1" => "VoucherNo",
						"2" => "Reference",
						"3" => "Date",
						"4" => "Description",
						"5" => "Category",
						"6" => "SalesmanCode",
						"7" => "Tax",
						"8" => "Dept",
						"9" => "Job",
						"10" => "ItemAmount",
						"11" => "TaxAmount",
						"12" => "TotalAmount",
						"13" => "Index",
						"14" => "TaxRate",
						"15" => "PaymentType",
						"16" => "PaymentAmt",
						"17" => "PaymentType2",
						"18" => "PaymentAmt2",
						"19" => "PaymentType3",
						"20" => "PaymentAmt3",
						"21" => "Unit",
						"22" => "Quantity",
						"23" => "Type",
						"24" => "CashBillVoucherNo"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Credit Notes (Goods Return)"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesmanCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Dept",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Index",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_rate",
										"org_field_label" => "Tax Rate",
										"field_label" => "Tax Rate"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PaymentType",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PaymentAmt",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"18" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PaymentType2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PaymentAmt2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PaymentType3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PaymentAmt3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "cn"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0006"
			),

		"6" => Array
			(
				"id" => "7",
				"title" => "TJH Credit Sales Template",
				"data_type" => "credit_sales",
				"file_format" => "txt",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "CustomerCode",
						"1" => "VoucherNo",
						"2" => "Reference",
						"3" => "Date",
						"4" => "Description",
						"5" => "Category",
						"6" => "SalesmanCode",
						"7" => "Tax",
						"8" => "Dept",
						"9" => "Job",
						"10" => "ItemAmount",
						"11" => "TaxAmount",
						"12" => "TotalAmount",
						"13" => "Index",
						"14" => "TaxRate",
						"15" => "PaymentType",
						"16" => "PaymentAmt",
						"17" => "PaymentType2",
						"18" => "PaymentAmt2",
						"19" => "PaymentType3",
						"20" => "PaymentAmt3",
						"21" => "Unit",
						"22" => "Quantity",
						"23" => "Type",
						"24" => "CashBillVoucherNo"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Category",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesmanCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Dept",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Index",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_rate",
										"org_field_label" => "Tax Rate",
										"field_label" => "Tax Rate"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_type1",
										"org_field_label" => "Payment Type",
										"field_label" => "Payment Type"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_amt1",
										"org_field_label" => "Payment Amount",
										"field_label" => "Payment Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_type2",
										"org_field_label" => "Payment Type 2",
										"field_label" => "Payment Type 2"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_amt2",
										"org_field_label" => "Payment Amount 2",
										"field_label" => "Payment Amount 2"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_type3",
										"org_field_label" => "Payment Type 3",
										"field_label" => "Payment Type 3"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_amt3",
										"org_field_label" => "Payment Amount 3",
										"field_label" => "Payment Amount 3"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"23" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "ar"
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CashBillVoucherNo",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0007"
			),

		"7" => Array
			(
				"id" => "8",
				"title" => "MrAccounting Cash Sales Template",
				"data_type" => "cash_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "*CustomerCode",
						"1" => "VoucherNo",
						"2" => "*ReferenceNo",
						"3" => "*Date",
						"4" => "Description",
						"5" => "*Category/GLCode",
						"6" => "SalesmanCode",
						"7" => "*Tax",
						"8" => "Dept",
						"9" => "Job",
						"10" => "*ItemAmount",
						"11" => "*TaxAmount",
						"12" => "*TotalAmount"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesmanCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Dept",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0008"
			),

		"8" => Array
			(
				"id" => "9",
				"title" => "MrAccounting Payment Template",
				"data_type" => "payment",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "*Customer Code",
						"1" => "Voucher No",
						"2" => "Bank Code",
						"3" => "*Description",
						"4" => "*Receipt Date",
						"5" => "*Cheque No",
						"6" => "*Amount"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "payment_description",
										"org_field_label" => "Description",
										"field_label" => "Description"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Cheque No",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0009"
			),

		"9" => Array
			(
				"id" => "10",
				"title" => "MrAccounting Credit Sales Template",
				"data_type" => "credit_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "*CustomerCode",
						"1" => "VoucherNo",
						"2" => "*ReferenceNo",
						"3" => "*Date",
						"4" => "Description",
						"5" => "*Category/GLCode",
						"6" => "SalesmanCode",
						"7" => "*Tax",
						"8" => "Dept",
						"9" => "Job",
						"10" => "*ItemAmount",
						"11" => "*TaxAmount",
						"12" => "*TotalAmount"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "*Category/GLCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesmanCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Dept",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0010"
			),

		"10" => Array
			(
				"id" => "11",
				"title" => "MrAccounting Sales Credit Notes Template",
				"data_type" => "cn_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "*CustomerCode",
						"1" => "VoucherNo",
						"2" => "*ReferenceNo",
						"3" => "*Date",
						"4" => "Description",
						"5" => "*Category/GLCode",
						"6" => "SalesmanCode",
						"7" => "*Tax",
						"8" => "Dept",
						"9" => "Job",
						"10" => "*ItemAmount",
						"11" => "*TaxAmount",
						"12" => "*TotalAmount"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Credit Notes (Goods Return)"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesmanCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Dept",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0011"
			),

		"11" => Array
			(
				"id" => "12",
				"title" => "MrAccounting Purchase Template",
				"data_type" => "purchase",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "*VoucherNumber",
						"1" => "*SuppCode",
						"2" => "*Reference",
						"3" => "*Date",
						"4" => "Description",
						"5" => "*GLCode",
						"6" => "Department",
						"7" => "Job",
						"8" => "*TaxType",
						"9" => "*ItemAmount",
						"10" => "*TaxAmount",
						"11" => "*TotalAmount"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Purchase"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "gl_code",
										"org_field_label" => "GL Code",
										"field_label" => "GL Code"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Department",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"10" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0012"
			),

		"12" => Array
			(
				"id" => "13",
				"title" => "MrAccounting Purchase Debit Note Template",
				"data_type" => "dn_purchase",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "*VoucherNumber",
						"1" => "*SuppCode",
						"2" => "*Reference",
						"3" => "*Date",
						"4" => "Description",
						"5" => "*GLCode",
						"6" => "Department",
						"7" => "Job",
						"8" => "*TaxType",
						"9" => "*ItemAmount",
						"10" => "*TaxAmount",
						"11" => "*TotalAmount"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Debit Notes"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Department",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Job",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"10" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0013"
			),

		"13" => Array
			(
				"id" => "14",
				"title" => "Sage 50 Cash Sales Template",
				"data_type" => "cash_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "Invoice Type",
						"1" => "Customer Code",
						"2" => "Document Number",
						"3" => "Document Date",
						"4" => "Currency Code",
						"5" => "Exchange Rate",
						"6" => "Reference Number",
						"7" => "Delivery Mode Code",
						"8" => "Agent Code",
						"9" => "Credit Term Code",
						"10" => "Project Code",
						"11" => "Attention",
						"12" => "Address 1",
						"13" => "Delivery Address 1",
						"14" => "SI Total Line Discount",
						"15" => "SI Total Line Tax",
						"16" => "SI Sub Total",
						"17" => "SI Footer Discount %",
						"18" => "SI Footer Discount % 2",
						"19" => "SI Footer Discount % 3",
						"20" => "SI Footer Discount Total",
						"21" => "SI Footer Tax Code",
						"22" => "SI Total Footer Tax",
						"23" => "SI Rounding Adjustment",
						"24" => "SI Grand Total",
						"25" => "Item Code",
						"26" => "Item Description",
						"27" => "Item Location Code",
						"28" => "Quantity",
						"29" => "UOM Code",
						"30" => "Price",
						"31" => "Amount",
						"32" => "Line Discount Amount",
						"33" => "Line Tax Code",
						"34" => "Line Tax Amount",
						"35" => "Line Tax GL Account Code",
						"36" => "Line Total",
						"37" => "Line Sales GL Account Code",
						"38" => "SI Tax Type"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Invoice Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "2"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"40" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MYR"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Exchange Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Mode Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit_term",
										"org_field_label" => "Credit Term Account Code",
										"field_label" => "Credit Term Account Code"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Total Line Discount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "total_tax_amount",
										"org_field_label" => "Total Tax Amount",
										"field_label" => "Total Tax Amount"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "sub_total",
										"org_field_label" => "Sub Total",
										"field_label" => "Sub Total"
									),

								"18" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount %",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount %2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount %3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount Total",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"22" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Tax Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Total Footer Tax",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "view",
										"field_type" => "rounding_adj",
										"org_field_label" => "Rounding Adjustment",
										"field_label" => "Rounding Adjustment"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Location Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DEFAULT"
									),

								"29" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"30" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"31" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"32" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"34" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"35" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"36" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_gl_code",
										"org_field_label" => "Tax GL Account Code",
										"field_label" => "Tax GL Account Code"
									),

								"37" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"38" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Tax Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0014"
			),

		"14" => Array
			(
				"id" => "15",
				"title" => "Sage 50 Credit Sales Template",
				"data_type" => "credit_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "Invoice Type",
						"1" => "Customer Code",
						"2" => "Document Number",
						"3" => "Document Date",
						"4" => "Currency Code",
						"5" => "Exchange Rate",
						"6" => "Reference Number",
						"7" => "Delivery Mode Code",
						"8" => "Agent Code",
						"9" => "Credit Term Code",
						"10" => "Project Code",
						"11" => "Attention",
						"12" => "Address 1",
						"13" => "Delivery Address 1",
						"14" => "SI Total Line Discount",
						"15" => "SI Total Line Tax",
						"16" => "SI Sub Total",
						"17" => "SI Footer Discount %",
						"18" => "SI Footer Discount % 2",
						"19" => "SI Footer Discount % 3",
						"20" => "SI Footer Discount Total",
						"21" => "SI Footer Tax Code",
						"22" => "SI Total Footer Tax",
						"23" => "SI Rounding Adjustment",
						"24" => "SI Grand Total",
						"25" => "Item Code",
						"26" => "Item Description",
						"27" => "Item Location Code",
						"28" => "Quantity",
						"29" => "UOM Code",
						"30" => "Price",
						"31" => "Amount",
						"32" => "Line Discount Amount",
						"33" => "Line Tax Code",
						"34" => "Line Tax Amount",
						"35" => "Line Tax GL Account Code",
						"36" => "Line Total",
						"37" => "Line Sales GL Account Code",
						"38" => "SI Tax Type"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Invoice Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"40" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "XXX"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Exchange Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Mode Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "view",
										"field_type" => "terms",
										"org_field_label" => "Terms",
										"field_label" => "Terms"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Total Line Discount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "total_tax_amount",
										"org_field_label" => "Total Tax Amount",
										"field_label" => "Total Tax Amount"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "sub_total",
										"org_field_label" => "Sub Total",
										"field_label" => "Sub Total"
									),

								"18" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount %",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount %2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount %3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Discount Total",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"22" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Footer Tax Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Total Footer Tax",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "view",
										"field_type" => "rounding_adj",
										"org_field_label" => "Rounding Adjustment",
										"field_label" => "Rounding Adjustment"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Location Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DEFAULT"
									),

								"29" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"30" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"31" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"32" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"34" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"35" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"36" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_gl_code",
										"org_field_label" => "Tax GL Account Code",
										"field_label" => "Tax GL Account Code"
									),

								"37" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"38" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SI Tax Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0015"
			),

		"15" => Array
			(
				"id" => "16",
				"title" => "Sage 50 Sales Credit Notes Template",
				"data_type" => "cn_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "Customer Code",
						"1" => "Customer Name",
						"2" => "Document Number",
						"3" => "Document Date",
						"4" => "Currency Code",
						"5" => "Exchange Rate",
						"6" => "Reference Number",
						"7" => "Delivery Mode Code",
						"8" => "Agent Code",
						"9" => "Credit Term Code",
						"10" => "Project Code",
						"11" => "Attention",
						"12" => "Address 1",
						"13" => "Address 2",
						"14" => "Address 3",
						"15" => "Address 4",
						"16" => "City",
						"17" => "Postcode",
						"18" => "State Code",
						"19" => "Country Code",
						"20" => "Delivery Address - Attention",
						"21" => "Delivery Address 1",
						"22" => "SCN Total Line Discount",
						"23" => "SCN Total Line Tax",
						"24" => "SCN Sub Total",
						"25" => "SCN Rounding Adjustment",
						"26" => "SCN Grand Total",
						"27" => "Item Code",
						"28" => "Item Description",
						"29" => "Item Location Code",
						"30" => "Quantity",
						"31" => "UOM Code",
						"32" => "Price",
						"33" => "Amount",
						"34" => "Line Discount %",
						"35" => "Line Discount % 2",
						"36" => "Line Discount % 3",
						"37" => "Line Discount Amount",
						"38" => "Line Tax Code",
						"39" => "Line Tax Amount",
						"40" => "Line Tax GL Account Code",
						"41" => "Line Tax GL Account Name",
						"42" => "Line Total",
						"43" => "Line Sales GL Account Code",
						"44" => "Line Sales GL Account Name",
						"45" => "Line Reason Code",
						"46" => "Sales Invoice Number",
						"47" => "Line Discount GL Account Code",
						"48" => "Line Discount GL Account Name"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MYR"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_rate",
										"org_field_label" => "Currency Rate",
										"field_label" => "Currency Rate"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Mode Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "view",
										"field_type" => "terms",
										"org_field_label" => "Terms",
										"field_label" => "Terms"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "City",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"18" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Postcode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "State Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Country Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Address - Att",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"22" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SCN Total Line Discount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"24" => Array
									(
										"field_label_type" => "view",
										"field_type" => "total_tax_amount",
										"org_field_label" => "Total Tax Amount",
										"field_label" => "Total Tax Amount"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "sub_total",
										"org_field_label" => "Sub Total",
										"field_label" => "Sub Total"
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SCN Rounding Adjustment",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Location Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DEFAULT"
									),

								"31" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"32" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"33" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"34" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"35" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount %",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"36" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount %2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"37" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount %3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"38" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"39" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"40" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"41" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_gl_code",
										"org_field_label" => "Tax GL Account Code",
										"field_label" => "Tax GL Account Code"
									),

								"42" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_gl_name",
										"org_field_label" => "Tax GL Account Name",
										"field_label" => "Tax GL Account Name"
									),

								"43" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"44" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"45" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"46" => Array
									(
										"field_label_type" => "view",
										"field_type" => "reason_code",
										"org_field_label" => "Reason Code",
										"field_label" => "Reason Code"
									),

								"47" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"48" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Disc GL Acc Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"49" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Disc GL Acc Name",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0016"
			),

		"16" => Array
			(
				"id" => "17",
				"title" => "Sage 50 Purchase Template",
				"data_type" => "purchase",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "Invoice Type",
						"1" => "Is Imported Service (Y/N)",
						"2" => "Supplier Code",
						"3" => "Document Number",
						"4" => "Document Date",
						"5" => "Currency Code",
						"6" => "Exchange Rate",
						"7" => "Permit No",
						"8" => "Reference Number",
						"9" => "Delivery Mode Code",
						"10" => "Purchaser Code",
						"11" => "Credit Term Code",
						"12" => "Project Code",
						"13" => "Attention",
						"14" => "Address 1",
						"15" => "PI Sub Total",
						"16" => "PI Total Line Discount",
						"17" => "PI Total Line Tax",
						"18" => "PI Footer Discount %",
						"19" => "PI Footer Discount % 2",
						"20" => "PI Footer Discount % 3",
						"21" => "PI Total Footer Discount",
						"22" => "PI Footer Tax Code",
						"23" => "PI Total Footer Tax",
						"24" => "PI Rounding Adjustment",
						"25" => "PI Grand Total",
						"26" => "Item Code",
						"27" => "Item Description",
						"28" => "Item Location Code",
						"29" => "Quantity",
						"30" => "UOM Code",
						"31" => "Price",
						"32" => "Amount",
						"33" => "Line Discount Amount",
						"34" => "Line Tax Code",
						"35" => "Line Tax Amount",
						"36" => "Line Tax GL Account Code",
						"37" => "Line Total",
						"38" => "Line Purchase GL Account Code",
						"39" => "Supplier Name",
						"40" => "Line Purchase GL Account Name",
						"41" => "Line Tax GL Account Name"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Invoice Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"2" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Is Imported Service (Y/N)",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MYR"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Exchange Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Permit No",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Mode Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Purchaser Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "view",
										"field_type" => "vendor_terms",
										"org_field_label" => "Vendor Terms",
										"field_label" => "Vendor Terms"
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "sub_total",
										"org_field_label" => "Sub Total",
										"field_label" => "Sub Total"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Total Line Discount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "total_tax_amount",
										"org_field_label" => "Total Tax Amount",
										"field_label" => "Total Tax Amount"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Footer Discount %",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Footer Discount %2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Footer Discount %3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"22" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Total Footer Discount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Footer Tax Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Total Footer Tax",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PI Rounding Adjustment",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"26" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Purchase"
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Location Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "Text"
									),

								"30" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"31" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"32" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"33" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"34" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"35" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"36" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"37" => Array
									(
										"field_label_type" => "view",
										"field_type" => "purchase_tax_code",
										"org_field_label" => "Purchase Tax Account Code",
										"field_label" => "Purchase Tax Account Code"
									),

								"38" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"39" => Array
									(
										"field_label_type" => "view",
										"field_type" => "gl_code",
										"org_field_label" => "GL Code",
										"field_label" => "GL Code"
									),

								"40" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"41" => Array
									(
										"field_label_type" => "view",
										"field_type" => "gl_name",
										"org_field_label" => "GL Name",
										"field_label" => "GL Name"
									),

								"42" => Array
									(
										"field_label_type" => "view",
										"field_type" => "purchase_tax_name",
										"org_field_label" => "Purchase Tax Account Name",
										"field_label" => "Purchase Tax Account Name"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "2",
				"templates_type" => "arms_preset",
				"template_code" => "T0017"
			),

		"17" => Array
			(
				"id" => "18",
				"title" => "Sage 50 Purchase Debit Notes Template",
				"data_type" => "dn_purchase",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "single_line",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "Supplier Code",
						"1" => "Supplier Name",
						"2" => "Document Number",
						"3" => "Document Date",
						"4" => "Currency Code",
						"5" => "Exchange Rate",
						"6" => "Reference Number",
						"7" => "Credit Term Code",
						"8" => "Address 1",
						"9" => "Delivery Address 1",
						"10" => "PDN Sub Total",
						"11" => "PDN Total Line Discount",
						"12" => "PDN Total Line Tax",
						"13" => "PDN Rounding Adjustment",
						"14" => "PDN Grand Total",
						"15" => "Item Code",
						"16" => "Item Description",
						"17" => "Item Location Code",
						"18" => "Quantity",
						"19" => "UOM Code",
						"20" => "Price",
						"21" => "Amount",
						"22" => "Line Discount %",
						"23" => "Line Discount Amount",
						"24" => "Line Tax Code",
						"25" => "Line Tax Amount",
						"26" => "Line Tax GL Account Code",
						"27" => "Line Tax GL Account Name",
						"28" => "Line Total",
						"29" => "Line Purchase GL Account Code",
						"30" => "Line Purchase GL Account Name",
						"31" => "Line Reason Code"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_code",
										"org_field_label" => "Currency Code",
										"field_label" => "Currency Code",
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_rate",
										"org_field_label" => "Currency Rate",
										"field_label" => "Currency Rate"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit_term",
										"org_field_label" => "Credit Term Account Code",
										"field_label" => "Credit Term Account Code"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"11" => Array
									(
										"field_label_type" => "view",
										"field_type" => "sub_total",
										"org_field_label" => "Sub Total",
										"field_label" => "Sub Total"
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PDN Total Line Discount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "total_tax_amount",
										"org_field_label" => "Total Tax Amount",
										"field_label" => "Total Tax Amount"
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "PDN Rounding Adjustment",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "grand_total",
										"org_field_label" => "Grand Total",
										"field_label" => "Grand Total"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "TEXT"
									),

								"18" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Location Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DEFAULT"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "qty",
										"org_field_label" => "Quantity",
										"field_label" => "Quantity"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "uom",
										"org_field_label" => "UOM",
										"field_label" => "UOM"
									),

								"21" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount %",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0"
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Line Discount Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"26" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"27" => Array
									(
										"field_label_type" => "view",
										"field_type" => "purchase_tax_code",
										"org_field_label" => "Purchase Tax Account Code",
										"field_label" => "Purchase Tax Account Code"
									),

								"28" => Array
									(
										"field_label_type" => "view",
										"field_type" => "purchase_tax_name",
										"org_field_label" => "Purchase Tax Account Name",
										"field_label" => "Purchase Tax Account Name"
									),

								"29" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"30" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"31" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"32" => Array
									(
										"field_label_type" => "view",
										"field_type" => "reason",
										"org_field_label" => "Reason",
										"field_label" => "Reason"
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0018"
			),

		"18" => Array
			(
				"id" => "19",
				"title" => "SQL Accounting Cash Sales Template",
				"data_type" => "cash_sales",
				"file_format" => "txt",
				"delimiter" => ";",
				"row_format" => "two_row",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => "",
				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MASTER"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc No Ex",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Area",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "terms",
										"org_field_label" => "Terms",
										"field_label" => "Terms"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Cancelled",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Validity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Term",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CC",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Branch Name",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "BILLING"
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"34" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"35" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"36" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"37" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"38" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"40" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Method",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"41" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Cheque Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"42" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Bank Charge",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"43" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"44" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

						"detail" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DETAIL"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Location",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Quantity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "UOM",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Sum Qty",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Disc",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Printable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"22" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0019"
			),

		"19" => Array
			(
				"id" => "20",
				"title" => "SQL Accounting Credit Sales Template",
				"data_type" => "credit_sales",
				"file_format" => "txt",
				"delimiter" => ";",
				"row_format" => "two_row",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => "",
				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MASTER"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc No Ex",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Area",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "terms",
										"org_field_label" => "Terms",
										"field_label" => "Terms"
									),

								"19" => Array
									(
										"field_label_type" => "view",
										"field_type" => "currency_rate",
										"org_field_label" => "Currency Rate",
										"field_label" => "Currency Rate"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Cancelled",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "F"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Validity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Term",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CC",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Branch Name",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "BILLING"
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"34" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"35" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"36" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"37" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"38" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"40" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Method",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"41" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Cheque Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"42" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Bank Charge",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"43" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"44" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

							),

						"detail" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DETAIL"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Location",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "SALES"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Quantity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "UOM",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Sum Qty",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Disc",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Printable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0020"
			),

		"20" => Array
			(
				"id" => "21",
				"title" => "SQL Accounting Purchase Template",
				"data_type" => "purchase",
				"file_format" => "txt",
				"delimiter" => ";",
				"row_format" => "two_row",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => "",
				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MASTER"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc No Ex",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Area",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"45" => Array
									(
										"field_label_type" => "view",
										"field_type" => "vendor_terms",
										"org_field_label" => "Vendor Terms",
										"field_label" => "Vendor Terms"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"20" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "PURCHASE"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Cancelled",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "F"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Validity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Term",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CC",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"26" => Array
									(
										"field_label_type" => "view",
										"field_type" => "batchno",
										"org_field_label" => "Batch No.",
										"field_label" => "Batch No."
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Branch Name",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "BILLING"
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"34" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"35" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"36" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"37" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"38" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"40" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Method",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"41" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Cheque Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"42" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Bank Charge",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"43" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"44" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

							),

						"detail" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DETAIL"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Location",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "PURCHASE"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Quantity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "UOM",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "UNIT"
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Sum Qty",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Disc",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Printable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "gl_code",
										"org_field_label" => "GL Code",
										"field_label" => "GL Code"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"22" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0021"
			),

		"21" => Array
			(
				"id" => "22",
				"title" => "SQL Accounting Purchase Debit Notes Template",
				"data_type" => "dn_purchase",
				"file_format" => "txt",
				"delimiter" => ";",
				"row_format" => "two_row",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => "",
				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MASTER"
									),

								"45" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc No Ex",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Area",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "terms",
										"org_field_label" => "Terms",
										"field_label" => "Terms"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"46" => Array
									(
										"field_label_type" => "view",
										"field_type" => "reason",
										"org_field_label" => "Reason",
										"field_label" => "Reason"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Cancelled",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "F"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Validity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Term",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CC",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Branch Name",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "BILLING"
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"34" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"35" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"36" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"37" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"38" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"40" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Method",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"41" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Cheque Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"42" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Bank Charge",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"43" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"44" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

							),

						"detail" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DETAIL"
									),

								"24" => Array
									(
										"field_label_type" => "view",
										"field_type" => "inv_no",
										"org_field_label" => "Invoice Number",
										"field_label" => "Invoice Number"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Location",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Quantity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "UOM",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "UNIT"
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Sum Qty",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Disc",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Printable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"26" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0022"
			),

		"22" => Array
			(
				"id" => "23",
				"title" => "SQL Accounting Sales Credit Notes Template",
				"data_type" => "cn_sales",
				"file_format" => "txt",
				"delimiter" => ";",
				"row_format" => "two_row",
				"date_format" => "d/m/Y",
				"time_format" => "",
				"header_column" => "",
				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MASTER"
									),

								"45" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc No Ex",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_code",
										"org_field_label" => "Customer Code",
										"field_label" => "Customer Code"
									),

								"7" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Area",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"16" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Agent",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"17" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "terms",
										"org_field_label" => "Terms",
										"field_label" => "Terms"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Currency Rate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"46" => Array
									(
										"field_label_type" => "view",
										"field_type" => "reason",
										"org_field_label" => "Reason",
										"field_label" => "Reason"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Cancelled",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "F"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_after_gst",
										"org_field_label" => "Amount After GST",
										"field_label" => "Amount After GST"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Validity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"24" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Delivery Term",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"25" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CC",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"26" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"27" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"28" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"29" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Ref 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"30" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Branch Name",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "BILLING"
									),

								"31" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"32" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"33" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"34" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Address 4",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"35" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Attention",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"36" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Phone 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"37" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Fax 1",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"38" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"39" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "D Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"40" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Method",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"41" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Cheque Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"42" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Bank Charge",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"43" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Amount",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"44" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "P Payment Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

							),

						"detail" => Array
							(
								"1" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Doc Type",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "DETAIL"
									),

								"24" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Number",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Item Code",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Location",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Project",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "----"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Description 3",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Quantity",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "UOM",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "UNIT"
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Sum Qty",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"13" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"14" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"15" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Disc",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "0.00"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"17" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_amount",
										"org_field_label" => "Tax Amount",
										"field_label" => "Tax Amount"
									),

								"18" => Array
									(
										"field_label_type" => "view",
										"field_type" => "selling_price_before_gst",
										"org_field_label" => "Amount Before GST",
										"field_label" => "Amount Before GST"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Printable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"20" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"21" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Transferable",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

								"26" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Remark 2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

							),

					),

				"active" => "1",
				"templates_ver" => "1",
				"templates_type" => "arms_preset",
				"template_code" => "T0023"
			),
		"23" => Array
			(
				"id" => "24",
				"title" => "Auto Count Cash Sales Template",
				"data_type" => "cash_sales",
				"file_format" => "csv",
				"delimiter" => ",",
				"row_format" => "no_repeat_master",
				"date_format" => "j/n/Y",
				"time_format" => "",
				"header_column" => Array
					(
						"0" => "DocNo",
						"1" => "DocDate",
						"2" => "JournalType",
						"3" => "DocNo2",
						"4" => "DocumentDescription",
						"5" => "CurrencyCode",
						"6" => "CurrencyRate",
						"7" => "Note",
						"8" => "InclusiveTax",
						"9" => "AccNo",
						"10" => "ToAccountRate",
						"11" => "ProjNo",
						"12" => "DeptNo",
						"13" => "TaxType",
						"14" => "Description",
						"15" => "FurtherDescription",
						"16" => "RefNo2",
						"17" => "SalesAgent",
						"18" => "TaxBRNo",
						"19" => "TaxBName",
						"20" => "TaxRefNo",
						"21" => "TaxPermitNo",
						"22" => "TaxExportCountry",
						"23" => "DR",
						"24" => "CR",
						"25" => "TaxableDR",
						"26" => "TaxableCR",
						"27" => "TaxAdjustment",
						"28" => "TaxDR",
						"29" => "TaxCR",
						"30" => "SupplyPurchase",
						"31" => "ToTaxCurrencyRate"
					),

				"data_column" => Array
					(
						"master" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "ref_no",
										"org_field_label" => "Reference No",
										"field_label" => "Reference No"
									),

								"2" => Array
									(
										"field_label_type" => "view",
										"field_type" => "date",
										"org_field_label" => "Date",
										"field_label" => "Date"
									),

								"3" => Array
									(
										"field_label_type" => "view",
										"field_type" => "customer_name",
										"org_field_label" => "Customer Name",
										"field_label" => "Customer Name"
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "DocNo2",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name"
									),

								"6" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CurrencyCode",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "MYR"
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "CurrencyRate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"8" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "Note",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "InclusiveTax",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "T"
									),

							),

						"detail" => Array
							(
								"1" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_code",
										"org_field_label" => "Account Code",
										"field_label" => "Account Code"
									),

								"2" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "ToAccountRate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1.00"
									),

								"3" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "ProjNo",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"4" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "DeptNo",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"5" => Array
									(
										"field_label_type" => "view",
										"field_type" => "second_tax_code",
										"org_field_label" => "Second Tax Code",
										"field_label" => "Second Tax Code"
									),

								"6" => Array
									(
										"field_label_type" => "view",
										"field_type" => "account_name",
										"org_field_label" => "Account Name",
										"field_label" => "Account Name",
									),

								"7" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "FurtherDescription",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"8" => Array
									(
										"field_label_type" => "view",
										"field_type" => "doc_no",
										"org_field_label" => "Document No",
										"field_label" => "Document No"
									),

								"9" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "SalesAgent",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"10" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "TaxBRNo",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"11" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "TaxBName",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"12" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "TaxRefNo",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"13" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "TaxPermitNo",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"14" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "TaxExportCountry",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"15" => Array
									(
										"field_label_type" => "view",
										"field_type" => "debit",
										"org_field_label" => "Debit",
										"field_label" => "Debit"
									),

								"16" => Array
									(
										"field_label_type" => "view",
										"field_type" => "credit",
										"org_field_label" => "Credit",
										"field_label" => "Credit"
									),

								"24" => Array
									(
										"field_label_type" => "view",
										"field_type" => "taxable_dr",
										"org_field_label" => "Taxable Debit",
										"field_label" => "Taxable Debit"
									),

								"25" => Array
									(
										"field_label_type" => "view",
										"field_type" => "taxable_cr",
										"org_field_label" => "Taxable Credit",
										"field_label" => "Taxable Credit"
									),

								"19" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "TaxAdjustment",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => ""
									),

								"26" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_dr",
										"org_field_label" => "Tax Debit",
										"field_label" => "Tax Debit"
									),

								"27" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_cr",
										"org_field_label" => "Tax Credit",
										"field_label" => "Tax Credit"
									),

								"22" => Array
									(
										"field_label_type" => "view",
										"field_type" => "tax_code",
										"org_field_label" => "Tax Code",
										"field_label" => "Tax Code"
									),

								"23" => Array
									(
										"field_label_type" => "open_field",
										"field_type" => "open_field",
										"org_field_label" => "User Defined Field",
										"field_label" => "ToTaxCurrencyRate",
										"field_desc" => "User Defined Field can be set after dropped into highlighted area. The text entered in Value field will be displayed when generating account export.",
										"field_value" => "1"
									),

							),

					),

				"active" => "0",
				"templates_ver" => "4",
				"templates_type" => "arms_preset",
				"template_code" => "T0024"
			)
	);
	//templates_type = arms_preset or cust_preset
	
	$con->sql_query("select ifnull(max(templates_ver),0) as max_ver
			from custom_acc_export_templates 
			where templates_type = 'arms_preset'");
	$max_ver = $con->sql_fetchfield("max_ver");
	$con->sql_freeresult();
	
	if($max_ver < $latest_ver){
		foreach($prelist_templates as $index => $item){
			$upd = array();
			$upd["title"] = $item["title"];
			$upd["data_type"] = $item["data_type"];
			$upd["file_format"] = $item["file_format"];
			$upd["delimiter"] = $item["delimiter"];
			$upd["row_format"] = $item["row_format"];
			$upd["date_format"] = $item["date_format"];
			if($item["time_format"] != "") $upd["time_format"] = $item["time_format"];
			if($item["header_column"] != "") $upd["header_column"] = serialize($item["header_column"]);
			$upd["data_column"] = serialize($item["data_column"]);
			$upd["active"] = mi($item["active"]);
			$upd["templates_ver"] = $item["templates_ver"];
			
			$ret = $con->sql_query("select template_code, templates_ver 
				from custom_acc_export_templates 
				where templates_type = 'arms_preset' and template_code = " . ms($item["template_code"]));
			
			if($con->sql_numrows($ret) > 0){
				while($r = $con->sql_fetchassoc($ret)){
					if($item["templates_ver"] > $r["templates_ver"]){
						$con->sql_query("update custom_acc_export_templates set " . mysql_update_by_field($upd) . "
						where templates_type = 'arms_preset' and template_code = " . ms($r["template_code"]));
					}
				}
			}else{
				$upd["templates_type"] = $item["templates_type"];
				$upd["template_code"] = $item["template_code"];
				$upd["added"] = "CURRENT_TIMESTAMP";
				
				$con->sql_query("insert into custom_acc_export_templates " . mysql_insert_by_field($upd));
			}
			$con->sql_freeresult($ret);
		}
		unset($upd);
	}	
}
?>