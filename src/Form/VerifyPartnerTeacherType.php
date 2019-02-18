<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-02-17
 * Time: 8:36 PM
 */

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;

class VerifyPartnerTeacherType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('teacherRecommanderUser', EntityType::class, [
                'label' => '成为合伙人时的讲师',
                'empty_data' => null,
                'placeholder' => '无',
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