<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => '名称'])
            ->add('shortDescription', null, ['label' => '短描述'])
            ->add('price', null, ['label' => '价格'])
            ->add('originalPrice', null, ['label' => '原价'])
            ->add('rewards', null, ['label' => '让利'])
            ->add('sku', null, ['label' => '库存单位'])
            ->add('stock', null, ['label' => '库存'])
            ->add('freight', null, ['label' => '运费'])
            ->add('images', DropzoneType::class, [
                'label' => '图片',
                'maxFiles' => 5,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('specImages', DropzoneType::class, [
                'label' => '描述图片',
                'maxFiles' => 5,
                'data_class' => null,
                'mapped' => false,
            ])
//            ->add('status', CheckboxType::class, ['label' => '状态', 'required' => true])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
