<?php

namespace App\Http\Controllers;

use App\Http\Requests\TicketRequest;
use App\Models\Ticket;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TicketController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $this->authorize('viewAny', Ticket::class);

        return Ticket::all();
    }

    public function store(TicketRequest $request)
    {
        $this->authorize('create', Ticket::class);

        return Ticket::create($request->validated());
    }

    public function show(Ticket $ticket)
    {
        $this->authorize('view', $ticket);

        return $ticket;
    }

    public function update(TicketRequest $request, Ticket $ticket)
    {
        $this->authorize('update', $ticket);

        $ticket->update($request->validated());

        return $ticket;
    }

    public function destroy(Ticket $ticket)
    {
        $this->authorize('delete', $ticket);

        $ticket->delete();

        return response()->json();
    }
}
