<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
      protected $fillable = [
        'wallet_id', 'type', 'amount', 'book_copy_barcode'
    ];

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }
}
