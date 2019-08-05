<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://cog.dog/
 * @since      1.0.0
 *
 * @package    Splotwriter
 * @subpackage Splotwriter/public
 */
/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Splotwriter
 * @subpackage Splotwriter/public
 * @author     Alan Levine <cogdogblog@gmail.com>
 */
class Splotwriter_Public {
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
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version = $version;
	}
	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {
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
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/splotwriter-public.css', array(), $this->version, 'all' );
	}
	/**
	 * Register the JavaScript for the public-facing side of the site.
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
		// wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/splotwriter-public.js', array( 'jquery' ), $this->version, false );
		
	 	if ( is_page( $this->splotwriter_get_write_page() ) ) { // use on just our form page
	 	
 			 // add media scripts if we are on our maker page and not an admin
			 // after http://wordpress.stackexchange.com/a/116489/14945
		 
			if (! is_admin() ) wp_enqueue_media();
		
			// Autoembed functionality in rich text editor
			// needs dependency on tiny_mce
			// h/t https://wordpress.stackexchange.com/a/287623
		
   			wp_enqueue_script( 'mce-view', '', array('tiny_mce') );		

			// Build in tag auto complete script
			wp_enqueue_script( 'suggest' );
		
			// custom jquery for the uploader on the form
			wp_register_script( 'jquery.writer' , plugin_dir_url( __FILE__ ) . 'js/jquery.writer.js', array( 'jquery', 'suggest' ), $this->version, true );	
			
			// add a local variable for the site's home url
			wp_localize_script(
			  'jquery.writer',
			  'writerObject',
			  array(
				'splotURL' => esc_url(home_url())
			  )
			);
			
			wp_enqueue_script( 'jquery.writer' );
		
			// add scripts for fancybox (used for help) 
			//-- h/t http://code.tutsplus.com/tutorials/add-a-responsive-lightbox-to-your-wordpress-theme--wp-28100
			wp_enqueue_script( 'fancybox', plugin_dir_url( __FILE__ ) . 'js/lightbox/js/jquery.fancybox.pack.js', array( 'jquery' ), $this->version, true );
			wp_enqueue_script( 'lightbox', plugin_dir_url( __FILE__ ) . 'js/lightbox/js/lightbox.js', array( 'fancybox' ), $this->version,
		null , '1.0', TRUE );
		
			wp_enqueue_style( 'lightbox-style', plugin_dir_url( __FILE__ ) . 'js/lightbox/css/jquery.fancybox.css' );

			
		} elseif ( is_single() ) {
			// single writings, give is the jQuery for edit link stuff
			wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/jquery.editlink.js', null, $this->version, TRUE ); 
		}		
	}

// load theme options Settings
	public function splotwriter_load_theme_options() {	
		// class for adding admin options
		require plugin_dir_path( __FILE__ ) . 'class-splotwriter-options.php';
	}

	
	public function splotwriter_rewrite_rules() {
		// rewrite rules for sending to random item
	   add_rewrite_rule('random/?$', 'index.php?random=1', 'top');
	   // rewrite rules  for edit link requests
	   add_rewrite_rule( '^get-edit-link/([^/]+)/?',  'index.php?elink=1&wid=$matches[1]','top');	 	
	}
	
	// -----  add allowable url parameters so we can do reall cool stuff, wally
	public function splotwriter_queryvars( $qvars ) {
		$qvars[] = 'tk'; // token key for editing previously made stuff
		$qvars[] = 'wid'; // post id for editing
		$qvars[] = 'random'; // random flag
		$qvars[] = 'elink'; // edit link flag
		return $qvars;
	} 
 
	public function splotwriter_write_director() {
		 /* create links for write pages */
		if ( is_page( $this->splotwriter_get_write_page() ) and !isset( $_POST['splotwriter_form_make_submitted'] ) ) {
	
			// check for query vars that indicate this is a edit request/ build qstring
			$wid = get_query_var( 'wid' , 0 );   // id of post
			$tk  = get_query_var( 'tk', 0 );    // magic token to check
			$args = ( $wid and $tk )  ? '?wid=' . $wid . '&tk=' . $tk : '';
		
				// normal entry check for author
			if ( !is_user_logged_in() ) {
				// not already logged in? go to desk.
				wp_redirect ( home_url('/') . $this->splotwriter_get_desk_page()  . $args );
				exit;
	
			} elseif ( !current_user_can( 'edit_others_posts' ) ) {
				// okay user, who are you? we know you are not an admin or editor
		
				// if the writer user not found, we send you to the desk
				if ( !$this->splotwriter_check_user() ) {
					// now go to the desk and check in properly
					wp_redirect ( home_url('/') . $this->splotwriter_get_desk_page() . $args  );
					exit;
				} 
			}
		}
	
		if ( is_page( $this->splotwriter_get_desk_page()) ) {
	
			// check for query vars that indicate this is a edit request/ build qstring
			$wid = get_query_var( 'wid' , 0 );   // id of post
			$tk  = get_query_var( 'tk', 0 );    // magic token to check
			$args = ( $wid and $tk )  ? '?wid=' . $wid . '&tk=' . $tk : '';
	
			// already logged in? go directly to the tool
			if ( is_user_logged_in() ) {
				if ( current_user_can( 'edit_others_posts' ) ) {
					// If user has edit/admin role, send them to the tool
					wp_redirect( $this->splot_redirect_url() . $args );
					exit;
				} else {
					// if the correct user already logged in, go directly to the tool
					if ( $this->splotwriter_check_user() ) {			
						wp_redirect( $this->splot_redirect_url()  . $args );
						exit;
					} 
				}	
	
			} elseif ( splotwriter_option('accesscode') == '')  {
				$this->splot_user_login('writer', true, $args );
				exit;
			} elseif ( isset( $_POST['splotwriter_form_access_submitted'] ) 
			&& wp_verify_nonce( $_POST['splotwriter_form_access_submitted'], 'splotwriter_form_access' ) ) {
 
				// access code from the form
				if ( stripslashes( $_POST['wAccess'] ) == splotwriter_option('accesscode') ) {
					$this->splot_user_login('writer', true, $args );
					exit;
				}
			
			}
			
		}
	
	  if ( get_query_var('random') == 1 ) {
			 // set arguments for WP_Query on published posts to get 1 at random
			$args = array(
				'post_type' => 'post',
				'post_status' => 'publish',
				'posts_per_page' => 1,
				'orderby' => 'rand'
			);
			// It's time! Go someplace random
			$my_random_post = new WP_Query ( $args );
			while ( $my_random_post->have_posts () ) {
			  $my_random_post->the_post ();
  
			  // redirect to the random post
			  wp_redirect ( get_permalink () );
			  exit;
			}  
	   } elseif ( get_query_var('elink') == 1 and get_query_var('wid')  ) {
   
			// get the id parameter from URL
			$wid = get_query_var( 'wid' , 0 );   // id of post
			$this->splotwriter_mail_edit_link ($wid);
			exit;
	   }
	}
	
# -----------------------------------------------------------------
# login stuff
# -----------------------------------------------------------------
	public function splot_user_login( $user_login = 'writer', $redirect = true, $query_str = '' ) {
		/* login the special user account to allow authoring
		   Somestimes we want to do it without redirection
		   other times we have to pass a query string
		*/
	
		// check for the correct user
		$autologin_user = get_user_by( 'login', $user_login ); 
	
		if ( $autologin_user ) {
	
			// just in case we have old cookies
			wp_clear_auth_cookie(); 
		
			// set the user directly
			wp_set_current_user( $autologin_user->ID, $autologin_user->user_login );
		
			// new cookie
			wp_set_auth_cookie( $autologin_user->ID);
		
			// do the login
			do_action( 'wp_login', $autologin_user->user_login );
		
			// send 'em on their way
			if ($redirect) wp_redirect( $this->splot_redirect_url() . $query_str  );
		
		
		} else {
			// uh on, problem
			die ('Required account missing. Looks like there is not an account set up for "' . $user_login . '". See the theme options to set up.');
	
		}
	}
	public function splot_redirect_url() {
		// where to send the writer user after invisible login
		return ( home_url('/') . $this->splotwriter_get_write_page() );
	}
	public function splot_is_admin() {
		// test current author as above basic level of splot user
		return ( current_user_can( 'edit_others_posts' )  );
	}	
	
	// remove admin tool bar for non-admins, remove access to dashboard
	// -- h/t http://www.wpbeginner.com/wp-tutorials/how-to-disable-wordpress-admin-bar-for-all-users-except-administrators/
	public function splot_remove_admin_bar() {
		if ( !$this->splot_is_admin()  ) show_admin_bar(false);
	}
	

