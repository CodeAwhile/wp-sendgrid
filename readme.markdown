=== WP SendGrid ===
Contributors: itsananderson 
Tags: email
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.0.1

Extends the wp_mail() function to use the SendGrid API to send emails

== Description ==

WordPress relies on email for various notifications, such as new user messages and comments. Many plugins also use email to notify blog owners about various events. For anyone who develops in Windows, this is frustrating, because PHP can't send emails by default.

SendGrid is a service that lets you send emails through an API (among other things). WP SendGrid is a plugin that extends WordPress' wp_mail() function so that it sends emails through SendGrid's API. If you develop on Windows (or any other environment where you have trouble sending emails with WordPress), you're going to love WP SendGrid.

To install, enable WP SendGrid like you would any other WordPress plugin. Enter your SendGrid credentials (you'll need a SendGrid account), and you should be ready to go. If you wish, you can also choose between SendGrid's REST API and their SMTP servers, and whether to connect to SendGrid using a secure connection.

== Installation ==

1. Upload the WP SendGrid to the /wp-contents/plugins/ folder.
1. Activate the plugin from the 'Plugins' menu in WordPress.
1. Configure WP SendGrid with your SendGrid API credentials. 

== Changelog ==

= 1.0.1 =
* Remove hardcoded "from" address and use WordPress provided address instead

= 1.0 =
* Initial release
