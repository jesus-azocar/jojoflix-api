<?php
namespace Jojoflix\Core; 

function get_parts_json(){
    
    $output = [];

    $args = [
                'post_type'=>'jojo_parts',
                'post_status'=>'publish',
                'posts_per_page'=>-1
            ];

    $parts = get_posts($args);

    if($parts){
        
        
        foreach( $parts as $part ){
            
        $thumbnail = get_the_post_thumbnail_url( $part->ID );

        if(!$thumbnail){
            $thumbnail = 'https://i.pinimg.com/736x/45/12/4b/45124b492779d3e564c841f979dc8b03.jpg';
        }
            $output[] = [
                'id'=> $part->ID,
                'title'=> $part->post_title,
                'content' => wp_strip_all_tags($part->post_content),
                'thumbnail' => $thumbnail
            ];
        }
    }
 
    return new \WP_REST_Response( $output, 200 );
}

function get_part_details( $request ){
    global $wpdb;

    $id = $request->get_param('id');
    $table_votes = $wpdb->prefix . 'votes';  
    $part = get_post( $id );
    
    if(!$part){ 
        return new \WP_Error( "Part not found" );
    }
    
    $thumbnail = get_the_post_thumbnail_url( $part->ID );

    if(!$thumbnail){
        $thumbnail = 'https://i.pinimg.com/736x/45/12/4b/45124b492779d3e564c841f979dc8b03.jpg';
    }
    
    $query = $wpdb->prepare(
        "SELECT * FROM $table_votes WHERE part_id = %d 
        ORDER BY created_at DESC",
        $id
    );

    $votes = $wpdb->get_results($query, ARRAY_A);

    $output = [
                'title'=> $part->post_title,
                'content' => wp_strip_all_tags($part->post_content),
                'thumbnail' => $thumbnail,
                'votes' => $votes
    ];

    return new \WP_REST_Response($output, 200);
    
}

function insert_part_vote($request) {
    global $wpdb;

    $table_votes = $wpdb->prefix . 'votes';  
    $params = $request->get_json_params();
    $part_id = $params['part_id'] ?? null; 
    $stars = $params['stars'] ?? null; 
    $content = $params['content'] ?? null; 

    if (!$part_id) {
        return new \WP_Error('missing_data', 'Missing part ID', ['status' => 400]);
    }

    $rs = $wpdb->insert(
        $table_votes,
        [
            'part_id' => $part_id, 
            'stars' =>  $stars,
            'the_content' =>  $content,
            'created_at' => current_time('mysql')
        ],
        ['%d', '%d', '%s', '%s']
    );

    if ($rs) {
        return new \WP_REST_Response(['message' => '¡Voto registrado, ORA!'], 201);
    }

    return new \WP_Error('db_error', 'Vote was not saved', ['status' => 500]);
}

function register_rest_routes() {

    register_rest_route( 
        'jojoflix/v1', 
        '/parts',
        array(
            'methods'  => 'GET',  
            'callback' => '\Jojoflix\Core\get_parts_json', 
            'permission_callback' => '__return_true', 
        ) 
    );
    
    
    register_rest_route( 
        'jojoflix/v1', 
        '/parts',
        array(
            'methods'  => 'POST',  
            'callback' => '\Jojoflix\Core\insert_part_vote', 
            'permission_callback' => '__return_true', 
        ) 
    );
    
    register_rest_route( 
        'jojoflix/v1', 
        '/parts/(?P<id>\d+)',
        array(
            'methods'  => 'GET',  
            'callback' => '\Jojoflix\Core\get_part_details', 
            'args'     => [
            'id' => [
                'validate_callback' => function($param) {
                    return is_numeric($param);
                }
            ]
            ],
            'permission_callback' => '__return_true'
        ) 
    ); 

}

add_action( 'rest_api_init', '\Jojoflix\Core\register_rest_routes' );