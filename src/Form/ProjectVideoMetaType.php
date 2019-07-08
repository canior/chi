<?php

namespace App\Form;


use App\Entity\ProjectVideoMeta;
use App\Form\Type\DropzoneType;
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
            ->add('previewImageFileId', DropzoneType::class, [
                'label' => '视频封面图片',
                'maxFiles' => 1,
                'priority' => false,
                'data_class' => null,
                'mapped' => false,
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
