/**
 * Inspecting the var:
 *                      console.log(CodeMirror.modes)
 * 
 */
jQuery(document).ready(function($)
{ 
   var b5f_fold_gutter = ( b5f_cm_config.mode == 'php' ) 
    ?  { rangeFinder: new CodeMirror.fold.combine(CodeMirror.fold.brace, CodeMirror.fold.comment) } 
    : true; 
    
    window.editor = CodeMirror.fromTextArea(document.getElementById("snipp"), {
        lineNumbers: b5f_cm_config.linenumbers,
        mode: b5f_cm_config.mode,
        viewportMargin: Infinity,
        styleActiveLine: true,
        matchBrackets: true,
        autoCloseBrackets: true,
        autoCloseTags: true,
        theme: b5f_cm_config.theme,
        readOnly: b5f_cm_config.is_admin,
        foldGutter: b5f_fold_gutter,
        gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter", "breakpoints"],
        highlightSelectionMatches: {showToken: /\w/},
        matchTags: {bothTags: true},
        extraKeys: {"Ctrl-J": "toMatchingTag"}
    });
    editor.foldCode(CodeMirror.Pos(8, 0));
    
    var charWidth = editor.defaultCharWidth(), basePadding = 10;
    editor.on("renderLine", function(cm, line, elt) {
        var off = CodeMirror.countColumn(line.text, null, cm.getOption("tabSize")) * charWidth;
        elt.style.textIndent = "-" + off + "px";
        elt.style.paddingLeft = (basePadding + off) + "px";
    });
    editor.refresh();
    
    editor.on("gutterClick", function(cm, n) {
        var info = cm.lineInfo(n);
        cm.setGutterMarker(n, "breakpoints", info.gutterMarkers ? null : makeMarker());
    });

    function makeMarker() {
        var marker = document.createElement("div");
        marker.style.color = "#822";
        marker.innerHTML = "●";
        return marker;
    }

    /* Only run what follows if in the backend */
    if( b5f_cm_config.is_admin )
        return;
    
    /**/
    
    /** /var editor = CodeMirror.fromTextArea(document.getElementById("snipp"), {
        lineNumbers: true,
        mode: b5f_cm_config.mode,
        viewportMargin: Infinity,
        styleActiveLine: true,
        matchBrackets: true,
        autoCloseBrackets: true,
        autoCloseTags: true,
        theme: b5f_cm_config.theme
      });/**/

    // Saving to TextArea on Codemirror.blur
    // http://codemirror.977696.n3.nabble.com/Can-t-save-the-editor-content-to-textarea-td4026384.html
    editor.on("blur", function() {
        editor.save();
    });
    $('#select-theme').change( function()
    { 
        editor.setOption("theme", $(this).val());
    });
    $('#select-language').change( function()
    { 
        editor.setOption("mode", $(this).val());
    });
    

    /**
     * Remove "Published on" from Publish Meta Box 
     */
    var b5f_ss_original = $('#timestamp').html();
    var b5f_ss_remove = b5f_ss_original.substr( 0, b5f_ss_original.indexOf('<b') );
    var b5f_ss_new = jQuery('#timestamp').html().replace( b5f_ss_remove,'' );
    jQuery('#timestamp').html(b5f_ss_new);
});
