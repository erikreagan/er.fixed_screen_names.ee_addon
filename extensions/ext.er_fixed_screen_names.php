<?php

/**
 * ER Fixed Screen Names
 * 
 * This file must be placed in the
 * /system/extensions/ folder in your ExpressionEngine installation.
 *
 * @package ERFixedScreenNames
 * @version 1.0.2
 * @author Erik Reagan http://erikreagan.com
 * @copyright Copyright (c) 2009 Erik Reagan
 * @see http://erikreagan.com/projects/er-fixed-screen-names/
 * @license http://creativecommons.org/licenses/by-sa/3.0/ Attribution-Share Alike 3.0 Unported 
 */

if ( ! defined('EXT_NAME_ERFSN') )
{
   define('EXT_NAME_ERFSN','Er_fixed_screen_names');
}

class Er_fixed_screen_names
{
   
   var $settings = array();

   var $name = 'ER Fixed Screen Names';
   var $version = '1.0.2';
   var $description = 'Allows the option to disable screen name changing on a member-group basis';
   var $settings_exist = 'y';
   var $docs_url = 'http://erikreagan.com/projects/er-fixed-screen-names/';


   /**
   * PHP4 Constructor
   *
   * @see __construct()
   */

   function Er_fixed_screen_names($settings='')
   {
      $this->__construct($settings);
   }

   
   /**
   * PHP 5 Constructor
   *
   * @param array|string  Extension settings associative array or an empty string
   */
   function __construct($settings='')
   {
      $this->settings = $this->_get_settings();
   }


   /**
   * Configuration for the extension settings page
   *
   * @return array
   */
   function _get_settings()
	{
		global $SESS, $DB, $REGX, $LANG, $PREFS;

		// assume there are no settings
		$settings = FALSE;
		
		// check the db for extension settings
		$query = $DB->query("SELECT settings FROM exp_extensions WHERE enabled = 'y' AND class = '".EXT_NAME_ERFSN."' LIMIT 1");

		// if there is a row and the row has settings
		if ($query->num_rows > 0 && $query->row['settings'] != '')
		{
			$settings = $REGX->array_stripslashes(unserialize($query->row['settings']));
		}
		
		return $settings;
	}
	
	
	
