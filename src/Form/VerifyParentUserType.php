<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class VerifyParentUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('recommanderName', TextType::class, [
                'label' => '用户输入推荐人',
                'required' => false,
                'disabled' => true,
            ])
            ->add('parentUser', EntityType::class, [
                'label' => '认证推荐人',
                'empty_data' => null,
                'placeholder' => '无',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'required' => false,
            ])
            ->add('parentUserExpiresAt', DateType::class, [
                'label' => '推荐人锁定至日期',
                'input' => 'timestamp',
                'widget' => 'single_text',
                'placeholder' => '请输入推荐人锁定至日期',
                'required' => false,
            ])


        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
