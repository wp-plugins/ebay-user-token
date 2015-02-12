=== Ebay User Token ===
Contributors: pws.ru
Tags: ebay, user token, e-commerce
Requires at least: 4.0
Tested up to: 4.0.1
Stable tag: trunk
Donate link: http://pwsdotru.com/donate/
Ebay User Token allow your users add ebay auth token to their metadata.
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Plugin allow your users save ebay token to metadata.


== Installation ==

Upload the Ebay User Token plugin to your blog

Activate it and enter keys for application. You can get it on ebay developer site.

Insert to any page short tag [ebay-user-token]


Configure ebay developer tools

1. Get Application ID, Developer ID and Certification.

2. Generate RuName and set for it success and fail backlink redirect url
   success: https://mysite.tld/?p=num&ebay-user-token=success
   fail: https://mysite.tld/?p=num&ebay-user-token=failed

   Where num - is post id where you insert short tag [ebay-user-token]

Details about ebay developer tools: http://developer.ebay.com/DevZone/guides/ebayfeatures/Basics/Tokens-MultipleUsers.html#OptionWebServerApplications

== Changelog ==

= 1.0 =

* First version
