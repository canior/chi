<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/25
 * Time: 17:30
 */

namespace App\Service\Product;


use App\Entity\CourseStudent;
use App\Entity\User;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;

/**
 * Class OfflineCourse
 * @package App\Service\Product
 * @author zxqc2018
 */
class OfflineCourseService extends CourseService
{
    /**
     * 线下签到
     * @param User $user
     * @param $courseId
     * @param $courseStudentStatus
     * @return \App\Service\ResultData
     * @author zxqc2018
     */
    public function offlineSign(User $user, $courseId, $courseStudentStatus)
    {
        $res = CommonUtil::resultData();
        $courseId = intval($courseId);
        $course = $this->getCourseById($courseId);

        if (empty($course)) {
            $res->throwErrorException(ErrorCode::ERROR_COURSE_NOT_EXISTS, []);
        }

        $groupUserOrder = FactoryUtil::groupUserOrderRepository()->findOneBy(['product' => $course->getProduct(), 'user' => $user]);

        if (!$groupUserOrder) {
            $memo = '未找到课程注册订单记录';
            $course->refuseStudent($user, $memo);
        } else if (!$course->hasStudent($user)) {
            $memo = '未找到注册记录';
            $course->refuseStudent($user, $memo);
        } else if ($course->isExpired()) {
            $memo = '课程已结束';
            $course->refuseStudent($user, $memo);
        } else if (!$course->getProduct()->isActive()) {
            $memo = '课程未发布';
            $course->refuseStudent($user, $memo);
        } else {
            if ($courseStudentStatus == CourseStudent::WELCOME) {
                if (!$course->isWelcomed($user)) {
                    $course->welcomeStudent($user);
                } else {
                    $course->signInStudent($user);
                }
            } else if ($courseStudentStatus == CourseStudent::SIGNIN) {
                $course->signInStudent($user);
            }
        }

        CommonUtil::entityPersist($course);

        $res['course'] = CommonUtil::obj2Array($course);
        $res['groupUserOrder'] = CommonUtil::obj2Array($groupUserOrder);
        return $res;
    }
}