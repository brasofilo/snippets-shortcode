<?php
/**
 * Snippets Shortcode
 *
 * First gets the Snippet Post ID, then the associated Code Meta Data, 
 * finally filters the string with http://php.net/manual/en/function.htmlentities.php 
 *
 * Accepts CodeMirror and WP-Syntax
 * 
 */

class B5F_SS_Shortcode
{
    /**
     * Enable shortcodes and print CSS
     */
    public function __construct() 
	{
		add_shortcode( 'snippet', array( $this, '_shortcode' ) );
		add_shortcode( 'wpsyntax', array( $this, '_wpsyntax' ) );
        add_action( 'wp_head', array( B5F_Snippets_Shortcode::get_instance(), 'mini_css' ) );
	}
	
    
   /**
     * Shortcode to use with CodeMirror
     * 
     * @global object $post
     * @param array $atts
     * @param string $content
     * @return string
     */
	public function _shortcode( $atts, $content )
	{
		global $post;
		$code = get_post_meta( $atts['id'], '_snippet_code', true );
		$lang = get_post_meta( $atts['id'], '_select_language', true );
		$snippet_info = get_post( $atts['id'] );
        $skin = isset( $atts['skin'] ) ? $atts['skin'] : 'elegant'; 
        
        if( !isset( $atts['disable'] ) )
        {
            $args = array();
            if( isset( $atts['linenumbers'] ) && 'true' != $atts['linenumbers'] )
                $args['linenumbers'] = false;
            B5F_Snippets_Shortcode::get_instance()->enqueue_codemirror_js( $skin, $lang, $args );
            B5F_Snippets_Shortcode::get_instance()->enqueue_codemirror_css( $skin );
        }
        
        # Fix for trimmed line numbers > 100
        echo '<style>.CodeMirror-linenumber.CodeMirror-gutter-elt {
width: 40px !important;
}</style>';
       
        # Codemirror not disabled
        if( !isset( $atts['disable'] ) )
            return sprintf(
                '<h3>%s</h3><textarea id="snipp" name="snipp" cols="90" rows="25" class="widefat">%s</textarea>',
                '',//__( 'Code' ),
                htmlentities( $code, ENT_QUOTES )
            );
        # Plain code without Codemirror
        else
            return sprintf(
                '<h3>%s</h3><pre>%s</pre>',
                '',//__( 'Code' ),
                htmlentities( $code, ENT_NOQUOTES )
            );
        
	}
    
    
    /**
     * Shortcode to use with WP Syntax
     * 
     * @global object $post
     * @param array $atts
     * @param string $content
     * @return string
     */
	public function _wpsyntax( $atts, $content )
	{
		global $post;
		$snippet_info = get_post( $atts['id'] );
		$code = get_post_meta( $atts['id'], '_snippet_code', true );
        $title = isset( $atts['show_title'] ) ? "<h3>{$snippet_info->post_title}</h3>" : '';
		$response = sprintf(
			'%s<pre lang="%s" %s %s escaped="true">%s</pre>',
			$title,
            $atts['lang'],
            isset( $atts['line'] ) ? 'line="'.$atts['line'].'"' : '',
            isset( $atts['highlight'] ) ? 'highlight="'.$atts['highlight'].'"' : '',
			htmlentities( $code, ENT_NOQUOTES )
		);
        return apply_filters ('the_content', $response );
	}

}

