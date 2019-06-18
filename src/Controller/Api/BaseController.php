<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-08-27
 * Time: 6:14 PM
 */

namespace App\Controller\Api;

use App\Controller\DefaultController;
use App\Entity\GroupUserOrder;
use App\Entity\ProjectBannerMeta;
use App\Entity\ProjectTextMeta;
use App\Entity\ShareSource;
use App\Entity\ShareSourceUser;
use App\Entity\User;
use App\Repository\GroupUserOrderRepository;
use App\Repository\ProjectBannerMetaRepository;
use App\Repository\ProjectTextMetaRepository;
use App\Repository\UserRepository;
use App\Service\Util\CommonUtil;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Entity\File;
use App\Entity\ProjectShareMeta;
use App\Service\Wx\WxCommon;
use App\Entity\Product;
use App\Service\ImageGenerator;
use App\Entity\GroupOrder;

class BaseController extends DefaultController
{
    protected function response403($msg = 'no_auth', $data = [])
    {
        return $this->responseJson($msg, 403, $data);
    }

    protected function response404($msg = 'not_found', $data = [])
    {
        return $this->responseJson($msg, 404, $data);
    }

    protected function response503($msg = 'there_is_an_error', $data = [])
    {
        return $this->responseJson($msg, 503, $data);
    }

    protected function responseJson($msg = null, $code = null, $data = null)
    {
        return $this->json(compact('msg', 'code', 'data'));
    }

    protected function responseRaw($content)
    {
        return new Response($content);
    }

    protected function responseNeedLogin($msg = null)
    {
        $msg = $msg ? $msg : 'need_login';
        return $this->responseJson($msg, 403, []);
    }

    /**
     * @param $thirdSession ex. {"userId": 123, "shareSourceId": 456}
     * @return User|null
     */
    protected function getWxUser($thirdSession):?User
    {
        /**
         * @var UserRepository $userRepository
         */
        $userRepository = $this->getEntityManager()->getRepository(User::class);
        return $userRepository->find($thirdSession);
    }

    protected function getImgUrlPrefix()
    {
        return $this->generateUrl('imagePreview', ['fileId' => null], UrlGeneratorInterface::ABSOLUTE_URL);
    }

    /**
     * @param ProjectTextMetaRepository $projectTextMetaRepository
     * @return array
     */
    protected function createProjectTextMetas(ProjectTextMetaRepository $projectTextMetaRepository) {
        return [
            'text_group_unlock_meta' => $projectTextMetaRepository->findOneBy(['metaKey' => ProjectTextMeta::TEXT_GROUP_UNLOCK])->getArray(),
            'text_unlock_all_meta' => $projectTextMetaRepository->findOneBy(['metaKey' => ProjectTextMeta::TEXT_UNLOCK_ALL])->getArray(),
            'text_watch_meta' => $projectTextMetaRepository->findOneBy(['metaKey' => ProjectTextMeta::TEXT_WATCH])->getArray(),
            'text_upgrade_meta' => $projectTextMetaRepository->findOneBy(['metaKey' => ProjectTextMeta::TEXT_UPGRADE])->getArray(),
            'text_unlock_together_meta' => $projectTextMetaRepository->findOneBy(['metaKey' => ProjectTextMeta::TEXT_UNLOCK_TOGETHER])->getArray(),
        ];
    }

    /**
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createHomePageProjectBannerMetas(ProjectBannerMetaRepository $projectBannerMetaRepository) {

        $where = [
            ProjectBannerMeta::BANNER_HOME_1,
            ProjectBannerMeta::BANNER_HOME_2,
            ProjectBannerMeta::BANNER_HOME_3,
            ProjectBannerMeta::BANNER_HOME_4,
        ];
        $banners = CommonUtil::entityArray2DataArray($projectBannerMetaRepository->findBy(['metaKey' => $where]));

        return $banners;
    }

    /**
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createProductPageProjectBannerMetas(ProjectBannerMetaRepository $projectBannerMetaRepository) {
        return [
            'banner_product' => $projectBannerMetaRepository->findOneBy(['metaKey' => ProjectBannerMeta::BANNER_PRODUCT])->getArray(),
        ];
    }

    /**
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createPreLoginPageProjectBannerMetas(ProjectBannerMetaRepository $projectBannerMetaRepository) {
        return [
            'banner_login' => $projectBannerMetaRepository->findOneBy(['metaKey' => ProjectBannerMeta::BANNER_LOGIN])->getArray(),
        ];
    }

    /**
     * @param ProjectBannerMetaRepository $projectBannerMetaRepository
     * @return array
     */
    protected function createMySharePageProjectBannerMetas(ProjectBannerMetaRepository $projectBannerMetaRepository) {
        return [
            'banner_my_share' => $projectBannerMetaRepository->findOneBy(['metaKey' => ProjectBannerMeta::BANNER_MY_SHARE])->getArray(),
        ];
    }

