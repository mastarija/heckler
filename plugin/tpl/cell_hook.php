<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<?php if ( !$conf_hook ): ?>
  <code class="mini-code hook-disabled">Disabled</code>
<?php else: ?>
  <code class="mini-code hook-enabled"><?php echo esc_html( $hook_active ); ?> / <?php echo esc_html( $hook_total ); ?></code>
<?php endif ?>
