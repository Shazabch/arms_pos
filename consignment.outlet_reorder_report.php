<?php
/*
10/23/2018 2:00 PM Justin
- Enhanced to load SKU Type list from its own table instead of load from SKU table.
*/

include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");
include('consignment.include.php');

ini_set('memory_limit','256M');
ini_set('display_error',0);

class ConsignmentOutletReorderReport extends Module{
  var $PAGE_TITLE="Consignment Outlet Reorder Report";
  var $branch_per_page=5;
  var $item_per_page=24;
  
  function init_load(){
    global $con,$smarty,$sessioninfo;
    
    $con->sql_query("select id,description from brand where active=1 order by description");
	$r = $con->sql_fetchrowset();
	$con->sql_freeresult();
	if($r)	array_unshift($r, array("id"=>'0', "description"=>"UN-BRANDED"));
	$smarty->assign("brands", $r);
	unset($r);

	$con->sql_query("select id,description from vendor where active=1 order by description");
	$smarty->assign("vendors", $con->sql_fetchrowset());
	$con->sql_freeresult();
    
    $con->sql_query("select code as sku_type from sku_type where active=1 order by code");
    $smarty->assign("sku_type", $con->sql_fetchrowset());
  }
  
  function _default(){
    global $con,$smarty,$sessioninfo;
        
    $this->init_load();
    
    $this->display('consignment.outlet_reorder_report.tpl');
  }
  
  function find($print=false){
    global $con,$smarty,$sessioninfo;
    
    $start = $_REQUEST['s'];
	if ($start<=1) $start = 1;
	$s = ($start - 1) * $this->item_per_page;
    
    if($_REQUEST['print']) $print=true; 
        
    $cond="";
    $sql = " from sku_items si
    left join sku on si.sku_id = sku.id
    left join sku_items_cost sic on sic.sku_item_id=si.id and sic.branch_id=1
    where si.active=1 and sku.active=1";
    
    if($_REQUEST['have_balance']) $cond=" and sic.qty > 0";
    if(isset($_REQUEST['brand_id']) && $_REQUEST['brand_id']!="") $cond.=" and sku.brand_id=".mi($_REQUEST['brand_id']);
    if(isset($_REQUEST['vendor_id']) && $_REQUEST['vendor_id']!="") $cond.=" and sku.vendor_id=".mi($_REQUEST['vendor_id']);
    if(isset($_REQUEST['sku_type']) && $_REQUEST['sku_type']!="") $cond.=" and sku.sku_type=".ms($_REQUEST['sku_type']);
    
    $con->sql_query("select count(*)".$sql.$cond);    
	$t = $con->sql_fetchrow();
	$con->sql_freeresult();
    $count=$t[0];
    $total = ceil($count/$this->item_per_page);
	if ($start > $total) $start = 0;
    
    
    $select="select si.*,sic.qty as stock_balance";
    $order=" order by si.artno asc";   
    
    if($print){
      $sku_items=array();
      
      for ($i=0;$i<$total;$i++){
        $s = $i * $this->item_per_page;
        $limit = " limit $s, ".$this->item_per_page;        
        
        $this->build_items($select.$sql.$cond.$order.$limit,$sku_items[$i]);
      }
    }
    else{
      if($count>$this->item_per_page){
        $pg = "<b>Goto Page</b> <select onchange=\"refresh(this.value)\">";
        for ($i=1;$i<=$total;$i++){
          $pg .= "<option value=$i";
          if ($i == $start){
            $pg .= " selected";
          }
          $pg .= ">$i</option>";
        }
        $pg .= "</select>";
        $smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
      }
        
      $limit = " limit $s, ".$this->item_per_page;
      
      $this->build_items($select.$sql.$cond.$order.$limit,$sku_items);
    }
        
    $branches=$this->branches();
    
    $total_to_print=sizeof($branches) * $total;
    
    $smarty->assign("total_to_print", $total_to_print);
    $smarty->assign("total_page", $total);    
    $smarty->assign("branches", $branches);
    $smarty->assign('sku_items', $sku_items);
    
    if($print){
      $this->display('consignment.outlet_reorder_report.print.tpl');
    }
    else{
      $this->init_load();
      $this->display('consignment.outlet_reorder_report.tpl');
    }
  }
  
  function build_items($sql,&$sku_items){
    global $con,$smarty,$sessioninfo;
    
    $con->sql_query($sql);
    
    $sku_items = array();
    while($r=$con->sql_fetchassoc()){
      if($_REQUEST['con_split_artno']){
        list($r['artno_code'],$r['artno_size']) = preg_split("/\s+/",$r['artno'],2);
      }
      $sku_items[] = $r;
    }
    $con->sql_freeresult();
    
    if(sizeof($sku_items)>0 && sizeof($sku_items)!=$this->item_per_page){
      $loop_size=$this->item_per_page-sizeof($sku_items);
      for($l=0; $l<$loop_size; $l++ ){
        $sku_items[]=null;
      }
    }
  }
  
  function load_branch(){
    global $con,$smarty,$sessioninfo;
    
    $b=load_region_branch_array(array('active'=>1));
   
    if($_REQUEST['region']=='no_region'){
      $branches=$b['no_region'];
    }
    elseif(isset($b['got_region'][$_REQUEST['region']])){
      $branches=$b['got_region'][$_REQUEST['region']];
    }
    
    $selected=explode(',',$_REQUEST['selected']);
    
    $smarty->assign("selected", $selected);   
    $smarty->assign("branches", $branches);   
    $this->display('consignment.outlet_reorder_report.branches.tpl');
  }
  
  private function branches(){
    global $con,$smarty,$sessioninfo;
    
    $cond="";
    if(isset($_REQUEST['b']) && !empty($_REQUEST['b'])){
      $cond=" and id in (".implode(',',$_REQUEST['b']).")";
    }
    
    
    if (BRANCH_CODE == 'HQ') $con->sql_query("select id,code from branch where active=1".$cond." order by sequence,code");
	else  $con->sql_query("select id,code from branch where code = ".ms(BRANCH_CODE)).$cond;

    $branch_page=0;
    $branch_count=0;
    while($b=$con->sql_fetchassoc()){
      $branches[$branch_page][] = $b;
      $branch_count++;
      if($branch_count>=$this->branch_per_page){
        $branch_count=0;
        $branch_page++;
      }
    }
    
    if($branch_count>0 && $branch_count!=$this->branch_per_page){
      $loop_size=$this->branch_per_page-sizeof($branches[$branch_page]);
      for($i=0; $i<$loop_size; $i++ ){
        $branches[$branch_page][]=null;
      }
    }
       
    return $branches;
  }  
}
$EFORM = new ConsignmentOutletReorderReport('Consignment Outlet Reorder Report');
?>