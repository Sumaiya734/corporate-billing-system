<?php

namespace App\Helpers;

class ProductTypeHelper
{
    public static function getTypeConfig($typeName)
    {
        // Predefined configurations for common types
        $predefinedTypes = [
            'regular' => [ 
                'icon' => 'fa-wifi', 
                'color' => '#3498db',
                'description' => 'Standard internet package'
            ],
            'special' => [ 
                'icon' => 'fa-star', 
                'color' => '#e74c3c',
                'description' => 'Special promotional package'
            ],
            'premium' => [ 
                'icon' => 'fa-crown', 
                'color' => '#f39c12',
                'description' => 'Premium high-speed package'
            ],
            'enterprise' => [ 
                'icon' => 'fa-building', 
                'color' => '#9b59b6',
                'description' => 'Business enterprise solution'
            ],
            'business' => [ 
                'icon' => 'fa-briefcase', 
                'color' => '#2ecc71',
                'description' => 'Business grade service'
            ],
            'starter' => [ 
                'icon' => 'fa-seedling', 
                'color' => '#1abc9c',
                'description' => 'Beginner friendly package'
            ],
            'professional' => [ 
                'icon' => 'fa-user-tie', 
                'color' => '#e67e22',
                'description' => 'Professional grade service'
            ],
            'ultimate' => [ 
                'icon' => 'fa-rocket', 
                'color' => '#e84393',
                'description' => 'Ultimate performance package'
            ],
            'custom' => [ 
                'icon' => 'fa-cogs', 
                'color' => '#6c5ce7',
                'description' => 'Custom tailored solution'
            ]
        ];
        
        // If type is predefined, return its configuration
        if (isset($predefinedTypes[strtolower($typeName)])) {
            $config = $predefinedTypes[strtolower($typeName)];
            $config['textColor'] = self::getContrastColor($config['color']);
            return $config;
        }
        
        // Generate dynamic configuration for new types
        return self::generateDynamicConfig($typeName);
    }
    
    // Generate unique color and icon for new product types
    private static function generateDynamicConfig($typeName)
    {
        // Generate consistent color based on type name hash
        $color = self::generateColorFromString($typeName);
        
        // Select icon based on type name keywords or generate from name
        $icon = self::generateIconFromString($typeName);
        
        // Generate description based on type name
        $description = self::generateDescriptionFromString($typeName);
        
        return [
            'icon' => $icon,
            'color' => $color,
            'textColor' => self::getContrastColor($color),
            'description' => $description
        ];
    }
    
    // Generate consistent color from string
    private static function generateColorFromString($str)
    {
        $hash = 0;
        for ($i = 0; $i < strlen($str); $i++) {
            $hash = ord($str[$i]) + (($hash << 5) - $hash);
        }
        
        // Generate HSL color for better consistency and accessibility
        $hue = $hash % 360;
        $saturation = 70 + ($hash % 20); // 70-90%
        $lightness = 50 + ($hash % 15); // 50-65%
        
        return "hsl($hue, {$saturation}%, {$lightness}%)";
    }
    
    // Generate icon based on type name keywords
    private static function generateIconFromString($typeName)
    {
        $lowerType = strtolower($typeName);
        $iconMap = [
            // Speed related
            'speed' => 'fa-tachometer-alt',
            'fast' => 'fa-bolt',
            'quick' => 'fa-running',
            'rapid' => 'fa-wind',
            
            // Business related
            'business' => 'fa-briefcase',
            'corporate' => 'fa-building',
            'office' => 'fa-desktop',
            'company' => 'fa-landmark',
            
            // Home related
            'home' => 'fa-home',
            'family' => 'fa-users',
            'residential' => 'fa-house-user',
            
            // Student related
            'student' => 'fa-graduation-cap',
            'education' => 'fa-book',
            'campus' => 'fa-school',
            
            // Gaming related
            'gaming' => 'fa-gamepad',
            'game' => 'fa-dice',
            'stream' => 'fa-video',
            
            // Streaming related
            'streaming' => 'fa-film',
            'video' => 'fa-video',
            'media' => 'fa-photo-video',
            
            // Economic related
            'economic' => 'fa-piggy-bank',
            'budget' => 'fa-money-bill-wave',
            'cheap' => 'fa-tags',
            
            // Professional related
            'professional' => 'fa-user-tie',
            'pro' => 'fa-award',
            'expert' => 'fa-certificate',
            
            // Ultimate related
            'ultimate' => 'fa-rocket',
            'extreme' => 'fa-fire',
            'maximum' => 'fa-chart-line',
            
            // Custom related
            'custom' => 'fa-cogs',
            'tailored' => 'fa-user-cog',
            'personal' => 'fa-user-edit',
            
            // Default fallbacks
            'basic' => 'fa-layer-group',
            'standard' => 'fa-certificate',
            'advanced' => 'fa-microchip'
        ];
        
        // Check for keywords in type name
        foreach ($iconMap as $keyword => $icon) {
            if (strpos($lowerType, $keyword) !== false) {
                return $icon;
            }
        }
        
        // Fallback: use first letter or default icon
        $firstLetter = strtolower(substr($typeName, 0, 1));
        $letterIcons = [
            'a' => 'fa-award', 'b' => 'fa-bolt', 'c' => 'fa-cube', 'd' => 'fa-diamond',
            'e' => 'fa-star', 'f' => 'fa-flag', 'g' => 'fa-gem', 'h' => 'fa-heart',
            'i' => 'fa-infinity', 'j' => 'fa-journal', 'k' => 'fa-key', 'l' => 'fa-leaf',
            'm' => 'fa-magic', 'n' => 'fa-network', 'o' => 'fa-orbit', 'p' => 'fa-palette',
            'q' => 'fa-question', 'r' => 'fa-rainbow', 's' => 'fa-shield', 't' => 'fa-trophy',
            'u' => 'fa-umbrella', 'v' => 'fa-volume', 'w' => 'fa-wifi', 'x' => 'fa-x-ray',
            'y' => 'fa-yin-yang', 'z' => 'fa-zap'
        ];
        
        return $letterIcons[$firstLetter] ?? 'fa-cube';
    }
    
