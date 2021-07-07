<?php

namespace Mastarija\Heckler;
use Elementor;

/*
  Version: 1.0.0

  Author: Maštarija
  Author URI: https://mastarija.com

  Plugin Name: Heckler
  Text Domain: heckler
  Domain Path: languages
  Description: Output custom reusable pieces of text or code on any action hook.
*/

if ( !defined( 'ABSPATH' ) )
{
  return;
}

require_once 'php/helper.php';

////////////////////////////////////////////////////////////////////////////////

init();

////////////////////////////////////////////////////////////////////////////////

class MODE
{
  public const TEXT = 'TEXT';
  public const CODE = 'CODE';

  public static function valid ( $mode )
  {
    return in_array( $mode , [ self::TEXT , self::CODE ] );
  }
}

class TYPE
{
  const RULE = 'RULE';
  const CODE = 'CODE';

  public static function valid ( $type )
  {
    return in_array( $type , [ self::RULE , self::CODE ] );
  }
}

class UTIL
{
  public const PATH = __DIR__;
  public const HEAD = "<?php if ( !defined( 'ABSPATH' ) ) die( 'direct access not allowed' );\n";
}

////////////////////////////////////////////////////////////////////////////////

function init ()
{
  $usr = make_path( '/usr' );

  if ( !is_dir( $usr ) )
  {
    mkdir( $usr , 0755 );
  }

  add_action( 'init'              , 'Mastarija\Heckler\make_post_type' );
  add_action( 'init'              , 'Mastarija\Heckler\init_hook_code' );


  add_action( 'add_meta_boxes'    , 'Mastarija\Heckler\make_meta_view' );

  add_action( 'save_post_heckler' , 'Mastarija\Heckler\save_meta_conf' );
  add_action( 'save_post_heckler' , 'Mastarija\Heckler\save_meta_hook' );

  add_action( 'save_post_heckler' , 'Mastarija\Heckler\save_meta_rule' );
  add_action( 'save_post_heckler' , 'Mastarija\Heckler\save_meta_code' );

  add_shortcode( 'heckler' , 'Mastarija\Heckler\make_shortcode' );

  add_action( 'current_screen' , 'Mastarija\Heckler\kill_styles' );
  add_action( 'admin_enqueue_scripts' , 'Mastarija\Heckler\load_scripts' );

  add_filter( 'manage_heckler_posts_columns' , 'Mastarija\Heckler\make_columns' );
  add_action( 'manage_heckler_posts_custom_column' , 'Mastarija\Heckler\data_columns' , 0 , 2 );
}

function kill_styles ()
{
  $screen = get_current_screen();

  if ( !$screen || !( $screen->post_type === 'heckler' ) )
  {
    return;
  }

  remove_editor_styles();
}

function load_scripts ()
{
  $screen = get_current_screen();

  if ( !$screen || !( $screen->post_type === 'heckler' ) )
  {
    return;
  }

  wp_enqueue_script
    ( 'heckler_jsc'
    , plugins_url( 'jsc/heckler.js' , __FILE__ )
    , [ 'jquery' , 'jquery-ui-sortable' , 'wp-codemirror' ]
    , 0
    , true
    );

  wp_enqueue_style
    ( 'heckler_css'
    , plugins_url( 'css/heckler.css' , __FILE__ )
    , [ 'wp-codemirror' ]
    , 0
    , 'all'
    );
}

function make_columns ( $columns )
{
  $columns[ 'hook' ] = 'Hook';
  $columns[ 'rule' ] = 'Rule';
  $columns[ 'mode' ] = 'Mode';
  $columns[ 'code' ] = 'Code';

  return $columns;
}

