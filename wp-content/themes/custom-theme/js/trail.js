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
  const galleryLimit = () =>
    window.matchMedia("(max-width: 900px)").matches ? 5 : 9;

  const syncGalleryToggle = (btn) => {
    const grid = btn.previousElementSibling;
    if (!grid || !grid.classList.contains("trail-gallery-grid")) return;

    const total = grid.querySelectorAll(".trail-gallery-grid-item").length;
    const limit = galleryLimit();
    btn.hidden = total <= limit;
  };

  document.querySelectorAll(".trail-gallery-toggle").forEach((btn) => {
    const grid = btn.previousElementSibling;
    if (!grid || !grid.classList.contains("trail-gallery-grid")) return;

    const moreLabel = btn.dataset.more || "Show all photos";
    const fewerLabel = btn.dataset.fewer || "Show fewer photos";

    syncGalleryToggle(btn);

    btn.addEventListener("click", () => {
      const collapsed = grid.getAttribute("data-collapsed") === "true";
      grid.setAttribute("data-collapsed", collapsed ? "false" : "true");
      btn.textContent = collapsed ? fewerLabel : moreLabel;
    });
  });

  window.addEventListener("resize", () => {
    document
      .querySelectorAll(".trail-gallery-toggle")
      .forEach(syncGalleryToggle);
  });
});

