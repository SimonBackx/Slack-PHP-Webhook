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
class Slack {
    // WebhookUrl e.g. https://hooks.slack.com/services/XXXXXXXXX/XXXXXXXXX/XXXXXXXXXXXXXXXXXXXXXXXX
    public $url;

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

    function __construct($webhookUrl) {
        $this->url = $webhookUrl;
    }

    function __isset($property) {
        return isset($this->$property);
    }

    function send(SlackMessage $message) {
        $data = $message->toArray();

        try {
            $json = json_encode($data);

            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_URL => $this->url,
                CURLOPT_USERAGENT => 'cURL Request',
                CURLOPT_POST => 1,
                CURLOPT_POSTFIELDS => array('payload' => $json),
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
        } catch (Exception $e) {
            return false;
        }

    }

    function setDefaultUnfurlLinks($unfurl) {
        $this->unfurl_links = $unfurl;
        return $this;
    }

    function setDefaultChannel($channel) {
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

    function toArray() {
        // Loading defaults
        if (isset($this->slack->username)) {
            $username = $this->slack->username;
        }

        if (isset($this->slack->channel)) {
            $channel = $this->slack->channel;
        }

        if (isset($this->slack->icon_url)) {
            $icon_url = $this->slack->icon_url;
        }

        if (isset($this->slack->icon_emoji)) {
            $icon_emoji = $this->slack->icon_emoji;
        }

        if (isset($this->slack->unfurl_links)) {
            $unfurl_links = $this->slack->unfurl_links;
        }

        // Overwrite/create defaults
        if (isset($this->username)) {
            $username = $this->username;
        }

        if (isset($this->channel)) {
            $channel = $this->channel;
        }

        if (isset($this->icon_url)) {
            $icon_url = $this->icon_url;
        }

        if (isset($this->icon_emoji)) {
            $icon_emoji = $this->icon_emoji;
        }

        if (isset($this->unfurl_links)) {
            $unfurl_links = $this->unfurl_links;
        }

        $data = array(
            'text' => $this->text,
        );
        if (isset($username)) {
            $data['username'] = $username;
        }

        if (isset($channel)) {
            $data['channel'] = $channel;
        }

        if (isset($icon_url)) {
            $data['icon_url'] = $icon_url;
        } else {
            if (isset($icon_emoji)) {
                $data['icon_emoji'] = $icon_emoji;
            }

        }

        if (isset($unfurl_links)) {
            $data['unfurl_links'] = $unfurl_links;
        }

        if (isset($this->attachments)) {
            $attachments = array();
            foreach ($this->attachments as $attachment) {
                $attachments[] = $attachment->toArray();
            }
            $data['attachments'] = $attachments;
        }
        return $data;
    }

    /*
     * Send this message to Slack
     */
    function send() {
        return $this->slack->send($this);
    }
}

class SlackAttachment {
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
    public $thumb_url;

    // Footer
    public $footer;
    public $footer_icon;
    public $ts;

    // Actions
    public $actions;

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
        if (isset($author_link)) {
            $this->setAuthorLink($author_link);
        }

