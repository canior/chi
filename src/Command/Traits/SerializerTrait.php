<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2017-09-28
 * Time: 19:53
 */

namespace App\Command\Traits;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

trait SerializerTrait
{
    /**
     * @return string json
     */
    public function serialize()
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->serialize($this, 'json');
    }

    /**
     * @param $json
     * @return $this
     */
    public function deserialize($json)
    {
        $encoders = array(new XmlEncoder(), new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        return $serializer->deserialize($json, get_class($this), 'json');
    }
}