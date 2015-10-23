<?php 
/*
* Free for commercial use.
* Created by Simon Backx
* You're free to make changes to this file, but keep attribution in comments. Have fun coding!
*
* Check Github for more details.
*/

/**
	Main Object. Construct it by passing your webhook url from slack.com (e.g. https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX)
	Needed for posting Slack Messages
*/
class Slack{
	// WebhookUrl e.g. https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX
	private $url;
	
	// Maximum amount of posts per page load. Keep this low for safety. Each post you place requires your server to send some data to slack, and that can take some time.
	const MAX_POSTS = 2; 
	
	public $posts = 0;
	
	// Empty => Default username set in Slack Webhook integration settings
	public $username; 
	
	// Empty => Default channel set in Slack Webhook integration settings
	public $channel;
	
	// Empty => Default icon set in Slack Webhook integration settings
	public $icon_url;
	
	// Empty => Default icon set in Slack Webhook integration settings
	public $icon_emoji;
	
	// Unfurl links: automatically fetch and create attachments for URLs
	// Empty = default (false)
	public $unfurl_links;
		
	function __construct($webhookUrl){
		$this->url = $webhookUrl;
	}
	
	public function __isset($property){
	    return isset($this->$property);
	}
	
	public function send(SlackMessage $message){
		if ($this->posts >= self::MAX_POSTS){
			return false;
		}
	
		// Loading defaults
		$this->posts++;
		
		if (isset($this->username))
			$username = $this->username;
		if (isset($this->channel))
			$channel = $this->channel;
		if (isset($this->icon_url))
			$icon_url = $this->icon_url;
		if (isset($this->icon_emoji))
			$icon_emoji = $this->icon_emoji;
		if (isset($this->unfurl_links))
			$unfurl_links = $this->unfurl_links;
			
		// Overwrite/create defaults
		if (isset($message->username))
			$username = $message->username;
		if (isset($message->channel))
			$channel = $message->channel;
		if (isset($message->icon_url))
			$icon_url = $message->icon_url;
		if (isset($message->icon_emoji))
			$icon_emoji = $message->icon_emoji;
		if (isset($message->unfurl_links))
			$unfurl_links = $message->unfurl_links;
		
		$data = array(
			'text' => $message->text
		);
		if (isset($username))
			$data['username'] = $username;
		if (isset($channel))
			$data['channel'] = $channel;
		if (isset($icon_url)){
			$data['icon_url'] = $icon_url;
		}else{
			if (isset($icon_emoji))
				$data['icon_emoji'] = $icon_emoji;
		}
		if (isset($unfurl_links))
			$data['unfurl_links'] = $unfurl_links;
			
		if (isset($message->attachments)){
			$attachments = array();
			
			foreach ($message->attachments as $attachment) {
				$attachments[] = $attachment->toArray();
			}
			$data['attachments'] = $attachments;
			
		}
		
		try {
			$json = json_encode($data);
			
			$curl = curl_init();
			curl_setopt_array($curl, array(
			    CURLOPT_RETURNTRANSFER => 1,
			    CURLOPT_URL => $this->url,
			    CURLOPT_USERAGENT => 'cURL Request',
			    CURLOPT_POST => 1,
			    CURLOPT_POSTFIELDS => array('payload' => $json)
			));
			$result = curl_exec($curl);
			if (!$result){
				return false;
			}
			curl_close($curl);
			if ($result == 'ok'){
				return true;
			}
			return false;
		}
		catch (Exception $e) {
			return false;
		}
		
	}
	function setUsername($username) {
		$this->username = $username;
		return $this;
	}
	function setEmoji($emoji) {
		$this->icon_emoji = $emoji;
		return $this;
	}

}
class SlackMessage{
	private $slack;
	
	// Message to post
	public $text = "";
	
	// Empty => Default username set in Slack instance
	public $username;
	
	// Empty => Default channel set in Slack instance
	public $channel;
	
	// Empty => Default icon set in Slack instance
	public $icon_url;
	
	// Empty => Default icon set in Slack instance
	public $icon_emoji;
	
	public $unfurl_links;
	
	// Array of SlackAttachment instances
	public $attachments;
	
