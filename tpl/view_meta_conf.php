<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<table>
  <tr>
    <td colspan="2">
      <code>[heckler id="<?php echo esc_html( $post ); ?>"]</code>
    </td>
  </tr>

  <tr>
    <td>Hook</td>
    <td>
      <label for="hook_on">On</label>
      <input id="hook_on" name="conf_hook" type="radio" value="true" <?php echo $conf_hook === true ? 'checked' : ''; ?> />

      <br />

      <label for="hook_off">Off</label>
      <input id="hook_off" name="conf_hook" type="radio" value="false" <?php echo $conf_hook === false ? 'checked' : ''; ?> />
    </td>
  </tr>

  <tr>
    <td>Rule</td>
    <td>
      <label for="rule_on">On</label>
      <input id="rule_on" name="conf_rule" type="radio" value="true" <?php echo $conf_rule === true ? 'checked' : ''; ?> />

      <br />

      <label for="rule_off">Off</label>
      <input id="rule_off" name="conf_rule" type="radio" value="false" <?php echo $conf_rule === false ? 'checked' : ''; ?> />
    </td>
  </tr>

  <tr>
    <td>Mode</td>
    <td>
      <label for="mode_text">Text</label>
      <input id="mode_text" name="conf_mode" type="radio" value="<?php echo MODE::TEXT; ?>" <?php echo $conf_mode === MODE::TEXT ? 'checked' : ''; ?>/>

      <br />

      <label for="mode_code">Code</label>
      <input id="mode_code" name="conf_mode" type="radio" value="<?php echo MODE::CODE; ?>" <?php echo $conf_mode === MODE::CODE ? 'checked' : ''; ?>/>
    </td>
  </tr>
</table>

<?php // echo esc_html( $nonc ); ?>
<?php make_nonc( 'nonc_mastarija_heckler_save_meta_conf' , true ); ?>
