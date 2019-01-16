<?php

namespace App\Form;

use App\Entity\UserAccountOrder;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use App\Entity\UpgradeUserOrder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class NewUserAccountOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userAccountOrderType', ChoiceType::class, [
                'label' => '交易类型',
                'placeholder' => '选择交易类型',
                'empty_data' => null,
                'mapped' => false,
                'choices' => array_flip(UserAccountOrder::$userAccountOrderTypes),
                'attr' => ['class' => 'form-control chosen'],
                'required' => true
            ])
            ->add('amount', MoneyType::class, ['label' => '金额', 'currency' => 'CNY'])
            ->add('upgradeUserOrder', EntityType::class, [
                'label' => '会员升级账单',
                'placeholder' => '选择会员升级账单',
                'empty_data' => null,
                'attr' => ['class' => 'form-control chosen'],
                'class' => UpgradeUserOrder::class,
                'required' => false,
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'label' => '支付状态',
                'mapped' => false,
                'choices' => array_flip(UserAccountOrder::$paymentStatuses),
            ])
            ->add('memo', TextareaType::class, [
                'label' => '备注',
                'required' => false
            ])
        ;

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserAccountOrder::class,
        ]);
    }
}
