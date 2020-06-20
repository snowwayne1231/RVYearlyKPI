var $yearFeedbackPage = $('#Year-Feedback').generalController(function() {
    var ts = this;
    var leaderArray = [];
    var newleaderArray = [];
    var targetStaffId = [];


    ts.onLogin(function(member) {
        var today = new Date(member.now);
        var current = $.ym.get();
        var todayYear = undoYear|| current.year;
        var yearlyProcessing = 0;
        ts.q('.rv-title span').text(undoYear|| current.year);
        API.getYearlyConfig({ year: todayYear }).then(function(e) {
            var result = API.format(e);
            if (result.is) {
                yearlyProcessing = result.get().processing;
            }
            if (yearlyProcessing > 0 && yearlyProcessing < 3) { gogoFeedback(); }
        });




        function gogoFeedback() {
            API.getYearlyFeedback({ year: todayYear }).then(function(e) {
                var cnt = API.format(e);
                if (cnt.is) {
                    var result = cnt.res();
                    var count = 1;
                    if (result.feedback.length == 0) {
                        ts.q('#NoData').show();
                    } else {
                        ts.q('#NoData').hide();
                    }
                    API.getAllLeader({ year: this.currentYear }).then(function(e) {
                        var cnt = API.format(e);
                        if (cnt.is) {
                            var list = cnt.res();
                            for (var i in list) {
                                var loc = list[i].name + ' ' + list[i].name_en;
                                leaderArray.push({ id: list[i].id, name: loc });
                            }
                            for (t in result.feedback) {
                                targetStaffId.push(result.feedback[t].target_staff_id);
                            }
                            //console.log(targetStaffId); // 受評主管的所有id array
                            var filterLeaders = leaderArray.filter(function(el) {
                                return targetStaffId.indexOf(el.id) < 0
                            })
                            newleaderArray = filterLeaders;
                            // console.log(filterLeaders)
                            for (var tfb in result.feedback) {
                                var getData = JSON.parse(localStorage.getItem("FeedbackNormal_" + member.id + result.feedback[tfb].id));
                                if (getData) {
                                    result.feedback[tfb].multiple_normal_json = getData.json;
                                   
                                } else {
                                    result.feedback[tfb].multiple_normal_json = [];
                                    for (var QN in result.question.normal) {
                                        var arrQN = {
                                            id: result.question.normal[QN].id,
                                            content: '',
                                            noAns:0 // 預設「沒有」不勾選
                                        }
                                        result.feedback[tfb].multiple_normal_json.push(arrQN);
                                    }

                                }
                                callVueRenderManager(result, result.feedback[tfb]);
                            }
                        } else {
                            console.log("Message:" + result.get());
                        }
                    });
                }

                function callVueRenderManager(allData, appraisee) {
                    var num = count;
                    var rand = 'row' + (count++);
                    ts.q('#YearFeedbackForm').append('<div id="' + rand + '" ></div>');
                    var vm = new Vue({
                        template: '#template-feedback',
                        el: '#' + rand,
                        data: {
                            rand: 0,
                            num: num,
                            processing: yearlyProcessing,
                            appraisee: appraisee,
                            currentYear: todayYear,
                            questionChoice: allData.choice,
                            questionNormal: allData.question.normal,
                            commitNeedData: {},
                            updateNormalData: {},
                            isActive: [1],
                            isActiveToQA: [1],
                            maxlength: 255, //字數限制
                            leaderArray: leaderArray,
                            newleaderArray: newleaderArray,
                            questionAboutCompany: allData.question.company,
                            questionOthers: allData.question.others,
                            noAnsAboutComp:false,//勾選沒有(ans about company)
                            ansAboutCompany: '', 
                            ansOthers: {},
                            updateAboutCompanyData: {},
                            updateOthersData: {},
                            isActiveOther: [],
                            isOpenChoice: true
                        },

                        methods: {
                            classObject:function(index){
                                var vs = this;
                                var theAns =  vs.ansOthers[index];
                                return{
                                    active: vs.isActiveOther[index],
                                    'has-content': theAns &&  theAns != null &&  theAns!=''
                                }
                            },
                            changeCheckCompany:function(){
                                var vm = this;
                                if(vm.noAnsAboutComp == false){
                                    vm.ansAboutCompany ='';
                                }else{
                                    vm.ansAboutCompany ='';
                                }
                            },
                            changeToWrite:function(normal_ID){
                                // console.log(normal_ID);
                                if(normal_ID.noAns == false ){
                                    normal_ID.content = "";
                                }else{
                                    normal_ID.content = "";
                                }
                            },
                            saveYearlyFeedback: function(appraiseeObj) {
                                var vm = this;
                                var multipleChoiceData = {};
                                //console.log(appraiseeObj.multiple_normal_json)
                                // 如果勾選沒有，則content為空值
                                for( a in appraiseeObj.multiple_normal_json){
                                    if(appraiseeObj.multiple_normal_json[a].noAns == true){
                                        appraiseeObj.multiple_normal_json[a].content = ''; 
                                    }
                                }

                                return new Promise(function(resolve, reject) {
                                    for (var am in appraiseeObj.multiple_choice_json) {
                                        var key = appraiseeObj.multiple_choice_json[am].id;
                                        multipleChoiceData[key] = appraiseeObj.multiple_choice_json[am].ans;
                                    }


                                    var data = {
                                        feedback_id: appraiseeObj.id,
                                        multiple_choice: multipleChoiceData
                                    };
                                    vm.commitNeedData = data;
                                    //console.log(appraiseeObj)
                                    vm.saveLocalFeedback(appraiseeObj);

                                    API.saveYearlyFeedback(data).then(function(e) {
                                        var result = API.format(e);
                                        if (result.is) {
                                            resolve('ok');
                                            vm.saveOtherFeedback(1);
                                            // swal("儲存成功", '已為您儲存問卷', 'success');
                                            // Materialize.toast('已為您儲存問卷成功', 2000);
                                        } else {
                                            swal("儲存失敗", result.get(), 'success');
                                        }
                                    });
                                });
                            },
                            saveLocalFeedback: function(obj) {
                                this.updateNormalData = obj.multiple_normal_json;
                                var saveData = { id: obj.id, json: obj.multiple_normal_json };;
                                localStorage.setItem("FeedbackNormal_" + member.id + obj.id, JSON.stringify(saveData));
                            },
                            saveOtherFeedback: function(show) {
                                var vs  = this;
                              
                                for (var QAC in vs.questionAboutCompany) {
                                   
                                    // 如果對公司的建議勾選沒有
                                    if(vs.noAnsAboutComp){
                                      
                                        vs.updateAboutCompanyData[vs.questionAboutCompany[QAC].id] = '';
                                    
                                    }else{
                                        vs.updateAboutCompanyData[vs.questionAboutCompany[QAC].id] = vs.ansAboutCompany;
                                    }
                                    // console.log(this.updateAboutCompanyData[this.questionAboutCompany[QAC].id])
                                }
                                for (var la in this.newleaderArray) {
                                    for (var QO in this.questionOthers) {
                                        var otherFeedback = {};
                                        otherFeedback[this.questionOthers[QO].id] = this.ansOthers[la];
                                    }
                                    this.updateOthersData[this.newleaderArray[la].id] = otherFeedback;
                                }
                                // swal("儲存成功", '成功儲存您的填寫內容。', 'success');
                             
                                if(show){
                                    Materialize.toast('成功儲存您的填寫內容。', 2000);
                                }
                                
                                localStorage.setItem("companyFeedback_" + member.id + this.appraisee.id, JSON.stringify(this.ansAboutCompany));
                                localStorage.setItem("otherFeedback_" + member.id + this.appraisee.id, JSON.stringify(this.ansOthers));
                                // 儲存對公司沒有意見的勾選紀錄
                                if(this.noAnsAboutComp == true ){
                                    localStorage.setItem("companyNoFeedback_" + member.id + this.appraisee.id, true);
                                }
                                // console.log(this.ansOthers)
                            },
                            commitManager: function(appraiseeObj) {
                                var obj = {};
                                var vm = this;
                                this.saveYearlyFeedback(appraiseeObj).then(function(e) {
                                    for (var und in vm.updateNormalData) {
                                        obj[vm.updateNormalData[und].id] = vm.updateNormalData[und].content;
                                    }
                                    vm.commitNeedData["normal_questions"] = obj;
                                    //console.log(vm.commitNeedData);console.log(vm.commitNeedData);
                                  
                                    API.commitYearlyFeedback(vm.commitNeedData).then(function(e) {
                                        var result = API.format(e);
                                        if (result.is) {
                                            // swal("送審成功", appraiseeObj.target_staff_name + ' ( ' + appraiseeObj.target_staff_name_en + ' ) 的問卷內容已送審成功。', 'success');

                                            vm.commitOther();
                                            $(vm.$el).remove();
                                            // setTimeout(function(){
                                            //     Materialize.toast('送審成功 !'+ appraiseeObj.target_staff_name + ' ( ' + appraiseeObj.target_staff_name_en + ' ) 的問卷內容已送審成功。', 1500);
                                            // },2000)
                                        } else {
                                            vm.generalFail(result.get());
                             
                                        }
                                    });
                                });
                            },
                            saveOtherComm:function(idx) {
                                var vm = this;
                                // console.log('idx',idx)
                                vm.saveOtherFeedback(1);
                                setTimeout(function(){  
                                    $(vm.$el).q('.modal').modal('close');
                                    //  vm.$nextTick(() => {
                                    //     vm.isActiveOther[idx] = 0; 
                                    // });
                                    vm.isActiveOther[idx] = 0; 
                                    vm.classObject(idx);
                                }, 1500);
                               
                            },
                            commitOther: function() {
                                this.saveOtherFeedback(0);
                                var vm = this;
                                var data = {
                                    year: this.currentYear,
                                    others_questions: this.updateOthersData,
                                    company_questions: this.updateAboutCompanyData
                                }
                                console.log('commit other ',data);
                                API.commitYearlySuggestion(data).then(function(e) {
                                    var result = API.format(e);
                                    if (result.is) {
                                        // swal("提交成功", '提交成功您的問卷內容。', 'success');
                                       
                                        setTimeout(function(){
                                            Materialize.toast('提交成功您的問卷內容。', 1500);
                                        },800)
                                    } else {
                                        vm.generalFail(result.get());
                                    }
                                });
                            },
                            initActive: function(type) {
                                /* type = 1 單選題； type = 2 問答題 */
                                if (type == 1) {
                                    this.isActive = [];
                                    for (var qc in this.questionChoice) {
                                        this.isActive.push(0);
                                    }
                                } else {
                                    this.isActiveToQA = [];
                                    for (var qn in vm.questionNormal) {
                                        this.isActiveToQA.push(0);
                                    }
                                }
                            },
                            currentTarget: function(index, type) {
                                /* type = 1 單選題； type = 2 問答題 */
                                if (type == 1) {
                                    this.initActive(type);
                                    this.isActive[index] = 1;
                                } else {
                                    this.initActive(type);
                                    this.isActiveToQA[index] = 1;
                                }
                            },
                            previous: function(index, type) {
                                var previousPage = index - 1;
                                if (type == 1) {
                                    this.initActive(type);
                                    this.isActive[previousPage] = 1;
                                } else {
                                    this.initActive(type);
                                    this.isActiveToQA[previousPage] = 1;
                                }
                            },
                            next: function(index, type) {
                                var nextPage = index + 1;
                                if (type == 1) {
                                    this.initActive(type);
                                    this.isActive[nextPage] = 1;
                                } else {
                                    this.initActive(type);
                                    this.isActiveToQA[nextPage] = 1;
                                }
                            },
                            currentManager: function(index, array) {
                                this.isActiveOther = [];
                                // console.log(index,array)
                                for (var a in array) {
                                    this.isActiveOther.push(0);
                                }
                                this.isActiveOther[index] = 1;
                                
                                setTimeout(function(){
                                   ts.q('#modal-leadercomment-'+array[index].id).modal('open');
                                },200)
                            },
                            generalFail: function(e) {
                                console.log(e);
                                if(e=='Multiple Choice Is Not Finished.') e = '單選題未填寫完成，請先填寫完成。'
                                swal("Fail", (e ? e : ''));
                            }
                        },
                        created: function() {
                            var vm = this;
                            /* GET::其他單位主管/公司回饋問卷 */
                            var localCF = localStorage.getItem("companyFeedback_" + member.id + this.appraisee.id);
                            var localOF = localStorage.getItem("otherFeedback_" + member.id + this.appraisee.id);
                            var localNCF = localStorage.getItem("companyNoFeedback_" + member.id + this.appraisee.id);

                            if (localCF || localOF || localNCF) {
                                this.ansAboutCompany = JSON.parse(localCF);
                                this.ansOthers = JSON.parse(localOF);
                                this.noAnsAboutComp = JSON.parse(localNCF);
                            }
                        },
                        mounted: function() {
                            var ele = this.$el;
                            ts.q(ele).q('.collapsible').collapsible();
                            ts.q(ele).q('.modal').modal();
                        }
                    });
                }
            });
        }

    });
});