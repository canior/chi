<?php

namespace App\Form;


use App\Entity\ProjectVideoMeta;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectVideoMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memo', null, [
                'label' => '描述',
                'required' => false,
            ])
            ->add('aliyunVideoId', null, [
                'label' => '阿里云视频ID',
                'required' => false,
            ])

        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectVideoMeta::class,
        ]);
    }
}
