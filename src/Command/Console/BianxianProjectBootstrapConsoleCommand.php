<?php
/**
 * Created by PhpStorm.
 * User: tandy
 * Date: 2018-11-03
 * Time: 5:07 PM
 */

namespace App\Command\Console;


use App\Entity\ProjectShareMeta;
use App\Entity\ShareSource;
use Psr\Log\LoggerInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use App\Entity\ProjectBannerMeta;

class BianxianProjectBootstrapConsoleCommand extends ContainerAwareCommand
{
    /**
     * @var ObjectManager
     */
    private $entityManager;

    /**
     * @var LoggerInterface
     */
    private $log;

    public function __construct(ObjectManager $entityManager, LoggerInterface $log, string $name=null) {
        parent::__construct($name);
        $this->entityManager = $entityManager;
        $this->log = $log;
    }

    protected function configure() {
        $this->setName('app:bianxian-project-bootstrap');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output) {

        $projectBannerMetaRepository = $this->entityManager->getRepository(ProjectBannerMeta::class);
        $homeBanner1 = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_home_1']);
        if ($homeBanner1 == null) {
            $homeBanner1 = new ProjectBannerMeta("banner_home_1");
            $this->entityManager->persist($homeBanner1);
            $this->entityManager->flush();
        }

        $homeBanner2 = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_home_2']);
        if ($homeBanner2 == null) {
            $homeBanner2 = new ProjectBannerMeta("banner_home_2");
            $this->entityManager->persist($homeBanner2);
            $this->entityManager->flush();
        }

        $homeBanner3 = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_home_3']);
        if ($homeBanner3 == null) {
            $homeBanner3 = new ProjectBannerMeta("banner_home_3");
            $this->entityManager->persist($homeBanner3);
            $this->entityManager->flush();
        }

        $loginBanner = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_login']);
        if ($loginBanner == null) {
            $loginBanner = new ProjectBannerMeta("banner_login");
            $this->entityManager->persist($loginBanner);
            $this->entityManager->flush();
        }


        //创建用户分享
        $projectShareRepository = $this->entityManager->getRepository(ProjectShareMeta::class);

        if ($projectShareRepository->findOneBy(['metaKey' => ShareSource::REFER_USER]) == null) {
            $referUserShareMeta = new ProjectShareMeta(ShareSource::REFER_USER);
            $referUserShareMeta->setShareMeta(ShareSource::$types[ShareSource::REFER_USER], ShareSource::$types[ShareSource::REFER], '邀请您来加入变现商学院', null, true);
            $this->entityManager->persist($referUserShareMeta);
            $this->entityManager->flush();
        }

        if ($projectShareRepository->findOneBy(['metaKey' => ShareSource::QUAN_USER]) == null) {
            $quanUserShareMeta = new ProjectShareMeta(ShareSource::QUAN_USER);
            $quanUserShareMeta->setShareMeta(ShareSource::$types[ShareSource::QUAN_USER], ShareSource::$types[ShareSource::QUAN], null, null, true);
            $this->entityManager->persist($quanUserShareMeta);
            $this->entityManager->flush();
        }

        //创建产品分享
        if ($projectShareRepository->findOneBy(['metaKey' => ShareSource::REFER_PRODUCT]) == null) {
            $referCourseShareMeta = new ProjectShareMeta(ShareSource::REFER_PRODUCT);
            $referCourseShareMeta->setShareMeta(ShareSource::$types[ShareSource::REFER_PRODUCT], ShareSource::$types[ShareSource::REFER], '邀请您来学习课程', null, false);
            $this->entityManager->persist($referCourseShareMeta);
            $this->entityManager->flush();
        }

        if ($projectShareRepository->findOneBy(['metaKey' => ShareSource::QUAN_PRODUCT]) == null) {
            $quanCourseShareMeta = new ProjectShareMeta(ShareSource::QUAN_PRODUCT);
            $quanCourseShareMeta->setShareMeta(ShareSource::$types[ShareSource::QUAN_PRODUCT], ShareSource::$types[ShareSource::QUAN], null, null, false);
            $this->entityManager->persist($quanCourseShareMeta);
            $this->entityManager->flush();
        }


        //创建团购分享
        if ($projectShareRepository->findOneBy(['metaKey' => ShareSource::REFER_GROUP_ORDER]) == null) {
            $referGroupOrderShareMeta = new ProjectShareMeta(ShareSource::REFER_GROUP_ORDER);
            $referGroupOrderShareMeta->setShareMeta(ShareSource::$types[ShareSource::REFER_GROUP_ORDER], ShareSource::$types[ShareSource::REFER], '邀请您来集call', null, false);
            $this->entityManager->persist($referGroupOrderShareMeta);
            $this->entityManager->flush();
        }

        if ($projectShareRepository->findOneBy(['metaKey' => ShareSource::QUAN_GROUP_ORDER]) == null) {
            $quanGroupOrderShareMeta = new ProjectShareMeta(ShareSource::QUAN_GROUP_ORDER);
            $quanGroupOrderShareMeta->setShareMeta(ShareSource::$types[ShareSource::QUAN_GROUP_ORDER], ShareSource::$types[ShareSource::QUAN], null, null, false);
            $this->entityManager->persist($quanGroupOrderShareMeta);
            $this->entityManager->flush();
        }


    }

}