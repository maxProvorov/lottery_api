<?php

namespace App\Http\Controllers;

use App\Models\EstatePool;
use App\Models\EstatePoolGift;
use App\Models\EstatePoolUserTicket;
use App\Models\UserBalance;
use App\Models\EstatePoolTicket;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class EstatePoolController extends Controller
{
    public function createPool(Request $request)
    {
        $validated = $request->validate([
            'sum_goal' => 'required|numeric|min:0',
        ]);

        $pool = EstatePool::create([
            'date_start' => Carbon::now(),
            'date_close' => Carbon::now()->addDays(30), 
            'sum_goal' => $validated['sum_goal'] ?? 0,
            'sum' => 0,
            'status' => 0,
        ]);

        return response()->json([
            'success' => true,
            'id_pool' => $pool->id,
        ], 201);
    }

    public function addGift(Request $request)
    {
        $validated = $request->validate([
            'id_pool' => 'required|exists:estatepool,id',
            'name' => 'required|string|max:255',
            'general' => 'nullable|boolean',
        ]);

        $idPool = $validated['id_pool'];
        $name = $validated['name'];
        $general = $validated['general'] ?? 0;

        if ($general == 1) {
            $hasGeneralGift = EstatePoolGift::where('id_pool', $idPool)
                ->where('general', 1)
                ->exists();

            if ($hasGeneralGift) {
                return response()->json([
                    'success' => false,
                    'message' => 'This pool already contains the main gift.',
                ], 400);
            }
        }

        $gift = EstatePoolGift::create([
            'id_pool' => $idPool,
            'name' => $name,
            'general' => $general,
            'date_close' => now()->addDays(30)->format('Y-m-d H:i:s'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Gift added successfully.',
            'data' => $gift,
        ]);
    }

    public function buyTickets(Request $request)
    {
        $validated = $request->validate([
            'id_pool' => 'required|exists:estatepool,id',
            'id_tickets' => 'required|exists:estatepool_tickets,id',
            'id_user' => 'required|exists:users,id',
        ]);

        $idPool = $validated['id_pool'];
        $idTickets = $validated['id_tickets'];
        $idUser = $validated['id_user'];

        $pool = EstatePool::findOrFail($idPool);
        $ticket = EstatePoolTicket::findOrFail($idTickets);
        $userBalance = UserBalance::where('id_user', $idUser)->first();

        if (!$userBalance) {
            return response()->json([
                'success' => false,
                'message' => 'User balance not found.',
            ], 404);
        }

        if ($userBalance->sum < $ticket->sum) {
            return response()->json([
                'success' => false,
                'message' => 'Insufficient funds to purchase a ticket.',
            ], 400);
        }

        if ($ticket->count <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'There are no tickets available for purchase.',
            ], 400);
        }

        $userBalance->sum -= $ticket->sum;
        $userBalance->save();

        $pool->sum += $ticket->sum;
        $pool->save();

        $ticket->count -= 1;
        $ticket->save();

        $tickets = [];
        for ($i = 0; $i < 1; $i++) {
            $tickets[] = [
                'ticket' => strtoupper(substr(uniqid('TCK'), 0, 9)),
                'id_ticket' => $idTickets,
                'id_user' => $idUser,
                'id_pool' => $idPool,
                'id_gift' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        EstatePoolUserTicket::insert($tickets);

        return response()->json([
            'success' => true,
            'message' => 'Tickets purchased successfully.',
            'data' => $tickets,
        ]);
    }

    public function checkGift(Request $request)
    {
        $validated = $request->validate([
            'id_pool' => 'required|exists:estatepool,id',
        ]);

        $idPool = $validated['id_pool'];

        $pool = EstatePool::with('gifts')->findOrFail($idPool);

        $currentSum = $pool->sum;
        $goalSum = $pool->sum_goal;

        $gifts = $pool->gifts;

        $mainGift = $gifts->where('general', 1)->first();
        if (!$mainGift) {
            return response()->json([
                'success' => false,
                'message' => 'Главный подарок не найден.',
            ], 404);
        }

        $additionalGifts = $gifts->where('general', 0);
        $numAdditionalGifts = $additionalGifts->count();

        if ($numAdditionalGifts > 0) {
            $checkpointValue = $goalSum / ($numAdditionalGifts + 1);
        } else {
            $checkpointValue = $goalSum;
        }

        $results = [];

        foreach ($gifts as $gift) {
            if (!is_null($gift->date_close)) {
                continue;
            }

            $checkpoint = $gift->general ? $goalSum : $checkpointValue * ($gifts->where('id', '<', $gift->id)->count() + 1);

            if ($currentSum >= $checkpoint) {
                $winner = null;

                if (!is_null($gift->id_fast_winner)) {
                    $winner = EstatePoolUserTicket::where('id_user', $gift->id_fast_winner)
                        ->where('id_pool', $idPool)
                        ->first();

                    if (!$winner) {
                        $winner = EstatePoolUserTicket::create([
                            'ticket' => strtoupper(substr(uniqid('WIN'), 0, 9)),
                            'id_ticket' => null,
                            'id_user' => $gift->id_fast_winner,
                            'id_pool' => $idPool,
                            'id_gift' => $gift->id,
                            'win' => 1,
                        ]);
                    }
                } else {
                    $winner = EstatePoolUserTicket::where('id_pool', $idPool)
                        ->inRandomOrder()
                        ->first();

                    if ($winner) {
                        $winner->win = 1;
                        $winner->id_gift = $gift->id;
                        $winner->save();
                    }
                }

                if ($winner) {
                    $gift->date_close = now()->timestamp;
                    $gift->save();

                    $results[] = [
                        'gift_id' => $gift->id,
                        'winner_ticket' => $winner->ticket,
                        'winner_user_id' => $winner->id_user,
                    ];
                }
            }
        }

        return response()->json([
            'success' => true,
            'results' => $results,
        ]);
    }

    public function getInfo(Request $request)
    {
        $idPool = $request->query('id_pool');

        if (!$idPool) {
            return response()->json([
                'success' => false,
                'message' => 'id_pool parameter is required',
            ], 400);
        }

        $pool = EstatePool::with('gifts')->find($idPool);

        if (!$pool) {
            return response()->json([
                'success' => false,
                'message' => 'No pool found for the given id_pool',
            ], 404);
        }

        $formattedGifts = $pool->gifts->map(function ($gift, $index) use ($pool) {
            $dynamicSum = ($index + 1) * ($pool->sum_goal / max($pool->gifts->count(), 1));
            return [
                'name' => $gift->name,
                'sum' => round($dynamicSum, 2), 
                'date_close' => $gift->date_close ?? null,
                'id_winner' => $gift->id_winner ?? 0,
                'general' => $gift->general,
            ];
        });

        $response = [
            'success' => true,
            'id_pool' => $pool->id,
            'status' => $pool->status,
            'sum_goal' => $pool->sum_goal,
            'sum' => $pool->sum,
            'gifts' => $formattedGifts,
        ];

        return response()->json($response);
    }

    public function getMyTickets(Request $request)
    {
        $idUser = $request->query('id_user');
        $idPool = $request->query('id_pool');

        if (!$idUser || !$idPool) {
            return response()->json([
                'success' => false,
                'message' => 'id_user and id_pool parameters are required',
            ], 400);
        }

        $user = User::find($idUser);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found',
            ], 404);
        }

        $pool = EstatePool::find($idPool);
        if (!$pool) {
            return response()->json([
                'success' => false,
                'message' => 'Pool not found',
            ], 404);
        }

        $tickets = EstatePoolUserTicket::where('id_user', $idUser)
            ->where('id_pool', $idPool)
            ->get();

        $data = $tickets->map(function ($ticket) {
            return [
                'ticket' => $ticket->ticket_number,
                'id_pool' => $ticket->id_pool,
                'win' => $ticket->win ? 1 : 0,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
        ]);
    }

    public function getPoolInfo(Request $request)
    {
        $idPool = $request->query('id_pool');

        if (!$idPool) {
            return response()->json([
                'success' => false,
                'message' => 'id_pool parameter is required',
            ], 400);
        }

        $pool = EstatePool::find($idPool);
        if (!$pool) {
            return response()->json([
                'success' => false,
                'message' => 'Pool not found',
            ], 404);
        }

        $currentDate = now();
        $status = $pool->gifts()->where('date_close', '>', $currentDate)->exists() ? 0 : 1;

        $winners = $pool->gifts->map(function ($gift, $index) {
            return [
                'num' => $index + 1,
                'id_gift' => $gift->id,
                'id_user' => $gift->id_winner,
                'ticket' => $gift->winner_ticket,
                'name' => $gift->name,
                'date_close' => $gift->date_close ? strtotime($gift->date_close) : 0,
            ];
        });

        $gifts = $pool->gifts->map(function ($gift) use ($pool) {
            $giftSum = ($pool->sum_goal / $pool->gifts->count()) * ($gift->point ?: 1);

            return [
                'name' => $gift->name,
                'point' => $gift->point,
                'sum' => round($giftSum, 2),
                'date_close' => $gift->date_close,
                'id_winner' => $gift->id_winner ?: 0,
                'general' => $gift->general,
            ];
        });

        return response()->json([
            'success' => true,
            'id_pool' => $pool->id,
            'sum_goal' => $pool->sum_goal,
            'sum' => $pool->sum,
            'status' => $status,
            'winners' => $winners,
            'gifts' => $gifts,
        ]);
    }

public function getMyTicketsInfo(Request $request)
{
    $idUser = $request->input('id_user');
    $idPool = $request->input('id_pool');

    $countTickets = EstatePoolUserTicket::where('id_user', $idUser)
        ->where('id_pool', $idPool)
        ->count();

    $ticketPrice = EstatePoolTicket::join('estatepool_usertickets', 'estatepool_tickets.id', '=', 'estatepool_usertickets.id_ticket')
        ->where('estatepool_usertickets.id_pool', $idPool)
        ->min('estatepool_tickets.sum');

    $totalSpent = $ticketPrice * $countTickets;

    $totalTicketsInPool = EstatePoolUserTicket::where('id_pool', $idPool)->count(); 

    $percent = $totalTicketsInPool > 0 ? ($countTickets / $totalTicketsInPool) : 0;

    $userBalance = UserBalance::where('id_user', $idUser) 
        ->where('status', 1)
        ->first();

    $autobalance = $userBalance ? $userBalance->sum : 0;

    return response()->json([
        'success' => true,
        'id_pool' => $idPool,
        'id_user' => $idUser,
        'count_tickets' => $countTickets,
        'percent' => $percent,
        'sum' => $totalSpent,
        'autobalance' => $autobalance
    ]);
}




}
