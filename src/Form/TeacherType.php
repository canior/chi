<?php

namespace App\Form;

use App\Entity\File;
use App\Entity\Teacher;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Form\Type\DropzoneType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

class TeacherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['label' => 'ID', 'disabled' => true])
            ->add('name', null, ['label' => '姓名'])
            ->add('title', null, ['label' => '职称', 'required' => false])
            ->add('description', TextareaType::class, ['label' => '简介', 'required' => false])
            ->add('teacherAvatarFile', DropzoneType::class, [
                'label' => '头像',
                'maxFiles' => 1,
                'priority' => true,
                'data_class' => null,
                'mapped' => false,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Teacher::class,
        ]);
    }
}
