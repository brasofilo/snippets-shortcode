<?php

/**
 * Meta boxex to select the Snippet and to display the correspondent Shortcode
 * 
 * Accepts CodeMirror and WP-Syntax
 *
 */

class B5F_SS_Posts_Pages_Metaboxes
{
    public function __construct() 
	{
		add_action( 'add_meta_boxes', array( $this, '_meta_boxes' ) );
	}
	
    
    /**
     * Post types where our snippets meta boxes will render
     * 
     * One of them, activates only if WP-Syntax is active
     * Filterable via `ss_show_shortcodes_in_cpts`
     * 
     */
	public function _meta_boxes() 
	{
        $cpts = apply_filters( 'ss_show_shortcodes_in_cpts', array( 'post', 'page' ) );
		foreach( $cpts as $pt )
        {
		    add_meta_box(
		        'snippets_mb',
		        __( 'Snippets' ), 
		        array( $this, '_snippets_mb' ),
		        $pt,
				'side'
		    );
            
            if ( class_exists( 'WP_Syntax' ) )
            {
                add_meta_box(
                    'ss_wpsyntax_mb',
                    __( 'Snippets with WP Syntax' ), 
                    array( $this, '_ss_wpsyntax_mb' ),
                    $pt,
                    'side'
                );
            }
        }
	}

    
    /**
     * For CodeMirror
     * 
     * @param object $post
     * @param object $box
     */
	function _snippets_mb( $post, $box ) 
	{
	    wp_nonce_field( B5F_Snippets_Shortcode::get_instance()->plugin_url, '_nonce_snippets' );
        wp_enqueue_script( 'cd-settings', B5F_Snippets_Shortcode::get_instance()->plugin_url . 'js/metabox-config.js' );
        $saved_meta = get_post_meta( $post->ID, '_snippet_shortcode', true);
        
        # Skins dropdown
        $skins = apply_filters( 'ss_gprettify_skins', B5F_Snippets_Shortcode::get_instance()->cm_themes );
        echo '<h3>Configuration:</h3>';
        echo '<p>Select a skin <select id="select-skin" name="_snippet_shortcode[skin]">';
        foreach( $skins as $skin )
            printf(
                '<option %s>%s</option>',
                selected( $saved_meta['skin'], $skin, false),
                $skin
            );
        echo '</select></p>';
        
        # Disable Line Numbers
        $this->print_checkbox( 'Disable Line Numbers', $saved_meta, 'linenumbers' );

        # Disable Gutter
        //$this->print_checkbox( 'Disable Fold', $saved_meta, 'fold' );

        # Disable CodeMirror
        $this->print_checkbox( 'Disable CodeMirror', $saved_meta, 'disable' );

        # Snippets pages dropdown
        // LAME CSS FIX !!!!!
		echo "<style>#_snippet_shortcode{width:100%} .js .postbox h3{cursor:default} input[type='checkbox'] { margin-left:10px }</style>"; 
        echo '<h3>Select snippet & create shortcode</h3>';
		wp_dropdown_pages( array(
			'post_type'=>'snippet',
			'selected' => isset($saved_meta['code']) ? $saved_meta['code'] : '', 
			'name' => '_snippet_shortcode[code]', 
			'id' => 'select-shortcode',
			'show_option_none' => '- Select -'
		) );
		echo '<br /><input type="text" class="widefat" id="render-shortcode" value="" />';
	}
    
    /**
     * For CodeMirror
     * 
     * @param object $post
     * @param object $box
     */
	function _ss_wpsyntax_mb( $post, $box ) 
	{
        wp_enqueue_script( 'wps-settings', B5F_Snippets_Shortcode::get_instance()->plugin_url . 'js/metabox-wpsyntax.js' );
        $saved_meta = get_post_meta( $post->ID, '_snippet_shortcode', true);
        
        echo '<h3>Configuration:</h3>';

        # Disable CodeMirror
        $this->print_inputbox( 'Language', $saved_meta, 'wps_lang' );
        # Disable CodeMirror
        $this->print_inputbox( 'Line number start', $saved_meta, 'wps_line' );
        # Disable CodeMirror
        $this->print_inputbox( 'Line number hightlight', $saved_meta, 'wps_hlight' );

        # Snippets pages dropdown
        echo '<h3>Select snippet & create shortcode</h3>';
		wp_dropdown_pages( array(
			'post_type'=>'snippet',
			'selected' => isset($saved_meta['wps_code']) ? $saved_meta['wps_code'] : '', 
			'name' => '_snippet_shortcode[wps_code]', 
			'id' => 'select-wps-shortcode',
			'show_option_none' => '- Select -'
		) );
        echo '<br /><input type="text" class="widefat" id="render-wps-shortcode" value="" />';
	}
    
    private function print_inputbox( $label, $saved, $key )
    {
        echo "<p><label>$label ";
        printf(
            '<input name="_snippet_shortcode[%1$s]" id="input-%1$s" type="text" value="%2$s" class="widefat" />',
            $key,
            isset( $saved[$key] ) ? esc_attr( $saved[$key] ) : ''
        );
        echo '</label></p>';
        
    }
    private function print_checkbox( $label, $saved, $key )
    {
        echo "<p><label>$label ";
        printf(
            '<input name="_snippet_shortcode[%1$s]" id="select-%1$s" type="checkbox" %2$s />',
            $key,
            checked( true, isset($saved[$key]), false )
        );
        echo '</label></p>';
        
    }
}
