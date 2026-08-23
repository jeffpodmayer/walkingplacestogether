/**
 * Adds an Expand/Collapse button next to each "Day N" tab in the post editor.
 * Days start collapsed so short trips don't require scrolling past empty days.
 * Display-only: hidden fields still submit their values on save.
 */
(function () {
  'use strict';

  var DAY_LABEL = /^day\s*\d+$/i;
  // Fields from Gear Notes onward are never part of a day section.
  var SECTION_END = /^(gear_notes|gear)$/i;

  // Collapse by clipping to zero height rather than display:none: TinyMCE
  // editors keep valid dimensions (no re-init glitches), and the page height
  // changes are less likely to trigger scroll-anchoring jumps.
  function injectStyles() {
    if (document.getElementById('wpt-day-collapse-css')) {
      return;
    }
    var style = document.createElement('style');
    style.id = 'wpt-day-collapse-css';
    style.textContent =
      '.wpt-day-hidden{' +
      'height:0 !important;min-height:0 !important;' +
      'margin:0 !important;padding:0 !important;' +
      'border:none !important;overflow:hidden !important;' +
      'visibility:hidden;' +
      '}' +
      '#poststuff,.acf-postbox{overflow-anchor:none;}';
    document.head.appendChild(style);
  }

  function isSectionEnd(el) {
    var name = el.getAttribute && el.getAttribute('data-name');
    return !!(name && SECTION_END.test(name));
  }

  function sectionFields(tabWrap) {
    var fields = [];
    var el = tabWrap.nextElementSibling;
    while (el && !el.classList.contains('acf-tab-wrap') && !isSectionEnd(el)) {
      fields.push(el);
      el = el.nextElementSibling;
    }
    return fields;
  }

  function setCollapsed(tabWrap, collapsed) {
    tabWrap.dataset.wptCollapsed = collapsed ? '1' : '';
    sectionFields(tabWrap).forEach(function (field) {
      field.classList.toggle('wpt-day-hidden', collapsed);
    });
    var btn = tabWrap.querySelector('.wpt-day-toggle');
    if (btn) {
      btn.textContent = collapsed ? 'Expand' : 'Collapse';
      btn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
    }
  }

  function initWrap(tabWrap) {
    if (tabWrap.dataset.wptDayToggle) {
      return;
    }
    var links = tabWrap.querySelectorAll('a.acf-tab-button');
    // Only single-tab "Day N" headers; real multi-tab bars already switch views.
    if (links.length !== 1 || !DAY_LABEL.test(links[0].textContent.trim())) {
      return;
    }
    tabWrap.dataset.wptDayToggle = '1';

    var btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'button button-small wpt-day-toggle';
    btn.style.cssText = 'margin:6px 0 6px 12px;vertical-align:middle;';
    btn.addEventListener('click', function () {
      setCollapsed(tabWrap, !tabWrap.dataset.wptCollapsed);
    });

    var group = tabWrap.querySelector('ul');
    if (group) {
      var li = document.createElement('li');
      li.style.cssText = 'float:left;list-style:none;';
      li.appendChild(btn);
      group.appendChild(li);
    } else {
      tabWrap.appendChild(btn);
    }

    setCollapsed(tabWrap, true);
  }

  function initAll() {
    injectStyles();
    document.querySelectorAll('.acf-tab-wrap').forEach(initWrap);
  }

  if (window.acf && window.acf.addAction) {
    // ACF builds tab bars with JS, so hook its lifecycle events.
    window.acf.addAction('ready', initAll);
    window.acf.addAction('append', initAll);
  } else {
    document.addEventListener('DOMContentLoaded', function () {
      setTimeout(initAll, 500);
    });
  }
})();
