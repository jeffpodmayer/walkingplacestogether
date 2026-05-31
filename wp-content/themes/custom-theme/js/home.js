( function () {
  function animateCount( el, target, duration ) {
    var start     = null;
    var startVal  = 0;

    function step( timestamp ) {
      if ( ! start ) start = timestamp;
      var progress = Math.min( ( timestamp - start ) / duration, 1 );
      var eased    = 1 - Math.pow( 1 - progress, 3 );
      var current  = Math.round( startVal + eased * ( target - startVal ) );
      el.textContent = current.toLocaleString();
      if ( progress < 1 ) {
        requestAnimationFrame( step );
      } else {
        el.textContent = target.toLocaleString();
      }
    }

    requestAnimationFrame( step );
  }

  document.addEventListener( 'DOMContentLoaded', function () {
    var statEls = document.querySelectorAll( '.home-stat-value' );
    if ( ! statEls.length ) return;

    statEls.forEach( function ( el ) {
      var raw = el.textContent.replace( /,/g, '' );
      el.dataset.target = parseInt( raw, 10 ) || 0;
      el.textContent    = '0';
    } );

    var observer = new IntersectionObserver(
      function ( entries, obs ) {
        entries.forEach( function ( entry ) {
          if ( entry.isIntersecting ) {
            var el     = entry.target;
            var target = parseInt( el.dataset.target, 10 );
            animateCount( el, target, 1800 );
            obs.unobserve( el );
          }
        } );
      },
      { threshold: 0.3 }
    );

    statEls.forEach( function ( el ) {
      observer.observe( el );
    } );
  } );
} )();
