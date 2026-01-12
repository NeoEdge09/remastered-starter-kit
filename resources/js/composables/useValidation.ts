import { VALIDATION_MESSAGES } from '@/constants/messages';
import { reactive, ref, type Ref } from 'vue';

 
export type ValidationRule<T = any> = (value: T, formData?: Record<string, unknown>) => string | true;

export interface FieldRules {
     
    [fieldName: string]: ValidationRule<any>[];
}

export interface ValidationErrors {
    [fieldName: string]: string | undefined;
}

export interface UseValidationOptions {
    /** Validate on input change */
    validateOnChange?: boolean;
    /** Validate on blur */
    validateOnBlur?: boolean;
    /** Custom field labels for error messages */
    fieldLabels?: Record<string, string>;
}

/**
 * Custom composable for form validation
 */
export function useValidation<T extends Record<string, unknown>>(formData: Ref<T> | T, rules: FieldRules, options: UseValidationOptions = {}) {
    const { validateOnChange = true, validateOnBlur = true, fieldLabels = {} } = options;

    const errors = reactive<ValidationErrors>({});
    const isValidating = ref(false);
    const hasBeenValidated = ref(false);

    /**
     * Get field label for error messages
     */
    const getFieldLabel = (fieldName: string): string => {
        if (fieldLabels[fieldName]) {
            return fieldLabels[fieldName];
        }
        // Convert camelCase/snake_case to Title Case
        return fieldName
            .replace(/([A-Z])/g, ' $1')
            .replace(/_/g, ' ')
            .replace(/^\w/, (c) => c.toUpperCase())
            .trim();
    };

    /**
     * Validate a single field
     */
    const validateField = (fieldName: string): boolean => {
        const fieldRules = rules[fieldName];
        if (!fieldRules) {
            errors[fieldName] = undefined;
            return true;
        }

        const data = ('value' in formData ? formData.value : formData) as T;
        const value = data[fieldName];

        for (const rule of fieldRules) {
            const result = rule(value, data as unknown as Record<string, unknown>);
            if (result !== true) {
                errors[fieldName] = result;
                return false;
            }
        }

        errors[fieldName] = undefined;
        return true;
    };

    /**
     * Validate all fields
     */
    const validate = (): boolean => {
        isValidating.value = true;
        hasBeenValidated.value = true;
        let isValid = true;

        for (const fieldName of Object.keys(rules)) {
            if (!validateField(fieldName)) {
                isValid = false;
            }
        }

        isValidating.value = false;
        return isValid;
    };

    /**
     * Clear all errors
     */
    const clearErrors = () => {
        Object.keys(errors).forEach((key) => {
            errors[key] = undefined;
        });
        hasBeenValidated.value = false;
    };

    /**
     * Clear error for a specific field
     */
    const clearFieldError = (fieldName: string) => {
        errors[fieldName] = undefined;
    };

    /**
     * Set error for a specific field (useful for server-side errors)
     */
    const setError = (fieldName: string, message: string) => {
        errors[fieldName] = message;
    };

    /**
     * Set multiple errors at once (useful for server-side errors)
     */
    const setErrors = (serverErrors: Record<string, string | string[]>) => {
        Object.entries(serverErrors).forEach(([field, message]) => {
            errors[field] = Array.isArray(message) ? message[0] : message;
        });
    };

    /**
     * Check if form has any errors
     */
    const hasErrors = (): boolean => {
        return Object.values(errors).some((error) => error !== undefined);
    };

    /**
     * Handler for input change event
     */
    const onFieldChange = (fieldName: string) => {
        if (validateOnChange && hasBeenValidated.value) {
            validateField(fieldName);
        }
    };

    /**
     * Handler for input blur event
     */
    const onFieldBlur = (fieldName: string) => {
        if (validateOnBlur) {
            validateField(fieldName);
        }
    };

    return {
        errors,
        isValidating,
        hasBeenValidated,
        validate,
        validateField,
        clearErrors,
        clearFieldError,
        setError,
        setErrors,
        hasErrors,
        onFieldChange,
        onFieldBlur,
        getFieldLabel,
    };
}

// ============================================
// Validation Rules Factory
// ============================================

/**
 * Required field validation
 */
export const required = (fieldLabel: string): ValidationRule => {
    return (value) => {
        if (value === null || value === undefined || value === '' || (Array.isArray(value) && value.length === 0)) {
            return VALIDATION_MESSAGES.required(fieldLabel);
        }
        return true;
    };
};

/**
 * Email validation
 */
export const email = (fieldLabel: string = 'Email'): ValidationRule<string> => {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return (value) => {
        if (!value) return true; // Let required handle empty
        if (!emailRegex.test(value)) {
            return VALIDATION_MESSAGES.email(fieldLabel);
        }
        return true;
    };
};

/**
 * Minimum length validation
 */
export const minLength = (fieldLabel: string, min: number): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        if (value.length < min) {
            return VALIDATION_MESSAGES.min(fieldLabel, min);
        }
        return true;
    };
};

/**
 * Maximum length validation
 */
export const maxLength = (fieldLabel: string, max: number): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        if (value.length > max) {
            return VALIDATION_MESSAGES.max(fieldLabel, max);
        }
        return true;
    };
};

/**
 * Minimum value validation (for numbers)
 */
export const minValue = (fieldLabel: string, min: number): ValidationRule<number> => {
    return (value) => {
        if (value === null || value === undefined) return true;
        if (value < min) {
            return VALIDATION_MESSAGES.minValue(fieldLabel, min);
        }
        return true;
    };
};

