<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://cog.dog/
 * @since      1.0.0
 *
 * @package    Splotwriter
 * @subpackage Splotwriter/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Splotwriter
 * @subpackage Splotwriter/includes
 * @author     Alan Levine <cogdogblog@gmail.com>
 */
class Splotwriter {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Splotwriter_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'SPLOTWRITER_VERSION' ) ) {
			$this->version = SPLOTWRITER_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'splotwriter';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Splotwriter_Loader. Orchestrates the hooks of the plugin.
	 * - Splotwriter_i18n. Defines internationalization functionality.
	 * - Splotwriter_Admin. Defines all hooks for the admin area.
	 * - Splotwriter_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-splotwriter-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-splotwriter-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-splotwriter-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-splotwriter-public.php';

		$this->loader = new Splotwriter_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Splotwriter_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Splotwriter_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Splotwriter_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
		$this->loader->add_action( 'after_setup_theme', $plugin_admin, 'splotwriter_load_theme_options', 9 );
		$this->loader->add_action( 'init', $plugin_admin, 'splotwriter_change_post_object' );
		$this->loader->add_action( 'admin_menu', $plugin_admin, 'splotwriter_change_label' );
		$this->loader->add_action( 'wp_before_admin_bar_render', $plugin_admin, 'splotwriter_options_to_admin' );
		$this->loader->add_action( 'add_meta_boxes', $plugin_admin, 'splotwriter_editlink_meta_box' );

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Splotwriter_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );
		
		$this->loader->add_action( 'after_switch_theme', $plugin_public, 'splotwriter_rewrite_rules' );
		$this->loader->add_action( 'init', $plugin_public, 'splotwriter_rewrite_rules' );
		$this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );
		$this->loader->add_action( 'customize_register', $plugin_public, 'splotwriter_register_theme_customizer' );

		$this->loader->add_action( 'after_setup_theme', $plugin_public, 'splot_remove_admin_bar' );
		$this->loader->add_action( 'query_vars', $plugin_public, 'splotwriter_queryvars' );	
		$this->loader->add_action( 'template_redirect', $plugin_public, 'splotwriter_write_director' );
		$this->loader->add_action( 'rest_api_init', $plugin_public, 'splotwriter_create_api_posts_meta_field' );

		$this->loader->add_filter( 'the_content', $plugin_public, 'splotwriter_mod_content' );
		$this->loader->add_action( 'wp_footer', $plugin_public, 'splotwriter_footer', 100 );
		$this->loader->add_filter( 'body_class', $plugin_public, 'splotwriter_formpage_class' );
		$this->loader->add_filter( 'mce_buttons', $plugin_public, 'splotwriter_tinymce_buttons' );
		$this->loader->add_filter( 'mce_buttons_2', $plugin_public, 'splotwriter_tinymce_2_buttons' );		
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    Splotwriter_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
