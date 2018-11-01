<?php

namespace App\Form;

use App\Entity\UserAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
//            ->add('user')
            ->add('address')
            ->add('name')
            ->add('phone')
            ->add('isDefault', null, [
                'label' => '是否默认'
            ])
            ->add('isDeleted', null, [
                'label' => '是否删除'
            ])
//            ->add('createdAt')
//            ->add('updatedAt')
//            ->add('region')
            ->add('provinceId', ChoiceType::class, [
                'mapped' => false,
                'choices' => []
            ])
            ->add('cityId', ChoiceType::class, [
                'mapped' => false,
                'choices' => []
            ])
            ->add('countyId', ChoiceType::class, [
                'mapped' => false,
                'choices' => []
            ]);

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            if (isset($event->getData()['provinceId'])) {
                $form->add('provinceId', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => [$event->getData()['provinceId'] => $event->getData()['provinceId']]
                ]);
            }
            if (isset($event->getData()['cityId'])) {
                $form->add('cityId', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => [$event->getData()['cityId'] => $event->getData()['cityId']]
                ]);
            }
            if (isset($event->getData()['countyId'])) {
                $form->add('countyId', ChoiceType::class, [
                    'mapped' => false,
                    'choices' => [$event->getData()['countyId'] => $event->getData()['countyId']]
                ]);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UserAddress::class,
        ]);
    }
}
