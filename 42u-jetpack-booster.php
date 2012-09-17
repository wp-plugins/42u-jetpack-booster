<?php
/*
Plugin Name: 42U Jetpack Booster
Plugin URI: http://www.42umbrellas.com/42u-jetpack-booster/
Description: The 42U Jetpack Booster adds redirect tags and HTML email templates to Jetpack Contact Forms
Author: Rick Bush | 42U
Author URI: http://www.42umbrellas.com/author/rick/
Version: 1.0
License: GPLv2 or later

Copyright (c) 2012 42Umbrellas (http://www.42umbrellas.com)

BY USING THIS SOFTWARE, YOU AGREE TO THE TERMS OF THE PLUGIN LICENSE AGREEMENT. 
IF YOU DO NOT AGREE TO THESE TERMS, DO NOT USE THE SOFTWARE.

*/

include_once('inc/FTU.php');

global $JETTPACKBOOSTER_REDIRECT;

class _42UJETPACK_BOOSTER {

    public $options;
    public $disabled_options;
    public $has_jetpack;

    public function __construct(){
    
        // check for jetpack or display alert on options page
        /* load all our options and set defaults */
        
        $this->options = get_option('ftu_jetpack_booster_options');
        
        $this->options = ($this->options) ? $this->options : array();
        
        $this->has_jetpack = false;
                                            
        /* this assumes we do not want any empty options */
        $this->options = FTU::cleanOptions($this->options);
                
        $this->options = array_merge(
				// Set Defaults
				array(
					'email_template' 			=> _x( '
<body>
    <div id="email_container">
        <div style="width:550px; padding:0 20px 20px 20px; background:#fff; margin:0 auto; border:3px #000 solid;
            moz-border-radius:5px; -webkit-border-radius:5px; border-radius:5px; color:#454545;line-height:1.5em; " id="email_content">
            
            <h1 style="padding:5px 0 0 0; font-family:georgia;font-weight:500;font-size:24px;color:#000;">
                !!!EMAIL_SUBJECT!!!
            </h1>
            
            !!!EMAIL_BODY!!!
                    
            <div style="text-align:center; border-top:1px solid #eee;padding:5px 0 0 0;" id="email_footer"> 
                <small style="font-size:11px; color:#999; line-height:14px;">
                    !!!EMAIL_FOOTER_LINKS!!!
                </small>
            </div>
            
        </div>
    </div>
</body>', 'default email template' ),
				),
				// set options
				$this->options
			);
						        
        /* actions */
        add_action( 'admin_head', array($this,'custom_post_type_icon'));
        
        /* look for Jetpack, we can hook into it */
        add_action( 'admin_init', array($this,'dependentplugin_activate'));
        
        /* actions */
        add_action( 'admin_head', array($this,'custom_post_type_icon'));
        
        /* queue frontend scripts */
		add_action('wp_enqueue_scripts', array($this,'script_init_42U'));
		
		/* queue admin scripts */
		add_action('admin_enqueue_scripts', array($this,'admin_script_init_42U'));
        add_action('admin_menu', array($this,'register_42U_page'));  
        
        /* set up our redirect */
        add_action('wp_footer', array($this, 'contact_form_redirect'),100);
        
        /* filters */
        
        /* filter to make our mail from jetpack pretty */
		add_filter('wp_mail', array($this,'wp_mail_hook'));
		
    }
    
    public function register_42U_page() {
        
        /* set up this 42U plugin admin page*/
        
        // queue backend scripts
        add_action( 'admin_init', array($this,'script_init_42U') );
        
        /* create nav and load page styles */
        $page = add_submenu_page('42-Umbrellas', '42U Jetpack Booster Options','Jetpack Booster Options','administrator',__FILE__, array($this,'booster_opts_42U'));
        
        /* register options */
        register_setting('ftu_jetpack_booster_options','ftu_jetpack_booster_options'); // 3rd param = optional callback
        add_settings_section('ftu_jetpack_booster_main_section','Jetpack Booster Settings',array($this,'ftu_jetpack_booster_main_section_cb'),__FILE__);
        add_settings_field('disable_HTMLemail',"Disable HTML Templates in Jetpack, I'd rather use plain text",array($this,'disable_HTMLemail_setting'),__FILE__,'ftu_jetpack_booster_main_section');
        /*
        add_settings_field('disable_modernizr',"Skip Modernizr, I don't need it!",array($this,'disable_modernizr_setting'),__FILE__,'ftu_jetpack_booster_main_section');
        */
        
    }
    
    public function script_init_42U() {
        /* Register our scripts. */
        
    }
    
    public function admin_script_init_42U() {
        wp_register_style( 'jetpack_boosterStylesheet', plugins_url('css/style.css', __FILE__) );
        wp_enqueue_style( 'jetpack_boosterStylesheet' );
    }
    
    public function booster_opts_42U() {
        ?> 
        
        <div class="wrap">
            <div id="jetpack_booster" class="icon32 icon32-posts-jetpack-booster"></div>
            <h2>Jetpack Booster Options</h2>
            <?php
                if (isset($_GET['settings-updated'])) { 
                    FTU::show_42U_message('<strong>Updated</strong>');
                } 
            ?>
             <p>
                The 42U Jetpack Booster adds redirect tags and HTML email templates to Jetpack
             </p>
             
             <h3>
                Setting a redirect.
            </h3>
            
            <table class="form-table">
                <tbody>
                    <tr valign="top">
                        <th scope="row">Add this line to the HTML view of your form. <br/>
                                        Set the <em>default</em> value to the location where you would like the form to redirect after submission.
                        </th>
                        <td>
                                <p>[contact&ndash;field label="redirect" type="hidden" default="/thanks/" /]</p>
                        </td>
                    </tr>
                </tbody>
            </table>
             
            <form method="POST" action="options.php" enctype="multipart/form-data">
                
                <?php settings_fields('ftu_jetpack_booster_options'); ?>
                <?php do_settings_sections(__FILE__); ?>
                
                <h3>
                Your Email Template
                </h3>
                
                <table class="form-table">
                    <tbody>
                        <tr valign="top">
                            <th scope="row">You can use these variables in your template</th>
                            <td>
                                    <p>Subject: !!!EMAIL_SUBJECT!!!</p>
                                    <p>Body: !!!EMAIL_BODY!!!</p>
                                    <p>Footer Links: !!!EMAIL_FOOTER_LINKS!!!</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                
                <?php wp_editor( $this->options['email_template'], '42Uemailtemplate', $settings = array('textarea_name'=>'ftu_jetpack_booster_options[email_template]') ); ?> 
                
                <p class="submit">
                    <input type="submit" name="submit" class="button-primary" value="Save Changes" />
                </p>
            </form>
                
        </div>
        
        <?php
    }
    
    public function ftu_jetpack_booster_main_section_cb() {
    
    }
    
    public function disable_HTMLemail_setting() {
        $checked = ( $this->options['disable_HTMLemail'] == '1' ) ? 'checked' : '';
        echo "<input type='checkbox' name='ftu_jetpack_booster_options[disable_HTMLemail]' value='1' $checked />";
    }
    
    public function dependentplugin_activate() {
        
        /* check for jetpack on every admin page load */
        $jetpack = new Theme_Plugin_Dependency( 'jetpack', 'http://wordpress.org/extend/plugins/jetpack/' );
        if ( $jetpack->check_active() ) {
            $this->has_jetpack = true;
        }
        
        if ( $_GET['page'] == '42u-jetpack-booster/42u-jetpack-booster.php' && current_user_can( 'install_plugins' ) ) {          
            if ( ! $jetpack->check_active() ) {
                $jetpack_msg = "What, no Jetpack? Jetpack greatly enhances the display of user profiles and managing feedback, along with a host of other cool features. It's free, you should try it out. This plugin won't do much without it.";
                if ( $jetpack->check() ) {
                    $jetpack_msg .= '<br><a href="'.$jetpack->activate_link().'" class="st_btn">Click here to activate it!</a>';
                } else if ( $install_link = $jetpack->install_link() ) {
                    $jetpack_msg .= '<br><a href="'.$install_link.'" class="st_btn">Click here to install it!</a>';
                }
                $jetpack_obj = new FTU_Admin_Notices( 'jetpack', $jetpack_msg, 'updated', true);
            }
        }
        
    }

    public function custom_post_type_icon() {
    
        FTU::set_post_type_icon($image_urls=array('plugin'=>'jetpack-booster',
                                                                'admin-image'=>'images/jetpack_booster_adminmenu16-sprite.png',
                                                                'posts-image'=>'images/jetpack_booster_adminpage32.png',
                                                                'admin-imageX2'=>'images/jetpack_booster_adminmenu16-sprite_2x.png',
                                                                'posts-imageX2'=>'images/jetpack_booster_adminpage32_2x.png',
                                                                'file' => __FILE__
                                                                ));
        
    }
    
    public function mail_content_type() {
        return "text/html";
    }
    
    public function contact_form_redirect() {
        global $JETTPACKBOOSTER_REDIRECT;
        if ($JETTPACKBOOSTER_REDIRECT) {
            echo("<script>window.location='$JETTPACKBOOSTER_REDIRECT';</script>");
        }
    }
    
    public function wp_mail_hook($data) {
    
        /* we only want to do this if JetPack is installed and we have contact form fields */
        
        global $post;
        
        global $contact_form_fields, $grunion_form, $JETTPACKBOOSTER_REDIRECT;
        
        $jetpack = new Theme_Plugin_Dependency( 'jetpack', 'http://wordpress.org/extend/plugins/jetpack/' );
        if ( $jetpack->check_active() ) {
            $this->has_jetpack = true;
        }
        
        if( $this->has_jetpack && $contact_form_fields) {
            
            foreach ($contact_form_fields as $v) {
                $match = preg_match('/redirect/', $v['id']);
                if ($match === 1) {
                     $JETTPACKBOOSTER_REDIRECT = $v['default'];
                }
            }

            if ($this->options['disable_HTMLemail']) {
                
                $pattern = '/redirect:.*?\n/i';
                $data["message"] = preg_replace($pattern, '', $data["message"]);
                
            } else {
    
                add_filter("wp_mail_content_type", array($this,'mail_content_type'));  
                
                $subject = $data["subject"];  
                
                $pattern = '/redirect:.*?\n/i';
                $message = nl2br(preg_replace($pattern, '', $data["message"]));
                
                $template = $this->options['email_template'];
                
                $footer_links = "You have received this email because you are a contact at " . get_bloginfo('name') . ".<br/><a href='" . network_site_url( '/wp-admin/' ) . "'>Log in</a> to change your preferences.";
                $template = preg_replace('/!!!EMAIL_BODY!!!/',$message,$template);
                $template = preg_replace('/!!!EMAIL_SUBJECT!!!/',$subject,$template);
                $template = preg_replace('/!!!EMAIL_FOOTER_LINKS!!!/',$footer_links,$template);
                
                $data["message"] = $template;
            
            }
        
        }
        
        return $data;
            
    }

}

$jetpack_booster = new _42UJETPACK_BOOSTER();
