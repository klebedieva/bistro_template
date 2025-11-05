<?php

namespace App\Service;

use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
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
     * Automatically maps array data to DTO properties, handling type conversion based on DTO property types.
     * This eliminates repetitive manual mapping code (e.g., `isset($data['key']) ? (int)$data['key'] : null`)
     * across multiple controllers.
     *
     * The method performs the following steps:
     * 1. Uses Symfony Serializer to deserialize array to DTO (handles basic mapping)
     * 2. Post-processes the DTO using reflection to ensure correct type conversion
     *    - String to integer (for int properties)
     *    - String to float (for float properties)
     *    - Null handling for optional fields
     *    - Boolean conversion
     *
     * Note: String trimming and custom sanitization must be done manually after mapping,
     * as this method doesn't perform these transformations.
     *
     * @template T of object
     * @param array<string, mixed> $data Source data array (from JSON request body or form data)
     * @param class-string<T> $dtoClass DTO class name to instantiate (must be a fully qualified class name)
     * @return T DTO instance with populated properties matching the input data structure
     *
     * @example
     * $data = ['name' => 'John', 'rating' => '5', 'itemId' => '123'];
     * $dto = $helper->mapArrayToDto($data, ReviewCreateRequest::class);
     * // Result: $dto->name = 'John', $dto->rating = 5 (int), $dto->itemId = 123 (int)
     */
    public function mapArrayToDto(array $data, string $dtoClass): object
    {
        // Step 1: Use Symfony Serializer to deserialize array to DTO
        // This handles basic mapping and structure conversion
        $dto = $this->serializer->deserialize(
            json_encode($data),
            $dtoClass,
            'json'
        );

        // Step 2: Post-process DTO to ensure correct type conversion
        // Symfony Serializer doesn't always convert types correctly (especially string -> int/float),
        // so we use reflection to check property types and convert values explicitly
        $reflection = new ReflectionClass($dto);
        $properties = $reflection->getProperties();

        foreach ($properties as $property) {
            // Skip if property is not accessible
            if (!$property->isPublic()) {
                continue;
            }

            // Get expected type from property declaration first
            $type = $property->getType();
            if (!$type instanceof ReflectionNamedType) {
                continue;
            }

            $typeName = $type->getName();
            
            // Skip if not a type we need to convert (string, object, etc. don't need conversion)
            if (!in_array($typeName, ['int', 'float', 'bool'], true)) {
                continue;
            }

            $currentValue = $property->getValue($dto);

            // Skip if value is null
            if ($currentValue === null) {
                continue;
            }

            // Convert value to expected type if needed
            if ($this->needsConversion($currentValue, $typeName)) {
                $convertedValue = $this->convertToType($currentValue, $typeName);
                // Only set if conversion actually changed the value
                if ($convertedValue !== $currentValue) {
                    $property->setValue($dto, $convertedValue);
                }
            }
        }

        return $dto;
    }

    /**
     * Check if a value needs type conversion
     *
     * Determines whether a value needs to be converted to match the target type.
     *
     * @param mixed $value Current value
     * @param string $targetType Target type name (int, float, bool, string, etc.)
     * @return bool True if conversion is needed, false otherwise
     */
    private function needsConversion(mixed $value, string $targetType): bool
    {
        return match ($targetType) {
            'int' => !is_int($value),
            'float' => !is_float($value) && !is_int($value), // int is also valid for float
            'bool' => !is_bool($value),
            'string' => !is_string($value),
            default => false, // For other types, no conversion needed
        };
    }

    /**
     * Convert a value to the specified type
     *
     * Handles type conversion for DTO properties, ensuring values match expected types.
     * This is necessary because JSON deserialization may leave values as strings even when
     * the property expects int, float, or bool.
     *
     * @param mixed $value Value to convert
     * @param string $targetType Target type name (int, float, bool, string, etc.)
     * @return mixed Converted value
     */
    private function convertToType(mixed $value, string $targetType): mixed
    {
        // Perform type conversion
        return match ($targetType) {
            'int' => $this->convertToInt($value),
            'float' => $this->convertToFloat($value),
            'bool' => $this->convertToBool($value),
            'string' => (string)$value,
            default => $value, // For other types, return as-is
        };
    }

    /**
     * Convert a value to integer
     *
     * Handles string-to-int conversion for numeric strings.
     * Returns the original value if conversion is not possible.
     *
     * @param mixed $value Value to convert
     * @return int|mixed Converted integer or original value if conversion failed
     */
    private function convertToInt(mixed $value): mixed
    {
        if (is_int($value)) {
            return $value;
        }
        
        // Convert string numbers to int
        if (is_string($value)) {
            // Trim whitespace first
            $trimmed = trim($value);
            if ($trimmed === '' || $trimmed === '0') {
                return 0;
            }
            if (is_numeric($trimmed)) {
                return (int)$trimmed;
            }
        }
        
        // Convert float to int if it's a whole number
        if (is_float($value)) {
            if ($value == (int)$value) {
                return (int)$value;
            }
        }
        
        // Try generic conversion as last resort
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        return $value;
    }

    /**
     * Convert a value to float
     *
     * Handles string-to-float conversion for numeric strings.
     * Returns the original value if conversion is not possible.
     *
     * @param mixed $value Value to convert
     * @return float|mixed Converted float or original value if conversion failed
     */
    private function convertToFloat(mixed $value): mixed
    {
        if (is_float($value) || is_int($value)) {
            return (float)$value;
        }
        
        if (is_string($value) && is_numeric($value)) {
            return (float)$value;
        }
        
        return $value;
    }

    /**
     * Convert a value to boolean
     *
     * Handles various boolean representations.
     *
     * @param mixed $value Value to convert
     * @return bool Converted boolean
     */
    private function convertToBool(mixed $value): bool
    {
        if (is_bool($value)) {
            return $value;
        }
        
        if (is_string($value)) {
            return in_array(strtolower($value), ['true', '1', 'yes', 'on'], true);
        }
        
        return (bool)$value;
    }
}
