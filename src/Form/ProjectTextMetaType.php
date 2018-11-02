<?php

namespace App\Form;

use App\Entity\ProjectTextMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectTextMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memo', null, ['label' => '描述', 'attr' => ['rows' => 5]])
            ->add('textMeta', null, ['label' => '文案'])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectTextMeta::class,
        ]);
    }
}
