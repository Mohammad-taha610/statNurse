window.addEventListener('load', () => {
    VeeValidate.extend('required', {
        validate (value) {
            return {
                required: true,
                valid: ['', null, undefined].indexOf(value) === -1
            }
        },
        computesRequired: true,
        message: 'The {_field_} is required'
    })

    Vue.use(window.VueTheMask)
    Vue.component('ValidationProvider', VeeValidate.ValidationProvider)

    Vue.component('nurse-background-check-form', {
        template: `
            <div class="container my-16 nurse-app-form" data-app>
                <div class="row">
                    <div class="col-md-8 offset-2">
                        <!-- Full Name -->
                        <div class="row">
                            <div class="col-md-12">
                                <v-card class="px-10 pt-10 pb-8" elevation="2" v-if="!submitted">
                                    <div class="d-flex align-items-center justify-content-between mt-3 mb-16">
                                        <h1>Background Check</h1>

                                        <div>
                                            <div class="d-flex align-items-center justify-content-between">
                                                <v-pagination
                                                    v-model="page"
                                                    :length="4"
                                                ></v-pagination>
                                            </div>
                                        </div>
                                    </div>

                                    <v-card-text>
                                        <div v-show="page == 1">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="nurse-form-flush">
                                                        <strong>Full Name *</strong>
                                                    </div>
                                                </div>

                                                <div class="col-md-9">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <validation-provider name="first name field" rules="required" v-slot="{ errors }">
                                                                <v-text-field label="First Name" v-model="form.first_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <validation-provider name="last name field" rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Last Name" v-model="form.last_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <p>
                                                I understand that <strong>NurseStat LLC</strong> (‘COMPANY’) will use Global Verification Network (GVN) to obtain a consumer report and/or investigative consumer report (“Report”) as part of the hiring process. I also understand that if hired, to the extent permitted by law, COMPANY may obtain further Reports from GVN so as to update, renew or extend my employment. I understand Global Verification Network’s (“GVN”) investigation may include obtaining information regarding my credit background, bank-ruptcies, lawsuits, judgments, paid tax liens, unlawful detainer actions, failure to pay spousal or child support, accounts places for collection, character, general reputation, personal characteristics and standard of living, driving record and criminal record, subject to any limitations imposed by applicable federal and state law. I understand such information may be obtained through direct or indirect contact with former employers, schools, financial institutions, landlords and public agencies or other persons who may have such knowledge. If an investigative consumer report is being requested, I understand such information may be obtained through any means, including but not limited to personal interviews with my acquaintances and/or associates or with others whom I am acquainted.
                                            </p>

                                            <p>
                                                The nature and scope of the investigation sought may be indicated by the services below: (Employer Use Only)
                                            </p>

                                            <ol>
                                                <li>Criminal background check</li>
                                                <li>Employment Credit Report</li>
                                                <li>Detailed 7 year address histor y report</li>
                                                <li>Education Verification</li>
                                                <li>Employment Verification</li>
                                                <li>Driving Record</li>
                                                <li>Drug Test</li>
                                                <li>Any other screening services as indicated, needed or required</li>
                                            </ol>

                                            <p>
                                                I acknowledge receipt of the attached summary of my rights under the Fair Credit Reporting Act and, as required by law, any related state summary of rights (collectively “Summaries of Rights” This consent will not affect my ability to question or dispute the accuracy of any information contained in a Report. I under stand if COMPANY makes a conditional decision to disqualify me based all or in part on my Report, I will be provided with a copy of the Report and another copy of the Summaries of Rights, and if I disagree with the accuracy of the purported disquali fying information in the Report, I must notify COMPANY within five business days of my receipt of the Report that I am chal lenging the accuracy of such information with GVN. I hereby consent to this investigation and authorize COMPANY to procure a Report on my background. In order to verify my identity for the purposes of Report preparation, I am voluntarily releasing my date of birth, social security number and other information and fully understand that all employment decisions are based on legitimate non-discriminatory reasons. The name, address and telephone number of the nearest unit of the consumer reporting agency designated to handle inquiries regarding the investigative consumer report is:
                                            </p>

                                            <p>
                                                <strong>Global Verification Network, 333 W. Wacker Drive, Suite #1680, Chicago, IL 60606 Phone: 1 (877) 695-1179</strong>
                                            </p>

                                            <p>
                                                <strong>Report obtained by COMPANY from GVN by checking the box. (Check only if you wish to receive a copy)</strong>
                                            </p>

                                            <p>
                                                <v-checkbox
                                                     v-model="form.rights"
                                                     label="California, Maine, Massachusetts, Minnesota, New Jersey & Oklahoma Applicants Only: I have the right to request a copy of any"
                                                ></v-checkbox>
                                            </p>

                                            <p>
                                                <v-checkbox
                                                     v-model="form.compa"
                                                     label="California, Connecticut, Maryland, Oregon and Washington State Applicants Only (AS APPLICABLE): I further understand that COMPA"
                                                ></v-checkbox>
                                            </p>

                                            <p>
                                                NY will not obtain information about my credit history, credit worthiness, credit standing, or credit capacity unless: (i) the information is required by law; (ii) I am seeking employment with a financial institution (California and Connecticut only — in California the financial institution must be subject to Sections 6801-6809 of the U.S. Code); (iii) I am seeking employment with a financial institution that accepts
                                            </p>
                                        </div>

                                        <div v-show="page == 2">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <h3>Disclosure & Consent to Request Consumer Report & Investigative Consumer Report Information</h3>

                                                    <p>deposits that are insured by a federal agency, or an affiliate or subsidiary of the financial institution or a credit union share guaranty corporation that is approved by the Maryland Commissioner of Financial Regulation or an entity or an affiliate of the entity that is an envestment advisor with the United States Securities and Exchange Commission (Maryland only); (iv) the information is substantially job related, and the bona fide reasons for using the information are disclosed to me in writing, (complete the question below) (Connecticut, Maryland, Oregon and Washington only); (v) I am seeking employment as a covered police, officer, peace officer or other law enforce ment position (California and Oregon only – in Oregon the police or peace officer position must be sought with a federally insured bank or credit union); (vi) the COMPANY reasonably believes I have engaged in specific activity that constitutes a violation of law related to my employment (Connecticut only); (vii) I am seeking a position with the state Department of Justice (California only); (viii) I am seeking a po sition as an exempt managerial employee (California only); or (viii) I am seeking employment in a position that involves regular access to personal information of others (i.e., bank or credit card account information, social security numbers, dates of birth), other than regular named signatory on the employer’s bank or credit card or otherwise authorized to enter into financial contracts on behalf of the employ er, I am seeking employment in a position that involves access to confidential or proprietary information of the Company or regular access to $10,000 or more in cash (California only</p>

                                                    <h3>APPLICABLE ONLY IF A CREDIT CHECK IS BEING RUN AS PART OF THE SCREENING PROCESS</h3>

                                                    <p>1. Bona fide reasons why COMPANY considers credit information substantially job related (complete if this is the sole basis for obtaining credit information) or in California the COMPANY’S basis for the credit check.</p>

                                                    <h3>SIGNIFICANT FIDUCIARY RESPONSIBILITIES RELATED TO THE APPLICATION POSITION</h3>

                                                    <h3>STATE SPECIFIED DISCLOSURES</h3>

                                                    <p><strong>NY Applicants Only</strong>: I also acknowledge that I have received the attached copy of Article 23A of New York’s Correction Law. I further understand that I may request a copy of any investigative consumer report by contacting GVN. I further understand that I will be advised if any further checks are requested and provided the name and address of the consumer reporting agency.</p>

                                                    <p><strong>California Applicants and Residents</strong>: If I am applying for employment in California or reside in California, I understand I have the right to visually inspect the files concerning me maintained by an investigative consumer reporting agency during normal business hours and upon reasonable notice. The inspection can be done in person, and, if I appear in person and furnish proper identification; I am entitled to a copy of the file for a fee not to exceed the actual costs of duplication. I am entitled to be accompanied by one person of my choosing, who shall furnish reasonable identification. The inspection can also be done via certified mail if I make a written request, with proper identification, for copies to be sent to a specified addressee. I can also request a summary of the information to be provided by telephone if I make a written request, with proper identification for telephone disclosure, and the toll charge, if any, for the telephone call is prepaid by or directly charged to me. I further understand that the investigative consumer reporting agency shall provide trained personnel to explain to me any of the information furnished to me; I shall receive from the investigative consumer reporting agency a written explanation of any coed information contained in files maintained on me. “Proper identification” as used in this paragraph means information generally deemed sufficient to identify a person, including documents such as a valid driver’s license, social security account number, military identi fication card and credit cards. I understand that I can access the following website – http://www.globalver.com/privacy – to view GVN’s privacy practices, including information with respect to GVN’s preparation and processing of investigative consumer reports and guidance as to whether my personal information will be sent outside the United States or its territories.</p>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Signature</strong>
                                                </div>

                                                <div class="col-md-9">
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <div style="width: 500px">
                                                                <canvas style="border: 1px solid #949494" height="200" width="500" id="canvas"></canvas>

                                                                <div style="text-align: right">
                                                                    <a href="#" @click.prevent="signaturePad.clear()" style="margin-top: 8px">Clear</a>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Today's Date</strong>
                                                            </div>

                                                            <v-text-field label="Date" disabled v-model="today"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-show="page == 3">
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong>Full Name *</strong>
                                                </div>

                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <validation-provider name="first name field" rules="required" v-slot="{ errors }">
                                                                <v-text-field label="First Name" v-model="form.personal_information.first_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>

                                                        <div class="col-md-6">
                                                            <v-text-field label="Middle Name" v-model="form.personal_information.middle_name"></v-text-field>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <validation-provider name="last name field" rules="required" v-slot="{ errors }">
                                                                <v-text-field label="Last Name" v-model="form.personal_information.last_name"></v-text-field>
                                                                <span class="required">{{ errors[0] }}</span>
                                                            </validation-provider>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <strong>Gender</strong>
                                                </div>

                                                <div class="col-md-12">
                                                    <v-radio-group v-model="form.personal_information.gender">
                                                        <v-radio
                                                            key="female"
                                                            label="Female"
                                                            value="Female"
                                                        ></v-radio>

                                                        <v-radio
                                                            key="male"
                                                            label="Male"
                                                            value="Male"
                                                        ></v-radio>
                                                    </v-radio-group>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Birth Date</strong>
                                                            </div>

                                                            <v-text-field label="Date" v-model="form.personal_information.date_of_birth"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Other Names Known By</strong>
                                                            </div>

                                                            <v-text-field label="Other Names" v-model="form.personal_information.also_known_as"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Social Security Number</strong>
                                                            </div>

                                                            <v-text-field label="Social Security Number" v-model="form.personal_information.ssn"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Primary Telephone Number</strong>
                                                            </div>

                                                            <v-text-field label="Phone" v-model="form.personal_information.phone_number"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Current Address</strong>
                                                            </div>

                                                            <v-text-field label="Current Address" v-model="form.personal_information.address"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Apartment Number</strong>
                                                            </div>

                                                            <v-text-field label="Apartment Number" v-model="form.personal_information.apartment_number"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>City</strong>
                                                            </div>

                                                            <v-text-field label="City" v-model="form.personal_information.city"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>State</strong>
                                                            </div>

                                                            <v-text-field label="State" v-model="form.personal_information.state"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Zip Code</strong>
                                                            </div>

                                                            <v-text-field label="Zip Code" v-model="form.personal_information.zip_code"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Years at Address</strong>
                                                            </div>

                                                            <v-text-field label="Years" v-model="form.personal_information.years_at_address"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Previous Address</strong>
                                                            </div>

                                                            <v-text-field label="Previous Address" v-model="form.personal_information.previous_address"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Apartment Number</strong>
                                                            </div>

                                                            <v-text-field label="Apartment Number" v-model="form.personal_information.previous_apartment_number"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>City</strong>
                                                            </div>

                                                            <v-text-field label="City" v-model="form.personal_information.previous_city"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>State</strong>
                                                            </div>

                                                            <v-text-field label="State" v-model="form.personal_information.previous_state"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Zip Code</strong>
                                                            </div>

                                                            <v-text-field label="Zip Code" v-model="form.personal_information.previous_zip_code"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Years at Address</strong>
                                                            </div>

                                                            <v-text-field label="Years" v-model="form.personal_information.previous_years_at_address"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <hr>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Driver's License Number</strong>
                                                            </div>

                                                            <v-text-field label="Driver's License Number" v-model="form.personal_information.drivers_license_number"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>License State</strong>
                                                            </div>

                                                            <v-text-field label="License State" v-model="form.personal_information.drivers_license_state"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Email Address</strong>
                                                            </div>

                                                            <v-text-field label="Email" v-model="form.personal_information.email"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-3">
                                                    <strong>Signature</strong>
                                                </div>

                                                <div class="col-md-9">
                                                    <div style="width: 500px">
                                                        <canvas style="border: 1px solid #949494" height="200" width="500" id="canvas-two"></canvas>

                                                        <div style="text-align: right">
                                                            <a href="#" @click.prevent="signaturePadTwo.clear()" style="margin-top: 8px">Clear</a>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <div>
                                                                <strong>Today's Date</strong>
                                                            </div>

                                                            <v-text-field label="Date" disabled v-model="today"></v-text-field>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div v-if="page == 4">
                                            <h2>A Summary of Your Rights Under the Fair Credit Reporting Act</h2>

                                            <p>
                                                <strong>Para Informacion en espanol, visite www.consumerfinance.gov/learnmore, o escribe a la FTC Consumer Response Center, Room 130-A 600 Pennsylvania Avenue. N.W., Washington DC 20580</strong>
                                            </p>

                                            <p>The Fair Credit Reporting Act (FCRA) promotes the accuracy, fairness, and privacy of information in the files of consumer re porting agencies. There are many types of consumer reporting agencies, including credit bureaus and specialty agencies (such as agencies that sell information about check writing histories, medical records, and rental history records</p>

                                            <p>Here is a summary of your major rights under the FCRA. For more information, including information about additional rights, go to www.consumerfinance.gov/learnmore or write to: <strong>Consumer financial Protection Bureau, 1700 G Street N.W., Washington, DC 20006.</strong></p>

                                            <ul>
                                                <li>You must be told if information in your file has been used against you. Anyone who uses credit report or any other type of consumer report to deny your application for credit, insurance, or employment – or to take adverse action against you – must tell you, and give you the name, address, and phone number of the agency that provided the information. You have the right to know what is in your file. You may request and obtain all the information about you in the files of a con sumer-reporting agency (your “file disclosure” You will be required to provide proper identification, which may include your Social Security number. In many cases, the disclosure will be free. You are entitled to a free file disclosure if:</li>
                                                <li>A person has taken adverse action against you because of information in your credit file;</li>
                                                <li>You are the victim of identity theft and place a fraud alert in your file;</li>
                                                <li>Your file contains inaccurate information as the result of fraud;</li>
                                                <li>You are on public assistance;</li>
                                                <li>You are unemployed but expect to apply for employment within 60 days In addition, all consumers are entitled to one free disclosure every 12 months upon request from each nationwide credit bureau and from nationwide specialty consumer reporting agencies. See www.consumerfinance.gov/learnmore for additional information.</li>
                                            </ul>

                                            <p><strong>You have the right to ask for a credit score.</strong> Credit scores are numerical summaries of your credit-worthiness based on infor mation from credit bureaus. You may request a credit score from consumer reporting agencies that create credit scores or distribute scores used in residential real property loans, but you will have to pay for it. In some mortgage transactions, you will receive credit score information free from the mortgage dispute procedures.</p>

                                            <p><strong>You have the right to dispute incomplete or inaccurate information.</strong> If you identify information in your file that is incomplete or inaccurate, and report it to the consumer-reporting agency, the agency must invetstigate unless your dispute is frivolous.</p>

                                            <p>See www.consumerfinance.gov/learnmore for an explanation of dispute procedures.</p>

                                            <p><strong>Consumer reporting agencies must correct or delete inaccurate, incomplete, or unverifiable information.</strong> Inaccurate, incom plete or unverifiable information must be removed or corrected, usually within 30 days. However a consumer reporting agency may continue to report information it has verified as accurate.</p>

                                            <p><strong>Consumer reporting agencies may not report outdated negative information.</strong> In most cases, a consumer-reporting agency may not report negative information that is more than seven years old, or bankruptcies that are more than 10 years old.</p>

                                            <p><strong>Access to your file is limited.</strong> A consumer report agency may provide information about you only to people with a valid need – usually to consider an application with a creditor, insurer, employer, landlord, or other business. The FCRA specifies those with a valid need for access.</p>

                                            <p><strong>You must give your consent for reports to be provided to employers.</strong> A consumer-reporting agency may not give out informa tion about your to your employer, or potential employer, without your written consent given to the employer. Written consent generally is not required in the trucking industry. For more information go to www.consumerfinance.gov/learnmore.</p>

                                            <p><strong>You may limit “prescreened” offers of credit and insurance you get based on information in your credit report.</strong> Unsolicited “prescreened” offers of credit and insurance must include a toll-free number you can call if you choose to remove your name and address from the lists these offers are based on. You may opt-out with the nationwide credit bureaus at 1-888-5-OPTOUT (1-888-567-8688)</p>

                                            <p><strong>You may seek damages from violators.</strong> If a consumer reporting agency, or, in some cases, a user of consumer reports or a furnisher of information to a consumer reporting agency violates the FCRA, you may be able to sue in state or federal court.</p>

                                            <h2>A Summary of Your Rights Under the Fair Credit Reporting Act</h2>

                                            <p><strong>Identity theft victims and active duty military personnel have additional rights.</strong> For more information visit <a href="https://consumerfinance.gov" target="_blank">https://consumerfinance.gov/</a></p>

                                            <p>State may enforce the FCRA, and many states have their own consumer reporting laws. In some cases, you may have more rights under state law. For more information, contact your state or local consumer protection agency or your state Attorney General. For info about your federal rights contact:</p>

                                            <h3>TYPE OF BUSINESS</h3>

                                            <h3>CONTACT</h3>

                                            <p><strong>1.</strong> a.Banks, savings associations and credit unions with total assets of over $10 billion and their affiliates.</p>

                                            <p>b.Such affiliates that are not banks, savings associations or credit unions also should list, in addition to the Bureau:</p>

                                            <p>a.Bureau of consumer Proteciton 1700 G Street NW Washington, DC 20006 b.Federal Trade Commission: Consumer Response Center - FCRA Washington, DC 20580 1 (877) 382-4357</p>

                                            <strong>2.</strong> To the extent not included in item 1 above: a.National banks, federal savings associations, and federal branches and federal agencies of foreign banks

                                            <p>b.State member banks, branches and agencies of foreign banks (other than federal branches, federal agencies, and insured state branches of foreign banks), commercial lending companies owned or controlled by foreign banks, and orga nizations operating under section 25 or 25A of the Federal Reserve Act. c.Nonmember Insured Banks, Insured State Branches of For eign Banks, and Insured state savings associations</p>

                                            <p>a.Office of the Comptroller of the Currency Customer Assistance Group 1301 McKinney Street, Suite 3450 Houston, TX 77010-9050 b.Federal Reserve Consumer Help Center PO Box 1200 Minneapolis, MN 55480</p>

                                            <p>c.FDIC Consumer Response Center 1100 Walnut Street, Box #11 Kansas City, MO 64106 d.National Credit Union Administration Office of Consumer Protection (OCP) Division of Consumer Compliance and Outreach (DCCO) 1775 Duke Street, Alexandria VA 22314</p>

                                            <p>Asst. General Counsel for Aviation Enforcement & Proceedings Department of Transportation 1925 K Street NW Washington, DC 20423</p>

                                            <p>4.Creditors Subject to Surface Transportation Board</p>

                                            <p>Office of Proceedings, Surface Transportation Board Department of Transportation 1925 K Street NW Washington, DC 20423</p>

                                            <p>5.Creditors Subject to Packers and Stockyards Act</p>

                                            <p>Nearest Packers and Stockyards Administration Area Supervisor</p>

                                            <p>6.Small Business Investment Companies</p>

                                            <p>Associate Deputy Administrator for Capital Access United State Small Business Administration 406 third Street, SW 8th Floor Washington, DC 20416</p>

                                            <p>Securities and Exchange Commission 100 F Street NE Washington, DC 20549</p>

                                            <p>8.Federal Land Banks, federal Land Bank Associations, Federal Intermediate Credit Banks, and Production Credit Associations</p>

                                            <p>Farm Credit Administration 1501 Farm Credit Drive McLean, VA 22102-5090</p>

                                            <p>9.Retailers, Finance Companies, and All Other Creditors Not Listed Above</p>

                                            <p>FTC Regional Office for region in which the creditor operates or Federal Trade Commission: Consumer Response Center - FCRA Washington, DC 20580 (877) 382-4357</p>
                                        </div>

                                        <div class="text-right">
                                            <v-btn elevation="2" v-if="page != 1" @click="page--">Previous</v-btn>

                                            <v-btn color="primary" elevation="2" @click="page++" v-if="page != 4">Next</v-btn>

                                            <v-btn color="primary" elevation="2" @click="onSubmit" v-else>Submit</v-btn>
                                        </div>
                                    </v-card-text>
                                </v-card>

                                <v-card class="px-10 pt-10 pb-8" elevation="2" v-else>
                                    <div class="text-center mt-4">
                                        <h2>Background Check Submitted</h2>

                                        <p class="mt-4">You can now proceed to part two of the nurse application process.</p>
                                    </div>
                                </v-card>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        `,

        watch: {
            page () {
                document.body.scrollTop = document.documentElement.scrollTop = 0
            }
        },

        created () {
            const date = new Date

            this.today = `${(date.getMonth() + 1)}/${date.getDate()}/${date.getFullYear()}`

            this.member = authenticatedMember

            const submitted = localStorage.getItem('submitted')

            if (submitted) {
                this.submitted = !! submitted
            }
        },

        mounted () {
            var canvas = document.querySelector('#canvas')

            var canvasTwo = document.querySelector('#canvas-two')

            this.signaturePad = new SignaturePad(canvas, {
                onEnd: () => this.form.signature = this.signaturePad.toData()
            })

            this.signaturePadTwo = new SignaturePad(canvasTwo, {
                onEnd: () => this.form.personal_information.signature = this.signaturePadTwo.toData()
            })
        },

        data: () => ({
            page: 1,
            form: {
                first_name: '',
                last_name: '',
                rights: false,
                compa: false,
                signature: '',
                personal_information: {
                    first_name: '',
                    middle_name: '',
                    last_name: '',
                    email: '',
                    gender: '',
                    date_of_birth: '',
                    also_known_as: '',
                    ssn: '',
                    phone_number: '',
                    address: '',
                    apartment_number: '',
                    city: '',
                    state: '',
                    zip_code: '',
                    years_at_address: '',
                    previous_address: '',
                    previous_apartment_number: '',
                    previous_city: '',
                    previous_state: '',
                    previous_zip_code: '',
                    previous_years_at_address: '',
                    drivers_license_number: '',
                    drivers_license_state: '',
                    email: '',
                    signature: '',
                }
            },
            today: '',
            member: null,
            submitted: false,
            signaturePad: null,
            signaturePadTwo: null
        }),

        methods: {
            onSubmit () {
                modRequest.request('nurse.background_check.store', null, this.form, () => {
                    this.submitted = true
                })
            }
        }
    })
})
