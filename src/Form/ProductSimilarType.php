<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\ProductSimilar;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProductSimilarType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('product', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'title',
                'attr' => [
                    'class' => 'chosen'
                ]
            ])
            ->add('similarProduct', EntityType::class, [
                'class' => Product::class,
                'choice_label' => 'title',
                'attr' => [
                    'class' => 'chosen'
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProductSimilar::class,
        ]);
    }
}
