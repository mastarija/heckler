<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<div class="heckler-container">
  <table id="hook_list" class="heckler-table heckler-excel">
    <thead>
      <tr>
        <th></th>
        <th>Tag</th>
        <th>Ord</th>
        <th>Arg</th>
        <th>Act</th>
        <th></th>
      </tr>
    </thead>

    <tbody>
      <?php foreach ( $hook_list as $i => $hook ): ?>
      <tr class="hook_item">
        <td class="heckler-hook-hdl">

        </td>

        <td class="heckler-hook-tag">
          <input name="hook_list[<?php echo $i; ?>][tag]" type="text" value="<?php echo $hook[ 'tag' ]; ?>" placeholder="example_hook_name" />
        </td>

        <td class="heckler-hook-ord">
          <input name="hook_list[<?php echo $i; ?>][ord]" type="number" value="<?php echo $hook[ 'ord' ]; ?>" placeholder="0" />
        </td>

        <td class="heckler-hook-arg">
          <input name="hook_list[<?php echo $i; ?>][arg]" type="number" value="<?php echo $hook[ 'arg' ]; ?>" placeholder="0" />
        </td>

        <td class="heckler-hook-act">
          <input name="hook_list[<?php echo $i; ?>][act]" type="checkbox" <?php echo $hook[ 'act' ] === true ? 'checked' : ''; ?> />
        </td>

        <td class="heckler-hook-del">
          <span class="action"></span>
        </td>
      </tr>
      <?php endforeach ?>
    </tbody>

    <tfoot>
      <tr class="hook_item">
        <td class="heckler-hook-hdl">

        </td>

        <td class="heckler-hook-tag">
          <input name="hook_list[x][tag]" type="text" value="" placeholder="example_hook_name" />
        </td>

        <td class="heckler-hook-ord">
          <input name="hook_list[x][ord]" type="number" value="" placeholder="0" />
        </td>

        <td class="heckler-hook-arg">
          <input name="hook_list[x][arg]" type="number" value="" placeholder="0" />
        </td>

        <td class="heckler-hook-act">
          <input name="hook_list[x][act]" type="checkbox" checked />
        </td>

        <td class="heckler-hook-del">
          <span class="action"></span>
        </td>
      </tr>
    </tfoot>
  </table>

  <?php echo $nonc; ?>
</div>