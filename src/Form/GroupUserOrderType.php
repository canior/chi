<?php

namespace App\Form;

use App\Entity\GroupUserOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupUserOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('total', null, ['label' => '支付金额'])
//            ->add('orderRewards')
            ->add('carrierName', null, ['label' => '物流商'])
            ->add('trackingNo', null, ['label' => '快递单号'])
//            ->add('prePayId')
//            ->add('status', ChoiceType::class, [
//                'mapped' => false,
//                'choices' => array_flip(GroupUserOrder::$statuses)
//            ])
//            ->add('paymentStatus', ChoiceType::class, [
//                'mapped' => false,
//                'choices' => array_flip(GroupUserOrder::$paymentStatuses)
//            ])
//            ->add('createdAt')
//            ->add('updatedAt')
//            ->add('groupOrder')
//            ->add('userAddress')
//            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupUserOrder::class,
        ]);
    }
}
