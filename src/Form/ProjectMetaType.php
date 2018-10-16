<?php

namespace App\Form;

use App\Entity\ProjectMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('metaKey')
            ->add('metaValue', TextareaType::class, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-control'
                ]
            ])
            ->add('memo', TextareaType::class, [
                'attr' => [
                    'rows' => 5,
                    'class' => 'form-control'
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectMeta::class,
        ]);
    }
}
