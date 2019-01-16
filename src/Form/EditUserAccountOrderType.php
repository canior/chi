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

class EditUserAccountOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('paymentStatus', ChoiceType::class, [
                'label' => '支付状态',
                'mapped' => false,
                'choices' => array_flip(UserAccountOrder::$paymentStatuses),
                'required' => true,
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
