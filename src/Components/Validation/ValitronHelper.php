<?php declare(strict_types=1);

namespace Romanzaycev\Fundamenta\Components\Validation;

use Valitron\Validator;

final class ValitronHelper
{
    public static function implodeValitronMessages(Validator | array $validator): string
    {
        $result = "";
        $errors = ($validator instanceof Validator) ? $validator->errors() : $validator;

        if (!empty($errors)) {
            $strings = [];

            foreach ($errors as $field => $errs) {
                $strings[] = $field . ": " . implode(", ", $errs);
            }

            $result = implode("; ", $strings);
        }

        return $result;
    }
}
