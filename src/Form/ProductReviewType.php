<?php

namespace App\Form;

use App\Entity\GroupUserOrder;
use App\Entity\Product;
use App\Entity\ProductReview;
use App\Form\Type\DropzoneType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('rate', ChoiceType::class, [
                'label' => '评分',
                'mapped' => false,
                'choices' => array_flip(ProductReview::$rates),
                'required' => true
            ])
            ->add('review', TextareaType::class, [
                'label' => '评价'
            ])
            ->add('images', DropzoneType::class, [
                'label' => '图片',
                'maxFiles' => 5,
                'priority' => false,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('status', ChoiceType::class, [
                'label' => '状态',
                'mapped' => false,
                'choices' => array_flip(ProductReview::$statuses)
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
