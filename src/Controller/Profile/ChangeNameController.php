<?php

declare(strict_types=1);

namespace Api\Controller\Profile;

use Api\Exception\ValidationException;
use Myks92\User\Model\User\Command\ChangeName;
use OpenApi\Annotations as OA;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function count;

/**
 * @author Maxim Vorozhtsov <myks1992@mail.ru>
 */
class ChangeNameController extends AbstractController
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
     *     path="/profile/change-name",
     *     tags={"Profile"},
     *     summary="Change name",
     *     @OA\RequestBody(
     *         @OA\JsonContent(
     *             type="object",
     *             required={"first", "last"},
     *             @OA\Property(property="first", type="string"),
     *             @OA\Property(property="last", type="string"),
     *         ),
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Errors validations",
     *         @OA\JsonContent(ref="#/components/schemas/ErrorValidation")
     *     ),
     *     security={{"oauth2": {"common"}}}
     * )
     * @Route("/profile/change-name", name="profile.change-name", methods={"PUT"})
     * @param Request $request
     * @param ChangeName\Handler $handler
     *
     * @return Response
     * @throws ValidationException
     */
    public function changeName(Request $request, ChangeName\Handler $handler): Response
    {
        /** @var ChangeName\Command $command */
        $command = $this->serializer->deserialize($request->getContent(), ChangeName\Command::class, 'json', [
            'object_to_populate' => new ChangeName\Command($this->getUser()->getId()),
            'ignored_attributes' => ['id'],
        ]);

        if (count($violations = $this->validator->validate($command))) {
            throw new ValidationException($violations);
        }

        $handler->handle($command);

        return $this->json([]);
    }
}