<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

class Helpers
{
  public static function load_jsc ( $name , $path , $deps )
  {
    $v = self::file_ver( $path );
    $u = self::file_url( $path );

    wp_enqueue_script( $name , $u , $deps , $v , true );
  }

  public static function load_css ( $name , $path , $deps )
  {
    $v = self::file_ver( $path );
    $u = self::file_url( $path );

    wp_enqueue_style( $name , $u , $deps , $v , 'all' );
  }

  public static function file_ver ( $path )
  {
    return date( 'ymd-Gis' , filemtime( self::plug_dir() . $path ) );
  }

  public static function file_url ( $path )
  {
    return plugins_url( $path , self::plug_dir() . 'bar' );
  }

  public static function plug_dir ( )
  {
    $path = wp_normalize_path( self::curr_file() );
    $pdir = wp_normalize_path( WP_PLUGIN_DIR . '/' );
    $prel = explode( '/' , trim( str_replace( $pdir , '', dirname( $path ) ) , '/' ) );

    if ( !$prel )
    {
      return $pdir;
    }

    return $pdir . $prel[ 0 ] . '/';
  }

  public static function plug_rel ( $path )
  {
    return self::plug_dir() . trim( wp_normalize_path( $path ) , '/' );
  }

  public static function curr_file ( )
  {
    return wp_normalize_path( debug_backtrace( 2 , 1 )[ 0 ][ 'file' ] );
  }

  public static function post_val ( $name , $dval )
  {
    return isset( $_POST[ $name ] ) ? $_POST[ $name ] : $dval;
  }

  public static function post_lst ( $name )
  {
    $value = isset( $_POST[ $name ] ) ? $_POST[ $name ] : [];
    $value = is_array( $value ) ? $value : [ $value ];
    return $value;
  }

  public static function mend_txt ( $text )
  {
    return trim( (string) $text );
  }

  public static function mend_num ( $numb )
  {
    $numb = self::mend_txt( $numb );
    return is_numeric( $numb ) ? $numb : 0;
  }

  public static function mend_bol ( $bool )
  {
    return in_array( mb_strtolower( self::mend_txt( $bool ) ) , [ 'true' , '1' ] );
  }

  public static function meta_val ( $post_id , $key , $def )
  {
    $val = get_post_meta( $post_id , $key , true );
    return empty( $val ) ? $def : $val;
  }
}