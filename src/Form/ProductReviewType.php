<?php

namespace App\Form;

use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\ProductReview;
use App\Form\Type\DropzoneType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rate')
            ->add('review')
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'title',
                'attr' => [
                    'class' => 'chosen'
                ]
            ])
            ->add('groupUserOrder', EntityType::class, [
                'class' => GroupUserOrder::class,
                'choice_label' => 'id',
                'attr' => [
                    'class' => 'chosen'
                ]
            ])
            ->add('images', DropzoneType::class, [
                'label' => '图片',
                'maxFiles' => 5,
                'priority' => false,
                'data_class' => null,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductReview::class,
        ]);
    }
}
