<?php
/**
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License 3.0 (AFL-3.0)
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright Since 2007 PrestaShop SA and Contributors
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 */

declare(strict_types=1);

namespace PrestaShop\Module\PsEditionBasic\Presenter;

use PrestaShop\Module\PsEditionBasic\Helper\SetupGuideHelper;
use PrestaShop\PrestaShop\Adapter\SymfonyContainer;
use PrestaShopBundle\Translation\TranslatorInterface;
use Psr\Container\ContainerInterface;

class SetupGuideDataPresenter
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @param string $serviceName
     *
     * @return object
     */
    public function get($serviceName)
    {
        if (null === $this->container) {
            $this->container = SymfonyContainer::getInstance();
        }

        return $this->container->get($serviceName);
    }

    /**
     * @var TranslatorInterface
     */
    public $translator;

    public function __construct()
    {
        $this->translator = \Context::getContext()->getTranslator();
    }

    /**
     * Get setup guide datas.
     *
     * @return array
     */
    public function getSetupGuideData(): array
    {
        $router = $this->get('router');

        $response = [];

        try {
            if (\Module::isInstalled('smb_edition') && \Module::isEnabled('smb_edition') && $router->generate('smb_edition_manage_domain_name')) {
                array_push($response, $this->buildStep(
                    'domain',
                    'onb.homepage.setupGuide.domain.title',
                    [
                        [
                            'description' => 'onb.homepage.setupGuide.domain.description',
                            'documentation' => [
                                'cta' => 'onb.homepage.setupGuide.domain.documentation.cta',
                                'href' => 'onb.homepage.setupGuide.domain.documentation.href',
                            ],
                            'buttons' => [
                                [
                                    'cta' => 'onb.homepage.setupGuide.domain.button.skip',
                                    'href' => '#',
                                    'variant' => 'secondary',
                                    'skip' => true,
                                ],
                                [
                                    'cta' => 'onb.homepage.setupGuide.domain.button.cta',
                                    'href' => $router->generate('smb_edition_manage_domain_name'),
                                    'userflow_id' => 'dbef1ad6-d589-4991-9f95-918190d153a9',
                                ],
                            ],
                        ],
                    ]
                ));
            } else {
                array_push($response, $this->buildStep(
                    'account',
                    'onb.homepage.setupGuide.account.title',
                    [
                        [
                            'description' => '',
                            'documentation' => [
                                'cta' => 'onb.homepage.setupGuide.account.documentation.cta',
                                'href' => 'onb.homepage.setupGuide.account.documentation.href',
                            ],
                            'buttons' => [],
                        ],
                    ],
                    true
                ));
            }
        } catch (\Exception $e) {
        }

        array_push($response, $this->buildStep(
            'product',
            'onb.homepage.setupGuide.product.title',
            [
                [
                    'description' => 'onb.homepage.setupGuide.product.description',
                    'documentation' => [
                        'cta' => 'onb.homepage.setupGuide.product.documentation.cta',
                        'href' => 'onb.homepage.setupGuide.product.documentation.href',
                    ],
                    'buttons' => [
                        [
                            'cta' => 'onb.homepage.setupGuide.product.button.cta',
                            'href' => $router->generate('admin_product_catalog'),
                            'userflow_id' => '0a517acb-531a-4aeb-ab8a-b850ddbfdf70', // TODO: get from parameters
                        ],
                    ],
                ],
            ]
        ));
        array_push($response, $this->buildStep(
            'payment',
            'onb.homepage.setupGuide.payment.title',
            [
                [
                    'description' => 'onb.homepage.setupGuide.payment.description',
                    'documentation' => [
                        'cta' => 'onb.homepage.setupGuide.payment.documentation.cta',
                        'href' => 'onb.homepage.setupGuide.payment.documentation.href',
                    ],
                    'buttons' => [
                        [
                            'cta' => 'onb.homepage.setupGuide.payment.button.cta',
                            'href' => $router->generate('admin_payment_methods'),
                            'userflow_id' => 'fea1c94c-591b-4c44-b05d-8236ec30c3e4',
                        ],
                    ],
                ],
            ]
        ));
        array_push($response, $this->buildStep(
            'shipping',
            'onb.homepage.setupGuide.shipping.title',
            [
                [
                    'description' => 'onb.homepage.setupGuide.shipping.description',
                    'documentation' => [
                        'cta' => 'onb.homepage.setupGuide.shipping.documentation.cta',
                        'href' => 'onb.homepage.setupGuide.shipping.documentation.href',
                    ],
                    'buttons' => [
                        [
                            'cta' => 'onb.homepage.setupGuide.shipping.button.skip',
                            'href' => '#',
                            'variant' => 'secondary',
                            'skip' => true,
                        ],
                        [
                            'cta' => 'onb.homepage.setupGuide.shipping.button.cta',
                            'href' => \Context::getContext()->link->getAdminLink('AdminCarriers'),
                            'userflow_id' => 'e085ee0c-cecf-4ba6-8a70-3f4310790dbf',
                        ],
                    ],
                ],
            ]
        ));

        try {
            if (\Module::isInstalled('psxlegalassistant') && \Module::isEnabled('psxlegalassistant') && $router->generate('psxlegalassistant_main_page')) {
                array_push($response, $this->buildStep(
                    'legal',
                    'onb.homepage.setupGuide.legal.title',
                    [
                        [
                            'description' => 'onb.homepage.setupGuide.legal.description',
                            'documentation' => [
                                'cta' => 'onb.homepage.setupGuide.legal.documentation.cta',
                                'href' => 'onb.homepage.setupGuide.legal.documentation.href',
                            ],
                            'buttons' => [
                                [
                                    'cta' => 'onb.homepage.setupGuide.legal.button.cta',
                                    'href' => $router->generate('psxlegalassistant_main_page'),
                                    'userflow_id' => '66ceeb79-9663-45f8-9276-b4b23ae750d6',
                                ],
                            ],
                        ],
                    ]
                ));
            }
        } catch (\Exception $e) {
        }

        $contentLogoAndTheme = [];
        try {
            $contentLogoAndTheme = [
                [
                    'subtitle' => 'onb.homepage.setupGuide.logo.subtitle',
                    'description' => 'onb.homepage.setupGuide.logo.description',
                    'documentation' => [
                        'cta' => 'onb.homepage.setupGuide.logo.documentation.cta',
                        'href' => 'onb.homepage.setupGuide.logo.documentation.href',
                    ],
                    'buttons' => [
                        [
                            'cta' => 'onb.homepage.setupGuide.logo.button.cta',
                            'href' => \Module::isInstalled('psxdesign') && \Module::isEnabled('psxdesign') && $router->generate('admin_logos_index') ? $router->generate('admin_logos_index') : $router->generate('admin_themes_index'),
                            'userflow_id' => '02f43ff1-f2ff-4ab6-9480-2229b2524f7c',
                        ],
                    ],
                ],
            ];
        } catch (\Exception $e) {
        }

        try {
            if (\Module::isInstalled('psxdesign') && \Module::isEnabled('psxdesign') && $router->generate('admin_psxdesign_themes_index')) {
                array_push($contentLogoAndTheme,
                    [
                        'subtitle' => 'onb.homepage.setupGuide.psxdesign.subtitle',
                        'description' => 'onb.homepage.setupGuide.psxdesign.description',
                        'documentation' => [
                            'cta' => 'onb.homepage.setupGuide.psxdesign.documentation.cta',
                            'href' => 'onb.homepage.setupGuide.psxdesign.documentation.href',
                            'variant' => 'secondary',
                        ],
                        'buttons' => [
                            [
                                'cta' => 'onb.homepage.setupGuide.psxdesign.button.cta',
                                'href' => $router->generate('admin_psxdesign_themes_index'),
                                'userflow_id' => '1262b67b-ab4b-49ad-8fa2-e6a5a9e7f8ad',
                            ],
                        ],
                    ]
                );
            }
        } catch (\Exception $e) {
        }

        array_push($response, $this->buildStep(
            'logo-and-theme',
            'onb.homepage.setupGuide.logo.title',
            $contentLogoAndTheme
        ));

        return $response;
    }

    /**
     * Build step for data presenter
     *
     * @param string $name
     * @param string $title
     * @param array $content [(string $title, string $description, array $documentation, array $buttons)]
     *
     * @return array
     */
    public function buildStep(string $name, string $title, array $content = [], bool $disabledForUser = false): array
    {
        return [
            'name' => $name,
            'title' => $title,
            'content' => $content,
            'disabledForUser' => $disabledForUser,
            'isCompleted' => SetupGuideHelper::isStepCompleted($name),
            'isUserCompleted' => SetupGuideHelper::isStepUserCompleted($name),
            'isAutoCompleted' => SetupGuideHelper::isStepAutoCompleted($name),
        ];
    }
}
