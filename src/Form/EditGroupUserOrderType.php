<?php

namespace App\Form;

use App\Entity\GroupUserOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class EditGroupUserOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label' => '订单状态',
                'mapped' => false,
                'choices' => array_flip(GroupUserOrder::$courseStatuses),
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'label' => '支付状态',
                'mapped' => false,
                'choices' => array_flip(GroupUserOrder::$paymentStatuses),
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupUserOrder::class,
        ]);
    }
}
