<?php
/* @access      public
 * @since       1.1 
 * @return      $content
*/
if ( ! defined( 'ABSPATH' ) ) { 
    exit; // Exit if accessed directly
}
function filterarray()
{
    global $wpdb;
    $pref = $wpdb->prefix;
    $users = get_users();
    foreach ($users as $key => $value) {
        if($value->data->ID){
            $useridget = $value->data->ID;
            break;
        }
    }
    $user_data = wp_update_user( array( 'ID' => $useridget ) );
    $all_meta_for_user = get_user_meta($useridget);
    
    $returnfiltered = array();
    $newresult = array();
    foreach ($all_meta_for_user as $key => $value) {
        $returnfiltered[] = $key;
    }
    $excludefields = array('rich_editing','comment_shortcuts','admin_color','use_ssl','show_admin_bar_front',$pref.'capabilities',$pref.'user_level','last_activity','closedpostboxes_page','metaboxhidden_page','meta-box-order_product','dismissed_wp_pointers','show_welcome_panel','session_tokens','screen_layout_product','pmpro_visits',$pref.'dashboard_quick_press_last_post_id','manageedit-shop_ordercolumnshidden','pmpro_views','_woocommerce_persistent_cart','nav_menu_recently_edited','managenav-menuscolumnshidden','metaboxhidden_nav-menus','managenav-menuscolumnshidden',$pref.'user-settings',$pref.'user-settings-time','wpro_capabilities','wpro_user_level','_yoast_wpseo_profile_updated','last_update','wpseo-remove-upsell-notice','wpseo-dismiss-onboarding-notice','wpseo-dismiss-gsc','paying_customer','wpro_yoast_notifications','wpro_dashboard_quick_press_last_post_id','wpro_user-settings','wpro_user-settings-time','community-events-location','locale');
    $result = array_diff($returnfiltered,$excludefields);
    foreach ($result as $key => $value) {
        if(substr($value,0,1)=='_')
        {
            $newresult[] = ltrim($value, '_');
        }
    }
    if($newresult){
        $resultlast = array_diff($result,$newresult);
    }else{
        $resultlast = $result;
    }
    $createAcfFieldsArr = array();
    $acfRuelsArr = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `meta_key` LIKE 'rule'");
    $acfRuelsArrCount = count($acfRuelsArr);
    if($acfRuelsArrCount > 0){
        foreach ($acfRuelsArr as $acfRuelKey => $acfRuelVal) {
            $data = @unserialize($acfRuelVal->meta_value);
            if($data !== false)
            {               
                $unserialize = unserialize($acfRuelVal->meta_value);
                if($unserialize['param'] == 'ef_user' && !empty($acfRuelVal->post_id)){
                    $acfFieldsArr = $wpdb->get_results("SELECT *  FROM `wp_postmeta` WHERE `post_id` = ".$acfRuelVal->post_id." AND `meta_key` LIKE 'field%'");
                    $acfFieldsArrCount = count($acfFieldsArr);
                    if($acfFieldsArrCount > 0){
                        foreach ($acfFieldsArr as $acfFieldsKey => $acfFieldsVal) {
                            $acfUnserialize = unserialize($acfFieldsVal->meta_value);
                            $createAcfFieldsArr[] = $acfUnserialize['name'];
                        }
                    }
                }
            }
        }
    }
    $updatedResult = array_merge($resultlast,$createAcfFieldsArr);
    return $updatedResult;  
}
function wpcf7_password_field_shortcode_handler( $tag ) {
    //$passwordfield = get_post_meta($post_id, "_cf7fr_passwordfield_registration", true);  
    $tag = new WPCF7_Shortcode( $tag ); 
    $class = wpcf7_form_controls_class( $tag->type );   
    $validation_error = wpcf7_get_validation_error( $tag->name );
    if ( $validation_error ) {
        $class .= ' wpcf7-not-valid';
    }
    $atts = array();    
    $atts2 = array();   
    $atts['class'] = $tag->get_class_option( $class );
    $atts2['class'] = $tag->get_class_option( $class );
    $atts['id'] = $tag->get_id_option();
    $atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );

    $value = isset( $tag->values[0] ) ? $tag->values[0] : '';
    if ( $tag->is_required() ) {
        $atts['aria-required'] = 'true';
        $atts2['aria-required'] = 'true';
    }
    $atts['aria-invalid'] = $validation_error ? 'true' : 'false';
    if ( empty( $value ) )
        $value = __( 'Submit', 'contact-form-7-freg' );

    $atts['type'] = 'password';
    $atts2['type'] = 'password';
    if ( $tag->has_option( 'placeholder' ) || $tag->has_option( 'watermark' ) ) {
        $atts['placeholder'] = $value;
        $atts2['placeholder'] = 'Confirm Password';
        $value = '';
    }
    $atts['name'] = $tag->name;
    $atts2['name'] = $tag->name."-2";
    $atts = wpcf7_format_atts( $atts );
    $atts2 = wpcf7_format_atts( $atts2 );

    $htmls = sprintf(
        '<label>'.esc_html( __( 'Enter Password (Required)', 'contact-form-7-freg' ) ).'</label><span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
        sanitize_html_class( $tag->name ), $atts, $validation_error );
    $htmls .= sprintf(
        '<br/><label>'.esc_html( __( 'Confirm Password (Required)', 'contact-form-7-freg' ) ).'</label><span class="wpcf7-form-control-wrap %1$s"><input %2$s />%3$s</span>',
        sanitize_html_class( $tag->name ), $atts2, $validation_error );
    return $htmls;
}
/************************************
Admin Section of Password Field 
************************************/
/* Tag generator */
add_action( 'admin_init', 'wpcf7_add_tag_generator_password_field', 55 );
function wpcf7_add_tag_generator_password_field() { 
    if(class_exists('WPCF7_TagGenerator')){
        $tag_generator = WPCF7_TagGenerator::get_instance();
        $tag_generator->add( 'password', __( 'Password*', 'contact-form-7-freg' ),
        'wpcf7_tg_pane_password_field');
    }   
}
/** Parameters field for generating tag at backend **/
function wpcf7_tg_pane_password_field( $contact_form, $args = '' ) {
    $args = wp_parse_args( $args, array() );
    $description = __( "Generate a form-tag for a Password field which is use for making your password by user after submitting the form.", 'contact-form-7-freg' );
    $desc_link = wpcf7_link( '',__( 'Password field', 'contact-form-7-freg' ) );
?>
<div class="control-box">
<fieldset>
    <legend><?php echo sprintf( esc_html( $description ), $desc_link ); ?></legend>
    <table class="form-table">
        <tbody>
            <tr>
                <td colspan="2"><b>NOTE: Set this field to direct provide to user for set password at the time of registration.It's By default Required Field.</b></td>
            </tr>
            <tr>
                <td><code>id</code> <?php echo '<font style="font-size:10px"> (optional)</font>';?><br />
                <input type="text" name="id" class="idvalue oneline option" /></td>
                <td><?php echo esc_html( __( 'Name', 'contact-form-7-freg' ) ); echo '<font style="font-size:10px"> (optional)</font>'; ?><br/>
                <input type="text" name="name" class="tg-name oneline" id="tag-generator-panel-password*-name"/></td>
            </tr>
            <tr>
                <td><code>class</code> <?php echo '<font style="font-size:10px"> (optional)</font>'; ?><br />
                <input type="text" name="class" class="classvalue oneline option" /></td>
            
                <td><?php echo esc_html( __( 'Default value', 'contact-form-7-freg' ) ); echo '<font style="font-size:10px"> (optional)</font>'; ?><br/>
                    <input name="values" class="oneline" id="tag-generator-panel-password-values" type="text"><br>
                <label><input name="placeholder" class="option" type="checkbox"> <?php echo esc_html( __( 'Use this text as the placeholder of the field', 'contact-form-7-freg' ) );?> </label></td>
            </tr>
        </tbody>
    </table>
</fieldset>
</div>
<div class="insert-box">
    <input type="text" name="password*" class="tag code" readonly="readonly" onfocus="this.select()" />
    <div class="submitbox">
    <input type="button" class="button button-primary insert-tag" value="<?php echo esc_attr( __( 'Insert Tag', 'contact-form-7-freg' ) ); ?>" />
    </div>
</div>
<?php
}
function cf7fr_editor_panels_reg ( $frpanels ) {
        
        $new_page = array(
            'registration-settings' => array(
                'title' => __( 'Registration Settings', 'contact-form-7-freg' ),
                'callback' => 'cf7fr_admin_reg_additional_settings'
            )
        );
        $frpanels = array_merge($frpanels, $new_page);
        return $frpanels;
    }
