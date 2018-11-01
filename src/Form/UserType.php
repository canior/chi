<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('username', null, ['label' => '用户名'])
//            ->add('usernameCanonical')
//            ->add('email')
//            ->add('emailCanonical')
            ->add('enabled', null, ['label' => '激活'])
//            ->add('salt')
            ->add('password', PasswordType::class, ['label' => '密码'])
//            ->add('lastLogin')
//            ->add('confirmationToken')
//            ->add('passwordRequestedAt')
            ->add('roles', ChoiceType::class, [
                'label' => '角色',
                'mapped' => false,
                'multiple' => true,
                'choices' => array_flip(User::$roleTexts)
            ])
            ->add('nickname', null, ['label' => '昵称'])
            ->add('pendingTotalRewards', null, ['label' => '待发返现'])
            ->add('totalRewards', null, ['label' => '返现总额'])
            ->add('wxOpenId', null, ['label' => '微信OpenID'])
            ->add('wxUnionId', null, ['label' => '微信UnionID'])
            ->add('avatarUrl', null, ['label' => '头像地址'])
            ->add('gender', null, ['label' => '性别'])
            ->add('location', null, ['label' => '城市'])
//            ->add('createdAt')
//            ->add('updatedAt')
//            ->add('region')
//            ->add('parentUser')
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
