<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://cog.dog/
 * @since      1.0.0
 *
 * @package    Splotwriter
 * @subpackage Splotwriter/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Splotwriter
 * @subpackage Splotwriter/admin
 * @author     Alan Levine <cogdogblog@gmail.com>
 */
class Splotwriter_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

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
		 * defined in Splotwriter_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Splotwriter_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_media();
		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/splotwriter-admin.js', null, $this->version, true );

	}

	public function splotwriter_change_post_object() {
		$thing_name = 'Writing';

		global $wp_post_types;
		$labels = &$wp_post_types['post']->labels;
		$labels->name =  $thing_name . 's';;
		$labels->singular_name =  $thing_name;
		$labels->add_new = 'Add ' . $thing_name;
		$labels->add_new_item = 'Add ' . $thing_name;
		$labels->edit_item = 'Edit ' . $thing_name;
		$labels->new_item =  $thing_name;
		$labels->view_item = 'View ' . $thing_name;
		$labels->search_items = 'Search ' . $thing_name;
		$labels->not_found = 'No ' . $thing_name . ' found';
		$labels->not_found_in_trash = 'No ' .  $thing_name . ' found in Trash';
		$labels->all_items = 'All ' . $thing_name;
		$labels->menu_name =  $thing_name;
		$labels->name_admin_bar =  $thing_name;
	}
	
	public function splotwriter_change_label() {
		global $menu;
		global $submenu;
	
		$thing_name = 'Writing';
	
		$menu[5][0] = $thing_name . 's';
		$submenu['edit.php'][5][0] = 'All ' . $thing_name . 's';
		$submenu['edit.php'][10][0] = 'Add ' . $thing_name;
		$submenu['edit.php'][15][0] = $thing_name .' Categories';
		$submenu['edit.php'][16][0] = $thing_name .' Tags';
		echo '';
		
		add_submenu_page('edit.php', 'Writings in Progress (not submitted)', 'In Progress', 'edit_pages', 'edit.php?post_status=draft&post_type=post&cat=' . get_cat_ID( 'In Progress' ) ); 
	
		add_submenu_page('edit.php', 'Writings Submitted for Approval', 'Pending Approval', 'edit_pages', 'edit.php?post_status=pending&post_type=post' ); 
	
	}

	public function splotwriter_options_to_admin() {
	// put the options on the menu and top stage
		global $wp_admin_bar;
	
		// we can add a submenu item too
		$wp_admin_bar->add_menu( array(
			'parent' => '',
			'id' => 'splotwriter-options',
			'title' => __('SPLOT Writer Options'),
			'href' => admin_url( 'options-general.php?page=splotwriter-options')
		) );
	}


	public function splotwriter_editlink_meta_box() {

		add_meta_box(
			're_editlink',
			'Author Re-Edit Link',
			array( $this, 'splotwriter_editlink_meta_box_callback' ),
			'post',
			'side'
		);
	}

	// content for edit link meta box
	public function splotwriter_editlink_meta_box_callback( $post ) {

		// Add a nonce field so we can check for it later.
		wp_nonce_field( 'splotwriter_editlink_meta_box_data', 'splotwriter_editlink_meta_box_nonce' );

		// get edit key, it's in the meta, baby!
		$ekey = get_post_meta( $post->ID, 'wEditKey', 1 );
	
		// Create an edit link if it does not exist
		if ( !$ekey ) {
			splotwriter_make_edit_link( $post->ID );
			$ekey = get_post_meta( $post->ID, 'wEditKey', 1 );
		}

		echo '<label for="writing_edit_link">';
		_e( 'Click to highlight, then copy', 'splotwriter' );
		echo '</label> ';
		echo '<input style="width:100%; type="text" id="writing_edit_link" name="writing_edit_link" value="' . get_bloginfo('url') . '/write/?wid=' . $post->ID . '&tk=' . $ekey  . '"  onclick="this.select();" />';
	
	}		
}
