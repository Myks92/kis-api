<?php

declare(strict_types=1);

namespace Api\Controller\Profile;

use Api\ReadModel\User\UserFetcher;
use Myks92\User\Model\User\Entity\User\Network;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ShowController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/profile",
     *     tags={"Profile"},
     *     summary="Show profile",
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="networks", type="array", @OA\Items(
     *                 type="object",
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="identity", type="string"),
     *             ))
     *         )
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/profile", name="profile", methods={"GET"})
     * @param UserFetcher $users
     *
     * @return Response
     */
    public function show(UserFetcher $users): Response
    {
        $user = $users->get($this->getUser()->getId());

        return $this->json([
            'id' => $user->getId()->getValue(),
            'first_name' => $user->getName()->getFirst(),
            'last_name' => $user->getName()->getLast(),
            'email' => $user->getEmail()->getValue(),
            'networks' => array_map(static function (Network $network): array {
                return [
                    'name' => $network->getNetwork(),
                    'identity' => $network->getIdentity(),
                ];
            }, $user->getNetworks())
        ], 200);
    }
}
