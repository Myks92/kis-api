<?php

declare(strict_types=1);

namespace Api\Controller;

use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API",
 *     description="HTTP JSON API",
 *     @OA\Contact(
 *         name="Maksim Vorozhtsov",
 *         email="myks1992@mail.ru",
 *     ),
 * ),
 * @OA\Server(
 *     url="/"
 * ),
 * @OA\SecurityScheme(
 *     type="oauth2",
 *     securityScheme="oauth2",
 *     @OA\Flow(
 *         flow="implicit",
 *         authorizationUrl="/authorize",
 *         scopes={
 *             "common": "Common"
 *         }
 *     )
 * ),
 * @OA\Schema(
 *     schema="ErrorModel",
 *     type="object",
 *     @OA\Property(property="error", type="object", nullable=true,
 *         @OA\Property(property="code", type="integer"),
 *         @OA\Property(property="message", type="string"),
 *     ),
 * ),
 * @OA\Schema(
 *     schema="ErrorValidation",
 *     type="object",
 *     @OA\Property(property="violations", type="array", nullable=true, @OA\Items(
 *         type="object",
 *         @OA\Property(property="propertyPath", type="string", description="The property path"),
 *         @OA\Property(property="title", type="string", description="The detail message error"),
 *     ))
 * ),
 * @OA\Schema(
 *     schema="Pagination",
 *     type="object",
 *     @OA\Property(property="count", type="integer", description="Count items"),
 *     @OA\Property(property="total", type="integer", description="Total item number available"),
 *     @OA\Property(property="per_page", type="integer", description="Number of items per page"),
 *     @OA\Property(property="page", type="integer", description="Currently used page number"),
 *     @OA\Property(property="pages", type="integer", description="Total pages"),
 * )
 */
class HomeController extends AbstractController
{
    /**
     * @OA\Get(
     *     path="/",
     *     tags={"API"},
     *     summary="API Home",
     *     @OA\Response(
     *         response="200",
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="name", type="string")
     *         )
     *     )
     * )
     * @Route("", name="home", methods={"GET"})
     * @return Response
     */
    public function home(): Response
    {
        return $this->json([
            'name' => 'API',
            'version' => '1.0',
        ]);
    }
}
