<?php

namespace App\Form;

use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;
use App\Entity\UserLevel;
use Symfony\Component\Form\Extension\Core\Type\DateType;

class VerifyParentUserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('parentUser', EntityType::class, [
                'label' => '认证推荐人',
                'empty_data' => null,
                'placeholder' => '无',
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'required' => false,
                'choice_label' => function (User $user) {
                    return $user->__toString();
                },
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('u')
                        ->where('u.userLevel in (:userLevels)')
                        ->setParameter('userLevels', [UserLevel::PARTNER, UserLevel::ADVANCED]);
                }
            ])
            ->add('parentUserExpiresAt', DateType::class, [
                'label' => '锁定至日期',
                'placeholder' => ['year' => '年', 'month' => '月', 'day' => '日'],
                'input' => 'timestamp',
                'required' => true
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
