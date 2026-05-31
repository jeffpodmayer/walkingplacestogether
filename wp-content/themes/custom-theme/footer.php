<?php
/**
 * Footer template.
 *
 * @package Custom_Theme
 */
if ( ! defined( 'ABSPATH' ) ) {
  exit;
}

// ── Footer content from ACF options (optional) ─────────────────────────────
// To make these editable in WP admin, create an ACF Options Page and add:
//   footer_blurb     (Textarea)
//   footer_instagram (URL)
//   footer_strava    (URL)
//   footer_facebook  (URL)
$blurb     = function_exists( 'get_field' ) ? get_field( 'footer_blurb', 'option' ) : '';
$instagram = function_exists( 'get_field' ) ? get_field( 'footer_instagram', 'option' ) : '';
$strava    = function_exists( 'get_field' ) ? get_field( 'footer_strava', 'option' ) : '';
$facebook  = function_exists( 'get_field' ) ? get_field( 'footer_facebook', 'option' ) : '';
?>

<?php astra_content_bottom(); ?>
  </div><!-- ast-container -->
  </div><!-- #content -->
<?php astra_content_after(); astra_footer_before(); ?>

<footer id="site-footer" class="site-footer">
  <div class="site-footer__inner">

    <div class="site-footer__col site-footer__col--brand">
      <a class="site-footer__site-name" href="<?php echo esc_url( home_url( '/' ) ); ?>">
        <?php bloginfo( 'name' ); ?>
      </a>
    </div>

    <div class="site-footer__col site-footer__col--nav">
      <h4 class="site-footer__heading">Explore</h4>
      <nav>
        <ul class="site-footer__nav">
          <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
          <li><a href="<?php echo esc_url( home_url( '/trails/' ) ); ?>">Trails &amp; Routes</a></li>
          <li><a href="<?php echo esc_url( home_url( '/trip-reports/' ) ); ?>">Trip Reports</a></li>
        </ul>
      </nav>
    </div>

  </div>

  <div class="site-footer__bottom">
    <p>&copy; <?php echo date( 'Y' ); ?> <?php bloginfo( 'name' ); ?>. All rights reserved.</p>
  </div>
</footer>

<?php astra_footer_after(); ?>
</div><!-- #page -->
<?php astra_body_bottom(); wp_footer(); ?>
</body>
</html>
