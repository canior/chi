<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/29
 * Time: 20:00
 */

namespace App\Form;

use App\Entity\Category;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['label' => 'ID', 'disabled' => true])
            ->add('name', TextType::class, ['label' => '名称'])
            ->add('iconFile', DropzoneType::class, [
                'label' => '图片',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('previewImageFile', DropzoneType::class, [
                'label' => '分类视频封面图片',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
            ])
            ->add('shortDescription', TextareaType::class, [
                'label' => '分类描述',
                'required' => false,
            ])
            ->add('aliyunVideoId', TextType::class, [
                'label' => '阿里云视频ID',
                'required' => false,
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
            'data_class' => Category::class,
        ]);
    }
}
