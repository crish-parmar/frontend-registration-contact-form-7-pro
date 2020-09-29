<?php
echo "<div id='additionalsettings-registration' class='meta-box'><div id='additionalsettingsdiv'>";
    echo "<div class='handlediv' title='Click to toggle'></div><h2 class='hndle ui-sortable-handle'> ".esc_html( 'Frontend Registration Settings')." </h2>";
        echo "<div class='inside'>";
            echo "<div class='checkbox-settings'>";
                echo "<div class='mail-field pretty p-switch p-fill'>";
                    echo "<input name='enablefr' type='checkbox' $checkedfr>";
                echo "<div class='state'><label>".esc_html( 'Enable Registration on this form')."</label></div>";
                echo "</div>";
                echo "<div class='mail-field pretty p-switch p-fill'>";
                    echo "<input name='enablemailfr' value='' type='checkbox' $checkedmailfr>";
                echo "<div class='state'><label>".esc_html( 'Skip Contact Form 7 Mails ?')."</label></div>";
                echo "</div>";
                echo "<div class='mail-field pretty p-switch p-fill'>";
                    echo "<input name='enablcustomemail' value='' type='checkbox' $cenablcustomemail>";
                echo "<div class='state'><label>".esc_html( 'Enable custom email for registration detail?')."</label></div>";
                echo "</div>";
                echo "<div class='mail-field pretty p-switch p-fill'>";
                    echo "<input name='passwordfield' value='' type='checkbox' $passwordfield>";
                echo "<div class='state'><label>".esc_html( 'Enable Password field for registration ?')."</label></div>";
                echo "</div>";
                echo "<div class='mail-field pretty p-switch p-fill'>";
                    echo "<input name='activationfield' value='' type='checkbox' $activationfield>";
                echo "<div class='state'><label>".esc_html( 'Enable activation link functionality ?')."</label></div>";
                echo "</div>";
                echo "<div class='mail-field pretty p-switch p-fill'>";
                    echo "<input name='autologinfield' value='' type='checkbox' $autologinfield>";
                echo "<div class='state'><label>".esc_html( 'Enable auto login after registration? ( If you enable Auto Login activation link will disable automatically. )')."</label></div>";
                echo "</div>";
                echo "</div>";
            echo "<div class='other-settings'>";
                echo "<h2 class='hndle ui-sortable-handle'>".esc_html( 'Form Field Settings:')."</h2>";
                echo "<table>";
                echo "<tr><td>".esc_html( 'Insert Custom Login Link Here :')."</td></tr>";
                echo "<tr><td><input type='text' name='_cf7frel_' value='$cf7frel' size='50' /></td></tr>";
                echo "<tr><td>".esc_html( 'Selected Field Name For User Name :')."</td></tr>";
                echo "<tr><td><select name='_cf7fru_'>";
                echo "<option value=''>".esc_html( 'Select Field')."</option>";
                foreach ($tags as $key => $value) {
                    if($cf7fru==$value['name']){$selected='selected=selected';}else{$selected = "";}            
                    echo "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
                }
                echo "</select>";
                echo "</td></tr>";
                echo "<tr><td>".esc_html( 'Selected Field Name For Email Address :')."</td></tr>";
                echo "<tr><td><select name='_cf7fre_'>";
                echo "<option value=''>".esc_html( 'Select Field')."</option>";
                foreach ($tags as $key => $value) {
                    if($cf7fre==$value['name']){$selected='selected=selected';}else{$selected = "";}            
                    echo "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
                }
                echo "</select>";
                echo "</td></tr>";
                echo "<tr><td><h2>".esc_html( 'Select Other User Fields values :')."</h2></td></tr>";
                foreach ($returnfieldarr as $key => $value) {
                    echo "<tr class='border'><td>Selected Field Name For <strong>".$value."</strong>:";
                    echo "</td><td><select name='_cf7".$value."_'>";
                    echo "<option value=''>Select Field</option>";
                    $cf7 = $value;
                    foreach ($tags as $key => $values) {
                        if($$cf7==$values['name']){$selected='selected=selected';}else{$selected = "";}         
                        echo "<option ".$selected." value='".$values['name']."'>".$values['name']."</option>";
                    }
                    echo "</select>";
                    echo "</td></tr>";
                }
                echo "<tr><td style='color:red;'><b>Note :</b> Above Field list are display from User Meta Table. If your custom Field not listed in above list then Just go in your admin Profile and Once Update Profile from Admin side. <a href='".get_site_url()."/wp-admin/profile.php'>Click Here and Update Profile.</a> For Custom field we must prefered Advance Custom Field Plugin (ACF Plugin).</td></tr>";
                echo "<tr><td>Selected User Role:</td></tr>";
                echo "<tr><td>";
                echo "<select name='_cf7frr_'>";
                    wp_dropdown_roles($selectedrole);
                echo "</select>";
                echo "</td></tr>";
                echo "<tr><td>Selected Field Name For Password :</td></tr>";
                echo "<tr><td><select name='_cf7frp_'>";
                echo "<option value=''>Select Field</option>";
                foreach ($tags as $key => $value) {
                    if($cf7frp==$value['name']){$selected='selected=selected';}else{$selected = "";}
                    echo "<option ".$selected." value='".$value['name']."'>".$value['name']."</option>";
                }
                echo "</select>";
                echo "</td></tr>";

                echo "<tr><td>Selected Page as activation : (optional)</td></tr>";
                echo "<tr><td><select name='_cf7frap_'>";
                echo "<option value=''>Select Field</option>";
                foreach ($pages as $key => $value) {
                    if($cf7frap==esc_url( get_permalink( $value->ID ) )){$selected='selected=selected';}else{$selected = "";}
                    echo "<option ".$selected." value='".esc_url( get_permalink( $value->ID ) )."'>".$value->post_title."</option>";
                }
                echo "</select>";
                echo "</td></tr>";

                echo "<tr style='display:none;'><td>Selected Page Redirect after Auto Login : (If not select any Page default Redirect on Home Page)</td></tr>";
                echo "<tr style='display:none;'><td><select name='_cf7frlpr_'>";
                echo "<option value=''>Select Field</option>";
                foreach ($pages as $key => $value) {
                    if($cf7frlpr==esc_url( get_permalink( $value->ID ) )){$selected='selected=selected';}else{$selected = "";}
                    echo "<option ".$selected." value='".esc_url( get_permalink( $value->ID ) )."'>".$value->post_title."</option>";
                }
                echo "</select>";
                echo "</td></tr>";
                
                echo "<tr><td><h2>Email Settings :</h2></td></tr>";
                echo "<tr><td>Use this shortcode for Mail content : [site-name] &nbsp; [login-link] &nbsp; [login-user] &nbsp; [login-user-name] &nbsp; [login-email] &nbsp; [login-password] &nbsp; [login-role] &nbsp; [activation-link]</td></tr>";
                echo "<tr><td>Add Email From Here :</td></tr>";
                echo "<tr><td><input type='email' name='_cf7frfrom_' value='$_cf7frfrom_' size='50' /></td></tr>";
                echo "<tr><td style='color:red;'>Enter From Email with your Site Domain Ex: info@sitedomain.com</td></tr>";
                echo "<tr><td>&nbsp;</td></tr>";
                echo "<tr><td>Add Email Subject Here:</td></tr>";
                echo "<tr><td><input type='text' name='_cf7frsub_' value='$_cf7frsub_' size='50' /></td></tr>";
                echo "<tr><td>Add Email Body Content Here :</td></tr>";
                echo "<tr><td>";
                echo "<textarea name='_cf7freb_' cols='70' rows='10'>$_cf7freb_</textarea>";
                echo "</td></tr>";
                echo "<tr><td>";
                echo "<input type='hidden' name='email' value='2'>";
                echo "<input type='hidden' name='post' value='$post_id'>";
                echo "</td></tr></table>";
            echo "</div>";
        echo "</div>";
    echo "</div>";
echo "</div>";
?>