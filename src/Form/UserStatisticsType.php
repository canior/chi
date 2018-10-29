<?php

namespace App\Form;

use App\Entity\UserStatistics;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserStatisticsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('childrenNum')
            ->add('sharedNum')
            ->add('groupOrderNum')
            ->add('groupUserOrderNum')
            ->add('spentTotal')
            ->add('orderRewardsTotal')
            ->add('userRewardsTotal')
            ->add('year')
            ->add('month')
            ->add('day')
            ->add('groupOrderJoinedNum')
            ->add('user')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserStatistics::class,
        ]);
    }
}
