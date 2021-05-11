<?php

/*
  Version: 1.0.0

  Author: Maštarija
  Author URI: https://mastarija.com

  Plugin Name: Heckler
  Text Domain: heckler
  Domain Path: languages
  Description: Used for creating reusable pieces of text or code and displaying them on specified hooks or as a short code.
*/

if ( !defined( 'ABSPATH' ) )
{
  return;
}

define( 'HECKLER_FILE' , __FILE__ );

require_once 'lib/Heckler.php';
require_once 'lib/HeckAux.php';

Heckler::init();