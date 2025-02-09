<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 10:32
 */

namespace App\Controller;

use App\Command\CommandInterface;
use App\DataAccess\DataAccess;
use Doctrine\Common\Persistence\ObjectManagerDecorator;
use FOS\UserBundle\Model\UserManagerInterface;
use Knp\Component\Pager\Paginator;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class DefaultController extends Controller
{
    const PAGE_LIMIT = 20;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CommandBus
     */
    private $commandBus;

    /**
     * @var DataAccess
     */
    private $dataAccess;

    /**
     * DefaultController constructor.
     */
    public function __construct(LoggerInterface $logger, CommandBus $commandBus, DataAccess $dataAccess)
    {
        $this->logger = $logger;
        $this->commandBus = $commandBus;
        $this->dataAccess = $dataAccess;
    }

    /**
     * @return LoggerInterface
     */
    public function getLog()
    {
        return $this->logger;
    }

    /**
     * @return Paginator
     */
    protected function getPaginator()
    {
        return $this->get('knp_paginator');
    }

    /**
     * Shortcut to return the CommandBus Registry service.
     *
     * @return CommandBus
     *
     * @throws \LogicException If TacticianBundle is not available
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @return DataAccess|object
     */
    public function getDataAccess()
    {
        return $this->dataAccess;
    }

    /**
     * @return \Doctrine\Common\Persistence\ObjectManager
     */
    public function getEntityManager() {
        return $this->getDoctrine()->getManager();
    }

    /**
     * @return object|ValidatorInterface
     */
    private function getValidator()
    {
        return $this->get('validator');
    }

    /**
     * 返回 key=>value 数组,key为表单字段,value为错误信息
     * @param array $form
     * @param CommandInterface $command
     * @return array
     */
    public function validate($form, CommandInterface $command)
    {
        $errors = $this->getValidator()->validate($command);
        return $this->loadError($form, $errors);
    }

    /**
     * @param array $form
     * @param ConstraintViolationListInterface $errors
     * @return array
     */
    private function loadError($form, $errors)
    {
        $validationErrors = [];
        foreach ($form as $field => $value) {
            foreach ($errors as $error) {
                if ($error->getPropertyPath() == $field) {
                    $validationErrors[$field] = $error->getMessage();
                }
            }
        }
        return $validationErrors;
    }

    /**
     * Get current env
     * @return string
     */
    public function getEnvironment() {
        return $this->get('kernel')->getEnvironment();
    }

    /**
     * Check if current env is dev
     * @return bool
     */
    public function isDev() {
        return $this->getEnvironment() == 'dev';
    }

    /**
     * Get user manager for Interface
     * @return UserManagerInterface
     */
    public function getUserManager() {
        return $this->get('fos_user.user_manager');
    }

    /**
     * @param string $name
     * @return mixed
     */
    public function getParameter(string $name)
    {
        return parent::getParameter($name);
    }

    /**
     * @param string $id The service id
     * @return mixed
     */
    public function getService($id)
    {
        return parent::get($id);
    }

    /**
     * Get ProjectMetaValue
     *
     * @param string $key
     * @return mixed
     */
    public function getProjectMetaValue($key)
    {
        return $this->get('App\Service\ProjectMetaHelper')->getMetaValue($key);
    }
}