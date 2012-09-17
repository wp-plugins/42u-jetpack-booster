<?php 
/*
Description: 42U Class.
Author: Rick Bush | 42U
Author URI: http://www.42umbrellas.com/author/rick/
Version: 1.0
License: GPLv2 or later

Copyright (c) 2012 42Umbrellas (http://www.42umbrellas.com)

BY USING THIS SOFTWARE, YOU AGREE TO THE TERMS OF THE PLUGIN LICENSE AGREEMENT. 
IF YOU DO NOT AGREE TO THESE TERMS, DO NOT USE THE SOFTWARE.

*/

global $this_FTU_version;
global $wp_version;
$this_FTU_version = "1.0";

/* need to add in version checking to make sure we load the most recent version */

/* Helper Class for admin notices */
if (!class_exists('FTU_Admin_Notices')) {
    class FTU_Admin_Notices {
    
        // input information
		var $id;
		var $msg;
		var $class;
		var $hide_hide;
		
        public function __construct($id,$msg,$class,$hide_hide = false,$filter = ''){
            $this->id = $id;
            $this->msg = $msg;
			$this->class = $class;
			$this->hide_hide = $hide_hide;
            /* Display a notice that can be dismissed */
            add_action('admin_notices', array($this,'hmg_admin_notice'));
            add_action('admin_init', array($this,'hmg_nag_ignore'));
            if ($filter) {
                return add_filter($filter, array($this,'hmg_admin_notice') ); 
            }
        }
        
        public function hmg_admin_notice() {
            global $current_user ;
                $user_id = $current_user->ID;
                /* Check that the user hasn't already clicked to ignore the message */
            if ( ! get_user_meta($user_id, $this->id . '_ignore_notice') ) {
                echo "<div class='" . $this->class . "'><p>";
                if ($this->hide_hide) {
                    echo $this->msg;
                } else {
                    parse_str($_SERVER['QUERY_STRING'], $params);
                    printf(__($this->msg . ' | <a href="%1$s">Hide Notice</a>'), '?' . http_build_query(array_merge($params, array($this->id . '_ignore_notice'=>'0'))));
                }
                echo "</p></div>";
            }
        }
        
        public function hmg_nag_ignore() {
            global $current_user;
                $user_id = $current_user->ID;
                /* If user clicks to ignore the notice, add that to their user meta */
                if ( isset($_GET[$this->id . '_ignore_notice']) && '0' == $_GET[$this->id . '_ignore_notice'] ) {
                     add_user_meta($user_id, $this->id . '_ignore_notice', 'true', true);
            }
        }
    }
}

if ( defined(FTU_VERSION) && version_compare(FTU_VERSION, $this_FTU_version, '<') ) {
    $FTU_version_obj = new FTU_Admin_Notices( 'ftuversion', 'Oh no, you have an older version of the core FTU library installed. To fix this, please update all your 42U plugins to the latest version.', 'error', true);
}

