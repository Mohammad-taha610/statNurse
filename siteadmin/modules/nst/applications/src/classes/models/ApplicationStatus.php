<?php

namespace nst\applications;

use nst\member\NurseApplicationPartTwo;

/**
 * @Entity(repositoryClass="ApplicationStatusRepository")
 * @Table(name="application_status")
 */
class ApplicationStatus
{
    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @OneToOne(targetEntity="nst\applications\ApplicationPart2", mappedBy="application_status") */
    protected $application_part2;

    /** @Column(type="boolean", nullable=true) */
    protected $is_active;

    /** @Column(type="datetime", nullable=true) */
    protected $last_active;

    /** @Column(type="boolean", nullable=true) */
    protected $application_submitted;

    /** @Column(type="boolean", nullable=true) */
    protected $license_submitted;

    /** @Column(type="boolean", nullable=true) */
    protected $license_verified;

    /** @Column(type="boolean", nullable=true) */
    protected $files_submitted;

    /** @Column(type="string", nullable=true) */
    protected $drug_screen_status;

    /** @Column(type="string", nullable=true) */
    protected $drug_screen_invitation_id;

    /** @Column(type="string", nullable=true) */
    protected $drug_screen_report_id;

    /** @Column(type="json", nullable=true) */
    protected $drug_screen_report;

    /** @Column(type="datetime", nullable=true) */
    protected $drug_screen_scheduled_date;

    /** @Column(type="boolean", nullable=true) */
    protected $drug_screen_accepted;

    /** @Column(type="string", nullable=true) */
    protected $background_check_invitation_id;

    /** @Column(type="string", nullable=true) */
    protected $background_check_report_id;

    /** @Column(type="datetime", nullable=true) */
    protected $background_check_started_date;

    /** @Column(type="json", nullable=true) */
    protected $background_check_report;

    /** @Column(type="boolean", nullable=true) */
    protected $background_check_returned;

    /** @Column(type="boolean", nullable=true) */
    protected $background_check_accepted;

    /** @Column(type="string", nullable=true) */
    protected $background_check_status;

    /** @Column(type="string", nullable=true) */
    protected $background_check_signature;

    /** @Column(type="datetime", nullable=true) */
    protected $background_check_signed_time;

    /** @Column(type="string", nullable=true) */
    protected $checkr_id;

    /** @Column(type="datetime", nullable=true) */
    protected $application_accepted;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     *
     * @return self
     */
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplication2()
    {
        return $this->application_part2;
    }

