<?php
/**
 * Plugin Name: Frontend Registration - Contact Form 7 Pro
 * Plugin URL: http://www.wpbuilderweb.com/frontend-registration-contact-form-7/
 * Description:  This plugin will convert your Contact form 7 in to registration form for WordPress. You can also use User meta field by created ACF plugin.
 * Version: 4.6
 * Author: David Pokorny
 * Author URI: http://www.wpbuilderweb.com/
 * Developer: Pokorny David
 * Developer E-Mail: pokornydavid4@gmail.com
 * Text Domain: contact-form-7-freg
 * Domain Path: /languages
 * 
 * Copyright: © 2009-2015 izept.com.
 * License: GNU General Public License v3.0
 * License URI: http://www.gnu.org/licenses/gpl-3.0.html
 */
/**
 * 
 * @access      public
 * @since       1.1
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
define( 'FRCF7_VERSION', '4.6' );

define( 'FRCF7_PLUGIN', __FILE__ );

define( 'FRCF7_PLUGIN_BASENAME', plugin_basename( FRCF7_PLUGIN ) );

define( 'FRCF7_PLUGIN_NAME', trim( dirname( FRCF7_PLUGIN_BASENAME ), '/' ) );

define( 'FRCF7_PLUGIN_DIR', untrailingslashit( dirname( FRCF7_PLUGIN ) ) );

define( 'FRCF7_PLUGIN_CSS_DIR', FRCF7_PLUGIN_DIR . '/css' );

define( 'FRCF7_PLUGIN_PAGE', get_admin_url().'plugins.php');

require_once (dirname(__FILE__) . '/frontend-registration-cf7-update.php');
require_once (dirname(__FILE__) . '/frontend-registration-opt-cf7.php');
require_once (dirname(__FILE__) . '/frontend-registration-activation-cf7.php');
require_once (dirname(__FILE__) . '/frontend-registration-login-cf7.php');
require_once (dirname(__FILE__) . '/frontend-registration-admin-cf7.php');