<?php declare(strict_types=1);

namespace Elio\ElioDataDiscovery\Core\Sync\DataTypes\Aggregation;

enum Visibilities: string
{
    case VISIBILITY_SEARCH = 'search';
    case VISIBILITY_ALL = 'all';
    case VISIBILITY_NONE = 'none';
}