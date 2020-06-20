Dropzone.autoDiscover = false;
var $ManagementImport2017JanToMar = $('#Management-Import2017JanToMar').generalController(function() {
    var ts = this;

    ts.onLogin(function() {
        startInit();
    });

    function startInit() {
        ts.$.off('click').on('click', '[data-toggle="downlaod-staff"]', API.download_2017_123_Monthly);
        dzoon();
    }

    function dzoon() {
        var dz = ts.dz = new Dropzone(ts.q(".dropzone")[0]);
        dz.options.uploadMultiple = true;
        dz.options.createImageThumbnails = false;
        dz.options.autoProcessQueue = false;
        dz.options.acceptedFiles = ".xls,.xlsx";
        dz.on('addedfile', function(f) {
            if (!f.name.match(/\.xlsx?/i)) {
                this.removeFile(f);
                return swal("Error", "不接受的檔案格式!");
            }
            var data = new FormData();
            data.append('file', f);
            API.upload_2017_123_Monthly(data).then(function(e) {
                var cnt = API.format(e);
                if (cnt.is) {
                    swal("上傳成功", f.name);
                } else {
                    swal("上傳失敗", f.name + '\r\n' + cnt.get());
                }
                clearInterval(f.setInterval);
                dz.removeFile(f);
            });

            //假進度條  直接抓 children position 如果套件修改HTML結構會報錯
            f.fakeProcess = 0;
            f.base = 512000 / f.upload.total;
            f.setInterval = setInterval(function() {
                f.fakeProcess += (f.fakeProcess >= 100) ? 0 : Math.random() * f.base + f.base;
                f.previewElement.children[2].firstChild.style.width = f.fakeProcess + "%";
            }, 100);
        });
    }
});