	function __construct(Slack $slack){
		$this->slack = $slack;
	}
	/*
		Settings
	*/
	function setText($text){
		$this->text = $text;
		return $this;
	}
	function setUsername($username){
		$this->username = $username;
		return $this;
	}
	function setChannel($channel){
		$this->channel = $channel;
		return $this;
	}
	function setEmoji($emoji){
		$this->icon_emoji = $emoji;
		return $this;
	}
	function setIcon($url){
		$this->icon_url = $url;
		return $this;
	}
	function setUnfurlLinks($bool){
		$this->unfurl_links = $bool;
		return $this;
	}
	function addAttachment(SlackAttachment $attachment){
		if (!isset($this->attachments)){
			$this->attachments = array($attachment);
			return $this;
		}
		$this->attachments[] = $attachment;
		return $this;
	}
	
	/*
		Posting
	*/
	function send(){
		return $this->slack->send($this);
	}
	
	

}

class SlackAttachment{
	// Required
	public $fallback = "";
	
	// Optional
	public $color;
	public $pretext;
	public $author_name;
	public $author_icon;
	public $author_link;
	public $title;
	public $title_link;
	public $text;
	public $fields;
	public $image_url;
	
	function __construct($fallback){
		$this->fallback = $fallback;
	}
	/**
		good, warning, danger or any hex color code
	*/
	function setColor($color){
		$this->color = $color;
		return $this;
	}
	function setText($text) {
		$this->text = $text;
		return $this;
	}
	function setPretext($pretext) {
		$this->pretext = $pretext;
		return $this;
	}
	function setAuthor($author_name, $author_link = NULL, $author_icon = NULL){
		$this->setAuthorName($author_name);
		if (isset($author_link))
			$this->setAuthorLink($author_link);
		if (isset($author_icon))
			$this->setAuthorIcon($author_icon);
		return $this;
	}
	function setAuthorName($author_name) {
		$this->author_name = $author_name;
		return $this;
	}
	/**
		Icon size: 16x16
	*/
	function setAuthorIcon($author_icon) {
		$this->author_icon = $author_icon;
		return $this;
	}
	function setAuthorLink($author_link) {
		$this->author_link = $author_link;
		return $this;
	}
	function setTitle($title, $link = NULL) {
		$this->title = $title;
		if (isset($link)){
			$this->title_link = $link;
		}
		return $this;
	}
	function addFieldInstance(SlackAttachmentField $field){
		if (!isset($this->fields)){
			$this->fields = array($field);
			return $this;
		}
		$this->fields[] = $field;
		return $this;
	}
	/**
		Shortcut without defining SlackAttachmentField
	*/
	function addField($title, $value, $short = NULL){
		return $this->addFieldInstance(new SlackAttachmentField($title, $value, $short));
	}
	function setImage($url) {
		$this->image_url = $url;
		return $this;
	}
	
	function toArray(){
		$data = array(
			'fallback' => $this->fallback
		);
		if (isset($this->color))
			$data['color'] = $this->color;
		if (isset($this->pretext))
			$data['pretext'] = $this->pretext;
		if (isset($this->author_name))
			$data['author_name'] = $this->author_name;
		if (isset($this->author_link))
			$data['author_link'] = $this->author_link;
		if (isset($this->author_icon))
			$data['author_icon'] = $this->author_icon;
		if (isset($this->title))
			$data['title'] = $this->title;
		if (isset($this->title_link))
			$data['title_link'] = $this->title_link;
		if (isset($this->text))
			$data['text'] = $this->text;
		if (isset($this->fields)){
			$fields = array();
			foreach ($this->fields as $field) {
				$fields[] = $field->toArray();
			}
			$data['fields'] = $fields;
		}
		if (isset($this->image_url))
			$data['image_url'] = $this->image_url;
		
		return $data;
	}
}
class SlackAttachmentField{
	// Required
	public $title = "";
	public $value = "";
	
	// Optional
	public $short;
	function __construct($title, $value, $short = NULL) {
		$this->title = $title;
		$this->value = $value;
		if (isset($short)){
			$this->short = $short;
		}
	}
	function setShort($bool = true){
		$this->short = $bool;
		return $this;
	}
	
	function toArray(){
		$data = array(
			'title' => $this->title,
			'value' => $this->value
		);
		if (isset($this->short)){
			$data['short'] = $this->short;
		}
		return $data;
	}
}


?>