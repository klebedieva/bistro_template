<?php

namespace App\Service;

use App\DTO\AddressFullValidationRequest;
use App\DTO\AddressValidationRequest;
use App\DTO\CartAddRequest;
use App\DTO\CartUpdateQuantityRequest;
use App\DTO\ContactCreateRequest;
use App\DTO\CouponValidateRequest;
use App\DTO\OrderCreateRequest;
use App\DTO\ReservationCreateRequest;
use App\DTO\ReviewCreateRequest;
use App\Service\InputSanitizer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Validation Helper Service
 *
 * Provides reusable methods for common validation operations.
 * Reduces code duplication across controllers.
 */
class ValidationHelper
{
    /**
     * Constructor for ValidationHelper
     *
     * Injects Symfony Serializer to enable automatic DTO mapping from array data.
     * The serializer handles type conversion automatically (e.g., string to int, string to float).
     *
     * @param SerializerInterface $serializer Symfony serializer for object deserialization
     */
    public function __construct(
        private SerializerInterface $serializer
    ) {}

    /**
     * Extract error messages from validation violations
     *
     * Converts Symfony ConstraintViolationList to a simple array of error messages.
     * Used by all controllers to format validation errors consistently.
     *
     * @param ConstraintViolationListInterface $violations Validation violations from Symfony Validator
     * @return array Array of error messages (strings)
     */
    public function extractViolationMessages(ConstraintViolationListInterface $violations): array
    {
        $errors = [];
        foreach ($violations as $violation) {
            $errors[] = $violation->getMessage();
        }
        return $errors;
    }

    /**
     * Map array data to DTO object using Symfony Serializer
     *
     * Simple approach for beginners - no Reflection API needed.
     * This method automatically maps array data to DTO properties and handles type conversion.
     * This eliminates repetitive manual mapping code (e.g., `isset($data['key']) ? (int)$data['key'] : null`)
     * across multiple controllers.
     *
     * The method performs the following steps:
     * 1. Uses Symfony Serializer to deserialize array to DTO (handles basic mapping)
     * 2. Converts numeric fields explicitly for known DTOs (simple switch/case approach)
     * 3. Trims all string properties automatically (if trimStrings=true)
     *
     * Supported DTOs with numeric field conversion:
     * - OrderCreateRequest: couponId (int), deliveryFee (float), discountAmount (float)
     * - ReviewCreateRequest: rating (int)
     * - CartUpdateQuantityRequest: quantity (int)
     * - CartAddRequest: itemId (int), quantity (int)
     * - CouponValidateRequest: orderAmount (float)
     * - ReservationCreateRequest: guests (int)
     * - ContactCreateRequest: consent (bool)
     *
     * When adding a new DTO with numeric fields, add a new case in convertNumericFields().
     *
     * @template T of object
     * @param array<string, mixed> $data Source data array (from JSON request body or form data)
     * @param class-string<T> $dtoClass DTO class name to instantiate (must be a fully qualified class name)
     * @param bool $trimStrings Whether to trim all public string properties (default true)
     * @return T DTO instance with populated properties matching the input data structure
     *
     * @example
     * $data = ['name' => 'John', 'rating' => '5', 'itemId' => '123'];
     * $dto = $helper->mapArrayToDto($data, ReviewCreateRequest::class);
     * // Result: $dto->name = 'John', $dto->rating = 5 (int), $dto->itemId = 123 (int)
     */
    public function mapArrayToDto(array $data, string $dtoClass, bool $trimStrings = true): object
    {
        // Step 1: Use Symfony Serializer for basic mapping
        // This handles structure conversion and basic type mapping
        $dto = $this->serializer->deserialize(
            json_encode($data),
            $dtoClass,
            'json'
        );

        // Step 2: Convert numeric fields for known DTOs
        // Simple switch/case approach - easy to understand and extend
        // Symfony Serializer doesn't always convert types correctly (especially string -> int/float),
        // so we explicitly convert known numeric fields
        $this->convertNumericFields($dto, $dtoClass);
        
        // Step 3: Trim all string properties automatically
        // This centralizes a common post-processing step and removes duplication in controllers
        if ($trimStrings) {
            $this->trimStringFields($dto);
        }

        return $dto;
    }

