<?php

	namespace fb;

	if (!defined('FBNL'))
	{
		define ('FBNL', "\u000A");
	}

	class fb {

		private $admin 					= '';

		private $input					= null;

		private $url					= 'https://graph.facebook.com/v2.6/me/messages?access_token=';

		private $access_token			= null;
		private $verify_token			= null;

		private $hub_mode				= null;
		private $hub_challenge			= null;
		private $hub_verify_token		= null;

		public $message					= null;
		public $sender					= null;

		private $quickReply				= [];

		private $triggers				= [];

		public function __construct(string $access_token, string $verify_token, string $admin_id)
		{

			$this->admin = $admin_id;

			$this->input = file_get_contents("php://input");

			$this->access_token = $access_token;

			$this->url = $this->url . $this->access_token;

			$this->verify_token = $verify_token;

			$this->hub_mode = $this->testRequest('hub_mode');
			$this->hub_challenge = $this->testRequest('hub_challenge');
			$this->hub_verify_token = $this->testRequest('hub_verify_token');

			if ($this->hub_mode && $this->hub_verify_token || empty($this->input))
			{
				if ($this->hub_mode == 'subscribe' && $this->hub_verify_token == $this->verify_token)
				{
					echo $this->hub_challenge;
				}
				else
				{

				}
				exit();
			}

			$this->sender = $this->admin;

			if(!empty($this->input))
			{
				$this->input = json_decode($this->input, true);

				$this->sender = $this->input['entry'][0]['messaging'][0]['sender']['id'];
				$this->message = $this->input['entry'][0]['messaging'][0]['message']['text'];

				$this->message = trim($this->message);

				if (!preg_match("/^[a-z\s]*/i", $this->message))
				{
					exit();
				}
			}

		}

		private function testRequest ($requestName)
		{
			if (isset($_REQUEST[$requestName]))
				return $_REQUEST[$requestName];
			else
				return null;
		}

		private function curl ($jsonData)
		{
			$ch = curl_init($this->url);

			$jsonDataEncoded = $jsonData;

			$curl_opt = [
				CURLOPT_POST			=> true,
				CURLOPT_POSTFIELDS		=> $jsonDataEncoded,
				CURLOPT_HTTPHEADER		=> array('Content-Type: application/json'),
				//CURLOPT_RETURNTRANSFER	=> true
			];


			curl_setopt_array($ch, $curl_opt);

			return curl_exec($ch);
		}

		public function addQuickReply($data)
		{

			if (is_array($data))
			{
				foreach($data as $d)
				{
					$this->addQuickReply($d);
				}
			}
			elseif (is_string($data))
			{
				if (count($this->quickReply) < 13)
					$this->quickReply[] = $data;
			}
		}

		public function trigger($regExp,$func)
		{
			$this->triggers[$regExp] = $func;
		}

		function message($msg,$sender=null)
		{
			if (empty($sender))
				$sender = $this->sender;

			$msg = addslashes($msg);

			$msg = trim($msg);
			$msg = str_replace(
				["\r\n","\n","\r"],
				FBNL,
				$msg
			);

			$msg = str_replace("\t",'',$msg);

			$replys = '';
			$qr = [];

			if (!empty($this->quickReply))
			{
				foreach($this->quickReply as $k => $r)
				{
					$qr[$k] = '{
						"content_type":"text",
						"title":"'.$r.'",
						"payload":"<POSTBACK_PAYLOAD>"
					  }';
				}
				$replys = ',"quick_replies":['.implode(',',$qr).']';
			}


			//The JSON data.
			$jsonData = '{
				"recipient":{
					"id":"'.$sender.'"
				},
				"message":{
					"text":"'.$msg.'"'.$replys.'
				}
			}';

			$curl = $this->curl($jsonData);

			$curl = json_decode($curl,true);

			if (is_array($curl) && array_key_exists('error',$curl))
			{

			}

			return $curl;
		}

		public function sendImage($url,$sender=null)
		{

			if (empty($sender))
				$sender = $this->sender;

			if (is_numeric($url))
				$i = '"attachment_id": "'.$url.'"';
			elseif (filter_var($url, FILTER_VALIDATE_URL))
				$i = '"url": "'.$url.'",
							"is_reusable": true';

			$jsonData = '{
				"recipient":{
					"id":"'.$sender.'"
				},
				"message":{
					"attachment":{
						"type": "image",
						"payload": {
							'.$i.'
						}
					}
				}
			}';
			return $this->curl($jsonData);
		}

		public function run()
		{
			foreach($this->triggers as $regExp => $func)
			{
				if (preg_match($regExp,$this->message))
				{
					if ($func($this) === true)
						break;
				}
			}
		}


		public function isAdmin()
		{
			return $this->sender == $this->admin;
		}

		public function admin()
		{
			return $this->admin;
		}
	}
