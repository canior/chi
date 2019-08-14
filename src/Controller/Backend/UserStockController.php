<?php

namespace App\Controller\Backend;

use App\Entity\UserAddress;
use App\Entity\UserLevel;
use App\Repository\UserAddressRepository;
use App\Repository\UserRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\File;
use App\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/backend")
 */
class UserStockController extends BackendController
{
    /**
     * @Route("/user/stock/statistic", name="user_stock_statistic", methods="GET")
     * @param UserRepository $userRepository
     * @param UserAddressRepository $userAddressRepository
     * @return Response
     */
    public function statistic(UserRepository $userRepository, UserAddressRepository $userAddressRepository, Request $request): Response
    {
        $data = [
            'title' => '名额管理',
            'form' => [
                'name' => $request->query->get('name', null),
                'page' => $request->query->getInt('page', 1)
            ]
        ];
        $data['data'] = array();

        if ($data['form']['name']) {
            $userAddressArray = $userAddressRepository->findBy(['name' => $data['form']['name']],['id' => 'DESC']);
            foreach($userAddressArray as $userAddress) {
                $data['data'][] = $userAddress->getUser();
            }
        }
        else {
            $data['data'] = $userRepository->findBy(['userLevel' => UserLevel::PARTNER],['id' => 'DESC']);
        }

        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/user_stock/statistic.html.twig', $data);
    }

    /**
     * @Route("/user/stock/{userId}", name="user_stock_index", methods="GET")
     * @param UserRepository $userRepository
     * @param $userId
     * @param Request $request
     * @return Response
     */
    public function index(UserRepository $userRepository, $userId, Request $request): Response
    {
        $data = [
            'title' => '用户名额管理',
            'form' => [
            ]
        ];
        /**
         * @var User $user
         */
        $user = $this->getEntityManager()->getRepository(User::class)->find($userId);
        $data['user'] = $user;

        return $this->render('backend/user_stock/index.html.twig', $data);
    }

    /**
     * @Route("/user/stockExport", name="user_stock_export", methods="GET")
     * @param UserRepository $userRepository
     * @param Request $request
     * @return Response
     */
    public function export(UserRepository $userRepository, Request $request): Response
    {
        /**
         * @var User[] $user
         */
        $exportData = $userRepository->findBy(['userLevel' => UserLevel::PARTNER]);

        $csvData = new ArrayCollection();
        $csvData->add([]);
        $csvData->add(['合伙人ID', '合伙人姓名', '合伙人电话', '变现等级', '剩余名额', '推荐人ID', '推荐人姓名', '推荐人电话']);

        foreach ($exportData as $user) {
            $parentUserId = '';
            $parentUserName = '';
            $parentUserPhone = '';

            if ($user->getParentUser()) {
                $parentUserId = $user->getParentUser()->getId();
                $parentUserName = $user->getParentUser()->getName();
                $parentUserPhone = $user->getParentUser()->getPhone();
            }

            $line = [
                $user->getId(),
                $user->getName(),
                $user->getPhone(),
                $user->getBianxianUserLevelText(),
                $user->getRecommandStock(),
                $parentUserId,
                $parentUserName,
                $parentUserPhone,
            ];

            $csvData->add($line);
        }

        $callBack = function () use ($csvData) {
            $csv = fopen('php://output', 'w+');
            //This line is important:
            fwrite($csv,"\xEF\xBB\xBF");
            while (false !== ($line = $csvData->next())) {
                fputcsv($csv, $line, ',');
            }
            fclose($csv);
        };

        return new StreamedResponse($callBack, 200, [
            'Content-Type' => 'text/csv; charset=gbk',
            'Content-Disposition' => 'attachment; filename="合伙人名额_'.date("Ymd_His").'.csv"'
        ]);
    }

}
