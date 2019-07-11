<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UeditorType extends AbstractType{

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

    }

    public function getParent()
    {
        return TextType::class;
    }
}