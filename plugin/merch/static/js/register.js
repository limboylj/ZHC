define(['core', 'tpl', 'biz/plugin/diyform'], function (core, tpl, diyform) {
    var modal = {params: {applytitle: '', open_protocol: 0,thumbsize:0}};
    modal.init = function (params) {
        modal.params = $.extend(modal.params, params || {});
        
        $('.img-uploader,.img-uploader2,.img-uploader3').uploader({
            uploadUrl: core.getUrl('util/uploader'),
            removeUrl: core.getUrl('util/uploader/remove'),
            imageCss: 'image-md',
            width:modal.params.thumbsize
        })

        $('.btn-submit').click(function () {
            var btn = $(this);
            if (btn.attr('stop')) {
                return
            }
            var html = btn.html();
            var diyformdata = false;
            if ($('#merchname').isEmpty()) {
                FoxUI.toast.show('请填写商户名称!');
                return
            }
            if ($('#salecate').isEmpty()) {
                FoxUI.toast.show('请填写主营项目!');
                return
            }
            if ($('#realname').isEmpty()) {
                FoxUI.toast.show('请填写联系人!');
                return
            }
            // if (!$('#mobile').isMobile()) {
            //     FoxUI.toast.show('请填写联系人手机!');
            //     return
            // }
            if ($('#uname').isEmpty()) {
                FoxUI.toast.show('请填写帐号!');
                return
            }
            if ($('#upass').isEmpty()) {
                FoxUI.toast.show('请填写密码!');
                return
            }
            var data = {
                'realname': $('#realname').val(),
                'mobile': $('#mobile').val(),
                'merchname': $('#merchname').val(),
                'uname': $('#uname').val(),
                'upass': $('#upass').val(),
                'salecate': $('#salecate').val(),
                'desc': $('#desc').val(),
                'mdata': {},
                'address': $('#address').val(),
                'tel': $('#tel').val()
            }

            if ($(".diyform-container").length > 0) {
                diyformdata = diyform.getData('.page-merch-register .diyform-container');
                if (!diyformdata) {
                    return
                }
                data.mdata =  diyformdata;
            }

            if (modal.params.open_protocol == 1) {
                if (!$('#agree').prop('checked')) {
                    FoxUI.toast.show('请阅读并了解【'+ modal.params.applytitle +'】!');
                    return
                }
            }

            if($('.img-uploader').prev().children().length == 0){
                FoxUI.toast.show('请上传商铺照片!');
                return
            } else {
                var len  = $('.img-uploader').prev().children().length
                data['shopimg'] = [];
                for(var i=0; i < len; i++){
                    data['shopimg'][i] = $($('.img-uploader').prev().children()[i]).data('filename');
                }
            }

            if($('.img-uploader2').prev().children().length == 0){
                FoxUI.toast.show('请上传营业执照!');
                return
            } else {
                data['certificate'] = $($('.img-uploader2').prev().children()[0]).data('filename')
            }

            if($('.img-uploader3').prev().children().length > 0){
                var len  = $('.img-uploader3').prev().children().length
                data['othercertificate'] = [];
                for(var i=0; i < len; i++){
                    data['othercertificate'][i] = $($('.img-uploader3').prev().children()[i]).data('filename');
                }
            }

            btn.attr('stop', 1).html('正在处理...');
            core.json('merch/register', data, function (pjson) {
                if (pjson.status == 0) {
                    btn.removeAttr('stop').html(html);
                    FoxUI.toast.show(pjson.result.message);
                    return
                }

                FoxUI.message.show({
                    icon: 'icon icon-info text-warning',
                    content: "您的申请已经提交，请等待我们联系您!",
                    buttons: [{
                        text: '先去商城逛逛', extraClass: 'btn-danger', onclick: function () {
                            location.href = core.getUrl('')
                        }
                    }]
                });

            }, true, true)
        });
        
        $("#btn-apply").unbind('click').click(function () {
            var html = $(".pop-apply-hidden").html();
            container = new FoxUIModal({
                content: html, extraClass: "popup-modal", maskClick: function () {
                    container.close();
                }
            });
            container.show();
            $('.verify-pop').find('.close').unbind('click').click(function () {
                container.close()
            });
            $('.verify-pop').find('.btn').unbind('click').click(function () {
                container.close();
            })
        });
    };
    return modal
});