   /**
   * Configuration for the extension settings page
   *
   * @return array
   */
   function settings_form($current)
   {
      global $LANG, $DB, $PREFS, $DSP;
      
      // Local settings
      $settings = $current;
      // Get my gravatar
      $grav_url = "http://www.gravatar.com/avatar.php?gravatar_id=".md5(strtolower('erik@erikreagan.com'))."&amp;default=".urlencode('http://erikreagan.com/gravatar.jpg')."&amp;size=70";
      
      // Grab the member groups from our current site
      $member_groups = $DB->query("SELECT group_id,site_id,group_title FROM exp_member_groups WHERE `site_id` = " . $PREFS->ini("site_id"));
      
      // Create an array of our member groups in the format that $settings needs
      foreach ($member_groups->result as $group)
      {
         $member_groups_array[$group['group_id']] = $group['group_title'];
      }

      // It just looks better...
      $DSP->crumbline = TRUE;
      
      // Start our block variable
      $b = '';
      
      // a little BK flavor
      $lgau_query = $DB->query("SELECT class FROM exp_extensions WHERE class = 'Lg_addon_updater_ext' AND enabled = 'y' LIMIT 1");
		$lgau_enabled = $lgau_query->num_rows ? TRUE : FALSE;
		$check_for_extension_updates = ($lgau_enabled AND $current['check_for_extension_updates'] == 'y') ? TRUE : FALSE;
      
      
      $b .= $DSP->div('box')
		   . '<div style="width:auto;overflow:auto;">
               <img src="'.$grav_url.'" alt="Erik Reagan" style="border: 1px solid #555;padding: 1px;float:right;"/>'
               . $DSP->heading($this->name . " &nbsp;&nbsp;<small>{$this->version}</small>").'<br/>'
               . '<p>by '.$DSP->anchor('http://erikreagan.com','Erik Reagan').' of '.$DSP->anchor('http://idealdesignfirm.com','Ideal Design Firm, LLC').'<br/>
               Contact me at '. $DSP->mailto('erik@erikreagan.com','erik@erikreagan.com').'</p>'
         . '</div>'
		   . $DSP->div_c();

      // Start the settings form
      $b .= $DSP->form_open(
               array(
                     'action' => 'C=admin'.AMP.'M=utilities'.AMP.'P=save_extension_settings'
                  ),
               array(
                     'name' => strtolower(EXT_NAME_ERFSN)
                  )
            );
      
      
      $b .= $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'width: 100%'));
      $b .= $DSP->tr()
         . $DSP->td('tableHeading', '', '2')
         . $LANG->line('general_settings')
         . $DSP->td_c()
			. $DSP->tr()
         . $DSP->td('tableCellOne', '50%', '1')
         . $LANG->line('groups')
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . $DSP->input_select_header('groups[]',1,6,'341px');
      foreach ($member_groups_array as $group_id => $group_title)
      {
         $selected = (in_array($group_id,$settings['groups'])) ? 1 : 0 ;
         $b .= $DSP->input_select_option($group_id, $group_title,$selected);
      }
      $b .= $DSP->input_select_footer()
         . $DSP->td_c()
         . $DSP->tr_c()
         . $DSP->table_close();

      $b .= '<br/>';
      
      $b .= $DSP->table_open(array('class' => 'tableBorder', 'border' => '0', 'style' => 'width: 100%'));            
      $b .= $DSP->tr()
         . $DSP->td('tableHeading', '', '2')
         . $LANG->line('addon_updater_title')
         . $DSP->td_c()
         . $DSP->tr_c()
         . $DSP->tr()
         . $DSP->td('tableCellOne', '50%', '1')
         . $LANG->line('check_for_extension_updates')
         . ($lgau_enabled ? '' : $LANG->line('lgau_required_message'))
         . $DSP->td_c()
         . $DSP->td('tableCellOne', '', '1')
         . '<select name="check_for_extension_updates"'.($lgau_enabled ? '' : ' disabled="disabled"').'>'
         . $DSP->input_select_option('y', $LANG->line('yes'), ($current['check_for_extension_updates'] == 'y' ? 'y' : ''))
         . $DSP->input_select_option('n', $LANG->line('no'),  ($current['check_for_extension_updates'] != 'y' ? 'y' : ''))
         . $DSP->input_select_footer()
         . $DSP->td_c()
         . $DSP->tr_c();
         
         
      $b .= $DSP->table_close()
         .  $DSP->qdiv('itemWrapperTop', $DSP->input_submit())
         .  $DSP->form_close();
      
      $DSP->set_return_data($this->name.' | '.$LANG->line('extension_settings'),$b,$this->name);


   }
   
      

   /**
	 * Save Settings
	 * 
	 */
	function save_settings()
	{
		global $DB, $IN, $PREFS, $REGX, $SESS;

      // unset the name
		unset($_POST['name']);

		// add the posted values to the settings
		$settings = $REGX->xss_clean($_POST);

		// update the settings
		$query = $DB->query($sql = "UPDATE exp_extensions SET settings = '" . addslashes(serialize($settings)) . "' WHERE class = '".EXT_NAME_ERFSN."'");
	}
   
   
   
   /**
   * Activates the extension
   *
   * @return bool
   */
   function activate_extension()
   {
      global $DB;

      $hooks = array(
         // Extension Hooks
         'show_full_control_panel_end' => 'show_full_control_panel_end',
         
         // LG Addon Updater
         'lg_addon_update_register_source' => 'lg_addon_update_register_source',
         'lg_addon_update_register_addon'  => 'lg_addon_update_register_addon'
      );

      foreach ($hooks as $hook => $method)
      {
         $sql[] = $DB->insert_string('exp_extensions',
            array(
               'extension_id' => '',
               'class'        => get_class($this),
               'method'       => $method,
               'hook'         => $hook,
               'settings'     => serialize(array('groups'=>array(),'check_for_extension_updates'=>'n')),
               'priority'     => 10,
               'version'      => $this->version,
               'enabled'      => "y"
            )
         );
      }

      // run all sql queries
      foreach ($sql as $query)
      {
         $DB->query($query);
      }
      
      return TRUE;
   }
   
   
   
   /**
    * Update the extension
    *
    * @param string
    * @return bool
    **/
   function update_extension($current='')
   {
       global $DB;

       if ($current == '' OR $current == $this->version)
       {
           return FALSE;
       }

       $DB->query("UPDATE exp_extensions 
                   SET version = '".$DB->escape_str($this->version)."' 
                   WHERE class = '".EXT_NAME_ERFSN."'");
   }
   
   
   
   /**
   * Disables the extension the extension and deletes settings from DB
   */
   function disable_extension()
   {
       global $DB;
       $DB->query("DELETE FROM exp_extensions WHERE class = '".EXT_NAME_ERFSN."'");
   }
   
   
   
   /**
    * Remove screen name text field from My Account page
    *
    * @return string
    **/
   function show_full_control_panel_end( $out )
   {
      global $EXT, $IN, $LANG, $SESS;
      
      if($EXT->last_call !== FALSE)
      {
         $out = $EXT->last_call;
      }
      
      if ( ! in_array($SESS->userdata['group_id'], $this->settings['groups']) )
      {
         return $out;
      }
      
      // if it's a CP request on the My Account page
      if (REQ == 'CP' && $IN->GBL('M') == "unpw_form")
      {
         
         $LANG->fetch_language_file('er_fixed_screen_names');
         
         $matches[0] = '/<td\s+?(class=\'[a-zA-Z]+\').+\n+?.+Screen Name.+\n+?<\/td>/';
         $replacements[0] = "<td $1 width='100%'>\n".$LANG->line('edit_msg')."\n</td>\n";
         
         $matches[1] = '/<td.+\n?.+name=\'screen_name\'.+\n+?<\/td>/';
         $replacements[1] = "";
         
         $out = preg_replace($matches, $replacements, $out);
      }
      
      return $out;
      
   }
   
   
   
   /**
    * Register a new Addon Source
    *
    * @param   array $sources The existing sources
    * @return  array The new source list
    * @since   version 1.0.0
    */
   function lg_addon_update_register_source($sources)
   {
       global $EXT;
       // -- Check if we're not the only one using this hook
       if($EXT->last_call !== FALSE)
           $sources = $EXT->last_call;

       if($this->settings['check_for_extension_updates'] == 'yes')
       {
           $sources[] = 'http://erikreagan.com/ee-addons/versions.xml';
       }
       return $sources;

   }


   /**
    * Register a new Addon
    *
    * @param    array $addons The existing sources
    * @return   array The new addon list
    * @since    version 1.0.0
    */
   function lg_addon_update_register_addon($addons)
   {
   	global $EXT;
   	// -- Check if we're not the only one using this hook
   	if($EXT->last_call !== FALSE)
   		$addons = $EXT->last_call;

   	if($this->settings['check_for_extension_updates'] == 'yes')
   	{
   		$addons[$this->name] = $this->version;
   	}
   	return $addons;
   }
   
   
}
// END class

/* End of file ext.er_fixed_screen_names.php */
/* Location: ./system/extensions/ext.er_er_fixed_screen_names.php */