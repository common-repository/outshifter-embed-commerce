<?php

/*
* Creating a function to create our CPT
*/

function custom_post_type() {
    // Set UI labels for Custom Post Type
    $labels = array(
        'name'                => _x( 'Shortcodes', 'Post Type General Name' ),
        'singular_name'       => _x( 'Shortcode', 'Post Type Singular Name' ),
        'menu_name'           => __( 'Shortcodes' ),
        'parent_item_colon'   => __( 'Parent Shortcodes' ),
        'all_items'           => __( 'All Shortcodes' ),
        'view_item'           => __( 'View Shortcode' ),
        'add_new_item'        => __( 'Add New Shortcode' ),
        'add_new'             => __( 'Add New' ),
        'edit_item'           => __( 'Edit Shortcode' ),
        'update_item'         => __( 'Update Shortcode' ),
        'search_items'        => __( 'Search Shortcode' ),
        'not_found'           => __( 'Not Found' ),
        'not_found_in_trash'  => __( 'Not found in Trash' ),
    );  
    // Set other options for Custom Post Type
    $args = array(
        'label'               => __( 'shortcodes' ),
        'description'         => __( 'Saved shortcodes' ),
        'labels'              => $labels,
        // Features this CPT supports in Post Editor
        'supports'            => array( 'title', 'editor', 'excerpt', 'author', 'thumbnail', 'comments', 'revisions', 'custom-fields', ),
        // You can associate this CPT with a taxonomy or custom taxonomy. 
        'taxonomies'          => array( 'genres' ),
        /* A hierarchical CPT is like Pages and can have
        * Parent and child items. A non-hierarchical CPT
        * is like Posts.
        */
        'hierarchical'        => false,
        'public'              => true,
        'show_ui'             => false,
        'show_in_menu'        => false,
        'show_in_nav_menus'   => false,
        'show_in_admin_bar'   => false,
        'menu_position'       => 5,
        'can_export'          => true,
        'has_archive'         => false,
        'exclude_from_search' => false,
        'publicly_queryable'  => true,
        'capability_type'     => 'post',
        'show_in_rest' => true,
    );
    // Registering your Custom Post Type
    register_post_type( 'shortcodes', $args );
}

function custom_metabox(){
    add_meta_box(
        "product",
        "Product",
        "custom_field",
        "shortcodes",
        "normal",
        "low"
    );
}
add_action("admin_init", "custom_metabox");

function custom_field(){
    global $post;
    $data = get_post_custom($post->ID);
    $fieldData = isset($data["product"]) ? esc_attr($data["product"][0]) : 'no value';
    echo "<input  type=\"text\" name=\"product\" id=\"product\" value=\"".$fieldData."\" />";
}
add_action('save_post', 'save_detail');

function save_detail( $post_id ){
    if ( defined('DOING_AUTOSAVE') && DOING_AUTOSAVE ) {
        return $post_id;
    }
    if ( ! current_user_can('edit_post', $post_id) ) {
        return $post_id;
    }
    if ( empty( $_POST["product"] ) ) {
        return $post_id;
    }
    update_post_meta($post_id, "product", sanitize_text_field($_POST["product"]));
    
    return $post_id;
}

function wl_shortcodes() {
    $args = [
        'post_type'   => 'shortcodes',
        'post_status' => 'publish',
        'numberposts' => 99999,
        'order'       => 'ASC',
    ];
    $posts = get_posts($args);
    $data = [];
    $i = 0;
    foreach($posts as $post) {
        $data[$i]['id'] = $post->ID;
        $data[$i]['title'] = $post->post_title;
        $data[$i]['product'] = get_post_meta($post->ID, 'product');
        $i++;
    }
    return $data;
}

function rest_endpoint_handler_single( WP_REST_Request $request ) { 
    $params = $request->get_params();
    $args = [
       'post_type' => 'shortcodes',
       'id' => $params['id'],
       'include' => array($params['id']),
    ];
    $post = get_posts($args);
    $data['id'] = $post[0]->ID;
    $data['title'] = $post[0]->post_title;
    $data['product'] = get_post_meta($post[0]->ID, 'product');
    return $data;
}

add_action('rest_api_init', function() {
    register_rest_route(
        'wl/v1',
        'shortcodes',
        [
            'methods' => 'GET',
            'callback' => 'wl_shortcodes',
            'permission_callback' => '__return_true'
        ]
    );
    register_rest_route( 
        'wl/v1',
        'shortcodes/(?P<id>\d+)',
        [
            'method' => 'GET', 
            'callback' => 'rest_endpoint_handler_single', 
            'permission_callback' => '__return_true'
        ]
    );
});

/* Hook into the 'init' action so that the function
* Containing our post type registration is not 
* unnecessarily executed. 
*/

add_action( 'init', 'custom_post_type', 0 );