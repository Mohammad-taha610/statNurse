window.addEventListener('load', function() {
    let regex = /\/[1-9]{1}[0-9]*\//
    let regexSecondPass = /[1-9]{1}[0-9]*/
    let url = window.location.href;

    let shiftId = url.match(regex)[0].match(regexSecondPass)[0];
    let recurrence = 0;
    let recurrenceId = null;
    if(url.includes('create')){
        if(url.includes('Recurrence')) {
            pendingShiftId = 0;
            recurrence = 1;
        }
        else{
            pendingShiftId = 0;
        }
    }else {
        if(url.includes('Recurrence')) {
            recurrence = 1;
            recurrenceId = url.match(regex)[0].match(regexSecondPass)[0];
        }
        else{
            shiftId = url.match(regex)[0].match(regexSecondPass)[0]
        }
    }


    let is_active_options = [{'value':true,'label':'Yes'},{'value':false,'label':'No'}];
    Vue.component('v-select',VueSelect.VueSelect);
    Vue.component('edit-pending-shift', {
        data: function(){
            return{
                errors: [],
                nurse_options: undefined,
                nurse_searchInput: "",
                nurse: undefined,

                is_active_options: is_active_options,
                nurse_approved: "",
                provider_approved: "",
                search_timeout: null,
            }},
        mounted: function(){
            modRequest.request('events.pendingShift.load', null, {"recurrence": recurrence, 'recurrenceId': recurrenceId, 'shiftId': shiftId, 'pendingShiftId': pendingShiftId},
                function(response) {
                    if(response.data.success) {
                        this.nurse_options = response.data.nurseOptions;
                        this.nurse = response.data.nurse;

                        this.nurse_approved = response.data.nurseApproved;
                        this.provider_approved = response.data.providerApproved;

                        if(this.nurse_approved === true) this.nurse_approved = {'value': 1, 'label':'Yes'}
                        if(this.nurse_approved === false) this.nurse_approved = {'value': 0, 'label':'No'}

                        if(this.provider_approved === true) this.provider_approved = {'value': 1, 'label':'Yes'}
                        if(this.provider_approved === false) this.provider_approved = {'value': 0, 'label':'No'}

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
                
            <div id="vue-component-context" class="form-group">
                <label for="">Nurse</label>
                <v-select v-model="nurse"  @search="nurseOnSearch" :options="nurse_options" searchable label="name" placeholder="Nurse Name"></v-select>
            </div>
            
            <div class="form-group">
                <label for="">Nurse Approved</label>
                <v-select v-model="nurse_approved" placeholder="Nurse Approval" :options="is_active_options" id="is_active" name="is_active"></v-select>
            </div>
            
            <div class="form-group">
                <label>Provider Approved</label>
                <v-select v-model="provider_approved" placeholder="Provider Approval" :options="is_active_options" id="is_active" name="is_active"></v-select>
            </div>
            
            <div class="clearfix form-actions" style="margin:0 -12px;">
                <input type="submit" class="btn btn-primary" value="Save" @click="savePendingShift();">
            </div>
        </div>`,
        methods: {

            savePendingShift: function () {
                this.errors = [];

                if (typeof(this.nurse) !== 'object') {
                    this.errors.push('Must select a material');
                }

                if (typeof(this.nurse_approved) !== 'object') {
                    this.errors.push('Must select approved or not approved option');
                }

                if (typeof(this.provider_approved) !== 'object') {
                    this.errors.push('Must select approved or not approved option');
                }


                if(this.errors.length!==0){
                    return;
                }
                let data = {
                    'id': shiftId,
                    'name': this.name,
                    'recurrence': recurrence,
                    "nurse": this.nurse,
                    "nurse_approved": this.nurse_approved.value,
                    "provider_approved": this.provider_approved.value
                };


                modRequest.request('events.pendingShift.save', null, data,
                    function(response) {
                        if(response.data.success) {
                            window.location.href = response.data.url;
                        }
                    }.bind(this),
                    function(error) {

                    }
                );
            },
            nurseOnSearch: function(val){
                modRequest.request('events.pendingShift.load.nurse', null, {"nurseName": val},
                    function(response) {
                        if(response.data.success) {
                            this.nurse_options = response.data.nurseOptions;
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