    /**
     * Convert numeric fields for known DTOs
     *
     * Simple switch/case approach - easy to understand and extend.
     * When adding a new DTO with numeric fields, just add a new case here.
     *
     * Why this is needed:
     * Symfony Serializer sometimes doesn't convert types correctly when deserializing JSON.
     * For example, if JSON contains {"rating": "5"} (string), the serializer might leave it as a string
     * instead of converting it to an integer. This method explicitly converts string values to their
     * expected types based on the DTO class.
     *
     * Conversion examples:
     * - String '5' → int 5 (for integer properties)
     * - String '10.5' → float 10.5 (for float properties)
     * - String 'true' → bool true (for boolean properties)
     *
     * @param object $dto DTO instance to convert (will be modified in place)
     * @param string $dtoClass DTO class name (fully qualified class name)
     */
    private function convertNumericFields(object $dto, string $dtoClass): void
    {
        switch ($dtoClass) {
            case OrderCreateRequest::class:
                // Convert integer fields: couponId should be an integer
                // Check if the value exists and is a string (needs conversion)
                if (isset($dto->couponId) && is_string($dto->couponId)) {
                    $dto->couponId = $this->convertToInt($dto->couponId);
                }
                // Convert float fields: deliveryFee and discountAmount should be floats
                if (isset($dto->deliveryFee) && is_string($dto->deliveryFee)) {
                    $dto->deliveryFee = $this->convertToFloat($dto->deliveryFee);
                }
                if (isset($dto->discountAmount) && is_string($dto->discountAmount)) {
                    $dto->discountAmount = $this->convertToFloat($dto->discountAmount);
                }
                break;
                
            case ReviewCreateRequest::class:
                // Convert integer field: rating should be an integer (1-5)
                if (isset($dto->rating) && is_string($dto->rating)) {
                    $dto->rating = $this->convertToInt($dto->rating);
                }
                break;
                
            case CartUpdateQuantityRequest::class:
                // Convert integer field: quantity should be an integer
                if (isset($dto->quantity) && is_string($dto->quantity)) {
                    $dto->quantity = $this->convertToInt($dto->quantity);
                }
                break;
                
            case CartAddRequest::class:
                // Convert integer fields: itemId and quantity should be integers
                if (isset($dto->itemId) && is_string($dto->itemId)) {
                    $dto->itemId = $this->convertToInt($dto->itemId);
                }
                if (isset($dto->quantity) && is_string($dto->quantity)) {
                    $dto->quantity = $this->convertToInt($dto->quantity);
                }
                break;
                
            case CouponValidateRequest::class:
                // Convert float field: orderAmount should be a float (price with decimals)
                if (isset($dto->orderAmount) && is_string($dto->orderAmount)) {
                    $dto->orderAmount = $this->convertToFloat($dto->orderAmount);
                }
                break;
                
            case ReservationCreateRequest::class:
                // Convert integer field: guests should be an integer (number of people)
                if (isset($dto->guests) && is_string($dto->guests)) {
                    $dto->guests = $this->convertToInt($dto->guests);
                }
                break;
                
            case ContactCreateRequest::class:
                // Convert boolean field: consent should be a boolean (true/false)
                if (isset($dto->consent) && is_string($dto->consent)) {
                    $dto->consent = $this->convertToBool($dto->consent);
                }
                break;
                
            // DTOs with only string fields don't need type conversion:
            // - AddressValidationRequest (only zipCode: string)
            // - AddressFullValidationRequest (only address: string, zipCode: string)
        }
    }

    /**
     * Trim all string properties in DTO
     *
     * Simple approach using get_object_vars() - no Reflection API needed.
     * This automatically trims all public string properties to remove leading/trailing whitespace.
     *
     * Why this is needed:
     * User input often contains accidental whitespace (spaces, tabs, newlines) at the beginning
     * or end of strings. Trimming removes this whitespace to ensure clean data.
     * For example: "  John Doe  " becomes "John Doe".
     *
     * How it works:
     * 1. get_object_vars() gets all public properties of the DTO object
     * 2. For each property, if it's a string, we trim it
     * 3. The trimmed value replaces the original value
     *
     * @param object $dto DTO instance to trim (will be modified in place)
     */
    private function trimStringFields(object $dto): void
    {
        // Get all public properties of the DTO object
        // This is simpler than using Reflection API
        foreach (get_object_vars($dto) as $property => $value) {
            // Only trim string values (skip integers, floats, booleans, null, etc.)
            if (is_string($value)) {
                // Remove leading and trailing whitespace
                $dto->$property = trim($value);
            }
        }
    }


