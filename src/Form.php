<?php

namespace Nio01\Validator;

use Nio01\Validator\Validator;


abstract class Form extends Validator
{
    public function __construct()
    {
        $rules = $this->rules();
        $messages = $this->messages();
        parent::__construct($rules, $messages);
        $this->make(array_merge($_REQUEST , $_FILES));
    }


    public function rules()
    {
        return [];
    }

    public function messages()
    {
        return [];
    }
}
