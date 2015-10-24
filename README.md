# SlackPhp
Easy to use PHP library to post messages in Slack.

# Setup
Log in at slack.com with your team. Go to the page with all your integrations. Add a new incoming webhook.

Select a default channel to post your messages.
![Setup1]
(http://www.cloock.be/uploads/slack1.png)

Confirm "Add Incoming WebHook integration"
Next, you will find your WebHook URL which you need to use this library. Save it somewhere secure.

![Setup2]
(http://www.cloock.be/uploads/slack2.png)

When you scroll all the way down, you get more options to change your default username, description and icon. You can overwrite these in your code.
	 
# Usage
## Simple message

```php
	// Use the url you got earlier
	$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');
	
	// Create a new message
	$message = new SlackMessage($slack)->setText("Hello world!");
	
	// Send it!
	if ($message->send()){
		echo "Hurray :D";
	}else{
		echo "Failed :(";
	}

```

## Send to a specified channel
```php
	// Use the url you got earlier
	$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');
	
	// Create a new message
	$message = new SlackMessage($slack)->setText("Hello world!")->setChannel("#general");
	
	// Send it!
	$message->send();

```

## Send to a specified user
```php
	// Use the url you got earlier
	$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');
	
	// Create a new message
	$message = new SlackMessage($slack)->setText("Hello world!")->setChannel("@simonbackx");
	
	// Send it!
	$message->send();

```
## Overwriting defaults
You can overwrite the defaults on two levels: in a Slack instance (defaults for all messages using this Slack instance) or SlackMessage instances (only for the current message). These methods will not modify your root defaults at Slack.com, but will overwrite them temporary in your code.

```php
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');
$slack->setDefaultUsername("SlackPHP robot"); 
$slack->setDefaultChannel("#general");

// Unfurl links: automatically fetch and create attachments for detected URLs
$slack->setDefaultUnfurlLinks(true); 

$slack->setDefaultIcon("http://www.domain.com/robot.png"); 
$slack->setDefaultEmoji(":ghost:");

// Create a new message
$message = new SlackMessage($slack)->setText("Hello world!");
$message->setChannel("#general");

// Unfurl links: automatically fetch and create attachments for detected URLs
$message->setUnfurlLinks(false);

$message->setIcon("http://www.domain.com/robot2.png");
$message->setEmoji(":simple_smile:");

// Send it!
$message->send();

```

## Attachments
### Create an attachment
Check out https://api.slack.com/docs/attachments for more details

```php
	$attachment = new SlackAttachment("Required plain-text summary of the attachment.");
	$attachment->setColor("#36a64f");
	$attachment->setText("Optional text that appears within the attachment");
	$attachment->setPretext("Optional text that appears above the attachment block");
	$attachment->setAuthor("Author name", "Optional author link e.g. http://flickr.com/bobby/", "Optional author icon e.g. http://flickr.com/bobby/picture.jpg");
	$attachment->setTitle("Title", "Optional link e.g. http://www.cloock.be/");
	$attachment->setImage("http://www.domain.com/picture.jpg");
	
	 // Add fields, last parameter stand for short (smaller field) and is optional
	$attachment->addField("Title", "Value", true);
	$attachment->addField("Title2", "Value2", true);
	$attachment->addField("Title", "Value", false);
```

### Add (multiple) attachments
```php
	$message = new SlackMessage($slack);
	$message->addAttachment($attachment1);
	$message->addAttachment($attachment2);
	$message->send();
```
Or short
```php
	$message = new SlackMessage($slack)->addAttachment($attachment1)->addAttachment($attachment2)->send();
```

# Warning
Each message requires a new request. So don't send too much messages. The current hardcoded maximum is 10, change it in slack.php.