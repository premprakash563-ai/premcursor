(function ($) {
  'use strict';

  function toggleFields($row) {
    var type = $row.find('.gn-type').val();
    $row.attr('data-type', type);
  }

  function bind($root) {
    $root.find('.gn-type').on('change', function () {
      toggleFields($(this).closest('.gn-section-row'));
    });
    $root.find('.gn-remove').on('click', function () {
      $(this).closest('.gn-section-row').remove();
    });
    $root.find('.gn-section-row').each(function () {
      toggleFields($(this));
    });
  }

  $(function () {
    var $list = $('#gn-sections');
    bind($list);

    $list.sortable({
      handle: '.gn-drag',
      placeholder: 'gn-placeholder'
    });

    $('#gn-add-section').on('click', function () {
      var html = $('#gn-section-tpl').html().replace(/__i__/g, String(Date.now()));
      var $row = $(html);
      $list.append($row);
      bind($row);
    });
  });
})(jQuery);
