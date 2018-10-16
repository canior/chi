<?php
/**
 * Created by PhpStorm.
 * User: Jimmy
 * Date: 2018-08-29
 * Time: 10:04
 */

namespace App\Service;

use App\DataAccess\DataAccess;
use App\Entity\ProjectMeta;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ProjectMetaHelper
{
    /**
     * @var DataAccess
     */
    private $dataAccess;

    /**
     * @var Container
     */
    private $container;

    /**
     * Meta constructor.
     * @param $dataAccess
     * @param $container
     */
    public function __construct(DataAccess $dataAccess, ContainerInterface $container)
    {
        $this->dataAccess = $dataAccess;
        $this->container = $container;
    }

    /**
     * Get dataAccess
     *
     * @return DataAccess
     */
    public function getDataAccess()
    {
        return $this->dataAccess;
    }

    /**
     * Get metaValue by key
     *
     * @param $key
     * @return null|string|array
     */
    public function getMetaValue($key)
    {
        /**
         * @var ProjectMeta $meta
         */
        $meta = $this->dataAccess->getDaoBy(ProjectMeta::class, ['metaKey' => $key]);
        return $meta ? $meta->getFormattedMetaValue() : null;
    }

    /**
     * Get metaValue or parameter by key
     *
     * @param $key
     * @return null|string
     */
    public function getMetaValueOrParameter($key)
    {
        /**
         * @var ProjectMeta $meta
         */
        $meta = $this->dataAccess->getDaoBy(ProjectMeta::class, ['metaKey' => $key]);
        if ($meta) {
            return $meta->getFormattedMetaValue();
        }
        return $this->container->getParameter($key);
    }

    /**
     * Is rush day
     *
     * @param int $timestamp
     * @return bool
     */
    public function isRushDay($timestamp = null)
    {
        /**
         * @var ProjectMeta $meta
         */
        $meta = $this->dataAccess->getDaoBy(ProjectMeta::class, ['metaKey' => ProjectMeta::RUSH_DAY]);
        return is_array($meta->getFormattedMetaValue()) ? in_array(date('N', $timestamp), $meta->getFormattedMetaValue()) : date('N', $timestamp) == $meta->getFormattedMetaValue();
    }

    /**
     * Get next rush day
     *
     * @param int $timestamp
     * @return bool
     */
    public function getNextRushDay($timestamp = null)
    {
        if (empty($timestamp)) {
            $timestamp = time();
        }
        while (!$this->isRushDay($timestamp)) {
            $timestamp += 60 * 60 * 24;
        }
        return $timestamp;
    }

    /**
     * Get next non rush day
     *
     * @param int $timestamp
     * @return bool
     */
    public function getNextNonRushDay($timestamp = null)
    {
        if (empty($timestamp)) {
            $timestamp = time();
        }
        while ($this->isRushDay($timestamp)) {
            $timestamp += 60 * 60 * 24;
        }
        return $timestamp;
    }
}