<?php

namespace App\Entity\Nst\Member;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'nurse_application')]
#[Entity(repositoryClass: 'NurseApplicationRepository')]
#[InheritanceType('SINGLE_TABLE')]
#[DiscriminatorColumn(name: 'discriminator', type: 'string')]
#[HasLifecycleCallbacks]
class NurseApplication
{
    #[Id]
    #[GeneratedValue]
    #[Column(type: 'integer')]
    protected $id;

    #[OneToOne(inversedBy: 'nurse_application', targetEntity: NstMember::class)]
    protected $member;

    #[Column(type: 'datetime', nullable: true)]
    protected $submitted_at;

    #[Column(type: 'datetime', nullable: true)]
    protected $approved_at;

    #[Column(type: 'datetime', nullable: true)]
    protected $declined_at;

    #[Column(type: 'json', nullable: true)]
    protected $nurse;

    #[Column(type: 'json', nullable: true)]
    protected $employment_details_one;

    #[Column(type: 'json', nullable: true)]
    protected $employment_details_two;

    #[Column(type: 'json', nullable: true)]
    protected $employment_details_three;

    #[Column(type: 'json', nullable: true)]
    protected $highschool;

    #[Column(type: 'json', nullable: true)]
    protected $college;

    #[Column(type: 'json', nullable: true)]
    protected $other_education;

    #[Column(type: 'json', nullable: true)]
    protected $professional_reference_one;

    #[Column(type: 'json', nullable: true)]
    protected $professional_reference_two;

    #[Column(type: 'json', nullable: true)]
    protected $employment;

    #[Column(type: 'json', nullable: true)]
    protected $criminal_record;

    #[Column(type: 'json', nullable: true)]
    protected $nurse_stat_info;

    #[Column(type: 'json', nullable: true)]
    protected $direct_deposit;

    #[Column(type: 'json', nullable: true)]
    protected $license_and_certifications;

    #[Column(type: 'json', nullable: true)]
    protected $emergency_contact_one;

    #[Column(type: 'json', nullable: true)]
    protected $emergency_contact_two;

    #[Column(type: 'json', nullable: true)]
    protected $terms;

    #[Column(type: 'json', nullable: true)]
    protected $medical_history;

    #[Column(type: 'json', nullable: true)]
    protected $tb;

    #[Column(type: 'json', nullable: true)]
    protected $files;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  mixed  $id
     * @return NurseApplication
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMember()
    {
        return $this->member;
    }

