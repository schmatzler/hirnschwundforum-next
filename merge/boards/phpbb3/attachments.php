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

class PHPBB3_Converter_Module_Attachments extends Converter_Module_Attachments {

	var $settings = array(
		'friendly_name' => 'attachments',
		'progress_column' => 'attach_id',
		'default_per_screen' => 20,
	);

	public $path_column = "physical_filename";

	function get_upload_path()
	{
		$query = $this->old_db->simple_select("config", "config_value", "config_name = 'server_protocol'", array('limit' => 1));
		$uploadspath = $this->old_db->fetch_field($query, 'config_value');
		$this->old_db->free_result($query);

		$query = $this->old_db->simple_select("config", "config_value", "config_name = 'server_name'", array('limit' => 1));
		$uploadspath .= $this->old_db->fetch_field($query, 'config_value');
		$this->old_db->free_result($query);

		$query = $this->old_db->simple_select("config", "config_value", "config_name = 'script_path'", array('limit' => 1));
		$uploadspath .= $this->old_db->fetch_field($query, 'config_value').'/';
		$this->old_db->free_result($query);

		$query = $this->old_db->simple_select("config", "config_value", "config_name = 'upload_path'", array('limit' => 1));
		$uploadspath .= $this->old_db->fetch_field($query, 'config_value');
		$this->old_db->free_result($query);

		return $uploadspath;
	}

	function import()
	{
		global $import_session;

		$query = $this->old_db->simple_select("attachments", "*", "", array('limit_start' => $this->trackers['start_attachments'], 'limit' => $import_session['attachments_per_screen']));
		while($attachment = $this->old_db->fetch_array($query))
		{
			$this->insert($attachment);
		}
	}

	function convert_data($data)
	{
		$insert_data = array();

		// phpBB 3 values
		$insert_data['import_aid'] = $data['attach_id'];
		$insert_data['uid'] = $this->get_import->uid($data['poster_id']);
		$insert_data['filename'] = $data['real_filename'];
		$insert_data['attachname'] = "post_".$insert_data['uid']."_".$data['filetime'].".attach";;
		$insert_data['filetype'] = $data['mimetype'];
		$insert_data['filesize'] = $data['filesize'];
		$insert_data['downloads'] = $data['download_count'];

		$attach_details = $this->get_import->post_attachment_details($data['post_msg_id']);

		$insert_data['pid'] = $attach_details['pid'];
		$insert_data['posthash'] = md5($attach_details['tid'].$attach_details['uid'].random_str());

		// Check if this is an image
		switch(strtolower($insert_data['filetype']))
		{
			case "image/gif":
			case "image/jpeg":
			case "image/x-jpg":
			case "image/x-jpeg":
			case "image/pjpeg":
			case "image/jpg":
			case "image/png":
			case "image/x-png":
				$is_image = 1;
				break;
			default:
				$is_image = 0;
				break;
		}

		// Check if this is an image
		if($is_image == 1)
		{
			$insert_data['thumbnail'] = 'SMALL';
		}
		else
		{
			$insert_data['thumbnail'] = '';
		}

		return $insert_data;
	}

	function fetch_total()
	{
		global $import_session;

		// Get number of attachments
		if(!isset($import_session['total_attachments']))
		{
			$query = $this->old_db->simple_select("attachments", "COUNT(*) as count");
			$import_session['total_attachments'] = $this->old_db->fetch_field($query, 'count');
			$this->old_db->free_result($query);
		}

		return $import_session['total_attachments'];
	}
}


