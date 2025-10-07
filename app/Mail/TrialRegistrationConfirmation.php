<?php

namespace App\Mail;

use App\Models\TrialRegistration;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TrialRegistrationConfirmation extends Mailable
{
    use SerializesModels;

    public $trialRegistration; // Menyimpan data pendaftaran

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(TrialRegistration $trialRegistration)
    {
        // Menginisialisasi trialRegistration
        $this->trialRegistration = $trialRegistration;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        // Menggunakan view 'emails.trial-registration' untuk konten email
        return $this->subject('Pendaftaran Portal VideaClass Berhasil')
            ->view('mail.trial-registration')
            ->with([
                'setupPortalUrl' => route('landing.registration_portal', ['id' => $this->trialRegistration->id]),
            ]);
    }

}
