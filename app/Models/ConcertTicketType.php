<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConcertTicketType extends Model
{
    protected $table = 'concert_ticket_options';

    protected $fillable = [
        'concert_id',
        'ticket_type_id',
        'custom_name',
        'price',
        'color',
        'quantity',
    ];

    protected $casts = [
        'price' => 'decimal:2',
    ];

    public function concert()
    {
        return $this->belongsTo(Concert::class);
    }

    public function ticketType()
    {
        return $this->belongsTo(TicketType::class);
    }

    public function getSectionAttribute()
    {
        return $this->custom_name ?: ($this->ticketType?->name ?? 'Unknown');
    }
}
