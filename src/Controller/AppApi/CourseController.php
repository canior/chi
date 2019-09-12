<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/27
 * Time: 19:37
 */

namespace App\Controller\AppApi;


use App\Entity\ProjectVideoMeta;
use App\Entity\User;
use App\Entity\Product;
use App\Entity\UserStatistics;
use App\Repository\CategoryRepository;
use App\Repository\ProductRepository;
use App\Repository\UserRepository;
use App\Repository\TeacherRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

use App\Entity\Course;
use App\Command\File\BatchUploadFilesCommand;
use App\Command\File\UploadFileCommand;
use App\Entity\Subject;
use App\Command\Product\Image\CreateOrUpdateProductImagesCommand;
use App\Repository\CourseRepository;
use App\Command\Product\Spec\Image\CreateOrUpdateProductSpecImagesCommand;
use App\Entity\File;
use App\Repository\GroupUserOrderRepository;
use App\Entity\GroupUserOrder;


class CourseController extends ProductController
{
    /**
     * 首页
     * @Route("/home", name="homeIndex", methods= "GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function homeAction(Request $request, ProductRepository $productRepository, ProjectBannerMetaRepository $projectBannerMetaRepository, CategoryRepository $categoryRepository)
    {
        $bannersArray = $this->createProductBanners($projectBannerMetaRepository);

        $recommendProductsArray = $this->findHomeRecommendProducts($categoryRepository);

        $newestProductsArray = $this->findHomeNewestProducts($productRepository);

        $category = $categoryRepository->findSiteCategoryListQuery(0, '', null)->getQuery()->getResult();
        $data = [
            'banners' => $bannersArray,
            'freeZoneBanner' => $this->createHomeFreeZoneBannerMetas($projectBannerMetaRepository),
            'recommendCategoryList' => $recommendProductsArray,
            'newestCategoryList' => $newestProductsArray,
            'category' => CommonUtil::entityArray2DataArray($category, 'simpleArray'),
            'userId' => $this->getAppUserId(),
        ];

        return CommonUtil::resultData($data)->toJsonResponse();
    }

    /**
     * 获取分类列表
     * @Route("/category/list", name="appGategoryList", methods= "POST")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function categoryListAction(Request $request, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'cateId', 'page', 'pageNum'
        ], ['cateId']);
        $user = $this->getAppUser();

        $parentCategory = $categoryRepository->find($requestProcess['cateId']);

        if (empty($parentCategory)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_CATEGORY_NOT_EXISTS, []);
        }
        $categoryQuery = $categoryRepository->findSiteCategoryListQuery($requestProcess['cateId'], '', null);
        $categoryList = $this->getPaginator()->paginate($categoryQuery, $requestProcess['page'], $requestProcess['pageNum']);


        //刷新视频
        $refreshStatus = $parentCategory->refreshAliyunVideo();

        if ($refreshStatus == 2) {
            $this->entityPersist($parentCategory);
        }
        return $requestProcess->toJsonResponse([
            'categoryList' => CommonUtil::entityArray2DataArray($categoryList),
            'category' => $this->getCategoryVideoArray($parentCategory),
            'user' => CommonUtil::obj2Array($user)
        ]);
    }

    /**
     * 获取分类详情
     * @Route("/category/detail", name="appCategoryDetail", methods= "POST")
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     * @author zxqc2018
     */
    public function categoryDetailAction(Request $request, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'cateId'
        ], ['cateId']);
        $user = $this->getAppUser(true);

        $category = $categoryRepository->find($requestProcess['cateId']);

        if (empty($category) || $category->isSingleCourse()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_CATEGORY_NOT_EXISTS, []);
        }

        return $requestProcess->toJsonResponse([
            'category' => $this->getCategoryVideoArray($category, $user),
            'user' => CommonUtil::obj2Array($user),
        ]);
    }

    /**
     * 获取课程详情
     *
     * @Route("/auth/course/detail", name="appCourseDetail", methods="POST")
     * @param Request $request
     * @return JsonResponse
     */
    public function detailAction(Request $request): JsonResponse {
        return parent::detailAction($request);
    }

    /**
     * 免费专区
     * @Route("/freeZone", name="appFreeZone", methods={"POST"})
     * @param Request $request
     * @param CategoryRepository $categoryRepository
     * @return JsonResponse
     * @author zxqc2018
     */
    public function freeZoneAction(Request $request, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest($request);
        return $requestProcess->toJsonResponse([
            'freeCategoryList' => $this->findHomeFreeZoneProducts($categoryRepository),
            'freeVideoInfo' => $this->getProjectVideoMeta(ProjectVideoMeta::VIDEO_FREE_ZONE)
        ]);
    }

    /**
     * 观看次数
     * @Route("/playTimes", name="appPlayTimes", methods={"POST"})
     * @return JsonResponse
     * @author zxqc2018
     */
    public function playTimesAction()
    {
        $requestProcess = $this->processRequest(null, [
            'courseId'
        ], ['courseId']);
        $userId = $this->getAppUserId();
        if (!empty($userId)) {
            FactoryUtil::courseService()->incLookNum($requestProcess['courseId']);
        }
        return $requestProcess->toJsonResponse();
    }

    /**
     * 获取解锁系列的产品详情
     * @Route("/unlock/category/product", name="appGetUnlockCategoryProduct", methods={"POST"})
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     * @author zxqc2018
     */
    public function getUnlockCategoryProduct(Request $request, ProductRepository $productRepository)
    {
        $requestProcess = $this->processRequest($request, [
            'cateId'
        ], ['cateId']);

        $user = $this->getAppUser(true);

        $unlockSku = 'unlock-'. $requestProcess['cateId'];
        $product = $productRepository->findOneBy(['sku' => $unlockSku]);
        $data = [
            'product' => CommonUtil::obj2Array($product),
            'textMetaArray' => [],
            'shareSources' => [],
            'user' => CommonUtil::obj2Array($user),
        ];

        return $requestProcess->toJsonResponse($data);
    }

    /**
     * 获取讲师
     * @Route("/course/theater", name="appCourseTheater", methods={"POST"})
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return JsonResponse
     * @author zxqc2018
     */
    public function getCourseTheater(Request $request, TeacherRepository $teacherRepository)
    {
        $requestProcess = $this->processRequest($request, [], []);

        $teachers = $teacherRepository->findAll();

        $teachers_arr = [];
        foreach ($teachers as $k => $v) {
            $teachers_arr[] = $v->getArray();
        }

        return $requestProcess->toJsonResponse(['teachers'=>$teachers_arr]);
    }

    /**
     * @Route("/auth/course/create", name="appCourseNew", methods="POST")
     * @param Request $request
     * @param TeacherRepository $teacherRepository
     * @return Response
     */
    public function createAction(Request $request, TeacherRepository $teacherRepository,CourseRepository $courseRepository)
    {

        $datas = json_decode($request->getContent(), true);

        $requestProcess = $this->processRequest($request, ['id','title', 'price','startDate', 'endDate', 'city','address','teacher_id','tableCount','tableUserCount','shortDescription','images','specImages','shareImageFileId'], ['title', 'price','startDate', 'endDate', 'city','teacher_id','tableCount','tableUserCount','shortDescription','images','specImages','shareImageFileId']);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }
        
        $id = isset($datas['id']) ? $datas['id'] : null;

        $title = isset($datas['title']) ? $datas['title'] : null;
        $price = isset($datas['price']) ? $datas['price'] : null;
        $startDate = isset($datas['startDate']) ? $datas['startDate'] : null;
        $endDate = isset($datas['endDate']) ? $datas['endDate'] : null;
        $city = isset($datas['city']) ? $datas['city'] : null;
        $address = isset($datas['address']) ? $datas['address'] : null;
        $teacher_id = isset($datas['teacher_id']) ? $datas['teacher_id'] : null;
        $tableCount = isset($datas['tableCount']) ? $datas['tableCount'] : null;
        $tableUserCount = isset($datas['tableUserCount']) ? $datas['tableUserCount'] : null;
        $shortDescription = isset($datas['shortDescription']) ? $datas['shortDescription'] : null;

        if( $id ){
            $course = $courseRepository->find($id);
            if( !$course ){
                return CommonUtil::resultData( [], ErrorCode::ERROR_COURSE_NOT_EXISTS )->toJsonResponse();
            }
        }else{
            $course = new Course();
        }
        
        $course->setIsOnline(false);
        $course->setSubject(Subject::THINKING);
        $course->setTitle($title);
        $course->setPrice($price);
        $course->setStartDate( $startDate?strtotime($startDate):null );
        $course->setEndDate( $startDate?strtotime($endDate):null );
        $course->setCity($city);
        $course->setAddress($address);
        $course->setTableCount($tableCount);
        $course->setTableUserCount($tableUserCount);
        $course->setShortDescription($shortDescription);
        $course->setInitiator($user);
        // $course->setStatus($status);

        if($teacher_id){
            $teacher = $teacherRepository->find($teacher_id);
            if($teacher){
                $course->setTeacher($teacher);
            }
        }
        $this->entityPersist($course);

        //update preview image
        $images = isset($datas['images']) ? $datas['images'] : null;
        if($images){
            $imagesCommand = new CreateOrUpdateProductImagesCommand($course->getProduct()->getId(), $images);
            $this->getCommandBus()->handle($imagesCommand);            
        }

        $specImages = isset($datas['specImages']) ? $datas['specImages'] : null;
        if($specImages){
            $specImagesCommand = new CreateOrUpdateProductSpecImagesCommand($course->getProduct()->getId(), $specImages);
            $this->getCommandBus()->handle($specImagesCommand);            
        }

        $shareImageFileId = isset($datas['shareImageFileId']) ? $datas['shareImageFileId'] : null;
        if ($shareImageFileId) {
            $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
            $course->getProduct()->setShareImageFile($shareImageFile);
            $this->getEntityManager()->persist($course->getProduct());
            $this->getEntityManager()->flush();
        }

        $course->setPriority( $course->getId() );
        $this->entityPersist($course);

        // 返回
        return CommonUtil::resultData( ['course'=>$course->getArray()] )->toJsonResponse();
    }

    /**
     * @Route("/auth/course/file/upload", name="coursefileUpload")
     * @param Request $request
     * @return Response
     */
    public function uploadFileAction(Request $request)
    {
        if (!$request->isMethod('POST')) {
            exit;
        }

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }
        
        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files;
        $fileId = null;
        $name = null;
        foreach ($files as $file) {
            try {
                $command = new UploadFileCommand($file, $user->getId());
                $fileId = $this->getCommandBus()->handle($command);
                $name = $file->getClientOriginalName();
            } catch (\Exception $e) {
                $this->getLog()->error('upload file failed {error}', ['error' => $e->getMessage()]);
                return new JsonResponse(['status' => false, 'error' => $e->getMessage()]);
            }
        }

        return new JsonResponse(['status' => true, 'fileId' => $fileId, 'name' => $name]);
    }

    /**
     * @Route("/auth/course/delete", name="course_delete", methods="POST")
     */
    public function del(Request $request,CourseRepository $courseRepository)
    {

        $datas = json_decode($request->getContent(), true);
        $requestProcess = $this->processRequest($request, ['id'],['id']);
        $id = isset($datas['id']) ? $datas['id'] : null;

        $course = $courseRepository->find($id);
        if( !$course ){
            return CommonUtil::resultData( [], ErrorCode::ERROR_COURSE_NOT_EXISTS )->toJsonResponse();
        }

        $course->setIsDeleted(true);
        $this->entityPersist($course);

        return CommonUtil::resultData( [] )->toJsonResponse();
    }
    
    /**
     * 生成内容签名
     * @param array $data
     * @param string $secret
     * @return string
     */
    function getSign($data, $secret = 'qXwaX1LVooCzrhWv')
    {
        $signContentMethod = function ($data) {
            ksort($data);
            $buff = '';
            foreach ($data as $k => $v) {
                $buff .= ($k != 'sign' && $v != '' && !is_array($v)) ? $k . '=' . $v . '&' : '';
            }
            return trim($buff, '&');
        };
        $string = md5($signContentMethod($data) . '&key=' . $secret);
        return strtoupper($string);
    }


    /**
     * @Route("/course/unlockCategory", name="AppApiUnlockCategory",  methods={"POST"})
     * @param JWTTokenManagerInterface $JWTTokenManager
     * @return JsonResponse|\Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function unlock(GroupUserOrderRepository $groupUserOrderRepository,UserRepository $userRepository, ProductRepository $productRepository, CategoryRepository $categoryRepository)
    {
        $requestProcess = $this->processRequest(null, 
            ['phone','nickname','unionid','time', 'unlock_category_id','sign'],
            ['phone', 'unlock_category_id']
        );

        // 验证签名
        $sign = $this->getSign([
            'phone'=>$requestProcess['phone'],
            'nickname'=>$requestProcess['nickname'],
            'unionid'=>$requestProcess['unionid'],
            'time'=>$requestProcess['time'],
            'unlock_category_id'=>$requestProcess['unlock_category_id'],
            'sign'=>$requestProcess['sign'],
        ]);

        if($sign != $requestProcess['sign']){
            $requestProcess->throwErrorException(ErrorCode::ERROR_SIGN);
        }

        $unlockCategoryId = $requestProcess['unlock_category_id'];
        $phone = $requestProcess['phone'];

        // 查询匹配用户
        $user = $userRepository->findOneBy(['phone' => $phone]);
        if (empty($user)) {

            // 创建用户
            $randPhone = $phone . mt_rand(1000,9999);
            $user = new User();
            $user->setUsername($randPhone);
            $user->setPhone($phone);
            $user->setUsernameCanonical($randPhone);
            $user->setEmail($randPhone . '@qq.com');
            $user->setEmailCanonical($randPhone . '@qq.com');
            $user->setPassword("IamCustomer");
            $user->setLastLoginTimestamp(time());
            $user->setNickname($requestProcess['nickname']?$requestProcess['nickname']:$randPhone);

            if( $requestProcess['unionid'] ){
                $user->setWxUnionId($requestProcess['unionid']);
            }
            $userStatistics = new UserStatistics($user);
            $user->addUserStatistic($userStatistics);
            $user->info('created user ' . $user);

            $this->entityPersist($user);
        }

        // 是否已经解锁
        $has = $groupUserOrderRepository->findOneBy(['user'=>$user,'unlockCategory' => $unlockCategoryId ]);
        if($has){
            $requestProcess->throwErrorException(ErrorCode::ERROR_COURSE_CATEGORY_ALREADY_PAY);
        }

        // 产品
        $product = $productRepository->findOneBy(['title'=>Product::CATEGORY_UNLOCK_PRODUCT]);
        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        //解锁系列课
        $groupUserOrder = GroupUserOrder::factory($user, $product);
        $unlockCategory = $categoryRepository->find($unlockCategoryId);
        if (empty($unlockCategory->getParentCategory()) || $unlockCategory->isSingleCourse()) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_UNLOCK_CATEGORY_NOT_PRIVILEGE);
        }
        $groupUserOrder->setUnlockCategory($unlockCategory);
        $groupUserOrder->setOrderPaymentStatus(GroupUserOrder::PAID);
        
        $this->entityPersist($groupUserOrder);
        
        return $requestProcess->toJsonResponse([]);
    }
}