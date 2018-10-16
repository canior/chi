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
            'path' => 'project_meta_index',
            'name' => '系统管理',
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
                    'name' => '系统设置',
                    'icon' => 'fa fa-cog',
                    'active' => false,
                    'role' => 'ROLE_ADMIN',
                ],
//                [
//                    'path' => [
//                        '_twig_error_test',
//                    ],
//                    'params' => ['code' => 404],
//                    'name' => 'TODO:拼团设置',
//                    'icon' => 'fa fa-object-group',
//                    'active' => false,
//                    'role' => 'ROLE_ADMIN',
//                ],
            ]
        ],
        [
            'path' => 'backendIndex',
            'name' => '客户管理',
            'icon' => 'glyphicons glyphicons-user',
            'active' => false,
            'role' => ['ROLE_CUSTOMER_SERVICE', 'ROLE_AGENT'],
            'subMenus' => [
                [
                    'path' => [
                        'backendUserList',
                        'backendUserAdd',
                        'backendUserEdit',
                        'backendUserDetail'
                    ],
                    'name' => '客户列表',
                    'icon' => 'glyphicons glyphicons-pencil',
                    'active' => false,
                    'role' => 'ROLE_CUSTOMER_SERVICE',
                ],
                [
                    'path' => 'backendUserLoginList',
                    'name' => '登陆日志',
                    'icon' => 'glyphicons glyphicons-log_book',
                    'active' => false,
                    'role' => 'ROLE_CUSTOMER_SERVICE',
                ]
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