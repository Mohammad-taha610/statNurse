window.addEventListener('load', function () {
    Vue.component('provider-users-list', {
        template:
            `
            <div class="container-fluid" id="provider-users-list">
                <v-app>
                    <v-row> 
                        <v-col cols="12" v-for="user in users">
                            <v-form
                                id="addUserForm"
                                ref="form"
                                v-model="valid"
                                v-bind:is-validated="false"
                                lazy-validation
                            >
                                <div class="card user-card"> 
                                    <v-card-title> 
                                        <h4 class="black--text mb-0 pl-2">{{ user.first_name + ' ' + user.last_name }}</h4>
                                        <v-spacer></v-spacer>
                                        <v-btn
                                            v-if="user.editing && ((user.user_type != 'Admin' && (adminUsers && adminUsers.length != 0)) || user.user_type == 'Admin')"
                                            color="success"
                                            text
                                            @click="saveUser(user)">Save</v-btn>
                                        <v-dialog max-width="350"> 
                                            <template v-slot:activator="{ on, attrs }">
                                                <v-btn
                                                    v-show="current_user_type == 'Admin' && user.id != current_user_id && (users && users.length > 1) && user.editing && ((user.user_type != 'Admin' && adminUsers.length != 0) || adminUsers.length > 1)"
                                                    v-on="on"
                                                    v-bind="attrs"
                                                    color="red"
                                                    text>Delete</v-btn>
                                            </template>
                                            <template v-slot:default="dialog">
                                                <v-card> 
                                                    <v-toolbar 
                                                        color="red"
                                                        class="text-h4 white--text">Delete User</v-toolbar>
                                                    <v-card-text class="pt-5">
                                                        Are you sure you want to <strong class="red--text">DELETE</strong> this user?                                        
                                                    </v-card-text>
                                                    <v-card-actions> 
                                                        <v-spacer></v-spacer>
                                                        <v-btn 
                                                            v-on:click="dialog.value = false"
                                                            color="light">Cancel</v-btn>
                                                        <v-btn 
                                                            v-on:click="deleteUser(user); dialog.value = false;"
                                                            color="red"
                                                            class="white--text">Yes, Delete</v-btn>
                                                    </v-card-actions>
                                                </v-card>
                                            </template>
                                        </v-dialog>
                                        <v-btn
                                            v-if="(current_user_type == 'Admin' || current_user_id == user.id)"
                                            light
                                            text
                                            @click="editUser(user)">Edit</v-btn>
                                    </v-card-title>
                                    <v-card-text class="pb-0"> 
                                        <v-row class="pr-2 pl-2 pb-4" v-if="user.editing">
                                            <v-col cols="12" md="6">
                                                <h5 class="primary--text">First Name:</h5>
                                                <v-text-field 
                                                    dense 
                                                    :disabled="!user.editing"
                                                    :rules="first_name_rules"
                                                    @input="validateInputs()"
                                                    v-model="user.first_name"
                                                    required></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <h5 class="primary--text">Last Name:</h5>
                                                <v-text-field 
                                                    dense 
                                                    :readonly="!user.editing"
                                                    :rules="last_name_rules"
                                                    @input="validateInputs()"
                                                    v-model="user.last_name"
                                                    required></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <h5 class="primary--text">Username:</h5>
                                                <v-text-field 
                                                    dense 
                                                    :readonly="!user.editing" 
                                                    :rules="username_rules"
                                                    @input="validateInputs()"
                                                    v-model="user.username"
                                                    required></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <h5 class="primary--text">Password:</h5>
                                                <v-text-field 
                                                    dense
                                                    :value="user.password"
                                                    :append-icon="value ? 'mdi-eye' : 'mdi-eye-off'"
                                                    @click:append="() => (value = !value)"
                                                    :type="value ? 'password' : 'text'"
                                                    :readonly="!user.editing"
                                                    :rules="password_rules"
                                                    @input="validateInputs()"
                                                    v-model="user.password"
                                                    :placeholder="user.id == 0 ? '' : 'Leave blank to keep the same password'"
                                                    required></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <h5 class="primary--text">Email:</h5>
                                                <v-text-field 
                                                    dense 
                                                    :readonly="!user.editing"
                                                    :rules="email_rules"
                                                    @input="validateInputs()"
                                                    v-model="user.email"
                                                    required></v-text-field>
                                            </v-col>
                                            <v-col cols="12" md="6">
                                                <h5 class="primary--text">User Type:</h5>
                                                <v-select
                                                    dense
                                                    :disabled="!user.editing || (users && users.length < 2) || current_user_id == user.id"
                                                    v-model="user.user_type"
                                                    :items="['Admin', 'Scheduler']"></v-select>
                                            </v-col>
                                            <v-col cols="12" md="6" v-show="user.user_type != 'Admin'">
                                                <h5 class="primary--text">Allowed to add Bonus/Incentive Pay</h5>
                                                <v-select
                                                    dense
                                                    :disabled="user.user_type == 'Admin'"
                                                    v-model="user.bonus_allowed"
                                                    :items="yes_or_no"
                                                    item-text="name"
                                                    item-value="value"></v-select>
                                            </v-col>
                                            <v-col cols="12" md="6" v-show="user.user_type != 'Admin'">
                                                <h5 class="primary--text">Allowed to add Covid Pay</h5>
                                                <v-select
                                                    dense
                                                    :disabled="user.user_type == 'Admin'"
                                                    v-model="user.covid_allowed"
                                                    :items="yes_or_no"
                                                    item-text="name"
                                                    item-value="value"></v-select>
                                            </v-col>
                                        </v-row>
                                    </v-card-text>
                                </div> 
                            </v-form>
                        </v-col>
                    </v-row>
                    <v-row> 
                        <v-col cols="12 text-right">
                            <v-btn
                                v-if="current_user_type == 'Admin'"
                                color="primary"
                                @click="addUser">Add User</v-btn>
                        </v-col>
                    </v-row>
                </v-app>
            </div>
        `,
        props: [
            'member_id',
            'current_user_id',
            'current_user_type'
        ],
        data: function () {
            return {
                value: true,
                validated: false,
                users: [],
                user_types: [
                    {
                        name: 'Admin',
                        value: 'admin'
                    },
                    {
                        name: 'Scheduler',
                        value: 'scheduler'
                    }
                ],
                first_name_rules: [
                    i => i && i.length >= 2 || 'At least 2 characters are required.'
                ],
                last_name_rules: [
                    i => i && i.length >= 2 || 'At least 2 characters are required.'
                ],
                email_rules: [
                    v => !v || /^\w+([.-]?\w+)*@\w+([.-]?\w+)*(\.\w{2,4})+$/.test(v) || 'A valid email address is required.'
                ],
                username_rules: [
                    i => i && i.length >= 4 || 'Username must be at least 4 characters.',
                ],
                password_rules: [
                    i => i && i.length >= 4 || 'Password must be at least 4 characters.'
                ],
                valid: true,
                yes_or_no: [
                    {
                        name: 'Yes',
                        value: 1
                    },
                    {
                        name: 'No',
                        value: 0
                    }
                ]
            };
        },
        created() {
            this.getUsers();
        },
        mounted() {
        },
        computed: {
            adminUsers() {
                return this.users.filter(function (user) {
                    return user.user_type == 'Admin';
                });
            }
        },
        methods: {
            validateInputs() {
                // check all inputs, if all have content, we're valid and can save
                let form = document.getElementById('addUserForm');
                let inputs = form.getElementsByTagName('input');
                let count = 0, actualCount = 0;
                for (let i = 0; i < inputs.length; i += 1) {
                    if (!inputs[i].disabled && inputs[i].type !== 'hidden') {
                        actualCount++;
                        if (inputs[i].value !== '') {
                            count++;
                        }
                    }
                }
                if (count === actualCount) {
                    this.validated = true;
                }
            },
            addUser() {
                this.users.push({
                    id: 0,
                    first_name: '',
                    last_name: '',
                    email: '',
                    username: '',
                    password: '',
                    user_type: '',
                    editing: true,
                    bonus_allowed: false,
                    covid_allowed: false
                });
                // need to run validation after adding
            },
            deleteUser(user) {
                if (user.id == 0) {
                    this.users.splice(this.users.indexOf(user));
                } else {
                    let data = {
                        id: user.id,
                    };
                    modRequest.request('member.delete_user', {}, data, function (response) {
                        if (response.success) {
                            this.users.splice(this.users.indexOf(user), 1);
                        } else {
                            console.log('Error');
                            console.log(response);
                        }
                    }.bind(this), function (response) {
                        console.log('Failed');
                        console.log(response);
                    });
                }
            },
            saveUser(user) {
                let data = {
                    member_id: this.member_id,
                    id: user.id,
                    first_name: user.first_name,
                    last_name: user.last_name,
                    email: user.email,
                    username: user.username,
                    password: user.password,
                    user_type: user.user_type,
                    bonus_allowed: user.bonus_allowed,
                    covid_allowed: user.covid_allowed
                };

                modRequest.request('member.save_user_data', {}, data, function (response) {
                    if (response.success) {
                        console.log('success');
                        user.id = response.id;
                        user.editing = false;
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
                this.validated = false;
            },
            editUser(user) {
                if(this.users){
                    for (let i = 0; i < this.users.length; i++) {
                        if (this.users[i] != user) {
                            this.users[i].editing = false;
                        } else {
                            this.users[i].editing = !this.users[i].editing;
                        }
                    }
                }
            },
            getUsers() {
                let data = {
                    member_id: this.member_id
                };

                modRequest.request('member.get_users_list', {}, data, function (response) {
                    this.users = [];
                    if (response.success) {
                        if(response.users){
                            for (let i = 0; i < response.users.length; i++) {
                                let user = response.users[i];
                                this.users.push({
                                    id: user.id,
                                    first_name: user.first_name,
                                    last_name: user.last_name,
                                    username: user.username,
                                    email: user.email,
                                    phone: user.phone,
                                    last_login: user.last_login,
                                    user_type: user.user_type,
                                    editing: false,
                                    bonus_allowed: user.bonus_allowed,
                                    covid_allowed: user.covid_allowed
                                })
                            }
                        }
                    } else {
                        console.log('Error');
                        console.log(response);
                    }
                }.bind(this), function (response) {
                    console.log('Failed');
                    console.log(response);
                });
            }
        }
    });
})