<?php

function splotwriter_setup() { 

	// make sure our categories are present, accounted for, named
	wp_insert_term( 'In Progress', 'category' );
	wp_insert_term( 'Published', 'category' );

	// Look for existence of pages with the appropriate template, if not found
	// make 'em cause it's good to make the pages

	if ( !count( splotwriter_find_pages ('[writerform]' ) ) ) {

		// create the writing form page if it does not exist
		// backdate creation date 2 days just to make sure they do not end up future dated
		// which causes all kinds of disturbances in the force
	
		$page_data = array(
			'post_title' 	=> 'Write? Write. Right.',
			'post_content'	=> 'Here is the place to compose, preview, and hone your fine words. If you are building this site, maybe edit this page to customize this wee bit of text. But leave this shortcode' . "\n\n" . '[writerform]' ,
			'post_name'		=> 'write',
			'post_status'	=> 'publish',
			'post_type'		=> 'page',
			'post_author' 	=> 1,
			'post_date' 	=> date('Y-m-d H:i:s', time() - 172800),
		);

		wp_insert_post( $page_data );
	}

	if ( !count( splotwriter_find_pages ('[writerdesk]' ) ) ) {

		// create the welcome entrance to the tool if it does not exist
		// backdate creation date 2 days just to make sure they do not end up future dated
	
		$page_data = array(
			'post_title' 	=> 'Welcome Desk',
			'post_content'	=> 'You are but one special key word away from being able to write. Hopefully the kind owner of this site has provided you the key phrase. Spelling and capitalization do count. If you are said owner, editing this page will let you personalize this bit. ' . "\n\n" . '[writerdesk]' ,
			'post_name'		=> 'desk',
			'post_status'	=> 'publish',
			'post_type'		=> 'page',
			'post_author' 	=> 1,
			'post_date' 	=> date('Y-m-d H:i:s', time() - 172800),
		);

		wp_insert_post( $page_data );
		
		
	}
	
	// flush rewrite rules
	flush_rewrite_rules();	
}

function splotwriter_find_pages ( $pattern = '[]', $for_menu = false ) {
	/* find all pages with pattern passed (meant for finding desk and writing form 
	   pages based on shortcode passed                             */

	// get all pages
	$seekpages = get_pages(); 

	// if we are building a menu, insert the menu selector
	$foundpages = ($for_menu) ?  array(0 => 'Select Page') : array();

	foreach ( $seekpages as $p ) {
		if ( strpos( $p->post_content, $pattern ) !== false) {
			$foundpages[$p->ID] = $p->post_title;
		}
	}

	return ($foundpages);
}

function reading_time_check() {
// checks for installation of Reading Time WP plugin https://wordpress.org/plugins/reading-time-wp/

	if ( shortcode_exists( 'rt_reading_time' ) ) {
		// yep, golden
		return ('The Reading Time WP plugin is installed. No further action necessary.');
	} else {
		// nope, send them off to set it up
		return ('The <a href="https://wordpress.org/plugins/reading-time-wp/" target="_blank">The Reading Time WP plugin</a> is NOT installed. You might want it-- it\'s not needed, but it\'s nifty.  <a href="' . admin_url( 'plugins.php') . '">Do it now!</a>');
	}
}

function splotwriter_author_user_check( $expected_user = 'writer' ) {
	// checks for the proper authoring account set up

		$auser = get_user_by( 'login', $expected_user );
		
		if ( !$auser) {
			return ('The Authoring account not set up. You need to <a href="' . admin_url( 'user-new.php') . '">create a user account</a> with login name <strong>' . $expected_user . '</strong> with a role of <strong>Author</strong>. Make a killer strong password; no one uses it. Not even you.');
		} elseif ( $auser->roles[0] != 'author') {
	
			// for multisite let's check if user is not member of blog
			if ( is_multisite() AND !is_user_member_of_blog( $auser->ID, get_current_blog_id() ) )  {
				return ('The user account <strong>' . $expected_user . '</strong> is set up but it has not been added as a user to this site (and needs to have a role of <strong>Author</strong>). You can <a href="' . admin_url( 'user-edit.php?user_id=' . $auser->ID ) . '">edit the account now</a>'); 
			
			} else {
		
				return ('The user account <strong>' . $expected_user . '</strong> is set up but needs to have it\'s role set to <strong>Author</strong>. You can <a href="' . admin_url( 'user-edit.php?user_id=' . $auser->ID ) . '">edit it now</a>'); 
			}	
		
		} else {
			return ('The authoring account <strong>' . $expected_user . '</strong> is correctly set up.');
		}
}

function splotwriter_get_licences() {
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

