Vue.component('sa-application-basic-info', {
template: /*html*/`
<div style="margin-top: 50px;">

<h2>Full Name</h2>
<v-row>
    <v-col cols="4">
        <v-text-field
            v-model="basic_info.first_name"
            label="First Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col cols="4">
        <v-text-field
            v-model="basic_info.middle_name"
            label="Middle Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col cols="4">
        <v-text-field
            v-model="basic_info.last_name"
            label="Last Name"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Contact Info</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="basic_info.email"
            label="Email"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="basic_info.phone"
            label="Phone Number"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Address</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="basic_info.street_address"
            label="Street Address"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="basic_info.street_address_2"
            label="Street Address 2"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col cols="4">
        <v-text-field
            v-model="basic_info.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
    <v-col cols="4">
        <v-text-field
            v-model="basic_info.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col cols="4">
        <v-text-field
            v-model="basic_info.zipcode"
            label="Zipcode"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Personal Info</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="basic_info.dob"
            label="Date of Birth"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="basic_info.ssn"
            label="Social Security Number"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="basic_info.citizen_of_us"
            label="Citizen of US"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="basic_info.authorized_to_work_in_us"
            label="Authorized to Work in US"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Position</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="basic_info.position"
            label="Position"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="basic_info.explanation"
            label="Explanation"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

</div>
`,
watch: {},
computed: {},
created() {},
props: {
    basic_info: Object,
},
data: () => ({}),
methods: {
},
});