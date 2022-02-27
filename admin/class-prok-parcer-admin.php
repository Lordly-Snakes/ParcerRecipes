<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://example.com
 * @since      1.0.0
 *
 * @package    prok_parcer
 * @subpackage prok_parcer/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    prok_parcer
 * @subpackage prok_parcer/admin
 * @author     Your Name <email@example.com>
 */
class prok_parcer_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $prok_parcer    The ID of this plugin.
	 */
	private $prok_parcer;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $prok_parcer       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $prok_parcer, $version ) {

		$this->prok_parcer = $prok_parcer;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in prok_parcer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The prok_parcer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->prok_parcer, plugin_dir_url( __FILE__ ) . 'css/prok-parcer-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in prok_parcer_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The prok_parcer_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->prok_parcer, plugin_dir_url( __FILE__ ) . 'js/prok-parcer-admin.js', array( 'jquery' ), $this->version, false );

	}

}
