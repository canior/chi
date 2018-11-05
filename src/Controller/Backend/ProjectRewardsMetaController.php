<?php

namespace App\Controller\Backend;

use App\Command\Product\UpdateProductRewardsCommand;
use App\Entity\ProjectRewardsMeta;
use App\Form\ProjectRewardsMetaType;
use App\Repository\ProductRepository;
use App\Repository\ProjectRewardsMetaRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class ProjectRewardsMetaController extends BackendController
{
    /**
     * @Route("/project/rewards/meta/", name="project_rewards_meta_index", methods="GET|POST")
     */
    public function index(ProjectRewardsMetaRepository $projectRewardsMetaRepository, Request $request): Response
    {
        $projectRewardsMeta = $projectRewardsMetaRepository->findOneBy(['metaKey' => ProjectRewardsMeta::PROJECT_REWARDS]);
        if (empty($projectRewardsMeta)) {
            throw $this->createNotFoundException('ProjectRewardsMeta ' . ProjectRewardsMeta::PROJECT_REWARDS . ' not found!');
        }

        $form = $this->createForm(ProjectRewardsMetaType::class, $projectRewardsMeta);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('notice', '修改成功');
            return $this->redirectToRoute('project_rewards_meta_index');
        }

        $data = [
            'title' => '返现配置',
            'projectRewardsMeta' => $projectRewardsMeta,
            'form' => $form->createView(),
        ];

        return $this->render('backend/project_rewards_meta/index.html.twig', $data);
    }

    /**
     * @Route("/project/rewards/meta/sync", name="project_rewards_meta_sync", methods="GET")
     */
    public function sync(ProductRepository $productRepository, Request $request): Response
    {
        $limit = 100;
        $offset = 0;
        while (true) {
            $products = $productRepository->findBy([], ['id' => 'DESC'], $limit, $offset);
            if (count($products) == 0) {
                break;
            }
            foreach ($products as $product) {
                try {
                    $productRewardsCommand = new UpdateProductRewardsCommand($product->getId());
                    $this->getCommandBus()->handle($productRewardsCommand);
                } catch (\Exception $e) {
                    $this->getLog()->error('can not run UpdateProductRewardsCommand because of' . $e->getMessage());
                    if ($this->isDev()) {
                        dump($e->getFile());
                        dump($e->getMessage());
                        die;
                    }
                    return new Response('页面错误', 500);
                }
            }
            $offset += $limit;
        }
        $this->addFlash('notice', '更新成功');
        return $this->redirectToRoute('project_rewards_meta_index');
    }
}
