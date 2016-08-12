<?php

class SpartanNash_WP_API extends WP_REST_Controller {

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version = '2';
        $namespace = 'spartannash/v' . $version;

        /*
         *  Post routes
         */
        register_rest_route( $namespace, '/posts', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_posts' ),
            ),
        ) );
        // All non-home url paths
        register_rest_route( $namespace, '/posts' . '/path' . '/(?P<path>.*)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_post_from_path' ),
            ),
        ) );
        // Home url path
        register_rest_route( $namespace, '/posts' . '/path/', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_post_from_path' ),
            ),
        ) );
        // Get post based on ID
        register_rest_route( $namespace, '/posts' . '/id' . '/(?P<id>\d+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_post_from_id' ),
            ),
        ) );
        // Get the uri from the ID
        register_rest_route( $namespace, '/path' . '/id' . '/(?P<id>\d+)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_path_from_id' ),
            ),
        ) );

        /*
         *  Get options
         */
        register_rest_route( $namespace, '/options' . '/(?P<options>.*)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_options' ),
            ),
        ) );

        /*
         *  Get Menus
         */
        register_rest_route( $namespace, '/menus', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_all_menus' ),
            ),
        ) );
        register_rest_route( $namespace, '/menus' . '/(?P<menu>.*)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_menu' ),
            ),
        ) );
    }

    /**
     * Get a collection of posts matching query parameters
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_posts( $request ) {
        // Get the query parameters and use them as query arguments
        $params = $request->get_query_params();
        // Explode out the comma separated post types to allow multiple post types in query
        if ($params['post_type']) {
            $params['post_type'] = explode(',', $params['post_type']);
        }
        // Check if the user specified a key to organize the array by, then unset the param before wp_query
        $primary_key = '';
        if (isset($params['key'])) {
            $primary_key = $params['key'];
            unset($params['key']);
        }
        $posts_query = new WP_Query($params);
        if ($posts_query->posts) {
            // Create array of just the posts from the query
            $posts_array = (array)$posts_query->posts;
            $posts = [];
            // Create new array with just posts and append the post meta to each post.
            for ($i = 0; $i < count($posts_array); $i++) {
                $postid = $posts_array[$i]->ID;
                $post_meta_raw = get_post_custom($postid);
                $post_meta = [];
                // Remove the _ at the start of each post meta
                foreach ($post_meta_raw as $key => $value) {
                    $pos = strpos($key, '_');
                    if ($pos == 0) {
                        $key = substr_replace($key, "", $pos, 1);
                    }
                    // unserialize if it needs it
                    $value[0] = maybe_unserialize($value[0]);
                    $post_meta[$key] = $value[0];
                }
                $posts_array[$i] = (array)$posts_array[$i];
                $posts_array[$i]['path'] = trim(str_replace(home_url(), '', get_permalink($postid)), "/");
                $posts_array[$i]['post_meta'] = $post_meta;

                // if primary_key is specified, organize the returned associated array with that as the key
                if ($primary_key !== '') {
                    $posts[$posts_array[$i][$primary_key]] = $posts_array[$i];
                }
                else {
                    $posts[$i] = $posts_array[$i];

                }
            }
            return new WP_REST_Response($posts, 200);
        }
        // No matching posts, return a detailed error
        else {
            $params_string = '[ ';
            $i = 1;
            foreach($params as $key => $value) {
                if (is_array($value)) {
                    $params_string .= $key . ' => ' . "[ " . implode(',', $value) . " ]";
                }
                else {
                    $params_string .= $key . ' => ' . $value;
                }
                if ($i < count($params)) {
                    $params_string .= ', ';
                }
                $i++;
            }
            $params_string .= ' ]';
            return [
                "code" => "rest_no_posts",
                "message" => "No post found matching wp_query parameters: " . $params_string,
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }

    /**
     * Get one post based on path
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_post_from_path( $request )
    {
        $url = get_site_url() . '/' . $request['path'];
        $postid = url_to_postid($url);
        if ($postid) {
            $post = (array)get_post($postid);
            $post_meta_raw = (array)get_post_meta($postid);

            foreach ($post_meta_raw as $key => $value) {
                $pos = strpos($key, '_');
                if ($pos == 0) {
                    $key = substr_replace($key, "", $pos, 1);
                }
                $post['post_meta'][$key] = maybe_unserialize($value[0]);
            }
            return new WP_REST_Response($post, 200);
        } else {
            return [
                "code" => "rest_no_posts",
                "message" => "No post found matching url: " . $url,
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }

    /**
     * Get the path uri based on the post id
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_path_from_id( $request )
    {
        $path = str_replace(home_url(), '', get_permalink($request['id']));

        if ($path) {
            return new WP_REST_Response($path, 200);
        } else {
            return [
                "code" => "rest_no_posts",
                "message" => "No post found matching id: " . $request['id'],
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }

    /**
     * Get one post based on path
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_post_from_id( $request )
    {
        $postid = $request['id'];
        $post = (array)get_post($postid);
        if ($post) {

            $post_meta_raw = (array)get_post_meta($postid);

            foreach ($post_meta_raw as $key => $value) {
                $pos = strpos($key, '_');
                if ($pos == 0) {
                    $key = substr_replace($key, "", $pos, 1);
                }
                $post['post_meta'][$key] = maybe_unserialize($value[0]);
            }
            return new WP_REST_Response($post, 200);
        } else {
            return [
                "code" => "rest_no_posts",
                "message" => "No post found matching id: " . $postid,
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }

    /**
     * Get option data
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_options( $request )
    {
        // get all parameters fed in the request and run get_option() on them all
        $options_array = explode(',', $request['options']);
        $options = [];
        foreach($options_array as $option) {
            $option_value = get_option($option);
            if($option_value) {
                if(is_array($option_value)) {
                    $option_value = implode(',', $option_value);
                }
                $options[$option] = $option_value;
            }
            else {
                $options[$option] = false;
            }
        }
        if($options) {
            return new WP_REST_Response($options, 200);
        }
        else {
            return [
                "code" => "rest_no_options",
                "message" => "No options found matching: " . implode(',', $options_array),
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }

    /**
     * Get all menu items
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_all_menus( $request )
    {
        $raw_menus = wp_get_nav_menus();

        $menus = [];
        foreach($raw_menus as $menu) {
            $menu_items = wp_get_nav_menu_items($menu->term_id);
            $menus[$menu->slug] = (array) $menu;

            for($i = 0; $i < count($menu_items); $i++) {
                $menus[$menu->slug]['menu_items'][$i] = (array) $menu_items[$i];
                $menus[$menu->slug]['menu_items'][$i]['path'] = trim(str_replace(home_url(), '', $menu_items[$i]->url), "/");
            }
        }

        if($menus) {
            return new WP_REST_Response($menus, 200);
        }
        else {
            return [
                "code" => "rest_no_options",
                "message" => "No options found matching: " . implode(',', $menus),
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }

    /**
     * Get one menu from id, name, or slug
     *
     * @param WP_REST_Request $request Full data about the request.
     * @return WP_Error|WP_REST_Response
     */
    public function get_menu( $request )
    {
        $menu = $request['menu'];

        $menu_items = wp_get_nav_menu_items($menu);

        $item = [];
        for($i = 0; $i < count($menu_items); $i++) {
            $item[$i] = (array)$menu_items[$i];
            $item[$i]['path'] = trim(str_replace(home_url(), '', $menu_items[$i]->url), "/");
        }

        if($item) {
            return new WP_REST_Response($item, 200);
        }
        else {
            return [
                "code" => "rest_no_options",
                "message" => "No options found matching: " . implode(',', $item),
                "data" => [
                    "status" => 404
                ]
            ];
        }
    }
}