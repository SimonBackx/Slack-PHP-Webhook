<?php 
/*
	The MIT License (MIT)
	
	Copyright (c) 2015 Simon Backx
	
	Permission is hereby granted, free of charge, to any person obtaining a copy
	of this software and associated documentation files (the "Software"), to deal
	in the Software without restriction, including without limitation the rights
	to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
	copies of the Software, and to permit persons to whom the Software is
	furnished to do so, subject to the following conditions:
	
	The above copyright notice and this permission notice shall be included in all
	copies or substantial portions of the Software.
	
	THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
	IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
	FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
	AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
	LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
	OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
	SOFTWARE.
*/

/**
	*  Main Object. Construct it by passing your webhook url from slack.com (e.g. https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX)
	*  Needed for posting Slack Messages
*/
class Slack{
	// WebhookUrl e.g. https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX
	private $url;
	
	// Maximum amount of posts per page load. Keep this low for safety. Each post you place requires your server to send some data to slack, and that can take some time.
	const MAX_POSTS = 10; 
	
	private $posts = 0;
	
	// Empty => Default username set in Slack Webhook integration settings
	private $username; 
	
	// Empty => Default channel set in Slack Webhook integration settings
	private $channel;
	
	// Empty => Default icon set in Slack Webhook integration settings
	private $icon_url;
	
	// Empty => Default icon set in Slack Webhook integration settings
	private $icon_emoji;
	
	// Unfurl links: automatically fetch and create attachments for URLs
	// Empty = default (false)
	private $unfurl_links;
		
	function __construct($webhookUrl) {
		$this->url = $webhookUrl;
	}
	
	public function __isset($property) {
	    return isset($this->$property);
	}
	
	public function send(SlackMessage $message) {
		if ($this->posts >= self::MAX_POSTS){
			return false;
		}
		$this->posts++;
		
		// Loading defaults
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
		if (isset($icon_url)) {
			$data['icon_url'] = $icon_url;
		} else {
			if (isset($icon_emoji))
				$data['icon_emoji'] = $icon_emoji;
		}

		if (isset($unfurl_links))
			$data['unfurl_links'] = $unfurl_links;
			
		if (isset($message->attachments)) {
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
			
            if (!$result) {
				return false;
			}

			curl_close($curl);
			
            if ($result == 'ok') {
				return true;
			}

			return false;
		}
		catch (Exception $e) {
			return false;
		}
		
	}

	function setDefaultUnfurlLinks($unfurl) {
		$this->unfurl_links = $unfurl;
		return $this;
	}

	function setDefaultChannel() {
		$this->channel = $channel;
		return $this;
	}

	function setDefaultUsername($username) {
		$this->username = $username;
		return $this;
	}

	function setDefaultIcon($url) {
		$this->icon_url = $url;
		return $this;
	}

	function setDefaultEmoji($emoji) {
		$this->icon_emoji = $emoji;
		return $this;
	}
}

class SlackMessage {
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
	
	function __construct(Slack $slack) {
		$this->slack = $slack;
	}

	/*
		Settings
	*/
	function setText($text) {
		$this->text = $text;
		return $this;
	}

	function setUsername($username) {
		$this->username = $username;
		return $this;
	}

	function setChannel($channel) {
		$this->channel = $channel;
		return $this;
	}

	function setEmoji($emoji) {
		$this->icon_emoji = $emoji;
		return $this;
	}

	function setIcon($url) {
		$this->icon_url = $url;
		return $this;
	}

	function setUnfurlLinks($bool) {
		$this->unfurl_links = $bool;
		return $this;
	}

	function addAttachment(SlackAttachment $attachment) {
		if (!isset($this->attachments)) {
			$this->attachments = array($attachment);
			return $this;
		}

		$this->attachments[] = $attachment;
		return $this;
	}
	
	/*
	 * Send this message to Slack
	*/
	function send() {
		return $this->slack->send($this);
	}
}

class SlackAttachment{
	// Required
	public $fallback = "";
	
	// Optionals
	public $color;
	public $pretext;
	public $author_name;
	public $author_icon;
	public $author_link;
	public $title;
	public $title_link;
	public $text;
	public $fields;
	public $mrkdwn_in;
	public $image_url;
	
	function __construct($fallback) {
		$this->fallback = $fallback;
	}

	/**
	 * Accepted values: "good", "warning", "danger" or any hex color code
	*/
	function setColor($color) {
		$this->color = $color;
		return $this;
	}

	function setText($text) {
		$this->text = $text;
		return $this;
	}

    /**
     * Optional text that appears above the attachment block
     */
	function setPretext($pretext) {
		$this->pretext = $pretext;
		return $this;
	}

    /**
     * The author parameters will display a small section at the top of a message attachment.
     * @param string $author_name [description]
     * @param optional string $author_link A valid URL that will hyperlink the author_name text mentioned above. Set to NULL to ignore this value.
     * @param optional string $author_icon A valid URL that displays a small 16x16px image to the left of the author_name text. Set to NULL to ignore this value.
     */
	function setAuthor($author_name, $author_link = NULL, $author_icon = NULL) {
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
     * Enable text formatting for: "pretext", "text" or "fields". 
     * Setting "fields" will enable markup formatting for the value of each field.
     */
	function enableMarkdownFor($mrkdwn_in) {
		if (!isset($this->mrkdwn_in_fields)){
			$this->mrkdwn_in_fields = array($mrkdwn_in);
			return $this;
		}
		$this->mrkdwn_in_fields[] = $mrkdwn_in;
		return $this;
	}

	/**
     * A valid URL that displays a small 16x16px image to the left of the author_name text.
     */
	function setAuthorIcon($author_icon) {
		$this->author_icon = $author_icon;
		return $this;
	}

    /**
     * A valid URL that will hyperlink the author_name text mentioned above.
     */
	function setAuthorLink($author_link) {
		$this->author_link = $author_link;
		return $this;
	}

    /**
     * The title is displayed as larger, bold text near the top of a message attachment. 
     * @param string $title
     * @param optional string $link  By passing a valid URL in the link parameter (optional), the title text will be hyperlinked.
     */
	function setTitle($title, $link = NULL) {
		$this->title = $title;
		if (isset($link)){
			$this->title_link = $link;
		}
		return $this;
	}

	function setImage($url) {
		$this->image_url = $url;
		return $this;
	}
	
	function addFieldInstance(SlackAttachmentField $field) {
		if (!isset($this->fields)){
			$this->fields = array($field);
			return $this;
		}
		$this->fields[] = $field;
		return $this;
	}

	/**
	 * Shortcut without defining SlackAttachmentField
	*/
	function addField($title, $value, $short = NULL) {
		return $this->addFieldInstance(new SlackAttachmentField($title, $value, $short));
	}
	
	
	function toArray() {
		$data = array(
			'fallback' => $this->fallback
		);
		if (isset($this->color))
			$data['color'] = $this->color;
		if (isset($this->pretext))
			$data['pretext'] = $this->pretext;
		if (isset($this->author_name))
			$data['author_name'] = $this->author_name;
		if (isset($this->mrkdwn_in_fields))
			$data['mrkdwn_in'] = $this->mrkdwn_in_fields;
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

class SlackAttachmentField {
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

	function setShort($bool = true) {
		$this->short = $bool;
		return $this;
	}
	
	function toArray() {
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
