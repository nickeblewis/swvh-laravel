<?php

use App\Support\Schedule;
use App\Support\Session;
use Carbon\CarbonImmutable;

// Ported from the Nuxt site's tests/schedule.test.ts.

/** A moment given as wall-clock time at the hall. */
function london(int $year, int $month, int $day, int $hour = 0, int $minute = 0): CarbonImmutable
{
    return CarbonImmutable::create($year, $month, $day, $hour, $minute, 0, 'Europe/London');
}

/** @return object{title: string, day: ?string, time: ?string} */
function club(?string $day, ?string $time = '7:00 PM - 9:00 PM', ?string $title = null): object
{
    return (object) ['title' => $title ?? "Club {$day}", 'day' => $day, 'time' => $time];
}

function hourFrom(CarbonImmutable $startsAt): Session
{
    return new Session($startsAt, $startsAt->addHour());
}

// Thursday 24 September 2026, 12:00 London time (BST).
beforeEach(fn () => $this->now = london(2026, 9, 24, 12, 0));

describe('next', function () {
    it('finds the next matching weekday', function () {
        $session = Schedule::next('Tuesday', '7:00 PM - 9:00 PM', $this->now);

        expect($session->startsAt->equalTo(london(2026, 9, 29, 19, 0)))->toBeTrue()
            ->and($session->endsAt->equalTo(london(2026, 9, 29, 21, 0)))->toBeTrue();
    });

    it('includes later today', function () {
        expect(Schedule::next('Thursday', '7:00 PM - 9:00 PM', $this->now)->startsAt->equalTo(london(2026, 9, 24, 19, 0)))->toBeTrue();
    });

    it('includes a session that is happening now', function () {
        $session = Schedule::next('Thursday', '11:00 AM - 1:00 PM', $this->now);

        expect($session->startsAt->equalTo(london(2026, 9, 24, 11, 0)))->toBeTrue()
            ->and($session->label($this->now))->toBe('On now');
    });

    it("moves to next week once today's session has finished", function () {
        expect(Schedule::next('Thursday', '9:00 AM - 10:00 AM', $this->now)->startsAt->equalTo(london(2026, 10, 1, 9, 0)))->toBeTrue();
    });

    it('handles several days', function () {
        expect(Schedule::next('Monday, Thursday', '7:00 PM', $this->now)->startsAt->equalTo(london(2026, 9, 24, 19, 0)))->toBeTrue()
            ->and(Schedule::next('Monday & Tuesday', '7:00 PM', $this->now)->startsAt->equalTo(london(2026, 9, 28, 19, 0)))->toBeTrue()
            ->and(Schedule::next('Saturday and Sunday', '7:00 PM', $this->now)->startsAt->equalTo(london(2026, 9, 26, 19, 0)))->toBeTrue();
    });

    it('accepts plural day names', function () {
        expect(Schedule::next('Saturdays', '7:00 PM', $this->now)->startsAt->equalTo(london(2026, 9, 26, 19, 0)))->toBeTrue();
    });

    it('handles nth weekday of the month', function () {
        expect(Schedule::next('1st Sunday of month', '10:00 AM - 1:00 PM', $this->now)->startsAt->equalTo(london(2026, 10, 4, 10, 0)))->toBeTrue()
            ->and(Schedule::next('3rd Sunday of the month', '10:00 AM - 1:00 PM', $this->now)->startsAt->equalTo(london(2026, 10, 18, 10, 0)))->toBeTrue();
    });

    it('handles the last weekday of the month', function () {
        expect(Schedule::next('Last Friday of month', '7:00 PM', $this->now)->startsAt->equalTo(london(2026, 9, 25, 19, 0)))->toBeTrue();
    });

    it('treats a session finishing after midnight as ending the next day', function () {
        expect(Schedule::next('Friday', '8:00 PM - 12:30 AM', $this->now)->endsAt->equalTo(london(2026, 9, 26, 0, 30)))->toBeTrue();
    });

    it('lasts an hour when there is no end time', function () {
        $session = Schedule::next('Friday', '8pm', $this->now);

        expect($session->startsAt->equalTo(london(2026, 9, 25, 20, 0)))->toBeTrue()
            ->and($session->endsAt->equalTo(london(2026, 9, 25, 21, 0)))->toBeTrue();
    });

    it("returns null when the day or time can't be parsed", function () {
        expect(Schedule::next('By arrangement', '7:00 PM', $this->now))->toBeNull()
            ->and(Schedule::next('Monday', 'Evenings', $this->now))->toBeNull()
            ->and(Schedule::next(null, null, $this->now))->toBeNull();
    });

    it('keeps wall-clock times across the clocks changing', function () {
        // Clocks go back on Sunday 25 October 2026. A 7 PM Tuesday session is
        // 18:00 UTC before the change and 19:00 UTC after it.
        $before = Schedule::next('Tuesday', '7:00 PM - 9:00 PM', london(2026, 10, 19, 12, 0));
        $after = Schedule::next('Tuesday', '7:00 PM - 9:00 PM', london(2026, 10, 26, 12, 0));

        expect($before->startsAt->toIso8601ZuluString())->toBe('2026-10-20T18:00:00Z')
            ->and($after->startsAt->toIso8601ZuluString())->toBe('2026-10-27T19:00:00Z');
    });

    it("uses the hall's date, not the server's, around midnight", function () {
        // 00:30 on Friday in London is still Thursday 23:30 in UTC.
        $justAfterMidnight = london(2026, 9, 25, 0, 30)->utc();

        expect(Schedule::next('Thursday', '7:00 PM', $justAfterMidnight)->startsAt->equalTo(london(2026, 10, 1, 19, 0)))->toBeTrue()
            ->and(Schedule::next('Friday', '7:00 PM', $justAfterMidnight)->startsAt->equalTo(london(2026, 9, 25, 19, 0)))->toBeTrue();
    });
});

