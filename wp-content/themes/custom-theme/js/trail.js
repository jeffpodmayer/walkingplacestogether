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

// Trip nav: reference sections (overview, map, gear, ...) act as tabs, while
// the day-by-day story is one continuous scroll inside its own tab. Day links
// jump within the story and are scroll-spied.
document.addEventListener("DOMContentLoaded", () => {
  const main = document.querySelector(".trip-report-main");
  const links = Array.from(document.querySelectorAll(".trip-nav-item a"));
  if (!main || !links.length) return;

  const targets = links
    .map((link) => {
      const id = link.getAttribute("href").slice(1);
      const el = document.getElementById(id);
      return el ? { link, el } : null;
    })
    .filter(Boolean);
  if (!targets.length) return;

  // The tab panel for a day link is the whole day-by-day wrapper.
  const panelFor = (t) =>
    t.el.classList.contains("trip-day") ? t.el.closest(".trail-section") : t.el;

  const dayTargets = targets.filter((t) => t.el.classList.contains("trip-day"));
  const daysPanel = dayTargets.length ? panelFor(dayTargets[0]) : null;

  // A panel includes any following siblings that belong to it
  // (e.g. the gear gallery rendered after the gear section).
  const trailingSiblings = (el) => {
    const extras = [];
    let next = el.nextElementSibling;
    while (next && !next.classList.contains("trail-section")) {
      extras.push(next);
      next = next.nextElementSibling;
    }
    return extras;
  };

  const stickyOffset = () => {
    const nav = document.querySelector(".trip-nav");
    return nav ? parseFloat(getComputedStyle(nav).top) || 0 : 0;
  };

  const scrollToLayoutTop = () => {
    const layout = document.querySelector(".trip-report-layout");
    if (!layout) return;
    const top = layout.getBoundingClientRect().top;
    const offset = stickyOffset();
    if (top < offset) {
      window.scrollTo({ top: window.scrollY + top - offset });
    }
  };

  const scrollToDay = (target) => {
    if (target === dayTargets[0]) {
      scrollToLayoutTop();
      return;
    }
    const y =
      target.el.getBoundingClientRect().top +
      window.scrollY -
      stickyOffset() -
      10;
    window.scrollTo({ top: y });
  };

  const setActiveLink = (link) => {
    links.forEach((l) => l.classList.toggle("is-active", l === link));
  };

  let activePanel = null;

  const showPanel = (panel) => {
    activePanel = panel;
    Array.from(main.children).forEach((child) => {
      child.hidden = true;
    });
    panel.hidden = false;
    trailingSiblings(panel).forEach((el) => (el.hidden = false));
    // Re-check caption overflow for the newly visible galleries
    window.dispatchEvent(new Event("resize"));
  };

  const openTarget = (target) => {
    showPanel(panelFor(target));
    if (target.el.classList.contains("trip-day")) {
      scrollToDay(target);
    } else {
      scrollToLayoutTop();
    }
    setActiveLink(target.link);
    history.replaceState(null, "", "#" + target.el.id);
  };

  targets.forEach((target) => {
    target.link.addEventListener("click", (e) => {
      e.preventDefault();
      openTarget(target);
      // Navigation is prevented, so drop focus to avoid a lingering ring
      target.link.blur();
    });
  });

  // Scroll-spy across days while the story tab is open
  const updateSpy = () => {
    if (!daysPanel || activePanel !== daysPanel) return;
    const line = stickyOffset() + 60;
    let current = dayTargets[0];
    dayTargets.forEach((t) => {
      if (t.el.getBoundingClientRect().top <= line) current = t;
    });
    if (!current.link.classList.contains("is-active")) {
      setActiveLink(current.link);
      history.replaceState(null, "", "#" + current.el.id);
    }
  };
  window.addEventListener("scroll", updateSpy, { passive: true });

  const initial =
    (location.hash && targets.find((t) => "#" + t.el.id === location.hash)) ||
    targets[0];
  showPanel(panelFor(initial));
  setActiveLink(initial.link);

  // The browser's native anchor jump ignores the sidebar's sticky offset;
  // realign after deep-linking (and land on the right day within the story).
  if (location.hash) {
    requestAnimationFrame(() => {
      if (initial.el.classList.contains("trip-day")) {
        scrollToDay(initial);
      } else {
        scrollToLayoutTop();
      }
    });
  }
});

// Floating route map: pop the map into a fixed panel that persists across tabs
document.addEventListener("DOMContentLoaded", () => {
  const embed = document.querySelector(".trail-map-embed");
  if (!embed) return;
  const section = embed.closest(".trail-section");
  const title = section ? section.querySelector(".trail-section-title") : null;
  if (!section || !title) return;

  const pipIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 9V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v10c0 1.1.9 2 2 2h4"/><rect width="10" height="7" x="12" y="13" rx="2"/></svg>';

  const popoutBtn = document.createElement("button");
  popoutBtn.type = "button";
  popoutBtn.className = "trip-map-popout";
  popoutBtn.title = "Float map on screen";
  popoutBtn.setAttribute("aria-label", "Float map on screen");
  popoutBtn.innerHTML = pipIcon;
  title.appendChild(popoutBtn);

  let panel = null;

  const dock = () => {
    if (!panel) return;
    section.appendChild(embed);
    panel.remove();
    panel = null;
    popoutBtn.hidden = false;
  };

  const popOut = () => {
    panel = document.createElement("div");
    panel.className = "trip-map-float";
    panel.innerHTML =
      '<div class="trip-map-float-bar">' +
      "<span>Route Map</span>" +
      '<button type="button" class="trip-map-float-close" aria-label="Close floating map">&times;</button>' +
      "</div>";
    document.body.appendChild(panel);
    panel.appendChild(embed);
    popoutBtn.hidden = true;

    panel
      .querySelector(".trip-map-float-close")
      .addEventListener("click", dock);

    // Drag by the title bar
    const bar = panel.querySelector(".trip-map-float-bar");
    let drag = null;
    bar.addEventListener("pointerdown", (e) => {
      if (e.target.closest("button")) return;
      const rect = panel.getBoundingClientRect();
      drag = { dx: e.clientX - rect.left, dy: e.clientY - rect.top };
      bar.setPointerCapture(e.pointerId);
    });
    bar.addEventListener("pointermove", (e) => {
      if (!drag) return;
      panel.style.left = Math.max(0, e.clientX - drag.dx) + "px";
      panel.style.top = Math.max(0, e.clientY - drag.dy) + "px";
      panel.style.right = "auto";
      panel.style.bottom = "auto";
    });
    bar.addEventListener("pointerup", () => {
      drag = null;
    });
  };

  popoutBtn.addEventListener("click", popOut);

  // Float/dock toggle on the Route Map row in the sidebar nav
  const navLink = document.querySelector(
    '.trip-nav-item a[href="#' + section.id + '"]',
  );
  if (navLink) {
    const navBtn = document.createElement("button");
    navBtn.type = "button";
    navBtn.className = "trip-nav-map-float";
    navBtn.title = "Float map on screen";
    navBtn.setAttribute("aria-label", "Float map on screen");
    navBtn.innerHTML = pipIcon;
    navLink.parentElement.appendChild(navBtn);
    navBtn.addEventListener("click", (e) => {
      e.preventDefault();
      if (panel) {
        dock();
      } else {
        popOut();
      }
    });
  }
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
