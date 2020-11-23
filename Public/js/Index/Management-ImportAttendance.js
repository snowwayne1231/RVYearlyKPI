Dropzone.autoDiscover = false;
var $Attendance = $('#Attendance').generalController(function () {
    var ts = this;
    var form = ts.q('form');
    var current = $.ym.get();
    var SelectYM = ts.q('#SelectYM');
    var YearSelect = ts.q('#getYear');
    var MonthSelect = ts.q('#getMonth');

    function initYM() {
        YearSelect.yearSet();
        YearSelect.change(function () {
            current.year = this.value;
            $.ym.save();
        });
        for (i = 1; i <= 12; i++) {
            MonthSelect.append('<option value="' + i + '">' + i + '月</option>');
        }
        MonthSelect.val(current.month).attr('selected');
        MonthSelect.change(function () { current.month = this.value; $.ym.save(); });
    }
    initYM();

    // Prevent Dropzone from auto discovering this element:
    // console.log(ts.q(".myDropzone"));
    var myDropzoneClasses = ts.q(".myDropzone"),
        myDropzoneObj = {};
    for (var mdi = 0; mdi < myDropzoneClasses.length; mdi++) {
        let curr = myDropzoneClasses[mdi];
        // console.log('myDropzone' + mdi);
        myDropzoneObj['myDropzone' + mdi] = new Dropzone(curr);
        myDropzoneObj['myDropzone' + mdi].options.uploadMultiple = true;
        myDropzoneObj['myDropzone' + mdi].options.autoProcessQueue = false;
        myDropzoneObj['myDropzone' + mdi].options.acceptedFiles = ".xls,.xlsx";
        // console.log('myDropzone' + mdi, myDropzoneObj['myDropzone' + mdi]);
        myDropzoneObj['myDropzone' + mdi].on('addedfile', function (f) {
            var mdThis = this;
            if (!f.name.match(/\.xlsx?/i)) {
                mdThis.removeFile(f);
                return swal("Fail", "不接受的檔案格式");
            }
            let data = new FormData(),
                apiName = 'addAbsence';
                
            data.append('file', f);
            if (mdThis.element.id == 'myDropzone1') {
                apiName = 'uploadForgetCardMonthly';
            }
            console.log(data);
            API[apiName](data).then(function (e) {
                var cnt = API.format(e);
                if (cnt.is) {
                    var error_record = cnt.get()['error_record'];
                    var str = (error_record && error_record.length > 0) ? '\r\n\r\n錯誤訊息：' + error_record : '';
                    swal('Success', '上傳成功 : ' + f.name + str);
                } else {
                    swal('Fail', '上傳失敗 : ' + f.name + '\r\n' + cnt.get());
                }
                clearInterval(f.setInterval);
                // if (apiName == 'addAbsence') {
                    mdThis.removeFile(f);
                // }
            });

            //假進度條  直接抓 children position 如果套件修改HTML結構會報錯
            f.fakeProcess = 0;
            f.base = 512000 / f.upload.total;
            f.setInterval = setInterval(function () {
                if (f.fakeProcess >= 100) { clearInterval(f.setInterval); mdThis.removeFile(f); return clearInterval(f.setInterval); }
                f.fakeProcess += Math.random() * f.base + f.base;
                f.previewElement.children[2].firstChild.style.width = f.fakeProcess + "%";
            }, 150);
        });
    }
});
