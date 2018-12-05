<?php

namespace MauticPlugin\MotaWordBundle;

use Mautic\CoreBundle\Factory\MauticFactory;
use Mautic\LeadBundle\Entity\LeadField;
use Mautic\LeadBundle\Entity\LeadFieldRepository;
use Mautic\LeadBundle\Model\FieldModel;
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
        try {
            /** @var FieldModel $model */
            $model = $factory->getModel('lead.field');

            $field = self::createMWIDField(self::getNextOrder($model));

            $model->saveEntity($field);

            parent::onPluginInstall($plugin, $factory, $metadata, $installedSchema);
        } catch (\Exception $e) {
            error_log($e);
        }
    }

    /**
     * @param FieldModel $model
     *
     * @return int
     */
    public static function getNextOrder($model)
    {
        /** @var LeadFieldRepository $repository */
        $repository = $model->getRepository();

        /** @var LeadField $lastLead */
        $lastLead = $repository->findOneBy([], ['order' => 'desc']);

        return $lastLead->getOrder() + 1;
    }

    /**
     * @param $order
     *
     * @return LeadField
     */
    public static function createMWIDField($order)
    {
        $field = new LeadField();
        $field->isPublished(true);
        $field->setLabel('Motaword ID');
        $field->setAlias('mw_id');
        $field->setType('number');
        $field->setGroup('core');
        $field->setIsRequired(true);
        $field->setIsFixed(true);
        $field->setIsVisible(true);
        $field->setIsShortVisible(true);
        $field->setIsListable(true);
        $field->setIsPubliclyUpdatable(false);
        $field->setIsUniqueIdentifer(true);
        $field->setObject('lead');
        $field->setOrder($order);

        return $field;
    }
}
