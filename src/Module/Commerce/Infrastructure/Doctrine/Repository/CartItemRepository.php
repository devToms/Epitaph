<?php

namespace App\Module\Commerce\Infrastructure\Doctrine\Repository;

use App\Module\Commerce\Domain\Entity\Cart;
use App\Module\Commerce\Domain\Entity\CartItem; // Poprawna przestrzeń nazw
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Module\Commerce\Domain\Repository\CartItemRepositoryInterface;

/**
 * @extends ServiceEntityRepository<CartItem>
 */
class CartItemRepository extends ServiceEntityRepository implements CartItemRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CartItem::class);
    }

    public function save(CartItem $cartItem, bool $flush = false): void
    {
        $this->getEntityManager()->persist($cartItem);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findByCartId(string $cartId): ?CartItem
    {
        return $this->createQueryBuilder('ci')
            ->where('ci.cart_id = :cartId') 
            ->setParameter('cartId', $cartId)
            ->getQuery()
            ->getOneOrNullResult();
    }

    
    public function findByCart(Cart $cart): array
    {
        return $this->createQueryBuilder('ci')
            ->where('ci.cart = :cart')
            ->setParameter('cart', $cart)
            ->getQuery()
            ->getResult();
    }
}

