<?php

/**
 * OAuth storage handler built on Sugar
 * @package Dropbox\Oauth
 * @subpackage Storage
 */
namespace Dropbox\OAuth\Storage;

class Sugar extends Session
{
  /**
   * Authenticated user ID
   * @var int
   */
  private $userID = null;
  
  const DROPBOX_REQUEST_TOKEN   = 'request_token';
  const DROPBOX_ACCESS_TOKEN    = 'access_token';
  const DROPBOX_TOKEN           = 'token';
  const DROPBOX_CATEGORY        = 'dropbox';
  
  /**
   * Construct the parent object and
   * set the authenticated user ID
   * @param \Dropbox\OAuth\Storage\Encrypter $encrypter
   * @param int $userID
   * @throws \Dropbox\Exception
   */
  public function __construct(Encrypter $encrypter = null, $userID)
  {
      // Throw an Exception if PDO is not loaded
      if ( ! is_a($GLOBALS['db'], 'DBManager')) {
          throw new \Dropbox\Exception('Cannot access SugarCRM database object');
      }
      
      // Construct the parent object so we can access the SESSION
      // instead of querying the database on every request
      parent::__construct($encrypter);
      
      // Set the authenticated user ID
      $this->userID = $userID;
  }
  
  
  /**
   * Get an OAuth token from the database or session (see below)
   * Request tokens are stored in the session, access tokens in the database
   * Once a token is retrieved it will be stored in the users session
   * for subsequent requests to reduce overheads
   * @param string $type Token type to retrieve
   * @return array|bool
   */
  public function get($type)
  {
    if ($type != 'request_token' && $type != 'access_token') {
      throw new \Dropbox\Exception("Expected a type of either 'request_token' or 'access_token', got '$type'");
    } else {
      $config_name  = $this->get_config_name($type, true);
      $admin = new \Administration();
      $admin->retrieveSettings(self::DROPBOX_CATEGORY);
      if (isset($admin->settings[$config_name])) {
        $token  = html_entity_decode($admin->settings[$config_name]);
        $token  = $this->decrypt($token);
        $_SESSION[$this->namespace][$type] = $admin->settings[$config_name];
        return $token;
      }
      return false;
    }
  }
  
  /**
   * Set an OAuth token in the database or session (see below)
   * Request tokens are stored in the session, access tokens in the database
   * @param \stdClass Token object to set
   * @param string $type Token type
   * @return void
   */
  public function set($token, $type)
  {
      if (($type != 'request_token' && $type != 'access_token')) {
          $message = "Expected a type of either 'request_token' or 'access_token', got '$type'";
          throw new \Dropbox\Exception($message);
      } elseif ( ! $GLOBALS['current_user']->is_admin) {
          $message = "Only an Administrator can authorize a Dropbox account";
          $GLOBALS['log']->fatal($message);
          throw new \Dropbox\Exception($message);
      } else {
          $config_name  = $this->get_config_name($type);
          $admin = new \Administration();
          
          $token = $this->encrypt($token);
          $admin->saveSetting(self::DROPBOX_CATEGORY, $config_name, $token);
          $_SESSION[$this->namespace][$type] = $token;
      }
  }
  
  /**
   * Delete access token for the current user ID from the database
   * @todo Add error checking
   * @return bool
   */
  public function delete()
  {
    parent::delete();
    $admin = new \Administration();
    $config_name  = $this->get_config_name('request_token');
    $admin->saveSetting(self::DROPBOX_CATEGORY, $config_name, '');
    $config_name  = $this->get_config_name('access_token');
    $admin->saveSetting(self::DROPBOX_CATEGORY, $config_name, '');
    return true;
  }
  
  /**
   * Get config category
   * @return string
   */
  protected function get_config_name($type, $include_category=false)
  {
    $category = ($include_category) ? self::DROPBOX_CATEGORY.'_' : ''; 
    switch($type) {
      case 'request_token'  :
        return $category.self::DROPBOX_REQUEST_TOKEN."_{$this->userID}";
        break;
      case 'access_token'   :
        return $category.self::DROPBOX_ACCESS_TOKEN."_{$this->userID}";
        break;
      default:
        return $category.self::DROPBOX_TOKEN."_{$this->userID}";
        break;
    }
  }
}