    /**
     * 返回产品转发和朋友圈的shareSource
     *
     * @param User $user
     * @param Product $product
     * @param $page
     * @return array
     */
    protected function createProductShareSource(User $user, Product $product, $page) {

        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referProductShare
         */
        $referProductShare = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_PRODUCT]);

        $shareSources = [];

        //产品信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user'=> $user, 'product' => $product, 'type' => ShareSource::REFER_PRODUCT]);
        if ($referShareSource == null) {
            $referShareSource = ShareSource::factory(ShareSource::REFER_PRODUCT, $page, $user, null, $referProductShare->getShareTitle(), $product);
            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //产品信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'page' => $page, 'product' => $product, 'type' => ShareSource::QUAN_PRODUCT]);
        if ($quanShareSource == null) {
            $quanShareSource = ShareSource::factory(ShareSource::QUAN_PRODUCT, $page, $user, null, null, $product);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'ss=' . $quanShareSource->getId() . '&p=' . $product->getId(), $page, true);

            $bannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $product->getShareImageFile());
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();


        return $shareSources;
    }

    /**
     * 返回转发和朋友圈的shareSource
     *
     * @param User $user
     * @param $page
     * @return array
     */
    protected function createUserShareSource(User $user, $page) {

        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referMeta
         */
        $referMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_USER]);

        /**
         * @var ProjectShareMeta $quanMeta
         */
        $quanMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::QUAN_USER]);

        $shareSources = [];

        //个人信息页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'type' => ShareSource::REFER_USER]);
        if ($referShareSource == null) {

            $referBannerFile = null;
            if ($referMeta->getShareBannerFileId()) {
                /**
                 * @var File $referBannerFile
                 */
                $referBannerFile = $fileRepository->find($referMeta->getShareBannerFileId());
            }
            $referShareSource = ShareSource::factory(ShareSource::REFER_USER, $page, $user, $referBannerFile, $referMeta->getShareTitle());

            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //个人信息朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['user' => $user, 'page' => $page, 'type' => ShareSource::QUAN_USER]);
        if ($quanShareSource == null) {

            $quanShareSource = ShareSource::factory(ShareSource::QUAN_USER, $page, $user);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'ss=' . $quanShareSource->getId(), $page, true);

            $quanBannerFile = null;
            if ($quanMeta->getShareBannerFileId()) {
                /**
                 * @var File $quanBannerFile
                 */
                $quanBannerFile = $fileRepository->find($quanMeta->getShareBannerFileId());
            }

            $bannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $quanBannerFile);
            $quanShareSource->setBannerFile($bannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();

        return $shareSources;
    }

    /**
     *
     * 返回转发和朋友圈的shareSource
     *
     * @param GroupOrder $groupOrder
     * @param $page
     * @return array
     */
    protected function createGroupOrderShareSource(GroupOrder $groupOrder, $page) {

        $user = $groupOrder->getUser();
        $fileRepository = $this->getEntityManager()->getRepository(File::class);
        $projectShareMeta = $this->getEntityManager()->getRepository(ProjectShareMeta::class);
        $shareSourceRepository = $this->getEntityManager()->getRepository(ShareSource::class);

        /**
         * @var ProjectShareMeta $referMeta
         */
        $referMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::REFER_GROUP_ORDER]);

        /**
         * @var ProjectShareMeta $quanMeta
         */
        $quanMeta = $projectShareMeta->findOneBy(['metaKey' => ShareSource::QUAN_GROUP_ORDER]);

        $shareSources = [];

        //拼团页面转发分享
        $referShareSource = $shareSourceRepository->findOneBy(['groupOrder' => $groupOrder, 'type' => ShareSource::REFER_GROUP_ORDER]);
        if ($referShareSource == null) {

            $referBannerFile = null;
            if ($referMeta->getShareBannerFileId()) {
                /**
                 * @var File $referBannerFile
                 */
                $referBannerFile = $fileRepository->find($referMeta->getShareBannerFileId());
            }
            $referShareSource = ShareSource::factory(ShareSource::REFER_GROUP_ORDER, $page, $user, $referBannerFile, $referMeta->getShareTitle(), $groupOrder->getProduct(), $groupOrder);

            $this->getEntityManager()->persist($referShareSource);
            $this->getEntityManager()->flush();
        }

        //拼团页面朋友圈图片
        $quanShareSource = $shareSourceRepository->findOneBy(['groupOrder' => $groupOrder, 'page' => $page, 'type' => ShareSource::QUAN_GROUP_ORDER]);
        if ($quanShareSource == null) {

            $quanShareSource = ShareSource::factory(ShareSource::QUAN_GROUP_ORDER, $page, $user);
            $wx = new WxCommon($this->getLog());
            $userQrFile = $wx->createWxQRFile($this->getEntityManager(), 'go=' . $groupOrder->getId() . '&ss=' . $quanShareSource->getId(), $page, true);

            $product = $groupOrder->getProduct();
            $bannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $product->getShareImageFile());

            $quanBannerFile = ImageGenerator::createShareQuanBannerImage($this->getEntityManager(), $userQrFile, $bannerFile);
            $quanShareSource->setBannerFile($quanBannerFile);

            $this->getEntityManager()->persist($quanShareSource);
            $this->getEntityManager()->flush();
        }

        $shareSources[ShareSource::REFER] = $referShareSource->getArray();
        $shareSources[ShareSource::QUAN] = $quanShareSource->getArray();

        return $shareSources;
    }

    //根据订单号获取桌号
    protected function getUserTable(GroupUserOrder $groupUserOrder)
    {
        //去除线上课程,产品订单
        if (!$groupUserOrder->getProduct()->isCourseProduct() || $groupUserOrder->getProduct()->getCourse()->isOnline()) {
            return false;
        }

        $user = $groupUserOrder->getUser();

        /**
         * @var GroupUserOrderRepository $groupUserOrderRepository
         */
        $groupUserOrderRepository = $this->getEntityManager()->getRepository(GroupUserOrder::class);

        // 默认按订单号取余
        // 如果共一个推荐人，分配到不同桌号
        $table_num = (int)$groupUserOrder->getProduct()->getCourse()->getTableCount();
        if( !$table_num ){
            return false;
        }

        // 取余
        $user_table = (int)$groupUserOrder->getId()%$table_num;


        // 如果该用户没有推荐用户
        if( !$user->getParentUser() && $groupUserOrderRepository->getTableUserCount($groupUserOrder->getProduct()->getId(),$user_table)){
            return $user_table;
        }

        // 本产品下所有订单
        $table = [];
        $count_arr = $groupUserOrderRepository->findOrdersUsers( $groupUserOrder->getProduct()->getId() );
        if( count($count_arr) > 0 ){
            foreach ($count_arr as $v) {
                $t = $v->getUser()->getParentUser()?$v->getUser()->getParentUser()->getId():0;
                if( $t == $user->getParentUser()->getId() ){
                    $table[$v->getTable()][] = $t;
                }
            }
        }


        // 取余桌没有同级推荐人
        $table_count = [];
        foreach ($table as $k => $v) {
            $table_count[$k] = count($v);
        }
        if( !isset($table_count[$user_table])  && $groupUserOrderRepository->getTableUserCount($groupUserOrder->getProduct()->getId(),$user_table) ){
            return $user_table;
        }

        // 取余桌有同级推荐人，相近的没有同推荐人用户的桌子
        for ( $i = $user_table+1; $i <= $table_num+$user_table-1; $i++) {
            if( $i > $table_num ){
                $j = $i-$table_num;
                if( !isset($table_count[$j]) && $groupUserOrderRepository->getTableUserCount($groupUserOrder->getProduct()->getId(),$j) ){
                    return $j;
                }
            }else{
                if( !isset($table_count[$i]) && $groupUserOrderRepository->getTableUserCount($groupUserOrder->getProduct()->getId(),$i) ){
                    return $i;
                }
            }
        }

        // 每桌都有同推荐人用户，哪一个桌子的同推荐人用户最少
        $table_count = [];
        foreach ($table as $k => $v) {
            $table_count[$k] = count($v);
        }
        asort($table_count);
        $user_table = array_keys($table_count)[0];

        // 桌子都满了
        if(  $groupUserOrderRepository->getTableUserCount($groupUserOrder->getProduct()->getId(),$user_table) ){
            return false;
        }

        return $user_table;
    }
}