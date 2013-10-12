/**
 * WP-Syntax meta box actions in regular posts/pages
 */
jQuery(document).ready(function( $ )
{
    var wps_cpt_dropdown = $('#select-wps-shortcode');
    var wps_lang = $('#select-wps_lang');
    var wps_line = $('#input-wps_line');
    var wps_hlight = $('#input-wps_hlight');
    var wps_render_input = $('#render-wps-shortcode');
    function do_the_wps_shortcode()
    {
        var short_wps_id = wps_cpt_dropdown.val();
        var short_wps_id_str = ( short_wps_id != '' ) ? ' id="'+short_wps_id+'"' : '';
        if( short_wps_id != '' )
            $('#render-wps-edit-link').html('<a href="/wp-admin/post.php?post='+short_wps_id+'&action=edit" title="opens in new window" target="_blank">edit #'+short_wps_id+'</a>');

        var short_lang = wps_lang.val();
        var short_lang_str = ( short_lang != '' ) ? ' lang="'+short_lang+'"' : '';

        var short_line = wps_line.val();
        var short_line_str = ( short_line != '' ) ? ' line="'+short_line+'"' : '';

        var short_hlight = wps_hlight.val();
        var short_hlight_str = ( short_hlight ) ? ' highlight="'+short_hlight+'"' : '';

        
        var full_shortcode = ( wps_cpt_dropdown != '' ) 
            ? '[wpsyntax ' 
                    + short_wps_id_str 
                    + short_lang_str 
                    + short_line_str 
                    + short_hlight_str 
                    +']' 
            : '';
        wps_render_input.val( full_shortcode );
    }

    wps_cpt_dropdown.change( do_the_wps_shortcode );
    wps_line.change( do_the_wps_shortcode );
    wps_hlight.change( do_the_wps_shortcode );
    wps_lang.change( do_the_wps_shortcode );
    do_the_wps_shortcode();

    /**
     * Select all on click
     * http://stackoverflow.com/a/3150370/1287812
     */ 
    $("#render-wps-shortcode").on("click", function () {
        $(this).select();
     });
});