<?php

namespace App\Form;

use App\Entity\UserAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address')
            ->add('name')
            ->add('phone')
            ->add('isDefault')
            ->add('isDeleted')
            ->add('createdAt')
            ->add('updatedAt')
            ->add('user')
            ->add('region')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserAddress::class,
        ]);
    }
}
