window.addEventListener('load', function () {
    Vue.component('sa-user-group-permission-create-view', {
        template: `
        <v-app>
            <v-container >
                <v-row>
                    <v-col class="col-md-6">
                        <v-card
                            elevation="2">
                            <v-card-text>
                                <v-form ref="createSaUserGroupPermission">            
                                    <v-container>
                                        <v-row>
                                            <v-col
                                                cols="12"
                                                sm="6"
                                                md="6">
                                                <v-text-field
                                                    required
                                                    :rules="[v => !!v || 'Item is required']"
                                                    v-model="groupPermObj.name"
                                                    label="Name"
                                                    @input="calculatePermissionCode">
                                                </v-text-field>
                                            </v-col>
                                            <v-col
                                                cols="12"
                                                sm="6"
                                                md="6">
                                                <v-select
                                                    :items="groupings"
                                                    required
                                                    :rules="[v => !!v || 'Item is required']"
                                                    v-model="groupPermObj.grouping"
                                                    label="Grouping"
                                                    @input="calculatePermissionCode">
                                                </v-select>
                                            </v-col>
                                            <v-col
                                                cols="12"
                                                sm="6"
                                                md="6">
                                                <v-text-field
                                                    required
                                                    :rules="[v => !!v || 'Item is required']"
                                                    v-model="groupPermObj.permission_code"
                                                    label="Permission Code (To be referenced in server code for permissions handling)">
                                                </v-text-field>
                                            </v-col>
                                        </v-row>
                                    </v-container>
                                </v-form>
                            </v-card-text>
                            <v-card-actions
                                class="d-flex">
                                <v-btn
                                    class="pa-4 ml-auto"
                                    color="#4CAF50"
                                    dark
                                    @click="save"
                                    :disabled="saving">
                                    Save
                                </v-btn>
                            </v-card-actions>
                        </v-card>
                    </v-col>
                </v-row>
                
                <v-snackbar
                    :color="snackbar.color"
                    v-model="snackbar.status"
                    :timeout="snackbar.timeout">
                        {{ snackbar.message }}
                    <template v-slot:action="{ attrs }">
                        <v-btn
                        color="white"
                        text
                        v-bind="attrs"
                        @click="snackbar.status = false"
                        >
                        Close
                        </v-btn>
                    </template>
                </v-snackbar>
            </v-container>
    </v-app>
            `,
        props: [],
        data: function () {
            return {
                saving: false,
                groupPermObj: {
                    name: "",
                    grouping: "",
                    permission_code: ""
                },
                snackbar: {
                    status: false,
                    message: [],
                    color: '#4CAF50',
                    timeout: 5000
                },
                groupings: [],
            }
        },
        created() {
        },
        mounted() {
            this.getGroupings();
        },
        computed: {

        },
        watch: {
        },
        methods: { 
            getGroupings() {
                modRequest.request('sa.user.group.permission.get.groupings', {}, {}, response => {
                    if (response.success) {
                        this.groupings = response.groupings;
                    } else {
                    }
                }, response => {
                });
            },
            calculatePermissionCode() {
                if (this.groupPermObj.name.length) {
                    if (this.groupPermObj.grouping.toLowerCase() == this.groupPermObj.name.split(' ')[0].toLowerCase()) {
                        this.groupPermObj.permission_code = this.groupPermObj.name.replace(/\s+/g, '-').toLowerCase();

                    } else {
                        this.groupPermObj.permission_code = this.groupPermObj.grouping + '-' +this.groupPermObj.name.replace(/\s+/g, '-').toLowerCase();
                    }
                } else {
                    this.groupPermObj.permission_code = this.groupPermObj.grouping + '-' + '';
                }
            },
            save() {
                this.saving = true;

                if(!this.$refs.createSaUserGroupPermission.validate()) {
                    this.saving = false;
                    this.triggerSnackbar("Please fill out required fields", "#F44336");
                    return;                
                }

                let data = this.groupPermObj;

                modRequest.request('sa.user.group.permission.create', {}, data, response => {
                    if (response.success) {
                        
                        this.triggerSnackbar('Success', '#4CAF50')
                        this.saving = false;
                        if (response.redirect_url) {
                            location.href = response.redirect_url;
                        }
                    } else {
                        if (response.message && response.message.length) {
                            this.triggerSnackbar(response.message, "#F44336")
                        } else {
                            this.triggerSnackbar('Error', "#F44336")
                        }
                        this.saving = false;
                    }
                }, response => {
                    this.triggerSnackbar('Error', "#F44336")
                    this.saving = false;
                });
            },
            triggerSnackbar(message, color, timeout = 4000) {
                this.snackbar.message = message;
                this.snackbar.color = color;
                this.snackbar.timeout = timeout;
                this.snackbar.status = true;
            },

        }
    });
})
