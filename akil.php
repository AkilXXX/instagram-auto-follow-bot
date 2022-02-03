<?php
/*
function GUID(){
    if (function_exists('com_create_guid') === true){
        return trim(com_create_guid(), '{}');
    }
    }

*/

class EzTGException extends Exception
{
}

function bot($method,$datas=[]){
    global $token;
$url = "https://api.telegram.org/bot".$token."/".$method;
$ch = curl_init();
curl_setopt($ch,CURLOPT_URL,$url);
curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
$res = curl_exec($ch);
if(curl_error($ch)){
var_dump(curl_error($ch));
}else{
return json_decode($res);
}
}


class EzTG
{
    private $settings;
    private $offset;
    private $json_payload;
    public function __construct($settings, $base = false)
    {
        $this->settings = array_merge(array(
      'endpoint' => 'https://api.telegram.org',
      'token' => '1234:abcd',
      'callback' => function ($update, $EzTG) {
          echo 'no callback' . PHP_EOL;
      },
      'objects' => true,
      'allow_only_telegram' => true,
      'throw_telegram_errors' => true,
      'magic_json_payload' => false
    ), $settings);
        if ($base !== false) {
            return true;
        }
        if (!is_callable($this->settings['callback'])) {
            $this->error('Invalid callback.', true);
        }
        if (php_sapi_name() === 'cli') {
            $this->settings['magic_json_payload'] = false;
            $this->offset = -1;
            $this->get_updates();
        } else {
            if ($this->settings['allow_only_telegram'] === true and $this->is_telegram() === false) {
                http_response_code(403);
                echo '403 - You are not Telegram,.,.';
                return 'Not Telegram';
            }
            if ($this->settings['magic_json_payload'] === true) {
                ob_start();
                $this->json_payload = false;
                register_shutdown_function(array($this, 'send_json_payload'));
            }
            if ($this->settings['objects'] === true) {
                $this->processUpdate(json_decode(file_get_contents('php://input')));
            } else {
                $this->processUpdate(json_decode(file_get_contents('php://input'), true));
            }
        }
    }
    private function is_telegram()
    {
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) { //preferisco non usare x-forwarded-for xk si può spoof
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        if (($ip >= '149.154.160.0' && $ip <= '149.154.175.255') || ($ip >= '91.108.4.0' && $ip <= '91.108.7.255')) { //gram'''s ip : https://core.telegram.org/bots/webhooks
            return true;
        } else {
            return false;
        }
    }
    private function get_updates()
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->settings['endpoint'] . '/bot' . $this->settings['token'] . '/getUpdates');
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        while (true) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, 'offset=' . $this->offset . '&timeout=10');
            if ($this->settings['objects'] === true) {
                $result = json_decode(curl_exec($ch));
                if (isset($result->ok) and $result->ok === false) {
                    $this->error($result->description, false);
                } elseif (isset($result->result)) {
                    foreach ($result->result as $update) {
                        if (isset($update->update_id)) {
                            $this->offset = $update->update_id + 1;
                        }
                        $this->processUpdate($update);
                    }
                }
            } else {
                $result = json_decode(curl_exec($ch), true);
                if (isset($result['ok']) and $result['ok'] === false) {
                    $this->error($result['description'], false);
                } elseif (isset($result['result'])) {
                    foreach ($result['result'] as $update) {
                        if (isset($update['update_id'])) {
                            $this->offset = $update['update_id'] + 1;
                        }
                        $this->processUpdate($update);
                    }
                }
            }
        }
    }
    public function processUpdate($update)
    {
        $this->settings['callback']($update, $this);
    }
    protected function error($e, $throw = 'default')
    {
        if ($throw === 'default') {
            $throw = $this->settings['throw_telegram_errors'];
        }
        if ($throw === true) {
            throw new EzTGException($e);
        } else {
            echo 'Telegram error: ' . $e . PHP_EOL;
            return array(
        'ok' => false,
        'description' => $e
      );
        }
    }
    public function newKeyboard($type = 'keyboard', $rkm = array('resize_keyboard' => true, 'keyboard' => array()))
    {
        return new EzTGKeyboard($type, $rkm);
    }
    public function __call($name, $arguments)
    {
        if (!isset($arguments[0])) {
            $arguments[0] = array();
        }
        if (!isset($arguments[1])) {
            $arguments[1] = true;
        }
        if ($this->settings['magic_json_payload'] === true and $arguments[1] === true) {
            if ($this->json_payload === false) {
                $arguments[0]['method'] = $name;
                $this->json_payload = $arguments[0];
                return 'json_payloaded'; //xd
            } elseif (is_array($this->json_payload)) {
                $old_payload = $this->json_payload;
                $arguments[0]['method'] = $name;
                $this->json_payload = $arguments[0];
                $name = $old_payload['method'];
                $arguments[0] = $old_payload;
                unset($arguments[0]['method']);
                unset($old_payload);
            }
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $this->settings['endpoint'] . '/bot' . $this->settings['token'] . '/' . urlencode($name));
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($arguments[0]));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($this->settings['objects'] === true) {
            $result = json_decode(curl_exec($ch));
        } else {
            $result = json_decode(curl_exec($ch), true);
        }
        curl_close($ch);
        if ($this->settings['objects'] === true) {
            if (isset($result->ok) and $result->ok === false) {
                return $this->error($result->description);
            }
            if (isset($result->result)) {
                return $result->result;
            }
        } else {
            if (isset($result['ok']) and $result['ok'] === false) {
                return $this->error($result['description']);
            }
            if (isset($result['result'])) {
                return $result['result'];
            }
        }
        return $this->error('Unknown error', false);
    }
    public function send_json_payload()
    {
        if (is_array($this->json_payload)) {
            ob_end_clean();
            echo json_encode($this->json_payload);
            header('Content-Type: application/json');
            ob_end_flush();
            return true;
        }
    }
}
class EzTGKeyboard
{
    public function __construct($type = 'keyboard', $rkm = array('resize_keyboard' => true, 'keyboard' => array()))
    {
        $this->line = 0;
        $this->type = $type;
        if ($type === 'inline') {
            $this->keyboard = array(
        'inline_keyboard' => array()
      );
        } else {
            $this->keyboard = $rkm;
        }
        return $this;
    }
    public function add($text, $callback_data = null, $type = 'auto')
    {
        if ($this->type === 'inline') {
            if ($callback_data === null) {
                $callback_data = trim($text);
            }
            if (!isset($this->keyboard['inline_keyboard'][$this->line])) {
                $this->keyboard['inline_keyboard'][$this->line] = array();
            }
            if ($type === 'auto') {
                if (filter_var($callback_data, FILTER_VALIDATE_URL)) {
                    $type = 'url';
                } else {
                    $type = 'callback_data';
                }
            }
            array_push($this->keyboard['inline_keyboard'][$this->line], array(
        'text' => $text,
        $type => $callback_data
      ));
        } else {
            if (!isset($this->keyboard['keyboard'][$this->line])) {
                $this->keyboard['keyboard'][$this->line] = array();
            }
            array_push($this->keyboard['keyboard'][$this->line], $text);
        }
        return $this;
    }
    public function newline()
    {
        $this->line++;
        return $this;
    }
    public function done()
    {
        if ($this->type === 'remove') {
            return '{"remove_keyboard": true}';
        } else {
            return json_encode($this->keyboard);
        }
    }
}
class ig {
	private $url = 'https://i.instagram.com/api/v1';
	private $account;
	private $ret = [];
	private $file;
	public function __construct($settings){
		$this->account = $settings['account'];
		$this->account['useragent'] = 'Instagram 27.0.0.7.97 Android (23/6.0.1; 640dpi; 1440x2392; LGE/lge; RS988; h1; h1; en_US)';
		$this->file = $settings['file'];
		
	}
	public function login($user,$pass){
		$uuid = $this->UUID();
		$guid = $this->GUID();
		return $this->request('accounts/login/',
			0,
			1,
			[
				'signed_body'=>'57afc5aa6cc94675a08329beaffaec7bad237df0198ed801280f459e80095abb.'.json_encode([
					'phone_id'=>$guid,
					'username'=>$user,
					'_uid'=>rand(1000000000,9999999999),
					'guid'=>$guid,
					'_uuid'=>$guid,
					'device_id'=>'android-'.$guid,
					'password'=>$pass,
					'login_attempt_count'=>'0',
				])
			],
			1
		);
	}
	public function news(){
		return $this->request('news/inbox/',1);
	}
	
