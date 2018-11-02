<?php

namespace App\Form;

use App\Entity\ProjectShareMeta;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProjectShareMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memo', null, ['label' => '描述', 'attr' => ['rows' => 5]])
            ->add('shareTitle', null, ['label' => ' 标题'])
            ->add('shareBannerFileId', DropzoneType::class, [
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
            'data_class' => ProjectShareMeta::class,
        ]);
    }
}
