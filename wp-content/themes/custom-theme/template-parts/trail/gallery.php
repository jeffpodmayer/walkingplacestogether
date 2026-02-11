<?php
/**
 * Gallery template part
 * Args: images, extra_class
 */
$images = $args['images'] ?? null;
if ( ! $images || ! is_array( $images ) ) {
  return;
}

$count = count( $images );
if ( $count === 1 ) {
  $layout_class = 'trail-gallery--1';
} elseif ( $count === 2 || $count === 4 ) {
  $layout_class = 'trail-gallery--2';
} else {
  $layout_class = 'trail-gallery--3';
}

$extra_class = $args['extra_class'] ?? '';
?>
<div class="trail-image-gallery <?php echo esc_attr( $layout_class . ' ' . $extra_class ); ?>">
  <?php foreach ( $images as $image ) :
    $image_id = $image['attachment']->ID;
    $full_url = $image['metadata']['full']['file_url'];
    $caption  = wp_get_attachment_caption( $image_id );
  ?>
    <figure class="trail-gallery-item">
      <a href="<?php echo esc_url( $full_url ); ?>" class="trail-lightbox" data-title="<?php echo esc_attr( $caption ); ?>">
        <?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
      </a>
      <?php if ( $caption ) : ?>
        <figcaption class="trail-gallery-caption"><?php echo esc_html( $caption ); ?></figcaption>
      <?php endif; ?>
    </figure>
  <?php endforeach; ?>
</div>