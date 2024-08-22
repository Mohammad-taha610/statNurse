<?php

namespace App\DTO\Member;

use App\Entity\Nst\Member\Nurse;

class NurseDTO
{
    public string $id;
    public string $firstName;
    public string $lastName;
    public string $fullName;
    public string $credentials;
    public string $nurseRoute;
    public string $middleName;
    public string $email;
    public string $phoneNumber;
    public string $birthDate;

    public function __construct(Nurse $nurse)
    {
        $this->id = $nurse->getId();
        $this->firstName = $nurse->getFirstName();
        $this->lastName = $nurse->getLastName();
        $this->fullName = $this->firstName . ' ' . $this->lastName;
        $this->credentials = $nurse->getCredentials();
        $this->middleName = $nurse->getMiddleName();
        $this->email = $nurse->getEmailAddress();
        $this->phoneNumber = $nurse->getPhoneNumber();
        if ($nurse->getDateOfBirth() !== null) {
            $this->birthDate = $nurse->getDateOfBirth()->format('Y-m-d');
        }
        $this->nurseRoute = "/executive/nurse/" . $nurse->getId();

    }
}
