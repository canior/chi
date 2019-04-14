<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-04-14
 * Time: 9:31 AM
 */

namespace App\Controller\Backend;


use App\Entity\GroupUserOrder;
use App\Repository\GroupUserOrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\CourseOrder;
use App\Form\EditGroupUserOrderType;
use App\Form\GroupUserOrderType;
use App\Form\VerifyParentUserType;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OfflineCourseGroupUserOrderController extends BackendController
{
    /**
     * @Route("/offlineCourse/order/", name="offline_course_order_index", methods="GET")
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param Request $request
     * @return Response
     */
    public function index(GroupUserOrderRepository $groupUserOrderRepository, Request $request): Response
    {
        $data = [
            'title' => '活动订单',
            'form' => [
                'groupUserOrderId' => $request->query->getInt('groupUserOrderId', null),
                'userId' => $request->query->getInt('userId', null),
                'productName' => $request->query->get('productName', null),
                'status' => $request->query->get('status', null),
                'paymentStatus' => $request->query->get('paymentStatus', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'statuses' => GroupUserOrder::$courseStatuses,
            'paymentStatuses' => GroupUserOrder::$paymentStatuses,
        ];
        $data['data'] = $groupUserOrderRepository->findOfflineCourseOrders($data['form']['groupUserOrderId'], $data['form']['userId'], $data['form']['productName'], $data['form']['status'], $data['form']['paymentStatus']);
        $data['pagination'] = $this->getPaginator()->paginate($data['data'], $data['form']['page'], self::PAGE_LIMIT);
        return $this->render('backend/offline_course_order/index.html.twig', $data);
    }

    /**
     * @Route("/offlineCourse/order/{id}/edit", name="offline_course_order_edit", methods="GET|POST")
     * @param Request $request
     * @param CourseOrder $groupUserOrder
     * @return Response
     */
    public function edit(Request $request, CourseOrder $groupUserOrder): Response
    {
        $form = $this->createForm(EditGroupUserOrderType::class, $groupUserOrder);
        $form->get('status')->setData(array_search($groupUserOrder->getCourseStatusText(), GroupUserOrder::$courseStatuses));
        $form->get('paymentStatus')->setData(array_search($groupUserOrder->getPaymentStatusText(), GroupUserOrder::$paymentStatuses));
        $form->handleRequest($request);

        $user = $groupUserOrder->getUser();
        $verifyParentForm = $this->createForm(VerifyParentUserType::class, $user);
        $verifyParentForm->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $groupUserOrder->setStatus($form->get('status')->getData());
            $groupUserOrder->setPaymentStatus($form->get('paymentStatus')->getData());
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('offline_course_order_edit', ['id' => $groupUserOrder->getId()]);
        }

        if ($verifyParentForm->isSubmitted() && $verifyParentForm->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('offline_course_order_edit', ['id' => $groupUserOrder->getId()]);
        }

        return $this->render('backend/offline_course_order/edit.html.twig', [
            'group_user_order' => $groupUserOrder,
            'title' => '编辑活动订单',
            'form' => $form->createView(),
            'verifyParentForm' => $verifyParentForm->createView(),
            'user' => $user
        ]);
    }

    /**
     * @Route("/offlineCourse/order/export", name="offline_course_order_export", methods="GET")
     * @param GroupUserOrderRepository $groupUserOrderRepository
     * @param Request $request
     * @return Response
     */
    public function export(GroupUserOrderRepository $groupUserOrderRepository, Request $request): Response
    {
        $data = [
            'title' => '活动订单',
            'form' => [
                'groupUserOrderId' => $request->query->getInt('groupUserOrderId', null),
                'userId' => $request->query->getInt('userId', null),
                'productName' => $request->query->get('productName', null),
                'status' => $request->query->get('status', null),
                'paymentStatus' => $request->query->get('paymentStatus', null),
                'page' => $request->query->getInt('page', 1)
            ],
            'statuses' => GroupUserOrder::$courseStatuses,
            'paymentStatuses' => GroupUserOrder::$paymentStatuses,
        ];

        /**
         * @var GroupUserOrder[] $exportData
         */
        $exportData = $groupUserOrderRepository->findOfflineCourseOrders($data['form']['groupUserOrderId'], $data['form']['userId'], $data['form']['productName'], $data['form']['status'], $data['form']['paymentStatus'])->getQuery()->getResult();

        $csvData = new ArrayCollection();
        $csvData->add([]);
        $csvData->add(['订单号','创建时间', '课程ID', '课程', '科目', '用户ID', '用户姓名', '用户电话', '用户等级', '推荐人ID', '推荐人姓名', '推荐人电话', '会务费', '支付状态']);

        foreach ($exportData as $groupUserOrder) {
            $parentUserId = '';
            $parentUserName = '';
            $parentUserPhone = '';

            if ($groupUserOrder->getUser()->getParentUser()) {
                $parentUserId = $groupUserOrder->getUser()->getParentUser()->getId();
                $parentUserName = $groupUserOrder->getUser()->getParentUser()->getName();
                $parentUserPhone = $groupUserOrder->getUser()->getParentUser()->getPhone();
            }

            $line = [
                $groupUserOrder->getId(),
                $groupUserOrder->getCreatedAtDateFormatted(),
                $groupUserOrder->getProduct()->getId(),
                $groupUserOrder->getProduct()->getTitle(),
                $groupUserOrder->getProduct()->getCourse()->getSubjectText(),
                $groupUserOrder->getUser()->getId(),
                $groupUserOrder->getUser()->getName(),
                $groupUserOrder->getUser()->getPhone(),
                $groupUserOrder->getUser()->getBianxianUserLevelText(),
                $parentUserId,
                $parentUserName,
                $parentUserPhone,
                $groupUserOrder->getTotal(),
                $groupUserOrder->getPaymentStatusText()
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
            'Content-Disposition' => 'attachment; filename="活动订单_'.date("Ymd_His").'.csv"'
        ]);
    }

}
