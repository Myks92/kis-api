<?php

declare(strict_types=1);

namespace Api\Controller\Profile;

use Api\Annotation\Guid;
use Api\Exception\ValidationException;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Myks92\User\Model\User\Command\ChangeEmail;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ChangeEmailController extends AbstractController
{
    /**
     * @var SerializerInterface
     */
    private SerializerInterface $serializer;
    /**
     * @var ValidatorInterface
     */
    private ValidatorInterface $validator;

    /**
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     */
    public function __construct(SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
    }

    /**
     * @OA\Put(
     *      path="/profile/change-email",
     *      tags={"Profile"},
     *      summary="Request change email",
     *      @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              required={"email"},
     *              @OA\Property(property="email", type="string"),
     *          ),
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Errors",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Errors validation",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *      ),
     *      security={{"oauth2": {"common"}}}
     * )
     * @Route("/profile/change-email", name="profile.change-email", methods={"PUT"})
     * @param Request $request
     * @param ChangeEmail\Request\Handler $handler
     *
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function request(Request $request, ChangeEmail\Request\Handler $handler): Response
    {
        /** @var ChangeEmail\Request\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), ChangeEmail\Request\Command::class, 'json', [
            'object_to_populate' => new ChangeEmail\Request\Command($this->getUser()->getId()),
            'ignored_attributes' => ['id'],
        ]);

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([]);
    }

    /**
     * @OA\Put(
     *      path="/profile/change-email/{token}",
     *      tags={"Profile"},
     *      summary="Сonfirmation change email by token",
     *      @OA\Parameter(
     *         in="path",
     *         name="token",
     *         required=true,
     *         @OA\Schema(type="uuid")
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Errors",
     *          @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *      ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors validations",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *      security={{"oauth2": {"common"}}}
     * )
     * @Route(
     *     "/profile/change-email/{token}",
     *      name="profile.change-email.confirm",
     *      methods={"PUT"},
     *     requirements={"token"=Guid::PATTERN}
 *     )
     * @param string $token
     * @param ChangeEmail\Confirm\Handler $handler
     *
     * @return Response
     * @throws Exception
     */
    public function confirm(string $token, ChangeEmail\Confirm\Handler $handler): Response
    {
        $command = new ChangeEmail\Confirm\Command($this->getUser()->getId(), $token);

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([]);
    }
}
