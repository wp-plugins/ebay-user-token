<?php
/**
 * Plugin Name:  Ebay User Token
 * Text Domain: ebay-user-token
 * Plugin URI: http://pwsdotru.com/wordpress/ebay-user-token
 * Description: Allow blog users save ebay user token to metadata
 * Version: 1.0.0
 * Author: Aleksandr Novikov
 * Author URI: http://pwsdotru.com/
 */

$CLASS_NAME = "WP_EbayUserToken";
require_once(__DIR__ . "/includes/ebay-api.php");
require_once(__DIR__ . "/includes/" . $CLASS_NAME . ".php");

register_activation_hook(__FILE__, array($CLASS_NAME, "install"));

add_action("init", array($CLASS_NAME, "init"));