# -----------------------------------------------------------------
# SPLOT Writer Display 
# -----------------------------------------------------------------

	// filter content on writing page so we do not submit the page content if form is submitted
	public function splotwriter_mod_content( $content ) {
		if ( is_single() && in_the_loop() && is_main_query() ) {
	
			global $post;
			// additional content for single views
		
			$preview_content = ( is_preview() ) ? $this->splotwriter_preview_notice() : '';
		
			$postcontent = '';
			$wAuthor = get_post_meta( $post->ID, 'wAuthor', 1 );
		
			if ( splotwriter_option('show_footer' ) ) {
				$wFooter = get_post_meta( $post->ID, 'wFooter', 1 ); 
				if ($wFooter) $postcontent .= '<p id="writerfooter"><em>' . make_clickable( $wFooter ) . '</em></p>';
			}
		
			$postcontent .= '<hr /><div id="splotwritermeta"><h3>' . $this->splotwriter_meta_title()  . '</h3><ul><li><span class="metalabel">Author:</span> ' . $this->twitternameify( $wAuthor ) . '</li>
		<li><span class="metalabel">Published:</span> ' . get_the_time( get_option('date_format'), $post->ID ) . '</li>
		<li><span class="metalabel">Word Count:</span> ' . str_word_count( get_the_content()) . '</li>';
	
		// output estimated reading time if we are using plugin
		$postcontent .=  $this->splotwriter_get_reading_time('<li><span class="metalabel">Reading time:</span>', '</li>');

	
			// show the request edit link button if they have provided an email and post is published
			if ( splotwriter_option('show_email') == 1  and get_post_meta( $post->ID, 'wEmail', 1 ) and get_post_status() == 'publish' ) {
				$postcontent .= '<li><span class="metalabel">Edit Link:</span> <em>(emailed to author)</em> <a href="#" id="getEditLink" class="pretty-button pretty-button-blue" data-widurl="' . get_bloginfo('url') . '/get-edit-link/' .   $post->ID . '">Request Now</a> <span id="getEditLinkResponse" class="writenew"></span></li>';
			}
							
			if ( splotwriter_option( 'use_cc' ) != 'none' ) {					
				$postcontent .= '<li><span class="metalabel">Rights: </span>';

				// get the license code, either define for site or post meta for user assigned						
				$cc_code = ( splotwriter_option( 'use_cc' ) == 'site') ? splotwriter_option( 'cc_site' ) : get_post_meta($post->ID, 'wLicense', true);
				$postcontent .= $this->splotwriter_license_html( $cc_code, $wAuthor, get_the_time( "Y", $post->ID ) ) . '</li>';
			}
		
			$postcontent .= '<li><span class="metalabel">Featured Image:</span> ' . $this->twitternameify( $this->make_links_clickable( get_post_meta($post->ID, 'wHeaderCaption', true) ) ) . '</li>';
		
		if ( splotwriter_option('show_tweet_button') ) {
							
				$postcontent .= '<li><span class="metalabel">Share: </span> <a href="https://twitter.com/share" class="twitter-share-button" data-hashtags="' . splotwriter_option( 'hashtags' ) . '" 
	<a href="https://twitter.com/share" class="twitter-share-button" data-text="' . addslashes(get_the_title()) . ' by ' .  $wAuthor . '" data-hashtags="' . splotwriter_option( 'hashtags' ) . '" data-dnt="true">Tweet</a>
	<script>!function(d,s,id){var js,fjs=d.getElementsByTagName(s)[0],p=/^http:/.test(d.location)?\'http\':\'https\';if(!d.getElementById(id)){js=d.createElement(s);js.id=id;js.src=p+\'://platform.twitter.com/widgets.js\';fjs.parentNode.insertBefore(js,fjs);}}(document, \'script\', \'twitter-wjs\');</script></li>';

		}
		
			$postcontent .= '</ul></div>';
		
			return $preview_content . $content . $preview_content . $postcontent;

		} elseif ( is_page( $this->splotwriter_get_write_page() ) && in_the_loop() && is_main_query() ) {
			// Check if we're inside the main loop on the writing page
		
			if ( isset( $_POST['splotwriter_form_make_submitted'] ) ) {
				// skip page content once form submitted, just echo the form shotcode
				return '[writerform]';
			} else {
				// normal content on first view
				 return $content;
			}
	   
		}
 
		return $content;
	}

	public function splotwriter_footer() {
		echo '<p style="text-align:center; font-size:0.8em;">' . $this->splot_the_author() . '</p>';
	}

	public function splotwriter_formpage_class( $classes ) {
		if ( is_page( $this->splotwriter_get_write_page() ) ) {
			$classes[] = 'writerform';
		}
		return $classes;
	}

	// Customize the headings for the comment form
	public function splotwriter_comment_mod( $defaults ) {
		$defaults['title_reply'] = 'Provide Feedback';
		$defaults['logged_in_as'] = '';
		$defaults['title_reply_to'] = 'Provide Feedback for %s';
		return $defaults;
	}

	public function splotwriter_tinymce_buttons($buttons) {
		//Remove the more button from editor
		$remove = 'wp_more';

		// Find the array key and then unset
		if ( ( $key = array_search($remove,$buttons) ) !== false )
			unset($buttons[$key]);

		return $buttons;
	}

	// remove row 2 buttons from the visual editor
	public function splotwriter_tinymce_2_buttons($buttons)
	 {
		//Remove the keybord shortcut and paste text buttons
		$remove = array('wp_help','pastetext');

		return array_diff($buttons,$remove);
	 }

# -----------------------------------------------------------------
# Shortcode
# -----------------------------------------------------------------

	// register shortcodes
	public function register_shortcodes(){
   		add_shortcode( 'writerdesk', array( &$this, 'splotwriter_do_desk' ) );
   		add_shortcode( 'writerform', array( &$this, 'splotwriter_do_writerform' ) );
 	}

	public function splotwriter_do_desk() {

		//passcode to enter
		$wAccessCode = splotwriter_option('accesscode');
	
		// holder for output
		$output = $wAccess =  '';

		// already logged in but as different user on multisite?
	
		if ( is_user_logged_in() and !$this->splotwriter_check_user()  ) {
			// we need to force a click through a logout
			return '<div class="notify notify-green"><span class="symbol icon-tick"></span>' .'Now <a href="' . wp_logout_url( home_url('/') . 'write' ) . '" class="pretty-button pretty-button-green">activate the writing tool</a>.</div>';	  	
		}

		// verify that a  form was submitted and it passes the nonce check
		if ( isset( $_POST['splotwriter_form_access_submitted'] ) 
				&& wp_verify_nonce( $_POST['splotwriter_form_access_submitted'], 'splotwriter_form_access' ) ) {

			// grab the variables from the form
			$wAccess = 	stripslashes( $_POST['wAccess'] );

			// let's do some validation, store  error message for code mismatch

			if ( $wAccess != $wAccessCode ) {
				$output .= '<div class="notify notify-red"><span class="symbol icon-error"></span> <p><strong>Incorrect Access Code</strong> - try again? Hint: ' . splotwriter_option('accesshint') . '</p></div>'; 	

			} // end form submmitted check
		}
			
			// add the form code	
			$output .= '<form  id="writerform" class="writenew" method="post" action="">
						<fieldset>
							<label for="wAccess">' . __('Access Code', 'splotwriter' ) . '</label><br />
							<p>Enter a proper code</p>
							<input type="text" name="wAccess" id="wAccess" class="required" value="' . $wAccess . '" />
						</fieldset>	
		
						<fieldset>' . wp_nonce_field( 'splotwriter_form_access', 'splotwriter_form_access_submitted' ) . '
							<input type="submit" class="pretty-button pretty-button-blue" value="Check Code" id="checkit" name="checkit">
						</fieldset>
				</form>';
	
		return ($output);	
	}
	
	public function splotwriter_do_writerform() {    	
		// ------------------------ defaults ------------------------
	
		// start making output
		$output = '';	

		// Parent category for published topics
		$published_cat_id = get_cat_ID( 'Published' );

		// track errors
		$errors = array();

		// creative commons usage mode
		$my_cc_mode = splotwriter_option( 'use_cc' ); 

		$is_published = $is_re_edit = $linkEmailed = false; 
		$post_id = $revcount = 0;
		$formclass = 'writenew';
		$wStatus = "New, not saved";

		$wTitle = $wEmail = $wFooter = $wTags = $wNotes = $wLicense =  '';	

		// default welcome message
		$feedback_msg = $this->splotwriter_form_default_prompt();

		$wAuthor = "Anonymous";
		$wText =  splotwriter_option('def_text'); // pre-fill the writing area
		$wCats = array( splotwriter_option('def_cat')); // preload default category

		$wHeaderImage_id = splotwriter_option('defheaderimg');
		$wNotes_required = splotwriter_option('require_extra_info');
		$wLicense = splotwriter_option( 'cc_site' ); //default if used

		// Get the attachment excerpt as a default caption
		$wHeaderImageCaption = $this->get_attachment_caption_by_id( $wHeaderImage_id );
	
		// default notifation box style
		$box_style = '<div class="notify"><span class="symbol icon-info"></span> ';
	

		// ------------------------ front gate ------------------------
	
		// check for query vars that indicate this is a edit request
		$wid = get_query_var( 'wid' , 0 );   // id of post
		$tk  = get_query_var( 'tk', 0 );    // magic token to check

		if ( ( $wid  and $tk )  ) {
			// re-edit attempt
			$is_re_edit = true;
			$formclass = 'writedraft';	
	
			// log in as author
			if ( !is_user_logged_in() ) {
				$this->splot_user_login( 'writer', false );
			}
		} 

		if ( $is_re_edit and !isset( $_POST['splotwriter_form_make_submitted'] )) {
			// check for first entry of re-edit.

			// look up the stored edit key
			$wEditKey = get_post_meta( $wid, 'wEditKey', 1 );


			if (  $tk == $wEditKey) {
				// keys match, we are GOLDEN

				// default welcome message for a re-edit
				$feedback_msg = $this->splotwriter_form_re_edit_prompt();

				$writing = get_post( $wid );

				$wTitle = get_the_title( $wid );
				$wAuthor =  get_post_meta( $wid, 'wAuthor', 1 );
				$wEmail =  get_post_meta( $wid, 'wEmail', 1 );
				$wText = $writing->post_content; 
				$wHeaderImage_id = get_post_thumbnail_id( $wid);
				$box_style = '<div class="notify notify-green"><span class="symbol icon-tick"></span> ';	
				$post_status = get_post_status( $wid );			

				// get categories
				$categories = get_the_category( $wid);
				foreach ( $categories as $category ) { 
					$wCats[] = $category->term_id;
				} 
		
				// Get the attachment excerpt as a default caption
				$wHeaderImageCaption = $this->get_attachment_caption_by_id( $wHeaderImage_id );
				$wNotes = get_post_meta( $wid, 'wEditorNotes', 1 );
				$wLicense = get_post_meta( $wid, 'wLicense', 1 );

				// load the tags
				$wTags = implode(', ', wp_get_post_tags( $wid, array( 'fields' => 'names' ) ) );
				$revcount = 1;
				$post_id = $wid;
				
				$wStatus = 'Re-edit (revision #' . $revcount . ' last saved ' . get_the_date( '', $wid) . ' '  . get_the_time( '', $wid) . ')';
	
				} else {

					$is_re_edit = false;

					// updates for display	
					$errors[] = '<strong>Token Mismatch</strong> - please check the url provided.';
					$wStatus = 'Form input error';
					$formclass = 'writeoops';	
					// default welcome message
					$feedback_msg = 'This URL does not match the edit key. Please check the link from your email again, or return to your published writing and click the button at the bottom to send an edit link.';
					$is_published = true;  // not really but it serves to hide the form.
				} // $tk == $wEditKey

		} // is re edit

		// verify that a form was submitted and it passes the nonce check
		if ( isset( $_POST['splotwriter_form_make_submitted'] )  )  {
 
				// grab the variables from the form
				$wTitle = 					sanitize_text_field( stripslashes( $_POST['wTitle'] ) );
				$wAuthor = 					( isset ($_POST['wAuthor'] ) ) ? sanitize_text_field( stripslashes($_POST['wAuthor']) ) : 'Anonymous';
				$wEmail = 					sanitize_text_field( $_POST['wEmail'] );			
				$wTags = 					sanitize_text_field( $_POST['wTags'] );	
				$wText = 					wp_kses_post( $_POST['wText'] );
				$wNotes = 					sanitize_text_field( stripslashes( $_POST['wNotes'] ) );
				$wFooter = 					sanitize_text_field( stripslashes( $_POST['wFooter'] ) ) ;
				$wHeaderImage_id = 			$_POST['wHeaderImage'];
				$linkEmailed = 				$_POST['linkEmailed'];
				$post_id = 					$_POST['post_id'];
				$wCats = 					( isset ($_POST['wCats'] ) ) ? $_POST['wCats'] : array();
				$wLicense = 				( isset ($_POST['wLicense'] ) ) ? $_POST['wLicense'] : '';
				$wHeaderImageCaption = 		sanitize_text_field(  $_POST['wHeaderImageCaption']  );
				$revcount =					$_POST['revcount'] + 1;		
		
				// let's do some validation, store an error message for each problem found

		
				if ( $wTitle == '' ) $errors[] = '<strong>Title Missing</strong> - please enter an interesting title.'; 	
		
				if ( strlen($wText) < 8 ) $errors[] = '<strong>Missing text?</strong> - that\'s not much text, eh?';
		
				if ( $wHeaderImageCaption == '' ) $errors[] = '<strong>Header Image Caption Missing</strong> - please provide a description or a credit for your header image. We would like to assume it is your own image or one that is licensed for re-use.'; 
		
				if ( $wNotes_required == 1  AND $wNotes == '' ) $errors[] = '<strong>Extra Information Missing</strong> - please provide the requested extra information.';
				
				// test for email only if enabled in options
				if ( !empty( splotwriter_option('show_email') ) )  {
				
					// check first for valid email address
					if ( is_email( $wEmail ) ) {
						// if email is good then check if we are limiting to domains
						if ( !empty(splotwriter_option('email_domains'))  AND !$this->splotwriter_allowed_email_domain( $wEmail )  ) {
							$errors[] = '<strong>Email Address Not Allowed</strong> - The email address you entered <code>' . $wEmail . '</code> is not from an domain accepted in this site. This site requests that  addresses are ones with domains <code>' .  splotwriter_option('email_domains') . '</code>. ';
						}
				
					} else {
						// bad email, sam.
						$errors[] = '<strong>Invalid Email Address</strong> - the email address entered <code>' . $wEmail . '</code> is not a valid address. Pleae check and try again.';
					}
				}
				
				
				
		
				if ( count($errors) > 0 ) {
					// form errors, build feedback string to display the errors
					$feedback_msg = 'Sorry, but there are a few errors in your entry. Please correct and try again.<ul>';
			
					// Hah, each one is an oops, get it? 
					foreach ($errors as $oops) {
						$feedback_msg .= '<li>' . $oops . '</li>';
					}
			
					$feedback_msg .= '</ul>';
			
					// updates for display
					$revcount =	$_POST['revcount'];		
					$wStatus = 'Form input error';
					$formclass = 'writeoops';
					$box_style = '<div class="notify notify-red"><span class="symbol icon-error"></span> ';				
			
				} else { // good enough, let's set up a post! 


					// set notifications and display status
					if ( isset( $_POST['wPublish'] ) ) {
						// set to status defined as option
						$post_status = splotwriter_option('pub_status');
			
						if ( splotwriter_option('pub_status') == 'pending' ) {
							$wStatus = 'Submitted for Review';
							$formclass = 'writedraft';
							$box_style = '<div class="notify notify-green"><span class="symbol icon-tick"></span> ';
						} else {
							$wStatus = 'Published';
							$formclass = 'writepublished';
							$box_style = '<div class="notify notify-blue"><span class="symbol icon-tick"></span> ';
						}
			
						$wStatus .= ' (version #' . $revcount . ' last saved ' . get_the_date( '', $post_id) . ' '  . get_the_time( '', $post_id) . ')';
			
					} else {
						// stay as draft
						$formclass = 'writedraft';
						$post_status = 'draft';
						$wStatus = 'In Draft (revision #' . $revcount . ' last saved ' . get_the_date( '', $post_id) . ' '  . get_the_time( '', $post_id) . ')';
						$box_style = '<div class="notify notify-green"><span class="symbol icon-tick"></span> ';
					}
			
					// the default category for in progress
					$def_category_id = get_cat_ID( 'In Progress' );
			
					$w_information = array(
						'post_title' => $wTitle,
						'post_content' => $wText,
						'post_status' => $post_status, 
						'post_category' => 	array( $def_category_id )		
					);
			
			
					// updates for display
							
					$wStatus = 'In Draft (revision #' . $revcount . ' last saved ' . get_the_date( '', $post_id) . ' '  . get_the_time( '', $post_id) . ')';
					$formclass = 'writedraft';
		
					// is this a first draft?
					if ( $post_id == 0 ) {
			
						// insert as a new post
						$post_id = wp_insert_post( $w_information );
				
						// store the author as post meta data
						add_post_meta($post_id, 'wAuthor', $wAuthor);
				
						// store the email as post meta data
						add_post_meta($post_id, 'wEmail', $wEmail);				
				
						// add the tags
						wp_set_post_tags( $post_id, $wTags);
			
						// set featured image
						set_post_thumbnail( $post_id, $wHeaderImage_id);
				
						// Add caption to featured image if there is none, this is 
						// stored as post_excerpt for attachment entry in posts table
				
						if ( !$this->get_attachment_caption_by_id( $wHeaderImage_id ) ) {
							$i_information = array(
								'ID' => $wHeaderImage_id,
								'post_excerpt' => $wHeaderImageCaption
							);
					
							wp_update_post( $i_information );
						}
				
						// store the header image caption as post metadata
						add_post_meta($post_id, 'wHeaderCaption', $wHeaderImageCaption);
				
						// store notes for editor
						if ( $wNotes ) add_post_meta($post_id, 'wEditorNotes', $wNotes);

						// store notes for editor
						if ( $wFooter ) add_post_meta($post_id, 'wFooter', nl2br( $wFooter ) );
				
						// user selected license
						if ( $my_cc_mode != 'none' ) add_post_meta( $post_id,  'wLicense', $wLicense);
				
						// add a token for editing
						$this->splotwriter_make_edit_link( $post_id,  $wTitle );
				
						$feedback_msg = 'We have saved this first version of your writing. You can <a href="'. site_url() . '/?p=' . $post_id . 'preview=true' . '" target="_blank">preview it now</a> (opens in a new window), or make edits and save again. ';
						
						// if user provided email address, send instructions to use link to edit
						if ( $wEmail != '' ) {
							$this->splotwriter_mail_edit_link( $post_id, 'draft' );
							$linkEmailed = true;
							$feedback_msg .= ' Since you provided an email address, a message has been sent to <strong>' . $wEmail . '</strong>  with a special link that can be used at any time later to edit and publish your writing. '; 
						}
									
					 } else { // the post exists, let's update
					
						// make a copy of the category array so we can append the default category ID
						$copy_cats = $wCats;

						// check if we have a publish button click or this is a re-edit,
						// in this case we update the post with the form information
						if ( isset( $_POST['wPublish'] )  ) {
											
							// roger, we have ignition
							$is_published = true;

							// for status message links						
							if ( $this->splot_is_admin() ) {
								$returnlink = site_url();
								$postlink = get_permalink( $post_id );
							} else {
								$returnlink = wp_logout_url( site_url() );
								$postlink = wp_logout_url( get_permalink( $post_id ) );
							}
								
							// set the published category
							$copy_cats[] = $published_cat_id;
						
							// revise status to pending (new ones) 
							$w_information['post_status'] = splotwriter_option('pub_status');
										
							if ( splotwriter_option('pub_status') == 'pending' ) {
								// theme options for saving as reviewed
						
								$feedback_msg = 'Your writing <strong>"' . $wTitle . '"</strong> is now in the queue for publishing and will appear on <strong>' . get_bloginfo() . '</strong> as soon as it has been reviewed. ';

								if ( $wEmail != ''  ) {
									$feedback_msg .=  'We will notify you by email at <strong>' . $wEmail . '</strong> when it has been published.';
								}
						
								$feedback_msg .= ' Now please <a href="' . $returnlink  . '">clear the writing tool and return to ' . get_bloginfo() . '</a>.';
							
								// set up admin email
								$subject = 'Review newly submitted writing at ' . get_bloginfo();
				
								$message = '<strong>"' . $wTitle . '"</strong> written by <strong>' . $wAuthor . '</strong>  has been submitted to ' . get_bloginfo() . ' for editorial review. You can <a href="'. site_url() . '/?p=' . $post_id . 'preview=true' . '">preview it now</a>.<br /><br /> To  publish simply <a href="' . admin_url( 'edit.php?post_status=pending&post_type=post') . '">find it in the submitted works</a> and change it\'s status from <strong>Draft</strong> to <strong>Publish</strong>';
						
							} else {
								// theme options for saving as published
						
								$feedback_msg = 'Your writing <strong>"' . $wTitle . '"</strong> has been published to <strong>' . get_bloginfo(). '</strong>. You can now exit the writing tool to  <a href="'.  $postlink   . '" >view it now</a> or <a href="' . $returnlink  . '">return to ' . get_bloginfo() . '</a>.';
						
								// set up admin email
								$subject = 'Recently published writing at ' . get_bloginfo();
				
								$message = '<strong>"' . $wTitle . '"</strong> written by <strong>' . $wAuthor . '</strong>  has been published to ' . get_bloginfo() . '. You can <a href="'. site_url() . '/?p=' . $post_id . 'preview=true' . '">view it now</a>,  review / edit if needed, or just enjoy the feeling of being published on your site.';
						
								// if user provided email address, send instructions to use link to edit if not done before
								if ( $wEmail != '' and !$linkEmailed  ) $this->splotwriter_mail_edit_link( $post_id, splotwriter_option('pub_status') );
					
							} // is_status pending
						

							// Let's do some EMAIL! 
				
							// who gets mail? They do.
							$to_recipients = explode( "," ,  splotwriter_option( 'notify' ) );
							
							if ( $wNotes ) $message .= '<br /><br />There are some extra notes from the author:<blockquote>' . $wNotes . '</blockquote>';
				
							// turn on HTML mail
							add_filter( 'wp_mail_content_type', 'set_html_content_type' );
				
							// mail it!
							wp_mail( $to_recipients, $subject, $message);
				
							// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
							remove_filter( 'wp_mail_content_type', 'set_html_content_type' );				
																							
						} else {
							// updated but still in draft mode

							// if user provided email address, send instructions to use link to edit if not done before
							if ( isset( $wEmail ) and !$linkEmailed  ) $this->splotwriter_mail_edit_link( $post_id, 'draft' );				

							// attach the default category ID
							$copy_cats[] = $def_category_id ;
					
							$feedback_msg = 'Your edits have been updated and are still saved as a draft mode. You can <a href="'. site_url() . '/?p=' . $post_id . 'preview=true' . '"  target="_blank">preview it now</a> (opens in a new window), or make edits, review again, or if you are ready, submit it for publishing. ';
					
							if (  $wEmail != '' )  $feedback_msg .= ' Since you provided an email address, you should have a message that provides instructions on how to return and make edits in a later session.';
						} // isset( $_POST['wPublish'] 
							
				
						// add the id to our array of post information so we can issue an update
						$w_information['ID'] = $post_id;
						$w_information['post_category'] = $copy_cats;
		 
						// update the post
						wp_update_post( $w_information );
				
						// update the tags
						wp_set_post_tags( $post_id, $wTags);
				
						// update featured image
						set_post_thumbnail( $post_id, $wHeaderImage_id);
				
						// Update caption to featured image if there is none, this is 
						// stored as post_excerpt for attachment entry in posts table

						if ( !$this->get_attachment_caption_by_id( $wHeaderImage_id ) ) {
							$i_information = array(
								'ID' => $wHeaderImage_id,
								'post_excerpt' => $wHeaderImageCaption
							);
					
							wp_update_post( $i_information );
						} 

						// store the author's name
						update_post_meta($post_id, 'wAuthor', $wAuthor);

						// update the email as post meta data
						update_post_meta($post_id, 'wEmail', $wEmail);	
				
																			
						// store the header image caption as post metadata
						update_post_meta($post_id, 'wHeaderCaption', $wHeaderImageCaption);

				
						// user selected license
						if ( $my_cc_mode != 'none' ) update_post_meta( $post_id,  'wLicense', $wLicense);

						// store notes for editor
						if ( $wNotes ) update_post_meta($post_id, 'wEditorNotes', $wNotes);

						// store any end notes
						if ( $wFooter ) update_post_meta($post_id, 'wFooter', nl2br( $wFooter ) );
												
					} // post_id = 0
							
				} // count errors	
						
		} // end form submmitted check	

								
		$output .= $box_style . $feedback_msg . '</div>'; 
			
		$output .= '<div class="clear"></div>';

						
		// show form in logged in and it has not been published 			
		if ( is_user_logged_in() and (!$is_published or $is_re_edit) ) { 
	
			$output .= '<form  id="writerform" class=' . $formclass . '" method="post" action="">
			<div class="writestatus">STATUS: <span class="statnow">' . $wStatus . '</span></div>
			<input name="post_id" type="hidden" value="' .  $post_id . '" />
			<input name="revcount" type="hidden" value="' .  $revcount . '" />
			<input name="linkEmailed" type="hidden" value="' . $linkEmailed . '" />
					<fieldset id="theTitle">
						<label for="wTitle">' . $this->splotwriter_form_item_title()  . '</label><br />
						<p>'  . $this->splotwriter_form_item_title_prompt() . '</p>
						<input type="text" name="wTitle" id="wTitle" class="required writerfield" value="' .  $wTitle  . '" />
					</fieldset>	
		

					<fieldset id="theAuthor">
						<label for="wAuthor">' . $this->splotwriter_form_item_byline()  . '</label><br />
						<p> ' . $this->splotwriter_form_item_byline_prompt() . '</p>
						<input type="text" name="wAuthor" id="wAuthor" class="required writerfield" value="' .  $wAuthor  . '"  />
					</fieldset>	
			
					<fieldset id="theText">
							<label for="wText">' . $this->splotwriter_form_item_writing_area()  . '</label>
							<p>' . $this->splotwriter_form_item_writing_area_prompt() .'</p>
					
							<p> See details on the formatting tools in the  
	<a class="video fancybox.iframe" href="' .  plugin_dir_url( __FILE__ ) . 'content/edit-help.html">editing tool tips</a>.</p>';

							// set up for inserting the WP post editor
							$settings = array( 
								'textarea_name' => 'wText', 
								'editor_height' => '400', 
								'drag_drop_upload' => true, 
							);
						
							// start output buffering to capture editor
							ob_start();						
							wp_editor(  stripslashes( $wText ), 'wtext', $settings );				
							$output .= ob_get_clean();
						
							$output .= '</fieldset>';

					if (splotwriter_option('show_footer') ) { 
						$output .= '<fieldset id="theFooter">
								<label for="wFooter">' . $this->splotwriter_form_item_footer() . '</label>						
								<p>' . $this->splotwriter_form_item_footer_prompt() . '</p>
								<textarea name="wFooter" id="wFooter" class="writerfield" rows="15">' .  stripslashes( $wFooter ) . '</textarea>
						</fieldset>';
					}
			
					$output .= '<fieldset id="theHeaderImage">
						<label for="headerImage">' . $this->splotwriter_form_item_header_image() . '</label>
					
						<div class="uploader">
							<input id="wHeaderImage" name="wHeaderImage" type="hidden" value="' .  $wHeaderImage_id . '" />';
				
							if ($wHeaderImage_id) {
								$defthumb = wp_get_attachment_image_src( $wHeaderImage_id, 'thumbnail' );
							} else {
								$defthumb = [];
								$defthumb[] = plugin_dir_url( __FILE__ ) . '/images/default-header-thumb.jpg';
								$wHeaderImageCaption = 'flickr photo by Lívia Cristina https://flickr.com/photos/liviacristinalc/3402221680 shared under a Creative Commons (BY-NC-ND) license';
							}
					
				
							$output .=  '<img src="' .  $defthumb[0] . '" alt="article banner image" id="headerthumb" /><br />
				
							<input type="button" id="wHeaderImage_button"  class="btn btn-success btn-medium  upload_image_button" name="_wImage_button"  data-uploader_title="Set Header Image" data-uploader_button_text="Select Image" value="Set Header Image" />	
							</div>
					
							<p>' . $this->splotwriter_form_item_header_image_prompt() . '<br clear="left"></p>
					
							<label for="wHeaderImageCaption">' . $this->splotwriter_form_item_header_caption() . '</label>
							<p>' . $this->splotwriter_form_item_header_caption_prompt() . '</p>
							<input type="text" name="wHeaderImageCaption" class="writerfield" id="wHeaderImageCaption" value="' .  htmlentities( stripslashes( $wHeaderImageCaption ), ENT_QUOTES) .'"  />
			
					</fieldset>';				
			
			
					if (splotwriter_option('show_cats') ) {

						$output .= '<fieldset  id="theCats">
							<label for="wCats">' . $this->splotwriter_form_item_categories() . '</label>
							<p>' . $this->splotwriter_form_item_categories_prompt() . '</p>';

							// set up arguments to get all categories that are children of "Published"
							$args = array(
								'child_of'                 => $published_cat_id,
								'hide_empty'               => 0,
							); 
				
							$article_cats = get_categories( $args );

							foreach ( $article_cats as $acat ) {
				
								$checked = ( in_array( $acat->term_id, $wCats) ) ? ' checked="checked"' : '';
					
								$output .= '<br /><input type="checkbox" name="wCats[]" value="' . $acat->term_id . '"' . $checked . '> ' . $acat->name . ' <em style="font-size:smaller">' . $acat->description . '</em>';
							}


							$output .= '</fieldset>';

						} // if show_cats
			
					if (splotwriter_option('show_tags') ) {
			
						$output .= '<fieldset id="theTags">
							<label for="wTags">' . $this->splotwriter_form_item_tags() . '</label>
							<p>' . $this->splotwriter_form_item_tags_prompt() . '</p>
				
							<input type="text" name="wTags" id="wTags" class="writerfield" value="' .  $wTags .'"   />
						</fieldset>';

					} // show_tags


					if (splotwriter_option('show_email') ) {
						$output .= '<fieldset id="theEmail">
							<label for="wEmail">' . $this->splotwriter_form_item_email() . ' (optional)</label><br />
							<p>' . $this->splotwriter_form_item_email_prompt();
							
							if  ( !empty( splotwriter_option('email_domains') ) ) {
								$output .= 'Allowable email addresses must be ones from domains <code>' . splotwriter_option('email_domains') . '</code>.';
							}
							
							$output .= '</p>
							<input type="text" name="wEmail" id="wTitle" class="writerfield"  value="' .  $wEmail . '" autocomplete="on"  />
						</fieldset>';
					}
			

					if ( $wNotes_required != -1 ) {
			
						$output .= '<fieldset id="theNotes">';
						$req_state = ( $wNotes_required == 1 ) ? 'required' : 'optional';
					
						$output .= '<label for="wNotes">' . $this->splotwriter_form_item_editor_notes() .  __(' (' . $req_state . ')' , 'splotwriter') .'</label>	<p><' . $this->splotwriter_form_item_editor_notes_prompt()  .'</p>
							<textarea name="wNotes" class="writerfield" id="wNotes" rows="15">' .  stripslashes( $wNotes ) .'</textarea></fieldset>';
			
					} // wNotes required


						if ( $my_cc_mode != 'none' ) {
					
							$output .= '<fieldset  id="theLicense">
								<label for="wLicense">' . $this->splotwriter_form_item_license() . '</label>';
						
								if ( $my_cc_mode == 'site' ) {
				
									$output .= '<p>All writing added to this site will be published under a rights statement like:</p>
							
									<p class="form-control">' .  $this->splotwriter_license_html( splotwriter_option( 'cc_site' ), $wAuthor ) . '</p>
									<input type="hidden" name="wLicense" id="wLicense" value="' .  splotwriter_option( 'cc_site' ) . '">';
							
			
								} elseif  ( $my_cc_mode == 'user' ) {
							
									$output .= '<p>' . $this->splotwriter_form_item_license_prompt() . '</p>
									<select name="wLicense" id="wLicense" class="form-control">
										<option value="--">Select...</option>
										' .  $this->cc_license_select_options( $wLicense ) . '</select>';			
								} // -- cc_mode type = site or user
							$output .= '</fieldset>';
						} // -- cc_mode != none

		
						$output .= '<fieldset>';
						$output .= wp_nonce_field( 'splotwriter_form_make', 'splotwriter_form_make_submitted', false, false ); 

				
						if ( $post_id ) {
							// set up button names
							if ( $is_re_edit ) {
								$save_btn_txt = "Update and Publish";
							} else {
								$save_btn_txt = "Publish Now";
								$output .= '<input type="submit" class="pretty-button pretty-button-green" value="Revise Draft" id="wSubDraft" name="wSubDraft"> Save changes as draft and continue writing.<br /><br />';
							}

							$output .= '<input type="submit" class="pretty-button pretty-button-blue" value="' .  $save_btn_txt .'" id="wPublish" name="wPublish"> All edits complete, publish to site.'; 
				
						} else {
				
							$output .= '<input type="submit" class="pretty-button pretty-button-green" value="Save Draft" id="wSubDraft" name="wSubDraft"> Save your first draft, then preview.';
				
						} // post_id
				
				
					$output .= '</fieldset>
					<div class="writestatus">STATUS: <span class="statnow">' .  $wStatus . '</span></div>
			</form>';
		} // show form 
	
		return ($output);
	}	
	 
# -----------------------------------------------------------------
# Edit Link
# -----------------------------------------------------------------


	public function splotwriter_make_edit_link( $post_id, $post_title='') {
		// add a token for editing by using the post title as a trugger
		// ----h/t via http://www.sitepoint.com/generating-one-time-use-urls/
	
		if ( $post_title == '')   $post_title = get_the_title($post_id );
		update_post_meta( $post_id, 'wEditKey', sha1( uniqid( $post_title, true ) ) );
	}

	public function splotwriter_mail_edit_link ( $wid, $mode = 'request' )  {

		// for post id = $wid
		// requested means by click of button vs one sent when published.
	
		// look up the stored edit key 
		$wEditKey = get_post_meta( $wid, 'wEditKey', 1 );

		// While in there get the email address
		$wEmail = get_post_meta( $wid, 'wEmail', 1 );

		// Link for the written thing
		$wLink = get_permalink( $wid );
	
		// who gets mail? They do.
		$to_recipient = $wEmail;

		$wTitle = htmlspecialchars_decode( get_the_title( $wid ) );
	
	
		$edit_instructions = '<p>To be able to edit this work use this special access link <a href="' . get_bloginfo('url') . '/' . $this->splotwriter_get_write_page() . '/?wid=' . $wid . '&tk=' . $wEditKey  . '">' . get_bloginfo('url') . '/' . $this->splotwriter_get_write_page() . '?wid=' . $wid . '&tk=' . $wEditKey  . '</p>It should open your last edited version so you can make any modifications to it. Save this email as a way to always return to edit your writing or use the Request Edit Link button at the bottom of your published work.</p>';
	
		if ( $mode == 'request' ) {
			// subject and message for a edut link request
			$subject ='Edit Link for "' . $wTitle . '"';
		
			$message = '<p>A request was made hopefully by you for the link to edit the content of <a href="' . $wLink . '">' . $wTitle . '</a> published on ' . get_bloginfo( 'name')  . ' at <strong>' . $wLink . '</strong>. (If this was not done by you, just ignore this message)</p>' . $edit_instructions;
		
		} elseif ( $mode == 'draft' ) {		
			// message for a draft notification

			$subject = '"' . $wTitle . '" ' . ' saved as draft';
			$message = '<a href="' . $wLink . '">' . $wTitle . '</a> has been saved as a draft on ' . get_bloginfo( 'name')  . '. </p>' . $edit_instructions;

		
		} else {
			// message for a just been published notification
			$subject = '"' . $wTitle . '" ' . 'is now published';
		
			$message = 'Your writing <a href="' . $wLink . '">' . $wTitle . '</a> has been published on ' . get_bloginfo( 'name')  . ' and is now available at <strong><a href="' . $wLink . '">' . $wLink . '</a></strong>.</p>' . $edit_instructions;
		}

		// turn on HTML mail
		add_filter( 'wp_mail_content_type', 'set_html_content_type' );

		// mail it!
		$mail_sent = wp_mail( $to_recipient, $subject, $message );

		// Reset content-type to avoid conflicts -- http://core.trac.wordpress.org/ticket/23578
		remove_filter( 'wp_mail_content_type', 'set_html_content_type' );	
		
		if ($mode == 'request') {
			if 	($mail_sent) {
				echo 'Instructions sent via email';
			} else {
				echo 'Uh oh email not sent';
			}
		}
	}



# -----------------------------------------------------------------
# Creative Commons Licensing
# -----------------------------------------------------------------

	public function splotwriter_license_html( $license, $author='', $yr='') {

		if ( !isset( $license ) or $license == '' ) return '';

		$all_licenses = $this->splotwriter_get_licences();
		
		// do we have an author?	
		$work_str_html = ($author == '') ? 'This work' : 'This work by ' . $author;
	
		switch ( $license ) {

			case 'copyright': 	
				return $work_str_html . ' is &copy;' . $yr . ' All Rights Reserved';
				break;


			case 'u': 
				return 'The rights of ' . lcfirst($work_str_html) . ' is unknown or not specified.';
				break;
		
			case 'cc0':
		
			return '<a rel="license" href=""http://creativecommons.org/publicdomain/zero/1.0/"><img src="https://i.creativecommons.org/p/zero/1.0/88x31.png" style="border-style: none;" alt="CC0" /></a> To the extent possible under law, all copyright and related or neighboring rights have been waived for ' . lcfirst($work_str_html) .  ' and is shared under a <a href="https://creativecommons.org/publicdomain/zero/1.0/">Creative Commons CC0 1.0 Universal Public Domain Dedication</a>.';
		
				break;
	
			case 'pd':
				return $work_str_html . ' has been explicitly released into the public domain.';
				break;
		
			default:
				return '<a rel="license" href="http://creativecommons.org/licenses/' . $license . '/4.0/"><img alt="Creative Commons ' . $all_licenses[$license] . ' License" style="border-width:0" src="https://i.creativecommons.org/l/' . $license . '/4.0/88x31.png" /></a> ' . $work_str_html  . ' is licensed under a <a rel="license" href="http://creativecommons.org/licenses/' . $license . '/4.0/">Creative Commons ' . $all_licenses[$license] . ' 4.0 International License</a>.';   
		}
	}


	public function cc_license_select_options ($curr) {
		// output for select form options for use in forms

		$str = '';
	
		$licenses = $this->splotwriter_get_licences();
	
		foreach ($licenses as $key => $value) {
			// build the striing of select options
			$selected = ( $key == $curr ) ? ' selected' : '';
			$str .= '<option value="' . $key . '"' . $selected  . '>' . $value . '</option>';
		}
	
		return ($str);
	}
	
	public function splotwriter_get_licences() {
			// return as an array the types of licenses available
	
			return ( array (
						'u' => 'Rights Status Unknown',
						'pd'	=> 'Public Domain',
						'cc0'	=> 'CC0 No Rights Reserved',
						'by' => 'CC-BY Attribution',
						'by-sa' => 'CC-BY-SA Attribution-ShareAlike',
						'by-nd' => 'CC-BY=ND Attribution-NoDerivs',
						'by-nc' => 'CC-BY-NC Attribution-NonCommercial',
						'by-nc-sa' => 'CC-BY-NC-SA Attribution-NonCommercial-ShareAlike',
						'by-nc-nd' => 'CC-BY-NC-ND Attribution-NonCommercial-NoDerivs',
						'copyright' => 'All Rights Reserved (copyrighted)',
					)
				);
	}

	public function splotwriter_the_license( $lcode ) {
			// output the title of a license
			$all_licenses = $this->splotwriter_get_licences();
	
			echo $all_licenses[$lcode];
	}

	public function splotwriter_get_the_license( $lcode ) {
			// return the ttitle of a license
			$all_licenses = $this->splotwriter_get_licences();
	
			return ($all_licenses[$lcode]);
	}

# -----------------------------------------------------------------
# Customizer Setup
# -----------------------------------------------------------------

	public function splotwriter_register_theme_customizer( $wp_customize ) {
		// Create custom panel.
		$wp_customize->add_panel( 'customize_writer', array(
			'priority'       => 500,
			'theme_supports' => '',
			'title'          => __( 'SPLOT Writer', 'splotwriter'),
			'description'    => __( 'Customize the Writer', 'splotwriter'),
		) );
	
		// Add section for display settings
		$wp_customize->add_section( 'write_display' , array(
			'title'    => __('Writer Display','splotwriter'),
			'panel'    => 'customize_writer',
			'priority' => 10
		) );
		// Add section for the collect form
		$wp_customize->add_section( 'write_form' , array(
			'title'    => __('Writing Form','splotwriter'),
			'panel'    => 'customize_writer',
			'priority' => 12
		) );
		// Add setting for default prompt
		$wp_customize->add_setting( 'splotwriter_meta_title', array(
			 'default'           => __( 'Writing Details', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
		// Add control for default prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'splotwriter_meta_title',
				array(
					'label'    => __( 'Title for Metadata Area', 'splotwriter'),
					'priority' => 10,
					'description' => __( 'Text for heading above where the extra details are appended to all items.' ),
					'section'  => 'write_display',
					'settings' => 'splotwriter_meta_title',
					'type'     => 'textarea'
				)
			)
		);
	
		// Add setting for default prompt
		$wp_customize->add_setting( 'default_prompt', array(
			 'default'           => __( 'Enter the content for your writing below. You must save first and preview once before it goes into the system as a draft. After that, continue to edit, save, and preview as much as needed. Remember to click  "Publish Final" when you are done. If you include your email address, we can send you a link that will allow you to make changes later.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Add control for default prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'default_prompt',
				array(
					'label'    => __( 'Default Prompt', 'splotwriter'),
					'priority' => 10,
					'description' => __( 'The opening message greeting above the form.' ),
					'section'  => 'write_form',
					'settings' => 'default_prompt',
					'type'     => 'textarea'
				)
			)
		);
	
	
		// Add setting for re-edit prompt
		$wp_customize->add_setting( 're_edit_prompt', array(
			 'default'           => __( 'You can now edit this previously saved writing, save the changes as a draft, or if done, submit for final publishing.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Add control for re-edit prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			're_edit_prompt',
				array(
					'label'    => __( 'Return Edit Prompt', 'splotwriter'),
					'priority' => 12,
					'description' => __( 'The opening message greeting above the form for a request to edit a previously published item.' ),
					'section'  => 'write_form',
					'settings' => 're_edit_prompt',
					'type'     => 'textarea'
				)
			)
		);
	
		// setting for title label
		$wp_customize->add_setting( 'item_title', array(
			 'default'           => __( 'The Title', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control fortitle label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_title',
				array(
					'label'    => __( 'Title Label', 'splotwriter'),
					'priority' => 16,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_title',
					'type'     => 'text'
				)
			)
		);
	
		// setting for title description
		$wp_customize->add_setting( 'item_title_prompt', array(
			 'default'           => __( 'A good title is important! Create an eye-catching title for your story, one that would make a person who sees it want to stop whatever they are doing and read it.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for title description
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_title_prompt',
				array(
					'label'    => __( 'Title Prompt', 'splotwriter'),
					'priority' => 17,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_title_prompt',
					'type'     => 'textarea'
				)
			)
		);
		// setting for byline label
		$wp_customize->add_setting( 'item_byline', array(
			 'default'           => __( 'How to List Author', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for byline label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_byline',
				array(
					'label'    => __( 'Author Byline Label', 'splotwriter'),
					'priority' => 18,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_byline',
					'type'     => 'text'
				)
			)
		);
		// setting for byline  prompt
		$wp_customize->add_setting( 'item_byline_prompt', array(
			 'default'           => __( 'Publish under your name, twitter handle, secret agent name, or remain "Anonymous". If you include a twitter handle such as @billyshakespeare, when someone tweets your work you will get a lovely notification.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for byline  prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_byline_prompt',
				array(
					'label'    => __( 'Author Byline Prompt', 'splotwriter'),
					'priority' => 19,
					'description' => __( 'Directions for the author entry field' ),
					'section'  => 'write_form',
					'settings' => 'item_byline_prompt',
					'type'     => 'textarea'
				)
			)
		);

		// setting for writing field  label
		$wp_customize->add_setting( 'item_writing_area', array(
			 'default'           => __( 'Writing Area', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for description  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_writing_area',
				array(
					'label'    => __( 'Writing Area Label', 'splotwriter'),
					'priority' => 20,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_writing_area',
					'type'     => 'text'
				)
			)
		);
		// setting for description  label prompt
		$wp_customize->add_setting( 'item_writing_area_prompt', array(
			 'default'           => __( 'Use the editing area below the toolbar to write and format your writing. You can also paste formatted content here (e.g. from MS Word or Google Docs). The editing tool will do its best to preserve standard formatting--headings, bold, italic, lists, footnotes, and hypertext links. Click "Add Media" to upload images to include in your writing or choose from the media already in the media library (click on the tab labelled "media library"). You can also embed audio and video from many social sites simply by putting the URL of the media on a blank line.  Click and drag the icon in the lower right to resize the editing space.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for description  label prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_writing_area_prompt',
				array(
					'label'    => __( 'Writing Area Prompt', 'splotwriter'),
					'priority' => 22,
					'description' => __( 'Directions for the main writing entry field' ),
					'section'  => 'write_form',
					'settings' => 'item_writing_area_prompt',
					'type'     => 'textarea'
				)
			)
		);
		// setting for footer  label
		$wp_customize->add_setting( 'item_footer', array(
			 'default'           => __( 'Additional Information for Footer', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for description  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_footer',
				array(
					'label'    => __( 'Footer Entry Label', 'splotwriter'),
					'priority' => 24,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_footer',
					'type'     => 'text'
				)
			)
		);
		// setting for description  label prompt
		$wp_customize->add_setting( 'item_footer_prompt', array(
			 'default'           => __( 'Add any endnote / credits information you wish to append to the end of your writing, such as a citation to where it was previously published or any other meta information. URLs will be automatically hyperlinked when published.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for description  label prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_footer_prompt',
				array(
					'label'    => __( 'Footer Prompt', 'splotwriter'),
					'priority' => 26,
					'description' => __( 'Directions for the footer entry field' ),
					'section'  => 'write_form',
					'settings' => 'item_footer_prompt',
					'type'     => 'textarea'
				)
			)
		);
		// setting for header image upload label
		$wp_customize->add_setting( 'item_header_image', array(
			 'default'           => __( 'Header Image', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for header image upload  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_header_image',
				array(
					'label'    => __( 'Header Image Upload Label', 'splotwriter'),
					'priority' => 30,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_header_image',
					'type'     => 'text'
				)
			)
		);
		// setting for header image upload prompt
		$wp_customize->add_setting( 'item_header_image_prompt', array(
			 'default'           => __( 'You can upload any image file to be used in the header or choose from ones that have already been added to the site. Ideally this image should be at least 1440px wide for photos. Any uploaded image should either be your own or one licensed for re-use; provide an attribution credit for the image in the caption field below.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for image upload prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_header_image_prompt',
				array(
					'label'    => __( 'Header Image Upload Prompt', 'splotwriter'),
					'priority' => 32,
					'description' => __( 'Directions for image uploads' ),
					'section'  => 'write_form',
					'settings' => 'item_header_image_prompt',
					'type'     => 'textarea'
				)
			)
		);
	
	
		// setting for header image caption label
		$wp_customize->add_setting( 'item_header_caption', array(
			 'default'           => __( 'Caption/Credits for Header Image', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for header image caption   label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_header_caption',
				array(
					'label'    => __( 'Header Image Caption Label', 'splotwriter'),
					'priority' => 34,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_header_caption',
					'type'     => 'text'
				)
			)
		);
		// setting for header image caption   label prompt
		$wp_customize->add_setting( 'item_header_caption_prompt', array(
			 'default'           => __( 'Provide full credit / attribution for the header image.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for header image caption   label prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_header_caption_prompt',
				array(
					'label'    => __( 'Header Image Caption Prompt', 'splotwriter'),
					'priority' => 36,
					'description' => __( 'Directions for the header caption field' ),
					'section'  => 'write_form',
					'settings' => 'item_header_caption_prompt',
					'type'     => 'textarea'
				)
			)
		);	
	
		// setting for categories  label
		$wp_customize->add_setting( 'item_categories', array(
			 'default'           => __( 'Kind of Writing', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for categories  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_categories',
				array(
					'label'    => __( 'Categories Label', 'splotwriter'),
					'priority' => 40,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_categories',
					'type'     => 'text'
				)
			)
		);
		// setting for categories  prompt
		$wp_customize->add_setting( 'item_categories_prompt', array(
			 'default'           => __( 'Check as many that apply.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for categories prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_categories_prompt',
				array(
					'label'    => __( 'Categories Prompt', 'splotwriter'),
					'priority' => 42,
					'description' => __( 'Directions for the categories selection' ),
					'section'  => 'write_form',
					'settings' => 'item_categories_prompt',
					'type'     => 'textarea'
				)
			)
		);
		
		// setting for tags  label
		$wp_customize->add_setting( 'item_tags', array(
			 'default'           => __( 'Tags', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for tags  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_tags',
				array(
					'label'    => __( 'Tags Label', 'splotwriter'),
					'priority' => 44,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_tags',
					'type'     => 'text'
				)
			)
		);
		// setting for tags  prompt
		$wp_customize->add_setting( 'item_tags_prompt', array(
			 'default'           => __( 'Add any descriptive tags for your writing. Separate multiple ones with commas.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for tags prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_tags_prompt',
				array(
					'label'    => __( 'Tags Prompt', 'splotwriter'),
					'priority' => 46,
					'description' => __( 'Directions for tags entry' ),
					'section'  => 'write_form',
					'settings' => 'item_tags_prompt',
					'type'     => 'textarea'
				)
			)
		);	
	
		// setting for email address  label
		$wp_customize->add_setting( 'item_email', array(
			 'default'           => __( 'Your Email Address', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for email address  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_email',
				array(
					'label'    => __( 'Email Address Label', 'splotwriter'),
					'priority' => 50,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_email',
					'type'     => 'text'
				)
			)
		);
		// setting for email address  prompt
		$wp_customize->add_setting( 'item_email_prompt', array(
			 'default'           => __( 'If you provide an email address when your writing is published, you can request a special link that will allow you to edit it again in the future.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for email address prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_email_prompt',
				array(
					'label'    => __( 'Email Address Prompt', 'splotwriter'),
					'priority' => 52,
					'description' => __( 'Directions for email address entry' ),
					'section'  => 'write_form',
					'settings' => 'item_email_prompt',
					'type'     => 'textarea'
				)
			)
		);		
	
		// setting for editor notes  label
		$wp_customize->add_setting( 'item_editor_notes', array(
			 'default'           => __( 'Extra Information for Editors', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for editor notes  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_editor_notes',
				array(
					'label'    => __( 'Editor Notes Label', 'splotwriter'),
					'priority' => 54,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_editor_notes',
					'type'     => 'text'
				)
			)
		);
		// setting for editor notes  prompt
		$wp_customize->add_setting( 'item_editor_notes_prompt', array(
			 'default'           => __( 'This information will *not* be published with your work, it is informational for the editor use only.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for editor notes prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_editor_notes_prompt',
				array(
					'label'    => __( 'Editor Notes Prompt', 'splotwriter'),
					'priority' => 56,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_editor_notes_prompt',
					'type'     => 'textarea'
				)
			)
		);	
	
		// setting for license  label
		$wp_customize->add_setting( 'item_license', array(
			 'default'           => __( 'Rights / Resuse License', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	
		// Control for license  label
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_license',
				array(
					'label'    => __( 'Rights Label', 'splotwriter'),
					'priority' => 27,
					'description' => __( '' ),
					'section'  => 'write_form',
					'settings' => 'item_license',
					'type'     => 'text'
				)
			)
		);
		// setting for license  prompt
		$wp_customize->add_setting( 'item_license_prompt', array(
			 'default'           => __( 'Choose your preferred reuse option.', 'splotwriter'),
			 'type' => 'theme_mod',
			 'sanitize_callback' => 'sanitize_text'
		) );
	

		// Control for license prompt
		$wp_customize->add_control( new WP_Customize_Control(
			$wp_customize,
			'item_license_prompt',
				array(
					'label'    => __( 'Image Source Prompt', 'splotwriter'),
					'priority' => 28,
					'description' => __( 'Directions for the rights selection' ),
					'section'  => 'write_form',
					'settings' => 'item_license_prompt',
					'type'     => 'textarea'
				)
			)
		);
		
		// Sanitize text
		function sanitize_text( $text ) {
			return sanitize_text_field( $text );
		}
	}

	// layout settings
	public function splotwriter_layout_width() {
		 if ( get_theme_mod( 'layout_width') != "" ) {
			$thewidth = ( get_theme_mod( 'layout_width') == 'wide' ) ? '' : get_theme_mod( 'layout_width'); 	
			echo $thewidth;
		 }	else {
			echo 'thin';
		 }
	}
	public function splotwriter_form_default_prompt() {
		 if ( get_theme_mod( 'default_prompt') != "" ) {
			return get_theme_mod( 'default_prompt');
		 }	else {
			return 'Enter the content for your writing below. You must save first and preview once before it goes into the system as a draft. After that, continue to edit, save, and preview as much as needed. Remember to click  "Publish Final" when you are done. If you include your email address, we can send you a link that will allow you to make changes later.';
		 }
	}
	public function splotwriter_form_re_edit_prompt() {
		 if ( get_theme_mod( 're_edit_prompt') != "" ) {
			return get_theme_mod( 're_edit_prompt');
		 }	else {
			return 'You can now edit this previously saved writing, save the changes as a draft, or if done, submit for final publishing.';
		 }
	}
	public function splotwriter_form_item_title() {
		 if ( get_theme_mod( 'item_title') != "" ) {
			return get_theme_mod( 'item_title');
		 }	else {
			return 'The Title';
		 }
	}
	public function splotwriter_form_item_title_prompt() {
		 if ( get_theme_mod( 'item_title_prompt') != "" ) {
			return get_theme_mod( 'item_title_prompt');
		 }	else {
			return 'A good title is important! Create an eye-catching title for your story, one that would make a person who sees it want to stop whatever they are doing and read it.';
		 }
	}
	public function splotwriter_form_item_byline() {
		 if ( get_theme_mod( 'item_byline') != "" ) {
			return get_theme_mod( 'item_byline');
		 }	else {
			return 'How to List Author';
		 }
	}
	public function splotwriter_form_item_byline_prompt() {
		 if ( get_theme_mod( 'item_byline_prompt') != "" ) {
			return get_theme_mod( 'item_byline_prompt');
		 }	else {
			return 'Publish under your name, twitter handle, secret agent name, or remain "Anonymous". If you include a twitter handle such as @billyshakespeare, when someone tweets your work you will get a lovely notification.';
		 }
	}
	public function splotwriter_form_item_header_image() {
		 if ( get_theme_mod( 'item_header_image') != "" ) {
			return get_theme_mod( 'item_header_image');
		 }	else {
			return 'Header Image';
		 }
	}
	public function splotwriter_form_item_header_image_prompt() {
		 if ( get_theme_mod( 'item_header_image_prompt') != "" ) {
			return get_theme_mod( 'item_header_image_prompt');
		 }	else {
			return 'You can upload any image file to be used in the header or choose from ones that have already been added to the site. Ideally this image should be at least 1440px wide for photos. Any uploaded image should either be your own or one licensed for re-use; provide an attribution credit for the image in the caption field below.';
		 }
	}
	public function splotwriter_form_item_header_caption() {
		 if ( get_theme_mod( 'item_header_caption') != "" ) {
			return get_theme_mod( 'item_header_caption');
		 }	else {
			return 'Caption/Credits for Header Image';
		 }
	}
	public function splotwriter_form_item_header_caption_prompt() {
		 if ( get_theme_mod( 'item_header_caption_prompt') != "" ) {
			return get_theme_mod( 'item_header_caption_prompt');
		 }	else {
			return 'Provide full credit / attribution for the header image.';
		 }
	}

	public function splotwriter_form_item_writing_area() {
		 if ( get_theme_mod( 'item_writing_area') != "" ) {
			return get_theme_mod( 'item_writing_area');
		 }	else {
			return 'Writing Area';
		 }
	}
	public function splotwriter_form_item_writing_area_prompt() {
		 if ( get_theme_mod( 'item_writing_area_prompt') != "" ) {
			return get_theme_mod( 'item_writing_area_prompt');
		 }	else {
			return 'Use the editing area below the toolbar to write and format your writing. You can also paste formatted content here (e.g. from MS Word or Google Docs). The editing tool will do its best to preserve standard formatting--headings, bold, italic, lists, footnotes, and hypertext links. Click "Add Media" to upload images to include in your writing or choose from the media already in the media library (click on the tab labelled "media library"). You can also embed audio and video from many social sites simply by putting the URL of the media on a blank line.  Click and drag the icon in the lower right to resize the editing space.';
		 }
	}
	public function splotwriter_form_item_footer() {
		 if ( get_theme_mod( 'item_footer') != "" ) {
			return get_theme_mod( 'item_footer');
		 }	else {
			return 'Additional Information for Footer';
		 }
	}
	public function splotwriter_form_item_footer_prompt() {
		 if ( get_theme_mod( 'item_footer_prompt') != "" ) {
			return get_theme_mod( 'item_footer_prompt');
		 }	else {
			return 'Add any endnote / credits information you wish to append to the end of your writing, such as a citation to where it was previously published or any other meta information. URLs will be automatically hyperlinked when published.';
		 }
	}
	public function splotwriter_form_item_license() {
		 if ( get_theme_mod( 'item_license') != "" ) {
			return get_theme_mod( 'item_license');
		 }	else {
			return 'Rights / Resuse License';
		 }
	}
	public function splotwriter_form_item_license_prompt() {
		 if ( get_theme_mod( 'item_license_prompt') != "" ) {
			return get_theme_mod( 'item_license_prompt');
		 }	else {
			return 'Choose your preferred reuse option.';
		 }
	}
	public function splotwriter_form_item_categories() {
		 if ( get_theme_mod( 'item_categories') != "" ) {
			return get_theme_mod( 'item_categories');
		 }	else {
			return 'Kind of Writing';
		 }
	}
	public function splotwriter_form_item_categories_prompt() {
		 if ( get_theme_mod( 'item_categories_prompt') != "" ) {
			return get_theme_mod( 'item_categories_prompt');
		 }	else {
			return 'Check as many that apply.';
		 }
	}
	public function splotwriter_form_item_tags() {
		 if ( get_theme_mod( 'item_tags') != "" ) {
			return get_theme_mod( 'item_tags');
		 }	else {
			return 'Tags';
		 }
	}
	public function splotwriter_form_item_tags_prompt() {
		 if ( get_theme_mod( 'item_tags_prompt') != "" ) {
			return get_theme_mod( 'item_tags_prompt');
		 }	else {
			return 'Add any descriptive tags for your writing. Separate multiple ones with commas.';
		 }
	}
	public function splotwriter_form_item_email() {
		 if ( get_theme_mod( 'item_email') != "" ) {
			return get_theme_mod( 'item_email');
		 }	else {
			return 'Your Email Address';
		 }
	}
	public function splotwriter_form_item_email_prompt() {
		 if ( get_theme_mod( 'item_email_prompt') != "" ) {
			return get_theme_mod( 'item_email_prompt');
		 }	else {
			return 'If you provide an email address when your writing is published, you can request a special link that will allow you to edit it again in the future.';
		 }
	}
	
	public function splotwriter_form_item_editor_notes() {
		 if ( get_theme_mod( 'item_editor_notes') != "" ) {
			return get_theme_mod( 'item_editor_notes');
		 }	else {
			return 'Extra Information for Editors';
		 }
	}
	public function splotwriter_form_item_editor_notes_prompt() {
		 if ( get_theme_mod( 'item_editor_notes_prompt') != "" ) {
			return get_theme_mod( 'item_editor_notes_prompt');
		 }	else {
			return 'This information will *not* be published with your work, it is only to sent to the editor of ' . get_bloginfo('name') . '.';
		 }
	}
	public function splotwriter_meta_title() {
		 if ( get_theme_mod( 'splotwriter_meta_title') != "" ) {
			return get_theme_mod( 'splotwriter_meta_title');
		 }	else {
			return 'Writing Details';
		 }
	}


# -----------------------------------------------------------------
# Useful spanners and wrenches
# -----------------------------------------------------------------

	public function splotwriter_get_write_page() {
		// return slug for page set in theme options for writing page (newer versions of SPLOT)
	
		if ( splotwriter_option( 'write_page' ) )  {
			return ( get_post_field( 'post_name', get_post( splotwriter_option( 'write_page' ) ) ) ); 
		} else {
			// older versions of SPLOT use the slug
			return ('write');
		}
	}


	public function splotwriter_get_desk_page() {
		// return slug for page set in theme options for writing page (newer versions of SPLOT)
	
		if (  splotwriter_option( 'desk_page' ) ) {
			return ( get_post_field( 'post_name', get_post( splotwriter_option( 'desk_page' ) ) ) ); 
		} else {
			// older versions of SPLOT use the slug
			return ('desk');
		}
	}


	public function splot_get_twitter_name( $str ) {
		// takes an author string and extracts a twitter handle if there is one 
	
		$found = preg_match('/@(\\w+)\\b/i', '$str', $matches);
	
		if ($found) {
			return $matches[0];
		} else {
			return false;
		}
	}

	public function splotwriter_get_reading_time( $prefix_string, $suffix_string ) {
		// return the estimated reading time only if the short code (aka plugin) exists. 
		// Start with the prefix string add an approximation symbol and append suffix

		if ( shortcode_exists( 'rt_reading_time' ) ) {		
			return ( $prefix_string . ' ~' . do_shortcode( '[rt_reading_time postfix="minutes" postfix_singular="minute"]' ) . $suffix_string );
		}
	}

	public function splotwriter_check_user( $allowed='writer' ) {
		// checks if the current logged in user is who we expect
	   $current_user = wp_get_current_user();
	
		// return check of match
		return ( $current_user->user_login == $allowed );
	}


	public function twitternameify( $str ) {
		// convert any "@" in astring to a linked twitter name
		// ----- h/t http://snipe.net/2009/09/php-twitter-clickable-links/
		$str = preg_replace( "/@(\w+)/", "<a href=\"https://www.twitter.com/\\1\" target=\"_blank\">@\\1</a>", $str );

		return $str;
	}

	public function splotwriter_preview_notice() {
		return ('<div class="notify"><span class="symbol icon-info"></span>
	This is a preview of your entry that shows how it will look when published. <a href="#" onclick="self.close();return false;">Close this window/tab</a> when done to return to the writing form. Make any changes and click "Revise Draft" again or if it is ready, click "Publish Now".		
					</div>');
	}
	
	public function splotwriter_allowed_email_domain( $email ) {
		// checks if an email address is within a list of allowed domains
		
		// extract domain h/t https://www.fraudlabspro.com/resources/tutorials/how-to-extract-domain-name-from-email-address/
		$domain = substr($email, strpos($email, '@') + 1);
		

		$allowables = explode(",", splotwriter_option('email_domains'));
		
		foreach ( $allowables as $item) {
			if ( $domain == trim($item)) return true;
		}
		
		return false;
	}

	public function splot_the_author() {
		// utility to put in template to show status of special logins
		// nothing is printed if there is not current user, 
		//   echoes (1) if logged in user is the special account
		//   echoes (0) if logged in user is the another account
		//   in both cases the code is linked to a logout script

	
		if ( is_user_logged_in() and !current_user_can( 'edit_others_posts' ) )  {
	
			$user_code = ( $this->splotwriter_check_user() ) ? 1 : 0;
			
			return ('<a href="' . wp_logout_url( site_url() ). '">(' . $user_code  .')</a>');
		}  else {
			// for admins, display an indicator on the active write page
			if ( is_page( $this->splotwriter_get_write_page() ) )  return  '(w)';
		}
	}
	


	public function br2nl ( $string )
	// convert HTML <br> tags to new lines
	// from http://php.net/manual/en/function.nl2br.php#115182
	{
		return preg_replace('/\<br(\s*)?\/?\>/i', PHP_EOL, $string);
	}

	public function make_links_clickable( $text ) {
	//----	h/t http://stackoverflow.com/a/5341330/2418186
		return preg_replace('!(((f|ht)tp(s)?://)[-a-zA-Zа-яА-Я()0-9@:%_+.~#?&;//=]+)!i', '<a href="$1">$1</a>', $text);
	}

	// -----  expose post meta date to API
	public function splotwriter_create_api_posts_meta_field() {
 
		register_rest_field( 'post', 'splot_meta', array(
									 'get_callback' => array( $this, 'splotwriter_get_splot_meta_for_api' ), 
									 'schema' => null,)
		);
	}
 
	public function splotwriter_get_splot_meta_for_api( $object ) {
		//get the id of the post object array
		$post_id = $object['id'];

		// meta data fields we wish to make available
		$splot_meta_fields = ['author' => 'wAuthor', 'license' => 'wLicense', 'footer' => 'wFooter'];
	
		// array to hold stuff
		$splot_meta = [];
 
		foreach ($splot_meta_fields as $meta_key =>  $meta_value) {
			//return the post meta for each field
			$splot_meta[$meta_key] =  get_post_meta( $post_id, $meta_value, true );
		 }
	 
		 return ($splot_meta);
	} 

	// function to get the caption for an attachment (stored as post_excerpt)
	// -- h/t http://wordpress.stackexchange.com/a/73894/14945
	 public function get_attachment_caption_by_id( $post_id ) {
		$the_attachment = get_post( $post_id );
		return ( $the_attachment->post_excerpt ); 
	}	
}