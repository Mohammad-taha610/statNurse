Vue.component('drug-screen', {
template: /*html*/`
<div>

<div v-show="drug_screen.status == 'pending license' || !drug_screen.status || drug_screen.status == ''">

    <h3 style="color: white; font-size: 18px;">Nurse License/s are being verified</h3>

    <p><br>Our team is verifying your nurse license information. You will be notified via sms what your next steps will be at the conclusion of this process.<br></p>

</div>

<div v-show="drug_screen.status == 'pending drug screen'">

    <h3 style="color: white; font-size: 18px;">Please complete drug screen process</h3>

    <p><br>Upon recieving drug screen results you will be notified via sms.<br></p>

</div>

</div>
`,
watch: {},
computed: {},
created() {},
props: {

    application_id: {
        
        type: [Number, String],
        default: 0
    },
    drug_screen: Object,
},
data: () => ({}),
methods: {
},
});