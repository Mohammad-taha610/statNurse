Vue.component('nurse-files-view', {
    // language=HTML
    template:
        `
            <v-app>
                <v-container>
                    <v-overlay :value="loading" absolute color="#eee">
                        <v-progress-circular
                                active
                                indeterminate
                                :size="100"
                                color="primary"></v-progress-circular>
                    </v-overlay>
                    <template v-if="this.uploads_allowed">
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
                                                <v-list dense>
                                                    <v-list-item
                                                            v-for="tag in tags"
                                                            @click="changeTag(file, tag)">
                                                        <v-list-item-title
                                                                class=""
                                                                :class="'member-file-tag ' + (tag == file.tag ? 'selected-tag' : '')">
                                                            {{tag.name}}
                                                        </v-list-item-title>
                                                    </v-list-item>
                                                    <v-dialog max-width="400">
                                                        <template v-slot:activator="{ on, attrs }">
                                                            <v-list-item
                                                                    v-on="on"
                                                                    v-bind="attrs"
                                                            >
                                                                <v-list-item-title class="member-file-tag">
                                                                    <v-icon>mdi-plus</v-icon>
                                                                    Add Tag
                                                                </v-list-item-title>
                                                            </v-list-item>
                                                        </template>
                                                        <template v-slot:default="dialog">
                                                            <v-card>
                                                                <v-toolbar
                                                                        color="primary"
                                                                        class="text-h4 white--text"
                                                                >Add File Tag
                                                                </v-toolbar>
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
                                                                    >Cancel
                                                                    </v-btn>
                                                                    <v-btn
                                                                            color="primary"
                                                                            v-on:click="createTag(file); dialog.value = false; list_open = false;"
                                                                            class="white--text"
                                                                    >Create
                                                                    </v-btn>
                                                                </v-card-actions>
                                                            </v-card>
                                                        </template>
                                                    </v-dialog>
                                                </v-list>
                                            </v-menu>
                                            <v-btn icon
                                                   @click.prevent="changes_exist = true; show_warning = true; deleteFile(file);">
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
                    </template>
                    <template v-else>
                        <v-row>
                            <a v-for="file in files" :href="file.route" target="_blank" class="mt-3 mr-3">
                                <template v-if="tagExists(file.tag.name)">
                                    <v-card class="member-file-card">
                                        <v-card-text>
                                            <div class="member-file-icon">
                                                <v-icon color="primary">mdi-file</v-icon>
                                            </div>
                                            <div class="member-file-tag-container mt-1">
                                                <span class="member-file-tag-name">{{file.tag.name}}</span>
                                            </div>
                                        </v-card-text>
                                    </v-card>
                                </template>
                            </a>
                        </v-row>
                    </template>
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
            </v-app>
        `,
    props: [
        'id',
        'uploads_allowed',
        'member-type',
        'files-route'
    ],
    data: function () {
        return {
            provider_requires_covid_vaccine: false,
            new_tag_name: '',
            new_tag_description: '',
            upload_config: {
                'id': 'nurse-file-uploader',
                'multiple': true,
                'upload_route': '/files/upload',
                'chunk_size': 1000000,
                'color': 'primary',
                'button_text': 'Upload File',
            },
            files: [],
            changes_exist: false,
            show_warning: false,
            loading: false,
            tags: [
                "TB Skin Test",
                "Nursing License",
                "Background Check",
                "Drug Screen",
                "CPR Card",
                "BLS Card",
                "Drivers License",
                "Social Security Card",
                "Nursing License",
                "i9",
                "Covid Vaccine Card",
                "Covid Vaccine Exemption",
                "Employee Profile"
            ],
            fileTags: [
                "TB Skin Test",
                "Nursing License",
                "Background Check",
                "Drug Screen",
                "CPR Card",
                "BLS Card",
                "Drivers License",
                "Social Security Card",
                "Nursing License",
                "i9",
                "Covid Vaccine Card",
                "Covid Vaccine Exemption",
                "Employee Profile",
                "Kentucky Adult Misconduct Registry"
            ],
            frontendTags: [
                "TB Skin Test",
                "Nursing License",
                "Background Check",
                "Drug Screen",
                "CPR Card",
                "BLS Card",
                "Nursing License",
                "i9",
                "Covid Vaccine Card",
                "Covid Vaccine Exemption",
                "Employee Profile"
            ],
        };
    },
    created() {
    },
    mounted() {
        if (window.location.pathname.indexOf('/nurses/profile') > -1) {
            this.tags = this.frontendTags;
        }

        this.loadNurseFiles();
    },
    computed: {},
    methods: {
        tagExists(tagName) {
            return this.fileTags.includes(tagName);
        },
        loadNurseFiles() {
            let data = {
                id: this.id
            }
            this.loading = true;
            let loadNurseFilesRoute = 'sa.member.load_nurse_files';
            if(this.filesRoute){
                loadNurseFilesRoute = this.filesRoute;
            }
            modRequest.request(loadNurseFilesRoute, {}, data, function (response) {
                let vm = this;
                this.files = [];
                this.tags = [];
                if (response.success) {
                    for (let i = 0; i < response.files.length; i++) {
                        let file = response.files[i];
                        //console.log("file: ", file);
                        this.files.push({
                            id: file.id,
                            filename: file.filename,
                            route: file.route,
                            tag: file.tag
                        });
                    }
                    // filter tags for Covid Vaccine tag
                    // if (response.providers) {
                    //     if (response.providers.length > 1) {
                    //         response.providers.forEach(function (e) {
                    //             console.log(e);
                    //             if (e.provider_requires_covid_vaccine) {
                    //                 vm.provider_requires_covid_vaccine = true;
                    //                 console.log('At least one of the nurse\'s providers require a covid vaccine');
                    //             }
                    //         });
                    //     } else if (response.providers.length === 1) {
                    //         response.providers[0].provider_requires_covid_vaccine = true;
                    //         console.log('At least one of the nurse\'s providers require a covid vaccine');
                    //     }
                    //     if (!vm.provider_requires_covid_vaccine) {
                    //         this.tags.splice(this.tags.indexOf('Covid Vaccine Card'), 1);
                    //         this.tags.splice(this.tags.indexOf('Covid Vaccine Exemption'), 1);
                    //         console.log('Removed Covid Vaccine tags');
                    //     }
                    // }
                    // end filter
                    for (let i = 0; i < response.tags.length; i++) {
                        let tag = response.tags[i];
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
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
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
            let file_ids = [];
            let file_tags = [];
            for (let i = 0; i < this.files.length; i++) {
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
            let data = {
                id: this.id,
                file_ids: file_ids,
                file_tags: file_tags
            }

            this.loading = true;
            modRequest.request('sa.member.save_nurse_files', {}, data, function (response) {
                if (response.success) {
                    this.loading = false;
                    this.show_warning = false;
                } else {
                    console.log('Error');
                    console.log(response);
                }
            }.bind(this), function (response) {
                console.log('Failed');
                console.log(response);
            });
        },
        deleteFile(file) {
            this.files.splice(this.files.indexOf(file), 1);
        },
        changeTag(file, tag) {
            if (file.tag !== tag) {
                this.changes_exist = true;
                this.show_warning = true;
                file.tag = tag;
            }
        },
        createTag(file) {
            let newTag = {
                id: 0,
                name: this.new_tag_name,
                description: this.new_tag_description
            };
            if (this.fileTags.includes(this.new_tag_name)) {
                this.tags.push(newTag);
                file.tag = newTag;
                this.new_tag_name = '';
                this.new_tag_description = '';
                this.show_warning = true;
                this.changes_exist = true;
            } else {
                console.log("File Tag NOT created because it doesn't exist in fileTags array: ", this.fileTags, newTag);
                this.new_tag_name = '';
                this.new_tag_description = '';
                this.show_warning = true;
                this.changes_exist = false;
            }
        }
    }
});
