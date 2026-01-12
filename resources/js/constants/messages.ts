export const HTTP_STATUS_MESSAGES: Record<number, { title: string; message: string; icon?: string }> = {
    400: {
        title: 'Bad Request',
        message: 'The request could not be understood by the server due to malformed syntax.',
        icon: 'AlertCircle',
    },
    401: {
        title: 'Unauthorized',
        message: 'You need to be logged in to access this resource.',
        icon: 'Lock',
    },
    403: {
        title: 'Forbidden',
        message: "You don't have permission to access this resource.",
        icon: 'ShieldX',
    },
    404: {
        title: 'Page Not Found',
        message: "The page you're looking for doesn't exist or has been moved.",
        icon: 'FileQuestion',
    },
    405: {
        title: 'Method Not Allowed',
        message: 'The request method is not supported for this resource.',
        icon: 'Ban',
    },
    408: {
        title: 'Request Timeout',
        message: 'The server timed out waiting for the request.',
        icon: 'Clock',
    },
    419: {
        title: 'Session Expired',
        message: 'Your session has expired. Please refresh and try again.',
        icon: 'TimerOff',
    },
    422: {
        title: 'Validation Error',
        message: 'The submitted data contains invalid values.',
        icon: 'AlertTriangle',
    },
    429: {
        title: 'Too Many Requests',
        message: "You've made too many requests. Please wait a moment and try again.",
        icon: 'Gauge',
    },
    500: {
        title: 'Server Error',
        message: 'Something went wrong on our end. Please try again later.',
        icon: 'ServerCrash',
    },
    502: {
        title: 'Bad Gateway',
        message: 'The server received an invalid response. Please try again later.',
        icon: 'CloudOff',
    },
    503: {
        title: 'Service Unavailable',
        message: 'The service is temporarily unavailable. Please try again later.',
        icon: 'Construction',
    },
    504: {
        title: 'Gateway Timeout',
        message: 'The server took too long to respond. Please try again.',
        icon: 'Clock',
    },
};

export const VALIDATION_MESSAGES = {
    required: (field: string) => `${field} is required.`,
    email: (field: string) => `${field} must be a valid email address.`,
    min: (field: string, min: number) => `${field} must be at least ${min} characters.`,
    max: (field: string, max: number) => `${field} must not exceed ${max} characters.`,
    minValue: (field: string, min: number) => `${field} must be at least ${min}.`,
    maxValue: (field: string, max: number) => `${field} must not exceed ${max}.`,
    between: (field: string, min: number, max: number) => `${field} must be between ${min} and ${max}.`,
    confirmed: (field: string) => `${field} confirmation does not match.`,
    alpha: (field: string) => `${field} may only contain letters.`,
    alphaNum: (field: string) => `${field} may only contain letters and numbers.`,
    alphaNumDash: (field: string) => `${field} may only contain letters, numbers, dashes, and underscores.`,
    numeric: (field: string) => `${field} must be a number.`,
    integer: (field: string) => `${field} must be an integer.`,
    url: (field: string) => `${field} must be a valid URL.`,
    regex: (field: string) => `${field} format is invalid.`,
    unique: (field: string) => `This ${field.toLowerCase()} is already taken.`,
    exists: (field: string) => `The selected ${field.toLowerCase()} is invalid.`,
    date: (field: string) => `${field} must be a valid date.`,
    dateAfter: (field: string, date: string) => `${field} must be after ${date}.`,
    dateBefore: (field: string, date: string) => `${field} must be before ${date}.`,
    in: (field: string, values: string[]) => `${field} must be one of: ${values.join(', ')}.`,
    notIn: (field: string, values: string[]) => `${field} must not be one of: ${values.join(', ')}.`,
    file: (field: string) => `${field} must be a file.`,
    image: (field: string) => `${field} must be an image.`,
    mimes: (field: string, types: string[]) => `${field} must be a file of type: ${types.join(', ')}.`,
    maxFileSize: (field: string, size: string) => `${field} must not exceed ${size}.`,
    phone: (field: string) => `${field} must be a valid phone number.`,
    password: () => 'Password must contain at least one uppercase letter, one lowercase letter, one number, and one special character.',
    passwordWeak: () => 'Password is too weak. Please choose a stronger password.',
};

export const FORM_MESSAGES = {
    success: {
        created: (entity: string) => `${entity} has been created successfully.`,
        updated: (entity: string) => `${entity} has been updated successfully.`,
        deleted: (entity: string) => `${entity} has been deleted successfully.`,
        saved: (entity: string) => `${entity} has been saved successfully.`,
    },
    error: {
        create: (entity: string) => `Failed to create ${entity.toLowerCase()}. Please try again.`,
        update: (entity: string) => `Failed to update ${entity.toLowerCase()}. Please try again.`,
        delete: (entity: string) => `Failed to delete ${entity.toLowerCase()}. Please try again.`,
        save: (entity: string) => `Failed to save ${entity.toLowerCase()}. Please try again.`,
        load: (entity: string) => `Failed to load ${entity.toLowerCase()}. Please try again.`,
        generic: 'Something went wrong. Please try again.',
    },
    confirm: {
        delete: (entity: string) => `Are you sure you want to delete this ${entity.toLowerCase()}? This action cannot be undone.`,
        unsavedChanges: 'You have unsaved changes. Are you sure you want to leave?',
        logout: 'Are you sure you want to log out?',
    },
};

export function getHttpErrorMessage(status: number): { title: string; message: string; icon?: string } {
    return (
        HTTP_STATUS_MESSAGES[status] || {
            title: 'Error',
            message: 'An unexpected error occurred. Please try again.',
            icon: 'AlertCircle',
        }
    );
}
