<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-09-06
 * Time: 07:29
 */

namespace App\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DropzoneType extends AbstractType
{
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['route'] = $options['route'];
        $view->vars['maxFiles'] = $options['maxFiles'];
        $view->vars['maxFilesize'] = $options['maxFilesize'];
        $view->vars['acceptedFiles'] = $options['acceptedFiles'];
        $view->vars['uploadMultiple'] = $options['uploadMultiple'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'route' => 'fileUpload',
            'maxFiles' => 1,
            'maxFilesize' => 5,
            'acceptedFiles' => 'image/*',
            'uploadMultiple' => 'false'
        ]);
    }

    public function getParent()
    {
        return FileType::class;
    }
}