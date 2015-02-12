<?php

class WP_EbayUserToken {
  static $prefix = "ebay-user-token";
  static $hooks = array("admin_notices", "admin_menu");
  public static function install() {

  }

  public static function init() {
    load_plugin_textdomain(self::$prefix, false, basename( dirname( __FILE__ ) ) . "/languages");
    foreach(self::$hooks AS $hook) {
        add_action($hook, array(__CLASS__, $hook));
    }
    add_shortcode(self::$prefix, array(__CLASS__, "shortcode"));
  }

  public static function admin_menu() {
    add_options_page(__("Ebay User Token", self::$prefix) . ":" . __("Settings", self::$prefix),
                     __("Ebay User Token", self::$prefix),
                    "manage_options", self::$prefix, array(__CLASS__, "options"));
  }

  public static function admin_notices() {
    if (self::AppId() == "" || self::DevId() == "" || self::Cert() == "" || self::RuName() == "") {
      echo('<div class="update-nag"><p>');
      _e('Ebay User Token', self::$prefix);
      echo(': ');
      _e('For continue work you should configure plugin.', self::$prefix);
      echo(' <a href="'. get_site_url() . '/wp-admin/options-general.php?page=' . self::$prefix . '">');
      _e('Click here for settings', self::$prefix);
      echo('</a>');
      echo('</p></div>');
    }
  }

  public static function options() {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST[self::$prefix . "-action"]) && $_POST[self::$prefix . "-action"] == "save") {
      self::AppId(strip_tags(trim($_POST[self::$prefix . "-app-id"])));
      self::DevId(strip_tags(trim($_POST[self::$prefix . "-dev-id"])));
      self::Cert(strip_tags(trim($_POST[self::$prefix . "-cert"])));
      self::RuName(strip_tags(trim($_POST[self::$prefix . "-runame"])));
      echo('<div id="message" class="updated fade"><p><strong>');
      _e("Settings has been updated", self::$prefix);
      echo('</strong></p></div>');
    }
    echo('<div class="wrap"><h2>');
	  _e("Ebay User Token", self::$prefix);
    echo(": ");
    _e("Settings", self::$prefix);
    echo('</h2><br class="clear" />');
    echo('<p><a target="_blank" style="font-size: 0.8em" href="http://developer.ebay.com/Devzone/xml/docs/HowTo/Tokens/GettingTokens.html#step1">');
    _e("How get application ids", self::$prefix);
    echo('</a></p>');
    echo('<form method="post">');
    echo("<p>");
    _e("APP ID", self::$prefix);
    echo(':<br /><input type="text" name="' . self::$prefix . '-app-id' . '" value="'. self::AppId() . '" size="50" /></p>');
    echo("<p>");
    _e("DEV ID", self::$prefix);
    echo(':<br /><input type="text" name="' . self::$prefix . '-dev-id' . '" value="'. self::DevId() . '" size="50" /></p>');
    echo("<p>");
    _e("CERT", self::$prefix);
    echo(':<br /><input type="text" name="' . self::$prefix . '-cert' . '" value="'. self::Cert() . '" size="50" /></p>');
    echo("<p>");
    _e("RUNAME", self::$prefix);
    echo(':<br /><input type="text" name="' . self::$prefix . '-runame' . '" value="'. self::RuName() . '" size="50" /></p>');
    echo('<p><input type="hidden" name="' . self::$prefix .'-action" value="save" />');
    echo('<input type="submit" value="' . __("Save", self::$prefix) . '" />');
    echo('</form>');
  }

  public static function shortcode($atts = array()) {
    $user_id = get_current_user_id();
    if ($user_id == 0) {
      return self::NeedLogin();
    } else {
      if (isset($_GET[self::$prefix]) && ($_GET[self::$prefix] == "success" || $_GET[self::$prefix] == "failed")) {
        if ($_GET[self::$prefix] == "failed") {
          return self::HtmlError(__("Failed auth", self::$prefix));
        } else {
          return self::GetToken($user_id);
        }
      } else {
        $token = get_metadata("user", $user_id, self::$prefix . "-auth-token", true);
        if ($token == "" || (is_array($token) && empty($token))) {
          $action = __('Add user token');
        } else {
          $action = __('Change user token');
        }
        return self::ShowWidget($user_id, $action);
      }
    }
    return '';
  }

  private static function GetToken($user_id) {
    $session_id = get_metadata("user", $user_id, self::$prefix . "-ebay-session", true);
    if ($session_id == "") {
      return self::HtmlError(__("Wrong session ID", self::$prefix));
    }
    if ($session_id == "done") {
      return self::HtmlSuccess(__("Token has been saved", self::$prefix));
    }
    $api = new EbayUserTokenAPI();
    $api->init(self::AppId(), self::DevId(), self::Cert(), self::RuName());
    $token = $api->getToken($session_id);
    if ($token == "") {
      return self::HtmlError(__("Error get token", self::$prefix));
    }
    update_metadata("user", $user_id, self::$prefix . "-auth-token", $token);
    update_metadata("user", $user_id, self::$prefix . "-ebay-session", "done");
    return self::HtmlSuccess(__("Token has been received", self::$prefix));
  }

  private static function ShowWidget($user_id, $action) {
    $url = self::LoginUrl($user_id);
    if ($url != null) {
      $html = '<a href="' . $url . '">' . $action . '</a>';
      return $html;
    } else {
      return self::HtmlError(__("Error connect to server", self::$prefix));
    }
  }

  private static function LoginUrl($user_id) {
    $url =  "https://signin.ebay.com/ws/eBayISAPI.dll?SignIn&runame=%s&SessID=%s";
    $api = new EbayUserTokenAPI();
    $api->init(self::AppId(), self::DevId(), self::Cert(), self::RuName());
    $session = $api->getSession();
    if ($session == "") {
      return null;
    }
    update_metadata("user", $user_id, self::$prefix."-ebay-session", $session);
    return sprintf($url, self::RuName(), $session);
  }
  private static function NeedLogin() {
    $html = '<b>';
    $html .= __("You are not logged.");
    $html .= '</b> <a href="' . get_site_url() . '/wp-login.php">';
    $html .= __("Please login to site");
    $html .= '</a>';
    return $html;
  }

  private static function AppId($value = null) {
    $key = self::$prefix . "APP_ID";
    if ($value !== null) {
      update_option($key, $value);
    }
    $value = get_option($key, "");
    return  $value;
  }

  private static function DevId($value = null) {
    $key = self::$prefix . "DEV_ID";
    if ($value !== null) {
      update_option($key, $value);
    }
    $value = get_option($key, "");
    return  $value;
  }

  private static function Cert($value = null) {
    $key = self::$prefix . "CERT";
    if ($value !== null) {
      update_option($key, $value);
    }
    $value = get_option($key, "");
    return  $value;
  }

  private static function RuName($value = null) {
    $key = self::$prefix . "RU_NAME";
    if ($value !== null) {
      update_option($key, $value);
    }
    $value = get_option($key, "");
    return  $value;
  }

  private static function HtmlError($error) {
    return '<div class="error"><i>' . $error . '</i></div>';
  }

  private static function HtmlSuccess($text) {
    return '<div>' . $text . '</div>';
  }
}
