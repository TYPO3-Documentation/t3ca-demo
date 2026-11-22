<?php

declare(strict_types=1);

namespace Mtosafi\Sitepackage\Search;

use Tpwd\KeSearch\Plugins\ResultlistPlugin;

/**
 * Hands the search word to the template of the ke_search result list, which says how
 * many results there are for it. The extension gives it to the search box only.
 *
 * Registered as hook "modifyResultList" in ext_localconf.php. The word goes in as the
 * visitor typed it; Fluid escapes it on output.
 */
final class AddSearchWordToResultList
{
    public function modifyResultList(array &$fluidTemplateVariables, ResultlistPlugin $plugin): void
    {
        $fluidTemplateVariables['searchword'] = trim((string)($plugin->piVars['sword'] ?? ''));
    }
}
