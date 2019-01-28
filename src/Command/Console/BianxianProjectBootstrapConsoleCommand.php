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
        $homeBanner2 = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_home_2']);
        $homeBanner3 = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_home_3']);
        $loginBanner = $projectBannerMetaRepository->findOneBy(['metaKey' => 'banner_login']);


        //创建用户分享
        $referUserShareMeta = new ProjectShareMeta(ShareSource::REFER_USER);
        $referUserShareMeta->setShareMeta(ShareSource::$types[ShareSource::REFER_USER], ShareSource::$types[ShareSource::REFER], '邀请您来加入变现商学院', null, true);
        $this->entityManager->persist($referUserShareMeta);

        $quanUserShareMeta = new ProjectShareMeta(ShareSource::QUAN_USER);
        $quanUserShareMeta->setShareMeta(ShareSource::$types[ShareSource::QUAN_USER], ShareSource::$types[ShareSource::QUAN], null, null, true);
        $this->entityManager->persist($quanUserShareMeta);

        //创建产品分享
        $referCourseShareMeta = new ProjectShareMeta(ShareSource::REFER_PRODUCT);
        $referCourseShareMeta->setShareMeta(ShareSource::$types[ShareSource::REFER_PRODUCT], ShareSource::$types[ShareSource::REFER], '邀请您来学习课程', null, false);
        $this->entityManager->persist($referCourseShareMeta);

        $quanCourseShareMeta = new ProjectShareMeta(ShareSource::QUAN_PRODUCT);
        $quanCourseShareMeta->setShareMeta(ShareSource::$types[ShareSource::QUAN_PRODUCT], ShareSource::$types[ShareSource::QUAN], null, null, false);
        $this->entityManager->persist($quanCourseShareMeta);


        //创建团购分享
        $referGroupOrderShareMeta = new ProjectShareMeta(ShareSource::REFER_GROUP_ORDER);
        $referGroupOrderShareMeta->setShareMeta(ShareSource::$types[ShareSource::REFER_GROUP_ORDER], ShareSource::$types[ShareSource::REFER], '邀请您来集call', null, false);
        $this->entityManager->persist($referGroupOrderShareMeta);

        $quanGroupOrderShareMeta = new ProjectShareMeta(ShareSource::QUAN_GROUP_ORDER);
        $quanGroupOrderShareMeta->setShareMeta(ShareSource::$types[ShareSource::QUAN_GROUP_ORDER], ShareSource::$types[ShareSource::QUAN], null, null, false);
        $this->entityManager->persist($quanGroupOrderShareMeta);

        $this->entityManager->flush();



    }

}