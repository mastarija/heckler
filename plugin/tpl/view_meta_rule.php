<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<div class="heckler-container">
  <div class="heckler-editor">
    <header>
      <pre>function rule() {</pre>
    </header>

    <div class="code">
      <textarea id="code_rule" name="code_rule"><?php echo esc_textarea( $code_rule ); ?></textarea>
    </div>

    <footer>
      <pre>}</pre>
      <div class="vim-enabler">
        <input id="vim-rule" type="checkbox" />
        <label for="vim-rule"></label>
      </div>
    </footer>
  </div>
</div>

<?php // echo esc_html( $nonc ); ?>
<?php make_nonc( 'nonc_mastarija_heckler_save_meta_rule' , true ); ?>
