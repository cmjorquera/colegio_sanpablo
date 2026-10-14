<?php

// Existing public color validation, shared without changing the home defaults.
if (!function_exists('sp_public_hex_color')) {
    function sp_public_hex_color(?string $value, string $fallback): string
    {
        $value = trim((string) $value);
        return preg_match('/^#(?:[0-9a-fA-F]{3}){1,2}$/', $value) ? $value : $fallback;
    }
}

function cms_public_institution_theme(array $institution): string
{
    $colors = [];
    foreach (['primary' => ['primario', '#F0A000'], 'secondary' => ['secundario', '#EF6C00'],
        'tertiary' => ['terciario', '#1976D2'], 'quaternary' => ['cuaternario', '#E53935']] as $role => [$field, $fallback]) {
        $colors[$role] = sp_public_hex_color($institution['color_' . $field] ?? null, $fallback);
    }
    $gradients = (int) ($institution['usar_gradientes'] ?? 0) === 1;
    $background = $gradients
        ? 'linear-gradient(135deg, var(--sp-db-primary), var(--sp-db-secondary))'
        : 'var(--sp-db-primary)';

    // Pick a readable neutral foreground, including both ends of a gradient.
    $luminance = static function (string $hex): float {
        $hex = ltrim($hex, '#');
        if (strlen($hex) === 3) {
            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
        }
        $channels = [];
        foreach ([0, 2, 4] as $offset) {
            $channel = hexdec(substr($hex, $offset, 2)) / 255;
            $channels[] = $channel <= 0.04045 ? $channel / 12.92 : (($channel + 0.055) / 1.055) ** 2.4;
        }
        return $channels[0] * 0.2126 + $channels[1] * 0.7152 + $channels[2] * 0.0722;
    };
    $backgrounds = [$luminance($colors['primary'])];
    if ($gradients) {
        $backgrounds[] = $luminance($colors['secondary']);
    }
    $blackContrast = min(array_map(static fn(float $value): float => ($value + 0.05) / 0.05, $backgrounds));
    $whiteContrast = min(array_map(static fn(float $value): float => 1.05 / ($value + 0.05), $backgrounds));
    // Very different endpoint luminances cannot share a readable foreground.
    if ($gradients && max($blackContrast, $whiteContrast) < 4.5) {
        $foreground = $luminance($colors['primary']) > 0.179 ? '#000000' : '#ffffff';
        $background = 'linear-gradient(135deg, var(--sp-db-primary), color-mix(in srgb, var(--sp-db-primary) 85%, var(--sp-db-secondary)))';
    } else {
        $foreground = $blackContrast >= $whiteContrast ? '#000000' : '#ffffff';
    }
    $properties = [];
    foreach ($colors as $role => $color) {
        $properties[] = '--sp-db-' . $role . ': ' . $color;
    }
    $properties[] = '--sp-db-accent-background: ' . $background;
    $properties[] = '--sp-db-accent-foreground: ' . $foreground;
    return implode('; ', $properties) . ';';
}
