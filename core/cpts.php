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
}

