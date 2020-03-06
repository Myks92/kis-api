<?php

declare(strict_types=1);

namespace Api\Controller\Users;

use Api\Annotation\Guid;
use Api\Exception\ValidationException;
use Api\ReadModel\User\Filter;
use Api\ReadModel\User\UserFetcher;
use Api\Service\PaginationSerializer;
use Exception;
use Myks92\User\Model\User\Command\Activate;
use Myks92\User\Model\User\Command\Block;
use Myks92\User\Model\User\Command\ChangeRole;
use Myks92\User\Model\User\Command\Create;
use Myks92\User\Model\User\Command\Edit;
use Myks92\User\Model\User\Command\Remove;
use Myks92\User\Model\User\Entity\User\User;
use OpenApi\Annotations as OA;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Exception\ExceptionInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * @Route("/users", name="users")
 * @IsGranted("ROLE_MANAGE_USERS")
 */
class UsersController extends AbstractController
{
    private const PER_PAGE = 50;

    /**
     * @var DenormalizerInterface
     */
    private DenormalizerInterface $denormalizer;
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
     * @param DenormalizerInterface $denormalizer
     * @param SerializerInterface $serializer
     * @param ValidatorInterface $validator
     * @param TranslatorInterface $translator
     */
    public function __construct(
        DenormalizerInterface $denormalizer,
        SerializerInterface $serializer,
        ValidatorInterface $validator,
        TranslatorInterface $translator
    ) {
        $this->denormalizer = $denormalizer;
        $this->serializer = $serializer;
        $this->validator = $validator;
        $this->translator = $translator;
    }

    /**
     * @OA\Get(
     *     path="/users",
     *     tags={"Users"},
     *     summary="Get user list",
     *     @OA\Parameter(
     *         name="filter[name]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         style="form"
     *     ),
     *     @OA\Parameter(
     *         name="filter[email]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         style="form"
     *     ),
     *     @OA\Parameter(
     *         name="filter[status]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         style="form"
     *     ),
     *     @OA\Parameter(
     *         name="filter[role]",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         style="form"
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="items", type="array", @OA\Items(
     *                 type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="date", type="array", @OA\Items(
     *                 type="string",
     *                 @OA\Property(property="id", type="string"),
     *                 @OA\Property(property="name", type="string"),
     *                 @OA\Property(property="status", type="string"),
     *             )),),
     *             )),
     *             @OA\Property(property="pagination", ref="#/components/schemas/Pagination"),
     *         )
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("", name="", methods={"GET"})
     *
     * @param Request $request
     * @param UserFetcher $fetcher
     *
     * @return Response
     * @throws ExceptionInterface
     */
    public function index(Request $request, UserFetcher $fetcher): Response
    {
        $filter = new Filter\Filter();

        /** @var Filter\Filter $filter */
        $filter = $this->denormalizer->denormalize($request->query->get('filter', []), Filter\Filter::class, 'json', [
            'object_to_populate' => $filter
        ]);

        $pagination = $fetcher->all(
            $filter,
            $request->query->getInt('page', 1),
            self::PER_PAGE,
            $request->query->get('sort', 'date'),
            $request->query->get('direction', 'desc')
        );

        return $this->json([
            'items' => array_map(static function (array $item) {
                return [
                    'id' => $item['id'],
                    'first_name' => $item['name_first'],
                    'last_name' => $item['name_last'],
                    'email' => $item['email'],
                    'role' => $item['role'],
                    'status' => $item['status'],
                    'date' => $item['date'],
                ];
                }, (array)$pagination->getItems()
            ), 'pagination' => PaginationSerializer::toArray($pagination),
        ]);
    }

    /**
     * @OA\Post(
     *     path="/users/create",
     *     tags={"Users"},
     *     summary="Create user",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "first_name", "last_name"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Created"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/create", name=".create", methods={"POST"})
     * @param Request $request
     * @param Create\Handler $handler
     *
     * @return Response
     * @throws Exception
     */
    public function create(Request $request, Create\Handler $handler): Response
    {
        /** @var Create\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Create\Command::class, 'json');

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([], 201);
    }

    /**
     * @OA\Put(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Edit user by id",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"email", "first_name", "last_name"},
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response"
     *     ),
     *     @OA\Response(
     *        response=403,
     *         description="Forbidden edit yourself"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/{id}", name=".edit", methods={"PUT"}, requirements={"id"=Guid::PATTERN})
     * @param User $user
     * @param Request $request
     * @param Edit\Handler $handler
     *
     * @return Response
     * @throws ValidationException
     */
    public function edit(User $user, Request $request, Edit\Handler $handler): Response
    {
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('Unable to edit yourself.', [],'error-access')
            );
        }

