<?php

namespace App\Form;

use App\Entity\ShareSource;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ShareSourceType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id')
            ->add('type')
            ->add('title')
            ->add('page')
            ->add('createdAt')
            ->add('user')
            ->add('product')
            ->add('bannerFile')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ShareSource::class,
        ]);
    }
}
