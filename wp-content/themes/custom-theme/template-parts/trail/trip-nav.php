<?php
/**
 * Trip report sidebar navigation (+ mobile bottom dock).
 * Args: items — array of [
 *   'href'  => '#anchor',
 *   'label' => 'Day 3',
 *   'sub'   => 'Upper Cascade Basin to Alta Lake' (optional),
 *   'icon'  => 'compass' | 'map' | 'backpack' | 'link' | 'image' | 'bookmark' (optional),
 * ]
 *       show_map — bool, add Map toggle to the mobile dock
 */
$items = $args['items'] ?? [];
if ( empty( $items ) ) {
  return;
}

$show_map = ! empty( $args['show_map'] );

$short_label = static function( $label, $icon = '' ) {
  $map = [
    'Trip Overview'        => 'Overview',
    'Trip Report'          => 'Report',
    'Gear Notes'           => 'Gear',
    'Gear'                 => 'Gear',
    'Resources & Links'    => 'Links',
    'Photo Gallery'        => 'Photos',
    'Related Trip Reports' => 'Related',
  ];
  if ( isset( $map[ $label ] ) ) {
    return $map[ $label ];
  }
  if ( $icon === 'compass' ) {
    return 'Overview';
  }
  if ( $icon === 'backpack' ) {
    return 'Gear';
  }
  if ( $icon === 'image' ) {
    return 'Photos';
  }
  if ( $icon === 'link' ) {
    return 'Links';
  }
  if ( $icon === 'bookmark' ) {
    return 'Related';
  }
  return $label;
};

$section_items = [];
$day_items     = [];
foreach ( $items as $item ) {
  if ( empty( $item['icon'] ) ) {
    $day_items[] = $item;
  } else {
    $section_items[] = $item;
  }
}

$by_icon = [];
foreach ( $section_items as $item ) {
  $by_icon[ $item['icon'] ] = $item;
}

$dock_slots = [];
$more_items = [];

$group_for_icon = static function( $icon ) {
  if ( $icon === 'compass' ) {
    return 'overview';
  }
  if ( $icon === 'backpack' ) {
    return 'gear';
  }
  if ( $icon === 'image' ) {
    return 'photos';
  }
  if ( $icon === 'link' ) {
    return 'links';
  }
  if ( $icon === 'bookmark' ) {
    return 'related';
  }
  return $icon;
};

// Priority order for the mobile dock (fill up to 5; overflow → More).
$candidates = [];

if ( ! empty( $by_icon['compass'] ) ) {
  $item         = $by_icon['compass'];
  $candidates[] = [
    'type'  => 'link',
    'group' => 'overview',
    'icon'  => 'compass',
    'label' => $short_label( $item['label'], 'compass' ),
    'href'  => $item['href'],
  ];
}

if ( ! empty( $day_items ) ) {
  $first_day    = $day_items[0];
  $candidates[] = [
    'type'  => 'link',
    'group' => 'days',
    'icon'  => 'list',
    'label' => 'Days',
    'href'  => $first_day['href'],
  ];
}

if ( $show_map ) {
  $candidates[] = [
    'type'  => 'map',
    'group' => 'map',
    'icon'  => 'map',
    'label' => 'Map',
  ];
}

$rest_icons = [ 'backpack', 'image', 'link', 'bookmark' ];
foreach ( $rest_icons as $icon_key ) {
  if ( empty( $by_icon[ $icon_key ] ) ) {
    continue;
  }
  $item         = $by_icon[ $icon_key ];
  $candidates[] = [
    'type'  => 'link',
    'group' => $group_for_icon( $icon_key ),
    'icon'  => $icon_key,
    'label' => $short_label( $item['label'], $icon_key ),
    'href'  => $item['href'],
    'item'  => $item,
  ];
}

foreach ( $section_items as $item ) {
  if ( in_array( $item['icon'], [ 'compass', 'backpack', 'image', 'link', 'bookmark' ], true ) ) {
    continue;
  }
  $candidates[] = [
    'type'  => 'link',
    'group' => $group_for_icon( $item['icon'] ),
    'icon'  => $item['icon'],
    'label' => $short_label( $item['label'], $item['icon'] ),
    'href'  => $item['href'],
    'item'  => $item,
  ];
}

