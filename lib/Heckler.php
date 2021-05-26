<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

require_once 'helpers.php';

class Heckler
{
  public const php_header = "<?php if ( !defined( 'ABSPATH' ) ) return;\n";

  public static function init ()
  {
    add_action( 'init'                  , 'Heckler::make_post_type' );
    add_action( 'add_meta_boxes'        , 'Heckler::make_meta_boxs' );
    add_action( 'admin_enqueue_scripts' , 'Heckler::load_main_srcs' );

    add_action( 'save_post_heckler'     , 'Heckler::save_conf_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_hook_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_rule_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_code_meta' );

    add_shortcode( 'heckler' , 'Heckler::make_shortcode' );
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
      [ 'rule' => self::load_rule_file( $post->ID , 'return true;' )
      , 'nonce' => wp_nonce_field( 'nonc_save_rule_meta' , 'nonc_save_rule_meta' , true , false )
      ];

    self::load_view_file( 'vue/rule_meta.php' , $data );
  }

  public static function view_code_meta ( $post )
  {
    $data =
      [ 'code' => self::load_code_file( $post->ID , 'echo "Hello world!";' )
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

      $name = isset( $hook[ 0 ] ) ? Helpers::mend_txt( $hook[ 0 ] ) : '';
      $args = isset( $hook[ 1 ] ) ? Helpers::mend_num( $hook[ 1 ] ) : 0;
      $sort = isset( $hook[ 2 ] ) ? Helpers::mend_num( $hook[ 2 ] ) : 0;

      if ( empty( $name ) )
      {
        continue;
      }

      $data[ 'hooks' ][] =
        [ 'name' => $name
        , 'args' => $args
        , 'sort' => $sort
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

    self::save_rule_file( $post_id , $rule );
  }

  public static function save_code_meta ( $post_id )
  {
    $code = Helpers::post_val( 'heckler_code_meta' , '' );

    if ( !self::save_cond( $post_id , 'nonc_save_code_meta' ) )
    {
      return;
    }

    self::save_code_file( $post_id , $code );
  }

  public static function make_shortcode ( $att , $con , $tag )
  {
    $def =
      [ 'id'    => 0
      , 'data'  => ''
      ];

    $att = shortcode_atts( $def , $att , $tag );

    if ( !isset( $att[ 'id' ] ) || empty( $att[ 'id' ] ) )
    {
      return;
    }

    $post = get_post( $att[ 'id' ] );

    if ( !$post )
    {
      return;
    }

    $rule_conf = Helpers::mend_bol( Helpers::meta_val( $post->ID , 'heckler_rule_conf' , false ) );
    $mode_conf = Helpers::mend_txt( Helpers::meta_val( $post->ID , 'heckler_mode_conf' , 'text' ) );

    if ( $rule_conf )
    {
      $rule = Helpers::mend_txt( Helpers::meta_val( $post->ID , 'heckler_rule_meta' , '' ) );

      if ( empty( $rule ) || !eval( $rule ) )
      {
        return;
      }
    }

    switch ( $mode_conf ) {
      case 'text':
        return apply_filters( 'the_content', $post->post_content );
        break;

      case 'code':
        $code = Helpers::mend_txt( Helpers::meta_val( $post->ID , 'heckler_code_meta' , '' ) );

        if ( empty( $code ) )
        {
          return;
        }

        ob_start();
        eval( $code );
        return ob_get_clean();
        break;

      default:
        return;
        break;
    }
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

  public static function save_user_file ( $post_id , $code , $type )
  {
    $path = plugin_dir_path( __DIR__ ) . "usr/{$type}_{$post_id}.php";
    $file = fopen( $path , 'w' );
    fwrite( $file , self::php_header . stripslashes( $code ) );
    fclose( $file );
  }

  public static function save_rule_file ( $post_id , $rule )
  {
    self::save_user_file( $post_id , $rule , 'rule' );
  }

  public static function save_code_file ( $post_id , $code )
  {
    self::save_user_file( $post_id , $code , 'code' );
  }

  public static function load_user_file ( $post_id , $def = '' , $type )
  {
    $path = plugin_dir_path( __DIR__ ) . "usr/{$type}_{$post_id}.php";
    $file = file_exists( $path ) ? file_get_contents( $path ) : $def;
    $temp = substr( $file , 0 , strlen( self::php_header ) );
    $file = ( $temp == self::php_header ) ? substr( $file , strlen( self::php_header ) ) : $file;
    return $file;
  }

  public static function load_rule_file ( $post_id , $def = '' )
  {
    return self::load_user_file( $post_id , $def , 'rule' );
  }

  public static function load_code_file ( $post_id , $def = '' )
  {
    return self::load_user_file( $post_id , $def , 'code' );
  }
}