function data_columns ( $column , $post_id )
{
  switch ( $column )
  {
    case 'rule' :
      $data =
        [ 'conf_rule' => prep_bool( load_meta( $post_id , 'mastarija_heckler_conf_rule' , false ) )
        ];

      load_view_file( 'tpl/cell_rule.php' , $data );
      break;

    case 'mode' :
      $data =
        [ 'conf_mode' => strtolower( prep_mode( load_meta( $post_id , 'mastarija_heckler_conf_mode' , MODE::TEXT ) ) )
        ];

      load_view_file( 'tpl/cell_mode.php' , $data );
      break;

    case 'hook' :
      $data = [];
      $data[ 'conf_hook' ] = prep_bool( load_meta( $post_id , 'mastarija_heckler_conf_hook' , false ) );

      if ( $data[ 'conf_hook' ] )
      {
        $hook_list = load_hook_list( $post_id );

        $total  = 0;
        $active = 0;

        foreach ( $hook_list as $hook_item )
        {
          $total++;
          if ( $hook_item[ 'act' ] )
          {
            $active++;
          }
        }

        $data[ 'hook_total' ] = $total;
        $data[ 'hook_active' ] = $active;
      }

      load_view_file( 'tpl/cell_hook.php' , $data );
      break;

    case 'code' :
      $data =
        [ 'post_id' => prep_numb( $post_id , 0 )
        ];

      load_view_file( 'tpl/cell_code.php' , $data );
      break;
  }
}

function init_hook_code ()
{
  if ( is_admin() )
  {
    return;
  }

  $qargs =
    [ 'post_type' => 'heckler'
    , 'post_status' => 'publish'
    , 'posts_per_page' => -1
    , 'meta_query'      =>
        [ 'relation' => 'AND'
        , [ 'key' => 'mastarija_heckler_conf_hook'
          , 'compare' => 'EXISTS'
          ]
        , [ 'key' => 'mastarija_heckler_conf_hook'
          , 'compare' => '='
          , 'value' => true
          ]
        , [ 'key' => 'mastarija_heckler_hook_list'
          , 'compare' => 'EXISTS'
          ]
        , [ 'key' => 'mastarija_heckler_hook_list'
          , 'compare' => '!='
          , 'value' => ''
          ]
        ]
    ];

  $query = new \WP_Query( $qargs );


  while ( $query->have_posts() )
  {

    $query->the_post();

    $post_id = $query->post->ID;

    $conf_rule = prep_bool( load_meta( $post_id , 'mastarija_heckler_conf_rule' , false      ) );
    $conf_mode = prep_mode( load_meta( $post_id , 'mastarija_heckler_conf_mode' , MODE::TEXT ) );

    if ( $conf_rule )
    {
      $rule_func = make_user_func( TYPE::RULE , $post_id );

      if ( !$rule_func || !$rule_func() )
      {
        continue;
      }
    }

    $hook_list = load_hook_list( $post_id );

    if ( empty( $hook_list ) )
    {
      continue;
    }

    $action = false;

    switch ( $conf_mode )
    {
      case MODE::TEXT :
        $action = make_text_func( $post_id , true );
        break;

      case MODE::CODE :
        $action = make_user_func( TYPE::CODE , $post_id );
        break;
    }

    if ( !$action )
    {
      continue;
    }

    foreach ( $hook_list as $hook_item )
    {
      if ( !$hook_item[ 'act' ] )
      {
        continue;
      }

      add_action( $hook_item[ 'tag' ] , $action , $hook_item[ 'ord' ] , $hook_item[ 'arg' ] );
    }
  }

  wp_reset_postdata();
}

