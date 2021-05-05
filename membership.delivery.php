<?php
/*
2017-09-13 10:41 AM Qiu Ying
- Bug fixed on treating special characters as wildcard character
*/
include("include/common.php");
if (!$login) js_redirect($LANG['YOU_HAVE_LOGGED_OUT'], "/index.php");

class MembershipDelivery extends Module{
  var $PAGE_TITLE="Delivery";
  var $PAGE_SIZE=25;
  var $item_per_page = 30;
  var $item_per_lastpage = 15;
  
  function _default(){
    global $con, $smarty;
    
    $smarty->assign('PAGE_TITLE', $this->PAGE_TITLE);
    
    $this->display('membership.delivery.tpl');
  }
  
  function reload(){
    global $con, $smarty, $sessioninfo;
    
    /*$start = $_REQUEST['s'];
	if ($start<=1) $start = 1;
	$s = ($start - 1) * $this->PAGE_SIZE;*/
    
    $cond="where pmpa.reason like 'Delivery' and pmpa.branch_id=".$sessioninfo['branch_id']." and p.cancel_status='0'";
    
    $sf=$_REQUEST['sf'];
    
    if(isset($sf['receipt_number']))  $filter.=" and p.receipt_no = '".$sf['receipt_number']."'";
    
    if(isset($sf['keyword_type']) && $sf['keyword_type']!=""){
      $filter.=" and pmpa.".$sf['keyword_type']." like " . ms("%".replace_special_char($sf['keyword'])."%");
    }
        
    if(isset($sf['date_from']))  $filter.=" and pmpa.date >= '".$sf['date_from']."'";
    if(isset($sf['date_to'])) $filter.=" and pmpa.date <= '".$sf['date_to']."'";
    if(isset($sf['status'])) $filter.=" and pmpa.is_delivery = ".mi($sf['status']);
    
    $sql="select pmpa.*,p.receipt_no,p.pos_time from pos_member_point_adjustment pmpa left join pos p on pmpa.ref_receipt_ref_no=p.receipt_ref_no ".$cond.$filter;
    //echo $sql;
    //$con->sql_query($sql." limit ".$s.",".$this->PAGE_SIZE);
    $con->sql_query($sql);
    $list = $con->sql_fetchrowset();
    $smarty->assign('list', $list);
    
    /*
    $count="select count(*) from pos_member_point_adjustment pmpa left join pos p on pmpa.ref_receipt_ref_no=p.receipt_ref_no ".$cond.$filter;
    $con->sql_query($count);
    $r = $con->sql_fetchrow();
    $total = $r[0];
    if($total>$this->PAGE_SIZE){
      $pg = "<b>Goto Page</b> <select id=\"page\" onchange=\"reload_list()\">";
      for ($i=1;$i<=$total;$i++){
          $pg .= "<option value=$i";
          if ($i == $start){
              $pg .= " selected";
          }
          $pg .= ">$i</option>";
      }
      $pg .= "</select>";
      $smarty->assign("pagination", "<div style=\"padding:4px;\">$pg</div>");
    }*/    
    
    $this->display('membership.delivery.table.tpl');
  }
  
  function print_delivery(){
    global $config,$con, $smarty, $sessioninfo;
    
    if($config['membership_item_per_page']) $this->item_per_page=$config['membership_item_per_page'];
    if($config['membership_item_per_lastpage']) $this->item_per_lastpage=$config['membership_item_per_lastpage'];
    
    $counter_id=$_REQUEST['counter_id'];
    $branch_id=$_REQUEST['branch_id'];
    $pos_id=$_REQUEST['pos_id'];
    $date=$_REQUEST['date'];
    $type=$_REQUEST['type'];
    
	//$cond1="where pmpa.counter_id='".$counter_id."' and pmpa.branch_id='".$branch_id."' and pmpa.pos_id='".$pos_id."' and pmpa.date='".$date."' and pmpa.type='".$type."'";
    $cond="where pmpa.counter_id='".$counter_id."' and pmpa.branch_id='".$branch_id."' and pmpa.pos_id='".$pos_id."' and pmpa.date='".$date."' and pmpa.type='".$type."' and p.cancel_status='0'";
    
    $sql="select pmpa.*,p.receipt_no,p.id as pid,p.pos_time from pos_member_point_adjustment pmpa left join pos p on pmpa.ref_receipt_ref_no=p.receipt_ref_no ".$cond;
    $con->sql_query($sql);
    $data=$con->sql_fetchassoc();
    if($data){
      
      //Update for first time 
      if(!$data['is_delivery'] && $data['delivery_date']=="0000-00-00 00:00:00"){
        $upd=array();
        $upd['is_delivery']=1;
        $upd['delivery_date']="CURRENT_TIMESTAMP";
        $con->sql_query("update pos_member_point_adjustment pmpa set ".mysql_update_by_field($upd)." ".$cond);
      }
      
      $con->sql_query("select * from branch where id = $branch_id");
      $from_branch = $con->sql_fetchrow();
      $smarty->assign("from_branch", $from_branch);
      
      
      $sql="select * from pos_items where branch_id='".$branch_id."' and counter_id='".$counter_id."' and pos_id='".$data['pid']."' and date='".$date."' order by id";
      $con->sql_query($sql);
      $pos_items=$con->sql_fetchrowset();
       
      foreach($pos_items as $k=>$v){
        $sql="select * from sku_items where id=".mi($v['sku_item_id']);
        $con->sql_query($sql);
        $item=$con->sql_fetchassoc();
        if($item){
          $arr=array();
          $arr['description']=$item['description'];
          $arr['receipt_description']=$item['receipt_description'];
          $arr['sku_item_code']=$item['sku_item_code'];
          $arr['mcode']=$item['mcode'];
          $arr['artno']=$item['artno'];          
          $pos_items[$k]['sku_item']=$arr;
        }
      }
     
      $totalpage = 1 + ceil((sizeof($pos_items)-$this->item_per_lastpage)/$this->item_per_page);
      
      $smarty->assign('data', $data);      
      for($i=0,$page=1;$page<=$totalpage;$i+=$this->item_per_page,$page++){        
          $smarty->assign("PAGE_SIZE", ($page < $totalpage)?$this->item_per_page:$this->item_per_lastpage);
          $smarty->assign("is_lastpage", ($page >= $totalpage));
          $smarty->assign("page", "Page $page of $totalpage");
          $smarty->assign("start_counter", $i);
          $smarty->assign("pos_items", array_slice($pos_items,$i,$this->item_per_page));
          $this->display('membership.delivery.print.tpl');
      }
    }
    else{
      die('Invalid.');
    }
  }  
}
$m = new MembershipDelivery('Membership Delivery');
?>