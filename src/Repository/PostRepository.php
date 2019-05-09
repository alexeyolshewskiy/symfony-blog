<?php

namespace App\Repository;

use App\Entity\Post;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Symfony\Bridge\Doctrine\RegistryInterface;

/**
 * @method Post|null find($id, $lockMode = null, $lockVersion = null)
 * @method Post|null findOneBy(array $criteria, array $orderBy = null)
 * @method Post[]    findAll()
 * @method Post[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PostRepository extends ServiceEntityRepository
{
    public function __construct(RegistryInterface $registry)
    {
        parent::__construct($registry, Post::class);
    }


    public function findAllWithTitleKey()
    {

        $result = $this->findAll();
        if(!empty($result)){
            $array = [];
            foreach ($result as $item){
                $array[$item->getId()][$item->getTitle()] = $item;
            }
            return $array;
        }
        return null;
    }

    public function getAllQuery(){
        return $this->createQueryBuilder('p')->orderBy('p.updated','DESC')->getQuery();
    }

}
