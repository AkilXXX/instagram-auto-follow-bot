<?php
$id = readline("ID : ");
$token = readline("TOKEN : ");
file_put_contents("st/token","$token");
file_put_contents("st/id","$id");
include 'akil.php';	


function getInfo($id,$cookies){
$search = curl_init(); 
curl_setopt($search, CURLOPT_URL, "https://i.instagram.com/api/v1/users/".trim($id)."/usernameinfo/"); 
curl_setopt($search, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($search, CURLOPT_ENCODING , "");
curl_setopt($search, CURLOPT_TIMEOUT, 15);
$h = explode("\n", 'Host: i.instagram.com
Connection: keep-alive
X-IG-Connection-Type: WIFI
X-IG-Capabilities: 3Ro=
Accept-Language: ar-AE
Cookie: '.$cookies.'
User-Agent: Instagram 27.0.0.7.97 Android (23/6.0.1; 640dpi; 1440x2392; LGE/lge; RS988; h1; h1; en_US)
Accept-Encoding: gzip, deflate, sdch');
curl_setopt($search, CURLOPT_HTTPHEADER, $h);
$search = curl_exec($search);
 echo $search;
$search = json_decode($search);

if(isset($search->user)){
    $user = $search->user;
$ret = ['f'=>$user->follower_count,'ff'=>$user->following_count,'m'=>$user->media_count,'user'=>$user->username];
    
}
return $ret;
}


		try {
	$callback = function ($update, $bot) {
		global $id;
		if($update != null){
		 
			if(isset($update->message)){
				$message = $update->message;
				$chatId = $message->chat->id;			
				$text = $message->text;							
				$com = $update->message;
				$lo = file_get_contents("akilX/lo");
			
			


					
			if($chatId == $id){		             				
              if($text == "/start" ){
             
              $bot->sendMessage([ 
              'chat_id'=>$chatId,                  
              'text'=>"Hi AKIL       
اختار الطريقه                 ",
              'reply_markup'=>json_encode([
              'inline_keyboard'=>[
              [['text'=>"متابعه تلقائي",'callback_data'=>'sd1'],['text'=>"مشاهده ستوريات",'callback_data'=>'s2']],         
              [['text'=>'الغاء المتابعات','callback_data'=>'s3']],    
              [['text'=>"تلقائي لايكات",'callback_data'=>'s4'],['text'=>"تعليقات تلقائي",'callback_data'=>'s5']],
              [['text'=>'اضف حساب','callback_data'=>'email']],
              ]
              ])
              ]);              
              }elseif($text != "/start" and $lo == "on"){
              
              file_put_contents("akilX/lo","of");
             $us = explode(":",$text)[0];
              
             $pas = explode(":",$text)[1];
             $ig = new ig(['file'=>'','account'=>['useragent'=>'Instagram 27.0.0.7.97 Android (23/6.0.1; 640dpi; 1440x2392; LGE/lge; RS988; h1; h1; en_US)']]);
             list($headers,$body) = $ig->login($us,$pas);
              echo $body;
          	$body2 = $body;
           	$body = json_decode($body);
           	
           	
            if(isset($body->message) ){
            
            if( $body->message == 'challenge_required'){
           $bot->sendMessage([
           'chat_id'=>$chatId,
           'text'=>"         \nلقد تم رفض الحساب لانه محظور او انه يطلب مصادقه⚙️"
          	]);
          	}				
          					
           	} elseif(isset($body->logged_in_user)) {
       		$body = $body->logged_in_user;
          	preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $headers, $matches);
          	
			$CookieStr = "";
			foreach($matches[1] as $item) {
			$CookieStr .= $item."; ";
			}
			
			
	file_put_contents("akilx/bod","$body2");
	file_put_contents("akilX/cok","$CookieStr");
	$s = getInfo($us,$CookieStr);
	
	    $body2 = json_decode($body2);
	    $username1 = $body2->logged_in_user->username;
		$profile_pic_url1 = $body2->logged_in_user->profile_pic_url;
		$full_name1 = 	$body2->logged_in_user->full_name;
		
		
		
	
	
		$fl = $s["f"];
		$fll = $s["ff"];
	    $po = $s["m"];
        $bot->sendphoto([ 'chat_id'=>$chatId,
                  'photo'=>"$profile_pic_url1",
                   'caption'=>"
FULL NAME : $full_name1

USERNAME : $username1

Following : $fll

Follower : $fl

POST : $po
                                      
                   
                   
                   ",
                  'reply_markup'=>json_encode([
                      'inline_keyboard'=>[
                          [['text'=>"متابعه تلقائي",'callback_data'=>'s1'],['text'=>"مشاهده ستوريات",'callback_data'=>'s2']],              
              [['text'=>'اضف هشتاجات','callback_data'=>'bod']],
              [['text'=>"لايكات",'callback_data'=>'txt1'],['text'=>"تعليقات",'callback_data'=>'html1']],
              [['text'=>'تسجيل الخروج','callback_data'=>'email']],
                    
                      ]
                  ])
              ]);
              
              
          	} else {
          	$bot->sendMessage([
        		'chat_id'=>$chatId,
          		'text'=>"كلمه السر او اليوزر خطأ ⛔"
          			
          					
          					]);
          	}
              
              
              
              
         
         }
         }
         
         }elseif(isset($update->callback_query)) {
              
          $chatId = $update->callback_query->message->chat->id;
          $mid = $update->callback_query->message->message_id;
          $data = $update->callback_query->data;
       
          echo $data."\n";

          
////////// SMTP                    
 	
if($data == "email"){
file_put_contents("akilX/lo","on");
$bot->sendMessage([ 
              'chat_id'=>$chatId,                  
              'text'=>"ارسل الحساب بهذا الشكل
 user:pass             
                    "]);



/*

*/
}

//////Start


elseif($data == "s1"){

bot('deletemessage',[
	'chat_id'=>$chatId,
	'message_id'=>$mid
	]);
$bot->sendMessage([
'chat_id'=>$chatId,
'text'=>" اضف يوزرات لتفاعل معها",
'reply_markup'=>json_encode([
              'inline_keyboard'=>[
              
[['text'=>"اكسبلور ",'callback_data'=>'exxx'],['text'=>"هشتاجات",'callback_data'=>'s3']],
              
              
]
])
]);






}
elseif($data == "exxx"){
bot('deletemessage',[
	'chat_id'=>$chatId,
	'message_id'=>$mid
	]);

system("screen -dmS n php ex.php");


}elseif($data == "sd1"){
$bot->answerCallbackQuery([
'callback_query_id'=>$update->callback_query->id,
'text'=>"اضف حساب اولا",
'show_alert'=>1
						]);
}elseif($data == "s2" or "s3" or "s4"){
$bot->answerCallbackQuery([
'callback_query_id'=>$update->callback_query->id,
'text'=>"تتوفر هذي الميزه في النسخه المدفوعه فقط ⛔",
'show_alert'=>1
						]);


}


}
         }
         };
         $bot = new EzTG(array('throw_telegram_errors'=>true,'token' => $token, 'callback' => $callback));
         }
    catch(Exception $e){
	echo $e->getMessage().PHP_EOL;
	sleep(1);
}
   
         