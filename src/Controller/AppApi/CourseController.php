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
use App\Repository\ProjectBannerMetaRepository;
use App\Service\ErrorCode;
use App\Service\Util\CommonUtil;
use App\Service\Util\FactoryUtil;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\GroupUserOrderRepository;
use App\Repository\UserRepository;
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
     * 获取分类获取产品详情
     * @Route("/category/product", name="appCategoryProduct", methods= "POST")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function productAction(Request $request, ProductRepository $productRepository)
    {
        $requestProcess = $this->processRequest($request, ['cateId'], ['cateId']);

        // 产品
        $product = $productRepository->findOneBy(['sku'=> CommonUtil::getSpecialTypeSku($requestProcess['cateId'])]);
        if (empty($product)) {
            $requestProcess->throwErrorException(ErrorCode::ERROR_PRODUCT_NOT_EXISTS);
        }

        return $requestProcess->toJsonResponse(['product' => $product->getArray() ]);
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
            ['phone','name','unionid','time', 'unlock_category_id','sign'],
            ['phone', 'unlock_category_id']
        );

        // 验证签名
        $sign = $this->getSign([
            'phone'=>$requestProcess['phone'],
            'name'=>$requestProcess['name'],
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
            $user->setName($requestProcess['name']?$requestProcess['name']:$randPhone);

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
        $product = $productRepository->findOneBy(['sku'=> CommonUtil::getSpecialTypeSku($unlockCategoryId)]);
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