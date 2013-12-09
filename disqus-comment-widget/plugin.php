<?php
/*
Plugin Name: Disqus Comment Widget
Plugin URI: http://eedee.net/disqus-comment-widget
Description: Lists disqus comments, decide between recent comments or popular comments. Uses the <a href="https://github.com/disqus/disqus-php">official disqus API</a>. 
Register <a href="http://help.disqus.com/customer/portal/articles/787016-how-to-create-an-api-application">here</a> for an API Key.
Uses Ajax to fetch comments dynamically.
Version: 1.0
Author: eedee
Author URI: http://eedee.net
Author Email: contact@niklasplessing.net
Text Domain: eedee
Domain Path: /lang/
Network: false
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Copyright 2013 edee (email@domain.com)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class Ed_Dq_Widget extends WP_Widget {

	/**
	 * Constants
	 */

	var $settings;

	/*--------------------------------------------------*/
	/* Constructor
	/*--------------------------------------------------*/

	/**
	 * Specifies the classname and description, instantiates the widget,
	 * loads localization files, and includes necessary stylesheets and JavaScript.
	 */
	public function __construct() {

		// load plugin text domain
		add_action( 'init', array( $this, 'widget_textdomain' ) );

		// Hooks fired when the Widget is activated and deactivated
		register_activation_hook( __FILE__, array( $this, 'activate' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivate' ) );

		parent::__construct(
			'disqus_comment_widget',
			__( 'My Disqus Widget', 'widget-name-locale' ),
			array(
				'classname'		=>	'Ed_Dq_Widget',
				'description'	=>	__( 'short description here', 'widget-name-locale' )
			)
		);

		$this->settings = array (
			'path'			=>  plugins_url( 'disqus-comment-widget' )
		);

		//register ajax functions
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_ajax_scripts' ) );
		add_action( 'wp_ajax_handle_disqus_post', array( $this, 'handle_disqus_post' ) );
        add_action( 'wp_ajax_nopriv_handle_disqus_post', array( $this, 'handle_disqus_post' ) );

		// Register admin styles and scripts
		add_action( 'admin_print_styles', array( $this, 'register_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_admin_scripts' ) );

		// Register site styles and scripts
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_widget_scripts' ) );

	} // end constructor

	public function enqueue_ajax_scripts() 
    {
        wp_enqueue_script( 
             'ajax-disqus' , plugins_url( '/js/ajax.js', __FILE__ ), array( 'jquery' )
        );
        # Here we send PHP values to JS
        wp_localize_script( 
        	'ajax-disqus', 
        	'ajax_object',
            array( 
            	'ajax_url' => admin_url( 'admin-ajax.php' ), 
            	'ajax_nonce' => wp_create_nonce( 'my_nonce' ),
            	'loading'    => 'http://i.stack.imgur.com/drgpu.gif'
            	) 
            );   
    }

	public function handle_disqus_post() {
	
		check_ajax_referer( 'my_nonce', 'security' );
	  
		if ($_SERVER['REQUEST_METHOD'] == 'POST') {
	
			$error = false;
	
			if ($error == false) {

				//if ( false === ( $dq_comments = get_transient( 'dq_comment_cache' ) ) ) {
					$options = get_option('widget_disqus_comment_widget');
					if( !isset($options) || count($options) != 2) {
						wp_send_json_error( __( "error in ajax call", "eedee") );	
					}

					if ( !is_numeric($_REQUEST['type']) ) {
						wp_send_json_error( __( "Your data is teh suX0R", "eedee") );
					}

					if ( $_REQUEST['type'] == 1 ) {
						$dq_comments = $this->get_dq_api_result('https://disqus.com/api/3.0/forums/listPosts.json?api_key='.$options['2']['disqus_api_key'].'&forum='.$options['2']['forum_key'].'&limit='.$options['2']['limit']);
					} elseif ( $_REQUEST['type'] == 2 ) {
						$dq_comments = $this->get_dq_api_result('https://disqus.com/api/3.0/posts/listPopular.json?api_key='.$options['2']['disqus_api_key'].'&forum='.$options['2']['forum_key'].'&limit='.$options['2']['limit']);
					} elseif ( $_REQUEST['type'] == 3 ) {
						if (is_numeric($_REQUEST['comment_id']) && is_numeric($_REQUEST['thread_id'])) {
							$thread = $this->get_dq_api_result('https://disqus.com/api/3.0/threads/details.json?api_key='.$options['2']['disqus_api_key'].'&thread='.$_REQUEST['thread_id']);
							$link = $thread->link;
							$dq_comments = $link . '#comment-' . $_REQUEST['comment_id'];
						}
					} else {
						wp_send_json_error();
					}

					//strip 
					// foreach ($dq_comments as $comment) {
					// 	$thread_id = $comment->thread;
					// 	$thread = $this->get_dq_api_result('https://disqus.com/api/3.0/threads/details.json?api_secret='.$options['2']['disqus_api_key'].'&thread='.$thread_id);
					// 	$link = $thread->link;
					// 	$comment->link = $link . '#comment-' . $comment->id;
					// }

					//set_transient( 'dq_comment_cache', $dq_comments, 60 * 60 * 3);	

				//} 

				wp_send_json_success($dq_comments);
			} 
		} else {
			wp_send_json_error();
		}
	}

	private function get_dq_api_result($endpoint) {
		ini_set('display_errors', 1); 

		// Get the results
		$session = curl_init($endpoint);
		$ch = curl_init();
		curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
		$data = curl_exec($session);
		curl_close($session);
		 
		// decode the json data to make it easier to parse with php
		$results = json_decode($data);
		 
		// parse the desired JSON data into HTML for use on your site
		$response = $results->response;	
		return $response;
	}


	/*--------------------------------------------------*/
	/* Widget API Functions
	/*--------------------------------------------------*/

	/**
	 * Outputs the content of the widget.
	 *
	 * @param	array	args		The array of form elements
	 * @param	array	instance	The current instance of the widget
	 */
	public function widget( $args, $instance ) {

		extract( $args, EXTR_SKIP );

		apply_filters('widget-title', $instance['title']);

		echo $before_widget;

		// TODO:	Here is where you manipulate your widget's values based on their input fields

		include( plugin_dir_path( __FILE__ ) . '/views/widget.php' );

		echo $after_widget;

	} // end widget

	/**
	 * Processes the widget's options to be saved.
	 *
	 * @param	array	new_instance	The previous instance of values before the update.
	 * @param	array	old_instance	The new instance of values to be generated via the update.
	 */
	public function update( $new_instance, $old_instance ) {

		$instance = $old_instance;

		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		$instance['disqus_api_key'] = ( ! empty ($new_instance['disqus_api_key']) ) ? strip_tags ($new_instance['disqus_api_key']) : '';
		$instance['forum_key'] = ( ! empty ($new_instance['forum_key']) ) ? strip_tags ($new_instance['forum_key']) : '';
		$instance['limit'] = ( ! empty ($new_instance['limit']) ) ? strip_tags ($new_instance['limit']) : '';

		return $instance;

	} // end widget

	/**
	 * Generates the administration form for the widget.
	 *
	 * @param	array	instance	The array of keys and values for the widget.
	 */
	public function form( $instance ) {

    	// TODO:	Define default values for your variables
		$instance = wp_parse_args(
			(array) $instance
		);

		$title = ( isset ($instance['title'] ) ) ? esc_attr($instance['title']) : __( 'New title', 'eedee' );
		$disqus_api_key = ( isset ($instance['disqus_api_key'] ) ) ? esc_attr($instance['disqus_api_key']) : '';
		$forum_key = ( isset ( $instance ['forum_key'] ) ) ? esc_attr($instance['forum_key']) : '' ;
		$limit = ( isset ( $instance ['limit'] ) ) ? esc_attr($instance['limit']) : 5 ;
		
		// Display the admin form
		include( plugin_dir_path(__FILE__) . '/views/admin.php' );

	} // end form

	/*--------------------------------------------------*/
	/* Public Functions
	/*--------------------------------------------------*/

	/**
	 * Loads the Widget's text domain for localization and translation.
	 */
	public function widget_textdomain() {

		// TODO be sure to change 'widget-name' to the name of *your* plugin
		load_plugin_textdomain( 'widget-name-locale', false, plugin_dir_path( __FILE__ ) . '/lang/' );

	} // end widget_textdomain

	/**
	 * Fired when the plugin is activated.
	 *
	 * @param		boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog.
	 */
	public function activate( $network_wide ) {
		// TODO define activation functionality here
	} // end activate

	/**
	 * Fired when the plugin is deactivated.
	 *
	 * @param	boolean	$network_wide	True if WPMU superadmin uses "Network Activate" action, false if WPMU is disabled or plugin is activated on an individual blog
	 */
	public function deactivate( $network_wide ) {
		// TODO define deactivation functionality here
	} // end deactivate

	/**
	 * Registers and enqueues admin-specific styles.
	 */
	public function register_admin_styles() {

		// TODO:	Change 'widget-name' to the name of your plugin
		wp_enqueue_style( 'widget-name-admin-styles', $this->settings['path'] . '/css/admin.css' );

	} // end register_admin_styles

	/**
	 * Registers and enqueues admin-specific JavaScript.
	 */
	public function register_admin_scripts() {

		// TODO:	Change 'widget-name' to the name of your plugin
		wp_enqueue_script( 'widget-name-admin-script', $this->settings['path'] . '/js/admin.js' , array('jquery') );

	} // end register_admin_scripts

	/**
	 * Registers and enqueues widget-specific styles.
	 */
	public function register_widget_styles() {

		// TODO:	Change 'widget-name' to the name of your plugin
		wp_enqueue_style( 'widget-name-widget-styles', $this->settings['path']. '/css/widget.css' );

	} // end register_widget_styles

	/**
	 * Registers and enqueues widget-specific scripts.
	 */
	public function register_widget_scripts() {

		// TODO:	Change 'widget-name' to the name of your plugin
		wp_enqueue_script( 'widget-name-script', $this->settings['path'] . '/js/widget.js' , array('jquery') );

	} // end register_widget_scripts

} // end class

// TODO:	Remember to change 'Widget_Name' to match the class name definition
add_action( 'widgets_init', create_function( '', 'register_widget("Ed_Dq_Widget");' ) );
