<?php
/*
db_edms-127.0.0.1-edmg_user-Edmg_2025

http://103.157.97.107/app/img/sandi.php
*/

echo "sandixx";
 //$con = mysqli_connect('127.0.0.1','edmg_user','Edmg_2025','db_edms') or die('Unable to Connect');
  $con = mysqli_connect('127.0.0.1','root','','db_edms') or die('Unable to Connect');
 //$sql="UPDATE sys_menus SET status = '0' WHERE sys_menus.id = '108'";

 //$sql="update `sys_config` set document_controll_email_address_notification='errin.lestari@forel-hanochem.com'";
 
 //$sql="truncate table `ref_vendor`";
// $sql="UPDATE `sys_user_menus` set user_id='3'";
 
 
 //$sql="truncate TABLE sys_user_menus";
 
 
 
 // mysqli_query($con,$sqlv);
 
 
$sqlx="select * from sys_users ";
//
//mysqli_query($con,$sqlx);

$que=mysqli_query($con,$sqlx);
while($row = mysqli_fetch_array($que)){
echo $row['id']."-".$row['name']."<br>";
}


// echo $sql;

?>