    /**
     * @param  mixed  $member
     * @return NurseApplication
     */
    public function setMember($member)
    {
        $this->member = $member;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getSubmittedAt()
    {
        return $this->submitted_at;
    }

    /**
     * @param  mixed  $submitted_at
     * @return NurseApplication
     */
    public function setSubmittedAt($submitted_at)
    {
        $this->submitted_at = $submitted_at;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApprovedAt()
    {
        return $this->approved_at;
    }

    /**
     * @param  mixed  $approved_at
     * @return NurseApplication
     */
    public function setApprovedAt($approved_at)
    {
        $this->approved_at = $approved_at;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDeclinedAt()
    {
        return $this->declined_at;
    }

    /**
     * @param  mixed  $declined_at
     * @return NurseApplication
     */
    public function setDeclinedAt($declined_at)
    {
        $this->declined_at = $declined_at;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNurse()
    {
        return $this->nurse;
    }

    /**
     * @param  mixed  $nurse
     * @return NurseApplication
     */
    public function setNurse($nurse)
    {
        $this->nurse = $nurse;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmploymentDetailsOne()
    {
        return $this->employment_details_one;
    }

    /**
     * @param  mixed  $employment_details_one
     * @return NurseApplication
     */
    public function setEmploymentDetailsOne($employment_details_one)
    {
        $this->employment_details_one = $employment_details_one;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmploymentDetailsTwo()
    {
        return $this->employment_details_two;
    }

    /**
     * @param  mixed  $employment_details_two
     * @return NurseApplication
     */
    public function setEmploymentDetailsTwo($employment_details_two)
    {
        $this->employment_details_two = $employment_details_two;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmploymentDetailsThree()
    {
        return $this->employment_details_three;
    }

    /**
     * @param  mixed  $employment_details_three
     * @return NurseApplication
     */
    public function setEmploymentDetailsThree($employment_details_three)
    {
        $this->employment_details_three = $employment_details_three;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getHighschool()
    {
        return $this->highschool;
    }

    /**
     * @param  mixed  $highschool
     * @return NurseApplication
     */
    public function setHighschool($highschool)
    {
        $this->highschool = $highschool;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCollege()
    {
        return $this->college;
    }

    /**
     * @param  mixed  $college
     * @return NurseApplication
     */
    public function setCollege($college)
    {
        $this->college = $college;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getOtherEducation()
    {
        return $this->other_education;
    }

    /**
     * @param  mixed  $other_education
     * @return NurseApplication
     */
    public function setOtherEducation($other_education)
    {
        $this->other_education = $other_education;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfessionalReferenceOne()
    {
        return $this->professional_reference_one;
    }

    /**
     * @param  mixed  $professional_reference_one
     * @return NurseApplication
     */
    public function setProfessionalReferenceOne($professional_reference_one)
    {
        $this->professional_reference_one = $professional_reference_one;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getProfessionalReferenceTwo()
    {
        return $this->professional_reference_two;
    }

    /**
     * @param  mixed  $professional_reference_two
     * @return NurseApplication
     */
    public function setProfessionalReferenceTwo($professional_reference_two)
    {
        $this->professional_reference_two = $professional_reference_two;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmployment()
    {
        return $this->employment;
    }

    /**
     * @param  mixed  $employment
     * @return NurseApplication
     */
    public function setEmployment($employment)
    {
        $this->employment = $employment;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCriminalRecord()
    {
        return $this->criminal_record;
    }

    /**
     * @param  mixed  $criminal_record
     * @return NurseApplication
     */
    public function setCriminalRecord($criminal_record)
    {
        $this->criminal_record = $criminal_record;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getNurseStatInfo()
    {
        return $this->nurse_stat_info;
    }

    /**
     * @param  mixed  $nurse_stat_info
     * @return NurseApplication
     */
    public function setNurseStatInfo($nurse_stat_info)
    {
        $this->nurse_stat_info = $nurse_stat_info;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDirectDeposit()
    {
        return $this->direct_deposit;
    }

    /**
     * @param  mixed  $direct_deposit
     * @return NurseApplication
     */
    public function setDirectDeposit($direct_deposit)
    {
        $this->direct_deposit = $direct_deposit;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseAndCertifications()
    {
        return $this->license_and_certifications;
    }

    /**
     * @param  mixed  $license_and_certifications
     * @return NurseApplication
     */
    public function setLicenseAndCertifications($license_and_certifications)
    {
        $this->license_and_certifications = $license_and_certifications;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactOne()
    {
        return $this->emergency_contact_one;
    }

    /**
     * @param  mixed  $emergency_contact_one
     * @return NurseApplication
     */
    public function setEmergencyContactOne($emergency_contact_one)
    {
        $this->emergency_contact_one = $emergency_contact_one;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getEmergencyContactTwo()
    {
        return $this->emergency_contact_two;
    }

    /**
     * @param  mixed  $emergency_contact_two
     * @return NurseApplication
     */
    public function setEmergencyContactTwo($emergency_contact_two)
    {
        $this->emergency_contact_two = $emergency_contact_two;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param  mixed  $terms
     * @return NurseApplication
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getMedicalHistory()
    {
        return $this->medical_history;
    }

    /**
     * @param  mixed  $medical_history
     * @return NurseApplication
     */
    public function setMedicalHistory($medical_history)
    {
        $this->medical_history = $medical_history;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getTb()
    {
        return $this->tb;
    }

    /**
     * @param  mixed  $tb
     * @return NurseApplication
     */
    public function setTb($tb)
    {
        $this->tb = $tb;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->files;
    }

    /**
     * @param  mixed  $files
     * @return NurseApplication
     */
    public function setFiles($files)
    {
        $this->files = $files;

        return $this;
    }
}
