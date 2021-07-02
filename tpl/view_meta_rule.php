<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<div class="heckler-container">
  <div class="heckler-editor">
    <header>
      <pre>function rule() {</pre>
    </header>

    <textarea id="code_rule" name="code_rule"><?php echo $code_rule; ?></textarea>

    <footer>
      <pre>}</pre>
      <div class="vim-enabler">
        <input id="vim-rule" type="checkbox" />
        <label for="vim-rule"></label>
      </div>
    </footer>
  </div>
</div>

<?php echo $nonc; ?>
