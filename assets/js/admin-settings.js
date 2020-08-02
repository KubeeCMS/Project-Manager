window.LecoCPSettings = null;

(function ($) {
    LecoCPSettings = function () {
        var self = this;

        this.init = function () {
            this.template = $('#template').val();

            $(document).on('cmb_init', function () {
                this.toggleCustomCSS(self.template);
            }).on('change', '#template', function () {
                self.toggleCustomCSS($(this).val());
            });

            this.dismissNotice();
        };

        this.toggleCustomCSS = function (template) {
            template = (template === 'default') ? '' : '-' + template

            $('.custom-css').hide();
            $('.cmb2-id-css' + template).show();
        };

        this.dismissNotice = function () {
            $('.leco-cp-notice.is-dismissible').on('click', '.notice-dismiss', function () {
                var notice_el = $(this).closest('.leco-cp-notice');

                var notice = notice_el.attr('id');
                var notice_nonce = notice_el.attr('data-nonce');
                $.post(
                    ajaxurl,
                    {
                        action: 'leco_cp_dismiss_notice',
                        nonce: notice_nonce,
                        notice: notice
                    }
                )
            });
        };

        this.init();
    }

    $(document).ready(LecoCPSettings);
})(jQuery);
