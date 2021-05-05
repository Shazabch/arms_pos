<?php
/*
1/20/2016 3:31 PM Andy
- Enhanced to auto create gst tax code list.

07/18/2016 16:30 Edwin
- New gst type: 'Flat Rate' added.

11/30/2016 4:20 PM Andy
- Enhanced to always have default tax invoice remark value. (Name, Address, BRN, GST Reg No)

8/22/2017 1:23 PM Andy
- Add gstManager function getGstOS().

5/22/2018 3:33 PM Andy
- Change all tax to 0% at first initial setup.

9/13/2018 4:41 PM Andy
- Add gstManager function getTextNR().
*/
class gstManager{
	// public var
	public $gstIsActive = false;
	public $skipGSTValidate = false;
	public $leftJoinOutputGSTString = "left join gst output_gst on output_gst.id=if(if(si.output_tax<0,sku.mst_output_tax,si.output_tax)<0,cc.output_tax,if(si.output_tax<0,sku.mst_output_tax,si.output_tax))";
	public $itemIsInclusiveTaxString = "if(if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax)='inherit',cc.inclusive_tax,if(si.inclusive_tax='inherit',sku.mst_inclusive_tax,si.inclusive_tax))";
	public $taxInvoiceDefaultRemarkList = array("Name","Address","BRN","GST Reg No");
	
	// private var
	private $gstOS = array();
	
	function __construct(){
		global $smarty, $con, $appCore;

		$this->initGST();
	}
	
