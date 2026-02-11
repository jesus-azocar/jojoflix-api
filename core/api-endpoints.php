<?php
namespace Jojoflix\Core; 

function get_total_votes( $part_id = null ){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes';
    if($part_id){
        $stmt = $wpdb->prepare( "SELECT COUNT(*) totalvotes FROM $votes WHERE part_id = %d" , $part_id );
    }else{
        $stmt = $wpdb->prepare( "SELECT COUNT(*) totalvotes FROM $votes" );
    }
    
    $row = $wpdb->get_row( $stmt );

    return $row->totalvotes;
}

function get_avg_votes( $part_id = null ){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes';
    if($part_id){
        $stmt = $wpdb->prepare( "SELECT AVG(stars) avgvotes FROM $votes WHERE part_id = %d" , $part_id );
    }else{
        $stmt = $wpdb->prepare( "SELECT AVG(stars) avgvotes FROM $votes" );
    }
    
    $row = $wpdb->get_row( $stmt );

    return $row->avgvotes;
}

function get_hype( $part_id = null ){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes';
    if($part_id){
        $stmt = $wpdb->prepare( "SELECT 
                current_month.total AS actual,
                previous_month.total AS anterior,
                ROUND(((current_month.total - previous_month.total) / NULLIF(previous_month.total, 0)) * 100, 2) AS hype_percentage
            FROM 
                (SELECT COUNT(*) AS total FROM $votes WHERE part_id = %d  AND created_at >= NOW() - INTERVAL 30 DAY) AS current_month,
                (SELECT COUNT(*) AS total FROM $votes WHERE part_id = %d AND created_at BETWEEN NOW() - INTERVAL 60 DAY AND NOW() - INTERVAL 31 DAY) AS previous_month;" , $part_id, $part_id);
    }else{
        $stmt = $wpdb->prepare( "SELECT 
                current_month.total AS actual,
                previous_month.total AS anterior,
                ROUND(((current_month.total - previous_month.total) / NULLIF(previous_month.total, 0)) * 100, 2) AS hype_percentage
            FROM 
                (SELECT COUNT(*) AS total FROM $votes WHERE created_at >= NOW() - INTERVAL 30 DAY) AS current_month,
                (SELECT COUNT(*) AS total FROM $votes WHERE created_at BETWEEN NOW() - INTERVAL 60 DAY AND NOW() - INTERVAL 31 DAY) AS previous_month;" );
    }
    
    $row = $wpdb->get_row( $stmt );

    return $row->hype_percentage;
}

function get_user_sentiment( $part_id = null ){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes';
    if($part_id){
        $stmt = $wpdb->prepare( "SELECT 
                    total_votos,
                    votos_positivos,
                    ROUND((votos_positivos / total_votos) * 100, 2) AS sentiment_data
                FROM (
                    SELECT 
                        COUNT(*) AS total_votos,
                        SUM(CASE WHEN stars >= 4 THEN 1 ELSE 0 END) AS votos_positivos
                    FROM $votes WHERE part_id = %d
                ) AS sentiment_data;" , $part_id );
    }else{
        $stmt = $wpdb->prepare( "SELECT 
                    total_votos,
                    votos_positivos,
                    ROUND((votos_positivos / total_votos) * 100, 2) AS sentiment_data
                FROM (
                    SELECT 
                        COUNT(*) AS total_votos,
                        SUM(CASE WHEN stars >= 4 THEN 1 ELSE 0 END) AS votos_positivos
                    FROM $votes
                ) AS sentiment_data;" );
    }
    
    $row = $wpdb->get_row( $stmt );

    return $row->sentiment_data;
}

function get_last_comments( $part_id = null ){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes';
    if($part_id){
        $stmt = $wpdb->prepare( 
            "SELECT the_content FROM $votes WHERE part_id = %d ORDER BY created_at DESC LIMIT 3", 
            $part_id );
    }else{
        $stmt = $wpdb->prepare( 
            "SELECT the_content,post_title,created_at FROM $votes 
            INNER JOIN {$wpdb->posts} p ON p.ID= $votes.part_id 
            ORDER BY created_at DESC LIMIT 2" 
        );
    }
    $r = $wpdb->get_results( $stmt );
    return $r;
}

function get_best_parts(){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes'; 
    
    $stmt = $wpdb->prepare( 
            "SELECT part_id,AVG(stars) avgvotes,post_title FROM $votes 
            INNER JOIN {$wpdb->posts} p ON p.ID = $votes.part_id 
            GROUP BY part_id,post_title
            ORDER BY avgvotes DESC LIMIT 3" );

    $r = $wpdb->get_results( $stmt );

    foreach ($r as $key => $value) {
        
        $thumbnail = get_the_post_thumbnail_url( $value->part_id );

        if(!$thumbnail){
            $thumbnail = 'https://i.pinimg.com/736x/45/12/4b/45124b492779d3e564c841f979dc8b03.jpg';
        }
        $value->thumbnail = $thumbnail;
    }
    
    return $r;
}

function get_most_commented_parts(){
    global $wpdb;
    $votes = $wpdb->prefix . 'votes'; 
    
    $stmt = $wpdb->prepare( 
            "SELECT part_id,COUNT(*) totalvotes,post_title FROM $votes 
            INNER JOIN {$wpdb->posts} p ON p.ID = $votes.part_id 
            GROUP BY part_id,post_title
            ORDER BY totalvotes DESC LIMIT 3" );

    $r = $wpdb->get_results( $stmt );

    foreach ($r as $key => $value) {
        
        $thumbnail = get_the_post_thumbnail_url( $value->part_id );

        if(!$thumbnail){
            $thumbnail = 'https://i.pinimg.com/736x/45/12/4b/45124b492779d3e564c841f979dc8b03.jpg';
        }
        $value->thumbnail = $thumbnail;
    }

    return $r;
}

function get_stars_dist( $part_id = null ){

    global $wpdb;
    $votes = $wpdb->prefix . 'votes'; 
    if( $part_id ){
        $stmt = $wpdb->prepare( 
                "SELECT stars,COUNT(*) totalvotes FROM $votes  
                WHERE part_id = %d
                GROUP BY stars
                ORDER BY totalvotes DESC LIMIT 5", $part_id );
    }else{
        $stmt = $wpdb->prepare( 
                "SELECT stars,COUNT(*) totalvotes FROM $votes  
                GROUP BY stars
                ORDER BY totalvotes DESC LIMIT 5" );
    }

    $r = $wpdb->get_results( $stmt );
    return $r;
}

function get_daily_votes( $part_id =null ){

    global $wpdb;
    $votes = $wpdb->prefix . 'votes'; 
    if( $part_id ){
        $stmt = $wpdb->prepare( 
                "SELECT COUNT(*) totalvotes, DATE_FORMAT(created_at,'%%m-%%d') daily FROM $votes  
                WHERE part_id = %d
                GROUP BY daily
                ORDER BY daily ASC LIMIT 7", $part_id );
    }else{
        $stmt = $wpdb->prepare( 
                "SELECT 
    COUNT(*) as totalvotes, 
    DATE_FORMAT(created_at, '%%d/%%m') as daily,
    DATE(created_at) as date_order
FROM $votes 
GROUP BY date_order, daily
ORDER BY date_order DESC 
LIMIT 7" );
    }

    $r = $wpdb->get_results( $stmt ); 
    return $r;
}

function get_global_stats_json(){
    $output[] = [
                //stat cards
                'totalvotes'=> get_total_votes(),
                'avgvotes'=> get_avg_votes(),
                'sentiment_data' => get_user_sentiment(),
                'hype_percentage' => get_hype(),
                // lists
                'last_comments' => get_last_comments(),
                'best_parts' => get_best_parts(),
                'most_commented' => get_most_commented_parts(),
                //graphics
                'stars_dist' => get_stars_dist(),
                'daily_votes' => get_daily_votes(),
            ];
 
    return new \WP_REST_Response( $output, 200 );
}

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

    register_rest_route( 
        'jojoflix/v1', 
        '/stats',
        array(
            'methods'  => 'GET',  
            'callback' => '\Jojoflix\Core\get_global_stats_json', 
            'permission_callback' => '__return_true', 
        ) 
    );

}

add_action( 'rest_api_init', '\Jojoflix\Core\register_rest_routes' );