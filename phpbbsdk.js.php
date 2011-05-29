<?php

    /**
     * phpBB3 Javascript development kit
     *
     * Javascript based phpbb API system.
     *
     * @package    phpBB3 Javascript Development Kit
     * @version    1.0.0
     * @copyright  Copyright (c) 2011 Alex Emilov Georgiev
     * @license    http://www.opensource.org/licenses/gpl-3.0.html   GNU GPL
     */

    /**
     * Phpbb3 default codes to integrate your phpBB
     *
     * If your board isn't in root directory, please
     * specify it at $phpbb_root_path
     * Replace ./ with your directory.
     *
     */

    define('IN_PHPBB', true);
    $phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
    $phpEx = substr(strrchr(__FILE__, '.'), 1);
    include($phpbb_root_path . 'common.' . $phpEx);

    $user->session_begin();
    $auth->acl($user->data);
    $user->setup();
  
    /**
     * Use user->data as separate array
     * for better security.
     *
     */
     
    $this_user = $user->data;
    
    /**
     * Unset any information, that may harm your board somehow.
     *
     */
    
    foreach(array('user_permissions', 'user_password', 'user_passchg',
    'user_pass_convert', 'user_email_hash', 'user_last_confirm_key',
    'user_sig_bbcode_uid', 'user_sig_bbcode_bitfield', 'user_newpasswd',
    'user_actkey', 'user_form_salt', 'session_id', 'session_admin',
    'session_forum_id', 'session_last_visit', 'session_start',
    'session_time', 'session_forwarded_for', 'session_autologin',
    'session_viewonline', 'user_topic_sortby_dir', 'user_topic_sortby_type',
    'user_post_sortby_dir', 'user_post_sortby_type', 'user_topic_show_days',
    'user_last_privmsg', 'user_inactive_time', 'user_inactive_reason',
    'user_login_attempts', 'user_post_sortby_dir') as $revoked)
    {
    
        if(isset($this_user[$revoked]))
        {
     
            unset($this_user[$revoked]);
      
        }
     
    }
    
    /**
     * We have only basic information like usernames, user_from, user_website ..etc.
     * Even unregistered have only information :: username, user_id, is_registered
     * and is_bot.
     *
     */

    /**
     * If user doesn't want to view his email,
     * unset mailing information.
     *
     */
     
    if(!! $this_user['user_allow_viewemail'] === false)
    {
        unset($this_user['user_email']);
        unset($this_user['user_emailtime']);
        unset($this_user['user_allow_massemail']);
        unset($this_user['user_allow_viewemail']);
    }
    
    /**
     * If unregistered specify what info to show.
     *
     */
     
    if(!$user->data['is_registered'])
    {
        foreach($this_user as $not_logged_key => $not_logged_user)
        {
            if($not_logged_key <> 'is_registered' && $not_logged_key <> 'user_id' && $not_logged_key <> 'username' && $not_logged_key <> 'is_bot')
            {
                unset($this_user[$not_logged_key]);
            }
        }
    }
    
    /**
     * Export user variables in javascript object format.
     *
     */
     
    $total_keys = array_keys($this_user);
    
    foreach($this_user as $key => $data)
    {
        $dot = ($key <> $total_keys[sizeof($this_user)-1]) ? ',' : '';
        $data = str_replace("'", "\'", $data);
        $data = str_replace("\n", '\n', $data);
        $data = (!empty($data)) ? "'{$data}'" : 'null';
        $tag .= " {$key} : {$data}{$dot} ";
    }
    
    /**
     * If auth as admin, specify config variables.
     *
     */
     
    if($auth->acl_gets('a_'))
    {
        $jsconfig = $config;
        $config_total_keys = array_keys($jsconfig);
      
        foreach($jsconfig as $config_key => $config_data)
        {
            $config_dot = ($config_key <> $config_total_keys[sizeof($jsconfig)-1]) ? ',' : '';
            $config_data = str_replace("'", "\'", $config_data);
            $config_data = str_replace("\n", '\n', $config_data);
            $config_data = (!empty($config_data)) ? "'{$config_data}'" : 'null';
            $config_tag .= " {$config_key} : {$config_data}{$config_dot} ";
        }
    
     } else {
        $config_tag = 'config : undefined';
     }
    
    /**
     * If admin or mod, specify global permission variables. (check)
     * Recheck, for correct counting for array.
     *
     */
    
    $jsauth = $auth->acl_options['global'];
    
    foreach($jsauth as $second_check => $second_name)
    {
        if(preg_match('/^m/', $second_check))
        {
            if(!$auth->acl_gets('a_') || !$auth->acl_gets('m_'))
            {
                unset($jsauth[$second_check]);
            }
        }
      
        if(preg_match('/^a/', $second_check))
        {
            if(!$auth->acl_gets('a_'))
            {
                unset($jsauth[$second_check]);
            }
        }
    }
    
    $auth_total_keys = array_keys($jsauth);

    /**
     * The actuall exporting
     *
     */

    foreach($jsauth as $auth_key => $auth_data)
    {
    
        $auth_dot = ($auth_key <> $auth_total_keys[sizeof($jsauth)-1]) ? ',' : '';
        $auth_saved = $auth_key;
        $auth_data = str_replace("'", "\'", $auth_data);
        $auth_data = str_replace("\n", '\n', $auth_data);
        $auth_data = (!empty($auth_data)) ? "'{$auth_data}'" : 'null';
        $auth_data = (!is_null($auth_data)) ? ($auth->acl_get($auth_key)) ? 1 : 0 : null;
        $auth_tag_to_add = " {$auth_key} : {$auth_data}{$auth_dot} ";
  
        if(preg_match('/^m/', $auth_key))
        {
            if(!$auth->acl_gets('a_') || !$auth->acl_gets('m_'))
            {
                unset($auth_tag_to_add);
            }
        }
  
        if(preg_match('/^a/', $auth_key))
        {
            if(!$auth->acl_gets('a_'))
            {
                unset($auth_tag_to_add);
            }
        }
    
        $auth_tag .= $auth_tag_to_add;
  
    }
    
    /**
     * If not registered, no info !
     *
     */
     
    if(!$user->data['is_registered'])
    {
      $auth_tag = "global : undefined";
    }
    
    /**
     * Load local permission.
     * Please add $_GET['f'] in order to get,
     * correct info for specified forum_id.
     * phpBB function is : request_var('f', 0);
     * phpBB template tag forum_id : {FORUM_ID}
     * <!-- IF FORUM_ID -->?f={FORUM_ID}<!-- ENDIF -->
     *
     */
     
    $jsauth_loc = $auth->acl_options['local'];
    
    foreach($jsauth_loc as $second_recheck => $second_rename)
    {
        if(preg_match('/^m/', $second_recheck))
        {
            if(!$auth->acl_gets('a_') || !$auth->acl_gets('m_'))
            {
                unset($jsauth_loc[$second_recheck]);
            }
        }
      
        if(preg_match('/^a/', $second_recheck))
        {
            if(!$auth->acl_gets('a_'))
            {
                unset($jsauth_loc[$second_recheck]);
          
            }
        }
    }
    
    /**
     * Local permissions
     *
     */
    
    $auth_total_keys_loc = array_keys($jsauth_loc);
 
    foreach($jsauth_loc as $auth_key_loc => $auth_data_loc)
    {
        $auth_dot_loc = ($auth_key_loc <> $auth_total_keys_loc[sizeof($jsauth_loc)-1]) ? ',' : '';
        $auth_data_loc = str_replace("'", "\'", $auth_data_loc);
        $auth_data_loc = str_replace("\n", '\n', $auth_data_loc);
        $auth_data_loc = (!empty($auth_data_loc)) ? "'{$auth_data_loc}'" : 'null';
        $auth_data_loc = ($auth->acl_gets($auth_key_loc, (int) request_var('f', 0))) ? 1 : 0;
        $auth_tag_loc_to_add = " {$auth_key_loc} : {$auth_data_loc}{$auth_dot_loc} ";
  
  
        if(preg_match('/^m/', $auth_key_loc))
        {
            if(!$auth->acl_gets('a_') || !$auth->acl_gets('m_'))
            {
                unset($auth_tag_loc_to_add);
            }
        }
      
      
        if(preg_match('/^a/', $auth_key_loc))
        {
            if(!$auth->acl_gets('a_'))
            {
                unset($auth_tag_loc_to_add);
            }
        }
      
        $auth_tag_loc .= $auth_tag_loc_to_add;
  
    }
    
    /**
     * Unset things that we don't need anymore.
     * Set content type as javascript format.
     *
     */
    
    unset($this_user);
    
    header("content-type: application/x-javascript");

?>
var user = {
   data : { <?php echo $tag; ?> }
};
var adm = {
   config : { <?php echo $config_tag; ?> },
   auth : {
   global : { <?php echo $auth_tag; ?> },
   local : { <?php echo $auth_tag_loc; ?> }
   }
};