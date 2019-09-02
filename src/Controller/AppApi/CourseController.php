<?php
/**
 * Created by PhpStorm.
 * User: zxqc2018
 * Date: 2019/5/27
 * Time: 19:37
 */

namespace App\Controller\AppApi;


use App\Entity\ProjectVideoMeta;
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

        return $requestProcess->toJsonResponse(['theaters'=>$teachers_arr]);
    }

    /**
     * @Route("/auth/course/new", name="appCourseNew", methods="POST")
     * @param Request $request
     * @param TeacherRepository $teacherRepository
     * @return Response
     */
    public function newAction(Request $request, TeacherRepository $teacherRepository,CategoryRepository $categoryRepository, UserRepository $userRepository)
    {

        $datas = json_decode($request->getContent(), true);

        $requestProcess = $this->processRequest($request, ['subject', 'title', 'price','startDate', 'endDate', 'city','address','teacher_id','tableCount','tableUserCount','shortDescription'], ['subject', 'title', 'price','startDate', 'endDate', 'city','teacher_id','tableCount','tableUserCount','shortDescription']);

        // 查询匹配用户
        $user =  $this->getAppUser();
        if ($user == null) {
            return CommonUtil::resultData( [], ErrorCode::ERROR_LOGIN_USER_NOT_FIND )->toJsonResponse();
        }
        
        $subject = isset($datas['subject']) ? $datas['subject'] : null;
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

        $course = new Course();
        $course->setIsOnline(false);
        $course->setSubject($subject);
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

        //update preview image
        $previewImageFileId = isset($datas['image']) ? $datas['image'] : null;
        if ($previewImageFileId) {
            $previewImageFile = $this->getEntityManager()->getRepository(File::class)->find($previewImageFileId);
            $course->setPreviewImageFile($previewImageFile);
        } else {
            $course->setPreviewImageFile(null);
        }

        $shareImageFileId = isset($datas['share_image']) ? $datas['share_image'] : null;
        if ($shareImageFileId) {
            $shareImageFile = $this->getEntityManager()->getRepository(File::class)->find($shareImageFileId);
            $course->setShareImageFile($shareImageFile);
        } else {
            $course->setShareImageFile(null);
        }

        $this->entityPersist($course);


        $course->setPriority( $course->getId() );
        $this->entityPersist($course);

        // 返回
        return CommonUtil::resultData( ['course'=>$course->getArray()] )->toJsonResponse();
    }

    /**
     * @Route("/course/file/upload", name="fileUpload")
     * @param Request $request
     * @return Response
     */
    public function uploadFileAction(Request $request)
    {
        if (!$request->isMethod('POST')) {
            exit;
        }

        /**
         * @var UploadedFile[] $files
         */
        $files = $request->files;
        $fileId = null;
        $name = null;
        foreach ($files as $file) {
            try {
                $command = new UploadFileCommand($file, $this->getUser()->getId());
                $fileId = $this->getCommandBus()->handle($command);
                $name = $file->getClientOriginalName();
            } catch (\Exception $e) {
                $this->getLog()->error('upload file failed {error}', ['error' => $e->getMessage()]);
                return new JsonResponse(['status' => false, 'error' => $e->getMessage()]);
            }
        }

        return new JsonResponse(['status' => true, 'fileId' => $fileId, 'name' => $name]);
    }
}