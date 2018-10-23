<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserRoleType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'mapped' => false,
                'multiple' => true,
                'choices' => [
                    '用户' => 'ROLE_USER',
                    '客服' => 'ROLE_CUSTOMER_SERVICE',
                    '管理员' => 'ROLE_ADMIN',
                    '超级管理员' => 'ROLE_SUPPER_ADMIN'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
