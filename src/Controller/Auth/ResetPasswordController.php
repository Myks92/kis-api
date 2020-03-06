<?php

declare(strict_types=1);

namespace Api\Controller\Auth;

use Api\Annotation\Guid;
use Api\Exception\ValidationException;
use Api\ReadModel\User\UserFetcher;
use Exception;
use Myks92\User\Model\User\Command\ResetPassword;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ResetPasswordController extends AbstractController
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
     * @var TranslatorInterface
     */
    private TranslatorInterface $translator;

    /**
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     */
    public function __construct(
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    )
    {
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * @OA\Put(
     *     path="/auth/reset-password/",
     *     tags={"Auth"},
     *     summary="Request reset password by email",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email"},
     *             @OA\Property(property="email", type="string"),
     *         ),
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
     *         description="Errors validations",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/auth/reset-password", name="auth.reset-password", methods={"PUT"})
     * @param Request $request
     * @param ResetPassword\Request\Handler $handler
     *
     * @return Response
     * @throws Exception
     */
    public function request(Request $request, ResetPassword\Request\Handler $handler): Response
    {
        /** @var ResetPassword\Request\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), ResetPassword\Request\Command::class, 'json');

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([]);
    }

    /**
     * @OA\Put(
     *     path="/auth/reset-password/{token}",
     *     tags={"Auth"},
     *     summary="Confirmation reset password by token",
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
     *         response=403,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors validations",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route(
     *     "/auth/reset-password/{token}",
     *     name="auth.reset-password.confirm",
     *     methods={"PUT"},
     *     requirements={"token"=Guid::PATTERN}
 *     )
     * @param string $token
     * @param Request $request
     * @param ResetPassword\Confirm\Handler $handler
     * @param UserFetcher $users
     *
     * @return Response
     * @throws Exception
     */
    public function confirm(string $token, Request $request, ResetPassword\Confirm\Handler $handler, UserFetcher $users): Response
    {
        if (!$users->existsByResetToken($token)) {
            $this->createAccessDeniedException(
                $this->translator->trans('Incorrect or already confirmed token.', [],'error-access')
            );
        }

        /** @var ResetPassword\Confirm\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), ResetPassword\Confirm\Command::class, 'json');

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([]);
    }
}
