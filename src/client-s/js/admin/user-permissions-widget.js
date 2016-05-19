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

    // jsGrid configuration.

    $grid.jsGrid($.extend({}, jsGridData.defaultOptions, {
      data: userPermissions, // Permissions array.
      noDataContent: data.i18n.noDataContent, // On empty.

      insertingByDefault: userPermissions.length === 0,
      sorting: false, // Not compatible w/ sortable.
      paging: false, // Not compatible w/ sortable.

      rowClick: function (args) {
        if (this._editingRow) {
          this.updateItem(); // Save current item.
        }
        this.editItem($(args.event.target).closest('tr'));
      },

      onRefreshed: function (args) {
        if (initialGridRefreshComplete) {
          return; // Done already.
        }
        initialGridRefreshComplete = true; // Doing it now.

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

            if (!value || value === '0') {
              return ''; // Empty.
            }
            var currentTime = parseInt(moment.utc().format('X'));

            if (item.is_trashed || item.status !== 'active') {
              isAllowed = false;
              isInactive = true; // Flag true.
            } // Access not allowed due to status.

            if (item.access_time && item.access_time > currentTime) {
              isAllowed = false;
              isScheduled = true; // Flag as true.
            } // Access is coming soon; i.e., scheduled.

            if (item.expire_time && item.expire_time <= currentTime) {
              isAllowed = false;
              isExpired = true; // Flag as true.
            } // Access has expired; i.e., no longer available.

            if (isAllowed) {
              display += '<span class="dashicons dashicons-unlock" style="color:#49a642;"' +
                ' title="' + _.escape(data.i18n.restrictionIdStatusIsAllowed) + '" data-toggle="jquery-ui-tooltip"></span>';
            } else if (isInactive) {
              display += '<span class="si si-octi-lock" style="color:#666;"' +
                ' title="' + _.escape(data.i18n.restrictionIdStatusIsInactive + ': ' + data.userPermissionStatuses[item.status]) + '" data-toggle="jquery-ui-tooltip"></span>';
            } else if (isScheduled) {
              display += '<span class="si si-calendar-check-o" style="color:#666;"' +
                ' title="' + _.escape(data.i18n.restrictionIdStatusIsScheduled) + '" data-toggle="jquery-ui-tooltip"></span>';
            } else if (isExpired) {
              display += '<span class="si si-calendar-times-o" style="color:#666;"' +
                ' title="' + _.escape(data.i18n.restrictionIdStatusIsExpired) + '" data-toggle="jquery-ui-tooltip"></span>';
            }
            display += ' <strong>' + _.escape(data.restrictionTitlesById[value]) + '</strong>';

            if (item.order_id) {
              if (data.current_user.can_edit_shop_orders) {
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.orderIdTitle) +
                  ' <a href="' + _.escape(data['orderViewUrl='] + encodeURIComponent(item.order_id)) + '">#' + _.escape(item.order_id) + '</a>' + '</small></em>';
              } else {
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.orderIdTitle) + ' #' + _.escape(item.order_id) + '</small></em>';
              }
            } else if (item.subscription_id) {
              if (data.current_user.can_edit_shop_subscriptions) {
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.subscriptionIdTitle) +
                  ' <a href="' + _.escape(data['subscriptionViewUrl='] + encodeURIComponent(item.subscription_id)) + '">#' + _.escape(item.subscription_id) + '</a>' + '</small></em>';
              } else {
                display += ' <em><small>| ' + _.escape(data.i18n.via + ' ' + data.i18n.subscriptionIdTitle) + ' #' + _.escape(item.subscription_id) + '</small></em>';
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
          title: data.i18n.expireTimeTitle,

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
                return '<em>' + _.escape(data.productPermissionExpireOffsetDirectives[item.expire_directive]) + '</em>';
              } else { // In case of a custom directive.
                return '<em>' + _.escape(item.expire_directive) + '</em>';
              }
            } else { // ↑ If no specific End date, and it's controlled by an Order/Subscription.
              return this._timestampFormat(value, this.subType, true); // Default behavior.
            }
          }
        }, {
          type: 'text',
          align: 'center',
          name: 'expire_directive',
          title: data.i18n.expireDirectiveTitle,

          visible: false,
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
        $.extend({}, jsGridData.controlDefaultOptions)
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

    var validateSaveOnSubmit = function (e) {
      var permissions = []; // Initialize permissions.

      // This catches a row that is still pending insertion.
      var insertModeOn = $grid.find('.jsgrid-insert-mode-button.jsgrid-mode-on-button').length !== 0;
      var $insertRow = $grid.find('.jsgrid-grid-header > table > tbody > tr.jsgrid-insert-row');
      var insertRowRestrictionId = $insertRow.find('> td:first-child select').val();

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
