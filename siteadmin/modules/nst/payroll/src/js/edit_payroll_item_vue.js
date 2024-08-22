window.addEventListener('load', function() {
    let regex = /\/[1-9]{1}[0-9]*\//
    let regexSecondPass = /[1-9]{1}[0-9]*/
    let url = window.location.href;
    let payroll_item_id;
    if(url.includes('create')){
        payroll_item_id = 0;
    }else {
        payroll_item_id = url.match(regex)[0].match(regexSecondPass)[0];
    }

    let payroll_id = 0;
    if(url.includes('payroll/')){
        payroll_id = url.match(regex)[0].match(regexSecondPass)[0];
    }

    let bonus_options = [{'value':true,'label':'Yes'},{'value':false,'label':'No'}];
    Vue.component('v-select',VueSelect.VueSelect);
    Vue.component('edit-payroll-item', {
        data: function(){
            return{
                errors: [],

                payroll_options: undefined,
                payroll: undefined,

                scheduled_shift: undefined,
                scheduled_shift_options: undefined,

                bonus_options: bonus_options,
                approved_options: bonus_options,
                type: "",
                description: "",
                amount: "",
                bonus: "",
                approved: "",
                payroll_item_id: payroll_item_id,
                payroll_id: payroll_id,
                is_active: "",
                search_timeout: null
            }},
        mounted: function(){
            modRequest.request('payroll.payroll_item.load', null, {"payrollItemId": this.payroll_item_id, "payrollId": this.payroll_id},
                function(response) {
                    if(response.data.success) {
                        this.payroll_options = response.data.payrollOptions;
                        this.payroll = response.data.payroll;
                        this.scheduled_shift = response.data.scheduledShift;
                        this.scheduled_shift_options = response.data.scheduledShiftOption;

                        this.description = response.data.description;
                        this.status = response.data.status;
                        this.type = response.data.type;
                        this.bonus = response.data.bonus;
                        if(this.bonus === true) this.bonus = {'value': 1, 'label':'Yes'}
                        if(this.bonus === false) this.bonus = {'value': 0, 'label':'No'}

                        this.approved = response.data.approved;
                        if(this.approved === true) this.approved = {'value': 1, 'label':'Yes'}
                        if(this.approved === false) this.approved = {'value': 0, 'label':'No'}
                    }
                }.bind(this),
                function(error) {
                }
            );
        },
        template: `
            <div>
                <div class="alert alert-danger errors" role="alert" v-if="errors.length">
                    <b>Please correct the following error(s):</b>
                    <ul>
                        <li v-for="error in errors">{{ error }}</li>
                    </ul>
                </div>
            
                <div class="form-group">
                    <label for="exampleInputEmail1">Payroll Item Description</label>
                    <input required v-model="description" class="form-control" name="description" type="text" placeholder="Description" />
                </div>
                 
                <div class="form-group">
                    <label for="">Mark as Approved</label>
                    <input type="checkbox" v-model="approved" id="is_approved" v-bind:checked="approved"/>
                </div>
                
                <div class="form-group">
                    <label for="">Mark as Bonus</label>
                    <input type="checkbox" v-model="bonus" id="is_bonus" v-bind:checked="bonus"/>
                </div>
                
                <div class="form-group">
                    <label for="">Amount</label>
                    <input type="number" v-model="amount" id="is_bonus" v-bind:checked="bonus"/>
                </div>
                
                <div id="vue-component-context" class="form-group">
                    <label for="">Payroll</label>
                    <v-select v-model="payroll" :options="payroll_options" searchable label="name" placeholder="Payroll"></v-select>
                </div>
                
                <div class="form-group">
                    Come back and implement Shift information
                </div>
                <div class="clearfix form-actions" style="margin:0 -12px;">
                    <input type="submit" class="btn btn-primary" value="Save" @click="savePayrollItem();">
                </div>
            </div>`,
        methods: {
            savePayrollItem: function () {

                this.errors = [];

                if (!this.type) {
                    this.errors.push('Type required.');
                }
                if (!this.description) {
                    this.errors.push('Payroll description required.');
                }

                if (typeof(this.material) !== 'object' || !this.is_active) {
                    this.errors.push('Must select a payroll');
                }

                if (typeof(this.is_active) !== 'object' || !this.is_active) {
                    this.errors.push('Must select active or inactive option');
                }

                if (typeof(this.bonus) !== 'object' || !this.bonus) {
                    this.errors.push('Must specify if this should be marked as a bonus');
                }


                if(this.errors.length!==0){
                    // console.log(this.errors.length);
                    return;
                }
                let data = {
                    'id': this.payroll_id,
                    'type': this.type,
                    'description': this.description,
                    'bonus': this.bonus,
                    'approved': this.approved,
                    "payroll": this.payroll,
                    "shift": this.shift
                };



                modRequest.request('payroll.payroll_item.save', null, data,
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
