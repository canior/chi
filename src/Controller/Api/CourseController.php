<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2019-01-14
 * Time: 12:36 AM
 */

namespace App\Controller\Api;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class CourseController extends ProductController
{
    public function indexAction(Request $request, ProductRepository $productRepository, ProjectBannerMetaRepository $projectBannerMetaRepository) : Response {

    }
}