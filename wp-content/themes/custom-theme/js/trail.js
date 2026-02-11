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
  document.querySelectorAll(".trail-gallery-toggle").forEach((btn) => {
    const grid = btn.previousElementSibling;
    if (!grid || !grid.classList.contains("trail-gallery-grid")) return;

    btn.addEventListener("click", () => {
      const collapsed = grid.getAttribute("data-collapsed") === "true";
      grid.setAttribute("data-collapsed", collapsed ? "false" : "true");
      btn.textContent = collapsed ? "Show fewer photos" : "Show all photos";
    });
  });
});

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".js-count").forEach((el) => {
    const target = parseInt(el.dataset.count, 10);
    if (Number.isNaN(target)) return;

    let current = 0;
    const duration = 2500;
    const step = Math.max(1, Math.floor(target / (duration / 16)));

    const tick = () => {
      current += step;
      if (current >= target) {
        el.textContent = target;
        return;
      }
      el.textContent = current;
      requestAnimationFrame(tick);
    };

    tick();
  });
});
