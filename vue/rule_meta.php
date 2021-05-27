<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

?>

<div class="heckler-container">
  <div class="heckler-editor">
    <header><pre>function rule() {</pre></header>
    <textarea id="heckler-rule-editor" class="heckler-rule-editor" name="heckler_rule_meta"><?php echo $rule; ?></textarea>
    <footer><pre>}</pre></footer>
  </div>

  <?php echo $nonc; ?>
</div>