if (!class_exists("FTU") || ( version_compare(FTU_VERSION, $this_FTU_version, '<') && version_compare(PHP_VERSION, '5.3', '>=') ) ) {
   
    define( 'FTU_VERSION', '1.0' );
    define( 'FTU_REQUIRED_WP_VERSION', '3.2' );
    define( 'FTU_REQUIRED_PHP_VERSION', '5.2' );
    
    /* 
    if (version_compare(PHP_VERSION, '5.3', '>=') && version_compare(FTU_VERSION, $this_FTU_version, '<') ) {
        # should be able to reclass this using a namespace
        namespace FTUnamespace10;
    }
    */
    
    if ( version_compare($wp_version, FTU_REQUIRED_WP_VERSION, '<') ) {
        $php_version_obj = new FTU_Admin_Notices( 'wpversion', 'Oh no, 42U plugins need at least WordPress version ' . FTU_REQUIRED_WP_VERSION . ' Some functions probably won\'t work.', 'error');
    }
    
    if ( version_compare(PHP_VERSION, FTU_REQUIRED_PHP_VERSION, '<') ) {
        $php_version_obj = new FTU_Admin_Notices( 'phpversion', 'Oh no, 42U plugins need at least PHP version ' . FTU_REQUIRED_PHP_VERSION . ' Some functions probably won\'t work.', 'error');
    }

    class FTU {

        public function __construct(){
            /* Custom Post Type Icon for Admin Menu & Post Screen */
            add_action( 'admin_head', array($this,'set_post_type_icon_cb'));
            add_action('admin_menu', array($this,'register_42U_mainpage'));
            
        }
        
        public function register_42U_mainpage() {
            $main_page = add_menu_page('42 Umbrellas', '42 Umbrellas', 'administrator', '42-Umbrellas', array($this,'fortytwou_init'),'div');//plugins_url('myplugin/images/icon.png')
            wp_register_style( 'ftUStylesheet', plugins_url('../css/style.css', __FILE__) );
            add_action( 'admin_print_styles-' . $main_page, array($this, 'add_42U_stylesheet') );
        }
          
        public function fortytwou_init() {
            include_once('splash.php');
        }
        
        public function add_42U_stylesheet() {
            wp_enqueue_style( 'ftUStylesheet' );
        }
        
        public function show_42U_message($msg,$class) {
            $class = (empty($class)) ? 'updated' : $class;
            ?>
            <div class="<?php echo $class ?>">
                <p>
                <?php echo $msg ?>
                </p>
            </div>
            <?php 
        }
        
        public function cleanOptions($arr) {
            return array_filter($arr);
        }
        
        public function smartCopy($source, $dest, $options=array('folderPermission'=>0755,'filePermission'=>0755)) {
            
            $result=false;
           
            if (is_file($source)) {
                if ($dest[strlen($dest)-1]=='/') {
                    if (!file_exists($dest)) {
                        cmfcDirectory::makeAll($dest,$options['folderPermission'],true);
                    }
                    $__dest=$dest."/".basename($source);
                } else {
                    $__dest=$dest;
                }
                $result=copy($source, $__dest);
                chmod($__dest,$options['filePermission']);
               
            } elseif(is_dir($source)) {
                if ($dest[strlen($dest)-1]=='/') {
                    if ($source[strlen($source)-1]=='/') {
                        //Copy only contents
                    } else {
                        //Change parent itself and its contents
                        $dest=$dest.basename($source);
                        @mkdir($dest);
                        chmod($dest,$options['filePermission']);
                    }
                } else {
                    if ($source[strlen($source)-1]=='/') {
                        //Copy parent directory with new name and all its content
                        @mkdir($dest,$options['folderPermission']);
                        chmod($dest,$options['filePermission']);
                    } else {
                        //Copy parent directory with new name and all its content
                        @mkdir($dest,$options['folderPermission']);
                        chmod($dest,$options['filePermission']);
                    }
                }
    
                $dirHandle=opendir($source);
                while($file=readdir($dirHandle)) {
                
                    if($file!="." && $file!="..") {
                         if(!is_dir($source."/".$file)) {
                            $__dest=$dest."/".$file;
                        } else {
                            $__dest=$dest."/".$file;
                        }
                        //echo "$source/$file ||| $__dest<br />";
                        $result=FTU::smartCopy($source."/".$file, $__dest, $options);
                    }
                }
                closedir($dirHandle);
               
            } else {
                $result=false;
            }
            return $result;
        } 
        
        public function license($key,$product) {
            
            $license = get_option("_hmg_license");
            $hash = md5($key . $product);
            $validity = 0;
            $owner = '';
            $url = "http://admin.haleymarketing.com/json/?k=$key&p=$product&pid=gwt&arg=validate_license";
            $whetherUpdateLicense = false;
            
            if (!($key && $product)) {
                return array("last_update"=>time(),"validity"=>false,"owner"=>$owner);
            }
            
            if (isset($license[$hash])){
               $timeOfLastFetch = intval(@$license[$hash]["last_update"]);
               if (time()-$timeOfLastFetch > 500) { //update the license every 500 minutes
                    $whetherUpdateLicense = true;
               } else {
                     if ($license[$hash]['validity'] == true) {
                        return $license[$hash];
                     } else {
                        $whetherUpdateLicense = true; 
                    }
               }
               
            } else {
               $whetherUpdateLicense = true;             
            }     
            
            if ($whetherUpdateLicense) {
                        
               if ($result = file_get_contents($url)) {
               
                   $result = json_decode($result);
                   $validity = intval($result->ResultSet->validity) ? true : false;
                   $owner = $result->ResultSet->owner;
                                      
               }
               $license = get_option("_hmg_license");
               $license[$hash] = array("last_update"=>time(),"validity"=>$validity,"owner"=>$owner);
               update_option("_hmg_license",$license);
            }
            
            return $license[$hash];
            
        }
        
        public function set_post_type_icon_cb() {
    
            FTU::set_post_type_icon($image_urls=array('plugin'=>'42-Umbrellas',
                                                                'admin-image'=>'images/42U_adminmenu16-sprite.png',
                                                                'posts-image'=>'images/42U_adminpage32.png',
                                                                'admin-imageX2'=>'images/42U_adminmenu16-sprite_2x.png',
                                                                'posts-imageX2'=>'images/42U_adminpage32_2x.png',
                                                                'file' => __FILE__
                                                                ));
            
        }
        
        public function set_post_type_icon($image_urls=array('plugin'=>'42-Umbrellas',
                                                                'admin-image'=>'images/42U_adminmenu16-sprite.png',
                                                                'posts-image'=>'images/42U_adminpage32.png',
                                                                'admin-imageX2'=>'images/42U_adminmenu16-sprite_2x.png',
                                                                'posts-imageX2'=>'images/42U_adminpage32_2x.png',
                                                                'file' => __FILE__
                                                                )) {
    ?>
            <style>
                /* Admin Menu - 16px */
                #toplevel_page_<?php echo $image_urls['plugin']?> .wp-menu-image {
                    background: url(<?php echo plugins_url($image_urls['admin-image'], $image_urls['file']) ?>) no-repeat 6px 6px !important;
                }
                #toplevel_page_<?php echo $image_urls['plugin']?>:hover .wp-menu-image, #menu-posts-<?php echo $image_urls['plugin']?>.wp-has-current-submenu .wp-menu-image {
                    background-position: 6px -26px !important;
                }
                /* Post Screen - 32px */
                .icon32-posts-<?php echo $image_urls['plugin']?> {
                    background: url(<?php echo plugins_url($image_urls['posts-image'], $image_urls['file']) ?>) no-repeat left top !important;
                }
                @media
                only screen and (-webkit-min-device-pixel-ratio: 1.5),
                only screen and (   min--moz-device-pixel-ratio: 1.5),
                only screen and (     -o-min-device-pixel-ratio: 3/2),
                only screen and (        min-device-pixel-ratio: 1.5),
                only screen and (                min-resolution: 1.5dppx) {
                     
                    /* Admin Menu - 16px @2x */
                    #toplevel_page_<?php echo $image_urls['plugin']?> .wp-menu-image {
                        background-image: url(<?php echo plugins_url($image_urls['admin-imageX2'], $image_urls['file']) ?>) !important;
                        -webkit-background-size: 16px 48px;
                        -moz-background-size: 16px 48px;
                        background-size: 16px 48px;
                    }
                    /* Post Screen - 32px @2x */
                    .icon32-posts-<?php echo $image_urls['plugin']?> {
                        background-image: url(<?php echo plugins_url($image_urls['posts-imageX2'], $image_urls['file']) ?>) !important;
                        -webkit-background-size: 32px 32px;
                        -moz-background-size: 32px 32px;
                        background-size: 32px 32px;
                    }        
                }
            </style>
    <?php 
    
        }
      
    }
    
    /*
    if (version_compare(PHP_VERSION, '5.3', '>=') && version_compare(FTU_VERSION, $this_FTU_version, '<') ) {
        # should be able to reclass this using a namespace
        use FTUnamespace10\FTU as FTU;
    }
    */
    
    $fortytwou = new FTU();
    
}

