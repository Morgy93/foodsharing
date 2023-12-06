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
        'region' => 'RegionAdmin',
        'register' => 'Register',
        'relogin' => 'Relogin',
        'report' => 'Report',
        'search' => 'Search',
        'settings' => 'Settings',
        'statistics' => 'Statistics',
        'betrieb' => 'Store',
        'fsbetrieb' => 'StoreUser',
        'wallpost' => 'WallPost',
        'groups' => 'WorkGroup',
        'store' => 'Store',
        'chain' => 'StoreChain',
    ];

    private const PORTED = [
        'content',
        'team',
        'bezirk',
    ];

    private const RENAMES = [
        'bezirk' => 'region'
    ];

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
        return array_key_exists($pageName, self::RENAMES) ? self::RENAMES[$pageName] : $pageName;
    }
}
