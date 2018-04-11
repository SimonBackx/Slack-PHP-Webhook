# Slack PHP Webhook
[![Latest Stable Version](https://poser.pugx.org/simonbackx/slack-php-webhook/v/stable)](https://packagist.org/packages/simonbackx/slack-php-webhook) [![License](https://poser.pugx.org/simonbackx/slack-php-webhook/license)](https://packagist.org/packages/simonbackx/slack-php-webhook)

Easy to use PHP library to post messages in Slack using incoming webhook integrations.

# Setup
Log in at slack.com with your team. Go to the page with all your integrations. Add a new incoming webhook.

Select a default channel to post your messages.
![Setup1](http://www.cloock.be/uploads/slack1.png)

Confirm "Add Incoming WebHook integration"
Next, you will find your WebHook URL which you need to use this library. Save it somewhere secure.

![Setup2](http://www.cloock.be/uploads/slack2.png)

When you scroll all the way down, you get more options to change your default username, description and icon. You can overwrite these in your code.
	 
# Usage
## Installation

### Composer

Add Slack-PHP-Webhook to your composer.json file or run `composer require simonbackx/slack-php-webhook`

```json
{
  "require": {
    "simonbackx/slack-php-webhook": "~1.0"
  }
}
```

### Alternative

Download slack.php and require/include it in your PHP file.

## Simple message

```php
// Use the url you got earlier
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');

// Create a new message
$message = new SlackMessage($slack)->setText("Hello world!");

// Send it!
if ($message->send()) {
    echo "Hurray 😄";
} else {
    echo "Failed 😢";
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

// Set the default icon for messages to a custom image
$slack->setDefaultIcon("http://www.domain.com/robot.png"); 

// Use a 👻 emoji as default icon for messages if it is not overwritten in messages
$slack->setDefaultEmoji(":ghost:");

// Create a new message
$message = new SlackMessage($slack)->setText("Hello world!");
$message->setChannel("#general");

// Unfurl links: automatically fetch and create attachments for detected URLs
$message->setUnfurlLinks(false);

// Set the icon for the message to a custom image
$message->setIcon("http://www.domain.com/robot2.png");

// Overwrite the default Emoji (if any) with 😊
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
$attachment->setText("*Optional text* that appears within the attachment");
$attachment->setPretext("Optional text that appears above the attachment block");
$attachment->setAuthor("Author name", "Optional author link e.g. http://flickr.com/bobby/", "Optional author icon e.g. http://flickr.com/bobby/picture.jpg");
$attachment->setTitle("Title", "Optional link e.g. http://www.cloock.be/");
$attachment->setImage("http://www.domain.com/picture.jpg");

// enableMarkdownFor enables message formatting (https://get.slack.help/hc/en-us/articles/202288908-How-can-I-add-formatting-to-my-messages-) in attachements.
// Possible values: "pretext", "text", "fields"
// More info: https://api.slack.com/docs/message-formatting
$attachment->enableMarkdownFor("text");

 // Add fields, last parameter stand for short (smaller field) and is optional
$attachment->addField("Title", "Value");
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
Each message initiates a new HTTPS request, which takes some time. Don't send too much messages at once if you are not running your script in a background task.
