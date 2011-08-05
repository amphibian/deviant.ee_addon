<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Deviant_ext
{
	var $settings        = array();
	var $name            = 'Deviant';
	var $version         = '1.0.4';
	var $description     = 'Break away from EE&rsquo;s default entry preview and choose a new path.';
	var $settings_exist  = 'y';
	var $docs_url        = 'http://github.com/amphibian/deviant.ee_addon';
	var $slug			 = 'deviant';


	function Deviant_ext($settings='')
	{
	    $this->settings = $settings;
	    $this->EE =& get_instance();
	}

	
	function settings_form($current)
	{	    
		// Initialize our variable array
		$vars = array();		
		
		// Add our existing settings
		$vars['current'] = $current;
		
		// Add site ID
		$vars['site'] = $this->EE->config->item('site_id');
		
		// We need our file name for the settings form
		$vars['file'] = $this->slug;
		
		// Add our potential redirect locations
		$vars['locations'] = array(
			'default' => $this->EE->lang->line('default_location'),
			'new' => $this->EE->lang->line('new_location'),
			'edit' => $this->EE->lang->line('edit_location'),
			'manage' => $this->EE->lang->line('manage_location'),
		);
		
		// Check to see if the Structure or Zenbu are installed
		// If so, add them to the locations array
		$modules = $this->EE->db->query("SELECT module_name
			FROM exp_modules 
			WHERE module_name IN('Structure', 'Zenbu', 'Pages')");
			
		if($modules->num_rows() > 0)
		{
			foreach($modules->result_array() as $module)
			{
				$slug = strtolower($module['module_name']);
				$vars['locations'][$slug] = $this->EE->lang->line($slug.'_location');
			}
		}
		
		// We need a similar array for the global menu
		$vars['global_locations'] = array_merge(array('none' => $this->EE->lang->line('none')), $vars['locations']); 
		
		// Get an array of channels for this site
		$vars['channels'] = array();
		
		$channels = $this->EE->db->query("SELECT channel_title, channel_id 
			FROM exp_channels 
			WHERE site_id = '".$this->EE->db->escape_str($this->EE->config->item('site_id'))."' 
			ORDER BY channel_title ASC");
			
		foreach($channels->result_array() as $channel)
		{
			extract($channel);
			$vars['channels'][$channel_id] = $channel_title;
		}
		
		// We have our vars set, so load and return the view file
		return $this->EE->load->view('settings', $vars, TRUE);
	}
	
	
	function save_settings()
	{
		// Get all settings
		$settings = $this->get_settings(TRUE);
		
		// print_r($settings); exit();
		
		unset(
			$_POST['file'], 
			$_POST['submit']
		);
				
		// Only update the settings we just posted
		// (Leave other sites' settings alone)
		foreach($_POST as $k => $v)
		{
			$settings[$k] = $v;
		}
			
		$this->EE->db->where('class', ucfirst(get_class($this)));
		$this->EE->db->update('extensions', array('settings' => serialize($settings)));
		
		$this->EE->session->set_flashdata('message_success', $this->EE->lang->line('preferences_updated'));

	}

	
	function get_settings($all_sites = FALSE)
	{
		$get_settings = $this->EE->db->query("SELECT settings 
			FROM exp_extensions 
			WHERE class = '".ucfirst(get_class($this))."' 
			LIMIT 1");
		
		$this->EE->load->helper('string');
		
		if ($get_settings->num_rows() > 0 && $get_settings->row('settings') != '')
        {
        	$settings = strip_slashes(unserialize($get_settings->row('settings')));
        	$settings = ($all_sites == TRUE) ? $settings : $settings[$this->EE->config->item('site_id')];
        }
        else
        {
        	$settings = array();
        }
        return $settings;
	}
	

	
	function entry_submission_redirect($entry_id, $meta, $data, $cp_call, $orig_loc)
	{
		
		// Just continue on if we're not in the control panel
		if($cp_call == FALSE)
		{
			return $orig_loc;
		}
				
		$type = (!empty($_POST['entry_id'])) ? 'updated' : 'new';
		$site = $this->EE->config->item('site_id');
		
		// If we have a global setting, use it
		if(isset($this->settings['global_'.$type.'_deviant_'.$site]) && 
			$this->settings['global_'.$type.'_deviant_'.$site] != 'none')
		{
			$redirect = $this->settings['global_'.$type.'_deviant_'.$site];
		}
		// If we have a channel-specific setting, use it
		elseif(isset($this->settings[$type.'_channel_id_'.$meta['channel_id']]))
		{
			$redirect = $this->settings[$type.'_channel_id_'.$meta['channel_id']];
		}
		// Otherwise, vanilla
		else
		{
			$redirect = 'default';
		}
		
		// Check to see if we came from a filtered entries list
		$filters = '';
		parse_str(parse_url($orig_loc, PHP_URL_QUERY), $orig_filters);
		if(isset($orig_filters['amp;filter']))
		{
			$orig_filters = unserialize(base64_decode($orig_filters['amp;filter']));
			foreach($orig_filters as $param => $value)
			{
				$value = ($param == 'keywords') ? base64_encode($value) : $value;
				$filters .= AMP.$param.'='.$value;
			}
		}
		
		// Uhura support
        $status = '';
        if($save_status = $this->EE->input->post('save_status'))
        {
            $status = AMP.'status='.$save_status;
        }
		
		switch($redirect)
		{
			case 'new':
				$loc = BASE.AMP.
				'C=content_publish'.AMP.
				'M=entry_form'.AMP.
				'channel_id='.$meta['channel_id'].
				$status;
				break;
			case 'edit':
				$loc = BASE.AMP.
				'C=content_publish'.AMP.
				'M=entry_form'.AMP.
				'channel_id='.$meta['channel_id'].AMP.
				'entry_id='.$entry_id.
				$status;
				break;
			case 'manage':
				$loc = BASE.AMP.
				'C=content_edit'.
				$filters;
				break;
			case 'structure':
				$loc = BASE.AMP.
				'C=addons_modules'.AMP.
				'M=show_module_cp'.AMP.
				'module=structure';
				break;
			case 'pages':
				$loc = BASE.AMP.
				'C=addons_modules'.AMP.
				'M=show_module_cp'.AMP.
				'module=pages';
				break;
			case 'zenbu':
				$loc = BASE.AMP.
				'C=addons_modules'.AMP.
				'M=show_module_cp'.AMP.
				'module=zenbu'.
				$filters;
				break;
			default:
				$loc = $orig_loc;
		}
		
		// Create the success notice
		if($type == 'new')
		{
			$message = $this->EE->lang->line('entry_has_been_added');
		}
		else
		{
			$message = $this->EE->lang->line('entry_has_been_updated');
		}
		
		// If we're going somewhere other than continuing to edit,
		// provide the entry title and edit link
		if($redirect != 'edit')
		{	
			$message .=	': <strong>'.$meta['title'].'</strong> &nbsp; ';
			$message .= '<small style="font-weight:normal;">';
			$message .= '<a href="'.BASE.AMP.
						'C=content_publish'.AMP.
						'M=entry_form'.AMP.
						'channel_id='.$meta['channel_id'].AMP.
						'entry_id='.$entry_id.'">';
			$message .= $this->EE->lang->line('edit');
			$message .= '</a></small>';
		}
				
		$this->EE->session->set_flashdata('message_success', $message);
		
		return $loc;
	}
	
	
	function activate_extension()
	{

	    $hooks = array(
	    	'entry_submission_redirect'
	    );
	    
	    foreach($hooks as $hook)
	    {
		    $this->EE->db->query($this->EE->db->insert_string('exp_extensions',
		    	array(
			        'class'        => ucfirst(get_class($this)),
			        'method'       => $hook,
			        'hook'         => $hook,
			        'settings'     => '',
			        'priority'     => 99,
			        'version'      => $this->version,
			        'enabled'      => "y"
					)
				)
			);
	    }		
	}


	function update_extension($current='')
	{
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    
		$this->EE->db->query("UPDATE exp_extensions 
	     	SET version = '". $this->EE->db->escape_str($this->version)."' 
	     	WHERE class = '".ucfirst(get_class($this))."'");
	}

	
	function disable_extension()
	{	    
		$this->EE->db->query("DELETE FROM exp_extensions WHERE class = '".ucfirst(get_class($this))."'");
	}

}