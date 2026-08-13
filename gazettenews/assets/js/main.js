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
    var track = root.querySelector('.gn-slider-track');
    var slides = root.querySelectorAll('.gn-slide');
    if (!track || !slides.length) return;
    var visible = parseInt(root.getAttribute('data-visible'), 10) || 4;
    if (window.innerWidth < 760) visible = 1;
    else if (window.innerWidth < 1024) visible = Math.min(visible, 2);
    var index = 0;
    var max = Math.max(0, slides.length - visible);
    function go(n) {
      index = n;
      if (index < 0) index = max;
      if (index > max) index = 0;
      track.style.transform = 'translateX(' + (-(100 / visible) * index) + '%)';
    }
    var prev = root.querySelector('.prev');
    var next = root.querySelector('.next');
    if (prev) prev.addEventListener('click', function () { go(index - 1); });
    if (next) next.addEventListener('click', function () { go(index + 1); });
    setInterval(function () { go(index + 1); }, 5000);
    slides.forEach(function (slide) {
      slide.style.flex = '0 0 ' + (100 / visible) + '%';
    });
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
