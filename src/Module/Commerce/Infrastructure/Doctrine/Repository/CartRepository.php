<?php

namespace App\Module\Commerce\Infrastructure\Doctrine\Repository;

use App\Module\Commerce\Domain\Entity\Cart;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Module\Commerce\Domain\Repository\CartRepositoryInterface;

/**
 * @extends ServiceEntityRepository<Cart>
 */
class CartRepository extends ServiceEntityRepository implements CartRepositoryInterface
{
    private ManagerRegistry $registry;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Cart::class);
        $this->registry = $registry; 
    }

    public function save(Cart $cart, bool $flush = false): void
    {
        $this->getEntityManager()->persist($cart);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function findById(string $id): ?Cart
    {
        return $this->createQueryBuilder('c')
            ->select('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findByIdWithFind(string $id): ?Cart
    {
        return $this->registry->getRepository(Cart::class)->find($id); 
    }
}

