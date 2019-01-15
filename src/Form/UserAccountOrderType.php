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

class UserAccountOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['label' => '账单ID', 'disabled' => true])
            ->add('amount', MoneyType::class, ['label' => '金额'])
            ->add('createdAt', DateTimeType::class, ['label' => '创建时间'])
            ->add('updatedAt', DateTimeType::class, ['label' => '更新时间'])
            ->add('user', EntityType::class, [
                'label' => '用户',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user->getId() . ' ' . $user->getNickname() . ' ' . $user->getName();
                }
            ])
            ->add('upgradeUserOrder', EntityType::class, [
                'label' => '用户',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'choice_label' => function (UpgradeUserOrder $upgradeUserOrder) {
                    return $upgradeUserOrder->getId() . ' ￥' . $upgradeUserOrder->getTotal();
                }
            ])
            ->add('userAccountOrderType', ChoiceType::class, [
                'label' => '类型',
                'mapped' => false,
                'choices' => array_flip(UserAccountOrder::$userAccountOrderTypes),
                'required' => true
            ])
            ->add('paymentStatus', ChoiceType::class, [
                'label' => '支付状态',
                'mapped' => false,
                'choices' => array_flip(UserAccountOrder::$paymentStatuses),
                'required' => true
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
