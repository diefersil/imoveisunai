
if (jQuery('body').hasClass('wp-customizer')) {
  wp.customize.bind('ready', function () {
    wp.customize.panel.each(function (panel) {
      panel.expanded.bind(function (isExpanding) {
        if (isExpanding && panel.id === 'cncb_settings') {
          jQuery('iframe').contents().find('#cookiebanner-root').show();
          jQuery('#customize-controls').addClass("cncb-open");
        } else {
          jQuery('iframe').contents().find('#cookiebanner-root').hide();
          jQuery('#customize-controls').removeClass("cncb-open");
        }
      });
    });
  });
}

jQuery(document).ready(function ($) {
  "use strict";

  $('.cncb_parent_field').on('click', function () {
    $(this).parents('fieldset').find('.cncb_sub_field_wrapper').toggle('show');
  });

  $('.cncb-notice .cncb-notice-dismiss').on('click', function (e) {
    e.preventDefault();

    $.ajax({
      type: 'POST',
      url: 'admin-ajax.php',
      data: {
        'action': 'cncb_disable_notice',
      },
      success: function (data) {
        $('.cncb-notice').hide();
      }
    });
  });

  $('.cncb-notice .button').on('click', function (e) {
    e.preventDefault();

    var url = $(this).attr("href");

    $.ajax({
      type: 'POST',
      url: 'admin-ajax.php',
      data: {
        'action': 'cncb_disable_notice',
      },
      success: function (data) {
        $('.cncb-notice').hide();
        window.location.href = url;
      }
    });
  });

  $('.cncb-nav-tab-js').on('click', function (e) {
    e.preventDefault();
    var tab = $(this).attr('href');
    $('.cncb-nav-tab-js').removeClass('nav-tab-active');
    $('.content-tab').hide();
    $(this).addClass('nav-tab-active');
    $(tab).show();
  });

  $('.request-feature').on('submit', function (e) {
    e.preventDefault();

    var that_obj = $(this);
    var feature_text = that_obj.find('#feature').val();

    $.ajax({
      type: 'POST',
      url: 'admin-ajax.php',
      data: {
        'action': 'cncb_feature_feedback',
        'feature': feature_text
      },
      success: function (data) {
        that_obj.find('.button').text('Thanks!');
      }
    });
  });
});
