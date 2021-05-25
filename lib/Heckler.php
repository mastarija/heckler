<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

require_once 'helpers.php';

class Heckler
{
  public static function init ()
  {
    add_action( 'init'                  , 'Heckler::make_post_type' );
    add_action( 'add_meta_boxes'        , 'Heckler::make_meta_boxs' );
    add_action( 'admin_enqueue_scripts' , 'Heckler::load_main_srcs' );

    add_action( 'save_post_heckler'     , 'Heckler::save_conf_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_hook_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_rule_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_code_meta' );
  }

  public static function make_post_type ()
  {
    $slug = 'heckler';
    $name = 'Heckler';
    $args =
      [ 'label'                 => $name
      , 'description'           => __( 'Reusable pieces of code and text.' , 'heckler' )

      , 'show_ui'               => true
      , 'show_in_menu'          => true

      , 'rewrite'               => false
      , 'supports'              => [ 'title' , 'editor' ]
      , 'menu_icon'             => 'dashicons-excerpt-view'
      ];

    register_post_type( $slug , $args );
  }

  public static function make_meta_boxs ()
  {
    add_meta_box( 'heckler-conf' , 'Conf' , 'Heckler::view_conf_meta' , 'heckler' , 'side'   , 'core' );
    add_meta_box( 'heckler-rule' , 'Rule' , 'Heckler::view_rule_meta' , 'heckler' , 'normal' , 'high' );
    add_meta_box( 'heckler-code' , 'Code' , 'Heckler::view_code_meta' , 'heckler' , 'normal' , 'high' );
    add_meta_box( 'heckler-hook' , 'Hook' , 'Heckler::view_hook_meta' , 'heckler' , 'normal' , 'high' );
  }

  public static function view_conf_meta ( $post )
  {
    $data =
      [ 'hcid' => $post->ID
      , 'rule' => Helpers::meta_val( $post->ID , 'heckler_rule_conf' , false )
      , 'mode' => Helpers::meta_val( $post->ID , 'heckler_mode_conf' , 'text' )
      , 'nonce' => wp_nonce_field( 'nonc_save_conf_meta' , 'nonc_save_conf_meta' , true , false )
      ];

    self::load_view_file( 'vue/conf_meta.php' , $data );
  }

  public static function view_rule_meta ( $post )
  {
    $data =
      [ 'rule' => Helpers::meta_val( $post->ID , 'heckler_rule_meta' , 'return true;' )
      , 'nonce' => wp_nonce_field( 'nonc_save_rule_meta' , 'nonc_save_rule_meta' , true , false )
      ];

    self::load_view_file( 'vue/rule_meta.php' , $data );
  }

  public static function view_code_meta ( $post )
  {
    $data =
      [ 'code' => Helpers::meta_val( $post->ID , 'heckler_code_meta' , 'echo "Hello world!";' )
      , 'nonce' => wp_nonce_field( 'nonc_save_code_meta' , 'nonc_save_code_meta' , true , false )
      ];

    self::load_view_file( 'vue/code_meta.php' , $data );
  }

  public static function view_hook_meta ( $post )
  {
    $data =
      [ 'hooks' => []
      , 'nonce' => wp_nonce_field( 'nonc_save_hook_meta' , 'nonc_save_hook_meta' , true , false )
      ];

    foreach ( explode( ',' , get_post_meta( $post->ID , 'heckler_hook_meta' , true ) ) as $hraw )
    {
      $hook = explode( ':' , $hraw );
      $data[ 'hooks' ][] =
        [ 'name' => isset( $hook[ 0 ] ) ? Helpers::mend_txt( $hook[ 0 ] ) : ''
        , 'args' => isset( $hook[ 1 ] ) ? Helpers::mend_num( $hook[ 1 ] ) : 0
        , 'sort' => isset( $hook[ 2 ] ) ? Helpers::mend_num( $hook[ 2 ] ) : 0
        ];
    }

    self::load_view_file( 'vue/hook_meta.php' , $data );
  }

  public static function save_cond ( $post_id , $nonce )
  {
    $nonce_value = isset( $_POST[ $nonce ] ) ? $_POST[ $nonce ] : false;

    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $valid_nonce = $nonce_value && wp_verify_nonce( $nonce_value , $nonce );

    return !$is_autosave && !$is_revision && $valid_nonce;
  }

  public static function save_conf_meta ( $post_id )
  {
    $rule = Helpers::mend_bol( Helpers::post_val( 'heckler_rule_conf' , false ) );
    $mode = Helpers::mend_txt( Helpers::post_val( 'heckler_mode_conf' , 'text' ) );

    if ( !in_array( $mode , [ 'text' , 'code' ] ) )
    {
      return;
    }

    if ( !self::save_cond( $post_id , 'nonc_save_conf_meta' ) )
    {
      return;
    }

    update_post_meta( $post_id , 'heckler_rule_conf' , $rule );
    update_post_meta( $post_id , 'heckler_mode_conf' , $mode );
  }

  public static function save_hook_meta ( $post_id )
  {
    $list = [];

    $name = array_map( 'Helpers::mend_txt' , Helpers::post_lst( 'heckler_name' ) );
    $args = array_map( 'Helpers::mend_num' , Helpers::post_lst( 'heckler_args' ) );
    $sort = array_map( 'Helpers::mend_num' , Helpers::post_lst( 'heckler_sort' ) );

    $same = count( array_unique( array_map( 'count' , [ $name , $args , $sort ] ) ) === 1 );

    if ( !$name || !$args || !$sort )
    {
      return;
    }

    if ( !self::save_cond( $post_id , 'nonc_save_hook_meta' ) )
    {
      return;
    }

    foreach ( array_map( null , $name , $args , $sort ) as $row )
    {
      if ( empty( $row[ 0 ] ) )
      {
        continue;
      }

      $list[] = implode( ':' , $row );
    }

    update_post_meta( $post_id , 'heckler_hook_meta' , implode( ',' , $list ) );
  }

  public static function save_rule_meta ( $post_id )
  {
    $rule = Helpers::post_val( 'heckler_rule_meta' , '' );

    if ( !self::save_cond( $post_id , 'nonc_save_rule_meta' ) )
    {
      return;
    }

    update_post_meta( $post_id , 'heckler_rule_meta' , $rule );
  }

  public static function save_code_meta ( $post_id )
  {
    $rule = Helpers::post_val( 'heckler_code_meta' , '' );

    if ( !self::save_cond( $post_id , 'nonc_save_code_meta' ) )
    {
      return;
    }

    update_post_meta( $post_id , 'heckler_code_meta' , $rule );
  }

  public static function make_shortcode ()
  {

  }

  public static function load_main_srcs ()
  {
    $screen = get_current_screen();

    if ( !$screen || !( $screen->post_type === 'heckler' ) )
    {
      return;
    }

    Helpers::load_jsc( 'heckler_jsc' , 'jsc/heckler.js'  , [ 'jquery' , 'wp-codemirror' ] );
    Helpers::load_css( 'heckler_css' , 'css/heckler.css' , [ 'wp-codemirror' ] );
  }

  public static function load_view_file ( $file , $data = [] )
  {
    if ( $data )
    {
      extract( $data );
    }

    require Helpers::plug_dir() . $file;
  }
}
