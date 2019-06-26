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
 * @ORM\Entity(repositoryClass="App\Repository\FollowTeacherMetaRepository")
 */
class FollowTeacherMeta extends Follow
{


    public function isCourse()
    {
        return false;
    }

    public function isTeacher()
    {
        return true;
    }

    /**
     * @return array
     */
    public function getArray()
    {
        return [

        ];
    }
}