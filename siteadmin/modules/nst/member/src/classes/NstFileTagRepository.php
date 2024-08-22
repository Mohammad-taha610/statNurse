<?php

namespace nst\member;

use nst\system\NstDefaultRepository;
use nst\member\NstFileTag;
use sacore\application\ioc;
use sacore\application\app;

class NstFileTagRepository extends NstDefaultRepository
{
    /**
     * @return NstFileTag
     */
    public function createNewTagByName($name, $desc, $type, $show_providers) {
        try {
            /** @var NstFileTag $newTag */
            $newTag = ioc::resolve('NstFileTag');
            $newTag->setName($name);
            $newTag->setDescription($desc);
            $newTag->setType($type);
            $newTag->setShowInProviderPortal(filter_var(strtolower($show_providers), FILTER_VALIDATE_BOOL));
            app::$entityManager->persist($newTag);
            app::$entityManager->flush();
            return $newTag;
        } catch(\Throwable $t) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'willtest.txt', $t->getMessage(), FILE_APPEND);
            return null;
        }
    }

    /**
     * @param NstFileTag $fileTag
     * @return array
     */
    public function assignToDefault($fileTag) {
        $response = ['success' => false];

        if($fileTag && $fileTag instanceof NstFileTag){
            $files = ioc::getRepository('NstFile')->findBy(['tag' => $fileTag]);
            $defaultTag = $this->getDefaultTag();
            /** @var NstFile $file */
            foreach ($files as $file) {
                $file->setTag($defaultTag);
            }
            app::$entityManager->flush();
        }


        $response['success'] = true;
        return $response;
    }

    /**
     * Gets the Default tag 
     * Default tag is for assigning to files that have invalid tags
     * Creates the default tag if necessary
     * @return NstFileTag
     */
    public function getDefaultTag() {
        $defaultTag = $this->findOneBy(['name' => 'Default']);
        if ($defaultTag) {
            return $defaultTag;
        } else {
            return $this->createNewTagByName('Default', 'Default tag', 'Nurse', false);
        }
    }

    /**
     * @param NstFileTag $fileTag
     * @return array
     */
    public function deleteTag($fileTag) {
        $response = ['success' => false];
        try {
            if($fileTag && $fileTag instanceof NstFileTag) {
                app::$entityManager->remove($fileTag);
                app::$entityManager->flush();
                $response['success'] = true;
            } else {
                $response['message'] = 'object passed is not instance of NstFileTag';
            }
        } catch(\Throwable $t) {
            file_put_contents(app::get()->getConfiguration()->get('tempDir')->getValue() . DIRECTORY_SEPARATOR . 'willtest.txt', $t->getMessage(), FILE_APPEND);
            return $response;
        }

        return $response;
    }

    public function updateTag($tagId, $name = null, $desc = null, $type = null, $show_providers = null) {     
        $response = ['success' => false];
        /** @var NstFileTag $tag */   
        $tag = $this->findOneBy(['id' => $tagId]);

        if(!$tag) {
            $response['error_message'] = 'Invalid tag ID';

            return $response;
        } else {
            if (!empty($name) && $tag->getName() != $name) {
                $tag->setName($name);
            }

            if (!empty($desc) && $tag->getDescription() != $desc) {
                $tag->setDescription($desc);
            }

            if (!empty($type) && $tag->getType() != $type) {
                $tag->setType($type);
            }
            
            if (!empty($show_providers) && $tag->getShowInProviderPortal() != filter_var($show_providers, FILTER_VALIDATE_BOOL)) {
                $tag->setShowInProviderPortal(filter_var($show_providers, FILTER_VALIDATE_BOOL));
            }

            app::$entityManager->flush();
        }

        $response['success'] = true;

        return $response;
    }

}