<?php

namespace App\Form;

use App\Entity\UpgradeUserOrderPayment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class NewUpgradeUserOrderPaymentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('amount', MoneyType::class, ['label' => '金额', 'currency' => 'CNY'])
            ->add('memo', TextareaType::class, [
                'label' => '备注',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpgradeUserOrderPayment::class,
        ]);
    }
}
