<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * UpgradeUserOrderRewards
 *
 * @ORM\Table(name="upgrade_user_order_rewards")
 * @ORM\Entity
 */
class UpgradeUserOrderRewards
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="upgrade_user_order_id", type="integer", nullable=false)
     */
    private $upgradeUserOrderId;

    /**
     * @var int
     *
     * @ORM\Column(name="recommander_user_id", type="integer", nullable=false)
     */
    private $recommanderUserId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="teacher_user_id", type="integer", nullable=true)
     */
    private $teacherUserId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="old_teacher_user_id", type="integer", nullable=true)
     */
    private $oldTeacherUserId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="rewards", type="decimal", precision=10, scale=2, nullable=true)
     */
    private $rewards;

    /**
     * @var int
     *
     * @ORM\Column(name="created_at", type="integer", nullable=false)
     */
    private $createdAt;


}
