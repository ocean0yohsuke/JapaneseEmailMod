<?php
/**
 * DO NOT CHANGE!
 */
if(!defined('IN_PHPBB'))
{
	exit;
}

if (isset($phpbb_root_path))
{
	$phpbb_hook->register(array('template', 'display'), array(new phpBB3_hook_JapaneseEmailMod(), 'template_display'));
}

class phpBB3_hook_JapaneseEmailMod
{
	private $current_page;

	function __construct()
	{
		global $phpEx;

		$this->current_page = basename($_SERVER['SCRIPT_NAME'], ".{$phpEx}");

		if (defined('ADMIN_START'))
		{
			$this->current_page = 'adm/' . $this->current_page;
		}
	}

	function template_display()
	{
		switch($this->current_page)
		{
			case 'ucp' :
				$i 		= request_var('i', '');
				$mode 	= request_var('mode', '');
				if (($mode == '') || ($mode == 'register') ||($i == 'prefs' && $mode == 'personal'))
				{
					phpBB3_JapaneseEmailMod::template_form_email_charset();
				}
				break;
			case 'adm/index' :
				$i 		= request_var('i', '');
				if ($i == 'users')
				{
					phpBB3_JapaneseEmailMod::template_form_email_charset();
				}
			default :
				break;
		}
	}
}

class phpBB3_JapaneseEmailMod
{
	static private $addresses = array();

	static function set_address($to)
	{
		global $db;

		if (empty($to) || isset(self::$addresses[$to]))
		{
			return;
		}

		if (preg_match('#^=\?(.*?)\?B\?(.*)\?= <(.*)>#i', $to, $matches))
		{
			$charset = strtolower($matches[1]);
			$address = $matches[3];
		}
		else
		{
			$address = $to;

			$sql = 'SELECT jem_email_charset
				FROM ' . USERS_TABLE . "
				WHERE user_email = '" . $db->sql_escape($address) . "'" . ' 
				AND user_type IN (' . USER_NORMAL . ', ' . USER_INACTIVE . ', ' . USER_FOUNDER . ') 
				ORDER BY user_type ASC';
			$result = $db->sql_query($sql);
			if ($row = $db->sql_fetchrow($result))
			{
				$charset = $row['jem_email_charset'];
			}
			else
			{
				$charset = 'utf-8';
			}
			$db->sql_freeresult($result);
		}

		switch ($charset)
		{
			case 'iso-2022-jp' :
			case 'utf-7' :
				$encoding = '7bit';
				break;
			case 'utf-8' :
			default :
				$encoding = '8bit';
		}

		self::$addresses[$address] = array(
			'charset' 	=> $charset,
			'encoding'	=> $encoding,
		);
	}

	static function get_charset($to)
	{
		if (preg_match('#^=\?(.*?)\?B\?(.*)\?= <(.*)>#i', $to, $matches))
		{
			$charset = strtolower($matches[1]);

			return $charset;
		}

		$address = $to;

		if (!isset(self::$addresses[$address]))
		{
			self::set_address($address);
		}

		return self::$addresses[$address]['charset'];
	}

	static function get_encoding($to)
	{
		if (preg_match('#^=\?(.*?)\?B\?(.*)\?= <(.*)>#i', $to, $matches))
		{
			$address = $matches[3];
		}
		else
		{
			$address = $to;
		}

		if (!isset(self::$addresses[$address]))
		{
			self::set_address($address);
		}

		return self::$addresses[$address]['encoding'];
	}

	static function mail_encode($str, $eol = "\r\n", $to)
	{
		$charset = self::get_charset($to);
	
		if ($charset != 'utf-8')
		{
			// define start delimimter, end delimiter and spacer
			//$start = "=?UTF-8?B?";
			$start = "=?" . strtoupper($charset) . "?B?";
			$end = "?=";
			//$delimiter = "$eol ";
	
			$str = mb_convert_encoding($str, $charset, 'utf-8');
			$encoded_str = base64_encode($str);
	
			return $start . $encoded_str . $end;
		}
		else
		{
			return mail_encode($str, $eol);
		}
	}	
	
