<?php
/**
 * Section template part
 * Args: id, title, content, before_content (optional, pre-built HTML)
 */
if ( empty( $args['content'] ) ) {
  return;
}
$id = $args['id'] ?? '';
$title = $args['title'] ?? '';
?>
<div class="trail-section" id="<?php echo esc_attr( $id ); ?>">
  <?php if ( ! empty( $args['before_content'] ) ) : ?>
    <div class="trail-section-header">
      <h2 class="trail-section-title"><?php echo esc_html( $title ); ?></h2>
      <?php echo $args['before_content']; ?>
    </div>
  <?php else : ?>
    <h2 class="trail-section-title"><?php echo esc_html( $title ); ?></h2>
  <?php endif; ?>
  <div class="trail-section-content">
    <?php echo wp_kses_post( $args['content'] ); ?>
  </div>
</div>
