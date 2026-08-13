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
  var searchBox = document.querySelector('.header-search');
  if (searchBtn && searchBox) {
    searchBtn.addEventListener('click', function () {
      var open = searchBox.classList.toggle('is-open');
      searchBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
      if (open) {
        var field = searchBox.querySelector('.search-field');
        if (field) field.focus();
      }
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
    var loop = root.getAttribute('data-loop') !== '0';
    var index = 0;
    var timer;
    var clones = [];
    var locked = false;

    function visibleCount() {
      var vis = visAttr;
      if (window.innerWidth < 760) vis = 1;
      else if (window.innerWidth < 1024) vis = Math.min(vis, 2);
      if (originals.length > 1) vis = Math.min(vis, originals.length - (loop ? 0 : 1));
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
      return viewport.clientWidth / visibleCount();
    }

    function applyWidths() {
      var w = slideWidth();
      allSlides().forEach(function (slide) {
        slide.style.flex = '0 0 ' + w + 'px';
        slide.style.maxWidth = w + 'px';
      });
    }

    function setX(n, animate) {
      if (animate === false) {
        track.style.transition = 'none';
      } else {
        track.style.transition = 'transform .45s ease';
      }
      track.style.transform = 'translateX(' + (-slideWidth() * n) + 'px)';
      if (animate === false) {
        track.offsetHeight;
        track.style.transition = 'transform .45s ease';
      }
    }

    function go(n, animate) {
      if (locked && animate !== false) return;
      var vis = visibleCount();
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
      var max = Math.max(0, originals.length - vis);
      if (index < 0) index = max;
      if (index > max) index = 0;
      setX(index, animate !== false);
    }

    track.addEventListener('transitionend', function () {
      locked = false;
      if (!loop || originals.length < 2) return;
      if (index >= originals.length) {
        index = 0;
        setX(0, false);
      }
    });

    function next() {
      if (loop && originals.length > 1) {
        go(index + 1);
        if (index >= originals.length) {
          /* shown clone; snap handled on transitionend */
        }
      } else {
        go(index + 1);
      }
    }

    function layout() {
      rebuildClones();
      applyWidths();
      if (index >= originals.length) index = 0;
      setX(index, false);
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
