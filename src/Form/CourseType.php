<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Teacher;
use Doctrine\ORM\EntityRepository;
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
use App\Entity\Subject;
use App\Entity\Product;
use App\Form\Type\DropzoneType;
use App\Entity\User;

class CourseType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['label' => 'ID', 'disabled' => true])
            ->add('subject', ChoiceType::class, [
                'label' => '科目',
                'mapped' => false,
                'choices' => array_flip(Subject::$subjectTextArray),
                'required' => true
            ])
            ->add('title', TextType::class, [
                'label' => '课程名称',
                'required' => true
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'CNY',
                'label' => '会务费',
                'required' => true
            ])
            ->add('status', ChoiceType::class, [
                'label' => '状态',
                'mapped' => false,
                'choices' => array_flip(Product::$statuses)
            ])
            ->add('startDate', DateType::class, [
                'label' => '开始时间',
                'input' => 'timestamp',
                'placeholder' => ['year' => '年', 'month' => '月', 'day' => '日'],
                'required' => true
            ])
            ->add('endDate', DateType::class, [
                'label' => '结束时间',
                'input' => 'timestamp',
                'placeholder' => ['year' => '年', 'month' => '月', 'day' => '日'],
                'required' => true
            ])
            ->add('address', TextType::class, [
                'label' => '开课地址 (必填)',
                'required' => true
            ])
            ->add('teacher', EntityType::class, [
                'label' => '讲师',
                'attr' => ['class' => 'form-control chosen'],
                'class' => Teacher::class,
                'choice_label' => function (Teacher $teacher) {
                    return $teacher->getName();
                }
            ])
            ->add('ownerUser', EntityType::class, [
                'label' => '安检后台用户',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'placeholder' => '选择安检权限的后台用户',
                'choice_label' => function (User $user) {
                    return $user->getId() . ' ' . $user->getUsername() . ' ' . $user->getName();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.password != :password')
                        ->setParameter('password', 'IamCustomer');
                }
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => '课程描述',
                'required' => true
            ])
            ->add('images', DropzoneType::class, [
                'label' => '课程简介图片（最多5张）',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('specImages', DropzoneType::class, [
                'label' => '课程详细介绍图片（最多5张）',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('shareImageFile', DropzoneType::class, [
                'label' => '课程分享图片（1张：注意留空保留二维码位置）',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Course::class,
        ]);
    }
}