	public function getComments($mediaId){
    return $this->request("media/{$mediaId}/comments/",1,0,['can_support_threading'=>true]);
  }
  public function getLikers($mediaId){
	  return $this->request("media/{$mediaId}/likers/",1);
	}
	public function getPosts($userId){
		return $this->request("feed/user/{$userId}/",1);
	}
	public function getInfo($username){
		return $this->request("users/{$username}/usernameinfo/",1)->user;
	}
	public function comment($mediaId,$comment){
		$uuid = $this->UUID();
		$guid = $this->GUID();
		return $this->request("media/{$mediaId}/comment/",1,1,[
						'user_breadcrumb'=>$this->generateUserBreadcrumb(mb_strlen($comment)),
            'idempotence_token'=>$uuid,
            '_uuid'=>$uuid,
            '_uid'=>rand(1000000000,9999999999),
            'comment_text'=>$comment,
            'containermodule'=>'comments_feed_timeline',
            'radio_type'=>'wifi-none'
		]);
	}
	public function like($mediaId){
		$uuid = $this->UUID();
		$guid = $this->GUID();
    return $this->request("media/{$mediaId}/like/",1,1,[
        '_uuid'=>$uuid,
        '_uid'=>rand(1000000000,9999999999),
        'media_id'=>$mediaId,
        'radio_type'=>'wifi-none',
        'module_name'=>'feed_timeline'
    ]);
  }
	public function unfollow($id){
		$uuid = $this->UUID();
		$guid = $this->GUID();
		return $this->request("friendships/destroy/$id/",1,1,[
					'_uid'=>rand(1000000000,9999999999),
					'_uuid'=>$guid,
					'user_id'=>$id,
					'radio_type'=>'wifi-none'
		]);
	}
	public function follow($id){
		$uuid = $this->UUID();
		$guid = $this->GUID();
		return $this->request("friendships/create/$id/",1,1,[
					'_uid'=>rand(1000000000,9999999999),
					'_uuid'=>$guid,
					'user_id'=>$id,
					'radio_type'=>'wifi-none'
		]);
	}
	public function getFollowing($id,$mid,$uuu,$maxId = null){
	    $config = json_decode(file_get_contents('config.json'),1);
	    $from = 'Following.';
		$file = $this->file;
		$rank_token = $this->UUID();
		$datas['rank_token'] = $rank_token;
		if($maxId != null){
			$datas['max_id'] = $maxId;
		}
		$res = $this->request("friendships/$id/following/",1,0,$datas,0);
		if(isset($res->users)){
			$in = explode("\n",file_get_contents($file));
			foreach($res->users as $user){
				if(!in_array($user->username, $in)){
					$users[] = $user->username;
					file_put_contents($file, $user->username."\n",FILE_APPEND);
				}
			}
    	
    	bot('editmessageText',[
    		'chat_id'=>$config['id'],
    		'message_id'=>$mid,
    		'text'=>"*Collection From* ~ [ _ $from _ ]\n\n*Status* ~> _ Working _\n*Users* ~> _ ".count(explode("\n", file_get_contents($file)))."_",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
			[['text'=>'Stop.','callback_data'=>'stopgr']]
		]])
    	]);
		}
		if($res->next_max_id != null){
			$this->getFollowing($id,$mid,$uuu,$res->next_max_id);
		} else {
			bot('editmessageText',[
    		'chat_id'=>$config['id'],
    		'message_id'=>$mid,
    		'text'=>"*Collection From* ~ [ _ $from _ ]\n\n*Status* ~> _ Working _\n*Users* ~> _ ".count(explode("\n", file_get_contents($file)))."_",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
			[['text'=>'Stop.','callback_data'=>'stopgr']]
		]])
    	]);
    	bot('sendMessage',[
    		'chat_id'=>$config['id'],
    		'reply_to_message_id'=>$mid,
    		'text'=>"*Done Collection . * \n All : ".count(explode("\n", file_get_contents($file))),
    		'parse_mode'=>'markdown',
        ]);
		}
	}
	public function getFollowers($id,$mid,$uuu,$maxId = null){
	    $config = json_decode(file_get_contents('config.json'),1);
	    $from = 'Followers';
		$file = $this->file;
		$rank_token = $this->UUID();
		$datas['rank_token'] = $rank_token;
		if($maxId != null){
			$datas['max_id'] = $maxId;
		}
		$res = $this->request("friendships/$id/followers/",1,0,$datas,0);
		if(isset($res->users)){
			$in = explode("\n",file_get_contents($file));
			foreach($res->users as $user){
				if(!in_array($user->username, $in)){
					$users[] = $user->username;
					file_put_contents($file, $user->username."\n",FILE_APPEND);
				}
				}
    	
    	bot('editmessageText',[
    		'chat_id'=>$config['id'],
    		'message_id'=>$mid,
    		'text'=>"*Collection From* ~ [ _ $from _ ]\n\n*Status* ~> _ Working _\n*Users* ~> _ ".count(explode("\n", file_get_contents($file)))."_",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
			[['text'=>'Stop.','callback_data'=>'stopgr']]
		]])
    	]);
		}
		if($res->next_max_id != null){
			$this->getFollowers($id,$mid,$uuu,$res->next_max_id);
		} else {
			bot('editmessageText',[
    		'chat_id'=>$config['id'],
    		'text'=>"*Collection From* ~ [ _ $from _ ]\n\n*Status* ~> _ Working _\n*Users* ~> _ ".count(explode("\n", file_get_contents($file)))."_",
	'parse_mode'=>'markdown',
	'reply_markup'=>json_encode(['inline_keyboard'=>[
			[['text'=>'Stop.','callback_data'=>'stopgr']]
		]])
    	]);
    	bot('sendMessage',[
    		'chat_id'=>$config['id'],
    		'reply_to_message_id'=>$mid,
    		'text'=>"*Done Collection . * \n All : ".count(explode("\n", file_get_contents($file))),
    		'parse_mode'=>'markdown',
        ]);
		}
	}
	
	
	public function bot($method,$datas=[]){
    $token = file_get_contents('token');
		$url = "https://api.telegram.org/bot".$token."/".$method;
		$ch = curl_init();
		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$datas);
		$res = curl_exec($ch);
		
		if(curl_error($ch)){
			return curl_error($ch);
		}else{
		    
			return json_decode($res);
		}
	}
	public function generateUserBreadcrumb($size){
      $key = 'iN4$aGr0m';
      $date = (int) (microtime(true) * 1000);
      $term = rand(2, 3) * 1000 + $size * rand(15, 20) * 100;
      $text_change_event_count = round($size / rand(2, 3));
      if ($text_change_event_count == 0) {
          $text_change_event_count = 1;
      }
      $data = $size.' '.$term.' '.$text_change_event_count.' '.$date;
      return base64_encode(hash_hmac('sha256', $data, $key, true))."\n".base64_encode($data)."\n";
  }
	private function GUID(){
    if (function_exists('com_create_guid') === true){
        return trim(com_create_guid(), '{}');
    }

    return sprintf('%04X%04X-%04X-%04X-%04X-%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(16384, 20479), mt_rand(32768, 49151), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
	}
	private function UUID(){
    $uuid = sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
    
    return $uuid;
	}
	private function request($path,$account = 0,$post = 0,$datas = 0,$returnHeaders = 0){
		$ch = curl_init(); 
	  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	  if($post == 1){
	  	curl_setopt($ch, CURLOPT_POST, 1);
	  }
	  if($datas != 0 and $post == 1){
		  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datas));
		  curl_setopt($ch, CURLOPT_URL, $this->url .'/'. $path); 
	  } elseif($datas != 0 and $post == 0){
	  	curl_setopt($ch, CURLOPT_URL, $this->url .'/'. $path.'?'.http_build_query($datas)); 
	  } else {
	  	curl_setopt($ch, CURLOPT_URL, $this->url .'/'. $path); 
	  }
	  if($account == 0){
	  	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
		     'x-ig-capabilities: 3w==',
		     'user-agent: Instagram 27.0.0.7.97 Android (23/6.0.1; 640dpi; 1440x2392; LGE/lge; RS988; h1; h1; en_US)',
		     'host: i.instagram.com',
		     'X-CSRFToken: missing',
		     'X-Instagram-AJAX: 1',
		     'Content-Type: application/x-www-form-urlencoded',
		     'X-Requested-With: XMLHttpRequest',
		     "Cookie: mid=XUzLlQABAAH63ME45I6TG-i46cOi",
		     'Connection: keep-alive'
		  ));
		  
		  
	  } elseif($account == 1){
	  	curl_setopt($ch, CURLOPT_HTTPHEADER, array(
	      'x-ig-capabilities: 3w==',
	      'user-agent: '.$this->account['useragent'],
	      'host: i.instagram.com',
	      'X-CSRFToken: missing',
	      'X-Instagram-AJAX: 1',
	      'Content-Type: application/x-www-form-urlencoded',
	      'X-Requested-With: XMLHttpRequest',
	      "Cookie: ".$this->account['cookies'],
	      'Connection: keep-alive'
	  ));
	  }
	  if($returnHeaders == 1){
		  curl_setopt($ch, CURLOPT_HEADER, 1);
		  $res = curl_exec($ch);
		  $res = explode("\r\n\r\n", $res);
	  } else {
		  $res = curl_exec($ch);
		  $res = json_decode($res);
	  }
	  return $res;
	}
}
