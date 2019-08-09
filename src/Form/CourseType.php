<?php

namespace App\Form;

use App\Entity\Category;
use App\Entity\Course;
use App\Entity\GroupUserOrder;
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
                'label' => '科目[和分钱逻辑相关谨慎选择]',
                'mapped' => false,
                'choices' => array_flip(Subject::$subjectTextArray),
                'required' => true
            ])
            ->add('title', TextType::class, [
                'label' => '课程名称',
                'required' => true
            ])
            ->add('courseCategory', EntityType::class, [
                'label' => '分类',
                'empty_data' => null,
                'placeholder' => '选择课程分类',
                'attr' => ['class' => 'form-control chosen'],
                'class' => Category::class,
                'required' => true,
                'choice_label' => function (Category $category) {
                    return $category->__toString();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('c')
                        ->where('c.isDeleted =:isDeleted')
                        ->setParameter('isDeleted', false)
                        ->andWhere('c.singleCourse =:singleCourse')
                        ->setParameter('singleCourse', false);
                }
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
            ->add('price', MoneyType::class, [
                'label' => '课程单独购买价格',
                'currency' => 'CNY'
            ])
            ->add('groupOrderValidForHours', IntegerType::class, [
                'label' => '集call有效期 (小时)',
                'required' => true
            ])
            ->add('totalGroupUserOrdersRequired', IntegerType::class, [
                'label' => '集call开启课程订单量',
                'required' => true
            ])
            ->add('courseTag', TextType::class, [
                'label' => '标签 (多个可以逗号拼接)',
                'required' => false
            ])
            ->add('unlockType', ChoiceType::class, [
                'label' => '解锁方式',
                'mapped' => false,
                'required' => true,
                'choices' => array_flip(Course::$unlockTypeTexts)
            ])
            ->add('courseShowType', ChoiceType::class, [
                'label' => '显示设备',
                'mapped' => false,
                'required' => true,
                'choices' => array_flip(Course::$courseShowTypeTexts)
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
                'label' => '课程详细介绍图片（<=10张）',
                'maxFiles' => 10,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('aliyunVideoId', TextType::class, [
                'label' => '课程视频（阿里云视频ID）',
                'required' => false,
            ])
            ->add('previewImageFile', DropzoneType::class, [
                'label' => '课程视频封面图片',
                'maxFiles' => 1,
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
