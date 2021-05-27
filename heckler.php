<?php

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

define( 'HECKLER_DIR' , wp_normalize_path( __DIR__ . '/' ) );
define( 'HECKLER_FILE' , wp_normalize_path( __FILE__ ) );

require_once 'lib/heckler.php';

Heckler::init();
