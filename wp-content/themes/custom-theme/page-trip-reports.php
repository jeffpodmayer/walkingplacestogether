<?php
/**
 * Template for the Trip Reports page (ID 15).
 *
 * @package Custom_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$reports_query = new WP_Query( [
  'post_type'      => 'post',
  'posts_per_page' => 12,
  'post_status'    => 'publish',
  'paged'          => $paged,
  'orderby'        => 'date',
  'order'          => 'DESC',
] );

$hero_url = get_the_post_thumbnail_url( get_the_ID(), 'full' );

get_header();
?>

<div id="primary" class="archive-page">

  <div class="archive-hero"<?php if ( $hero_url ) : ?> style="background-image:url('<?php echo esc_url( $hero_url ); ?>')"<?php endif; ?>>
    <div class="archive-hero__overlay"></div>
    <h1 class="archive-hero__title">Trip Reports</h1>
  </div>

  <div class="archive-content">
    <div class="archive-inner">

      <?php if ( $reports_query->have_posts() ) : ?>
        <div class="archive-grid">
          <?php while ( $reports_query->have_posts() ) : $reports_query->the_post(); ?>
            <?php
            $post_id    = get_the_ID();
            $thumb      = get_the_post_thumbnail_url( $post_id, 'large' );
            $hero       = function_exists( 'get_field' ) ? get_field( 'hero_image', $post_id ) : null;
            $img_url    = $thumb ?: ( is_array( $hero ) ? ( $hero['url'] ?? '' ) : (string) $hero );
            $distance   = function_exists( 'get_field' ) ? (float) get_field( 'distance', $post_id ) : 0;
            $days       = function_exists( 'get_field' ) ? (float) get_field( 'trip_duration_days', $post_id ) : 0;
            $start_date = function_exists( 'get_field' ) ? get_field( 'start_date', $post_id ) : '';
            ?>
            <a class="archive-card" href="<?php the_permalink(); ?>">
              <div class="archive-card__image">
                <?php if ( $img_url ) : ?>
                  <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php the_title_attribute(); ?>">
                <?php else : ?>
                  <div class="archive-card__image-placeholder"></div>
                <?php endif; ?>
              </div>
              <div class="archive-card__body">
                <?php if ( $start_date ) : ?>
                  <span class="archive-card__eyebrow"><?php echo esc_html( date( 'F Y', strtotime( $start_date ) ) ); ?></span>
                <?php endif; ?>
                <h2 class="archive-card__title"><?php the_title(); ?></h2>
                <div class="archive-card__meta">
                  <?php if ( $distance ) : ?>
                    <span><?php echo esc_html( number_format( $distance, 1 ) ); ?> mi</span>
                  <?php endif; ?>
                  <?php if ( $days ) : ?>
                    <span><?php echo esc_html( (int) $days ); ?> days</span>
                  <?php endif; ?>
                </div>
              </div>
            </a>
          <?php endwhile; ?>
        </div>

        <div class="archive-pagination">
          <?php
          echo paginate_links( [
            'total'   => $reports_query->max_num_pages,
            'current' => $paged,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
          ] );
          ?>
        </div>

      <?php else : ?>
        <p class="archive-empty">No trip reports found.</p>
      <?php endif; ?>

      <?php wp_reset_postdata(); ?>
    </div>
  </div>

</div><!-- .archive-page -->

<?php get_footer(); ?>
