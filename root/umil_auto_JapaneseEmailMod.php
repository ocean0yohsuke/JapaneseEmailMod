<?php

/**
*
* @author ocean (Yohsuke) ocean0yohsuke@gmail.com 
* @copyright (c) 2011 ocean 
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/

/**
* @ignore
*/
define('UMIL_AUTO', true);
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
$user->session_begin();
$auth->acl($user->data);
$user->setup();

if (!file_exists($phpbb_root_path . 'umil/umil_auto.' . $phpEx))
{
	trigger_error('Please download the latest UMIL (Unified MOD Install Library) from: <a href="http://www.phpbb.com/mods/umil/">phpBB.com/mods/umil</a>', E_USER_ERROR);
}

// The name of the mod to be displayed during installation.
$mod_name = 'JapaneseEmailMod';

/*
* The name of the config variable which will hold the currently installed version
* UMIL will handle checking, setting, and updating the version itself.
*/
$version_config_name = 'JapaneseEmailMod_version';

/*
* The language file which will be included when installing
* Language entries that should exist in the language file for UMIL (replace $mod_name with the mod's name you set to $mod_name above)
* $mod_name
* 'INSTALL_' . $mod_name
* 'INSTALL_' . $mod_name . '_CONFIRM'
* 'UPDATE_' . $mod_name
* 'UPDATE_' . $mod_name . '_CONFIRM'
* 'UNINSTALL_' . $mod_name
* 'UNINSTALL_' . $mod_name . '_CONFIRM'
*/
$language_file = 'mods/JapaneseEmailMod';

/*
* Options to display to the user (this is purely optional, if you do not need the options you do not have to set up this variable at all)
* Uses the acp_board style of outputting information, with some extras (such as the 'default' and 'select_user' options)
*/
if (!isset($config[$version_config_name]))
{
	$options = array(
		'jem_email_charset'	=> array('lang' => 'JEM_DEFAULT_EMAIL_CHARSET', 'type' => 'select', 'function' => 'jem_email_charset_select', 'explain' => true, 'default' => 'utf-8'),
	);
}

/*
* Optionally we may specify our own logo image to show in the upper corner instead of the default logo.
* $phpbb_root_path will get prepended to the path specified
* Image height should be 50px to prevent cut-off or stretching.
*/
//$logo_img = 'styles/prosilver/imageset/site_logo.gif';

/*
* The array of versions and actions within each.
* You do not need to order it a specific way (it will be sorted automatically), however, you must enter every version, even if no actions are done for it.
*
* You must use correct version numbering.  Unless you know exactly what you can use, only use X.X.X (replacing X with an integer).
* The version numbering must otherwise be compatible with the version_compare function - http://php.net/manual/en/function.version-compare.php
*/
$versions = array(
	// Version 1.0.0
	'1.0.0' => array(
		// Lets add a new column to the phpbb_test table named test_time
		'table_column_add' => array(
			array('phpbb_users', 'jem_email_charset', array('VCHAR:32', request_var('jem_email_charset', 'utf-8'))),
		),
	),
);

// Include the UMIF Auto file and everything else will be handled automatically.
include($phpbb_root_path . 'umil/umil_auto.' . $phpEx);

function jem_email_charset_select()
{
	global $user;
	
	$option = <<<EOD
<option value="utf-8">{L_JEM_EMAIL_CHARSET_UTF8}</option>
<option value="iso-2022-jp">{L_JEM_EMAIL_CHARSET_JIS}</option>
<option value="utf-7">{L_JEM_EMAIL_CHARSET_UTF7}</option>
EOD;
	$option = str_replace(array(
		'{L_JEM_EMAIL_CHARSET_UTF8}',
		'{L_JEM_EMAIL_CHARSET_JIS}',
		'{L_JEM_EMAIL_CHARSET_UTF7}',
	), array(
		$user->lang['JEM_EMAIL_CHARSET_UTF8'],
		$user->lang['JEM_EMAIL_CHARSET_JIS'],
		$user->lang['JEM_EMAIL_CHARSET_UTF7'],
	), $option);
	
	return $option;
}

?>
