<?php
/**
 * Plugin Name: Snippets Shortcode
 * Plugin URI: https://github.com/brasofilo/snippets-shortcode
 * Description: Add code snippets as a Custom Post Type. Display in regular posts and pages using a Shortcode. Uses CodeMirror on backend and CM/WP-Syntax in frontend. 
 * Version: 2013.10.12
 * Author: Rodolfo Buaiz
 * Author URI: http://brasofilo.com
 * License: GPLv2 or later
 *  
 *
 * 
 * This program is free software; you can redistribute it and/or modify it 
 * under the terms of the GNU General Public License version 2, 
 * as published by the Free Software Foundation.  You may NOT assume 
 * that you can use any other version of the GPL.
 *
 * This program is distributed in the hope that it will be useful, 
 * but WITHOUT ANY WARRANTY; without even the implied warranty 
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 */

/**
 * Based on Plugin Class Demo
 * https://gist.github.com/toscho/3804204
 */
add_action(
	'plugins_loaded',
	array ( B5F_Snippets_Shortcode::get_instance(), 'plugin_setup' )
);

/**
 * Main class
 *
 * Fires all classes
 * Handles save_post action for other classes
 *
 */
class B5F_Snippets_Shortcode 
{
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;
	
    
    /**
     * Snippets Custom Post Type name
     * @type string
     */
	public $post_type = 'snippet';
    
    
    /**
     * CodeMirror languages to prettify
     * @type array
     */
    public $cm_languages = array( 'php', 'css', 'htmlmixed', 'javascript','xml', 'markdown', 'sql' );
    
    
    /**
     * CodeMirror languages to enqueue
     * @type array 
     */
    public $modes = array('xml','clike','javascript','css','htmlmixed','css','php', 'markdown');
    
    
    /**
     * CodeMirror add-ons to enqueue
     * @type array 
     */
    public $add_ons = array(
        'active-line'   => 'selection',
        'matchbrackets' => 'edit',
        'closebrackets' => 'edit',
        'closetag'      => 'edit', 	
        'foldcode'      => 'fold', 	
        'foldgutter'    => 'fold', 	
        'brace-fold'    => 'fold', 	
        'xml-fold'      => 'fold', 	
        'comment-fold'  => 'fold', 
        'searchcursor'      => 'search', 	
        'match-highlighter'  => 'search', 
        'matchtags'      => 'edit', 	
    );
    
    
    /**
     * Themes to use with CodeMirror
     * @type array
     */
    public $cm_themes = array( '3024-day', '3024-night', 'ambiance-mobile', 'ambiance', 'base16-dark', 'base16-light', 'blackboard', 'cobalt', 'eclipse', 'elegant', 'erlang-dark', 'lesser-dark', 'midnight', 'monokai', 'neat', 'night', 'paraiso-dark', 'paraiso-light', 'rubyblue', 'solarized', 'the-matrix', 'tomorrow-night-eighties', 'twilight', 'vibrant-ink', 'xq-dark', 'xq-light' );

    
    /**
     * This plugin URL
     * @type string
     */
    public $plugin_url;

    
    /**
     * Codemirror scripts URL
     * @type string
     */
    public $cm_url;

    
	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.10
	 * @return  void
	 */
	public function plugin_setup()
	{
        $this->plugin_url = plugins_url( '/', __FILE__ );
        $this->cm_url = plugins_url( '/js/codemirror/', __FILE__ );
        $this->load_extensions();
		new B5F_SS_Cpt();
		new B5F_SS_Posts_Pages_Metaboxes();
		new B5F_SS_Shortcode();
        
		add_action( 'save_post', array( $this, '_save_post' ), 10, 2 );
        
		include_once 'inc/plugin-update-checker.php';
		new PluginUpdateCheckerB(
			'https://raw.github.com/brasofilo/snippets-shortcode/master/inc/update.json',
			__FILE__,
			'snippets-shortcode-master'
		);
		
		add_filter( 'upgrader_source_selection', array( $this, 'rename_github_zip' ), 1, 3);
        
        add_filter( 'plugin_row_meta', array( $this, 'donate_link' ), 10, 4 );
	}

    
	/**
	 * Constructor. Intentionally left empty and public.
	 *
	 * @see plugin_setup()
	 * @since 2012.09.12
	 */
    public function __construct() {}

    
	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   2012.09.13
	 * @return  object of this class
	 */
    public function get_instance() 
	{
		NULL === self::$instance and self::$instance = new self;
		return self::$instance;
    }

    
    /**
     * Handles both CPT and regular Types post meta saving
     * 
     * @param int $post_id
     * @param objec $post_object
     * @return void
     */
	public function _save_post( $post_id, $post_object ) 
	{
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )  
			return;
        
        $nonce_check = B5F_Snippets_Shortcode::get_instance()->plugin_url;
		if ( 
			!isset( $_POST['_nonce_snippets'] ) 
			|| !wp_verify_nonce( $_POST['_nonce_snippets'], $nonce_check ) 
			)
			return;
			
