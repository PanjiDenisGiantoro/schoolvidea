<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Unit;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialRegistrationConfirmationnext extends Mailable
{
    use SerializesModels;

    public $user;
    public $unit;

    // Konstruktor untuk menerima data user dan unit
    public function __construct(User $user, Unit $unit)
    {
        $this->user = $user;
        $this->unit = $unit;
    }

    public function build()
    {
        return $this->subject('Pendaftaran Portal VideaClass Berhasil')
            ->view('mail.trial-registrationnext')
            ->with([
                'email' => $this->user->email,
                'password' => '123456',  // Sebaiknya tidak kirim password asli
                'unit_code' => $this->unit->code, // unit code
            ]);
    }
}
