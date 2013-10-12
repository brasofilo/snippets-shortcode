/**
 * CodeMirror meta box actions in regular posts/pages
 */
jQuery(document).ready(function( $ )
{
    var cpt_dropdown = $('#select-shortcode');
    var skin_dropdown = $('#select-skin');
    var disable_linenumbers = $('#select-linenumbers');
    var disable_checkbox = $('#select-disable');
    var render_input = $('#render-shortcode');
    function do_the_shortcode()
    {
        var short_id = cpt_dropdown.val();
        var short_id_str = ( short_id != '' ) ? ' id="'+short_id+'"' : '';
        if( short_id != '' )
            $('#render-cm-edit-link').html('<a href="/wp-admin/post.php?post='+short_id+'&action=edit" title="opens in new window" target="_blank">edit #'+short_id+'</a>');

        var short_sk = skin_dropdown.val();
        var short_sk_str = ( short_sk != '' && short_sk != 'default' ) ? ' skin="'+short_sk+'"' : '';

        var short_dsb = disable_checkbox.is(':checked');
        var short_dsb_str = ( short_dsb ) ? ' disable="true"' : '';

        var short_linenum = disable_linenumbers.is(':checked');
        var short_linenum_str = ( short_linenum ) ? ' linenumbers="false"' : '';

        // Remove stuff if disable is selected
        if( short_dsb ) {
            short_sk_str = '';
            short_linenum_str = '';
            short_fold_str = '';
        }
        var full_shortcode = ( short_id != '' ) 
            ? '[snippet ' 
                    + short_id_str 
                    + short_dsb_str 
                    + short_linenum_str 
                    + short_sk_str 
                    +']' 
            : '';
        render_input.val( full_shortcode );
    }

    skin_dropdown.change( do_the_shortcode );
    disable_checkbox.change( do_the_shortcode );
    disable_linenumbers.change( do_the_shortcode );
    cpt_dropdown.change( do_the_shortcode );
    do_the_shortcode();

    /**
     * Select all on click
     * http://stackoverflow.com/a/3150370/1287812
     */ 
    $("#render-shortcode").on("click", function () {
        $(this).select();
     });
});