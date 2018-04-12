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
$message = new SlackMessage($slack);
$message->setText("Hello world!");

// Send it!
if ($message->send()) {
    echo "Hurray 😄";
} else {
    echo "Failed 😢";
}
```

## Send to a channel
```php
// Use the url you got earlier
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');

// Create a new message
$message = new SlackMessage($slack);
$message->setText("Hello world!")->setChannel("#general");

// Send it!
$message->send();
```

## Send to a user
```php
// Use the url you got earlier
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');

// Create a new message
$message = new SlackMessage($slack);
$message->setText("Hello world!")->setChannel("@simonbackx");

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
$message = new SlackMessage($slack);
$message->setText("Hello world!");
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
// Use the url you got earlier
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');
$slack->setDefaultUsername('Fly company');

// Create a new message
$message = new SlackMessage($slack);

$attachment = new SlackAttachment("Required plain-text summary of the attachment.");
$attachment->setColor("#36a64f");
$attachment->setText("*Optional text* that appears within the attachment");
$attachment->setPretext("Optional text that appears above the attachment block");
$attachment->setAuthor(
    "Author name", 
    "http://flickr.com/bobby/", //Optional author link
    "http://flickr.com/bobby/picture.jpg" // Optional author icon
);
$attachment->setTitle("Title", "Optional link e.g. http://www.google.com/");
$attachment->setImage("http://www.domain.com/picture.jpg");

/*
Slack messages may be formatted using a simple markup language similar to Markdown. Supported 
formatting includes: ```pre```, `code`, _italic_, *bold*, and even ~strike~.; full details are 
available on the Slack help site.

By default bot message text will be formatted, but attachments are not. To enable formatting on 
attachment fields, you can use enableMarkdownFor
 */
$attachment->enableMarkdownFor("text");
$attachment->enableMarkdownFor("pretext");
$attachment->enableMarkdownFor("fields");

 // Add fields, last parameter stand for short (smaller field) and is optional
$attachment->addField("Title", "Value");
$attachment->addField("Title2", "Value2", true);
$attachment->addField("Title", "Value", false);

// Add a footer
$attachment->setFooterText('By Simon');
$attachment->setFooterIcon('https://www.simonbackx.com/favicon.png');
$attachment->setTimestamp(time());

// Add it to your message
$message->addAttachment($attachment);

// Send
$message->send();
```
[View the result](https://api.slack.com/docs/messages/builder?msg=%7B%0A%20%20%20%20%22text%22%3A%20%22%22%2C%0A%20%20%20%20%22username%22%3A%20%22Fly%20company%22%2C%0A%20%20%20%20%22attachments%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%22fallback%22%3A%20%22Required%20plain-text%20summary%20of%20the%20attachment.%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22color%22%3A%20%22%2336a64f%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22pretext%22%3A%20%22Optional%20text%20that%20appears%20above%20the%20attachment%20block%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22author_name%22%3A%20%22Author%20name%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22mrkdwn_in%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22text%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22pretext%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22fields%22%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22author_link%22%3A%20%22http%3A%2F%2Fflickr.com%2Fbobby%2F%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22author_icon%22%3A%20%22http%3A%2F%2Fflickr.com%2Fbobby%2Fpicture.jpg%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22title%22%3A%20%22Title%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22title_link%22%3A%20%22Optional%20link%20e.g.%20http%3A%2F%2Fwww.google.com%2F%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22text%22%3A%20%22%2AOptional%20text%2A%20that%20appears%20within%20the%20attachment%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22fields%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22title%22%3A%20%22Title%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22value%22%3A%20%22Value%22%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22title%22%3A%20%22Title2%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22value%22%3A%20%22Value2%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22short%22%3A%20true%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22title%22%3A%20%22Title%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22value%22%3A%20%22Value%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22short%22%3A%20false%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22image_url%22%3A%20%22http%3A%2F%2Fwww.domain.com%2Fpicture.jpg%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22footer%22%3A%20%22By%20Simon%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22footer_icon%22%3A%20%22https%3A%2F%2Fwww.simonbackx.com%2Ffavicon.png%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22ts%22%3A%201523486931%0A%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%5D%0A%7D)

## Add buttons
```php
// Use the url you got earlier
$slack = new Slack('https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX');
$slack->setDefaultUsername('Fly company');

// Create a new message
$message = new SlackMessage($slack);
$message->setText("<@W1A2BC3DD> approved your travel request. Book any airline you like by continuing below.");

// Create a new Attachment with fallback text, a plain-text summary of the attachment. 
// This text will be used in clients that don't show formatted text (eg. IRC, mobile 
// notifications) and should not contain any markup.
$attachment = new \SlackAttachment('Book your flights at https://flights.example.com/book/r123456');
$attachment->addButton('Book flights 🛫', 'https://flights.example.com/book/r123456');
$attachment->addButton('Unsubscribe', 'https://flights.example.com/unsubscribe', 'danger');

$message->addAttachment($attachment);

$message->send();
```
[View the result](https://api.slack.com/docs/messages/builder?msg=%7B%0A%20%20%20%20%22text%22%3A%20%22%3C%40W1A2BC3DD%3E%20approved%20your%20travel%20request.%20Book%20any%20airline%20you%20like%20by%20continuing%20below.%22%2C%0A%20%20%20%20%22username%22%3A%20%22Fly%20company%22%2C%0A%20%20%20%20%22icon_emoji%22%3A%20%22%3Aairplane%3A%22%2C%0A%20%20%20%20%22attachments%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%22fallback%22%3A%20%22Book%20your%20flights%20at%20https%3A%2F%2Fflights.example.com%2Fbook%2Fr123456%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%22actions%22%3A%20%5B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22type%22%3A%20%22button%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22text%22%3A%20%22Book%20flights%20%F0%9F%9B%AB%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22url%22%3A%20%22https%3A%2F%2Fflights.example.com%2Fbook%2Fr123456%22%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7B%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22type%22%3A%20%22button%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22text%22%3A%20%22Unsubscribe%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22url%22%3A%20%22https%3A%2F%2Fflights.example.com%2Funsubscribe%22%2C%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%22style%22%3A%20%22danger%22%0A%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%20%20%20%20%20%20%20%20%5D%0A%20%20%20%20%20%20%20%20%7D%0A%20%20%20%20%5D%0A%7D)

### Add (multiple) attachments
```php
$message = new SlackMessage($slack);
$message->addAttachment($attachment1);
$message->addAttachment($attachment2);
$message->send();
```

## Short syntax

All methods support a short syntax. E.g.:

```php
(new SlackMessage($slack))
    ->addAttachment($attachment1)
    ->addAttachment($attachment2)
    ->send();
```

# Warning
Each message initiates a new HTTPS request, which takes some time. Don't send too much messages at once if you are not running your script in a background task.
