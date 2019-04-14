<?php

namespace App\Form;

use App\Entity\Product;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use App\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class ProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('sku', TextType::class, [
                'label' => 'SKU',
                'required' => true,
            ])
            ->add('title', TextType::class, ['label' => '名称'])
            ->add('shortDescription', TextareaType::class, ['label' => '短描述'])
            ->add('price', MoneyType::class, [
                'label' => '零售价',
                'currency' => 'CNY'
            ])
//            ->add('freight', MoneyType::class, [
//                'label' => '运费',
//                'currency' => 'CNY'
//            ])
            ->add('supplierUser', EntityType::class, [
                'label' => '供货商',
                'empty_data' => null,
                'placeholder' => '选择供货商用户',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'required' => true,
            ])
            ->add('supplierPrice', MoneyType::class, [
                'label' => '供货价',
                'currency' => 'CNY'
            ])
            ->add('stock', NumberType::class, ['label' => '库存'])
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
            ->add('shareImageFile', DropzoneType::class, [
                'label' => '产品分享图片（1张：注意留空保留二维码位置）',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('hasCoupon', ChoiceType::class, [
                'label' => '是否特级VIP升级码产品',
                'mapped' => false,
                'choices' => array_flip(Product::$hasCouponValues)
            ])
            ->add('status', ChoiceType::class, [
                'label' => '状态',
                'mapped' => false,
                'choices' => array_flip(Product::$statuses)
            ])
            ->add('priority', IntegerType::class, [
                'label' => '排序优先级（数字越大越靠前）',
                'required' => true,
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
