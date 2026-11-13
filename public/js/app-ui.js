/* ==========================================================================
   SHARED UI INTERACTIONS — PANGERAN CONDET ERP
   Handles panel tabs, chip filters, live search/sort/expand, and <dialog> modals.
   ========================================================================== */
(function () {
  'use strict';

  function openDialog(id) {
    var modal = typeof id === 'string' ? document.getElementById(id) : id;
    if (!modal) return;
    if (typeof modal.showModal === 'function') {
      if (!modal.open) modal.showModal();
    } else {
      modal.setAttribute('open', '');
    }
  }

  function closeDialog(id) {
    var modal = typeof id === 'string' ? document.getElementById(id) : id;
    if (!modal) return;
    if (typeof modal.close === 'function') {
      if (modal.open) modal.close();
    } else {
      modal.removeAttribute('open');
    }
  }

  window.openDialog = openDialog;
  window.closeDialog = closeDialog;

  function initDialogs() {
    document.querySelectorAll('dialog.modal').forEach(function (modal) {
      modal.querySelectorAll('[data-close]').forEach(function (btn) {
        btn.addEventListener('click', function () {
          closeDialog(modal);
        });
      });

      modal.addEventListener('click', function (e) {
        if (e.target !== modal) return;
        var rect = modal.getBoundingClientRect();
        var outside =
          e.clientX < rect.left ||
          e.clientX > rect.right ||
          e.clientY < rect.top ||
          e.clientY > rect.bottom;
        if (outside) closeDialog(modal);
      });
    });

    document.querySelectorAll('[data-open-dialog]').forEach(function (trigger) {
      trigger.addEventListener('click', function () {
        openDialog(trigger.getAttribute('data-open-dialog'));
      });
    });
  }

  function activatePanelTab(panel, targetName) {
    var tabs = panel.querySelectorAll('.tabs button[data-t]');
    tabs.forEach(function (btn) {
      var active = btn.getAttribute('data-t') === targetName;
      btn.setAttribute('aria-selected', active ? 'true' : 'false');
      var pane = panel.querySelector('#t-' + btn.getAttribute('data-t'));
      if (pane) pane.hidden = !active;
    });
  }

  window.activatePanelTab = activatePanelTab;

  function initPanels() {
    document.querySelectorAll('.panel').forEach(function (panel) {
      var tabs = panel.querySelectorAll('.tabs button[data-t]');
      tabs.forEach(function (btn) {
        btn.addEventListener('click', function () {
          activatePanelTab(panel, btn.getAttribute('data-t'));
        });
      });
    });
  }

  function updateList(box) {
    var chip = box.querySelector('.chips button[aria-pressed="true"]');
    var f = chip ? chip.getAttribute('data-f') : 'semua';
    var searchEl = box.querySelector('[data-search]');
    var q = searchEl ? searchEl.value.trim().toLowerCase() : '';
    var kanalEl = box.querySelector('[data-kanal]');
    var kanal = kanalEl ? kanalEl.value : 'semua';
    var showAll =
      box.getAttribute('data-all') === '1' ||
      f !== 'semua' ||
      q !== '' ||
      kanal !== 'semua';

    var rows = box.querySelectorAll('.rows .row');
    var n = 0;

    rows.forEach(function (r) {
      var matchChip = true;
      if (f === 'terkunci') {
        matchChip = r.getAttribute('data-locked') === '1';
      } else if (f !== 'semua') {
        matchChip = r.getAttribute('data-s') === f;
      }

      var haystack = (r.getAttribute('data-q') || r.textContent || '').toLowerCase();
      var matchSearch = !q || haystack.indexOf(q) > -1;

      var matchKanal = true;
      if (kanal !== 'semua') {
        var rowKanal = (r.getAttribute('data-kanal') || '').toLowerCase();
        var rowTipe = (r.getAttribute('data-tipe-jual') || '').toLowerCase();
        if (kanal === 'ecommerce' || kanal === 'manual') {
          matchKanal = rowKanal === kanal;
        } else {
          matchKanal = rowTipe === kanal.toLowerCase();
        }
      }

      var matchMore = showAll || !r.hasAttribute('data-more');
      var on = matchChip && matchSearch && matchKanal && matchMore;
      r.hidden = !on;
      if (on) n++;
    });

    var cnt = box.querySelector('#cnt, [data-cnt]');
    if (cnt) {
      var total = box.getAttribute('data-total') || String(rows.length);
      var unit = box.getAttribute('data-unit') || 'item';
      cnt.textContent = 'Menampilkan ' + n + ' dari ' + total + ' ' + unit;
    }

    var expandEl = box.querySelector('[data-expand]');
    if (expandEl) {
      expandEl.hidden = f !== 'semua' || q !== '' || kanal !== 'semua';
    }

    var empty = box.querySelector('.empty-filter');
    if (empty) {
      empty.hidden = n > 0;
    }
  }

  function initFilterBoxes() {
    document.querySelectorAll('.chips').forEach(function (group) {
      var chips = group.querySelectorAll('button[data-f]');
      var box = group.closest('[data-filter-box]');
      chips.forEach(function (c) {
        c.addEventListener('click', function () {
          chips.forEach(function (x) {
            x.setAttribute('aria-pressed', x === c ? 'true' : 'false');
          });
          if (box) updateList(box);
        });
      });
    });

    document.querySelectorAll('[data-filter-box]').forEach(function (box) {
      var searchEl = box.querySelector('[data-search]');
      var kanalEl = box.querySelector('[data-kanal]');
      var sortEl = box.querySelector('[data-sort]');
      var resetEl = box.querySelector('[data-reset]');
      var expandEl = box.querySelector('[data-expand]');
      var listEl = box.querySelector('.rows');

      if (searchEl) {
        searchEl.addEventListener('input', function () {
          updateList(box);
        });
      }

      if (kanalEl) {
        kanalEl.addEventListener('change', function () {
          updateList(box);
        });
      }

      if (sortEl && listEl) {
        sortEl.addEventListener('change', function () {
          var by = sortEl.value;
          var items = Array.prototype.slice.call(listEl.children);
          items.sort(function (a, b) {
            var av = Number(a.getAttribute('data-' + by) || 0);
            var bv = Number(b.getAttribute('data-' + by) || 0);
            return by === 'short' || by === 'num' ? bv - av : av - bv;
          });
          items.forEach(function (el) {
            listEl.appendChild(el);
          });
          updateList(box);
        });
      }

      if (resetEl) {
        resetEl.addEventListener('click', function () {
          if (searchEl) searchEl.value = '';
          if (kanalEl) kanalEl.value = 'semua';
          if (sortEl) {
            sortEl.value = 'prio';
            sortEl.dispatchEvent(new Event('change'));
          }
          var firstChip = box.querySelector('.chips button[data-f="semua"]');
          if (firstChip) {
            firstChip.click();
          } else {
            updateList(box);
          }
        });
      }

      if (expandEl) {
        expandEl.addEventListener('click', function () {
          var next = box.getAttribute('data-all') !== '1';
          box.setAttribute('data-all', next ? '1' : '0');
          expandEl.textContent = next
            ? expandEl.getAttribute('data-collapse')
            : expandEl.getAttribute('data-expand');
          updateList(box);
        });
      }
    });
  }

  function initMobileNav() {
    var sidebar = document.getElementById('app-sidebar');
    var toggleBtn = document.querySelector('[data-nav-toggle]');
    var closeBtn = document.querySelector('[data-nav-close]');
    var backdrop = document.querySelector('[data-nav-backdrop]');
    if (!sidebar || !toggleBtn) return;

    var mobileMq = window.matchMedia('(max-width: 860px)');
    var hideTimer = null;

    function syncAria() {
      var isMobile = mobileMq.matches;
      var isOpen = sidebar.classList.contains('is-open');
      toggleBtn.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
      if (isMobile) {
        sidebar.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
      } else {
        sidebar.removeAttribute('aria-hidden');
      }
    }

    function openDrawer() {
      if (!mobileMq.matches) return;
      if (hideTimer) {
        clearTimeout(hideTimer);
        hideTimer = null;
      }
      sidebar.classList.add('is-open');
      document.body.classList.add('nav-drawer-open');
      if (backdrop) {
        backdrop.hidden = false;
        window.requestAnimationFrame(function () {
          backdrop.classList.add('is-open');
        });
      }
      syncAria();
      if (closeBtn) closeBtn.focus();
    }

    function closeDrawer(restoreFocus) {
      sidebar.classList.remove('is-open');
      document.body.classList.remove('nav-drawer-open');
      if (backdrop) {
        backdrop.classList.remove('is-open');
        if (hideTimer) clearTimeout(hideTimer);
        hideTimer = window.setTimeout(function () {
          if (!sidebar.classList.contains('is-open')) {
            backdrop.hidden = true;
          }
        }, 260);
      }
      syncAria();
      if (restoreFocus && mobileMq.matches) {
        toggleBtn.focus();
      }
    }

    toggleBtn.addEventListener('click', function () {
      if (sidebar.classList.contains('is-open')) {
        closeDrawer(false);
      } else {
        openDrawer();
      }
    });

    if (closeBtn) {
      closeBtn.addEventListener('click', function () {
        closeDrawer(true);
      });
    }

    if (backdrop) {
      backdrop.addEventListener('click', function () {
        closeDrawer(true);
      });
    }

    sidebar.querySelectorAll('.nav a').forEach(function (link) {
      link.addEventListener('click', function () {
        if (mobileMq.matches) closeDrawer(false);
      });
    });

    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && sidebar.classList.contains('is-open')) {
        closeDrawer(true);
      }
    });

    function handleViewportChange() {
      if (!mobileMq.matches && sidebar.classList.contains('is-open')) {
        closeDrawer(false);
      } else {
        syncAria();
      }
    }

    if (typeof mobileMq.addEventListener === 'function') {
      mobileMq.addEventListener('change', handleViewportChange);
    } else if (typeof mobileMq.addListener === 'function') {
      mobileMq.addListener(handleViewportChange);
    }

    syncAria();
  }

  document.addEventListener('DOMContentLoaded', function () {
    initMobileNav();
    initDialogs();
    initPanels();
    initFilterBoxes();
  });
})();
