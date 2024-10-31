<?php
/**
 * Plugin Name: Parknav WP
 * Description: Easily help your visitors find parking close to you. Embed the Parknav Map on your Wordpress website using Elementor or with shortcode [parknav lat="37.78" lon="-122.4"]
 * Plugin URI:  https://parknav.com/plugins/wp
 * Version:     1.0.5
 * Author:      Parknav
 * Author URI:  https://parknav.com/
 * Text Domain: parknav-wp
 * Requires PHP: 5.6
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

// ------------------------ Start up ------------------------



add_filter( 'plugin_row_meta', 'my_plugin_row_meta', 10, 2 );

/**
 * my_plugin_row_meta
 */
 
function my_plugin_row_meta( $actions, $plugin_file ) {
 
 $action_links = array(
 
   'details' => array(
      'label' => __('Details', 'Parknav.com'),
      'url'   => 'http://www.parknav.com/plugins/wp/details'
    ));
 
  return plugin_action_links( $actions, $plugin_file, $action_links, 'after');
}
 
/**
 * plugin_action_links
 */
 
function  plugin_action_links ( $actions, $plugin_file,  $action_links = array(), $position = 'after' ) { 
 
  static $plugin;
 
  if( !isset($plugin) ) {
      $plugin = plugin_basename( __FILE__ );
  }
 
  if( $plugin == $plugin_file && !empty( $action_links ) ) {
 
     foreach( $action_links as $key => $value ) {
 
        $link = array( $key => '<a href="' . $value['url'] . '">' . $value['label'] . '</a>' );
 
         if( $position == 'after' ) {
 
            $actions = array_merge( $actions, $link );    
 
         } else {
 
            $actions = array_merge( $link, $actions );
         }
 
 
      }//foreach
 
  }// if
 
  return $actions;
 
}

if ( is_admin() ){ // admin actions
    function plugin_add_settings_link( $links ) {
        $settings_link = '<a href="https://parknav.com/plugins/settings">Settings</a>';
        array_push( $links, $settings_link );
          return $links;
    }
    $plugin = plugin_basename( __FILE__ );
    add_filter( "plugin_action_links_$plugin", 'plugin_add_settings_link' );
}
else {
    // non-admin enqueues, actions, and filters
}



/**
 * Main Parknav for WP Extension Class
 *
 * The main class that initiates and runs the plugin.
 *
 * @since 1.0.0
 */
final class Parknav_WP {

	/**
	 * Plugin Version
	 *
	 * @since 1.0.0
	 *
	 * @var string The plugin version.
	 */
	const VERSION = '1.0.0';

	/**
	 * Minimum Elementor Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum Elementor version required to run the plugin.
	 */
	const MINIMUM_ELEMENTOR_VERSION = '2.0.0';

	/**
	 * Minimum PHP Version
	 *
	 * @since 1.0.0
	 *
	 * @var string Minimum PHP version required to run the plugin.
	 */
	const MINIMUM_PHP_VERSION = '5.6';

