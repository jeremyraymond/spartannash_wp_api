<?php
/**
 * Plugin Name: SpartanNash WP API
 * Plugin URI: https://github.com/jeremyraymond/spartannash_wp_api
 * Description: Extends wordpress api with some customized methods for returning posts/pages/custom_posts and options.
 * Version: 2.1.0
 * Author: Jeremy Raymond
 * Author URI: http://jeremy-raymond.com
 * License: GPL2
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

add_action( 'rest_api_init', function () {
    include dirname(__FILE__) . '/SpartanNash_WP_API.class.php';
    $posts = new SpartanNash_WP_API();
    $posts->register_routes();
} );