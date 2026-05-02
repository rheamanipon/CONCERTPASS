<?php

namespace Database\Seeders;

use App\Models\Concert;
use App\Models\ConcertTicketType;
use App\Models\Seat;
use App\Models\TicketType;
use App\Models\Venue;
use App\Services\VenueSeatPoolService;
use Illuminate\Database\Seeder;

class ConcertSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get venues
        $manilaVenue = Venue::where('location', 'like', '%Manila%')->first();
        $nyVenue = Venue::where('location', 'like', '%New York%')->first();

        if (!$manilaVenue || !$nyVenue) {
            return; // Skip if venues not found
        }

        // Create ticket types if not exist
        $ticketTypesData = [
            ['name' => 'VIP Standing', 'description' => 'VIP Standing'],
            ['name' => 'VIP Seated', 'description' => 'VIP Seated'],
            ['name' => 'LBB', 'description' => 'Lower Box B'],
            ['name' => 'UBB', 'description' => 'Upper Box B'],
            ['name' => 'LBA', 'description' => 'Lower Box A'],
            ['name' => 'UBA', 'description' => 'Upper Box A'],
            ['name' => 'GEN AD', 'description' => 'General Admission'],
        ];

        $ticketTypeIds = [];
        foreach ($ticketTypesData as $data) {
            $ticketType = TicketType::firstOrCreate(
                ['name' => $data['name']],
                ['description' => $data['description']]
            );
            $ticketTypeIds[] = $ticketType->id;
        }

        $concerts = [
            [
                'title' => 'Born Pink World Tour Manila',
                'description' => 'Epic comeback concert by BLACKPINK featuring hits from Born Pink album.',
                'artist' => 'BLACKPINK',
                'date' => '2026-12-01',
                'time' => '19:00:00',
                'venue_id' => $manilaVenue->id,
                'ticket_types' => ['VIP Standing', 'UBB', 'LBB', 'GEN AD'],
                'prices' => [15000.00, 6500.00, 8000.00, 800.00],
                'colors' => ['#FFD700', '#1E90FF', '#32CD32', '#F4A460'],
            ],
            [
                'title' => 'Permission to Dance On Stage Manila',
                'description' => 'BTS brings their stadium-filling Permission to Dance tour to Manila!',
                'artist' => 'BTS',
                'date' => '2026-12-08',
                'time' => '19:00:00',
                'venue_id' => $manilaVenue->id,
                'ticket_types' => ['VIP Standing', 'VIP Seated', 'LBA', 'UBA', 'GEN AD'],
                'prices' => [18000.00, 12000.00, 6000.00, 4500.00, 1000.00],
                'colors' => ['#FFD700', '#FF6347', '#A020F0', '#00CED1', '#F4A460'],
            ],
            [
                'title' => 'BINIverse Concert',
                'description' => 'P-pop sensation BINI takes over the stage in their universe-themed concert.',
                'artist' => 'BINI',
                'date' => '2026-12-15',
                'time' => '20:00:00',
                'venue_id' => $manilaVenue->id,
                'ticket_types' => ['VIP Standing', 'UBB', 'LBB', 'GEN AD'],
                'prices' => [12000.00, 5500.00, 7000.00, 600.00],
                'colors' => ['#FFD700', '#1E90FF', '#32CD32', '#F4A460'],
            ],
            [
                'title' => 'Pagtatag! World Tour Manila',
                'description' => 'SB19 returns home for their powerful Pagtatag! World Tour performance.',
                'artist' => 'SB19',
                'date' => '2026-12-22',
                'time' => '19:00:00',
                'venue_id' => $manilaVenue->id,
                'ticket_types' => ['VIP Standing', 'VIP Seated', 'LBA', 'UBA', 'GEN AD'],
                'prices' => [16000.00, 11000.00, 5500.00, 4000.00, 900.00],
                'colors' => ['#FFD700', '#FF6347', '#A020F0', '#00CED1', '#F4A460'],
            ],
            [
                'title' => 'Follow Tour Manila',
                'description' => 'SEVENTEEN\'s dynamic 13-member group performs their Follow Tour in Manila.',
                'artist' => 'SEVENTEEN',
                'date' => '2027-01-05',
                'time' => '19:00:00',
                'venue_id' => $manilaVenue->id,
                'ticket_types' => ['VIP Standing', 'UBB', 'LBB', 'GEN AD'],
                'prices' => [17000.00, 7000.00, 8500.00, 900.00],
                'colors' => ['#FFD700', '#1E90FF', '#32CD32', '#F4A460'],
            ],
        ];

        foreach ($concerts as $concertData) {
            // Update existing concert fields and ensure ticket pricing is refreshed.
            $concert = Concert::updateOrCreate(
                ['title' => $concertData['title'], 'date' => $concertData['date']],
                [
                    'description' => $concertData['description'],
                    'artist' => $concertData['artist'],
                    'venue_id' => $concertData['venue_id'],
                    'time' => $concertData['time'],
                    'poster_url' => null,
                ]
            );

            ConcertTicketType::where('concert_id', $concert->id)->delete();

            $ticketTypes = $concertData['ticket_types'];
            $prices = $concertData['prices'];
            $colors = $concertData['colors'];
            $quantities = $concertData['quantities'] ?? [];
            $venue = Venue::find($concertData['venue_id']);
            if (!$venue || $venue->capacity <= 0) {
                continue;
            }

            $venueCapacity = $venue->capacity;
            $typeCount = count($ticketTypes);
            if ($typeCount === 0) {
                continue;
            }

            $baseQuantity = intdiv($venueCapacity, $typeCount);
            $remainder = $venueCapacity % $typeCount;

            foreach ($ticketTypes as $index => $ticketTypeName) {
                $ticketType = TicketType::where('name', $ticketTypeName)->first();
                if ($ticketType) {
                    $quantity = isset($quantities[$index])
                        ? $quantities[$index]
                        : $baseQuantity + ($index < $remainder ? 1 : 0);

                    ConcertTicketType::create([
                        'concert_id' => $concert->id,
                        'ticket_type_id' => $ticketType->id,
                        'price' => $prices[$index],
                        'color' => $colors[$index],
                        'quantity' => $quantity,
                    ]);
                }
            }

            $concert->load('concertTicketTypes.ticketType');
            app(VenueSeatPoolService::class)->syncPhysicalSeatsForVenueTicketTypes($venue, $concert->concertTicketTypes);
        }
    }
}

