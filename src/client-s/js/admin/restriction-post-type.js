(function ($) {
  $(document).ready(function () {
    // Essential variables.

    var prefix = 'fdbmjuxwzjfjtaucytprkbcqfpftudyg';
    var data = window[prefix + 'RestrictionPostTypeData'];
    var chosenData = jazssggqbtujeebgvnskynzyzwqttqqzJQueryChosenData;

    var $slugMetaBox = $('#slugdiv.postbox');
    var $prefixedMetaBoxes = $('.postbox[id^="' + prefix + '"]');
    var $aToggles = $prefixedMetaBoxes.find('a[data-toggle][href="#"]');

    // Title and slug tweaks.

    $('#title-prompt-text').text(data.i18n.titlePlaceholder);
    $slugMetaBox.find('input').attr('placeholder', data.i18n.slugPlaceholder);
    $slugMetaBox.find('.hndle').append('<span class="-label">' + data.i18n.suggestedLabel + '</span>');

    // Prefixed meta boxes; jQuery Chosen, labels, toggles, etc.

    $prefixedMetaBoxes.find('select[data-toggle~="jquery-chosen"]').chosen(chosenData.defaultOptions);
    $prefixedMetaBoxes.not('#' + prefix + '-about').find('.hndle').append('<span class="-label">' + data.i18n.optionalLabel + '</span>');
    $prefixedMetaBoxes.find('a[data-toggle][href="#"]').on('click', function (e) {
      e.preventDefault();
      e.stopImmediatePropagation();
      $($(this).data('toggle')).toggle();
    });
    // Publish button translation enhancement.

    $('input#publish[type="submit"], input#publish[type="button"]').val(data.i18n.publishButtonCreate);
    $('button#publish').text(data.i18n.publishButtonCreate);
  });
})(jQuery);
