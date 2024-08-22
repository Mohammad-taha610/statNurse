window.addEventListener('load', () => {
    Vue.component('nurse-app-part-two', {
        template: `
            <div class="container mb-16 nurse-app-form">
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex mb-16" style="justify-content: space-between">
                            <v-pagination
                                v-model="page"
                                :length="4"
                            ></v-pagination>
                        </div>

                        <div v-show="page == 1">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Associate Name *</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="First Name" v-model="form.associate_first_name"></v-text-field>
                                        </div>

                                        <div class="col-md-6">
                                            <v-text-field disabled label="Last Name" v-model="form.associate_last_name"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Phone Number *</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="Phone Number" v-model="form.associate_phone_number"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-9 offset-3">
                                    <hr class="mb-5 pb-1">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Name</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="First Name" v-model="form.emergency_contact_one_first_name"></v-text-field>
                                        </div>

                                        <div class="col-md-6">
                                            <v-text-field disabled label="Last Name" v-model="form.emergency_contact_one_last_name"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Relationship</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="Relationship" v-model="form.emergency_contact_one_relationship"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Phone Number *</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <v-text-field disabled label="Phone Number" v-model="form.emergency_contact_one_phone_number"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-9 offset-3">
                                    <hr class="mb-5 pb-1">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Name</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="First Name" v-model="form.emergency_contact_two_first_name"></v-text-field>
                                        </div>

                                        <div class="col-md-6">
                                            <v-text-field disabled label="Last Name" v-model="form.emergency_contact_two_last_name"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Emergency Contact Relationship</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="Relationship" v-model="form.emergency_contact_two_relationship"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Phone Number *</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <v-text-field disabled label="Phone Number" v-model="form.emergency_contact_two_phone_number"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 2">
                            <div class="row">
                                <div class="col-md-12">
                                    <p><strong>EDUCATION AND TRAINING</strong>. Applicant states that he/she has obtained education and training in the healthcare field and is duly licensed and authorized to practice nursing.</p>

                                    <p><strong>EMPLOYEE AT WILL</strong>. Applicant acknowledges NurseStat LLC employs Applicant ''at will'' and no employment promises have been made for any duration of time. Specifically, Applicant understands he/she may quit employment at any time with NurseStat LLC, with or without notice. Similarly, Applicant understands he/she may be discharged by NurseStat LLC at any time, without notice, for any lawful reason. Contracts of employment can only be made by a written agreement between Applicant and NurseStat LLC and require the approval and signature of a manger of NurseStat LLC or authorized representative. Further, should Facility decide to end Applicant's assignment prior to completion date, NurseStat LLC may propose a new assignment as long as Applicant is in good standing with NurseStat LLC</p>

                                    <p><strong>NONDISCLOSURE AND LIMITED NONCOMPETE</strong>. Applicant agrees not to disclose any NurseStat LLC trade secrets or any confidential or proprietary information of NurseStat LLC, NurseStat LLC employees, Facilities, or patients of Facilities. Applicant further agrees not to compete either as a direct competitor or with a competing company at the Facility assignment where Applicant has been placed by NurseStat LLC for a term of six months after Applicant's final day of work at Facility.</p>

                                    <p><strong>NONSOLICITATION OF CORPORATION EMPLOYEES</strong>. Applicant agrees not to solicit NurseStat LLC employees to work for any competing company while on assignment with a NurseStat LLC facility, and for a period of six months thereafter.</p>

                                    <p><strong>NONSOLICITATION OF CORPORATION EMPLOYEES</strong>. Applicant agrees not to solicit NurseStat LLC employees to work for any competing company while on assignment with a NurseStat LLC facility, and for a period of six months thereafter.</p>

                                    <p><strong>DRUG SCREENS</strong>. Prior to placement and throughout employment with NurseStat LLC, Applicant consents to a urine, blood or breath sample for the purposes of an alcohol, drug, intoxicant, or substance abuse screening test. Applicant also gives permission for the release of the test results for determining the fitness of employment or continued employment. Applicant willutilize clinics that are approved by NurseStat LLC</p>

                                    <p><strong>BACKGROUND CHECKS</strong>. Before the Applicant is placed and throughout employment with NurseStat LLC, NurseStat LLC may, upon a facility's request, conduct background checks of any kind from any location for any purpose NurseStat LLC considers reasonable. Applicant also gives permission for release of the results for determining fitness of employment and/or continued employment.</p>

                                    <p><strong>EMPLOYMENT AND MEDICAL INFORMATION RELEASE</strong>. I authorize NurseStat LLC to release any and all confidential employment and medical information contained in my employment file to any medical facility or entity with whom NurseStat LLC has a staffing agreement, and to any other governmental or regulatory agency at such agency's request. For all other purposes, NurseStat LLC shall keep my employment and medical records confidential and shall advise any medical facility or other entity to whom records have been provided to also keep such records confidential. I hereby release and hold NurseStat LLC harmless for any result(s) that may arise with regard to the release of this confidential information by NurseStat LLC.</p>

                                    <p><strong>REIMBURSEMENTS</strong>. Applicant agrees to adhere to all rules. and policies regarding reimbursements, including but not limited to submitting expenses within 90 days of incurring expense. Further, Applicant acknowledges NurseStat LLC rules and regulations regarding reimbursements may be modified at any time with or without notice for any reason.</p>

                                    <p><strong>RECORDING 0F TIME WORKED</strong>. Applicant agrees to abide by NurseStat LLC procedures. For reporting time worked, including hospital supervisor approval for shift time worked and missed lunch periods. The NurseStat LLC workweek begins at 7:00 AM on Monday and concludes at 6:59 AM on the following Monday. Applicant's time sheet must reach NurseStat LLC each Tuesday by 10 AM Eastern Standard Time in order to be paid in the current week. Late submissions may be paid the following week.</p>

                                    <p><strong>LUNCH BREAK POLICY</strong>.  Applicant will clock in and out for a minimum of thirty (30) minutes and up to a maximum of one (1) hour for meal periods, unless otherwise specified by facility policy. If the facility requests applicant to work their lunch period due to patient care and safety, Applicant agrees to obtain two supervisor signatures of approval from Facility Healthcare Professional Managers for each applicable shift.</p>

                                    <p><strong>PERSONAL PROPERTY</strong>. NurseStat LLC is not responsible for the theft, loss, destruction, or damage to the personal property of its employees.</p>

                                    <p><strong>TERMINATION</strong>. Applicant understands if he/she leaves his/her assignment early for any reason or is terminated by NurseStat LLC, any fees due to NurseStat LLC or fees incurred by NurseStat as a result of this situation shall be deducted from their paycheck.</p>

                                    <p><strong>CONFIDENTIALITY OF AGREEMENT</strong>. NurseStat LLC and Applicant will maintain the confidentiality and exclusivity of this Agreement.</p>

                                    <p><strong>AGREEMENT REVIEW</strong>. NurseStat LLC and Applicant agree each party has fully read and reviewed this agreement. Should any ambiguities arise, the interpretation of the ambiguity will not automatically be construed infavor of the Applicant.</p>

                                    <p><strong>EQUAL OPPORTUNITY EMPLOYER</strong>. NurseStat LLC is an equal opportunity employer in the State of Kentucky and in good standing with the Kentucky Secretary of State. NurseStat LLC does not discriminate in respect to hiring, firing, compensation, and all other terms and conditions of privileges of employment on the basis of race, color, national origin, ancestry, sex, age, pregnancy or related medical conditions, marital status, religious creed, or disability.</p>

                                    <h2 class="pb-3">Agreement between applicant and NurseStat LLC</h2>

                                    <h3>HIPPA and Privacy Standards</h3>

                                    <p>I certify that I will comply with the specific policies and procedures of HIPPA and Privacy of Protected Information for each client of NurseStat LLC to which I am assigned. I also certify that I understand and will adhere to all of organizations privacy policies and procedures. I understand that failure to follow these privacy policies and procedures will result in disciplinary action which could include termination of my employment with NurseStat LLC, termination of current assignment, restriction as well as potential personal civil and/or criminal action.</p>

                                    <h4>Competing Agency Agreement</h4>

                                    <p>NurseStat LLC understands that many nurses will work for multiple agencies at the same time. While we understand this, it must be understood that a nurse working on behalf of NurseStat LLC cannot also be working for another agency at the same facility. This is a conflict of interest for the agency. It is acceptable for the nurse to work for facilities that do not contract with NurseStat LLC.</p>

                                    <p>Violation of this agreement will result in a lost profit penalty payment of $1000 to be paid by the contracted nurse to NurseStat. Payable upon proof of violation of this agreement by NurseStat LLC</p>

                                    <h4>Contract Labor Agreement</h4>

                                    <p>This Agreement constitutes an understanding that the individual listed below is being employed as contract labor and is not guaranteed any hours of work or minimum work time, does not qualify for any company benefits and will not be eligible for unemployment compensation based on hours worked under contract with NurseStat LLC.</p>

                                    <p>This agreement also clarifies to both parties that the individual is operating as an independent entity and will; be responsible for remitting their own taxes as such. NurseStat LLC will not be responsible for withholding or remitting any taxes on behalf of the individual. At the end of the year NurseStat associates will be issued a 1099-m to report for tax purposes.           </p>

                                    <h4>Healthcare Professional Conduct Expectations</h4>

                                    <p>NurseStat LLC requires you to adhere to the following Professional Conduct Expectations while on assignment. Failure to meet these expectations could lead to your termination from NurseStat LLC. Please represent our company in a professional manner.</p>

                                    <p><em>Please:</em></p>

                                    <ul>
                                        <li>Do not discuss any elements of  your compensation with anyone employed at the host facility.</li>
                                        <li>Do not discuss any previous assignments worked for NurseStat LLC with anyone employed at the host facility.</li>
                                        <li>Do not recruit any Healthcare Professionals at the host facility, whether temporary or permanent employees.</li>
                                        <li>Communicate with the management, staff and patients of the host facility in a respectful manner at all times.</li>
                                        <li>Honor all terms of this agreement letter, including but not limited to beginning and ending assignment dates and travel arrangements if applicable.</li>
                                        <li>Honor the policies and procedures of NurseStat LLC and the host facility.</li>
                                    </ul>

                                    <p>I certify that I have read, understand and intend to comply with the Primary Applicant Agreement and Professional Conduct Expectations and the facts contained in this application are true and accurate. I understand any misrepresentation or omission of facts is cause for dismissal. I authorize the employer to investigate any and all statements contained herein and request the persons, firms, and/or corporations named above to answer any and all questions relating to this application. I release all parties from all liability, including but not limited to, the employer and any person, firm or corporation who provides information concerning my prior education, employment or character</p>

                                    <p><em>Please sign below indicating your acceptance of this agreement</em></p>

                                    <p class="mb-0"><strong>Please sign in the space below (fingertip signature) *</strong></p>
                                </div>

                                <div class="col-md-6">
                                    <canvas style="border: 1px solid #949494" height="200" width="515" id="canvas"></canvas>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="Date" v-model="form.terms_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 3">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Medical History Questionnaire</h3>

                                    <p>Have you had any of the following conditions or diseases?</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <v-simple-table>
                                        <template v-slot:default>
                                            <thead>
                                                <tr>
                                                    <th class="text-left" style="font-size: 14px!important"></th>
                                                    <th class="text-left" style="font-size: 14px!important">Yes</th>
                                                    <th class="text-left" style="font-size: 14px!important">No</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr>
                                                    <td>Anemia</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_anemia">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_anemia">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Smallpox</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_smallpox">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_smallpox">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Diabetes</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_diabetes">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_diabetes">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Diphtheria</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_diphtheria">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_diphtheria">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Epilepsy</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_epilepsy">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_epilepsy">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Heart Disease</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_heart_disease">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_heart_disease">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Kidney Trouble</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_kidney_trouble">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_kidney_trouble">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Mononucleosis</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_mononucleosis">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_mononucleosis">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Scarlet Fever</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_scarlet_fever">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_scarlet_fever">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Typhoid Fever</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_typhoid_fever">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_typhoid_fever">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Hypertension</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_hypertension">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_hypertension">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Latex Allergies</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_latex_allergies">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_latex_allergies">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Hernia</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_hernia">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_hernia">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Depression</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_depression">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_depression">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Measles</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_measles">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_measles">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Hepatitis</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_hepatitis">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_hepatitis">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Mumps</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_mumps">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_mumps">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Pleurisy</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_pleurisy">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_pleurisy">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Pneumonia</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_pneumonia">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_pneumonia">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Chicken Pox</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_chicken_pox">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_chicken_pox">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Emphysema</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_emphysema">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_emphysema">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Tuberculosis</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_tuberculosis">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_tuberculosis">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Whooping Cough</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_whooping_cough">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_whooping_cough">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Rheumatic Fever</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_rheumatic_fever">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_rheumatic_fever">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Carpal Tunnel</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_carpal_tunnel">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_carpal_tunnel">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>Sight or Hearing problems</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_sight_hearing_problems">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_sight_hearing_problems">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>

                                                <tr>
                                                    <td>including colorblindness</td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_including_colorblindness">
                                                            <v-radio disabled value="yes"></v-radio>
                                                        </v-radio-group>
                                                    </td>

                                                    <td>
                                                        <v-radio-group v-model="form.medical_history_including_colorblindness">
                                                            <v-radio disabled value="no"></v-radio>
                                                        </v-radio-group>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </template>
                                    </v-simple-table>
                                </div>

                                <div class="col-md-12">
                                    <p>If you have had any of the following please provide approximate dates and a brief description  of the occurrance</p>

                                    <ul>
                                        <li>Fractures</li>
                                        <li>Back Problems or Injuries</li>
                                        <li>Other Injuries that caused you to miss work more than 10 days</li>
                                        <li>Surgeries</li>
                                        <li>Permanent physical restrictions</li>
                                    </ul>

                                    <p class="pt-4">If none of these apply please enter None in the box</p>
                                </div>

                                <div class="col-md-6">
                                    <v-textarea
                                        name="input-7-1"
                                        label="Details"
                                        value=""
                                        auto-grow
                                        rows="1"
                                        v-model="form.medical_history_explanation"
                                    ></v-textarea>
                                </div>

                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-6">
                                        <p>
                                            <strong>Have you been Vaccinated for *</strong>
                                        </p>

                                            <v-simple-table>
                                                <template v-slot:default>
                                                    <thead>
                                                        <tr>
                                                            <th class="text-left" style="font-size: 14px!important"></th>
                                                            <th class="text-left" style="font-size: 14px!important">Yes</th>
                                                            <th class="text-left" style="font-size: 14px!important">No</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <tr>
                                                            <td>Routine Vaccinations Current</td>

                                                            <td>
                                                                <v-radio-group>
                                                                    <v-radio disabled value="yes"></v-radio>
                                                                </v-radio-group>
                                                            </td>

                                                            <td>
                                                                <v-radio-group>
                                                                    <v-radio disabled value="no"></v-radio>
                                                                </v-radio-group>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Hepatitis B</td>

                                                            <td>
                                                                <v-radio-group>
                                                                    <v-radio disabled value="yes"></v-radio>
                                                                </v-radio-group>
                                                            </td>

                                                            <td>
                                                                <v-radio-group>
                                                                    <v-radio disabled value="no"></v-radio>
                                                                </v-radio-group>
                                                            </td>
                                                        </tr>

                                                        <tr>
                                                            <td>Hepatitis A</td>

                                                            <td>
                                                                <v-radio-group>
                                                                    <v-radio disabled value="yes"></v-radio>
                                                                </v-radio-group>
                                                            </td>

                                                            <td>
                                                                <v-radio-group>
                                                                    <v-radio disabled value="no"></v-radio>
                                                                </v-radio-group>
                                                            </td>
                                                        </tr>
                                                    </tbody>
                                                </template>
                                            </v-simple-table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-show="page == 4">
                            <div class="row">
                                <div class="col-md-12">
                                    <h3>Tuberculosis Screening Questionnaire</h3>
                                </div>
                            </div>

                            <!-- References -->
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>Have you had a positive TB skin test in the past? if so please list date</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="Date" v-model="form.tb_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="nurse-form-flush">
                                        <strong>If you have had a Chest Xray in the past please list date</strong>
                                    </div>
                                </div>

                                <div class="col-md-9">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <v-text-field disabled label="Date" v-model="form.tb_chest_date"></v-text-field>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <p>I have answered the questions fully and declare that I have no known injury, Illness, or ailment other than those previously noted. I further understand that any misrepresentation, or omission may be grounds for corrective action up to and including termination of my contract</p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <strong>Signature</strong>
                                        </div>

                                        <div class="col-md-12">
                                            <div style="width: 500px">
                                                <canvas style="border: 1px solid #949494" height="200" width="500" id="canvas-two"></canvas>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-16">
                            <div class="d-flex">
                                <v-pagination
                                    v-model="page"
                                    :length="4"
                                ></v-pagination>
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
            this.form = formTwo
        },

        mounted () {
            var canvas = document.querySelector('#canvas')
            var canvasTwo = document.querySelector('#canvas-two')

            this.signaturePad = new SignaturePad(canvas, {
                onEnd: () => this.form.terms.signature = this.signaturePad.toData()
            })

            this.signaturePadTwo = new SignaturePad(canvasTwo, {
                onEnd: () => this.form.tb.signature = this.signaturePadTwo.toData()
            })

            if (this.form.terms.signature) {
                this.signaturePad.fromData(this.form.terms.signature)
            }

            if (this.form.tb.signature) {
                this.signaturePadTwo.fromData(this.form.tb.signature)
            }
        },

        data: () => ({
            page: 1,
            picker: '',
            dialog: '',
            member: null,
            dialogTwo: '',
            radioGroup: '',
            registerForm: {
                email: '',
                password: ''
            },
            checkbox: false,
            signaturePad: null,
            signaturePadTwo: null,
        }),

        methods: {
            saveProgress () {
                const data = { application: this.form }

                modRequest.request('nurse.application.storeTwo', null, data, (res) => {
                    const formString = JSON.stringify(this.form)

                    localStorage.setItem('formTwo', formString)
                }, () => console.log('no'))
            }
        }
    })
})