describe('byNext', function () {
    it('returns every activity, soonest first, unscheduled ones last', function () {
        $schedule = Schedule::byNext([
            club('By arrangement', title: 'Unscheduled Club'),
            club('Tuesday', title: 'Tuesday Club'),
            club('Thursday', title: 'Tonight Club'),
        ], $this->now);

        expect(array_map(fn (array $pair) => $pair[0]->title, $schedule))->toBe(['Tonight Club', 'Tuesday Club', 'Unscheduled Club'])
            ->and($schedule[2][1])->toBeNull()
            ->and($schedule[0][1]->startsAt->equalTo(london(2026, 9, 24, 19, 0)))->toBeTrue();
    });
});

describe('upcoming', function () {
    it('returns scheduled activities soonest first, limited', function () {
        $sessions = Schedule::upcoming([
            club('Tuesday', title: 'Tuesday Club'),
            club('Friday', title: 'Friday Club'),
            club('Thursday', title: 'Tonight Club'),
            club('By arrangement', title: 'Unscheduled Club'),
        ], 2, $this->now);

        expect(array_map(fn (array $pair) => $pair[0]->title, $sessions))->toBe(['Tonight Club', 'Friday Club']);
    });
});

describe('label', function () {
    it('labels sessions relative to today', function () {
        expect(hourFrom($this->now->subMinutes(30))->label($this->now))->toBe('On now')
            ->and(hourFrom(london(2026, 9, 24, 19, 30))->label($this->now))->toBe('Today · 7:30 PM')
            ->and(hourFrom(london(2026, 9, 25, 10, 0))->label($this->now))->toBe('Tomorrow · 10:00 AM')
            ->and(hourFrom(london(2026, 10, 4, 10, 0))->label($this->now))->toBe('Sun 4 Oct · 10:00 AM')
            ->and(hourFrom(london(2026, 10, 6, 12, 0))->label($this->now))->toBe('Tue 6 Oct · 12:00 PM');
    });

    it('highlights sessions that are today or under way', function () {
        expect(hourFrom(london(2026, 9, 24, 19, 30))->toArray($this->now)['highlight'])->toBeTrue()
            ->and(hourFrom(london(2026, 9, 25, 10, 0))->toArray($this->now)['highlight'])->toBeFalse();
    });
});

describe('todayLabel', function () {
    it('names the day at the hall', function () {
        expect(Schedule::todayLabel($this->now))->toBe('Thursday 24 September')
            ->and(Schedule::todayLabel(london(2026, 9, 25, 0, 30)->utc()))->toBe('Friday 25 September');
    });
});
