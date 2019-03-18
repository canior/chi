<?php

namespace App\Form;

use App\Entity\Course;
use App\Entity\Teacher;
use App\Entity\UpgradeOrderCoupon;
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
use App\Entity\User;

class UpgradeOrderCouponType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('coupon', null, ['label' => '升级码', 'disabled' => true])
            ->add('upgradeUser', EntityType::class, [
                'label' => '用户',
                'placeholder' => '请选择用户',
                'empty_data' => null,
                'attr' => ['class' => 'form-control chosen'],
                'class' => User::class,
                'choice_label' => function (User $user) {
                    return $user;
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => UpgradeOrderCoupon::class,
        ]);
    }
}
