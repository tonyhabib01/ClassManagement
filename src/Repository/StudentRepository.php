<?php

namespace App\Repository;

use App\Entity\Classe;
use App\Entity\Student;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\Query\ResultSetMappingBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method Student|null find($id, $lockMode = null, $lockVersion = null)
 * @method Student|null findOneBy(array $criteria, array $orderBy = null)
 * @method Student[]    findAll()
 * @method Student[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class StudentRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Student::class);
    }

    public function paginate($currentPage = 1, $limit){
        $em = $this->getEntityManager();

        $qb = $em->createQueryBuilder();

        $qb->select('s')
            ->from('App:Student', 's')
            ->getQuery();
        $paginator = new Paginator($qb);
        $paginator->setUseOutputWalkers(false);
        $paginator->getQuery()
            ->setFirstResult($limit * ($currentPage - 1))
            ->setMaxResults($limit);

        return $paginator;


    }

//    public function filter($name) {
////        $conn = $this->getEntityManager()->getConnection();
////
////
////        $sql = "
////            SELECT * FROM student
////            INNER JOIN classe ON student.classe_id = classe.id
////            INNER JOIN course_classe ON classe.id = course_classe.classe_id
////            INNER JOIN course ON course_classe.course_id = course.id
////            WHERE course.name = :name
////        ";
////
////        $stmt = $conn->prepare($sql);
////        $stmt->execute(['name' => $name]);
////
////        return $stmt->fetchAll();
//
////        $em = $this->getEntityManager();
////
////        $rsm = new ResultSetMappingBuilder($em);
////        $rsm->addEntityResult('App:Student','s');
////        $rsm->addFieldResult('s','id','id');
////        $rsm->addFieldResult('s','first_name','firstName');
////        $rsm->addFieldResult('s','last_name','lastName');
////        $rsm->addFieldResult('s','date_of_birth','dateOfBirth');
////        $rsm->addFieldResult('s','gpa','gpa');
////        $rsm->addJoinedEntityResult('App:Classe','classe','u','classe');
////        $rsm->addFieldResult('id')
////
////        $query = $em->createNativeQuery("
////        SELECT * FROM student
////        INNER JOIN classe ON student.classe_id = classe.id
////        INNER JOIN course_classe ON classe.id = course_classe.classe_id
////        INNER JOIN course ON course_classe.course_id = course.id
////        WHERE course.name = :name
////        ", $rsm);
////        $query->setParameter('name',$name);
////
////        return $query->getResult();
//
////        $em = $this->getEntityManager();
////        $qb = $em->createQueryBuilder();
////        $qb->select('s')
////            ->from('App:Student','s')
////            ->innerJoin('s.classe','cl','WITH','s.classe = cl.id')
////            ->innerJoin('cl.courses','co','WITH','cl.courses = co.classe')
////            ->where("co.name = :name");
////
////        $qb->setParameter('name',$name);
////
////        return $qb->getQuery()->getResult();
//
//    }

    // /**
    //  * @return Student[] Returns an array of Student objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('s.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Student
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
