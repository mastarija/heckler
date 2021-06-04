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
    add_action( 'init'                  , 'Heckler::init_hook_code' );
    add_action( 'add_meta_boxes'        , 'Heckler::make_meta_boxs' );
    add_action( 'admin_enqueue_scripts' , 'Heckler::load_main_srcs' );

    add_action( 'save_post_heckler'     , 'Heckler::save_conf_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_hook_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_rule_meta' );
    add_action( 'save_post_heckler'     , 'Heckler::save_code_meta' );

    add_shortcode( 'heckler' , 'Heckler::make_shortcode' );
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

    if ( $rule_conf && !self::exec_rule( $post->ID ) )
    {
      return;
    }

    switch ( $mode_conf ) {
      case 'text':
        return self::make_text( $post , false )();
        break;

      case 'code':
        ob_start();
        self::make_code( $post->ID )();
        return ob_get_clean();
        break;

      default:
        return;
        break;
    }
  }

  public static function init_hook_code ()
  {
    $qargs =
      [ 'post_type'       => 'heckler'
      , 'post_status'     => 'publish'
      , 'posts_per_page'  => -1
      , 'meta_query'      =>
        [ 'relation' => 'AND'
        , [ 'key' => 'heckler_hook_meta'
          , 'compare' => 'EXISTS'
          ]
        , [ 'key' => 'heckler_hook_meta'
          , 'compare' => '!='
          , 'value' => ''
          ]
        ]
      ];

    $query = new WP_Query( $qargs );

    foreach ( $query->posts as $post )
    {
      $hooks = self::load_hook_meta( $post->ID );

      $rule_conf = Helpers::meta_val( $post->ID , 'heckler_rule_conf' , false );
      $mode_conf = Helpers::meta_val( $post->ID , 'heckler_mode_conf' , 'text' );

      if ( $rule_conf && !self::exec_rule( $post->ID ) )
      {
        continue;
      }

      $action = null;

      switch ( $mode_conf ) {
        case 'text':
          $action = self::make_text( $post , true );
          break;

        case 'code':
          $action = self::make_code( $post->ID );
          break;
      }

      foreach ( $hooks as $hook )
      {
        add_action( $hook[ 'name' ] , $action , $hook[ 'sort' ] , $hook[ 'args' ] );
      }
    }
  }

  public static function make_text ( $post , $echo = false )
  {
    if ( did_action( 'elementor/loaded' ) && Elementor\Plugin::$instance->db->is_built_with_elementor( $post->ID ) )
    {
      return function ( ...$args ) use ( $post , $echo )
      {
        $val = Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post->ID , false );

        if ( $echo )
        {
          echo $val;
        }

        else
        {
          return $val;
        }
      };
    }

    else
    {
      return function ( ...$args ) use ( $post , $echo )
      {
        $val = apply_filters( 'the_content' , get_the_content( null , false , $post ) );

        if ( $echo )
        {
          echo $val;
        }

        else
        {
          return $val;
        }
      };
    }
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

      , 'public'                => true
      , 'rewrite'               => false
      , 'supports'              => [ 'title' , 'editor' ]
      , 'menu_icon'             => 'dashicons-excerpt-view'
      , 'capabilities'          =>
        [ 'edit_post'          => 'update_core'
        , 'read_post'          => 'update_core'
        , 'delete_post'        => 'update_core'
        , 'edit_posts'         => 'update_core'
        , 'edit_others_posts'  => 'update_core'
        , 'delete_posts'       => 'update_core'
        , 'publish_posts'      => 'update_core'
        , 'read_private_posts' => 'update_core'
        ]
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
      , 'nonc' => wp_nonce_field( 'nonc_save_conf_meta' , 'nonc_save_conf_meta' , true , false )
      ];

    self::load_view_file( 'vue/conf_meta.php' , $data );
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

  public static function view_rule_meta ( $post )
  {
    $data =
      [ 'rule' => self::load_user_file( $post->ID , 'return true;' , 'rule' )
      , 'nonc' => wp_nonce_field( 'nonc_save_rule_meta' , 'nonc_save_rule_meta' , true , false )
      ];

    self::load_view_file( 'vue/rule_meta.php' , $data );
  }

  public static function save_rule_meta ( $post_id )
  {
    $rule = Helpers::post_val( 'heckler_rule_meta' , '' );

    if ( !self::save_cond( $post_id , 'nonc_save_rule_meta' ) )
    {
      return;
    }

    self::save_user_file( $post_id , $rule , 'rule' );
  }

  public static function view_code_meta ( $post )
  {
    $data =
      [ 'code' => self::load_user_file( $post->ID , 'echo "Hello world!";' , 'code' )
      , 'nonc' => wp_nonce_field( 'nonc_save_code_meta' , 'nonc_save_code_meta' , true , false )
      ];

    self::load_view_file( 'vue/code_meta.php' , $data );
  }

  public static function save_code_meta ( $post_id )
  {
    $code = Helpers::post_val( 'heckler_code_meta' , '' );

    if ( !self::save_cond( $post_id , 'nonc_save_code_meta' ) )
    {
      return;
    }

    self::save_user_file( $post_id , $code , 'code' );
  }

  public static function view_hook_meta ( $post )
  {
    $data =
      [ 'list' => self::load_hook_meta( $post->ID )
      , 'nonc' => wp_nonce_field( 'nonc_save_hook_meta' , 'nonc_save_hook_meta' , true , false )
      ];

    self::load_view_file( 'vue/hook_meta.php' , $data );
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

    update_post_meta( $post_id , 'heckler_hook_meta' , trim( implode( ',' , $list ) ) );
  }

  public static function save_cond ( $post_id , $nonce )
  {
    $nonce_value = isset( $_POST[ $nonce ] ) ? $_POST[ $nonce ] : false;

    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $valid_nonce = $nonce_value && wp_verify_nonce( $nonce_value , $nonce );

    return !$is_autosave && !$is_revision && $valid_nonce;
  }

  public static function load_hook_meta ( $post_id )
  {
    $hooks = [];

    foreach ( explode( ',' , get_post_meta( $post_id , 'heckler_hook_meta' , true ) ) as $hraw )
    {
      $hook = explode( ':' , $hraw );

      $name = isset( $hook[ 0 ] ) ? Helpers::mend_txt( $hook[ 0 ] ) : '';
      $args = isset( $hook[ 1 ] ) ? Helpers::mend_num( $hook[ 1 ] ) : 0;
      $sort = isset( $hook[ 2 ] ) ? Helpers::mend_num( $hook[ 2 ] ) : 0;

      if ( empty( $name ) )
      {
        continue;
      }

      $hooks[] =
        [ 'name' => $name
        , 'args' => $args
        , 'sort' => $sort
        ];
    }

    return $hooks;
  }

  public static function load_main_srcs ()
  {
    $screen = get_current_screen();

    if ( !$screen || !( $screen->post_type === 'heckler' ) )
    {
      return;
    }

    wp_enqueue_script
      ( 'heckler_jsc'
      , plugins_url( 'jsc/heckler.js' , HECKLER_FILE )
      , [ 'jquery' , 'wp-codemirror' ]
      , 0
      , true
      );

    wp_enqueue_style
      ( 'heckler_css'
      , plugins_url( 'css/heckler.css' , HECKLER_FILE )
      , [ 'wp-codemirror' ]
      , 0
      , 'all'
      );
  }

  public static function load_view_file ( $file , $data = [] )
  {
    if ( $data )
    {
      extract( $data );
    }

    require HECKLER_DIR . $file;
  }

  public static function save_user_file ( $post_id , $code , $type )
  {
    $path = self::make_user_path( $post_id , $type );
    $file = fopen( $path , 'w' );
    fwrite( $file , self::php_header . stripslashes( $code ) );
    fclose( $file );
  }

  public static function load_user_file ( $post_id , $def = '' , $type )
  {
    $path = self::make_user_path( $post_id , $type );
    $file = file_exists( $path ) ? file_get_contents( $path ) : $def;
    $temp = substr( $file , 0 , strlen( self::php_header ) );
    $file = ( $temp == self::php_header ) ? substr( $file , strlen( self::php_header ) ) : $file;
    return $file;
  }

  public static function make_user_path ( $post_id , $type )
  {
    return HECKLER_DIR . "usr/{$type}_{$post_id}.php";
  }

  public static function exec_rule ( $post_id )
  {
    $file = self::make_user_path( $post_id , $rule );

    if ( !file_exists( $file ) )
    {
      return false;
    }

    return include $file;
  }

  public static function make_code ( $post_id )
  {
    $file = self::make_user_path( $post_id , 'code' );

    if ( !file_exists( $file ) )
    {
      return function ( ...$args )
      {
        // a noop
      };
    }

    return function ( ...$args ) use ( $file )
    {
      include $file;
    };
  }
}
