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
	var $attachment = "\[attach=([0-9]+)\](\[/attach\])?";

	function convert($message)
	{
		// As WBB changed the attach bbcode they support three different type: "[attach={id}]", "[attach={id}][/attach]", "[attach]{id}[/attach]"
		// The first two are handled by the regex above, however the third one needs to be changed to the first so the handle_attachment function detects it
		$message = preg_replace("#\[attach\]([0-9]+)\[/attach\]#i", "[attach=$1]", $message);

		$message = $this->handle_attachments($message);

		// You won't believe it: WBB uses the same mycodes like we do!
		return $message;
	}
}
