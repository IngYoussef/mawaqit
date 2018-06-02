<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Mosque;
use AppBundle\Entity\User;

/**
 * MosqueRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MosqueRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param User $user
     * @param array $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    function search(User $user, array $search)
    {
        $qb = $this->createQueryBuilder("m")
            ->leftJoin("m.user", "u", "m.user_id = u.id");

        if (!empty($search)) {
            if (!empty($search["word"])) {
                $qb->where("m.name LIKE :word "
                    . "OR m.associationName LIKE :word "
                    . "OR m.email LIKE :word "
                    . "OR m.address LIKE :word "
                    . "OR m.city LIKE :word "
                    . "OR m.zipcode LIKE :word "
                    . "OR u.username LIKE :word "
                    . "OR u.email LIKE :word"
                )->setParameter(":word", "%" . trim($search["word"]) . "%");
            }


            if (!empty($search["id"])) {
                $qb->andWhere("m.id = :id")
                    ->setParameter(":id", trim($search["id"]));
            }

            if (!empty($search["status"])) {
                $qb->andWhere("m.status = :status")
                    ->setParameter(":status", $search["status"]);
            }

            if (!empty($search["type"]) && $search["type"] !== 'ALL') {
                $qb->andWhere("m.type = :type")
                    ->setParameter(":type", $search["type"]);
            }

            if (!empty($search["department"])) {
                $qb->andWhere("m.zipcode LIKE :zipcode")
                    ->setParameter(":zipcode", trim($search["department"]) . "%");
            }

            if (!empty($search["country"])) {
                $qb->andWhere("m.country = :country")
                    ->setParameter(":country", $search["country"]);
            }
        }

        if (!empty($search["userId"])) {
            $qb->andWhere("m.user = :userId")
                ->setParameter(":userId", $search["userId"]);
        }

        // By default not show homes for admin user
        if (empty($search["userId"]) && $user->isAdmin() && empty($search["type"])) {
            $qb->andWhere("m.type = :type")
                ->setParameter(":type", "mosque");
        }

        if (!$user->isAdmin()) {
            $qb->andWhere("u.id = :userId")
                ->setParameter(":userId", $user->getId());
        }

        $qb->orderBy("m.created", "DESC");

        return $qb;
    }


    /**
     * @param string $search
     * @return \Doctrine\ORM\QueryBuilder
     */
    function publicSearch($search)
    {
        $qb = $this->createQueryBuilder("m");

        if (!empty($search)) {
            $qb->where("m.type = 'mosque'")
                ->andWhere("m.status = :status")
                ->andwhere("m.name LIKE :word "
                    . "OR m.associationName LIKE :word "
                    . "OR m.address LIKE :word "
                    . "OR m.city LIKE :word "
                    . "OR m.zipcode LIKE :word "
                    . "OR m.country LIKE :word "
                )->setParameter(":word", "%$search%")
                ->setParameter(':status', Mosque::STATUS_VALIDATED);
        }

        return $qb;
    }


    /**
     * get configured mosques
     * @param integer $nbMax
     * @return \Doctrine\ORM\QueryBuilder
     */
    private function getMosquesQuery($nbMax = null)
    {
        $qb = $this->createQueryBuilder("m");
        if (is_numeric($nbMax)) {
            $qb->setMaxResults($nbMax);
        }
        return $qb;
    }

    /**
     * get configured mosques with minimum one image set (image1)
     * @param integer $nbMax
     * @return array
     */
    function getMosquesWithImage($nbMax = null)
    {
        return $this->getMosquesQuery($nbMax)
            ->where("m.image1 IS NOT NULL")
            ->andWhere("m.type = 'mosque'")
            ->orderBy("m.id", "DESC")
            ->getQuery()
            ->getResult();
    }

    /**
     * set updated to now for all mosques
     */
    function forceUpdateAll()
    {
        $qb = $this->createQueryBuilder("m")
            ->update()
            ->set("m.updated", ":date")
            ->setParameter(":date", new \DateTime());
        $qb->getQuery()->execute();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    function getCount()
    {
        return $this->createQueryBuilder("m")
            ->select("count(m.id)")
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    function countMosques()
    {
        return $this->createQueryBuilder("m")
            ->select("count(m.id)")
            ->where("m.type = 'mosque'")
            ->getQuery()
            ->getSingleScalarResult();
    }

    /**
     * get mosques information for google map
     * @return array
     */
    function getAllMosquesForMap()
    {
        return $this->createQueryBuilder("m")
            ->leftJoin("m.configuration", "c", "m.id = c.mosque_id")
            ->select("m.slug, m.name, m.address, m.city, m.zipcode, m.country,  c.longitude as lng, c.latitude as lat")
            ->where("m.addOnMap = 1")
            ->andWhere("m.type = 'mosque'")
            ->andWhere("m.status = :status")
            ->andWhere("c.latitude is not null")
            ->andWhere("c.longitude is not null")
            ->setParameter(':status', Mosque::STATUS_VALIDATED)
            ->getQuery()
            ->getArrayResult();
    }


    /**
     * get mosques by country
     * @return array
     */
    function getNumberByCountry()
    {
        return $this->createQueryBuilder("m")
            ->select("count(m.id) as nb, m.country")
            ->where("m.status = :status")
            ->orderBy("nb", "DESC")
            ->groupBy("m.country")
            ->getQuery()
            ->setParameter(':status', Mosque::STATUS_VALIDATED)
            ->getResult();
    }

    /**
     * @return array
     */
    function getCitiesByCountry($country)
    {
        $cities =  $this->createQueryBuilder("m")
            ->select("m.city")
            ->where("m.country = :country")
            ->setParameter(':country', $country)
            ->getQuery()
            ->getScalarResult();

        return  $cities;
    }
}
