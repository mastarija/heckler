<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

?>

<div class="heckler-container">
  <table class="heckler-table heckler-excel">
    <thead>
      <tr>
        <th class="heckler-hook-name">Name</th>
        <th class="heckler-hook-args">Args</th>
        <th class="heckler-hook-sort">Sort</th>
        <th class="heckler-hook-kill"></th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ( $hooks as $hook ): ?>
        <?php row ( $hook[ 'name' ] , $hook[ 'args' ] , $hook[ 'sort' ] ); ?>
      <?php endforeach ?>
    </tbody>

    <tfoot>
      <?php row ( '' , '' , '' ); ?>
    </tfoot>
  </table>

  <?php echo $nonce; ?>
</div>

<?php

function row ( $name , $args , $sort )
{
?>
  <tr>
    <td class="heckler-hook-name">
      <input
        type="text"
        name="heckler_name[]"
        class="heckler-name"
        value="<?php echo $name; ?>"
        placeholder="hook_name"
      />
    </td>

    <td class="heckler-hook-args">
      <input
        type="number"
        name="heckler_args[]"
        class="heckler-args"
        value="<?php echo $args; ?>"
        placeholder="0"
      />
    </td>

    <td class="heckler-hook-sort">
      <input
        type="number"
        name="heckler_sort[]"
        class="heckler-sort"
        value="<?php echo $sort; ?>"
        placeholder="0"
      />
    </td>

    <td class="heckler-hook-kill">
      <span class="dashicons"></span>
    </td>
  </tr>
<?php
}
