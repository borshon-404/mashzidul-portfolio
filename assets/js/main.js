/* MTB Portfolio — main.js (vanilla, no dependencies) */
(function () {
  'use strict';
  var $ = function (s, c) { return (c || document).querySelector(s); };
  var $$ = function (s, c) { return Array.prototype.slice.call((c || document).querySelectorAll(s)); };
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /* ---------- Header scroll state ---------- */
  var header = $('.header');
  function onScroll() {
    if (!header) return;
    header.classList.toggle('is-scrolled', window.scrollY > 24);
    var tt = $('.to-top');
    if (tt) tt.classList.toggle('is-on', window.scrollY > 600);
  }
  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll();

  /* ---------- Mobile menu ---------- */
  var burger = $('.burger'), menu = $('.mobile-menu');
  function setMenu(open) {
    if (!burger || !menu) return;
    burger.setAttribute('aria-expanded', String(open));
    burger.setAttribute('aria-label', open ? 'Close menu' : 'Open menu');
    menu.classList.toggle('is-open', open);
    document.body.classList.toggle('menu-open', open);
    if (open) {
      var first = menu.querySelector('a');
      if (first) setTimeout(function () { first.focus(); }, 350);
    } else {
      burger.focus();
    }
  }
  if (burger && menu) {
    burger.addEventListener('click', function () {
      setMenu(burger.getAttribute('aria-expanded') !== 'true');
    });
    $$('a', menu).forEach(function (a) {
      a.addEventListener('click', function () { setMenu(false); });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && menu.classList.contains('is-open')) setMenu(false);
    });
    window.addEventListener('resize', function () {
      if (window.innerWidth > 1024 && menu.classList.contains('is-open')) setMenu(false);
    });
  }

  /* ---------- Scroll reveal ---------- */
  var revealEls = $$('[data-reveal], .stagger');
  if (revealEls.length) {
    if (reduced || !('IntersectionObserver' in window)) {
      revealEls.forEach(function (el) { el.classList.add('is-in'); });
    } else {
      var io = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
        });
      }, { threshold: 0.14, rootMargin: '0px 0px -6% 0px' });
      revealEls.forEach(function (el) { io.observe(el); });
    }
  }

  /* ---------- Counters ---------- */
  var counters = $$('[data-count]');
  if (counters.length) {
    var runCount = function (el) {
      var target = parseInt(el.getAttribute('data-count'), 10) || 0;
      if (reduced) { el.textContent = String(target); return; }
      var t0 = null, dur = 1400;
      function step(ts) {
        if (!t0) t0 = ts;
        var p = Math.min((ts - t0) / dur, 1);
        var eased = 1 - Math.pow(1 - p, 3);
        el.textContent = String(Math.round(target * eased));
        if (p < 1) requestAnimationFrame(step);
      }
      requestAnimationFrame(step);
    };
    if ('IntersectionObserver' in window) {
      var cio = new IntersectionObserver(function (entries) {
        entries.forEach(function (en) {
          if (en.isIntersecting) { runCount(en.target); cio.unobserve(en.target); }
        });
      }, { threshold: 0.5 });
      counters.forEach(function (el) { cio.observe(el); });
    } else {
      counters.forEach(runCount);
    }
  }

  /* ---------- FAQ accordion ---------- */
  $$('.faq-item__q').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var item = btn.closest('.faq-item');
      var list = item.parentElement;
      var isOpen = item.classList.contains('is-open');
      $$('.faq-item.is-open', list).forEach(function (o) {
        o.classList.remove('is-open');
        $('.faq-item__q', o).setAttribute('aria-expanded', 'false');
      });
      if (!isOpen) {
        item.classList.add('is-open');
        btn.setAttribute('aria-expanded', 'true');
      }
    });
  });

  /* ---------- Project & Blog filters (reusable) ---------- */
  function setupFilterGroup(gridSelector, cardSelector) {
    var container = $(gridSelector);
    if (!container) return;
    var group = container.closest('.section') || container.parentElement;
    if (!group) return;
    var btns = $$('.filter-btn', group);
    if (!btns.length) return;
    // Only bind if this group contains the target grid
    var hasGrid = group.querySelector(gridSelector) || document.querySelector(gridSelector);
    if (!hasGrid) return;
    btns.forEach(function (btn) {
      btn.addEventListener('click', function () {
        var scope = btn.closest('.section') || document;
        var localBtns = $$('.filter-btn', scope);
        localBtns.forEach(function (b) { b.setAttribute('aria-pressed', 'false'); });
        btn.setAttribute('aria-pressed', 'true');
        var f = btn.getAttribute('data-filter');
        $$(cardSelector).forEach(function (card) {
          // Only filter cards that are inside the same section as the button group for blog,
          // but for projects we filter globally (projects page has only one grid)
          var cardSection = card.closest('.section');
          var btnSection = btn.closest('.section');
          if (cardSection && btnSection && cardSection !== btnSection) return;
          var cat = card.getAttribute('data-category') || '';
          var show = f === 'all' || cat === f;
          card.style.display = show ? '' : 'none';
        });
        // Handle empty state for blog
        var empty = $('.blog-empty');
        if (empty && gridSelector.indexOf('blog') > -1) {
          var visible = $$(cardSelector).filter(function (c) { return c.style.display !== 'none'; });
          empty.style.display = visible.length ? 'none' : '';
        }
      });
    });
  }
  setupFilterGroup('.proj-grid', '.proj-grid .proj-card');
  setupFilterGroup('.blog-grid', '.blog-grid .blog-card');

  /* ---------- Blog search filter (title, category, tags) ---------- */
  var search = $('.blog-search');
  if (search) {
    search.addEventListener('input', function () {
      var q = search.value.trim().toLowerCase();
      $$('.blog-grid .blog-card').forEach(function (card) {
        var t = (card.getAttribute('data-title') || '').toLowerCase();
        var c = (card.getAttribute('data-category') || '').toLowerCase();
        var tags = (card.getAttribute('data-tags') || '').toLowerCase();
        var excerpt = (card.querySelector('.blog-card__excerpt')?.textContent || '').toLowerCase();
        var match = !q || t.indexOf(q) > -1 || c.indexOf(q) > -1 || tags.indexOf(q) > -1 || excerpt.indexOf(q) > -1;
        card.style.display = match ? '' : 'none';
      });
      var empty = $('.blog-empty');
      if (empty) {
        var visible = $$('.blog-grid .blog-card').filter(function (c) { return c.style.display !== 'none'; });
        empty.style.display = visible.length ? 'none' : '';
      }
    });
  }

  /* ---------- Share buttons (copy link) ---------- */
  $$('.share-copy').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-copy');
      if (!url) return;
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          var orig = btn.innerHTML;
          btn.classList.add('is-copied');
          btn.innerHTML = '✓ Copied';
          setTimeout(function () { btn.classList.remove('is-copied'); btn.innerHTML = orig; }, 2000);
        });
      } else {
        // fallback
        var ta = document.createElement('textarea');
        ta.value = url;
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); } catch(e){}
        document.body.removeChild(ta);
        btn.classList.add('is-copied');
        setTimeout(function(){ btn.classList.remove('is-copied'); }, 2000);
      }
    });
  });

  /* ---------- Forms (validation + mailto handoff, endpoint-ready) ---------- */
  var FORM_ENDPOINT = null; /* set to e.g. '/api/contact' or Formspree URL when available */
  function validate(form) {
    var ok = true;
    $$('[required]', form).forEach(function (input) {
      var field = input.closest('.field');
      var bad = !input.value.trim() || (input.type === 'email' && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(input.value));
      if (field) field.classList.toggle('has-error', bad);
      if (bad) ok = false;
    });
    return ok;
  }
  $$('form[data-form]').forEach(function (form) {
    form.addEventListener('submit', function (e) {
      e.preventDefault();
      var status = $('.form-status', form);
      if (!validate(form)) {
        if (status) { status.className = 'form-status is-err'; status.textContent = 'Please fix the highlighted fields and try again.'; }
        return;
      }
      var data = {};
      $$('input, textarea, select', form).forEach(function (i) { if (i.name) data[i.name] = i.value; });
      if (FORM_ENDPOINT) {
        fetch(FORM_ENDPOINT, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(data) })
          .then(function (r) { if (!r.ok) throw new Error(); if (status) { status.className = 'form-status is-ok'; status.textContent = 'Thank you! Your message has been sent.'; } form.reset(); })
          .catch(function () { if (status) { status.className = 'form-status is-err'; status.textContent = 'Something went wrong. Please email me directly at mail@mashzidultanun.com.'; } });
      } else {
        /* functional fallback: open prefilled email */
        var kind = form.getAttribute('data-form');
        var subject, body;
        if (kind === 'booking') {
          subject = 'Consultation Request — ' + (data.name || '');
          body = 'Hello Mashzidul,\n\nI would like to book a free consultation.\n\nName: ' + (data.name || '') +
            '\nEmail: ' + (data.email || '') + '\nPreferred date: ' + (data.date || 'flexible') +
            '\nAgenda: ' + (data.agenda || []).join(', ') + '\n\nNotes:\n' + (data.message || '');
          if (typeof data.agenda !== 'string' && data.agenda) body = body.replace('Agenda: ', 'Agenda: ');
        } else {
          subject = (data.subject || 'Project enquiry') + ' — ' + (data.name || '');
          body = 'Hello Mashzidul,\n\n' + (data.message || '') + '\n\n— ' + (data.name || '') + '\n' + (data.email || '') + (data.phone ? '\n' + data.phone : '');
        }
        /* collect checkboxes for booking */
        if (kind === 'booking') {
          var agenda = $$('input[name="agenda"]:checked', form).map(function (c) { return c.value; });
          body = body.replace(/Agenda: [^\n]*/, 'Agenda: ' + (agenda.join(', ') || 'General discussion'));
        }
        window.location.href = 'mailto:mail@mashzidultanun.com?subject=' + encodeURIComponent(subject) + '&body=' + encodeURIComponent(body);
        if (status) { status.className = 'form-status is-ok'; status.textContent = 'Opening your email client with the details prefilled… (A direct sending endpoint can be connected later without design changes.)'; }
      }
    });
    $$('input, textarea', form).forEach(function (i) {
      i.addEventListener('input', function () { var f = i.closest('.field'); if (f) f.classList.remove('has-error'); });
    });
  });

  /* ---------- Table of Contents active state ---------- */
  var tocLinks = $$('.post-toc a');
  var tocHeads = tocLinks.map(function (a) {
    try { return document.getElementById(a.getAttribute('href').slice(1)); } catch(e){ return null; }
  }).filter(Boolean);
  if (tocLinks.length && tocHeads.length && 'IntersectionObserver' in window) {
    var tocIO = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) {
          var id = en.target.id;
          tocLinks.forEach(function (l) {
            l.classList.toggle('is-active', l.getAttribute('href') === '#' + id);
          });
        }
      });
    }, { rootMargin: '-20% 0px -70% 0px', threshold: 0 });
    tocHeads.forEach(function (h) { tocIO.observe(h); });
    // smooth scroll for TOC
    tocLinks.forEach(function (a) {
      a.addEventListener('click', function (e) {
        e.preventDefault();
        var target = document.getElementById(a.getAttribute('href').slice(1));
        if (target) {
          window.scrollTo({ top: target.getBoundingClientRect().top + window.pageYOffset - 100, behavior: reduced ? 'auto' : 'smooth' });
          history.pushState(null, '', a.getAttribute('href'));
        }
      });
    });
  }

  /* ---------- Back to top ---------- */
  var tt = $('.to-top');
  if (tt) tt.addEventListener('click', function (e) { e.preventDefault(); window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' }); });

  /* ---------- Year ---------- */
  $$('[data-year]').forEach(function (el) { el.textContent = String(new Date().getFullYear()); });
})();
