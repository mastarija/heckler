<?php namespace Mastarija\Heckler; ?>
<?php if ( !defined( 'ABSPATH' ) ) return; ?>

<?php $conf_rule = $conf_rule ? 'enabled' : 'disabled'; ?>

<code class="mini-code rule-<?php echo esc_attr( $conf_rule ); ?>"><?php echo esc_html( ucfirst( $conf_rule ) ); ?></code>
