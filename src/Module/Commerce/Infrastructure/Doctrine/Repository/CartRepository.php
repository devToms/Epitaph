<?php

namespace App\Module\Commerce\Infrastructure\Doctrine\Repository;

use DateTimeImmutable;
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
        return $this->find($id);
    }


    public function findByIdWithFind(string $id): ?Cart
    {
        return $this->registry->getRepository(Cart::class)->find($id); 
    }

    public function softDelete(Cart $cart): bool
    {
        return (bool) $this->createQueryBuilder('c')
            ->update()
            ->set('p.deletedAt', ':deletedAt')
            ->where('c.id = :id')
            ->setParameter('deletedAt', new DateTimeImmutable())
            ->setParameter('id', $cart->getId())
            ->getQuery()
            ->execute();
    }
}

