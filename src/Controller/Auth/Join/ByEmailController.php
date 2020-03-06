<?php

declare(strict_types=1);

namespace Api\Controller\Auth\Join;

use Api\Annotation\Guid;
use Api\Exception\ValidationException;
use Api\ReadModel\User\UserFetcher;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Exception;
use Myks92\User\Model\User\Command\JoinByEmail;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use function count;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ByEmailController extends AbstractController
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
     * @var UserFetcher
     */
    private UserFetcher $users;
    /**
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param UserFetcher $users
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        UserFetcher $users,
        TranslatorInterface $translator
    )
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->users = $users;
        $this->translator = $translator;
    }

    /**
     * @OA\Post(
     *     path="/auth/join-by-email",
     *     tags={"Auth"},
     *     summary="Join by email",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"first_name", "last_name", "email", "password"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="password", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors validation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     * )
     * @Route("/auth/join-by-email", name="auth.join-by-email", methods={"POST"})
     * @param Request $request
     * @param JoinByEmail\Request\Handler $handler
     *
     * @return Response
     * @throws NoResultException
     * @throws NonUniqueResultException
     * @throws ValidationException
     */
    public function request(Request $request, JoinByEmail\Request\Handler $handler): Response
    {
        /** @var JoinByEmail\Request\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), JoinByEmail\Request\Command::class, 'json');

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([], 201);
    }

    /**
     * @OA\Put(
     *     path="/auth/join-by-email/{token}",
     *     tags={"Auth"},
     *     summary="Confirmation join by token",
     *    @OA\Parameter(
     *         in="path",
     *         name="token",
     *         required=true,
     *         @OA\Schema(type="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors validation",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/auth/join-by-email/{token}", name="auth.join-by-email.confirm", methods={"PUT"}, requirements={"id"=Guid::PATTERN})
     * @param Request $request
     * @param string $token
     * @param JoinByEmail\Confirm\Handler $handler
     *
     * @return Response
     * @throws Exception
     */
    public function confirm(Request $request, string $token, JoinByEmail\Confirm\Handler $handler): Response
    {
        if (!$user = $this->users->findBySignUpConfirmToken($token)) {
            $this->createAccessDeniedException(
                $this->translator->trans('Incorrect or already confirmed token.', [],'error-access')
            );
        }

        $command = new JoinByEmail\Confirm\Command($token);

        $handler->handle($command);

        return $this->json([]);
    }
}
