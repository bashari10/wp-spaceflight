<?php
/*
 * Plugin Name: WP Spaceflight
 * Description: Improve your Google PageSpeed Insights score
 * Author: Avi Bashari
 * Author URI: https://facebook.com/bashari10
 * Version: 0.0.1
 */

if( function_exists('acf_add_options_page') ) {

    $args = array(
        'page_title'    => 'WP Spaceflight',
        'menu_slug'     => 'wpspaceflight',
        'capability'    => 'manage_options',
        'post_id'       => 'wpspaceflight',
        'parent_slug' 	=> 'options-general.php',
    );
    acf_add_options_sub_page($args);

}

if( function_exists('get_field') ) {

    $wpspaceflight_disable_emojies = get_field('wpspaceflight_disable_emojies', 'wpspaceflight');
    $wpspaceflight_remove_script_version = get_field('wpspaceflight_remove_script_version', 'wpspaceflight');
    $wpspaceflight_remove_embed_option = get_field('wpspaceflight_remove_embed_option', 'wpspaceflight');
    $wpspaceflight_remove_head_scripts = get_field('wpspaceflight_remove_head_scripts', 'wpspaceflight');
    $wpspaceflight_remove_comment_reply = get_field('wpspaceflight_remove_comment_reply', 'wpspaceflight');
    $wpspaceflight_remove_jquery_migrate = get_field('wpspaceflight_remove_jquery_migrate', 'wpspaceflight');
    $wpspaceflight_defer_parsing_of_js = get_field('wpspaceflight_defer_parsing_of_js', 'wpspaceflight');

    $wpspaceflight_remove_rest_api_endpoint = get_field('wpspaceflight_remove_rest_api_endpoint', 'wpspaceflight');
    $wpspaceflight_turn_off_oembed_auto_discovery = get_field('wpspaceflight_turn_off_oembed_auto_discovery', 'wpspaceflight');
    $wpspaceflight_dont_filter_oembed_results = get_field('wpspaceflight_dont_filter_oembed_results', 'wpspaceflight');
    $wpspaceflight_remove_oembed_discovery_links = get_field('wpspaceflight_remove_oembed_discovery_links', 'wpspaceflight');
    $wpspaceflight_remove_oembed_specific_js_from_frontend_and_backend = get_field('wpspaceflight_remove_oembed_specific_js_from_frontend_and_backend', 'wpspaceflight');

    if( $wpspaceflight_disable_emojies === true ) {
        add_action( 'init', 'wpspaceflight_disable_emojicons' );
    }

    if( $wpspaceflight_remove_script_version === true ) {
        add_filter( 'script_loader_src', 'wpspaceflight_remove_script_version', 15, 1 );
        add_filter( 'style_loader_src', 'wpspaceflight_remove_script_version', 15, 1 );
    }

    if( $wpspaceflight_remove_embed_option === true ) {
        add_action('init', 'wpspaceflight_remove_embed_option');
    }

    if( $wpspaceflight_remove_head_scripts === true ) {
        add_action( 'wp_enqueue_scripts', 'wpspaceflight_remove_head_scripts' );
    }

    if( $wpspaceflight_remove_comment_reply === true ) {
        add_action('init','wpspaceflight_remove_comment_reply');
    }

    if( $wpspaceflight_remove_jquery_migrate === true ) {
        add_filter( 'wp_default_scripts', 'wpspaceflight_remove_jquery_migrate' );
    }

    if( $wpspaceflight_defer_parsing_of_js === true && !is_admin() ) {
        add_filter( 'clean_url', 'wpspaceflight_defer_parsing_of_js', 11, 1 );
    }

    if( $wpspaceflight_remove_rest_api_endpoint === true ) {
        // Remove the REST API endpoint.
        remove_action( 'rest_api_init', 'wp_oembed_register_route' );
    }

    if( $wpspaceflight_turn_off_oembed_auto_discovery === true ) {
        // Turn off oEmbed auto discovery.
        add_filter( 'embed_oembed_discover', '__return_false' );
    }

    if( $wpspaceflight_dont_filter_oembed_results === true ) {
        // Don't filter oEmbed results.
        remove_filter( 'oembed_dataparse', 'wp_filter_oembed_result', 10 );
    }

    if( $wpspaceflight_remove_oembed_discovery_links === true ) {
        // Remove oEmbed discovery links.
        remove_action( 'wp_head', 'wp_oembed_add_discovery_links' );
    }

    if( $wpspaceflight_remove_oembed_specific_js_from_frontend_and_backend === true ) {
        // Remove oEmbed-specific JavaScript from the front-end and back-end.
        remove_action( 'wp_head', 'wp_oembed_add_host_js' );
    }

}


function wpspaceflight_disable_emojicons() {
    remove_action( 'admin_print_styles', 'print_emoji_styles' );
    remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
    remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
    remove_action( 'wp_print_styles', 'print_emoji_styles' );
    remove_filter( 'wp_mail', 'wp_staticize_emoji_for_email' );
    remove_filter( 'the_content_feed', 'wp_staticize_emoji' );
    remove_filter( 'comment_text_rss', 'wp_staticize_emoji' );
    add_filter( 'tiny_mce_plugins', function( $plugins ) {
        if ( is_array( $plugins ) ) {
            return array_diff( $plugins, array( 'wpemoji' ) );
        } else {
            return array();
        }
    } );
}

