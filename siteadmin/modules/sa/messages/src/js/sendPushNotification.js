$(window).load(function() {
    const app = new Vue({
        el: '#push-notification-container',
        data: {
            title: null,
            message: null,
            isLoading: false,
            errorMessage: null,
            successMessage: null,
        },
        methods: {
            sendNotification: function() {
                this.isLoading = true;
                
                modRequest.request('siteadmin.notification.queue', null, { title: this.title, message: this.message }, function(response) {
                    if(response.data.success === true) {
                        this.errorMessage = null;
                        this.successMessage = 'Push notification was successfully queued.';
                    } else {
                        this.errorMessage = 'Sorry, the notification was not sent. Check the configuration, then try again.';
                    }
                    
                    this.isLoading = false;
                }.bind(this), function() {
                    this.successMessage = null;
                    this.errorMessage = 'Sorry, the notification was not sent. Check the configuration, then try again.';
                    this.isLoading = false;
                }.bind(this));
            }
        },
        computed: {
            isSubmitDisabled: function() {
                var formDisabled = false;

                if(this.title == null || this.title.length > 1024 || this.title.length === 0) {
                    formDisabled = true;
                }

                if(this.message == null || this.message.length > 1024 || this.message.length === 0) {
                    formDisabled = true;
                }
                
                if(this.isLoading) {
                    formDisabled = true;
                }

                return formDisabled;
            }
        }
    });
});