<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Domain\Model;

/**
 * The registration of an event, for the templates.
 *
 * EXT:news merges the body of this class into GeorgRinger\News\Domain\Model\News
 * through its ClassCacheManager (registered in ext_localconf.php), as EXT:eventnews
 * does with its own. In that generated file every unqualified class name resolves
 * against the GeorgRinger\News\Domain\Model namespace, so references here stay fully
 * qualified. The class itself is never instantiated.
 */
class News extends \GeorgRinger\News\Domain\Model\News
{
    protected string $txMtosafisitepackageRegistrationLink = '';

    protected string $txMtosafisitepackageRegistrationLabel = '';

    public function getRegistrationLink(): string
    {
        return $this->txMtosafisitepackageRegistrationLink;
    }

    public function getRegistrationLabel(): string
    {
        return $this->txMtosafisitepackageRegistrationLabel;
    }
}
