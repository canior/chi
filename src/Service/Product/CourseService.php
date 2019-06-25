<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/6/25
 * Time: 17:30
 */

namespace App\Service\Product;

use App\Entity\Course;
use App\Service\Util\FactoryUtil;

/**
 * Class Course
 * @package App\Service\Product
 * @author zxqc2018
 */
class CourseService extends ProductService
{
    /**
     * @param int $courseId
     * @author zxqc2018
     * @return Course
     */
    public function getCourseById(int $courseId)
    {
        /**
         * @var Course $course
         */
        $course = FactoryUtil::courseRepository()->find($courseId);

        return $course;
    }
}