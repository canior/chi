<?php

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;


use Symfony\Component\EventDispatcher\EventDispatcherInterface;

class UeditorType extends AbstractType{

    private $twig;
    private $dispatcher;

    public function __construct(\Twig_Environment $twig, EventDispatcherInterface $dispatcher){
        $this->twig = $twig;
        $this->dispatcher = $dispatcher;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

    }
}