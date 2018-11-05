<?php

namespace App\Form;

use App\Entity\ProjectRewardsMeta;
use Doctrine\DBAL\Types\DecimalType;
use JsonSchema\Constraints\NumberConstraint;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Constraints\Type;

class ProjectRewardsMetaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('memo', null, ['label' => '描述', 'attr' => ['rows' => 5]])
            ->add('groupOrderRewardsRate', null, [
                'label' => '拼团订单收益 返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
            ->add('groupOrderUserRewardsRate', null, [
                'label' => '拼团订单传销收益 返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
            ->add('regularOrderRewardsRate', null, [
                'label' => ' 普通订单收益 返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
            ->add('regularOrderUserRewardsRate', null, [
                'label' => '普通订单传销收益 返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ProjectRewardsMeta::class,
        ]);
    }
}
