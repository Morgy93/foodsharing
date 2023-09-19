<?php

namespace Foodsharing\Lib;

class Routing
{
    private const CLASSES = [
        'activity' => 'Activity',
        'application' => 'Application',
        'basket' => 'Basket',
        'bell' => 'Bell',
        'blog' => 'Blog',
        'buddy' => 'Buddy',
        'bcard' => 'BusinessCard',
        'content' => 'Content',
        'dashboard' => 'Dashboard',
        'email' => 'Email',
        'event' => 'Event',
        'fairteiler' => 'FoodSharePoint',
        'foodsaver' => 'Foodsaver',
        'index' => 'Index',
        'legal' => 'Legal',
        'login' => 'Login',
        'logout' => 'Logout',
        'mailbox' => 'Mailbox',
        'main' => 'Main',
        'map' => 'Map',
        'msg' => 'Message',
        'message' => 'Message',
        'passgen' => 'PassportGenerator',
        'poll' => 'Voting',
        'profile' => 'Profile',
        'quiz' => 'Quiz',
        'bezirk' => 'Region',
        'region' => 'RegionAdmin',
        'register' => 'Register',
        'relogin' => 'Relogin',
        'report' => 'Report',
        'search' => 'Search',
        'settings' => 'Settings',
        'statistics' => 'Statistics',
        'betrieb' => 'Store',
        'fsbetrieb' => 'StoreUser',
        'team' => 'Team',
        'wallpost' => 'WallPost',
        'groups' => 'WorkGroup',
        'store' => 'Store',
        'chain' => 'StoreChain'
    ];

    private const PORTED = [];

    private const RENAMES = [];

    public const FQCN_PREFIX = '\\Foodsharing\\Modules\\';

    public static function getClassName(string $appName, $type = 'Xhr'): ?string
    {
        if (!array_key_exists($appName, self::CLASSES)) {
            return null;
        }

        return self::FQCN_PREFIX . self::CLASSES[$appName] . '\\' . self::CLASSES[$appName] . $type;
    }

    public static function getModuleName(string $appName): ?string
    {
        return self::CLASSES[$appName];
    }

    public static function isPorted(string $pageName): bool
    {
        return in_array($pageName, self::PORTED);
    }

    public static function getPortedName(string $pageName): string
    {
        // ignored because PHPStan complains about RENAMES being empty. This will change with the RegionController port though
        /* @phpstan-ignore-next-line */
        return array_key_exists($pageName, self::RENAMES) ? self::RENAMES[$pageName] : $pageName;
    }
}