/**
 * Maximum value validation (for numbers)
 */
export const maxValue = (fieldLabel: string, max: number): ValidationRule<number> => {
    return (value) => {
        if (value === null || value === undefined) return true;
        if (value > max) {
            return VALIDATION_MESSAGES.maxValue(fieldLabel, max);
        }
        return true;
    };
};

/**
 * Confirmed field validation (e.g., password confirmation)
 */
export const confirmed = (fieldLabel: string, confirmFieldName: string): ValidationRule<string> => {
    return (value, formData) => {
        if (!value) return true;
        const confirmValue = formData?.[confirmFieldName];
        if (value !== confirmValue) {
            return VALIDATION_MESSAGES.confirmed(fieldLabel);
        }
        return true;
    };
};

/**
 * Numeric validation
 */
export const numeric = (fieldLabel: string): ValidationRule => {
    return (value) => {
        if (!value && value !== 0) return true;
        if (isNaN(Number(value))) {
            return VALIDATION_MESSAGES.numeric(fieldLabel);
        }
        return true;
    };
};

/**
 * Integer validation
 */
export const integer = (fieldLabel: string): ValidationRule => {
    return (value) => {
        if (!value && value !== 0) return true;
        if (!Number.isInteger(Number(value))) {
            return VALIDATION_MESSAGES.integer(fieldLabel);
        }
        return true;
    };
};

/**
 * URL validation
 */
export const url = (fieldLabel: string = 'URL'): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        try {
            new URL(value);
            return true;
        } catch {
            return VALIDATION_MESSAGES.url(fieldLabel);
        }
    };
};

/**
 * Regex pattern validation
 */
export const pattern = (fieldLabel: string, regex: RegExp): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        if (!regex.test(value)) {
            return VALIDATION_MESSAGES.regex(fieldLabel);
        }
        return true;
    };
};

/**
 * Alpha only validation (letters only)
 */
export const alpha = (fieldLabel: string): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        if (!/^[a-zA-Z]+$/.test(value)) {
            return VALIDATION_MESSAGES.alpha(fieldLabel);
        }
        return true;
    };
};

/**
 * Alphanumeric validation
 */
export const alphaNum = (fieldLabel: string): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        if (!/^[a-zA-Z0-9]+$/.test(value)) {
            return VALIDATION_MESSAGES.alphaNum(fieldLabel);
        }
        return true;
    };
};

/**
 * Alphanumeric with dash and underscore validation
 */
export const alphaNumDash = (fieldLabel: string): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        if (!/^[a-zA-Z0-9_-]+$/.test(value)) {
            return VALIDATION_MESSAGES.alphaNumDash(fieldLabel);
        }
        return true;
    };
};

/**
 * In array validation
 */
export const inArray = <T>(fieldLabel: string, allowedValues: T[]): ValidationRule<T> => {
    return (value) => {
        if (!value) return true;
        if (!allowedValues.includes(value)) {
            return VALIDATION_MESSAGES.in(fieldLabel, allowedValues.map(String));
        }
        return true;
    };
};

/**
 * Not in array validation
 */
export const notInArray = <T>(fieldLabel: string, disallowedValues: T[]): ValidationRule<T> => {
    return (value) => {
        if (!value) return true;
        if (disallowedValues.includes(value)) {
            return VALIDATION_MESSAGES.notIn(fieldLabel, disallowedValues.map(String));
        }
        return true;
    };
};

/**
 * Phone number validation (basic)
 */
export const phone = (fieldLabel: string = 'Phone'): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        // Basic phone validation - allows digits, spaces, dashes, parentheses, plus sign
        if (!/^[+]?[\d\s()-]{10,}$/.test(value.replace(/\s/g, ''))) {
            return VALIDATION_MESSAGES.phone(fieldLabel);
        }
        return true;
    };
};

/**
 * Strong password validation
 */
export const strongPassword = (): ValidationRule<string> => {
    return (value) => {
        if (!value) return true;
        // At least 8 characters, 1 uppercase, 1 lowercase, 1 number, 1 special character
        const hasUppercase = /[A-Z]/.test(value);
        const hasLowercase = /[a-z]/.test(value);
        const hasNumber = /\d/.test(value);
        const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(value);
        const hasMinLength = value.length >= 8;

        if (!hasUppercase || !hasLowercase || !hasNumber || !hasSpecial || !hasMinLength) {
            return VALIDATION_MESSAGES.password();
        }
        return true;
    };
};

/**
 * Array min items validation
 */
export const minItems = (fieldLabel: string, min: number): ValidationRule<unknown[]> => {
    return (value) => {
        if (!value) return true;
        if (value.length < min) {
            return `${fieldLabel} must have at least ${min} item${min > 1 ? 's' : ''}.`;
        }
        return true;
    };
};

/**
 * Array max items validation
 */
export const maxItems = (fieldLabel: string, max: number): ValidationRule<unknown[]> => {
    return (value) => {
        if (!value) return true;
        if (value.length > max) {
            return `${fieldLabel} must not have more than ${max} item${max > 1 ? 's' : ''}.`;
        }
        return true;
    };
};

/**
 * Custom validation rule
 */
export const custom = <T>(validator: (value: T, formData?: Record<string, unknown>) => boolean, message: string): ValidationRule<T> => {
    return (value, formData) => {
        if (!validator(value, formData)) {
            return message;
        }
        return true;
    };
};
