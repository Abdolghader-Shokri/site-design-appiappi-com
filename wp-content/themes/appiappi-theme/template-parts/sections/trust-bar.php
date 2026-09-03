<?php
/**
 * Four-item trust strip.
 */

$items = array(
	array( 'icon' => 'shield',      'label' => __( 'Secure & Fast Hosting', 'appiappi' ) ),
	array( 'icon' => 'headset',     'label' => __( 'Daily Support', 'appiappi' ) ),
	array( 'icon' => 'trending-up', 'label' => __( 'SEO & Continuous Growth', 'appiappi' ) ),
	array( 'icon' => 'refresh',     'label' => __( 'Updates & Security', 'appiappi' ) ),
);
?>
<section class="section section--tight trust-bar">
	<div class="container">
		<div class="trust-bar__grid">
			<?php foreach ( $items as $item ) : ?>
				<div class="icon-tile">
					<span class="icon-tile__icon"><?php echo appiappi_icon( $item['icon'] ); ?></span>
					<p class="icon-tile__title"><?php echo esc_html( $item['label'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