/* Helper Class for version checking */

/* Helper Class for options frame work */

/* Helper Class for checking for plugin dependencies */
if (!class_exists('Theme_Plugin_Dependency')) {
    // we need this to enable plugin checks outside of admin
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
	class Theme_Plugin_Dependency {
		// input information from the theme
		var $slug;
		var $uri;

		// installed plugins and uris of them
		private $plugins; // holds the list of plugins and their info
		private $uris; // holds just the URIs for quick and easy searching

		// both slug and PluginURI are required for checking things
		function __construct( $slug, $uri ) {
			$this->slug = $slug;
			$this->uri = $uri;
			if ( empty( $this->plugins ) ) 
				$this->plugins = get_plugins();
			if ( empty( $this->uris ) ) 
				$this->uris = wp_list_pluck($this->plugins, 'PluginURI');
		}

		// return true if installed, false if not
		function check() {
			return in_array($this->uri, $this->uris);
		}

		// return true if installed and activated, false if not
		function check_active() {
			$plugin_file = $this->get_plugin_file();
			if ($plugin_file) return is_plugin_active($plugin_file);
			return false;
		}

		// gives a link to activate the plugin
		function activate_link() {
			$plugin_file = $this->get_plugin_file();
			if ($plugin_file) return wp_nonce_url(self_admin_url('plugins.php?action=activate&plugin='.$plugin_file), 'activate-plugin_'.$plugin_file);
			return false;
		}

		// return a nonced installation link for the plugin. checks wordpress.org to make sure it's there first.
		function install_link() {
			include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

			$info = plugins_api('plugin_information', array('slug' => $this->slug ));

			if ( is_wp_error( $info ) ) 
				return false; // plugin not available from wordpress.org

			return wp_nonce_url(self_admin_url('update.php?action=install-plugin&plugin=' . $this->slug), 'install-plugin_' . $this->slug);
		}

		// return array key of plugin if installed, false if not, private because this isn't needed for themes, generally
		private function get_plugin_file() {
			return array_search($this->uri, $this->uris);
		}
	}
}

