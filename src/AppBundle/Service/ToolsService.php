<?php

namespace AppBundle\Service;

use AppBundle\Entity\Configuration;
use Doctrine\ORM\EntityManager;

class ToolsService
{


    /**
     * @var EntityManager
     */
    private $em;

    public function __construct(EntityManager $em)
    {
        $this->em = $em;
    }


    public function updateFrCalendar()
    {
        $confs = $this->em
            ->getRepository("AppBundle:Configuration")
            ->createQueryBuilder("c")
            ->innerJoin("c.mosque", "m", "c.mosque_id = m.id")
            ->where("c.sourceCalcul = 'calendar'")
            ->andWhere("m.country LIKE '%france%'")
            ->getQuery()
            ->getResult();

        /**
         * @var $conf Configuration
         */
        foreach ($confs as $conf) {
            $cal = $conf->getCalendar();
            if (!empty($cal) && is_array($cal)) {
                for ($month = 3; $month <= 9; $month++) {
                    for ($day = 1; $day <= count($cal[$month]); $day++) {
                        for ($prayer = 1; $prayer <= count($cal[$month][$day]); $prayer++) {
                            if (!empty($cal[$month][$day][$prayer])) {
                                $cal[$month][$day][$prayer] = $this->removeOneHour($cal[$month][$day][$prayer]);
                            }
                        }
                    }
                }


                $conf->setCalendar($cal);
                $this->em->persist($conf);
            }
        }

        $this->em->flush();

    }

    private function removeOneHour($time)
    {
        try {
            $date = new \DateTime("2018-03-01 $time:00");
            $date->sub(new \DateInterval('PT1H'));
            return $date->format("H:i");
        } catch (\Exception $e) {

        }
        return $time;
    }
}