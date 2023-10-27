<?php

declare(strict_types=1);
namespace Crowdsec;

use Symfony\Component\Yaml\Yaml;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Exception\DumpException;

class CrowdsecUtils
{
    private const CROWDSEC_DIR = '/etc/crowdsec';
    private const ACQUIS_DIR = 'acquis.d';
    private const ACQUIS_FILE = 'acquis.yaml';
    private const CONFIG_FILE = 'config.yaml';

    /**
     * Retrieve yaml content as an array
     *
     * @param $filepath
     * @return array|mixed
     */
    private static function getYamlContent($filepath)
    {
        $result = [];

        try {
            $result = Yaml::parseFile($filepath);
        } catch (ParseException $exception) {
            syslog(LOG_ERR, 'Unable to parse ' . $filepath . ': ' . $exception->getMessage());
        }

        return $result;
    }

    private static function getYamlContentFromString($value)
    {
        $result = [];

        try {
            $parser = new Parser();
            $result = $parser->parse($value);
        } catch (ParseException $exception) {
            syslog(LOG_ERR, 'Unable to parse string' . $value . ': ' . $exception->getMessage());
        }

        return $result;
    }

    public static function callApi($user, $token, $query): array
    {
        $result = [];

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);

        $header[0] = "Authorization: whm $user:$token";
        curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
        curl_setopt($curl, CURLOPT_URL, $query);

        $curlResult = curl_exec($curl);

        $http_status = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        if ($http_status != 200) {
            $result['error'] = '[!] Error: ' . $http_status . ' returned';
        } else {
            $result = (array) json_decode($curlResult);
        }

        curl_close($curl);

        return $result;
    }

    public static function getConfig(): array
    {
        return self::getYamlContent(self::CROWDSEC_DIR . '/' . self::CONFIG_FILE);
    }

    public static function getAcquis(): array
    {
        $filePath = self::CROWDSEC_DIR . '/' . self::ACQUIS_FILE;

        self::backupFile($filePath);
        $mainAcquis = ['path' => $filePath, 'contents' => self::getYamlFileContents($filePath)];
        $overrideAcquis = self::getAllAcquisFiles();

        $result = [];

        $allAcquis = array_merge($mainAcquis, $overrideAcquis);

        foreach ($allAcquis['contents'] as $acquis) {
            $result[] = self::getYamlContentFromString($acquis);
        }

        return $result;
    }

    public static function getFileContents($file): string
    {
        if (is_file($file)) {
            $contents = self::system('cat ' . $file);
        } else {
            $contents = $file . ' not found';
        }

        return $contents;
    }

    public static function getYamlFileContents($file): array
    {
        $multiFileContents = explode('---', self::getFileContents($file));
        $lastIndex = count($multiFileContents) - 1;
        if (
            count($multiFileContents)
            && strlen($multiFileContents[$lastIndex]) < 2
        ) {
            unset($multiFileContents[(int)$lastIndex]);
        }

        return $multiFileContents;
    }

    private static function getAllAcquisFiles(): array
    {
        $acquisOverrideDir = self::CROWDSEC_DIR . '/' . self::ACQUIS_DIR;
        $foundFiles = self::system('find ' . $acquisOverrideDir . ' -type f | tr "\n" "+++"');
        $overrideFiles = explode('+++', $foundFiles);
        $acquisFiles = [];

        foreach ($overrideFiles as $i => $filePath) {
            self::backupFile($filePath);
            $acquisFiles[] = ['path' => $filePath, 'contents' => (self::getYamlFileContents($filePath))];
        }

        return $acquisFiles;
    }

    private static function backupFile($filePath): void
    {
        if (!self::fileExists($filePath . '.orig')) {
            self::copyFile($filePath, $filePath . '.orig');
        }

        self::copyFile($filePath, $filePath . '.bak');
    }

    private static function restoreFile($filePath, $fromOrig = false): void
    {
        $ext = $fromOrig ? '.orig' : '.bak';

        if (self::fileExists($filePath . $ext)) {
            self::copyFile($filePath . $ext, $filePath);
        }
    }

    private static function fileExists($filePath): bool
    {
        $result = self::system('ls ' . $filePath . ' | wc -l');

        return $result !== '0';
    }

    private static function copyFile($filePath, $ext = 'bak'): void
    {
        self::system("cp {$filePath} {$filePath}.{$ext}");
    }

    public static function system($cmd): string
    {
        ob_start();
        system($cmd);
        $result = ob_get_contents();
        ob_end_clean();

        return $result ?? '';
    }
}
