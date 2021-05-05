<?php
/*
5/20/2016 3:41 PM Andy
- Change the check login status sequence to check arms user login first.

5/23/2016 10:24 AM Andy
- Removed the session start, because it was declared in common.

10/10/2016 4:23 PM Andy
- Fixed debtor portal login session.
*/
include("include/common.php");

if (isset($_REQUEST['a'])){
	switch($_REQUEST['a']){
        case 'check_login':
			if($login || (isset($_SESSION['debtor_portal']) && $_SESSION['debtor_portal'])){
				die("OK");
			}elseif(isset($_SESSION['ticket']['ac'])){
				$u_id = ms($_SESSION['ticket']['ac']);
				$con->sql_query("select login_tickets.*,vendor.description as vendor, vendor.address as address, vendor.phone_1 as phone_1, vendor.phone_2 as phone_2 , vendor.phone_3 as phone_3 ,category.description as dept_name, user.u, user.fullname, user2.u as u_create, user2.fullname as fullname_create
				from login_tickets
				left join user on login_tickets.user_id=user.id
				left join user user2 on login_tickets.create_by=user2.id
				left join category on dept_id=category.id
				left join vendor on login_tickets.vendor_id=vendor.id
				where login_tickets.ac=$u_id");
				$r=$con->sql_fetchrow();

				if($ssid!=$r['ssid'] or $_SESSION['ticket']['ac']!=$r['ac']){
					die("Logout");
				}
				else{
					die("OK");
				}
			}else{
                die("Logout");
            }
        break;
        case 'login':
            if (check_login($_REQUEST['u'], $_REQUEST['p'], $errmsg)){
                die('OK');
	    	}
            else{
                die($errmsg);
            }
        break;
        default:
           die("Unhandled Request");
    }
}
else{
    die("Unhandled Request");
}

?>
