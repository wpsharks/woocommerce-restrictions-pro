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
    var initialGridRefreshComplete = false; // Initialize.
    var userPermissions = $.parseJSON($widget.find('.-user-permissions').val());

    // Restriction items.

    var restrictionItems = [{
      ID: 0,
      title: ''
    }]; // Initialize array.
    $.each(data.restrictionTitlesById, function (ID, title) {
      restrictionItems.push({
        ID: ID,
        title: title
      });
    });
    // Status items.

    var statusItems = []; // Initialize array.
    $.each(data.userPermissionStatuses, function (status, title) {
      statusItems.push({
        status: status,
        title: title
      });
    });
    // Easy tip builder; needed below.

    var tip = function (tip) {
      return '<i class="si si-question-circle" data-toggle="jquery-ui-tooltip" title="' + _.escape(tip) + '"></i>';
    };
    // jsGrid configuration.

    $grid.jsGrid($.extend({}, jsGridData.defaultOptions, {
      data: userPermissions, // Permissions array.
      noDataContent: data.i18n.noDataContent, // On empty.

      insertingByDefault: userPermissions.length === 0,
      sorting: false, // Not compatible w/ sortable.
      paging: false, // Not compatible w/ sortable.

      onRefreshed: function (args) {
        if (initialGridRefreshComplete) {
          return; // Done already.
        }
        initialGridRefreshComplete = true; // Doing it now.

        $grid.find('.jsgrid-grid-body > table > tbody').sortable({
          placeholder: 'ui-state-highlight'
        });
      }, // This allows drag n' drop.

      onItemInserting: function (args) {
        var currentTime = parseInt(moment.utc().format('X'));

        if (args.item.status !== 'expired' && args.item.expire_time && args.item.expire_time <= currentTime) {
          args.item.status = 'expired'; // Force a matching status.
        } else if (args.item.status === 'expired' && args.item.expire_time && args.item.expire_time > currentTime) {
          args.item.status = 'enabled'; // Force a matching status.
        }
      },
      onItemUpdating: function (args) {
        this.onItemInserting(args); // Same exact validation as the above.
      },

      rowClass: function (item) {
        if (item.is_trashed || item.status !== 'enabled') {
          return '-is-not-allowed'; // Row classes.
        } // Access not allowed due to status.

        if (item.status === 'expired' || (item.expire_time && item.expire_time <= parseInt(moment.utc().format('X')))) {
          return '-is-not-allowed'; // Row classes.
        } // Access has expired; i.e., no longer available.

        if (item.access_time && item.access_time > parseInt(moment.utc().format('X'))) {
          return '-is-not-allowed'; // Row classes.
        } // Access is coming soon; i.e., scheduled for future access.

        return '-is-allowed'; // Row classes.
      },

      rowClick: function (args) {
        var $target = $(args.event.target);

        if ($target.is('a')) { // Not on anchor clicks.
          return; // No edit if user clicked a link in the row.
        }
        if (this._editingRow) {
          this.updateItem(); // Save current item.
        }
        this.editItem($target.closest('tr'));
      },

      fields: [
        // Assigned IDs.
        {
          type: 'number',
          align: 'center',
          name: 'ID',
          css: '-property-ID',
          title: data.i18n.idTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'user_id',
          css: '-property-user-id',
          title: data.i18n.userIdTitle,

          visible: false,
        },

        // Reference IDs.
        {
          type: 'number',
          align: 'center',
          name: 'order_id',
          css: '-property-order-id',
          title: data.i18n.orderIdTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'subscription_id',
          css: '-property-subscription-id',
          title: data.i18n.subscriptionIdTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'product_id',
          css: '-property-product-id',
          title: data.i18n.productIdTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'item_id',
          css: '-property-item-id',
          title: data.i18n.itemIdTitle,

          visible: false,
        },

        // Restriction ID.
        {
          width: '30%',
          type: 'select',
          align: 'center',
          name: 'restriction_id',
          css: '-property-restriction-id',
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
            var display = '';

            if (!value || value === '0') {
              return ''; // Empty.
            }
            display = '<span class="-title">' + _.escape(data.restrictionTitlesById[value]) + '</span>';

            if (item.order_id) {
              if (data.current_user.can_edit_shop_orders) {
                display += ' <span class="-via">' + _.escape(data.i18n.via + ' ' + data.i18n.orderIdTitle) +
                  ' <a href="' + _.escape(data['orderViewUrl='] + encodeURIComponent(item.order_id)) + '">#' + _.escape(item.order_id) + '</a>' + '</span>';
              } else {
                display += ' <span class="-via">' + _.escape(data.i18n.via + ' ' + data.i18n.orderIdTitle) + ' #' + _.escape(item.order_id) + '</span>';
              }
            } else if (item.subscription_id) {
              if (data.current_user.can_edit_shop_subscriptions) {
                display += ' <span class="-via">' + _.escape(data.i18n.via + ' ' + data.i18n.subscriptionIdTitle) +
                  ' <a href="' + _.escape(data['subscriptionViewUrl='] + encodeURIComponent(item.subscription_id)) + '">#' + _.escape(item.subscription_id) + '</a>' + '</span>';
              } else {
                display += ' <span class="-via">' + _.escape(data.i18n.via + ' ' + data.i18n.subscriptionIdTitle) + ' #' + _.escape(item.subscription_id) + '</span>';
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
          css: '-property-access-time',
          title: data.i18n.accessTimeTitle + ' ' + tip(data.i18n.accessTimeTitleTip),

          datePlaceholderText: data.i18n.accessDatePlaceholder,
          timePlaceholderText: data.i18n.accessTimePlaceholder,
          emptyDateTimeItemText: data.i18n.emptyAccessDateTime,

          noTimeEquals: 'startOfDay',

          visible: true,
          editing: true,
          inserting: true,

          validate: {
            validator: function (value, item) {
              return typeof value === 'number' && (!value || !item.expire_time || value < item.expire_time);
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
          css: '-property-expire-time',
          title: data.i18n.expireTimeTitle + ' ' + tip(data.i18n.expireTimeTitleTip),

          datePlaceholderText: data.i18n.expireDatePlaceholder,
          timePlaceholderText: data.i18n.expireTimePlaceholder,
          emptyDateTimeItemText: data.i18n.emptyExpireDateTime,

          noTimeEquals: 'endOfDay',

          visible: true,
          editing: true,
          inserting: true,

          itemTemplate: function (value, item) {
            if ((item.order_id || item.subscription_id) && item.expire_directive && !parseInt(value)) {
              if (typeof data.productPermissionExpireOffsetDirectives[item.expire_directive] === 'string') {
                return _.escape(data.productPermissionExpireOffsetDirectives[item.expire_directive]);
              } else { // In case of a custom directive.
                return _.escape(item.expire_directive);
              }
            } else { // Otherwise let `_timestampFormat()` work out a proper display.
              return this._timestampFormat(value, this.subType, true); // Default behavior.
            }
          }
        }, {
          type: 'text',
          align: 'center',
          name: 'expire_directive',
          css: '-property-expire-directive',
          title: data.i18n.expireDirectiveTitle,

          visible: false,
        },

        // Status.
        {
          width: '15%',
          type: 'select',
          align: 'center',
          name: 'status',
          css: '-property-status',
          title: data.i18n.statusTitle + ' ' + tip(data.i18n.statusTitleTip),

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
          },
          itemTemplate: function (value, item) {
            var display = '';

            var isAllowed = true,
              isDisabled = false,
              isScheduled = false,
              isExpired = false;

            if (!value || value === '0') {
              return ''; // Empty.
            }
            /*jshint -W030 */ // Allow chaining here.

            if (item.is_trashed || item.status !== 'enabled') {
              isAllowed = false, isDisabled = true;
            }
            if (item.status === 'expired' || (item.expire_time && item.expire_time <= parseInt(moment.utc().format('X')))) {
              isAllowed = false, isDisabled = true, isExpired = true;
            }
            if (item.access_time && item.access_time > parseInt(moment.utc().format('X'))) {
              isAllowed = false, isScheduled = true;
            }
            if (!isAllowed) { // If not allowed, provide a tooltip to help explain why.
              if (isDisabled) { // Disabled statuses take precedence in this display.
                display += '<span class="si si-' + (isExpired ? 'calendar-times-o' : 'eye-slash') + '" title="' + _.escape(data.i18n.statusIsDisabled + ': ' + data.userPermissionStatuses[item.status]) + '" data-toggle="jquery-ui-tooltip"></span>';
              } else if (isScheduled) {
                display += '<span class="si si-calendar-check-o" title="' + _.escape(data.i18n.statusIsScheduled) + '" data-toggle="jquery-ui-tooltip"></span>';
              }
            }
            display += ' <span class="-title">' + _.escape(data.userPermissionStatuses[item.status]) + '</span>';

            return display;
          }
        }, {
          type: 'number',
          align: 'center',
          name: 'is_trashed',
          css: '-property-is-trashed',
          title: data.i18n.isTrashedTitle,

          visible: false,
        },

        // Display order.
        {
          type: 'number',
          align: 'center',
          name: 'display_order',
          css: '-property-display-order',
          title: data.i18n.displayOrderTitle,

          visible: false,
        },

        // Misc times.
        {
          type: 'number',
          align: 'center',
          name: 'insertion_time',
          css: '-property-insertion-time',
          title: data.i18n.insertionTimeTitle,

          visible: false,
        }, {
          type: 'number',
          align: 'center',
          name: 'last_update_time',
          css: '-property-last-update-time',
          title: data.i18n.lastUpdateTimeTitle,

          visible: false,
        },

        // And the field controls now.
        // This defaults to a `10%` width also.
        $.extend({}, jsGridData.controlDefaultOptions)
      ]
    }));
    // Tooltips for column headings; i.e., `th` elements.

    $grid.find('.jsgrid-grid-header').tooltip({
      position: {
        my: 'center bottom',
        at: 'center top-10',
        using: function (position, feedback) {
          $(this).css(position).addClass(feedback.vertical + ' ' + feedback.horizontal);
        }
      },
      content: function () {
        return $(this).prop('title');
      },
      tooltipClass: prefix + '-tooltip',
      items: 'th [data-toggle~="jquery-ui-tooltip"]'
    });
    // Tooltips for access column; i.e., first `td` child.

    $grid.find('.jsgrid-grid-body').tooltip({
      position: {
        my: 'right center',
        at: 'left-10 center',
        using: function (position, feedback) {
          $(this).css(position).addClass(feedback.vertical + ' ' + feedback.horizontal);
        }
      },
      content: function () {
        return $(this).prop('title');
      },
      tooltipClass: prefix + '-tooltip',
      items: 'td [data-toggle~="jquery-ui-tooltip"]'
    });
    // Form submission handler.
    // This pulls together all of the data.

    var validateSaveOnSubmit = function (e) {
      var permissions = []; // Initialize permissions.

      // This catches a row that is still pending insertion.
      var insertModeOn = $grid.find('.jsgrid-insert-mode-button.jsgrid-mode-on-button').length !== 0;
      var $insertRow = $grid.find('.jsgrid-grid-header > table > tbody > tr.jsgrid-insert-row');
      var insertRowRestrictionId = $insertRow.find('> td.-property-restriction-id select').val();

      if (insertModeOn && insertRowRestrictionId && insertRowRestrictionId !== '0') {
        alert(data.i18n.notReadyToSave + '\n• ' + data.i18n.stillInserting);
        /*jshint -W030 */ // Ignore this rule and allow chaining here.
        e.preventDefault(), e.stopImmediatePropagation();
        return false; // Do not allow at this time.
      }
      // This catches a row that is still open with unsaved changes.
      if ($grid.find('.jsgrid-grid-body > table > tbody > tr.jsgrid-edit-row').length) {
        $grid.data('JSGrid').updateItem(); // Save changes automatically.

        // alert(data.i18n.notReadyToSave + '\n• ' + data.i18n.stillEditing);
        /*jshint -W030 */ // Ignore this rule and allow chaining here.
        // e.preventDefault(), e.stopImmediatePropagation();
        // return false; // Do not allow at this time.
      }
      // Ready to go! Let's collect all permission items; i.e., each row in the table.
      $grid.find('.jsgrid-grid-body > table > tbody > tr:not(.jsgrid-nodata-row)').each(function (index) {
        permissions.push($.extend($(this).data('JSGridItem'), {
          display_order: index // Set display order.
        }));
      });
      $widget.find('.-user-permissions').val(JSON.stringify(permissions));
      // console.log('Updated permissions to: %o', permissions); // For console debugging.
    };
    $widget.closest('form').on('submit', validateSaveOnSubmit);
  });
})(jQuery);
