<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ExpenseResource extends JsonResource
{
    
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'description' => $this->description,
            'expense_date' => $this->expense_date,
            'category' => [
                'id' => $this->category?->id,
                'name' => $this->category?->name,
            ],
            'user' => [
                'id' => $this->user?->id,
                'name' => $this->user?->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
