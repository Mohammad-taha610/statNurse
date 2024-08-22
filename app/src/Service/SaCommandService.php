<?php

namespace App\Service;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class SaCommandService
{
    private string $phpPath;
    private string $saConsolePath;

    public function __construct()
    {
        $this->phpPath = $_SERVER['PHP_PATH'];
        $this->saConsolePath = $_SERVER['SACONSOLE_PATH'];
    }

    public function executeSaCommand($commandName, array $params = []): string
    {
        $phpPath = strpos($this->phpPath, ' ') !== false ? "\"{$this->phpPath}\"" : $this->phpPath;
        $saconsolePath = strpos($this->saConsolePath, ' ') !== false ? "\"{$this->saConsolePath}\"" : $this->saConsolePath;

        $argString = '';
        foreach ($params as $name => $value) {
            $argString .= sprintf('--%s=%s ', $name, escapeshellarg($value));
        }

        $command = sprintf('%s %s %s %s', $phpPath, $saconsolePath, $commandName, $argString);

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }

    public function executeSaCommandWithJson($commandName, string $json): string
    {
        $phpPath = strpos($this->phpPath, ' ') !== false ? "\"{$this->phpPath}\"" : $this->phpPath;
        $saconsolePath = strpos($this->saConsolePath, ' ') !== false ? "\"{$this->saConsolePath}\"" : $this->saConsolePath;

        $argString = sprintf('--%s=%s ', 'data', escapeshellarg($json));

        $command = sprintf('%s %s %s %s', $phpPath, $saconsolePath, $commandName, $argString);

        $process = Process::fromShellCommandline($command);
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        return $process->getOutput();
    }
}
