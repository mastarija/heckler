<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

?>

<div class="heckler-container">
  <table class="heckler-table heckler-table-conf">
    <tr>
      <td colspan="2">
        <pre>[heckler id="<?php echo $hcid; ?>"]</pre>
      </td>
    </tr>

    <tr>
      <th>
        <label for="heckler-rule-conf">Use rule:</label>
      </th>

      <td>
        <input id="heckler-rule-conf" type="checkbox" name="heckler_rule_conf" value="true" <?php echo (bool) $rule ? 'checked' : ''; ?> />
      </td>
    </tr>

    <tr>
      <th>
        <label for="heckler-mode-conf-text">Use text:</label>
      </th>

      <td>
        <input id="heckler-mode-conf-text" type="radio" name="heckler_mode_conf" value="text" <?php echo $mode === 'text' ? 'checked' : ''; ?> />
      </td>
    </tr>

    <tr>
      <th>
        <label for="heckler-mode-conf-code">Use code:</label>
      </th>

      <td>
        <input id="heckler-mode-conf-code" type="radio" name="heckler_mode_conf" value="code" <?php echo $mode === 'code' ? 'checked' : ''; ?>/>
      </td>
    </tr>

    <tr>
      <th>
        <label for="heckler-mode-cvim">Vim mode:</label>
      </th>

      <td>
        <input id="heckler-mode-cvim" type="checkbox" name="heckler_cvim_conf" />
      </td>
    </tr>
  </table>

  <?php echo $nonc; ?>
</div>
