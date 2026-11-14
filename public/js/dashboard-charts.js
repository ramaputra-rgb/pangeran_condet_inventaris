/* ==========================================================================
   DASHBOARD CHART TOOLTIPS & INTERACTIONS — PANGERAN CONDET ERP
   ========================================================================== */
(function () {
  'use strict';

  function initDashboardCharts() {
    var tooltip = document.getElementById('dash-chart-tooltip');
    var targets = document.querySelectorAll('[data-tip-title]');
    if (!tooltip || !targets.length) return;

    function renderTooltip(el, clientX, clientY) {
      var title = el.getAttribute('data-tip-title') || '';
      var linesRaw = el.getAttribute('data-tip-lines') || '';
      var lines = linesRaw ? linesRaw.split('|') : [];

      tooltip.innerHTML = '';

      var heading = document.createElement('strong');
      heading.textContent = title;
      tooltip.appendChild(heading);

      lines.forEach(function (line) {
        if (!line) return;
        var row = document.createElement('span');
        row.textContent = line;
        tooltip.appendChild(row);
      });

      tooltip.hidden = false;

      var rect = el.getBoundingClientRect();
      var tipW = tooltip.offsetWidth || 180;
      var tipH = tooltip.offsetHeight || 60;
      var pad = 12;

      var x = typeof clientX === 'number' && clientX > 0 ? clientX + 14 : rect.left + rect.width / 2 - tipW / 2;
      var y = typeof clientY === 'number' && clientY > 0 ? clientY - tipH - 12 : rect.top - tipH - 10;

      if (x + tipW + pad > window.innerWidth) {
        x = Math.max(pad, window.innerWidth - tipW - pad);
      }
      if (x < pad) x = pad;

      if (y < pad) {
        y = Math.min(window.innerHeight - tipH - pad, rect.bottom + 10);
      }

      tooltip.style.left = Math.round(x) + 'px';
      tooltip.style.top = Math.round(y) + 'px';
    }

    function hideTooltip() {
      tooltip.hidden = true;
    }

    targets.forEach(function (el) {
      el.addEventListener('mouseenter', function (e) {
        el.classList.add('is-hovered');
        renderTooltip(el, e.clientX, e.clientY);
      });

      el.addEventListener('mousemove', function (e) {
        renderTooltip(el, e.clientX, e.clientY);
      });

      el.addEventListener('mouseleave', function () {
        el.classList.remove('is-hovered');
        hideTooltip();
      });

      el.addEventListener('focus', function () {
        el.classList.add('is-hovered');
        renderTooltip(el);
      });

      el.addEventListener('blur', function () {
        el.classList.remove('is-hovered');
        hideTooltip();
      });
    });

    window.addEventListener('scroll', hideTooltip, { passive: true });
  }

  document.addEventListener('DOMContentLoaded', initDashboardCharts);
})();