// Trip nav: multi-day reports use tabs; day-trip reports are one continuous scroll.
// Mobile uses a bottom dock (+ Days/More sheets) wired through the same anchors.
document.addEventListener("DOMContentLoaded", () => {
  const main = document.querySelector(".trip-report-main");
  const sidebarLinks = Array.from(
    document.querySelectorAll(".trip-nav-item a.js-trip-nav-link")
  );
  const allNavLinks = Array.from(document.querySelectorAll("a.js-trip-nav-link"));
  if (!main || !sidebarLinks.length) return;

  const isDayTrip = Boolean(document.querySelector(".single-day-trip-report"));

  // Keep fixed dock/sheets out of the sticky sidebar stacking context.
  const dockEl = document.querySelector("[data-trip-dock]");
  const backdropEl = document.querySelector("[data-trip-sheet-backdrop]");
  document.querySelectorAll("[data-trip-sheet-panel]").forEach((panel) => {
    document.body.appendChild(panel);
  });
  if (backdropEl) document.body.appendChild(backdropEl);
  if (dockEl) document.body.appendChild(dockEl);

  const targets = sidebarLinks
    .map((link) => {
      const id = link.getAttribute("href").slice(1);
      const el = document.getElementById(id);
      return el ? { link, el } : null;
    })
    .filter(Boolean);
  if (!targets.length) return;

  const targetByHref = new Map(
    targets.map((t) => ["#" + t.el.id, t])
  );

  // Clear sticky sidebar + WP admin bar so section titles aren't clipped.
  const stickyOffset = () => {
    const sidebar = document.querySelector(".trip-report-sidebar");
    const isMobileNav =
      window.matchMedia("(max-width: 900px)").matches &&
      Boolean(document.querySelector("[data-trip-dock]"));
    const stickyTop =
      !isMobileNav && sidebar
        ? parseFloat(getComputedStyle(sidebar).top) || 0
        : 0;
    const adminBar = document.getElementById("wpadminbar");
    const adminBottom =
      adminBar && getComputedStyle(adminBar).position === "fixed"
        ? adminBar.getBoundingClientRect().bottom
        : 0;
    return Math.max(stickyTop, adminBottom) + 8;
  };

  const syncDock = (href) => {
    const dock = document.querySelector("[data-trip-dock]");
    if (!dock) return;

    let group = null;
    const direct = dock.querySelector(
      `.trip-dock-bar a.trip-dock-item[href="${href}"]`
    );
    if (/^#trip-day-/.test(href)) {
      group = "days";
    } else if (direct) {
      group = direct.dataset.tripDockGroup;
    } else if (
      document.querySelector(
        `#trip-dock-sheet-more a.js-trip-nav-link[href="${href}"]`
      )
    ) {
      group = "more";
    }

    dock.querySelectorAll("[data-trip-dock-group]").forEach((el) => {
      // Map open state is managed separately (is-map-open).
      if (el.dataset.tripDockGroup === "map") return;
      el.classList.toggle("is-active", el.dataset.tripDockGroup === group);
    });

    document
      .querySelectorAll(".trip-dock-sheet-link.js-trip-nav-link")
      .forEach((el) => {
        el.classList.toggle("is-active", el.getAttribute("href") === href);
      });
  };

  const setActiveLink = (link) => {
    const href = link.getAttribute("href");
    allNavLinks.forEach((l) =>
      l.classList.toggle("is-active", l.getAttribute("href") === href)
    );
    syncDock(href);
  };

  const scrollToEl = (el) => {
    const y =
      el.getBoundingClientRect().top + window.scrollY - stickyOffset();
    window.scrollTo({ top: Math.max(0, y) });
  };

  // ── Mobile sheets (Days / More) ───────────────────────────────
  const backdrop = document.querySelector("[data-trip-sheet-backdrop]");
  const sheetPanels = Array.from(
    document.querySelectorAll("[data-trip-sheet-panel]")
  );
  const sheetButtons = Array.from(document.querySelectorAll("[data-trip-sheet]"));

  const closeSheets = () => {
    sheetPanels.forEach((panel) => {
      panel.hidden = true;
      panel.classList.remove("is-dragging");
      panel.style.transform = "";
      panel.style.transition = "";
    });
    if (backdrop) {
      backdrop.hidden = true;
      backdrop.style.opacity = "";
    }
    sheetButtons.forEach((btn) => {
      btn.setAttribute("aria-expanded", "false");
    });
    document.body.classList.remove("trip-dock-sheet-open");
  };

  const openSheet = (name) => {
    const panel = document.querySelector(`[data-trip-sheet-panel="${name}"]`);
    if (!panel) return;
    sheetPanels.forEach((p) => {
      p.hidden = p !== panel;
      p.classList.remove("is-dragging");
      p.style.transform = "";
      p.style.transition = "";
    });
    if (backdrop) {
      backdrop.hidden = false;
      backdrop.style.opacity = "";
    }
    sheetButtons.forEach((btn) => {
      const open = btn.dataset.tripSheet === name;
      btn.setAttribute("aria-expanded", open ? "true" : "false");
    });
    document.body.classList.add("trip-dock-sheet-open");
  };

  sheetButtons.forEach((btn) => {
    btn.addEventListener("click", () => {
      const name = btn.dataset.tripSheet;
      const isOpen =
        btn.getAttribute("aria-expanded") === "true" &&
        document.querySelector(`[data-trip-sheet-panel="${name}"]:not([hidden])`);
      if (isOpen) {
        closeSheets();
      } else {
        openSheet(name);
      }
      btn.blur();
    });
  });

  if (backdrop) {
    backdrop.addEventListener("click", closeSheets);
  }
  document.querySelectorAll("[data-trip-sheet-close]").forEach((btn) => {
    btn.addEventListener("click", closeSheets);
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") closeSheets();
  });

  // Swipe sheet down to dismiss from the handle / header.
  sheetPanels.forEach((panel) => {
    let drag = null;

    const onPointerDown = (e) => {
      if (panel.hidden || e.button) return;
      if (e.target.closest(".trip-dock-sheet-close")) return;
      if (!e.target.closest(".trip-dock-sheet-handle, .trip-dock-sheet-header")) {
        return;
      }

      drag = {
        pointerId: e.pointerId,
        startY: e.clientY,
        lastY: e.clientY,
        lastT: performance.now(),
        dy: 0,
        active: false,
      };
      panel.setPointerCapture(e.pointerId);
    };

    const onPointerMove = (e) => {
      if (!drag || e.pointerId !== drag.pointerId) return;
      const dy = e.clientY - drag.startY;
      const now = performance.now();

      if (!drag.active) {
        if (dy < 6) return;
        drag.active = true;
        panel.classList.add("is-dragging");
        panel.style.transition = "none";
      }

      drag.dy = Math.max(0, dy);
      drag.lastY = e.clientY;
      drag.lastT = now;
      panel.style.transform = `translateY(${drag.dy}px)`;
      if (backdrop) {
        const fade = Math.max(0.25, 1 - drag.dy / 280);
        backdrop.style.opacity = String(fade);
      }
    };

    const finishDrag = (e) => {
      if (!drag || e.pointerId !== drag.pointerId) return;
      const { dy, active, lastY, lastT } = drag;
      const elapsed = Math.max(1, performance.now() - lastT);
      const velocity = active ? (e.clientY - lastY) / elapsed : 0;
      drag = null;

      panel.classList.remove("is-dragging");

      if (active && (dy > 90 || velocity > 0.45)) {
        panel.style.transition = "transform 0.2s ease-out";
        panel.style.transform = `translateY(110%)`;
        if (backdrop) {
          backdrop.style.transition = "opacity 0.2s ease-out";
          backdrop.style.opacity = "0";
        }
        window.setTimeout(() => {
          if (backdrop) backdrop.style.transition = "";
          closeSheets();
        }, 200);
        return;
      }

      panel.style.transition = "transform 0.22s ease-out";
      panel.style.transform = "";
      if (backdrop) {
        backdrop.style.transition = "opacity 0.22s ease-out";
        backdrop.style.opacity = "";
        window.setTimeout(() => {
          backdrop.style.transition = "";
        }, 220);
      }
      window.setTimeout(() => {
        panel.style.transition = "";
      }, 220);
    };

    panel.addEventListener("pointerdown", onPointerDown);
    panel.addEventListener("pointermove", onPointerMove);
    panel.addEventListener("pointerup", finishDrag);
    panel.addEventListener("pointercancel", finishDrag);
  });

  // ── Day trip: everything visible, sidebar jumps + scroll-spy ──
  if (isDayTrip) {
    const scrollToTarget = (target) => {
      scrollToEl(target.el);
      setActiveLink(target.link);
      history.replaceState(null, "", "#" + target.el.id);
      closeSheets();
      target.link.blur();
    };

    allNavLinks.forEach((link) => {
      link.addEventListener("click", (e) => {
        const href = link.getAttribute("href");
        const target = targetByHref.get(href);
        if (!target) return;
        e.preventDefault();
        scrollToTarget(target);
      });
    });

    const updateSpy = () => {
      const line = stickyOffset() + 60;
      let current = targets[0];
      targets.forEach((t) => {
        if (t.el.getBoundingClientRect().top <= line) current = t;
      });
      if (!current.link.classList.contains("is-active")) {
        setActiveLink(current.link);
        history.replaceState(null, "", "#" + current.el.id);
      }
    };
    window.addEventListener("scroll", updateSpy, { passive: true });

    const initial =
      (location.hash &&
        targets.find((t) => "#" + t.el.id === location.hash)) ||
      targets[0];
    setActiveLink(initial.link);
    if (location.hash) {
      requestAnimationFrame(() => scrollToEl(initial.el));
    } else {
      updateSpy();
    }
    return;
  }

  // ── Multi-day: reference sections are tabs; days scroll together ──
  const panelFor = (t) =>
    t.el.classList.contains("trip-day") ? t.el.closest(".trail-section") : t.el;

  const dayTargets = targets.filter((t) => t.el.classList.contains("trip-day"));
  const daysPanel = dayTargets.length ? panelFor(dayTargets[0]) : null;

  const trailingSiblings = (el) => {
    const extras = [];
    let next = el.nextElementSibling;
    while (next && !next.classList.contains("trail-section")) {
      extras.push(next);
      next = next.nextElementSibling;
    }
    return extras;
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
    scrollToEl(target.el);
  };

  let activePanel = null;

  const showPanel = (panel) => {
    activePanel = panel;
    Array.from(main.children).forEach((child) => {
      child.hidden = true;
    });
    panel.hidden = false;
    trailingSiblings(panel).forEach((el) => (el.hidden = false));
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
    closeSheets();
  };

  allNavLinks.forEach((link) => {
    link.addEventListener("click", (e) => {
      const href = link.getAttribute("href");
      const target = targetByHref.get(href);
      if (!target) return;
      e.preventDefault();
      openTarget(target);
      link.blur();
    });
  });

  main.addEventListener("click", (e) => {
    const link = e.target.closest("a.js-trip-goto");
    if (!link) return;
    const href = link.getAttribute("href");
    const target = targetByHref.get(href);
    if (!target) return;
    e.preventDefault();
    openTarget(target);
    link.blur();
  });

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

// Floating route map: FAB on desktop; mobile dock Map item on smaller screens
document.addEventListener("DOMContentLoaded", () => {
  const embed = document.querySelector(".trail-map-embed");
  const home = document.querySelector(".trail-map-home");
  if (!embed || !home) return;

  const mapIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M14.106 5.553a2 2 0 0 0 1.788 0l3.659-1.83A1 1 0 0 1 21 4.619v12.764a1 1 0 0 1-.553.894l-4.553 2.277a2 2 0 0 1-1.788 0l-4.212-2.106a2 2 0 0 0-1.788 0l-3.659 1.83A1 1 0 0 1 3 19.381V6.618a1 1 0 0 1 .553-.894l4.553-2.277a2 2 0 0 1 1.788 0z"/><path d="M15 5.764v15"/><path d="M9 3.236v15"/></svg>';

  const fab = document.createElement("button");
  fab.type = "button";
  fab.className = "trip-map-fab";
  fab.setAttribute("aria-pressed", "false");
  fab.innerHTML = mapIcon + '<span class="trip-map-fab-label">Map</span>';
  document.body.appendChild(fab);

  const dockMapBtns = () =>
    Array.from(document.querySelectorAll("[data-trip-map-toggle]"));

  let panel = null;

  const setMapOpen = (open) => {
    fab.classList.toggle("is-open", open);
    fab.hidden = open;
    fab.setAttribute("aria-pressed", open ? "true" : "false");
    fab.setAttribute("aria-hidden", open ? "true" : "false");
    fab.title = open ? "Close map" : "Open map while you scroll";
    fab.setAttribute(
      "aria-label",
      open ? "Close map" : "Open map while you scroll"
    );

    dockMapBtns().forEach((btn) => {
      btn.classList.toggle("is-map-open", open);
      btn.classList.remove("is-active");
      btn.setAttribute("aria-pressed", open ? "true" : "false");
      btn.setAttribute(
        "aria-label",
        open ? "Close map" : "Open map while you scroll"
      );
    });
  };

  setMapOpen(false);

  const dockMap = () => {
    if (!panel) return;
    home.appendChild(embed);
    panel.remove();
    panel = null;
    setMapOpen(false);
  };

  const enlargeIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6"/><path d="M9 21H3v-6"/><path d="M21 3l-7 7"/><path d="M3 21l7-7"/></svg>';
  const shrinkIcon =
    '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.25" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 14h6v6"/><path d="M20 10h-6V4"/><path d="M14 10l7-7"/><path d="M3 21l7-7"/></svg>';

  const popOut = () => {
    if (panel) return;
    panel = document.createElement("div");
    panel.className = "trip-map-float";
    panel.innerHTML =
      '<div class="trip-map-float-bar">' +
      '<span class="trip-map-float-title">' +
      mapIcon +
      "<span>Map</span>" +
      "</span>" +
      '<span class="trip-map-float-actions">' +
      '<button type="button" class="trip-map-float-enlarge" aria-label="Enlarge map" title="Enlarge map">' +
      enlargeIcon +
      "</button>" +
      '<button type="button" class="trip-map-float-close" aria-label="Close map">&times;</button>' +
      "</span>" +
      "</div>";
    document.body.appendChild(panel);
    panel.appendChild(embed);
    setMapOpen(true);

    const enlargeBtn = panel.querySelector(".trip-map-float-enlarge");
    const setEnlarged = (large) => {
      panel.classList.toggle("is-large", large);
      enlargeBtn.innerHTML = large ? shrinkIcon : enlargeIcon;
      enlargeBtn.setAttribute(
        "aria-label",
        large ? "Shrink map" : "Enlarge map"
      );
      enlargeBtn.title = large ? "Shrink map" : "Enlarge map";
    };

    enlargeBtn.addEventListener("click", () => {
      setEnlarged(!panel.classList.contains("is-large"));
      enlargeBtn.blur();
    });

    panel
      .querySelector(".trip-map-float-close")
      .addEventListener("click", dockMap);

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

  const toggleMap = () => {
    if (panel) dockMap();
    else popOut();
  };

  fab.addEventListener("click", () => {
    if (!panel) popOut();
  });

  document.addEventListener("click", (e) => {
    const btn = e.target.closest("[data-trip-map-toggle]");
    if (!btn) return;
    e.preventDefault();
    toggleMap();
    btn.blur();
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
