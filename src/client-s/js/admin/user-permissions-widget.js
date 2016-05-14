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
    var userPermissionStatuses = $.parseJSON($widget.find('.-user-permission-statuses').val());

    // Restriction items.

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

    // Status items.

    var statusItems = [{
      status: null,
      title: ''
    }]; // Initialize array.
    $.each(statusItems, function (status, title) {
      restrictionItems.push({
        status: status,
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
        // Assigned IDs.
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
        },

        // Reference IDs.
        {
          type: 'number',
          align: 'center',
          name: 'order_id',
          title: data.i18n.orderIdTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'subscription_id',
          title: data.i18n.subscriptionIdTitle,

          visible: false,
        }, {
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
            var display = '';

            var isAllowed = true,
              isInactive = false,
              isScheduled = false,
              isExpired = false;

            var currentTime = parseInt(moment.utc().format('X'));

            if (item.is_trashed || item.status !== 'active') {
              isAllowed = false;
              isInactive = true;
            }
            if (item.access_time) {
              if (item.access_time > currentTime) {
                isAllowed = false;
                isScheduled = true;
              }
            }
            if (item.expire_time) {
              if (item.expire_time <= currentTime) {
                isAllowed = false;
                isExpired = true;
              }
            }
            if (value && typeof restrictionTitlesById[value] === 'string') {
              if (isAllowed) {
                display += '<span class="dashicons dashicons-unlock" style="color:#49a642;"' +
                  ' title="' + _.escape(data.i18n.restrictionIdStatusIsAllowed) + '" data-toggle="jquery-ui-tooltip"></span>';
              } else if (isInactive) {
                display += '<span class="si si-octi-lock" style="color:#666;"' +
                  ' title="' + _.escape(data.i18n.restrictionIdStatusIsInactive + ': ' + (typeof userPermissionStatuses[item.status] === 'string' ? userPermissionStatuses[item.status] : item.status)) + '" data-toggle="jquery-ui-tooltip"></span>';
              } else if (isScheduled) {
                display += '<span class="si si-calendar-check-o" style="color:#666;"' +
                  ' title="' + _.escape(data.i18n.restrictionIdStatusIsScheduled) + '" data-toggle="jquery-ui-tooltip"></span>';
              } else if (isExpired) {
                display += '<span class="si si-calendar-times-o" style="color:#666;"' +
                  ' title="' + _.escape(data.i18n.restrictionIdStatusIsExpired) + '" data-toggle="jquery-ui-tooltip"></span>';
              }
              display += ' <strong>' + _.escape(restrictionTitlesById[value]) + '</strong>';

              if (item.order_id) { // Applied via order ID in WooCommerce?
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.orderIdTitle) +
                  ' <a href="' + _.escape(data['orderViewUrl='] + encodeURIComponent(item.order_id)) + '">#' + _.escape(item.order_id) + '</a>' + '</small></em>';
              } else if (item.subscription_id) { // Applied via subscription ID in WooCommerce?
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.subscriptionIdTitle) +
                  ' <a href="' + _.escape(data['subscriptionViewUrl='] + encodeURIComponent(item.subscription_id)) + '">#' + _.escape(item.subscription_id) + '</a>' + '</small></em>';
              }
            }
            return display;
          }
        },

        // Access time.
        {
          width: '22.5%',
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
        },

        // Expire time.
        {
          width: '22.5%',
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
          inserting: true,

          itemTemplate: function (value, item) {
            if (item.subscription_id && !parseInt(timestamp)) {
              return '<em>' + _.escape(data.i18n.emptyExpireDateTimeSubscription) + '</em>';
            } else {
              return this._timestampFormat(value, this.subType, true);
            }
          }
        },

        // Status.
        {
          width: '15%',
          type: 'select',
          align: 'center',
          name: 'status',
          title: data.i18n.statusTitle,

          visible: true,
          editing: true,
          inserting: true,

          valueField: 'status',
          valueType: 'string',
          textField: 'title',

          items: statusItems,

          validate: {
            validator: function (value, item) {
              return typeof value === 'string' && value && value !== '0';
            },
            message: function (value, item) {
              return '• ' + data.i18n.restrictionStatusRequired;
            }
          }
        }, {
          type: 'number',
          align: 'center',
          name: 'is_trashed',
          title: data.i18n.isTrashedTitle,

          visible: false,
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
        // This defaults to a `10%` width also.
        $.extend({}, jsGridData.controlDefaultOptions, {})
      ]
    }));
    // Tooltips.
    $widget.tooltip({
      position: {
        my: 'right center',
        at: 'left-10 center',
        using: function (position, feedback) {
          $(this).css(position).addClass(feedback.vertical + ' ' + feedback.horizontal);
        }
      },
      tooltipClass: prefix + '-tooltip',
      items: '[data-toggle~="jquery-ui-tooltip"]'
    });
    // Form submission handler.
    // This pulls together all of the data.
    $widget.closest('form').on('submit', function (e) {
      var permissions = []; // Initialize permissions.

      // This catches a row that is still pending insertion.
      if ($grid.find('.jsgrid-grid-header > table > tbody > tr.jsgrid-insert-row').filter(':visible').find('> td:first-child select').val()) {
        alert(data.i18n.notReadyToSave + '\n• ' + data.i18n.stillInserting);
        /*jshint -W030 */ // Ignore this rule and allow chaining here.
        e.preventDefault(), e.stopImmediatePropagation();
        return false; // Do not allow at this time.
      }
      // This catches a row that is still open with unsaved changes.
      if ($grid.find('.jsgrid-grid-body > table > tbody > tr.jsgrid-edit-row').length) {
        alert(data.i18n.notReadyToSave + '\n• ' + data.i18n.stillEditing);
        /*jshint -W030 */ // Ignore this rule and allow chaining here.
        e.preventDefault(), e.stopImmediatePropagation();
        return false; // Do not allow at this time.
      }
      // Ready to go! Let's collect all permission items; i.e., each row in the table.
      $grid.find('.jsgrid-grid-body > table > tbody > tr:not(.jsgrid-nodata-row)').each(function (index) {
        permissions.push($.extend($(this).data('JSGridItem'), {
          display_order: index // Set display order.
        }));
      });
      $widget.find('.-user-permissions').val(JSON.stringify(permissions)); // For server-side handling.
    });
  });
})(jQuery);