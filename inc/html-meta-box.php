<?php

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */
$get_themes = B5F_Snippets_Shortcode::get_instance()->cm_themes;
$get_langs  = B5F_Snippets_Shortcode::get_instance()->cm_languages;
$themes = array_merge( array('default'), apply_filters( 'ss_codemirror_themes', $get_themes ) );
$saved_theme = get_post_meta( $post->ID, '_select_theme', true);

$languages = apply_filters( 'ss_codemirror_languages', $get_langs );
$saved_language = get_post_meta( $post->ID, '_select_language', true);

echo '<p>Select a language: <select id="select-language" name="_select_language">';

foreach( $languages as $language )
    printf(
        '<option %s>%s</option>',
        selected( $saved_language, $language, false),
        $language
    );

echo '</select></p>';

echo '<p>Select a theme: <select id="select-theme" name="_select_theme">';

foreach( $themes as $theme )
    printf(
        '<option %s>%s</option>',
        selected( $saved_theme, $theme, false),
        $theme
    );

echo '</select></p>';

echo '<p><label>Disable CodeMirror: ';
echo '<input name="_select_disable" id="select-disable" type="checkbox" ' . checked( 'on', get_post_meta( $post->ID, '_select_disable', true), false ) . ' />';


echo '</label></p>';