/* Helper Class for admin notices */
if (!class_exists('FTU_Admin_Notices')) {
    class FTU_Admin_Notices {
    
        // input information
		var $id;
		var $msg;
		var $class;
		var $hide_hide;
		
        public function __construct($id,$msg,$class,$hide_hide,$filter = ''){
            $this->id = $id;
            $this->msg = $msg;
			$this->class = $class;
			$this->hide_hide = $hide_hide;
            /* Display a notice that can be dismissed */
            add_action('admin_notices', array($this,'hmg_admin_notice'));
            add_action('admin_init', array($this,'hmg_nag_ignore'));
            if ($filter) {
                return add_filter($filter, array($this,'hmg_admin_notice') ); 
            }
        }
        
        public function hmg_admin_notice() {
            global $current_user ;
                $user_id = $current_user->ID;
                /* Check that the user hasn't already clicked to ignore the message */
            if ( ! get_user_meta($user_id, $this->id . '_ignore_notice') ) {
                echo "<div class='" . $this->class . "'><p>";
                if ($this->hide_hide) {
                    echo $this->msg;
                } else {
                    parse_str($_SERVER['QUERY_STRING'], $params);
                    printf(__($this->msg . ' | <a href="%1$s">Hide Notice</a>'), '?' . http_build_query(array_merge($params, array($this->id . '_ignore_notice'=>'0'))));
                }
                echo "</p></div>";
            }
        }
        
        public function hmg_nag_ignore() {
            global $current_user;
                $user_id = $current_user->ID;
                /* If user clicks to ignore the notice, add that to their user meta */
                if ( isset($_GET[$this->id . '_ignore_notice']) && '0' == $_GET[$this->id . '_ignore_notice'] ) {
                     add_user_meta($user_id, $this->id . '_ignore_notice', 'true', true);
            }
        }
    }
}

