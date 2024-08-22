window.addEventListener('load', (number) => {
Vue.component('create-login', {
template: /*html*/`
<validation-observer
    ref="observer"
    v-slot="{ invalid }"
>

<v-overlay
    v-show="loading"
>
    <v-progress-circular
        indeterminate
        size="64"
    ></v-progress-circular>
</v-overlay>

<v-text-field
    v-model="first_name"
    label="First Name *"
    ref="first_name"
    outlined
    autocomplete="given-name"
    :rules="nameRules"
></v-text-field>

<v-text-field
    v-model="middle_name"
    label="Middle Name"
    outlined
    autocomplete="additional-name"
    :rules="nameRules"
></v-text-field>

<v-text-field
    v-model="last_name"
    label="Last Name *"
    ref="last_name"
    outlined
    autocomplete="family-name"
    :rules="nameRules"
></v-text-field>

<v-text-field
    v-model="username"
    label="Email Address / Username"
    ref="username"
    outlined
    hint="This will be used to login to the application portal."
    persistent-hint
></v-text-field>

<v-text-field
    v-model="password"
    label="Password"
    ref="password"
    outlined
    type="password"
></v-text-field>

<v-text-field
    v-model="password_confirmation"
    label="Confirm Password"
    ref="password_confirmation"
    outlined
    type="password"
></v-text-field>

<div style="display: flex; flex-direction: row; justify-content: flex-end; align-items: flex-end; margin-bottom: 25px;">
<v-btn
    color="#C62828"
    @click="createLogin"
    right
>Create Login</v-btn>
</div>

</validation-observer>
`,
props: {

    message: String,
    color: String,
    timeout: Number,
},
watch: {},
computed: {},
created() {},
data: () => ({

    loading: false,
    
    first_name: '',
    middle_name: '',
    last_name: '',
    username: '',
    password: '',
    password_confirmation: '',
    nameRules: [ v => v.length <= 50 || 'Max 50 characters' ],
}),
methods: {

    createLogin() {

        this.loading = true;

        if (this.first_name === '') {

            this.showSnackbar('First name is required', 'red', 5000)
            this.$refs.first_name.focus();
            this.loading = false;
            return;
        }

        if (this.first_name.length > 50) {

            this.showSnackbar('First name must be less than 50 characters', 'red', 5000)
            this.$refs.first_name.focus();
            this.loading = false;
            return;
        }

        if (this.middle_name.length > 50) {

            this.showSnackbar('Middle name must be less than 50 characters', 'red', 5000)
            this.$refs.middle_name.focus();
            this.loading = false;
            return;
        }

        if (this.last_name === '') {

            this.showSnackbar('Last name is required', 'red', 5000)
            this.$refs.last_name.focus();
            this.loading = false;
            return;
        }

        if (this.last_name.length > 50) {

            this.showSnackbar('Last name must be less than 50 characters', 'red', 5000)
            this.$refs.last_name.focus();
            this.loading = false;
            return;
        }

        if (this.username === '' || this.username === null) {

            this.showSnackbar('Email address is required', 'red', 5000)
            this.$refs.username.focus();
            this.loading = false;
            return;
        }

        let $emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!$emailPattern.test(this.username)) {

            this.showSnackbar('Email address is invalid', 'red', 5000)
            this.$refs.username.focus();
            this.loading = false;
            return;
        }

        // password must be 8 characters long
        if (this.password.length < 8) {
            
            this.showSnackbar('Password must be at least 8 characters long', 'red', 5000)
            this.$refs.password.focus();
            this.loading = false;
            return;
        }

        // password and password_confirmation must match
        if (this.password !== this.password_confirmation) {
            
            this.showSnackbar('Passwords do not match', 'red', 5000)
            this.$refs.password_confirmation.focus();
            this.loading = false;
            return;
        }

        data = {
        
            first_name: this.first_name,
            middle_name: this.middle_name,
            last_name: this.last_name,

            username: this.username,
            password: this.password,
        }
        
        modRequest.request('nurse.application.createLogin', null, data, function(response) {
            if (response.success) {

                if (response.message == "Email already in use") {

                    this.showSnackbar('Email already in use. Please login with email.', 'red', 5000);
                    this.$refs.username.focus();
                    this.loading = false;
                    return;
                }

                this.$emit('login-user', {
        
                    first_name: this.first_name,
                    middle_name: this.middle_name,
                    last_name: this.last_name,
        
                    username: this.username,
                    password: this.password,

                    // application_id: response.login.application_id,
                });        
            }
        }.bind(this));

    },
    showSnackbar(message, color, timeout) {

        this.$emit('show-snackbar', {
            message,
            color,
            timeout
        })
    }
},
})});