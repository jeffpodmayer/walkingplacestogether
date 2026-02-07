document.addEventListener("DOMContentLoaded", function () {
  function updateTrailCaptionOverflow() {
    document.querySelectorAll(".trail-gallery-caption").forEach((caption) => {
      caption.classList.remove("trail-caption--overflow");

      const isOverflowing =
        caption.scrollWidth > caption.clientWidth ||
        caption.scrollHeight > caption.clientHeight;

      if (isOverflowing) {
        caption.classList.add("trail-caption--overflow");
      }
    });
  }

  updateTrailCaptionOverflow();
  window.addEventListener("resize", updateTrailCaptionOverflow);
});

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".trail-gallery-strip").forEach((strip) => {
    const wrap = strip.closest(".trail-gallery-strip-wrap");
    const rightLabel = wrap?.querySelector(".trail-gallery-strip-label--right");
    const leftLabel = wrap?.querySelector(".trail-gallery-strip-label--left");
    if (!rightLabel || !leftLabel) return;

    let timeout;

    const updateLabels = () => {
      const maxScroll = strip.scrollWidth - strip.clientWidth;
      const atEnd = strip.scrollLeft >= maxScroll - 5;

      if (atEnd) {
        rightLabel.classList.add("is-hidden");
        leftLabel.classList.remove("is-hidden");
      } else {
        rightLabel.classList.remove("is-hidden");
        leftLabel.classList.add("is-hidden");
      }
    };

    const hideOnScroll = () => {
      rightLabel.classList.add("is-hidden");
      leftLabel.classList.add("is-hidden");
      clearTimeout(timeout);
      timeout = setTimeout(() => {
        updateLabels();
      }, 800);
    };

    updateLabels();
    strip.addEventListener("scroll", hideOnScroll);
    window.addEventListener("resize", updateLabels);
  });
});
