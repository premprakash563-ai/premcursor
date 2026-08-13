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
})();
