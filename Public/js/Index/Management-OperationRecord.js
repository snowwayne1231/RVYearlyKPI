var $managementOperationRecord = $('#Management-OperationRecord').generalController(function() {
    var ts = this;

    ts.onLogin(function(member) {
        var vm = new Vue({
            el: ts.q('.operation-record')[0],
            data: {
                count: 10,
                type: 0,
                record: {
                  staff:[],
                  system:[]
                }
            },
            created: function() {
                this.operationRecord();
            },
            methods: {
                operationRecord: function() {
                    var vm = this;
                    API.getAdminOperatingRecord({ count: this.count, type: this.type }).then(function(e) {
                        var result = API.format(e);
                        if (result.is) {
                            vm.record = result.res();
                            console.log(vm.record)
                        }
                    });
                },
                showDetail : function(tr){
                  if(!tr.type){tr.type=7;}
                  API.getAdminOperatingRecordDetail(tr).then(function(e){
                    var f = API.format(e);
                    if(f.is){
                      var result = f.get();
                      console.log(result);
                      var str = JSON.stringify(result.changed_json);
                      swal('詳細',str,'');
                    }
                  });
                }
            },
            watch: {
                count: function(val) {
                    this.operationRecord();
                },
                type: function(val) {
                    this.operationRecord();
                },
            }
        });
    });
});