if ( count( $candidates ) <= 5 ) {
  $dock_slots = $candidates;
} else {
  $dock_slots = array_slice( $candidates, 0, 4 );
  $overflow   = array_slice( $candidates, 4 );
  foreach ( $overflow as $slot ) {
    if ( ! empty( $slot['item'] ) ) {
      $more_items[] = $slot['item'];
      continue;
    }
    if ( $slot['type'] !== 'link' || empty( $slot['href'] ) ) {
      continue;
    }
    $more_items[] = [
      'icon'  => $slot['icon'] === 'list' ? '' : $slot['icon'],
      'label' => $slot['label'],
      'href'  => $slot['href'],
    ];
  }
  $dock_slots[] = [
    'type'  => 'sheet',
    'group' => 'more',
    'sheet' => 'more',
    'icon'  => 'more',
    'label' => 'More',
  ];
}
?>
<nav class="trip-nav" aria-label="Trip report sections">
  <ul class="trip-nav-list">
    <?php foreach ( $items as $item ) : ?>
      <li class="trip-nav-item<?php echo empty( $item['icon'] ) ? ' trip-nav-item--dot' : ''; ?>">
        <a class="js-trip-nav-link" href="<?php echo esc_url( $item['href'] ); ?>">
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

<?php if ( ! empty( $dock_slots ) ) : ?>
  <div class="trip-dock" data-trip-dock>
    <nav class="trip-dock-bar" aria-label="Trip sections">
      <?php foreach ( $dock_slots as $slot ) : ?>
        <?php if ( $slot['type'] === 'link' ) : ?>
          <a
            class="trip-dock-item js-trip-nav-link"
            href="<?php echo esc_url( $slot['href'] ); ?>"
            data-trip-dock-group="<?php echo esc_attr( $slot['group'] ); ?>"
          >
            <span class="trip-dock-icon"><?php echo custom_theme_trip_meta_icon( $slot['icon'] ); ?></span>
            <span class="trip-dock-label"><?php echo esc_html( $slot['label'] ); ?></span>
          </a>
        <?php elseif ( $slot['type'] === 'map' ) : ?>
          <button
            type="button"
            class="trip-dock-item"
            data-trip-dock-group="map"
            data-trip-map-toggle
            aria-pressed="false"
            aria-label="Open map while you scroll"
          >
            <span class="trip-dock-icon"><?php echo custom_theme_trip_meta_icon( 'map' ); ?></span>
            <span class="trip-dock-label">Map</span>
          </button>
        <?php else : ?>
          <button
            type="button"
            class="trip-dock-item"
            data-trip-dock-group="<?php echo esc_attr( $slot['group'] ); ?>"
            data-trip-sheet="<?php echo esc_attr( $slot['sheet'] ); ?>"
            aria-expanded="false"
            aria-controls="trip-dock-sheet-<?php echo esc_attr( $slot['sheet'] ); ?>"
          >
            <span class="trip-dock-icon"><?php echo custom_theme_trip_meta_icon( $slot['icon'] ); ?></span>
            <span class="trip-dock-label"><?php echo esc_html( $slot['label'] ); ?></span>
          </button>
        <?php endif; ?>
      <?php endforeach; ?>
    </nav>
  </div>

  <div class="trip-dock-backdrop" data-trip-sheet-backdrop hidden></div>

  <?php if ( ! empty( $more_items ) ) : ?>
    <div
      class="trip-dock-sheet"
      id="trip-dock-sheet-more"
      data-trip-sheet-panel="more"
      role="dialog"
      aria-modal="true"
      aria-label="More"
      hidden
    >
      <div class="trip-dock-sheet-handle" aria-hidden="true"></div>
      <div class="trip-dock-sheet-header">
        <h2 class="trip-dock-sheet-title">More</h2>
        <button type="button" class="trip-dock-sheet-close" data-trip-sheet-close aria-label="Close">
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
        </button>
      </div>
      <ul class="trip-dock-sheet-list">
        <?php foreach ( $more_items as $item ) : ?>
          <li>
            <a class="trip-dock-sheet-link js-trip-nav-link" href="<?php echo esc_url( $item['href'] ); ?>">
              <?php if ( ! empty( $item['icon'] ) ) : ?>
                <span class="trip-dock-sheet-link-icon"><?php echo custom_theme_trip_meta_icon( $item['icon'] ); ?></span>
              <?php endif; ?>
              <span class="trip-dock-sheet-link-text">
                <span class="trip-dock-sheet-link-label"><?php echo esc_html( $item['label'] ); ?></span>
              </span>
            </a>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  <?php endif; ?>
<?php endif; ?>
