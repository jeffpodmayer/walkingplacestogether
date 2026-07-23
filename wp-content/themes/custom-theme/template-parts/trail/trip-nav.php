<?php
/**
 * Trip report sidebar navigation.
 * Args: items — array of [
 *   'href'  => '#anchor',
 *   'label' => 'Day 3',
 *   'sub'   => 'Upper Cascade Basin to Alta Lake' (optional),
 *   'icon'  => 'compass' | 'map' | 'backpack' | 'link' | 'image' | 'bookmark' (optional),
 * ]
 */
$items = $args['items'] ?? [];
if ( empty( $items ) ) {
  return;
}
?>
<nav class="trip-nav" aria-label="Trip report sections">
  <ul class="trip-nav-list">
    <?php foreach ( $items as $item ) : ?>
      <li class="trip-nav-item<?php echo empty( $item['icon'] ) ? ' trip-nav-item--dot' : ''; ?>">
        <a href="<?php echo esc_url( $item['href'] ); ?>">
          <?php if ( ! empty( $item['icon'] ) ) : ?>
            <span class="trip-nav-bullet">
              <?php echo custom_theme_trip_meta_icon( $item['icon'] ); ?>
            </span>
          <?php else : ?>
            <span class="trip-nav-bullet trip-nav-bullet--dot"></span>
          <?php endif; ?>
          <span class="trip-nav-text">
            <span class="trip-nav-label"><?php echo esc_html( $item['label'] ); ?></span>
            <?php if ( ! empty( $item['sub'] ) ) : ?>
              <span class="trip-nav-sub"><?php echo esc_html( $item['sub'] ); ?></span>
            <?php endif; ?>
          </span>
        </a>
      </li>
    <?php endforeach; ?>
  </ul>
</nav>
