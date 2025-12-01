<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Mail;
use App\Mail\OverdueBook;


Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');



Schedule::call(function () {
    $loans = DB::table('loans')
        ->whereNull('returned_at')
        ->whereNotNull('due_at')
        ->get();


    foreach ($loans as $loan) {
        // Send an email reminder to each user with overdue books (maintain log mailer, no need to send actual emails).
        Mail::to($loan->user)->send(new OverdueBook());
    }



})->cron('0 0 * * *');