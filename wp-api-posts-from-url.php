
<?php
/**
 * Plugin Name: WP API Posts from URL
 * Plugin URI: https://github.com/jeremyraymond/wp-api-posts-from-url
 * Description: Extends wordpress api to returns post/page information based on a url rather than id or slug.
 * Version: 1.0.0
 * Author: Jeremy Raymond
 * Author URI: http://jeremy-raymond.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

function get_posts_from_url( $request ) {
    $params = $request->get_query_params();
    $postid = url_to_postid( $params['url'] );
    $post = (array) get_post($postid);
    $post_meta_raw = (array) get_post_meta($postid);
    $post_meta = [];
    foreach($post_meta_raw as $key => $value) {
        $pos = strpos($key, '_');
        if ($pos == 0) {
            $key = substr_replace($key, "", $pos, 1);
        }
        $post['post_meta'][$key] = maybe_unserialize($value[0]);
    }
    return (object) $post;
}

add_action( 'rest_api_init', function () {
    register_rest_route( 'posts-from-url/v2', '/post', array(
        'methods' => 'GET',
        'callback' => 'get_posts_from_url',
    ) );
} );