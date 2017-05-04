<?php
/**
 * MyBB 1.8 Merge System
 * Copyright 2014 MyBB Group, All Rights Reserved
 * FluxBB NewPMS 1.8.0 converter by Danny Schmarsel
 * http://lichtmetzger.de
 *
 * Website: http://www.mybb.com
 * License: http://www.mybb.com/download/merge-system/license/
 */

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.<br /><br />Please make sure IN_MYBB is defined.");
}

class FLUXBB_Converter_Module_Privatemessages extends Converter_Module_Privatemessages {

	var $settings = array(
		'friendly_name' => 'private messages',
		'progress_column' => 'pmid',
		'default_per_screen' => 1000,
	);

	function import()
	{
		global $import_session;

		$query = $this->old_db->query("
			SELECT *
			FROM ".OLD_TABLE_PREFIX."pms_new_topics pt
			LEFT JOIN ".OLD_TABLE_PREFIX."pms_new_posts pp ON(pt.id=pp.topic_id)
			LIMIT ".$this->trackers['start_privatemessages'].", ".$import_session['privatemessages_per_screen']
		);
		while($pm = $this->old_db->fetch_array($query))
		{
			$this->insert($pm);
		}
	}

  function convert_data($data)
	{
		global $db;

		$insert_data = array();

		// FluxBB + New PMS 1.8.0 values
		// https://fluxbb.org/resources/mods/new-private-messaging-system/
    $insert_data['uid'] = $this->get_import->uid($data['to_id']);
		$insert_data['fromid'] = $this->get_import->uid($data['poster_id']);
    $insert_data['toid'] = $this->get_import->uid($data['to_id']);
		$insert_data['subject'] = encode_to_utf8(utf8_unhtmlentities($data['topic']), "pms_new_posts", "privatemessages");
		$insert_data['dateline'] = $data['posted'];
		$insert_data['message'] = encode_to_utf8($this->bbcode_parser->convert($data['message']), "pms_new_posts", "privatemessages");
    // I'm lazy, so for now mark all messages as read
		// TODO: Make this dynamic
    $insert_data['status'] = PM_STATUS_READ;
    $insert_data['readtime'] = 0;


    // If the creator of a thread is not the poster of a message
		// in this thread, sort it into the outgoing folder.
		// Wait...what? It works, do not ask.
		if ($data['starter_id'] != $data['poster_id'])
		{
    $insert_data['folder'] = PM_FOLDER_OUTBOX;
		}

		// Now figure out who is participating
		$to = explode(':', $data['to_id']);
		foreach($to as $key => $uid)
		{
			if(empty($uid))
			{
				unset($to[$key]);
				continue;
			}
			$to[$key] = $this->get_import->uid($uid);
		}

		$recipients = array();

		if(!empty($to))
		{
			$recipients['to'] = $to;
		}

		$insert_data['recipients'] = serialize($recipients);

		return $insert_data;
	}

	function fetch_total()
	{
		global $import_session;

		// Get number of private messages
		if(!isset($import_session['total_privatemessages']))
		{
			$query = $this->old_db->simple_select("pms_new_posts", "COUNT(*) as count");
			$import_session['total_privatemessages'] = $this->old_db->fetch_field($query, 'count');
			$this->old_db->free_result($query);
		}

		return $import_session['total_privatemessages'];
	}
}
