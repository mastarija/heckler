<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

class Helpers
{
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