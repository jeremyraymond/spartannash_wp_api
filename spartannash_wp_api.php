<?php
/**
 * Plugin Name: SpartanNash WP API Posts
 * Plugin URI: https://github.com/jeremyraymond/spartannash_wp_api
 * Description: Extends wordpress api to returns post/page information based on a url rather than id or slug.
 * Version: 2.0.0
 * Author: Jeremy Raymond
 * Author URI: http://jeremy-raymond.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'rest_api_init', function () {
    include dirname(__FILE__) . '/SpartanNash_WP_API.class.php';
    $posts = new SpartanNash_Posts();
    $posts->register_routes();
} );