Vue.component('provider-files-view', {
    // language=HTML
    template:
        /*html*/`
                <v-app>
                    <v-container>
                        <v-overlay :value="loading" absolute color="#eee">
                            <v-progress-circular
                                    active
                                    indeterminate
                                    :size="100"
                                    color="primary"></v-progress-circular>
                        </v-overlay>
                        <v-row>
                            <file-uploader
                                    :config="upload_config"
                                    v-on:fileUploaded="handleUploaded($event)"
                            >
                            </file-uploader>
                        </v-row>
                        <v-row>
                            <a v-for="file in files" :href="file.route" target="_blank" class="mt-3 mr-3">
                                <v-card class="member-file-card">
                                    <v-card-text>
                                        <div class="member-file-icons">
                                            <v-menu
                                                    bottom
                                                >
                                                <template v-slot:activator="{ on, attrs }">
                                                    <v-btn 
                                                            v-on="on"
                                                            v-bind="attrs"
                                                            icon
                                                            @click.prevent
                                                    >
                                                        <v-icon color="primary">mdi-tag</v-icon>
                                                    </v-btn>
                                                </template>
                                                <v-list dense ref="list">
                                                    <v-list-item 
                                                            v-for="tag in tags"
                                                            @click="changeTag(file, tag)">
                                                        <v-list-item-title 
                                                                class=""
                                                                :class="'member-file-tag ' + (tag == file.tag ? 'selected-tag' : '')">{{tag.name}}</v-list-item-title>
                                                    </v-list-item>
                                                    <v-dialog max-width="400">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-list-item
                                                                    v-on="on"
                                                                    v-bind="attrs"
                                                            ><v-list-item-title class="member-file-tag">
                                                                <v-icon>mdi-plus</v-icon> Add Tag
                                                            </v-list-item-title></v-list-item>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                    color="primary"
                                                                    class="text-h4 white--text"
                                                                >Add File Tag</v-toolbar>
                                                                <v-card-text
                                                                    class="pt5"
                                                                >
                                                                    <v-text-field 
                                                                            v-model="new_tag_name"
                                                                            label="Tag Name"
                                                                    ></v-text-field>
                                                                    <v-text-field
                                                                            v-model="new_tag_description"
                                                                            label="Tag Description"
                                                                    ></v-text-field>
                                                                    
                                                                </v-card-text>
                                                                <v-card-actions>
                                                                    <v-spacer></v-spacer>
                                                                    <v-btn
                                                                        color="light"
                                                                        v-on:click="dialog.value = false"
                                                                    >Cancel</v-btn>
                                                                    <v-btn
                                                                        color="primary"
                                                                        v-on:click="createTag(file); dialog.value = false;"
                                                                        class="white--text"
                                                                    >Create</v-btn>
                                                                </v-card-actions>        
                                                            </v-card>
                                                        </template>
                                                    </v-dialog>
                                                </v-list>
                                            </v-menu>
                                            <v-btn icon @click.prevent="changes_exist = true; show_warning = true; deleteFile(file);">
                                                <v-icon color="red">mdi-trash-can-outline</v-icon>
                                            </v-btn>
                                        </div>
                                        <div class="member-file-icon">
                                            <v-icon color="primary">mdi-file</v-icon>
                                        </div>
                                        <div class="member-file-name-container">
                                            <span class="member-file-name">{{file.filename}}</span>
                                        </div>
                                        <div class="member-file-tag-container mt-1">
                                            <span class="member-file-tag-name">{{file.tag.name}}</span>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </a>
                        </v-row>
                        <v-row>
                            <span v-if="show_warning" class="mt-3 member-file-warning">*IMPORTANT* Changes will not be final until saved</span>
                        </v-row>
                        <v-row>
                            <v-btn
                                    v-if="changes_exist"
                                    color="primary"
                                    x-large
                                    class="mt-3"
                                    @click="saveFileChanges">
                                Save File Changes
                            </v-btn>
                        </v-row>
                    </v-container>
                    <!-- <v-container fluid>
                        <h4 class="header primary--text bolder smaller">File Types Visible in Provider Portal</h4>
                        <v-container fluid>
                            <v-checkbox v-for="(tag, index) in nurse_file_tags"
                                v-model="selected_tags"
                                :label="tag.file_name"
                                :value="tag.id"
                            ></v-checkbox>
                        </v-container
                                cols="12"
                                sm="4"
                                md="4">
                        <v-btn
                          depressed
                          color="primary"
                          @click="commitTags"
                        >
                          Save Tags
                        </v-btn>
                    </v-container> -->
                </v-app>
            `,
    props: [
        'id',
        'member-type'
    ],
    data: function() {
        return {
            new_tag_name: '',
            new_tag_description: '',
            upload_config: {
                'id': 'provider-file-uploader',
                'multiple': true,
                'upload_route': '/files/upload',
                'chunk_size' : 1000000,
                'color': 'primary'
            },
            files: [],
            changes_exist: false,
            show_warning: false,
            loading: false,
            tags: [
                "Driver's License",
                "Social Security Card",
                "Covid Vaccine",
                "TB Skin Test",
                "Drug Screen",
                "Background Test",
                "Nursing License",
                "CPR/BLS Card",
                "Other"
            ],
            nurse_file_tags: [],
            selected_tags: []
        };
    },
    created() {
    },
    mounted() {
        if (this.memberType === 'Provider') {
            this.loadProviderFiles();
        }
        if (this.memberType === 'Provider') {
            this.loadTags();
        }
    },
    computed: {

    },
    methods: {
        loadProviderFiles() {
            var data = {
                id: this.id
            }
            this.loading = true;
            modRequest.request('sa.member.load_provider_files', {}, data, function(response) {
                this.files = [];
                this.tags = [];
                if(response.success) {
                    for (var i = 0; i < response.files.length; i++) {
                        var file = response.files[i];
                        this.files.push({
                            id: file.id,
                            filename: file.filename,
                            route: file.route,
                            tag: file.tag
                        });
                    }
                    for (var i = 0; i < response.tags.length; i++) {
                        var tag = response.tags[i];
                        this.tags.push({
                            id: tag.id,
                            name: tag.name,
                            description: tag.description
                        });
                    }
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        loadTags() {
            var data = {
                id: this.id
            }
            this.loading = true;
            modRequest.request('sa.member.load_provider_filetags', {}, data, function(response) {
                this.nurse_file_tags = [];
                var selectedTags = [];
                // var file_selected = false;
                if(response.success) {
                    for (var j = 0; j < response.provider_tags.length; j++) {
                        selectedTags.push(response.provider_tags[j].id);
                        console.log(response.provider_tags.id)
                    }
                    for (var i = 0; i < response.nurse_tags.length; i++) {
                        var file = response.nurse_tags[i];
                        if (selectedTags.includes(file.id)) {
                            this.selected_tags.push(file.id);
                            console.log("true")
                        }
                        this.nurse_file_tags.push({
                            id: file.id,
                            file_name: file.name,
                            description: file.description
                        });
                    }
                    console.log(selectedTags)
                    console.log(this.selected_tags)
                    console.log(this.nurse_file_tags)
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        commitTags() {
            var data = {
                id: this.id,
                selected_tags: this.selected_tags
            }
            this.loading = true;
            modRequest.request('sa.member.save_provider_filetags', {}, data, function(response) {
                this.nurse_file_tags = [];
                this.selected_tags = [];
                if(response.success) {
                    //         CHANGE TO SAVE AT BOTTOM USING EMIT AND SAVED ALERT
                    //         THIS BUTTON SHOULD COMMIT CHANGES FOR BELOW SAVE BUTTON
                    this.loadTags();
                    this.loading = false;
                } else {
                    console.log('Error');
                    console.log(response);
                    this.loading = false;
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        commitCustomTags() {},
        saveTags() {},
        handleUploaded(e) {
            this.files.push({
                id: e.id,
                filename: e.name,
                route: e.url,
                tag: {},
            });
            this.changes_exist = true;
            this.show_warning = true;
        },
        saveFileChanges() {
            var file_ids = [];
            var file_tags = [];
            for (var i = 0; i < this.files.length; i++) {
                file_ids.push(this.files[i].id);
                file_tags.push({
                    file_id: this.files[i].id,
                    tag: {
                        id: this.files[i].tag.id,
                        name: this.files[i].tag.name,
                        description: this.files[i].tag.description
                    }
                });
            }
            var data = {
                id: this.id,
                file_ids: file_ids,
                file_tags: file_tags
            }

            this.loading = true;
            modRequest.request('sa.member.save_provider_files', {}, data, function(response) {
                if(response.success) {
                    this.loading = false;
                    this.show_warning = false;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function(response) {
                console.log('Failed');
                console.log(response);
            });
        },
        deleteFile(file) {
            this.files.splice(this.files.indexOf(file), 1);
        },
        changeTag(file, tag) {
            if(file.tag != tag) {
                this.changes_exist = true;
                this.show_warning = true;
                file.tag = tag;
            }
        },
        createTag(file) {
            var newTag = {
                id: 0,
                name: this.new_tag_name,
                description: this.new_tag_description
            };
            this.tags.push(newTag);
            file.tag = newTag;
            this.new_tag_name = '';
            this.new_tag_description = '';
            this.show_warning = true;
            this.changes_exist = true;
            //
            // this.$refs.list[0].$emit('input', false)
        }
    }
});
