Vue.component('nurse-app-document-upload', {
    // language=HTML
    template:
        /*html*/`
            <v-app>
                <v-container style="margin: 10px;">
                    <v-overlay :value="loading" absolute color="#eee">
                        <v-progress-circular
                                active
                                indeterminate
                                :size="100"
                                color="primary"></v-progress-circular>
                    </v-overlay>
                    <template v-if="uploads_allowed">
                        <v-row>
                            <file-uploader
                                :config="upload_config"
                                v-on:fileUploaded="handleUploaded($event)"
                            >
                            </file-uploader>
                        </v-row>
                        <v-row>
                            <a v-for="(file, index) in tagFiles" :href="file.route" target="_blank" class="mt-3 mr-3">
                                <v-card class="member-file-card">
                                    <v-card-text>
                                        <div class="member-file-icons">
                                            <!--<v-menu
                                                    ref="showTagMenus"
                                                    :id="'menu'+index"
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
                                                        @click="changeTag(file, tag)"
                                                        :key="tag"
                                                    >
                                                        <v-list-item-title
                                                                class=""
                                                                :class="'member-file-tag ' + (tag == file.tag ? 'selected-tag' : '')">
                                                            {{tag}}
                                                        </v-list-item-title>
                                                    </v-list-item>
                                                </v-list>
                                            </v-menu>-->
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
                                            <span class="member-file-tag-name">{{tag}}</span>
                                        </div>
                                    </v-card-text>
                                </v-card>
                            </a>
                        </v-row>
                    </template>
                </v-container>
            </v-app>
        `,
    /*props: [
        'uploads_allowed'
    ],*/
    props: {
        button_text: String,
        uploads_allowed: Boolean,
        tag: String,
        fileTag: String,
        files: Array
    },
    data: function () {
        return {
            provider_requires_covid_vaccine: false,
            new_tag_name: '',
            new_tag_description: '',
            upload_config: {
                'id': this.fileTag,
                'multiple': true,
                'upload_route': '/files/upload',
                'chunk_size': 1000000,
                'color': 'primary',
                'button_text': this.button_text,
                'fileTag': this.fileTag
            },
            tagFiles: [],
            changes_exist: false,
            show_warning: false,
            loading: false,
            /*tags: [
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
                "Covid Vaccine Exemption"
            ]*/
        };
    },
    created() {
        // check if local storage has files, if so load them (on saved)
        // const form = localStorage.getItem('form');
        // if (form) {
        //     console.log('form.files exists.. attempting to populate');
        //     this.files = JSON.parse(form).files;
        // }
    },
    // watch: {
    //     files
    // },
    mounted() {
        this.tagFiles = this.files;
    },
    computed: {},
    methods: {
        tagExists(tagName) {
            return this.tags.includes(tagName);
        },
        handleUploaded(e) {
            file = {
                id: e.id,
                filename: e.name,
                route: e.url,
                fileTag: e.fileTag,
                tag: this.tag
            };
            this.tagFiles.push(file);
            this.tagFiles.pop();
            this.changes_exist = true;
            this.show_warning = true;
            this.$emit('uploaded', file, this.fileTag);
        },
        saveFileChanges() {
            console.log("moved to form submission");
        },
        deleteFile(file) {
            // ACTUALLY DELETE FILE NOW
            console.log(file);
            modRequest.request('nurse.application.delete_application_file', null, file, (res) => {
                if (res.success) {
                    console.log(res);
                    this.$emit('deletedFile', file, this.fileTag);
                    this.files.splice(this.files.indexOf(file), 1);              
                } else {
                    toastr.warning('Error removing file', 'Something went wrong, please try again.');
                }
            }, (res) => {
                toastr.error('Error', 'Something went wrong, please try again.');
            })
        },
        changeTag(file, tag) {
            if (file.tag !== tag) {
                this.changes_exist = true;
                this.show_warning = true;
                file.tag = this.fileTag;
            }
        },
        createTag(file) {
            let newTag = {
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
        }
    }
});
