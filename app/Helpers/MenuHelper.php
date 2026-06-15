<?php

namespace App\Helpers;

class MenuHelper
{
    public static function getMainNavItems()
    {
        return [

            [
                'icon' => 'dashboard',
                'name' => 'Dashboard',
                'path' => '/dashboard',
            ],
            [
                'icon' => 'apartment',
                'name' => 'รายการทรัพย์สิน',
                'path' => route('property.index', absolute: false),
            ],
            [
                'icon' => 'home',
                'name' => 'ฝากขายบ้าน-ที่ดิน',
                'path' => route('propertyRequest.index', ['type' => 'sell'], absolute: false),
            ],
            [
                'icon' => 'search',
                'name' => 'ฝากหาบ้าน-ที่ดิน',
                'path' => route('propertyRequest.index', ['type' => 'buy'], absolute: false),
            ],


            /*
            [
                'icon' => 'user',
                'name' => 'User Profile',
                'path' => '/profile',
            ],
            [
                'name' => 'Forms',
                'icon' => 'file',
                'subItems' => [
                    ['name' => 'Form Elements', 'path' => '/form-elements', 'pro' => false],
                ],
            ],
            [
                'name' => 'Tables',
                'icon' => 'table',
                'subItems' => [
                    ['name' => 'Basic Tables', 'path' => '/basic-tables', 'pro' => false]
                ],
            ],
            [
                'name' => 'Pages',
                'icon' => 'files',
                'subItems' => [
                    ['name' => 'Blank Page', 'path' => '/blank', 'pro' => false],
                    ['name' => '404 Error', 'path' => '/error-404', 'pro' => false]
                ],
            ],
            */
        ];
    }

    public static function getOthersItems()
    {
        return [
            [
                'icon' => 'apartment',
                'name' => 'ทรัพย์สิน',
                'subItems' => [
                    ['name' => 'ประเภทของทรัพย์สิน', 'path' => route('propertyType.index', absolute: false), 'pro' => false],
                    ['name' => 'โซน', 'path' => '/bar-chart', 'pro' => false],
                    ['name' => 'สิ่งอำนวยความสะดวก', 'path' => '/line-chart', 'pro' => false],
                    ['name' => 'สถานที่ใกล้เคียง', 'path' => '/line-chart', 'pro' => false],
                ],
            ],
            [
                'icon' => 'users',
                'name' => 'ตัวแทนขาย/ผู้ใช้งานระบบ',
                'path' => route('user.index', absolute: false),
            ],

            [
                'icon' => 'book',
                'name' => 'บทความ',
                'subItems' => [
                    ['name' => 'รายการบทความ', 'path' => '/line-chart', 'pro' => false],
                    ['name' => 'ประเภทของบทความ', 'path' => '/bar-chart', 'pro' => false]
                ],
            ],

        ];
    }

    public static function getMenuGroups()
    {
        return [
            [
                'title' => 'เมนู',
                'items' => self::getMainNavItems()
            ],
            [
                'title' => 'Others',
                'items' => self::getOthersItems()
            ]
        ];
    }

    public static function isActive($path): bool
    {
        if (blank($path)) {
            return false;
        }

        $parsed = parse_url($path);
        $targetPath = '/' . ltrim($parsed['path'] ?? $path, '/');

        if (request()->getPathInfo() !== $targetPath) {
            return false;
        }

        if (empty($parsed['query'])) {
            return true;
        }

        parse_str($parsed['query'], $targetQuery);

        foreach ($targetQuery as $key => $value) {
            if ((string) request()->query($key) !== (string) $value) {
                return false;
            }
        }

        return true;
    }

    /**
     * Render a Lineicons icon for sidebar menus.
     *
     * @see https://lineicons.com/icons
     */
    public static function getIcon(string $iconName): string
    {
        $iconClass = self::resolveIconClass($iconName);

        return sprintf(
            '<i class="lni lni-%s text-[22px] leading-none" aria-hidden="true"></i>',
            e($iconClass)
        );
    }

    /**
     * @deprecated Use getIcon() instead.
     */
    public static function getIconSvg(string $iconName): string
    {
        return self::getIcon($iconName);
    }

    protected static function resolveIconClass(string $iconName): string
    {
        $icons = [
            // Main menu
            'dashboard' => 'dashboard-square-1',
            'home' => 'home-2',
            'search' => 'search-1',
            'user' => 'user-4',

            // Property & real estate
            'apartment' => 'buildings-1',
            'building' => 'buildings-1',
            'map-marker' => 'map-marker-1',
            'service' => 'service-bell-1',
            'direction' => 'direction-ltr',

            // People & content
            'users' => 'user-multiple-4',
            'book' => 'books-2',
            'files' => 'file-multiple',
            'tag' => 'bookmark-1',

            // Legacy keys (demo menus)
            'user-profile' => 'user-4',
            'forms' => 'file-pencil',
            'file' => 'file-pencil',
            'tables' => 'bar-chart-4',
            'table' => 'bar-chart-4',
            'pages' => 'file-multiple',
            'charts' => 'bar-chart-4',
            'ui-elements' => 'layers-1',
            'authentication' => 'locked-1',
            'calendar' => 'calendar-days',
            'task' => 'clipboard',
            'ecommerce' => 'cart-1',
            'chat' => 'comment-1',
            'email' => 'envelope-1',
            'support-ticket' => 'headphone-1',
            'ai-assistant' => 'search-text',
        ];

        return $icons[$iconName] ?? 'star-fat';
    }
}
