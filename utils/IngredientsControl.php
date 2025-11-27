<?php
/**
 * Validate and normalize a semicolon-separated list of ingredients.
 *
 * This helper takes a raw ingredients string (typically from a form), trims whitespace,
 * removes empty entries, validates each ingredient, and returns a cleaned, normalized
 * version joined by semicolons (`;`).
 *
 * Validation rules:
 * - At least 1 ingredient is required (otherwise exits with JSON error).
 * - Maximum 78 ingredients allowed.
 * - Each ingredient must:
 *   - Be ≤ 50 characters long.
 *   - Match the pattern `/^[A-Za-z0-9+\-,.%:;() ]+$/u`
 *     (letters, digits, basic punctuation and spaces only).
 *
 * On any validation error, the function sends a JSON response like:
 * ```json
 * {"status": "error", "message": "Invalid ingredient"}
 * ```
 * and immediately terminates execution via `exit`.
 *
 * Typical usage:
 * ```php
 * $validated = validateIngredients($_POST['ingredients'] ?? '');
 * ```
 *
 *
 *
 * @author Valentyn Deshel
 *
 * @param string $ingredientsRaw Raw ingredients input string (semicolon-separated).
 *
 * @return string Normalized and validated ingredients string joined with `;`.
 *
 * @throws void This function terminates with a JSON response on validation error.
 */

function validateIngredients(string $ingredientsRaw): string {
    $parts = array_filter(array_map('trim', explode(';', $ingredientsRaw)), fn($v) => $v !== '');

    if (count($parts) === 0) {
        echo json_encode(["status" => "error", "message" => "Please add at least one ingredient"]);
        exit;
    }
    if (count($parts) > 78) {
        echo json_encode(["status" => "error", "message" => "Too many ingredients (max 78)"]);
        exit;
    }

    $pattern = '/^[A-Za-z0-9+\-,.%:;() ]+$/u';
    foreach ($parts as $ing) {
        if (mb_strlen($ing, 'UTF-8') > 50) {
            echo json_encode(["status" => "error", "message" => "Each ingredient must be ≤ 50 characters"]);
            exit;
        }
        if (!preg_match($pattern, $ing)) {
            echo json_encode(["status" => "error", "message" => "Ingredient contains invalid characters"]);
            exit;
        }
    }

    return implode(';', $parts);
}