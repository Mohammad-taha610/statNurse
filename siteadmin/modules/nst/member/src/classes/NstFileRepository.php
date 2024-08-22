<?php

namespace nst\member;

use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use sacore\application\app;
use sacore\application\DateTime;
use sacore\application\Exception;
use sacore\application\ioc;
use sacore\application\modRequest;
use sa\events\Event;
use sa\events\EventRecurrenceRepository;
use sa\member\auth;
use sa\files\saFile;
use sacore\utilities\doctrineUtils;
use nst\member\NstFile;

/**
 * Class NstFileRepository
 * @package nst\member
 */
class NstFileRepository extends \sa\files\saFileRepository
{
    public function findFileByFileTag($tagId)
    {
        $q = $this->createQueryBuilder('n')
            ->select('n')
            ->innerJoin('n.tag', 't')
            ->where('t.id = :tag_id')
            ->setParameter(':tag_id', $tagId);

        return $q->getQuery()->getArrayResult();
    }
    /** This method checks for a file with a tag for the given year
     *  The year should be a string with a trailing %, ex: '2022%'
    */
    public function nurseHasFileWithTagInYear( $year, $tagId, $nurseId ) {
        // Commented out year for now and returning end() of array to get the latest 1099 generation
        $q = $this->createQueryBuilder( 'n' )
                  ->select( 'n' )
                  ->innerJoin('n.nurse', 's')
                  ->innerJoin('n.tag', 't')
                  ->where('s.id = :nurse_id')
                //   ->andWhere( 'n.date_created LIKE :year' )
                  ->andWhere( 't.id = :tag' )
                //   ->setParameter( ':year', $year )
                  ->setParameter( ':tag', $tagId )
                  ->setParameter( ':nurse_id', $nurseId );

        $file = end($q->getQuery()->getArrayResult());

        if ($file) {
            $response['route'] = app::get()->getRouter()->generate('files_browser_view_file', ['folder' => $file['folder'], 'file' => $file['filename']]);
            $response['file_id'] = $file['id'];

            return $response;
        } else return null;
    }

    public function filePathFor1099( $nurseId, $tagId ) {

        $q = $this->createQueryBuilder( 'n' )
                  ->select( 'n' )
                  ->where( 'n.nurse = :nurse_id' )
                  ->andWhere( 'n.tag = :tag_id' )
                  ->setParameter( ':nurse_id', $nurseId )
                  ->setParameter( ':tag_id', $tagId );

        $diskFileName = $q->getQuery()->getArrayResult()[0]['disk_file_name'];

        if ($diskFileName) {
            return $diskFileName;
        } else return null;
    }
}