<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\ClientesPorRenovarWidget;
use App\Filament\Widgets\FinancialStatsWidget;
use App\Filament\Widgets\YapeClientesTableWidget;
use App\Filament\Widgets\YapesTotalesDelDiaWidget;
use Filament\Pages\Dashboard as BasePage;

class Dashboard extends BasePage
{
    protected static bool $shouldRegisterNavigation = false;



    protected function getColumns(): int | array
    {
        return 1;

    }

    protected function getWidgets(): array
    {
        return [
                   FinancialStatsWidget::class,
                   YapeClientesTableWidget::class,
                   YapesTotalesDelDiaWidget::class,
                   ClientesPorRenovarWidget::class

        ];
    }
}