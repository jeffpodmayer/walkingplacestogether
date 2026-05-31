<?php
/**
 * Front Page template.
 *
 * @package Custom_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// ── Stats (cached for 12 hours) ───────────────────────────────────────────
// Sums distance and trip_duration_days from trail CPT posts and trip reports.
// Cache is cleared when any trail or trip report post is saved.
$stats = get_transient( 'custom_theme_home_stats' );
if ( false === $stats ) {
  $total_miles = 0;
  $total_days  = 0;

  $trail_ids = get_posts( [
    'post_type'      => 'trail',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
  ] );

  foreach ( $trail_ids as $id ) {
    $total_miles += (float) get_field( 'distance', $id );
    $total_days  += (float) get_field( 'trip_duration_days', $id );
  }

  $report_ids = get_posts( [
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'post_status'    => 'publish',
    'fields'         => 'ids',
    'meta_query'     => [ [ 'key' => 'is_trip_report', 'value' => '1' ] ],
  ] );

  foreach ( $report_ids as $id ) {
    $total_miles += (float) get_field( 'distance', $id );
    $total_days  += (float) get_field( 'trip_duration_days', $id );
  }

  $stats = [
    'miles' => (int) round( $total_miles ),
    'days'  => (int) round( $total_days ),
  ];

  set_transient( 'custom_theme_home_stats', $stats, 12 * HOUR_IN_SECONDS );
}

// ── Hero image ─────────────────────────────────────────────────────────────
// Set via the featured image on this page in the WordPress admin.
$hero_url = get_the_post_thumbnail_url( get_option( 'page_on_front' ), 'full' );

// ── About photos ───────────────────────────────────────────────────────────
// Add two ACF image fields on this page:
//   Field name: carolyn_photo  (return format: Image Array or URL)
//   Field name: jeff_photo     (return format: Image Array or URL)
$page_id      = (int) get_option( 'page_on_front' );
$carolyn_photo = function_exists( 'get_field' ) ? get_field( 'carolyn_photo', $page_id ) : null;
$jeff_photo    = function_exists( 'get_field' ) ? get_field( 'jeff_photo', $page_id ) : null;
$carolyn_bio   = function_exists( 'get_field' ) ? get_field( 'carolyn_bio', $page_id ) : '';
$jeff_bio      = function_exists( 'get_field' ) ? get_field( 'jeff_bio', $page_id ) : '';

$carolyn_src = is_array( $carolyn_photo ) ? ( $carolyn_photo['url'] ?? '' ) : (string) $carolyn_photo;
$jeff_src    = is_array( $jeff_photo )    ? ( $jeff_photo['url'] ?? '' )    : (string) $jeff_photo;

// ── Why We Do It ───────────────────────────────────────────────────────────
// Add an ACF textarea or WYSIWYG field on this page:
//   Field name: why_we_do_it
$why_we_do_it = function_exists( 'get_field' ) ? get_field( 'why_we_do_it', $page_id ) : '';

// ── Recent trip reports ────────────────────────────────────────────────────
$recent_reports = get_posts( [
  'post_type'      => 'post',
  'posts_per_page' => 3,
  'post_status'    => 'publish',
] );

// ── Recent trails ──────────────────────────────────────────────────────────
$recent_trails = get_posts( [
  'post_type'      => 'trail',
  'posts_per_page' => 3,
  'post_status'    => 'publish',
] );

get_header();
?>

<div id="primary" class="home-page">

  <section class="home-hero"<?php if ( $hero_url ) : ?> style="background-image:url('<?php echo esc_url( $hero_url ); ?>')"<?php endif; ?>>
    <div class="home-hero__stats">
      <div class="home-hero__stat">
        <span class="home-stat-value"><?php echo esc_html( number_format( $stats['miles'] ) ); ?></span>
        <span class="home-stat-label">Miles Hiked</span>
      </div>
      <div class="home-hero__stat-divider"></div>
      <div class="home-hero__stat">
        <span class="home-stat-value"><?php echo esc_html( number_format( $stats['days'] ) ); ?></span>
        <span class="home-stat-label">Days Outside</span>
      </div>
      <div class="home-hero__stat-divider"></div>
      <div class="home-hero__stat">
        <span class="home-stat-value">120</span>
        <span class="home-stat-label">Peaks Climbed</span>
      </div>
    </div>
  </section>

  <?php if ( $recent_reports ) : ?>
  <section class="home-reports">
    <div class="home-reports__inner">
      <h2 class="home-reports__title">Recent Trip Reports</h2>
      <div class="home-reports__grid">

        <?php foreach ( $recent_reports as $report ) : ?>
          <?php
          $thumb      = get_the_post_thumbnail_url( $report->ID, 'large' );
          $hero       = function_exists( 'get_field' ) ? get_field( 'hero_image', $report->ID ) : null;
          $img_url    = $thumb ?: ( is_array( $hero ) ? ( $hero['url'] ?? '' ) : (string) $hero );
          $distance   = function_exists( 'get_field' ) ? (float) get_field( 'distance', $report->ID ) : 0;
          $days       = function_exists( 'get_field' ) ? (float) get_field( 'trip_duration_days', $report->ID ) : 0;
          $start_date = function_exists( 'get_field' ) ? get_field( 'start_date', $report->ID ) : '';
          ?>
          <a class="home-report-card" href="<?php echo esc_url( get_permalink( $report->ID ) ); ?>">

            <div class="home-report-card__image">
              <?php if ( $img_url ) : ?>
                <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $report->post_title ); ?>">
              <?php else : ?>
                <div class="home-report-card__image-placeholder"></div>
              <?php endif; ?>
            </div>

            <div class="home-report-card__body">
              <?php if ( $start_date ) : ?>
                <span class="home-report-card__date"><?php echo esc_html( date( 'F Y', strtotime( $start_date ) ) ); ?></span>
              <?php endif; ?>
              <h3 class="home-report-card__title"><?php echo esc_html( $report->post_title ); ?></h3>
              <div class="home-report-card__meta">
                <?php if ( $distance ) : ?>
                  <span><?php echo esc_html( number_format( $distance, 1 ) ); ?> mi</span>
                <?php endif; ?>
                <?php if ( $days ) : ?>
                  <span><?php echo esc_html( (int) $days ); ?> days</span>
                <?php endif; ?>
              </div>
            </div>

          </a>
        <?php endforeach; ?>

      </div>

      <div class="home-reports__footer">
        <a class="home-reports__all-link" href="<?php echo esc_url( home_url( '/trip-reports/' ) ); ?>">View All Trip Reports →</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <?php if ( $recent_trails ) : ?>
  <section class="home-trails">
    <div class="home-trails__inner">
      <h2 class="home-trails__title">Trails &amp; Routes</h2>
      <div class="home-trails__grid">

        <?php foreach ( $recent_trails as $trail ) : ?>
          <?php
          $thumb = '';
          if ( function_exists( 'get_field' ) ) {
            $thumb = custom_theme_get_image_url( get_field( 'hero_image', $trail->ID ), 'large' );
          }
          if ( ! $thumb ) {
            $thumb = get_the_post_thumbnail_url( $trail->ID, 'large' );
          }
          $distance   = function_exists( 'get_field' ) ? (float) get_field( 'distance', $trail->ID ) : 0;
          $days       = function_exists( 'get_field' ) ? (float) get_field( 'trip_duration_days', $trail->ID ) : 0;
          $region     = function_exists( 'get_field' ) ? get_field( 'region', $trail->ID ) : '';
          $trail_date = function_exists( 'get_field' ) ? custom_theme_format_trail_date_range( get_field( 'start_date', $trail->ID ), get_field( 'end_date', $trail->ID ) ) : '';
          if ( is_array( $region ) ) {
            $region_parts = [];
            foreach ( $region as $item ) {
              if ( is_object( $item ) && isset( $item->name ) ) $region_parts[] = $item->name;
              elseif ( is_object( $item ) && isset( $item->post_title ) ) $region_parts[] = $item->post_title;
              elseif ( is_scalar( $item ) ) $region_parts[] = $item;
            }
            $region = implode( ', ', $region_parts );
          }
          ?>
          <a class="home-trail-card" href="<?php echo esc_url( get_permalink( $trail->ID ) ); ?>">

            <div class="home-trail-card__image">
              <?php if ( $thumb ) : ?>
                <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php echo esc_attr( $trail->post_title ); ?>">
              <?php else : ?>
                <div class="home-trail-card__image-placeholder"></div>
              <?php endif; ?>
            </div>

            <div class="home-trail-card__body">
              <?php if ( $region ) : ?>
                <span class="home-trail-card__region"><?php echo esc_html( $region ); ?></span>
              <?php endif; ?>
              <h3 class="home-trail-card__title"><?php echo esc_html( $trail->post_title ); ?></h3>
              <?php if ( $trail_date ) : ?>
                <span class="home-trail-card__date"><?php echo esc_html( $trail_date ); ?></span>
              <?php endif; ?>
              <div class="home-trail-card__meta">
                <?php if ( $distance ) : ?>
                  <span><?php echo esc_html( number_format( $distance, 1 ) ); ?> mi</span>
                <?php endif; ?>
                <?php if ( $days ) : ?>
                  <span><?php echo esc_html( (int) $days ); ?> days</span>
                <?php endif; ?>
              </div>
            </div>

          </a>
        <?php endforeach; ?>

      </div>

      <div class="home-trails__footer">
        <a class="home-trails__all-link" href="<?php echo esc_url( home_url( '/trails/' ) ); ?>">View All Trails &amp; Routes →</a>
      </div>
    </div>
  </section>
  <?php endif; ?>

  <section class="home-why">
    <div class="home-why__inner">
      <h2 class="home-why__title">Why We Do It</h2>
      <div class="home-why__content">
        <?php if ( $why_we_do_it ) : ?>
          <?php echo wp_kses_post( $why_we_do_it ); ?>
        <?php else : ?>
          <p>Add your text by creating an ACF field named <code>why_we_do_it</code> on the Home page.</p>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="home-about">
    <div class="home-about__inner">
      <h2 class="home-about__title">About Us</h2>
      <div class="home-about__grid">

        <div class="home-person-card">
          <?php if ( $carolyn_src ) : ?>
            <img class="home-person-card__photo" src="<?php echo esc_url( $carolyn_src ); ?>" alt="Carolyn Blessing">
          <?php endif; ?>
          <h3 class="home-person-card__name">Carolyn Blessing</h3>
          <?php if ( $carolyn_bio ) : ?>
            <p class="home-person-card__bio"><?php echo wp_kses_post( $carolyn_bio ); ?></p>
          <?php endif; ?>
        </div>

        <div class="home-person-card">
          <?php if ( $jeff_src ) : ?>
            <img class="home-person-card__photo" src="<?php echo esc_url( $jeff_src ); ?>" alt="Jeff Podmayer">
          <?php endif; ?>
          <h3 class="home-person-card__name">Jeff Podmayer</h3>
          <?php if ( $jeff_bio ) : ?>
            <p class="home-person-card__bio"><?php echo wp_kses_post( $jeff_bio ); ?></p>
          <?php endif; ?>
        </div>

      </div>
    </div>
  </section>

</div><!-- .home-page -->

<?php get_footer(); ?>
