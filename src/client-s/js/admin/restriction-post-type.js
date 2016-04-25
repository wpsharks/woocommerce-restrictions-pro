(function ($) {
  $(document).ready(function () {
    var prefix = 'fdbmjuxwzjfjtaucytprkbcqfpftudyg';
    var data = window[prefix + 'RestrictionPostTypeData'];
    var chosenOptions = jazssggqbtujeebgvnskynzyzwqttqqzJQueryChosenDefaults;

    var $slugMetaBox = $('#slugdiv.postbox');
    var $prefixedMetaBoxes = $('.postbox[id^="' + prefix + '"]');

    $('#title-prompt-text').text(data.i18n.titlePlaceholder);
    $slugMetaBox.find('input').attr('placeholder', data.i18n.slugPlaceholder);
    $slugMetaBox.find('.hndle').append('<span class="-label">' + data.i18n.suggestedLabel + '</span>');

    $prefixedMetaBoxes.find('select[data-toggle~="jquery-chosen"]').chosen(chosenOptions);
    $prefixedMetaBoxes.not('#' + prefix + '-about').find('.hndle').append('<span class="-label">' + data.i18n.optionalLabel + '</span>');
  });
})(jQuery);
