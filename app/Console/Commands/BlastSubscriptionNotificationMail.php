<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use App\Mail\StartSubscriptionNotificationMail;
use App\Models\User;

class BlastSubscriptionNotificationMail extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blast:subscription';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Blast email for subscription notification';

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
            foreach (User::where('email_verified_at', '!=', null)->get() as $user) {
                $this->sendMail($user);
            }
        }
        return 0;
    }

    private function sendMail(User $user)
    {
        $this->info('Sending mail for ' . $user->email . '.');
        Mail::to($user->email)->send(new StartSubscriptionNotificationMail());
        $this->info('Email sended to ' . $user->email . '.');
    }
}
