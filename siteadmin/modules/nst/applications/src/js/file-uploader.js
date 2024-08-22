Vue.component('file-uploader', {
    template:
        /*html*/`
            <v-btn
                :color="color"
                @click.stop="triggerUpload">
                {{button_text}}
                <input 
                    type="file" 
                    :id="id"
                    :multiple="multiple"
                    :disabled="disabled"
                    @change="uploadFile($event)"
                    style="display: none;">
            </v-btn>
    `,
    props: [
        'config',
    ],
    emits: [
        'customUpload'
    ],
    data: function () {
        return {
            upload_route: '/files/upload',
            id: '',
            name: '',
            multiple: false,
            disabled: false,
            chunk_size: 1000000,
            file: null,
            import_errors: [],
            response: {},
            file_id: 0,
            button_text: 'Upload File',
            color: 'primary',
            fileTag: ''
        };
    },
    created() {
        this.id = this.config.id;
        this.multiple = this.config.multiple;
        this.upload_route = this.config.upload_route;
        this.chunk_size = this.config.chunk_size;
        this.color = this.config.color != undefined && this.config.color.length > 0 ? this.config.color : 'primary';
        this.button_text = this.config.button_text != undefined && this.config.button_text.length > 0 ? this.config.button_text : 'Upload File';
        this.formData = new FormData();
        this.fileTag = this.config.fileTag;
    },
    mounted() {},
    methods: {
        triggerUpload() {
            document.getElementById(this.id).click();
        },
        uploadFile(event) {
            this.import_errors = [];

            if (event.target.files.length > 1 && this.multiple) {
                for (var f = 0; f < event.target.files.length; f++) {
                    this.doUpload(event.target.files[f]);
                }
            } else {
                var file = event.target.files[0];
                this.doUpload(file);
            }
        },
        doUpload(file) {
            this.name = file.name;

            var formData = new FormData();

            var numChunks = parseInt(file.size / this.chunk_size);
            if (numChunks < (file.size / this.chunk_size)) {
                numChunks += 1;
            }

            // Split into chunks
            var i = 0;
            while (i < file.size) {
                var chunk = file.slice(i, i + this.chunk_size);
                var end = i + this.chunk_size - 1;
                if (end > file.size) {
                    end = file.size - 1;
                }
                formData.set('file', chunk, file.name)

                var request = new XMLHttpRequest();
                request.open('POST', this.upload_route, true);
                request.setRequestHeader('Content-Range', 'bytes ' + i + '-' + end + '/' + file.size);

                request.onload = function (e) {
                    if (request.readyState === 4) {
                        if (request.status !== 200) {
                            console.log('error: ', request.statusText);
                        } else {
                            this.response = JSON.parse(e.currentTarget.response);
                            if (this.response.files.is_completed_file) {
                                this.name = file.name; // needed to keep file.name for multiple files
                                this.file_id = this.response.files.id;
                            }
                        }
                    }
                }.bind(this);
                i += this.chunk_size;
                request.send(formData);
            }
        }
    },
    watch: {
        file_id: function (oldId, newId) {
            var data = {
                id: this.file_id,
                name: this.name,
                url: this.response.files.url,
                file: this.response.files,
                fileTag: this.fileTag
            };
            this.$emit('fileUploaded', data);
        }
    }
});
