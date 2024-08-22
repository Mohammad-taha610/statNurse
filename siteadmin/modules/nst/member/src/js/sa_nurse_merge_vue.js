window.addEventListener('load', function() {
    Vue.component('v-select', VueSelect.VueSelect);
    Vue.component('sa-nurse-merge-view', {
        template:
            `
            <v-app id="are-you-sure-dilaog">    
                <div class="container-fluid">
                    <div class="row">                 
                        <div class="col-lg-12">
                            <div class="row">
                                <div class="col-md-8 col-md-offset-2 col-12">
                                    <p class="center">
                                        Merging nurses will merge the shifts and data from the <strong>Duplicate Nurse</strong> into the <strong>Primary Nurse</strong>. 
                                        Existing information on the primary nurse wil not be overridden. 
                                        <br><br>
                                        Currently you may only search by nurse first name, non-case-specific.
                                    </p>
                                </div>                        
                            </div>
                            <div class="row" style="height: 100px">                                    
                                <div class="col-md-2 col-md-offset-3 col-12">    
                                    <label for="primaryNurse">Primary Nurse</label>                            
                                    <v-select
                                        name="primaryNurse"
                                        key="primaryNurse"
                                        v-model="selectedPrimaryNurse"
                                        v-on:search="onNurseNameSearch"
                                        :options="nurses"
                                        searchable
                                        label="label"
                                        placeholder="Type to search"
                                        @input="selectedPrimaryNurse ? '' : selectedPrimaryNurse = defaultNurseObj"
                                        class="nurse-merge-selects"
                                        ></v-select>
                                </div>                                    
                                <div class="col-md-2 col-12">
                                    <label for="duplicateNurse">Duplicate Nurse</label>                
                                    <v-select
                                        name="duplicateNurse"
                                        key="duplicateNurse"
                                        v-model="selectedDuplicateNurse"
                                        v-on:search="onNurseNameSearch"
                                        :options="nurses"
                                        searchable
                                        label="label"
                                        @input="selectedDuplicateNurse ? '' : selectedDuplicateNurse = defaultNurseObj"
                                        placeholder="Type to search"
                                        class="nurse-merge-selects"
                                        ></v-select>
                                </div>
                                <div class="col-md-5 col-12"
                                        style="align-self: center">
                                    <button v-if="!merging" 
                                        @click.stop="dialog = true" 
                                        class="primary-btn btn" 
                                        type="button" 
                                        :disabled="(selectedDuplicateNurse.id && selectedPrimaryNurse.id) == 0">MERGE</button>     
                                    <div v-else >
                                        <v-progress-circular v-if="mergingCompletedPercent < 99"
                                            indeterminate 
                                            :rotate="360"
                                            :width="3"
                                            :value="mergingCompletedPercent"
                                            color="primary">{{mergingCompletedPercent}}</v-progress-circular>
                                        <button v-else 
                                            @click.stop="reset" 
                                            class="primary-btn btn" 
                                            type="button">Reset</button> 
                                    </div>                           
                                    <v-dialog v-model="dialog" max-width="290">
                                        <v-card>
                                            <v-card-title class="text-h5">
                                              Are you sure?
                                            </v-card-title>
                                
                                            <v-card-text>
                                              This operation cannot be undone. Merging these nurse accounts will migrate all the shifts, 
                                              payments and data from the duplicate nurse onto the primary nurse. The duplicated nurse will be deactivated as well. 
                                            </v-card-text>
                                
                                            <v-card-actions>
                                                <v-spacer></v-spacer>                                    
                                                <v-btn color="red darken-1"
                                                    text
                                                    @click="dialog = false">Cancel</v-btn>
                                    
                                                <v-btn color="green darken-1"
                                                    text
                                                    @click="mergeNurses">Yes</v-btn>
                                            </v-card-actions>
                                        </v-card>
                                    </v-dialog>    
                                </div>
                            </div>
                            <div v-show="!merging" class="row">                            
                                <div class="col-md-8 col-md-offset-2 col-12">
                                    <div>
                                        <h4>Information display</h4>
                                        <p>Information between selected nurses that match will be highlighted in <span class="matching"><strong>green</strong></span>.
                                        <br><br>
                                        Data from the duplicate nurse that will be migrated to the primary nurse will be highlighted in <span class="migrating"><strong>blue</strong></span></p>
                                    </div>
                                    <table style="width:100%">
                                        <tr>
                                            <th style="width:15%">Fields</th>
                                            <th style="width:25%">Primary Nurse Data</th>
                                            <th style="width:25%">Duplicate Nurse Data</th>
                                        </tr>
                                        <tr v-for="(val, key) in defaultNurseObj" 
                                            v-if="key != 'label'" >
                                            <td>{{humanize(key)}}</td>
                                            <td>
                                                <span v-if="selectedPrimaryNurse[key] && selectedPrimaryNurse[key] != ''" 
                                                    :class="{matching: selectedPrimaryNurse[key] == selectedDuplicateNurse[key]}"
                                                    class="nurse-info-td">
                                                    {{getFieldValues(selectedPrimaryNurse[key])}}
                                                </span>
                                            </td>
                                            <td>
                                                <span v-if="selectedDuplicateNurse[key] && selectedDuplicateNurse[key] != ''"
                                                    :class="{matching: selectedPrimaryNurse[key] == selectedDuplicateNurse[key], 
                                                        migrating: (selectedPrimaryNurse[key] == undefined || selectedPrimaryNurse[key] == '') && 
                                                        (selectedDuplicateNurse[key] !== undefined && selectedDuplicateNurse[key] !== '')}"
                                                    class="nurse-info-td">
                                                    {{getFieldValues(selectedDuplicateNurse[key])}}
                                                </span>                                            
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                            <div v-show="merging" class="row">
                                <div class="col-md-6 col-md-offset-3 col-12">
                                    <h3>Merging Nurses...</h3>
                                    <ul>
                                        <li v-for="status in mergingStatus">{{status.label}}: {{status.status}} {{status.message}}
                                        <v-progress-circular v-if="status.status === 'Pending'" 
                                            indeterminate 
                                            :size="20"
                                            :width="3"
                                            color="primary"></v-progress-circular>  
                                            <v-icon v-if="status.status === 'Completed'" 
                                                color="green"
                                                small>mdi-check-circle</v-icon>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>                    
                    </div>
                </div>                          
            </v-app>
            `,
        props: [],
        data() {
            return {
                dialog: false,
                nurses: [],
                defaultNurseObj: {
                    'label': ''
                },
                selectedPrimaryNurse: {
                    'id': '',
                    'label': ''
                },
                selectedDuplicateNurse: {
                    'id': '',
                    'label': ''
                },
                mergingStatus: {
                    'shifts': {
                        'status': 'Pending',
                        'label': 'Shifts',
                        'message': ''
                    },
                    'migratingData': {
                        'status': 'Pending',
                        'label': 'Migrating nurse account data',
                        'message': ''
                    },
                    'deactivatedDuplicate': {
                        'status': 'Pending',
                        'label': 'Deactivating duplicate account',
                        'message': ''
                    }
                },
                merging: false,
                mergingCompletedPercent: 0,
            };
        },
        mounted() {
            this.getNurseMetaData();
        },
        watch: {
        },
        computed: {
        },
        methods: {
            onNurseNameSearch(query) {
                if (query === null || query === undefined || !query.length) {
                    return;
                }
                let data = {
                    'fields': {'first_name': query, 'last_name': query},
                    'order_by': 'first_name',
                    'per_page': 20,
                    'offset': null,
                    'count': null,
                    'secondary_sort': 'id',
                    'where_andor': 'or',
                    'search_start': null,
                    'search_end': true,
                };
                modRequest.request('nurse.search', {}, data, function (response) {
                    if (response.success) {
                        this.items = [];
                        this.nurses = response.nurses;
                        this.nurses.forEach((n, i, t) => {
                            if(n.is_deleted){
                                console.log(n.is_deleted);
                                t.splice(i, 1);
                            }
                        });

                        this.nurses.forEach((n) => {
                            n.label = n.first_name + ' ' + n.last_name + ' ID: ' + n.id;
                        });
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            getNurseMetaData() {
                modRequest.request('nurse.get.metadata', {}, {}, function (response) {
                    if (response.success) {
                        response.metaData.forEach((md) => {
                            this.defaultNurseObj[md] = '';
                        });
                        // this.defaultNurseObj['label'] = '';
                        this.selectedPrimaryNurse = this.defaultNurseObj;
                        this.selectedDuplicateNurse = this.defaultNurseObj;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            },
            humanize(str) {
                var i, frags = str.split('_');
                for (i=0; i<frags.length; i++) {
                    frags[i] = frags[i].charAt(0).toUpperCase() + frags[i].slice(1);
                }
                return frags.join(' ');
            },
            async mergeNurses(){
                this.dialog = false;
                console.log('merging nurses');
                if(this.selectedDuplicateNurse.id && this.selectedPrimaryNurse.id){
                    this.merging = true;

                    const data = {
                        'primaryNurseId': this.selectedPrimaryNurse.id,
                        'duplicateNurseId': this.selectedDuplicateNurse.id
                    };

                    await this.mergeNurseShifts(data);
                    await this.mergeNurseData(data);
                    await this.deactivateDuplicateNurse(data.duplicateNurseId);
                }
            },
            setDefault(type) {
                if(type === 'primary'){
                    this.selectedPrimaryNurse = this.defaultNurseObj;
                } else {
                    this.selectedDuplicateNurse = this.defaultNurseObj;
                }
            },
            getFieldValues(nurseField){
                // Checking for date fields to output date instead of json array
                if((nurseField !== null && nurseField !== undefined) && (nurseField.date !== null && nurseField.date !== undefined)){
                    return nurseField.date;
                } else {
                    return nurseField;
                }
            },
            mergeNurseShifts(data){
                return new Promise((resolve, reject) => {
                    modRequest.request('nurse.merge.shifts', {}, data, (response) => {
                        if (response.success) {
                            this.mergingStatus.shifts.status = 'Completed';
                            this.mergingStatus.shifts.message = ' - ' + response.duplicateShiftCount + ' shifts & associated payments migrated to primary nurse';
                            this.mergingCompletedPercent += 33;
                        } else {
                            this.mergingStatus.shifts.status = 'Failed';
                            this.mergingStatus.shifts.message = ' - ' + response.duplicateShiftCount + ' shifts & associated payments migrated to primary nurse';
                            this.mergingCompletedPercent += 33;
                            console.log('Error');
                            console.log(response);
                        }
                        resolve();
                    }, (response) => {
                        this.mergingStatus.shifts.status = 'Failed';
                        this.mergingStatus.shifts.message = ' - ' + response.duplicateShiftCount + ' shifts & associated payments migrated to primary nurse';
                        this.mergingCompletedPercent += 33;
                        console.log('Failed');
                        console.log(response);
                        resolve();
                    });
                });
            },
            mergeNurseData(data){
                return new Promise((resolve, reject) => {
                    modRequest.request('nurse.merge.data', {}, data, (response) => {
                        if (response.success) {
                            this.mergingStatus.migratingData.status = 'Completed';
                            this.mergingCompletedPercent += 33;
                        } else {
                            this.mergingStatus.migratingData.status = 'Failed';
                            this.mergingCompletedPercent += 33;
                            console.log('Error');
                            console.log(response);
                        }
                        resolve();
                    }, (response) => {
                        this.mergingStatus.migratingData.status = 'Failed';
                        this.mergingCompletedPercent += 33;
                        console.log('Failed');
                        console.log(response);
                        resolve();
                    });
                });
            },
            deactivateDuplicateNurse(data){
                return new Promise((resolve, reject) => {
                    modRequest.request('nurse.deactivate', {}, data, (response) => {
                        if (response.success) {
                            this.mergingStatus.deactivatedDuplicate.status = 'Completed';
                            this.mergingStatus.deactivatedDuplicate.message = ' - duplicate nurse member account has been deactivated';
                            this.mergingCompletedPercent += 33;
                        } else {
                            this.mergingStatus.deactivatedDuplicate.status = 'Failed';
                            this.mergingCompletedPercent += 33;
                            console.log('Error');
                            console.log(response);
                        }
                        resolve();
                    }, (response) => {
                        this.mergingStatus.deactivatedDuplicate.status = 'Failed';
                        this.mergingCompletedPercent += 33;
                        console.log('Failed');
                        console.log(response);
                        resolve();
                    });
                });
            },
            reset(){
                this.merging = false;
                this.selectedPrimaryNurse = this.defaultNurseObj;
                this.selectedDuplicateNurse = this.defaultNurseObj;
                this.mergingCompletedPercent = 0;
                this.mergingStatus.shifts.status = 'Pending';
                this.mergingStatus.migratingData.status = 'Pending';
                this.mergingStatus.deactivatedDuplicate.status = 'Pending';
                this.mergingStatus.shifts.message = '';
                this.mergingStatus.migratingData.message = '';
                this.mergingStatus.deactivatedDuplicate.message = '';
                this.nurses = [];
            }
        }
    });
});
