<?php

$translations = getContextValue('languageTranslations') ?? [];

return [
    'is_unique'           => $translations['form_validation_is_unique'] ?? 'The {field} field must contain a unique value.',
    'matches'             => $translations['form_validation_matches'] ?? 'The {field} field does not match the {param} field.',
    'max_length'          => $translations['form_validation_max_length'] ?? 'The {field} field cannot exceed {param} characters in length.',
    'min_length'          => $translations['form_validation_min_length'] ?? 'The {field} field must be at least {param} characters in length.',
    'required'            => $translations['form_validation_required'] ?? 'The {field} field is required.',
    'valid_email'         => $translations['form_validation_valid_email'] ?? 'The {field} field must contain a valid email address.',
    'alpha_numeric_space' => $translations['form_validation_alpha_numeric_space'] ?? 'The {field} field may only contain alphanumeric and space characters.',
    'alpha_dash'          => $translations['form_validation_alpha_dash'] ?? 'The {field} field may only contain alphanumeric, underscore, and dash characters.',
];