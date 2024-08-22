window.addEventListener('load', function() {
    let regex = /\/[1-9]{1}[0-9]*\//
    let regexSecondPass = /[1-9]{1}[0-9]*/
    let url = window.location.href;
    let payroll_id;
    if(url.includes('create')){
        payroll_id = 0;
    }else {
        payroll_id = url.match(regex)[0].match(regexSecondPass)[0];
    }

    Vue.component('v-select',VueSelect.VueSelect);
    Vue.component('edit-payroll', {
        data: function(){
            return{
                errors: [],

                id:payroll_id,
                start_date:"",
                end_date:"",
                nurse:"",
                nurse_options:undefined,
                payroll_items:undefined,
                total_amount: 0,
                search_timeout: null
            }},
        mounted: function(){
            modRequest.request('payroll.payroll.load', null, {"payrollId": this.payroll_id},
                function(response) {
                    if(response.data.success) {
                        this.start_date = response.data.start_date;
                        this.end_date = response.data.end_date;

                        this.nurse = response.data.nurse;
                        this.nurse_options = response.data.nurseOptions;
                        // this.payroll_items = response.data.payrollItems;

                        this.total_amount = response.data.totalAmount;
                    }
                }.bind(this),
                function(error) {
                }
            );
        },
        template: `    
    <div class="col-xs-12">
        <div class="alert alert-danger errors" role="alert" style="display:none">
        </div>
        <div class="tabbable">
            <ul class="nav nav-tabs padding-16">
                <li class="active">
                    <a data-toggle="tab" href="#edit-basic">
                        <i class="blue fa fa-edit bigger-125"></i>
                        Basic Info
                    </a>
                </li>
                <li v-if='id>0' class="">
                    <a data-toggle="tab" href="#edit-license">
                        <i class="orange2 fa fa-clock bigger-125"></i>
                        Payroll Items
                    </a>
                </li>
            </ul>
            <div class="tab-content profile-edit-tab-content">
                <div id="edit-basic" class="tab-pane active">
                    <div class="form-group">
                        <h4 class="header blue bolder smaller">Basic Info</h4>
                    </div>
                    <div class="row">
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div class="form-group">
                                <label for="title">Start Date</label>
                                <input  class="form-control" name="name" type="date" placeholder="Start Date" value="<?=$start_date?>"/>
                            </div>
                            <div class="form-group">
                                <label for="title">End Date</label>
                                <input  class="form-control" name="name" type="date" placeholder="End Date" value="<?=$end_date?>"/>
                            </div>
                            <div class="form-group">
                                <label for="title">Nurses</label>
                                <v-select v-model="nurse" :options="nurse_options" searchable label="name" placeholder="Nurse"></v-select>
                            </div>
                        </div>
                        <div class="col-md-6 col-sm-12 col-xs-12">
                            <div v-if="total_amount=0" class="form-group">
                                <label for="title">Total Amount</label>
                                <p class="lead">$ {{ total_amount }}</p>
                            </div>
                        </div>
                        <div class="col-xs-12 text-center">
                            <div class="clearfix form-actions">
                                <button @click="savePayrollItem" class="btn btn-info" type="button" id="submit">
                                    <i class="fa fa-save bigger-110"></i>
                                    Save
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <div v-if="id>0" id="edit-license" class="tab-pane">
                    <div class="form-group">
                        <h4 class="header blue bolder smaller">Payroll Items</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>`,
        methods: {

            savePayroll: function () {
                this.errors = [];


                if(this.errors.length!==0){
                    // console.log(this.errors.length);
                    return;
                }
                let data = {
                    'id': this.payroll_id,
                    'start_date': this.start_date,
                    'end_date': this.end_date
                };



                modRequest.request('payroll.payroll.save', null, data,
                    function(response) {
                        if(response.data.success) {
                            window.location.href = response.data.url;
                        }
                    }.bind(this),
                    function(error) {

                    }
                );
            }
        }
    });
    window.selects_vue = new Vue({
        el: '#vue-context'
    });

});
