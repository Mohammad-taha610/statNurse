<?php

namespace sa\system;
use Doctrine\Common\Util\Debug;
use sacore\application\app;
use sacore\application\Exception;
use sacore\application\ioc;

/**
 * saEntityUuidRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class saEntityUuidRepository extends \sacore\application\DefaultRepository
{

    /**
     * @param $entity_name
     * @param null $id
     * @param null $uuid
     * @param bool $issue
     * @return saEntityUuid
     * @throws Exception
     * @throws \sacore\application\IocDuplicateClassException
     * @throws \sacore\application\IocException
     */
    public function getUuid($entity_name, $id=null, $uuid=null, $issue=true) {

        if (empty($id) && empty($uuid)) {
            throw new Exception('An id or uuid is required. Please specify either one of the params.');
        }

        if ($uuid) {
            $uuidObj = ioc::get('saEntityUuid', array('uuid'=>$uuid, 'name'=>$entity_name));
        }
        else
        {
            $uuidObj = ioc::get('saEntityUuid', array('entity_id'=>$id, 'name'=>$entity_name));
        }


        if (!$uuidObj && $issue) {
            if (empty($id)) {
                throw new Exception('To generate a new uuid, an entity id is required.');
            }
            /** @var saEntityUuid $uuidObj */
            $uuidObj = ioc::get('saEntityUuid');
            $uuidObj->setEntityId( $id );
            $uuidObj->setName( $entity_name );
            $uuidObj->setUuid( empty($uuid) ? static::uuid() : $uuid );
            app::$entityManager->persist($uuidObj);
            app::$entityManager->flush($uuidObj);
        }

        return $uuidObj;
    }

    /**
     * Generates unique ids
     *
     * @return string
     */
    private static function uuid()
    {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),

            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,

            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,

            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }


}
