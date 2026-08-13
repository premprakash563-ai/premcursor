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

  function addRow(overrides) {
    var html = $('#gn-section-tpl').html().replace(/__i__/g, String(Date.now()));
    var $row = $(html);
    if (overrides) {
      if (overrides.type) {
        $row.find('.gn-type').val(overrides.type);
      }
      if (overrides.position) {
        $row.find('.gn-position').val(overrides.position);
      }
      if (overrides.layout) {
        $row.find('select[name*="[layout]"]').val(overrides.layout);
      }
    }
    $('#gn-sections').append($row);
    bind($row);
    toggleFields($row);
  }

  $(function () {
    var $list = $('#gn-sections');
    bind($list);

    $list.sortable({
      handle: '.gn-drag',
      placeholder: 'gn-placeholder'
    });

    $('#gn-add-section').on('click', function () {
      addRow();
    });

    $('.gn-add-preset').on('click', function () {
      addRow({
        type: $(this).data('type'),
        position: $(this).data('position'),
        layout: $(this).data('layout')
      });
    });

    $('input[name="gazettenews_reset_home"]').on('click', function () {
      return window.confirm('Reset homepage sections to the default left/right news layout?');
    });
  });
})(jQuery);
