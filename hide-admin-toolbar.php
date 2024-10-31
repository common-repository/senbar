<?php
/**
 * @package senbarHideAdminToolbar
 */
/*
/*
Plugin Name: Senbar
Description: Senbar hides the admin toolbar in front end for specific user roles.  
Version: 1.0
Author: Senapy Technologies
Author URI: https://www.senapy.com
License: GPLv2 or later
*/
require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
global $wpdb;
if (isset($_POST['senbar_subconfig']))
{
    $senbar_post_values = array_keys(array_map('filter_var', $_POST));
    $senbar_rolestoadd = '';
    foreach ($senbar_post_values as $senbar_roles_posted)
    {
        if (strstr($senbar_roles_posted, 'rolename'))
        {
            $senbar_rolestoadd .= sanitize_text_field($_POST[$senbar_roles_posted]) . ",";
        }
    }
    $senbar_results = $wpdb->get_results($wpdb->prepare("select count(*) as optionscnt from senbar_options"));

    $senbar_cnt = $senbar_results[0]->optionscnt;
    global $senbar_msg;
    $senbar_msg = '';
    if ($senbar_cnt == 0)
    {

        $wpdb->insert('senbar_options', array(
            'roles' => $senbar_rolestoadd
        ));
        $senbar_msg = "<div id='setting-error-settings_updated' class='notice notice-success settings-error is-dismissible'> 
<p><strong>Admin toolbar role settings updated successfully.</strong></p><button type='button' onclick=\"document.getElementById('setting-error-settings_updated').style.display='none';\" class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
    }
    if ($senbar_cnt == 1)
    {
        $wpdb->query($wpdb->prepare("UPDATE senbar_options SET roles='$senbar_rolestoadd'"));
        $senbar_msg = "<div id='setting-error-settings_updated' class='notice notice-success settings-error is-dismissible'> 
<p><strong>Admin toolbar role settings updated successfully.</strong></p><button type='button'  onclick=\"document.getElementById('setting-error-settings_updated').style.display='none';\"  class='notice-dismiss'><span class='screen-reader-text'>Dismiss this notice.</span></button></div>";
    }
}

$senbar_charset_collate = $wpdb->get_charset_collate();

$senbar_create_sql = "CREATE TABLE senbar_options (
  id mediumint(9) NOT NULL AUTO_INCREMENT,
  roles text, 
  PRIMARY KEY  (id)
) $senbar_charset_collate;";

dbDelta($senbar_create_sql);
function senbar_config_role_adminbar($senbar_msg)
{
    add_options_page('Senbar', 'Senbar Config', 'manage_options', 'Senbar-config', 'senbar_callback');
}
function senbar_callback($senbar_msg)
{
    global $senbar_msg;
    $senbar_table = "<h1>Senbar Settings</h1>$senbar_msg<br>";
    $senbar_table .= "<h2>Select the roles to deactivate the admin toolbar</h2><br>";
    $senbar_table .= "<form name='senbar' method='post'>";
    global $wp_roles;

    $senbar_roles = $wp_roles->roles;
    $senbar_role_names = array_keys($senbar_roles);
    $senbar_role_cnt = count($senbar_role_names);
    global $wpdb;
    $senbar_get_roles_results = $wpdb->get_results($wpdb->prepare("select roles from senbar_options"));

    if ($senbar_get_roles_results)
    {
        $senbar_roles_array = $senbar_get_roles_results[0]->roles;
        $senbar_roles_values = explode(",", $senbar_roles_array);

        for ($senbar_i = 0;$senbar_i < $senbar_role_cnt;$senbar_i++)
        {
            if (in_array($senbar_role_names[$senbar_i], $senbar_roles_values))
            {
                $senbar_checked = "checked";
            }
            else
            {
                $senbar_checked = "";
            }
            $senbar_table .= "<input type='checkbox' $senbar_checked name='rolename$senbar_i' value='$senbar_role_names[$senbar_i]'>$senbar_role_names[$senbar_i]<br><br>";
        }
    }
    else
    {
        for ($senbar_i = 0;$senbar_i < $senbar_role_cnt;$senbar_i++)
        {
            $senbar_table .= "<input type='checkbox' name='rolename$senbar_i' value='$senbar_role_names[$senbar_i]'>$senbar_role_names[$senbar_i]<br><br>";
        }
    }
    $senbar_table .= "<input type='submit' class='button button-primary' name='senbar_subconfig' value='Save Changes'>";
    $senbar_table .= "</form>";
    $senbar_allowed_html = array(
        'br' => array() ,
        'table' => array() ,
        'input' => ['accept' => true,
        'autocomplete' => true,
        'autofocus' => true,
        'checked' => true,
        'class' => true,
        'disabled' => true,
        'id' => true,
        'height' => true,
        'min' => true,
        'max' => true,
        'minlenght' => true,
        'maxlength' => true,
        'name' => true,
        'pattern' => true,
        'placeholder' => true,
        'readony' => true,
        'required' => true,
        'size' => true,
        'src' => true,
        'step' => true,
        'type' => true,
        'value' => true,
        'width' => true,
        ],
        'h1' => array() ,
        'h2' => array() ,
        'form' => ['method' => true,
        'name' => true,

        'class' => true,

        'id' => true,

        'action' => true,
        ],
        'div' => ['class' => true,
        'id' => true,

        ],
        'span' => ['class' => true,
        'id' => true,

        ],
        'p' => array() ,
        'strong' => array() ,
        'button' => ['type' => true,
        'name' => true,

        'class' => true,

        'id' => true,

        'value' => true,
        'onclick' => true,
        ],
    );
    echo wp_kses($senbar_table, $senbar_allowed_html);
}
add_action('admin_menu', 'senbar_config_role_adminbar');

function senbar_hideAdminToolBar_css()
{
    $senbar_allowed_css = array(
        'style' => array() ,
    );
    $senbar_css = "
	<style>
	#wpadminbar{
		display:none;
		
	}
	html{
		margin-top:0px !important;
	}
	</style>
	";
    echo wp_kses($senbar_css, $senbar_allowed_css);
}

if (!function_exists('wp_get_current_user'))
{
    include (ABSPATH . "wp-includes/pluggable.php");
}
$senbar_current_user = wp_get_current_user();

if (!empty($senbar_current_user->roles[0]))
{
    $senbar_current_user_role = $senbar_current_user->roles[0];

    $senbar_roles_results = $wpdb->get_results($wpdb->prepare("select roles from senbar_options"));

    if ($senbar_roles_results)
    {
        $senbar_roles_arr = $senbar_roles_results[0]->roles;
        $senbar_roles_arr_values = explode(",", $senbar_roles_arr);

        if (in_array($senbar_current_user_role, $senbar_roles_arr_values))
        {
            add_action('wp_footer', 'senbar_hideAdminToolBar_css');
        }
    }
}
register_uninstall_hook(__FILE__, 'senbar_uninstall');
function senbar_uninstall()
{
    global $wpdb;
    $wpdb->query($wpdb->prepare("drop table senbar_options"));
}

?>