	/**
	 * Instance
	 *
	 * @since 1.0.0
	 *
	 * @access private
	 * @static
	 *
	 * @var Parknav_WP The single instance of the class.
	 */
	private static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of the class is loaded or can be loaded.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 * @static
	 *
	 * @return Parknav_WP An instance of the class.
	 */
	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;

	}

	/**
	 * Constructor
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function __construct() {

		add_action( 'init', [ $this, 'i18n' ] );
		add_action( 'plugins_loaded', [ $this, 'init' ] );

	}

	/**
	 * Load Textdomain
	 *
	 * Load plugin localization files.
	 *
	 * Fired by `init` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function i18n() {

		load_plugin_textdomain( 'elementor-test-extension' );

	}

	/**
	 * Initialize the plugin
	 *
	 * Load the plugin only after Elementor (and other plugins) are loaded.
	 * Checks for basic plugin requirements, if one check fail don't continue,
	 * if all check have passed load the files required to run the plugin.
	 *
	 * Fired by `plugins_loaded` action hook.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init() {

		// Check if Elementor installed and activated
		if ( ! did_action( 'elementor/loaded' ) ) {
			// add_action( 'admin_notices', [ $this, 'admin_notice_missing_main_plugin' ] );
			return;
		}

		// Check for required Elementor version
		if ( ! version_compare( ELEMENTOR_VERSION, self::MINIMUM_ELEMENTOR_VERSION, '>=' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_elementor_version' ] );
			return;
		}

		// Check for required PHP version
		if ( version_compare( PHP_VERSION, self::MINIMUM_PHP_VERSION, '<' ) ) {
			add_action( 'admin_notices', [ $this, 'admin_notice_minimum_php_version' ] );
			return;
		}

		// Add Plugin actions
		add_action( 'elementor/widgets/widgets_registered', [ $this, 'init_widgets' ] );

		// add_action( 'elementor/controls/controls_registered', [ $this, 'init_controls' ] );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have Elementor installed or activated.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_missing_main_plugin() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor */
			esc_html__( '"%1$s" requires "%2$s" to be installed and activated.', 'elementor-test-extension' ),
			'<strong>' . esc_html__( 'Parknav WP Elementor Extension', 'elementor-test-extension' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-test-extension' ) . '</strong>'
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required Elementor version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_elementor_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: Elementor 3: Required Elementor version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'elementor-test-extension' ) . '</strong>',
			'<strong>' . esc_html__( 'Elementor', 'elementor-test-extension' ) . '</strong>',
			 self::MINIMUM_ELEMENTOR_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Admin notice
	 *
	 * Warning when the site doesn't have a minimum required PHP version.
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function admin_notice_minimum_php_version() {

		if ( isset( $_GET['activate'] ) ) unset( $_GET['activate'] );

		$message = sprintf(
			/* translators: 1: Plugin name 2: PHP 3: Required PHP version */
			esc_html__( '"%1$s" requires "%2$s" version %3$s or greater.', 'elementor-test-extension' ),
			'<strong>' . esc_html__( 'Elementor Test Extension', 'elementor-test-extension' ) . '</strong>',
			'<strong>' . esc_html__( 'PHP', 'elementor-test-extension' ) . '</strong>',
			 self::MINIMUM_PHP_VERSION
		);

		printf( '<div class="notice notice-warning is-dismissible"><p>%1$s</p></div>', $message );

	}

	/**
	 * Init Widgets
	 *
	 * Include widgets files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_widgets() {

		// Include Widget files
		require_once( __DIR__ . '/widgets/parknavwp-widget.php' );

		// Register widget
		\Elementor\Plugin::instance()->widgets_manager->register_widget_type( new \Elementor_Parknav_Widget() );

	}

	/**
	 * Init Controls
	 *
	 * Include controls files and register them
	 *
	 * @since 1.0.0
	 *
	 * @access public
	 */
	public function init_controls() {

		// Include Control files
		require_once( __DIR__ . '/controls/parknavwp-control.php' );

		// Register control
		\Elementor\Plugin::$instance->controls_manager->register_control( 'control-type-', new \Test_Control() );

	}

}

Parknav_WP::instance();

function parknav_wp_shortcode($atts) {
	$Content = '<iframe id="parknav" width="100%" height=' . $atts['height'];
	$Content .= ' frameborder="0" seamless="seamless" scrolling="no" ';
	$Content .= 'marginheight="0" marginwidth="0" ';
	$Content .= 'src="https://widget.parknav.com/simple?lat=' . $atts['lat'] . '&lon=' . $atts['lon'] . '&zoom=' . $atts['zoom'] .  '&highColor=339933&mediumColor=cc9900&lowColor=ffffff&restrictedColor=b3b3b3&chanceThreshold=41&numGarages=4" ';
	$Content .= 'style="overflow: hidden; overflow-x: hidden; overflow-y: hidden; border: 0px;"></iframe> ';
	 
    return $Content;
}

add_shortcode('parknav', 'parknav_wp_shortcode');





