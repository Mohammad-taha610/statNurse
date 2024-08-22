window.addEventListener('load', (number) => {
Vue.component('nurse-app-form-step5', {
template: /*html*/`
<validation-observer
    ref="observer"
    v-slot="{ invalid }"
>
    
    <h2 style="color: white; font-size: 24px; margin-bottom: 25px;">Emergency Contacts</h2>

    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Emergency Contact 1</h3>

        <div>

            <v-btn @click="emergency_contact1.show = !emergency_contact1.show">
                {{ emergency_contact1.show ? 'Hide Emergency Contact 1 Info' : 'Show Emergency Contact 1 Info' }}
            </v-btn>

            <v-btn @click="removeInfo(1)">Remove Info</v-btn>

        </div>

    </div>
    
    <div v-show="emergency_contact1.show">
    
        <validation-provider
            v-slot="{ errors }"
            name="First Name"
        >
            <v-text-field
                v-model="emergency_contact1.first_name"
                :rules="[() => !!emergency_contact1.first_name || 'This field is required']"
                label="First Name"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

        <validation-provider
            v-slot="{ errors }"
            name="Last Name"
        >
            <v-text-field
                v-model="emergency_contact1.last_name"
                :rules="[() => !!emergency_contact1.last_name || 'This field is required']"
                label="Last Name"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

        <validation-provider
            v-slot="{ errors }"
            name="Relationship"
        >
            <v-text-field
                v-model="emergency_contact1.relationship"
                :rules="[() => !!emergency_contact1.relationship || 'This field is required']"
                label="Relationship"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

        <validation-provider
            v-slot="{ errors }"
            name="Phone Number"
        >
            <v-text-field
                v-model="emergency_contact1.phone"
                :rules="[() => !!emergency_contact1.phone || 'This field is required']"
                label="Phone Number"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

    </div>

    <div style="display: flex; flex-direction: row; justify-content: space-between; align-items: center; margin-bottom: 25px;">

        <h3 style="color: white; font-size: 20px;">Emergency Contact 2</h3>

        <div>

            <v-btn @click="emergency_contact2.show = !emergency_contact2.show">
                {{ emergency_contact2.show ? 'Hide Emergency Contact 2 Info' : 'Show Emergency Contact 2 Info' }}
            </v-btn>

            <v-btn @click="removeInfo(2)">Remove Info</v-btn>

        </div>

    </div>
    
    <div v-show="emergency_contact2.show">
    
        <validation-provider
            v-slot="{ errors }"
            name="First Name"
        >
            <v-text-field
                v-model="emergency_contact2.first_name"
                :rules="[() => !!emergency_contact2.first_name || 'This field is required']"
                label="First Name"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

        <validation-provider
            v-slot="{ errors }"
            name="Last Name"
        >
            <v-text-field
                v-model="emergency_contact2.last_name"
                :rules="[() => !!emergency_contact2.last_name || 'This field is required']"
                label="Last Name"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

        <validation-provider
            v-slot="{ errors }"
            name="Relationship"
        >
            <v-text-field
                v-model="emergency_contact2.relationship"
                :rules="[() => !!emergency_contact2.relationship || 'This field is required']"
                label="Relationship"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

        <validation-provider
            v-slot="{ errors }"
            name="Phone Number"
        >
            <v-text-field
                v-model="emergency_contact2.phone"
                :rules="[() => !!emergency_contact2.phone || 'This field is required']"
                label="Phone Number"
                :error-messages="errors"
                outlined
            ></v-text-field>
        </validation-provider>

    </div>

</div>
</validation-observer>
`,

watch: {},
computed: {},
created() {},
data: () => ({

    emergency_contact1: {

        show: true,
        first_name: '',
        last_name: '',
        relationship: '',
        phone: '',
    },
    emergency_contact2: {

        show: false,
        first_name: '',
        last_name: '',
        relationship: '',
        phone: '',
    },
}),
methods: {

    removeInfo(index) {

        if (index === 1) {

            this.emergency_contact1.show = true;
            this.emergency_contact1.first_name = '';
            this.emergency_contact1.last_name = '';
            this.emergency_contact1.relationship = '';
            this.emergency_contact1.phone = '';
        }

        if (index === 2) {

            this.emergency_contact2.show = true;
            this.emergency_contact2.first_name = '';
            this.emergency_contact2.last_name = '';
            this.emergency_contact2.relationship = '';
            this.emergency_contact2.phone = '';
        }
    }
},
})});