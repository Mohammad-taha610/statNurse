Vue.component('nst-overlay', {
    // language=HTML
    template:
    `
        <v-overlay :value="loading" absolute color="#eee">
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
            
        };
    },
    created() {

    },
    mounted() {

    },
    computed: {

    },
    methods: {

    }
});