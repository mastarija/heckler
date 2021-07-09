<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<div class="heckler-container">
  <div class="heckler-editor">
    <header>
      <pre>function code( ...$args ) {</pre>
    </header>

    <div class="code">
      <textarea id="code_code" name="code_code"><?php echo esc_textarea( $code_code ); ?></textarea>
    </div>

    <footer>
      <pre>}</pre>
      <div class="vim-enabler">
        <input id="vim-code" type="checkbox" />
        <label for="vim-code"></label>
      </div>
    </footer>
  </div>
</div>

<?php // echo esc_html( $nonc ); ?>
<?php make_nonc( 'nonc_mastarija_heckler_save_meta_code' , true ); ?>