        $cpts = apply_filters( 'ss_show_shortcodes_in_cpts', array( 'post', 'page' ) );
        
		switch( $post_object->post_type )
		{
			case $this->post_type:
                foreach( $this->get_plugin_meta_data('snippets') as $pd => $name )
                {
                    if( isset( $_POST[ $pd ] ) )
                        update_post_meta( $post_id, $name['meta'], $_POST[ $pd ] );
                    elseif( $name['del'] )
                        delete_post_meta( $post_id, $name['meta'], $_POST[ $pd ] );
                }
			break;
            
            case in_array( $post_object->post_type, $cpts ):
                $new_input = array();
                foreach( $this->get_plugin_meta_data('_snippet_shortcode') as $pd => $name )
                {
                    if( isset( $_POST['_snippet_shortcode'][ $pd ] ) ) 
                        $new_input[ $pd ] = $_POST['_snippet_shortcode'][ $pd ];
                }
                update_post_meta( $post_id, '_snippet_shortcode', $new_input );
			break;
		}
	}
    
 
    /**
	 * Removes the prefix "-master" when updating from GitHub zip files
	 * 
	 * See: https://github.com/YahnisElsts/plugin-update-checker/issues/1
	 * 
	 * @param string $source
	 * @param string $remote_source
	 * @param object $thiz
	 * @return string
	 */
	public function rename_github_zip( $source, $remote_source, $thiz )
	{
		if(  strpos( $source, 'snippets-shortcode') === false )
			return $source;

		$path_parts = pathinfo($source);
		$newsource = trailingslashit($path_parts['dirname']). trailingslashit('snippets-shortcode');
		rename($source, $newsource);
		return $newsource;
	}


    /**
     * Enqueues CodeMirror scripts
     * 
     * Called by the CPT and by the Shortcode
     * 
     * @param string $cm_theme Theme selection
     * @param string $cm_mode Language selection
     */
    public function enqueue_codemirror_js( $cm_theme, $cm_mode, $args = null )
    {
        $handles = array();
        
        wp_register_script( 'cm-js',     "{$this->cm_url}lib/codemirror.js" );
        wp_register_script( 'cm-keymap', "{$this->cm_url}keymap/extra.js" );
        
        foreach ( $this->modes as $mode )
        {
            $handles[] = "mode-$mode";
            wp_register_script( "mode-$mode", "{$this->cm_url}mode/$mode/$mode.js" );
        }
        
        foreach( $this->add_ons as $file => $folder )
        {
            $handles[] = "cm-add-$file";
            wp_register_script( "cm-add-$file", "{$this->cm_url}addon/$folder/$file.js" );
        }
        
        $registered = array( 'cm-js', 'cm-keymap', 'jquery' );
        $dependencies = array_merge( $registered, $handles );
        
        wp_enqueue_script( 
            'ss-cm', 
             "{$this->plugin_url}js/codemirror-config.js", 
            $dependencies
        );
             
        # Passed by the shortcode
        $front_end = array();
        if( $args )
            foreach( $args as $key => $value )
                $front_end[$key] = $value;

        $defaults = array( 
            'theme'=> !empty($cm_theme) ? $cm_theme : 'default',
            'mode'=> !empty($cm_mode) ? $cm_mode : 'php',
            'is_admin' => !is_admin(),
            'linenumbers' => true,
            'fold' => true,
        ) ;
        wp_localize_script( 
            'ss-cm', 
            'b5f_cm_config', 
             array_merge( $defaults, $front_end )
        );

    }
    
    
    /**
     * Enqueues CodeMirror styles
     * 
     * Called by the CPT and by the Shortcode
     * The shortcode also passes the skin to load
     * in the backend, all styles are loaded
     * 
     * @param string $skin Theme selection
     */
    public function enqueue_codemirror_css( $skin = '' )
    { 
        wp_enqueue_style( 'cm-css', $this->cm_url . 'lib/codemirror.css' );
        wp_enqueue_style( 'cm-fold', $this->cm_url . 'addon/fold/foldgutter.css' );
        
        if( !empty( $skin ) )
            wp_enqueue_style( "cm-$skin", $this->cm_url . "theme/$skin.css" );
        else
            foreach( apply_filters( 'ss_codemirror_themes', $this->cm_themes ) as $theme )
                wp_enqueue_style( "cm-$theme", $this->cm_url . "theme/$theme.css" );
    }

    
    /**
     * CodeMirror CSS and mine
     */
    public function mini_css()
    {
        if( !is_admin() && !$this->has_shortcode('snippet') )
            return;
        ?>
<!-- Snippets Shortcode MiniCSS -->
<style>
.CodeMirror {
    border: 1px solid #eee;
    height: auto;/*  Important for frontend */
}
.CodeMirror-scroll {
    overflow-y: hidden;
    overflow-x: auto;
}
.breakpoints {width: .8em;}
.breakpoint { color: #822; }

.CodeMirror-focused .cm-matchhighlight {
    background-image: url(data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAIAAAACCAYAAABytg0kAAAAFklEQVQI12NgYGBgkKzc8x9CMDAwAAAmhwSbidEoSQAAAABJRU5ErkJggg==);
    background-position: bottom;
    background-repeat: repeat-x;
}
.CodeMirror-matchingtag { background: rgba(255, 150, 0, .3); }
.cm-header { font-size: 150%; font-family: arial; }
.cm-strong { font-size: 140%; }
<?php
        if(  is_admin() ):
?>
/* Hide some Publish meta box stuff */
.misc-pub-section#visibility,
label[for=post_status] {
    display:none
}
<?php
        endif;
?>
</style>
<!-- Snippets Shortcode MiniCSS -->
<?php
    }
    

    /**
     * Check for shortcode in frontend
     * 
     * @param string $shortcode
     * @return boolean
     */
    private function has_shortcode($shortcode = '') 
    {
        if( !is_singular() )
            return false;
        
        $post_to_check = get_post(get_the_ID());  
        $found = false;  
        
        if (!$shortcode)   
            return $found;  

        if ( stripos($post_to_check->post_content, '[' . $shortcode) !== false ) 
            $found = true;  

        return $found;  
    }
    
    
    /**
	 * Extension/File/Class loader
	 * 
	 * @author kaiser
	 * @return void
	 */
	public function load_extensions()
	{
		$files = array( 
			'class-ss-cpt',
            'class-ss-posts-pages-metaboxes',
            'class-ss-shortcode'
		);

		foreach ( $files as $extension )
		{
			$file = plugin_dir_path( __FILE__ )."/inc/$extension.php";
			if ( is_readable( $file ) )
				include_once $file;
		}
	}
    
    
    /**
     * Update post meta, used with $_POST
     * 
     * Descriptions only indicative, not used in plugin
     * 
     * @var array
     */
    private function get_plugin_meta_data( $type )
    {
        $meta_data = array(
            'snippets' => array( // CodeMirror in Snippet post type
                'snipp' => array(
                    'meta' => '_snippet_code',
                    'del' => false,
                    'desc' => 'Holds the code data'
                ),
                '_select_theme' => array(
                    'meta' => '_select_theme',
                    'del' => false,
                    'desc' => 'Theme'
                ),
                '_select_language' => array(
                    'meta' => '_select_language',
                    'del' => false,
                    'desc' => 'Select highlight language'
                ),
                '_select_disable' => array(
                    'meta' => '_select_disable',
                    'del' => true,
                    'desc' => 'Disable CodeMirror'
                ),
            ),
            '_snippet_shortcode' => array( // Shortcode metabox in other post types
                'code' => array(
                    'meta' => 'code',
                    'del' => true,
                    'desc' => 'Shortcode sample text'
                ),
                'linenumbers' => array(
                    'meta' => 'linenumbers',
                    'del' => true,
                    'desc' => 'Disable line numbers'
                ),
                'fold' => array(
                    'meta' => 'fold',
                    'del' => true,
                    'desc' => 'Disable fold'
                ),
                'disable' => array(
                    'meta' => 'disable',
                    'del' => true,
                    'desc' => 'Disable CM in frontend'
                ),
                'skin' => array(
                    'meta' => 'skin',
                    'del' => false,
                    'desc' => 'Google Prettify skin'
                ),
                'wps_code' => array(
                    'meta' => 'wps_code',
                    'del' => true,
                    'desc' => 'WP Syntax snippet id'
                ),
                'wps_lang' => array(
                    'meta' => 'wps_lang',
                    'del' => true,
                    'desc' => 'WP Syntax language'
                ),
                'wps_line' => array(
                    'meta' => 'wps_line',
                    'del' => true,
                    'desc' => 'WP Syntax line number'
                ),
                'wps_hlight' => array(
                    'meta' => 'wps_hlight',
                    'del' => true,
                    'desc' => 'WP Syntax line hightlight'
                ),

            )
        );
        return $meta_data[$type];
    }

    
    /**
     * Add donate link to plugin description in /wp-admin/plugins.php
     * 
     * @param array $plugin_meta
     * @param string $plugin_file
     * @param string $plugin_data
     * @param string $status
     * @return array
     */
    public function donate_link( $plugin_meta, $plugin_file, $plugin_data, $status ) 
	{
		if( plugin_basename( __FILE__ ) == $plugin_file )
			$plugin_meta[] = '&hearts; <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=JNJXKWBYM9JP6&lc=ES&item_name=Snippets%20Shortcode%20%3a%20Rodolfo%20Buaiz&currency_code=EUR&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted">Buy me a beer :o)</a>';
		return $plugin_meta;
	}

} 


