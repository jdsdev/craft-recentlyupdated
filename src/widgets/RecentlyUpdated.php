<?php

namespace jdsdev\recentlyupdated\widgets;

use jdsdev\recentlyupdated\assets\RecentlyUpdatedAsset;

use Craft;
use craft\base\Widget;
use craft\elements\Entry;
use craft\helpers\Json;
use craft\models\Section;

/**
 * Class RecentlyUpdated
 *
 * @package RecentlyUpdated
 * @author  Jonathan Sarmiento
 * @since   2.0.0
 */
class RecentlyUpdated extends Widget
{
  // Static
  // ===================================================================

  /**
   * @inheritdoc
   */
  public static function displayName(): string
  {
    return Craft::t('app', 'Recently Updated');
  }

  /**
   * @inheritdoc
   */
  public static function iconPath()
  {
    return Craft::getAlias('@jdsdev/recentlyupdated/assets/icon.svg');
  }

  // Properties
  // ===================================================================

  /**
   * @var string|int[] Section IDs that widget should pull entries from
   */
  public $section = '*';

  /**
   * string Site ID that widget should pull entries from
   */
  public $siteId;

  /**
   * int Total number of entries that widget should show
   */
  public $limit = 10;

  // Public Methods
  // ===================================================================

  /**
   * @inheritdoc
   */
  public function init()
  {
    parent::init();

    if ($this->siteId === null) {
      $this->siteId = Craft::$app->getSites()->getCurrentSite()->id;
    }
  }

  /**
   * @inheritdoc
   */
  public function rules()
  {
    $rules = parent::rules();
    $rules[] = [['siteId', 'limit'], 'number', 'integerOnly' => true];

    return $rules;
  }

  /**
   * @inheritdoc
   */
  public function getSettingsHtml()
  {
    $view = Craft::$app->getView();

    return $view->renderTemplate('recently-updated/settings', [
      'widget' => $this,
    ]);
  }

  /**
   * @inheritdoc
   */
  public function getTitle(): string
  {
    if (is_numeric($this->section)) {
      $section = Craft::$app->getSections()->getSectionById($this->section);

      if ($section) {
        $title = Craft::t('app', 'Recently Updated {section}', [
          'section' => Craft::t('site', $section->name),
        ]);
      }
    }

    /** @noinspection UnSafeIsSetOverArrayInspection - FP */
    if (!isset($title)) {
      $title = Craft::t('app', 'Recently Updated Entries');
    }

    // See if they are pulling entries from a different site
    $targetSiteId = $this->_getTargetSiteId();

    if ($targetSiteId !== false && $targetSiteId != Craft::$app->getSites()->getCurrentSite()->id) {
      $site = Craft::$app->getSites()->getSiteById($targetSiteId);

      if ($site) {
        $title = Craft::t('app', '{title} ({site})', [
          'title' => $title,
          'site' => Craft::t('site', $site->name),
        ]);
      }
    }

    return $title;
  }

  /**
   * @inheritdoc
   */
  public function getBodyHtml()
  {
    $params = [];

    if (is_numeric($this->section)) {
      $params['sectionId'] = (int)$this->section;
    }

    $view = Craft::$app->getView();

    $view->registerAssetBundle(RecentlyUpdatedAsset::class);
    $js = 'new Craft.RecentlyUpdatedWidget('.$this->id.', '.Json::encode($params).');';
    $view->registerJs($js);

    $entries = $this->_getEntries();

    return $view->renderTemplate('recently-updated/body', [
      'entries' => $entries,
      'siteId' => $this->siteId,
    ]);
  }

  // Private Methods
  // ===================================================================

  /**
   * Returns recently updated entries,
   * based on widget settings and user permissions.
   *
   * @return array
   */
  private function _getEntries(): array
  {
    $targetSiteId = $this->_getTargetSiteId();

    if ($targetSiteId === false) {
      // Hopeless
      return [];
    }

    // Normalize target section ID value.
    $editableSectionIds = $this->_getEditableSectionIds();
    $targetSectionId = $this->section;

    if (!$targetSectionId || $targetSectionId === '*' || !in_array($targetSectionId, $editableSectionIds, false)) {
      $targetSectionId = array_merge($editableSectionIds);
    }

    if (!$targetSectionId) {
      return [];
    }

    $query = Entry::find();
    $query->status(null);
    $query->enabledForSite(false);
    $query->siteId($targetSiteId);
    $query->sectionId($targetSectionId);
    $query->editable(true);
    $query->limit($this->limit ?: 100);
    $query->orderBy('elements.dateUpdated desc');

    return $query->all();
  }

  /**
   * Returns IDs of Channels and Structures that user is allowed to edit.
   *
   * @return array
   */
  private function _getEditableSectionIds(): array
  {
    $sectionIds = [];

    foreach (Craft::$app->getSections()->getEditableSections() as $section) {
      if ($section->type != Section::TYPE_SINGLE) {
        $sectionIds[] = $section->id;
      }
    }

    return $sectionIds;
  }

  /**
   * Returns target site ID for widget.
   *
   * @return string|false
   */
  private function _getTargetSiteId()
  {
    if (!Craft::$app->getIsMultiSite()) {
      return $this->siteId;
    }

    // Make sure that user is allowed to edit entries in current site.
    // Otherwise grab entries in their first editable site.

    // Figure out which sites user is allowed to edit
    $editableSiteIds = Craft::$app->getSites()->getEditableSiteIds();

    // If user isn't allowed to edit *any* sites, return false
    if (empty($editableSiteIds)) {
      return false;
    }

    // Figure out which site was selected in settings
    $targetSiteId = $this->siteId;

    // Only use site if it exists and user is allowed to edit it.
    // Otherwise go with first site that user is allowed to edit.
    if (!in_array($targetSiteId, $editableSiteIds, false)) {
      $targetSiteId = $editableSiteIds[0];
    }

    return $targetSiteId;
  }
}
