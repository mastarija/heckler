<?php

namespace Mastarija\Heckler;

function make_path ( $path )
{
  return wp_normalize_path( UTIL::PATH . '/' . $path );
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
  return wp_unslash( list_data( $_POST , $name , $dval ) );
}

function prep_natn ( $value )
{
  return abs( (int) prep_numb( $value ));
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
