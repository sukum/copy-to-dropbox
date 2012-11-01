<?php

class sk_Dropbox {

  private static $dropbox   = null;
  private static $storage   = null;
  private static $callback  = null;
  private static $OAuth     = null;
  
  const API_KEY     = 'gtwirorrxrcujnq';
  const API_SECRET  = 'et220go37cvl1lp';

  const ENCRYPT_KEY = 'X123456789001234567890012345678X';
  
  const DROPBOX_PATH  = 'custom/include/dropbox2/';
 
  function save_in_dropbox(&$bean, $event, $arguments) {
    require_once 'include/upload_file.php';
    $upload_file = new UploadFile('filename_file');
    $file_name  = $upload_file->create_stored_filename();
    $GLOBALS['log']->debug($file_name);
    $id = ($bean->object_name == 'Document') ? $bean->document_revision_id : $bean->id;
    $file_path  = UploadStream::path($upload_file->get_upload_path($id));
    $GLOBALS['log']->debug($file_path);
    $GLOBALS['log']->debug($bean->filename);
    if (isset($_FILES['filename_file']) && $file_name == $bean->filename && is_file($file_path)) {
      $GLOBALS['log']->debug($_FILES['filename_file']['name']);
      // Get dropbox api object
      $dropbox = self::get_dropbox();
      if (!$dropbox) return false;
      
      $count = 0;
      do {
        $count++;
        if ($count > 10) {
          $GLOBALS['log']->error('dropbox filename clash');
          return;
        }
        try {
          $meta_data = $dropbox->metaData($file_name);
          $GLOBALS['log']->debug(print_r($meta_data, true));
        } catch (Exception  $e) {
          $meta_data = null;
        }
        if (is_array($meta_data) && $meta_data['code'] == 200) {
          $path_parts = pathinfo($file_name);
          $file_name = $path_parts['filename'].uniqid('_').'.'.$path_parts['extension'];
          $GLOBALS['log']->debug($file_name);
        }
      } while (is_array($meta_data) && $meta_data['code'] == 200);
      $GLOBALS['log']->debug($file_name);
      $GLOBALS['log']->info('Uploading file to dropbox...');
      $put = $dropbox->putFile($file_path, $file_name);
      $GLOBALS['log']->debug(print_r($put, true));
    }
  }
  
  function get_dropbox_account_info() {
    $dropbox = self::get_dropbox();
    if (!$dropbox) return array();
    $accountInfo = $dropbox->accountInfo();
    
    return $accountInfo;
  }
  
  function init() {
    global $current_user;
    $userID = $current_user->id;
  
    // Register a simple autoload function
    spl_autoload_register(function($class){
        $class = str_replace('\\', '/', $class);
        require_once(self::DROPBOX_PATH.$class . '.php');
    });

    // Set your callback URL
    // Check whether to use HTTPS and set the callback URL
    $protocol = (!empty($_SERVER['HTTPS'])) ? 'https' : 'http';
    self::$callback = $protocol . '://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

    // User ID assigned by your auth system (used by persistent storage handlers)
    $userID = 1;

    // Instantiate the Encrypter and storage objects
    if (extension_loaded('mcrypt')) {
      $encrypter = new \Dropbox\OAuth\Storage\Encrypter(self::ENCRYPT_KEY);
    } else {
      $encrypter = null;
    }

    // Instantiate the database data store and connect
    self::$storage = new \Dropbox\OAuth\Storage\Sugar($encrypter, $userID);
  }
  
  function get_dropbox() {
    self::init();
    try {
      $OAuth = new \Dropbox\OAuth\Consumer\Curl(self::API_KEY, self::API_SECRET, self::$storage, self::$callback);
      $dropbox = new \Dropbox\API($OAuth, 'sandbox');
    } catch (Exception $ex) {
      $GLOBALS['log']->fatal($ex->getMessage());
      return null;
    }
    
    return $dropbox;
  }
  
  function clear_token () {
    self::init();
    self::$storage->delete();
  }
  
}

