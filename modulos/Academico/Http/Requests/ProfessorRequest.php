<?php

namespace Modulos\Academico\Http\Requests;

use Modulos\Core\Http\Request\BaseRequest;

class ProfessorRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        $rules = [
            'prf_pes_id' => 'required|unique:acd_professores',
            'prf_codigo' => 'nullable|max:11'
        ];

        if ($this->method() == 'PUT') {
            $rules = [
                'prf_codigo' => 'integer|max:99999999999'
            ];
        }

        return $rules;
    }
}
