Vue.component('sa-application-files', {
template: /*html*/`
<div>

<h2 style="margin-top: 50px;">Required Files</h2>
<v-row>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <h4>Driver License</h4>

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.driver_license.url"
            target="_blank"
            v-show="files.driver_license.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.driver_license.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.driver_license.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <h4>Social Security</h4>

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.social_security.url"
            target="_blank"
            v-show="files.social_security.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.social_security.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.social_security.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <h4>TB Skin Test</h4>

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.tb_skin_test.url"
            target="_blank"
            v-show="files.tb_skin_test.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.tb_skin_test.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.tb_skin_test.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>
</v-row>

<h2 style="margin-top: 50px;">Optional Files</h2>
<v-row>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <h4>CPR Card</h4>

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.cpr_card.url"
            target="_blank"
            v-show="files.cpr_card.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.cpr_card.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.cpr_card.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <h4>BLS ACL</h4>

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.bls_acl.url"
            target="_blank"
            v-show="files.bls_acl.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.bls_acl.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.bls_acl.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <h4>Covid Vaccine</h4>

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.covid_vaccine.url"
            target="_blank"
            v-show="files.covid_vaccine.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.covid_vaccine.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.covid_vaccine.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>
</v-row>

<h2 style="margin-top: 50px;">ID Badge</h2>
<v-row>

    <v-col cols="12" md="2" style="margin-top: 15px;">

        <v-card
            class="member-file-card"
            style="margin-top: 20px;"
            color="#ECEFF1"
            :href="files.id_badge.url"
            target="_blank"
            v-show="files.id_badge.name"
        >
            <v-card-text>

                <div class="member-file-icon" style="margin-bottom: 20px;">
                    <v-icon color="#4FC3F7">mdi-file</v-icon>
                </div>
                <div class="member-file-name-container">
                    <span class="member-file-name" style="color: #212121;">{{ files.id_badge.name }}</span>
                </div>
                <div class="member-file-tag-container mt-1">
                    <span class="member-file-tag-name" style="color: #0277BD;">{{ files.id_badge.fileTag }}</span>
                </div>

            </v-card-text>
        </v-card>
    </v-col>
</v-row>
</div>
`,
watch: {},
computed: {},
created() {},
props: {

    files: Object,
    application_id: Number,
},
data: () => ({}),
methods: {
},
});