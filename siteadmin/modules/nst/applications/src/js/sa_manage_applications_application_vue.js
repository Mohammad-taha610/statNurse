Vue.component('sa-application-application', {
template: /*html*/`
<div style="margin-top: 50px;">

<h2>Job History</h2>
<v-row>
    <v-col cols="6">
        <v-text-field
            v-model="application.currently_employed"
            label="Currently Employed"
            disabled
        ></v-text-field>
    </v-col>
    <v-col cols="6">
        <v-text-field
            v-model="application.one_year_ltc_experience"
            label="One Year Long Term Care Experience"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col cols="12">
        <v-textarea
            v-model="application.one_year_experience_explanation"
            label="One Year Experience Explanation"
            disabled
        ></v-textarea>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Company 1</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.name"
            label="Company Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.supervisor_name"
            label="Supervisor Name"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.address"
            label="Address"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.zipcode"
            label="Zipcode"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.phone"
            label="Phone"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.email"
            label="Email"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.job_title"
            label="Job Title"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.start_date"
            label="Start Date"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.end_date"
            label="End Date"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.responsibilities"
            label="Responsibilities"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company1.reason_for_leaving"
            label="Reason for Leaving"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company1.may_we_contact_employer"
            label="May We Contact Employer"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<div v-show="application.company2.name">
<h2 style="margin-top: 75px;">Company 2</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.name"
            label="Company Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.supervisor_name"
            label="Supervisor Name"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.address"
            label="Address"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.zipcode"
            label="Zipcode"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.phone"
            label="Phone"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.email"
            label="Email"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.job_title"
            label="Job Title"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.start_date"
            label="Start Date"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.end_date"
            label="End Date"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.responsibilities"
            label="Responsibilities"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company2.reason_for_leaving"
            label="Reason for Leaving"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company2.may_we_contact_employer"
            label="May We Contact Employer"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
</div>

<div v-show="application.company3.name">
<h2 style="margin-top: 75px;">Company 3</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.name"
            label="Company Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.supervisor_name"
            label="Supervisor Name"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.address"
            label="Address"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.zipcode"
            label="Zipcode"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.phone"
            label="Phone"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.email"
            label="Email"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.job_title"
            label="Job Title"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.start_date"
            label="Start Date"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.end_date"
            label="End Date"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.responsibilities"
            label="Responsibilities"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.company3.reason_for_leaving"
            label="Reason for Leaving"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.company3.may_we_contact_employer"
            label="May We Contact Employer"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
</div>

<h2 style="margin-top: 75px;">Education</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.hs_or_ged"
            label="High School or GED"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h3 style="margin-top: 50px;">High School</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.high_school.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.high_school.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.high_school.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.high_school.year_graduated"
            label="Year Graduated"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h3 style="margin-top: 50px;">GED</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.ged.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.ged.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.ged.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.ged.year_graduated"
            label="Year Graduated"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h3 style="margin-top: 50px;">College</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.college.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.college.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.college.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.college.year_graduated"
            label="Year Graduated"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.college.subjects_major_degree"
            label="Subjects / Major / Degree"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h3 style="margin-top: 50px;">Other Education</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.other.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.other.city"
            label="City"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.other.state"
            label="State"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.other.year_graduated"
            label="Year Graduated"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.other.subjects_major_degree"
            label="Subjects / Major / Degree"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">References</h2>

<h3 style="margin-top: 50px;">Reference 1</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.reference1.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.reference1.relationship"
            label="Relationship"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.reference1.company"
            label="Company"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.reference1.phone"
            label="Phone Number"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<div v-show="application.reference2.name">
<h3 style="margin-top: 50px;">Reference 2</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.reference2.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.reference2.relationship"
            label="Relationship"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.reference2.company"
            label="Company"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.reference2.phone"
            label="Phone Number"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
</div>

<div v-show="application.reference3.name">
<h3 style="margin-top: 50px;">Reference 3</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.reference3.name"
            label="Name"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.reference3.relationship"
            label="Relationship"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.reference3.company"
            label="Company"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.reference3.phone"
            label="Phone Number"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
</div>

<h3 style="margin-top: 50px;">Licenses and Certifications</h3>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.licenses_and_certifications"
            label="Licenses and Certifications"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Medical History</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.medical_history"
            label="Positive Medical History"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.injury_explanation"
            label="Injury Explanation"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.routine_vaccinations"
            label="Routine Vaccinations"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.hepatitis_b"
            label="Hepatitis B"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.hepatitis_a"
            label="Hepatitis A"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.covid_19"
            label="Covid 19"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.covid_19_exemption"
            label="Covid 19 Exemption"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.positive_tb_screening"
            label="Positive TB Screening"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.positive_tb_date"
            label="Positive TB Date"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.xray"
            label="X-Ray"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.xray_date"
            label="X-Ray Date"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Payment Information</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.pay_type"
            label="Payment Type"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.account_type"
            label="Account Type"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.account_number"
            label="Account Number"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.routing_number"
            label="Routing Number"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.bank_name"
            label="Bank Name"
            disabled
        ></v-text-field>
    </v-col>
</v-row>

<h2 style="margin-top: 75px;">Heard About Us</h2>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.heard_about_us"
            label="Heard About Us"
            disabled
        ></v-text-field>
    </v-col>
    <v-col>
        <v-text-field
            v-model="application.heard_about_us_other"
            label="Heard About Us Other Explanation"
            disabled
        ></v-text-field>
    </v-col>
</v-row>
<v-row>
    <v-col>
        <v-text-field
            v-model="application.referrer"
            label="Referrer"
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
    application: Object,
},
data: () => ({}),
methods: {
},
});