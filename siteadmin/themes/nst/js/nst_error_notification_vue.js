Vue.component('nst-error-notification', {
    // language=HTML
    template:
    `
        <div v-if="is_shown" :class="'nst-error-notification ' + type">
            <label class="nst-error-header">{{headers[type]}}</label>
            <span class="nst-error-message">{{message}}</span>
        </div>
    `,
    props: [
        'error',
        'disappearing',
        'disappearing_time'
    ],
    data: function() {
        return {
            is_shown: true,
            headers: {
                'danger': 'Error',
                'warning': 'Warning'
            },
            type: '',
            message: '',
            disappearingTime: 5000
        };
    },
    created() {
        this.type = this.error.type;
        this.message = this.error.message;
        this.disappearingTime = this.disappearing_time;
        if(this.disappearing) {
            setTimeout(function() {
                this.is_shown = false;
            }.bind(this), this.disappearingTime)
        }
    },
    mounted() {

    },
    computed: {

    },
    methods: {

    }
});