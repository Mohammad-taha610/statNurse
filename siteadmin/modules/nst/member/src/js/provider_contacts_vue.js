Vue.component('provider-contacts-view', {
    // language=HTML
    template:
    `
    <v-container>
        <v-row>
            <v-data-table
                class="table table-responsive-md"
                :headers="headers"
                :items="contacts"
                :key="updateKey"
                multi-sort>
                <template v-slot:item.first_name="{ item }">
                    <span key="1" class="block mt-3" v-if="!item.editing">{{item.first_name}}</span>
                    <v-text-field key="2" class="mt-3" dense v-else v-model="item.first_name"></v-text-field>
                </template>
                <template v-slot:item.last_name="{ item }">
                    <span class="block mt-3" v-if="!item.editing">{{item.last_name}}</span>
                    <v-text-field class="mt-3" dense v-else v-model="item.last_name"></v-text-field>
                </template>
                <template v-slot:item.email_address="{ item }">
                    <span class="block mt-3" v-if="!item.editing">{{item.email_address}}</span>
                    <v-text-field class="mt-3" dense v-else v-model="item.email_address"></v-text-field>
                </template>
                <template v-slot:item.phone_number="{ item }">
                    <span class="block mt-3" v-if="!item.editing">{{item.phone_number}}</span>
                    <v-text-field class="mt-3" dense v-else v-model="item.phone_number"></v-text-field>
                </template>
                <template v-slot:item.receives_sms="{ item }">
                    <span class="block mt-3" v-if="!item.editing">{{item.receives_sms ? 'Yes' : 'No'}}</span>
                    <v-switch
                            class="mt-3"
                            v-else
                            v-model="item.receives_sms"
                            hide-details></v-switch>
                </template>
                <template v-slot:item.receives_invoices="{ item }">
                    <span class="block mt-3" v-if="!item.editing">{{item.receives_invoices ? 'Yes' : 'No'}}</span>
                    <v-switch
                            class="mt-3"
                            v-else
                            v-model="item.receives_invoices"
                            hide-details></v-switch>
                </template>
                <template v-slot:item.actions="{ item }">
                    <v-btn 
                            v-show="!item.editing" 
                            class="mt-1 mr-1" 
                            color="primary" 
                            @click="editContact(item); updateTable();"
                    >Edit</v-btn>
                    <v-dialog max-width="300">
                        <template v-slot:activator="{ on, attrs }">
                            <v-btn
                                    v-show="!item.editing"
                                    class="mt-1 mr-1 white--text"
                                    color="red"
                                    v-on="on"
                                    v-bind="attrs"
                            >Delete</v-btn>
                        </template>
                        <template v-slot:default="dialog">
                            <v-card>
                                <v-toolbar color="red" class="text-h4 white--text">
                                    Are you sure?
                                </v-toolbar>
                                <v-card-text class="pt-5">
                                    Do you wish to <strong class="red--text">DELETE</strong> this contact?</v-card-text>
                                </v-card-text>
                                <v-card-actions class="justify-end">
                                    <v-btn
                                            text
                                            color="grey dark-3"
                                            v-on:click="dialog.value = false; updateTable();">Cancel</v-btn>
                                    <v-btn
                                            color="red"
                                            v-on:click="deleteProviderContact(item, dialog); updateTable();"
                                            prepend-icon="mdi-window-close"
                                            class="white--text"
                                    >Yes, Delete</v-btn>
                                </v-card-actions>
                            </v-card>
                        </template>
                    </v-dialog>
                    <v-btn v-show="item.editing" class="mt-2 mr-1" color="primary" @click="saveProviderContact(item); updateTable();">Save</v-btn>
                    <v-btn v-show="item.editing" class="mt-2 mr-1" color="light" @click="item.editing = false; updateTable();">Cancel</v-btn>
                </template>
            </v-data-table>

        </v-row>
        <v-row>
            <v-spacer></v-spacer>
            <v-btn
                    color="primary"
                    @click="addProviderContact"
            >Add Contact</v-btn>
        </v-row>
    </v-container>
    `,
    props: [
        'id',
        'member-type'
    ],
    data: function() {
        return {
            deleteDialog: false,
            receives_invoices_options: [
                {t: 'Yes'},
                {t: 'No'}
            ],
            deleting_id: 0,
            updateKey: 0,
            updateKeyy: 0,
            headers: [
                {
                    text: 'First Name',
                    sortable: true,
                    value: 'first_name'
                },
                {
                    text: 'Last Name',
                    sortable: true,
                    value: 'last_name'
                },
                {
                    text: 'Email Address',
                    sortable: true,
                    value: 'email_address'
                },
                {
                    text: 'Phone Number',
                    sortable: true,
                    value: 'phone_number'
                },
                {
                    text: 'Opt-in to Receive Texts',
                    sortable: true,
                    value: 'receives_sms'
                },
                {
                    text: 'Receives Invoices',
                    sortable: true,
                    value: 'receives_invoices'
                },
                {
                    text: 'Actions',
                    sortable: false,
                    value: 'actions'
                }
            ],
            contacts: [],
            contact: null,
            is_loading: false,
        };
    },
    created() {
        if (this.memberType === 'Provider') {
            this.loadProviderContacts();
        }
    },
    mounted() {

    },
    computed: {

    },
    methods: {
        showDeleteModal(contact) {
            this.deleting_id = contact.id;
            this.deleteDialog = true;
            this.updateTable();
        },
        updateTable() {
            this.updateKey++;
            this.updateKeyy++;
        },
        loadProviderContacts() {
            var data = {
                id: this.id
            }
            this.is_loading = true;
            modRequest.request('sa.member.load_provider_contacts', {}, data, function(response) {
                if(response.success) {
                    this.contacts = response.contacts;
                    for (var i = 0; i < this.contacts.length; i++) {
                        this.contacts[i].editing = false;
                    }
                    this.is_loading = false;
                    this.updateTable();
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        saveProviderContact(contact) {
            var data = {
                provider_id: this.id,
                contact: contact,
            }
            this.is_loading = true;
            modRequest.request('sa.member.save_provider_contact', {}, data, function(response) {
                if(response.success) {
                    contact.id = response.id;
                    contact.editing = false;
                    this.is_loading = false;
                    this.updateTable();
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        addProviderContact() {
            this.contacts.push({
                id: 0,
                name: '',
                email_address: '',
                phone_number: '',
                receives_sms: false,
                receives_invoices: false,
                editing: true
            });
        },
        deleteProviderContact(contact, dialog) {
            if(contact.id == 0) {
                this.contacts.splice(this.contacts.indexOf(contact), 1);
                return;
            }
            var data = {
                provider_id: this.id,
                contact_id: contact.id,
            }
            this.is_loading = true;
            modRequest.request('sa.member.delete_provider_contact', {}, data, function(response) {
                if(response.success) {
                    this.contacts.splice(this.contacts.indexOf(contact), 1);
                    this.is_loading = false;
                    dialog.value = false;
                    this.updateTable();
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        editContact(contact) {
            for (var i = 0; i < this.contacts; i++) {
                this.contacts[i].editing = false;
            }
            contact.editing = true;
            this.updateTable();
        }
    }
});

/*


 */
