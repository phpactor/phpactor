<?php

namespace Phpactor;

use XdgBaseDir\Xdg;
use Webmozart\PathUtil\Path;
use Symfony\Component\Yaml\Yaml;

class Phpactor
{
    /**
     * If the path is relative we need to use the current working path
     * because otherwise it will be the script path, which is wrong in the
     * context of a PHAR.
     *
     * @param string $path
     *
     * @return string
     */
    public static function normalizePath(string $path): string
    {
        if (substr($path, 0, 1) == DIRECTORY_SEPARATOR) {
            return $path;
        }

        return getcwd().DIRECTORY_SEPARATOR.$path;
    }

    public static function relativizePath(string $path): string
    {
        if (0 === strpos($path, getcwd())) {
            return substr($path, strlen(getcwd()) + 1);
        }

        return $path;
    }

    public static function isFile(string $string)
    {
        $containsInvalidNamespaceChars = (bool) preg_match('{[\.\*/]}', $string);

        if ($containsInvalidNamespaceChars) {
            return true;
        }

        if (true === file_exists($string)) {
            return true;
        }

        return false;
    }

    public static function loadConfig(): array
    {
        $xdg = new Xdg();
        $configDirs = $xdg->getConfigDirs();

        $configPaths = array_map(function ($configPath) {
            return Path::join($configPath, '/phpactor/phpactor.yml');
        }, $configDirs);

        $localConfigPath = Path::join(getcwd(), '.phpactor.yml');
        if (file_exists($localConfigPath)) {
            $configPaths[] = $localConfigPath;
        }

        $config = [];
        foreach ($configPaths as $configPath) {
            if (file_exists($configPath)) {
                $config = array_merge_recursive(
                    $config,
                    (array) Yaml::parse(file_get_contents($configPath))
                );
            }
        }

        $templatePaths = array_map(function ($configPath) {
            return Path::join($configPath, '/phpactor/templates');
        }, $configDirs);
        $templatePaths[] = Path::join(getcwd(), '.phpactor/templates');
        $templatePaths[] = __DIR__ . '/../../../templates';

        $templatePaths = array_filter($templatePaths, function ($templatePath) {
            return file_exists($templatePath);
        });
        $config['code_transform.template_paths'] = $templatePaths;

        return $config;
    }
}
