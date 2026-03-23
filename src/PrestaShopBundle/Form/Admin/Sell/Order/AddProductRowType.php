<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Form\Admin\Sell\Order;

use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Form type used to add a product row
 */
class AddProductRowType extends TranslatorAwareType
{
    /**
     * @var ConfigurableFormChoiceProviderInterface
     */
    private $orderInvoiceByIdChoiceProvider;

    /**
     * @var int
     */
    private $contextLangId;

    public function __construct(
        TranslatorInterface $translator,
        array $locales,
        ConfigurableFormChoiceProviderInterface $orderInvoiceByIdChoiceProvider,
        int $contextLangId
    ) {
        parent::__construct($translator, $locales);

        $this->orderInvoiceByIdChoiceProvider = $orderInvoiceByIdChoiceProvider;
        $this->contextLangId = $contextLangId;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $invoices = $options['order_id'] ?
            $this->orderInvoiceByIdChoiceProvider->getChoices([
                'id_order' => $options['order_id'],
                'id_lang' => $this->contextLangId,
                'display_total' => false,
            ]) : [];

        $builder
            ->add('product_id', HiddenType::class)
            ->add('tax_rate', HiddenType::class)
            ->add('search', TextType::class, [
                'label' => $this->trans('Add a product', 'Admin.Orderscustomers.Feature'),
                'attr' => [
                    'class' => 'col-sm-12',
                    'autocomplete' => 'off',
                    'placeholder' => $this->trans('Search for a product', 'Admin.Orderscustomers.Feature'),
                    'data-currency' => $options['currency_id'],
                    'data-order' => $options['order_id'],
                ],
            ])
            ->add('addProductCombinations', ChoiceType::class, [
                'attr' => [
                    'class' => 'custom-select',
                ],
            ])
            ->add('price_tax_excluded', NumberType::class, [
                'label' => false,
                'unit' => sprintf('%s %s',
                    $options['symbol'],
                    $this->trans('tax excl.', 'Admin.Global')
                ),
            ])
            ->add('price_tax_included', NumberType::class, [
                'label' => false,
                'unit' => sprintf('%s %s',
                    $options['symbol'],
                    $this->trans('tax incl.', 'Admin.Global')
                ),
            ])
            ->add('quantity', NumberType::class, [
                'label' => false,
                'data' => 1,
                'scale' => 0,
                'attr' => [
                    'min' => 1,
                ],
            ])
            ->add('invoice', ChoiceType::class, [
                'label' => false,
                'disabled' => true,
                'choices' => [
                    $this->trans('Existing', 'Admin.Global') => $invoices,
                    $this->trans('New', 'Admin.Global') => [
                        $this->trans('Create a new invoice', 'Admin.Orderscustomers.Feature') => 0,
                    ],
                ],
            ])
            ->add('free_shipping', CheckboxType::class,
                [
                    'required' => false,
                    'label' => $this->trans('Free shipping', 'Admin.Orderscustomers.Feature', []),
                    'attr' => [
                        'material_design' => true,
                    ],
                ]
            )
            ->add('cancel', ButtonType::class, [
                'label' => $this->trans('Cancel', 'Admin.Actions'),
                'attr' => [
                    'class' => 'btn btn-sm btn-secondary js-product-add-action-btn mr-2 mt-2 mb-2',
                ],
            ])
            ->add('add', ButtonType::class, [
                'label' => $this->trans('Add', 'Admin.Actions'),
                'disabled' => true,
                'attr' => [
                    'class' => 'btn btn-sm btn-primary js-product-add-action-btn mt-2 mb-2',
                    'data-order-id' => $options['order_id'],
                ],
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired(['symbol'])
            ->setDefaults([
                'order_id' => null,
                'currency_id' => null,
            ])
            ->setAllowedTypes('order_id', ['int', 'null'])
            ->setAllowedTypes('currency_id', ['int', 'null'])
            ->setAllowedTypes('symbol', ['string'])
        ;
    }
}
