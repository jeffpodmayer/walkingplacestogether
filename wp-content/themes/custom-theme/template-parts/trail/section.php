<?php
/**
 * Section template part
 * Args: id, title, content
 */
if ( empty( $args['content'] ) ) {
  return;
}
$id = $args['id'] ?? '';
$title = $args['title'] ?? '';
?>
<div class="trail-section" id="<?php echo esc_attr( $id ); ?>">
  <h2 class="trail-section-title"><?php echo esc_html( $title ); ?></h2>
  <div class="trail-section-content">
    <?php echo wp_kses_post( $args['content'] ); ?>
  </div>
</div>