function make_shortcode ( $att , $con , $tag )
{
  $def =
    [ 'id'    => 0
    , 'name'  => ''
    , 'data'  => ''
    ];

  $att = shortcode_atts( $def , $att , $tag );

  $id   = trim( $att[ 'id' ] );
  $name = trim( $att[ 'name' ] );
  $data = trim( $att[ 'data' ] );

  $has_id = !empty( $id );
  $has_name = !empty( $name );

  if ( $has_name )
  {
    ob_start();
    do_action( $name , $data );
    return ob_get_clean();
  }

  if ( !$has_id )
  {
    return;
  }

  $post = get_post( $id );

  if ( !$post )
  {
    return;
  }

  $conf_rule = prep_bool( load_meta( $id , 'mastarija_heckler_conf_rule' , false      ) );
  $conf_mode = prep_mode( load_meta( $id , 'mastarija_heckler_conf_mode' , MODE::TEXT ) );

  if ( $conf_rule )
  {
    $rule_func = make_user_func( TYPE::RULE , $id );

    if ( !$rule_func || !$rule_func() )
    {
      return;
    }
  }

  switch ( $conf_mode ) {
    case MODE::TEXT :
      $text_func = make_text_func( $id );

      if ( !$text_func )
      {
        return;
      }

      return $text_func();
      break;

    case MODE::CODE :
      $code_func = make_user_func( TYPE::CODE , $id );

      if ( !$code_func )
      {
        return;
      }

      ob_start();
      $code_func();
      return ob_get_clean();

      break;
  }
}

////////////////////////////////////////////////////////////////////////////////

function make_post_type ()
{
  $name = 'Heckler';
  $slug = 'heckler';

  $caps =
    [ 'edit_post'          => 'update_core'
    , 'read_post'          => 'update_core'
    , 'delete_post'        => 'update_core'
    , 'edit_posts'         => 'update_core'
    , 'edit_others_posts'  => 'update_core'
    , 'delete_posts'       => 'update_core'
    , 'publish_posts'      => 'update_core'
    , 'read_private_posts' => 'update_core'
    ];

  $args =
    [ 'label'         => $name

    , 'show_ui'       => true
    , 'show_in_menu'  => true
    , 'menu_icon'     => 'dashicons-excerpt-view'

    , 'public'        => true
    , 'rewrite'       => false
    , 'supports'      => [ 'title' , 'editor' ]
    , 'show_in_rest'  => false // TODO : maybe in the future
    , 'capabilities'  => $caps
    ];

  register_post_type( $slug , $args );
}

////////////////////////////////////////////////////////////////////////////////

function make_meta_view ()
{
  add_meta_box( 'mastarija-heckler-conf' , 'Conf' , 'Mastarija\Heckler\view_meta_conf' , 'heckler' , 'side'   , 'core' );
  add_meta_box( 'mastarija-heckler-hook' , 'Hook' , 'Mastarija\Heckler\view_meta_hook' , 'heckler' , 'normal' , 'core' );
  add_meta_box( 'mastarija-heckler-rule' , 'Rule' , 'Mastarija\Heckler\view_meta_rule' , 'heckler' , 'normal' , 'core' );
  add_meta_box( 'mastarija-heckler-code' , 'Code' , 'Mastarija\Heckler\view_meta_code' , 'heckler' , 'normal' , 'core' );
}

////////////////////////////////////////////////////////////////////////////////

function view_meta_conf ( $post )
{
  $data =
    [ 'post'      => $post->ID
    , 'nonc'      => make_nonc( 'nonc_mastarija_heckler_save_meta_conf' )
    , 'conf_hook' => prep_bool( load_meta( $post->ID , 'mastarija_heckler_conf_hook' , false ) )
    , 'conf_rule' => prep_bool( load_meta( $post->ID , 'mastarija_heckler_conf_rule' , false ) )
    , 'conf_mode' => prep_mode( load_meta( $post->ID , 'mastarija_heckler_conf_mode' , MODE::TEXT ) )
    ];

  load_view_file( 'tpl/view_meta_conf.php' , $data );
}

