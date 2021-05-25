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

require_once 'lib/heckler.php';

Heckler::init();
