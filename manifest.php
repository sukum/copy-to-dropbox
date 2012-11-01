<?php

$manifest = array (
  'acceptable_sugar_versions' => 
  array (
    'regex_matches' => array (
      "6\.0\.*.*", "6\.1\.*.*", "6\.2\.*.*", "6\.3\.*.*", "6\.4\.*.*",  "6\.5\.*.*", 
    ),
  ),
  'acceptable_sugar_flavors' =>
  array(
    'CE', 'PRO', 'ENT', 'DEV',
  ),
  'readme'=>'',
  'key'=>'sk',
  'author' => 'Sukum',
  'description' => 'Uploads made to SugarCRM Notes and Documents are also copied to one authorized Dropbox account.',
  'icon' => '',
  'is_uninstallable' => true,
  'name' => 'Copy uploads to Dropbox',
  'published_date' => '2012-10-23 08:00:00',
  'type' => 'module',
  'version' => '1.0.0',
  //'remove_tables' => 'prompt',
  );
$installdefs = array (
  'id' => 'copy-to-dropbox',
  'image_dir' => '<basepath>/icons',
  'copy' => 
  array (
    array (
      'from' => '<basepath>/SugarModules/modules/sk_Dropbox',
      'to' => 'modules/sk_Dropbox',
    ),
    array (
      'from' => '<basepath>/SugarModules/custom/include/dropbox2',
      'to' => 'custom/include/dropbox2',
    ),
  ),
  
  // start logic hooks
  'logic_hooks' => array(
    array(
      'module'      => 'Notes',
      'hook'        => 'after_save',
      'order'       => 100,
      'description' => 'Upload file to Dropbox',
      'file'        => 'modules/sk_Dropbox/sk_Dropbox.php',
      'class'       => 'sk_Dropbox',
      'function'    => 'save_in_dropbox',
    ),
    array(
      'module'      => 'Documents',
      'hook'        => 'after_save',
      'order'       => 100,
      'description' => 'Upload file to Dropbox',
      'file'        => 'modules/sk_Dropbox/sk_Dropbox.php',
      'class'       => 'sk_Dropbox',
      'function'    => 'save_in_dropbox',
    ),
  ),
  // logic hooks end
  
  // Administration begin
  'administration' => array(
    array(
      'from'        => '<basepath>/SugarModules/custom/Extension/modules/Administration/Ext/Administration/sk_Dropbox.php',
    ),
  ),
  // Administration end
  'language' => 
  array (
    array (
      'from' => '<basepath>/SugarModules/custom/Extension/modules/Administration/Ext/Language/en_us.sk_Dropbox.php',
      'to_module' => 'Administration',
      'language' => 'en_us',
    ),
  ),
);
