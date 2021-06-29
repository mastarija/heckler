<?php namespace Mastarija\Heckler; ?>

<?php if ( !$conf_hook ): ?>
  <code class="mini-code hook-disabled">Disabled</code>
<?php else: ?>
  <code class="mini-code hook-enabled"><?php echo $hook_active; ?> / <?php echo $hook_total; ?></code>
<?php endif ?>
