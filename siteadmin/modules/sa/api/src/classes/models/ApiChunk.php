<?php

namespace sa\api;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\DiscriminatorColumn;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\HasLifecycleCallbacks;
use Doctrine\ORM\Mapping\InheritanceType;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="apiChunkRepository")
 * @InheritanceType("SINGLE_TABLE")
 * @DiscriminatorColumn(name="discriminator", type="string")
 * @HasLifecycleCallbacks
 * @Table(name="sa_api_chunks", indexes={
 *      @Index(name="IDX_sa_api_chuck_key", columns={"chunk_key"})
 * })
 */
class ApiChunk {


    /** @Id @Column(type="integer") @GeneratedValue */
    protected $id;

    /** @Column(type="string") */
    protected $chunk_key;

    /** @Column(type="integer") */
    protected $beginning_offset;

    /** @Column(type="integer") */
    protected $ending_offset;

    /** @Column(type="integer") */
    protected $size;

    /** @Column(type="integer") */
    protected $file_size;

    /** @Column(type="string") */
    protected $file_name;


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }
    

    /**
     * Set beginningOffset
     *
     * @param integer $beginningOffset
     *
     * @return ApiChunk
     */
    public function setBeginningOffset($beginningOffset)
    {
        $this->beginning_offset = $beginningOffset;

        return $this;
    }

    /**
     * Get beginningOffset
     *
     * @return integer
     */
    public function getBeginningOffset()
    {
        return $this->beginning_offset;
    }

    /**
     * Set endingOffset
     *
     * @param integer $endingOffset
     *
     * @return ApiChunk
     */
    public function setEndingOffset($endingOffset)
    {
        $this->ending_offset = $endingOffset;

        return $this;
    }

    /**
     * Get endingOffset
     *
     * @return integer
     */
    public function getEndingOffset()
    {
        return $this->ending_offset;
    }

    /**
     * Set size
     *
     * @param integer $size
     *
     * @return ApiChunk
     */
    public function setSize($size)
    {
        $this->size = $size;

        return $this;
    }

    /**
     * Get size
     *
     * @return integer
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * Set fileSize
     *
     * @param integer $fileSize
     *
     * @return ApiChunk
     */
    public function setFileSize($fileSize)
    {
        $this->file_size = $fileSize;

        return $this;
    }

    /**
     * Get fileSize
     *
     * @return integer
     */
    public function getFileSize()
    {
        return $this->file_size;
    }

    /**
     * Set fileSize
     *
     * @param string $fileName
     *
     * @return ApiChunk
     */
    public function setFileName($fileName)
    {
        $this->file_name = $fileName;

        return $this;
    }

    /**
     * Get fileName
     *
     * @return string
     */
    public function getFileName()
    {
        return $this->file_name;
    }

    /**
     * Set chunkKey
     *
     * @param string $chunkKey
     *
     * @return ApiChunk
     */
    public function setChunkKey($chunkKey)
    {
        $this->chunk_key = $chunkKey;

        return $this;
    }

    /**
     * Get chunkKey
     *
     * @return string
     */
    public function getChunkKey()
    {
        return $this->chunk_key;
    }
}
