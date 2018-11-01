<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-03-22
 * Time: 1:06 AM
 */

namespace App\Controller;

use App\Controller\Api\BaseController;
use AppBundle\Controller\Traits\Api\ResponseTrait;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/queue")
 */
class QueueHandlerController extends BaseController {

    /**
     * @Route("/handle", name="queueHandler")
     * @param Request $request
     * @return
     */
    public function indexAction(Request $request) {

        $result = null;
        try {
            $commandClass = $request->request->get("commandClass");
            $commandData = $request->request->get("commandData");

            $this->getLog()->info("received command " . $commandClass . ' | ' . $commandData);

            $encoders = array(new XmlEncoder(), new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizers, $encoders);

            $command = $serializer->deserialize($commandData, $commandClass, 'json');
            $this->getCommandBus()->handle($command);

        } catch (\Exception $e) {
            $this->getLog()->error('failed to process command' . $e->getTraceAsString());
            $result = ['status' => false, 'reason' => $e->getTraceAsString()];
        }

        return $this->responseJson('ok', 200, $result);
    }

}