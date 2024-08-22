<?php

namespace App\Service;

use App\DTO\File\NstFileDTO;
use App\Entity\Nst\Member\Nurse;
use App\Repository\Nst\Member\NurseRepository;

class NurseService
{
    private NurseRepository $nurseRepository;

    public function __construct(NurseRepository $nurseRepository)
    {
        $this->nurseRepository = $nurseRepository;
    }

    public function getNurseById(int $id): Nurse
    {
        return $this->nurseRepository->find($id);
    }

    /**
     * @param Nurse $nurse
     * @return array|mixed
     */
    public function getProviderNurseFiles($nurse)
    {
        $files = $nurse->getNurseFiles();
        $filesForProviderPortal = array_filter($files->toArray(), function ($file) {
            return $file->getTag()?->getShowInProviderPortal();
        });

         usort($filesForProviderPortal, function ($a, $b) {
            $aName = $a->getTag()?->getName() ?? '';
            $bName = $b->getTag()?->getName() ?? '';
            return strcmp($aName, $bName);
        });
        return  array_map(function ($file) {
            return new NstFileDTO($file);
        }, $filesForProviderPortal);
    }
}
