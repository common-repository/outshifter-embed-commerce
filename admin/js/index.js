(function($) {
	'use strict';
  $( window ).load(function() {

    $('#outshifter-btn-save-settings').on("click", function(e) {
      e.preventDefault();
      $.ajax({
        url: outshifter_blocks_admin_vars.ajaxurl,
        type: 'post',
        data: {
          action: 'outshifter_blocks_save_settings',
          currency: $('#outshifter-select-currency').val(),
          fontSelected: $('#outshifter-select-font').val(),
          supplierLogo: $('#image_attachment_id').val(),
          supplierLogoWhite: $('#image_attachment_id_white').val(),
          layoutSelected: $("#radio-group input[name='layoutSelected']:checked").val(),
          modalPosition: $("#radio-group input[name='modalPosition']:checked").val(),
          blocksTitleAlignment: $(".radio-group input[name='blocksTitleAlignment']:checked").val(),
          shopColor: $('.my-color-field').val(),
          buttonPrevType: $('#outshifter-select-button-prev-type').val(),
          buttonNextType: $('#outshifter-select-button-next-type').val(),
          buttonNextColor: $('.button-color-field').val(),
          buttonPrevColor: $('.button-prev-color-field').val(),
          buttonNextHoverColor: $('.button-hover-color-field').val(),
          buttonNextTextColor: $('.button-text-color-field').val(),
          buttonPrevTextColor: $('.button-prev-text-color-field').val(),
          buttonNextHoverTextColor: $('.button-text-hover-color-field').val(),
          buttonPrevBorderColor: $('.button-prev-border-color-field').val(),
          buttonBorderRatio: $('#select-border-ratio').val(),
          blockTitleSize: $('#select-title-size').val(),
          mixpanel: $('#outshifter-mixpanel').val(),
          stripeKey: $('#outshifter-stripe-key').val(),
          stripeId: $('#outshifter-stripe-id').val(),
          gAnalytics: $('#outshifter-g-analytics').val(),
          createShortcode: $('#outshifter-create-shortcode').val(),
          notGutemberg: $("#not-gutemberg:checked").val(),
          allowUploadToMedia: $("#allow-media:checked").val(),
          shopLogoSelected: $(".custom-shop-icon input[name='shopLogoSelected']:checked").val(),
          supplierLogoShop: $('#image_attachment_id_shop').val(),
          shopTextSelected: $(".input-text-shop").val(),
          shopButtonColor: $("#button-shop-color").val(),
          textIconColor: $("#text-icon-color").val(),
          shopButtonRatio: $("#shop-button-ratio").val(),
          showShopIcon: $("#show-shop-icon:checked").val(),
          addShopUrl: $("#shop-custom-url:checked").val(),
          shopCustomUrl: $("#input-url-shop").val(),
          showCardTitle: $("#show-card-title:checked").val(),
          showCardPrice: $("#show-card-price:checked").val(),
          showCardSupplier: $("#show-card-supplier:checked").val(),
          showCardButton: $("#show-card-button:checked").val(),
        },
      }).always(function() {
        window.location.reload();
      });
    });
    $('#outshifter-blocks-form').on("submit", function(e) {
      e.preventDefault();
      $.ajax({
        url: outshifter_blocks_admin_vars.ajaxurl,
        type: 'post',
        data: {
          action: 'outshifter_blocks_connect',
          token: $('#outshifter-blocks-form-token').val(),
        },
      }).always(function() {
        window.location.reload();
      });
    });
    $('#outshifter-btn-disconnect').on('click', function(e) {
      e.preventDefault();
      $.ajax({
        url: outshifter_blocks_admin_vars.ajaxurl,
        type: 'post',
        data: {
          action: 'outshifter_blocks_disconnect',
        },
      }).always(function() {
        window.location.reload();
      });
    });
    $('#btn-save-reset-media-upload').on('click', function(e) {
      e.preventDefault();
      $.ajax({
        url: outshifter_blocks_admin_vars.ajaxurl,
        type: 'post',
        data: {
          action: 'save_reset_media_uploads',
          allowUploadToMedia: $(".switch-allow-media input[name='allow-media']:checked").val(),
        },
      }).always(function() {
        window.location.reload();
      });
    });
    $('.save-shortcode-btn').off("click").on("click", function(e) {
      e.preventDefault();
      $(this).prop('disabled', true);
      var $dataRef = $(this).data('ref');
      var $shortcodeText = '.shortcode-text-' + $dataRef;
      var $shortcodeName = '.shortcode-name-' + $dataRef;
      var $creatingShortcode = '.creating-shortcode-' + $dataRef;
      $($creatingShortcode).css('display', 'flex');
      $.ajax({
        url: outshifter_blocks_admin_vars.ajaxurl,
        type: 'post',
        data: {
          action: 'store_shortcode',
          shortcodeName: $($shortcodeName).val(),
          shortcode: $($shortcodeText).text(),
          shortcodeType: $dataRef,
          selectedProducts: $('#obj-selected-products').text()
        },
        success: function(data, textStatus, XMLHttpRequest) {
          $(".creating-shortcode").hide();
          $("#saved-shortcodes-container").empty();
          $('.text-shortcode-saved').show();
          setTimeout(function() { 
            $('.text-shortcode-saved').hide();
          }, 1000);
          $(".save-shortcode-btn").each(function() {
            $(this).prop('disabled', false);
          });
          var containers = document.getElementsByClassName("shortcode-box-container");
          for (var i = 0, len = containers.length; i < len; i++) {
            containers[i].style.display = "flex";
          }
          var shortcodes = document.getElementsByClassName("saved-shortcode-container");
          for (var i = 0, len = shortcodes.length; i < len; i++) {
            shortcodes[i].innerHTML = data[0].content;
          }
          for (var entry in data) {
            var postID = data[entry].id;
            var title = data[entry].title;
            var excerpt = data[entry].excerpt;
            var content = data[entry].content;
            $("#saved-shortcodes-container").append(
              "<div class='shortcode-list-item'>" +
                "<span>" + title + "</span>" +
                "<span>" + excerpt + "</span>" +
                "<span>" + content + "</span>" +
                "<button data-id='" + postID + "' class='shortcode-delete-btn'>" +
                  "<svg width='10' height='13' viewBox='0 0 10 13' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M7.16111 1.05739L7.30103 1.19885H7.5H9.28571C9.41038 1.19885 9.52337 1.30548 9.52337 1.44444C9.52337 1.58341 9.41038 1.69003 9.28571 1.69003H0.714286C0.589617 1.69003 0.476632 1.58341 0.476632 1.44444C0.476632 1.30548 0.589617 1.19885 0.714286 1.19885H2.5H2.69897L2.83889 1.05739L3.34603 0.544607C3.38666 0.503528 3.45099 0.476632 3.50714 0.476632H6.49286C6.54901 0.476632 6.61334 0.503528 6.65397 0.544607L7.16111 1.05739ZM2.14286 12.5234C1.62533 12.5234 1.19092 12.0917 1.19092 11.5556V4.33333C1.19092 3.79715 1.62533 3.36552 2.14286 3.36552H7.85714C8.37467 3.36552 8.80908 3.79715 8.80908 4.33333V11.5556C8.80908 12.0917 8.37467 12.5234 7.85714 12.5234H2.14286Z' stroke='#E81D1D' stroke-width='0.953265'></path></svg>" +
                "</button>" +
              "</div>"
            );
          }
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {},
        complete: function(XMLHttpRequest, textStatus) {},
        dataType: 'json'
      });
    });
    $(document).on('click', '.shortcode-delete-btn', function(e) {
      e.preventDefault();
      var $dataId = $(this).data('id');
      $.ajax({
        url: outshifter_blocks_admin_vars.ajaxurl,
        type: 'post',
        data: {
          action: 'delete_shortcode',
          shortcodeId: $dataId,
        },
        success: function(data, textStatus, XMLHttpRequest) {
          $("#saved-shortcodes-container").empty();
          for (var entry in data) {
            var postID = data[entry].id;
            var title = data[entry].title;
            var excerpt = data[entry].excerpt;
            var content = data[entry].content;
            $("#saved-shortcodes-container").append(
              "<div class='shortcode-list-item'>" +
                "<span>" + title + "</span>" +
                "<span>" + excerpt + "</span>" +
                "<span>" + content + "</span>" +
                "<button data-id='" + postID + "' class='shortcode-delete-btn'>" +
                  "<svg width='10' height='13' viewBox='0 0 10 13' fill='none' xmlns='http://www.w3.org/2000/svg'><path d='M7.16111 1.05739L7.30103 1.19885H7.5H9.28571C9.41038 1.19885 9.52337 1.30548 9.52337 1.44444C9.52337 1.58341 9.41038 1.69003 9.28571 1.69003H0.714286C0.589617 1.69003 0.476632 1.58341 0.476632 1.44444C0.476632 1.30548 0.589617 1.19885 0.714286 1.19885H2.5H2.69897L2.83889 1.05739L3.34603 0.544607C3.38666 0.503528 3.45099 0.476632 3.50714 0.476632H6.49286C6.54901 0.476632 6.61334 0.503528 6.65397 0.544607L7.16111 1.05739ZM2.14286 12.5234C1.62533 12.5234 1.19092 12.0917 1.19092 11.5556V4.33333C1.19092 3.79715 1.62533 3.36552 2.14286 3.36552H7.85714C8.37467 3.36552 8.80908 3.79715 8.80908 4.33333V11.5556C8.80908 12.0917 8.37467 12.5234 7.85714 12.5234H2.14286Z' stroke='#E81D1D' stroke-width='0.953265'></path></svg>" +
                "</button>" +
              "</div>"
            );
          }
        },
        error: function(MLHttpRequest, textStatus, errorThrown) {},
        complete: function(XMLHttpRequest, textStatus) {},
        dataType: 'json'
      });
    });
	});

})( jQuery );