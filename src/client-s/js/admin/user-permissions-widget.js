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
      noDataContent: data.i18n.noDataContent,
      data: userPermissions,
      sorting: false,

      fields: [{
        width: '30%',
        type: 'select',
        align: 'center',
        name: 'restriction_id',
        title: data.i18n.restrictionIdTitle,

        visible: true,
        editing: true,
        inserting: true,

        valueField: 'ID',
        textField: 'title',
        items: restrictionItems,
        validate: {
          validator: 'required',
          message: function () {
            return data.i18n.restrictionAccessRequired;
          }
        }
      }, {
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
        inserting: true
      }, {
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
      }, $.extend({}, jsGridData.controlDefaultOptions, {
        width: '10%',
        modeSwitchButton: true
      })]
    }));

    $widget.closest('form').on('submit', function (e) {
      var data = $grid.jsGrid('option', 'data');
      $widget.find('.-user-permissions').val(JSON.stringify(data));
    });
  });
})(jQuery);
