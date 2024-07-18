<?php

declare(strict_types=1);

namespace App\Validator;

use App\Document\Event;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

final class ConstraintValidProgramValidator extends ConstraintValidator
{
	public function validate($value, Constraint $constraint)
	 {
        if (!$constraint instanceof ConstraintValidProgram) {
            throw new UnexpectedTypeException($constraint, ConstraintValidProgram::class);
        }

        if (!$value instanceof Event) {
            throw new UnexpectedValueException($value, Event::class);
        }

        // Ensure no overlapping speech times
        $speeches = $value->getProgram();
        $times = [];

        foreach ($speeches as $speech) {
            $startTime = $speech->getStartTime();
            $endTime = $speech->getEndTime();

            foreach ($times as $time) {
                if (($startTime >= $time['start'] && $startTime < $time['end']) ||
                    ($endTime > $time['start'] && $endTime <= $time['end']) ||
                    ($startTime <= $time['start'] && $endTime >= $time['end'])) {
                    $this->context
                        ->buildViolation($constraint->overlappingSpeechesMessage)
                        ->atPath('program')
                        ->addViolation();
                    return;
                }
            }

            $times[] = ['start' => $startTime, 'end' => $endTime];
        }
    }
}
