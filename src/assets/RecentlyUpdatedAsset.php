<?php

namespace jdsdev\recentlyupdated\assets;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * Class RecentlyUpdatedAsset
 * @since 2.0.0
 */
class RecentlyUpdatedAsset extends AssetBundle
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = '@jdsdev/recentlyupdated/assets';

        $this->depends = [
            CpAsset::class
        ];

        $this->js = [
            'js/RecentlyUpdatedWidget.js',
        ];

        parent::init();
    }
}
