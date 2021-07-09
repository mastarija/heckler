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
          <input name="hook_list[<?php echo esc_attr( $i ); ?>][tag]" type="text" value="<?php echo esc_attr( $hook[ 'tag' ] ); ?>" placeholder="example_hook_name" />
        </td>

        <td class="heckler-hook-ord">
          <input name="hook_list[<?php echo esc_attr( $i ); ?>][ord]" type="number" value="<?php echo esc_attr( $hook[ 'ord' ] ); ?>" placeholder="0" step="any" />
        </td>

        <td class="heckler-hook-arg">
          <input name="hook_list[<?php echo esc_attr( $i ); ?>][arg]" type="number" value="<?php echo esc_attr( $hook[ 'arg' ] ); ?>" placeholder="0" min="0" step="1" />
        </td>

        <td class="heckler-hook-act">
          <input name="hook_list[<?php echo esc_attr( $i ); ?>][act]" type="checkbox" <?php echo $hook[ 'act' ] === true ? 'checked' : ''; ?> />
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
          <input name="hook_list[x][ord]" type="number" value="" placeholder="0" step="any" />
        </td>

        <td class="heckler-hook-arg">
          <input name="hook_list[x][arg]" type="number" value="" placeholder="0" min="0" step="1" />
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

  <?php // echo esc_html( $nonc ); ?>
  <?php make_nonc( 'nonc_mastarija_heckler_save_meta_hook' , true ); ?>
</div>