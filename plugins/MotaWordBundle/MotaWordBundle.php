<?php

namespace MauticPlugin\MotaWordBundle;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\PluginBundle\Bundle\PluginBundleBase;
use Mautic\PluginBundle\Entity\Plugin;

class MotaWordBundle extends PluginBundleBase
{
    /**
     * Called by PluginController::reloadAction when adding a new plugin that's not already installed.
     *
     * @param Plugin        $plugin
     * @param MauticFactory $factory
     * @param null          $metadata
     * @param null          $installedSchema
     */
    public static function onPluginInstall(Plugin $plugin, MauticFactory $factory, $metadata = null, $installedSchema = null)
    {
        // @todo add custom field to Contacts for MW ID (contacts are "Leads" in the codebase)

        parent::onPluginInstall($plugin, $factory, $metadata, $installedSchema);
    }
}
