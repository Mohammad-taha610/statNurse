<?php

namespace nst\applications;

use sacore\utilities\doctrineUtils;
use nst\member\NurseApplication;

class NurseService
{
    public function storeApplication($data)
    {
        $nurseApplication = doctrineUtils::setEntityData(array_merge(...(array_values($data['application']))), new NurseApplication);
    }
}