        if (isset($author_icon)) {
            $this->setAuthorIcon($author_icon);
        }

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
        if (!isset($this->mrkdwn_in_fields)) {
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
     * @param optional string $link  By passing a valid URL in the link parameter (optional), the
     * title text will be hyperlinked.
     */
    function setTitle($title, $link = NULL) {
        $this->title = $title;
        if (isset($link)) {
            $this->title_link = $link;
        }
        return $this;
    }

    /**
     * A valid URL to an image file that will be displayed inside a message attachment. We currently
     *  support the following formats: GIF, JPEG, PNG, and BMP.
     *
     *  Large images will be resized to a maximum width of 400px or a maximum height of 500px, while
     *   still maintaining the original aspect ratio.
     * @param [type] $url [description]
     */
    function setImage($url) {
        $this->image_url = $url;
        return $this;
    }

    /**
     * A valid URL to an image file that will be displayed as a thumbnail on the right side of a
     * message attachment. We currently support the following formats: GIF, JPEG, PNG, and BMP.
     *
     * The thumbnail's longest dimension will be scaled down to 75px while maintaining the aspect
     * ratio of the image. The filesize of the image must also be less than 500 KB.
     *
     * For best results, please use images that are already 75px by 75px.
     * @param string $url HTTP url of the thumbnail
     */
    function setThumbnail($url) {
        $this->thumb_url = $url;
        return $this;
    }

    /**
     * Add some brief text to help contextualize and identify an attachment. Limited to 300
     * characters, and may be truncated further when displayed to users in environments with limited
     *  screen real estate.
     * @param string $text max 300 characters
     */
    function setFooterText($text) {
        $this->footer = $text;
        return $this;
    }

    /**
     * To render a small icon beside your footer text, provide a publicly accessible URL string in
     * the footer_icon field. You must also provide a footer for the field to be recognized.
     *
     * We'll render what you provide at 16px by 16px. It's best to use an image that is similarly
     * sized.
     * @param string $url 16x16 image url
     */
    function setFooterIcon($url) {
        $this->footer_icon = $url;
        return $this;
    }

    /**
     * Does your attachment relate to something happening at a specific time?
     *
     * By providing the ts field with an integer value in "epoch time", the attachment will display
     * an additional timestamp value as part of the attachment's footer. Use ts when referencing
     * articles or happenings. Your message will have its own timestamp when published.
     *
     * Example: Providing 123456789 would result in a rendered timestamp of Nov 29th, 1973.
     * @param int $timestamp Integer value in "epoch time"
     */
    function setTimestamp($timestamp) {
        $this->ts = $timestamp;
        return $this;
    }

    function addFieldInstance(SlackAttachmentField $field) {
        if (!isset($this->fields)) {
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

    private function addAction($action) {
        if (!isset($this->actions)) {
            $this->actions = array($action);
            return $this;
        }
        $this->actions[] = $action;
        return $this;
    }

    /**
     * @param string $text  A UTF-8 string label for this button. Be brief but descriptive and
     * actionable.
     * @param string $url   The fully qualified http or https URL to deliver users to. Invalid URLs
     * will result in a message posted with the button omitted
     * @param string $style  (optional) Setting to primary turns the button green and indicates the
     * best forward action to take. Providing danger turns the button red and indicates it some kind
     *  of destructive action. Use sparingly. Be default, buttons will use the UI's default text
     *  color.
     */
    function addButton($text, $url, $style = null) {
        $action = (object) [
            "type" => "button",
            "text" => $text,
            "url" => $url,
        ];
        if (isset($style)) {
            $action->style = $style;
        }
        $this->addAction($action);
        return $this;
    }

    function toArray() {
        $data = array(
            'fallback' => $this->fallback,
        );
        if (isset($this->color)) {
            $data['color'] = $this->color;
        }

        if (isset($this->pretext)) {
            $data['pretext'] = $this->pretext;
        }

        if (isset($this->author_name)) {
            $data['author_name'] = $this->author_name;
        }

        if (isset($this->mrkdwn_in_fields)) {
            $data['mrkdwn_in'] = $this->mrkdwn_in_fields;
        }

        if (isset($this->author_link)) {
            $data['author_link'] = $this->author_link;
        }

        if (isset($this->author_icon)) {
            $data['author_icon'] = $this->author_icon;
        }

        if (isset($this->title)) {
            $data['title'] = $this->title;
        }

        if (isset($this->title_link)) {
            $data['title_link'] = $this->title_link;
        }

        if (isset($this->text)) {
            $data['text'] = $this->text;
        }

        if (isset($this->fields)) {
            $fields = array();
            foreach ($this->fields as $field) {
                $fields[] = $field->toArray();
            }
            $data['fields'] = $fields;
        }

        if (isset($this->image_url)) {
            $data['image_url'] = $this->image_url;
        }

        if (isset($this->thumb_url)) {
            $data['thumb_url'] = $this->thumb_url;
        }

        if (isset($this->footer)) {
            $data['footer'] = $this->footer;
        }

        if (isset($this->footer_icon)) {
            $data['footer_icon'] = $this->footer_icon;
        }

        if (isset($this->ts)) {
            $data['ts'] = $this->ts;
        }

        if (isset($this->actions)) {
            $data['actions'] = (array) $this->actions;
        }

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
        if (isset($short)) {
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
            'value' => $this->value,
        );
        if (isset($this->short)) {
            $data['short'] = $this->short;
        }
        return $data;
    }
}
?>
