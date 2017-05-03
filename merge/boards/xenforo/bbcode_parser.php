<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class BBCode_Parser extends BBCode_Parser_Plain {

	// This contains the attachment bbcode which is handled as special code as the id needs to be changed too
	var $attachment = "\[attach\]([0-9]+)\[/attach\]";

	function convert($text)
	{
		// Attachment codes have an optional full parameter which we need to remove
		$text = str_ireplace("[attach=full]", "[attach]", $text);

		$text = parent::convert($text);

		$text = preg_replace("#\[html\](.*?)\[/html\]#si", "[php]$1[/php]", $text);

		return $text;
	}
}
