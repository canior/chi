<?php

namespace App\Form;

use App\Entity\CourseStudent;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\Course;
use App\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class CourseStudentType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('id', null, ['label' => '注册ID', 'disabled' => true])
            ->add('course', EntityType::class, [
                'label' => '课程',
                'attr' => ['class' => 'form-control chosen'],
                'class' => Course::class,
                'choice_label' => function (Course $course) {
                    return $course->getId() . ' ' . $course->getSubjectText() . ' ' . $course->getStartDateFormatted() . '-' . $course->getEndDateFormatted();
                }
            ])
            ->add('studentUser', EntityType::class, [
                'label' => '学生',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
            ])
            ->add('status', ChoiceType::class, [
                'label' => '科目',
                'mapped' => false,
                'choices' => array_flip(CourseStudent::$statusTexts),
                'required' => true
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CourseStudent::class,
        ]);
    }
}
