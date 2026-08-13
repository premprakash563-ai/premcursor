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
          btn.textContent = btn.getAttribute('data-done') || 'Copied';
        });
      }
    });
  });

  document.querySelectorAll('.gn-slider').forEach(function (root) {
    var viewport = root.querySelector('.gn-slider-viewport');
    var track = root.querySelector('.gn-slider-track');
    var slides = Array.prototype.slice.call(root.querySelectorAll('.gn-slide'));
    if (!viewport || !track || !slides.length) return;

    var visAttr = parseInt(root.getAttribute('data-visible'), 10) || 3;
    var index = 0;
    var timer;

    function visibleCount() {
      var vis = visAttr;
      if (window.innerWidth < 760) vis = 1;
      else if (window.innerWidth < 1024) vis = Math.min(vis, 2);
      if (slides.length > 1) vis = Math.min(vis, slides.length - 1);
      else vis = 1;
      return Math.max(1, vis);
    }

    function slideWidth() {
      return viewport.clientWidth / visibleCount();
    }

    function applyWidths() {
      var w = slideWidth();
      slides.forEach(function (slide) {
        slide.style.flex = '0 0 ' + w + 'px';
        slide.style.maxWidth = w + 'px';
      });
    }

    function go(n, animate) {
      var vis = visibleCount();
      var max = Math.max(0, slides.length - vis);
      if (max === 0) {
        index = 0;
        track.style.transform = 'translateX(0)';
        return;
      }
      index = n;
      if (index < 0) index = max;
      if (index > max) index = 0;
      if (animate === false) {
        track.style.transition = 'none';
      } else {
        track.style.transition = 'transform .45s ease';
      }
      track.style.transform = 'translateX(' + (-slideWidth() * index) + 'px)';
      if (animate === false) {
        track.offsetHeight;
        track.style.transition = 'transform .45s ease';
      }
    }

    function layout() {
      applyWidths();
      go(index, false);
    }

    var prev = root.querySelector('.prev');
    var next = root.querySelector('.next');
    if (prev) {
      prev.addEventListener('click', function (e) {
        e.preventDefault();
        go(index - 1);
      });
    }
    if (next) {
      next.addEventListener('click', function (e) {
        e.preventDefault();
        go(index + 1);
      });
    }

    function start() {
      clearInterval(timer);
      if (slides.length > visibleCount()) {
        timer = setInterval(function () { go(index + 1); }, 5000);
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
