(function ($) {
  $(document).ready(function () {
    // Essential variables.

    var prefix = 'rsxqjzypgdqmrnnkkkrmgshvnnnkzzvu';
    var data = window[prefix + 'ProductPostTypeData'];
    var jsGridData = neyjfbxruwddgfeedwacfbggzbxkwfxhJQueryJsGridData;

    var setupProductMeta = function () {
      $('.' + prefix + '-product-meta').each(function () {
        var $postbox = $('#woocommerce-product-data');
        var $meta = $(this); // Current meta div we are attaching to.
        var $grid = $meta.find('.-permissions-grid[data-toggle~="jquery-jsgrid"]');

        if (!$postbox.length || !$meta.length || !$grid.length) {
          return; // Not possible at this time.
        } else if ($meta.data('setupComplete')) {
          return; // Setup already complete here.
        }
        $meta.data('setupComplete', true); // Doing it now.

        var initialGridRefreshComplete = false; // Initialize.
        var variationKey = $meta.data('variationKey'); // Zero-based index.
        var isVariation = variationKey !== undefined; // A product variation?
        var productPermissions = $.parseJSON($meta.find('.-product-permissions').val());

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

        // Access offset directive items.

        var accessOffsetDirectiveItems = []; // Initialize array.
        $.each(data.productPermissionAccessOffsetDirectives, function (key, title) {
          accessOffsetDirectiveItems.push({
            key: key,
            title: title
          });
        });

        // Expire offset directive items.

        var expireOffsetDirectiveItems = []; // Initialize array.
        $.each(data.productPermissionExpireOffsetDirectives, function (key, title) {
          expireOffsetDirectiveItems.push({
            key: key,
            title: title
          });
        });
        // Easy tip builder; needed below.

        var tip = function (tip) {
          return '<span class="woocommerce-help-tip" data-toggle="jquery-tip" data-tip="' + _.escape(tip) + '"></span>';
        };
        // jsGrid configuration.

        $grid.jsGrid($.extend({}, jsGridData.defaultOptions, {
          data: productPermissions, // Permissions array.
          noDataContent: data.i18n.noDataContent, // On empty.

          insertingByDefault: productPermissions.length === 0,
          sorting: false, // Not compatible w/ sortable.
          paging: false, // Not compatible w/ sortable.

          onRefreshed: function (args) {
            if (initialGridRefreshComplete) {
              return; // Done already.
            }
            initialGridRefreshComplete = true; // Doing it now.

            $grid.find('.jsgrid-grid-body > table > tbody').sortable({
              placeholder: 'ui-state-highlight',
              stop: function (e, ui) {
                  this.wcVariationMightNeedUpdate();
                }.bind(this) // Preserve `this`.
            });
          }, // This allows drag n' drop.

          onItemInserted: function (args) {
            this.wcVariationMightNeedUpdate();
          },

          onItemUpdated: function (args) {
            this.wcVariationMightNeedUpdate();
          },

          onItemDeleted: function (args) {
            this.wcVariationMightNeedUpdate();
          },

          wcVariationMightNeedUpdate: function () {
            if (!isVariation) { // Not a product variation?
              return; // Nothing to do in this case; i.e., not a variation.
            }
            $meta.closest('.woocommerce_variation').addClass('variation-needs-update');
            $postbox.find('#variable_product_options').find('button.save-variation-changes')
              .removeAttr('disabled').trigger('woocommerce_variations_input_changed');
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
              name: 'product_id',
              css: '-property-product-id',
              title: data.i18n.productIdTitle,

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
                return value ? '<strong>' + _.escape(data.restrictionTitlesById[value]) + '</strong>' : '';
              }
            },

            // Access offset directive.
            {
              width: '30%',
              type: 'select',
              align: 'center',
              name: 'access_offset_directive',
              css: '-property-access-offset-directive',
              title: data.i18n.accessOffsetDirectiveTitle + ' ' + tip(data.i18n.accessOffsetDirectiveTitleTip),

              visible: true,
              editing: true,
              inserting: true,

              valueField: 'key',
              valueType: 'string',
              textField: 'title',
              allowOther: true,
              otherPlaceholderText: data.i18n.accessOffsetDirectiveOtherPlaceholder,

              items: accessOffsetDirectiveItems,

              validate: {
                validator: function (value, item) {
                  return typeof value === 'string' && value && value !== '0';
                },
                message: function (value, item) {
                  return '• ' + data.i18n.accessOffsetDirectiveRequired;
                }
              }
            },

            // Expire offset directive.
            {
              width: '30%',
              type: 'select',
              align: 'center',
              name: 'expire_offset_directive',
              css: '-property-expire-offset-directive',
              title: data.i18n.expireOffsetDirectiveTitle + ' ' + tip(data.i18n.expireOffsetDirectiveTitleTip1) + tip(data.i18n.expireOffsetDirectiveTitleTip2),

              visible: true,
              editing: true,
              inserting: true,

              valueField: 'key',
              valueType: 'string',
              textField: 'title',
              allowOther: true,
              otherPlaceholderText: data.i18n.expireOffsetDirectiveOtherPlaceholder,

              items: expireOffsetDirectiveItems,

              validate: {
                validator: function (value, item) {
                  return typeof value === 'string' && value && value !== '0';
                },
                message: function (value, item) {
                  return '• ' + data.i18n.expireOffsetDirectiveRequired;
                }
              }
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

            // And the field controls now.
            // This defaults to a `10%` width also.
            $.extend({}, jsGridData.controlDefaultOptions)
          ]
        }));
        // Setup hover tips via jQuery TipTip.
        // Tooltip system provided by WooCommerce.

        $meta.find('[data-toggle~="jquery-tip"]').tipTip({
          attribute: 'data-tip',
          fadeIn: 50,
          fadeOut: 50,
          delay: 200
        });
        // Form submission handlers.
        // This pulls together all of the data.

        var moveToPermissionsTab = function () {
          if (isVariation && isVariableProductType()) {
            $postbox.find('.product_data_tabs .variations_tab > a').click();
          } else { // It's in the general product data tab.
            $postbox.find('.product_data_tabs .general_tab > a').click();
          }
        };

        var isVariableProductType = function () {
          var type = $postbox.find('#product-type').val();
          return $.inArray(type, ['variable', 'variable-subscription', 'variable_subscription']) !== -1;
        };

        var maybeStopVariationChangesTemporarily = function () {
          if (!isVariation) { // Only for variations.
            return; // Not applicable; i.e., not a variation.
          }
          var $variableProductOptions = $postbox.find('#variable_product_options');
          var $needsUpdate = $variableProductOptions.find('.variation-needs-update');

          $needsUpdate.removeClass('variation-needs-update')
            .addClass('variation-needed-update');

          setTimeout(function () { // After just a very short delay.
            $needsUpdate.removeClass('variation-needed-update').addClass('variation-needs-update');
          }, 1000); // This restores the `.variation-needs-update` class now.
        };

        var validateSaveOnSubmit = function (e) {
          var permissions = []; // Initialize.

          // Still in the DOM?
          if (!$.contains($postbox[0], $meta[0])) {
            return; // Nothing to do here.
          }
          // Check if this data is applicable.
          if (isVariation !== isVariableProductType()) {
            return; // N/A; i.e., product type mismatch.
          }
          // This catches a row that is still pending insertion.
          var insertModeOn = $grid.find('.jsgrid-insert-mode-button.jsgrid-mode-on-button').length !== 0;
          var $insertRow = $grid.find('.jsgrid-grid-header > table > tbody > tr.jsgrid-insert-row');
          var insertRowRestrictionId = $insertRow.find('> td.-property-restriction-id select').val();

          if (insertModeOn && insertRowRestrictionId && insertRowRestrictionId !== '0') {
            moveToPermissionsTab(); // Open the relevant tab so they can fix problem.
            alert(data.i18n.notReadyToSave + '\n• ' + data.i18n.stillInserting);

            /*jshint -W030 */ // Ignore this rule and allow chaining here.
            e.preventDefault(), e.stopImmediatePropagation();
            maybeStopVariationChangesTemporarily();

            return false; // Do not allow at this time.
          }
          // This catches a row that is still open with unsaved changes.
          if ($grid.find('.jsgrid-grid-body > table > tbody > tr.jsgrid-edit-row').length) {
            $grid.data('JSGrid').updateItem(); // Save changes automatically.

            // moveToPermissionsTab(); // Open the relevant tab so they can see problem.
            // alert(data.i18n.notReadyToSave + '\n• ' + data.i18n.stillEditing);

            /*jshint -W030 */ // Ignore this rule and allow chaining here.
            // e.preventDefault(), e.stopImmediatePropagation();
            // maybeStopVariationChangesTemporarily();

            // return false; // Do not allow at this time.
          }
          // Ready to go! Let's collect all permission items; i.e., each row in the table.
          $grid.find('.jsgrid-grid-body > table > tbody > tr:not(.jsgrid-nodata-row)').each(function (index) {
            permissions.push($.extend($(this).data('JSGridItem'), {
              display_order: index // Set display order.
            }));
          });
          $meta.find('.-product-permissions').val(JSON.stringify(permissions));
          // console.log('Updated' + (isVariation ? ' variation key ' + variationKey : '') + ' permissions to: %o', permissions);
        };
        // Note: ↓ These survive variations loading/unloading, which is unfortunate.
        // They survive because they're attached to a parent element, not to the variation.
        // For that reason, `validateSaveOnSubmit()` should check if the variation still exists
        // in the DOM before it does anything. If the variation doesn't, it must bypass.

        if (isVariation) { // This is triggered in WooCommerce before a final submit.
          $postbox.on('woocommerce_variations_save_variations_button', validateSaveOnSubmit);
          $postbox.on('woocommerce_variations_save_variations_on_submit', validateSaveOnSubmit);
        } else {
          $meta.closest('form').on('submit', validateSaveOnSubmit);
        }
      });
    };
    setupProductMeta(); // Initialize any existing product meta; e.g., standard product meta.
    $(document).on('woocommerce_variations_added woocommerce_variations_loaded', setupProductMeta);
  });
})(jQuery);
