<?php
/**
 * Creative Elements - live Theme & Page Builder
 *
 * @author    WebshopWorks, Elementor
 * @copyright 2019-2023 WebshopWorks.com & Elementor.com
 * @license   https://www.gnu.org/licenses/gpl-3.0.html
 */

namespace CE;

defined('_PS_VERSION_') or exit;

use CE\CoreXDocumentTypesXPageBase as PageBase;
use CE\ModulesXThemeXDocumentsXThemeDocument as ThemeDocument;

abstract class ModulesXThemeXDocumentsXHeaderFooterBase extends ThemeDocument
{
    public function getCssWrapperSelector()
    {
        return '#' . $this->getName();
    }

    public function getElementUniqueSelector(ElementBase $element)
    {
        return '#' . $this->getName() . ' .elementor-element' . $element->getUniqueSelector();
    }

    protected static function getEditorPanelCategories()
    {
        // Move to top as active.
        $categories = [
            'theme-elements' => [
                'title' => __('Site'),
                'active' => true,
            ],
        ];

        return $categories + parent::getEditorPanelCategories();
    }

    protected function _registerControls()
    {
        parent::_registerControls();

        PageBase::registerStyleControls($this);

        $this->updateControl(
            'section_page_style',
            [
                'label' => __('Style'),
            ]
        );

        if ($this->getName() === 'footer') {
            $this->updateControl('padding', [
                'default' => [
                    'isLinked' => false,
                ],
            ]);
        }

        $this->startInjection([
            'of' => 'padding',
        ]);

        $this->addGroupControl(
            GroupControlBoxShadow::getType(),
            [
                'name' => 'box_shadow',
                'selector' => '{{WRAPPER}}',
                'fields_options' => $this->getName() === 'header' ? [
                    'box_shadow_type' => [
                        'default' => 'yes',
                    ],
                    'box_shadow' => [
                        'default' => [
                            'blur' => 0,
                        ],
                    ],
                ] : [],
            ]
        );

        $this->endInjection();
    }
}
