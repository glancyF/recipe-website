<?php
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