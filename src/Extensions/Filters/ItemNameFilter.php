<?php //strict

namespace IO\Extensions\Filters;

use IO\Extensions\AbstractFilter;
use IO\Services\TemplateConfigService;
use IO\Services\TemplateTranslationService;

/**
 * Class ItemNameFilter
 * @package IO\Extensions\Filters
 */
class ItemNameFilter extends AbstractFilter
{
    private $defaultConfigItemName;
    private $defaultConfigItemDisplayName;

    /** @var TemplateTranslationService $translationService */
    private $translationService;

    /**
     * ItemNameFilter constructor.
     */
    public function __construct()
    {
        /** @var TemplateConfigService $configService */
        $configService = pluginApp( TemplateConfigService::class );

        $this->translationService = pluginApp(TemplateTranslationService::class);

        $this->defaultConfigItemName = $configService->get('item.name');
        $this->defaultConfigItemDisplayName = $configService->get('item.displayName');

        parent::__construct();
    }

    /**
     * Return the available filter methods
     * @return array
     */
    public function getFilters():array
    {
        return [
            "itemName" => "itemName"
        ];
    }

    /**
     * Build the item name from the configuration
     * @param object $itemData
     * @param string $configName
     * @param string $displayName
     * @return string
     */
    public function itemName( $itemData, $configName = null, $displayName = null )
    {
        if ( $configName === null )
        {
            $configName = $this->defaultConfigItemName;
        }

        if ( $displayName === null )
        {
            $displayName = $this->defaultConfigItemDisplayName;
        }

        $bundleType = $itemData['variation']['bundleType'];
        $itemTexts = $itemData['texts'];
        $variationName = $itemData['variation']['name'];

        $showName = '';

        if ($configName === '1' && strlen($itemTexts['name2']))
        {
            $showName = $itemTexts['name2'];
        }
        elseif ($configName === '2' && strlen($itemTexts['name3']))
        {
            $showName = $itemTexts['name3'];
        }
        else
        {
            $showName = $itemTexts['name1'];
        }

        if ($displayName === 'itemNameVariationName' && strlen($variationName))
        {
            $showName .= ' ' . $variationName;
        }

        if ($displayName === 'variationName' && strlen($variationName))
        {
            $showName = $variationName;
        }

        if($bundleType)
        {
            $showName = $this->translationService->trans('Template.itemBundleName', ['itemName' => $showName]);
        }

        return $showName;
    }
}
