<?php

namespace jdsdev\recentlyupdated;

use Craft;
use craft\events\RegisterComponentTypesEvent;
use craft\services\Dashboard;
use craft\web\twig\variables\CraftVariable;
use jdsdev\recentlyupdated\variables\RecentlyUpdatedVariable;
use jdsdev\recentlyupdated\widgets\RecentlyUpdated;
use yii\base\Event;

/**
 * Add a dashboard widget that shows recently updated entries
 *
 * Shameless ripoff of Craft's Recent Entries Widget
 *
 * @package RecentlyUpdated
 * @author  Jonathan Sarmiento
 * @since   2.0.0
 */
class Plugin extends \craft\base\Plugin
{
    /**
    * @inheritdoc
    */
    public $schemaVersion = '1.0.0';

    /**
    * @inheritdoc
    */
    public $hasCpSettings = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (!Craft::$app->getRequest()->getIsCpRequest()) {
            return false;
        }

        // Register variables
        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('recentlyUpdated', RecentlyUpdatedVariable::class);
            }
        );

        // Register Dashboard Widgets
        Event::on(
            Dashboard::class,
            Dashboard::EVENT_REGISTER_WIDGET_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = RecentlyUpdated::class;
            }
        );
    }
}
