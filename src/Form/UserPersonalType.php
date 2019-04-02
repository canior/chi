<?php

namespace App\Form;

use App\Entity\BianxianUserLevel;
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
use App\Form\Type\DropzoneType;
use App\Entity\UserLevel;
use App\Entity\User;
use App\Entity\Teacher;

class UserPersonalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('userLevel', ChoiceType::class, [
                'label' => '会员等级',
                'mapped' => false,
                'choices' => array_flip(UserLevel::$userLevelTextArray),
                'required' => true
            ])
            ->add('bianxianUserLevel', ChoiceType::class, [
                'label' => '变现等级',
                'mapped' => false,
                'choices' => array_flip(BianxianUserLevel::$userLevelTextArray),
                'required' => true
            ])
            ->add('name', TextType::class, [
                'label' => '姓名',
                'required' => false,
            ])
            ->add('phone', TextType::class, [
                'label' => '电话',
                'required' => false
            ])
            ->add('company', TextType::class, [
                'label' => '公司',
                'required' => false
            ])
            ->add('wechat', TextType::class, [
                'label' => '微信号',
                'required' => false
            ])
            ->add('idNum', TextType::class, [
                'label' => '身份证',
                'required' => false,
            ])
            ->add('teacher', EntityType::class, [
                'label' => '讲师身份',
                'placeholder' => '请匹配讲师',
                'empty_data' => null,
                'attr' => ['class' => 'form-control chosen'],
                'class' => Teacher::class,
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
