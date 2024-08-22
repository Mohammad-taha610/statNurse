window.addEventListener('load', function () {
    Vue.component('sa-user-group-edit-view', {
        template: `
        <v-app>
            <v-container class="v-application">
                <v-row>
                    <v-col class="col-md-6">
                        <v-card>

                            <v-tabs
                                v-model="tabs"
                                fixed-tabs>
                                <v-tabs-slider></v-tabs-slider>

                                <v-tab
                                    href="#basic-info"
                                    class="primary--text">
                                    Basic Info
                                </v-tab>

                                <v-tab
                                    href="#permissions"
                                    class="primary--text">
                                    Permissions
                                </v-tab>
                            </v-tabs>

                            <v-tabs-items v-model="tabs">

                                <v-tab-item
                                    :value="'basic-info'">
                                    <v-card flat>
                                        <v-card-text>
                                            <v-form ref="createSaUserGroup">            
                                                <v-container fluid>
                                                    <v-row>
                                                        <v-col
                                                            cols="12"
                                                            sm="6"
                                                            md="6">
                                                            <v-text-field
                                                                required
                                                                :rules="[v => !!v || 'Item is required']"
                                                                v-model="groupObj.name"
                                                                label="Name"
                                                                @input="calculateGroupCode">
                                                            </v-text-field>
                                                        </v-col>
                                                        <v-col
                                                            cols="12"
                                                            sm="6"
                                                            md="6">
                                                            <v-text-field
                                                                required
                                                                :rules="[v => !!v || 'Item is required']"
                                                                v-model="groupObj.description"
                                                                label="Description">
                                                            </v-text-field>
                                                        </v-col>
                                                        <v-col
                                                            cols="12"
                                                            sm="6"
                                                            md="6">
                                                            <v-text-field
                                                                required
                                                                :rules="[v => !!v || 'Item is required']"
                                                                v-model="groupObj.code"
                                                                label="Code">
                                                            </v-text-field>
                                                        </v-col>
                                                        <v-col
                                                            cols="12"
                                                            sm="6"
                                                            md="6">
                                                            <v-switch
                                                                v-model="groupObj.is_admin"
                                                                label=" Is Admin Group"
                                                                color="primary"
                                                                :value="true"
                                                            ></v-switch>
                                                        </v-col>
                                                    </v-row>
                                                </v-container>
                                            </v-form>
                                        </v-card-text>
                                    </v-card>
                                </v-tab-item>

                                <v-tab-item
                                    :value="'permissions'">
                                    <v-card flat>
                                        <v-card-text>
                                            <v-card
                                                v-for="(module, modKey) in groupObj.permissions"
                                                elevation="1"
                                                rounded
                                                class="ma-2">
                                                <v-card-text>
                                                    {{makeHumanReadable(modKey)}}
                                                    <v-switch
                                                        v-for="(perm, permKey) in module"
                                                        :label="makeHumanReadable(permKey)"
                                                        v-model="groupObj.permissions[modKey][permKey]"
                                                        :disabled="groupObj.is_admin">
                                                    </v-switch>
                                                </v-card-text>
                                            </v-card>
                                        </v-card-text>
                                    </v-card>
                                </v-tab-item>

                            </v-tabs-items>

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
                    <v-col class="col-md-6">
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
        props: [
            'id'
        ],
        data: function () {
            return {
                saving: false,
                groupObj: {
                    code: "",
                    description: "",
                    id: 0,
                    name: "",
                    permissions:[],
                    is_admin: false
                },
                snackbar: {
                    status: false,
                    message: [],
                    color: '#4CAF50',
                    timeout: 5000
                },
                tabs: null,
            }
        },
        created() {
        },
        mounted() {
            this.getSaUserGroupData();
        },
        computed: {
            isAdminGroup() {
                return this.groupObj.is_admin == "true";
            }

        },
        methods: { 
            triggerSnackbar(message, color, timeout = 4000) {
                this.snackbar.message = message;
                this.snackbar.color = color;
                this.snackbar.timeout = timeout;
                this.snackbar.status = true;
            },
            getSaUserGroupData() {
                let data = {
                    id: this.id
                }

                modRequest.request('sa.user.group.get', {}, data, response => {
                    if (response.success) {
                        this.groupObj = response.group;

                        // Convert string values to boolean values on permissions
                        Object.keys(this.groupObj.permissions).forEach((moduleKey, moduleIndex) => {
                            Object.keys(this.groupObj.permissions[moduleKey]).forEach((permKey, permIndex) => {
                                if (this.groupObj.permissions[moduleKey] && this.groupObj.permissions[moduleKey][permKey]) {
                                    this.groupObj.permissions[moduleKey][permKey] = this.groupObj.permissions[moduleKey][permKey] == 'false' ? false : true;
                                }
                            });
                        });
                    } else {
                    }
                }, response => {});
            },
            makeHumanReadable(stringy) {
                let words = stringy.split("-");
                let word = "";

                words.map(w => {
                    word += w[0].toUpperCase() + w.substr(1) + ' ';
                })

                return word.trim();
            },
            calculateGroupCode() {
                if (this.groupObj.name.length) {
                    this.groupObj.code = this.groupObj.name.replace(/\s+/g, '-').toLowerCase();
                } else {
                    this.groupObj.code = '';
                }
            },
            save() {
                this.saving = true;

                if(!this.$refs.createSaUserGroup.validate()) {
                    this.saving = false;
                    this.triggerSnackbar("Please fill out required fields", "#F44336");
                    return;                
                }

                modRequest.request('sa.user.group.save', {}, this.groupObj, response => {
                    if (response.success) {
                        
                        this.triggerSnackbar('Success', '#4CAF50')
                        this.saving = false;
                        if (response.redirect_url) {
                            location.href = response.redirect_url;
                        }
                    } else {
                        this.triggerSnackbar('Error', "#F44336")
                        this.saving = false;
                    }
                }, response => {
                    this.triggerSnackbar('Error', "#F44336")
                    this.saving = false;
                });
            },

        }
    });
})
