<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\ReverificationMail;
use App\Models\Rab;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class BlastReverificationMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blast:reverification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        if ($this->confirm('This will send the verification mail to all unverified users. Are you sure want to continue?')) {
            foreach ($this->getUnverifiedUsers() as $unverifiedUser) {

                $token = Crypt::encryptString($unverifiedUser->email);

                $unverifiedUser->verification_token = $token;
                $unverifiedUser->save();

                $this->sendWhatsappMessage($unverifiedUser, $token);
                $this->sendMail($unverifiedUser, $token);
            }
        }
        return 0;
    }

    private function getUnverifiedUsers()
    {
        return User::where('email_verified_at', null)->get();
    }

    private function sendWhatsappMessage(User $user, $token)
    {

        $this->info('Sending whatsapp message to user ' . $user->email . ' with phone number ' . $user->phone);

        if (!$user->phone) {
            $this->error('Phone field is empty');
            return false;
        }

        $checkNumber = Http::asForm()->post('https://app.ruangwa.id/api/check_number', [
            'token' => env('RUANG_WA_TOKEN'),
            'number' => '6289681925152'
        ]);

        if ($checkNumber->status() < 200 || $checkNumber->status() >= 300) {
            $this->error('Error when contacting RuangWA Gateway!');
            return false;
        }

        $checkNumberResult = $checkNumber->collect();

        if ($checkNumberResult->get('onwhatsapp') == 'false' || $checkNumberResult->get('result') == 'false') {
            $this->error('Wrong number / not registered to whatsapp');
            $this->info('Trying to send message through SMS service');

            $sendSMSMessage = Http::asForm()->post('https://app.ruangwa.id/api/send_sms', [
                'accesskey' => env('RUANG_WA_ACCESS_KEY'),
                'number' => $user->phone,
                'message' => $this->generateMessage($user, $token)
            ]);

            if ($sendSMSMessage->status() == 200) {
                $sendSMSMessageResult = $sendSMSMessage->collect();
                if ($sendSMSMessageResult->get('status') == 'success') {
                    $this->info('Send SMS to ' . $user->phone . ' success in queue');
                    return true;
                } else {
                    $this->error('There\'s an error when sending a SMS message. Maybe next time :)');
                    return false;
                }
            }
            return false;
        }

        $sendWhatsappMessage = Http::asForm()->post('https://app.ruangwa.id/api/send_message', [
            'token' => env('RUANG_WA_TOKEN'),
            'number' => $user->phone,
            'message' => $this->generateMessage($user, $token),
        ]);

        if ($checkNumber->status() < 200 || $checkNumber->status() >= 300) {
            $this->error('Failed to send whatsapp message! Error related to communication between app and WA Gateway');
            return false;
        }

        $sendMessageResult = $sendWhatsappMessage->collect();

        if ($sendMessageResult->get('status') == 'error' || $sendMessageResult->get('result') == 'false') {
            $this->error('Failed to send whatsapp message!');
            if ($sendMessageResult->has('message')) {
                $this->error($sendMessageResult->get('message'));
            }
            return false;
        }

        if ($sendMessageResult->get('status') == 'sent') {
            $this->info('Message sent to ' . $user->phone);
            return true;
        }
    }

    private function sendMail(User $user, $token)
    {
        $this->info('Sending mail for ' . $user->email . '.');
        Mail::to($user->email)->send(new ReverificationMail($user, $token));
        $this->info('Email sended to ' . $user->email . '.');
    }

    private function generateMessage(User $user, $token)
    {
        return 'Hi ' . $user->first_name . ' ' . $user->last_name . '.

Terimakasih ya sudah tertarik dan menggunakan layanan rencanakan id!

Mohon maaf jika saat ini kamu mengalami kesulitan dalam langkah verifikasi email, jangan khawatir ya

bagi teman teman yang belum bisa masuk karena gagal verifikasi bisa langsung klik link di bawah ini


' . route('register.confirm_email', ['token' => $token]) . '

Selamat bereksplorasi di platform kami ya!

Salam Rencanakan!
CS Team.
        ';
    }
}
