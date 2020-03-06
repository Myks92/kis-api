<?php
declare(strict_types=1);


namespace Api\Exception;


use Exception;
use Symfony\Component\Validator\ConstraintViolationListInterface;

class ValidationException extends Exception
{
    /**
     * @var ConstraintViolationListInterface
     */
    private ConstraintViolationListInterface $violations;

    public function __construct(ConstraintViolationListInterface $violations)
    {
        $this->violations = $violations;
        parent::__construct('Validation errors.');
    }

    /**
     * @return ConstraintViolationListInterface
     */
    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }
}