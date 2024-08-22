Vue.component('edit-tags-view', {
    template: /*html*/`
        <v-app>
            <v-container>
                <nst-overlay :loading="loading"></nst-overlay>
                <nst-error-notification
                        v-if="error"
                        :error="error"></nst-error-notification>
                <h5>* All changes must be saved by 'Save All Changes' button at bottom of the page to be finalized</h5>
                <v-simple-table>
                    <template v-slot:default>
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 70%;">
                                    Name
                                </th>
                                <th class="text-left">
                                    Shown To Providers
                                </th>
                                <th class="text-left">
                                    Edit
                                </th>
                                <th class="text-left">
                                    Delete
                                </th>
                            </tr>
                        </thead>
                        <tbody v-for="(tag, index) in tags">
                            <tr>
                                <td>{{ tag.name }}</td>
                                <td v-show="tag.show_in_provider_portal" style="color: green;">Yes</td>
                                <td v-show="!tag.show_in_provider_portal" style="color: red;">No</td>
                                <td>
                                    <v-btn
                                    color="primary"
                                    @click="editTag(index)"
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
                            <tr v-show="tag.edit">
                                <td colspan="2">
                                    <v-text-field
                                            label="Edit Tag Name"
                                            v-model="editedTag"
                                    ></v-text-field>
                                </td>
                                <td colspan="2">
                                    <v-select
                                        v-model="editShown"
                                        :items="trueOrFalse"
                                        label="Show in Provider Portal"
                                    ></v-select>
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
                            <tr v-show="tag.delete">
                                <td colspan="3" style="color: red;">
                                    To delete this tag click 'Confirm Delete' on right and 'Save All Changes' at bottom of page.
                                </td>
                                <td colspan="2">                        
                                    <v-btn
                                        color="secondary"
                                        @click="deleteTag(index)"
                                    >
                                        Confirm Delete
                                    </v-btn>
                                </td>
                            </tr>
                            <tr v-show="tag.cannot_delete">
                                <td colspan="3" style="color: red;">
                                    This tag is in use and cannot be deleted at this time.
                                </td>
                            </tr>
                        </tbody>
                    </template>
                </v-simple-table><br><br>
                <v-row v-show="!showAddTag"  style="margin: 10px;">
                    <v-btn
                        color="primary"
                        @click="showCreateTag"
                    >
                        Add Tag
                    </v-btn>
                </v-row>
                <v-row v-show="showAddTag" style="margin: 10px;">
                    <v-text-field
                        v-show="showAddTag"
                            label="New Tag Name"
                            v-model="newTag.name"
                    ></v-text-field>
                    <p>&nbsp</p>
                    <v-select
                        v-show="showAddTag"
                        v-model="newShown"
                        :items="trueOrFalse"
                        label="Show in Provider Portal"
                    ></v-select>
                </v-row>
                <v-row v-show="showAddTag" style="margin: 10px;">
                    <v-btn
                        color="error"
                        v-show="showAddTag"
                        @click="showCreateTag"
                    >
                        Cancel
                    </v-btn>
                    <p>&nbsp</p>
                    <v-btn
                        color="primary"
                        v-show="showAddTag"
                        @click="pushTag" 
                    >
                        Save Tag
                    </v-btn>
                </v-row>
                <v-row style="width: 100%; display: flex; flex-direction: row; justify-content: center;">
                    <v-btn
                        x-large
                        color="success"
                        dark
                        @click="saveTags"
                    >
                        Save All Changes
                    </v-btn>
              </v-row>
            </v-container>
        </v-app>
    `,
    data: function () {
        return{
            error: null,
            tags: [],
            tags_to_delete: [],
            newTag: {
                id: 'New',
                name: '',
                show_in_provider_portal: '',
                edit: false,
                delete: false,
                cannot_delete: false
            },
            editedTag: '',
            editShown: '',
            newShown: '',
            trueOrFalse: ["Yes", "No"],
            loading: false,
            showAddTag: false
        }
    },
    created() {
        this.loadTags();
    },
    methods: {
        loadTags() {
            var data = {
                id: 1
            }
            this.loading = true;
            modRequest.request('sa.member.load_provider_filetags', {}, data, function(response) {
                if(response.success) {
                    for (var i = 0; i < response.tags.length; i++) {
                        response.tags[i].edit = false;
                        response.tags[i].delete = false;
                        response.tags[i].cannot_delete = false;
                        this.tags.push(response.tags[i]);
                    }
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.loading = false;
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
                this.loading = false;
            });
        },
        saveTags() {
            let data = {
                tags: this.tags,
                tags_to_delete: this.tags_to_delete
            };
            this.loading = true;
            modRequest.request('sa.member.save_provider_filetags', {}, data, function (response) {
                if (response.success) {
                    this.error = null;
                    $.growl.notice({ title: "Success!", message: "Changes to tags saved.", size: "large" });
                } else {
                    $.growl.error({ title: "Error!", message: "Error saving changes", size: "large" });
                    console.log('Error');
                    console.log(response);
                }
                this.loading = false;
            }.bind(this), function (response) {
                $.growl.error({ title: "Error!", message: "Error saving changes", size: "large" });
                console.log('Failed');
                console.log(response);
            });
        },
        showCreateTag() {
            this.showAddTag = !this.showAddTag;
        },
        pushTag() {
            if (this.newTag.name == '' || this.newShown == '') {
                $.growl.error({ title: "Error!", message: "Please make an entry in both sections", size: "large" });
                return;
            }

            if (this.newShown == "Yes") {
                this.newTag.show_in_provider_portal = true;
            } else if (this.newShown == "No") {
                this.newTag.show_in_provider_portal = false;
            }

            this.tags.push(this.newTag);
            this.newTag = {
                id: 'New',
                name: '',
                shown_to_providers: '',
                edit: false,
                delete: false
            }
            this.newShown = '';
            this.showAddTag = false;
        },
        editTag(index) {
            changeTo = !this.tags[index].edit;
            for (let i = 0; i < this.tags.length; i++) {
                this.tags[i].edit = false;
                this.tags[i].delete = false;
                this.tags[i].cannot_delete = false;
            }
            this.tags[index].edit = changeTo;
            this.editedTag = this.tags[index].name;
        },
        confirmEdit(index) {
            if (this.editShown == "Yes") {
                this.tags[index].show_in_provider_portal = true;
            } else if (this.editShown == "No") {
                this.tags[index].show_in_provider_portal = false;
            }

            this.tags[index].name = this.editedTag;
            this.editedTag = '';
            this.tags[index].edit = !this.tags[index].edit;
        },
        deleteConfirmation(index) {
            this.loading = true;

            if (this.tags[index].delete) {
                this.tags[index].delete = false;
                this.loading = false;
                return;
            } else if (this.tags[index].cannot_delete) {
                this.tags[index].cannot_delete = false;
                this.loading = false;
                return;
            }

            for (let i = 0; i < this.tags.length; i++) {
                this.tags[i].delete = false;
                this.tags[i].edit = false;
                this.tags[i].cannot_delete = false;
            }
            
            let data = null;
            if (this.tags[index].id == 'New') {
                data = {
                    id: -1,
                };
            } else {
                data = {
                    id: this.tags[index].id,
                };
            }
            modRequest.request('sa.member.check_delete_tag', {}, data, function (response) {
                if (response.success) {
                    this.error = null;
                    if (response.can_delete) {
                        this.tags[index].delete = true;
                    } else if (!response.can_delete) {
                        this.tags[index].cannot_delete = true;
                    }
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
        deleteTag(index) {
            if (this.tags[index].id != "New") {
                this.tags_to_delete.push(this.tags[index].id);
            }
            this.tags.splice(index, 1)
        },
    }
});