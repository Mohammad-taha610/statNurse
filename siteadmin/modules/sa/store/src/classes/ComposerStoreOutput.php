<?php

namespace sa\store;

use Doctrine\Common\Cache\Cache;
use Symfony\Component\Console\Output\OutputInterface;

class ComposerStoreOutput extends \Symfony\Component\Console\Output\Output implements OutputInterface
{
    /** @var Cache */
    private $writeCache;

    public function setWriteCache($cache)
    {
        $this->writeCache = $cache;
    }

    /**
     * Writes a message to the output.
     *
     * @param  string  $message A message to write to the output
     * @param  bool  $newline Whether to add a newline or not
     */
    protected function doWrite($message, $newline = true)
    {
        if ($this->writeCache) {
            $log = $this->writeCache->fetch('log');
            $log .= $message.($newline ? "\n" : '');
            $this->writeCache->save('log', $log, saStoreController::STORE_CACHE_TTL);
        }
    }
}
