<?php

namespace Webit\UserLogBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * BatchActivityLogRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class BatchActivityLogRepository extends EntityRepository
{
    /**
     * 
     * @param \Application\Sonata\UserBundle\Entity\User $fos_usr
     * @param string $module
     * @param int $operation_type
     * @param int $records_count
     * @param string $ip
     * @return \Webit\UserLogBundle\Entity\BatchActivityLog
     */
    public function createLog( $fos_usr, $module, $operation_type, $records_count, $ip=null)
    {
        $batch_log = new BatchActivityLog();

        $batch_log->setFosUser($fos_usr);
        $batch_log->setModule($module);
        $batch_log->setOperationType($operation_type);
        $batch_log->setRecordCount($records_count);
        $batch_log->setIpAddress($ip);
        $batch_log->setTime(new \DateTime());

        $em = $this->getEntityManager();
        $em->persist($batch_log);
        $em->flush();

        return $batch_log;
    }
        
}
