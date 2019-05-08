<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
    * @return User[] Returns an array of User objects
    */

    public function findAllWithEmailKey()
    {
        return $this->createQueryBuilder('u', 'u.email')
            ->orderBy('u.id', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function findAllCommentators(){
        return $this->createQueryBuilder('u')
                ->innerJoin(\App\Entity\Comment::class, 'c', 'u.id = c.author')
                ->orderBy('u.id', 'ASC')
                ->getQuery()
                ->getResult()
            ;
    }

}
