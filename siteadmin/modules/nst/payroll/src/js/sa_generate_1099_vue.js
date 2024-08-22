Vue.component('sa-generate-1099-view', {
    template: /*html*/`
        <div class="container-fluid" id="pay-period-container">

            <!-- invisible form that calls method to retrieve zip files -->
            <form id="zip_form" method="GET" action="/siteadmin/download_1099">
                <input name="pdf_pages" class="hidden" v-model="pdf_page_id" />
            </form>
            
            <form id="csv_form" method="GET" action="/siteadmin/download_1099_csv">
                <input name="pdf_pages" class="hidden" v-model="pdf_page_id" />
            </form>
            
            <div class="row">
                <div class="col-12">
                    <v-app>
                        <v-alert
                            v-if="gen_success"
                            type="success"
                            style="width: 100%;"
                            dismissible
                        >1099's successfully generated and added to export list.</v-alert>
                        <v-alert
                            v-if="gen_failure"
                            type="error"
                            style="width: 100%;"
                            dismissible
                        >Error generating 1099's.</v-alert>
                        <v-card class="d-flex justify-space-around">
                            <v-col class="d-flex flex-column justify-space-around" style="width: 20%;">
                                <v-btn
                                    style="min-width: 20%; margin-top: 1%; margin-bottom: 1%;"
                                    elevation="2"
                                    large
                                    color="primary"
                                    @click="gen1099Forms(0)"
                                >Generate 1099's for {{ to_create_1099_count }} nurses</v-btn>
                                <v-btn
                                    style="min-width: 20%; margin-top: 1%; margin-bottom: 1%;"
                                    elevation="2"
                                    large
                                    color="secondary"
                                    @click="addAllToGenerate"
                                >Add All To Generate</v-btn>
                                <v-btn
                                    style="min-width: 20%; margin-top: 1%; margin-bottom: 1%;"
                                    elevation="2"
                                    large
                                    color="error"
                                    @click="clearGenerationList"
                                >Clear generation list</v-btn>
                            </v-col>
                            <v-col class="d-flex flex-column justify-space-around" style="width: 20%;">
                                <v-btn
                                    style="min-width: 20%; margin-top: 1%; margin-bottom: 1%;"
                                    elevation="2"
                                    large
                                    color="success"
                                    @click="exportForms(0)"
                                >Export {{ to_export_count }} 1099's</v-btn>
                                <v-btn
                                    style="min-width: 20%; margin-top: 1%; margin-bottom: 1%;"
                                    elevation="2"
                                    large
                                    color="secondary"
                                    @click="addAllToExport"
                                >Add All To Export</v-btn>
                                <v-btn
                                    style="min-width: 20%; margin-top: 1%; margin-bottom: 1%;"
                                    elevation="2"
                                    large
                                    color="error"
                                    @click="clearExportList"
                                >Clear export list</v-btn>
                            </v-col>
                            <v-col>
                                <v-select
                                    :items="pdf_pages"
                                    label="Select 1099 Pages To Export"
                                    item-text="name"
                                    item-value="page"
                                    v-model="pdf_page_name"
                                    @input="changePdfPages()"
                                    solo
                                ></v-select>
                            </v-col>
                        </v-card>
                        <div class="card">
                            <div class="card-body">
                                <v-spacer></v-spacer>
                                <div class="table-responsive">
                                    <v-card>
                                        <v-autocomplete
                                            style="margin: 1% 1% 0 1%;"
                                            v-model="search"
                                            :items="searchable_nurses"
                                            item-text="name"
                                            item-value="name"
                                            label="Search for a Nurse"
                                            clearable
                                            width="300"
                                        ></v-autocomplete>
                                        <v-card-text>                                                  
                                            <div v-if="loading" class="center">
                                                <h2>Loading data</h2>
                                                <v-progress-circular
                                                    :size="100"
                                                    :width="15"
                                                    :value=loadingPercent
                                                    color="green"
                                                >{{ loadingPercent }}</v-progress-circular>
                                            </div>
                                            <v-data-table v-else
                                                dense
                                                class="table table-responsive-md"
                                                :headers="headers"
                                                :items="nurses"
                                                :search="search"
                                                :footer-props="footer_props"
                                                multi-sort
                                            >
                                                <template v-slot:item.name="{ item }">
                                                    <a v-bind:href="item.nurse_route" class="blue--text mt-3 block" target="_blank">
                                                        {{ item.name }}
                                                    </a>
                                                </template>

                                                <template v-slot:item.has_1099="{ item }">
                                                    <a v-bind:href="item.has_1099"
                                                       class="blue--text mt-3 block"
                                                       v-if="item.has_1099"
                                                       target="_blank"
                                                       >View 1099</a>
                                                    <p v-else class="red--text mt-3 block" style="margin-bottom: 0;">No 1099 on file</p>
                                                </template>

                                                <template v-slot:item.set_to_gen_sort="{ item }">
                                                    <v-btn
                                                        v-if="item.set_to_gen_sort && !item.has_1099"
                                                        small
                                                        elevation="2"
                                                        @click="removeFromCreate(item.id)"
                                                        color="blue"
                                                        class="white--text"
                                                    >Added to create</v-btn>
                                                    <v-btn
                                                        v-if="!item.set_to_gen_sort && !item.has_1099"
                                                        small
                                                        elevation="2"
                                                        @click="addToCreate(item.id)"
                                                        color="blue lighten-5"
                                                    >Not added to create</v-btn>
                                                    <v-btn
                                                        v-show="to_create_1099.includes(item.id) && item.has_1099"
                                                        small
                                                        elevation="2"
                                                        @click="removeFromCreate(item.id)"
                                                        color="secondary"
                                                    >Set to recreate</v-btn>
                                                    <v-btn
                                                        v-show="!to_create_1099.includes(item.id) && item.has_1099"
                                                        small
                                                        elevation="2"
                                                        @click="addToCreate(item.id)"
                                                        color="light-blue lighten-5"
                                                    >Not set to recreate</v-btn>
                                                </template>

                                                <template v-slot:item.set_to_export_sort="{ item }">
                                                    <v-btn
                                                        v-show="to_export_ids.includes(item.id)"
                                                        small
                                                        elevation="2"
                                                        @click="removeFromExport(item.id)"
                                                        color="success"
                                                    >Added to export</v-btn>
                                                    <v-btn
                                                        v-show="!to_export_ids.includes(item.id) && item.has_1099"
                                                        small
                                                        elevation="2"
                                                        @click="addToExport(item.id)"
                                                        color="green lighten-5"
                                                    >Not added to export</v-btn>
                                                </template>
                                            </v-data-table>
                                        </v-card-text>
                                    </v-card>
                                </div>
                            </div>
                        </div>
                    </v-app>
                </div>
            </div>
        </div>
    `,
    data: function() {
        return {
            nurses: [],
            searchable_nurses: [],
            to_create_1099: [],
            to_create_1099_count: 0,
            to_export: [],
            to_export_ids: [],
            to_export_count: 0,
            gen_to_export: [],
            headers: [
                {
                    text: 'Nurse Name',
                    align: 'start',
                    sortable: true,
                    value: 'name'
                },
                {
                    text: '1099',
                    sortable: true,
                    value: 'has_1099'
                },
                {
                    text: 'Add to Generate 1099',
                    sortable: true,
                    value: 'set_to_gen_sort'
                },
                {
                    text: 'Add to Export',
                    sortable: true,
                    value: 'set_to_export_sort'
                }
            ],
            loading: true,
            loadingPercent: 0,
            search: '',
            gen_success: false,
            gen_failure: false,
            footer_props: {'items-per-page-options': [ 15, 30, 50, -1 ]},
            pdf_pages: [
                {
                    id: "all",
                    name: "All Pages"
                },
                {
                    id: 2,
                    name: "Copy A for IRS"
                },
                {
                    id: 3,
                    name: "Copy 1 for State Tax Department"
                },
                {
                    id: 4,
                    name: "Copy B for Recipient"
                },
                {
                    id: 6,
                    name: "Copy 2"
                },
                {
                    id: 7,
                    name: "Copy C for Payer"
                },
                {
                    id: 8,
                    name: "Export to CSV"
                }
            ],
            pdf_page_name: 'All',
            pdf_page_id: 'all'
        };
    },
    created () {
        this.getNursesWithShift();
    },
    methods: {
        getNursesWithShift() {
            if (this.loadingPercent == 0) {
                this.loadingPercent = 20;
            }
            modRequest.request('sa.payroll.get_nurses_with_shift', {}, null, function(response) {
                if (response.success) {
                    if (this.loadingPercent == 20) {
                        this.loadingPercent = 50;
                    }
                    this.checkFor1099(response.nurses);
                } else {
                    this.loading = false;
                    this.loadingPercent = 0;
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                this.loading = false;
                this.loadingPercent = 0;
                console.log('Failed');
                console.log(response);
            });
        },
        checkFor1099(nurses) {
            data = { nurses: nurses }
            if (this.loadingPercent < 75) {
                this.loadingPercent = 75;
            }

            modRequest.request('sa.payroll.check_for_1099', {}, data, function(response) {
                if (response.success) {
                    this.loadingPercent = 90
                    this.nurses = response.nurses;
                    this.gen_to_export = [];
                    
                    this.moveGeneratedToExport()
                    
                    for (var i = 0; i < this.nurses.length; i++) {
                        this.searchable_nurses.push(this.nurses[i].name);
                        
                        if (this.gen_to_export.includes(this.nurses[i].id)) {
                            this.gen_success = true;
                            this.nurses[i].set_to_export_sort = true;
                        } else {
                            this.nurses[i].set_to_export_sort = false;
                        }
                        this.nurses[i].set_to_gen_sort = false;
                    }          
                } else {
                    console.log('Error');
                    console.log(response);
                    this.loading = false;
                    this.loadingPercent = 0;
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
                this.loading = false;
                this.loadingPercent = 0;
            });
        },
        gen1099Forms(counter) {
            this.loading = true;
            const grouping = 10;

            numberToExport = this.to_create_1099.length;
            iterations = parseFloat(numberToExport) / grouping;

            iterationsSplit = String(iterations).split(".");
            finalIterations = Number(iterationsSplit[0]);
            if (Number(iterationsSplit[1]) > 0) {
                finalIterations += 1;
            }

            tempNurseIds = this.to_create_1099;
            if (counter < finalIterations) {
                groupOfGrouping = [];
                for (let i = 0; (i + (counter * grouping)) < (counter * grouping + grouping); i++) {
                    if ((i + (counter * grouping)) >= numberToExport) { continue; }

                    nurseId = tempNurseIds[(i + (counter * grouping))];
                    nurseComp = this.nurses.filter(nurse => nurse.id == nurseId);
                    totalComp = nurseComp[0].total_comp;

                    nurse = {
                        id: nurseId,
                        total_comp: totalComp
                    };
                    groupOfGrouping.push(nurse);
                }
                
                this.gen1099FormsBatch(groupOfGrouping, counter);
                this.loadingPercent = ((counter / finalIterations) * 100).toFixed();
                counter += 1;
            } else {
                this.getNursesWithShift();
            }
        },
        gen1099FormsBatch(nurses, counter) {
            data = { nurses: nurses };

            modRequest.request('sa.payroll.gen_1099s', {}, data, function(response) {
                if (response.success) {
                    counter += 1;
                    this.gen1099Forms(counter);
                } else {
                    console.log('Error');
                    console.log(response);
                    this.gen_failure = true;
                    this.loading = false;
                    this.loadingPercent = 0;
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
                this.gen_failure = true;
                this.loading = false;
                this.loadingPercent = 0;
            });
        },
        exportForms(counter) {
            this.loading = true;
            const grouping = 50;

            numberToExport = this.to_export.length;
            iterations = parseFloat(numberToExport) / grouping;

            iterationsSplit = String(iterations).split(".");
            finalIterations = Number(iterationsSplit[0]);
            if (Number(iterationsSplit[1]) > 0) {
                finalIterations += 1;
            }

            tempNurseIds = this.to_export;
            if (counter < finalIterations) {
                groupOfGrouping = [];
                for (let i = 0; (i + (counter * grouping)) < (counter * grouping + grouping); i++) {
                    if ((i + (counter * grouping)) >= numberToExport) { continue; }

                    // Had to adjust this to include total_comp to support exporting via CSV
                    let tempNurse = tempNurseIds[(i + (counter * grouping))];
                    console.log(tempNurse);
                    nurseComp = this.nurses.filter(nurse => nurse.id == tempNurse.id);
                    tempNurse['total_comp'] = nurseComp[0].total_comp;
                    
                    groupOfGrouping.push(tempNurse);
                    // groupOfGrouping.push(tempNurseIds[(i + (counter * grouping))]);
                }
                
                this.exportFormsBatch(groupOfGrouping, counter);
                this.loadingPercent = ((counter / finalIterations) * 100).toFixed();
            } else {
                if (this.pdf_page_id != 8) {
                    document.getElementById("zip_form").submit();
                } else {
                    document.getElementById("csv_form").submit();
                }

                this.loading = false;
                this.loadingPercent = 0;
                this.clearExportList();
            }
        },
        exportFormsBatch(nurses, counter) {
            data = {
                nurses: nurses,
                pdf_pages: this.pdf_page_id,
                counter: counter            
            };

            modRequest.request('sa.payroll.gen_1099_export_group', {}, data, function(response) {
                if (response.success) {
                    counter += 1;
                    this.exportForms(counter);
                } else {
                    console.log('Error');
                    console.log(response);
                    this.gen_failure = true;
                    this.loading = false;
                    this.loadingPercent = 0;
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
                this.gen_failure = true;
                this.loading = false;
                this.loadingPercent = 0;
            });
        },
        moveGeneratedToExport() {
            for (var i = 0; i < this.to_create_1099.length; i ++) {
                if (this.to_export_ids.includes(this.to_create_1099[i])) {
                    continue;
                }
                this.addToExport(this.to_create_1099[i]);
            }
            for (var j = 0; j < this.to_export_ids.length; j++) {
                if (this.gen_to_export.includes(this.to_export_ids[j])) { continue; }
                this.gen_to_export.push(this.to_export_ids[j]);
            }
            
            this.clearGenerationList();
        },
        addToCreate(id) {
            this.to_create_1099.push(id);
            this.to_create_1099_count = this.to_create_1099.length;
            
            index = this.nurses.findIndex(nurse => {
                return nurse.id == id;
            })
            this.nurses[index].set_to_gen_sort = true;
        },
        removeFromCreate(id) {
            for (var i = 0; i < this.to_create_1099.length; i++) {
                if (this.to_create_1099[i] == id) {
                    this.to_create_1099.splice(i, 1);
                    break;
                }
            }
            this.to_create_1099_count = this.to_create_1099.length;

            index = this.nurses.findIndex(nurse => {
                return nurse.id == id;
            })
            this.nurses[index].set_to_gen_sort = false;
        },
        addToExport(id) {
            nurse = this.nurses.filter(nurse => nurse.id == id);

            if (!nurse[0].has_1099) {
                return;
            }

            fileNameArray = nurse[0].name.split(" ");
            fileName = fileNameArray[1] + "_" + fileNameArray[0];

            nurseToExport = {
                id: nurse[0].id,
                file_name: fileName,
                url: nurse[0].has_1099
            }

            if (this.to_export.includes(id)) {
                this.removeFromExport(id);
            }

            this.to_export.push(nurseToExport);
            this.to_export_count = this.to_export.length;
            this.to_export_ids.push(String(id));

            index = this.nurses.findIndex(nurse => {
                return nurse.id == id;
            })
            this.nurses[index].set_to_export_sort = true;
        },
        removeFromExport(id) {
            for (var i = 0; i < this.to_export.length; i ++) {
                if (this.to_export[i].id == id) {
                    this.to_export.splice(i, 1);
                    this.to_export_ids.splice(i, 1);
                }
            }
            this.to_export_count = this.to_export.length;
            
            index = this.nurses.findIndex(nurse => {
                return nurse.id == id;
            })
            this.nurses[index].set_to_export_sort = false;
        },
        clearGenerationList() {
            for (let i = 0; i < this.to_create_1099.length; i++) {
                index = this.nurses.findIndex(nurse => {
                    return nurse.id == this.to_create_1099[i];
                })
                this.nurses[index].set_to_gen_sort = false;
            }

            this.to_create_1099_count = 0;
            this.to_create_1099 = [];
            
            this.loading = false;
            this.loadingPercent = 0;
        },
        clearExportList() {
            for (let i = 0; i < this.to_export.length; i++) {
                index = this.nurses.findIndex(nurse => {
                    return nurse.id == this.to_export[i].id;
                })
                this.nurses[index].set_to_export_sort = false;
            }

            this.to_export_count = 0;
            this.to_export = [];
            this.to_export_ids = [];
        },
        changePdfPages() {
            pdfPagesObj = this.pdf_pages.filter(pdfPagesObj => pdfPagesObj.name == this.pdf_page_name);

            this.pdf_page_id = pdfPagesObj[0].id
        },
        addAllToGenerate() {
            this.clearGenerationList();

            for (let i = 0; i < this.nurses.length; i++) {
                this.addToCreate(this.nurses[i].id);
            }
        },
        addAllToExport() {
            this.clearExportList();

            for (let i = 0; i < this.nurses.length; i++) {

                if (this.nurses[i].has_1099){
                    this.addToExport(this.nurses[i].id);
                }
            }
        }
    }
});