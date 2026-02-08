<?php
namespace Jojoflix\Core; 

add_action( 'init', __NAMESPACE__.'\register_cpts' );

function register_cpts() {
    $args = array(
        'label'               => __( 'Jojo Parts', 'jam' ),
        'description'         => __( 'Parts from Jojo', 'jam' ), 
        'supports'            => array( 'title', 'editor', 'thumbnail' ), 
        'public'              => true,
        'has_archive'         => true, 
        'show_in_menu'        => true,
        'show_in_rest'        => true, 
        'menu_icon'           => 'dashicons-welcome-write-blog',
        
    );
    register_post_type( 'jojo_parts', $args );

    $args_ep = array(
        'label'               => __( 'Jojo Episodes', 'jam' ),
        'description'         => __( 'Episodes from Jojo', 'jam' ), 
        'supports'            => array( 'title', 'editor', 'thumbnail' ), 
        'public'              => true,
        'has_archive'         => true, 
        'show_in_menu'        => true,
        'show_in_rest'        => true
        
    );
    register_post_type( 'jojo_episodes', $args_ep );
}

function create_tables(){
     global $wpdb;
    
    $votes_table_name = $wpdb->prefix . 'votes'; 
    $comments_table_name = $wpdb->prefix . 'episodes_comments'; 
    $charset_collate = $wpdb->get_charset_collate();

    $sql_votes = "CREATE TABLE $votes_table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT, 
        part_id int(11) NOT NULL,
        stars int(11) NOT NULL,
        the_content text NOT NULL,
        created_at DATETIME NOT NULL, 
        PRIMARY KEY  (id)
    ) $charset_collate;
    CREATE TABLE $comments_table_name (
        id bigint(20) unsigned NOT NULL AUTO_INCREMENT, 
        episode_id int(11) NOT NULL,
        stars int(11) NOT NULL,
        content text  NOT NULL,
        created_at DATETIME NOT NULL, 
        PRIMARY KEY  (id)
    ) $charset_collate;"; 
 
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    
    $rs = dbDelta( $sql_votes );  
    flush_rewrite_rules();
}