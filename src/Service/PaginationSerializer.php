<?php

declare(strict_types=1);

namespace Api\Service;

use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class PaginationSerializer
{
    /**
     * @param PaginationInterface $pagination
     *
     * @return array
     */
    public static function toArray(PaginationInterface $pagination): array
    {
        return [
            'count' => $pagination->count(),
            'total' => $pagination->getTotalItemCount(),
            'per_page' => $pagination->getItemNumberPerPage(),
            'page' => $pagination->getCurrentPageNumber(),
            'pages' => ceil($pagination->getTotalItemCount() / $pagination->getItemNumberPerPage()),
        ];
    }
}