	static function phpbb_mail($to, $subject, $msg, $headers, $eol, &$err_msg)
	{
		$charset 	= self::get_charset($to);
		$encoding 	= self::get_encoding($to);
	
		if ($charset != 'utf-8')
		{
			$msg = mb_convert_encoding($msg, $charset, 'utf-8');
		}
	
		foreach ($headers as $i => $header)
		{
			if (preg_match('#^(Content\-Type: text/plain; charset=)(.*)$#i', $header, $matches))
			{
				$headers[$i] = $matches[1] . strtoupper($charset);
				continue;
			}
			if (preg_match('#^(Content\-Transfer\-Encoding: )(.*)$#i', $header, $matches))
			{
				$headers[$i] = $matches[1] . $encoding;
				continue;
			}
		}
	
		//self::dump_property();
	
		global $config;
	
		// We use the EOL character for the OS here because the PHP mail function does not correctly transform line endings. On Windows SMTP is used (SMTP is \r\n), on UNIX a command is used...
		// Reference: http://bugs.php.net/bug.php?id=15841
		$headers = implode($eol, $headers);
	
		ob_start();
		// On some PHP Versions mail() *may* fail if there are newlines within the subject.
		// Newlines are used as a delimiter for lines in mail_encode() according to RFC 2045 section 6.8.
		// Because PHP can't decide what is wanted we revert back to the non-RFC-compliant way of separating by one space (Use '' as parameter to mail_encode() results in SPACE used)
		if ($charset == 'utf-8')
		{
			$result = $config['email_function_name']($to, mail_encode($subject, ''), wordwrap(utf8_wordwrap($msg), 997, "\n", true), $headers);
		}
		else
		{
			$result = $config['email_function_name']($to, self::mail_encode($subject, '', $to), wordwrap($msg, 997, "\n", true), $headers);
		}
		$err_msg = ob_get_clean();
	
		return $result;
	}	
	
	static function dump_property()
	{
		global $user;

		if ($user->data['user_type'] == USER_FOUNDER)
		{
			var_dump(self::$addresses);
		}
	}

	static function template_form_email_charset($charset = null)
	{
		global $template, $user;

		if ($charset == null)
		{
			$charset = $user->data['jem_email_charset'];
		}

		$user->add_lang('mods/JapaneseEmailMod');

		$html = <<<EOD
<dl>
	<dt><label for="jem_email_charset">{L_JEM_EMAIL_CHARSET}:</label><br /><span>{L_JEM_EMAIL_CHARSET_EXPLAIN}</span></dt>
	<dd><select name="jem_email_charset" id="jem_email_charset">
		<option value="utf-8" {SELECTED_UTF8}>{L_JEM_EMAIL_CHARSET_UTF8}</option>
		<option value="iso-2022-jp" {SELECTED_JIS}>{L_JEM_EMAIL_CHARSET_JIS}</option>
		<option value="utf-7" {SELECTED_UTF7}>{L_JEM_EMAIL_CHARSET_UTF7}</option>
	</select></dd>
</dl>
EOD;
		$html = str_replace(array(
			'{L_JEM_EMAIL_CHARSET}',
			'{L_JEM_EMAIL_CHARSET_EXPLAIN}',
			'{L_JEM_EMAIL_CHARSET_UTF8}',
			'{L_JEM_EMAIL_CHARSET_JIS}',
			'{L_JEM_EMAIL_CHARSET_UTF7}',
			'{SELECTED_UTF8}',
			'{SELECTED_JIS}',
			'{SELECTED_UTF7}',
		), array(
			$user->lang['JEM_EMAIL_CHARSET'],
			$user->lang['JEM_EMAIL_CHARSET_EXPLAIN'],
			$user->lang['JEM_EMAIL_CHARSET_UTF8'],
			$user->lang['JEM_EMAIL_CHARSET_JIS'],
			$user->lang['JEM_EMAIL_CHARSET_UTF7'],
			($charset == 'utf-8')? ' selected="selected" ' : '',
			($charset == 'iso-2022-jp')? ' selected="selected" ' : '',
			($charset == 'utf-7')? ' selected="selected" ' : '',
		), $html);

		$template->assign_vars(array(
			'JEM_EMAIL_CHARSET' => $html,
		));
	}
}



