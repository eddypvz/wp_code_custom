
const WPCC_modal_tools = function() {

    this.modal = (url) => {
        const $ = jQuery;

        if (!url) {
            $('#supermodal_close_btn').trigger('click');
        }
        else {
            const supermodal = $('.wpcc_supermodal');
            const supermodalIframe = $('<iframe id="wpcc_supermodal_iframe"></iframe>');
            const supermodalIframeLoading = $('#loadingMessage');

            // set url
            supermodalIframe.attr('src', url);

            $('#supermodal_close_btn').unbind('click').click(function () {
                supermodal.hide();
                supermodalIframe.remove();
                supermodalIframeLoading.show();
                $('body').css({'overflow': 'auto'});
            });

            supermodalIframe.load(function () {
                supermodalIframeLoading.css('display', 'none');
            });

            $('.wpcc_supermodal_content').append(supermodalIframe);

            $('body').css({'overflow': 'hidden'});
            supermodal.show();
        }
    }

    this.loading = (open) => {
        if (open !== false) open = true;

        const $ = jQuery;
        const loading = $("#wpcc_loading");

        if (!open) {
            loading.hide();
        }
        else {
            loading.show();
        }
    }
};