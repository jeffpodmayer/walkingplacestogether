<?php
/**
 * Photo gallery grid (3x3 collapsed)
 * Args: images
 */
$images = $args['images'] ?? null;
if ( ! $images || ! is_array( $images ) ) {
  return;
}
?>
<div class="trail-section trail-photo-gallery" id="trail-gallery">
  <h2 class="trail-section-title">Photo Gallery</h2>

  <div class="trail-gallery-grid" data-collapsed="true">
    <?php foreach ( $images as $image ) :
      $image_id = $image['attachment']->ID;
      $full_url = $image['metadata']['full']['file_url'];
      $caption  = wp_get_attachment_caption( $image_id );
    ?>
      <figure class="trail-gallery-grid-item">
        <a href="<?php echo esc_url( $full_url ); ?>" class="trail-lightbox" data-title="<?php echo esc_attr( $caption ); ?>">
          <?php echo wp_get_attachment_image( $image_id, 'large', false, array( 'loading' => 'lazy' ) ); ?>
        </a>
        <?php if ( $caption ) : ?>
          <figcaption class="trail-gallery-caption"><?php echo esc_html( $caption ); ?></figcaption>
        <?php endif; ?>
      </figure>
    <?php endforeach; ?>
  </div>

  <button class="trail-gallery-toggle" type="button">Show all photos</button>
</div>