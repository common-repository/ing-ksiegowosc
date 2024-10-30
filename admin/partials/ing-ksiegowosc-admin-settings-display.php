<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @var $this
 */

$name = $this->plugin_name . '_settings';

?>
<div class="wrap">
	<h2><?php echo esc_html__( 'ING Księgowość', 'ing-ksiegowosc' ); ?></h2>

	<p><?php echo esc_html__( 'Configure module', 'ing-ksiegowosc' ); ?></p>

	<?php settings_errors(); ?>
	<form method="POST" action="options.php">
		<?php
		settings_fields( $name );
		do_settings_sections( $name );
		submit_button();
		?>
	</form>
</div>