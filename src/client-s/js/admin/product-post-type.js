(function ($) {
  $(document).ready(function () {
    // Essential variables.

    var prefix = 'rsxqjzypgdqmrnnkkkrmgshvnnnkzzvu';
    var data = window[prefix + 'ProductPostTypeData'];
    var jsGridData = neyjfbxruwddgfeedwacfbggzbxkwfxhJQueryJsGridData;

    $('.' + prefix + '-product-meta').each(function () {
      var $meta = $(this); // Current meta div.
      var $permissionsGrid = $meta.find('.-permissions-grid[data-toggle~="jquery-jsgrid"]');

      if (!$meta.length || !$permissionsGrid.length) {
        return; // Not possible at this time.
      }
      var initialGridRefreshComplete = false; // Initialize.
      var variationKey = $meta.data('variationKey'); // Zero-based index.
      var isVariation = variationKey ? true : false; // A product variation?
      var productPermissions = $.parseJSON($meta.find('.-product-permissions').val());

      // Easy tip builder; needed below.

      var tip = function (tip) {
        return '<span class="woocommerce-help-tip" data-toggle="jquery-tip" data-tip="' + _.escape(tip) + '"></span>';
      };
      // Restriction items.

      var restrictionItems = [{
        ID: null,
        title: ''
      }]; // Initialize array.
      $.each(data.restrictionTitlesById, function (ID, title) {
        restrictionItems.push({
          ID: ID,
          title: title
        });
      });

      // Access offset time items.

      var accessOffsetTimeItems = []; // Initialize array.
      $.each(data.productPermissionAccessOffsetTimes, function (key, title) {
        accessOffsetTimeItems.push({
          key: key, // Unique string key identifier or `X days`, etc.
          title: !/^[0-9]/.test(key) ? title : data.i18n.accessOffsetTimeAfter + ' ' + title
        });
      });

      // Expire offset time items.

      var expireOffsetTimeItems = []; // Initialize array.
      $.each(data.productPermissionExpireOffsetTimes, function (key, title) {
        expireOffsetTimeItems.push({
          key: key, // Unique string key identifier or `X days`, etc.
          title: !/^[0-9]/.test(key) ? title : title + ' ' + data.i18n.expireOffsetTimeLater
        });
      });
      // jsGrid configuration.

      $permissionsGrid.jsGrid($.extend({}, jsGridData.defaultOptions, {
        data: productPermissions, // Permissions array.
        noDataContent: data.i18n.noDataContent, // On empty.

        insertingByDefault: productPermissions.length === 0,
        sorting: false, // Not compatible w/ sortable.
        paging: false, // Not compatible w/ sortable.

        onRefreshed: function () {
          if (initialGridRefreshComplete) {
            return; // Done already.
          }
          initialGridRefreshComplete = true; // Doing it now.

          $permissionsGrid.find('.jsgrid-grid-body > table > tbody').sortable({
            placeholder: 'ui-state-highlight'
          });
        }, // This allows drag n' drop.

        fields: [
          // Assigned IDs.
          {
            type: 'number',
            align: 'center',
            name: 'product_id',
            title: data.i18n.productIdTitle,

            visible: false,
          },

          // Restriction ID.
          {
            width: '30%',
            type: 'select',
            align: 'center',
            name: 'restriction_id',
            title: data.i18n.restrictionIdTitle + ' ' + tip(data.i18n.restrictionIdTitleTip),

            visible: true,
            editing: true,
            inserting: true,

            valueField: 'ID',
            valueType: 'number',
            textField: 'title',

            items: restrictionItems,

            validate: {
              validator: function (value, item) {
                return typeof value === 'number' && value > 0;
              },
              message: function (value, item) {
                return '• ' + data.i18n.restrictionAccessRequired;
              }
            },
            itemTemplate: function (value, item) {
              return value ? '<strong>' + _.escape(data.restrictionTitlesById[value]) + '</strong>' : '';
            }
          },

          // Access offset time.
          {
            width: '30%',
            type: 'select',
            align: 'center',
            name: 'access_offset_time',
            title: data.i18n.accessOffsetTimeTitle + ' ' + tip(data.i18n.accessOffsetTimeTitleTip),

            visible: true,
            editing: true,
            inserting: true,

            valueField: 'key',
            valueType: 'string',
            textField: 'title',
            allowOther: true,
            otherPlaceholderText: data.i18n.accessOffsetTimeOtherPlaceholder,

            items: accessOffsetTimeItems,

            validate: {
              validator: function (value, item) {
                return typeof value === 'string' && value && value !== '0';
              },
              message: function (value, item) {
                return '• ' + data.i18n.accessOffsetTimeRequired;
              }
            }
          },

          // Expire offset time.
          {
            width: '30%',
            type: 'select',
            align: 'center',
            name: 'expire_offset_time',
            title: data.i18n.expireOffsetTimeTitle + ' ' + tip(data.i18n.expireOffsetTimeTitleTip),

            visible: true,
            editing: true,
            inserting: true,

            valueField: 'key',
            valueType: 'string',
            textField: 'title',
            allowOther: true,
            otherPlaceholderText: data.i18n.expireOffsetTimeOtherPlaceholder,

            items: expireOffsetTimeItems,

            validate: {
              validator: function (value, item) {
                return typeof value === 'string' && value && value !== '0';
              },
              message: function (value, item) {
                return '• ' + data.i18n.expireOffsetTimeRequired;
              }
            }
          },

          // Display order.
          {
            type: 'number',
            align: 'center',
            name: 'display_order',
            title: data.i18n.displayOrderTitle,

            visible: false,
          },

          // And the field controls now.
          // This defaults to a `10%` width also.
          $.extend({}, jsGridData.controlDefaultOptions)
        ]
      }));
      // Setup hover tips via jQuery TipTip in WooCommerce.

      $meta.find('[data-toggle~="jquery-tip"]').tipTip({
        attribute: 'data-tip',
        fadeIn: 50,
        fadeOut: 50,
        delay: 200
      });
    });
  });
})(jQuery);