function save_meta_conf ( $post_id )
{
  $conf_hook = sanitize_meta( 'mastarija_heckler_conf_hook' , prep_bool( post_data( 'conf_hook' , false ) ) , 'heckler' );
  $conf_rule = sanitize_meta( 'mastarija_heckler_conf_rule' , prep_bool( post_data( 'conf_rule' , false ) ) , 'heckler' );
  $conf_mode = sanitize_meta( 'mastarija_heckler_conf_rule' , prep_mode( post_data( 'conf_mode' , MODE::TEXT ) ) , 'heckler' );

  if ( !save_cond( $post_id , 'nonc_mastarija_heckler_save_meta_conf' ) )
  {
    return;
  }

  update_post_meta( $post_id , 'mastarija_heckler_conf_hook' , $conf_hook );
  update_post_meta( $post_id , 'mastarija_heckler_conf_rule' , $conf_rule );
  update_post_meta( $post_id , 'mastarija_heckler_conf_mode' , $conf_mode );
}

////////////////////////////////////////////////////////////////////////////////

function view_meta_hook ( $post )
{
  $hook_list = load_hook_list( $post->ID );

  // escape hook_list data for use in value attributes
  foreach ( $hook_list as $i => $hook_item )
  {
    $hook_list[ $i ] = array_map( 'esc_attr' , $hook_item );
  }

  $data =
    [ 'nonc'      => make_nonc( 'nonc_mastarija_heckler_save_meta_hook' )
    , 'hook_list' => load_hook_list( $post->ID )
    ];

  load_view_file( 'tpl/view_meta_hook.php' , $data );
}

function save_meta_hook ( $post_id )
{
  $hook_list = post_data( 'hook_list' , [] );

  if ( !save_cond( $post_id , 'nonc_mastarija_heckler_save_meta_hook' ) )
  {
    return;
  }

  if ( !is_array( $hook_list ) )
  {
    return;
  }

  $hook_list_meta = [];

  foreach ( $hook_list as $hook )
  {
    $tag = list_data( $hook , 'tag' , null );
    $ord = list_data( $hook , 'ord' , null );
    $arg = list_data( $hook , 'arg' , null );
    $act = list_data( $hook , 'act' , null );

    if ( is_null( $tag ) || is_null( $ord ) || is_null( $arg ) )
    {
      return;
    }

    $tag = sanitize_text_field( $tag );
    $ord = prep_numb( $ord );
    $arg = prep_natn( $arg );
    $act = prep_bool( $act ) ? 1 : 0;

    if ( empty( $tag ) )
    {
      continue;
    }

    if ( strpos( $tag , ':' ) || strpos( $tag , ';' ) )
    {
      continue;
    }

    $hook_list_meta[] = implode( ':' , [ $tag , $ord , $arg , $act ] );
  }

  $hook_list_meta = trim( implode( ';' , $hook_list_meta ) ) ;
  $hook_list_meta = sanitize_meta( 'mastarija_heckler_hook_list' , $hook_list_meta , 'heckler' );

  update_post_meta( $post_id , 'mastarija_heckler_hook_list' , $hook_list_meta );
}

////////////////////////////////////////////////////////////////////////////////

function view_meta_rule ( $post )
{
  $code_rule = load_code_file( make_path( "/usr/rule_{$post->ID}.php" ) );
  $code_rule = $code_rule === false ? '' : $code_rule;

  $data =
    [ 'post'      => $post->ID
    , 'nonc'      => make_nonc( 'nonc_mastarija_heckler_save_meta_rule' )
    , 'code_rule' => $code_rule
    ];

  load_view_file( 'tpl/view_meta_rule.php' , $data );
}

function save_meta_rule ( $post_id )
{
  $code_rule = post_data( 'code_rule' , '' );

  if ( !save_cond( $post_id , 'nonc_mastarija_heckler_save_meta_rule' ) )
  {
    return;
  }

  save_code_file( make_path( "/usr/rule_{$post_id}.php" ) , $code_rule );
}

////////////////////////////////////////////////////////////////////////////////

