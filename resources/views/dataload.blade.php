<?php
header('Content-Type: text/event-stream');
header('Cache-Control: no-cache');

$cnt1=0;
if(isset($_SESSION['WEB']))
{
	$cnt = $_SESSION['WEB'];
	echo "data:{$cnt} \n\n";
}
/*if(isset($_SESSION['CNT']))
{
	$cnt1 = $_SESSION['CNT'];
}
if($cnt1 !=$cnt)
{
	$_SESSION['CNT'] = $cnt;
	echo "data:{$cnt} \n\n";
}
else
{
	echo "data:{$cnt} \n\n";
}*/
/*$roll='1';
$db='dcs';
$cnt='';
$getnotification = DB::Table($db.'.notifications')->where('status',1)->get();
if($getnotification->isEmpty())
{
	
}
else
{
	$roleid = $getnotification[0]->roleid;
	$sms = $getnotification[0]->sms;
	$web = $getnotification[0]->web;
	$email = $getnotification[0]->email;
	$inapp = $getnotification[0]->inapp;
	if(!empty($roleid))
	{
		$roleexplde = explode(",",$roleid);
		$count = count($roleexplde);
		for($i=0;$i<$count;$i++)
		{
			$rolid = $roleexplde[$i];
			if($rolid==$roll)
			{
				if($sms==1)
				{
					
				}
				else if($web==1)
				{
					$notificationmsg = DB::table($db.'.notifications')->where('status',1)->where('web',1)->get();
					$notificationcnt = DB::table($db.'.notifications')->where('status',1)->where('web',1)->count();
					if($notificationmsg->isEmpty())
					{
						
					}
					else
					{
						$msg = $notificationmsg[0]->msgcontent;
						$cnt = $notificationcnt;
						//echo $cnt.",".$msg;
					}
				}
				else if($email==1)
				{
					
				}
				else if($inapp==1)
				{
					
				}
			}
		}
	}
	
}
$time = date('Y-m-d h:i:s');*/

flush();
?>