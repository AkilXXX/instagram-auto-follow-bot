<?php
$cok = file_get_contents('akilX/cok');
$file = "ex";
$id = file_get_contents("st/id");
$token = file_get_contents("st/token");
$ex  = explode("\n",file_get_contents("ex"));
include 'akil.php';

function flo($co,$id){
$explore = curl_init();
$url = "https://i.instagram.com/api/v1/friendships/create/$id/";
	  curl_setopt($explore, CURLOPT_URL, $url);
	  curl_setopt($explore, CURLOPT_RETURNTRANSFER, 1);
	  curl_setopt($explore, CURLOPT_FOLLOWLOCATION, 1);
	  curl_setopt($explore, CURLOPT_HEADER, 1);
	  curl_setopt($explore, CURLOPT_HTTPHEADER, array(
	      'x-ig-capabilities: 3w==',
	      'host: i.instagram.com',
	      'X-CSRFToken: missing',
	      'X-Instagram-AJAX: 1',
	      'Content-Type: application/x-www-form-urlencoded',
	      'X-Requested-With: XMLHttpRequest',
	      'Cookie: '.$co,
		  'User-Agent: Instagram 27.0.0.7.97 Android (23/6.0.1; 640dpi; 1440x2392; LGE/lge; RS988; h1; h1; en_US)',
	      'Connection: keep-alive'
	  ));
	  curl_setopt($explore, CURLOPT_POST, 1);
	  $res = curl_exec($explore);
      curl_close($explore);
      echo $res;
if(strpos($res,'spam":true')){

return "s";

}elseif(strpos($res,'friendship_status')){

return "d";

}
}



$don = 0;
$rr = 0;
$mid = bot('sendMessage',[
		'chat_id'=>$id,
		'text'=>"التحكم",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
			[['text'=>'follow ','callback_data'=>'stopgr']],
			[['text'=>'sleep : 10 ','callback_data'=>'stopgr']],
			[['text'=>"error : $rr",'callback_data'=>'stopgr'],['text'=>"done : $don",'callback_data'=>'stopgr']],
		]])
	])->result->message_id;
$ex  = explode("\n",file_get_contents("ex"));
foreach ($ex as $vv => $ll) {
sleep("30"); 
$cok = file_get_contents('akilX/cok');
$vvvv = flo($cok,$ll);
echo "$cok";
if($vvvv == "d"){
$don+=1;       	
       bot('editmessageText',[
        		'chat_id'=>$id,
        		'message_id'=>$mid,
		'text'=>"التحكم",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
			[['text'=>'follow ','callback_data'=>'stopgr']],
			[['text'=>'sleep : 10 ','callback_data'=>'stopgr']],
			[['text'=>"error : $rr",'callback_data'=>'stopgr'],['text'=>"done : $don",'callback_data'=>'stopgr']],
		]])
	]);

}else{
$rr+=1;

bot('editmessageText',[
        		'chat_id'=>$id,
        		'message_id'=>$mid,
		'text'=>"التحكم",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
		[['text'=>'follow ','callback_data'=>'stopgr']],
			[['text'=>'sleep : 10 ','callback_data'=>'stopgr']],
			[['text'=>"error : $rr",'callback_data'=>'stopgr'],['text'=>"done : $don",'callback_data'=>'stopgr']],
		]])
	]);

}
sleep("10"); 

}

bot('sendMessage',[
		'chat_id'=>$id,
		'text'=>"تم الانتهاء",
		]);