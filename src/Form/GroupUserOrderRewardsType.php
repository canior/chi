<?php

namespace App\Form;

use App\Entity\GroupUserOrderRewards;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupUserOrderRewardsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userRewards')
            ->add('createdAt')
            ->add('groupUserOrder')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupUserOrderRewards::class,
        ]);
    }
}
