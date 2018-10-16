<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-09-03
 * Time: 20:24
 */

namespace App\Maker;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Doctrine\Common\Inflector\Inflector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Doctrine\DoctrineHelper;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Str;
use Symfony\Bundle\MakerBundle\Validator;
use Symfony\Bundle\TwigBundle\TwigBundle;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Validator\Validation;

class MakeCrudBackend extends AbstractMaker
{
    private $doctrineHelper;
    public function __construct(DoctrineHelper $doctrineHelper)
    {
        $this->doctrineHelper = $doctrineHelper;
    }
    public static function getCommandName(): string
    {
        return 'make:crud:backend';
    }
    /**
     * {@inheritdoc}
     */
    public function configureCommand(Command $command, InputConfiguration $inputConfig)
    {
        $help = 'The <info>%command.name%</info> command generates crud controller with templates for selected entity.

<info>php %command.full_name% BlogPost</info>

If the argument is missing, the command will ask for the entity class name interactively.';

        $command
            ->setDescription('Creates CRUD for Doctrine entity class')
            ->addArgument('entity-class', InputArgument::OPTIONAL, sprintf('The class name of the entity to create CRUD (e.g. <fg=yellow>%s</>)', Str::asClassName(Str::getRandomTerm())))
            ->setHelp($help)
        ;
        $inputConfig->setArgumentAsNonInteractive('entity-class');
    }
    public function interact(InputInterface $input, ConsoleStyle $io, Command $command)
    {
        if (null === $input->getArgument('entity-class')) {
            $argument = $command->getDefinition()->getArgument('entity-class');
            $entities = $this->doctrineHelper->getEntitiesForAutocomplete();
            $question = new Question($argument->getDescription());
            $question->setAutocompleterValues($entities);
            $value = $io->askQuestion($question);
            $input->setArgument('entity-class', $value);
        }
    }
    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator)
    {
        $entityClassDetails = $generator->createClassNameDetails(
            Validator::entityExists($input->getArgument('entity-class'), $this->doctrineHelper->getEntitiesForAutocomplete()),
            'Entity\\'
        );
        $entityDoctrineDetails = $this->doctrineHelper->createDoctrineDetails($entityClassDetails->getFullName());
        $repositoryVars = [];
        if (null !== $entityDoctrineDetails->getRepositoryClass()) {
            $repositoryClassDetails = $generator->createClassNameDetails(
                '\\'.$entityDoctrineDetails->getRepositoryClass(),
                'Repository\\',
                'Repository'
            );
            $repositoryVars = [
                'repository_full_class_name' => $repositoryClassDetails->getFullName(),
                'repository_class_name' => $repositoryClassDetails->getShortName(),
                'repository_var' => lcfirst(Inflector::singularize($repositoryClassDetails->getShortName())),
            ];
        }
        $controllerClassDetails = $generator->createClassNameDetails(
            $entityClassDetails->getRelativeNameWithoutSuffix(),
            'Controller\\Backend\\',
            'Controller'
        );
        $iter = 0;
        do {
            $formClassDetails = $generator->createClassNameDetails(
                $entityClassDetails->getRelativeNameWithoutSuffix().($iter ?: ''),
                'Form\\',
                'Type'
            );
            ++$iter;
        } while (class_exists($formClassDetails->getFullName()));
        $entityVarPlural = lcfirst(Inflector::pluralize($entityClassDetails->getShortName()));
        $entityVarSingular = lcfirst(Inflector::singularize($entityClassDetails->getShortName()));
        $entityTwigVarPlural = Str::asTwigVariable($entityVarPlural);
        $entityTwigVarSingular = Str::asTwigVariable($entityVarSingular);
        $routeName = Str::asRouteName($controllerClassDetails->getRelativeNameWithoutSuffix());
        $generator->generateClass(
            $controllerClassDetails->getFullName(),
            'templates/crud/controller/Controller.tpl.php',
            array_merge([
                'parent_class_name' => \method_exists(AbstractController::class, 'getParameter') ? 'AbstractController' : 'Controller',
                'entity_full_class_name' => $entityClassDetails->getFullName(),
                'entity_class_name' => $entityClassDetails->getShortName(),
                'form_full_class_name' => $formClassDetails->getFullName(),
                'form_class_name' => $formClassDetails->getShortName(),
                'route_path' => Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix()),
                'route_name' => $routeName,
                'entity_var_plural' => $entityVarPlural,
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_var_singular' => $entityVarSingular,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
            ],
                $repositoryVars
            )
        );
        $generator->generateClass(
            $formClassDetails->getFullName(),
            'form/Type.tpl.php',
            [
                'bounded_full_class_name' => $entityClassDetails->getFullName(),
                'bounded_class_name' => $entityClassDetails->getShortName(),
                'form_fields' => $entityDoctrineDetails->getFormFields(),
            ]
        );
        $templatesPath = Str::asFilePath($controllerClassDetails->getRelativeNameWithoutSuffix());
        $templates = [
//            '_delete_form' => [
//                'route_name' => $routeName,
//                'entity_twig_var_singular' => $entityTwigVarSingular,
//                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
//            ],
            '_form' => [
                'route_name' => $routeName,
            ],
            'edit' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'route_name' => $routeName,
            ],
            'index' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'entity_twig_var_plural' => $entityTwigVarPlural,
                'entity_twig_var_singular' => $entityTwigVarSingular,
                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
                'route_name' => $routeName,
            ],
            'new' => [
                'entity_class_name' => $entityClassDetails->getShortName(),
                'route_name' => $routeName,
            ],
//            'show' => [
//                'entity_class_name' => $entityClassDetails->getShortName(),
//                'entity_twig_var_singular' => $entityTwigVarSingular,
//                'entity_identifier' => $entityDoctrineDetails->getIdentifier(),
//                'entity_fields' => $entityDoctrineDetails->getDisplayFields(),
//                'route_name' => $routeName,
//            ],
        ];
        foreach ($templates as $template => $variables) {
            $generator->generateFile(
                'templates/backend/'.$templatesPath.'/'.$template.'.html.twig',
                'templates/crud/templates/'.$template.'.tpl.php',
                $variables
            );
        }
        $generator->writeChanges();
        $this->writeSuccessMessage($io);
        $io->text(sprintf('Next: Check your new CRUD by going to <fg=yellow>%s/</>', Str::asRoutePath($controllerClassDetails->getRelativeNameWithoutSuffix())));
    }
    /**
     * {@inheritdoc}
     */
    public function configureDependencies(DependencyBuilder $dependencies)
    {
        $dependencies->addClassDependency(
            Route::class,
            'annotations'
        );
        $dependencies->addClassDependency(
            AbstractType::class,
            'form'
        );
        $dependencies->addClassDependency(
            Validation::class,
            'validator'
        );
        $dependencies->addClassDependency(
            TwigBundle::class,
            'twig-bundle'
        );
        $dependencies->addClassDependency(
            DoctrineBundle::class,
            'orm-pack'
        );
        $dependencies->addClassDependency(
            CsrfTokenManager::class,
            'security-csrf'
        );
    }
}