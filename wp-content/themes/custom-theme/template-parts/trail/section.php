<?php
/**
 * Section template part
 * Args: id, title, content, before_content (optional), after_content (optional)
 */
if ( empty( $args['content'] ) && empty( $args['after_content'] ) ) {
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
  <?php if ( ! empty( $args['content'] ) ) : ?>
    <div class="trail-section-content">
      <?php echo wp_kses_post( $args['content'] ); ?>
    </div>
  <?php endif; ?>
  <?php if ( ! empty( $args['after_content'] ) ) { echo $args['after_content']; } ?>
</div>
