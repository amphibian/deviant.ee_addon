<?php

if(!defined('EXT'))
{
	exit('Invalid file request');
}

class Deviant
{
	var $settings        = array();
	var $name            = 'Deviant';
	var $version         = '1.0';
	var $description     = 'Break away from EE&rsquo;s default entry preview and choose a new path.';
	var $settings_exist  = 'y';
	var $docs_url        = 'http://github.com/amphibian/deviant.ee_addon';

	function Deviant($settings='')
	{
	    $this->settings = $settings;
	}

	
	function settings_form($current)
	{	    
	    global $DB, $DSP, $IN, $LANG, $PREFS;
		$site = $PREFS->ini('site_id');  
				
		$locations = array('default','new','edit','manage');
		
		// Check to see if the Structure module is installed
		$structure = $DB->query("SELECT module_id FROM exp_modules WHERE module_name = 'Structure'");
		if($structure->num_rows > 0)
		{
			$locations[] = 'structure';
		}

		$global_locations = array_merge(array('none'), $locations);
		
		$weblogs = $DB->query("SELECT blog_title, weblog_id 
			FROM exp_weblogs 
			WHERE site_id = '".$DB->escape_str($PREFS->ini('site_id'))."' 
			ORDER BY blog_title ASC");
								
		// Start building the page
		$DSP->crumbline = TRUE;
		
		$DSP->title  = $LANG->line('extension_settings');
		$DSP->crumb  = $DSP->anchor(BASE.AMP.'C=admin'.AMP.'area=utilities', $LANG->line('utilities')).
		$DSP->crumb_item($DSP->anchor(BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=extensions_manager', $LANG->line('extensions_manager')));
		$DSP->crumb .= $DSP->crumb_item($this->name);
		
		$DSP->right_crumb($LANG->line('disable_extension'), BASE.AMP.'C=admin'.AMP.'M=utilities'.AMP.'P=toggle_extension_confirm'.AMP.'which=disable'.AMP.'name='.$IN->GBL('name'));
		
		$DSP->body = $DSP->form_open(
			array(
				'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings',
				'name'   => 'deviant',
				'id'     => 'deviant'
			),
			array('name' => get_class($this))
		);
		
		// $DSP->body .=	'<pre>'.print_r($current, TRUE).'</pre>';
	
		$DSP->body .=   $DSP->heading($this->name.NBS.$DSP->qspan('defaultLight', $this->version), 1);
		
		// Open the table
		$DSP->body .=   $DSP->table('tableBorder', '0', '', '100%');
		$DSP->body .=  	$DSP->tr();
		
		$DSP->body .=   $DSP->td('tableHeading', '30%');
		$DSP->body .=   ucfirst($PREFS->ini('weblog_nomenclature'));
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableHeading', '30%');
		$DSP->body .=   $LANG->line('redirect_after_new');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->td('tableHeading', '30%');
		$DSP->body .=   $LANG->line('redirect_after_update');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->tr_c();
		
		// Global location controls
		$DSP->body .=  	$DSP->tr();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; font-weight: bold; margin: 0; padding: 6px;">';
		$DSP->body .=   $LANG->line('global_redirect');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .= 	$DSP->input_select_header('global_new_deviant_'.$site);
		foreach($global_locations as $location)
		{
			$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'), (isset($current['global_new_deviant_'.$site]) && $current['global_new_deviant_'.$site] == $location) ? 1 : '');		
		}
		$DSP->body .= 	$DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .= 	$DSP->input_select_header('global_updated_deviant_'.$site);
		foreach($global_locations as $location)
		{
			$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'), (isset($current['global_updated_deviant_'.$site]) && $current['global_updated_deviant_'.$site] == $location) ? 1 : '');			
		}
		$DSP->body .= 	$DSP->input_select_footer();
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   $DSP->tr_c();		
		
		// Per-weblog settings
		$i = 1;
		foreach($weblogs->result as $value)
		{
			$class = ($i % 2) ? 'tableCellTwo' : 'tableCellOne';
			extract($value);
			
			$new_extra = $updated_extra = '';
			if(isset($current['global_new_deviant_'.$site]) && $current['global_new_deviant_'.$site] != 'none')
			{
				$new_selected = $current['global_new_deviant_'.$site];
				$new_extra = 'disabled="disabled"';
			}
			elseif(isset($current['new_channel_id_'.$weblog_id]))
			{
				$new_selected = $current['new_channel_id_'.$weblog_id];
			}
			else
			{
				$new_selected = 'default';
			}
			if(isset($current['global_updated_deviant_'.$site]) && $current['global_updated_deviant_'.$site] != 'none')
			{
				$updated_selected = $current['global_updated_deviant_'.$site];
				$updated_extra = 'disabled="disabled"';
			}
			elseif(isset($current['updated_channel_id_'.$weblog_id]))
			{
				$updated_selected = $current['updated_channel_id_'.$weblog_id];
			}
			else
			{
				$updated_selected = 'default';
			}
			
			
			$DSP->body .=  	$DSP->tr();
			
			$DSP->body .=   $DSP->td($class);
			$DSP->body .=   $blog_title;
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->td($class);
			$DSP->body .= 	$DSP->input_select_header('new_channel_id_'.$weblog_id, null, null, null, $new_extra);
			foreach($locations as $location)
			{
				$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'), ($new_selected == $location) ? 1 : '');		
			}
			$DSP->body .= 	$DSP->input_select_footer();
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->td($class);
			$DSP->body .= 	$DSP->input_select_header('updated_channel_id_'.$weblog_id, null, null, null, $updated_extra);
			foreach($locations as $location)
			{
				$DSP->body .= $DSP->input_select_option($location, $LANG->line($location.'_lang'), ($updated_selected == $location) ? 1 : '');		
			}
			$DSP->body .= 	$DSP->input_select_footer();
			$DSP->body .=   $DSP->td_c();
			
			$DSP->body .=   $DSP->tr_c();
			
			$i++;
		}
		
		// Setting for message display
		$DSP->body .=  	$DSP->tr();
		
		$DSP->body .=   '<td class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .=   $LANG->line('hide_success_message');
		$DSP->body .=   $DSP->td_c();
		
		$DSP->body .=   '<td colspan="2" class="box" style="border-width: 0 0 1px; margin: 0; padding: 6px;">';
		$DSP->body .= 	$DSP->input_text('hide_success_message', (isset($current['hide_success_message'])) ? $current['hide_success_message'] : '5', 10, '2', '', '50px');
		$DSP->body .=   $DSP->td_c();
				
		$DSP->body .=   $DSP->tr_c();		
	    
		// Wrap it up
		$DSP->body .=   $DSP->table_c();
		$DSP->body .=   $DSP->qdiv('itemWrapperTop', $DSP->input_submit());
		$DSP->body .=   $DSP->form_c();	  
	}
	
	
	function save_settings()
	{
		global $DB;

		// Get all settings
		$settings = $this->get_settings(TRUE);
		
		unset($_POST['name']);
				
		// Only update the settings we just posted
		// (Leave other sites' settings alone)
		foreach($_POST as $k => $v)
		{
			$settings[$k] = $v;
		}
			
		$data = array('settings' => addslashes(serialize($settings)));
		$update = $DB->update_string('exp_extensions', $data, "class = 'deviant'");
		$DB->query($update);
	}

	
	function get_settings($all_sites = FALSE)
	{
		global $DB, $PREFS, $REGX;
		$site = $PREFS->ini('site_id');

		$get_settings = $DB->query("SELECT settings FROM exp_extensions WHERE class = 'deviant' LIMIT 1");
		if ($get_settings->num_rows > 0 && $get_settings->row['settings'] != '')
        {
        	$settings = $REGX->array_stripslashes(unserialize($get_settings->row['settings']));
        	$settings = ($all_sites == TRUE) ? $settings : $settings[$site];
        }
        else
        {
        	$settings = array();
        }
        return $settings;		
	}		
	
	
	// --------------------------------
	//  Put $_POST['return_url'] into a global variable so we can access it later
	//  (No access to this variable from the other, more appropriate hooks)
	// --------------------------------  	
	
	function grab_return_url() {		
		global $saef_return_url;
		$saef_return_url = ($_POST['return_url']) ? $_POST['return_url'] : '';
	}	
    
	
	// --------------------------------
	//  Do the redirect
	// --------------------------------  	
	
	function redirect_location($entry_id, $data, $cp_call) {
				
		if($cp_call == TRUE)
		{
			global $PREFS;
			$site = $PREFS->ini('site_id');  		
			
			$type = (isset($_POST['entry_id']) && !empty($_POST['entry_id'])) ? 'updated' : 'new';
			
			// If we have a global setting, use it
			if(isset($this->settings['global_'.$type.'_deviant_'.$site]) && 
			$this->settings['global_'.$type.'_deviant_'.$site] != 'none')
			{
				$redirect = $this->settings['global_'.$type.'_deviant_'.$site];
			}
			// If we have a channel-specific setting, use it
			elseif(isset($this->settings[$type.'_channel_id_'.$data['weblog_id']]))
			{
				$redirect = $this->settings[$type.'_channel_id_'.$data['weblog_id']];
			}
			// Otherwise, vanilla
			else
			{
				$redirect = 'default';
			}
			
			switch($redirect)
			{
				case 'new':
					$location = BASE.AMP.
					'C=publish'.AMP.
					'M=entry_form'.AMP.
					'weblog_id='.$data['weblog_id'].AMP.
					'deviant_entry_id='.$entry_id.AMP.
					'U='.$type;
					break;
				case 'edit':
					$location = BASE.AMP.
					'C=edit'.AMP.
					'M=edit_entry'.AMP.
					'weblog_id='.$data['weblog_id'].AMP.
					'entry_id='.$entry_id.AMP.
					'deviant_entry_id='.$entry_id.AMP.
					'U='.$type;
					break;
				case 'manage':
					$location = BASE.AMP.
					'C=edit'.AMP.
					'M=view_entries'.AMP.
					'weblog_id='.$data['weblog_id'].AMP.
					'deviant_entry_id='.$entry_id.AMP.
					'U='.$type;
					break;
				case 'structure':
					$location = BASE.AMP.
					'C=modules'.AMP.
					'M=Structure'.AMP.
					'weblog_id='.$data['weblog_id'].AMP.
					'deviant_entry_id='.$entry_id.AMP.
					'U='.$type;
					break;
				default:
					$location = BASE.AMP.
					'C=edit'.AMP.
					'M=view_entry'.AMP.
					'weblog_id='.$data['weblog_id'].AMP.
					'entry_id='.$entry_id.AMP.
					'U='.$type;			
			}
		}
		else
		{
			global $FNS, $saef_return_url;
			$FNS->template_type = 'webpage';
			$location = ($saef_return_url == '') ? $FNS->fetch_site_index() : $FNS->create_url($saef_return_url, 1, 1);
		}
		
		return $location;

	}	
    
    
	// --------------------------------
	//  Edits to the control panel output
	// --------------------------------  	
	
	function cp_changes($out) {
				
		global $DB, $EXT, $DSP, $IN, $LANG;
		if ($EXT->last_call !== FALSE)
		{
			$out = $EXT->last_call;
		}
		
		// Add jQuery to extension settings page
		if($IN->GBL('P') == 'extension_settings' && $IN->GBL('name') == 'deviant')
		{
			$target = '</head>';
			$js = '
			<script type="text/javascript">
			<!-- Added by Deviant -->
			$(document).ready(function()
			{
				$("select[name^=global_new_deviant]").change(function(){
					var setValue = $(this).val();
					if(setValue != "none")
					{
						$("select[name^=new_channel_id] option[value=" + setValue + "]").attr("selected", "selected");
						$("select[name^=new_channel_id]").attr("disabled", "disabled"); 
					}
					else
					{
						$("select[name^=new_channel_id]").removeAttr("disabled");
					}						
				});
				$("select[name^=global_updated_deviant]").change(function(){
					var setValue = $(this).val();
					if(setValue != "none")
					{
						$("select[name^=updated_channel_id] option[value=" + setValue + "]").attr("selected", "selected");
						$("select[name^=updated_channel_id]").attr("disabled", "disabled"); 
					}
					else
					{
						$("select[name^=updated_channel_id]").removeAttr("disabled");
					}
				});
			});
			</script>
			</head>
			';
			$out = str_replace($target, $js, $out);
		}
		
		
		// Display success messages
		$find = array("<div id='contentNB'>", "<div id='content'>", "</head>");
		
		if(isset($_GET['deviant_entry_id']))
		{
			// Success message goes bye-bye? If so, add the callback.
			$auto_hide = ($this->settings['hide_success_message']) ? ',function(){
				setTimeout(
					function(){
						$("div#deviant_message").slideUp();
					}
				,'.($this->settings['hide_success_message']*1000).'
				);
			}' : '';	
						
			// Build the success message
			$get_title = $DB->query("SELECT title FROM exp_weblog_titles 
				WHERE entry_id = " . $DB->escape_str($_GET['deviant_entry_id']) . " LIMIT 1");
			$title = $get_title->row['title'];
			
			$LANG->fetch_language_file('publish');
			
			$message = '
			<div id="deviant_message"><div>'.$DSP->span('success');
			if($_GET['U'] == 'new')
			{
				$message .= $LANG->line('entry_has_been_added');
			}
			elseif($_GET['U'] == 'updated')
			{	
				$message .= $LANG->line('entry_has_been_updated');
			}

			if($_GET['M'] != 'edit_entry')
			{
				$message .= ': '.$DSP->span_c();
				$message .= $DSP->qspan('defaultBold', $title).
				$DSP->span('defaultSmall').$DSP->qspan('defaultLight', '&nbsp;|&nbsp;').
				$DSP->anchor(BASE.AMP.'C=edit'.AMP.'M=edit_entry'.AMP.
					'weblog_id='.$_GET['weblog_id'].AMP.'entry_id='.$_GET['deviant_entry_id'], 
					$LANG->line('edit_this_entry')).
				$DSP->span_c();
			}
			else
			{
				$message .= $DSP->span_c();
			}
			
			$message .= '<a href="#" id="deviant_close" title="Hide this notice">&times;</a>';
			$message .= $DSP->div_c().$DSP->div_c();
		
			// CSS and JS for the success message
			$head = '
				<style type="text/css">
					#deviant_message { border-bottom:1px solid #CCC9A4; position: fixed; width: 100%; left: 0; top: 0; display: none; }
					* html #deviant_message { position: absolute; }
					#deviant_message div { padding: 10px 15px; background-color: rgb(252,252,222); }
					#deviant_message > div { background-color: rgba(252,252,222,0.95); }
					a#deviant_close { display: block; position: absolute; right: 15px; top: 7px; padding: 0 3px; border: 1px solid #CCC9A4; font-size: 18px; line-height: 18px; color: #CCC9A4; text-decoration: none; -webkit-border-radius: 3px; -moz-border-radius: 3px; border-radius: 3px; }
					a#deviant_close:hover { background-color: #CCC9A4; color: rgb(252,252,222); }
				</style>
				
				<script type="text/javascript">
					$(document).ready(function()
					{
						$("div#deviant_message").slideDown("normal"'.$auto_hide.');
						$("a#deviant_close").click(function(){
							$("div#deviant_message").slideUp();
							return false;
						});
					});
				</script>
			';
			
			$replace = array(
				"<div id='contentNB'>$message",
				"<div id='content'>$message",
				$head."</head>"
			);
			
			$out = str_replace($find, $replace, $out);
				
		}
		
		// May as well make the other success messages a little nicer
		// (Delete and multi-entry category update. No message for multi-entry edit for some reason.)
		if( isset($_GET['C']) && $_GET['C'] == 'edit' && isset($_GET['M']) && ($_GET['M'] == 'delete_entries' || $_GET['M'] == 'entry_category_update'))
		{
			$target = "/<div class='success' >\s*([^<]*)\s*/";
			$message = '<div class="box"><span class="success">$1</span>';
			$out = preg_replace($target, $message, $out);
		}
		
		return $out;

	}	
	
	
	function activate_extension()
	{
	    global $DB;

	    $hooks = array(
	    	'weblog_standalone_insert_entry' => 'grab_return_url',
	    	'submit_new_entry_redirect' => 'redirect_location',
	    	'show_full_control_panel_end' => 'cp_changes'
	    );
	    
	    foreach($hooks as $hook => $method)
	    {
		    $DB->query($DB->insert_string('exp_extensions',
		    	array(
					'extension_id' => '',
			        'class'        => "deviant",
			        'method'       => $method,
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
	    global $DB;
	    
	    if ($current == '' OR $current == $this->version)
	    {
	        return FALSE;
	    }
	    
	    $DB->query("UPDATE exp_extensions 
	                SET version = '".$DB->escape_str($this->version)."' 
	                WHERE class = 'deviant'");
	}
	

	function disable_extension()
	{
	    global $DB;
	    
	    $DB->query("DELETE FROM exp_extensions WHERE class = 'deviant'");
	}


}