/* Helper Class for Taxonomy Widgets */
if (!class_exists('WP_Widget_Taxonomy_Terms')) {
    class WP_Widget_Taxonomy_Terms extends WP_Widget {
     
      function WP_Widget_Taxonomy_Terms() {
        $widget_ops = array( 'classname' => 'widget_taxonomy_terms' , 'description' => __( "A list, dropdown, or cloud of taxonomy terms" ) );
        $this->WP_Widget( 'taxonomy_terms' , __( 'Taxonomy Terms' ) , $widget_ops );
      }
     
      function widget( $args , $instance ) {
        extract( $args );
     
        $current_taxonomy = $this->_get_current_taxonomy( $instance );
        $tax = get_taxonomy( $current_taxonomy );
        if ( !empty( $instance['title'] ) ) {
          $title = $instance['title'];
        } else {
          $title = $tax->labels->name;
        }
     
        global $t;
        $t = $instance['taxonomy'];
        $f = $instance['format'];
        $c = $instance['count'] ? '1' : '0';
        $h = $instance['hierarchical'] ? '1' : '0';
     
        $w = $args['widget_id'];
        $w = 'ttw' . str_replace( 'taxonomy_terms-' , '' , $w );
     
        echo $before_widget;
        if ( $title )
          echo $before_title . $title . $after_title;
     
        $tax_args = array( 'orderby' => 'name' , 'show_count' => $c , 'hierarchical' => $h , 'taxonomy' => $t );
     
        if ( $f == 'dropdown' ) {
          $tax_args['show_option_none'] = __( 'Select ' . $tax->labels->singular_name );
          $tax_args['name'] = __( $w );
          $tax_args['echo'] = false;
          $my_dropdown_categories = wp_dropdown_categories( apply_filters( 'widget_categories_dropdown_args' , $tax_args ) );
     
          $my_get_term_link = create_function( '$matches' , 'global $t; return "value=\"" . get_term_link( (int) $matches[1] , $t ) . "\"";' );
          echo preg_replace_callback( '#value="(\\d+)"#' , $my_get_term_link , $my_dropdown_categories );
     
    ?>
    <script type='text/javascript'>
    /* <![CDATA[ */
      var dropdown<?php echo $w; ?> = document.getElementById("<?php echo $w; ?>");
      function on<?php echo $w; ?>change() {
        if ( dropdown<?php echo $w; ?>.options[dropdown<?php echo $w; ?>.selectedIndex].value != '-1' ) {
          location.href = dropdown<?php echo $w; ?>.options[dropdown<?php echo $w; ?>.selectedIndex].value;
        }
      }
      dropdown<?php echo $w; ?>.onchange = on<?php echo $w; ?>change;
    /* ]]> */
    </script>
    <?php
     
        } elseif ( $f == 'list' ) {
     
    ?>
        <ul>
    <?php
     
        $tax_args['title_li'] = '';
        wp_list_categories( apply_filters( 'widget_categories_args' , $tax_args ) );
     
    ?>
        </ul>
    <?php
     
        } else {
     
    ?>
        <div>
    <?php
     
          wp_tag_cloud( apply_filters( 'widget_tag_cloud_args' , array( 'taxonomy' => $t ) ) );
     
    ?>
        </div>
    <?php
     
        }
        echo $after_widget;
      }
     
      function update( $new_instance, $old_instance ) {
        $instance = $old_instance;
        $instance['title'] = strip_tags( $new_instance['title'] );
        $instance['taxonomy'] = stripslashes( $new_instance['taxonomy'] );
        $instance['format'] = stripslashes( $new_instance['format'] );
        $instance['count'] = !empty( $new_instance['count'] ) ? 1 : 0;
        $instance['hierarchical'] = !empty( $new_instance['hierarchical'] ) ? 1 : 0;
     
        return $instance;
      }
     
      function form( $instance ) {
        //Defaults
        $instance = wp_parse_args( (array) $instance , array( 'title' => '' ) );
        $current_taxonomy = $this->_get_current_taxonomy( $instance );
        $current_format = esc_attr( $instance['format'] );
        $title = esc_attr( $instance['title'] );
        $count = isset( $instance['count'] ) ? (bool) $instance['count'] : false;
        $hierarchical = isset( $instance['hierarchical'] ) ? (bool) $instance['hierarchical'] : false;
     
    ?>
        <p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
        <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo $title; ?>" /></p>
     
        <p><label for="<?php echo $this->get_field_id( 'taxonomy' ); ?>"><?php _e( 'Taxonomy:' ); ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id( 'taxonomy' ); ?>" name="<?php echo $this->get_field_name( 'taxonomy' ); ?>">
    <?php
     
        $args = array(
          'public' => true ,
          '_builtin' => false
        );
        $output = 'names';
        $operator = 'and';
     
        $taxonomies = get_taxonomies( $args , $output , $operator );
        $taxonomies = array_merge( $taxonomies, array( 'category' , 'post_tag' ) );
        foreach ( $taxonomies as $taxonomy ) {
          $tax = get_taxonomy( $taxonomy );
          if ( empty( $tax->labels->name ) )
            continue;
    ?>
        <option value="<?php echo esc_attr( $taxonomy ); ?>" <?php selected( $taxonomy , $current_taxonomy ); ?>><?php echo $tax->labels->name; ?></option>
    <?php
     
        }
     
    ?>
        </select></p>
     
        <p><label for="<?php echo $this->get_field_id( 'format' ); ?>"><?php _e( 'Format:' ) ?></label>
        <select class="widefat" id="<?php echo $this->get_field_id( 'format' ); ?>" name="<?php echo $this->get_field_name( 'format' ); ?>">
    <?php
     
        $formats = array( 'list' , 'dropdown' , 'cloud' );
        foreach( $formats as $format ) {
     
    ?>
        <option value="<?php echo esc_attr( $format ); ?>" <?php selected( $format , $current_format ); ?>><?php echo ucfirst( $format ); ?></option>
    <?php
     
        }
     
    ?>
        </select></p>
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'count' ); ?>" name="<?php echo $this->get_field_name( 'count' ); ?>"<?php checked( $count ); ?> />
        <label for="<?php echo $this->get_field_id( 'count' ); ?>"><?php _e( 'Show post counts' ); ?></label><br />
     
        <input type="checkbox" class="checkbox" id="<?php echo $this->get_field_id( 'hierarchical' ); ?>" name="<?php echo $this->get_field_name( 'hierarchical' ); ?>"<?php checked( $hierarchical ); ?> />
        <label for="<?php echo $this->get_field_id( 'hierarchical' ); ?>"><?php _e( 'Show hierarchy' ); ?></label></p>
    <?php
     
      }
     
      function _get_current_taxonomy( $instance ) {
        if ( !empty( $instance['taxonomy'] ) && taxonomy_exists( $instance['taxonomy'] ) )
          return $instance['taxonomy'];
        else
          return 'category';
      }
    }
}

