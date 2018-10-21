<?php

namespace App\Form;

use App\Entity\GroupOrder;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupOrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'mapped' => false,
                'choices' => array_flip(GroupOrder::$statuses)
            ])
//            ->add('expiredAt')
//            ->add('updatedAt')
//            ->add('createdAt')
//            ->add('user')
//            ->add('product')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => GroupOrder::class,
        ]);
    }
}
