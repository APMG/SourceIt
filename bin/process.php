<?php

// HTML directories
define( "PUBLIC_HTML", "public_html" );
define( "PUBLIC_HTML_TEMP", "public_html/temp" );

// JS templates directories
define( "PUBLIC_HTML_JS_TEMPLATES", "public_html/js/templates" );
define( "PUBLIC_HTML_JS_TEMPLATES_TEMP", "public_html/js/templates/temp" );

// JS directories
define( "PUBLIC_HTML_JS", "public_html/js" );
define( "PUBLIC_HTML_JS_TEMP", "public_html/js/temp" );

// CSS directories
define( "PUBLIC_HTML_CSS", "public_html/css" );
define( "PUBLIC_HTML_CSS_TEMP", "public_html/css/temp" );

/*
 * DEPLOYMENT PREPERATION
 * mostly to replace server URLs that change depending on developement/staging/production
 *
 * - looks at the my_profile file to get the profile for the deployment server
 * - looks at the profiles file to get the values that change per deployment
 * - scans through the two temporary directories looking for html and javascript files with variables to replace and update them
 */

$profile_name = trim(file_get_contents("etc/my_profile"));
$json = file_get_contents("etc/profiles.json");
$json = json_decode($json, true);

$public_html_dir = scandir( PUBLIC_HTML_TEMP );
$public_html_js_templates_dir = scandir( PUBLIC_HTML_JS_TEMPLATES_TEMP );
$public_html_js_dir = scandir( PUBLIC_HTML_JS_TEMP );
$public_html_css_dir = scandir( PUBLIC_HTML_CSS_TEMP );

/*
	Process HTML files
*/
foreach ( $public_html_dir as $file_name ) {
    if ( is_file( PUBLIC_HTML_TEMP."/".$file_name )) {
        $buf = file_get_contents( PUBLIC_HTML_TEMP."/".$file_name );
        echo "Processing $file_name ";
        $n = 0;
        foreach ( $json[$profile_name] as $key => $value ) {
            $key_str = 'REPLACE_' . strtoupper($key);
            $buf  = str_replace( $key_str, $value, $buf, $m );
            $n += $m;
        }
		file_put_contents(PUBLIC_HTML."/".$file_name, $buf );
        echo "$n replaced\n";
    }
}

/*
	Process JS files
*/
foreach ( $public_html_js_dir as $file_name ) {
    if ( is_file( PUBLIC_HTML_JS_TEMP."/".$file_name )) {
        $buf = file_get_contents( PUBLIC_HTML_JS_TEMP."/".$file_name );
        echo "Processing $file_name ";
        $n = 0;
        foreach ( $json[$profile_name] as $key => $value ) {
            $key_str = 'REPLACE_' . strtoupper($key);
            $buf  = str_replace( $key_str, $value, $buf, $m );
            $n += $m;
        }
        file_put_contents(PUBLIC_HTML_JS."/".$file_name, $buf );
        echo "$n replaced\n";
    }
}

/*
	Process JS templates files
*/
foreach ( $public_html_js_templates_dir as $file_name ) {
    if ( is_file( PUBLIC_HTML_JS_TEMPLATES_TEMP."/".$file_name )) {
        $buf = file_get_contents( PUBLIC_HTML_JS_TEMPLATES_TEMP."/".$file_name );
        echo "Processing $file_name ";
        $n = 0;
        foreach ( $json[$profile_name] as $key => $value ) {
            $key_str = 'REPLACE_' . strtoupper($key);
            $buf  = str_replace( $key_str, $value, $buf, $m );
            $n += $m;
        }
        file_put_contents(PUBLIC_HTML_JS_TEMPLATES."/".$file_name, $buf );
		$buf = "<?php header('Access-Control-Allow-Origin: *'); ?>".$buf;
        file_put_contents(PUBLIC_HTML_JS_TEMPLATES."/".str_replace( ".html", ".php", $file_name), $buf );
        echo "$n replaced\n";
    }
}


/*
	Process CSS files
*/
foreach ( $public_html_css_dir as $file_name ) {
    if ( is_file( PUBLIC_HTML_CSS_TEMP."/".$file_name )) {
        $buf = file_get_contents( PUBLIC_HTML_CSS_TEMP."/".$file_name );
        echo "Processing $file_name ";
        $n = 0;
        foreach ( $json[$profile_name] as $key => $value ) {
            $key_str = 'REPLACE_' . strtoupper($key);
            $buf  = str_replace( $key_str, $value, $buf, $m );
            $n += $m;
        }
        file_put_contents(PUBLIC_HTML_CSS."/".$file_name, $buf );
        echo "$n replaced\n";
    }
}