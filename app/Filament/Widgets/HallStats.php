<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Activities\ActivityResource;
use App\Filament\Resources\Enquiries\EnquiryResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Spaces\SpaceResource;
use App\Filament\Resources\Users\UserResource;
use App\Models\Activity;
use App\Models\Enquiry;
use App\Models\Page;
use App\Models\Space;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

/** What's in the admin at a glance, as the Nuxt dashboard showed. */
class HallStats extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $pollingInterval = null;

    /**
     * @return list<Stat>
     */
    protected function getStats(): array
    {
        $unread = Enquiry::whereNull('read_at')->count();

        return [
            Stat::make('Regular activities', Activity::count())
                ->description(Activity::where('draft', true)->count().' drafts')
                ->url(ActivityResource::getUrl()),
            Stat::make('Pages', Page::count())->url(PageResource::getUrl()),
            Stat::make('Spaces', Space::count())->url(SpaceResource::getUrl()),
            Stat::make('Unread enquiries', $unread)
                ->color($unread > 0 ? 'warning' : 'gray')
                ->url(EnquiryResource::getUrl()),
            Stat::make('Admin users', User::count())->url(UserResource::getUrl()),
        ];
    }
}