    /**
     * Convert a value to integer
     *
     * Handles string-to-int conversion for numeric strings.
     * This method safely converts various input types to integers.
     *
     * Conversion logic:
     * 1. If already an integer, return as-is
     * 2. If it's a string, trim whitespace and check if it's numeric
     * 3. If it's a float that's a whole number (e.g., 5.0), convert to int
     * 4. If it's numeric in any other form, try to cast to int
     * 5. Otherwise, return the original value (conversion failed)
     *
     * Examples:
     * - "5" → 5
     * - "  10  " → 10
     * - 5.0 → 5
     * - "abc" → "abc" (returns original, conversion failed)
     *
     * @param mixed $value Value to convert (can be string, int, float, etc.)
     * @return int|mixed Converted integer or original value if conversion failed
     */
    private function convertToInt(mixed $value): mixed
    {
        // If already an integer, no conversion needed
        if (is_int($value)) {
            return $value;
        }
        
        // Convert string numbers to int
        if (is_string($value)) {
            // Trim whitespace first (e.g., "  5  " becomes "5")
            $trimmed = trim($value);
            
            // Handle empty string or "0" as special cases
            if ($trimmed === '' || $trimmed === '0') {
                return 0;
            }
            
            // Check if the trimmed string is numeric (e.g., "5", "10", "123")
            if (is_numeric($trimmed)) {
                return (int)$trimmed;
            }
        }
        
        // Convert float to int if it's a whole number (e.g., 5.0 → 5)
        // This handles cases where JSON might have {"rating": 5.0} instead of {"rating": 5}
        if (is_float($value)) {
            // Check if float is a whole number (5.0 == 5)
            if ($value == (int)$value) {
                return (int)$value;
            }
        }
        
        // Try generic conversion as last resort
        // This handles edge cases where the value might be numeric in some other form
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        // Conversion failed, return original value
        // This allows the validator to catch the error later
        return $value;
    }

    /**
     * Convert a value to float
     *
     * Handles string-to-float conversion for numeric strings.
     * This method safely converts various input types to floats.
     *
     * Conversion logic:
     * 1. If already a float or integer, cast to float
     * 2. If it's a numeric string (e.g., "10.5", "10"), convert to float
     * 3. Otherwise, return the original value (conversion failed)
     *
     * Examples:
     * - "10.5" → 10.5
     * - "10" → 10.0
     * - 10 → 10.0
     * - 10.5 → 10.5
     * - "abc" → "abc" (returns original, conversion failed)
     *
     * @param mixed $value Value to convert (can be string, int, float, etc.)
     * @return float|mixed Converted float or original value if conversion failed
     */
    private function convertToFloat(mixed $value): mixed
    {
        // If already a float or integer, just cast to float
        // Integers are valid floats (e.g., 10 is the same as 10.0)
        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }
        
        // Convert numeric strings to float
        // This handles cases like "10.5" or "10" from JSON
        if (is_string($value) && is_numeric($value)) {
            return (float)$value;
        }
        
