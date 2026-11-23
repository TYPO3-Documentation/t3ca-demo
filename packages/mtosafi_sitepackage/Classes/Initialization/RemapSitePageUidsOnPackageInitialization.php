<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Initialization;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\Exception\SiteConfigurationWriteException;
use TYPO3\CMS\Core\Configuration\SiteConfiguration;
use TYPO3\CMS\Core\Configuration\SiteWriter;
use TYPO3\CMS\Core\Package\Event\PackageInitializationEvent;
use TYPO3\CMS\Core\Site\SiteSettingsFactory;
use TYPO3\CMS\Impexp\Import;
use TYPO3\CMS\Impexp\Initialization\ImportContentOnPackageInitialization;
use TYPO3\CMS\Impexp\Initialization\ImportSiteConfigurationsOnPackageInitialization;

/**
 * Points the site configuration at the pages Initialisation/data.xml brought, after
 * the import.
 *
 * The site settings mtosafi.navigation.serviceMenu, footerMenu and searchPage,
 * mtosafi.events.detailPage and the 404 error handling name pages by uid. Those are the uids the pages had on the
 * installation the export was made on. The import gives every page a new uid and
 * remaps only the root page of a site it copies, so this listener translates the rest
 * with the map the import kept.
 *
 * It runs only in the setup that imported the data, and only for the site "main"
 * where its root page is one of the imported pages. A uid the import did not bring
 * stays as it is.
 */
#[AsEventListener(after: ImportSiteConfigurationsOnPackageInitialization::class)]
final readonly class RemapSitePageUidsOnPackageInitialization
{
    private const EXTENSION_KEY = 'mtosafi_sitepackage';
    private const SITE_IDENTIFIER = 'main';

    /**
     * The site settings that hold a page uid, as their path in settings.yaml.
     */
    private const PAGE_SETTINGS = [
        ['mtosafi', 'navigation', 'serviceMenu'],
        ['mtosafi', 'navigation', 'footerMenu'],
        ['mtosafi', 'navigation', 'searchPage'],
        ['mtosafi', 'events', 'detailPage'],
    ];

    private const PAGE_LINK_PATTERN = '/^t3:\/\/page\?uid=(\d+)$/';

    public function __construct(
        private SiteConfiguration $siteConfiguration,
        private SiteSettingsFactory $siteSettingsFactory,
        private SiteWriter $siteWriter,
        #[Autowire(service: 'cache.core')]
        private FrontendInterface $codeCache,
        private LoggerInterface $logger,
    ) {}

    public function __invoke(PackageInitializationEvent $event): void
    {
        if ($event->getExtensionKey() !== self::EXTENSION_KEY
            || !$event->hasStorageEntry(ImportContentOnPackageInitialization::class)
        ) {
            return;
        }
        $import = $event->getStorageEntry(ImportContentOnPackageInitialization::class)->getResult()['import'] ?? null;
        if (!$import instanceof Import) {
            return;
        }
        $importedPages = array_map('intval', $import->getImportMapId()['pages'] ?? []);
        if (!isset($this->siteConfiguration->getAllExistingSites(false)[self::SITE_IDENTIFIER])) {
            return;
        }
        $configuration = $this->siteConfiguration->load(self::SITE_IDENTIFIER);
        if (!in_array((int)($configuration['rootPageId'] ?? 0), $importedPages, true)) {
            $this->logger->info('The root page of site "' . self::SITE_IDENTIFIER . '" was not imported: its page uids were left as they are.');
            return;
        }

        try {
            $this->remapErrorHandling($configuration, $importedPages);
            $this->remapSettings($importedPages);
        } catch (SiteConfigurationWriteException $e) {
            $this->logger->warning('The page uids of site "' . self::SITE_IDENTIFIER . '" could not be remapped: ' . $e->getMessage());
        }
        // SiteWriter::writeSettings() leaves the cached site configuration alone (#103804).
        $this->codeCache->flush();
    }

    /**
     * @param array<int, int> $importedPages exported uid => imported uid
     */
    private function remapErrorHandling(array $configuration, array $importedPages): void
    {
        $changed = false;
        foreach ($configuration['errorHandling'] ?? [] as $index => $errorHandler) {
            $source = (string)($errorHandler['errorContentSource'] ?? '');
            if (preg_match(self::PAGE_LINK_PATTERN, $source, $matches) !== 1) {
                continue;
            }
            $uid = $importedPages[(int)$matches[1]] ?? null;
            if ($uid === null || $uid === (int)$matches[1]) {
                continue;
            }
            $configuration['errorHandling'][$index]['errorContentSource'] = 't3://page?uid=' . $uid;
            $changed = true;
        }
        if ($changed) {
            $this->siteWriter->write(self::SITE_IDENTIFIER, $configuration);
        }
    }

    /**
     * @param array<int, int> $importedPages exported uid => imported uid
     */
    private function remapSettings(array $importedPages): void
    {
        $settings = $this->siteSettingsFactory->loadLocalSettings(self::SITE_IDENTIFIER) ?? [];
        $changed = false;
        foreach (self::PAGE_SETTINGS as $path) {
            $value = $this->valueAt($settings, $path);
            if (!is_int($value) && !(is_string($value) && ctype_digit($value))) {
                continue;
            }
            $uid = $importedPages[(int)$value] ?? null;
            if ($uid === null || $uid === (int)$value) {
                continue;
            }
            $this->setValueAt($settings, $path, $uid);
            $changed = true;
        }
        if ($changed) {
            $this->siteWriter->writeSettings(self::SITE_IDENTIFIER, $settings);
        }
    }

    /**
     * A setting is written nested or as one dotted key; both read the same.
     *
     * @param list<string> $path
     */
    private function valueAt(array $settings, array $path): mixed
    {
        $dotted = implode('.', $path);
        if (array_key_exists($dotted, $settings)) {
            return $settings[$dotted];
        }
        foreach ($path as $key) {
            if (!is_array($settings) || !array_key_exists($key, $settings)) {
                return null;
            }
            $settings = $settings[$key];
        }
        return $settings;
    }

    /**
     * @param list<string> $path
     */
    private function setValueAt(array &$settings, array $path, int $value): void
    {
        $dotted = implode('.', $path);
        if (array_key_exists($dotted, $settings)) {
            $settings[$dotted] = $value;
            return;
        }
        $node = &$settings;
        foreach ($path as $key) {
            $node = &$node[$key];
        }
        $node = $value;
    }
}
