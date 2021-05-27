<?php

if ( !defined( 'ABSPATH' ) )
{
  return;
}

?>

<div class="heckler-container">
  <div class="heckler-editor">
    <header><pre>function action( ...$args ) {</pre></header>
    <textarea id="heckler-code-editor" class="heckler-code-editor" name="heckler_code_meta"><?php echo $code; ?></textarea>
    <footer><pre>}</pre></footer>
  </div>

  <?php echo $nonc; ?>
</div>