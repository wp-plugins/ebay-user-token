<?php
class EbayUserTokenAPI {
  var $app_id = "";
  var $dev_id = "";
  var $cert = "";
  var $runame = "";

  public function init($app_id, $dev_id, $cert, $runame) {
    $this->app_id = $app_id;
    $this->dev_id = $dev_id;
    $this->cert = $cert;
    $this->runame = $runame;
  }

  public function getSession() {
    $XML = '<?xml version="1.0" encoding="utf-8"?><GetSessionIDRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $XML .= '<RuName>' . $this->runame . '</RuName></GetSessionIDRequest>';
    $response = $this->request("GetSessionID", $XML);
    $id = $this->cut($response, "SessionID");
    return $id;
  }
  public function getToken($session) {
    $XML = '<?xml version="1.0" encoding="utf-8"?><FetchTokenRequest xmlns="urn:ebay:apis:eBLBaseComponents">';
    $XML .= '<SessionID>'. $session . '</SessionID></FetchTokenRequest>';
    $response = $this->request("FetchToken", $XML);
    $token = $this->cut($response, "eBayAuthToken");
    return $token;
  }

  private function cut($str, $tag) {
    $str = stristr($str, "<" . $tag . ">");
    if ($str != "") {
      $pos = stripos($str, "</".$tag . ">");
      if ($pos > 0) {
        $start = strlen($tag) + 2;
        $str = substr($str, $start, $pos - $start);
      }
    }
    return $str;
  }

  private function request($method, $xml) {
    $header=array(
      "X-EBAY-API-COMPATIBILITY-LEVEL: 967",
      "X-EBAY-API-DEV-NAME: " . $this->dev_id,
      "X-EBAY-API-APP-NAME: " . $this->app_id,
      "X-EBAY-API-CERT-NAME: " . $this->cert,
      "X-EBAY-API-CALL-NAME: " . $method,
      "X-EBAY-API-SITEID:" . 0,
      "Content-Type: text/xml"
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.ebay.com/ws/api.dll");
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $response=curl_exec ($ch);
    //error_log($response);
    return $response;
  }
}