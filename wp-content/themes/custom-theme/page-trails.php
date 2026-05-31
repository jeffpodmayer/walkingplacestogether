<?php
/**
 * Template for the Trails & Routes page (ID 4380).
 *
 * @package Custom_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

$paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;

$trails_query = new WP_Query( [
  'post_type'      => 'trail',
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
    <h1 class="archive-hero__title">Trails &amp; Routes</h1>
  </div>

  <div class="archive-content">
    <div class="archive-inner">

      <?php if ( $trails_query->have_posts() ) : ?>
        <div class="archive-grid">
          <?php while ( $trails_query->have_posts() ) : $trails_query->the_post(); ?>
            <?php
            $post_id  = get_the_ID();
            $thumb    = '';
            if ( function_exists( 'get_field' ) ) {
              $thumb = custom_theme_get_image_url( get_field( 'hero_image', $post_id ), 'large' );
            }
            if ( ! $thumb ) {
              $thumb = get_the_post_thumbnail_url( $post_id, 'large' );
            }
            $distance   = function_exists( 'get_field' ) ? (float) get_field( 'distance', $post_id ) : 0;
            $days       = function_exists( 'get_field' ) ? (float) get_field( 'trip_duration_days', $post_id ) : 0;
            $region     = function_exists( 'get_field' ) ? get_field( 'region', $post_id ) : '';
            $trail_date = function_exists( 'get_field' ) ? custom_theme_format_trail_date_range( get_field( 'start_date', $post_id ), get_field( 'end_date', $post_id ) ) : '';
            if ( is_array( $region ) ) {
              $parts = [];
              foreach ( $region as $item ) {
                if ( is_object( $item ) && isset( $item->name ) ) $parts[] = $item->name;
                elseif ( is_object( $item ) && isset( $item->post_title ) ) $parts[] = $item->post_title;
                elseif ( is_scalar( $item ) ) $parts[] = $item;
              }
              $region = implode( ', ', $parts );
            }
            ?>
            <a class="archive-card" href="<?php the_permalink(); ?>">
              <div class="archive-card__image">
                <?php if ( $thumb ) : ?>
                  <img src="<?php echo esc_url( $thumb ); ?>" alt="<?php the_title_attribute(); ?>">
                <?php else : ?>
                  <div class="archive-card__image-placeholder"></div>
                <?php endif; ?>
              </div>
              <div class="archive-card__body">
                <?php if ( $region ) : ?>
                  <span class="archive-card__eyebrow"><?php echo esc_html( $region ); ?></span>
                <?php endif; ?>
                <h2 class="archive-card__title"><?php the_title(); ?></h2>
                <?php if ( $trail_date ) : ?>
                  <span class="archive-card__date"><?php echo esc_html( $trail_date ); ?></span>
                <?php endif; ?>
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
            'total'   => $trails_query->max_num_pages,
            'current' => $paged,
            'prev_text' => '← Previous',
            'next_text' => 'Next →',
          ] );
          ?>
        </div>

      <?php else : ?>
        <p class="archive-empty">No trails found.</p>
      <?php endif; ?>

      <?php wp_reset_postdata(); ?>
    </div>
  </div>

</div><!-- .archive-page -->

<?php get_footer(); ?>
