(function (wp) {
  var el = wp.element.createElement;
  var registerBlockType = wp.blocks.registerBlockType;
  var InspectorControls = wp.blockEditor.InspectorControls;
  var PanelBody = wp.components.PanelBody;
  var TextControl = wp.components.TextControl;
  var SelectControl = wp.components.SelectControl;
  var RangeControl = wp.components.RangeControl;
  var ServerSideRender = wp.serverSideRender;
  var __ = wp.i18n.__;
  var cats = (window.gazetteNewsBlocks && window.gazetteNewsBlocks.categories) ? window.gazetteNewsBlocks.categories : [{ value: 0, label: 'Latest posts' }];

  registerBlockType('gazettenews/mosaic', {
    title: __('Gazette: Featured mosaic', 'gazettenews'),
    icon: 'grid-view',
    category: 'widgets',
    attributes: {
      cat: { type: 'number', default: 0 },
      count: { type: 'number', default: 5 }
    },
    edit: function (props) {
      return el('div', {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Mosaic', 'gazettenews'), initialOpen: true },
            el(SelectControl, {
              label: __('Category', 'gazettenews'),
              value: props.attributes.cat,
              options: cats,
              onChange: function (v) { props.setAttributes({ cat: parseInt(v, 10) || 0 }); }
            }),
            el(RangeControl, {
              label: __('Posts', 'gazettenews'),
              value: props.attributes.count,
              min: 3,
              max: 5,
              onChange: function (v) { props.setAttributes({ count: v }); }
            })
          )
        ),
        el(ServerSideRender, { block: 'gazettenews/mosaic', attributes: props.attributes })
      );
    },
    save: function () { return null; }
  });

  registerBlockType('gazettenews/posts', {
    title: __('Gazette: Posts module', 'gazettenews'),
    icon: 'screenoptions',
    category: 'widgets',
    attributes: {
      title: { type: 'string', default: '' },
      cat: { type: 'number', default: 0 },
      layout: { type: 'string', default: 'grid' },
      count: { type: 'number', default: 6 }
    },
    edit: function (props) {
      return el('div', {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Module', 'gazettenews'), initialOpen: true },
            el(TextControl, {
              label: __('Title', 'gazettenews'),
              value: props.attributes.title,
              onChange: function (v) { props.setAttributes({ title: v }); }
            }),
            el(SelectControl, {
              label: __('Category', 'gazettenews'),
              value: props.attributes.cat,
              options: cats,
              onChange: function (v) { props.setAttributes({ cat: parseInt(v, 10) || 0 }); }
            }),
            el(SelectControl, {
              label: __('Layout', 'gazettenews'),
              value: props.attributes.layout,
              options: [
                { label: __('Split (big + list)', 'gazettenews'), value: 'split' },
                { label: __('Grid', 'gazettenews'), value: 'grid' },
                { label: __('List', 'gazettenews'), value: 'list' }
              ],
              onChange: function (v) { props.setAttributes({ layout: v }); }
            }),
            el(RangeControl, {
              label: __('Posts', 'gazettenews'),
              value: props.attributes.count,
              min: 2,
              max: 12,
              onChange: function (v) { props.setAttributes({ count: v }); }
            })
          )
        ),
        el(ServerSideRender, { block: 'gazettenews/posts', attributes: props.attributes })
      );
    },
    save: function () { return null; }
  });

  registerBlockType('gazettenews/shop', {
    title: __('Gazette: Shop', 'gazettenews'),
    icon: 'cart',
    category: 'widgets',
    attributes: {
      title: { type: 'string', default: 'Shop' },
      count: { type: 'number', default: 4 }
    },
    edit: function (props) {
      return el('div', {},
        el(InspectorControls, {},
          el(PanelBody, { title: __('Shop', 'gazettenews'), initialOpen: true },
            el(TextControl, {
              label: __('Title', 'gazettenews'),
              value: props.attributes.title,
              onChange: function (v) { props.setAttributes({ title: v }); }
            }),
            el(RangeControl, {
              label: __('Products', 'gazettenews'),
              value: props.attributes.count,
              min: 2,
              max: 8,
              onChange: function (v) { props.setAttributes({ count: v }); }
            })
          )
        ),
        el(ServerSideRender, { block: 'gazettenews/shop', attributes: props.attributes })
      );
    },
    save: function () { return null; }
  });
})(window.wp);
