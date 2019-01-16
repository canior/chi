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
use App\Form\Type\DropzoneType;
use App\Entity\UserLevel;
use App\Entity\User;

class UserPersonalType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['label' => 'ID', 'disabled' => true])
            ->add('userLevel', ChoiceType::class, [
                'label' => '会员等级',
                'mapped' => false,
                'choices' => array_flip(UserLevel::$userLevelTextArray),
                'required' => true
            ])
            ->add('name', TextType::class, [
                'label' => '姓名',
                'required' => true
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
            ->add('parentUser', EntityType::class, [
                'label' => '推荐人',
                'placeholder' => '请选择推荐人',
                'empty_data' => null,
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
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
