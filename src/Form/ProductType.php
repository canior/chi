<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, ['label' => '名称'])
            ->add('shortDescription', null, ['label' => '短描述'])
            ->add('originalPrice', null, ['label' => '原价'])
            ->add('price', null, ['label' => '零售价'])
            ->add('groupPrice', null, ['label' => '拼团价'])
            ->add('rewards', null, ['label' => '返现总额'])
            ->add('freight', null, ['label' => '运费'])
            ->add('sku', null, ['label' => 'SKU'])
            ->add('stock', null, ['label' => '库存'])
            ->add('images', DropzoneType::class, [
                'label' => '图片',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('specImages', DropzoneType::class, [
                'label' => '描述图片',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => '状态',
                'mapped' => false,
                'choices' => array_flip(Product::$statuses)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
