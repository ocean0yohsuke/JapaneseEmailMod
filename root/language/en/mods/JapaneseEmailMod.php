<?php
/**
 * DO NOT CHANGE
 */
if (empty($lang) || !is_array($lang))
{
	$lang = array();
}
// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'JEM_DEFAULT_EMAIL_CHARSET'			=> '登録ユーザーのメール文字エンコーディングのデフォルト設定',
	'JEM_DEFAULT_EMAIL_CHARSET_EXPLAIN'	=> '登録ユーザーに送信されるメールの文字エンコーディングのデフォルトを指定できます',

	'JEM_EMAIL_CHARSET'			=> 'メール文字エンコーディング',
	'JEM_EMAIL_CHARSET_EXPLAIN'	=> '掲示板から送られてくるメールの文字エンコーディングです',
	'JEM_EMAIL_CHARSET_UTF8' 	=> 'UTF-8',
	'JEM_EMAIL_CHARSET_JIS'  	=> 'iso-2022-jp (JIS)',
	'JEM_EMAIL_CHARSET_UTF7' 	=> 'UTF-7',
));