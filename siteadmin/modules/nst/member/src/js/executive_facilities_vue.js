Vue.component('executive-facilities-view', {
    template: /*html*/`
        <v-app>
            <v-container>
                <nst-overlay :loading="loading"></nst-overlay>
                <nst-error-notification
                        v-if="error"
                        :error="error"></nst-error-notification>
                        <v-simple-table>
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 70%;">
                                    Facility
                                </th>
                                <th class="text-left">
                                    Delete
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <v-select
                                        v-model="newFacility"
                                        :items="allFacilities"
                                        item-text="company"
                                        label="Add facility"
                                        return-object
                                    ></v-select>
                                </td>
                                <td>
                                    <v-btn
                                        color="primary"
                                        @click="addFacility()"
                                    >
                                        Add
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-for="(facility, index) in facilities">
                            <tr>
                                <td>{{facility.company}}</td>
                                <td>
                                    <v-btn
                                        color="error"
                                        @click="removeFacility(facility)"
                                    >
                                        Remove
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </v-simple-table>
            </v-container>
        </v-app>
    `,
    props: [
        'id',
        'current_tab'
    ],
    data: function () {
        return {
            error: null,
            loading: false,
            facilities: [],
            allFacilities: [],
            newFacility: null,
        }
    },
    created() {
        this.loadExecutiveFacilities();
        this.loadFacilities();
    },
    mounted() {
        this.$root.$on('saveMemberData', function () {
            this.saveNotes()
        }.bind(this));
    },
    methods: {
        addFacility() {
            const facilityId = this.newFacility.id;
            if (this.facilities.find(facility => facility.id === facilityId)) {
                alert("Facility already added");
                return;
            } else {
                modRequest.request('sa.member.add_executive_facility', {}, {
                    id: this.id,
                    facility_id: facilityId,
                }, function (response) {
                    if (response.success) {

                        this.facilities.push(this.newFacility);
                        this.facilities = this.facilities.sort(function (a, b) {
                            return a.company.localeCompare(b.company);
                        });
                        this.newFacility = null;
                    } else {
                        alert("Could not add facility");
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            }
        },
        removeFacility(facility) {
            modRequest.request('sa.member.remove_executive_facility', {}, {
                id: this.id,
                facility_id: facility.id,
            }, function (response) {
                if (response.success) {

                    this.facilities = this.facilities.filter(function (item) {
                        return item.id !== facility.id;
                    });
                } else {
                    alert("Could not remove facility");
                }
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });

        },
        loadFacilities() {
            let data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_facilities', {}, data, function (facilities) {
                this.allFacilities = facilities.sort(function (a, b) {
                    return a.company.localeCompare(b.company);
                });
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        loadExecutiveFacilities() {
            let data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_executive_facilities', {}, data, function (facilities) {
                this.facilities = facilities.sort(function (a, b) {
                    return a.company.localeCompare(b.company);
                });
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
    }
});
