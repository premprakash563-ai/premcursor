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
    searchModal.hidden = false;
    searchModal.classList.add('is-open');
    document.body.classList.add('gn-search-on');
    if (searchBtn) searchBtn.setAttribute('aria-expanded', 'true');
    var field = searchModal.querySelector('.search-field');
    if (field) window.setTimeout(function () { field.focus(); }, 40);
  }
  function closeSearch() {
    if (!searchModal) return;
    searchModal.classList.remove('is-open');
    searchModal.hidden = true;
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
    var originals = Array.prototype.slice.call(root.querySelectorAll('.gn-slide:not(.is-clone)'));
    if (!viewport || !track || !originals.length) return;

    var visAttr = parseInt(root.getAttribute('data-visible'), 10) || 3;
    var speed = parseInt(root.getAttribute('data-speed'), 10);
    if (!speed && window.gnTheme && gnTheme.sliderSpeed) speed = parseInt(gnTheme.sliderSpeed, 10);
    if (!speed || speed < 1500) speed = 5000;
    var loop = root.getAttribute('data-loop') !== '0';
    var index = 0;
    var timer;
    var clones = [];
    var locked = false;

    function visibleCount() {
      var vis = visAttr;
      if (window.innerWidth < 760) vis = 1;
      else if (window.innerWidth < 1024) vis = Math.min(vis, 2);
      vis = Math.min(vis, originals.length);
      return Math.max(1, vis);
    }

    function rebuildClones() {
      clones.forEach(function (c) { if (c.parentNode) c.parentNode.removeChild(c); });
      clones = [];
      if (!loop || originals.length < 2) return;
      var vis = visibleCount();
      for (var i = 0; i < vis; i++) {
        var c = originals[i].cloneNode(true);
        c.classList.add('is-clone');
        track.appendChild(c);
        clones.push(c);
      }
    }

    function allSlides() {
      return Array.prototype.slice.call(track.querySelectorAll('.gn-slide'));
    }

    function slideWidth() {
      var w = viewport.clientWidth || root.clientWidth || 0;
      return Math.max(w, 1) / visibleCount();
    }

    function applyWidths() {
      var w = slideWidth();
      allSlides().forEach(function (slide) {
        slide.style.flex = '0 0 ' + w + 'px';
        slide.style.width = w + 'px';
        slide.style.maxWidth = w + 'px';
      });
    }

    function setX(n, animate) {
      if (animate === false) {
        track.style.transition = 'none';
      } else {
        track.style.transition = 'transform .45s ease';
      }
      track.style.transform = 'translate3d(' + (-slideWidth() * n) + 'px,0,0)';
      if (animate === false) {
        track.offsetHeight;
        track.style.transition = 'transform .45s ease';
      }
    }

    function go(n, animate) {
      if (locked && animate !== false) return;
      if (originals.length <= 1) {
        index = 0;
        setX(0, false);
        return;
      }
      index = n;
      if (loop) {
        if (index < 0) index = originals.length - 1;
        if (animate !== false) {
          locked = true;
          window.setTimeout(function () { locked = false; }, 520);
        }
        setX(index, animate !== false);
        return;
      }
      var max = Math.max(0, originals.length - visibleCount());
      if (index < 0) index = max;
      if (index > max) index = 0;
      setX(index, animate !== false);
    }

    track.addEventListener('transitionend', function (e) {
      if (e.target !== track) return;
      locked = false;
      if (!loop || originals.length < 2) return;
      if (index >= originals.length) {
        index = 0;
        setX(0, false);
      }
    });

    function next() {
      go(index + 1);
    }

    var laying = false;
    function layout() {
      if (laying) return;
      laying = true;
      rebuildClones();
      applyWidths();
      if (index >= originals.length) index = 0;
      setX(index, false);
      var showNav = originals.length > 1;
      root.querySelectorAll('.gn-slide-btn').forEach(function (btn) {
        btn.hidden = !showNav;
      });
      window.requestAnimationFrame(function () { laying = false; });
    }

    var prev = root.querySelector('.prev');
    var nextBtn = root.querySelector('.next');
    if (prev) {
      prev.addEventListener('click', function (e) {
        e.preventDefault();
        go(index - 1);
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', function (e) {
        e.preventDefault();
        go(index + 1);
      });
    }

    function start() {
      clearInterval(timer);
      if (originals.length > 1) {
        timer = setInterval(next, speed);
      }
    }

    root.addEventListener('mouseenter', function () { clearInterval(timer); });
    root.addEventListener('mouseleave', start);
    window.addEventListener('resize', function () {
      layout();
      start();
    });
    window.addEventListener('load', layout);
    if (window.ResizeObserver) {
      new ResizeObserver(layout).observe(viewport);
    }

    layout();
    start();
  });

  document.querySelectorAll('.video-playlist').forEach(function (box) {
    var frame = box.querySelector('.gn-yt-player');
    var now = box.querySelector('.now-title');
    box.querySelectorAll('.video-item').forEach(function (btn) {
      btn.addEventListener('click', function () {
        var id = btn.getAttribute('data-id');
        var title = btn.getAttribute('data-title') || '';
        if (frame && id) {
          frame.src = 'https://www.youtube.com/embed/' + id + '?autoplay=1';
        }
        if (now) now.textContent = title;
        box.querySelectorAll('.video-item').forEach(function (el) { el.classList.remove('is-active'); });
        btn.classList.add('is-active');
      });
    });
  });
})();
