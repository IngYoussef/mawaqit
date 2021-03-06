<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Mosque;
use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Sortable\Entity\Repository\SortableRepository;

/**
 * MessageRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageRepository extends SortableRepository
{

    /**
     * @param Mosque $mosque
     * @param boolean $desktop include or not desktop tagged message
     * @param boolean $mobile include or not mobile tagged message
     * @return array
     */
    function getMessagesByMosque(Mosque $mosque, $desktop = null, $mobile = null)
    {
        $qb = $this->createQueryBuilder("mes")
            ->select("mes.id, mes.title, mes.content, mes.image")
            ->innerJoin("mes.mosque", "mos", Join::WITH, "mes.mosque = :mosqueId")
            ->where("mes.enabled = 1")
            ->andWhere("mes.content IS NOT NULL OR mes.image is NOT NULL")
            ->andWhere("mes.content IS NOT NULL OR mes.image is NOT NULL")
            ->orderBy("mes.position", "ASC")
            ->setParameter(":mosqueId", $mosque->getId());

        if ($mobile !== null) {
            $qb->andWhere("mes.mobile = :mobile")
                ->setParameter(":mobile", $mobile);
        }
        if ($desktop !== null) {
            $qb->andWhere("mes.desktop = :desktop")
                ->setParameter(":desktop", $desktop);
        }

        return $qb->getQuery()->getResult();
    }
}