function view_meta_code ( $post )
{
  $code_code = load_code_file( make_path( "/usr/code_{$post->ID}.php" ) );
  $code_code = $code_code === false ? '' : $code_code;

  $data =
    [ 'post'      => $post->ID
    , 'nonc'      => make_nonc( 'nonc_mastarija_heckler_save_meta_code' )
    , 'code_code' => $code_code
    ];

  load_view_file( 'tpl/view_meta_code.php' , $data );
}

function save_meta_code ( $post_id )
{
  $code_code = post_data( 'code_code' , '' );

  if ( !save_cond( $post_id , 'nonc_mastarija_heckler_save_meta_code' ) )
  {
    return;
  }

  save_code_file( make_path( "/usr/code_{$post_id}.php" ) , $code_code );
}

////////////////////////////////////////////////////////////////////////////////

function make_nonc ( $name )
{
  return wp_nonce_field( $name , $name , true , false );
}

function load_meta ( $post_id , $key , $def )
{
  $value = get_post_meta( $post_id , $key , true );
  return empty( $value ) ? $def : $value;
}

function load_hook_list ( $post_id )
{
  $hook_list = [];

  foreach ( explode( ';' , load_meta( $post_id , 'mastarija_heckler_hook_list' , '' ) ) as $hook_item )
  {
    $hook_item = explode( ':' , $hook_item );

    $tag = sanitize_text_field( list_data( $hook_item , 0 , ''    ) );
    $ord = prep_numb( list_data( $hook_item , 1 , 0     ) );
    $arg = prep_natn( list_data( $hook_item , 2 , 0     ) );
    $act = prep_bool( list_data( $hook_item , 3 , false ) );

    if ( empty( $tag ) )
    {
      continue;
    }

    $hook_list[] =
      [ 'tag' => $tag
      , 'ord' => $ord
      , 'arg' => $arg
      , 'act' => $act
      ];
  }

  return $hook_list;
}

function load_view_file ( $file , $data = [] )
{
  if ( is_array( $data ) && !empty( $data ) )
  {
    extract( $data );
  }

  require $file;
}

function load_code_file ( $file )
{
  if ( !file_exists( $file ) )
  {
    return false;
  }

  $data = file_get_contents( $file );

  if ( strpos( $data , UTIL::HEAD ) !== 0 )
  {
    return false;
  }

  return substr( $data , strlen( UTIL::HEAD ) );
}

function save_code_file ( $file , $data = '' )
{
  file_put_contents( $file , UTIL::HEAD . $data );
}

function make_user_func ( $type , $post_id )
{
  $type = TYPE::valid( $type ) ? strtolower( $type ) : false;

  if ( !$type )
  {
    return false;
  }

  $file = make_path( "/usr/{$type}_{$post_id}.php" );

  if ( !file_exists( $file ) )
  {
    return false;
  }

  return function ( ...$args ) use ( $file )
  {
    return include $file;
  };
}

function test_elementor ( $post_id )
{
  return did_action( 'elementor/loaded' ) && Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id );
}

function make_text_func ( $post_id , $echo = false )
{
  $text = '';

  $stat = get_post_status( $post_id );

  if ( !$stat )
  {
    return false;
  }

  if ( test_elementor( $post_id ) )
  {
    $text = Elementor\Plugin::$instance->frontend->get_builder_content_for_display( $post_id , false );
  }

  else
  {

    $text = apply_filters( 'the_content' , get_the_content( null , false , $post_id ) );
  }

  if ( $echo )
  {
    return function ( ...$args ) use ( $text )
    {
      echo $text;
    };
  }

  return function ( ...$args ) use ( $text )
  {
    return $text;
  };
}

////////////////////////////////////////////////////////////////////////////////

function save_cond ( $post_id , $nonce )
{
  $nonce_value = post_data( $nonce , false );

  $is_autosave = wp_is_post_autosave( $post_id );
  $is_revision = wp_is_post_revision( $post_id );
  $valid_nonce = $nonce_value && wp_verify_nonce( $nonce_value , $nonce );

  return !$is_autosave && !$is_revision && $valid_nonce;
}
