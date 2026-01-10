<?php

namespace KimaiPlugin\SimpleAccountingBundle\EventSubscriber;

use App\Event\ConfigureMainMenuEvent;
use App\Utils\MenuItemModel;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class MenuSubscriber implements EventSubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ConfigureMainMenuEvent::class => ['onConfigureMainMenu', 100],
        ];
    }

    public function onConfigureMainMenu(ConfigureMainMenuEvent $event): void
    {
        $menu = $event->getMenu();

        // Create the new top-level menu item "Simple Accounting"
        $simpleAccounting = new MenuItemModel(
            'simple_accounting',
            'menu.simple_accounting',
            'simple_accounting_index', // Route to dashboard
            [],
            'fas fa-file-invoice'
        );

        // Add the child "Dashboard"
        $simpleAccounting->addChild(
            new MenuItemModel(
                'simple_accounting_dashboard',
                'menu.create_simple_invoice', // Label (Dashboard)
                'simple_accounting_index', // Route
                [],
                'fas fa-plus-circle'
            )
        );

        // Add to main menu
        $menu->addChild($simpleAccounting);
    }
}
