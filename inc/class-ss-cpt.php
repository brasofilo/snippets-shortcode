<?php
/**
 * Register Snippet post type and render Textarea Field after the Title
 *
 * CPT is hierarchical 
 * CPT menu shows up inside Post post type (cool!)
 * Custom Metabox for CPT is left empty, can be used as Language Selector
 *
 */

class B5F_SS_Cpt
{
    /* Shortcut */
    private $post_type;

    /**
     * Start up
     */
	public function __construct()
	{
        $this->post_type = B5F_Snippets_Shortcode::get_instance()->post_type;
        add_action( 'init', array( $this, '_cpt' ) );
		add_action( 'edit_form_after_title', array( $this, 'input_text_area' ) );        
	}
	
    
    /**
     * Register CPT
     * 
     * `show_in_menu` is set to Posts screen
     * this way, our CPT goes straight into a submenu, no hacks needed
     * 
     * `register_meta_box_cb` is used to enqueue styles/scripts only in our CPT page
     * 
     */
	public function _cpt() 
	{
		$labels = array(
		    'menu_name' 		 => 'Snippets',
		    'singular_name' 	 => 'Snippet',
		    'add_new' 			 => 'Add snippet',
		    'add_new_item' 		 => 'Add new snippet',
		    'edit_item' 		 => 'Edit snippet',
		    'new_item' 		 	 => 'New snippet',
		    'view_item' 		 => 'View snippet',
		    'search_items' 		 => 'Search snippet',
		    'not_found' 		 => 'Snippet not fount',
		    'not_found_in_trash' => 'No snippet in trash',
		    'parent_item_colon'  => 'Parent snippet',
		);
		$args = array(
		    'labels'             => $labels,
		    'public'             => false,
		    'publicly_queryable' => false,
		    'show_ui'            => true,
		    'show_in_menu'       => 'edit.php',
		    'query_var'          => true,
		    'rewrite'            => false,
		    'capability_type'    => 'post',
		    'has_archive'        => false,
		    'hierarchical'       => true,
		    'menu_position'      => null,
		    'supports'           => array( 'title', 'page-attributes' ),
			'register_meta_box_cb' => array( $this, '_meta_box' )
		);
		register_post_type( $this->post_type, $args );
    }

    
    /**
     * Enqueue only in our CPT screen
     */
    public function _meta_box() 
    { 
        add_action( "admin_print_scripts", array( $this, 'codemirror_js' ) );
        add_action( "admin_print_styles", array( $this, 'codemirror_css' ) );
        add_action( 'admin_head', array( B5F_Snippets_Shortcode::get_instance(), 'mini_css' ) );
        add_meta_box(
	        'snippets_configs',
	        __( 'Config' ), 
	        array( $this, '_print_meta_box' ),
	        $this->post_type,
			'side'
	    );
    }
    
    
    /**
     * Echo meta box content
     * 
     * @param object $post
     */
    public function _print_meta_box( $post )
    {
        include_once 'html-meta-box.php';
    }
    
    
    /**
     * Enqueue JS
     * 
     * @global object $post
     */
    public function codemirror_js()
    {
        if( $this->break_execution() )
            return;
        global $post;
        $cm_theme = get_post_meta( $post->ID, '_select_theme', true);
        $cm_mode = get_post_meta( $post->ID, '_select_language', true);
        B5F_Snippets_Shortcode::get_instance()->enqueue_codemirror_js( $cm_theme, $cm_mode );
    }

    /**
     * Enqueue CSS
     */
    public function codemirror_css()
    {
        if( $this->break_execution() )
            return;
        
        B5F_Snippets_Shortcode::get_instance()->enqueue_codemirror_css();
    }
    
    
    /**
     * Disable CodeMirror in the backend
     * 
     * @global object $post
     * @return boolean
     */
    private function break_execution()
    {
        global $post;
        return( get_post_meta( $post->ID, '_select_disable', true) );
    }
    


    /**
     * Echo TextArea for code snippets paste/edit
     * 
     * @param object $post
     * @return string
     */
    public function input_text_area( $post )
    {
		if( $this->post_type != $post->post_type )
			return;
        
		$option = ( $get = get_post_meta( $post->ID, '_snippet_code', true ) ) ? $get : '';
		wp_nonce_field( B5F_Snippets_Shortcode::get_instance()->plugin_url, '_nonce_snippets' );
		printf(
			'<h3>%s</h3><textarea id="snipp" name="snipp" cols="90" rows="25" class="widefat">%s</textarea>',
			'',//__( 'Code' ),
			esc_html( $option )
		);
    }

}

