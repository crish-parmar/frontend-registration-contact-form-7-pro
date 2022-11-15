<?php
/* @access      public
 * @since       1.1 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}

if(isset($_GET['key']) && isset($_GET['action'])){
	global $wpdb;
	$retID = $wpdb->get_results("SELECT ID FROM $wpdb->users WHERE user_activation_key = '".$_GET['key']."'");
	
	if($retID){
	    $userid = $retID[0]->ID;
		$wpdb->query("UPDATE $wpdb->usermeta SET meta_value = 0 WHERE meta_key='cf7fu_disable_user' and user_id=".$userid);
		$wpdb->query("UPDATE $wpdb->users SET user_activation_key = '' WHERE ID=".$retID[0]->ID);
	}
}
function frcf7_plugin_url( $path = '' ) {
    $url = plugins_url( $path, FRCF7_PLUGIN );

    if ( is_ssl()
    and 'http:' == substr( $url, 0, 5 ) ) {
        $url = 'https:' . substr( $url, 5 );
    }

    return $url;
}
 
function create_user_from_registration($cfdata) {
    global $wpcf7,$loginlink;
    
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $cf7fru = get_post_meta($post_id, "_cf7fru_", true);
    $cf7fre = get_post_meta($post_id, "_cf7fre_", true);
    $returnfieldarr = filterarray();
    foreach ($returnfieldarr as $key => $value) {
        $cf7 = $value;
        $$cf7 = get_post_meta($post_id, "_cf7".$value."_", true);
    }
    $cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    $cf7frel = get_post_meta($post_id, "_cf7frel_", true);
    $cf7rarg = get_post_meta($post_id, "_cf7rarg_", true);

    $passwordfield = get_post_meta($post_id, "_cf7fr_passwordfield_registration", true);
    $activationfield = get_post_meta($post_id, "_cf7fr_activationfield_reg", true);
    $autologinfield = get_post_meta($post_id, "_cf7fr_autologinfield_reg", true);
    $redirectafterreg = get_post_meta($post_id, "_cf7fr_redirectafterreg_reg", true);
    if ($autologinfield == "1") { $activationfield = ""; }
    $cf7frp = get_post_meta($post_id, "_cf7frp_", true);

    $enable = get_post_meta($post_id,'_cf7fr_enable_registration');
    if($enable[0]!=0)
    {
            if (!isset($cfdata->posted_data) && class_exists('WPCF7_Submission')) {
                $submission = WPCF7_Submission::get_instance();
                if ($submission) {
                    $formdata = $submission->get_posted_data();
                }
            } elseif (isset($cfdata->posted_data)) {
                $formdata = $cfdata->posted_data;
            }
        if($cf7fre != ''){ $email = $formdata["".$cf7fre.""]; }
        if($cf7fru != ''){ $name = $formdata["".$cf7fru.""]; }
        if($cf7frp != ''){ $pass = $formdata["".$cf7frp.""]; }
        if($activationfield=="1"){$user_status = 1;}else{$user_status=0;}
        // Construct a username from the user's name
        $username = strtolower(str_replace(' ', '', $name));
        $name_parts = explode(' ',$name);
        if ( !email_exists( $email ) ) 
        {
            //Find an unused username
            $username_tocheck = $username;
            $i = 1;
            while ( username_exists( $username_tocheck ) ) {
                $username_tocheck = $username . $i++;
            }
            $username = $username_tocheck;
            $user_email = $email;
            // Create the user
            
            foreach ($returnfieldarr as $key => $value) {
                    $cf7 = $value;
                    $key = "_cf7".$value."_";
                    $cf7 = get_post_meta($post_id, $key, true);
                    if($value != '' && $cf7 != ''){
                        $dynamicarray[$value] = $formdata[$cf7];
                    }
                }
                
            if($passwordfield =="1"){
                $password = $pass;
            }else{
                $password = wp_generate_password( 12, false );
            }
            // Create the user
            $userdata = array(
                'user_login' => $username,
                'user_pass' => $password,
                'user_email' => $email,
                'role' => $cf7frr
            );
            $mergeuserdata = array_merge($dynamicarray,$userdata);
            $user_id = wp_insert_user( wp_slash ( $mergeuserdata ) );
            
            if($user_id)
            {
                if($dynamicarray){
                    foreach ($dynamicarray as $key => $value) {
                        if(substr($key, 0,1)=="_"){
                            $field_name = str_replace("_","",$key);
                            $fieldkey = acf_get_field_key($field_name);
                            update_user_meta( $user_id, $key, $fieldkey );
                            update_user_meta( $user_id, $field_name, $value );
                        }else{
                            update_user_meta( $user_id, $key, $value );
                        }
                    }
                }
                update_user_meta( $user_id, 'cf7fu_disable_user', $user_status );
            }
            if(!$cf7frel)
            {
                $loginlink = esc_url(wp_login_url());
            }
            else
            {
                $loginlink = esc_url($cf7frel);
            }
            
            if($activationfield == "1"){
            	if ( $user_id && !is_wp_error( $user_id ) ) {
                    $salt = wp_generate_password(20);
                    $code = sha1($salt . $user_email . uniqid(time(), true));
                    global $wpdb;
                    $wpdb->query("UPDATE $wpdb->users SET user_activation_key = '".$code."' WHERE ID=".$user_id);
                    $cf7frap = get_post_meta($post_id, "_cf7frap_", true);
                    if(isset($cf7frap)){
                        $activation_URL = esc_url($cf7frap);
                    }else{
                        $activation_URL = esc_url(get_site_url());
                    }
                    $activation_link = add_query_arg( array( 'key' => $code, 'action' => 'act' ), $activation_URL);
                    sendEmailToUserInactive($post_id, $user_email, $username , $activation_link, $loginlink, $password);    
                }
                
            }else{
            	if ( !is_wp_error($user_id) ) {
                    // Email login details to user
                    sendEmailToUser($post_id, $email, $username, $loginlink, $password);    
                }
            }
            if ($autologinfield == "1") {

                $user = get_user_by( 'id', $user_id );

                if( $user ) {
                    wp_set_current_user( $user_id, $user->user_login );
                    wp_set_auth_cookie( $user_id );
                    do_action( 'wp_login', $user->user_login );

                }

            }
        }

    }
    return $cfdata;
}
add_action('wpcf7_before_send_mail', 'create_user_from_registration', 1, 2);
function your_validation_text_func( $result, $tag ) 
{
    global $wpcf7;
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $cf7fru = get_post_meta($post_id, "_cf7fru_", true);
    $tag = new WPCF7_FormTag( $tag );
    $type = $tag->type;
    $name = $tag->name;
    global $wpdb;
    if(isset($_POST[''.$cf7fru.'']) && $_POST[''.$cf7fru.'']!="")
    {
        if($name =="".$cf7fru."")
        {
            $username = $_POST[''.$cf7fru.''];
            if(username_exists($username))
            {
                   $result->invalidate($tag, __( 'Username already registered!.', 'contact-form-7-freg' ));
                 
            }

        }
    }
   
    return $result;
 }
add_filter( 'wpcf7_validate_text*', 'your_validation_text_func', 20, 2 );
function your_validation_password_func( $result, $tag ) 
{
    global $wpcf7;
    $tag = new WPCF7_FormTag( $tag );
    $type = $tag->type;
    $name = $tag->name;
    $name2 = $tag->name."-2";
    //global $wpdb;
    if( isset($_POST[''.$name.'']) && $_POST[''.$name.'']=="" )
    {
       $result->invalidate($tag, __( 'Please enter Password', 'contact-form-7-freg' ));
    }
    if( isset( $_POST[''.$name2.''] ) && $_POST[''.$name2.'']=="" )
    {
       $result->invalidate($tag, __( 'Please enter Confirm Password', 'contact-form-7-freg' ));
    }
    if( $_POST[''.$name.''] != $_POST[''.$name2.''])
    {
       // $result->invalidate( $tag, __( 'Password & Confirm Password do not match.', 'contact-form-7-freg' )); 
        $result['valid'] = false;
        $result['reason'] = array( $name => sprintf( __( "Password & Confirm Password do not match.", 'contact-form-7-freg' ) ) ); 
    }

    return $result;
 }
add_filter( 'wpcf7_validate_password*', 'your_validation_password_func', 20, 2 );
add_filter( 'wpcf7_validate_password', 'your_validation_password_func', 20, 2 );
function your_validation_email_filter( $result, $tag ) 
{
    global $wpcf7;
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $cf7fre = get_post_meta($post_id, "_cf7fre_", true);
    $tag = new WPCF7_FormTag( $tag );
    $type = $tag->type;
    $name = $tag->name;
    global $wpdb;
     if(isset($_POST[''.$cf7fre.'']) && $_POST[''.$cf7fre.'']!="")
    {
        if($name =="".$cf7fre."")
        {
            $email = $_POST[''.$cf7fre.''];
            if(email_exists($email))
            {
                $result->invalidate($tag, __( 'Email already registered!', 'contact-form-7-freg' ));
                 
            }
        }
    }
    return $result;
}
add_filter( 'wpcf7_validate_email*', 'your_validation_email_filter', 20, 2 );
add_filter( 'wpcf7_validate_email', 'your_validation_email_filter', 20, 2 );

add_filter( 'wpcf7_skip_mail', function( $skip_mail, $contact_form ) {
    $post_id = sanitize_text_field($_POST['_wpcf7']);
    $enablemail = get_post_meta($post_id,'_cf7fr_enablemail_registration');
    if($enablemail[0]==1){
        $skip_mail = true;
    }
    return $skip_mail;
}, 10, 2 );
function sendEmailToUser($post_id, $email, $username, $loginlink, $password)
{
    $_cf7frfrom_ = get_post_meta($post_id, "_cf7frfrom_", true);
    $_cf7frsub_ = get_post_meta($post_id, "_cf7frsub_", true);
    $_cf7freb_ = get_post_meta($post_id, "_cf7freb_", true);
    $cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $enablcustomemail = get_post_meta($post_id,'_cf7fr_enablcustomemail_registration');
    
    if($enablcustomemail[0]==1){
        $_cf7freb_ = str_replace("[login-user]",$username,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-user-name]",$username,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-password]",$password,$_cf7freb_);
        $_cf7freb_ = str_replace("[site-name]",$blogname,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-link]",$loginlink,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-email]",$email,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-role]",$cf7frr,$_cf7freb_);
        // Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: '.$blogname.' <'.$_cf7frfrom_.'>' . "\r\n";
        $body = nl2br($_cf7freb_,false);
        $body = html_entity_decode($body);
        $message = '<html>';
        $message .= '<body>';
        $message .= $body;
        $message .= '</body>';
        $message .= '</html>';
        wp_mail($email, sprintf(__('[%s] - '.$_cf7frsub_), $blogname), $message, $headers);
    }else{
        $message = "Welcome! Your login details are as follows:" . "\r\n";
        $message .= sprintf(__('Username: %s'), $username) . "\r\n";
        $message .= sprintf(__('Password: %s'), $password) . "\r\n";
        $message .= $loginlink . "\r\n";
        wp_mail($email, sprintf(__('[%s] Your username and password', 'contact-form-7-freg'), $blogname), $message);
    }
}
function sendEmailToUserInactive($post_id, $email, $username, $activationlink, $loginlink, $password){
    $_cf7frfrom_ = get_post_meta($post_id, "_cf7frfrom_", true);
    $_cf7frsub_ = get_post_meta($post_id, "_cf7frsub_", true);
    $_cf7freb_ = get_post_meta($post_id, "_cf7freb_", true);
    $cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
    $enablcustomemail = get_post_meta($post_id,'_cf7fr_enablcustomemail_registration');
    if($enablcustomemail[0]==1){
        $_cf7freb_ = str_replace("[login-user]",$username,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-user-name]",$username,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-password]",$password,$_cf7freb_);
        $_cf7freb_ = str_replace("[site-name]",$blogname,$_cf7freb_);
        $_cf7freb_ = str_replace("[activation-link]",$activationlink,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-email]",$email,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-link]",$loginlink,$_cf7freb_);
        $_cf7freb_ = str_replace("[login-role]",$cf7frr,$_cf7freb_);
        // Always set content-type when sending HTML email
		$headers = "MIME-Version: 1.0" . "\r\n";
		$headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
		$headers .= 'From: '.$blogname.' <'.$_cf7frfrom_.'>' . "\r\n";
        $body = nl2br($_cf7freb_,false);
        $body = html_entity_decode($body);
        $message = '<html>';
        $message .= '<body>';
        $message .= $body;
        $message .= '</body>';
        $message .= '</html>';
        wp_mail($email, sprintf(__('[%s] - '.$_cf7frsub_), $blogname), $message, $headers);
    }else{
        $message = "Welcome! Your login details are as follows:" . "\r\n";
        $message .= sprintf(__('Username: %s'), $username) . "\r\n";
        $message .= sprintf(__('Password: %s'), $password) . "\r\n";
        $message .= $activationlink . "\r\n";
        wp_mail($email, sprintf(__('[%s] Your username and password', 'contact-form-7-freg' ), $blogname), $message);
    }
}
function acf_field_key($field_name, $post_id = false){
    
    if ( $post_id )
        return get_field_reference($field_name, $post_id);
    
    if( !empty($GLOBALS['acf_register_field_group']) ) {
        
        foreach( $GLOBALS['acf_register_field_group'] as $acf ) :
            
            foreach($acf['fields'] as $field) :
                
                if ( $field_name === $field['name'] )
                    return $field['key'];
            
            endforeach;
            
        endforeach;
    }
        return $field_name;
}
function acf_get_field_key( $field_name ) {
    global $wpdb;
    $result = $wpdb->get_results("SELECT * from ".$wpdb->prefix."postmeta WHERE meta_value like '%".$field_name."%' AND meta_key like '%field_%'");
    return $result[0]->meta_key;
}
//This function prints the JavaScript to the footer
function cf7_footer_script(){ ?>
<script>
document.addEventListener( 'wpcf7mailsent', function( event ) {
    location = '<?php echo $url = get_option( '_cf7rarg_' ) ? get_option( '_cf7rarg_' ) : get_home_url(); ?>';
}, false );
</script>
<?php } 
  add_action('wp_footer', 'cf7_footer_script');
?>