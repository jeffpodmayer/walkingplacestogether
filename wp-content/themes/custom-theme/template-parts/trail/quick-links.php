<?php
/**
 * Quick links template part
 * Args: links (assoc array label => href)
 */
$links = $args['links'] ?? [];
if ( empty( $links ) ) {
  return;
}
?>
<div class="trail-section trail-quick-links-section">
  <div class="trail-details-right">
    <h2 class="trail-section-title">Quick Links</h2>
    <div class="trail-quick-links">
      <?php foreach ( $links as $label => $href ) : ?>
        <a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $label ); ?></a>
      <?php endforeach; ?>
    </div>
  </div>
</div>