    // Generate description based on type name
    private static function generateDescriptionFromString($typeName)
    {
        $lowerType = strtolower($typeName);
        
        if (strpos($lowerType, 'basic') !== false || strpos($lowerType, 'starter') !== false) {
            return 'Essential package for basic needs';
        } elseif (strpos($lowerType, 'standard') !== false || strpos($lowerType, 'regular') !== false) {
            return 'Standard package for everyday use';
        } elseif (strpos($lowerType, 'premium') !== false || strpos($lowerType, 'pro') !== false) {
            return 'Premium package with enhanced features';
        } elseif (strpos($lowerType, 'ultimate') !== false || strpos($lowerType, 'extreme') !== false) {
            return 'Ultimate package for maximum performance';
        } elseif (strpos($lowerType, 'business') !== false || strpos($lowerType, 'corporate') !== false) {
            return 'Business-grade solution for companies';
        } elseif (strpos($lowerType, 'gaming') !== false || strpos($lowerType, 'game') !== false) {
            return 'Optimized for gaming and low latency';
        } elseif (strpos($lowerType, 'streaming') !== false || strpos($lowerType, 'media') !== false) {
            return 'Perfect for streaming and media consumption';
        } elseif (strpos($lowerType, 'student') !== false || strpos($lowerType, 'education') !== false) {
            return 'Student-friendly package with educational benefits';
        } elseif (strpos($lowerType, 'family') !== false || strpos($lowerType, 'home') !== false) {
            return 'Family package for multiple users';
        } else {
            return "{$typeName} package with customized features";
        }
    }
    
    // Get contrasting text color (black or white) for background
    private static function getContrastColor($hexcolor)
    {
        // If using HSL, convert to RGB first
        if (strpos($hexcolor, 'hsl') === 0) {
            preg_match_all('/(\d+)/', $hexcolor, $matches);
            $h = $matches[0][0] / 360;
            $s = $matches[0][1] / 100;
            $l = $matches[0][2] / 100;
            
            if ($s == 0) {
                $r = $g = $b = $l;
            } else {
                $hue2rgb = function($p, $q, $t) {
                    if ($t < 0) $t += 1;
                    if ($t > 1) $t -= 1;
                    if ($t < 1/6) return $p + ($q - $p) * 6 * $t;
                    if ($t < 1/2) return $q;
                    if ($t < 2/3) return $p + ($q - $p) * (2/3 - $t) * 6;
                    return $p;
                };
                
                $q = $l < 0.5 ? $l * (1 + $s) : $l + $s - $l * $s;
                $p = 2 * $l - $q;
                $r = $hue2rgb($p, $q, $h + 1/3);
                $g = $hue2rgb($p, $q, $h);
                $b = $hue2rgb($p, $q, $h - 1/3);
            }
            
            $r = round($r * 255);
            $g = round($g * 255);
            $b = round($b * 255);
        } else {
            // Handle hex colors
            $hexcolor = str_replace("#", "", $hexcolor);
            $r = hexdec(substr($hexcolor, 0, 2));
            $g = hexdec(substr($hexcolor, 2, 2));
            $b = hexdec(substr($hexcolor, 4, 2));
        }
        
        // Calculate luminance
        $luminance = (0.299 * $r + 0.587 * $g + 0.114 * $b) / 255;
        return $luminance > 0.5 ? '#000000' : '#ffffff';
    }
}