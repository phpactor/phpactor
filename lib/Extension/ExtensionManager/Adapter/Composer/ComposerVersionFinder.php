<?php

namespace Phpactor\Extension\ExtensionManager\Adapter\Composer;

use Composer\Package\Version\VersionSelector;
use Phpactor\Extension\ExtensionManager\Model\VersionFinder;
use RuntimeException;

class ComposerVersionFinder implements VersionFinder
{
    /**
     * @var VersionSelector
     */
    private $selector;

    /**
     * @var string
     */
    private $minimumStability;


    public function __construct(VersionSelector $selector, string $minimumStability)
    {
        $this->selector = $selector;
        $this->minimumStability = $minimumStability;
    }

    public function findBestVersion(string $extensionName): string
    {
        $package = $this->selector->findBestCandidate($extensionName, null, null, $this->minimumStability);

        if (is_bool($package)) {
            throw new RuntimeException(sprintf(
                'Could not find suitable version for extension "%s"',
                $extensionName
            ));
        }

        $requireVersion = $this->selector->findRecommendedRequireVersion($package);

        return $requireVersion;
    }
}