	// function to init all GST var
	// return null
	private function initGST(){
		global $appCore, $config, $con;
		
		// gst
		if($config['enable_gst']){
			// check and create default gst
			if(BRANCH_CODE == 'HQ'){
				$con->sql_query("select count(*) from gst");
				$gst_count = mi($con->sql_fetchfield(0));
				$con->sql_freeresult();
				
				// insert default tax code
				if($gst_count <= 0){
					$taxCode = array(
						array("code"=>"TX","inc_item_cost"=>"0","vendor_gst_setting"=>"Required","type"=>"purchase","rate"=>"0","description"=>"Purchases with GST incurred at 6% and directly attributable to taxable supplies."),
						array("code"=>"IM","inc_item_cost"=>"1","vendor_gst_setting"=>"Disabled","type"=>"purchase","rate"=>"0","description"=>"Import of goods with GST incurred."),
						array("code"=>"IS","inc_item_cost"=>"1","vendor_gst_setting"=>"Disabled","type"=>"purchase","rate"=>"0","description"=>"Imports under special scheme with no GST incurred."),
						array("code"=>"BL","inc_item_cost"=>"1","vendor_gst_setting"=>"Disabled","type"=>"purchase","rate"=>"0","description"=>"Purchases with GST incurred but not claimable(Disallowance of Input Tax)."),
						array("code"=>"NR","inc_item_cost"=>"0","vendor_gst_setting"=>"Optional","type"=>"purchase","rate"=>"0","description"=>"Purchase from non GST-registered supplier with no GST incurred."),
						array("code"=>"ZP","inc_item_cost"=>"0","vendor_gst_setting"=>"Optional","type"=>"purchase","rate"=>"0","description"=>"Purchase from GST-registered supplier with no GST incurred."),
						array("code"=>"EP","inc_item_cost"=>"0","vendor_gst_setting"=>"Optional","type"=>"purchase","rate"=>"0","description"=>"Purchases exempted from GST."),
						array("code"=>"OP","inc_item_cost"=>"0","vendor_gst_setting"=>"Optional","type"=>"purchase","rate"=>"0","description"=>"Purchase transactions which is out of the scope of GST legislation"),
						array("code"=>"TX-E43","inc_item_cost"=>"0","vendor_gst_setting"=>"Required","type"=>"purchase","rate"=>"0","description"=>"Purchase with GST incurred directly attributable to incidental exempt supplies."),
						array("code"=>"TX-N43","inc_item_cost"=>"0","vendor_gst_setting"=>"Required","type"=>"purchase","rate"=>"0","description"=>"Purchase with GST incurred directly attributable to non-incidental exempt supplies."),
						array("code"=>"TX-RE","inc_item_cost"=>"0","vendor_gst_setting"=>"Required","type"=>"purchase","rate"=>"0","description"=>"Purchase with GST incurred that is not directly attributable to taxable or exempt supplies."),
						array("code"=>"GP","inc_item_cost"=>"0","vendor_gst_setting"=>"Optional","type"=>"purchase","rate"=>"0","description"=>"Purchase transactions which disregarded under GST legislation"),
						array("code"=>"AJP","inc_item_cost"=>"0","vendor_gst_setting"=>"Optional","type"=>"purchase","rate"=>"0","description"=>"Any adjustment made to Input Tax"),
						array("code"=>"SR","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Standard-rated supplies with GST Charged.","indicator_receipt"=>"S"),
						array("code"=>"ZRL","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Local supply of goods or services which are subject to zero rated supplies.","indicator_receipt"=>"Z"),
						array("code"=>"ZRE","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Exportation of goods or services which are subject to zero rated supplies.","indicator_receipt"=>"Z"),
						array("code"=>"ES43","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Incidental Exempt supplies.","indicator_receipt"=>"E"),
						array("code"=>"DS","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Deemed supplies","indicator_receipt"=>"S"),
						array("code"=>"OS","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Out-of-scope supplies.","indicator_receipt"=>"Z"),
						array("code"=>"ES","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Exempt supplies under GST","indicator_receipt"=>"E"),
						array("code"=>"RS","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Relief supply under GST.","indicator_receipt"=>"Z"),
						array("code"=>"GS","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Disregarded supplies.","indicator_receipt"=>"Z"),
						array("code"=>"AJS","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"supply","rate"=>"0","description"=>"Any adjustment made to Output Tax","indicator_receipt"=>"S"),
						array("code"=>"TX-FR","inc_item_cost"=>"0","vendor_gst_setting"=>"-","type"=>"purchase","rate"=>"0","description"=>"Flat Rate")
					);
					
					foreach($taxCode as $r){
						$ins = array();
						$ins['code'] = $r['code'];
						$ins['description'] = $r['description'];
						$ins['type'] = $r['type'];
						$ins['rate'] = $r['rate'];
						$ins['inc_item_cost'] = $r['inc_item_cost'];
						$ins['vendor_gst_setting'] = $r['vendor_gst_setting'];
						$ins['indicator_receipt'] = $r['indicator_receipt'];
						$ins['last_update'] = $ins['added'] = "CURRENT_TIMESTAMP";
						$ins['active'] = $ins['user_id'] = 1;
											
						$con->sql_query_false("insert into gst ".mysql_insert_by_field($ins));
					}
				}
			}
			
			
			// check general if the GST is active
			$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'active' and setting_value = 1");
			$gst_is_active = $con->sql_fetchassoc($q1);
			$con->sql_freeresult($q1);

			if($gst_is_active){
				// mark gst is activated
				$this->gstIsActive = true;
				
				// pickup and see if GST general settings only needs to check GST active status
				$q1 = $con->sql_query("select setting_value from gst_settings where setting_name = 'skip_gst_validate' and setting_value = 1");
				$skip_gst_validate = $con->sql_fetchassoc($q1);
				$con->sql_freeresult($q1);
				
				if($skip_gst_validate)	$this->skipGSTValidate = true;
			}
		}
	}
	
	public function getTaxInvoiceRemarkList(){
		global $global_gst_settings;
		if(!$global_gst_settings['tax_invoice_remark']['title'])	$global_gst_settings['tax_invoice_remark']['title'] = array();
		return array_unique(array_merge($this->taxInvoiceDefaultRemarkList, $global_gst_settings['tax_invoice_remark']['title']));
	}
	
	// function to get GST Type 'OS'
	// return array
	public function getGstOS(){
		global $con;
		
		if(!$this->gstOS){
			$con->sql_query("select * from gst where code='OS'");
			$this->gstOS = $con->sql_fetchassoc();
			$con->sql_freeresult();
		}
		return $this->gstOS;
	}
	
	public function getTextNR(){
		return $this->gstIsActive ? "NR" : '';
	}
}
?>
