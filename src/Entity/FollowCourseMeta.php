<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-01
 * Time: 1:07 PM
 */

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\FollowCourseMetaRepository")
 */
class FollowCourseMeta extends Follow
{


    public function isTeacher()
    {
        return false;
    }

    public function isCourse()
    {
        return true;
    }


    /**
     * @return array
     */
    public function getArray()
    {
        return [
            'id'=>$this->getId(),
            'course'=>$this->getDataId()->getArray(),
            'user'=>$this->getUser()->getArray(),
        ];
    }
}