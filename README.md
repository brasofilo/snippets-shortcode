Snippets Shortcode
==================

Custom post type to hold Code Snippets. 
A Shortcode is provided to show code blocks in the frontend. 
Uses CodeMirror.

## FAQ

### Hooks
```
add_filter('ss_codemirror_languages', function( $modes )
{
    $remove = array( 'markdown' );
    return array_diff( $modes, $remove );
});

add_filter('ss_codemirror_themes', function($themes)
{
    $remove = array( '3024-night', 'ambiance-mobile', 'ambiance', 'base16-dark', 'base16-light', 'blackboard', 'cobalt', 'eclipse', 'elegant', 'erlang-dark' );
    return array_diff( $themes, $remove );
});


add_filter('ss_gprettify_skins', function( $skins )
{
    $remove = array( 'sons-of-obsidian' );
    return array_diff( $skins, $remove );
});

add_filter('ss_show_shortcodes_in_cpts', function( $cpts )
{
    $cpts[] = 'attachment';
    return $cpts;
});
```

### Height of the code box in the frontend
Add to your style.css:

```
.CodeMirror {
    height: auto !important /* or fixed pixels */
}
```

##Screenshots

**TEMPORATY SCREENSHOT**:  
> ![Plugin meta box](https://raw.github.com/brasofilo/snippets-shortcode/master/assets/screenshot.png)


##Acknowledgments

* Code syntax highlight: [CodeMirror](http://codemirror.net/)



##Changelog

### 1.0
* Initial Public Release

##Credits

This plugin is built and maintained by [Rodolfo Buaiz](http://brasofilo.com), aka brasofilo.

##License

This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation; either version 2 of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.

You should have received a copy of the GNU General Public License along with this program; if not, write to:

Free Software Foundation, Inc.
51 Franklin Street, Fifth Floor,
Boston, MA
02110-1301, USA.
