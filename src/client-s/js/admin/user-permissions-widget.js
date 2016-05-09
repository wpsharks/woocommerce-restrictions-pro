(function ($) {
  $(document).ready(function () {
    // Essential variables.

    var prefix = 'accrhcdehngpugpudhpcwtykbfdykarp';
    var data = window[prefix + 'UserPermissionsWidgetData'];
    var jsGridData = neyjfbxruwddgfeedwacfbggzbxkwfxhJQueryJsGridData;

    var $widget = $('#' + prefix + '-user-permissions-widget');
    var $grid = $widget.find('.-grid[data-toggle~="jquery-jsgrid"]');

    if (!$widget.length || !$grid.length) {
      return; // Not possible at this time.
    }
    var userPermissions = $.parseJSON($widget.find('.-user-permissions').val());
    var restrictionTitlesById = $.parseJSON($widget.find('.-restriction-titles-by-id').val());

    var restrictionItems = [{
      ID: null,
      title: ''
    }]; // Initialize array.
    $.each(restrictionTitlesById, function (ID, title) {
      restrictionItems.push({
        ID: ID,
        title: title
      });
    });
    // jsGrid configuration.

    $grid.jsGrid($.extend({}, jsGridData.defaultOptions, {
      data: userPermissions,
      noDataContent: data.i18n.noDataContent,

      sorting: false, // Not compatible w/ sortable.
      paging: false, // Not compatible w/ sortable.

      onRefreshed: function () {
        $grid.find('.jsgrid-grid-body > table > tbody').sortable({
          placeholder: 'ui-state-highlight'
        });
      }, // This allows drag n' drop.

      fields: [
        // Misc IDs.
        {
          type: 'number',
          align: 'center',
          name: 'ID',
          title: data.i18n.idTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'user_id',
          title: data.i18n.userIdTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'order_id',
          title: data.i18n.orderIdTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'product_id',
          title: data.i18n.productIdTitle,

          visible: false,
        },

        // Restriction IDs.
        {
          width: '30%',
          type: 'select',
          align: 'center',
          name: 'restriction_id',
          title: data.i18n.restrictionIdTitle,

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
            var display = ''; // Initialize.
            var isAllowed = true,
              isDisabled = false,
              isScheduled = false,
              isExpired = false;
            var currentTime = parseInt(moment.utc().format('X'));

            if (!item.is_enabled) {
              isDisabled = true;
              isAllowed = false;
            }
            if (item.access_time) {
              if (item.access_time > currentTime) {
                isScheduled = true;
                isAllowed = false;
              }
            }
            if (item.expire_time) {
              if (item.expire_time <= currentTime) {
                isExpired = true;
                isAllowed = false;
              }
            }
            if (value && typeof restrictionTitlesById[value] === 'string') {
              if (isAllowed) {
                display = '<span title="' + _.escape(data.i18n.restrictionIdStatusIsAllowed) + '">';
                display += '<span class="dashicons dashicons-unlock" style="color:#49a642;"></span>';
              } else if (isDisabled) {
                display = '<span title="' + _.escape(data.i18n.restrictionIdStatusIsDisabled) + '">';
                display += '<span class="dashicons dashicons-hidden" style="color:#F54D3D;"></span>';
              } else if (isScheduled) {
                display = '<span title="' + _.escape(data.i18n.restrictionIdStatusIsScheduled) + '">';
                display += '<span class="dashicons dashicons-clock" style="color:#666666;"></span>';
              } else if (isExpired) {
                display = '<span title="' + _.escape(data.i18n.restrictionIdStatusIsExpired) + '">';
                display += '<span class="dashicons dashicons-backup" style="color:#F54D3D;"></span>';
              }
              display += ' <strong>' + _.escape(restrictionTitlesById[value]) + '</strong></span>';

              if (item.order_id) // Applied via order ID in WooCommerce?
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.orderIdTitle) +
                ' <a href="' + _.escape(data['orderViewUrl='] + encodeURIComponent(item.order_id)) + '">#' + _.escape(item.order_id) + '</a>' + '</small></em>';
            }
            return display; // Should not be empty given the validator, but just in case.
          }
        }, {
          type: 'number',
          align: 'center',
          name: 'original_restriction_id',
          title: data.i18n.original + ' ' + data.i18n.restrictionIdTitle,

          visible: false,
        },

        // Access times.
        {
          width: '25%',
          type: 'dateTime',
          align: 'center',
          name: 'access_time',
          title: data.i18n.accessTimeTitle,

          datePlaceholderText: data.i18n.accessDatePlaceholder,
          timePlaceholderText: data.i18n.accessTimePlaceholder,
          emptyDateTimeItemText: data.i18n.emptyAccessDateTime,

          noTimeEquals: 'startOfDay',

          visible: true,
          editing: true,
          inserting: true,

          validate: {
            validator: function (value, item) {
              return !value || !item.expire_time || value < item.expire_time;
            },
            message: function (value, item) {
              return '• ' + data.i18n.accessTimeLtExpireTime;
            }
          },
        }, {
          type: 'number',
          align: 'center',
          name: 'original_access_time',
          title: data.i18n.original + ' ' + data.i18n.accessTimeTitle,

          visible: false,
        },

        // Expire times.
        {
          width: '25%',
          type: 'dateTime',
          align: 'center',
          name: 'expire_time',
          title: data.i18n.expireTimeTitle,

          datePlaceholderText: data.i18n.expireDatePlaceholder,
          timePlaceholderText: data.i18n.expireTimePlaceholder,
          emptyDateTimeItemText: data.i18n.emptyExpireDateTime,

          noTimeEquals: 'endOfDay',

          visible: true,
          editing: true,
          inserting: true
        }, {
          type: 'number',
          align: 'center',
          name: 'original_expire_time',
          title: data.i18n.original + ' ' + data.i18n.expireTimeTitle,

          visible: false,
        },

        // Enabled?
        {
          width: '10%',
          type: 'checkbox',
          align: 'center',
          name: 'is_enabled',
          title: data.i18n.isEnabledTitle,

          visible: true,
          editing: true,
          inserting: true,

          _createCheckbox: function () {
            return $('<input type="checkbox" checked />');
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

        // Misc times.
        {
          type: 'number',
          align: 'center',
          name: 'insertion_time',
          title: data.i18n.insertionTimeTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'last_update_time',
          title: data.i18n.lastUpdateTimeTitle,

          visible: false,
        },

        // And the field controls now.
        $.extend({}, jsGridData.controlDefaultOptions, {})
      ]
    }));

    // Form submission handler.
    // This pulls together all of the data.
    $widget.closest('form').on('submit', function (e) {
      var data = []; // Array of all items.

      $grid.find('.jsgrid-grid-body > table > tbody > tr').each(function (index) {
        data.push($.extend($(this).data('JSGridItem'), {
          display_order: index // Set display order.
        }));
      });
      $widget.find('.-user-permissions').val(JSON.stringify(data));
    });
  });
})(jQuery);
