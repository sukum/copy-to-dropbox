<?php

$admin_option_defs = array();

$admin_option_defs['Administration']['sk_Dropbox'] = array('sk_Dropbox', 'LBL_SK_DROPBOX', 'LBL_SK_DROPBOX', './index.php?module=sk_Dropbox');


$admin_group_header[] = array(
    'LBL_SK_DROPBOX_TITLE',
    '',
    false,
    $admin_option_defs,
    'LBL_SK_DROPBOX_DESC',
);