        // Conversion failed, return original value
        // This allows the validator to catch the error later
        return $value;
    }

    /**
     * Convert a value to boolean
     *
     * Handles various boolean representations commonly found in JSON/form data.
     * This method converts string representations of boolean values to actual booleans.
     *
     * Conversion logic:
     * 1. If already a boolean, return as-is
     * 2. If it's a string, check for common "true" representations
     * 3. Otherwise, use PHP's standard boolean conversion
     *
     * String values that are considered "true":
     * - "true" (case-insensitive)
     * - "1"
     * - "yes" (case-insensitive)
     * - "on" (case-insensitive)
     *
     * Examples:
     * - "true" → true
     * - "1" → true
     * - "yes" → true
     * - "false" → false
     * - "0" → false
     * - "" → false
     *
     * @param mixed $value Value to convert (can be string, bool, int, etc.)
     * @return bool Converted boolean value
     */
    private function convertToBool(mixed $value): bool
    {
        // If already a boolean, no conversion needed
        if (is_bool($value)) {
            return $value;
        }
        
        // Convert string representations of boolean values
        // This handles cases like "true", "1", "yes", "on" from JSON or form data
        if (is_string($value)) {
            // Check if the string (lowercased) is one of the "true" values
            // The third parameter (true) makes the comparison case-sensitive for the array values
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }
        
        // For other types (int, float, etc.), use PHP's standard boolean conversion
        // 0, 0.0, null, empty string, etc. → false
        // Any other value → true
        return (bool)$value;
    }

    /**
     * Validate XSS attempts in DTO fields
     *
     * Checks multiple DTO properties for XSS attack patterns using InputSanitizer.
     * Returns array of error messages for fields that contain XSS attempts.
     * This centralizes XSS validation logic and eliminates code duplication across controllers.
     *
     * Simple approach using get_object_vars() - no Reflection needed.
     *
     * @param object $dto DTO object with public properties to check
     * @param array<string> $fieldNames Array of property names to validate (e.g., ['firstName', 'lastName', 'email'])
     * @return array<string> Array of error messages for fields with XSS attempts (empty if none found)
     *
     * @example
     * $errors = $helper->validateXssAttempts($dto, ['firstName', 'lastName', 'email', 'phone', 'message']);
     * if (!empty($errors)) {
     *     // Return validation error with XSS errors
     * }
     */
    public function validateXssAttempts(object $dto, array $fieldNames): array
    {
        $errors = [];
        
        // Get all public properties using simple get_object_vars() - no Reflection API needed
        // This returns an associative array of property names and their values
        $properties = get_object_vars($dto);
        
        // Check each field name provided by the caller
        foreach ($fieldNames as $fieldName) {
            // Skip if property doesn't exist in the DTO
            // This prevents errors if a field name is provided that doesn't exist
            if (!isset($properties[$fieldName])) {
                continue;
            }
            
            // Get the value of the property
            $value = $properties[$fieldName];
            
            // Skip null or empty values
            // XSS validation is only needed for actual content, not empty fields
            if ($value === null || $value === '') {
                continue;
            }
            
            // Check for XSS (Cross-Site Scripting) attack patterns
            // InputSanitizer::containsXssAttempt() checks for common XSS patterns like:
            // - <script> tags
            // - javascript: URLs
            // - Event handlers (onclick, onerror, etc.)
            // - Other dangerous HTML/JavaScript patterns
            if (InputSanitizer::containsXssAttempt($value)) {
                // Generate user-friendly French error message based on field name
                // For example: "Le prénom contient des éléments non autorisés"
                $fieldLabel = $this->getFieldLabel($fieldName);
                $errors[] = $fieldLabel . ' contient des éléments non autorisés';
            }
        }
        
        // Return array of error messages (empty if no XSS attempts found)
        return $errors;
    }

    /**
     * Get user-friendly French label for field name
     *
     * Converts camelCase or snake_case field names to French labels for error messages.
     * This makes error messages more readable for French-speaking users.
     *
     * How it works:
     * 1. First, check if the field name exists in the predefined mapping
     * 2. If found, return the French label (e.g., 'firstName' → 'Le prénom')
     * 3. If not found, generate a label from the field name by converting camelCase
     *
     * Examples:
     * - 'firstName' → 'Le prénom'
     * - 'clientEmail' → 'L\'email du client'
     * - 'unknownField' → 'Le champ unknown field' (fallback)
     *
     * @param string $fieldName Field name in camelCase or snake_case (e.g., 'firstName', 'client_email')
     * @return string French label with article (e.g., 'Le prénom', 'L\'email du client')
     */
    private function getFieldLabel(string $fieldName): string
    {
        // Map common field names to their French labels
        // This provides user-friendly error messages in French
        $labels = [
            'firstName' => 'Le prénom',
            'lastName' => 'Le nom',
            'email' => 'L\'email',
            'phone' => 'Le numéro de téléphone',
            'message' => 'Le message',
            'subject' => 'Le sujet',
            'clientFirstName' => 'Le prénom du client',
            'clientLastName' => 'Le nom du client',
            'clientEmail' => 'L\'email du client',
            'clientPhone' => 'Le numéro de téléphone du client',
            'deliveryAddress' => 'L\'adresse de livraison',
            'deliveryInstructions' => 'Les instructions de livraison',
            'address' => 'L\'adresse',
            'zipCode' => 'Le code postal',
        ];
        
        // Return mapped label if it exists
        // This provides the best user experience with proper French labels
        if (isset($labels[$fieldName])) {
            return $labels[$fieldName];
        }
        
        // Fallback: convert camelCase to readable French
        // This handles unknown field names by converting camelCase to words
        // Example: 'unknownField' → 'unknown Field' → 'Le champ unknown field'
        $converted = preg_replace('/([A-Z])/', ' $1', $fieldName); // Add space before capital letters
        $converted = strtolower($converted); // Convert to lowercase
        return 'Le champ ' . trim($converted); // Add French article "Le champ"
    }
}
