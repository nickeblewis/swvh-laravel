<?php

namespace App\Support;

use Carbon\CarbonImmutable;
use DateTimeInterface;

/**
 * Works out when an activity next happens from its free-text `day` and `time`
 * fields, e.g. day "Monday, Thursday" / "1st Sunday of month" / "Last Friday of
 * month" and time "7:30 PM - 9:00 PM". Anything it can't parse has no next
 * session rather than throwing.
 *
 * All the calendar maths is done in the hall's time zone, whatever zone the
 * server runs in. A port of the Nuxt site's `server/utils/schedule.ts`.
 */
final class Schedule
{
    public const TIME_ZONE = 'Europe/London';

    private const MONTHLY_RULE = '/^(1st|2nd|3rd|4th|last)\s+(\w+)\s+of\s+(?:the\s+)?month$/i';

    private const LOOKAHEAD_DAYS = 62;

    private const DAY_NAMES = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

    /**
     * The next session at or after `$from`, including one that has started but
     * not finished. With no end time a session lasts an hour; an end at or
     * before the start means it finishes the following day.
     */
    public static function next(?string $day, ?string $time, ?DateTimeInterface $from = null): ?Session
    {
        $times = self::times($time);

        if ($times === []) {
            return null;
        }

        [$start, $end] = [$times[0], $times[1] ?? null];
        $from = CarbonImmutable::instance($from ?? now());
        $today = $from->setTimezone(self::TIME_ZONE)->startOfDay();

        for ($offset = 0; $offset <= self::LOOKAHEAD_DAYS; $offset++) {
            $date = $today->addDays($offset);

            if (! self::occursOn($day, $date)) {
                continue;
            }

            $startsAt = $date->setTime($start[0], $start[1]);
            $endsAt = $end ? $date->setTime($end[0], $end[1]) : $startsAt->addHour();

            if ($end && $endsAt <= $startsAt) {
                $endsAt = $date->addDay()->setTime($end[0], $end[1]);
            }

            if ($endsAt > $from) {
                return new Session($startsAt, $endsAt);
            }
        }

        return null;
    }

    /**
     * Every item paired with its next session, soonest first. Items whose
     * schedule can't be parsed follow, in the order given, with a null session.
     *
     * @template T of object{day: ?string, time: ?string}
     *
     * @param  iterable<T>  $items
     * @return list<array{0: T, 1: Session|null}>
     */
    public static function byNext(iterable $items, ?DateTimeInterface $from = null): array
    {
        $scheduled = [];
        $unscheduled = [];

        foreach ($items as $item) {
            $session = self::next($item->day, $item->time, $from);

            if ($session) {
                $scheduled[] = [$item, $session];
            } else {
                $unscheduled[] = [$item, null];
            }
        }

        // usort is stable, so sessions starting together keep the order given.
        usort($scheduled, fn (array $a, array $b): int => $a[1]->startsAt <=> $b[1]->startsAt);

        return [...$scheduled, ...$unscheduled];
    }

    /**
     * The next sessions across the items given, soonest first.
     *
     * @template T of object{day: ?string, time: ?string}
     *
     * @param  iterable<T>  $items
     * @return list<array{0: T, 1: Session}>
     */
    public static function upcoming(iterable $items, int $limit = 6, ?DateTimeInterface $from = null): array
    {
        /** @var list<array{0: T, 1: Session}> $scheduled */
        $scheduled = array_values(array_filter(self::byNext($items, $from), fn (array $pair): bool => $pair[1] !== null));

        return array_slice($scheduled, 0, $limit);
    }

    /** "Thursday 24 September", for the "Today is …" lines. */
    public static function todayLabel(?DateTimeInterface $now = null): string
    {
        return CarbonImmutable::instance($now ?? now())->setTimezone(self::TIME_ZONE)->format('l j F');
    }

    private static function occursOn(?string $day, CarbonImmutable $date): bool
    {
        $rules = preg_split('/,|&|\band\b/i', $day ?? '') ?: [];

        foreach ($rules as $rule) {
            if (self::ruleMatches(trim($rule), $date)) {
                return true;
            }
        }

        return false;
    }

    private static function ruleMatches(string $rule, CarbonImmutable $date): bool
    {
        if (! preg_match(self::MONTHLY_RULE, $rule, $monthly)) {
            return self::weekdayIndex($rule) === $date->dayOfWeek;
        }

        if (self::weekdayIndex($monthly[2]) !== $date->dayOfWeek) {
            return false;
        }

        if (strtolower($monthly[1]) === 'last') {
            return $date->addDays(7)->month !== $date->month;
        }

        return intdiv($date->day - 1, 7) + 1 === (int) $monthly[1];
    }

    /** "Tuesday" or "Tuesdays" => 2, anything else => -1 */
    private static function weekdayIndex(string $name): int
    {
        $wanted = strtolower(trim($name));

        foreach (self::DAY_NAMES as $index => $dayName) {
            $lower = strtolower($dayName);

            if ($wanted === $lower || $wanted === "{$lower}s") {
                return $index;
            }
        }

        return -1;
    }

    /**
     * "7:30 PM - 9:00 PM" => [[19, 30], [21, 0]]
     *
     * @return list<array{0: int, 1: int}>
     */
    private static function times(?string $time): array
    {
        preg_match_all('/(\d{1,2})(?::(\d{2}))?\s*([ap])\.?m\.?/i', $time ?? '', $matches, PREG_SET_ORDER);

        return array_map(function (array $match): array {
            $hour = ((int) $match[1]) % 12;

            if (strtolower($match[3]) === 'p') {
                $hour += 12;
            }

            return [$hour, $match[2] !== '' ? (int) $match[2] : 0];
        }, $matches);
    }
}
