<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-10-18
 * Time: 12:04 PM
 */

namespace App\Controller\Api;

use App\Entity\Product;
use App\Repository\ProductRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/wxapi")
 */
class ProductController extends BaseController
{

    /**
     * @Route("/product/", name="productIndex", methods="GET")
     * @param Request $request
     * @param ProductRepository $productRepository
     * @return Response
     */
    public function indexAction(Request $request, ProductRepository $productRepository) : Response {

    }


    /**
     * @Route("/product/{id}", name="productDetail", methods="GET")
     * @param Request $request
     * @param Product $product
     * @return Response
     */
    public function detailAction(Request $request, Product $product): Response {

    }

}