        /** @var Edit\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), Edit\Command::class, 'json', [
            'object_to_populate' => Edit\Command::fromUser($user),
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
     *     path="/users/{id}/change-role",
     *     tags={"Users"},
     *     summary="Change role user by id",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"role"},
     *             @OA\Property(property="role", type="string", enum={"ROLE_USER", "ROLE_ADMIN"}),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *        response=403,
     *         description="Forbidden role change for self"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors validations",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/{id}/change-role", name=".change-role", methods={"PUT"}, requirements={"id"=Guid::PATTERN})
     * @param User $user
     * @param Request $request
     * @param ChangeRole\Handler $handler
     *
     * @return Response
     * @throws ValidationException
     */
    public function changeRole(User $user, Request $request, ChangeRole\Handler $handler): Response
    {
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('Unable to change role for yourself.', [], 'error-access')
            );
        }

        /** @var ChangeRole\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), ChangeRole\Command::class, 'json', [
            'object_to_populate' => ChangeRole\Command::fromUser($user),
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
     *     path="/users/{id}/activate",
     *     tags={"Users"},
     *     summary="Activate user by id",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/{id}/activate", name=".activate", methods={"PUT"}, requirements={"id"=Guid::PATTERN})
     * @param User $user
     * @param Request $request
     * @param Activate\Handler $handler
     *
     * @return Response
     */
    public function activate(User $user, Request $request, Activate\Handler $handler): Response
    {
        $command = new Activate\Command($user->getId()->getValue());

        $handler->handle($command);

        return $this->json([]);
    }

    /**
     * @OA\Put(
     *     path="/users/{id}/block",
     *     tags={"Users"},
     *     summary="Block user by id",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *        response=403,
     *         description="Forbidden block yourself"
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/{id}/block", name=".block", methods={"PUT"}, requirements={"id"=Guid::PATTERN})
     * @param User $user
     * @param Request $request
     * @param Block\Handler $handler
     *
     * @return Response
     */
    public function block(User $user, Request $request, Block\Handler $handler): Response
    {
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('Unable to block yourself.', [], 'error-access')
            );
        }

        $command = new Block\Command($user->getId()->getValue());

        $handler->handle($command);

        return $this->json([]);
    }

    /**
     * @OA\Delete(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Remove user by id",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response"
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Errors",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorModel")
     *     ),
     *     @OA\Response(
     *        response=403,
     *         description="Forbidden remove yourself"
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/{id}", name=".remove", methods={"DELETE"}, requirements={"id"=Guid::PATTERN})
     * @param User $user
     * @param Request $request
     * @param Remove\Handler $handler
     *
     * @return Response
     */
    public function remove(User $user, Request $request, Remove\Handler $handler): Response
    {
        if ($user->getId()->getValue() === $this->getUser()->getId()) {
            throw $this->createAccessDeniedException(
                $this->translator->trans('Unable to remove yourself.', [], 'error-access')
            );
        }

        $command = new Remove\Command($user->getId()->getValue());

        $handler->handle($command);

        return $this->json([]);
    }

    /**
     * @OA\Get(
     *     path="/users/{id}",
     *     tags={"Users"},
     *     summary="Getting user by id",
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string", format="uuid")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(property="id", type="integer"),
     *             @OA\Property(property="first_name", type="string"),
     *             @OA\Property(property="last_name", type="string"),
     *             @OA\Property(property="email", type="string"),
     *             @OA\Property(property="role", type="string"),
     *             @OA\Property(property="date", type="string"),
     *         )
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/{id}", name=".show", methods={"GET"}, requirements={"id"=Guid::PATTERN})
     * @param User $user
     *
     * @return Response
     */
    public function show(User $user): Response
    {
        return $this->json([
            'id' => $user->getId()->getValue(),
            'first_name' => $user->getName()->getFirst(),
            'last_name' => $user->getName()->getLast(),
            'email' => $user->getEmail()->getValue(),
            'role' => $user->getRole()->getName(),
            'status' => $user->getStatus()->getName(),
            'date' => $user->getDate()->format(DATE_ATOM),
        ], 200);
    }
}
