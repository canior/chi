<?php

namespace App\Form;

use App\Entity\ProjectTextMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectTextMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memo', TextType::class, ['label' => '描述', 'required' => false])
            ->add('textMeta', TextareaType::class, ['label' => '文案', 'attr' => ['rows' => 5]])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectTextMeta::class,
        ]);
    }
}
