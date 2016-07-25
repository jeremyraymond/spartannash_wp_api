<?php

class SpartanNash_Posts extends WP_REST_Controller {

    /**
     * Register the routes for the objects of the controller.
     */
    public function register_routes() {
        $version = '2';
        $namespace = 'spartannash/v' . $version;
        $base = 'posts';

        register_rest_route( $namespace, '/' . $base, array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_posts' ),
            ),
        ) );
        // All non-home url paths
        register_rest_route( $namespace, '/' . $base . '/path' . '/(?P<path>.*)', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_post_from_path' ),
            ),
        ) );
        // Home url path
        register_rest_route( $namespace, '/' . $base . '/path/', array(
            array(
                'methods'         => WP_REST_Server::READABLE,
                'callback'        => array( $this, 'get_post_from_path' ),
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
        if ($params['post_type'])
            $params['post_type'] = explode(',',$params['post_type']);
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
                    $post_meta[$key] = $value[0];
                }
                $posts[$i] = (array)$posts_array[$i];
                $posts[$i]['post_meta'] = $post_meta;
            }
            return new WP_REST_Response($posts, 200);
        }
        else {
            $params_string = '[ ';
            $i = 1;
            foreach($params as $key => $value) {
                $params_string .= $key . ' => ' . $value;
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
}