function wpspaceflight_remove_script_version( $src ){
    $parts = explode( '?ver', $src );
    return $parts[0];
}

function wpspaceflight_remove_embed_option() {
    if (!is_admin()) {
        wp_deregister_script('wp-embed');
    }
}

// Move JavaScript from the Head to the Footer
function wpspaceflight_remove_head_scripts() {
    remove_action('wp_head', 'wp_print_scripts');
    remove_action('wp_head', 'wp_print_head_scripts', 9);
    remove_action('wp_head', 'wp_enqueue_scripts', 1);

    add_action('wp_footer', 'wp_print_scripts', 5);
    add_action('wp_footer', 'wp_enqueue_scripts', 5);
    add_action('wp_footer', 'wp_print_head_scripts', 5);
}

// Remove comment-reply.min.js from footer
function wpspaceflight_remove_comment_reply(){
    wp_deregister_script( 'comment-reply' );
}

// Remove jQuery Migrate Script from header and Load jQuery from Google CDN
function wpspaceflight_remove_jquery_migrate( &$scripts ) {
    if ( ! is_admin() ) {
        $scripts->remove( 'jquery' );
        $scripts->remove( 'jquery-core' );
        $scripts->add( 'jquery', '//ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js', null, null );
    }
}

/* defer all js except jquery.min.js & revolution slider */
if ( !is_admin() ) {
    function wpspaceflight_defer_parsing_of_js ( $url ) {
        if ( FALSE === strpos( $url, '.js' ) ) return $url;
        if ( strpos( $url, 'jquery.min.js' ) ) return $url;
        if ( strpos( $url, 'jquery.themepunch.tools.min' ) ) return $url;
        if ( strpos( $url, 'jquery.themepunch.revolution.min' ) ) return $url;

        return "$url' defer onload='";
    }
}


if( function_exists('acf_add_local_field_group') ):

    acf_add_local_field_group(array (
        'key' => 'group_58a494eb97ab9',
        'title' => 'WP Spaceflight',
        'fields' => array (
            array (
                'key' => 'field_58a494f8fcbb1',
                'label' => 'Disable Emojies',
                'name' => 'wpspaceflight_disable_emojies',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a1193ef72',
                'label' => 'Remove Script Version',
                'name' => 'wpspaceflight_remove_script_version',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a30b3ef73',
                'label' => 'Remove Embed Options',
                'name' => 'wpspaceflight_remove_embed_option',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a3653ef74',
                'label' => 'Move JavaScript Files from the Head to the Footer',
                'name' => 'wpspaceflight_remove_head_scripts',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a4973ef75',
                'label' => 'Remove comment-reply.min.js from footer',
                'name' => 'wpspaceflight_remove_comment_reply',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a4fe3ef76',
                'label' => 'Remove jQuery Migrate Script from header and Load jQuery from Google CDN',
                'name' => 'wpspaceflight_remove_jquery_migrate',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a51a3ef77',
                'label' => 'Defer parsing of all JS except jquery.min.js & Revolution Slider',
                'name' => 'wpspaceflight_defer_parsing_of_js',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a8343ef78',
                'label' => 'Remove Rest API Endpoint',
                'name' => 'wpspaceflight_remove_rest_api_endpoint',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a9433ef79',
                'label' => 'Turn off oEmbed auto discovery',
                'name' => 'wpspaceflight_turn_off_oembed_auto_discovery',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a9643ef7a',
                'label' => 'Don\'t filter oEmbed results',
                'name' => 'wpspaceflight_dont_filter_oembed_results',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a9843ef7b',
                'label' => 'Remove oEmbed discovery links',
                'name' => 'wpspaceflight_remove_oembed_discovery_links',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
            array (
                'key' => 'field_58a4a9a63ef7c',
                'label' => 'Remove oEmbed-specific JavaScript from the front-end and back-end',
                'name' => 'wpspaceflight_remove_oembed_specific_js_from_frontend_and_backend',
                'type' => 'true_false',
                'instructions' => '',
                'required' => 0,
                'conditional_logic' => 0,
                'wrapper' => array (
                    'width' => '',
                    'class' => '',
                    'id' => '',
                ),
                'message' => '',
                'default_value' => 0,
                'ui' => 0,
                'ui_on_text' => '',
                'ui_off_text' => '',
            ),
        ),
        'location' => array (
            array (
                array (
                    'param' => 'options_page',
                    'operator' => '==',
                    'value' => 'wpspaceflight',
                ),
            ),
        ),
        'menu_order' => 0,
        'position' => 'normal',
        'style' => 'default',
        'label_placement' => 'top',
        'instruction_placement' => 'label',
        'hide_on_screen' => '',
        'active' => 1,
        'description' => '',
    ));

endif;

