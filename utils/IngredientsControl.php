<?php

function validateIngredients(string $ingredientsRaw): string {

    $parts = array_map('trim', explode(';', $ingredientsRaw));

    $parts = array_values(array_filter($parts, static fn($v) => $v !== ''));
    if (count($parts) === 0) {
        echo json_encode(["status" => "error", "message" => "Please add at least one ingredient"]);
        exit;
    }
    if (count($parts) > 78) {
        echo json_encode(["status" => "error", "message" => "Too many ingredients (max 78)"]);
        exit;
    }

    $pattern = '~^[-\x{2010}-\x{2015} A-Za-z\x{00C0}-\x{024F}\x{0400}-\x{052F}0-9+.,%:;()\'"*!/]+$~u';

    foreach ($parts as $ing) {
        $len = mb_strlen($ing, 'UTF-8');
        if ($len < 1 || $len > 50) {
            echo json_encode(["status" => "error", "message" => "Each ingredient must be ≤ 50 characters"]);
            exit;
        }
        if (!preg_match($pattern, $ing)) {
            echo json_encode(["status" => "error", "message" => "Ingredient contains invalid characters"]);
            exit;
        }
    }

    $parts = array_values(array_unique(array_map(
        fn($s) => $s,
        $parts
    )));

    return implode(';', $parts);
}
