<?php

namespace App\Form;

use App\Entity\ProjectBannerMeta;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectBannerMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memo', null, ['label' => '描述', 'attr' => ['rows' => 5]])
            ->add('redirectUrl', null, ['label' => '跳转页面'])
            ->add('bannerFileId', DropzoneType::class, [
                'label' => '图片',
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
            'data_class' => ProjectBannerMeta::class,
        ]);
    }
}
