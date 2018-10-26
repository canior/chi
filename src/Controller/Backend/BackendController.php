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
            'name' => '用户管理',
            'icon' => 'fa fa-users',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'user_index',
                    ],
                    'name' => '注册用户',
                    'icon' => 'fa fa-user',
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
                        'user_rewards',
                        'user_rewards_info',
                    ],
                    'name' => '用户收益',
                    'icon' => 'fa fa-rmb',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'product_index',
            'name' => '产品管理',
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
                    'name' => '产品数据',
                    'icon' => 'fa fa-database',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        '_twig_error_test',
                    ],
                    'params' => ['code' => 404],
                    'name' => 'TODO:产品销售',
                    'icon' => 'fa fa-dollar',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'product_review_index',
                        'product_review_info',
                        'product_review_new',
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
            'name' => '拼团管理',
            'icon' => 'fa fa-shopping-bag',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'group_order_index',
                        'group_order_info',
                    ],
                    'name' => '拼团订单',
                    'icon' => 'fa fa-list-ol',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        'group_user_order_index',
                        'group_user_order_info',
                    ],
                    'name' => '用户订单',
                    'icon' => 'fa fa-list-ul',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'backendIndex',
            'name' => '运营分析',
            'icon' => 'fa fa-line-chart',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        '_twig_error_test',
                    ],
                    'params' => ['code' => 404],
                    'name' => 'TODO:用户行为',
                    'icon' => 'fa fa-area-chart',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        '_twig_error_test',
                    ],
                    'params' => ['code' => 404],
                    'name' => 'TODO:用户画像',
                    'icon' => 'fa fa-image',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        '_twig_error_test',
                    ],
                    'params' => ['code' => 404],
                    'name' => 'TODO:用户分享',
                    'icon' => 'fa fa-share-alt-square',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
            ]
        ],
        [
            'path' => 'project_meta_index',
            'name' => '项目配置',
            'icon' => 'fa fa-cogs',
            'active' => false,
            'role' => 'ROLE_ADMIN',
            'subMenus' => [
                [
                    'path' => [
                        'project_meta_index',
                        'project_meta_new',
                        'project_meta_edit'
                    ],
                    'name' => '小程序配置',
                    'icon' => 'fa fa-cog',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
                [
                    'path' => [
                        '_twig_error_test',
                    ],
                    'params' => ['code' => 404],
                    'name' => 'TODO:分享设置',
                    'icon' => 'fa fa-object-group',
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
        $data = ['title' => 'Dashboard', 'intro' => ''];

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