<?php
/* @access      public
 * @since       1.1 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
function user_login_message() {
    $message = '';
    // Show the error message if it seems to be a disabled user
    if ( isset( $_GET['disabled'] ) && $_GET['disabled'] == 1 ) 
        $message =  '<div id="login_error">' . apply_filters( 'ja_disable_users_notice', __( 'Account disabled. Please confirm first from your email.', 'ja_disable_users' ) ) . '</div>';

    return $message;
}
add_filter( 'login_message','user_login_message');

add_action( 'wp_authenticate','user_login');
function user_login($username) {
    global $wpdb;
    if ( ! username_exists( $username ) ) {
        return;
    }
    $userinfo = get_user_by( 'login', $username );
    // Get user meta
    $disabled = get_user_meta( $userinfo->data->ID, 'cf7fu_disable_user', true );
    // Is the use logging in disabled?
    if ( $disabled == '1' ) {
        // Clear cookies, a.k.a log user out
        wp_clear_auth_cookie();
        // Build login URL and then redirect
        $login_url = site_url( 'wp-login.php', 'login' );
        $login_url = add_query_arg( 'disabled', '1', $login_url );
        wp_redirect( $login_url );
        exit;
    }
}
