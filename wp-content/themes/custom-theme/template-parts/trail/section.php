<?php
/**
 * Section template part
 * Args: id, title, content,
 *       before_content (optional, sits beside title in header),
 *       intro_content (optional, between header and body),
 *       after_content (optional, after body)
 */
if ( empty( $args['content'] ) && empty( $args['after_content'] ) && empty( $args['intro_content'] ) ) {
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
  <?php if ( ! empty( $args['intro_content'] ) ) { echo $args['intro_content']; } ?>
  <?php if ( ! empty( $args['content'] ) ) : ?>
    <div class="trail-section-content">
      <?php echo wp_kses_post( $args['content'] ); ?>
    </div>
  <?php endif; ?>
  <?php if ( ! empty( $args['after_content'] ) ) { echo $args['after_content']; } ?>
</div>
