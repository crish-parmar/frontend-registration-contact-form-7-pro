<?php
/* @access      public
 * @since       1.1 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
add_action('admin_enqueue_scripts', 'callback_frcf7_setting_up_scripts');
function callback_frcf7_setting_up_scripts() {
    if($_GET['page']=='wpcf7'){
        wp_enqueue_style('frcf7css', frcf7_plugin_url('/css/style.css'), array(), FRCF7_VERSION,'all');
    }
}

add_action( 'init', 'activate_au' );
function activate_au()
{
    $plugin_current_version = '4.6';
    $plugin_remote_path = 'http://www.wpbuilderweb.com/plugin/updates/cf7freg.php'; 
    $plugin_slug = FRCF7_PLUGIN_BASENAME;
    new WP_fra_AutoUpdate( $plugin_current_version, $plugin_remote_path, $plugin_slug );    
}

// check to make sure contact form 7 is installed and active
register_activation_hook (__FILE__, 'cf7fr_submit_activation_check');
if (function_exists('is_plugin_active')) {
    if ( !is_plugin_active( 'contact-form-7/wp-contact-form-7.php' ) ) { 
        // give warning if contact form 7 is not active
        wp_die( __( '<b>Warning</b> : Install/Activate <a href="https://wordpress.org/plugins/contact-form-7/" target="_blank">Contact Form 7</a> to activate "Contact Form 7 - Frontend Registration" plugin. <a href='.FRCF7_PLUGIN_PAGE.'>Back</a>', 'contact-form-7' ) );   
    }
}
add_action('init', 'contact_form_7_password_field', 11);
function contact_form_7_password_field() {  
    if(function_exists('wpcf7_add_form_tag')) {
        wpcf7_add_form_tag( 'Password*', 'wpcf7_password_field_shortcode_handler', true );      
    } else {
         return;        
    }
}