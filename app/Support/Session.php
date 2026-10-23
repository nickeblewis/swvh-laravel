<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/** One dated session of a regular activity, in the hall's time zone. */
final readonly class Session
{
    public function __construct(
        public CarbonImmutable $startsAt,
        public CarbonImmutable $endsAt,
    ) {}

    public function isHappening(?DateTimeInterface $now = null): bool
    {
        $now ??= now();

        return $this->startsAt <= $now && $now < $this->endsAt;
    }

    /** "On now", "Today · 7:30 PM", "Tomorrow · 10:00 AM" or "Sun 4 Oct · 10:00 AM" */
    public function label(?DateTimeInterface $now = null): string
    {
        $now = CarbonImmutable::instance($now ?? now())->setTimezone(Schedule::TIME_ZONE);

        if ($this->isHappening($now)) {
            return 'On now';
        }

        $startsAt = $this->startsAt->setTimezone(Schedule::TIME_ZONE);

        $day = match (true) {
            $startsAt->isSameDay($now) => 'Today',
            $startsAt->isSameDay($now->addDay()) => 'Tomorrow',
            default => $startsAt->format('D j M'),
        };

        return "{$day} · {$startsAt->format('g:i A')}";
    }

    /**
     * As sent to the browser, with the label already worked out so the server
     * render and the browser always agree on "Today". `highlight` (amber)
     * means today or under way.
     *
     * @return array{startsAt: string, endsAt: string, label: string, highlight: bool}
     */
    public function toArray(?DateTimeInterface $now = null): array
    {
        $now = CarbonImmutable::instance($now ?? now());

        return [
            'startsAt' => $this->startsAt->toIso8601ZuluString(),
            'endsAt' => $this->endsAt->toIso8601ZuluString(),
            'label' => $this->label($now),
            'highlight' => $this->isHappening($now)
                || $this->startsAt->setTimezone(Schedule::TIME_ZONE)->isSameDay($now->setTimezone(Schedule::TIME_ZONE)),
        ];
    }
}