    /**
     * @param mixed $application_part2
     *
     * @return self
     */
    public function setApplication2($application_part2)
    {
        $this->application_part2 = $application_part2;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * @param mixed $is_active
     *
     * @return self
     */
    public function setIsActive($is_active)
    {
        $this->is_active = $is_active;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLastActive()
    {
        return $this->last_active;
    }

    /**
     * @param mixed $last_active
     *
     * @return self
     */
    public function setLastActive($last_active)
    {
        $this->last_active = $last_active;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationSubmitted()
    {
        return $this->application_submitted;
    }

    /**
     * @param mixed $application_submitted
     *
     * @return self
     */
    public function setApplicationSubmitted($application_submitted)
    {
        $this->application_submitted = $application_submitted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseSubmitted()
    {
        return $this->license_submitted;
    }

    /**
     * @param mixed $license_submitted
     *
     * @return self
     */
    public function setLicenseSubmitted($license_submitted)
    {
        $this->license_submitted = $license_submitted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getLicenseVerified()
    {
        return $this->license_verified;
    }

    /**
     * @param mixed $license_verified
     *
     * @return self
     */
    public function setLicenseVerified($license_verified)
    {
        $this->license_verified = $license_verified;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getFilesSubmitted()
    {
        return $this->files_submitted;
    }

    /**
     * @param mixed $files_submitted
     *
     * @return self
     */
    public function setFilesSubmitted($files_submitted)
    {
        $this->files_submitted = $files_submitted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrugScreenStatus()
    {
        return $this->drug_screen_status;
    }

    /**
     * @param mixed $drug_screen_status
     *
     * @return self
     */
    public function setDrugScreenStatus($drug_screen_status)
    {
        $this->drug_screen_status = $drug_screen_status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrugScreenInvitationId()
    {
        return $this->drug_screen_invitation_id;
    }

    /**
     * @param mixed $checkr_drug_screen_id
     *
     * @return self
     */
    public function setDrugScreenInvitationId($drug_screen_invitation_id)
    {
        $this->drug_screen_invitation_id = $drug_screen_invitation_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrugScreenReportId()
    {
        return $this->drug_screen_report_id;
    }

    /**
     * @param mixed $drug_screen_report_id
     *
     * @return self
     */
    public function setDrugScreenReportId($drug_screen_report_id)
    {
        $this->drug_screen_report_id = $drug_screen_report_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrugScreenReport()
    {
        return $this->drug_screen_report;
    }

    /**
     * @param mixed $drug_screen_report
     *
     * @return self
     */
    public function setDrugScreenReport($drug_screen_report)
    {
        $this->drug_screen_report = $drug_screen_report;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrugScreenScheduledDate()
    {
        return $this->drug_screen_scheduled_date;
    }

    /**
     * @param mixed $drug_screen_scheduled_date
     *
     * @return self
     */
    public function setDrugScreenScheduledDate($drug_screen_scheduled_date)
    {
        $this->drug_screen_scheduled_date = $drug_screen_scheduled_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDrugScreenAccepted()
    {
        return $this->drug_screen_accepted;
    }

    /**
     * @param mixed $drug_screen_accepted
     *
     * @return self
     */
    public function setDrugScreenAccepted($drug_screen_accepted)
    {
        $this->drug_screen_accepted = $drug_screen_accepted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckInvitationId()
    {
        return $this->background_check_invitation_id;
    }

    /**
     * @param mixed $background_check_id
     *
     * @return self
     */
    public function setBackgroundCheckInvitationId($background_check_invitation_id)
    {
        $this->background_check_invitation_id = $background_check_invitation_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckReportId()
    {
        return $this->background_check_report_id;
    }

    /**
     * @param mixed $background_check_report_id
     *
     * @return self
     */
    public function setBackgroundCheckReportId($background_check_report_id)
    {
        $this->background_check_report_id = $background_check_report_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckStartedDate()
    {
        return $this->background_check_started_date;
    }

    /**
     * @param mixed $background_check_started_date
     *
     * @return self
     */
    public function setBackgroundCheckStartedDate($background_check_started_date)
    {
        $this->background_check_started_date = $background_check_started_date;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckReport()
    {
        return $this->background_check_report;
    }

    /**
     * @param mixed $background_check_report
     *
     * @return self
     */
    public function setBackgroundCheckReport($background_check_report)
    {
        $this->background_check_report = $background_check_report;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckReturned()
    {
        return $this->background_check_returned;
    }

    /**
     * @param mixed $background_check_returned
     *
     * @return self
     */
    public function setBackgroundCheckReturned($background_check_returned)
    {
        $this->background_check_returned = $background_check_returned;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckAccepted()
    {
        return $this->background_check_accepted;
    }

    /**
     * @param mixed $background_check_accepted
     *
     * @return self
     */
    public function setBackgroundCheckAccepted($background_check_accepted)
    {
        $this->background_check_accepted = $background_check_accepted;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckStatus()
    {
        return $this->background_check_status;
    }

    /**
     * @param mixed $background_check_status
     *
     * @return self
     */
    public function setBackgroundCheckStatus($background_check_status)
    {
        $this->background_check_status = $background_check_status;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckSignature()
    {
        return $this->background_check_signature;
    }

    /**
     * @param mixed $background_check_signature
     *
     * @return self
     */
    public function setBackgroundCheckSignature($background_check_signature)
    {
        $this->background_check_signature = $background_check_signature;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getBackgroundCheckSignedTime()
    {
        return $this->background_check_signed_time;
    }

    /**
     * @param mixed $background_check_signed_time
     *
     * @return self
     */
    public function setBackgroundCheckSignedTime($background_check_signed_time)
    {
        $this->background_check_signed_time = $background_check_signed_time;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getCheckrId()
    {
        return $this->checkr_id;
    }

    /**
     * @param mixed $checkr_id
     *
     * @return self
     */
    public function setCheckrId($checkr_id)
    {
        $this->checkr_id = $checkr_id;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getApplicationAccepted()
    {
        return $this->application_accepted;
    }

    /**
     * @param mixed $application_accepted
     *
     * @return self
     */
    public function setApplicationAccepted($application_accepted)
    {
        $this->application_accepted = $application_accepted;

        return $this;
    }
}