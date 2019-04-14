<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Teacher;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Subject;
use App\Entity\Product;
use App\Form\Type\DropzoneType;

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
            ->add('status', ChoiceType::class, [
                'label' => '状态',
                'mapped' => false,
                'choices' => array_flip(Product::$statuses)
            ])
            ->add('teacher', EntityType::class, [
                'label' => '讲师',
                'attr' => ['class' => 'form-control chosen'],
                'class' => Teacher::class,
                'choice_label' => function (Teacher $teacher) {
                    return $teacher->getName();
                }
            ])
            ->add('groupOrderValidForHours', IntegerType::class, [
                'label' => '集call有效期 (小时)',
                'required' => true
            ])
            ->add('totalGroupUserOrdersRequired', IntegerType::class, [
                'label' => '集call开启课程订单量',
                'required' => true
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => '课程描述',
                'required' => true
            ])
            ->add('images', DropzoneType::class, [
                'label' => '课程简介图片（<=5张）',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('specImages', DropzoneType::class, [
                'label' => '课程详细介绍图片（<=5张）',
                'maxFiles' => 5,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('aliyunVideoId', TextType::class, [
                'label' => '课程视频（阿里云视频ID）',
                'required' => false,
            ])
            ->add('shareImageFile', DropzoneType::class, [
                'label' => '课程分享图片（1张：注意留空保留二维码位置）',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
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
