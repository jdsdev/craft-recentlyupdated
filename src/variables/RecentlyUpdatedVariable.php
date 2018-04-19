<?php

namespace jdsdev\recentlyupdated\variables;

use Craft;
use craft\models\EntryVersion;

/**
 * Class RecentlyUpdatedVariable
 *
 * https://craftcms.com/docs/plugins/variables
 *
 * @package RecentlyUpdated
 * @author  Jonathan Sarmiento
 * @since   1.1.0
 */
class RecentlyUpdatedVariable
{
  /**
   * Returns versions for specied entry.
   *
   * @param int $entry The entry to search for.
   * @param int|null $site The site to search on.
   * @param int|null $limit The limit on the number of versions to retrieve.
   * @param bool $includeCurrent Whether to include the current "top" version of the entry.
   *
   * @return EntryVersion[]
   */
  public function getVersions($entryId, $siteId = null, $limit = 10, $includeCurrent = false): array
  {
    return Craft::$app->entryRevisions->getVersionsByEntryId($entryId, $siteId, $limit, $includeCurrent);
  }

  /**
   * Returns most recent version for specified entry.
   *
   * @param int $entry The entry to search for.
   * @param int|null $site The site to search on.
   *
   * @return EntryVersion
   */
  public function getCurrentVersion($entryId, $siteId = null): EntryVersion
  {
    $versions = Craft::$app->entryRevisions->getVersionsByEntryId($entryId, $siteId, 1, true);
    return reset($versions);
  }
}
