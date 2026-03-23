<?php
/**
 * For the full copyright and license information, please view the
 * docs/licenses/LICENSE.txt file that was distributed with this source code.
 */

namespace PrestaShopBundle\Form\Admin\Sell\Order;

use PrestaShop\PrestaShop\Core\Form\ConfigurableFormChoiceProviderInterface;
use PrestaShopBundle\Form\Admin\Type\TranslatorAwareType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

class EditProductRowType extends TranslatorAwareType
{
    /**
     * @var ConfigurableFormChoiceProviderInterface
     */
    private $orderInvoiceByIdChoiceProvider;

    /**
     * @var int
     */
    private $contextLangId;

    /**
     * EditProductRowType constructor.
     *
     * @param TranslatorInterface $translator
     * @param array $locales
     * @param ConfigurableFormChoiceProviderInterface $orderInvoiceByIdChoiceProvider
     * @param int $contextLangId
     */
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
            ->add('price_tax_excluded', NumberType::class, [
                'label' => false,
                'unit' => sprintf('%s %s',
                    $options['symbol'],
                    $this->trans('tax excl.', 'Admin.Global')
                ),
                'attr' => [
                    'class' => 'editProductPriceTaxExcl',
                ],
            ])
            ->add('price_tax_included', NumberType::class, [
                'label' => false,
                'unit' => sprintf('%s %s',
                    $options['symbol'],
                    $this->trans('tax incl.', 'Admin.Global')
                ),
                'attr' => [
                    'class' => 'editProductPriceTaxIncl',
                ],
            ])
            ->add('quantity', NumberType::class, [
                'label' => false,
                'data' => 1,
                'scale' => 0,
                'attr' => [
                    'min' => 1,
                    'class' => 'editProductQuantity',
                ],
            ])
            ->add('invoice', ChoiceType::class, [
                'choices' => $invoices,
                'label' => false,
                'attr' => [
                    'class' => 'editProductInvoice custom-select',
                ],
            ])
            ->add('cancel', ButtonType::class, [
                'label' => $this->trans('Cancel', 'Admin.Actions'),
                'attr' => [
                    'class' => 'btn btn-sm btn-secondary js-product-edit-action-btn mr-2 mt-2 mb-2 productEditCancelBtn',
                ],
            ])
            ->add('save', ButtonType::class, [
                'label' => $this->trans('Save', 'Admin.Actions'),
                'disabled' => true,
                'attr' => [
                    'class' => 'btn btn-sm btn-primary js-product-edit-action-btn mt-2 mb-2 productEditSaveBtn',
                    'data-order-id' => $options['order_id'],
                    'data-update-message' => $this->trans('Are you sure?', 'Admin.Notifications.Warning'),
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
            ])
            ->setAllowedTypes('order_id', ['int', 'null'])
            ->setAllowedTypes('symbol', ['string'])
        ;
    }
}
