<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 10:36
 */

namespace App\Controller\Backend;

use App\Controller\DefaultController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BackendController extends DefaultController
{
    protected $menus = [
        [
            'path' => 'backendIndex',
            'name' => '后台首页',
            'icon' => 'fa fa-home',
            'active' => false,
            'role' => 'ROLE_USER'
        ],
        [
            'path' => 'user_index',
            'name' => '用户中心',
            'icon' => 'fa fa-users',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'user_index',
                        'user_new',
                        'user_edit',
                        'user_personal_edit',
                    ],
                    'name' => '用户管理',
                    'icon' => 'fa fa-user',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'user_account_order_statistic',
                        'user_account_order_index',
                        'user_account_order_new',
                        'user_account_order_edit',
                    ],
                    'name' => '账户管理',
                    'icon' => 'fa fa-rmb',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'share_source_index',
                        'share_source_info',
                    ],
                    'name' => '用户分享',
                    'icon' => 'fa fa-share-alt',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'user_stock_statistic',
                        'user_stock_index',
                        'user_recommand_stock_order_new'
                    ],
                    'name' => '用户名额',
                    'icon' => 'fa fa-paper-plane',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'user_activity_index',
                    ],
                    'name' => '用户行为',
                    'icon' => 'fa fa-area-chart',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'course_index',
            'name' => '课程中心',
            'icon' => 'fa fa-book',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'course_index',
                        'course_new',
                        'course_edit',
                    ],
                    'name' => '课程管理',
                    'icon' => 'fa fa-calendar',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'teacher_index',
                        'teacher_new',
                        'teacher_edit',
                    ],
                    'name' => '讲师管理',
                    'icon' => 'fa fa-user',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'course_student_statistic_index',
                        'course_student_index',
                        'course_student_new',
                        'course_student_edit',
                    ],
                    'name' => '注册管理',
                    'icon' => 'fa fa-calendar-check-o',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'course_review_index',
                        'course_review_edit',
                    ],
                    'name' => '课程评价',
                    'icon' => 'fa fa-comments',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'product_index',
            'name' => '产品中心',
            'icon' => 'fa fa-product-hunt',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'product_index',
                        'product_new',
                        'product_edit',
                    ],
                    'name' => '产品管理',
                    'icon' => 'fa fa-cube',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'product_review_index',
                        'product_review_edit',
                    ],
                    'name' => '产品评价',
                    'icon' => 'fa fa-comments',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'group_order_index',
            'name' => '订单中心',
            'icon' => 'fa fa-shopping-bag',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'group_order_index',
                        'group_order_info',
                        'group_order_edit'
                    ],
                    'name' => '集call订单',
                    'icon' => 'fa fa-gift',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'upgrade_user_order_index',
                        'upgrade_user_order_edit',
                        'upgrade_user_order_payment_new',
                    ],
                    'name' => '会员升级',
                    'icon' => 'fa fa-level-up',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'group_user_order_index',
                        'group_user_order_info',
                        'group_user_order_edit',
                    ],
                    'name' => '产品订单',
                    'icon' => 'fa fa-shopping-cart',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'user_account_withdraw_orders',
                    ],
                    'name' => '提现订单',
                    'icon' => 'fa fa-money',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'project_meta_index',
            'name' => '配置中心',
            'icon' => 'fa fa-cogs',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'project_text_meta_index',
                        'project_text_meta_edit',
                    ],
                    'name' => '文案配置',
                    'icon' => 'fa fa-file-text',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'project_share_meta_index',
                        'project_share_meta_edit',
                    ],
                    'name' => '分享配置',
                    'icon' => 'fa fa-share-alt',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'project_banner_meta_index',
                        'project_banner_meta_edit',
                    ],
                    'name' => '横幅配置',
                    'icon' => 'fa fa-image',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
    ];

    /**
     * @Route("/", name="backendIndex", methods="GET")
     */
    public function indexAction()
    {
        $data = ['title' => '仪表盘', 'intro' => ''];

        return $this->render('backend/index.html.twig', $data);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function sidebar(Request $request)
    {
        $menus = $this->menus;
        for ($i = 0; $i < count($menus); $i++) {
            $menu = $menus[$i];

            if (is_array($menu['path'])) {
                $menus[$i]['path'] = $menu['path'][0]; // NOTE: 默认设置数组第一个
                if (in_array($request->attributes->get('_route'), $menu['path'])) {
                    $menus[$i]['active'] = true;
                }
            } else if (is_string($menu['path']) && $menu['path'] == $request->attributes->get('_route')) {
                $menus[$i]['active'] = true;
            }

            if (isset($menu['subMenus'])) {
                foreach ($menu['subMenus'] as $index => $subMenu) {
                    if (isset($subMenu['subMenus'])) {
                        foreach ($subMenu['subMenus'] as $j => $sMenu) {
                            if (is_array($sMenu['path'])) {
                                $menus[$i]['subMenus'][$index]['path'] = $subMenu['path'][0]; // NOTE: 默认设置数组第一个
                                $menus[$i]['subMenus'][$index]['subMenus'][$j]['path'] = $sMenu['path'][0]; // NOTE: 默认设置数组第一个
                                if (in_array($request->attributes->get('_route'), $sMenu['path'])) {
                                    $menus[$i]['subMenus'][$index]['subMenus'][$j]['active'] = true;
                                    $menus[$i]['subMenus'][$index]['active'] = true;
                                    $menus[$i]['active'] = true;
                                }
                            } else if (is_string($sMenu['path']) && $sMenu['path'] == $request->attributes->get('_route')) {
                                $menus[$i]['subMenus'][$index]['subMenus'][$j]['active'] = true;
                                $menus[$i]['subMenus'][$index]['active'] = true;
                                $menus[$i]['active'] = true;
                            }
                        }
                    } else {
                        if (is_array($subMenu['path'])) {
                            $menus[$i]['subMenus'][$index]['path'] = $subMenu['path'][0]; // NOTE: 默认设置数组第一个
                            if (in_array($request->attributes->get('_route'), $subMenu['path'])) {
                                $menus[$i]['subMenus'][$index]['active'] = true;
                                $menus[$i]['active'] = true;
                            }
                        } else if (is_string($subMenu['path']) && $subMenu['path'] == $request->attributes->get('_route')) {
                            $menus[$i]['subMenus'][$index]['active'] = true;
                            $menus[$i]['active'] = true;
                        }
                    }
                }
            }
        }

        return $this->render('backend/sidebar.html.twig', ['menus' => $menus]);
    }

    /**
     * @return Response
     */
    public function header()
    {
        return $this->render('backend/header.html.twig');
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function breadcrumb(Request $request)
    {
        $breadcrumbs = [];

        for ($i = 0; $i < count($this->menus); $i++) {
            $menu = $this->menus[$i];
            if (isset($menu['subMenus'])) {
                foreach ($menu['subMenus'] as $index => $subMenu) {
                    if (isset($subMenu['subMenus'])) {
                        foreach ($subMenu['subMenus'] as $j => $sMenu) {
                            if ($sMenu['path'] == $request->attributes->get('_route')
                                || (is_array($sMenu['path']) && in_array($request->attributes->get('_route'), $sMenu['path']))) {
                                if (is_array($subMenu['path'])) {
                                    $this->menus[$i]['subMenus'][$index]['path'] = $subMenu['path'][0];
                                }
                                $this->menus[$i]['subMenus'][$index]['subMenus'][$j]['path'] = $request->attributes->get('_route');
                                $this->menus[$i]['subMenus'][$index]['subMenus'][$j]['params'] = $request->attributes->get('_route_params');
                                $breadcrumbs[] = $this->menus[$i];
                                $breadcrumbs[] = $this->menus[$i]['subMenus'][$index];
                                $breadcrumbs[] = $this->menus[$i]['subMenus'][$index]['subMenus'][$j];
                            }
                        }
                    } elseif ($subMenu['path'] == $request->attributes->get('_route')
                    || (is_array($subMenu['path']) && in_array($request->attributes->get('_route'), $subMenu['path']))) {
                        $this->menus[$i]['subMenus'][$index]['path'] = $request->attributes->get('_route');
                        $this->menus[$i]['subMenus'][$index]['params'] = $request->attributes->get('_route_params');
                        $breadcrumbs[] = $this->menus[$i];
                        $breadcrumbs[] = $this->menus[$i]['subMenus'][$index];
                    }
                }
            } elseif (strpos($request->getUri(), $this->generateUrl($menu['path'])) !== false && $menu['path'] !== 'backendIndex') {
                $breadcrumbs[] = $this->menus[$i];
            }
        }

        return $this->render('backend/breadcrumb.html.twig', ['breadcrumbs' => $breadcrumbs]);
    }
}