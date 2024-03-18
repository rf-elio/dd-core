<?php


namespace Elio\ElioDataDiscovery\Tests\Core\Export\Mock;


use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class EventDispatcherMock
 *
 * @package Elio\ElioDataDiscovery\Tests\Core\Export\Mock
 */
class EventDispatcherMock implements EventDispatcherInterface
{
    public function dispatch(object $event, ?string $eventName = null): object
    {
        return $event;
    }
}
