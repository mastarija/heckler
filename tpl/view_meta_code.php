<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<div class="heckler-container">
  <div class="heckler-editor">
    <header>
      <pre>function code( ...$args ) {</pre>
    </header>

    <textarea id="code_code" name="code_code"><?php echo $code_code; ?></textarea>

    <footer>
      <pre>}</pre>
    </footer>
  </div>
</div>
<?php echo $nonc; ?>
