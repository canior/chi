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
            ->add('captainRewardsRate', null, [
                'label' => '拼团订单【团长】返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
            ->add('joinerRewardsRate', null, [
                'label' => '拼团订单【团员】返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
            ->add('regularRewardsRate', null, [
                'label' => '【普通订单】返现此产品总返现金额(%)',
                'constraints' => [
                    new NotBlank(['message' => '不能为空']),
                    new Range(['min' => 0.01, 'max' => 0.99, 'invalidMessage' => '请输入 0.01 ~ 0.99 范围内的数值'])
                ]
            ])
            ->add('userRewardsRate', null, [
                'label' => '【传销上线】返现此产品总返现金额(%)',
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