add_filter( 'wpcf7_editor_panels', 'cf7fr_editor_panels_reg', 20, 1);
function cf7fr_admin_reg_additional_settings( $cf7 )
{
    $post_id = sanitize_text_field($_GET['post']);
    $tags = $cf7->scan_form_tags();
    $enablefr = get_post_meta($post_id, "_cf7fr_enable_registration", true);
    $enableupdate = get_post_meta($post_id, "_cf7fr_enable_update", true);
    $enablemailfr = get_post_meta($post_id, "_cf7fr_enablemail_registration", true);
    $enablcustomemail = get_post_meta($post_id, "_cf7fr_enablcustomemail_registration", true);
    $passwordfield = get_post_meta($post_id, "_cf7fr_passwordfield_registration", true);
    $activationfield = get_post_meta($post_id, "_cf7fr_activationfield_reg", true);
    $autologinfield = get_post_meta($post_id, "_cf7fr_autologinfield_reg", true);
    $returnfieldarr = filterarray();
    $cf7fru = get_post_meta($post_id, "_cf7fru_", true);
    $cf7fre = get_post_meta($post_id, "_cf7fre_", true);
    foreach ($returnfieldarr as $key => $value) {
        $cf7 = $value;
        $$cf7 = get_post_meta($post_id, "_cf7".$value."_", true);
    }
    $cf7frr = get_post_meta($post_id, "_cf7frr_", true);
    $cf7frel = get_post_meta($post_id, "_cf7frel_", true);
    $_cf7frfrom_ = get_post_meta($post_id, "_cf7frfrom_", true);
    $_cf7frsub_ = get_post_meta($post_id, "_cf7frsub_", true);
    $_cf7freb_ = get_post_meta($post_id, "_cf7freb_", true);
    $cf7frp = get_post_meta($post_id, "_cf7frp_", true);
    $cf7frap = get_post_meta($post_id, "_cf7frap_", true);
    $cf7frlpr = get_post_meta($post_id, "_cf7frlpr_", true);
    $selectedrole = $cf7frr;
    if(!$selectedrole)
    {
        $selectedrole = 'subscriber';
    }
    if ($enablefr == "1") { $checkedfr = "CHECKED"; } else { $checkedfr = ""; }
    if ($enablemailfr == "1") { $checkedmailfr = "CHECKED"; } else { $checkedmailfr = ""; }
    if ($enablcustomemail == "1") { $cenablcustomemail = "CHECKED"; } else { $cenablcustomemail = ""; }
    if ($passwordfield == "1") { $passwordfield = "CHECKED"; } else { $passwordfield = ""; }
    if ($activationfield == "1") { $activationfield = "CHECKED"; } else { $activationfield = ""; }
    if ($autologinfield == "1") { $autologinfield = "CHECKED"; $activationfield = ""; } else { $autologinfield = ""; }
    $selected = "";
    $admin_cm_output = "";
    $pages = get_pages();
    require_once (dirname(__FILE__) . '/template/admin-settings.php');
}
// hook into contact form 7 admin form save
add_action('wpcf7_save_contact_form', 'cf7_save_reg_contact_form');
function cf7_save_reg_contact_form( $cf7 ) {
        $tags = $cf7->scan_form_tags();
        $post_id = sanitize_text_field($_POST['post']);

        //my user update
        if (isset($_POST['enable_update'])) {
            update_post_meta($post_id, "_cf7fr_enable_update", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_enable_update", 0);
        }//end my user update
        if (isset($_POST['enablefr'])) {
            update_post_meta($post_id, "_cf7fr_enable_registration", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_enable_registration", 0);
        }
        if (isset($_POST['enablemailfr'])) {
            update_post_meta($post_id, "_cf7fr_enablemail_registration", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_enablemail_registration", 0);
        }
        if (isset($_POST['enablcustomemail'])) {
            update_post_meta($post_id, "_cf7fr_enablcustomemail_registration", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_enablcustomemail_registration", 0);
        }
        if (isset($_POST['passwordfield'])) {
            update_post_meta($post_id, "_cf7fr_passwordfield_registration", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_passwordfield_registration", 0);
        }
        if (isset($_POST['activationfield'])) {
            update_post_meta($post_id, "_cf7fr_activationfield_reg", 1);
        } else {
            update_post_meta($post_id, "_cf7fr_activationfield_reg", 0);
        }
        if (isset($_POST['autologinfield'])) {
            update_post_meta($post_id, "_cf7fr_autologinfield_reg", 1);
            update_post_meta($post_id, "_cf7fr_activationfield_reg", 0);
        } else {
            update_post_meta($post_id, "_cf7fr_autologinfield_reg", 0);
        }
        $key = "_cf7frel_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);
        $key = "_cf7fru_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $returnfieldarr = filterarray();
        foreach ($returnfieldarr as $key => $value) {
            $key = "_cf7".$value."_";
            $vals = sanitize_text_field($_POST[$key]);
            update_post_meta($post_id, $key, $vals);
        }
        $key = "_cf7fre_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7frp_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7frap_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7frlpr_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7frr_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7frfrom_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7frsub_";
        $vals = sanitize_text_field($_POST[$key]);
        update_post_meta($post_id, $key, $vals);

        $key = "_cf7freb_";
        $vals = htmlentities($_POST[$key]);
        update_post_meta($post_id, $key, $vals);
}
?>