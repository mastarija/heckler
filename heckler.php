<?php

namespace Mastarija\Heckler;

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

init();

class MODE
{
  public const TEXT = 'TEXT';
  public const CODE = 'CODE';

  public static function valid( $mode )
  {
    return in_array( $mode , [ self::TEXT , self::CODE ] );
  }
}

class UTIL
{
  public const HEAD = '';
}

function init ()
{
  add_action( 'init'              , 'Mastarija\Heckler\make_post_type' );
  add_action( 'add_meta_boxes'    , 'Mastarija\Heckler\make_meta_view' );

  add_action( 'save_post_heckler' , 'Mastarija\Heckler\save_meta_conf' );
  add_action( 'save_post_heckler' , 'Mastarija\Heckler\save_meta_hook' );
}

function make_post_type ()
{
  $name = 'Heckler';
  $slug = 'heckler';

  $args =
    [ 'label'         => $name

    , 'show_ui'       => true
    , 'show_in_menu'  => true
    , 'menu_icon'     => 'dashicons-excerpt-view'

    , 'public'        => false
    , 'rewrite'       => false
    , 'supports'      => [ 'title' , 'editor' ]
    ];

  register_post_type( $slug , $args );
}

function make_meta_view ()
{
  add_meta_box( 'mastarija-heckler-conf' , 'Conf' , 'Mastarija\Heckler\view_meta_conf' , 'heckler' , 'side'   , 'core' );
  add_meta_box( 'mastarija-heckler-hook' , 'Hook' , 'Mastarija\Heckler\view_meta_hook' , 'heckler' , 'normal' , 'core' );
  add_meta_box( 'mastarija-heckler-rule' , 'Rule' , 'Mastarija\Heckler\view_meta_rule' , 'heckler' , 'normal' , 'core' );
  add_meta_box( 'mastarija-heckler-code' , 'Code' , 'Mastarija\Heckler\view_meta_code' , 'heckler' , 'normal' , 'core' );
}

function view_meta_conf ( $post )
{
  $data =
    [ 'post'      => $post->ID
    , 'nonc'      => wp_nonce_field( 'nonc_mastarija_heckler_save_meta_conf' , 'nonc_mastarija_heckler_save_meta_conf' , true , false )
    , 'conf_rule' => prep_bool( load_meta( $post->ID , 'mastarija_heckler_conf_rule' , false ) )
    , 'conf_mode' => prep_mode( load_meta( $post->ID , 'mastarija_heckler_conf_mode' , MODE::TEXT ) )
    ];

  load_view_file( 'php/view_meta_conf.php' , $data );
}

function save_meta_conf ( $post_id )
{
  $conf_rule = prep_bool( post_data( 'conf_rule' , false ) );
  $conf_mode = prep_mode( post_data( 'conf_mode' , MODE::TEXT ) );

  if ( !save_cond( $post_id , 'nonc_mastarija_heckler_save_meta_conf' ) )
  {
    return;
  }

  update_post_meta( $post_id , 'mastarija_heckler_conf_rule' , $conf_rule );
  update_post_meta( $post_id , 'mastarija_heckler_conf_mode' , $conf_mode );
}

function view_meta_hook ( $post )
{
  $data =
    [ 'nonc'      => wp_nonce_field( 'nonc_mastarija_heckler_save_meta_hook' , 'nonc_mastarija_heckler_save_meta_hook' , true , false )
    , 'hook_list' => load_hook_list( $post->ID )
    ];

  load_view_file( 'php/view_meta_hook.php' , $data );
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
      make_note( 'Invalid Hook data!' , 'notice notice-error' );
      return;
    }

    $tag = prep_text( $tag );
    $ord = prep_numb( $ord );
    $arg = prep_numb( $arg );
    $act = prep_bool( $act ) ? 1 : 0; // make bool serializable

    if ( empty( $tag ) )
    {
      // ignore empty entries
      continue;
    }

    if ( strpos( $tag , ':' ) || strpos( $tag , ';' ) )
    {
      make_note( "The tag '{$tag}' should not contain <code>:</code> or <code>;</code> characters." , 'notice notice-error' );
      continue;
    }

    $hook_list_meta[] = implode( ':' , [ $tag , $ord , $arg , $act ] );
  }

  update_post_meta( $post_id , 'mastarija_heckler_hook_list' , implode( ';' , $hook_list_meta ) );
}

function view_meta_rule ( $post )
{
  $data =
    [ 'post'      => $post->ID
    , 'code_rule' => 'hello, I am a rule'
    ];

  load_view_file( 'php/view_meta_rule.php' , $data );
}

function view_meta_code ( $post )
{
  $data =
    [ 'post' => $post->ID
    , 'code_code' => 'hello, I am a code'
    ];

  load_view_file( 'php/view_meta_code.php' , $data );
}

function load_view_file ( $file , $data = [] )
{
  if ( is_array( $data ) && !empty( $data ) )
  {
    extract( $data );
  }

  require $file;
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

    $tag = prep_text( list_data( $hook_item , 0 , ''    ) );
    $ord = prep_numb( list_data( $hook_item , 1 , 0     ) );
    $arg = prep_numb( list_data( $hook_item , 2 , 0     ) );
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

function list_data ( $list , $name , $dval )
{
  if ( !is_array( $list ) )
  {
    return $dval;
  }

  return isset( $list[ $name ] ) ? $list[ $name ] : $dval;
}

function post_data ( $name , $dval )
{
  return list_data( $_POST , $name , $dval );
}

function prep_numb ( $value )
{
  return is_numeric( $value ) ? $value : 0;
}

function prep_bool ( $value )
{
  if ( is_string( $value ) )
  {
    $value = strtolower( $value );

    if ( in_array( $value , [ 'false' , '0' ] , true ) )
    {
      $value = false;
    }
  }

  return (bool) $value;
}

function prep_text ( $value )
{
  return (string) sanitize_text_field( $value );
}

function prep_mode ( $value )
{
  return MODE::valid( $value ) ? $value : MODE::TEXT;
}

function save_cond ( $post_id , $nonce )
{
  $nonce_value = post_data( $nonce , false );

  $is_autosave = wp_is_post_autosave( $post_id );
  $is_revision = wp_is_post_revision( $post_id );
  $valid_nonce = $nonce_value && wp_verify_nonce( $nonce_value , $nonce );

  return !$is_autosave && !$is_revision && $valid_nonce;
}

function make_note ( $note , $class )
{
  add_action
    ( 'admin_notices'
    , function () use ( $note , $class )
      {
        echo "<div class=\"{$class}\">{$note}</div>";
      }
    );
}