/* Helper Class for Custom Post Types */
if (!class_exists('Custom_Post_Type_42U')) {
    class Custom_Post_Type_42U {
    
            public $post_type_name;
            public $post_type_args;
            public $post_type_labels;
            public $validations;
            
            /* Class constructor */
            public function __construct( $id, $name, $plural, $args = array(), $labels = array() ) {
                // Set some important variables
                $this->post_type_id         = strtolower( str_replace( ' ', '_', $id ) );
                $this->post_type_name		= strtolower( str_replace( ' ', '_', $name ) );
                $this->post_type_plural		= $plural;
                $this->post_type_args 		= $args;
                $this->post_type_labels 	= $labels;
    
                // Add action to register the post type, if the post type doesnt exist
                if( ! post_type_exists( $this->post_type_id ) ) {
                    add_action( 'init', array($this,'cpt_register_post_type') );
                }
    
                // Listen for the save post hook
                $this->save();
            }
            
            /* Method which registers the post type */
            public function cpt_register_post_type() {		
                        
                //Capitilize the words and make it plural
                $name 		= ucwords( str_replace( '_', ' ', $this->post_type_name ) );
                $plural 	= ($this->post_type_plural) ? $this->post_type_plural : $name . 's';
    
                // We set the default labels based on the post type name and plural. We overwrite them with the given labels.
                $labels = array_merge(
    
                    // Default
                    array(
                        'name' 					=> _x( $name, 'post type general name' ),
                        'singular_name' 		=> _x( $name, 'post type singular name' ),
                        'add_new' 				=> _x( 'Add New', strtolower( $name ) ),
                        'add_new_item' 			=> __( 'Add New ' . $name ),
                        'edit_item' 			=> __( 'Edit ' . $name ),
                        'new_item' 				=> __( 'New ' . $name ),
                        'all_items' 			=> __( 'All ' . $plural ),
                        'view_item' 			=> __( 'View ' . $name ),
                        'search_items' 			=> __( 'Search ' . $plural ),
                        'not_found' 			=> __( 'No ' . strtolower( $plural ) . ' found'),
                        'not_found_in_trash' 	=> __( 'No ' . strtolower( $plural ) . ' found in Trash'), 
                        'parent_item_colon' 	=> '',
                        'menu_name' 			=> $plural
                    ),
    
                    // Given labels
                    $this->post_type_labels
    
                );
    
                // Same principle as the labels. We set some default and overwite them with the given arguments.
                $args = array_merge(
    
                    // Default
                    
                    array(
                        'labels' => $labels,
                        'public' => true,
                        'publicly_queryable' => true,
                        'show_ui' => true,
                        'query_var' => true,		
                        'rewrite' => true,
                        'capability_type' => 'post',
                        'hierarchical' => false,
                        'menu_position' => null,
                        'has_archive' => true,
                        'supports' => array('title', 'editor', 'thumbnail', 'comments', 'revisions', 'excerpt')
                    ),
    
                    // Given args
                    $this->post_type_args
    
                );
    
                // Register the post type
                register_post_type( $this->post_type_id, $args );
                flush_rewrite_rules();
            }
            
            /* Method to attach the taxonomy to the post type */
            public function add_taxonomy( $id, $name, $plural, $args = array(), $labels = array() ) {
                if( ! empty( $id ) && ! empty( $name) ) {			
                    // We need to know the post type name, so the new taxonomy can be attached to it.
                    $post_type_id = $this->post_type_id;
                    
                    // Taxonomy properties
                    $taxonomy_name		= strtolower( str_replace( ' ', '_', $id ) );
                    $taxonomy_labels	= $labels;
                    $taxonomy_args		= $args;
    
                    if( ! taxonomy_exists( $taxonomy_name ) ) {
                        //Capitilize the words and make it plural
                            $name 		= ucwords( str_replace( '_', ' ', $name ) );
                            $plural 	= ($plural) ? $plural : $name . 's';
    
                            // Default labels, overwrite them with the given labels.
                            $labels = array_merge(
    
                                // Default
                                array(
                                    'name' 					=> _x( $plural, 'taxonomy general name' ),
                                    'singular_name' 		=> _x( $name, 'taxonomy singular name' ),
                                    'search_items' 			=> __( 'Search ' . $plural ),
                                    'all_items' 			=> __( 'All ' . $plural ),
                                    'parent_item' 			=> __( 'Parent ' . $name ),
                                    'parent_item_colon' 	=> __( 'Parent ' . $name . ':' ),
                                    'edit_item' 			=> __( 'Edit ' . $name ), 
                                    'update_item' 			=> __( 'Update ' . $name ),
                                    'add_new_item' 			=> __( 'Add New ' . $name ),
                                    'new_item_name' 		=> __( 'New ' . $name . ' Name' ),
                                    'menu_name' 			=> __( $plural ),
                                ),
    
                                // Given labels
                                $taxonomy_labels
    
                            );
    
                            // Default arguments, overwitten with the given arguments
                            $args = array_merge(
    
                                // Default
                                array(
                                    'labels'				=> $labels,
                                    'hierarchical'          => true
                                ),
                                // Given
                                $taxonomy_args
    
                            );
                            
                            register_taxonomy($taxonomy_name, $post_type_id, $args);
                            flush_rewrite_rules();
                            
                    }
                }
            }
            
            /* Attaches meta boxes to the post type */
            public function add_meta_boxes( $id, $title, $fields = array(), $context = 'normal', $priority = 'default' ) {
                if( ! empty( $title ) ) {		
                    // We need to know the Post Type name again
                    $post_type_id = $this->post_type_id;
    
                    // Meta variables	
                    $box_id 		= strtolower( str_replace( ' ', '_', $id ) );
                    $box_title		= ucwords( str_replace( '_', ' ', $title ) );
                    $box_context	= $context;
                    $box_priority	= $priority;
    
                    // Make the fields global
                    global $custom_fields;
                    $custom_fields[$id] = $fields;
                    
                    add_meta_box(
                                    $box_id,
                                    $box_title,
                                    array($this,'add_meta_box_cb'),
                                    $post_type_id,
                                    $box_context,
                                    $box_priority,
                                    array( $fields )
                                );
                    
                }
            }
            
            public function add_meta_box_cb($post, $data) {
                    global $post;
    
                    // Nonce field for some validation
                    wp_nonce_field( plugin_basename( __FILE__ ), 'custom_post_type' );
    
                    // Get all inputs from $data
                    $custom_fields = $data['args'][0];
    
                    // Get the saved values
                    $meta = get_post_custom( $post->ID );
    
                    // Check the array and loop through it
                    if( ! empty( $custom_fields ) ) {
                        /* Loop through $custom_fields */
                        
                        // need to save validation hooks
                        // $this->validations[fieldname] = array($this,'function')
                        
                        foreach( $custom_fields as $label => $atts ) {
                            $type = $atts[0];
                            // not functional yet
                            // $callback = $atts[1];
                            $class='';
                            $err_msg = '';
                            $field_id_name 	= strtolower( str_replace( ' ', '_', $data['id'] ) ) . '_' . strtolower( str_replace( ' ', '_', $label ) );
                            
                            if ($field_id_name == 'talent_details_owner') {
                                
                                // do a test to make sure the user exists in this blog
                                $args = array(
                                                'blog_id' => $GLOBALS['blog_id'],
                                                'search'  => $meta[$field_id_name][0]
                                            );
                                $blogusers = get_users($args);
                                if ($blogusers) {
                                    
                                } else {
                                $class='_error';
                                $err_msg = "<strong>This must be a valid wordpress user.</strong><script type='text/javascript'>
                                                 var error = '<div class=\"error below-h2\"><p><strong>Invalid Data: </strong>The Owner be a valid wordpress user.</p></div>';
                                                 // Append the error
                                                 jQuery( '#post' ).prepend( error );
                                            </script>";
                                }           
                            
                            }
                            
                            echo '<label class="hmg_mb_label" for="' . $field_id_name . '">' . $label . '</label><input type="text" name="custom_meta[' . $field_id_name . ']" id="' . $field_id_name . '" value="' . $meta[$field_id_name][0] . '" class="hmg_mb_field' . $class . '"/><br/>' . $err_msg;
                            // this is where we need to do validation on the fields!
                            
                        }
                    }
    
                }
            
            /* Listens for when the post type being saved */
            public function save() {
                add_action('save_post',array($this,'save_cb'),20);
            }
            
            public function save_cb() {
                
                $post_type_id = $this->post_type_id;
                // Deny the wordpress autosave function
                if( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) return;
                
                if ( ! wp_verify_nonce( $_POST['custom_post_type'], plugin_basename(__FILE__) ) ) return;
    
                global $post;
    
                if( isset( $_POST ) && isset( $post->ID ) && get_post_type( $post->ID ) == $post_type_id ) {
                    global $custom_fields;
                    // Loop through each meta box
                    foreach( $custom_fields as $title => $fields ) {
                        // Loop through all fields
                        foreach( $fields as $label => $type ) {
                            $field_id_name 	= strtolower( str_replace( ' ', '_', $title ) ) . '_' . strtolower( str_replace( ' ', '_', $label ) );
                            // need to hook into custom validation methods here
                            update_post_meta( $post->ID, $field_id_name, $_POST['custom_meta'][$field_id_name] );
                        }
                    } 
                }
                
            }
    
    }
}

?>