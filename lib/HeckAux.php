<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

if ( class_exists( 'HeckAux' ) )
{
  return;
}

class HeckAux
{
  const CSS = 'CSS';
  const JSC = 'JSC';

  static function file_url ( $path )
  {
    return plugins_url( $path , HECKLER_FILE );
  }

  static function file_ver ( $path )
  {
    return date( 'ymd-Gis' , filemtime( plugin_dir_path( HECKLER_FILE ) . $path ) );
  }

  static function load_css ( $name , $path , $deps = [] )
  {
    self::load_src( self::CSS , $name , $path , $deps );
  }

  static function load_jsc ( $name , $path , $deps = [] )
  {
    self::load_src( self::JSC , $name , $path , $deps );
  }

  static function load_src ( $type , $name , $path , $deps = [] )
  {
    $u = self::file_url( $path );
    $v = self::file_ver( $path );

    switch ( $type )
    {
      case self::CSS :
        wp_enqueue_style ( $name , $u , $deps , $v , 'all' );
        break;

      case self::JSC :
        wp_enqueue_script( $name , $u , $deps , $v , true  );
        break;

      default :
        break;
    }
  }
}

