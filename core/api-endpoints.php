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
        
        $thumbnail = get_the_post_thumbnail_url( $part->ID );

        if(!$thumbnail){
            $thumbnail = 'https://i.pinimg.com/736x/45/12/4b/45124b492779d3e564c841f979dc8b03.jpg';
        }
        
        foreach( $parts as $part ){
            $output[] = [
                'title'=> $part->post_title,
                'content' => $part->post_content,
                'thumbnail' => $thumbnail
            ];
        }
    }
 
    return new \WP_REST_Response( $output, 200 );
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

}

add_action( 'rest_api_init', '\Jojoflix\Core\register_rest_routes' );