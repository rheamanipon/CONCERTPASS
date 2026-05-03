<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Concert;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConcertApiController extends Controller
{
    public function index()
    {
        return response()->json(Concert::with('venue')->orderByDesc('id')->paginate(20));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:20000',
            'artist' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $concert = Concert::create($data);
        return response()->json($concert, 201);
    }

    public function show(Concert $concert)
    {
        return response()->json($concert->load('venue'));
    }

    public function update(Request $request, Concert $concert)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:20000',
            'artist' => 'required|string|max:255',
            'venue_id' => 'required|exists:venues,id',
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
        ]);

        $concert->update($data);
        return response()->json($concert);
    }

    public function destroy(Concert $concert)
    {
        if ($concert->hasSoldTickets()) {
            return response()->json([
                'message' => 'Cannot delete this concert because tickets have already been sold.',
            ], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $concert->delete();
        return response()->json(['message' => 'Concert deleted']);
    }
}
