<?php

namespace App\Rules;

use Illuminate\Container\Container;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rules\Password;

class NeoPassword extends Password
{
    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->messages = [];

        $validator = Validator::make(
            $this->data,
            [$attribute => array_merge(['string', 'min:' . $this->min], $this->customRules)],
            $this->validator->customMessages,
            $this->validator->customAttributes
        )->after(function ($validator) use ($attribute, $value) {
            if (!is_string($value)) {
                return;
            }

            $value = (string) $value;

            if ($this->mixedCase && !preg_match('/(\p{Ll}+.*\p{Lu})|(\p{Lu}+.*\p{Ll})/u', $value)) {
                // $validator->errors()->add($attribute, 'The :attribute must contain at least one uppercase and one lowercase letter.');
                $validator->errors()->add($attribute, ':attribute harus mengandung setidaknya satu huruf besar dan satu huruf kecil.');
            }

            if ($this->letters && !preg_match('/\pL/u', $value)) {
                // $validator->errors()->add($attribute, 'The :attribute must contain at least one letter.');
                $validator->errors()->add($attribute, ':attribute harus mengandung setidaknya satu karakter huruf.');
            }

            if ($this->symbols && !preg_match('/\p{Z}|\p{S}|\p{P}/u', $value)) {
                // $validator->errors()->add($attribute, 'The :attribute must contain at least one symbol.');
                $validator->errors()->add($attribute, ':attribute harus mengandung setidaknya satu karakter simbol.');
            }

            if ($this->numbers && !preg_match('/\pN/u', $value)) {
                // $validator->errors()->add($attribute, 'The :attribute must contain at least one number.');
                $validator->errors()->add($attribute, ':attribute harus mengandung setidaknya satu angka.');
            }
        });

        if ($validator->fails()) {
            return $this->fail($validator->errors()->all());
        }

        if ($this->uncompromised && !Container::getInstance()->make(UncompromisedVerifier::class)->verify([
            'value' => $value,
            'threshold' => $this->compromisedThreshold,
        ])) {
            return $this->fail(
                // 'The given :attribute has appeared in a data leak. Please choose a different :attribute.'
                ':attribute yang diberikan telah muncul dalam kebocoran data. Silakan pilih yang :attribute lain.'
            );
        }

        return true;
    }
}
