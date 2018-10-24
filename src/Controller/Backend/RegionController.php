<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-10-24
 * Time: 10:00
 */

namespace App\Controller\Backend;

use App\Entity\Region;
use App\Repository\RegionRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/backend")
 */
class RegionController extends BackendController
{
    /**
     * @Route("/region/{regionId}/{selectedId}", name="backend_region")
     * @param int $regionId
     * @param int $selectedId
     * @return JsonResponse
     */
    public function region($regionId = null, $selectedId = null, RegionRepository $regionRepository)
    {
        $data = [];
        $regionId = $regionId ? $regionId : null;
        /**
         * @var Region[] $provinces
         */
        $provinces = $regionRepository->findBy(['parentRegion' => $regionId]);
        foreach ($provinces as $province) {
            $selected = ($selectedId == $province->getId()) ? 'selected' : null;
            $data[] = [
                'id' => $province->getId(),
                'name' => $province->getName(),
                'selected' => $selected,
            ];
        }
        return $this->json(['region' => $data]);
    }
}