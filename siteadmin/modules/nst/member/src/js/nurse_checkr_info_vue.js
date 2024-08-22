Vue.component("nurse-checkr-info-view", {
	// language=HTML
	template: `
            <v-app>
                <v-container>
                    <nst-overlay :loading="loading"></nst-overlay>
                    <nst-error-notification
                            v-if="error"
                            :error="error"></nst-error-notification>
                    <v-row>
                        <label>Checkr Pay ID</label>
                    </v-row>
                    <v-row>
                        <v-text-field
																:disabled="isDisabled()"
                                label="Checkr Pay ID"
                                v-model="checkr_pay_id"
                        ></v-text-field>
                    </v-row>
										<v-row>
                        <v-btn
												:disabled="isDisabled()"
                        color="primary"
                        @click="createCheckrPayWorker"
                    >
                        Create Checkr Pay Worker ID
                    </v-btn>
                    </v-row>
                </v-container>
            </v-app>
        `,
	props: ["id"],
	data: function () {
		return {
			error: null,
			checkr_pay_id: null,
			loading: false,
		};
	},
	created() {
		this.loadNurseCheckrPayInfo();
		this.isDisabled();
	},
	mounted() {
		this.$root.$on(
			"saveMemberData",
			function () {
				this.saveData();
			}.bind(this)
		);
	},
	computed: {},
	methods: {
		loadNurseCheckrPayInfo() {
			let data = {
				id: this.id,
			};
			this.loading = true;
			modRequest.request(
				"sa.member.load_nurse_checkr_pay_info",
				{},
				data,
				function (response) {
					if (response.success) {
						if (response.checkr_pay) {
							this.checkr_pay_id = response.checkr_pay.checkr_pay_id;
						}
						this.error = null;
					} else {
						console.log("Error occured loading checkr pay id.");
						console.log(response);
						this.error = {
							type: "danger",
							message: response.message,
						};
					}
					this.loading = false;
				}.bind(this),
				function (response) {
					console.log("Failed to load checkr pay id.");
					console.log(response);
				}
			);
		},
		saveData() {
			let data = {
				id: this.id,
				nurse: this.nurse,
				checkr_pay_id: this.checkr_pay_id,
			};

			this.loading = true;
			modRequest.request(
				"sa.member.save_nurse_checkr_pay_info",
				{},
				data,
				function (response) {
					if (response.success) {
						//$.growl.notice({ title: "Success!", message: "Changes to nurse bank information was saved.", size: "large" });
						this.error = null;
					} else {
						console.log("Error saving Checkr Pay data.");
						console.log(response);
						this.error = {
							type: "danger",
							message: response.message,
						};
					}
					this.loading = false;
				}.bind(this),
				function (response) {
					console.log("Failed to save Checkr Pay data.");
					console.log(response);
				}
			);
		},
		createCheckrPayWorker() {
			let data = {
				id: this.id,
				nurse: this.nurse,
			};

			this.loading = true;
			modRequest.request(
				"sa.member.create_checkr_pay_worker",
				{},
				data,
				function (response) {
					if (response.success) {
						this.error = null;
						this.loadNurseCheckrPayInfo();
					} else {
						console.log("Error creating Checkr Pay Id.");
						console.log(response);
						this.error = {
							type: "danger",
							message: response.message,
						};
					}
					this.loading = false;
				}.bind(this),
				function (response) {
					console.log("Failed to create Checkr Pay Id.");
					console.log(response);
				}
			);
		},
		isDisabled() {
			if (this.checkr_pay_id) {
				return true;
			} else {
				return false;
			}
		}
	},
});
