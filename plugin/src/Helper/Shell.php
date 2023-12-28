<?php

declare(strict_types=1);

namespace CrowdSec\Whm\Helper;

use CrowdSec\Whm\Exception;

class Shell extends Yaml
{
    public const NO_EXEC_FUNC = 'no_exec_func';
    private $commandWhitelist = [
        'cscli alerts list -l 0 -o json',
        'cscli bouncers list -o json',
        'cscli collections list -o json',
        'cscli decisions list -l 0 -o json',
        'cscli machines list -o json',
        'cscli parsers list -o json',
        'cscli postoverflows list -o json',
        'cscli scenarios list -o json',
        'cscli metrics -o json',
        'systemctl is-active crowdsec',
        'systemctl restart crowdsec',
        'crowdsec -t 2>&1',
        'systemctl show -p ActiveEnterTimestamp --value crowdsec',
    ];
    private $execFunc;
    private $readFileAcquisitions;

    /**
     * @throws Exception
     */
    public function checkConfig(): bool
    {
        $checkConfig = $this->exec('crowdsec -t 2>&1');
        if (0 !== $checkConfig['return_code']) {
            throw new Exception('Invalid config: ' . $checkConfig['output']);
        }

        return true;
    }

    public function exec(string $command): array
    {
        $execFunc = $this->getExecFunc();
        $returnCode = -1;
        if (self::NO_EXEC_FUNC === $execFunc) {
            return ['output' => self::NO_EXEC_FUNC, 'return_code' => $returnCode];
        }

        if (!in_array($command, $this->getWhitelist())) {
            return ['output' => 'Command not allowed', 'return_code' => $returnCode];
        }

        $command = $this->escapeShellCmd($command);

        ob_start();
        switch ($execFunc) {
            case 'exec':
                $output = [];
                $execFunc($command, $output, $returnCode);
                echo implode("\n", $output);
                break;
            default:
                $execFunc($command, $returnCode);
                break;
        }

        $output = ob_get_clean();

        return ['output' => (string) $output, 'return_code' => $returnCode];
    }

    public function getLastRestartSince(): int
    {
        return time() - $this->getLastRestart();
    }

    public function getReadFileAcquisitions(): array
    {
        if (null === $this->readFileAcquisitions) {
            $readAcquisitions = $this->getReadAcquisitionsBySource();
            $this->readFileAcquisitions = $readAcquisitions['file'] ?? [];
        }

        return $this->readFileAcquisitions;
    }

    /**
     * Check if exec functions are available.
     */
    public function hasNoExecFunc(): bool
    {
        return self::NO_EXEC_FUNC === $this->getExecFunc();
    }

    protected function getExecFunc(): string
    {
        if (null === $this->execFunc) {
            $result = self::NO_EXEC_FUNC;
            /**
             * @see https://www.php.net/manual/en/function.system.php
             * @see https://www.php.net/manual/en/function.passthru.php
             * @see https://www.php.net/manual/en/function.exec.php
             */
            $functions = ['system', 'passthru', 'exec'];
            foreach ($functions as $func) {
                if (function_exists($func)) {
                    $result = $func;
                    break;
                }
            }

            $this->execFunc = $result;
        }

        return $this->execFunc;
    }

    protected function getWhitelist(): array
    {
        return $this->commandWhitelist;
    }

    private function escapeShellCmd(string $command): string
    {
        $command = trim($command);
        $stderrToStdout = false;
        if (' 2>&1' === substr($command, -5)) {
            $command = substr($command, 0, -5);
            $stderrToStdout = true;
        }
        $command = escapeshellcmd($command);
        if ($stderrToStdout) {
            $command .= ' 2>&1';
        }

        return $command;
    }

    private function getAcquisitionMetrics(): array
    {
        $metrics = $this->getMetrics();

        return $metrics['acquisition'] ?? [];
    }

    private function getLastRestart(): int
    {
        $rawResult = $this->exec('systemctl show -p ActiveEnterTimestamp --value crowdsec');
        if (0 !== $rawResult['return_code']) {
            return 0;
        }

        return strtotime($rawResult['output']);
    }

    private function getMetrics(): array
    {
        $metrics = $this->exec('cscli metrics -o json');
        if (0 !== $metrics['return_code']) {
            return [];
        }

        return json_decode($metrics['output'], true);
    }

    private function getReadAcquisitionsBySource(): array
    {
        $metrics = $this->getAcquisitionMetrics();

        $result = [];

        foreach ($metrics as $key => $value) {
            preg_match('/^([^:]+):/', $key, $matches);
            if ((int) $value['reads'] > 0) {
                preg_match('/^([^:]+):([^:]+)$/', $key, $matches);
                if (3 === count($matches)) {
                    $result[$matches[1]][] = $matches[2];
                }
            }
        }

        return $result;
    }
}
