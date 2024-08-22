Vue.component('nst-loading', {
    // language=HTML
    template:
    `
        <v-overlay :value="is_loading" absolute color="#eee">
            <v-progress-circular
                    active
                    indeterminate
                    :size="100"
                    color="primary"></v-progress-circular>
        </v-overlay>
    `,
    props: [
        'loading'
    ],
    data: function() {
        return {
            is_loading: false
        };
    },
    created() {
        this.is_loading = this.$props.loading;
        var self = this;
        EventBus.$on('start-loading', () => {
            self.is_loading = true;
        });
        EventBus.$on('end-loading', () => {
            self.is_loading = false;
        });
    },
    mounted() {

    },
    computed: {

    },
    methods: {

    },
});