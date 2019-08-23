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
use Symfony\Component\Form\Extension\Core\Type\IntegerType;

class OfflineCourseType extends AbstractType
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
                'label' => '活动名称',
                'required' => true
            ])
            ->add('price', MoneyType::class, [
                'currency' => 'CNY',
                'label' => '会务费',
                'required' => true
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
            ->add('city', TextType::class, [
                'label' => '开课城市 (必填)',
                'required' => true
            ])
            ->add('address', TextType::class, [
                'label' => '开课地址 (选填)',
                'required' => false
            ])
            ->add('addressImageFile', DropzoneType::class, [
                'label' => '地址定位 (请把定位置于图片中间)',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
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
            ->add('refCourse', EntityType::class, [
                'label' => '关联活动',
                'attr' => ['class' => 'form-control chosen'],
                'class' => Course::class,
                'placeholder' => '选择关联活动',
                'required' => false,
                'choice_label' => function (Course $course) {
                    $res =  $course->getTitle() . '-' . $course->getSubjectText();
                    if ($course->getRefCourse()) {
                        $res .= '-[已经关联'  .$course->getRefCourse()->getTitle() . ']';
                    }
                    return $res;
                },
                'query_builder' => function (EntityRepository $er) {
                    $queryBuilder =  $er->createQueryBuilder('c')
                        ->where('c.isOnline = :isOnline')
                        ->setParameter('isOnline', false)
                        ->andWhere('c.subject in (:subjects)')
                        ->setParameter('subjects', [Subject::SYSTEM_2, Subject::SYSTEM_1, Subject::TRADING]);
                    $queryBuilder->orderBy('c.refCourse', 'asc');
                    return $queryBuilder;
                }
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => '活动描述',
                'required' => true
            ])
            ->add('tableCount', IntegerType::class, [
                'label' => '桌数',
                'required' => true,
            ])
            ->add('tableUserCount', IntegerType::class, [
                'label' => '每桌人数',
            ])
            ->add('images', DropzoneType::class, [
                'label' => '活动简介图片（最多5张）',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('specImages', DropzoneType::class, [
                'label' => '活动详细介绍图片（最多10张）',
                'maxFiles' => 10,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('shareImageFile', DropzoneType::class, [
                'label' => '活动分享图片（1张：注意留空保留二维码位置）',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('initiator', TextType::class, [
                'label' => '发起人ID(不填为后台创建)',
                'required' => false
            ])
            ->add('checkStatus', ChoiceType::class, [
                'label' => '审核状态',
                'mapped' => false,
                'required' => false,
                'choices' => array_flip(Course::$checkStatusTexts)
            ])
            ->add('reason', TextType::class, [
                'label' => '驳回理由',
                'required' => false
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
            'data_class' => Course::class,
        ]);
    }
}
