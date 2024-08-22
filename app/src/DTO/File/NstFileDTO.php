<?php

namespace App\DTO\File;

class NstFileDTO
{
    /**
     * 'id' => $file->getId(),
     * 'filename' => $file->getFilename(),
     * 'type' => $file->getFileType(),
     * 'route' => app::get()->getRouter()->generate('files_browser_view_file_by_id', ['id' => $file->getId()]),
     * 'tag' => $file->getTag() ? [
     *
     * 'id' => $file->getTag()->getId(),
     * 'name' => $file->getTag()->getName()
     * ] : []
     */

    public int $id;
    public string $filename;
    public string $type;
    public string $route;
    public NstFileTagDTO $tag;

    public function __construct($file)
    {
        $this->id = $file->getId();
        $this->filename = $file->getFilename();
        $this->type = $file->getFileType();
        $this->route = "/assets/files/id/{$file->getId()}";
        $this->tag = $file->getTag() ? new NstFileTagDTO($file->getTag()) : null;
    }
}
