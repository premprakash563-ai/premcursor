(function () {
  'use strict';

  var nav = document.querySelector('.main-nav');
  var toggle = document.querySelector('.menu-toggle');
  if (nav && toggle) {
    toggle.addEventListener('click', function () {
      var open = nav.classList.toggle('is-open');
      toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
  }

  var searchBtn = document.querySelector('.search-toggle');
  var searchModal = document.getElementById('gn-search-modal');
  function openSearch() {
    if (!searchModal) return;
    searchModal.classList.add('is-open');
    searchModal.setAttribute('aria-hidden', 'false');
    document.body.classList.add('gn-search-on');
    if (searchBtn) searchBtn.setAttribute('aria-expanded', 'true');
    var field = searchModal.querySelector('.search-field');
    if (field) window.setTimeout(function () { field.focus(); }, 40);
  }
  function closeSearch() {
    if (!searchModal) return;
    searchModal.classList.remove('is-open');
    searchModal.setAttribute('aria-hidden', 'true');
    document.body.classList.remove('gn-search-on');
    if (searchBtn) searchBtn.setAttribute('aria-expanded', 'false');
  }
  if (searchBtn && searchModal) {
    searchBtn.addEventListener('click', function () {
      if (searchModal.classList.contains('is-open')) closeSearch();
      else openSearch();
    });
    searchModal.querySelectorAll('[data-gn-search-close]').forEach(function (el) {
      el.addEventListener('click', closeSearch);
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && searchModal.classList.contains('is-open')) closeSearch();
    });
  }

  var list = document.querySelector('.breaking-list');
  if (list && list.children.length) {
    list.innerHTML = list.innerHTML + list.innerHTML;
  }

  document.querySelectorAll('.share-copy').forEach(function (btn) {
    btn.addEventListener('click', function () {
      var url = btn.getAttribute('data-url') || '';
      if (navigator.clipboard && navigator.clipboard.writeText) {
        navigator.clipboard.writeText(url).then(function () {
          btn.classList.add('is-copied');
          btn.setAttribute('aria-label', btn.getAttribute('data-done') || 'Copied');
        });
      }
    });
  });

  document.querySelectorAll('.gn-slider').forEach(function (root) {
    var viewport = root.querySelector('.gn-slider-viewport');
    var track = root.querySelector('.gn-slider-track');
    var originals = Array.prototype.slice.call(root.querySelectorAll('.gn-slide'));
    if (!viewport || !track || !originals.length) return;

    var visAttr = parseInt(root.getAttribute('data-visible'), 10) || 3;
    var speed = parseInt(root.getAttribute('data-speed'), 10);
    if (!speed && window.gnTheme && gnTheme.sliderSpeed) speed = parseInt(gnTheme.sliderSpeed, 10);
    if (!speed || speed < 1500) speed = 5000;
    var loop = root.getAttribute('data-loop') !== '0' && originals.length > 1;
    var index = 0;
    var timer;
    var lastVis = 0;

    function visibleCount() {
      var vis = visAttr;
      if (window.innerWidth < 760) vis = 1;
      else if (window.innerWidth < 1024) vis = Math.min(vis, 2);
      return Math.max(1, Math.min(vis, originals.length));
    }

    function slideWidth() {
      return Math.max(viewport.clientWidth || root.clientWidth || 1, 1) / visibleCount();
    }

    function applyWidths() {
      var w = slideWidth();
      Array.prototype.forEach.call(track.children, function (slide) {
        slide.style.flex = '0 0 ' + w + 'px';
        slide.style.width = w + 'px';
        slide.style.maxWidth = w + 'px';
      });
    }

    function setX(n) {
      track.style.transform = 'translate3d(' + (-slideWidth() * n) + 'px,0,0)';
    }

    function go(n) {
      if (originals.length <= 1) {
        index = 0;
        setX(0);
        return;
      }
      var max = originals.length - 1;
      if (loop) {
        if (n < 0) n = max;
        if (n > max) n = 0;
      } else {
        if (n < 0) n = 0;
        if (n > max) n = 0;
      }
      index = n;
      setX(index);
    }

    function layout() {
      var vis = visibleCount();
      applyWidths();
      if (index >= originals.length) index = 0;
      setX(index);
      lastVis = vis;
      var showNav = originals.length > 1;
      root.querySelectorAll('.gn-slide-btn').forEach(function (btn) {
        btn.hidden = !showNav;
      });
    }

    var prev = root.querySelector('.prev');
    var nextBtn = root.querySelector('.next');
    if (prev) prev.addEventListener('click', function (e) { e.preventDefault(); go(index - 1); });
    if (nextBtn) nextBtn.addEventListener('click', function (e) { e.preventDefault(); go(index + 1); });

    function start() {
      clearInterval(timer);
      if (originals.length > 1) timer = setInterval(function () { go(index + 1); }, speed);
    }

    var resizeTimer;
    window.addEventListener('resize', function () {
      clearTimeout(resizeTimer);
      resizeTimer = setTimeout(layout, 150);
    });

    layout();
    start();
  });

  document.querySelectorAll('.video-playlist').forEach(function (box) {
    var frame = box.querySelector('.gn-yt-player');
    var wrap = box.querySelector('.video-frame');
    var now = box.querySelector('.now-title');
    function playId(id, title) {
      if (!frame || !id) return;
      frame.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1';
      if (wrap) wrap.classList.add('is-playing');
      if (now && title) now.textContent = title;
    }
    var poster = box.querySelector('.gn-yt-poster');
    if (poster) {
      poster.addEventListener('click', function () {
        playId(poster.getAttribute('data-id'), now ? now.textContent : '');
      });
    }
    box.querySelectorAll('.video-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        playId(btn.getAttribute('data-id'), btn.getAttribute('data-title') || '');
        box.querySelectorAll('.video-item').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
      });
    });
  });
})();
