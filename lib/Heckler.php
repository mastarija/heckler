<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

if ( class_exists( 'Heckler' ) )
{
  return;
}

class Heckler
{
  static function init ()
  {
    add_action( 'init'                  , 'Heckler::make_post_type' );
    add_action( 'init'                  , 'Heckler::hook_heck_data' );
    add_action( 'add_meta_boxes'        , 'Heckler::make_meta_data' );
    add_action( 'save_post_heckler'     , 'Heckler::save_meta_data' );
    add_action( 'admin_enqueue_scripts' , 'Heckler::load_root_srcs' );
  }

  static function hook_heck_data ()
  {
    $qargs =
      [ 'post_type'       => 'heckler'
      , 'post_status'     => 'publish'
      , 'posts_per_page'  => -1
      ];

    $query = new WP_Query( $qargs );

    foreach ( $query->posts as $post )
    {
      $auxfn = function ( $row ) { return explode( ':' , $row ); };

      $hooks = get_post_meta( $post->ID , 'heckler_hooks' , true );
      $hooks = !$hooks ? [] : array_map( $auxfn , explode( ',' , $hooks ) );

      $displ = function () use ( $post )
      {
        echo apply_filters( 'the_content' , $post->post_content );
      };

      foreach ( $hooks as $hook )
      {
        if ( count( $hook ) !== 3 )
        {
          continue;
        }

        add_action( $hook[ 0 ] , $displ , $hook[ 1 ] );
      }
    }
  }

  static function make_post_type ()
  {
    $slug = 'heckler';
    $name = 'Heckler';
    $args =
      [ 'label'        => $name
      , 'show_ui'      => true
      , 'show_in_menu' => true
      , 'rewrite'      => false
      , 'supports'     => [ 'title' , 'editor' ]
      , 'menu_icon'    => 'dashicons-feedback'
      ];

    register_post_type( $slug , $args );
  }

  static function make_meta_data ()
  {
    $slug = 'heckler';
    $name = 'Options';
    $view = 'Heckler::view_meta_data';

    add_meta_box( $slug , $name , $view , $slug , 'normal' , 'high' );
  }

  static function save_meta_data ( $post_id )
  {
    $list = [];
    $hnon = isset( $_POST[ 'hnon' ] ) ? $_POST[ 'hnon' ] : false;

    $acts = array_map( 'Heckler::mend_text' , self::post_list( 'acts' ) );
    $ords = array_map( 'Heckler::mend_numb' , self::post_list( 'ords' ) );
    $args = array_map( 'Heckler::mend_numb' , self::post_list( 'args' ) );

    $same = array_map( 'count' , [ $acts , $ords , $args ] );
    $same = count( array_unique( $same ) ) === 1;

    $is_autosave = wp_is_post_autosave( $post_id );
    $is_revision = wp_is_post_revision( $post_id );
    $valid_nonce = $hnon && wp_verify_nonce( $hnon , 'hnon_save_meta' );

    if ( !$acts || !$ords || !$args || !$same )
    {
      return;
    }

    if ( $is_autosave || $is_revision || !$valid_nonce )
    {
      return;
    }

    foreach ( array_map( null , $acts , $ords , $args ) as $row )
    {
      $row = array_map( 'trim' , $row );

      if ( empty( $row[ 0 ] ) )
      {
        continue;
      }

      foreach ( $row as $key => $val )
      {
        $row[ $key ] = empty( $val ) ? 0 : $val;
      }

      $list[] = implode( ':' , $row );
    }

    update_post_meta( $post_id , 'heckler_hooks' , implode( ',' , $list ) );
  }

  static function view_meta_data ( $post )
  {
    $auxfn = function ( $row ) { return explode( ':' , $row ); };

    $hooks = get_post_meta( $post->ID , 'heckler_hooks' , true );
    $hooks = !$hooks ? [] : array_map( $auxfn , explode( ',' , $hooks ) );

    ?>
    <table class="hooks_table">
      <thead>
        <tr>
          <th>Hook</th>
          <th>Order</th>
          <th>Inputs</th>
          <th></th>
        </tr>
      </thead>

      <tbody>
        <?php foreach ( $hooks as $hook ) : ?>
        <tr>
          <td>
            <input type="text" name="acts[]" value="<?php echo $hook[0]; ?>" placeholder="<?php _e( 'new_hook' , 'heckler' ); ?>" />
          </td>

          <td>
            <input type="number" name="ords[]" value="<?php echo $hook[1]; ?>" placeholder="0" />
          </td>

          <td>
            <input type="number" name="args[]" value="<?php echo $hook[2]; ?>" placeholder="0" />
          </td>

          <td>
            <a href="#" class="del" title="<?php _e( 'Delete' , 'heckler' ); ?>">
              <span class="dashicons dashicons-dismiss"></span>
            </a>
          </td>
        </tr>
        <?php endforeach ?>
      </tbody>

      <tfoot>
        <tr>
          <td>
            <input type="text" name="acts[]" placeholder="<?php _e( 'new_hook' , 'heckler' ); ?>" />
          </td>

          <td>
            <input type="number" name="ords[]" placeholder="0" />
          </td>

          <td>
            <input type="number" name="args[]" placeholder="0" />
          </td>

          <td>
            <a href="#" class="add" title="<?php _e( 'Add' , 'heckler' ); ?>">
              <span class="dashicons dashicons-plus-alt"></span>
            </a>
          </td>
        </tr>
      </tfoot>
    </table>

    <?php wp_nonce_field( 'hnon_save_meta' , 'hnon'  ); ?>
    <?php
  }

  static function load_hook_data ( $post_id )
  {
    $hooks = get_post_meta( $post->ID , 'heckler_hooks' , true );

    if ( !$hooks )
    {
      return;
    }


  }

  static function load_root_srcs ()
  {
    $screen = get_current_screen();

    if ( !$screen || !( $screen->post_type === 'heckler' ) )
    {
      return;
    }

    HeckAux::load_jsc( 'heckler_jsc' , 'jsc/heckler.js'  , [ 'wp-codemirror' ] );
    HeckAux::load_css( 'heckler_css' , 'css/heckler.css' , [ 'wp-codemirror' ] );
  }

  static function post_list ( $name )
  {
    return isset( $_POST[ $name ] ) ? $_POST[ $name ] : [];
  }

  static function mend_text ( $text )
  {
    return trim( (string) $text );
  }

  static function mend_numb ( $numb )
  {
    $numb = trim( $numb );
    return is_numeric( $numb ) ? $numb : 0;
  }
}
