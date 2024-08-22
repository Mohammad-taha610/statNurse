Vue.component('nurse-notes-view', {
    template: /*html*/`
        <v-app>
            <v-container>
                <nst-overlay :loading="loading"></nst-overlay>
                <nst-error-notification
                        v-if="error"
                        :error="error"></nst-error-notification>
                <h5>* All changes must be saved by 'Save' button at bottom of the page to be finalized</h5>
                <v-simple-table>
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 70%;">
                                    Note
                                </th>
                                <th class="text-left">
                                    Date
                                </th>
                                <th class="text-left">
                                    Time
                                </th>
                                <th class="text-left">
                                    Admin
                                </th>
                                <th class="text-left">
                                    Edit
                                </th>
                                <th class="text-left">
                                    Delete
                                </th>
                            </tr>
                        </thead>
                        <tbody v-for="(note, index) in notes">
                            <tr>
                                <td>{{ note.note }}</td>
                                <td>{{ note.date }}</td>
                                <td>{{ note.time }}</td>
                                <td>{{ note.admin }}</td>
                                <td>
                                    <v-btn
                                    color="primary"
                                    @click="editNote(index)"
                                    >
                                    Edit
                                    </v-btn>
                                </td>
                                <td>
                                    <v-btn
                                        color="error"
                                        @click="deleteConfirmation(index)"
                                    >
                                        Delete
                                    </v-btn>
                                </td>
                            </tr>
                            <tr v-show="note.edit">
                                <td colspan="5">
                                    <v-text-field
                                            label="Edit Note"
                                            v-model="editedNote"
                                    ></v-text-field>
                                </td>
                                <td>                        
                                    <v-btn
                                        color="success"
                                        @click="confirmEdit(index)"
                                    >
                                        Change
                                    </v-btn>
                                </td>
                            </tr>
                            <tr v-show="note.delete">
                                <td colspan="5">
                                    To delete this note click 'Confirm Delete' on right and 'Save' at bottom of page.
                                </td>
                                <td colspan="2">                        
                                    <v-btn
                                        color="error"
                                        @click="deleteNote(index)"
                                    >
                                        Confirm Delete
                                    </v-btn>
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </v-simple-table><br><br>
                <v-row v-show="!showAddNote"  style="margin: 10px;">
                    <v-btn
                        color="primary"
                        @click="showCreateNote"
                    >
                        Add Note
                    </v-btn>
                </v-row>
                <v-row v-show="showAddNote" style="margin: 10px;">
                    <v-textarea
                        solo
                        name="input-7-4"
                        label="Save note by clicking 'Add Note' button and then by clicking 'Save' button at bottom"
                        v-show="showAddNote"
                        v-model="newNote.note"
                    ></v-textarea>
                </v-row>
                <v-row v-show="showAddNote" style="margin: 10px;">
                    <v-btn
                        color="error"
                        v-show="showAddNote"
                        @click="showCreateNote"
                    >
                        Cancel
                    </v-btn>
                    <p>&nbsp</p>
                    <v-btn
                        color="primary"
                        v-show="showAddNote"
                        @click="pushNote"
                    >
                        Add Note
                    </v-btn>
                </v-row>
            </v-container>
        </v-app>
    `,
    props: [
        'id'
    ],
    data: function () {
        return{
            error: null,
            notes: [],
            newNote: {
                date: '',
                time: '',
                note: '',
                edit: false,
                delete: false
            },
            editedNote: '',
            loading: false,
            showAddNote: false,
            admin: ''
        }
    },
    created() {
        this.loadNotes();
        this.getAdminName();
    },
    mounted() {
        this.$root.$on('saveMemberData', function () {
            this.saveNotes()
        }.bind(this));
    },
    methods: {
        loadNotes() {
            let data = {
                id: this.id,
            };
            this.loading = true;
            modRequest.request('sa.member.load_nurse_notes', {}, data, function (response) {
                if (response.success) {
                    if (response.notes) {
                        for (let i = 0; i <response.notes.length; i++) {
                            response.notes[i].edit = false;
                            response.notes[i].delete = false;
                        }
                        this.notes = response.notes
                    }
                    this.error = null;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        saveNotes() {
            let data = {
                id: this.id,
                notes: this.notes
            };
            this.loading = true;
            modRequest.request('sa.member.save_nurse_notes', {}, data, function (response) {
                if (response.success) {
                    this.error = null;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        getAdminName() {
            modRequest.request('sa.member.admin_for_note', {}, null, function (response) {
                if (response.success) {
                    this.admin = response.first_name + response.last_name;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.error = {
                        type: 'danger',
                        message: response.message,
                    }
                }
                this.loading = false;
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        getDateAndTime() {
            const d = new Date();

            let day = d.getDate();
            let month = d.getMonth() + 1;
            let year = d.getFullYear();

            const time = d.toLocaleTimeString('en-US', {
                hour: '2-digit',
                minute: '2-digit',
            });

            let currentDate = {
                date: `${month}-${day}-${year}`,
                time: time
            }

            return currentDate;
        },
        showCreateNote() {
            this.showAddNote = !this.showAddNote;
        },
        pushNote() {
            if (this.newNote.note == '') {
                $.growl.error({ title: "Error!", message: "Please add a note to note", size: "large" });
                return;
            }

            const date = this.getDateAndTime();
            this.newNote.date = date.date;
            this.newNote.time = date.time;
            this.newNote.admin = this.admin;

            this.notes.push(this.newNote);
            this.newNote = {
                date: '',
                time: '',
                note: '',
                edit: false,
                delete: false,
            }
            this.showAddNote = false;
        },
        editNote(index) {
            changeTo = !this.notes[index].edit;
            for (let i = 0; i < this.notes.length; i++) {
                this.notes[i].edit = false;
                this.notes[i].delete = false;
            }
            this.notes[index].edit = changeTo;
            this.editedNote = this.notes[index].note;
        },
        confirmEdit(index) {
            const date = this.getDateAndTime();
            this.notes[index].date = date.date;
            this.notes[index].time = date.time;

            this.notes[index].note = this.editedNote;
            this.editedNote = '';
            this.notes[index].edit = !this.notes[index].edit;
        },
        deleteConfirmation(index) {
            changeTo = !this.notes[index].delete;
            for (let i = 0; i < this.notes.length; i++) {
                this.notes[i].delete = false;
                this.notes[i].edit = false;
            }
            this.notes[index].delete = changeTo;
        },
        deleteNote(index) {
            this.notes.splice(index, 1)
        },
    }
});